<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['admin', 'superadmin']), 403);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'nullable|string|max:191',
            'email'   => 'nullable|email|max:191',
            'phone'   => 'nullable|string|max:60',
            'message' => 'required|string',
        ]);

        $data['ip_address']  = $request->ip();
        $data['user_agent']  = $request->userAgent();

        $message = ContactMessage::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your message has been sent.',
            'data' => $message
        ]);
    }
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $query = ContactMessage::query()->orderByDesc('created_at');

        if ($request->boolean('unhandled_only')) {
            $query->where('handled', false);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('message', 'like', "%{$q}%");
            });
        }

        return response()->json(
            $query->paginate($request->get('per_page', 20))
        );
    }

    public function show(Request $request, ContactMessage $contactMessage)
    {
        $this->authorizeAdmin($request);
        return response()->json($contactMessage);
    }

    public function markHandled(Request $request, ContactMessage $contactMessage)
    {
        $this->authorizeAdmin($request);

        $contactMessage->update(['handled' => true]);

        return response()->json([
            'success' => true,
            'data' => $contactMessage
        ]);
    }
    public function bulkMarkHandled(Request $request)
    {
        $this->authorizeAdmin($request);

        $request->validate(['ids' => 'required|array']);

        ContactMessage::whereIn('id', $request->ids)
            ->update(['handled' => true]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, ContactMessage $contactMessage)
    {
        $this->authorizeAdmin($request);
        $contactMessage->delete();

        return response()->json(['success' => true]);
    }
    public function recent(Request $request)
    {
        $limit = $request->get('limit', 10);

        $messages = ContactMessage::latest()
            ->take($limit)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'name' => $msg->name,
                    'text' => $msg->message,
                    'time' => $msg->created_at->diffForHumans(),
                    'avatarColor' => 'bg-blue-500',
                    'email' => $msg->email,
                ];
            });

        return response()->json($messages);
    }
}
