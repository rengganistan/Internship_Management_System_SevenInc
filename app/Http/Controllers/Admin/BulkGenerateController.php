<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\BrandHelper;
use App\Http\Controllers\Controller;
use App\Models\InternshipRegistration as IR;
use App\Models\DocumentDownload;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BulkGenerateController extends Controller
{
    /** GET /admin/bulk-generate */
    public function index(Request $request)
    {
        $brands   = BrandHelper::list();
        $brand    = $request->get('brand', '');
        $docType  = $request->get('doc_type', 'skl');
        $angkatan = $request->get('angkatan', '');

        // Query pemagang yang sesuai filter
        $query = IR::where('internship_status', IR::STATUS_COMPLETED)
            ->whereNotNull('brand');

        if ($brand) {
            $query->where('brand', $brand);
        }

        if ($angkatan) {
            $query->whereYear('start_date', $angkatan);
        }

        $interns = $query->with('user')->orderBy('fullname')->get();

        // Hitung yang siap & yang belum punya brand
        $withBrand    = $interns->filter(fn($i) => !empty($i->brand));
        $withoutBrand = $interns->filter(fn($i) => empty($i->brand));

        // Daftar tahun angkatan yang tersedia
        $angkatanList = IR::where('internship_status', IR::STATUS_COMPLETED)
            ->whereNotNull('start_date')
            ->selectRaw('YEAR(start_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('admin.bulk_generate.index', compact(
            'brands', 'brand', 'docType', 'angkatan',
            'interns', 'withBrand', 'withoutBrand', 'angkatanList'
        ));
    }

    /** POST /admin/bulk-generate — jalankan bulk generate */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'brand'    => 'required|string',
            'doc_type' => 'required|in:skl,sertifikat,loa',
            'angkatan' => 'nullable|integer',
            'ids'      => 'required|array|min:1',
            'ids.*'    => 'integer|exists:internship_registrations,id',
        ]);

        $docType = $validated['doc_type'];
        $brand   = $validated['brand'];

        // Ambil hanya intern yang dipilih + punya brand sesuai + completed
        $interns = IR::whereIn('id', $validated['ids'])
            ->where('brand', $brand)
            ->where('internship_status', IR::STATUS_COMPLETED)
            ->with('user')
            ->get();

        if ($interns->isEmpty()) {
            return back()->with('error', 'Tidak ada pemagang yang memenuhi syarat untuk di-generate.');
        }

        $success = 0;
        $failed  = 0;
        $errors  = [];

        foreach ($interns as $intern) {
            try {
                $this->generateDoc($docType, $intern, $request);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "{$intern->fullname}: " . $e->getMessage();
                Log::error("BulkGenerate failed for intern {$intern->id}", [
                    'doc_type' => $docType,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        $brandLabel = BrandHelper::label($brand);
        $msg = "Bulk generate {$docType} brand {$brandLabel}: {$success} berhasil" .
               ($failed > 0 ? ", {$failed} gagal" : '') . '.';

        if ($failed > 0) {
            return back()->with('warning', $msg)->with('gen_errors', $errors);
        }

        return back()->with('success', $msg);
    }

    /** Generate satu dokumen untuk satu intern */
    private function generateDoc(string $docType, IR $intern, Request $request): void
    {
        $user = $intern->user;

        match ($docType) {
            'skl' => $this->generateSkl($intern, $request),
            'loa' => $this->generateLoa($intern, $request),
            'sertifikat' => $this->generateCertificate($intern, $request),
            default => throw new \InvalidArgumentException("Tipe dokumen '{$docType}' belum didukung bulk generate."),
        };
    }

    private function generateSkl(IR $intern, Request $request): void
    {
        // Delegasikan ke SKLController yang sudah ada
        $sklCtrl = app(\App\Http\Controllers\SKLController::class);

        // Buat request palsu dengan user_id pemagang
        $fakeRequest = Request::create(
            url('/user/documents/skl/download'),
            'GET',
            ['user_id' => $intern->user_id]
        );
        $fakeRequest->setUserResolver(fn() => auth()->user());

        $response = $sklCtrl->download($fakeRequest, $intern->user_id);

        // Kirim notifikasi ke pemagang
        if ($intern->user_id) {
            AppNotification::create([
                'user_id' => $intern->user_id,
                'title'   => 'SKL Anda sudah siap',
                'message' => 'Surat Keterangan Lulus telah di-generate oleh admin dan dapat diunduh.',
                'url'     => route('pemagang.documents'),
                'type'    => 'document',
            ]);
        }
    }

    private function generateLoa(IR $intern, Request $request): void
    {
        $loaCtrl = app(\App\Http\Controllers\LoaController::class);

        $fakeRequest = Request::create(
            url('/admin/loa/generate'),
            'POST',
            ['intern_id' => $intern->id]
        );
        $fakeRequest->setUserResolver(fn() => auth()->user());

        $loaCtrl->generate($fakeRequest);

        if ($intern->user_id) {
            AppNotification::create([
                'user_id' => $intern->user_id,
                'title'   => 'LOA Anda sudah siap',
                'message' => 'Letter of Acceptance telah di-generate oleh admin.',
                'url'     => route('pemagang.documents'),
                'type'    => 'document',
            ]);
        }
    }

    private function generateCertificate(IR $intern, Request $request): void
    {
        $internController = app(\App\Http\Controllers\Admin\InternController::class);
        $pdfService = app(\App\Services\CertificatePdf::class);

        $internController->certificatePdf($intern, $pdfService);

        if ($intern->user_id) {
            AppNotification::create([
                'user_id' => $intern->user_id,
                'title'   => 'Sertifikat Anda sudah siap',
                'message' => 'Sertifikat magang telah di-generate oleh admin dan dapat diunduh.',
                'url'     => route('pemagang.documents'),
                'type'    => 'document',
            ]);
        }
    }
}
