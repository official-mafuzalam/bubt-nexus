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
        'semester',
        'intake',
        'program',
        'student_id',
        'cgpa',
        'department',
        'faculty_id',
        'designation',
        'office_room',
        'office_hours',
        'phone',
        'address',
        'date_of_birth',
        'emergency_contact',
        'profile_picture',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
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
        return $query->whereNotNull('faculty_id');
    }

    /**
     * Get full user type
     */
    public function getUserTypeAttribute(): string
    {
        if ($this->student_id) {
            return 'Student';
        } elseif ($this->faculty_id) {
            return 'Faculty';
        }

        return 'Unknown';
    }
}