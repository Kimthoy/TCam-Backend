<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WidgetController extends Controller
{
    /**
     * Display a listing of widgets
     */
public function index()
{
    $widgets = Widget::latest()->get()->map(function($w) {
        $w->app_logo_url = $w->app_logo ? Storage::disk('public')->url($w->app_logo) : null;
        return $w;
    });

    return response()->json([
        'success' => true,
        'data' => $widgets,
    ]);
}

    /**
     * Store a newly created widget
     */
   public function store(Request $request)
{
    $data = $request->validate([
        'app_name'          => 'nullable|string|max:255',
        'app_sort_desc'     => 'nullable|string|max:255',
        'abstract_desc'     => 'nullable|string',
        'app_logo'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'contact_email'     => 'nullable|email',
        'contact_number'    => 'nullable|string|max:50',
        'contact_address'   => 'nullable|string',
        'contact_facebook'  => 'nullable|string',
        'contact_youtube'   => 'nullable|string',
        'contact_telegram'  => 'nullable|string',
        'footer_ownership'  => 'nullable|string|max:255',
    ]);

    // upload logo
    if ($request->hasFile('app_logo')) {
        $data['app_logo'] = $request->file('app_logo')
            ->store('widgets', 'public');
    }

    $widget = Widget::create($data);

    return response()->json([
        'success' => true,
        'message' => 'Widget created successfully',
        'data' => $widget
    ], 201);
}


    /**
     * Display a specific widget
     */
    public function show($id)
    {
        $widget = Widget::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $widget
        ]);
    }

    /**
     * Update the specified widget
     */
   public function update(Request $request, $id)
{
    $widget = Widget::findOrFail($id);

    $data = $request->validate([
        'app_name'          => 'nullable|string|max:255',
        'app_sort_desc'     => 'nullable|string|max:255',
        'abstract_desc'     => 'nullable|string',
        'app_logo'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'contact_email'     => 'nullable|email',
        'contact_number'    => 'nullable|string|max:50',
        'contact_address'   => 'nullable|string',
        'contact_facebook'  => 'nullable|string',
        'contact_youtube'   => 'nullable|string',
        'contact_telegram'  => 'nullable|string',
        'footer_ownership'  => 'nullable|string|max:255',
    ]);

    if ($request->hasFile('app_logo')) {

        // delete old image
        if ($widget->app_logo && Storage::disk('public')->exists($widget->app_logo)) {
            Storage::disk('public')->delete($widget->app_logo);
        }

        // upload new image
        $data['app_logo'] = $request->file('app_logo')
            ->store('widgets', 'public');
    }

    $widget->update($data);

    return response()->json([
        'success' => true,
        'message' => 'Widget updated successfully',
        'data' => $widget
    ]);
}
    /**
     * Remove the specified widget
     */
    public function destroy($id)
    {
        $widget = Widget::findOrFail($id);
        $widget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Widget deleted successfully'
        ]);
    }
}
