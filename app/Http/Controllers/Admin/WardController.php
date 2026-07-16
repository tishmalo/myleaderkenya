<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportWardRequest;
use App\Http\Requests\Admin\StoreWardRequest;
use App\Http\Requests\Admin\UpdateWardRequest;
use App\Models\Ward;
use App\Services\Admin\WardService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WardController extends Controller
{
    public function __construct(
        private WardService $wardService
    ) {}

    public function index()
    {
        $search = request('search');
        $wards = $this->wardService->getPaginatedWards(15, $search);
        return view('wards.index', compact('wards'));
    }

    public function create()
    {
        $constituencies = $this->wardService->getOrderedConstituency();
        return view('wards.create', compact('constituencies'));
    }

    public function store(StoreWardRequest $request)
    {
        $data = $this->withStoredImage($request->validated(), $request->file('image'), 'wards');
        $this->wardService->createWard($data);

        return redirect()->route('wards.index')
            ->with('success', 'Ward created successfully');
    }

    public function edit(Ward $ward)
    {
        $constituencies = $this->wardService->getOrderedConstituency();
        return view('wards.edit', compact('ward', 'constituencies'));
    }

    public function update(UpdateWardRequest $request, Ward $ward)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $this->deleteImage($ward->image);
            $data = $this->withStoredImage($data, $request->file('image'), 'wards');
        } else {
            unset($data['image']);
        }

        $this->wardService->updateWard($ward, $data);

        return redirect()->route('wards.index')
            ->with('success', 'Ward updated successfully');
    }

    public function destroy(Ward $ward)
    {
        $this->deleteImage($ward->image);
        $this->wardService->deleteWard($ward);

        return redirect()->route('wards.index')
            ->with('success', 'Ward deleted successfully');
    }

    public function import(ImportWardRequest $request)
    {
        $imported = $this->wardService->importWards($request->wards);

        return response()->json([
            'message' => 'Wards imported successfully',
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
