<?php

namespace App\Http\Controllers\Pemagang;

use App\Http\Controllers\Controller;
use App\Models\InternshipRegistration as IR;
use App\Models\DocumentDownload;
use App\Models\InternAssessment;
use App\Models\InternExtra;
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
                'route'       => $status === IR::STATUS_COMPLETED
                    ? route('pemagang.documents.sertifikat')
                    : null,
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
            'membercard' => [
                'label'       => 'Membercard Digital',
                'description' => 'Kartu anggota alumni magang Seveninc',
                'icon'        => 'fa-id-card',
                'available'   => $status === IR::STATUS_COMPLETED,
                'route'       => $status === IR::STATUS_COMPLETED
                    ? route('pemagang.membercard')
                    : null,
                'date'        => null,
            ],
        ];

        // Riwayat download — filter berdasarkan registrasi pemagang ini
        $downloadHistory = DocumentDownload::where('user_id', $user->id)
            ->when($registration, fn($q) => $q->orWhere('internship_registration_id', $registration->id))
            ->latest('downloaded_at')
            ->take(10)
            ->get();

        // Extras — surat rekomendasi, alumni group, job info
        $extras = $registration
            ? InternExtra::where('internship_registration_id', $registration->id)->first()
            : null;

        return view('pemagang.documents.index', compact(
            'user',
            'registration',
            'docs',
            'downloadHistory',
            'extras'
        ));
    }

    /**
     * Halaman lihat & download Membercard digital milik pemagang (2D PDF).
     * Hanya tersedia setelah status = completed.
     */
    public function viewMembercard()
    {
        $user       = auth()->user();
        $reg        = IR::where('user_id', $user->id)->latest('id')->first();

        // Membercard hanya tersedia setelah selesai magang
        if (!$reg || $reg->internship_status !== IR::STATUS_COMPLETED) {
            return back()->with('error', 'Membercard hanya tersedia setelah masa magang selesai.');
        }

        $membercard = $user->downloads()->latest()->first();

        if (!$membercard) {
            return back()->with('error', 'Membercard belum tersedia. Hubungi admin.');
        }

        return view('pemagang.membercard', compact('membercard', 'reg'));
    }

    /**
     * Download Membercard sebagai PDF.
     * Hanya tersedia setelah status = completed.
     */
    public function downloadMembercard()
    {
        $user       = auth()->user();
        $reg        = IR::where('user_id', $user->id)->latest('id')->first();

        // Membercard hanya tersedia setelah selesai magang
        if (!$reg || $reg->internship_status !== IR::STATUS_COMPLETED) {
            abort(403, 'Membercard hanya tersedia setelah masa magang selesai.');
        }

        $membercard = $user->downloads()->latest()->first();

        if (!$membercard) {
            return back()->with('error', 'Membercard belum tersedia. Hubungi admin.');
        }

        $data = [
            'name'     => $membercard->name,
            'code'     => $membercard->code,
            'brand'    => $membercard->brand ?? 'magangjogja.com',
            'angkatan' => $membercard->angkatan,
            'instansi' => $membercard->instansi,
        ];

        // Pakai Browsershot karena DomPDF tidak support CSS gradient
        $html = view('pemagang.membercard-pdf', $data)->render();

        $safeName = \Illuminate\Support\Str::slug($membercard->name);
        $filename = "Membercard-{$safeName}-{$membercard->code}.pdf";
        $tmpPath  = storage_path("app/tmp/{$filename}");

        if (!is_dir(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0775, true);
        }

        \Spatie\Browsershot\Browsershot::html($html)
            ->emulateMedia('screen')
            ->showBackground()
            ->margins(0, 0, 0, 0)
            ->windowSize(856, 540)   // 85.6mm x 54mm @ 96dpi * 2.54 = ~323x204 → scale up for quality
            ->deviceScaleFactor(2)
            ->setOption('preferCSSPageSize', true)
            ->setOption('printBackground', true)
            ->timeout(60)
            ->savePdf($tmpPath);

        // Update status has_downloaded
        if (!$membercard->has_downloaded) {
            $membercard->update([
                'has_downloaded' => true,
                'downloaded_at'  => now(),
            ]);
        }

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download Sertifikat milik pemagang yang login.
     * Cari sertifikat berdasarkan fullname pemagang di tabel certificates.
     */
    public function downloadSertifikat()
    {
        $user         = auth()->user();
        $registration = IR::where('user_id', $user->id)->latest('id')->first();

        if (!$registration || $registration->internship_status !== IR::STATUS_COMPLETED) {
            abort(403, 'Sertifikat hanya tersedia setelah magang selesai.');
        }

        // Cari sertifikat berdasarkan nama pemagang — case-insensitive & trim
        $certificate = \App\Models\Certificate::whereRaw('LOWER(TRIM(name)) = ?', [
                strtolower(trim($registration->fullname))
            ])
            ->latest()
            ->first();

        // Fallback: cari dengan LIKE kalau exact tidak ketemu
        if (!$certificate) {
            $certificate = \App\Models\Certificate::where('name', 'LIKE', '%' . trim($registration->fullname) . '%')
                ->latest()
                ->first();
        }

        if (!$certificate) {
            return back()->with('error', 'Sertifikat belum tersedia. Hubungi admin.');
        }

        // Delegate ke CertificateController
        return app(\App\Http\Controllers\CertificateController::class)
            ->downloadPdf($certificate);
    }

    /**
     * Download Surat Rekomendasi milik pemagang yang login.
     * Hanya tersedia jika admin sudah memberikan (rekomendasi_path diisi).
     */
    public function downloadRekomendasi()
    {
        $user         = auth()->user();
        $registration = IR::where('user_id', $user->id)->latest('id')->first();

        if (!$registration || $registration->internship_status !== IR::STATUS_COMPLETED) {
            abort(403, 'Surat rekomendasi hanya tersedia setelah magang selesai.');
        }

        $extra = \App\Models\InternExtra::where('internship_registration_id', $registration->id)->first();

        if (!$extra || !$extra->rekomendasi_path) {
            return back()->with('error', 'Surat rekomendasi belum tersedia. Hubungi admin.');
        }

        $fullPath = storage_path('app/public/' . $extra->rekomendasi_path);

        if (!file_exists($fullPath)) {
            return back()->with('error', 'File surat rekomendasi tidak ditemukan. Hubungi admin.');
        }

        $filename = 'Surat-Rekomendasi-' . \Illuminate\Support\Str::slug($registration->fullname) . '.pdf';
        return response()->download($fullPath, $filename, ['Content-Type' => 'application/pdf']);
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

        // Cari assessment berdasarkan intern_id dulu, fallback ke nama pemagang
        $assessment = InternAssessment::where('intern_id', $registration->id)->latest()->first()
            ?? InternAssessment::where('fullname', $registration->fullname)->latest()->first();

        if (!$assessment) {
            return back()->with('error', 'Surat penilaian belum tersedia. Hubungi admin.');
        }

        // Jika ditemukan lewat nama tapi intern_id belum diisi, update sekaligus
        if (!$assessment->intern_id) {
            $assessment->update(['intern_id' => $registration->id]);
        }

        // Delegate ke InternAssessmentController
        return app(\App\Http\Controllers\InternAssessmentController::class)
            ->downloadPDF($assessment->id);
    }
}
