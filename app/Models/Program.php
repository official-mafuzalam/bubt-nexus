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
}