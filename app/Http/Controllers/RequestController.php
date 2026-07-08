<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RequestController extends Controller
{
    /**
     * Show Pengurus request list (pending anggota in their units).
     */
    public function pengurusIndex()
    {
        $user = Auth::user();

        $orgIds = $user->activeOrganizations()->pluck('organizations.id');

        $pendingAnggota = [];
        foreach ($orgIds as $orgId) {
            $org = Organization::find($orgId);
            $pending = $org->pendingUsers()
                ->where('users.role', 'anggota')
                ->get()
                ->map(function ($u) use ($org) {
                    $profilePic = $u->profile_picture;
                    if ($profilePic && !str_starts_with($profilePic, 'http')) {
                        $profilePic = \Illuminate\Support\Facades\Storage::url($profilePic);
                    }
                    return [
                        'id'              => $u->id,
                        'name'            => $u->name,
                        'nim'             => $u->nim,
                        'phone'           => $u->phone,
                        'fakultas'        => $u->fakultas,
                        'jabatan'         => $u->pivot->jabatan,
                        'profile_picture' => $profilePic,
                        'org_id'          => $org->id,
                        'org_name'        => $org->name,
                    ];
                });
            $pendingAnggota = array_merge($pendingAnggota, $pending->toArray());
        }

        return Inertia::render('Pengurus/RequestList', [
            'pendingRequests' => $pendingAnggota,
        ]);
    }

    /**
     * Accept anggota into a unit.
     */
    public function acceptAnggota(Request $request, User $user, Organization $organization)
    {
        $authUser = Auth::user();

        // Ensure auth user belongs to this org as pengurus
        $membership = $authUser->organizations()->where('organization_id', $organization->id)->first();
        if (!$membership) {
            abort(403);
        }

        $jabatan = strtolower(trim($membership->pivot->jabatan ?? ''));
        if (!in_array($jabatan, ['ketua', 'wakil', 'wakil ketua'])) {
            return back()->with('error', 'Hanya Ketua dan Wakil yang berhak menyetujui anggota.');
        }

        $user->organizations()->updateExistingPivot($organization->id, [
            'membership_status' => 'active',
        ]);

        return back()->with('success', "{$user->name} telah diterima.");
    }

    /**
     * Decline anggota from a unit.
     */
    public function declineAnggota(Request $request, User $user, Organization $organization)
    {
        $authUser = Auth::user();
        $membership = $authUser->organizations()->where('organization_id', $organization->id)->first();
        if (!$membership) {
            abort(403);
        }

        $jabatan = strtolower(trim($membership->pivot->jabatan ?? ''));
        if (!in_array($jabatan, ['ketua', 'wakil', 'wakil ketua'])) {
            return back()->with('error', 'Hanya Ketua dan Wakil yang berhak menolak anggota.');
        }

        $user->organizations()->detach($organization->id);

        return back()->with('success', "{$user->name} telah ditolak.");
    }

    /**
     * Remove an existing anggota from a unit (Pengurus action).
     */
    public function removeAnggota(Request $request, User $user, Organization $organization)
    {
        $authUser = Auth::user();
        $membership = $authUser->organizations()->where('organization_id', $organization->id)->first();
        if (!$membership || !$membership->pivot->is_pengurus) {
            abort(403);
        }

        $user->organizations()->detach($organization->id);
        return back()->with('success', "{$user->name} telah dikeluarkan dari unit.");
    }

    /**
     * Show Admin request list (pending pengurus & pelatih).
     */
    public function adminIndex()
    {
        $pendingUsers = User::whereIn('role', ['pengurus', 'pelatih'])
            ->where('account_status', 'pending')
            ->with('organizations')
            ->get()
            ->map(function ($user) {
                // Transform relative profile picture paths to full storage URLs
                if ($user->profile_picture && !str_starts_with($user->profile_picture, 'http')) {
                    $user->profile_picture = \Illuminate\Support\Facades\Storage::url($user->profile_picture);
                }
                return $user;
            });

        return Inertia::render('Admin/RequestList', [
            'pendingUsers' => $pendingUsers,
        ]);
    }

    /**
     * Admin accepts a pengurus/pelatih account.
     */
    public function adminAccept(User $user)
    {
        $user->update(['account_status' => 'active']);
        return back()->with('success', "{$user->name} telah diaktifkan.");
    }

    /**
     * Admin declines a pengurus/pelatih account.
     */
    public function adminDecline(User $user)
    {
        $user->delete();
        return back()->with('success', "Akun telah ditolak dan dihapus.");
    }
}
