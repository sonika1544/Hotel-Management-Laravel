<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\Facility;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPanelController extends Controller
{
    /**
     * Show general hotel information including room types, facilities, and pricing.
     */
    public function index()
    {
        $types = Type::with('rooms.facilities')->get();
        $facilities = Facility::where('is_active', true)->get();

        return view('userpanel.index', compact('types', 'facilities'));
    }

    /**
     * Show details of a specific room including images, facilities, and pricing.
     */
    public function showRoom(Room $room)
    {
        $room->load('type', 'facilities', 'image');

        return view('userpanel.room', compact('room'));
    }

    /**
     * Show booking form for a room.
     */
    public function booking(Room $room)
    {
        if (!Auth::guard('userpanel')->check()) {
            return view('userpanel.auth.ask')->with('room_id', $room->id);
        }

        return view('userpanel.booking', compact('room'));
    }


    /**
     * Handle booking submission.
     */
    public function submitBooking(Request $request, Room $room)
    {
        if (!Auth::check()) {
            return redirect()->route('userpanel.login')->with('room_id', $room->id);
        }

        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
        ]);

        // TODO: Implement booking logic here

        return redirect()->route('hotel.index')->with('success', 'Booking successful!');
    }
}
