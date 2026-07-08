<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function home()
    {
        $organizations = Organization::all();
        return Inertia::render('Admin/Home', ['organizations' => $organizations]);
    }

    public function unitDetail(Organization $organization)
    {
        $organization->load([
            'activeUsers',
            'upcomingSchedules',
            'pastSchedules',
        ]);

        $members = $organization->users()->withPivot('jabatan', 'membership_status')->get()->map(function ($u) {
            $pic = $u->profile_picture;
            if ($pic && !str_starts_with($pic, 'http')) {
                $pic = \Illuminate\Support\Facades\Storage::url($pic);
            }
            return [
                'id'               => $u->id,
                'name'             => $u->name,
                'nim'              => $u->nim,
                'email'            => $u->email,
                'role'             => $u->role,
                'jabatan'          => $u->pivot->jabatan,
                'membership_status'=> $u->pivot->membership_status,
                'profile_picture'  => $pic,
            ];
        });

        return Inertia::render('Admin/UnitDetail', [
            'organization' => $organization,
            'members' => $members,
            'upcomingSchedules' => $organization->upcomingSchedules()->get(),
            'pastSchedules' => $organization->pastSchedules()->get(),
        ]);
    }

    public function removeAnggota(Request $request, Organization $organization, User $user)
    {
        $user->organizations()->detach($organization->id);
        return back()->with('success', "{$user->name} telah dikeluarkan dari unit.");
    }
}
