<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        // 1. Fetch all units/categories so the frontend can build a dropdown
        $organizations = \App\Models\Organization::where('level', '!=', 'master')->get();

        return Inertia::render('Auth/Register', [
            'organizations' => $organizations
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 2. Validate the dynamic data!
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => 'required|in:anggota,bph',
            'organization_id' => 'required|exists:organizations,id',
            
            // Conditional validation (NIM only required if Anggota, etc.)
            'nim' => 'required_if:role,anggota|nullable|string|max:20',
            'phone' => 'required_if:role,bph|nullable|string|max:20',
            'jabatan' => 'required_if:role,bph|nullable|string|max:50',
        ]);

        // 3. The Security Gate: BPH accounts are locked (pending) by default
        $status = $request->role === 'bph' ? 'pending' : 'active';

        // 4. Save to database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            'organization_id' => $request->organization_id,
            'nim' => $request->nim,
            'phone' => $request->phone,
            'jabatan' => $request->jabatan,
            'account_status' => $status,
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
