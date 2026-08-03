<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Scope: hanya divisi aktif, urut by sort_order lalu name */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** Auto-generate slug dari name jika belum ada */
    protected static function booted(): void
    {
        static::creating(function (Division $div) {
            if (empty($div->slug)) {
                $div->slug = Str::slug($div->name, '_');
            }
        });
    }
}
