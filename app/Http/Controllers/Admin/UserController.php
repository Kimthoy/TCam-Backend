<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Optional: simple role check helper (replace with spatie/permissions in prod)
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        if (! $user || ($user->role !== 'superadmin' && $user->role !== 'admin')) {
            abort(403, 'Unauthorized');
        }
    }

    // List users (including soft-deleted when ?with_trashed=1)
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $perPage = (int) $request->get('per_page', 15);
        $query = User::query()->orderBy('id', 'desc');

        if ($request->boolean('with_trashed')) {
            $query = $query->withTrashed();
        }

        if ($q = $request->get('q')) {
            $query->where(function ($qf) use ($q) {
                $qf->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return response()->json($query->paginate($perPage));
    }

    // Create user
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['nullable', Rule::in(['superadmin', 'admin', 'editor'])],
            'is_active' => 'nullable|boolean',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'editor';
        $data['is_active'] = $data['is_active'] ?? true;

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'data' => $user
        ], 201);
    }

    // Show user
    public function show(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $user->photo_url = $user->photo_url ?? null;

        return response()->json($user);
    }


    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:191',
            'email' => ['sometimes', 'required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['nullable', Rule::in(['superadmin', 'admin', 'editor'])],
            'is_active' => 'nullable|boolean',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(['success' => true, 'data' => $user]);
    }

    public function uploadPhoto(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = $request->file('photo')->store("users/{$user->id}", 'public');

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->photo = $path;
        $user->save();

        return response()->json(['success' => true, 'photo_path' => $path, 'photo_url' => $user->photo_url]);
    }


    public function destroy(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User soft-deleted']);
    }

    public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $user = User::withTrashed()->findOrFail($id);
        if ($user->trashed()) {
            $user->restore();
            return response()->json(['success' => true, 'message' => 'User restored', 'data' => $user]);
        }

        return response()->json(['success' => false, 'message' => 'User is not trashed'], 400);
    }

    public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $user = User::withTrashed()->findOrFail($id);

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->forceDelete();

        return response()->json(['success' => true, 'message' => 'User permanently deleted']);
    }
}
