<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportBlocRequest;
use App\Http\Requests\Admin\StoreBlocRequest;
use App\Http\Requests\Admin\UpdateBlocRequest;
use App\Models\Bloc;
use App\Services\Admin\BlocService;
use Illuminate\Http\Request;

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
        $blocs = $this->blocService->getPaginatedBlocs(15);
        return view('blocs.index', compact('blocs'));
    }

    public function create()
    {
        return view('blocs.create');
    }

    public function store(StoreBlocRequest $request)
    {
        $this->blocService->createBloc($request->validated());

        return redirect()->route('blocs.index')
            ->with('success', 'Bloc created successfully');
    }

    public function edit(Bloc $bloc)
    {
        return view('blocs.edit', compact('bloc'));
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