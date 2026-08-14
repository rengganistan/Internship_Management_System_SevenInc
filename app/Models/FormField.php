<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $table = 'form_fields';

    protected $fillable = [
        'field_key',
        'field_type',
        'label',
        'placeholder',
        'options',
        'is_required',
        'is_active',
        'is_system',
        'group_name',
        'sort_order',
        'column_span',
        'helper_text',
    ];

    protected $casts = [
        'options'     => 'array',
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
        'is_system'   => 'boolean',
        'sort_order'  => 'integer',
        'column_span' => 'integer',
    ];

    /** Scope: hanya field aktif, diurutkan */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** Scope: berdasarkan grup */
    public function scopeInGroup($query, ?string $group)
    {
        return $query->where('group_name', $group);
    }

    /**
     * Semua tipe field yang didukung.
     */
    public static function supportedTypes(): array
    {
        return [
            'text'     => 'Text',
            'email'    => 'Email',
            'tel'      => 'Telepon',
            'date'     => 'Tanggal',
            'textarea' => 'Teks Panjang (Textarea)',
            'select'   => 'Dropdown (Select)',
            'radio'    => 'Pilihan Tunggal (Radio)',
            'checkbox' => 'Pilihan Banyak (Checkbox)',
            'file'     => 'Upload File',
        ];
    }

    /**
     * Daftar grup yang tersedia.
     */
    public static function groups(): array
    {
        return [
            null                   => 'Form Utama',
            'informasi_tambahan'   => 'Informasi Tambahan',
        ];
    }
}
