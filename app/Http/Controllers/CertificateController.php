<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\InternshipRegistration as IR;
use App\Models\ExternalParticipant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Spatie\Browsershot\Browsershot;

use Carbon\Carbon;
use ZipArchive;


class CertificateController extends Controller
{

    public function externalBulkDownloadFromForm(Request $request)
    {
        // Validasi sama seperti storeExternal, tapi kita TIDAK menyimpan ke DB
        $validBrands = ['MJ','AK','RW','TS','AP','BK','BC','LK','LJT','LJG','PJ','SB','TV','TN','TL','AKI','SI'];

        $request->validate([
            'company'           => ['required','string','max:255'],
            'city'              => ['required','string','max:255'],
            'brand'             => ['required', Rule::in($validBrands)],
            'background_image'  => ['required','string','starts_with:bg_'],
            'logo1'             => ['required','string','starts_with:logo_'],
            'logo2'             => ['nullable','string','starts_with:logo_'],
            'signature_image1'  => ['required','string','starts_with:ttd_'],
            'signature_image2'  => ['nullable','string','starts_with:ttd_'],
            'start_date'        => ['required','date','before_or_equal:end_date'],
            'end_date'          => ['required','date','after_or_equal:start_date'],
            'name_signatory1'   => ['required','string','max:255'],
            'name_signatory2'   => ['nullable','string','max:255'],
            'role1'             => ['required','string','max:255'],
            'role2'             => ['nullable','string','max:255'],
            'participants'           => ['required','array','min:1'],
            'participants.*.name'    => ['required','string','max:255'],
        ]);

        $d = $request->all();

        // Normalisasi path (relatif terhadap disk public)
        $bg   = "images/backgrounds/{$d['background_image']}";
        $l1   = "images/logos/{$d['logo1']}";
        $l2   = !empty($d['logo2']) ? "images/logos/{$d['logo2']}" : null;
        $s1   = "images/signature/{$d['signature_image1']}";
        $s2   = !empty($d['signature_image2']) ? "images/signature/{$d['signature_image2']}" : null;

        foreach ([$bg,$l1,$s1,$l2,$s2] as $p) {
            if ($p && !Storage::exists("public/{$p}")) {
                return back()->withErrors(['file_missing'=>"File tidak ditemukan: {$p}"])->withInput();
            }
        }

        $start   = \Carbon\Carbon::parse($d['start_date']);
        $end     = \Carbon\Carbon::parse($d['end_date']);
        $brand   = strtoupper($d['brand']);
        $company = $d['company'];
        $companyCode = $this->companyCode($company);

        $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        $monthRoman = $roman[$end->month];
        $year       = $end->year;
        $division   = 'EXT';

        // Mulai dari sequence terakhir bulan tsb (supaya konsisten)
        $seq = $this->startingSequence($end);

        // Temp folder untuk PDF
        $stamp  = now()->format('Ymd_His') . '_' . \Illuminate\Support\Str::random(5);
        $tmpDir = storage_path("app/tmp/external_form_{$stamp}");
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0775, true);

        $pdfFiles = [];

        foreach ($d['participants'] as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') continue;

            $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
            $serial = "{$seqStr}/SERT/{$division}/{$companyCode}.{$brand}/{$monthRoman}/{$year}";
            $seq++;

            // Buat instance model TANPA menyimpan (untuk dipakai view pdf)
            $cert = new \App\Models\Certificate([
                'name'              => $name,
                'division'          => $division,
                'company'           => $company,
                'background_image'  => $bg,
                'start_date'        => $start,
                'end_date'          => $end,
                'city'              => $d['city'],
                'brand'             => $brand,
                'serial_number'     => $serial,
                'logo1'             => $l1,
                'logo2'             => $l2,
                'signature_image1'  => $s1,
                'signature_image2'  => $s2,
                'name_signatory1'   => $d['name_signatory1'],
                'name_signatory2'   => $d['name_signatory2'] ?? null,
                'role1'             => $d['role1'],
                'role2'             => $d['role2'] ?? null,
            ]);

            // Render HTML view sertifikat
            $html = View::make('certificates.pdf', ['certificate' => $cert])->render();

            // <base href> agar /storage/... kebaca Chromium
            $base = rtrim(config('app.url'), '/') . '/';
            $html = preg_replace('/<head>/i', '<head><base href="'.$base.'">', $html, 1);

            // Nama file
            $brandMap = [
                'MJ'=>'magangjogja','AK'=>'areakerja','RW'=>'republikweb','TS'=>'titipsini','AP'=>'ambilpaket',
                'BK'=>'bikinkepo','BC'=>'bimbelcerdas.com','LK'=>'latihankerja.com','LJT'=>'lowkerjateng.com',
                'LJG'=>'lowkerjogja.com','PJ'=>'pijatjogja.com','SB'=>'sayabantu.com','TV'=>'titikvisual',
                'TN'=>'tuantanah','TL'=>'tukanglas.org','AKI'=>'adakamar.id','SI'=>'seven inc',
            ];
            $brandText = $brandMap[$brand] ?? $brand;
            $brandSlug = \Illuminate\Support\Str::slug($brandText, '-');
            $nameSlug  = \Illuminate\Support\Str::slug($name, '-');
            $pdfPath   = "{$tmpDir}/{$brandSlug}-{$nameSlug}.pdf";

            // Generate PDF (Browsershot)
            \Spatie\Browsershot\Browsershot::html($html)
                ->emulateMedia('print')->format('A4')->landscape()->margins(0,0,0,0)
                ->waitUntilNetworkIdle()->timeout(180)
                ->setOption('args', ['--no-sandbox','--disable-setuid-sandbox'])
                ->savePdf($pdfPath);

            $pdfFiles[] = $pdfPath;
        }

        if (empty($pdfFiles)) {
            @rmdir($tmpDir);
            return back()->withErrors(['participants'=>'Tidak ada peserta valid untuk dibuatkan sertifikat.'])->withInput();
        }

