<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory; // ← Fixed: Use PostCategory, not Category
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
{

    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $perPage = (int) $request->get('per_page', 15);

        $query = Post::with('categories')
            ->withCount('categories')
            ->orderBy('id', 'desc');

        if ($request->boolean('with_trashed')) $query->withTrashed();
        if ($request->boolean('trashed_only')) $query->onlyTrashed();

        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('id', $request->category));
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->paginate($perPage)->appends($request->query())
        );
    }
    public function publicIndex(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);

        $query = Post::query()
            ->select([
                'id',
                'title',
                'short_description',
                'content',
                'feature_image',
                'published_at',
                'created_at'
            ])
            ->with('categories:id,name')
            ->published()
            ->orderByDesc('published_at');

        // Filter by category (supports name or ID)
        if ($request->filled('category')) {
            $category = $request->input('category');

            $query->whereHas('categories', function ($q) use ($category) {
                if (is_numeric($category)) {
                    $q->where('id', $category);
                } else {
                    $q->whereRaw('LOWER(name) = ?', [strtolower($category)]);
                }
            });
        }

        // Search
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate($perPage);

        $posts->getCollection()->transform(function ($post) {
            return [
                'id'                => $post->id,
                'title'             => $post->title,
                'short_description' => $post->short_description,
                'content'           => $post->content,
                'feature_image_url' => $post->feature_image_url,
                'published_at'      => $post->published_at?->format('Y-m-d H:i:s'),
                'categories'        => $post->categories->pluck('name')->toArray(),
            ];
        });

        return response()->json($posts);
    }
    public function show(Request $request, Post $post)
    {
        $this->authorizeAdmin($request);
        return response()->json($post->load('categories')->append('feature_image_url'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'content'           => 'required|string',
            'category_ids'      => 'sometimes|array',
            'category_ids.*'    => 'exists:post_categories,id',
            'feature_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'images'            => 'sometimes|array',
            'images.*'          => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'is_active'         => 'boolean',
            'is_featured'       => 'boolean',
            'published_at'      => 'nullable|date',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['is_active'] ??= true;
        $data['content'] = Purifier::clean($data['content'], 'default');
        $post = Post::create($data);

        if ($request->hasFile('feature_image')) {
            $path = $request->file('feature_image')
                ->store("uploads/posts/{$post->id}", 'public');
            $post->feature_image = $path;
            $post->save();
        }

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $image) {
                $paths[] = $image->store("uploads/posts/{$post->id}/gallery", 'public');
            }
            $post->images = $paths;
            $post->save();
        }

        if ($request->filled('category_ids')) {
            $post->categories()->sync($request->category_ids);
        }

        return response()->json([
            'success' => true,
            'data'    => $post->fresh()->load('categories')
        ], 201);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title'             => 'sometimes|required|string|max:255',
            'short_description' => 'nullable|string',
            'content'           => 'sometimes|required|string',
            'category_ids'      => 'sometimes|array',
            'category_ids.*'    => 'exists:post_categories,id', // ← Fixed
            'feature_image'     => 'sometimes|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'images'            => 'sometimes|array',
            'images.*'          => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'is_active'         => 'boolean',
            'is_featured'       => 'boolean',
            'published_at'      => 'nullable|date',
        ]);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($request->hasFile('feature_image')) {
            if ($post->feature_image) {
                Storage::disk('public')->delete($post->feature_image);
            }
            $data['feature_image'] = $request->file('feature_image')
                ->store("uploads/posts/{$post->id}", 'public');
        }

        if ($request->hasFile('images')) {
            if ($post->images) {
                foreach ($post->images as $old) Storage::disk('public')->delete($old);
            }
            $paths = [];
            foreach ($request->file('images') as $image) {
                $paths[] = $image->store("uploads/posts/{$post->id}/gallery", 'public');
            }
            $data['images'] = $paths;
        }

        $post->update($data);

        if ($request->has('category_ids')) {
            $post->categories()->sync($request->category_ids);
        }

        return response()->json([
            'success' => true,
            'data'    => $post->fresh()->load('categories')
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorizeAdmin($request);
        $post->delete();
        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $post = Post::withTrashed()->findOrFail($id);
        $post->restore();
        return response()->json(['success' => true]);
    }

    public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $post = Post::withTrashed()->findOrFail($id);
        Storage::disk('public')->deleteDirectory("uploads/posts/{$id}");
        $post->forceDelete();

        return response()->json(['success' => true]);
    }
}
