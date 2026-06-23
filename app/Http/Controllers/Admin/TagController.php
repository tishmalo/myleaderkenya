<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\Admin\TagService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreTagRequest;

class TagController extends Controller
{
    public function __construct(
        private TagService $tagService
    ) {}

    public function index()
    {
        $tags = $this->tagService->getAllTags();
        return view('dashboard.index', compact('tags'));
    }

    /**
     * Store a newly created tag
     */
    public function store(StoreTagRequest $request)
    {

        $tag = $this->tagService->createTag($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'tag'     => $tag
        ]);
    }

    
 

    /**
     * Delete a tag
     */
    public function destroy($id)
    {
        $result = $this->tagService->deleteTag($id);

        return response()->json($result, $result['success'] ? 200 : ($result['message'] === 'Tag not found' ? 404 : 500));
    }
}