<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Post;
use App\Models\Product;
use App\Models\Partner;
use App\Models\Customer;
use Illuminate\Http\Request;
use Log;
use Str;

class PublicHomeController extends Controller
{
    public function banners()
    {
        try {
            $banners = Post::published()
                ->whereHas('categories', function ($query) {
                    $query->whereRaw('LOWER(name) = ?', ['banner']);
                })
                ->orWhere('is_featured', true)
                ->latest('published_at')
                ->take(8)
                ->get()
                ->map(function ($post) {
                    return [
                        'id'         => $post->id,
                        'title'      => $post->title,
                        'subtitle'   => $post->short_description,
                        'link'       => $post->slug ? "/insights/{$post->slug}" : null,
                        'image_url'  => $post->feature_image_url,
                    ];
                });

            return response()->json($banners);
        } catch (\Exception $e) {
            Log::error('Banners fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load banners'], 500);
        }
    }

    public function homePosts()
    {
        try {
            $posts = Post::published()
                ->whereHas('categories', function ($query) {
                    $query->whereRaw('LOWER(name) = ?', ['home']);
                })
                ->with('categories')
                ->latest('published_at')
                ->take(6)
                ->get()
                ->map(function ($post) {
                    return [
                        'id'               => $post->id,
                        'title'            => $post->title,
                        'slug'             => $post->slug,
                        'excerpt'          => Str::limit(strip_tags($post->content), 160),
                        'short_description' => $post->short_description,
                        'image'            => $post->feature_image_url,
                        'date'             => $post->published_at?->format('M d, Y'),
                    ];
                });

            return response()->json($posts);
        } catch (\Exception $e) {
            Log::error('Home posts fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load posts'], 500);
        }
    }
}
