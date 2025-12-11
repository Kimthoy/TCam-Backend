<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerCategory;
use Illuminate\Http\Request;

class CustomerCategoryController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['admin', 'superadmin']), 403);
    }

    
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        $q = $request->get('q');
        $query = CustomerCategory::query();
        if ($q) $query->where('name', 'like', "%$q%");
        return response()->json($query->orderBy('name')->paginate(20));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['name' => 'required|string|max:191|unique:customer_categories']);
        $cat = CustomerCategory::create($data);
        return response()->json(['success' => true, 'data' => $cat], 201);
    }

    public function update(Request $request, CustomerCategory $customerCategory)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['name' => 'required|string|max:191|unique:customer_categories,name,' . $customerCategory->id]);
        $customerCategory->update($data);
        return response()->json(['success' => true, 'data' => $customerCategory]);
    }

    public function destroy(Request $request, CustomerCategory $customerCategory)
    {
        $this->authorizeAdmin($request);
        if ($customerCategory->customers()->exists()) {
            return response()->json(['success' => false, 'message' => 'Category has customers'], 422);
        }
        $customerCategory->delete();
        return response()->json(['success' => true]);
    }
}
