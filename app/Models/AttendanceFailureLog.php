<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceFailureLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'failure_reason',
        'face_match_score',
        'distance_meter',
        'photo_path',
        'latitude',
        'longitude',
        'attempted_at',
    ];

    protected $casts = [
        'face_match_score' => 'float',
        'distance_meter'   => 'float',
        'latitude'         => 'float',
        'longitude'        => 'float',
        'attempted_at'     => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFailureReasonLabelAttribute(): string
    {
        return match($this->failure_reason) {
            'face_mismatch'      => 'Wajah Tidak Cocok',
            'out_of_radius'      => 'Di Luar Area Kantor',
            'both'               => 'Wajah & Lokasi Tidak Valid',
            'no_face_detected'   => 'Wajah Tidak Terdeteksi',
            'api_error'          => 'Error API Face Recognition',
            default              => $this->failure_reason,
        };
    }
}