        // ZIP semua PDF
        $zipName = 'sertifikat_external_bulk_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path("app/tmp/{$zipName}");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            foreach ($pdfFiles as $f) @unlink($f);
            @rmdir($tmpDir);
            return back()->withErrors(['bulk'=>'Gagal membuat ZIP.'])->withInput();
        }
        foreach ($pdfFiles as $f) $zip->addFile($f, basename($f));
        $zip->close();

        // Bereskan temp PDFs
        foreach ($pdfFiles as $f) @unlink($f);
        @rmdir($tmpDir);

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }



    // ====== EXTERNAL PARTICIPANTS ======

    /**
     * Form create sertifikat eksternal (tanpa divisi).
     * Reuse daftar background/logo/ttd seperti create().
     */
    public function createExternal()
    {
        $backgroundFiles = collect(Storage::files('public/images/backgrounds'))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => str_starts_with($f, 'bg_'))
            ->values();

        $logoFiles = collect(Storage::files('public/images/logos'))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => str_starts_with($f, 'logo_'))
            ->values();

        $signatureFiles = collect(Storage::files('public/images/signature'))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => str_starts_with($f, 'ttd_'))
            ->values();

        // Brand (kode => label)
        $brands = [
            'MJ'=>'Magangjogja','AK'=>'Areakerja','RW'=>'Republikweb','TS'=>'Titipsini','AP'=>'Ambilpaket',
            'BK'=>'Bikinkepo','BC'=>'Bimbelcerdas.com','LK'=>'Latihankerja.com','LJT'=>'Lowkerjateng.com',
            'LJG'=>'Lowkerjogja.com','PJ'=>'Pijatjogja.com','SB'=>'Sayabantu.com','TV'=>'Titikvisual',
            'TN'=>'Tuantanah','TL'=>'Tukanglas.org','AKI'=>'Adakamar.id','SI'=>'Seven Inc',
        ];

        return view('certificates.create_external', compact(
            'backgroundFiles','logoFiles','signatureFiles','brands'
        ));
    }


    /**
     * Simpan banyak sertifikat untuk peserta eksternal (tanpa divisi).
     * - Peserta dikirim sebagai array: participants[][name]
     * - Division dipaksa 'EXT'
     * - Running number konsisten per bulan-tahun (naik untuk tiap peserta)
     */
    public function storeExternal(Request $request)
    {
        $validBrands = ['MJ','AK','RW','TS','AP','BK','BC','LK','LJT','LJG','PJ','SB','TV','TN','TL','AKI','SI'];

        $request->validate([
            // field global (berlaku untuk semua peserta)
            'company'          => ['required','string','max:255'],
            'city'             => ['required','string','max:255'],
            'brand'            => ['required', Rule::in($validBrands)],

            'background_image' => ['required','string','starts_with:bg_'],
            'logo1'            => ['required','string','starts_with:logo_'],
            'logo2'            => ['nullable','string','starts_with:logo_'],
            'signature_image1' => ['required','string','starts_with:ttd_'],
            'signature_image2' => ['nullable','string','starts_with:ttd_'],

            'start_date'       => ['required','date','before_or_equal:end_date'],
            'end_date'         => ['required','date','after_or_equal:start_date'],

            'name_signatory1'  => ['required','string','max:255'],
            'name_signatory2'  => ['nullable','string','max:255'],
            'role1'            => ['required','string','max:255'],
            'role2'            => ['nullable','string','max:255'],

            // peserta (minimal 1 nama)
            'participants'           => ['required','array','min:1'],
            'participants.*.name'    => ['required','string','max:255'],
        ], [
            'participants.required' => 'Minimal satu peserta harus diisi.',
            'participants.*.name.required' => 'Nama peserta wajib diisi.',
        ]);

        $data = $request->all();

        // Normalisasi path relatif (disk 'public')
        $backgroundPath = "images/backgrounds/{$data['background_image']}";
        $logo1Path      = "images/logos/{$data['logo1']}";
        $logo2Path      = $data['logo2'] ? "images/logos/{$data['logo2']}" : null;
        $sig1Path       = "images/signature/{$data['signature_image1']}";
        $sig2Path       = $data['signature_image2'] ? "images/signature/{$data['signature_image2']}" : null;

        // Pastikan file ada
        foreach ([$backgroundPath,$logo1Path,$sig1Path,$logo2Path,$sig2Path] as $p) {
            if ($p && !Storage::exists("public/{$p}")) {
                return back()->withErrors(['file_missing' => "File tidak ditemukan: {$p}"])->withInput();
            }
        }

        // Tanggal & kode-kode
        $startDate = Carbon::parse($data['start_date']);
        $endDate   = Carbon::parse($data['end_date']);
        $roman     = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        $monthRoman= $roman[$endDate->month];
        $year      = $endDate->year;

        $companyCode = $this->companyCode($data['company']);
        $brandCode   = strtoupper($data['brand']);
        $divisionCode= 'EXT'; // <- fitur eksternal: divisi diset tetap

        // Ambil starting sequence bulan/tahun tsb
        $last = Certificate::whereYear('created_at', $endDate->year)
                ->whereMonth('created_at', $endDate->month)
                ->orderByDesc('id')
                ->first();

        $seq = 1;
        if ($last && preg_match('/^(\d{3})\/SERT\//', $last->serial_number, $m)) {
            $seq = (int)$m[1] + 1;
        }

        // Simpan banyak peserta sekali jalan
        $created = 0;
        DB::transaction(function () use (
            $data,$startDate,$endDate,$monthRoman,$year,$companyCode,$brandCode,$divisionCode,
            $backgroundPath,$logo1Path,$logo2Path,$sig1Path,$sig2Path,&$seq,&$created
        ) {
            foreach ($data['participants'] as $p) {
                $name = trim($p['name'] ?? '');
                if ($name === '') continue;

                $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
                $segments = [$seqStr, 'SERT'];
                if (strtoupper($divisionCode) !== 'EXT') {
                    $segments[] = strtoupper($divisionCode);
                }
                $segments[] = "{$companyCode}.{$brandCode}";
                $segments[] = $monthRoman;
                $segments[] = $year;

                $serial = implode('/', $segments);
                $seq++;

                Certificate::create([
                    'name'              => $name,
                    'division'          => $divisionCode,
                    'company'           => $data['company'],
                    'background_image'  => $backgroundPath,
                    'start_date'        => $startDate,
                    'end_date'          => $endDate,
                    'city'              => $data['city'],
                    'brand'             => $brandCode,
                    'serial_number'     => $serial,
                    'logo1'             => $logo1Path,
                    'logo2'             => $logo2Path,
                    'signature_image1'  => $sig1Path,
                    'signature_image2'  => $sig2Path,
                    'name_signatory1'   => $data['name_signatory1'],
                    'name_signatory2'   => $data['name_signatory2'],
                    'role1'             => $data['role1'],
                    'role2'             => $data['role2'],
                ]);

                $created++;
            }
        });

        return redirect()->route('admin.certificate.index')
            ->with('success', "Berhasil membuat {$created} sertifikat eksternal.");
    }


        /** ====== FITUR 1: Bulk create from interns ====== */
        public function createFromInterns()
        {
            // Sediakan list file (seperti create()) tapi tanpa dropdown divisi (auto dari intern)
            $backgroundFiles = collect(Storage::files('public/images/backgrounds'))
                ->map(fn ($f) => basename($f))
                ->filter(fn ($f) => str_starts_with($f, 'bg_'))
                ->values();

            $logoFiles = collect(Storage::files('public/images/logos'))
                ->map(fn ($f) => basename($f))
                ->filter(fn ($f) => str_starts_with($f, 'logo_'))
                ->values();

            $signatureFiles = collect(Storage::files('public/images/signature'))
                ->map(fn ($f) => basename($f))
                ->filter(fn ($f) => str_starts_with($f, 'ttd_'))
                ->values();

            // Brand (kode => label)
            $brands = [
                'MJ'=>'Magangjogja','AK'=>'Areakerja','RW'=>'Republikweb','TS'=>'Titipsini','AP'=>'Ambilpaket',
                'BK'=>'Bikinkepo','BC'=>'Bimbelcerdas.com','LK'=>'Latihankerja.com','LJT'=>'Lowkerjateng.com',
                'LJG'=>'Lowkerjogja.com','PJ'=>'Pijatjogja.com','SB'=>'Sayabantu.com','TV'=>'Titikvisual',
                'TN'=>'Tuantanah','TL'=>'Tukanglas.org','AKI'=>'Adakamar.id','SI'=>'Seven Inc',
            ];

            return view('certificates.create_interns', compact('backgroundFiles','logoFiles','signatureFiles','brands'));
        }

        public function storeFromInterns(Request $request)
        {
            // brand, perusahaan, kota, aset, penandatangan → dipakai untuk semua intern
            $validBrands = ['MJ','AK','RW','TS','AP','BK','BC','LK','LJT','LJG','PJ','SB','TV','TN','TL','AKI','SI'];

            $request->validate([
                'intern_ids'        => ['required','array','min:1'],
                'intern_ids.*'      => ['integer','exists:internship_registrations,id'],
                'company'           => ['required','string','max:255'],
                'city'              => ['required','string','max:255'],
                'brand'             => ['required', Rule::in($validBrands)],
                'background_image'  => ['required','string','starts_with:bg_'],
                'logo1'             => ['required','string','starts_with:logo_'],
                'logo2'             => ['nullable','string','starts_with:logo_'],
                'signature_image1'  => ['required','string','starts_with:ttd_'],
                'signature_image2'  => ['nullable','string','starts_with:ttd_'],
                'name_signatory1'   => ['required','string','max:255'],
                'role1'             => ['required','string','max:255'],
                'name_signatory2'   => ['nullable','string','max:255'],
                'role2'             => ['nullable','string','max:255'],
                // Opsi: pakai tanggal intern (default true)
                'use_intern_dates'  => ['nullable','boolean'],
                // Kalau admin ingin override tanggal utk semua (opsional)
                'start_date'        => ['nullable','date'],
                'end_date'          => ['nullable','date','after_or_equal:start_date'],
            ]);

            $data = $request->all();
            $brandCode     = strtoupper($data['brand']);
            $company       = $data['company'];
            $companyCode   = $this->companyCode($company);
            $city          = $data['city'];
            $useInternDate = (bool)($data['use_intern_dates'] ?? true);

            // Normalisasi path relatif (disk 'public')
            $backgroundPath = "images/backgrounds/{$data['background_image']}";
            $logo1Path      = "images/logos/{$data['logo1']}";
            $logo2Path      = $data['logo2'] ? "images/logos/{$data['logo2']}" : null;
            $sig1Path       = "images/signature/{$data['signature_image1']}";
            $sig2Path       = $data['signature_image2'] ? "images/signature/{$data['signature_image2']}" : null;

            // Pastikan file ada
            foreach ([$backgroundPath,$logo1Path,$sig1Path,$logo2Path,$sig2Path] as $p) {
                if ($p && !Storage::exists("public/{$p}")) {
                    return back()->withErrors(['file_missing'=>"File tidak ditemukan: {$p}"])->withInput();
                }
            }

            // Preload interns
            $interns = IR::whereIn('id', $data['intern_ids'])->get();
            if ($interns->isEmpty()) {
                return back()->withErrors(['intern_ids'=>'Data pemagang tidak ditemukan'])->withInput();
            }

            // Untuk generator running number per (YYYY-MM)
            $seqCache = []; // ["2025-05" => 12]  → next seq = 13
            $roman    = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];

            DB::transaction(function () use (
                $interns,$useInternDate,$data,$company,$companyCode,$brandCode,$city,
                $backgroundPath,$logo1Path,$logo2Path,$sig1Path,$sig2Path,$roman,&$seqCache
            ) {
                foreach ($interns as $ir) {
                    // Nama & tanggal per intern
                    $name = $ir->fullname;
                    $start = $useInternDate ? Carbon::parse($ir->start_date) : ($data['start_date'] ? Carbon::parse($data['start_date']) : Carbon::parse($ir->start_date));
                    $end   = $useInternDate ? Carbon::parse($ir->end_date)   : ($data['end_date']   ? Carbon::parse($data['end_date'])   : Carbon::parse($ir->end_date));

                    // Divisi auto dari interest
                    $divisionCode = $this->divisionFromInterest((string)$ir->internship_interest) ?? 'ADM'; // fallback aman

                    // Ambil seq per (YYYY-MM) end date
                    $ym = $end->format('Y-m');
                    if (!isset($seqCache[$ym])) {
                        // cari last seq di bulan tsb (gunakan pola 3 digit di depan serial)
                        $last = Certificate::whereYear('created_at', $end->year)
                            ->whereMonth('created_at', $end->month)
                            ->orderByDesc('id')->first();
                        $seq = 1;
                        if ($last && preg_match('/^(\d{3})\/SERT\//', $last->serial_number, $m)) {
                            $seq = (int)$m[1] + 1;
                        }
                        $seqCache[$ym] = $seq;
                    }
                    $seq = $seqCache[$ym]++;
                    $seqStr = str_pad((string)$seq, 3, '0', STR_PAD_LEFT);

                    // Serial: NNN/SERT/DIV/COMP.BRAND/ROMAWI/TAHUN
                    $serial = "{$seqStr}/SERT/{$divisionCode}/{$companyCode}.{$brandCode}/".$roman[$end->month]."/".$end->year;

                    $cert = Certificate::create([
                        'name'              => $name,
                        'division'          => $divisionCode,
                        'company'           => $company,
                        'background_image'  => $backgroundPath,
                        'start_date'        => $start,
                        'end_date'          => $end,
                        'city'              => $city,
                        'brand'             => $brandCode,
                        'serial_number'     => $serial,
                        'logo1'             => $logo1Path,
                        'logo2'             => $logo2Path,
                        'signature_image1'  => $sig1Path,
                        'signature_image2'  => $sig2Path,
                        'name_signatory1'   => $data['name_signatory1'],
                        'name_signatory2'   => $data['name_signatory2'] ?? null,
                        'role1'             => $data['role1'],
                        'role2'             => $data['role2'] ?? null,
                    ]);

                    // Simpan ke document_downloads supaya pemagang bisa lihat di Dokumen Saya
                    if ($ir->user_id) {
                        \App\Models\DocumentDownload::create([
                            'user_id'                    => $ir->user_id,
                            'internship_registration_id' => $ir->id,
                            'doc_type'                   => \App\Models\DocumentDownload::TYPE_SERTIFIKAT,
                            'file_path'                  => null,
                            'file_url'                   => route('admin.certificate.pdf', $cert->id),
                            'downloaded_at'              => now(),
                            'status'                     => 'success',
                        ]);
                    }
                }
            });

            return redirect()->route('admin.certificate.index')->with('success',
                '✅ Sertifikat untuk pemagang terpilih berhasil dibuat dan sudah tersedia di halaman Dokumen masing-masing pemagang.');
        }

        /** Map internship_interest → kode divisi (sinkron dengan InternApiController::search) */
        private function divisionFromInterest(string $interest): ?string
        {
            $map = [
                'administration'=>'ADM','administrasi'=>'ADM',
                'uiux'=>'UIUX','ui-ux'=>'UIUX','ui/ux'=>'UIUX',
                'programmer'=>'PROG','programmer (front end / backend)'=>'PROG',
                'hr'=>'HR','human resources (hr)'=>'HR',
                'social-media-specialist'=>'SMM','spesialis media sosial'=>'SMM',
                'photographer'=>'PV','videographer'=>'VID','fotografer'=>'PV','videografer'=>'VID',
                'content-writer'=>'CW','penulis konten'=>'CW',
                'marketing-and-sales'=>'MS','penjualan & pemasaran'=>'MS','penjualan dan pemasaran'=>'MS',
                'graphic-designer'=>'CD','desainer grafis'=>'CD',
                'digital-marketing'=>'DM','pemasaran digital'=>'DM',
                'public-relation'=>'PR','public relations (marcomm)'=>'PR','hubungan masyarakat (marcomm)'=>'PR',
                'tiktok-creator'=>'TC','kreator tiktok'=>'TC',
                'content-planner'=>'CP','perencana konten'=>'CP',
                'project-manager'=>'PM','manajer proyek'=>'PM',
                'welding'=>'LAS','pengelasan'=>'LAS',
                'animation'=>'ANIM','animasi'=>'ANIM',
            ];
            $key = Str::of($interest)->lower()->replace('/', '-')->toString();
            return $map[$key] ?? null;
        }





    public function downloadPdf(Certificate $certificate)
    {
        $brandMap = [
            'MJ'  => 'magangjogja',
            'AK'  => 'areakerja',
            'RW'  => 'republikweb',
            'TS'  => 'titipsini',
            'AP'  => 'ambilpaket',
            'BK'  => 'bikinkepo',
            'BC'  => 'bimbelcerdas.com',
            'LK'  => 'latihankerja.com',
            'LJT' => 'lowkerjateng.com',
            'LJG' => 'lowkerjogja.com',
            'PJ'  => 'pijatjogja.com',
            'SB'  => 'sayabantu.com',
            'TV'  => 'titikvisual',
            'TN'  => 'tuantanah',
            'TL'  => 'tukanglas.org',
            'AKI' => 'adakamar.id',
            'SI'  => 'seven Inc',
        ];

        $brandText = $brandMap[$certificate->brand] ?? $certificate->brand;
        $brandSlug = Str::slug($brandText, '-');
        $nameSlug  = Str::slug($certificate->name, '-');
        $pdfTitle  = "{$brandText}-{$certificate->name}";

        // ── Pilih template berdasarkan jenis sertifikat ──
        // Division 'WBN' = sertifikat webinar → pakai template khusus
        $isWebinar = $certificate->division === 'WBN';
        $viewName  = $isWebinar ? 'certificates.webinar-pdf' : 'certificates.pdf';
        $prefix    = $isWebinar ? 'sertifikat-webinar' : $brandSlug;
        $filename  = "{$prefix}-{$nameSlug}.pdf";

        $html = View::make($viewName, [
            'certificate' => $certificate,
            'pdfTitle'    => $pdfTitle,
        ])->render();

        $tmpPath = storage_path('app/tmp/' . $filename);
        if (!is_dir(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0775, true);
        }

        Browsershot::html($html)
            ->emulateMedia('print')
            ->format('A4')
            ->landscape()
            ->margins(0, 0, 0, 0)
            ->timeout(180)
            ->setOption('args', [
                '--no-sandbox',
                '--disable-setuid-sandbox',
            ])
            ->savePdf($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }


    public function index()
    {
        $certificates = Certificate::orderByDesc('id')->get();
        return view('certificates.index', compact('certificates'));
    }


    public function create()
    {

        $interns = IR::orderByDesc('id')
            ->select('id','fullname','start_date','end_date','current_city','internship_interest','institution_name')
            ->get();

        $internData = ($interns ?? collect())
            ->map(function ($ir) {
                return [
                    'id'          => $ir->id,
                    'fullname'    => $ir->fullname,
                    'start_date'  => (string) $ir->start_date,
                    'end_date'    => (string) $ir->end_date,
                    'city'        => $ir->current_city,
                    'interest'    => $ir->internship_interest,
                    'institution' => $ir->institution_name,
                ];
            })
            ->values()
            ->toArray();

        // List file dari storage (hanya nama file)
        $backgroundFiles = collect(Storage::files('public/images/backgrounds'))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => str_starts_with($f, 'bg_'))
            ->values();

        $logoFiles = collect(Storage::files('public/images/logos'))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => str_starts_with($f, 'logo_'))
            ->values();

        $signatureFiles = collect(Storage::files('public/images/signature'))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => str_starts_with($f, 'ttd_'))
            ->values();

        // Divisi (kode => label)
        $divisions = [
            'ADM'  => 'Administrasi',
            'UIUX' => 'UI/UX Designer',
            'PROG' => 'Programmer (Front end / Back end)',
            'HR'   => 'Human Resource',
            'SMM'  => 'Social Media Specialist',
            'PV'   => 'Photographer',
            'VID'  => 'Videographer',
            'CW'   => 'Content Writer',
            'MS'   => 'Marketing & Sales',
            'CD'   => 'Content Creative (Desain Grafis)',
            'DM'   => 'Digital Marketing',
            'PR'   => 'Marcom/Public Relations',
            'TC'   => 'Tik Tok Creator',
            'CP'   => 'Content Planner',
            'PM'   => 'Project Manager',
            'LAS'  => 'Las',
            'ANIM' => 'Animasi',
        ];

        // Brand (kode => label)
        $brands = [
            'MJ'  => 'Magangjogja',
            'AK'  => 'Areakerja',
            'RW'  => 'Republikweb',
            'TS'  => 'Titipsini',
            'AP'  => 'Ambilpaket',
            'BK'  => 'Bikinkepo',
            'BC'  => 'Bimbelcerdas.com',
            'LK'  => 'Latihankerja.com',
            'LJT' => 'Lowkerjateng.com',
            'LJG' => 'Lowkerjogja.com',
            'PJ'  => 'Pijatjogja.com',
            'SB'  => 'Sayabantu.com',
            'TV'  => 'Titikvisual',
            'TN'  => 'Tuantanah',
            'TL'  => 'Tukanglas.org',
            'AKI' => 'Adakamar.id',
            'SI'  => 'Seven Inc',
        ];

        return view('certificates.create', compact(
            'backgroundFiles', 'logoFiles', 'signatureFiles', 'divisions', 'brands', 'interns', 'internData'
        ));
    }



    public function store(Request $request)
    {
        // List kode yang valid
        $validDivisions = ['ADM','UIUX','PROG','HR','SMM','PV','CW','MS','CD','DM','PR','TC','CP','PM','LAS','ANIM'];
        $validBrands    = ['MJ','AK','RW','TS','AP','BK','BC','LK','LJT','LJG','PJ','SB','TV','TN','TL','AKI','SI'];

        // Validasi
        $request->validate([
            'name'             => ['required','string','max:255'],
            'division'         => ['required', Rule::in($validDivisions)],
            'company'          => ['required','string','max:255'],
            // dropdown file (bukan upload), wajib prefix tertentu
            'background_image' => ['required','string','starts_with:bg_'],
            'logo1'            => ['required','string','starts_with:logo_'],
            'logo2'            => ['nullable','string','starts_with:logo_'],
            'signature_image1' => ['required','string','starts_with:ttd_'],
            'signature_image2' => ['nullable','string','starts_with:ttd_'],

            'start_date'       => ['required','date','before_or_equal:end_date'],
            'end_date'         => ['required','date','after_or_equal:start_date'],

            'city'             => ['required','string','max:255'],
            'brand'            => ['required', Rule::in($validBrands)],
            // serial_number auto-generate
            'name_signatory1'  => ['required','string','max:255'],
            'name_signatory2'  => ['nullable','string','max:255'],
            'role1'            => ['required','string','max:255'],
            'role2'            => ['nullable','string','max:255'],
        ]);

        $data = $request->all();

        // Normalisasi path (disimpan relatif dari disk 'public')
        $backgroundPath = "images/backgrounds/{$data['background_image']}";
        $logo1Path      = "images/logos/{$data['logo1']}";
        $logo2Path      = $data['logo2'] ? "images/logos/{$data['logo2']}" : null;
        $sig1Path       = "images/signature/{$data['signature_image1']}";
        $sig2Path       = $data['signature_image2'] ? "images/signature/{$data['signature_image2']}" : null;

        // Pastikan file ada
        foreach ([$backgroundPath, $logo1Path, $sig1Path, $logo2Path, $sig2Path] as $checkPath) {
            if ($checkPath && !Storage::exists("public/{$checkPath}")) {
                return back()->withErrors(['file_missing' => "File tidak ditemukan: {$checkPath}"])->withInput();
            }
        }

        // Tanggal
        $startDate = Carbon::parse($data['start_date']);
        $endDate   = Carbon::parse($data['end_date']);

        // Map bulan ke romawi berdasarkan END DATE
        $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        $monthRoman = $roman[$endDate->month];
        $year       = $endDate->year;

        // Codes
        $companyCode  = $this->companyCode($data['company']); // contoh: "SEVEN"
        $brandCode    = strtoupper($data['brand']);
        $divisionCode = strtoupper($data['division']);

        // Running number 3 digit per bulan-tahun (reset tiap bulan)
        $last = Certificate::whereYear('created_at', $endDate->year)
                ->whereMonth('created_at', $endDate->month)
                ->orderByDesc('id')
                ->first();

        $seq = 1;
        if ($last && preg_match('/^(\d{3})\/SERT\//', $last->serial_number, $m)) {
            $seq = (int)$m[1] + 1;
        }
        $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);

        $serial = "{$seqStr}/SERT/{$divisionCode}/{$companyCode}.{$brandCode}/{$monthRoman}/{$year}";

        // Simpan
        Certificate::create([
            'name'              => $data['name'],
            'division'          => $data['division'],  // simpan kode divisi
            'company'           => $data['company'],
            'background_image'  => $backgroundPath,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'city'              => $data['city'],
            'brand'             => $brandCode,         // simpan kode brand
            'serial_number'     => $serial,
            'logo1'             => $logo1Path,
            'logo2'             => $logo2Path,
            'signature_image1'  => $sig1Path,
            'signature_image2'  => $sig2Path,
            'name_signatory1'   => $data['name_signatory1'],
            'name_signatory2'   => $data['name_signatory2'],
            'role1'             => $data['role1'],
            'role2'             => $data['role2'],
        ]);

        return redirect()->route('admin.certificate.index')->with('success', 'Sertifikat berhasil dibuat');
    }



    /**
     * Normalisasi kode company dari nama perusahaan.
     * - Buang PT/CV/CO/LTD/INC/TBK/PERSERO
     * - Ambil kata pertama, uppercase, alnum only
     */
    private function companyCode(string $company): string
    {
        $t = strtoupper($company);
        $t = preg_replace('/\b(PT|CV|CO\.?|LTD\.?|INC\.?|TBK|PERSERO)\b\.?/i', '', $t);
        $t = trim($t);
        $first = preg_split('/\s+/', $t)[0] ?? $t;
        $first = preg_replace('/[^A-Z0-9]/', '', $first);
        return $first ?: 'COMP';
    }



    public function show($id)
    {
        $certificate = Certificate::findOrFail($id);
        return view('certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        // Ambil file dari storage (hanya nama file) + filter prefix
        $backgroundFiles = collect(Storage::files('public/images/backgrounds'))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => Str::startsWith($f, 'bg_'))
            ->values();

        $logoFiles = collect(Storage::files('public/images/logos'))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => Str::startsWith($f, 'logo_'))
            ->values();

        $signatureFiles = collect(Storage::files('public/images/signature'))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => Str::startsWith($f, 'ttd_'))
            ->values();

        // Pastikan nilai yang tersimpan tetap ada di opsi (kalau file-nya sudah pindah/hilang dari folder)
        $savedBg   = basename($certificate->background_image ?? '');
        $savedL1   = basename($certificate->logo1 ?? '');
        $savedL2   = basename($certificate->logo2 ?? '');
        $savedTtd1 = basename($certificate->signature_image1 ?? '');
        $savedTtd2 = basename($certificate->signature_image2 ?? '');

        if ($savedBg && !$backgroundFiles->contains($savedBg))   $backgroundFiles->prepend($savedBg);
        if ($savedL1 && !$logoFiles->contains($savedL1))         $logoFiles->prepend($savedL1);
        if ($savedL2 && $savedL2 !== '' && !$logoFiles->contains($savedL2)) $logoFiles->prepend($savedL2);
        if ($savedTtd1 && !$signatureFiles->contains($savedTtd1)) $signatureFiles->prepend($savedTtd1);
        if ($savedTtd2 && $savedTtd2 !== '' && !$signatureFiles->contains($savedTtd2)) $signatureFiles->prepend($savedTtd2);

        // Divisi (kode => label)
        $divisions = [
            'ADM'  => 'Administrasi',
            'UIUX' => 'UI/UX Designer',
            'PROG' => 'Programmer (Front end / Back end)',
            'HR'   => 'Human Resource',
            'SMM'  => 'Social Media Specialist',
            'PV'   => 'Photographer',
            'VID'   => 'Videographer',
            'CW'   => 'Content Writer',
            'MS'   => 'Marketing & Sales',
            'CD'   => 'Content Creative (Desain Grafis)',
            'DM'   => 'Digital Marketing',
            'PR'   => 'Marcom/Public Relations',
            'TC'   => 'Tik Tok Creator',
            'CP'   => 'Content Planner',
            'PM'   => 'Project Manager',
            'LAS'  => 'Las',
            'ANIM' => 'Animasi',
        ];

        // Brand (kode => label)
        $brands = [
            'MJ'  => 'Magangjogja',
            'AK'  => 'Areakerja',
            'RW'  => 'Republikweb',
            'TS'  => 'Titipsini',
            'AP'  => 'Ambilpaket',
            'BK'  => 'Bikinkepo',
            'BC'  => 'Bimbelcerdas.com',
            'LK'  => 'Latihankerja.com',
            'LJT' => 'Lowkerjateng.com',
            'LJG' => 'Lowkerjogja.com',
            'PJ'  => 'Pijatjogja.com',
            'SB'  => 'Sayabantu.com',
            'TV'  => 'Titikvisual',
            'TN'  => 'Tuantanah',
            'TL'  => 'Tukanglas.org',
            'AKI' => 'Adakamar.id',
            'SI'  => 'Seven Inc',
        ];

        return view('certificates.edit', compact(
            'certificate', 'backgroundFiles', 'logoFiles', 'signatureFiles', 'divisions', 'brands'
        ));
    }

    private function getBrandCode($brand)
    {
        $brandCodes = [
            'Magangjogja'               => 'MJ',
            'Areakerja'                 => 'AK',
            'Republikweb'               => 'RW',
            'Titipsini'                 => 'TS',
            'Ambilpaket'                => 'AP',
            'Bikinkepo'                 => 'BK',
            'Bimbelcerdas.com'          => 'BC',
            'Latihankerja.com'          => 'LK',
            'Lowkerjateng.com'          => 'LJT',
            'Lowkerjogja.com'           => 'LJG',
            'Pijatjogja.com'            => 'PJ',
            'Sayabantu.com'             => 'SB',
            'Titikvisual'               => 'TV',
            'Tuantanah'                 => 'TN',
            'Tukanglas.org'             => 'TL',
            'Adakamar.id'               => 'AKI',
            'Seven Inc'                 => 'SI',
        ];

        return $brandCodes[$brand] ?? 'UNKNOWN';
    }


    public function update(Request $request, $id)
    {
        // Validasi form
        $request->validate([
            'name'              => 'required|string|max:255',
            'division'          => 'required|string|max:255',
            'company'           => 'required|string|max:255',
            'background_image'  => 'nullable|image|max:2048',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date',
            'city'              => 'required|string|max:255',
            'brand'             => 'required|string|max:255',
            'serial_number'     => 'required|string|max:255|unique:certificates,serial_number,' . $id,
            'logo1'             => 'nullable|image|max:2048',
            'logo2'             => 'nullable|image|max:2048',
            'signature_image1'  => 'nullable|image|max:2048',
            'signature_image2'  => 'nullable|image|max:2048',
            'name_signatory1'   => 'required|string|max:255',
            'name_signatory2'   => 'nullable|string|max:255',
            'role1'             => 'required|string|max:255',
            'role2'             => 'nullable|string|max:255',
        ]);

        // Ambil data sertifikat
        $certificate = Certificate::findOrFail($id);

        // Hapus file lama jika ada dan upload yang baru
        if ($request->hasFile('background_image')) {
            Storage::delete($certificate->background_image);
            $certificate->background_image = $request->file('background_image')->store('backgrounds');
        }
        if ($request->hasFile('logo1')) {
            Storage::delete($certificate->logo1);
            $certificate->logo1 = $request->file('logo1')->store('logos');
        }
        if ($request->hasFile('logo2')) {
            Storage::delete($certificate->logo2);
            $certificate->logo2 = $request->file('logo2')->store('logos');
        }
        if ($request->hasFile('signature_image1')) {
            Storage::delete($certificate->signature_image1);
            $certificate->signature_image1 = $request->file('signature_image1')->store('signatures');
        }
        if ($request->hasFile('signature_image2')) {
            Storage::delete($certificate->signature_image2);
            $certificate->signature_image2 = $request->file('signature_image2')->store('signatures');
        }

        // Update data sertifikat
        $certificate->update([
            'name'              => $request->name,
            'division'          => $request->division,
            'company'           => $request->company,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'city'              => $request->city,
            'brand'             => $request->brand,
            'serial_number'     => $request->serial_number,
            'name_signatory1'   => $request->name_signatory1,
            'name_signatory2'   => $request->name_signatory2,
            'role1'             => $request->role1,
            'role2'             => $request->role2,
        ]);

        return redirect()->route('admin.certificate.index')->with('success', 'Sertifikat berhasil diupdate');
    }

    /**
     * Upload helper umum: validasi mime/size, auto-rename dengan prefix, simpan ke disk public
     */
    private function uploadCommon(Request $request, string $prefix, string $subdir)
    {
        $request->validate([
            'file' => ['required','file','mimes:png,jpg,jpeg,webp','max:2048'], // max 2MB
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());

        // Ambil nama dasar tanpa ekstensi, lalu slug pakai underscore
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($base, '_');
        // Buang prefix jika user sudah menamai dengan bg_/logo_/ttd_
        $slug = preg_replace('/^(bg_|logo_|ttd_)/i', '', $slug);

        $timestamp = now()->format('Ymd_His');
        $filename  = $prefix.$slug.'_'.$timestamp.'.'.$ext;

        $dir = "public/images/{$subdir}"; // contoh: public/images/backgrounds
        // Pastikan folder ada (umumnya sudah ada pada disk public). Jika belum, buat.
        if (!Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }

        $file->storeAs($dir, $filename);

        return back()->with('success', "File diunggah: {$filename}");
    }

    /** Upload Background → simpan ke storage/app/public/images/backgrounds, prefix bg_ */
    public function uploadBackground(Request $request)
    {
        return $this->uploadCommon($request, 'bg_', 'backgrounds');
    }

    /** Upload Logo → simpan ke storage/app/public/images/logos, prefix logo_ */
    public function uploadLogo(Request $request)
    {
        return $this->uploadCommon($request, 'logo_', 'logos');
    }

    /** Upload Tanda Tangan → simpan ke storage/app/public/images/signature, prefix ttd_ */
    public function uploadSignature(Request $request)
    {
        return $this->uploadCommon($request, 'ttd_', 'signature');
    }


    public function destroy($id)
    {
        // Hapus sertifikat
        $certificate = Certificate::findOrFail($id);

        // Pastikan file ada sebelum mencoba menghapusnya
        if (!empty($certificate->background_image) && Storage::exists($certificate->background_image)) {
            Storage::delete($certificate->background_image);
        }

        if (!empty($certificate->logo1) && Storage::exists($certificate->logo1)) {
            Storage::delete($certificate->logo1);
        }

        if (!empty($certificate->logo2) && Storage::exists($certificate->logo2)) {
            Storage::delete($certificate->logo2);
        }

        if (!empty($certificate->signature_image1) && Storage::exists($certificate->signature_image1)) {
            Storage::delete($certificate->signature_image1);
        }

        if (!empty($certificate->signature_image2) && Storage::exists($certificate->signature_image2)) {
            Storage::delete($certificate->signature_image2);
        }

        // Hapus data sertifikat dari database
        $certificate->delete();

        return redirect()->route('admin.certificate.index')->with('success', 'Sertifikat berhasil dihapus');
    }



    // ======= BULK CREATE (via Interns) =======
    public function bulkCreateInterns()
    {
        // Dropdown file (nama file saja)
        [$backgroundFiles, $logoFiles, $signatureFiles] = $this->loadAssetOptions();

        // Brand list (kode => label) – sama seperti create()
        $brands = [
            'MJ'=>'Magangjogja','AK'=>'Areakerja','RW'=>'Republikweb','TS'=>'Titipsini','AP'=>'Ambilpaket',
            'BK'=>'Bikinkepo','BC'=>'Bimbelcerdas.com','LK'=>'Latihankerja.com','LJT'=>'Lowkerjateng.com',
            'LJG'=>'Lowkerjogja.com','PJ'=>'Pijatjogja.com','SB'=>'Sayabantu.com','TV'=>'Titikvisual',
            'TN'=>'Tuantanah','TL'=>'Tukanglas.org','AKI'=>'Adakamar.id','SI'=>'Seven Inc',
        ];

        return view('certificates.bulk_interns', compact('backgroundFiles','logoFiles','signatureFiles','brands'));
    }

    public function bulkStoreInterns(Request $request)
    {
        // Valid common fields
        $validBrands = ['MJ','AK','RW','TS','AP','BK','BC','LK','LJT','LJG','PJ','SB','TV','TN','TL','AKI','SI'];

        $request->validate([
            // peserta
            'intern_ids'        => ['required','array','min:1'],
            'intern_ids.*'      => ['integer'],
            // common
            'company'           => ['required','string','max:255'],
            'city'              => ['required','string','max:255'],
            'brand'             => ['required', Rule::in($validBrands)],
            'start_date'        => ['required','date','before_or_equal:end_date'],
            'end_date'          => ['required','date','after_or_equal:start_date'],
            'background_image'  => ['required','string','starts_with:bg_'],
            'logo1'             => ['required','string','starts_with:logo_'],
            'logo2'             => ['nullable','string','starts_with:logo_'],
            'signature_image1'  => ['required','string','starts_with:ttd_'],
            'signature_image2'  => ['nullable','string','starts_with:ttd_'],
            'name_signatory1'   => ['required','string','max:255'],
            'name_signatory2'   => ['nullable','string','max:255'],
            'role1'             => ['required','string','max:255'],
            'role2'             => ['nullable','string','max:255'],
        ]);

        $data = $request->all();

        // Normalisasi path
        $backgroundPath = "images/backgrounds/{$data['background_image']}";
        $logo1Path      = "images/logos/{$data['logo1']}";
        $logo2Path      = !empty($data['logo2']) ? "images/logos/{$data['logo2']}" : null;
        $sig1Path       = "images/signature/{$data['signature_image1']}";
        $sig2Path       = !empty($data['signature_image2']) ? "images/signature/{$data['signature_image2']}" : null;

        // Cek file exist di disk public
        foreach ([$backgroundPath,$logo1Path,$sig1Path,$logo2Path,$sig2Path] as $p) {
            if ($p && !Storage::exists("public/{$p}")) {
                return back()->withErrors(['file_missing'=>"File tidak ditemukan: {$p}"])->withInput();
            }
        }

        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate   = \Carbon\Carbon::parse($data['end_date']);
        $brandCode = strtoupper($data['brand']);
        $companyCode = $this->companyCode($data['company']);

        // Roman & tahun
        $monthRoman = $this->monthRoman($endDate->month);
        $year       = $endDate->year;

        // Ambil starting sequence untuk bulan-tahun tsb
        $seq = $this->startingSequence($endDate);

        // Ambil interns
        // NOTE: sesuaikan model namespace kalau beda (App\Models\Intern)
        $interns = \App\Models\InternshipRegistration::whereIn('id', $data['intern_ids'])->get();

        $created = 0;
        foreach ($interns as $intern) {
            // Ambil kode divisi dari intern (diasumsikan kolom 'division' berisi kode seperti ADM, PROG, dst.)
            $divisionCode = strtoupper($intern->division ?? 'EXT'); // fallback EXT kalau kosong

            $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
            $serial = "{$seqStr}/SERT/{$divisionCode}/{$companyCode}.{$brandCode}/{$monthRoman}/{$year}";
            $seq++; // increment untuk berikutnya

            \App\Models\Certificate::create([
                'name'              => $intern->name,
                'division'          => $divisionCode,
                'company'           => $data['company'],
                'background_image'  => $backgroundPath,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'city'              => $data['city'],
                'brand'             => $brandCode,
                'serial_number'     => $serial,
                'logo1'             => $logo1Path,
                'logo2'             => $logo2Path,
                'signature_image1'  => $sig1Path,
                'signature_image2'  => $sig2Path,
                'name_signatory1'   => $data['name_signatory1'],
                'name_signatory2'   => $data['name_signatory2'] ?? null,
                'role1'             => $data['role1'],
                'role2'             => $data['role2'] ?? null,
            ]);

            $created++;
        }

        return redirect()->route('admin.certificate.index')->with('success', "Berhasil membuat {$created} sertifikat (via interns).");
    }

    // ======= BULK CREATE (External / Non-Interns) =======
    public function bulkCreateExternal()
    {
        [$backgroundFiles, $logoFiles, $signatureFiles] = $this->loadAssetOptions();

        $brands = [
            'MJ'=>'Magangjogja','AK'=>'Areakerja','RW'=>'Republikweb','TS'=>'Titipsini','AP'=>'Ambilpaket',
            'BK'=>'Bikinkepo','BC'=>'Bimbelcerdas.com','LK'=>'Latihankerja.com','LJT'=>'Lowkerjateng.com',
            'LJG'=>'Lowkerjogja.com','PJ'=>'Pijatjogja.com','SB'=>'Sayabantu.com','TV'=>'Titikvisual',
            'TN'=>'Tuantanah','TL'=>'Tukanglas.org','AKI'=>'Adakamar.id','SI'=>'Seven Inc',
        ];

        return view('certificates.bulk_external', compact('backgroundFiles','logoFiles','signatureFiles','brands'));
    }

    public function bulkStoreExternal(Request $request)
    {
        $validBrands = ['MJ','AK','RW','TS','AP','BK','BC','LK','LJT','LJG','PJ','SB','TV','TN','TL','AKI','SI'];

        $request->validate([
            'names'             => ['required','array','min:1'],
            'names.*'           => ['required','string','max:255'],
            'company'           => ['required','string','max:255'],
            'city'              => ['required','string','max:255'],
            'brand'             => ['required', Rule::in($validBrands)],
            'start_date'        => ['required','date','before_or_equal:end_date'],
            'end_date'          => ['required','date','after_or_equal:start_date'],
            'background_image'  => ['required','string','starts_with:bg_'],
            'logo1'             => ['required','string','starts_with:logo_'],
            'logo2'             => ['nullable','string','starts_with:logo_'],
            'signature_image1'  => ['required','string','starts_with:ttd_'],
            'signature_image2'  => ['nullable','string','starts_with:ttd_'],
            'name_signatory1'   => ['required','string','max:255'],
            'name_signatory2'   => ['nullable','string','max:255'],
            'role1'             => ['required','string','max:255'],
            'role2'             => ['nullable','string','max:255'],
        ]);

        $data = $request->all();

        // Paths
        $backgroundPath = "images/backgrounds/{$data['background_image']}";
        $logo1Path      = "images/logos/{$data['logo1']}";
        $logo2Path      = !empty($data['logo2']) ? "images/logos/{$data['logo2']}" : null;
        $sig1Path       = "images/signature/{$data['signature_image1']}";
        $sig2Path       = !empty($data['signature_image2']) ? "images/signature/{$data['signature_image2']}" : null;

        foreach ([$backgroundPath,$logo1Path,$sig1Path,$logo2Path,$sig2Path] as $p) {
            if ($p && !Storage::exists("public/{$p}")) {
                return back()->withErrors(['file_missing'=>"File tidak ditemukan: {$p}"])->withInput();
            }
        }

        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate   = \Carbon\Carbon::parse($data['end_date']);
        $brandCode = strtoupper($data['brand']);
        $companyCode = $this->companyCode($data['company']);
        $monthRoman = $this->monthRoman($endDate->month);
        $year       = $endDate->year;

        // Untuk external: divisinya DIHILANGKAN di form → kita pakai konstanta 'EXT'
        $divisionCode = 'EXT';

        $seq = $this->startingSequence($endDate);

        $created = 0;
        foreach ($data['names'] as $nm) {
            $name = trim($nm);
            if ($name === '') continue;

            $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
            $segments = [$seqStr, 'SERT'];
            if (strtoupper($divisionCode) !== 'EXT') {
                $segments[] = strtoupper($divisionCode);
            }
            $segments[] = "{$companyCode}.{$brandCode}";
            $segments[] = $monthRoman;
            $segments[] = $year;

            $serial = implode('/', $segments);
            $seq++;

            \App\Models\Certificate::create([
                'name'              => $name,
                'division'          => $divisionCode,
                'company'           => $data['company'],
                'background_image'  => $backgroundPath,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'city'              => $data['city'],
                'brand'             => $brandCode,
                'serial_number'     => $serial,
                'logo1'             => $logo1Path,
                'logo2'             => $logo2Path,
                'signature_image1'  => $sig1Path,
                'signature_image2'  => $sig2Path,
                'name_signatory1'   => $data['name_signatory1'],
                'name_signatory2'   => $data['name_signatory2'] ?? null,
                'role1'             => $data['role1'],
                'role2'             => $data['role2'] ?? null,
            ]);

            $created++;
        }

        return redirect()->route('admin.certificate.index')->with('success', "Berhasil membuat {$created} sertifikat (external).");
    }


    // ======= Helpers (private) =======
    private function loadAssetOptions(): array
    {
        $backgroundFiles = collect(Storage::files('storage/app/public/images/backgrounds'))
            ->map(fn($f)=>basename($f))->filter(fn($f)=>str_starts_with($f,'bg_'))->values();

        $logoFiles = collect(Storage::files('storage/app/public/images/logos'))
            ->map(fn($f)=>basename($f))->filter(fn($f)=>str_starts_with($f,'logo_'))->values();

        $signatureFiles = collect(Storage::files('storage/app/public/images/signature'))
            ->map(fn($f)=>basename($f))->filter(fn($f)=>str_starts_with($f,'ttd_'))->values();

        return [$backgroundFiles,$logoFiles,$signatureFiles];
    }

    private function monthRoman(int $m): string
    {
        $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        return $roman[$m] ?? '-';
    }

    private function startingSequence(\Carbon\Carbon $endDate): int
    {
        $last = \App\Models\Certificate::whereYear('created_at', $endDate->year)
            ->whereMonth('created_at', $endDate->month)
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last && preg_match('/^(\d{3})\/SERT\//', $last->serial_number, $m)) {
            $seq = (int)$m[1] + 1;
        }
        return $seq;
    }




}
