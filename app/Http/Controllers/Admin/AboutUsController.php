<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutUsController extends Controller
{
    /**
     * Public API: List all About Us entries
     */
    public function publicIndex()
    {
        $abouts = AboutUs::all()->map(function ($about) {
            $about->operational_offices = is_string($about->operational_offices)
                ? json_decode($about->operational_offices, true)
                : ($about->operational_offices ?? []);
            return $about;
        });

        return response()->json([
            'success' => true,
            'data' => $abouts,
        ]);
    }

    /**
     * Admin API: List all About Us entries
     */
    public function index()
    {
        $abouts = AboutUs::all()->map(function ($about) {
            $about->operational_offices = is_string($about->operational_offices)
                ? json_decode($about->operational_offices, true)
                : ($about->operational_offices ?? []);
            return $about;
        });

        return response()->json([
            'success' => true,
            'data' => $abouts,
        ]);
    }

    /**
     * Show single About Us entry
     */
    public function show($id)
    {
        $aboutUs = AboutUs::findOrFail($id);
        $aboutUs->operational_offices = is_string($aboutUs->operational_offices)
            ? json_decode($aboutUs->operational_offices, true)
            : ($aboutUs->operational_offices ?? []);

        return response()->json([
            'success' => true,
            'data' => $aboutUs,
        ]);
    }

    /**
     * Store new About Us entry
     */
    public function store(Request $request)
    {
        // Decode operational_offices if sent as JSON string
        if ($request->has('operational_offices') && is_string($request->operational_offices)) {
            $request->merge([
                'operational_offices' => json_decode($request->operational_offices, true),
            ]);
        }

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'company_image' => 'nullable|file|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'founding_year' => 'nullable|digits:4|integer',
            'founders_info' => 'nullable|string',
            'intro_text' => 'nullable|string',
            'operational_offices' => 'nullable|array',
            'services_description' => 'nullable|string',
            'company_profile' => 'nullable|string',
            'project_count' => 'nullable|integer',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'value_proposition' => 'nullable|string',
        ]);

        // Handle image upload
        if ($request->hasFile('company_image')) {
            $file = $request->file('company_image');
            $path = $file->store('public/about_us');
            $data['company_image'] = Storage::url($path);
        }

        if (isset($data['operational_offices'])) {
            $data['operational_offices'] = json_encode($data['operational_offices']);
        }

        $aboutUs = AboutUs::create($data);
        $aboutUs->operational_offices = json_decode($aboutUs->operational_offices ?? '[]', true);

        return response()->json([
            'success' => true,
            'message' => 'About Us created successfully',
            'data' => $aboutUs,
        ], 201);
    }

    /**
     * Update About Us entry
     */
    public function update(Request $request, $id)
{
    $aboutUs = AboutUs::findOrFail($id);

    // Decode operational_offices if sent as string JSON
    if ($request->has('operational_offices') && is_string($request->operational_offices)) {
        $request->merge([
            'operational_offices' => json_decode($request->operational_offices, true),
        ]);
    }

    // Validate fields (allow nullable for update)
    $data = $request->validate([
        'title' => 'nullable|string|max:255',
        'company_image' => 'nullable|file|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        'founding_year' => 'nullable|digits:4|integer',
        'founders_info' => 'nullable|string',
        'intro_text' => 'nullable|string',
        'operational_offices' => 'nullable|array',
        'services_description' => 'nullable|string',
        'company_profile' => 'nullable|string',
        'project_count' => 'nullable|integer',
        'vision' => 'nullable|string',
        'mission' => 'nullable|string',
        'value_proposition' => 'nullable|string',
    ]);

     // Only handle image if a new file is uploaded
    if ($request->hasFile('company_image')) {
        $file = $request->file('company_image');
        $path = $file->store('public/about_us');
        $data['company_image'] = Storage::url($path);
    }


    // Encode operational_offices as JSON if array
    if (isset($data['operational_offices']) && is_array($data['operational_offices'])) {
        $data['operational_offices'] = json_encode($data['operational_offices']);
    }

    // Use fill + save instead of update() to allow partial updates
    $aboutUs->fill($data);
    $aboutUs->update();

    // Decode operational_offices for JSON response
    $aboutUs->operational_offices = is_string($aboutUs->operational_offices)
        ? json_decode($aboutUs->operational_offices, true)
        : ($aboutUs->operational_offices ?? []);

    return response()->json([
        'success' => true,
        'message' => 'About Us updated successfully',
        'data' => $aboutUs,
    ]);
}


    /**
     * Delete About Us entry
     */
    public function destroy($id)
    {
        $aboutUs = AboutUs::findOrFail($id);
        $aboutUs->delete();

        return response()->json([
            'success' => true,
            'message' => 'About Us deleted successfully',
        ]);
    }
}
