<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresenceController extends Controller
{
    /**
     * Submit attendance for an event.
     * New logic: allowed any time on event day as long as schedule is_open = true.
     * Status saved as 'menunggu' — Pengurus validates later.
     */
    public function store(Request $request, Schedule $schedule)
    {
        $user = Auth::user();

        // Prevent duplicate submissions
        if ($schedule->presences()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda sudah mengisi absensi untuk event ini.');
        }

        // Validate: schedule must be open and today
        if (!$schedule->is_open) {
            return back()->with('error', 'Presensi untuk event ini belum/sudah ditutup oleh pengurus.');
        }

        if (!$schedule->event_date->isToday()) {
            return back()->with('error', 'Presensi hanya bisa dilakukan pada hari yang sama dengan event.');
        }

        // Validate input — photo + GPS required
        $request->validate([
            'foto'      => 'required|mimes:jpeg,png,jpg,gif,svg,webp,heic,heif|max:5120',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ], [
            'foto.required'      => 'Foto bukti kehadiran wajib diunggah.',
            'latitude.required'  => 'Lokasi GPS harus diizinkan.',
            'longitude.required' => 'Lokasi GPS harus diizinkan.',
        ]);

        $fotoPath = $request->file('foto')->store('presences', 'public');

        Presence::create([
            'schedule_id' => $schedule->id,
            'user_id'     => $user->id,
            'status'      => 'menunggu', // Pengurus validates later
            'alasan'      => null,
            'foto_path'   => $fotoPath,
            'latitude'    => $request->input('latitude'),
            'longitude'   => $request->input('longitude'),
        ]);

        return redirect()->route('anggota.home')->with('success', 'Absensi berhasil dikirim! Menunggu validasi pengurus.');
    }

    /**
     * Pengurus validates a presence record — set final status.
     */
    public function validatePresence(Request $request, Presence $presence)
    {
        $user = Auth::user();

        // Ensure pengurus belongs to the schedule's organization and is active
        $orgId = $presence->schedule->organization_id;
        $membership = $user->organizations()->where('organization_id', $orgId)->first();
        if (!$membership || $membership->pivot->membership_status !== 'active') {
            abort(403);
        }

        // Restrict to Ketua and Wakil
        $jabatan = strtolower(trim($membership->pivot->jabatan ?? ''));
        if (!in_array($jabatan, ['ketua', 'wakil', 'wakil ketua'])) {
            return response()->json(['success' => false, 'message' => 'Hanya Ketua dan Wakil yang berhak memvalidasi presensi.'], 403);
        }

        $request->validate([
            'status' => 'required|in:hadir,terlambat,tidak_hadir',
        ]);

        \Log::info('Validating presence:', ['id' => $presence->id, 'new_status' => $request->status, 'old_status' => $presence->status]);

        $saved = $presence->update(['status' => $request->status]);

        \Log::info('Update result:', ['saved' => $saved, 'db_status' => $presence->fresh()->status]);

        return response()->json(['success' => true, 'status' => $request->status]);
    }
}
