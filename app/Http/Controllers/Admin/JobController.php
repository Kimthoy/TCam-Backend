<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Controllers\Controller;
    use App\Models\Job;
    use Illuminate\Http\Request;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\DB;

    class JobController extends Controller
    {
        /**
         * List all Jobs
         */
        public function index()
        {
            $jobs = Job::with([
                'qualification',
                'application_info',
                'responsibilities',
                'benefits',
                'certifications',
                'attributes'
            ])->paginate(15);

            return response()->json($jobs);
        }

        /**
         * Show single Job
         */
        public function show($id)
        {
            $job = Job::with([
                'qualification',
                'application_info',
                'responsibilities',
                'benefits',
                'certifications',
                'attributes',
            ])->findOrFail($id);

            return response()->json($job);
        }

        /**
         * Store a new Job with all related data
         */
        public function store(Request $request)
        {
            $validated = $this->validateJob($request);

            DB::transaction(function () use ($validated) {
                $job = Job::create([
                    'job_title' => $validated['job_title'],
                    'job_slug'  => Str::slug($validated['job_title']),
                    'location'  => $validated['location'] ?? null,
                    'closing_date' => $validated['closing_date'] ?? null,
                    'hiring_number' => $validated['hiring_number'] ?? 1,
                    'job_summary' => $validated['job_summary'] ?? null,
                    'status' => $validated['status'] ?? 'open',
                ]);

                $this->createOrUpdateLists($job, $validated);
            });

            return response()->json(['message' => 'Job created successfully']);
        }

        /**
         * Update an existing Job with all related data
         */
        public function update(Request $request, $id)
        {
            $job = Job::findOrFail($id);
            $validated = $this->validateJob($request);

            DB::transaction(function () use ($job, $validated) {
                // Update main job
                $job->update([
                    'job_title' => $validated['job_title'],
                    'job_slug'  => Str::slug($validated['job_title']),
                    'location'  => $validated['location'] ?? null,
                    'closing_date' => $validated['closing_date'] ?? null,
                    'hiring_number' => $validated['hiring_number'] ?? 1,
                    'job_summary' => $validated['job_summary'] ?? null,
                    'status' => $validated['status'] ?? 'open',
                ]);

                // Delete existing lists before re-inserting
                $job->responsibilities()->delete();
                $job->benefits()->delete();
                $job->certifications()->delete();
                $job->attributes()->delete();
                $job->application_info()->delete();
                $job->qualification()->delete();

                // Re-create related lists and one-to-one
                $this->createOrUpdateLists($job, $validated);
            });

            return response()->json(['message' => 'Job updated successfully']);
        }

        /**
         * Delete a Job and all related data
         */
        public function destroy($id)
        {
            $job = Job::findOrFail($id);
            $job->delete();

            return response()->json(['message' => 'Job deleted successfully']);
        }

        /**
         * Validate Job Request
         */
        protected function validateJob(Request $request)
        {
            return $request->validate([
                'job_title' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'closing_date' => 'nullable|date',
                'hiring_number' => 'nullable|integer|min:1',
                'job_summary' => 'nullable|string',
                'status' => 'nullable|in:open,closed',

                // Lists
                'responsibilities' => 'nullable|array',
                'responsibilities.*' => 'required|string',
                'benefits' => 'nullable|array',
                'benefits.*.title' => 'required|string',
                'benefits.*.description' => 'nullable|string',
                'certifications' => 'nullable|array',
                'certifications.*.name' => 'required|string',
                'certifications.*.required' => 'boolean',
                'attributes' => 'nullable|array',
                'attributes.*' => 'required|string',

                // One-to-one
                'qualifications' => 'nullable|array',
                'qualifications.education_level' => 'nullable|string',
                'qualifications.experience_required' => 'nullable|string',
                'qualifications.technical_skills' => 'nullable|string',
                'qualifications.soft_skills' => 'nullable|string',
                'qualifications.language_requirement' => 'nullable|string',

                'application_info' => 'nullable|array',
                'application_info.email' => 'nullable|email',
                'application_info.phone_number' => 'nullable|string',
                'application_info.telegram_link' => 'nullable|string',
                'application_info.note' => 'nullable|string',
            ]);
        }

        /**
         * Helper: Create related lists & one-to-one
         */
        protected function createOrUpdateLists(Job $job, array $validated)
        {
            // Responsibilities
            foreach ($validated['responsibilities'] ?? [] as $item) {
                $job->responsibilities()->create([
                    'responsibility_text' => $item,
                ]);
            }

            // Benefits
            foreach ($validated['benefits'] ?? [] as $benefit) {
                $job->benefits()->create([
                    'benefit_title' => $benefit['title'],
                    'benefit_description' => $benefit['description'] ?? null,
                ]);
            }

            // Certifications
            foreach ($validated['certifications'] ?? [] as $cert) {
                $job->certifications()->create([
                    'certification_name' => $cert['name'],
                    'is_required' => $cert['required'] ?? false,
                ]);
            }

            // Attributes
            foreach ($validated['attributes'] ?? [] as $attr) {
                $job->attributes()->create([
                    'attribute_text' => $attr,
                ]);
            }

            // Qualification (one-to-one)
            if (!empty($validated['qualifications'])) {
                $job->qualification()->updateOrCreate([], $validated['qualifications']);
            }

            // Application Info (one-to-one)
            if (!empty($validated['application_info'])) {
                $job->application_info()->updateOrCreate([], $validated['application_info']);
            }
        }
    }
