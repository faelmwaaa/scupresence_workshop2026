<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RekapController extends Controller
{
    public function anggota(Request $request)
    {
        $user = Auth::user();
        $orgId = $request->get('org_id');
        $organizations = $user->activeOrganizations()->get();
        $stats = null;

        if ($orgId) {
            $org = Organization::find($orgId);
            if ($org) {
                $schedules = $org->schedules()->with(['presences' => fn($q) => $q->where('user_id', $user->id)])->get();
                $hadir = 0; $terlambat = 0; $tidakHadir = 0; $history = [];
                foreach ($schedules as $schedule) {
                    $presence = $schedule->presences->first();
                    if ($presence) {
                        if ($presence->status === 'hadir') $hadir++;
                        elseif ($presence->status === 'terlambat') $terlambat++;
                        else $tidakHadir++;
                        $history[] = ['schedule_id' => $schedule->id, 'title' => $schedule->title, 'event_date' => $schedule->event_date->format('d M Y'), 'status' => $presence->status];
                    }
                }
                $stats = compact('hadir', 'terlambat', 'tidakHadir', 'history');
            }
        }

        return Inertia::render('Anggota/Rekap', [
            'organizations' => $organizations,
            'selectedOrgId' => $orgId ? (int)$orgId : null,
            'stats' => $stats,
        ]);
    }

    public function pengurus(Request $request)
    {
        $user = Auth::user();
        $orgId = $request->get('org_id');
        $organizations = $user->activeOrganizations()->get();
        $anggotaList = [];

        if ($orgId) {
            $org = Organization::find($orgId);
            if ($org) {
                $anggotaList = $org->activeUsers()->where('users.role', 'anggota')->get()->map(function ($anggota) use ($org) {
                    $presences = $anggota->presences()->whereHas('schedule', fn($q) => $q->where('organization_id', $org->id))->get();
                    $pic = $anggota->profile_picture;
                    if ($pic && !str_starts_with($pic, 'http')) {
                        $pic = \Illuminate\Support\Facades\Storage::url($pic);
                    }
                    return [
                        'id'            => $anggota->id,
                        'name'          => $anggota->name,
                        'nim'           => $anggota->nim,
                        'profile_picture' => $pic,
                        'hadir'         => $presences->where('status', 'hadir')->count(),
                        'terlambat'     => $presences->where('status', 'terlambat')->count(),
                        'tidak_hadir'   => $presences->where('status', 'tidak_hadir')->count(),
                    ];
                });
            }
        }

        return Inertia::render('Pengurus/Rekap', [
            'organizations' => $organizations,
            'selectedOrgId' => $orgId ? (int)$orgId : null,
            'anggotaList' => $anggotaList,
        ]);
    }

    public function anggotaDetail(Request $request, User $anggota, Organization $organization)
    {
        $presences = $anggota->presences()
            ->whereHas('schedule', fn($q) => $q->where('organization_id', $organization->id))
            ->with('schedule')->latest()->get()
            ->map(fn($p) => ['id' => $p->id, 'title' => $p->schedule->title, 'event_date' => $p->schedule->event_date->format('d M Y'), 'status' => $p->status, 'alasan' => $p->alasan]);

        $pic = $anggota->profile_picture;
        if ($pic && !str_starts_with($pic, 'http')) {
            $pic = \Illuminate\Support\Facades\Storage::url($pic);
        }
        return response()->json(['anggota' => ['name' => $anggota->name, 'nim' => $anggota->nim, 'profile_picture' => $pic], 'presences' => $presences]);
    }

    /**
     * Pelatih: same logic as pengurus but renders Pelatih/Rekap.
     */
    public function pelatih(Request $request)
    {
        $user = Auth::user();
        $orgId = $request->get('org_id');
        $organizations = $user->activeOrganizations()->get();
        $anggotaList = [];

        if ($orgId) {
            $org = Organization::find($orgId);
            if ($org) {
                $anggotaList = $org->activeUsers()->where('users.role', 'anggota')->get()->map(function ($anggota) use ($org) {
                    $presences = $anggota->presences()->whereHas('schedule', fn($q) => $q->where('organization_id', $org->id))->get();
                    $pic = $anggota->profile_picture;
                    if ($pic && !str_starts_with($pic, 'http')) {
                        $pic = \Illuminate\Support\Facades\Storage::url($pic);
                    }
                    return [
                        'id'            => $anggota->id,
                        'name'          => $anggota->name,
                        'nim'           => $anggota->nim,
                        'profile_picture' => $pic,
                        'hadir'         => $presences->where('status', 'hadir')->count(),
                        'terlambat'     => $presences->where('status', 'terlambat')->count(),
                        'tidak_hadir'   => $presences->where('status', 'tidak_hadir')->count(),
                    ];
                });
            }
        }

        return Inertia::render('Pelatih/Rekap', [
            'organizations' => $organizations,
            'selectedOrgId' => $orgId ? (int)$orgId : null,
            'anggotaList'   => $anggotaList,
        ]);
    }

    /**
     * Get all presences for a specific schedule (for Pengurus/Pelatih validation UI).
     */
    public function schedulePresences(\App\Models\Schedule $schedule)
    {
        $schedule->load(['presences.user', 'organization']);

        $presences = $schedule->presences->map(function ($p) {
            $pic = $p->user->profile_picture;
            if ($pic && !str_starts_with($pic, 'http')) {
                $pic = \Illuminate\Support\Facades\Storage::url($pic);
            }
            return [
                'id'         => $p->id,
                'status'     => $p->status,
                'alasan'     => $p->alasan,
                'foto_path'  => $p->foto_path ? \Illuminate\Support\Facades\Storage::url($p->foto_path) : null,
                'latitude'   => $p->latitude,
                'longitude'  => $p->longitude,
                'created_at' => $p->created_at->format('H:i'),
                'user' => [
                    'id'              => $p->user->id,
                    'name'            => $p->user->name,
                    'nim'             => $p->user->nim,
                    'profile_picture' => $pic,
                ],
            ];
        });

        return response()->json([
            'schedule'  => [
                'id'         => $schedule->id,
                'title'      => $schedule->title,
                'event_date' => $schedule->event_date->format('d M Y'),
                'is_open'    => $schedule->is_open,
            ],
            'presences' => $presences,
        ]);
    }
}

