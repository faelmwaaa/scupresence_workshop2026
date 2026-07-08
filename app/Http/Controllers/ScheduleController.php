<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    /**
     * Get schedules for a specific organization (for Pengurus/Pelatih home).
     */
    public function index(Request $request, Organization $organization)
    {
        $user = Auth::user();

        // Verify user belongs to this org
        $membership = $user->organizations()->where('organization_id', $organization->id)->first();
        if (!$membership || $membership->pivot->membership_status !== 'active') {
            abort(403);
        }

        $upcomingSchedules = $organization->upcomingSchedules()->with('creator')->get();
        $pastSchedules = $organization->pastSchedules()->with('creator')->get();

        return response()->json([
            'upcoming' => $upcomingSchedules,
            'past'     => $pastSchedules,
        ]);
    }

    /**
     * Create a new event.
     */
    public function store(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'title'           => 'required|string|max:255',
            'event_date'      => 'required|date',
            'start_time'      => 'required|date_format:H:i',
            'end_time'        => 'required|date_format:H:i|after:start_time',
            'location'        => 'required|string|max:255',
            'description'     => 'nullable|string',
            'is_recurring'    => 'nullable|boolean',
            'repeat_until'    => 'nullable|date|after_or_equal:event_date',
        ]);

        $user = Auth::user();

        // Verify user belongs to the org and is active
        $membership = $user->organizations()
            ->where('organization_id', $request->organization_id)
            ->first();

        if (!$membership || $membership->pivot->membership_status !== 'active') {
            abort(403, 'Anda tidak berhak membuat event untuk unit ini.');
        }

        $isRecurring = $request->boolean('is_recurring');
        $repeatUntil = $isRecurring && $request->repeat_until ? \Carbon\Carbon::parse($request->repeat_until) : \Carbon\Carbon::parse($request->event_date);
        $currentDate = \Carbon\Carbon::parse($request->event_date);

        while ($currentDate->lte($repeatUntil)) {
            Schedule::create([
                'organization_id' => $request->organization_id,
                'created_by'      => $user->id,
                'title'           => $request->title,
                'event_date'      => $currentDate->format('Y-m-d'),
                'start_time'      => $request->start_time,
                'end_time'        => $request->end_time,
                'location'        => $request->location,
                'description'     => $request->description,
                'is_recurring'    => $isRecurring,
                'repeat_until'    => $request->repeat_until,
            ]);

            if (!$isRecurring) {
                break;
            }

            $currentDate->addWeek();
        }

        return back()->with('success', 'Event berhasil dibuat!');
    }

    /**
     * Show a single schedule for attendance submission.
     */
    public function show(Schedule $schedule)
    {
        $user = Auth::user();
        $schedule->load('organization');

        // Check if user already submitted attendance
        $existingPresence = $schedule->presences()->where('user_id', $user->id)->first();

        return Inertia::render('Anggota/AttendanceForm', [
            'schedule'         => $schedule,
            'existingPresence' => $existingPresence,
            'canAttend'        => $schedule->canAttend(),
        ]);
    }

    /**
     * Toggle is_open for a schedule (Pengurus/Pelatih only).
     */
    public function toggleOpen(Schedule $schedule)
    {
        $user = Auth::user();

        $membership = $user->organizations()
            ->where('organization_id', $schedule->organization_id)
            ->first();

        if (!$membership || $membership->pivot->membership_status !== 'active') {
            abort(403, 'Anda tidak berhak mengubah status presensi ini.');
        }

        if (!$schedule->event_date->isToday()) {
            return response()->json(['error' => 'Presensi hanya bisa dibuka atau ditutup pada hari event berlangsung.'], 400);
        }

        $schedule->update(['is_open' => !$schedule->getRawOriginal('is_open')]);

        return response()->json(['is_open' => $schedule->is_open]);
    }
}
