<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'file_url',
        'storage_path',
        'file_type',
        'file_size',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
