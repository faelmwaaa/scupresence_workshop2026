<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'type'];

    /**
     * Parent organization (for hierarchical structure).
     */
    public function parent()
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    /**
     * Child organizations.
     */
    public function children()
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }

    /**
     * Members of this organization (via pivot).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('jabatan', 'membership_status')
            ->withTimestamps();
    }

    /**
     * Active members only.
     */
    public function activeUsers()
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('jabatan', 'membership_status')
            ->wherePivot('membership_status', 'active')
            ->withTimestamps();
    }

    /**
     * Pending member requests.
     */
    public function pendingUsers()
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('jabatan', 'membership_status')
            ->wherePivot('membership_status', 'pending')
            ->withTimestamps();
    }

    /**
     * Schedules belonging to this organization.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Upcoming schedules.
     */
    public function upcomingSchedules()
    {
        return $this->hasMany(Schedule::class)
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->orderBy('start_time');
    }

    /**
     * Past schedules.
     */
    public function pastSchedules()
    {
        return $this->hasMany(Schedule::class)
            ->where('event_date', '<', now()->toDateString())
            ->orderByDesc('event_date');
    }
}
