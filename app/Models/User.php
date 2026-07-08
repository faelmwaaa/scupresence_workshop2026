<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'google_id',
        'name',
        'email',
        'password',
        'profile_picture',
        'nim',
        'phone',
        'fakultas',
        'role',
        'account_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Organizations this user belongs to (via pivot).
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('jabatan', 'membership_status', 'is_pengurus')
            ->withTimestamps();
    }

    /**
     * Active organizations only.
     */
    public function activeOrganizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('jabatan', 'membership_status', 'is_pengurus')
            ->wherePivot('membership_status', 'active')
            ->withTimestamps();
    }

    /**
     * Schedules created by this user.
     */
    public function createdSchedules()
    {
        return $this->hasMany(Schedule::class, 'created_by');
    }

    /**
     * Presences submitted by this user.
     */
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPengurus(): bool
    {
        return $this->role === 'pengurus';
    }

    public function isPelatih(): bool
    {
        return $this->role === 'pelatih';
    }

    public function isAnggota(): bool
    {
        return $this->role === 'anggota';
    }

    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }
}
