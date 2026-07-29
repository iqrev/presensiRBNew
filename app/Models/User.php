<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'nik',
        'jabatan',
        'department',
        'phone',
        'status',
        'biometric_consent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'biometric_consent_at'   => 'datetime',
            'password'               => 'hashed',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    // Relationships
    public function faceReferences()
    {
        return $this->hasMany(FaceReference::class);
    }

    public function activeFaceReferences()
    {
        return $this->hasMany(FaceReference::class)->where('is_active', true);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Helpers
    public function hasGivenBiometricConsent(): bool
    {
        return $this->biometric_consent_at !== null;
    }

    public function todayCheckIn()
    {
        return $this->attendances()
            ->where('type', 'check_in')
            ->whereDate('attendance_time', today())
            ->first();
    }

    public function todayCheckOut()
    {
        return $this->attendances()
            ->where('type', 'check_out')
            ->whereDate('attendance_time', today())
            ->first();
    }
}
