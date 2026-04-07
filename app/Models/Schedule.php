<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'created_by',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'jenis_kegiatan',
        'is_active',
    ];

    // Automatically convert the JSON array back into a usable PHP array
    protected $casts = [
        'jenis_kegiatan' => 'array',
        'is_active' => 'boolean',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    // The unit holding the event
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // The BPH who made it
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // The attendances linked to this schedule (We will make the Presence model next!)
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }
}