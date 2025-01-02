<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::all();

        return Inertia::render('Events/Index', [
            'events' => $events
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'event_name' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'status' => 'required|in:active,inactive',
        'shop' => 'required', // Assuming 'shop' is either the shop name or shop identifier
    ]);

        // Step 1: Get the user by the 'shop' field (assuming 'shop' is a unique identifier or name)
        $user = User::where('shop', $request->shop)->first();

        // Step 2: Check if the user exists
        if (!$user) {
            return response()->json([
                'message' => 'User not found for the provided shop.'
            ], 404);
        }

        // Step 3: Save the event with the user's id in the shop_id field
        $eventData = $validated;
        $eventData['shop_id'] = $user->id; // Save the user's id in the 'shop_id' field

        // Create the event
        $event = Event::create($eventData);

        // Return the newly created event as a JSON response
        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
