<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }
    }

    // LIST + search + post count
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $query = PostCategory::withCount('posts')
            ->orderBy('id', 'desc');

        if ($search = $request->get('q')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($request->boolean('trashed_only')) {
            $query->onlyTrashed();
        }

        return response()->json(
            $query->paginate($request->get('per_page', 15))->appends($request->query())
        );
    }

    // SHOW SINGLE
    public function show(Request $request, PostCategory $postCategory)
    {
        $this->authorizeAdmin($request);

        return response()->json(
            $postCategory->loadCount('posts')
        );
    }

    // CREATE
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name'        => 'required|string|max:150|unique:post_categories,name',
            'description' => 'nullable|string',
        ]);

        $category = PostCategory::create($data);

        return response()->json([
            'success' => true,
            'data'    => $category->loadCount('posts')
        ], 201);
    }

    // UPDATE
    public function update(Request $request, PostCategory $postCategory)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:150|unique:post_categories,name,' . $postCategory->id,
            'description' => 'nullable|string',
        ]);

        $postCategory->update($data);

        return response()->json([
            'success' => true,
            'data'    => $postCategory->fresh()->loadCount('posts')
        ]);
    }

    // SOFT DELETE
    public function destroy(Request $request, PostCategory $postCategory)
    {
        $this->authorizeAdmin($request);

        // Prevent deletion if category has posts
        if ($postCategory->posts()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that has associated posts.'
            ], 422);
        }

        $postCategory->delete();

        return response()->json(['success' => true]);
    }

    // RESTORE
    public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $category = PostCategory::withTrashed()->findOrFail($id);
        $category->restore();

        return response()->json(['success' => true]);
    }

    // FORCE DELETE (permanent)
    public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $category = PostCategory::withTrashed()->findOrFail($id);

        if ($category->posts()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot permanently delete category with posts.'
            ], 422);
        }

        $category->forceDelete();

        return response()->json(['success' => true]);
    }
}
