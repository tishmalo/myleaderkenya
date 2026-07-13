<?php

namespace App\Services\Web;

use App\Models\CampaignTool;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AspirantWorkspaceService
{
    public function candidateForUser(User $user): ?Candidate
    {
        $relations = ['position', 'politicalParty'];

        if (Schema::hasTable('candidate_sms_settings')) {
            $relations[] = 'smsSetting';
        }

        $query = Candidate::with($relations);

        if (Schema::hasColumn('candidates', 'user_id')) {
            $candidate = (clone $query)->where('user_id', $user->id)->latest()->first();

            if ($candidate) {
                return $candidate;
            }
        }

        return $query->where(function (Builder $candidateQuery) use ($user): void {
            if (! empty($user->email)) {
                $candidateQuery->orWhere('email', $user->email);
            }

            if (! empty($user->phone)) {
                $candidateQuery->orWhere('phone', $user->phone);
            }
        })->latest()->first();
    }

    public function toolDefinitions(): array
    {
        return [
            'bulk-sms' => [
                'title' => 'Bulk SMS',
                'icon' => 'fa-comment-sms',
                'summary' => 'Send targeted messages to voters, agents, volunteers, and supporters.',
                'voter_facing' => true,
            ],
            'bulk-whatsapp' => [
                'title' => 'Bulk WhatsApp',
                'icon' => 'fa-brands fa-whatsapp',
                'summary' => 'Coordinate groups, county teams, and rapid campaign updates.',
                'voter_facing' => true,
            ],
            'opinion-polls' => [
                'title' => 'Opinion Polls',
                'icon' => 'fa-square-poll-vertical',
                'summary' => 'Run issue polls and track voter sentiment by location.',
                'voter_facing' => true,
            ],
            'campaign-website' => [
                'title' => 'Campaign Website',
                'icon' => 'fa-globe',
                'summary' => 'Publish your biography, agenda, donation links, photos, and updates.',
                'voter_facing' => false,
            ],
            'voter-database' => [
                'title' => 'Voter Database',
                'icon' => 'fa-database',
                'summary' => 'Organize supporter lists, locations, segments, and follow-ups.',
                'voter_facing' => true,
            ],
            'call-center' => [
                'title' => 'Call Center',
                'icon' => 'fa-headset',
                'summary' => 'Manage outreach calls, scripts, callbacks, and canvassing feedback.',
                'voter_facing' => true,
            ],
        ];
    }

    public function toolModules(Collection $campaignTools, ?Candidate $candidate = null): array
    {
        return collect($this->toolDefinitions())->map(function (array $module, string $key) use ($campaignTools, $candidate): array {
            $match = $this->matchingCampaignTool($campaignTools, $key, $module['title']);
            $availability = $this->toolAvailability($key, $match, $candidate);

            return array_merge($module, [
                'key' => $key,
                'tool' => $match,
                'url' => $availability['available'] ? route('aspirant.tools.show', $key) : '#',
                'available' => $availability['available'],
                'disabled_reason' => $availability['reason'],
            ]);
        })->values()->all();
    }

    public function publishedToolForKey(string $key): ?CampaignTool
    {
        $definition = $this->toolDefinitions()[$key] ?? null;

        if (! $definition) {
            return null;
        }

        return $this->matchingCampaignTool(CampaignTool::published()->ordered()->get(), $key, $definition['title']);
    }


    public function canUseTool(string $key, ?Candidate $candidate): array
    {
        $definition = $this->toolDefinitions()[$key] ?? null;

        if (! $definition) {
            return ['available' => false, 'reason' => 'Unknown campaign tool.'];
        }

        $tool = $this->publishedToolForKey($key);

        return $this->toolAvailability($key, $tool, $candidate);
    }

    public function scopeForCandidate(?Candidate $candidate): array
    {
        if (! $candidate) {
            return [
                'type' => 'missing',
                'column' => null,
                'value' => null,
                'label' => 'No aspirant profile',
                'missing' => true,
                'message' => 'No aspirant profile is linked to this account yet.',
            ];
        }

        $position = strtolower((string) ($candidate->position->name ?? ''));

        if (str_contains($position, 'president')) {
            return $this->scope('national', null, null, 'Kenya', false);
        }

        if (
            str_contains($position, 'governor')
            || str_contains($position, 'senator')
            || str_contains($position, 'woman representative')
            || str_contains($position, 'women representative')
            || str_contains($position, 'woman rep')
            || str_contains($position, 'women rep')
        ) {
            return $this->scope('county', 'county', $candidate->county, 'County');
        }

        if (str_contains($position, 'member of parliament') || preg_match('/\bmp\b/', $position)) {
            return $this->scope('constituency', 'constituency', $candidate->constituency, 'Constituency');
        }

        if (
            str_contains($position, 'mca')
            || str_contains($position, 'member of county assembly')
            || str_contains($position, 'county assembly')
        ) {
            return $this->scope('ward', 'ward', $candidate->ward, 'Ward');
        }

        foreach ([
            ['ward', 'ward', $candidate->ward, 'Ward'],
            ['constituency', 'constituency', $candidate->constituency, 'Constituency'],
            ['county', 'county', $candidate->county, 'County'],
        ] as [$type, $column, $value, $suffix]) {
            if (! empty($value)) {
                return $this->scope($type, $column, $value, $suffix);
            }
        }

        return $this->scope('missing', null, null, 'jurisdiction', true);
    }

    public function registeredVotersQuery(array $scope): Builder
    {
        $query = User::query()
            ->where(function (Builder $voterQuery): void {
                $voterQuery->where('is_voter', true)
                    ->orWhere('is_registered', true);
            });

        if ($scope['missing']) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope['column'] && $scope['value']) {
            $query->where($scope['column'], $scope['value']);
        }

        return $query;
    }


    private function toolAvailability(string $key, ?CampaignTool $tool, ?Candidate $candidate): array
    {
        if (! $tool) {
            return ['available' => false, 'reason' => 'Ask an admin to publish this campaign tool.'];
        }

        if ($key === 'bulk-sms') {
            if (! Schema::hasTable('candidate_sms_settings')) {
                return ['available' => false, 'reason' => 'Run the Bulk SMS settings migration before enabling this tool.'];
            }

            if (! $candidate) {
                return ['available' => false, 'reason' => 'Link an aspirant profile before using Bulk SMS.'];
            }

            $setting = $candidate->smsSetting;

            if (! $setting || ! $setting->isReady()) {
                return ['available' => false, 'reason' => 'Ask an admin to enable Bulk SMS and add Infobip credentials for this candidate.'];
            }
        }

        return ['available' => true, 'reason' => null];
    }

    private function matchingCampaignTool(Collection $campaignTools, string $key, string $title): ?CampaignTool
    {
        return $campaignTools->first(function (CampaignTool $tool) use ($key, $title): bool {
            $haystack = strtolower($tool->title . ' ' . $tool->nav_label . ' ' . $tool->excerpt . ' ' . $tool->slug);

            return str_contains($haystack, str_replace('-', ' ', $key))
                || str_contains($haystack, strtolower($title));
        });
    }

    private function scope(string $type, ?string $column, ?string $value, string $suffix, ?bool $missing = null): array
    {
        $value = $value ? trim($value) : null;
        $missing ??= $column !== null && empty($value);

        return [
            'type' => $type,
            'column' => $column,
            'value' => $value,
            'label' => $value ? $value . ' ' . $suffix : $suffix,
            'missing' => $missing,
            'message' => $missing ? 'Ask an admin to complete your campaign jurisdiction before using voter-facing tools.' : null,
        ];
    }
}
