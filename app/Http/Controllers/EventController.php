<?php

namespace App\Http\Controllers;

use App\Events\NewReservationEvent;
use App\Models\User;

class EventController extends Controller
{
    public function sendEvent()
    {
        $message = 'New reservation has been made';
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            event(new NewReservationEvent($message, $admin));
        }
    }

    public function seeEvent()
    {
        return view('event.index');
    }
}
