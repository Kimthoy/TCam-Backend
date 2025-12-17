<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplyCV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManageCVController extends Controller
{
    //
    public function index(Request $request)
    {
        $applications = ApplyCV::with('job')
            ->latest()
            ->paginate(15);

        return response()->json($applications);
    }

    public function show($id)
    {
        $application = ApplyCV::with('job')->findOrFail($id);

        return response()->json($application);
    }

    public function destroy($id)
    {
        $application = ApplyCV::findOrFail($id);

        if ($application->cv_file) {
            Storage::disk('public')->delete($application->cv_file);
        }

        $application->delete();

        return response()->json([
            'message' => 'Application deleted successfully'
        ]);
    }
    public function updateStatus(Request $request, $id)
{
    $data = $request->validate([
        'status' => 'required|in:pending,approved,reviewed,shortlisted,rejected',
    ]);

    $application = ApplyCV::findOrFail($id);

    $application->update([
        'status' => $data['status'],
    ]);

    return response()->json([
        'message' => 'Application status updated successfully',
        'status' => $application->status,
    ]);
}
}
