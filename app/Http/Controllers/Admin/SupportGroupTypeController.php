<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportGroupType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupportGroupTypeController extends Controller
{
    public function index(): View
    {
        $supportGroupTypes = SupportGroupType::withCount('contacts')->ordered()->get();

        return view('support-group-types.index', compact('supportGroupTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:support_group_types,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        SupportGroupType::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Support group type added.');
    }

    public function update(Request $request, SupportGroupType $supportGroupType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:support_group_types,name,' . $supportGroupType->id],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supportGroupType->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Support group type updated.');
    }

    public function destroy(SupportGroupType $supportGroupType): RedirectResponse
    {
        if ($supportGroupType->contacts()->exists()) {
            return back()->with('warning', 'This group has contacts. Deactivate it instead of deleting it.');
        }

        $supportGroupType->delete();

        return back()->with('success', 'Support group type deleted.');
    }
}
