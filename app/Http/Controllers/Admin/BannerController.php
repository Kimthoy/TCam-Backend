<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }
    }

    // Admin: List all banners (with search + trashed)
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $perPage = (int) $request->get('per_page', 15);
        $query = Banner::query()->orderBy('id', 'desc');

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($q = $request->get('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%{$q}%")
                    ->orWhere('subtitle', 'like', "%{$q}%")
                    ->orWhere('page', 'like', "%{$q}%");
            });
        }

        return response()->json($query->paginate($perPage));
    }

    // Admin: Show single banner
    public function show(Request $request, Banner $banner)
    {
        $this->authorizeAdmin($request);
        return response()->json($banner->append('image_url'));
    }

    // Admin: Create new banner
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'title'    => 'nullable|string|max:191',
            'subtitle' => 'nullable|string|max:255',
            'link'     => 'nullable|url|max:500',
            'status'   => 'nullable|boolean',
            'page'     => ['required', Rule::in(['home', 'about', 'services', 'products', 'contact','partners','careers', 'blog'])],
            'image'    => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $banner = Banner::create([
            'title'    => $request->title,
            'subtitle' => $request->subtitle,
            'link'     => $request->link,
            'status'   => $request->boolean('status', true),
            'page'     => $request->page,
        ]);

        // Store image
        $path = $request->file('image')->store("banners/{$banner->id}", 'public');
        $banner->update(['image' => $path]);

        return response()->json([
            'success' => true,
            'data'    => $banner->fresh()->append('image_url')
        ], 201);
    }

    // Admin: Update banner
    public function update(Request $request, Banner $banner)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title'    => 'sometimes|nullable|string|max:191',
            'subtitle' => 'sometimes|nullable|string|max:255',
            'link'     => 'sometimes|nullable|url|max:500',
            'status'   => 'sometimes|boolean',
            'page'     => ['sometimes', 'required', Rule::in(['home', 'about', 'services', 'products', 'contact','partners','jobs', 'blog'])],
            'image'    => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $path = $request->file('image')->store("banners/{$banner->id}", 'public');
            $data['image'] = $path;
        }

        $banner->update(array_filter($data, fn($v) => $v !== null));

        return response()->json([
            'success' => true,
            'data'    => $banner->fresh()->append('image_url')
        ]);
    }

    // Soft delete
    public function destroy(Request $request, Banner $banner)
    {
        $this->authorizeAdmin($request);
        $banner->delete();

        return response()->json(['success' => true, 'message' => 'Banner moved to trash']);
    }

    // Restore
    public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $banner = Banner::withTrashed()->findOrFail($id);
        $banner->restore();

        return response()->json(['success' => true, 'message' => 'Banner restored']);
    }

    // Permanent delete + remove image
    public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $banner = Banner::withTrashed()->findOrFail($id);

        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->forceDelete();

        return response()->json(['success' => true, 'message' => 'Banner permanently deleted']);
    }

    public function publicBanners(Request $request)
    {
        $page = $request->get('page', 'home');

        $allowedPages = ['home', 'about', 'services', 'products', 'contact','partners','jobs', 'blog'];
        if (!in_array($page, $allowedPages)) {
            $page = 'home';
        }

        $banners = Banner::query()
            ->published()
            ->where('page', $page)
            ->select('id', 'title', 'subtitle', 'image', 'link')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($b) {
                return [
                    'id'        => $b->id,
                    'title'     => $b->title,
                    'subtitle'  => $b->subtitle,
                    'link'      => $b->link,
                    'image_url' => $b->image_url, // accessor
                ];
            });

        return response()->json($banners);
    }
}
