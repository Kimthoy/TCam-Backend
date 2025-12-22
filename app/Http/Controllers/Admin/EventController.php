<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * List events
     */
public function index()
{
    $events = Event::latest()->paginate(10);

    $events->getCollection()->transform(function ($event) {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'subtitle' => $event->subtitle,
            'event_date' => $event->event_date,
            'location' => $event->location,
            'category' => $event->category,
            'poster_image' => $event->poster_image,
            'poster_image_url' => $event->poster_image_url, // accessor used here
            'description' => $event->description,
            'participants' => json_decode($event->participants[0] ?? '[]'), // decode properly
            'certifications' => json_decode($event->certifications[0] ?? '[]'),
            'certificates' => json_decode($event->certificates[0] ?? '[]'),
            'is_published' => $event->is_published,
            'created_at' => $event->created_at,
            'updated_at' => $event->updated_at,
        ];
    });

    return response()->json($events);
}



    /**
     * Store new event
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:255',
            'event_date'     => 'nullable|date',
            'location'       => 'nullable|string|max:255',
            'category'       => 'nullable|string|max:100',
            'poster_image'   => 'nullable|file|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'description'    => 'nullable|string',
            'participants'   => 'nullable|array',
            'certifications' => 'nullable|array',
            'certificates'   => 'nullable|array',
            'is_published'   => 'boolean',
        ]);

        // Handle poster image upload
        if ($request->hasFile('poster_image')) {
            $data['poster_image'] = $request->file('poster_image')
                ->store('events/posters', 'public');
        }

        $event = Event::create($data);

        return response()->json([
            'message' => 'Event created successfully',
            'data'    => $event,
        ], 201);
    }

    /**
     * Show single event
     */
    public function show(Event $event)
    {
        return response()->json($event);
    }

    /**
     * Update event
     */
    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title'          => 'sometimes|required|string|max:255',
            'subtitle'       => 'nullable|string|max:255',
            'event_date'     => 'nullable|date',
            'location'       => 'nullable|string|max:255',
            'category'       => 'nullable|string|max:100',
            'poster_image'   => 'nullable|image|max:2048',
            'description'    => 'nullable|string',
            'participants'   => 'nullable|array',
            'certifications' => 'nullable|array',
            'certificates'   => 'nullable|array',
            'is_published'   => 'boolean',
        ]);

        // Replace poster image if uploaded
        if ($request->hasFile('poster_image')) {
            if ($event->poster_image) {
                Storage::disk('public')->delete($event->poster_image);
            }

            $data['poster_image'] = $request->file('poster_image')
                ->store('events/posters', 'public');
        }

        $event->update($data);

        return response()->json([
            'message' => 'Event updated successfully',
            'data'    => $event,
        ]);
    }

    /**
     * Delete event
     */
    public function destroy(Event $event)
    {
        if ($event->poster_image) {
            Storage::disk('public')->delete($event->poster_image);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully',
        ]);
    }
}
