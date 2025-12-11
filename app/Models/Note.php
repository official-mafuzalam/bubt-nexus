<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_name',  // Added: Missing in your model
        'course_code',  // Added: Missing in your model
        'title',
        'file_url',
        'storage_path',
        'file_type',
        'file_size',
    ];

    // Optional: Cast file_size to integer for better type handling
    protected $casts = [
        'file_size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}