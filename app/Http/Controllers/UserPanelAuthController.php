<?php

namespace App\Http\Controllers;

use App\Models\UserPanelUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class UserPanelAuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('userpanel.auth.register');
    }

    /**
     * Handle customer registration and send welcome email.
     */
    public function register(Request $request)
{
    // Validate the input fields
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:userpanel_users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);
    
    // Create the user in the userpanel_users table
    $user = UserPanelUser::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'random_key' => Str::random(32), // Optionally you can use this for additional security or user verification
    ]);

    // Log the user in using the 'userpanel' guard
    Auth::guard('userpanel')->login($user);

    // Send welcome email to the user
    Mail::to($user->email)->send(new WelcomeUserMail($user));
    
    // Redirect to booking page if room_id exists, otherwise redirect to hotel index
    $roomId = $request->input('room_id');
    if ($roomId) {
        return redirect()->route('userpanel.booking', ['room' => $roomId]);
    } else {
        return redirect()->route('hotel.index');
    }
}


    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('userpanel.auth.login');
    }

    /**
     * Handle login.
     */
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    // Attempt login
    if (Auth::guard('userpanel')->attempt($request->only('email', 'password'))) {
        // After login, check if there's a room_id to redirect to booking page
        $roomId = $request->input('room_id');

        if ($roomId) {
            // Redirect to the booking page with the room id
            return redirect()->route('userpanel.booking', ['room' => $roomId]);
        }

        // Otherwise, redirect to the default page
        return redirect()->route('dashboard.index');
    }

    // If authentication fails, return with error
    return back()->withErrors(['email' => 'Invalid credentials.']);
}

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('userpanel')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('hotel.index');
    }
}
