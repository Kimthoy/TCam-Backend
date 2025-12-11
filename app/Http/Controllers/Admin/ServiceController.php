<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }
    }
    /**
     * Public: Get published services for frontend
     */
    public function publicIndex(Request $request)
    {
        $query = Service::query()
            ->select('id', 'title', 'description', 'feature_image')
            ->where('is_published', true)
            ->with('category:id,name')
            ->orderBy('id');

        // Optional: filter by category name
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->whereRaw('LOWER(name) = ?', [strtolower($request->category)]);
            });
        }

        $services = $query->get();

        // Add full image URL
        $services = $services->map(function ($service) {
            return [
                'id'          => $service->id,
                'title'       => $service->title,
                'description' => $service->description,
                'image_url'   => $service->image_url,
                'category'    => $service->category?->name,
            ];
        });

        return response()->json($services);
    }
    /**
     * List services (paginated + filters).
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $perPage = (int) $request->get('per_page', 15);

        $query = Service::with('category')->orderBy('id', 'desc');

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($request->boolean('trashed_only')) {
            $query->onlyTrashed();
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $paginated = $query->paginate($perPage)->appends($request->query());

        return response()->json($paginated);
    }

    /**
     * Show single service.
     */
    public function show(Request $request, Service $service)
    {
        $this->authorizeAdmin($request);

        $service->load('category');

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    /**
     * Create new service with image.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'category_id'   => 'required|exists:service_categories,id',
            'description'   => 'nullable|string',
            'feature_image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'is_published'  => 'boolean',
        ]);

        $service = Service::create([
            'title'         => $data['title'],
            'category_id'   => $data['category_id'],
            'description'   => $data['description'] ?? null,
            'is_published'  => $data['is_published'] ?? true,
        ]);

        if ($request->hasFile('feature_image')) {
            $path = $request->file('feature_image')
                ->store("uploads/services/{$service->id}", 'public');

            $service->feature_image = $path;
            $service->save();
        }

        $service->load('category');

        return response()->json([
            'success' => true,
            'data'    => $service,
        ], 201);
    }

    /**
     * Update service; optionally replace image.
     */
    public function update(Request $request, Service $service)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title'         => 'sometimes|required|string|max:255',
            'category_id'   => 'sometimes|required|exists:service_categories,id',
            'description'   => 'nullable|string',
            'feature_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'is_published'  => 'boolean',
        ]);

        if ($request->hasFile('feature_image')) {
            // Delete old image folder (if exists)
            if ($service->feature_image) {
                Storage::disk('public')->deleteDirectory("uploads/services/{$service->id}");
            }

            $path = $request->file('feature_image')
                ->store("uploads/services/{$service->id}", 'public');

            $data['feature_image'] = $path;
        }

        $service->update($data);

        $service->load('category');

        return response()->json([
            'success' => true,
            'data'    => $service,
        ]);
    }

    /**
     * Soft delete (trash) a service.
     */
    public function destroy(Request $request, Service $service)
    {
        $this->authorizeAdmin($request);
        $service->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Restore a soft-deleted service.
     */
    public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $service = Service::withTrashed()->findOrFail($id);
        $service->restore();

        return response()->json(['success' => true]);
    }

    /**
     * Force delete + delete image directory.
     */
    public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $service = Service::withTrashed()->findOrFail($id);

        Storage::disk('public')->deleteDirectory("uploads/services/{$service->id}");
        $service->forceDelete();

        return response()->json(['success' => true]);
    }
}
