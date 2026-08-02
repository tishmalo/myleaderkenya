<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignPriorityCategory;
use App\Models\CandidateCampaignPriority;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CampaignPriorityCategoryController extends Controller
{
    public function index(): View
    {
        $request = request();
        $categories = CampaignPriorityCategory::query()
            ->withCount('candidatePriorities')
            ->orderBy('sort_order')->orderBy('name')->get();
        $submissions = CandidateCampaignPriority::query()
            ->with(['candidate:id,name,slug', 'category:id,name,icon', 'submitter:id,name'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', (string) $request->input('status')))
            ->when($request->filled('candidate'), fn ($query) => $query->whereHas('candidate', fn ($candidateQuery) => $candidateQuery->where('name', 'like', '%'.trim((string) $request->candidate).'%')))
            ->latest('updated_at')->paginate(30)->withQueryString();

        return view('campaign-priorities.index', compact('categories', 'submissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
        CampaignPriorityCategory::create($data);

        return back()->with('success', 'Campaign priority category created.');
    }

    public function update(Request $request, CampaignPriorityCategory $campaignPriorityCategory): RedirectResponse
    {
        $data = $this->validated($request);
        if ($campaignPriorityCategory->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $campaignPriorityCategory->id);
        }
        $data['updated_by'] = $request->user()->id;
        $campaignPriorityCategory->update($data);
        $campaignPriorityCategory->candidatePriorities()->update(['sort_order' => $data['sort_order']]);

        return back()->with('success', 'Campaign priority category updated.');
    }

    public function destroy(CampaignPriorityCategory $campaignPriorityCategory): RedirectResponse
    {
        if ($campaignPriorityCategory->candidatePriorities()->exists()) {
            throw ValidationException::withMessages(['category' => 'Deactivate this category instead; aspirants have already used it.']);
        }
        $campaignPriorityCategory->delete();

        return back()->with('success', 'Unused campaign priority category deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['required', Rule::in(CampaignPriorityCategory::ICONS)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'campaign-priority';
        $slug = $base;
        $suffix = 2;
        while (CampaignPriorityCategory::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }
        return $slug;
    }
}