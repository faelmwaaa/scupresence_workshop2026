<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // 1. Send the user to Google
    public function redirect(Request $request)
    {
        // Remember if they clicked the 'Anggota' or 'BPH' button
        $intendedRole = $request->query('role', 'anggota');
        session(['intended_role' => $intendedRole]);

        return Socialite::driver('google')->redirect();
    }

    // 2. Google sends them back here
    public function callback()
    {
        try {
            // Grab the user details from Google
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Google Login failed. Please try again.']);
        }

        // Check if this student already exists in our database
        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            // Welcome back! Log them in and send to their dashboard
            Auth::login($existingUser);
            return redirect()->route('dashboard');
        } else {
            // New user! We need to ask for their NIM and Unit.
            // Save their Google data to the session so we can pre-fill the form for them
            session([
                'google_name' => $googleUser->getName(),
                'google_email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
            ]);

            return redirect()->route('register');
        }
    }
}