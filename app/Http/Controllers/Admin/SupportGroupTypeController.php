<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportGroupType;
use App\Services\Admin\SupportGroupTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportGroupTypeController extends Controller
{
    public function __construct(
        private SupportGroupTypeService $supportGroupTypes
    ) {}

    public function index(): View
    {
        return view('support-group-types.index', [
            'supportGroupTypes' => $this->supportGroupTypes->list(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:support_group_types,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $result = $this->supportGroupTypes->create($data, $request->boolean('is_active', true));

        return back()->with($result['status'], $result['message']);
    }

    public function update(Request $request, SupportGroupType $supportGroupType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:support_group_types,name,'.$supportGroupType->id],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $result = $this->supportGroupTypes->update($supportGroupType, $data, $request->boolean('is_active'));

        return back()->with($result['status'], $result['message']);
    }

    public function destroy(SupportGroupType $supportGroupType): RedirectResponse
    {
        $result = $this->supportGroupTypes->delete($supportGroupType);

        return back()->with($result['status'], $result['message']);
    }
}
