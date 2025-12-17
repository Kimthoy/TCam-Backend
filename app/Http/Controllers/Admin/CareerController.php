<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class CareerController extends Controller
{
    //
     protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }
    }
     public function publicIndex(Request $request)
    {
        // Public list: only active partners, ordered by sort_order then name
        $careers = Career::query()
            ->select('id', 'job_title', 'company', 'location', 'experience'
            ,'skills','salary','benefits','description','job_type','feature_image',
            'contact_email','contact_phone','deadline','featured','education_level',
            'language_requirements','slug') 
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('job_title')
            ->get();

        $payload = $careers->map(function ($c) {
            return [
                'id' => $c->id,'job_title' => $c->job_title,
                'company' => $c->company,'location' => $c->location,
                'experience' => $c->experience, 'skills' => $c->skills, 
                'salary' => $c->salary, 'benefits' => $c->benefits, 
                'description' => $c->description, 'job_type' => $c->job_type, 
                'featured_image' => $c->featured_image, 'contact_email' => $c->contact_email, 
                'contact_phone' => $c->contact_phone, 
                'deadline' => $c->deadline, 
                'featured' => $c->featured, 
                'education_level' => $c->education_level, 
                'language_requirements' => $c->language_requirements, 
                'slug' => $c->slug, 
               
            ];
        });

        return response()->json($payload);
    }

     public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $perPage = (int) $request->get('per_page', 15);

        $query = Career::query()
            ->orderBy('id', 'desc');

        if ($request->boolean('with_trashed')) $query->withTrashed();
        if ($request->boolean('trashed_only')) $query->onlyTrashed();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('job_title', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->paginate($perPage)->appends('feature_image_url')->appends($request->query())
        );
    }
    public function show(Request $request, Career $career)
    {
        $this->authorizeAdmin($request);
        return response()->json($career->append('feature_image_url'));
    }
     public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'job_title'         => 'required|string|max:255',
            'company'       => 'nullable|string|max:150',
            'location'      => 'nullable|string|max:150',
            'experience'        => 'nullable|string|max:100',
            'skills'      => 'nullable|string|max:100',
            'salary'    => 'nullable|string|max:100',
            'benefits'   => 'nullable|string',
            'description'  => 'nullable|string',
            'job_type'      => 'nullable|string',
            'contact_email'   => 'nullable|email',
            'contact_phone'    => 'nullable|string',
            'deadline'      => 'nullable|date',
            'featured'     => 'boolean',
            'education_level'     => 'nullable|string',
            'language_requirements'     => 'nullable|string',
            'slug'     => 'nullable|string',
            'status'     => 'boolean',
            'feature_image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $data['slug'] = Str::slug($data['job_title']);
        $data['status'] ??= true;

        $career = Career::create($data);

        if ($request->hasFile('feature_image')) {
            $path = $request->file('feature_image')
                ->store("uploads/careers/{$career->id}", 'public');
            $career->feature_image = $path;
            $career->save();
        }

        return response()->json([
            'success' => true,
            'data'    => $career->fresh()
        ], 201);
    }

   public function update(Request $request, $id)
{
    $this->authorizeAdmin($request);

    $career = Career::findOrFail($id);

    $data = $request->validate([
        'job_title' => 'nullable|string|max:255',
        'company' => 'nullable|string|max:150',
        'location' => 'nullable|string|max:150',
        'experience' => 'nullable|string|max:100',
        'skills' => 'nullable|string|max:100',
        'salary' => 'nullable|string|max:100',
        'benefits' => 'nullable|string',
        'description' => 'nullable|string',
        'job_type' => 'nullable|string',
        'contact_email' => 'nullable|email',
        'contact_phone' => 'nullable|string',
        'deadline' => 'nullable|date',   // FIXED HERE
        'featured' => 'boolean',
        'education_level' => 'nullable|string',
        'language_requirements' => 'nullable|string',
        'slug' => 'nullable|string',
        'status' => 'boolean',
        'feature_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
    ]);

    if (!empty($data['job_title'])) {
        $data['slug'] = Str::slug($data['job_title']);
    }

    if ($request->hasFile('feature_image')) {
        if ($career->feature_image) {
            Storage::disk('public')->delete($career->feature_image);
        }
        $data['feature_image'] = $request->file('feature_image')
            ->store("uploads/careers/{$career->id}", 'public');
    }

    $career->update($data);

    return response()->json([
        'success' => true,
        'data' => $career->fresh()
    ]);
}
 public function destroy(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $career = Career::findOrFail($id);
        $career->delete();
        return response()->json(['success' => true]);
    }
  public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $career = Career::withTrashed()->findOrFail($id);
        $career->restore();
        return response()->json(['success' => true]);
    }
 public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $career = Career::withTrashed()->findOrFail($id);
        Storage::disk('public')->deleteDirectory("uploads/careers/{$id}");
        $career->forceDelete();

        return response()->json(['success' => true]);
    }
}
