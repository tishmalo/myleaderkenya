<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportBlocRequest;
use App\Http\Requests\Admin\StoreBlocRequest;
use App\Http\Requests\Admin\UpdateBlocRequest;
use App\Models\Bloc;
use App\Services\Admin\BlocService;

class BlocController extends Controller
{
    public function __construct(
        private BlocService $blocService
    ) {}

    public function import(ImportBlocRequest $request)
    {
        $imported = $this->blocService->importBlocs($request->blocs);

        return response()->json([
            'message' => 'Blocs imported successfully',
            'imported' => $imported
        ]);
    }

    public function index()
    {
        $blocs = $this->blocService->getPaginatedBlocs(15, request('search'));
        return view('blocs.index', compact('blocs'));
    }

    public function create()
    {
        return view('blocs.create', $this->blocService->getFormData());
    }

    public function store(StoreBlocRequest $request)
    {
        $this->blocService->createBloc($request->validated());

        return redirect()->route('blocs.index')
            ->with('success', 'Bloc created successfully');
    }

    public function edit(Bloc $bloc)
    {
        $bloc->load('counties');

        return view('blocs.edit', array_merge(
            $this->blocService->getFormData(),
            compact('bloc')
        ));
    }

    public function update(UpdateBlocRequest $request, Bloc $bloc)
    {
        $this->blocService->updateBloc($bloc, $request->validated());

        return redirect()->route('blocs.index')
            ->with('success', 'Bloc updated successfully');
    }

    public function destroy(Bloc $bloc)
    {
        $this->blocService->deleteBloc($bloc);

        return redirect()->route('blocs.index')
            ->with('success', 'Bloc deleted successfully');
    }
}