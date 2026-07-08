<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    /** Show Mahasiswa onboarding form */
    public function showMahasiswa()
    {
        $organizations = Organization::all();
        return Inertia::render('Onboarding/Mahasiswa', [
            'user' => Auth::user(),
            'organizations' => $organizations,
        ]);
    }

    /** Save Mahasiswa profile */
    public function saveMahasiswa(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'nim'       => 'required|string|max:50',
            'phone'     => 'required|string|max:20',
            'fakultas'  => 'required|string|max:100',
            'units'     => 'required|array|min:1',
            'units.*.organization_id' => 'required|exists:organizations,id',
            'units.*.is_pengurus'     => 'required|boolean',
            'units.*.jabatan'         => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture = $path;
        }

        $hasPengurus = collect($request->units)->contains('is_pengurus', true);

        $user->update([
            'name'           => $request->name,
            'nim'            => $request->nim,
            'phone'          => $request->phone,
            'fakultas'       => $request->fakultas,
            'role'           => $hasPengurus ? 'pengurus' : 'anggota',
            'account_status' => $hasPengurus ? 'pending' : 'active',
        ]);

        // Attach organizations
        foreach ($request->units as $unit) {
            // Avoid duplicate entries
            $existing = $user->organizations()->where('organization_id', $unit['organization_id'])->first();
            if (!$existing) {
                $isPengurus = $unit['is_pengurus'] ?? false;
                $user->organizations()->attach($unit['organization_id'], [
                    'is_pengurus'      => $isPengurus,
                    'jabatan'          => $isPengurus ? ($unit['jabatan'] ?? 'Pengurus') : ($unit['jabatan'] ?? null),
                    // If Pengurus, they are 'active' locally (but their whole account is pending admin approval).
                    // If Anggota, they are 'pending' locally (waiting for Pengurus approval).
                    'membership_status' => $isPengurus ? 'active' : 'pending',
                ]);
            }
        }

        if ($hasPengurus) {
            return redirect()->route('pengurus.home');
        } else {
            return redirect()->route('anggota.home');
        }
    }

    /** Show Pelatih onboarding form */
    public function showPelatih()
    {
        $organizations = Organization::where('type', 'ukm')->get();
        return Inertia::render('Onboarding/Pelatih', [
            'user' => Auth::user(),
            'organizations' => $organizations,
        ]);
    }

    /** Save Pelatih profile */
    public function savePelatih(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'units' => 'required|array|min:1',
            'units.*.organization_id' => 'required|exists:organizations,id',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture = $path;
        }

        $user->update([
            'name'           => $request->name,
            'phone'          => $request->phone,
            'account_status' => 'pending',
        ]);

        foreach ($request->units as $unit) {
            $existing = $user->organizations()->where('organization_id', $unit['organization_id'])->first();
            if (!$existing) {
                $user->organizations()->attach($unit['organization_id'], [
                    'membership_status' => 'active',
                ]);
            }
        }

        return redirect()->route('pelatih.home');
    }

    /** Update profile picture */
    public function updateProfilePicture(Request $request)
    {
        $request->validate(['profile_picture' => 'required|image|max:2048']);
        $user = Auth::user();
        $path = $request->file('profile_picture')->store('profile-pictures', 'public');
        $user->update(['profile_picture' => $path]);
        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
