<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiSetting;
use App\Models\InternExtra;
use App\Models\InternshipRegistration as IR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        return view('admin.intern_extras.rekomendasi_editor', compact('config'));
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
            $config->logo_path = $request->file('logo')
                ->storeAs('images/logos', 'logo_rekomendasi.png', 'public');
        }
        if ($request->hasFile('stamp')) {
            $config->stamp_path = $request->file('stamp')
                ->storeAs('images/signature', 'ttd_rekomendasi.png', 'public');
        }

        $config->save();

        return back()->with('success', 'Template surat rekomendasi berhasil disimpan.');
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

        // Dummy data
        $participantName      = 'Rifka Meilani Nurlatifah (Preview)';
        $participantId        = '1910 (Preview)';
        $participantMajor     = 'Ilmu Hukum';
        $participantInstitute = 'UIN Sunan Kalijaga Yogyakarta';
        $divisionName         = 'Human Resource';
        $startStr             = 'Februari 2025';
        $endStr               = 'Mei 2025';
        $durationStr          = '3 bulan';
        $letterDateStr        = Carbon::now()->isoFormat('D MMMM Y');
        $letterNumber         = '001/SR/SEVEN.MJ/' . now()->format('m/Y');

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
     * Generate PDF + simpan ke InternExtra
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
            $letterNumber  = $running . '/SR/SEVEN.MJ/' . now()->format('m/Y');
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
            $fullDir  = storage_path('app/public/documents/rekomendasi');
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

            // Hapus file lama jika ada
            if ($extra->rekomendasi_path && file_exists(storage_path('app/public/' . $extra->rekomendasi_path))) {
                @unlink(storage_path('app/public/' . $extra->rekomendasi_path));
            }

            $extra->internship_registration_id = $intern->id;
            $extra->rekomendasi_path           = $relPath;
            $extra->rekomendasi_url            = asset('storage/' . $relPath);
            $extra->rekomendasi_granted_at     = now();
            $extra->save();

            return response()->download($fullPath, $fileName, ['Content-Type' => 'application/pdf'])
                ->deleteFileAfterSend(false);

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
