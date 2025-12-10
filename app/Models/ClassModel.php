<?php

// app/Models/ClassModel.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassModel extends Model
{
    protected $table = 'classes';
    protected $fillable = [
        'name',
        'code',
        'description',
        'faculty_id',
        'subject_code',
        'program_id',
        'intake',
        'section',
        'enrollment_code',
        'is_active'
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'class_enrollments', 'class_id', 'student_id')
            ->withPivot('status')
            ->wherePivot('status', 'active');
    }

    // Generate unique enrollment code
    public static function generateEnrollmentCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('enrollment_code', $code)->exists());

        return $code;
    }
}