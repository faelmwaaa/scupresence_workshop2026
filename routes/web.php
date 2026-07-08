<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ScheduleController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ======================
// DEMO QUICK-LOGIN (presentation only)
// ======================
Route::get('/demo-login/{role}', function (string $role) {
    $user = User::where('role', $role)->where('account_status', 'active')->first();
    if (!$user) abort(404, "No active demo user for role: $role");
    Auth::login($user);
    return match ($role) {
        'admin'    => redirect()->route('admin.home'),
        'pengurus' => redirect()->route('pengurus.home'),
        'pelatih'  => redirect()->route('pelatih.home'),
        default    => redirect()->route('anggota.home'),
    };
})->whereIn('role', ['admin', 'pengurus', 'pelatih', 'anggota']);


// ======================
// PUBLIC API ROUTES
// ======================
Route::get('/api/organizations', function () {
    return \App\Models\Organization::orderBy('type')->orderBy('name')->get();
})->middleware('auth');

// ======================
// PUBLIC ROUTES
// ======================
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->role) {
            'admin'    => redirect()->route('admin.home'),
            'pengurus' => redirect()->route('pengurus.home'),
            'pelatih'  => redirect()->route('pelatih.home'),
            default    => redirect()->route('anggota.home'),
        };
    }
    return Inertia::render('Auth/Login');
})->name('login');

// Admin Secret Door
Route::get('/admin/secret-door', function () {
    return Inertia::render('Admin/SecretDoor');
})->name('admin.secret-door');

Route::post('/admin/secret-door', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.home');
        }
        Auth::logout();
        return back()->withErrors(['email' => 'Akun ini bukan admin.']);
    }
    return back()->withErrors(['email' => 'Kredensial tidak valid.']);
})->name('admin.login');

// ======================
// GOOGLE OAUTH
// ======================
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
Route::post('/logout', [GoogleController::class, 'logout'])->name('logout');

// ======================
// ONBOARDING
// ======================
Route::middleware('auth')->group(function () {
    Route::get('/onboarding/mahasiswa', [OnboardingController::class, 'showMahasiswa'])->name('onboarding.mahasiswa');
    Route::post('/onboarding/mahasiswa', [OnboardingController::class, 'saveMahasiswa'])->name('onboarding.mahasiswa.save');

    Route::get('/onboarding/pelatih', [OnboardingController::class, 'showPelatih'])->name('onboarding.pelatih');
    Route::post('/onboarding/pelatih', [OnboardingController::class, 'savePelatih'])->name('onboarding.pelatih.save');

    Route::post('/profile/picture', [OnboardingController::class, 'updateProfilePicture'])->name('profile.picture');

    // API accessible by pengurus/pelatih
    Route::post('/presence/{presence}/validate', [\App\Http\Controllers\PresenceController::class, 'validatePresence'])->name('presence.validate');
});

// ======================
// ANGGOTA ROUTES
// ======================
Route::middleware(['auth', 'role:anggota'])->prefix('anggota')->name('anggota.')->group(function () {
    Route::get('/home', function () {
        $user = Auth::user();
        $organizations = $user->activeOrganizations()->get();
        $allOrganizations = $user->organizations()->withPivot('jabatan', 'membership_status')->get();
        return Inertia::render('Anggota/Home', [
            'user' => $user,
            'organizations' => $organizations,
            'allOrganizations' => $allOrganizations,
        ]);
    })->name('home');

    Route::get('/rekap', [RekapController::class, 'anggota'])->name('rekap');
    Route::get('/profil', function () {
        return Inertia::render('Anggota/Profil', ['user' => Auth::user()]);
    })->name('profil');

    // Fetch schedules for a specific organization (anggota-accessible)
    Route::get('/org-schedules/{organization}', function (\App\Models\Organization $organization) {
        $user = Auth::user();
        $membership = $user->organizations()->where('organization_id', $organization->id)->wherePivot('membership_status', 'active')->first();
        if (!$membership) return response()->json(['upcoming' => [], 'past' => []]);
        return response()->json([
            'upcoming' => $organization->upcomingSchedules()->get(),
            'past'     => $organization->pastSchedules()->get(),
        ]);
    })->name('org-schedules');

    Route::get('/schedules/{schedule}', [ScheduleController::class, 'show'])->name('schedule.show');
    Route::post('/schedules/{schedule}/hadir', [PresenceController::class, 'store'])->name('presence.store');

    // Join new unit
    Route::post('/join-unit', function (\Illuminate\Http\Request $request) {
        $request->validate(['organization_id' => 'required|exists:organizations,id', 'jabatan' => 'nullable|string']);
        $user = Auth::user();
        $existing = $user->organizations()->where('organization_id', $request->organization_id)->first();
        if (!$existing) {
            $user->organizations()->attach($request->organization_id, ['jabatan' => $request->jabatan, 'membership_status' => 'pending']);
        }
        return back()->with('success', 'Permintaan bergabung telah dikirim!');
    })->name('join-unit');
});

