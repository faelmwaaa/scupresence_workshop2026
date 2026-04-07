<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'user_id',
        'status_kehadiran',
        'alasan_absen',
        'foto_path',
        'latitude',
        'longitude',
        'kegiatan_dipilih',
        'status_validasi',
    ];

    // The event they attended
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // The student who submitted it
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}