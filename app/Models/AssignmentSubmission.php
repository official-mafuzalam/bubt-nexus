<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $fillable = ['assignment_id', 'student_id', 'content', 'attachments', 'marks_obtained', 'feedback', 'submitted_at', 'status'];
    
    protected $casts = [
        'attachments' => 'array',
        'submitted_at' => 'datetime',
    ];
    
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }
    
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}