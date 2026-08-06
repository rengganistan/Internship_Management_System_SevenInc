<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webinar extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_end_date',
        'zoom_link',
        'platform',
        'certificate_background',
        'certificate_logo1',
        'certificate_logo2',
        'certificate_signature1',
        'certificate_signature2',
        'certificate_signatory1_name',
        'certificate_signatory1_role',
        'certificate_signatory2_name',
        'certificate_signatory2_role',
        'certificate_company',
        'certificate_city',
        'certificate_brand',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'event_date'     => 'datetime',
        'event_end_date' => 'datetime',
        'is_active'      => 'boolean',
    ];

    /** Admin yang membuat webinar */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Semua attendance/bukti kehadiran webinar ini */
    public function attendances(): HasMany
    {
        return $this->hasMany(WebinarAttendance::class);
    }

    /** Attendance yang sudah di-approve */
    public function approvedAttendances(): HasMany
    {
        return $this->hasMany(WebinarAttendance::class)->where('status', 'approved');
    }

    /** Attendance yang masih pending */
    public function pendingAttendances(): HasMany
    {
        return $this->hasMany(WebinarAttendance::class)->where('status', 'pending');
    }

    /** Cek apakah user tertentu sudah submit bukti */
    public function hasAttendance(int $userId): bool
    {
        return $this->attendances()->where('user_id', $userId)->exists();
    }

    /** Ambil attendance milik user tertentu */
    public function attendanceOf(int $userId): ?WebinarAttendance
    {
        return $this->attendances()->where('user_id', $userId)->first();
    }
}
