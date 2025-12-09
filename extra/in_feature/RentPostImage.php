<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentPostImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'rent_post_id',
        'image_path',
    ];

    // Not used yet
    public function rentPost()
    {
        return $this->belongsTo(RentPost::class);
    }
}
