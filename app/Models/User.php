<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\DailyReport;
use App\Models\LeaveRequest;
use App\Models\PendingTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_online',
        'phone_number',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function internshipRegistration(): HasOne
    {
        return $this->hasOne(\App\Models\InternshipRegistration::class, 'user_id');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(\App\Models\Download::class, 'user_id');
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'user_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    public function pendingTasks(): HasMany
    {
        return $this->hasMany(PendingTask::class, 'user_id');
    }

    // Event to listen to when a user's status changes
    protected static function booted()
    {
        static::updated(function ($user) {
            // internship_status bukan kolom di tabel users,
            // status magang ada di InternshipRegistration.
            // Cukup cek role saja; member card dibuat dari InternController saat status berubah.
            if ($user->role === 'pemagang') {
                $user->createMemberCard();
            }
        });
    }

    public function createMemberCard()
    {
        $intern = $this->internshipRegistration;

        if (!$intern || !$intern->start_date) return;

        $angkatanYear = \Carbon\Carbon::parse($intern->start_date)->format('Y');
        $angkatan     = substr($angkatanYear, -2);
        $idPadded     = str_pad($this->id, 3, '0', STR_PAD_LEFT);
        $brand        = $intern->brand ?? 'magangjogja.com';
        $prefix       = $this->getBrandPrefix($brand);
        $code         = "{$prefix}{$angkatan}{$idPadded}";

        // Cari berdasarkan code dulu (idempoten: jangan duplikat)
        $existing = \App\Models\Download::where('code', $code)->first();

        if ($existing) {
            // Sudah ada, update data terbaru saja
            $existing->update([
                'name'     => $this->name,
                'angkatan' => $angkatanYear,
                'instansi' => $intern->institution_name,
                'brand'    => $brand,
                'user_id'  => $this->id,
            ]);
            return;
        }

        // Cari record lama tanpa code tapi nama cocok
        $orphan = \App\Models\Download::whereNull('code')
            ->where('name', $this->name)
            ->first();

        if ($orphan) {
            $orphan->update([
                'code'     => $code,
                'angkatan' => $angkatanYear,
                'instansi' => $intern->institution_name,
                'brand'    => $brand,
                'user_id'  => $this->id,
            ]);
            return;
        }

        // Tidak ada record sama sekali → buat baru otomatis
        \App\Models\Download::create([
            'code'           => $code,
            'name'           => $this->name,
            'user_id'        => $this->id,
            'angkatan'       => $angkatanYear,
            'instansi'       => $intern->institution_name,
            'brand'          => $brand,
            'model_url'      => null, // admin bisa isi nanti lewat halaman edit membercard
            'has_downloaded' => false,
        ]);
    }




    public function getBrandPrefix(string $brand): string
    {
        return match (strtolower($brand)) {
            'magangjogja.com' => 'MJ',
            'areakerja.com'   => 'AK',
            'republikweb.net'   => 'RW',
            'titipsini.com'   => 'TS',
            'ambilpaket.com'   => 'AP',
            'bikinkepo.com'   => 'BK',
            'bimbelcerdas.com'   => 'BC',
            'latihankerja.com'   => 'LK',
            'lowkerjateng.com'   => 'LJT',
            'lowkerjogja.com'   => 'LJG',
            'pijatjogja.com'   => 'PJ',
            'sayabantu.com'   => 'SB',
            'titikvisual.com'   => 'TV',
            'tuantanah.com'   => 'TN',
            'tukanglas.org'   => 'TL',
            'adakamarid'   => 'AKI',
            'seven inc'   => 'SI',
            default           => 'XX', // fallback default
        };
    }

}
