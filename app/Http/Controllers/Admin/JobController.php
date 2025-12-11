<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobController extends Controller
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

        $query = Job::query()
            ->orderBy('id', 'desc');

        if ($request->boolean('with_trashed')) $query->withTrashed();
        if ($request->boolean('trashed_only')) $query->onlyTrashed();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function show(Request $request, Job $job)
    {
        $this->authorizeAdmin($request);
        return response()->json($job->append('feature_image_url'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'company'       => 'nullable|string|max:150',
            'location'      => 'nullable|string|max:150',
            'salary'        => 'nullable|string|max:100',
            'job_type'      => 'nullable|string|max:100',
            'experience'    => 'nullable|string|max:100',
            'description'   => 'required|string',
            'requirements'  => 'nullable|string',
            'benefits'      => 'nullable|string',
            'apply_email'   => 'nullable|email',
            'apply_link'    => 'nullable|url|max:500',
            'deadline'      => 'nullable|date|after:today',
            'is_active'     => 'boolean',
            'feature_image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['is_active'] ??= true;

        $job = Job::create($data);

        if ($request->hasFile('feature_image')) {
            $path = $request->file('feature_image')
                ->store("uploads/jobs/{$job->id}", 'public');
            $job->feature_image = $path;
            $job->save();
        }

        return response()->json([
            'success' => true,
            'data'    => $job->fresh()
        ], 201);
    }

    public function update(Request $request, Job $job)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title'         => 'sometimes|required|string|max:255',
            'company'       => 'nullable|string|max:150',
            'location'      => 'nullable|string|max:150',
            'salary'        => 'nullable|string|max:100',
            'job_type'      => 'nullable|string|max:100',
            'experience'    => 'nullable|string|max:100',
            'description'   => 'sometimes|required|string',
            'requirements'  => 'nullable|string',
            'benefits'      => 'nullable|string',
            'apply_email'   => 'nullable|email',
            'apply_link'    => 'nullable|url|max:500',
            'deadline'      => 'nullable|date',
            'is_active'     => 'boolean',
            'is_closed'     => 'boolean',
            'feature_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($request->hasFile('feature_image')) {
            if ($job->feature_image) {
                Storage::disk('public')->delete($job->feature_image);
            }
            $data['feature_image'] = $request->file('feature_image')
                ->store("uploads/jobs/{$job->id}", 'public');
        }

        $job->update($data);

        return response()->json([
            'success' => true,
            'data'    => $job->fresh()
        ]);
    }

    public function destroy(Request $request, Job $job)
    {
        $this->authorizeAdmin($request);
        $job->delete();
        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $job = Job::withTrashed()->findOrFail($id);
        $job->restore();
        return response()->json(['success' => true]);
    }

    public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $job = Job::withTrashed()->findOrFail($id);
        Storage::disk('public')->deleteDirectory("uploads/jobs/{$id}");
        $job->forceDelete();

        return response()->json(['success' => true]);
    }
}
