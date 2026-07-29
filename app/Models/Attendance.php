<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'attendance_time',
        'latitude',
        'longitude',
        'distance_meter',
        'is_within_radius',
        'face_match_score',
        'is_face_verified',
        'photo_path',
        'photo_size_kb',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'attendance_time'  => 'datetime',
        'latitude'         => 'float',
        'longitude'        => 'float',
        'distance_meter'   => 'float',
        'is_within_radius' => 'boolean',
        'face_match_score' => 'float',
        'is_face_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        return route('photos.show', ['path' => base64_encode($this->photo_path)]);
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }

    public function scopeCheckIn($query)
    {
        return $query->where('type', 'check_in');
    }

    public function scopeCheckOut($query)
    {
        return $query->where('type', 'check_out');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('attendance_time', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('attendance_time', now()->month)
                     ->whereYear('attendance_time', now()->year);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'valid'          => 'Valid',
            'rejected'       => 'Ditolak',
            'manual_request' => 'Perlu Approval',
            default          => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'valid'          => 'success',
            'rejected'       => 'danger',
            'manual_request' => 'warning',
            default          => 'secondary',
        };
    }
}
