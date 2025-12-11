<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['admin', 'superadmin']), 403);
    }
    public function publicIndex(Request $request)
    {

        $customers = Customer::query()
            ->select('id', 'name', 'logo', 'link')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $payload = $customers->map(function ($c) {
            return [
                'id'       => $c->id,
                'name'     => $c->name,
                'link'     => $c->link,
                'logo_url' => $c->logo_url,
            ];
        });

        return response()->json($payload);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $query = Customer::with('category')->orderBy('id', 'desc');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($q = $request->get('q')) {
            $query->where('name', 'like', "%$q%");
        }
        if ($request->boolean('with_trashed')) $query->withTrashed();
        if ($request->boolean('trashed_only')) $query->onlyTrashed();

        return response()->json($query->paginate($request->get('per_page', 15))->appends($request->query()));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name'              => 'required|string|max:191',
            'category_id'       => 'required|exists:customer_categories,id',
            'link'              => 'nullable|url|max:500',
            'short_description' => 'nullable|string',
            'logo'              => 'required|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'is_active'         => 'boolean',
        ]);

        $customer = Customer::create([
            'name'              => $data['name'],
            'category_id'       => $data['category_id'],
            'link'              => $data['link'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'is_active'         => $data['is_active'] ?? true,
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store("uploads/customers/{$customer->id}", 'public');
            $customer->logo = $path;
            $customer->save();
        }

        return response()->json([
            'success' => true,
            'data'    => $customer->fresh()->load('category')
        ], 201);
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name'              => 'sometimes|required|string|max:191',
            'category_id'       => 'sometimes|required|exists:customer_categories,id',
            'link'              => 'nullable|url|max:500',
            'short_description' => 'nullable|string',
            'logo'              => 'sometimes|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'is_active'         => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            Storage::disk('public')->deleteDirectory("uploads/customers/{$customer->id}");
            $path = $request->file('logo')->store("uploads/customers/{$customer->id}", 'public');
            $data['logo'] = $path;
        }

        $customer->update($data);

        return response()->json([
            'success' => true,
            'data'    => $customer->fresh()->load('category')
        ]);
    }

    public function destroy(Request $request, Customer $customer)
    {
        $this->authorizeAdmin($request);
        $customer->delete();
        return response()->json(['success' => true]);
    }

    // restore & forceDelete same as before if needed...
}
