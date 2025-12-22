<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user(); // ← Fixed: removed the wrong parentheses

        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }
    }
    // LIST - Products with pagination, search, category filter & soft-delete support
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $perPage = (int) $request->get('per_page', 15);

        $query = Product::query()
            ->with('category')
            ->orderBy('id', 'desc');
        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($request->boolean('trashed_only')) {
            $query->onlyTrashed();
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        return response()->json(
            $query->paginate($perPage)->appends($request->query())
        );
    }
    // SHOW SINGLE
    public function show(Request $request, Product $product)
    {
        $this->authorizeAdmin($request);

        return response()->json(
            $product->load('category')->append('image_url')
        );
    }

    // CREATE
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'category_id'       => 'nullable|exists:product_categories,id',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'website_link'       => 'nullable|string',
            'price'              => 'nullable|numeric|min:0',
            'is_published'      => 'boolean',
            'feature_image'     => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120', // 5MB
        ]);

        // Create product first to get ID
        $product = Product::create([
            'title'             => $data['title'],
            'category_id'       => $data['category_id'],
            'short_description' => $data['short_description'],
            'description'       => $data['description'],
            'website_link'       => $data['website_link'],
            'price'              => $data['price'],
            'is_published'      => $data['is_published'] ?? true,
        ]);

        // Store image in /storage/app/public/uploads/products/{id}/
        if ($request->hasFile('feature_image')) {
            $path = $request->file('feature_image')
                ->store("uploads/products/{$product->id}", 'public');

            $product->feature_image = $path;
            $product->save();
        }

        return response()->json([
            'success' => true,
            'data'    => $product->fresh()->load('category')
        ], 201);
    }

    // UPDATE
    public function update(Request $request, Product $product)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title'             => 'sometimes|required|string|max:255',
            'category_id'       => 'sometimes|required|exists:product_categories,id',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'website_link'       => 'nullable|string',
            'price'              => 'nullable|numeric|min:0',
            'is_published'      => 'boolean',
            'feature_image'     => 'sometimes|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        // Handle new image
        if ($request->hasFile('feature_image')) {
            // Delete old image folder if exists
            if ($product->feature_image) {
                Storage::disk('public')->deleteDirectory("uploads/products/{$product->id}");
            }

            $path = $request->file('feature_image')
                ->store("uploads/products/{$product->id}", 'public');

            $data['feature_image'] = $path;
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'data'    => $product->fresh()->load('category')
        ]);
    }

    // SOFT DELETE
    public function destroy(Request $request, Product $product)
    {
        $this->authorizeAdmin($request);

        $product->delete();

        return response()->json(['success' => true]);
    }

    // RESTORE
    public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return response()->json(['success' => true]);
    }

    // PERMANENT DELETE + REMOVE FILES
    public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $product = Product::withTrashed()->findOrFail($id);

        // Delete image folder
        Storage::disk('public')->deleteDirectory("uploads/products/{$id}");

        $product->forceDelete();

        return response()->json(['success' => true]);
    }
    // inside ProductController (add these public methods)

    /**
     * PUBLIC: List published products (paginated).
     * GET /api/products/public
     * Query params:
     *  - category (id)
     *  - q (search)
     *  - per_page (int)
     *  - sort (e.g. "-id" or "price")
     */
    public function publicIndex(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);

        $query = Product::query()
            ->with('category')              // include category name
            ->where('is_published', true);  // only public products

        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        if ($q = $request->get('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }


        if ($sort = $request->get('sort')) {
            if (str_starts_with($sort, '-')) {
                $query->orderBy(ltrim($sort, '-'), 'desc');
            } else {
                $query->orderBy($sort, 'asc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $p = $query->paginate($perPage)->appends($request->query());


        return response()->json($p);
    }

    public function publicShow(Request $request, $id)
    {
        $product = Product::with('category')->where('is_published', true)->find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }
}
