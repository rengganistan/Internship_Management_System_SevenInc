<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FormSetting extends Model
{
    protected $table    = 'form_settings';
    protected $fillable = ['form_key', 'fields'];
    protected $casts    = ['fields' => 'array'];

    // ── Default field definitions untuk form pendaftaran magang ──────────────
    public static function defaultInternshipFields(): array
    {
        return [
            // ── Data Pribadi ────────────────────────────────────────────────
            ['key' => 'fullname',            'label' => 'Nama Lengkap',                      'section' => 'Data Pribadi',       'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'student_id',          'label' => 'NIM / NPM',                         'section' => 'Data Pribadi',       'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'born_date',           'label' => 'Tanggal Lahir',                      'section' => 'Data Pribadi',       'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'gender',              'label' => 'Jenis Kelamin',                      'section' => 'Data Pribadi',       'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'email',               'label' => 'Email',                              'section' => 'Data Pribadi',       'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'phone_number',        'label' => 'No. HP (WhatsApp)',                  'section' => 'Data Pribadi',       'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'current_city',        'label' => 'Kota Domisili',                      'section' => 'Data Pribadi',       'is_active' => true,  'is_required' => true,  'is_lockable' => false],

            // ── Data Akademik ───────────────────────────────────────────────
            ['key' => 'institution_name',    'label' => 'Universitas / Sekolah',              'section' => 'Data Akademik',      'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'study_program',       'label' => 'Program Studi',                      'section' => 'Data Akademik',      'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'faculty',             'label' => 'Fakultas',                           'section' => 'Data Akademik',      'is_active' => true,  'is_required' => true,  'is_lockable' => false],

            // ── Informasi Magang ────────────────────────────────────────────
            ['key' => 'internship_interest', 'label' => 'Divisi Diminati',                   'section' => 'Informasi Magang',   'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'internship_type',     'label' => 'Jenis Magang',                       'section' => 'Informasi Magang',   'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'internship_arrangement','label' => 'Sistem Magang',                    'section' => 'Informasi Magang',   'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'internship_reason',   'label' => 'Alasan Ingin Magang',               'section' => 'Informasi Magang',   'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'start_date',          'label' => 'Tanggal Mulai',                      'section' => 'Informasi Magang',   'is_active' => true,  'is_required' => false, 'is_lockable' => false],
            ['key' => 'end_date',            'label' => 'Tanggal Selesai',                    'section' => 'Informasi Magang',   'is_active' => true,  'is_required' => false, 'is_lockable' => false],
            ['key' => 'current_status',      'label' => 'Status Saat Ini',                    'section' => 'Informasi Magang',   'is_active' => true,  'is_required' => true,  'is_lockable' => false],
            ['key' => 'english_book_ability','label' => 'Kemampuan Bahasa Inggris',           'section' => 'Informasi Magang',   'is_active' => true,  'is_required' => true,  'is_lockable' => false],

            // ── Keahlian ────────────────────────────────────────────────────
            ['key' => 'design_software',     'label' => 'Software Desain',                    'section' => 'Keahlian',           'is_active' => true,  'is_required' => false, 'is_lockable' => false],
            ['key' => 'programming_languages','label' => 'Bahasa Pemrograman',                'section' => 'Keahlian',           'is_active' => true,  'is_required' => false, 'is_lockable' => false],
            ['key' => 'video_software',      'label' => 'Materi Digital Marketing',           'section' => 'Keahlian',           'is_active' => true,  'is_required' => false, 'is_lockable' => false],

            // ── Berkas ──────────────────────────────────────────────────────
            ['key' => 'cv_ktp_portofolio_pdf','label' => 'Dokumen Pendukung (PDF)',           'section' => 'Berkas',             'is_active' => true,  'is_required' => false, 'is_lockable' => false],
            ['key' => 'portofolio_visual',   'label' => 'CV / Portfolio',                     'section' => 'Berkas',             'is_active' => true,  'is_required' => false, 'is_lockable' => false],

            // ── Informasi Tambahan ──────────────────────────────────────────
            ['key' => 'boarding_info',       'label' => 'Butuh Informasi Kost?',              'section' => 'Informasi Tambahan', 'is_active' => true,  'is_required' => false, 'is_lockable' => false],
            ['key' => 'parent_wa_contact',   'label' => 'No. WA Wali / Orang Tua',           'section' => 'Informasi Tambahan', 'is_active' => true,  'is_required' => false, 'is_lockable' => false],
            ['key' => 'social_media_instagram','label' => 'Instagram',                        'section' => 'Informasi Tambahan', 'is_active' => true,  'is_required' => false, 'is_lockable' => false],
            ['key' => 'internship_info_sources','label' => 'Tahu Info Magang Dari',           'section' => 'Informasi Tambahan', 'is_active' => true,  'is_required' => false, 'is_lockable' => false],
        ];
    }

    /**
     * Ambil setting form magang — gunakan cache 5 menit.
     * Return: array keyed by field key, value = ['is_active'=>bool,'is_required'=>bool]
     */
    public static function getInternshipFields(): array
    {
        return Cache::remember('form_settings_internship', 300, function () {
            $setting = static::where('form_key', 'internship_registration')->first();
            $fields  = $setting?->fields ?? static::defaultInternshipFields();

            $map = [];
            foreach ($fields as $f) {
                $map[$f['key']] = [
                    'is_active'   => (bool) ($f['is_active'] ?? true),
                    'is_required' => (bool) ($f['is_required'] ?? false),
                    'label'       => $f['label'] ?? $f['key'],
                    'section'     => $f['section'] ?? 'Lainnya',
                ];
            }
            return $map;
        });
    }

    /** Hapus cache saat setting disimpan */
    public static function clearCache(): void
    {
        Cache::forget('form_settings_internship');
    }
}
