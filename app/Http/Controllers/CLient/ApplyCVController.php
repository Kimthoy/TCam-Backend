<?php

namespace App\Http\Controllers\CLient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\ApplyCV;
use Illuminate\Support\Facades\Storage;

class ApplyCVController extends Controller
{
    //
     public function store(Request $request, Job $job)
{
    $data = $request->validate([
        'email' => 'required|email|max:191',
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'gender' => 'required|string|max:6',
        'position_apply' => 'required|string|max:50',
        'phone_number' => 'required|string|max:20',
        'hear_about_job' => 'nullable|string|max:191',
        'referral_name' => 'nullable|string|max:191',
        'status' => 'nullable|string|max:191',
        'cv' => 'required|file|mimes:pdf|max:2048',
        'consent' => 'accepted',
    ]);

    // ✅ Store CV publicly
    $cvPath = $request->file('cv')->store(
        'job-applications',
        'public'
    );

    ApplyCV::create([
        'job_id' => $job->id,
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'gender' => $data['gender'],
        'status' => $data['status'],
        'position_apply' => $data['position_apply'],
        'email' => $data['email'],
        'phone_number' => $data['phone_number'],
        'hear_about_job' => $data['hear_about_job'] ?? null,
        'referral_name' => $data['referral_name'] ?? null,
        'cv_file' => $cvPath, // <-- store RELATIVE path only
        'consent' => true,
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    return response()->json([
        'message' => 'Application submitted successfully',
    ], 201);
}



}
