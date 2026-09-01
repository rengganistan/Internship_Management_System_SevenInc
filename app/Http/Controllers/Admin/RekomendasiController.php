<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiSetting;
use App\Models\InternExtra;
use App\Models\InternshipRegistration as IR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RekomendasiController extends Controller
{
    /** GET /admin/rekomendasi/editor */
    public function edit()
    {
        $config = RekomendasiSetting::first() ?? RekomendasiSetting::create([
            'company_name'         => 'SEVEN INC.',
            'company_address'      => 'Jl. Raya Janti, Gang Arjuna No. 59, Karangjambe, Banguntapan, Bantul, Yogyakarta',
            'company_city'         => 'Yogyakarta',
            'company_phone'        => '0274-4534571',
            'company_postal_code'  => '55198',
            'leader_name'          => 'Rekario Danny Sanjaya, S.Kom',
            'leader_title'         => 'CEO',
            'company_brand'        => 'Seven Inc (Magangjogja.com)',
            'body_template'        => RekomendasiSetting::defaultBodyTemplate(),
        ]);

        // Ambil semua brand dari pemagang yang sudah completed
        $brands = IR::query()
            ->where('internship_status', IR::STATUS_COMPLETED)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->values();

        return view('admin.intern_extras.rekomendasi_editor', compact('config', 'brands'));
    }

    /**
     * GET /admin/rekomendasi/interns-by-brand?brand=XXX
     * API: ambil pemagang completed berdasarkan brand
     */
    public function getInternsByBrand(Request $request)
    {
        $brand = $request->query('brand');

        if (!$brand) {
            return response()->json(['interns' => []]);
        }

        $interns = IR::query()
            ->where('internship_status', IR::STATUS_COMPLETED)
            ->where('brand', $brand)
            ->select('id', 'fullname', 'student_id', 'study_program', 'institution_name', 'start_date', 'end_date', 'internship_interest')
            ->latest('id')
            ->get()
            ->map(fn ($r) => [
                'id'               => $r->id,
                'fullname'         => $r->fullname,
                'student_id'       => $r->student_id ?? '',
                'study_program'    => $r->study_program ?? '',
                'institution_name' => $r->institution_name ?? '',
                'start_date'       => $r->start_date ?? '',
                'end_date'         => $r->end_date ?? '',
                'internship_interest' => $r->internship_interest ?? '',
                'has_rekomendasi'  => InternExtra::where('internship_registration_id', $r->id)
                    ->whereNotNull('rekomendasi_path')
                    ->exists(),
            ]);

        return response()->json(['interns' => $interns]);
    }

    /** POST /admin/rekomendasi/editor */
    public function update(Request $request)
    {
        $request->validate([
            'company_name'        => 'required|string|max:100',
            'company_address'     => 'required|string|max:500',
            'company_city'        => 'required|string|max:100',
            'company_phone'       => 'nullable|string|max:50',
            'company_postal_code' => 'nullable|string|max:10',
            'leader_name'         => 'required|string|max:150',
            'leader_title'        => 'required|string|max:100',
            'company_brand'       => 'nullable|string|max:150',
            'body_template'       => 'nullable|string',
            'logo'                => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'stamp'               => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $config = RekomendasiSetting::firstOrCreate([]);

        $config->fill($request->only([
            'company_name','company_address','company_city','company_phone',
            'company_postal_code','leader_name','leader_title','company_brand','body_template',
        ]));

        if ($request->hasFile('logo')) {
            $brandSlug = Str::slug($request->company_brand ?? $request->company_name, '_');
            $config->logo_path = $request->file('logo')
                ->storeAs('images/logos', 'logo_rekomendasi_' . $brandSlug . '.png', 'public');
        }
        if ($request->hasFile('stamp')) {
            $brandSlug = Str::slug($request->company_brand ?? $request->company_name, '_');
            $config->stamp_path = $request->file('stamp')
                ->storeAs('images/signature', 'ttd_rekomendasi_' . $brandSlug . '.png', 'public');
        }

        $config->save();

        return back()->with('success', 'Template surat rekomendasi berhasil disimpan.');
    }

    /**
     * POST /admin/rekomendasi/generate-brand
     * Generate surat rekomendasi untuk banyak pemagang sekaligus (bulk).
     * PDF disimpan ke InternExtra masing-masing, TIDAK didownload.
     * Mengembalikan JSON { success, generated, failed, names }.
     */
    public function generateBulk(Request $request)
    {
        // Tangkap validation error sebagai JSON
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'intern_ids'          => 'required|array|min:1',
            'intern_ids.*'        => 'integer|exists:internship_registrations,id',
            'company_name'        => 'required|string|max:100',
            'company_address'     => 'required|string|max:500',
            'company_city'        => 'required|string|max:100',
            'company_phone'       => 'nullable|string|max:50',
            'company_postal_code' => 'nullable|string|max:10',
            'leader_name'         => 'required|string|max:150',
            'leader_title'        => 'required|string|max:100',
            'company_brand'       => 'nullable|string|max:150',
            'body_template'       => 'nullable|string',
            'logo'                => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'stamp'               => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'generated' => 0,
                'failed' => 0,
            ], 422);
        }

        // Build config dari input form (tidak wajib simpan ke DB, tapi update tetap dilakukan)
        $config = RekomendasiSetting::firstOrNew([]);
        $config->fill($request->only([
            'company_name','company_address','company_city','company_phone',
            'company_postal_code','leader_name','leader_title','company_brand','body_template',
        ]));

        if ($request->hasFile('logo')) {
            $brandSlug = Str::slug($request->company_brand ?? $request->company_name, '_');
            $config->logo_path = $request->file('logo')
                ->storeAs('images/logos', 'logo_rekomendasi_' . $brandSlug . '.png', 'public');
        }
        if ($request->hasFile('stamp')) {
            $brandSlug = Str::slug($request->company_brand ?? $request->company_name, '_');
            $config->stamp_path = $request->file('stamp')
                ->storeAs('images/signature', 'ttd_rekomendasi_' . $brandSlug . '.png', 'public');
        }
        $config->save();

        // Resolve aset visual ke base64
        $logoData  = $this->toDataUri($config->logo_path);
        $stampData = $this->toDataUri($config->stamp_path);

        $interns = IR::whereIn('id', $request->intern_ids)
            ->where('internship_status', IR::STATUS_COMPLETED)
            ->get();

        if ($interns->isEmpty()) {
            return response()->json([
                'success'   => false,
                'message'   => 'Tidak ada pemagang valid (status selesai) yang dipilih.',
                'generated' => 0,
                'failed'    => 0,
                'names'     => [],
            ]);
        }

        Storage::disk('public')->makeDirectory('documents/rekomendasi');

        $generated = [];
        $failed    = [];

        Carbon::setLocale('id');

        foreach ($interns as $intern) {
            try {
                $startStr = $intern->start_date
                    ? Carbon::parse($intern->start_date)->isoFormat('MMMM Y')
                    : '-';
                $endStr = $intern->end_date
                    ? Carbon::parse($intern->end_date)->isoFormat('MMMM Y')
                    : '-';

                $durationStr = 'beberapa bulan';
                if ($intern->start_date && $intern->end_date) {
                    $months = (int) round(
                        Carbon::parse($intern->start_date)->diffInDays(Carbon::parse($intern->end_date)) / 30
                    );
                    $durationStr = $months . ' bulan';
                }

                $running      = str_pad((string) $intern->id, 3, '0', STR_PAD_LEFT);
                $letterNumber = $running . '/SR/' . Str::upper(Str::slug($config->company_brand ?? $config->company_name, '.')) . '/' . now()->format('m/Y');
                $letterDateStr = now()->isoFormat('D MMMM Y');

                $bodyText = $this->buildBodyText(
                    $config->body_template ?? RekomendasiSetting::defaultBodyTemplate(),
                    [
                        'nama'          => $intern->fullname,
                        'divisi'        => $intern->internship_interest ?? '-',
                        'mulai'         => $startStr,
                        'selesai'       => $endStr,
                        'durasi'        => $durationStr,
                        'instansi'      => $intern->institution_name ?? '-',
                        'nim'           => $intern->student_id ?? '-',
                        'company_brand' => $config->company_brand ?? $config->company_name,
                    ]
                );

                $html = view('admin.rekomendasi_letter', [
                    'companyName'          => $config->company_name,
                    'companyAddress'       => $config->company_address,
                    'companyCity'          => $config->company_city,
                    'companyPhone'         => $config->company_phone,
                    'companyPostalCode'    => $config->company_postal_code,
                    'companyBrand'         => $config->company_brand,
                    'leaderName'           => $config->leader_name,
                    'leaderTitle'          => $config->leader_title,
                    'letterNumber'         => $letterNumber,
                    'letterDateStr'        => $letterDateStr,
                    'participantName'      => $intern->fullname,
                    'participantId'        => $intern->student_id ?? '-',
                    'participantMajor'     => $intern->study_program ?? '-',
                    'participantInstitute' => $intern->institution_name ?? '-',
                    'bodyText'             => $bodyText,
                    'logoData'             => $logoData,
                    'stampData'            => $stampData,
                ])->render();

                $safeName = Str::slug($intern->fullname ?? 'pemagang', '-');
                $fileName = "rekomendasi-{$intern->id}-{$safeName}-" . now()->format('Ymd_His') . '.pdf';
                $relPath  = "documents/rekomendasi/{$fileName}";
                $fullPath = storage_path("app/public/{$relPath}");

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)
                    ->setPaper('A4', 'portrait')
                    ->setOptions([
                        'isRemoteEnabled'      => true,
                        'isHtml5ParserEnabled' => true,
                        'defaultPaperSize'     => 'A4',
                    ]);

                $pdfContents = $pdf->output();
                if ($pdfContents === false) {
                    throw new \RuntimeException('Gagal menghasilkan PDF rekomendasi.');
                }

                if (!Storage::disk('public')->put($relPath, $pdfContents)) {
                    throw new \RuntimeException("Gagal menyimpan file ke: {$relPath}");
                }

                // Update InternExtra
                $extra = InternExtra::firstOrNew(['internship_registration_id' => $intern->id]);

                // Hapus file lama jika ada
                if ($extra->rekomendasi_path && file_exists(storage_path('app/public/' . $extra->rekomendasi_path))) {
                    @unlink(storage_path('app/public/' . $extra->rekomendasi_path));
                }

                $extra->internship_registration_id = $intern->id;
                $extra->rekomendasi_path           = $relPath;
                $extra->rekomendasi_url            = asset('storage/' . $relPath);
                $extra->rekomendasi_granted_at     = now();
                $extra->save();

                $generated[] = $intern->fullname;

            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Generate rekomendasi bulk gagal', [
                    'intern_id' => $intern->id,
                    'error'     => $e->getMessage(),
                ]);
                $failed[] = $intern->fullname;
            }
        }

        return response()->json([
            'success'   => count($generated) > 0,
            'message'   => count($generated) > 0
                ? count($generated) . ' surat rekomendasi berhasil digenerate.'
                : 'Semua surat gagal digenerate.',
            'generated' => count($generated),
            'failed'    => count($failed),
            'names'     => $generated,
            'failed_names' => $failed,
        ]);
    }

    /** GET /admin/rekomendasi/preview */
    public function preview(Request $request)
    {
        $config = RekomendasiSetting::first() ?? new RekomendasiSetting([
            'company_name'    => 'SEVEN INC.',
            'company_address' => 'Jl. Raya Janti, Gang Arjuna No. 59, Karangjambe, Banguntapan, Bantul, Yogyakarta',
            'company_city'    => 'Yogyakarta',
            'leader_name'     => 'Rekario Danny Sanjaya, S.Kom',
            'leader_title'    => 'CEO',
            'company_brand'   => 'Seven Inc (Magangjogja.com)',
        ]);

        // Override dari query params (live preview)
        foreach (['company_name','company_address','company_city','company_phone','company_postal_code',
                  'leader_name','leader_title','company_brand','body_template'] as $field) {
            if ($request->filled($field)) {
                $config->$field = $request->get($field);
            }
        }

        Carbon::setLocale('id');

        // Jika ada intern_id di query, load data pemagang asli
        $intern = null;
        if ($request->filled('intern_id')) {
            $intern = IR::find((int) $request->get('intern_id'));
        }

        if ($intern) {
            $participantName      = $intern->fullname;
            $participantId        = $intern->student_id ?? '-';
            $participantMajor     = $intern->study_program ?? '-';
            $participantInstitute = $intern->institution_name ?? '-';
            $divisionName         = $intern->internship_interest ?? '-';

            $startStr = $intern->start_date
                ? Carbon::parse($intern->start_date)->isoFormat('MMMM Y')
                : '-';
            $endStr = $intern->end_date
                ? Carbon::parse($intern->end_date)->isoFormat('MMMM Y')
                : '-';

            $durationStr = 'beberapa bulan';
            if ($intern->start_date && $intern->end_date) {
                $months = (int) round(
                    Carbon::parse($intern->start_date)->diffInDays(Carbon::parse($intern->end_date)) / 30
                );
                $durationStr = $months . ' bulan';
            }

            $running      = str_pad((string) $intern->id, 3, '0', STR_PAD_LEFT);
            $letterNumber = $running . '/SR/' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::slug($config->company_brand ?? $config->company_name, '.')) . '/' . now()->format('m/Y');
        } else {
            // Dummy data jika belum ada pemagang dipilih
            $participantName      = '— Pilih pemagang untuk preview —';
            $participantId        = '-';
            $participantMajor     = '-';
            $participantInstitute = '-';
            $divisionName         = '-';
            $startStr             = 'Bulan Tahun';
            $endStr               = 'Bulan Tahun';
            $durationStr          = '? bulan';
            $letterNumber         = '000/SR/BRAND/' . now()->format('m/Y');
        }

        $letterDateStr = Carbon::now()->isoFormat('D MMMM Y');

        $bodyText = $this->buildBodyText($config->body_template ?? RekomendasiSetting::defaultBodyTemplate(), [
            'nama'          => $participantName,
            'divisi'        => $divisionName,
            'mulai'         => $startStr,
            'selesai'       => $endStr,
            'durasi'        => $durationStr,
            'instansi'      => $participantInstitute,
            'nim'           => $participantId,
            'company_brand' => $config->company_brand ?? 'Seven Inc',
        ]);

        $logoData  = $this->toDataUri($config->logo_path);
        $stampData = $this->toDataUri($config->stamp_path);

        return view('admin.rekomendasi_letter', [
            'companyName'         => $config->company_name,
            'companyAddress'      => $config->company_address,
            'companyCity'         => $config->company_city,
            'companyPhone'        => $config->company_phone,
            'companyPostalCode'   => $config->company_postal_code,
            'companyBrand'        => $config->company_brand,
            'leaderName'          => $config->leader_name,
            'leaderTitle'         => $config->leader_title,
            'letterNumber'        => $letterNumber,
            'letterDateStr'       => $letterDateStr,
            'participantName'     => $participantName,
            'participantId'       => $participantId,
            'participantMajor'    => $participantMajor,
            'participantInstitute'=> $participantInstitute,
            'bodyText'            => $bodyText,
            'logoData'            => $logoData,
            'stampData'           => $stampData,
        ]);
    }

    /**
     * POST /admin/rekomendasi/generate/{intern}
     * Generate PDF single + simpan ke InternExtra (dari halaman edit intern_extra).
     * Tidak download — redirect kembali dengan notif.
     */
    public function generate(Request $request, IR $intern)
    {
        if ($intern->internship_status !== IR::STATUS_COMPLETED) {
            return back()->with('error', 'Surat rekomendasi hanya dapat di-generate untuk pemagang yang sudah selesai.');
        }

        $config = RekomendasiSetting::first();
        if (!$config) {
            return back()->with('error', 'Konfigurasi template rekomendasi belum diatur. Silakan atur di halaman Template Rekomendasi.');
        }

        // Override nama perusahaan dengan brand pemagang jika ada
        if (!empty($intern->brand)) {
            $config = clone $config;
            $config->company_name  = $intern->brand;
            $config->company_brand = $intern->brand;
        }

        try {
            Carbon::setLocale('id');

            $startStr = $intern->start_date
                ? Carbon::parse($intern->start_date)->isoFormat('MMMM Y')
                : '-';
            $endStr = $intern->end_date
                ? Carbon::parse($intern->end_date)->isoFormat('MMMM Y')
                : '-';

            $durationStr = 'beberapa bulan';
            if ($intern->start_date && $intern->end_date) {
                $months = (int) round(Carbon::parse($intern->start_date)->diffInDays(Carbon::parse($intern->end_date)) / 30);
                $durationStr = $months . ' bulan';
            }

            $running       = str_pad((string) $intern->id, 3, '0', STR_PAD_LEFT);
            $letterNumber  = $running . '/SR/' . Str::upper(Str::slug($config->company_brand ?? $config->company_name, '.')) . '/' . now()->format('m/Y');
            $letterDateStr = now()->isoFormat('D MMMM Y');

            $bodyText = $this->buildBodyText(
                $config->body_template ?? RekomendasiSetting::defaultBodyTemplate(),
                [
                    'nama'          => $intern->fullname,
                    'divisi'        => $intern->internship_interest ?? '-',
                    'mulai'         => $startStr,
                    'selesai'       => $endStr,
                    'durasi'        => $durationStr,
                    'instansi'      => $intern->institution_name ?? '-',
                    'nim'           => $intern->student_id ?? '-',
                    'company_brand' => $config->company_brand ?? 'Seven Inc',
                ]
            );

            $logoData  = $this->toDataUri($config->logo_path);
            $stampData = $this->toDataUri($config->stamp_path);

            $html = view('admin.rekomendasi_letter', [
                'companyName'          => $config->company_name,
                'companyAddress'       => $config->company_address,
                'companyCity'          => $config->company_city,
                'companyPhone'         => $config->company_phone,
                'companyPostalCode'    => $config->company_postal_code,
                'companyBrand'         => $config->company_brand,
                'leaderName'           => $config->leader_name,
                'leaderTitle'          => $config->leader_title,
                'letterNumber'         => $letterNumber,
                'letterDateStr'        => $letterDateStr,
                'participantName'      => $intern->fullname,
                'participantId'        => $intern->student_id ?? '-',
                'participantMajor'     => $intern->study_program ?? '-',
                'participantInstitute' => $intern->institution_name ?? '-',
                'bodyText'             => $bodyText,
                'logoData'             => $logoData,
                'stampData'            => $stampData,
            ])->render();

            // Simpan PDF
            $safeName = Str::slug($intern->fullname ?? 'pemagang', '-');
            $fileName = "rekomendasi-{$intern->id}-{$safeName}-" . now()->format('Ymd_His') . '.pdf';
            $relPath  = "documents/rekomendasi/{$fileName}";
            $fullPath = storage_path("app/public/{$relPath}");

            Storage::disk('public')->makeDirectory('documents/rekomendasi');

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled'      => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultPaperSize'     => 'A4',
                ]);

            $pdfContents = $pdf->output();
            if ($pdfContents === false) {
                throw new \RuntimeException('Gagal menghasilkan PDF rekomendasi.');
            }

            if (!Storage::disk('public')->put($relPath, $pdfContents)) {
                throw new \RuntimeException("Gagal menyimpan file rekomendasi ke: {$relPath}");
            }

            if (!file_exists($fullPath)) {
                throw new \RuntimeException("PDF gagal disimpan ke: {$fullPath}");
            }

            // Update InternExtra
            $extra = InternExtra::firstOrNew(['internship_registration_id' => $intern->id]);

            if ($extra->rekomendasi_path && file_exists(storage_path('app/public/' . $extra->rekomendasi_path))) {
                @unlink(storage_path('app/public/' . $extra->rekomendasi_path));
            }

            $extra->internship_registration_id = $intern->id;
            $extra->rekomendasi_path           = $relPath;
            $extra->rekomendasi_url            = asset('storage/' . $relPath);
            $extra->rekomendasi_granted_at     = now();
            $extra->save();

            // Tidak download — simpan saja ke InternExtra, pemagang bisa akses di halaman dokumen mereka
            return back()->with('success', "Surat rekomendasi untuk <strong>{$intern->fullname}</strong> berhasil digenerate dan sudah tersedia di halaman dokumen pemagang.");

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Generate rekomendasi gagal', [
                'intern_id' => $intern->id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal generate surat rekomendasi: ' . $e->getMessage());
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function buildBodyText(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    private function toDataUri(?string $path): ?string
    {
        if (!$path) return null;

        // Coba beberapa lokasi
        $candidates = [
            storage_path('app/public/' . $path),
            public_path($path),
        ];

        foreach ($candidates as $file) {
            if (file_exists($file)) {
                $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
                return "data:{$mime};base64," . base64_encode(file_get_contents($file));
            }
        }
        return null;
    }
}
