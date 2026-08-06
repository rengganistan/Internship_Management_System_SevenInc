<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentDownload extends Model
{
    use HasFactory;

    public const TYPE_SKL              = 'SKL';
    public const TYPE_LOA              = 'LOA';
    public const TYPE_SERTIFIKAT       = 'SERTIFIKAT';
    public const TYPE_PENILAIAN        = 'PENILAIAN';
    public const TYPE_SERTIFIKAT_WEBINAR = 'SERTIFIKAT_WEBINAR';

    protected $fillable = [
        'user_id',
        'internship_registration_id',
        'doc_type',
        'file_path',
        'file_url',
        'downloaded_at',
        'ip_address',
        'user_agent',
        'status',
        'error_message',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function internshipRegistration()
    {
        return $this->belongsTo(\App\Models\InternshipRegistration::class);
    }

    // Scopes enak buat filter
    public function scopeSkl($q) { return $q->where('doc_type', self::TYPE_SKL); }
    public function scopeLoa($q) { return $q->where('doc_type', self::TYPE_LOA); }
}
