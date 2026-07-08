<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'created_by',
        'title',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'description',
        'is_open',
        'is_recurring',
        'repeat_until',
    ];

    protected $casts = [
        'event_date'   => 'date',
        'repeat_until' => 'date',
        'is_recurring' => 'boolean',
        'is_open'      => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    /**
     * Attendance auto-opens on the event day.
     * is_open = false means Pengurus manually closed it.
     * Default is_open = true, so no manual action needed to open.
     */
    public function canAttend(): bool
    {
        return $this->event_date->isToday() && $this->is_open;
    }

    /**
     * Accessor to ensure is_open is only true on the day of the event.
     */
    public function getIsOpenAttribute($value)
    {
        return $this->event_date->isToday() && $value;
    }
}
