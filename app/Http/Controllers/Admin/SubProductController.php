<?php

namespace App\Http\Controllers\Admin;

use App\Models\SubProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SubProductController extends Controller
{
    // List all sub-products
 public function index()
{
    $subProducts = SubProduct::with(['images', 'properties'])
        ->orderBy('id', 'desc')
        ->get();

    return response()->json($subProducts);
}

    // Show single sub-product
    public function show($id)
    {
        $subProduct = SubProduct::with(['images', 'properties'])->findOrFail($id);
        return response()->json($subProduct);
    }

    // Create sub-product
   public function store(Request $request)
{
    // -----------------------------
    // Step 0: Ensure proper types
    // -----------------------------
    // Ensure 'images' is always an array of uploaded files
    $images = $request->file('images');
    if (!is_array($images)) {
        $images = [];
    }
    $request->merge(['images' => $images]);

    // Ensure 'properties' is always an array
    $properties = $request->input('properties');
    if (!is_array($properties)) {
        $properties = [];
    }
    $request->merge(['properties' => $properties]);

    // -----------------------------
    // Step 1: Validate request
    // -----------------------------
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'name'       => 'required|string|max:255',
        'description'=> 'nullable|string',
        'price'      => 'nullable|numeric',

        'images'     => 'required|array|min:1',
        'images.*'   => 'image|mimes:jpg,jpeg,png|max:2048',
        'primary_image_index' => 'nullable|integer|min:0',

        'properties' => 'nullable|array',
        'properties.*.key'   => 'required|string',
        'properties.*.value' => 'required|string',
    ]);

    DB::beginTransaction();
    try {
        // -----------------------------
        // Step 2: Create sub-product
        // -----------------------------
        $subProduct = SubProduct::create([
            'product_id'  => $request->product_id,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'is_active'   => true,
        ]);

        // -----------------------------
        // Step 3: Store images
        // -----------------------------
        foreach ($request->file('images') as $index => $imageFile) {
            $path = $imageFile->store('sub_product', 'public');

            $subProduct->images()->create([
                'image_path' => $path,
                'is_primary' => ($request->primary_image_index ?? 0) == $index,
            ]);
        }

        // -----------------------------
        // Step 4: Store properties
        // -----------------------------
        if (!empty($request->properties)) {
            $uniqueProps = collect($request->properties)
                ->unique('key')
                ->values()
                ->toArray();

            $subProduct->properties()->createMany($uniqueProps);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'sub_product_id' => $subProduct->id,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}




public function update(Request $request, $id)
{
    $subProduct = SubProduct::with(['images', 'properties'])->findOrFail($id);

    $request->validate([
        'product_id' => 'sometimes|required|exists:products,id',
        'name'       => 'sometimes|required|string|max:255',
        'description'=> 'sometimes|nullable|string',
        'price'      => 'sometimes|nullable|numeric',

        'images'     => 'sometimes|array',
        'images.*'   => 'image|mimes:jpg,jpeg,png|max:2048',

        'existing_image_ids' => 'sometimes|array',
        'existing_image_ids.*' => 'integer|exists:sub_product_images,id',

        'primary_image_id' => 'sometimes|integer|exists:sub_product_images,id',

        'properties' => 'sometimes|array',
        'properties.*.key'   => 'required|string',
        'properties.*.value' => 'required|string',
    ]);

    DB::beginTransaction();

    try {
        /** ✅ Update main fields */
        $subProduct->update(
            $request->only(['product_id', 'name', 'description', 'price'])
        );

        /** ✅ DELETE REMOVED IMAGES (ONLY if frontend sends existing_image_ids) */
        if ($request->has('existing_image_ids')) {
            $toDelete = $subProduct->images()
                ->whereNotIn('id', $request->existing_image_ids)
                ->get();

            foreach ($toDelete as $img) {
                \Storage::disk('public')->delete($img->image_path);
                $img->delete();
            }
        }

        /** ✅ ADD NEW IMAGES */
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('sub_product', 'public');

                $subProduct->images()->create([
                    'image_path' => $path,
                    'is_primary' => false,
                ]);
            }
        }

        /** ✅ SET PRIMARY IMAGE (SAFE) */
        if ($request->filled('primary_image_id')) {
            $subProduct->images()->update(['is_primary' => false]);

            $subProduct->images()
                ->where('id', $request->primary_image_id)
                ->update(['is_primary' => true]);
        }

        /** ✅ HANDLE PROPERTIES */
        if ($request->has('properties')) {
            $incomingKeys = collect($request->properties)
                ->pluck('key')
                ->unique()
                ->toArray();

            $subProduct->properties()
                ->whereNotIn('key', $incomingKeys)
                ->delete();

            foreach ($request->properties as $prop) {
                $subProduct->properties()->updateOrCreate(
                    ['key' => $prop['key']],
                    ['value' => $prop['value']]
                );
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Sub product updated successfully',
            'sub_product_id' => $subProduct->id,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}




    // Delete sub-product
    public function destroy($id)
    {
        $subProduct = SubProduct::findOrFail($id);

        DB::beginTransaction();
        try {
            $subProduct->images()->delete();
            $subProduct->properties()->delete();
            $subProduct->delete();

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
