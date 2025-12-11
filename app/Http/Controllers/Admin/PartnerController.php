<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['admin', 'superadmin']), 403);
    }
    // inside PartnerController

    public function publicIndex(Request $request)
    {
        // Public list: only active partners, ordered by sort_order then name
        $partners = Partner::query()
            ->select('id', 'name', 'logo', 'link', 'short_description') 
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $payload = $partners->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'link' => $p->link,
                'short_description' => $p->short_description,
                'logo_url' => $p->logo_url, 
            ];
        });

        return response()->json($payload);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $query = Partner::query()->orderBy('sort_order')->orderBy('id', 'desc');

        if ($q = $request->get('q')) {
            $query->where('name', 'like', "%{$q}%");
        }

        if ($request->boolean('with_trashed')) $query->withTrashed();
        if ($request->boolean('trashed_only')) $query->onlyTrashed();

        return response()->json(
            $query->paginate($request->get('per_page', 15))->appends($request->query())
        );
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name'              => 'required|string|max:191',
            'link'              => 'nullable|url|max:500',
            'short_description' => 'nullable|string',
            'logo'              => 'required|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'sort_order'        => 'integer|min:0',
            'is_active'         => 'boolean',
        ]);

        $partner = Partner::create([
            'name'              => $data['name'],
            'link'              => $data['link'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'sort_order'        => $data['sort_order'] ?? 0,
            'is_active'         => $data['is_active'] ?? true,
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store("uploads/partners/{$partner->id}", 'public');
            $partner->logo = $path;
            $partner->save();
        }

        return response()->json([
            'success' => true,
            'data'    => $partner->fresh()
        ], 201);
    }

    public function update(Request $request, Partner $partner)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name'              => 'sometimes|required|string|max:191',
            'link'              => 'nullable|url|max:500',
            'short_description' => 'nullable|string',
            'logo'              => 'sometimes|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'sort_order'        => 'integer|min:0',
            'is_active'         => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            Storage::disk('public')->deleteDirectory("uploads/partners/{$partner->id}");
            $path = $request->file('logo')->store("uploads/partners/{$partner->id}", 'public');
            $data['logo'] = $path;
        }

        $partner->update($data);

        return response()->json([
            'success' => true,
            'data'    => $partner->fresh()
        ]);
    }

    public function destroy(Request $request, Partner $partner)
    {
        $this->authorizeAdmin($request);
        $partner->delete();
        return response()->json(['success' => true]);
    }

    // Optional: restore & force delete
    public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $partner = Partner::withTrashed()->findOrFail($id);
        $partner->restore();
        return response()->json(['success' => true]);
    }

    public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $partner = Partner::withTrashed()->findOrFail($id);
        Storage::disk('public')->deleteDirectory("uploads/partners/{$id}");
        $partner->forceDelete();
        return response()->json(['success' => true]);
    }
}
