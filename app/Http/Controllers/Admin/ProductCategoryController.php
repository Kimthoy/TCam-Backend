<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    // Reuse the same admin check from your other controllers
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Display a listing of categories (with product count)
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $categories = ProductCategory::withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => 'required|string|max:191|unique:product_categories,name',
        ]);

        $category = ProductCategory::create([
            'name' => trim($data['name']),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $category->fresh()->append('products_count')
        ], 201);
    }

    /**
     * Display single category
     */
    public function show(Request $request, ProductCategory $category)
    {
        $this->authorizeAdmin($request);

        $category->loadCount('products');

        return response()->json([
            'success' => true,
            'data'    => $category
        ]);
    }

    /**
     * Update the category
     */
    public function update(Request $request, ProductCategory $category)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => 'required|string|max:191|unique:product_categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => trim($data['name']),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $category->fresh()->loadCount('products')
        ]);
    }

    /**
     * Remove the category (soft delete if you want, or hard delete)
     */
    public function destroy(Request $request, ProductCategory $category)
    {
        $this->authorizeAdmin($request);

        // Optional: prevent delete if has products
        if ($category->products()->withTrashed()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that has products.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ]);
    }
}
