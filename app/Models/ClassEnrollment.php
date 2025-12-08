<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassEnrollment extends Model
{
    protected $fillable = ['class_id', 'student_id', 'status'];
    
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
    
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}