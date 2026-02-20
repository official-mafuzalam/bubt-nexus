<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user details associated with this program.
     */
    public function userDetails(): HasMany
    {
        return $this->hasMany(UserDetail::class);
    }

    /**
     * Get the students in this program.
     */
    public function students(): HasMany
    {
        return $this->hasMany(UserDetail::class)->whereNotNull('student_id');
    }
    /**
     * Get the class routines for this program.
     */
    public function classRoutines(): HasMany
    {
        return $this->hasMany(ClassRoutine::class);
    }

    /**
     * Scope a query to only include active programs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}