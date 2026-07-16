<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportConstituencyRequest;
use App\Http\Requests\Admin\StoreConstituencyRequest;
use App\Http\Requests\Admin\UpdateConstituencyRequest;
use App\Models\Constituency;
use App\Services\Admin\ConstituencyService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ConstituencyController extends Controller
{
    public function __construct(
        private ConstituencyService $constituencyService
    ) {}

    public function index()
    {
        $search = request('search');
        $constituencies = $this->constituencyService->getPaginatedConstituencies(15, $search);
        return view('constituencies.index', compact('constituencies'));
    }

    public function create()
    {
        $counties = $this->constituencyService->getOrderedCounties();
        return view('constituencies.create', compact('counties'));
    }

    public function store(StoreConstituencyRequest $request)
    {
        $data = $this->withStoredImage($request->validated(), $request->file('image'), 'constituencies');
        $this->constituencyService->createConstituency($data);

        return redirect()->route('constituencies.index')
            ->with('success', 'Constituency created successfully');
    }

    public function edit(Constituency $constituency)
    {
        $counties = $this->constituencyService->getOrderedCounties();
        return view('constituencies.edit', compact('constituency', 'counties'));
    }

    public function update(UpdateConstituencyRequest $request, Constituency $constituency)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $this->deleteImage($constituency->image);
            $data = $this->withStoredImage($data, $request->file('image'), 'constituencies');
        } else {
            unset($data['image']);
        }

        $this->constituencyService->updateConstituency($constituency, $data);

        return redirect()->route('constituencies.index')
            ->with('success', 'Constituency updated successfully');
    }

    public function destroy(Constituency $constituency)
    {
        $this->deleteImage($constituency->image);
        $this->constituencyService->deleteConstituency($constituency);

        return redirect()->route('constituencies.index')
            ->with('success', 'Constituency deleted successfully');
    }

    public function import(ImportConstituencyRequest $request)
    {
        $imported = $this->constituencyService->importConstituencies($request->constituencies);

        return response()->json([
            'message' => 'Constituencies imported successfully',
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
