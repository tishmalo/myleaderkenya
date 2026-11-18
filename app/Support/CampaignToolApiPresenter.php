<?php

namespace App\Support;

use App\Models\CandidateSupportContact;
use App\Models\CandidateTokenWallet;
use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use App\Models\CampaignWebsiteRequest;
use App\Models\SupportGroupType;
use Illuminate\Support\Facades\Storage;

class CampaignToolApiPresenter
{
    public static function tool(CampaignTool $tool, bool $includeExcerpt = false, bool $includeContent = false): array
    {
        $data = [
            'id' => $tool->id,
            'title' => $tool->title,
            'slug' => $tool->slug,
            'nav_label' => $tool->nav_label,
            'nav_title' => $tool->nav_title,
            'featured_image' => $tool->featured_image,
            'featured_image_url' => $tool->featured_image ? asset(Storage::url($tool->featured_image)) : null,
            'status' => $tool->status,
            'sort_order' => $tool->sort_order,
        ];

        if ($includeExcerpt) {
            $data['excerpt'] = $tool->excerpt;
            $data['meta_title'] = $tool->meta_title;
            $data['meta_description'] = $tool->meta_description;
        }

        if ($includeContent) {
            $data['content'] = $tool->content;
        }

        return $data;
    }

    public static function optionalTool(?CampaignTool $tool): ?array
    {
        return $tool ? self::tool($tool) : null;
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    public static function module(array $module): array
    {
        return [
            'key' => $module['key'],
            'title' => $module['title'],
            'icon' => $module['icon'],
            'summary' => $module['summary'],
            'voter_facing' => $module['voter_facing'],
            'available' => $module['available'],
            'disabled_reason' => $module['disabled_reason'],
            'tool' => self::optionalTool($module['tool']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function featureRequest(CampaignToolRequest $request): array
    {
        return [
            'id' => $request->id,
            'campaign_tool_id' => $request->campaign_tool_id,
            'campaign_tool' => self::optionalTool($request->campaignTool),
            'request_type' => $request->request_type ?: 'feature',
            'tool_key' => $request->tool_key,
            'tool_title' => $request->tool_title,
            'requester_name' => $request->requester_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'requested_feature' => $request->requested_feature,
            'use_case' => $request->use_case,
            'disabled_reason' => $request->disabled_reason,
            'status' => $request->status,
            'created_at' => optional($request->created_at)->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function websiteRequest(?CampaignWebsiteRequest $request): ?array
    {
        if (! $request) {
            return null;
        }

        return [
            'id' => $request->id,
            'candidate_name' => $request->candidate_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'preferred_domain' => $request->preferred_domain,
            'website_type' => $request->website_type,
            'reference_url' => $request->reference_url,
            'notes' => $request->notes,
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'created_at' => optional($request->created_at)->toISOString(),
            'updated_at' => optional($request->updated_at)->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function wallet(CandidateTokenWallet $wallet): array
    {
        return [
            'id' => $wallet->id,
            'candidate_id' => $wallet->candidate_id,
            'balance' => $wallet->balance,
            'initial_granted_at' => optional($wallet->initial_granted_at)->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function supportContact(CandidateSupportContact $contact): array
    {
        return [
            'id' => $contact->id,
            'support_group_type_id' => $contact->support_group_type_id,
            'support_group_type' => $contact->groupType ? [
                'id' => $contact->groupType->id,
                'name' => $contact->groupType->name,
                'slug' => $contact->groupType->slug,
            ] : null,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'created_at' => optional($contact->created_at)->toISOString(),
            'updated_at' => optional($contact->updated_at)->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function supportGroupType(SupportGroupType $type): array
    {
        return [
            'id' => $type->id,
            'name' => $type->name,
            'slug' => $type->slug,
            'sort_order' => $type->sort_order,
        ];
    }
}
