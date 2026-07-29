<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceReference extends Model
{
    protected $fillable = [
        'user_id',
        'image_path',
        'face_token',
        'file_size_kb',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): string
    {
        return route('photos.show', ['path' => base64_encode($this->image_path)]);
    }
}
