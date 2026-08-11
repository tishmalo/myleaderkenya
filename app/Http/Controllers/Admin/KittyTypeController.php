<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKittyTypeRequest;
use App\Http\Requests\Admin\UpdateKittyTypeRequest;
use App\Models\KittyType;
use App\Services\Admin\KittyTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KittyTypeController extends Controller
{
    public function __construct(private KittyTypeService $kittyTypes) {}
    public function index(): View { return view('kitty-types.index', ['kittyTypes' => $this->kittyTypes->all()]); }
    public function store(StoreKittyTypeRequest $request): RedirectResponse
    {
        $this->kittyTypes->create($request->validated());
        return back()->with('success', 'Kitty type added.');
    }
    public function update(UpdateKittyTypeRequest $request, KittyType $kittyType): RedirectResponse
    {
        $this->kittyTypes->update($kittyType, $request->validated());
        return back()->with('success', 'Kitty type updated.');
    }
    public function destroy(KittyType $kittyType): RedirectResponse
    {
        try { $this->kittyTypes->delete($kittyType); }
        catch (ValidationException $e) { return back()->withErrors($e->errors()); }
        return back()->with('success', 'Kitty type deleted.');
    }
}
