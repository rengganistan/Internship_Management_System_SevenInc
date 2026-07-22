<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class InternshipRegistration extends Model
{
    use HasFactory;

    protected $table = 'internship_registrations';

    // ===== Workflow status (dipakai filter/menu) =====
    public const STATUS_WAITING     = 'waiting';
    public const STATUS_ACTIVE      = 'active';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_EXITED      = 'exited';
    public const STATUS_PENDING     = 'pending';
    public const STATUS_ACCEPTED    = 'accepted';  
    public const STATUS_REJECTED    = 'rejected';   

    protected $fillable = [
        'user_id',
        'fullname', 'born_date', 'student_id', 'email', 'gender', 'phone_number',
        'institution_name', 'study_program', 'faculty', 'current_city',
        'internship_reason', 'internship_type', 'internship_arrangement',
        'current_status',
        'english_book_ability', 'supervisor_contact',
        'internship_interest', 'internship_interest_other',
        'design_software', 'video_software', 'programming_languages',
        'digital_marketing_type', 'digital_marketing_type_other',
        'laptop_equipment', 'owned_tools', 'owned_tools_other',
        'start_date', 'end_date',
        'internship_info_sources', 'internship_info_other',
        'cv_ktp_portofolio_pdf', 'portofolio_visual',
        'current_activities', 'boarding_info', 'family_status',
        'parent_wa_contact', 'social_media_instagram',
        'internship_status',
    ];

    /**
     * Catatan casts:
     * - JANGAN cast born_date/start_date/end_date sebagai 'date' karena sudah ditangani
     *   oleh Accessor/Mutator di bawah. Casting ganda akan menyebabkan konflik di Laravel 9+.
     * - Simpan created_at/updated_at saja sebagai datetime.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ============================================================
     |  Normalisasi tanggal → string 'Y-m-d' (untuk penyimpanan)
     * ============================================================ */

    /**
     * Coba ubah berbagai format tanggal (termasuk "08 Agustus 2022")
     * menjadi string 'YYYY-MM-DD'. Jika tidak bisa, kembalikan string asli.
     */
    private function toYmdString(mixed $value): ?string
    {
        if ($value === null) return null;
        $s = trim((string) $value);
        if ($s === '') return null;

        // 1) Format yang sudah benar
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return $s;
        }

        // 2) DD-MM-YYYY atau DD/MM/YYYY
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $s, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }

        // 3) "08 Agustus 2022" / "8 agustus 2022"
        $bulan = [
            'januari'=>1,'februari'=>2,'maret'=>3,'april'=>4,'mei'=>5,'juni'=>6,
            'juli'=>7,'agustus'=>8,'september'=>9,'oktober'=>10,'november'=>11,'desember'=>12
        ];
        if (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/u', $s, $m)) {
            $mon = strtolower($m[2]);
            if (isset($bulan[$mon])) {
                return sprintf('%04d-%02d-%02d', (int)$m[3], $bulan[$mon], (int)$m[1]);
            }
        }

        // 4) ISO/Datetime → pakai Carbon sebagai fallback
        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            // Tidak bisa diparse → biarkan apa adanya
            return $s;
        }
    }

    /* ============================================================
     |  Accessor/Mutator: SELALU kembalikan STRING, bukan Carbon
     * ============================================================ */

    /** born_date: simpan Y-m-d bila bisa, get selalu string apa adanya */
    protected function bornDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => is_string($value) ? $value : (string) $value,
            set: fn ($value) => $this->toYmdString($value)
        );
    }

    /** start_date: simpan Y-m-d bila bisa, get selalu string apa adanya */
    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => is_string($value) ? $value : (string) $value,
            set: fn ($value) => $this->toYmdString($value)
        );
    }

    /** end_date: simpan Y-m-d bila bisa, get selalu string apa adanya */
    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => is_string($value) ? $value : (string) $value,
            set: fn ($value) => $this->toYmdString($value)
        );
    }

    /* ============================================================
     |  Scopes (query helper)
     * ============================================================ */
    public function scopeStatus($query, string $status)
    {
        return $query->where('internship_status', $status);
    }

    public function scopeIsNew($query)     { return $query->status(self::STATUS_WAITING); }
    public function scopeActive($query)    { return $query->status(self::STATUS_ACTIVE); }
    public function scopeCompleted($query) { return $query->status(self::STATUS_COMPLETED); }
    public function scopeExited($query)    { return $query->status(self::STATUS_EXITED); }
    public function scopePending($query)   { return $query->status(self::STATUS_PENDING); }
    public function scopeAccepted($query)  { return $query->status(self::STATUS_ACCEPTED); }
    public function scopeRejected($query)  { return $query->status(self::STATUS_REJECTED); }

    /* ============================================================
     |  Helpers untuk UI (label & badge)
     * ============================================================ */
    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_WAITING    => 'Menunggu',
            self::STATUS_ACTIVE     => 'Aktif',
            self::STATUS_COMPLETED  => 'Selesai',
            self::STATUS_EXITED     => 'Keluar',
            self::STATUS_PENDING    => 'Pending',
            self::STATUS_ACCEPTED   => 'Diterima',
            self::STATUS_REJECTED   => 'Ditolak',
        ][$this->internship_status] ?? ucfirst((string) $this->internship_status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            self::STATUS_WAITING    => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-200',
            self::STATUS_ACTIVE     => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
            self::STATUS_COMPLETED  => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200',
            self::STATUS_EXITED     => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200',
            self::STATUS_PENDING    => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
            self::STATUS_ACCEPTED   => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
            self::STATUS_REJECTED   => 'bg-gray-200 text-gray-700 dark:bg-gray-800/60 dark:text-gray-200',
        ][$this->internship_status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }

    /* ============================================================
     |  Optional: list helper untuk kolom CSV
     * ============================================================ */
    public function getOwnedToolsListAttribute(): array
    {
        $raw = (string) ($this->attributes['owned_tools'] ?? '');
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    public function getInfoSourcesListAttribute(): array
    {
        $raw = (string) ($this->attributes['internship_info_sources'] ?? '');
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /* ============================================================
     |  Ringkasan cepat untuk dashboard
     * ============================================================ */
    public static function countsByStatus(): array
    {
        return [
            'waiting'   => static::status(self::STATUS_WAITING)->count(),
            'active'    => static::status(self::STATUS_ACTIVE)->count(),
            'completed' => static::status(self::STATUS_COMPLETED)->count(),
            'exited'    => static::status(self::STATUS_EXITED)->count(),
            'pending'   => static::status(self::STATUS_PENDING)->count(),
            'accepted'  => static::status(self::STATUS_ACCEPTED)->count(),
            'rejected'  => static::status(self::STATUS_REJECTED)->count(),
        ];
    }

    // app/Models/InternshipRegistration.php

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

}
