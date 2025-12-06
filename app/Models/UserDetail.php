<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_id', // Changed from 'program'
        'semester',
        'intake',
        'section',
        'student_id',
        'cgpa',
        'department',//for faculty
        'faculty_code',
        'designation',
        'phone',
        'profile_picture',
    ];

    protected $casts = [
        'cgpa' => 'decimal:2',
    ];

    /**
     * Get the user that owns the details.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the program associated with the user detail.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Scope for students
     */
    public function scopeStudents($query)
    {
        return $query->whereNotNull('student_id');
    }

    /**
     * Scope for faculty
     */
    public function scopeFaculty($query)
    {
        return $query->whereNotNull('faculty_code');
    }

    /**
     * Get full user type
     */
    public function getUserTypeAttribute(): string
    {
        if ($this->student_id) {
            return 'Student';
        } elseif ($this->faculty_code) {
            return 'Faculty';
        }

        return 'Unknown';
    }

    /**
     * Get program name (accessor for backward compatibility)
     */
    public function getProgramAttribute(): ?string
    {
        return $this->programRelation?->name;
    }
}