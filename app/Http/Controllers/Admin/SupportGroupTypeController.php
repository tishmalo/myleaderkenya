<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupportGroupTypeRequest;
use App\Http\Requests\Admin\UpdateSupportGroupTypeRequest;
use App\Models\SupportGroupType;
use App\Services\Admin\SupportGroupTypeService;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreSupportGroupTypeRequest $request): RedirectResponse
    {
        $result = $this->supportGroupTypes->create($request->validated(), $request->boolean('is_active', true));

        return back()->with($result['status'], $result['message']);
    }

    public function update(UpdateSupportGroupTypeRequest $request, SupportGroupType $supportGroupType): RedirectResponse
    {
        $result = $this->supportGroupTypes->update($supportGroupType, $request->validated(), $request->boolean('is_active'));

        return back()->with($result['status'], $result['message']);
    }

    public function destroy(SupportGroupType $supportGroupType): RedirectResponse
    {
        $result = $this->supportGroupTypes->delete($supportGroupType);

        return back()->with($result['status'], $result['message']);
    }
}
