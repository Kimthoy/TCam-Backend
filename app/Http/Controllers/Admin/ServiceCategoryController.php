<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }
    }

    // LIST
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $query = ServiceCategory::query();

        if ($q = $request->get('q')) {
            $query->where('name', 'like', "%{$q}%");
        }

        $categories = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json($categories);
    }

    // STORE
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => 'required|string|max:191|unique:service_categories,name'
        ]);

        $category = ServiceCategory::create($data);

        return response()->json([
            'success' => true,
            'data'    => $category
        ], 201);
    }

    // SHOW
    public function show(Request $request, ServiceCategory $serviceCategory)
    {
        $this->authorizeAdmin($request);
        return response()->json($serviceCategory);
    }

    // UPDATE
    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => 'required|string|max:191|unique:service_categories,name,' . $serviceCategory->id
        ]);

        $serviceCategory->update($data);

        return response()->json([
            'success' => true,
            'data'    => $serviceCategory
        ]);
    }

    // DELETE (soft)
    public function destroy(Request $request, ServiceCategory $serviceCategory)
    {
        $this->authorizeAdmin($request);

        // Optional: prevent delete if has services
        if ($serviceCategory->services()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing services.'
            ], 422);
        }

        $serviceCategory->delete();

        return response()->json(['success' => true]);
    }
}
