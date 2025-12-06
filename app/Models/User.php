<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected $appends = [
        'user_type',
        'is_student',
        'is_faculty',
        'program'  // Added to access program through user detail
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Get the user details associated with the user.
     */
    public function userDetail(): HasOne
    {
        return $this->hasOne(UserDetail::class);
    }

    /**
     * Check if user is a student
     */
    public function getIsStudentAttribute(): bool
    {
        return $this->userDetail && $this->userDetail->student_id !== null;
    }

    /**
     * Check if user is faculty
     */
    public function getIsFacultyAttribute(): bool
    {
        // Fixed: Changed from faculty_id to faculty_code
        return $this->userDetail && $this->userDetail->faculty_code !== null;
    }

    /**
     * Get user type
     */
    public function getUserTypeAttribute(): string
    {
        if (!$this->userDetail) {
            return 'Unknown';
        }

        if ($this->userDetail->student_id) {
            return 'Student';
        } elseif ($this->userDetail->faculty_code) { // Fixed: Changed from faculty_id to faculty_code
            return 'Faculty';
        }

        return 'Unknown';
    }

    /**
     * Get program through user details
     */
    public function getProgramAttribute()
    {
        return $this->userDetail?->program;
    }

    /**
     * Get program name (convenience method)
     */
    public function getProgramNameAttribute(): ?string
    {
        return $this->userDetail?->program?->name;
    }

    /**
     * Get program code (convenience method)
     */
    public function getProgramCodeAttribute(): ?string
    {
        return $this->userDetail?->program?->code;
    }

    /**
     * Scope for students
     */
    public function scopeStudents($query)
    {
        return $query->whereHas('userDetail', function ($q) {
            $q->whereNotNull('student_id');
        });
    }

    /**
     * Scope for faculty
     */
    public function scopeFaculty($query)
    {
        return $query->whereHas('userDetail', function ($q) {
            $q->whereNotNull('faculty_code'); // Fixed: Changed from faculty_id to faculty_code
        });
    }

    /**
     * Scope for users in a specific program
     */
    public function scopeInProgram($query, $programId)
    {
        return $query->whereHas('userDetail', function ($q) use ($programId) {
            $q->where('program_id', $programId);
        });
    }

    /**
     * Check if user is currently online (within last 2 minutes)
     */
    public function isOnline(): bool
    {
        return Cache::has('user-is-online-' . $this->id);
    }

    /**
     * Get online status with different states
     */
    public function getOnlineStatus(): string
    {
        if ($this->isOnline()) {
            return 'online';
        } elseif ($this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5))) {
            return 'recently';
        } else {
            return 'offline';
        }
    }

    /**
     * Get formatted last seen time
     */
    public function getLastSeenText(): string
    {
        if (!$this->last_seen_at) {
            return 'Never';
        }

        if ($this->isOnline()) {
            return 'Online now';
        }

        $diffInMinutes = $this->last_seen_at->diffInMinutes(now());

        if ($diffInMinutes < 1) {
            return 'Just now';
        } elseif ($diffInMinutes < 60) {
            return $diffInMinutes . ' min ago';
        } elseif ($diffInMinutes < 1440) {
            $hours = floor($diffInMinutes / 60);
            return $hours . ' ' . ($hours === 1 ? 'hour' : 'hours') . ' ago';
        } else {
            return $this->last_seen_at->format('M j, Y g:i A');
        }
    }

    /**
     * Get detailed last seen information
     */
    public function getLastSeenDetailed(): array
    {
        if (!$this->last_seen_at) {
            return [
                'text' => 'Never been online',
                'icon' => 'never',
                'color' => 'gray'
            ];
        }

        $diffInMinutes = $this->last_seen_at->diffInMinutes(now());

        if ($this->isOnline()) {
            return [
                'text' => 'Online now',
                'icon' => 'online',
                'color' => 'green'
            ];
        } elseif ($diffInMinutes < 5) {
            return [
                'text' => 'Just now',
                'icon' => 'recent',
                'color' => 'green'
            ];
        } elseif ($diffInMinutes < 60) {
            return [
                'text' => number_format($diffInMinutes) . ' minutes ago',
                'icon' => 'recent',
                'color' => 'amber'
            ];
        } elseif ($diffInMinutes < 1440) {
            $hours = floor($diffInMinutes / 60);
            return [
                'text' => $hours . ' ' . ($hours === 1 ? 'hour' : 'hours') . ' ago',
                'icon' => 'away',
                'color' => 'amber'
            ];
        } else {
            return [
                'text' => $this->last_seen_at->format('M j, Y g:i A'),
                'icon' => 'offline',
                'color' => 'gray'
            ];
        }
    }

    public function block(): void
    {
        $this->status = 'blocked';
        $this->save();
    }

    public function unblock(): void
    {
        $this->status = 'active';
        $this->save();
    }

    /*
     * Ride Sharing Relationships and Scopes
     */
    public function ridesAsDriver()
    {
        return $this->hasMany(Ride::class, 'driver_id');
    }

    public function rideRequests()
    {
        return $this->hasMany(RideRequest::class, 'passenger_id');
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function locations()
    {
        return $this->hasMany(RideLocation::class);
    }

    public function scopeAvailableDrivers($query, $excludeUserId = null)
    {
        $query = $query->where('status', 'active');

        if ($excludeUserId) {
            $query = $query->where('id', '!=', $excludeUserId);
        }

        return $query;
    }
}