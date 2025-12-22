<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhyJoinUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhyJoinUsController extends Controller
{
    /**
     * List all sections
     */
    public function index()
    {
        $sections = WhyJoinUs::orderBy('sort_order', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => $sections
        ]);
    }

    /**
     * Store a new section
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_tag' => 'nullable|string|max:255',
            'section_title' => 'required|string|max:255',
            'section_description' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.title' => 'required_with:items|string|max:255',
            'items.*.desc' => 'nullable|string',
            'items.*.icon' => 'nullable|string',
            'status' => 'required|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $section = WhyJoinUs::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully',
            'data' => $section
        ]);
    }

    /**
     * Show a specific section
     */
   public function show($id)
{
    $whyJoinUs = WhyJoinUs::findOrFail($id);
    return response()->json([
        'success' => true,
        'data' => $whyJoinUs
    ]);
}

public function update(Request $request, $id)
{
    $whyJoinUs = WhyJoinUs::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'section_tag' => 'nullable|string|max:255',
        'section_title' => 'required|string|max:255',
        'section_description' => 'nullable|string',
        'items' => 'nullable|array',
        'items.*.title' => 'required_with:items|string|max:255',
        'items.*.desc' => 'nullable|string',
        'items.*.icon' => 'nullable|string',
        'status' => 'required|boolean',
        'sort_order' => 'nullable|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $whyJoinUs->update($request->all());

    return response()->json([
        'success' => true,
        'message' => 'Section updated successfully',
        'data' => $whyJoinUs
    ]);
}

public function destroy($id)
{
    $whyJoinUs = WhyJoinUs::findOrFail($id);
    $whyJoinUs->delete();

    return response()->json([
        'success' => true,
        'message' => 'Section deleted successfully'
    ]);
}

}
