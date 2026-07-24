<?php

namespace App\Http\Controllers\Pemagang;

use App\Http\Controllers\Controller;
use App\Models\InternshipRegistration as IR;
use App\Models\DocumentDownload;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Halaman Dokumen Saya — tampilkan card dokumen sesuai status.
     *
     * Dokumen yang ditampilkan:
     * - Bukti Pendaftaran  → tersedia setelah submit form
     * - Surat Diterima     → tersedia setelah status = accepted
     * - LOA                → tersedia setelah status = completed
     * - SKL                → tersedia setelah status = completed
     * - Sertifikat         → tersedia setelah status = completed
     */
    public function index()
    {
        $user         = auth()->user();
        $registration = IR::where('user_id', $user->id)->latest('id')->first();

        $status = $registration?->internship_status;

        // Tentukan availability tiap dokumen
        $docs = [
            'bukti_pendaftaran' => [
                'label'       => 'Bukti Pendaftaran',
                'description' => 'Diunduh saat form dikirim',
                'icon'        => 'fa-file-check',
                'available'   => $registration && !$registration->is_draft,
                'route'       => null, // generate PDF dari data registrasi
                'date'        => $registration?->created_at,
            ],
            'surat_diterima' => [
                'label'       => 'Surat Diterima',
                'description' => 'Menunggu hasil review',
                'icon'        => 'fa-envelope-open-text',
                'available'   => in_array($status, [
                    IR::STATUS_ACCEPTED, IR::STATUS_ACTIVE, IR::STATUS_COMPLETED
                ]),
                'route'       => null,
                'date'        => null,
            ],
            'loa' => [
                'label'       => 'LOA (Letter of Acceptance)',
                'description' => 'Diberikan setelah magang selesai',
                'icon'        => 'fa-file-signature',
                'available'   => $status === IR::STATUS_COMPLETED,
                'route'       => $status === IR::STATUS_COMPLETED
                    ? route('user.loa.generate')
                    : null,
                'date'        => null,
            ],
            'skl' => [
                'label'       => 'SKL (Surat Keterangan Lulus)',
                'description' => 'Diberikan setelah magang selesai',
                'icon'        => 'fa-certificate',
                'available'   => $status === IR::STATUS_COMPLETED,
                'route'       => $status === IR::STATUS_COMPLETED
                    ? route('skl.download')
                    : null,
                'date'        => null,
            ],
            'sertifikat' => [
                'label'       => 'Sertifikat Magang',
                'description' => 'Diberikan setelah masa magang selesai',
                'icon'        => 'fa-award',
                'available'   => $status === IR::STATUS_COMPLETED,
                'route'       => null,
                'date'        => null,
            ],
        ];

        // Riwayat download (opsional untuk ditampilkan)
        $downloadHistory = DocumentDownload::where('user_id', $user->id)
            ->latest('downloaded_at')
            ->take(5)
            ->get();

        return view('pemagang.documents.index', compact(
            'user',
            'registration',
            'docs',
            'downloadHistory'
        ));
    }
}
