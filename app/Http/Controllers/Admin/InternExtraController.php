<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternExtra;
use App\Models\RekomendasiSetting;
use App\Models\InternshipRegistration as IR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InternExtraController extends Controller
{
    /**
     * Daftar semua pemagang completed beserta status extras-nya.
     * Bisa difilter by brand.
     */
    public function index(Request $request)
    {
        // Ambil semua brand dari pemagang completed
        $brands = IR::where('internship_status', IR::STATUS_COMPLETED)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->values();

        $selectedBrand = $request->get('brand');

        $query = IR::where('internship_status', IR::STATUS_COMPLETED)
            ->with('user')
            ->orderByDesc('updated_at');

        if ($selectedBrand) {
            $query->where('brand', $selectedBrand);
        }

        $interns = $query->paginate(20)->appends($request->only('brand'));

        return view('admin.intern_extras.index', compact('interns', 'brands', 'selectedBrand'));
    }

    /**
     * Form edit extras untuk satu intern — termasuk template rekomendasi.
     */
    public function edit(IR $intern)
    {
        $extra = InternExtra::firstOrNew([
            'internship_registration_id' => $intern->id,
        ]);

        // Load konfigurasi rekomendasi — override nama perusahaan dengan brand pemagang jika ada
        $config = RekomendasiSetting::first() ?? new RekomendasiSetting([
            'company_name'    => 'SEVEN INC.',
            'company_address' => 'Jl. Raya Janti, Gang Arjuna No. 59, Karangjambe, Banguntapan, Bantul, Yogyakarta',
            'company_city'    => 'Yogyakarta',
            'company_phone'   => '0274-4534571',
            'company_postal_code' => '55198',
            'leader_name'     => 'Rekario Danny Sanjaya, S.Kom',
            'leader_title'    => 'CEO',
            'company_brand'   => 'Seven Inc (Magangjogja.com)',
        ]);

        // Override brand & company name dengan brand pemagang jika ada
        if (!empty($intern->brand)) {
            $config = clone $config;
            $config->company_name  = $intern->brand;
            $config->company_brand = $intern->brand;
        }

        return view('admin.intern_extras.edit', compact('intern', 'extra', 'config'));
    }

    /**
     * Simpan/update extras (link alumni + info kerja) untuk satu intern.
     */
    public function update(Request $request, IR $intern)
    {
        $request->validate([
            'alumni_group_url'     => 'nullable|url|max:500',
            'alumni_group_label'   => 'nullable|string|max:100',
            'job_info_url'         => 'nullable|url|max:500',
            'job_info_description' => 'nullable|string|max:500',
        ]);

        // Jika mode all_brand, simpan ke semua pemagang brand yang sama
        $allBrandMode = $request->input('mode') === 'all_brand';
        $targets = ($allBrandMode && !empty($intern->brand))
            ? IR::where('internship_status', IR::STATUS_COMPLETED)->where('brand', $intern->brand)->get()
            : collect([$intern]);

        foreach ($targets as $target) {
            $extra = InternExtra::firstOrNew([
                'internship_registration_id' => $target->id,
            ]);

            // Alumni group
            if ($request->input('clear_alumni') === '1') {
                $extra->alumni_group_url        = null;
                $extra->alumni_group_label      = null;
                $extra->alumni_group_granted_at = null;
            } elseif ($request->has('alumni_group_url')) {
                if ($request->filled('alumni_group_url')) {
                    $extra->alumni_group_url   = $request->alumni_group_url;
                    $extra->alumni_group_label = $request->alumni_group_label ?: 'Grup Alumni Seveninc';
                    if (!$extra->alumni_group_granted_at) {
                        $extra->alumni_group_granted_at = now();
                    }
                } else {
                    $extra->alumni_group_url        = null;
                    $extra->alumni_group_label      = null;
                    $extra->alumni_group_granted_at = null;
                }
            }

            // Job info
            if ($request->input('clear_job_info') === '1') {
                $extra->job_info_url         = null;
                $extra->job_info_description = null;
                $extra->job_info_granted_at  = null;
            } elseif ($request->has('job_info_url')) {
                if ($request->filled('job_info_url')) {
                    $extra->job_info_url         = $request->job_info_url;
                    $extra->job_info_description = $request->job_info_description;
                    if (!$extra->job_info_granted_at) {
                        $extra->job_info_granted_at = now();
                    }
                } else {
                    $extra->job_info_url         = null;
                    $extra->job_info_description = null;
                    $extra->job_info_granted_at  = null;
                }
            }

            $extra->save();
        }

        $redirectUrl = route('admin.intern_extras.edit', $intern->id)
            . ($allBrandMode ? '?mode=all_brand' : '');

        $msg = ($allBrandMode && $targets->count() > 1)
            ? "Link grup alumni & info kerja berhasil disimpan untuk <strong>{$targets->count()} pemagang</strong> brand <strong>{$intern->brand}</strong>."
            : "Akses eksklusif untuk <strong>{$intern->fullname}</strong> berhasil diperbarui.";

        return redirect($redirectUrl)->with('success', $msg);
    }

    /**
     * Simpan template rekomendasi (AJAX) — dari halaman edit single intern.
     * Return JSON.
     */
    public function saveTemplate(Request $request, IR $intern)
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
            'company_name', 'company_address', 'company_city', 'company_phone',
            'company_postal_code', 'leader_name', 'leader_title', 'company_brand', 'body_template',
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

        return response()->json(['success' => true, 'message' => 'Template berhasil disimpan.']);
    }

    /**
     * Kirim semua sekaligus: generate surat rekomendasi + simpan link alumni + info kerja
     * untuk satu pemagang atau semua pemagang satu brand.
     * Return JSON.
     */
    public function sendAll(Request $request, IR $intern)
    {
        $allBrandMode = $request->input('mode') === 'all_brand';

        $request->validate([
            'company_name'         => 'required|string|max:100',
            'company_address'      => 'required|string|max:500',
            'company_city'         => 'required|string|max:100',
            'company_phone'        => 'nullable|string|max:50',
            'company_postal_code'  => 'nullable|string|max:10',
            'leader_name'          => 'required|string|max:150',
            'leader_title'         => 'required|string|max:100',
            'company_brand'        => 'nullable|string|max:150',
            'body_template'        => 'nullable|string',
            'alumni_group_url'     => 'nullable|url|max:500',
            'alumni_group_label'   => 'nullable|string|max:100',
            'job_info_url'         => 'nullable|url|max:500',
            'job_info_description' => 'nullable|string|max:500',
        ]);

        // Simpan template rekomendasi
        $config = RekomendasiSetting::firstOrCreate([]);
        $config->fill($request->only([
            'company_name', 'company_address', 'company_city', 'company_phone',
            'company_postal_code', 'leader_name', 'leader_title', 'company_brand', 'body_template',
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

        // Tentukan daftar target
        $targets = ($allBrandMode && !empty($intern->brand))
            ? IR::where('internship_status', IR::STATUS_COMPLETED)->where('brand', $intern->brand)->get()
            : collect([$intern]);

        if ($targets->isEmpty()) {
            return response()->json([
                'success' => false, 'message' => 'Tidak ada pemagang valid yang ditemukan.',
                'generated' => 0, 'failed' => 0, 'names' => [],
            ]);
        }

        Storage::disk('public')->makeDirectory('documents/rekomendasi');
        $logoData  = $this->toDataUri($config->logo_path);
        $stampData = $this->toDataUri($config->stamp_path);
        Carbon::setLocale('id');

        $generated = [];
        $failed    = [];

        foreach ($targets as $target) {
            try {
                // ── 1. Generate PDF surat rekomendasi ──
                $startStr    = $target->start_date ? Carbon::parse($target->start_date)->isoFormat('MMMM Y') : '-';
                $endStr      = $target->end_date   ? Carbon::parse($target->end_date)->isoFormat('MMMM Y')   : '-';
                $durationStr = 'beberapa bulan';
                if ($target->start_date && $target->end_date) {
                    $months = (int) round(
                        Carbon::parse($target->start_date)->diffInDays(Carbon::parse($target->end_date)) / 30
                    );
                    $durationStr = $months . ' bulan';
                }

                $running       = str_pad((string) $target->id, 3, '0', STR_PAD_LEFT);
                $letterNumber  = $running . '/SR/' . Str::upper(Str::slug($config->company_brand ?? $config->company_name, '.')) . '/' . now()->format('m/Y');
                $letterDateStr = now()->isoFormat('D MMMM Y');

                $bodyText = $this->buildBodyText(
                    $config->body_template ?? RekomendasiSetting::defaultBodyTemplate(),
                    [
                        'nama'          => $target->fullname,
                        'divisi'        => $target->internship_interest ?? '-',
                        'mulai'         => $startStr,
                        'selesai'       => $endStr,
                        'durasi'        => $durationStr,
                        'instansi'      => $target->institution_name ?? '-',
                        'nim'           => $target->student_id ?? '-',
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
                    'participantName'      => $target->fullname,
                    'participantId'        => $target->student_id ?? '-',
                    'participantMajor'     => $target->study_program ?? '-',
                    'participantInstitute' => $target->institution_name ?? '-',
                    'bodyText'             => $bodyText,
                    'logoData'             => $logoData,
                    'stampData'            => $stampData,
                ])->render();

                // Nama file unik per pemagang — uniqid() cegah collision saat bulk
                $safeName = Str::slug($target->fullname ?? 'pemagang', '-');
                $fileName = "rekomendasi-{$target->id}-{$safeName}-" . now()->format('Ymd_His') . '-' . uniqid() . '.pdf';
                $relPath  = "documents/rekomendasi/{$fileName}";

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)
                    ->setPaper('A4', 'portrait')
                    ->setOptions([
                        'isRemoteEnabled'      => true,
                        'isHtml5ParserEnabled' => true,
                        'defaultPaperSize'     => 'A4',
                        'defaultFont'          => 'serif',
                        'dpi'                  => 96,
                    ]);

                $pdfContents = $pdf->output();
                if ($pdfContents === false) {
                    throw new \RuntimeException('Gagal menghasilkan PDF.');
                }
                if (!Storage::disk('public')->put($relPath, $pdfContents)) {
                    throw new \RuntimeException("Gagal menyimpan file ke: {$relPath}");
                }

                // ── 2. Simpan semua ke InternExtra ──
                $extra = InternExtra::firstOrNew(['internship_registration_id' => $target->id]);

                // Hapus file PDF lama
                if ($extra->rekomendasi_path && file_exists(storage_path('app/public/' . $extra->rekomendasi_path))) {
                    @unlink(storage_path('app/public/' . $extra->rekomendasi_path));
                }

                $extra->internship_registration_id = $target->id;
                $extra->rekomendasi_path           = $relPath;
                $extra->rekomendasi_url            = asset('storage/' . $relPath);
                $extra->rekomendasi_granted_at     = now();

                // Link grup alumni (jika diisi)
                if ($request->filled('alumni_group_url')) {
                    $extra->alumni_group_url   = $request->alumni_group_url;
                    $extra->alumni_group_label = $request->alumni_group_label ?: 'Grup Alumni Seveninc';
                    if (!$extra->alumni_group_granted_at) {
                        $extra->alumni_group_granted_at = now();
                    }
                }

                // Info kerja (jika diisi)
                if ($request->filled('job_info_url')) {
                    $extra->job_info_url         = $request->job_info_url;
                    $extra->job_info_description = $request->job_info_description;
                    if (!$extra->job_info_granted_at) {
                        $extra->job_info_granted_at = now();
                    }
                }

                $extra->save();
                $generated[] = $target->fullname;

            } catch (\Throwable $e) {
                Log::error('sendAll gagal', ['intern_id' => $target->id, 'error' => $e->getMessage()]);
                $failed[] = $target->fullname;
            }
        }

        $total = count($generated);
        return response()->json([
            'success'      => $total > 0,
            'message'      => $total > 0
                ? "Berhasil mengirim ke {$total} pemagang: surat rekomendasi, link grup alumni, dan info kerja."
                : 'Semua proses gagal.',
            'generated'    => $total,
            'failed'       => count($failed),
            'names'        => $generated,
            'failed_names' => $failed,
        ]);
    }

    /**
     * Hapus surat rekomendasi.
     */
    public function destroyRekomendasi(IR $intern)
    {
        $extra = InternExtra::where('internship_registration_id', $intern->id)->first();
        if ($extra?->rekomendasi_path) {
            Storage::disk('public')->delete($extra->rekomendasi_path);
            $extra->update([
                'rekomendasi_path'       => null,
                'rekomendasi_url'        => null,
                'rekomendasi_granted_at' => null,
            ]);
        }

        return back()->with('success', 'Surat rekomendasi berhasil dihapus.');
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

        $candidates = [
            storage_path('app/public/' . $path),
            public_path($path),
        ];

        foreach ($candidates as $file) {
            if (file_exists($file)) {
                $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $mime = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/png';
                return "data:{$mime};base64," . base64_encode(file_get_contents($file));
            }
        }
        return null;
    }
}
