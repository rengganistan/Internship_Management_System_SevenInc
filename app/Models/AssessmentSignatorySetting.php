<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentSignatorySetting extends Model
{
    protected $table = 'assessment_signatory_settings';

    protected $fillable = [
        'brand',
        'company_name',
        'company_address',
        'signature_name',
        'signature_position',
        'signature_image_path',
        'company_logo_path',
    ];

    /**
     * Ambil setting berdasarkan brand, atau buat instance kosong kalau belum ada.
     */
    public static function forBrand(string $brand): self
    {
        return static::where('brand', $brand)->first() ?? new self(['brand' => $brand]);
    }
}
