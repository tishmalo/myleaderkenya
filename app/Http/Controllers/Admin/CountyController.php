<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportCountyRequest;
use App\Http\Requests\Admin\StoreCountyRequest;
use App\Http\Requests\Admin\UpdateCountyRequest;
use App\Models\County;
use App\Services\Admin\CountyService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CountyController extends Controller
{
    public function __construct(
        private CountyService $countyService
    ) {}

    public function index()
    {
        $search = request('search');
        $counties = $this->countyService->getPaginatedCounties(15, $search);
        return view('counties.index', compact('counties'));
    }

    public function create()
    {
        $blocs = $this->countyService->getOrderedBlocs();
        return view('counties.create', compact('blocs'));
    }

    public function store(StoreCountyRequest $request)
    {
        $data = $this->withStoredImage($request->validated(), $request->file('image'), 'counties');
        $this->countyService->createCounty($data);

        return redirect()->route('counties.index')
            ->with('success', 'County created successfully');
    }

    public function edit(County $county)
    {
        $blocs = $this->countyService->getOrderedBlocs();
        return view('counties.edit', compact('county', 'blocs'));
    }

    public function update(UpdateCountyRequest $request, County $county)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $this->deleteImage($county->image);
            $data = $this->withStoredImage($data, $request->file('image'), 'counties');
        } else {
            unset($data['image']);
        }

        $this->countyService->updateCounty($county, $data);

        return redirect()->route('counties.index')
            ->with('success', 'County updated successfully');
    }

    public function destroy(County $county)
    {
        $this->deleteImage($county->image);
        $this->countyService->deleteCounty($county);

        return redirect()->route('counties.index')
            ->with('success', 'County deleted successfully');
    }

    public function import(ImportCountyRequest $request)
    {
        $imported = $this->countyService->importCounties($request->counties);

        return response()->json([
            'message' => 'Counties imported successfully',
            'imported' => $imported
        ]);
    }

    private function withStoredImage(array $data, ?UploadedFile $image, string $directory): array
    {
        if ($image) {
            $data['image'] = $image->store($directory, 'public');
        }

        return $data;
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