// ======================
// PENGURUS ROUTES
// ======================
Route::middleware(['auth', 'role:pengurus'])->prefix('pengurus')->name('pengurus.')->group(function () {
    Route::get('/home', function () {
        $user = Auth::user();
        $organizations = $user->activeOrganizations()->get();
        return Inertia::render('Pengurus/Home', ['user' => $user, 'organizations' => $organizations]);
    })->name('home');

    Route::get('/rekap', [RekapController::class, 'pengurus'])->name('rekap');
    Route::get('/rekap/{anggota}/{organization}', [RekapController::class, 'anggotaDetail'])->name('rekap.detail');
    Route::get('/requests', [RequestController::class, 'pengurusIndex'])->name('requests');
    Route::post('/requests/{user}/{organization}/accept', [RequestController::class, 'acceptAnggota'])->name('requests.accept');
    Route::post('/requests/{user}/{organization}/decline', [RequestController::class, 'declineAnggota'])->name('requests.decline');
    Route::delete('/rekap/{user}/{organization}/remove', [RequestController::class, 'removeAnggota'])->name('rekap.remove');
    Route::get('/profil', function () {
        return Inertia::render('Pengurus/Profil', ['user' => Auth::user()]);
    })->name('profil');

    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{organization}', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules/{schedule}/toggle-open', [ScheduleController::class, 'toggleOpen'])->name('schedules.toggleOpen');
    Route::get('/presences/{schedule}', [RekapController::class, 'schedulePresences'])->name('presences.index');
});

// ======================
// PELATIH ROUTES
// ======================
Route::middleware(['auth', 'role:pelatih'])->prefix('pelatih')->name('pelatih.')->group(function () {
    Route::get('/home', function () {
        $user = Auth::user();
        $organizations = $user->activeOrganizations()->get();
        return Inertia::render('Pelatih/Home', ['user' => $user, 'organizations' => $organizations]);
    })->name('home');

    Route::get('/rekap', [RekapController::class, 'pelatih'])->name('rekap');
    Route::get('/rekap/{anggota}/{organization}', [RekapController::class, 'anggotaDetail'])->name('rekap.detail');
    Route::get('/profil', function () {
        return Inertia::render('Pelatih/Profil', ['user' => Auth::user()]);
    })->name('profil');

    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{organization}', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules/{schedule}/toggle-open', [ScheduleController::class, 'toggleOpen'])->name('schedules.toggleOpen');
    Route::get('/presences/{schedule}', [RekapController::class, 'schedulePresences'])->name('presences.index');
});

// ======================
// ADMIN ROUTES
// ======================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/home', [AdminController::class, 'home'])->name('home');
    Route::get('/unit/{organization}', [AdminController::class, 'unitDetail'])->name('unit.detail');
    Route::get('/requests', [RequestController::class, 'adminIndex'])->name('requests');
    Route::post('/requests/{user}/accept', [RequestController::class, 'adminAccept'])->name('requests.accept');
    Route::post('/requests/{user}/decline', [RequestController::class, 'adminDecline'])->name('requests.decline');
});
