<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'level', 'parent_id'];

    // Allows a unit (e.g., BEM FIKOM) to find its category (BEM Fakultas)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    // Allows a category (e.g., ORMAWA) to find all its units
    public function children(): HasMany
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }
}