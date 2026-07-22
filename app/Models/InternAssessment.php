<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternAssessment extends Model
{
    use HasFactory;

    // Menambahkan properti $fillable untuk semua kolom yang bisa diisi
    protected $fillable = [
        'intern_id',
        'fullname',
        'nim_or_nis',
        'study_program',
        'div',
        'aspek_penilaian',
        'rata_rata',
        'company_name',
        'company_address',
        'company_logo_path',
        'signature_name',
        'signature_position',
        'signature_image_path',
    ];

    protected $casts = [
        'aspek_penilaian' => 'array',
        'rata_rata' => 'float',
    ];

    public function intern()
    {
        return $this->belongsTo(InternshipRegistration::class, 'intern_id');
    }

}
