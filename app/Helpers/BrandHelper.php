<?php

namespace App\Helpers;

class BrandHelper
{
    /**
     * Daftar brand resmi — key adalah nilai yang disimpan di DB,
     * value adalah nama tampilan untuk admin.
     */
    public static function list(): array
    {
        return [
            'magangjogja.com'  => 'Magangjogja.com',
            'areakerja.com'    => 'Areakerja.com',
            'republikweb.net'  => 'Republikweb.net',
            'titipsini.com'    => 'Titipsini.com',
            'ambilpaket.com'   => 'Ambilpaket.com',
            'bikinkepo.com'    => 'Bikinkepo.com',
            'bimbelcerdas.com' => 'Bimbelcerdas.com',
            'latihankerja.com' => 'Latihankerja.com',
            'lowkerjateng.com' => 'Lowkerjateng.com',
            'lowkerjogja.com'  => 'Lowkerjogja.com',
            'pijatjogja.com'   => 'Pijatjogja.com',
            'sayabantu.com'    => 'SayaBantu.com',
            'titikvisual.com'  => 'Titikvisual.com',
            'tuantanah.com'    => 'Tuantanah.com',
            'tukanglas.org'    => 'Tukanglas.org',
            'adakamarid'       => 'Adakamar.id',
            'seven inc'        => 'Seven Inc',
        ];
    }

    /** Ambil nama tampilan dari key brand */
    public static function label(string $brand): string
    {
        return static::list()[strtolower($brand)] ?? ucfirst($brand);
    }

    /** Certificate template berdasarkan brand */
    public static function certificateTemplate(string $brand): ?string
    {
        return match (strtolower($brand)) {
            'magangjogja.com'  => 'certmagangjogjacom',
            'areakerja.com'    => 'certareakerjacom',
            'titipsini.com'    => 'certtitipsinicom',
            default            => 'certmagangjogjacom', // fallback default
        };
    }
}
