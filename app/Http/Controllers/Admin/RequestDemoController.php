<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestDemo;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Mail;
use App\Mail\DemoRequestSubmitted;

class RequestDemoController extends Controller
{
    //


     public function index(): JsonResponse
    {
        $requests = RequestDemo::all();
        return response()->json($requests);
    }



    public function store(Request $request): JsonResponse
    {
        // -----------------------
        // 1️⃣ Honeypot (bot protection)
        // -----------------------
        if ($request->filled('website')) {
            return response()->json(['message' => 'Invalid request'], 422);
        }

        // -----------------------
        // 2️⃣ Validate input
        // -----------------------
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255',
            'company' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // -----------------------
        // 3️⃣ Check DB for duplicate email
        // -----------------------
        if (RequestDemo::where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => 'You have already submitted a demo request. Please wait 24 hours.'
            ], 429);
        }


        // -----------------------
        // 5️⃣ Email-based limit (1 request per 24 hours)
        // -----------------------
        $emailKey = 'request_demo_email_' . md5($validated['email']);
        if (Cache::has($emailKey)) {
            return response()->json([
                'message' => 'You have already submitted a demo request. Please wait 24 hours.'
            ], 429);
        }

        Cache::put($emailKey, true, now()->addHours(24));

        // -----------------------
        // 6️⃣ Save data
        // -----------------------
        $requestDemo = RequestDemo::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'company'     => $validated['company'] ?? null,
            'description' => $validated['description'] ?? null,
            'status'      => 'PENDING',   // always PENDING for client submissions
            'ip_address'  => $request->ip(),
        ]);

        // -----------------------
        // 7️⃣ Send email notification
        // -----------------------
        try {
            Mail::to('pheangtiger03@gmail.com')->send(new DemoRequestSubmitted($requestDemo));
        } catch (\Exception $e) {
            // If email fails, still allow submission
            \Log::error('DemoRequest email failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Demo request submitted successfully',
            'data'    => $requestDemo
        ], 201);
    }
    /**
     * Display the specified demo request.
     */
    public function show(RequestDemo $requestDemo): JsonResponse
    {
        return response()->json($requestDemo);
    }

    /**
     * Update the specified demo request.
     */
   public function update(Request $request, $id): JsonResponse
{
    $requestDemo = RequestDemo::findOrFail($id);

    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:request_demos,email,' . $requestDemo->id,
        'company' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'status' => 'sometimes|string',
    ]);

    $requestDemo->update($validated);

    return response()->json([
        'message' => 'Demo request updated successfully',
        'data' => $requestDemo
    ]);
}
public function destroy($id): JsonResponse
{
    $requestDemo = RequestDemo::findOrFail($id);
    $requestDemo->delete();

    return response()->json([
        'message' => 'Demo request deleted successfully'
    ]);
}

public function updateStatus(Request $request, $id): JsonResponse
{
    $requestDemo = RequestDemo::findOrFail($id);

    // Validate only status
    $validated = $request->validate([
        'status' => 'required|string|in:PENDING,APPROVED,REJECTED,CONTACTED',
    ]);

    // Update status
    $requestDemo->status = $validated['status'];
    $requestDemo->save(); // <-- important!

    return response()->json([
        'message' => "Demo request status updated to {$validated['status']}",
        'data' => $requestDemo,
    ]);
}


}
