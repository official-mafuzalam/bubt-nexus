<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'class_id',
        'title',
        'description',
        'instructions',
        'total_marks',
        'deadline',
        'attachments',
        'status'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'attachments' => 'array',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'assignment_id');
    }

    public function isLateSubmission($submissionDate): bool
    {
        return $submissionDate > $this->deadline;
    }

    public function submissionCount(): array
    {
        return [
            'total' => $this->submissions()->count(),
            'submitted' => $this->submissions()->where('status', 'submitted')->count(),
            'graded' => $this->submissions()->where('status', 'graded')->count(),
            'pending' => $this->submissions()->where('status', 'pending')->count(),
        ];
    }
}