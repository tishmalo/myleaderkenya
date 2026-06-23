<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('name')->get();
        return view('dashboard.index', compact('tags'));
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:tags,name',
            'slug'        => 'required|string|max:100|unique:tags,slug|regex:/^[a-z0-9-]+$/',
            'description' => 'nullable|string|max:500',
        ]);

        $tag = Tag::create([
            'name'        => trim($request->name),
            'slug'        => trim($request->slug),
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'tag'     => $tag
        ]);
    }

        /**
     * Delete a tag
     */
    // public function destroy(Tag $tag)
    // {
    //     try {
    //         $tag->delete();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Tag deleted successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to delete tag: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

        /**
     * Delete a tag
     */
    public function destroy($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found'
            ], 404);
        }

        try {
            $tag->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tag: ' . $e->getMessage()
            ], 500);
        }
    }
}