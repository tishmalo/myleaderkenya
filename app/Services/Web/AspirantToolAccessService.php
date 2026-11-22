<?php

namespace App\Services\Web;

use App\Models\Candidate;
use App\Models\User;
use App\Support\AspirantToolAccess;

class AspirantToolAccessService
{
    public function __construct(private AspirantWorkspaceService $workspace) {}

    public function resolve(User $user, string $key): AspirantToolAccess
    {
        $definition = $this->workspace->toolDefinitions()[$key] ?? null;

        if ($definition === null) {
            return new AspirantToolAccess(null, null, null, null, $this->workspace->scopeForCandidate(null));
        }

        $candidate = $this->workspace->candidateForUser($user);
        $tool = $this->workspace->publishedToolForKey($key);
        $denial = null;

        if (! $tool) {
            $denial = 'That campaign tool is not enabled yet. Ask an admin to publish it first.';
        } elseif (! $candidate) {
            $denial = 'No aspirant profile is linked to this account yet.';
        } else {
            $availability = $this->workspace->canUseTool($key, $candidate);

            if (! $availability['available']) {
                $denial = $availability['reason'];
            }
        }

        return new AspirantToolAccess(
            $key,
            $definition,
            $candidate,
            $tool,
            $this->workspace->scopeForCandidate($candidate),
            $denial
        );
    }

    public function candidateForUser(User $user): ?Candidate
    {
        return $this->workspace->candidateForUser($user);
    }
}
