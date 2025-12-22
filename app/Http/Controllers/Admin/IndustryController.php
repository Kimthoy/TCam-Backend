<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\Request;

class IndustryController extends Controller
{
    /**
     * List all industries
     */
    public function index()
    {
        $industries = Industry::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $industries,
        ]);
    }

    /**
     * Store new industry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'industry_name' => 'required|string|max:255',
            'industry_description' => 'nullable|string',
            'solutions' => 'nullable|array',
            'solutions.*.title' => 'required|string|max:255',
            'solutions.*.description' => 'nullable|string',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $industry = Industry::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Industry created successfully',
            'data' => $industry,
        ], 201);
    }

    /**
     * Show single industry
     */
    public function show($id)
    {
        $industry = Industry::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $industry,
        ]);
    }

    /**
     * Update industry
     */
    public function update(Request $request, $id)
    {
        $industry = Industry::findOrFail($id);

        $validated = $request->validate([
            'industry_name' => 'required|string|max:255',
            'industry_description' => 'nullable|string',
            'solutions' => 'nullable|array',
            'solutions.*.title' => 'required|string|max:255',
            'solutions.*.description' => 'nullable|string',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $industry->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Industry updated successfully',
            'data' => $industry,
        ]);
    }

    /**
     * Delete industry
     */
    public function destroy($id)
    {
        $industry = Industry::findOrFail($id);
        $industry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Industry deleted successfully',
        ]);
    }
    
}
