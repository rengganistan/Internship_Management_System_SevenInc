<?php

namespace App\Http\Controllers\Pemagang;

use App\Http\Controllers\Controller;
use App\Models\InternshipRegistration as IR;
use App\Models\DocumentDownload;
use App\Models\InternAssessment;
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
            'loa' => [
                'label'       => 'LOA (Letter of Acceptance)',
                'description' => 'Surat penerimaan magang dari perusahaan',
                'icon'        => 'fa-file-signature',
                'available'   => in_array($status, [
                    IR::STATUS_ACCEPTED, IR::STATUS_ACTIVE, IR::STATUS_COMPLETED
                ]),
                'route'       => null, // pakai form POST di view karena butuh intern_id
                'intern_id'   => $registration?->id,
                'date'        => null,
            ],
            'skl' => [
                'label'       => 'SKL (Surat Keterangan Lulus)',
                'description' => 'Diberikan setelah magang selesai',
                'icon'        => 'fa-certificate',
                'available'   => $status === IR::STATUS_COMPLETED,
                'route'       => $status === IR::STATUS_COMPLETED
                    ? route('user.skl.download')
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
            'surat_penilaian' => [
                'label'       => 'Surat Penilaian',
                'description' => 'Penilaian kinerja selama magang',
                'icon'        => 'fa-star-half-alt',
                'available'   => $status === IR::STATUS_COMPLETED,
                'route'       => $status === IR::STATUS_COMPLETED
                    ? route('pemagang.documents.surat_penilaian')
                    : null,
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

    /**
     * Download Surat Penilaian milik pemagang yang login.
     * Cari assessment berdasarkan intern_id (FK ke internship_registrations).
     */
    public function downloadSuratPenilaian()
    {
        $user         = auth()->user();
        $registration = IR::where('user_id', $user->id)->latest('id')->first();

        if (!$registration || $registration->internship_status !== IR::STATUS_COMPLETED) {
            abort(403, 'Surat penilaian hanya tersedia setelah magang selesai.');
        }

        // Cari assessment berdasarkan intern_id
        $assessment = InternAssessment::where('intern_id', $registration->id)->latest()->first();

        if (!$assessment) {
            return back()->with('error', 'Surat penilaian belum tersedia. Hubungi admin.');
        }

        // Delegate ke InternAssessmentController
        return app(\App\Http\Controllers\InternAssessmentController::class)
            ->downloadPDF($assessment->id);
    }
}
