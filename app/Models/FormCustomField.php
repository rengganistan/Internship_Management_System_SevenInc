<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FormCustomField extends Model
{
    use SoftDeletes;

    protected $table    = 'form_custom_fields';
    protected $fillable = [
        'form_key', 'label', 'field_key', 'type',
        'placeholder', 'section', 'options',
        'is_required', 'is_active', 'sort_order',
    ];
    protected $casts = [
        'options'     => 'array',
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    // Tipe input yang didukung
    public static array $supportedTypes = [
        'text'     => 'Text',
        'textarea' => 'Textarea',
        'number'   => 'Number',
        'date'     => 'Date',
        'email'    => 'Email',
        'phone'    => 'Phone',
        'select'   => 'Select (Dropdown)',
        'radio'    => 'Radio (Pilihan Tunggal)',
        'checkbox' => 'Checkbox (Pilihan Ganda)',
    ];

    // Tipe yang membutuhkan opsi pilihan
    public static function needsOptions(string $type): bool
    {
        return in_array($type, ['select', 'radio', 'checkbox']);
    }

    // Generate field_key dari label secara otomatis
    public static function generateKey(string $label): string
    {
        return Str::slug($label, '_');
    }

    // Scope: aktif saja, urut sort_order
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // Scope: untuk form tertentu
    public function scopeForForm($query, string $formKey = 'internship_registration')
    {
        return $query->where('form_key', $formKey);
    }
}
