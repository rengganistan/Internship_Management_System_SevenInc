<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekomendasiSetting extends Model
{
    protected $table = 'rekomendasi_settings';

    protected $fillable = [
        'company_name',
        'company_address',
        'company_city',
        'company_phone',
        'company_postal_code',
        'leader_name',
        'leader_title',
        'company_brand',
        'body_template',
        'logo_path',
        'stamp_path',
    ];

    /**
     * Default body template dengan placeholder.
     * Placeholder: {nama}, {divisi}, {mulai}, {selesai}, {durasi}, {instansi}, {nim}
     */
    public static function defaultBodyTemplate(): string
    {
        return 'Adalah alumni magang dari {company_brand} yang telah melaksanakan magang selama {durasi} sebagai {divisi} dengan tuntas sejak {mulai} – {selesai} dan mematuhi seluruh peraturan yang berlaku di {company_brand}. Saudara/i {nama} merupakan salah satu pemagang terbaik kami yang memiliki semangat dan etos kerja yang baik.';
    }
}
