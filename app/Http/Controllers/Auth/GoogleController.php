<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth.
     * Accepts ?role= query param (mahasiswa|pelatih)
     */
    public function redirect(Request $request)
    {
        $role = $request->get('role', 'mahasiswa');
        session(['intended_role' => $role]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Google login gagal. Silakan coba lagi.');
        }

        $intendedRole = session('intended_role', 'mahasiswa');

        // Find existing user by google_id or email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Update google_id if not set
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
            Auth::login($user);

            return $this->redirectToDashboard($user);
        }

        // New user - create and redirect
        if (!$user) {
            $user = User::create([
                'google_id'       => $googleUser->getId(),
                'name'            => $googleUser->getName(),
                'email'           => $googleUser->getEmail(),
                'profile_picture' => $googleUser->getAvatar(),
                'role'            => $intendedRole === 'mahasiswa' ? 'anggota' : $intendedRole,
                'account_status'  => 'pending',
            ]);
        } Auth::login($user);

        return redirect()->route('onboarding.' . $intendedRole);
    }

    /**
     * Redirect user to the correct dashboard based on role.
     */
    private function redirectToDashboard(User $user)
    {
        // If user hasn't completed onboarding (no nim/phone for non-pelatih)
        if (in_array($user->role, ['anggota', 'pengurus']) && !$user->nim) {
            return redirect()->route('onboarding.mahasiswa');
        }
        if ($user->role === 'pelatih' && !$user->phone) {
            return redirect()->route('onboarding.pelatih');
        }

        return match ($user->role) {
            'admin'    => redirect()->route('admin.home'),
            'pengurus' => redirect()->route('pengurus.home'),
            'pelatih'  => redirect()->route('pelatih.home'),
            default    => redirect()->route('anggota.home'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
