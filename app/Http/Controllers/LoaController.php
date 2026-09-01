<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentDownload;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InternshipRegistration as IR;
use App\Models\LoaSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoaController extends Controller
{
    public function edit()
    {
        $loaSettings = LoaSettings::first();

        // Ambil daftar brand yang punya pemagang accepted & belum punya LOA
        $brands = IR::query()
            ->where('internship_status', IR::STATUS_ACCEPTED)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->whereNotIn('id', function ($q) {
                $q->select('internship_registration_id')
                  ->from('document_downloads')
                  ->where('doc_type', DocumentDownload::TYPE_LOA)
                  ->whereNotNull('internship_registration_id');
            })
            ->distinct()
            ->pluck('brand')
            ->sort()
            ->values();

        return view('admin.loa_editor', compact('loaSettings', 'brands'));
    }

    public function update(Request $request)
    {
        $loaSettings = LoaSettings::firstOrCreate([]);

        $data = $request->only([
            'company_name','company_contact_email','signatory_name','signatory_position',
            'header_text','footer_text'
        ]);

        // upload optional: logo_path & stamp_path
        if ($request->hasFile('logo_path')) {
            $data['logo_path'] = $request->file('logo_path')->store('images/logos','public');
        }
        if ($request->hasFile('stamp_path')) {
            $data['stamp_path'] = $request->file('stamp_path')->store('images/signature','public');
        }

        $loaSettings->update($data);

        return redirect()->route('admin.loa.editor')->with('success', 'LOA Settings updated successfully');
    }

    /**
     * GET /admin/loa/interns-by-brand?brand=XXX
     * API: ambil pemagang accepted yang belum punya LOA untuk brand tertentu
     */
    public function getInternsByBrand(Request $request)
    {
        $brand = $request->query('brand');

        if (!$brand) {
            return response()->json(['interns' => []]);
        }

        // ID pemagang yang sudah punya LOA
        $alreadyHasLoa = DocumentDownload::where('doc_type', DocumentDownload::TYPE_LOA)
            ->whereNotNull('internship_registration_id')
            ->pluck('internship_registration_id')
            ->toArray();

        $interns = IR::query()
            ->where('internship_status', IR::STATUS_ACCEPTED)
            ->where('brand', $brand)
            ->whereNotIn('id', $alreadyHasLoa)
            ->select('id', 'fullname', 'student_id', 'study_program', 'institution_name', 'start_date', 'end_date', 'phone_number')
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
                'phone_number'     => $r->phone_number ?? '',
            ]);

        return response()->json(['interns' => $interns]);
    }

    /**
     * POST /admin/loa/generate-brand
     * Generate LOA untuk setiap intern yang dipilih (1 PDF per pemagang), dikemas dalam ZIP
     */
    public function generateForBrand(Request $request)
    {
        $validated = $request->validate([
            'intern_ids'         => ['required', 'array', 'min:1'],
            'intern_ids.*'       => ['integer', 'exists:internship_registrations,id'],
            'signatory_name'     => ['required', 'string', 'max:255'],
            'signatory_position' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        $interns = IR::whereIn('id', $validated['intern_ids'])->get();

        if ($interns->isEmpty()) {
            return back()->with('error', 'Data pemagang tidak ditemukan.');
        }

        $loaSettings = LoaSettings::firstOrNew([]);

        // Override dengan input dari form
        $loaSettings = clone $loaSettings;
        $loaSettings->signatory_name     = $validated['signatory_name'];
        $loaSettings->signatory_position = $validated['signatory_position'];

        if ($request->filled('company_contact_email')) {
            $loaSettings->company_contact_email = $request->input('company_contact_email');
        }

        // Logo: pakai upload baru atau fallback ke saved setting atau default
        $logoData  = $this->resolveBase64Image($request, 'logo_upload',  $loaSettings->logo_path,  'images/logos/logo_seveninc.png');
        $stampData = $this->resolveBase64Image($request, 'stamp_upload', $loaSettings->stamp_path, 'images/signature/ttd_arisetiahusbana.png');

        $dir = 'documents/loa';
        $this->ensurePublicDir($dir);

        $generatedFiles = [];
        $errors         = [];

        foreach ($interns as $intern) {
            try {
                // Brand → nama perusahaan untuk surat ini
                $loaSettings->company_name = $intern->brand ?: ($loaSettings->company_name ?? 'Seven Inc');

                $rows = $this->buildRows([$intern]);

                $pdf = Pdf::loadView('user.loa', [
                    'intern'          => $intern,
                    'user'            => $user,
                    'loaSettings'     => $loaSettings,
                    'rows'            => $rows,
                    'openingGreeting' => $request->input('opening_greeting', 'Dengan ini kami mengonfirmasi bahwa pendaftar di bawah ini telah diterima untuk mengikuti program magang.'),
                    'closingGreeting' => $request->input('closing_greeting', 'Harap konfirmasi kehadiran Anda melalui email atau telepon yang tertera.'),
                    'logoData'        => $logoData,
                    'stampData'       => $stampData,
                ])->setPaper('A4', 'portrait');

                $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true]);

                $safeName = Str::slug($intern->fullname ?? 'intern', '-');
                $fileName = 'LOA-' . $intern->id . '-' . $safeName . '-' . now()->format('Ymd_His') . '.pdf';
                $path     = $dir . '/' . $fileName;

                Storage::disk('public')->put($path, $pdf->output());
                $publicUrl = asset('storage/' . $path);

                // Simpan record ke document_downloads (target = user pemagang)
                $targetUserId = $intern->user_id ?? $user->id;

                DocumentDownload::create([
                    'user_id'                    => $targetUserId,
                    'internship_registration_id' => $intern->id,
                    'doc_type'                   => DocumentDownload::TYPE_LOA,
                    'file_path'                  => $path,
                    'file_url'                   => $publicUrl,
                    'downloaded_at'              => now(),
                    'ip_address'                 => $request->ip(),
                    'user_agent'                 => $request->userAgent(),
                    'status'                     => 'success',
                ]);

                $generatedFiles[$intern->id] = [
                    'fullname' => $intern->fullname,
                    'path'     => storage_path("app/public/{$path}"),
                    'filename' => $fileName,
                ];

            } catch (\Throwable $e) {
                Log::error('Gagal generate LOA (brand)', ['err' => $e->getMessage(), 'intern_id' => $intern->id]);
                $errors[] = $intern->fullname;
            }
        }

        if (empty($generatedFiles)) {
            return back()->with('error', 'Gagal membuat LOA. Silakan coba lagi.');
        }

        // Kalau hanya 1 pemagang → langsung download PDF
        if (count($generatedFiles) === 1) {
            $file = reset($generatedFiles);
            $errMsg = !empty($errors) ? ' (Gagal: ' . implode(', ', $errors) . ')' : '';
            return response()->download($file['path'], $file['filename'])->deleteFileAfterSend(false);
        }

        // Multiple → kemas ke ZIP
        $zipName = 'LOA-BATCH-' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path("app/public/documents/loa/{$zipName}");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file ZIP.');
        }

        foreach ($generatedFiles as $file) {
            if (file_exists($file['path'])) {
                $zip->addFile($file['path'], $file['filename']);
            }
        }
        $zip->close();

        $successMsg = '✅ LOA untuk <strong>' . count($generatedFiles) . ' pemagang</strong> berhasil dibuat dan sudah tersimpan.';
        if (!empty($errors)) {
            $successMsg .= ' <span class="text-red-600">Gagal: ' . implode(', ', $errors) . '</span>';
        }

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(false);
    }

    /**
     * Resolve gambar: dari upload baru → dari path tersimpan → dari fallback default
     * Kembalikan base64 data URI atau null
     */
    protected function resolveBase64Image(Request $request, string $inputName, ?string $savedPath, string $fallbackRelative): ?string
    {
        // 1. Upload baru dari form
        if ($request->hasFile($inputName) && $request->file($inputName)->isValid()) {
            $file = $request->file($inputName);
            $mime = $file->getMimeType();
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        // 2. Path tersimpan di settings
        if ($savedPath) {
            $fullPath = storage_path('app/public/' . $savedPath);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath);
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        // 3. Fallback default
        $fallbackPath = storage_path('app/public/' . $fallbackRelative);
        if (file_exists($fallbackPath)) {
            $mime = mime_content_type($fallbackPath);
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fallbackPath));
        }

        return null;
    }

    /**
     * GET /admin/loa/generate/{intern}
     * Form review sebelum generate LOA
     */
    public function generateForm(Request $request, IR $intern)
    {
        $loaSettings = LoaSettings::first();

        // Override company_name dengan brand pemagang
        $companyName = $intern->brand ?: ($loaSettings?->company_name ?? 'Seven Inc');

        $signatoryName     = $loaSettings?->signatory_name     ?? 'Ari Setia Husbana';
        $signatoryPosition = $loaSettings?->signatory_position ?? 'HRD';
        $openingGreeting   = $loaSettings?->header_text        ?? 'Dengan ini kami mengonfirmasi bahwa pendaftar di bawah ini telah diterima untuk mengikuti program magang.';
        $closingGreeting   = $loaSettings?->footer_text        ?? 'Harap konfirmasi kehadiran Anda melalui email atau telepon yang tertera.';

        return view('admin.loa_generate_form', compact(
            'intern',
            'loaSettings',
            'companyName',
            'signatoryName',
            'signatoryPosition',
            'openingGreeting',
            'closingGreeting'
        ));
    }

    /**
     * Generate LOA untuk single intern (PDF)
     */
    public function generate(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'intern_id' => ['required', 'integer', 'exists:internship_registrations,id'],
        ]);

        $user = $request->user();

        // Admin bisa generate LOA untuk intern siapapun
        // Pemagang hanya bisa download file yang sudah di-generate admin
        if ($user->role === 'admin') {
            $intern = IR::where('id', $validated['intern_id'])->firstOrFail();
        } else {
            $intern = IR::where('id', $validated['intern_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
            // Pastikan status memenuhi syarat
            $this->ensureCanAccessCompletedDocs($user, $intern);

            // Pemagang: cari file LOA yang sudah di-generate admin
            $record = DocumentDownload::where(function($q) use ($user, $intern) {
                    $q->where('user_id', $user->id)
                      ->orWhere('internship_registration_id', $intern->id);
                })
                ->where('doc_type', DocumentDownload::TYPE_LOA)
                ->whereNotNull('file_path')
                ->where('status', 'success')
                ->latest('downloaded_at')
                ->first();

            if (!$record) {
                return back()->with('error', 'LOA belum tersedia. Hubungi admin untuk mendapatkan LOA Anda.');
            }

            $fullPath = storage_path('app/public/' . $record->file_path);
            if (!file_exists($fullPath)) {
                return back()->with('error', 'File LOA tidak ditemukan. Hubungi admin.');
            }

            $safeName = \Illuminate\Support\Str::slug($intern->fullname ?? $user->name, '-');
            return response()->download($fullPath, "LOA-{$safeName}.pdf", ['Content-Type' => 'application/pdf']);
        }

        // === Mulai dari sini: hanya admin ===

        // Get the LOA settings (e.g., logo, signature)
        $loaSettings = LoaSettings::first();

        // Override dari form generate (jika admin datang dari loa_generate_form)
        if ($loaSettings) {
            $loaSettings = clone $loaSettings;
        } else {
            $loaSettings = new LoaSettings();
        }

        // Override nama perusahaan: prioritas form > brand > setting
        $loaSettings->company_name = $request->input('company_name_display')
            ?: ($intern->brand ?: ($loaSettings->company_name ?? 'Seven Inc'));

        // Override penandatangan jika dikirim dari form
        if ($request->filled('signatory_name')) {
            $loaSettings->signatory_name = $request->input('signatory_name');
        }
        if ($request->filled('signatory_position')) {
            $loaSettings->signatory_position = $request->input('signatory_position');
        }
        if ($request->filled('contact_email')) {
            $loaSettings->company_contact_email = $request->input('contact_email');
        }

        // Kalau admin tidak isi kolom manual, langsung pakai data dari registrasi
        $loaNamaSiswa = $request->input('loa_nama_siswa', []);

        if (empty($loaNamaSiswa)) {
            // Tidak ada input manual → pakai buildRows() langsung
            $rows = $this->buildRows([$intern]);
        } else {
            // Ada input manual dari form → merge dengan data registrasi
            $loaNimNis    = $request->input('loa_nim_nis', []);
            $loaJurusan   = $request->input('loa_jurusan', []);
            $loaInstansi  = $request->input('loa_instansi', []);
            $loaPeriode   = $request->input('loa_periode', []);
            $loaKontak    = $request->input('loa_kontak', []);

            $loaNamaSiswa = $this->autoFillData($loaNamaSiswa, $intern->fullname);
            $loaNimNis    = $this->autoFillData($loaNimNis,    $intern->student_id);
            $loaJurusan   = $this->autoFillData($loaJurusan,   $intern->study_program);
            $loaInstansi  = $this->autoFillData($loaInstansi,  $intern->institution_name);
            $loaPeriode   = $this->autoFillData($loaPeriode,
                Carbon::parse($intern->start_date)->format('d F Y') . ' - ' .
                Carbon::parse($intern->end_date)->format('d F Y')
            );
            $loaKontak = $this->autoFillData($loaKontak, $intern->phone_number);

            $rows = array_map(function ($index) use (
                $loaNamaSiswa, $loaNimNis, $loaJurusan, $loaInstansi, $loaPeriode, $loaKontak
            ) {
                return [
                    'nama_siswa' => $loaNamaSiswa[$index] ?? '',
                    'nim_nis'    => $loaNimNis[$index]    ?? '',
                    'jurusan'    => $loaJurusan[$index]   ?? '',
                    'instansi'   => $loaInstansi[$index]  ?? '',
                    'periode'    => $loaPeriode[$index]   ?? '',
                    'kontak'     => $loaKontak[$index]    ?? '',
                ];
            }, array_keys($loaNamaSiswa));
        }

        try {
            // Read the image files and encode them to base64
            $logoFile = storage_path('app/public/images/logos/logo_seveninc.png');
            $logoData = file_exists($logoFile)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
                : null;

            $stampFile = storage_path('app/public/images/signature/ttd_arisetiahusbana.png');
            // fallback ke file TTD lain yang ada jika ttd_arisetiahusbana.png tidak ditemukan
            if (!file_exists($stampFile)) {
                $candidates = glob(storage_path('app/public/images/signature/*.{png,jpg,jpeg}'), GLOB_BRACE);
                $stampFile = !empty($candidates) ? $candidates[0] : null;
            }
            $stampData = $stampFile && file_exists($stampFile)
                ? 'data:image/' . (str_ends_with($stampFile, '.png') ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($stampFile))
                : null;

            // Generate the PDF from the view, passing the required data
            $pdf = Pdf::loadView('user.loa', [
                'intern' => $intern,
                'user' => $user,
                'loaSettings' => $loaSettings,
                'rows' => $rows,
                'openingGreeting' => $request->input('openingGreeting'),
                'closingGreeting' => $request->input('closingGreeting'),
                'logoData' => $logoData,
                'stampData' => $stampData,
            ])->setPaper('A4', 'portrait');

            $pdf->setOptions([
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'defaultPaperSize' => 'A4',
            ]);

            // Create the file name for the generated PDF
            $safeName = Str::slug($intern->fullname ?? $user->name, '-');
            $fileName = 'LOA-' . $intern->id . '-' . $safeName . '-' . now()->format('Ymd_His') . '.pdf';

            // Define the directory to store the PDF
            $dir = 'documents/loa';
            $this->ensurePublicDir($dir); // Ensure directory exists
            $path = $dir . '/' . $fileName;

            // Store the generated PDF in the public disk
            Storage::disk('public')->put($path, $pdf->output());
            $publicUrl = asset('storage/' . $path);

            // Log the document download
            // Kalau admin yang generate → simpan dengan user_id PEMAGANG supaya masuk ke dokumen pemagang
            $targetUserId = ($user->role === 'admin' && $intern->user_id)
                ? $intern->user_id
                : $user->id;

            DocumentDownload::create([
                'user_id'                    => $targetUserId,
                'internship_registration_id' => $intern->id,
                'doc_type'                   => DocumentDownload::TYPE_LOA,
                'file_path'                  => $path,
                'file_url'                   => $publicUrl,
                'downloaded_at'              => now(),
                'ip_address'                 => $request->ip(),
                'user_agent'                 => $request->userAgent(),
                'status'                     => 'success',
            ]);

            // Return the PDF for download
            // Kalau admin dari form generate → redirect ke Data Pemagang dengan notif + link
            if ($user->role === 'admin') {
                $serveUrl = route('admin.documents.serve', [
                    'type'     => 'loa',
                    'filename' => basename($path),
                ]);
                // Cek apakah request datang dari form generate (ada signatory_name)
                if ($request->filled('signatory_name') || $request->filled('openingGreeting')) {
                    return redirect()->route('admin.interns.pemagang')
                        ->with('success', "✅ LOA untuk <strong>{$intern->fullname}</strong> berhasil dibuat. <a href=\"{$serveUrl}\" target=\"_blank\" class=\"underline font-bold\">Buka PDF →</a>");
                }
                return back()->with('success',
                    "✅ LOA untuk <strong>{$intern->fullname}</strong> berhasil dibuat dan sudah tersedia di halaman Dokumen pemagang."
                );
            }

            return response()->download(storage_path("app/public/{$path}"));
        } catch (\Throwable $e) {
            // Handle error and log the exception
            Log::error('Gagal generate LOA (single)', [
                'err' => $e->getMessage(),
                'intern_id' => $intern->id,
                'user_id' => $user->id,
            ]);
            return back()->with('error', 'Gagal membuat LOA. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Function to auto-fill data if input is empty
     */
    protected function autoFillData($data, $defaultValue)
    {
        // If the input is empty, return an array filled with the default value
        return array_map(function ($item) use ($defaultValue) {
            return $item ?: $defaultValue;
        }, $data);
    }





    /**
     * Generate LOA untuk multiple interns (sesuai revisi mentor) (PDF)
     */
    public function generateBatch(Request $request)
    {
        $validated = $request->validate([
            'intern_ids' => ['required','array','min:1'],
            'intern_ids.*' => ['integer','exists:internship_registrations,id'],
        ]);

        $user = $request->user();

        // Admin bisa generate LOA untuk intern siapapun
        // Pemagang hanya bisa untuk intern miliknya sendiri
        if ($user->role === 'admin') {
            $interns = IR::whereIn('id', $validated['intern_ids'])->get();
        } else {
            $interns = IR::whereIn('id', $validated['intern_ids'])
                ->where('user_id', $user->id)
                ->get();
        }

        if ($interns->isEmpty()) {
            return back()->with('error', 'Data pemagang tidak ditemukan atau tidak berhak diakses.');
        }

        // Pemagang: pastikan semuanya completed & milik user
        if ($user->role !== 'admin') {
            foreach ($interns as $intern) {
                $this->ensureCanAccessCompletedDocs($user, $intern);
            }
        }

        $loaSettings = LoaSettings::first();
        $rows = $this->buildRows($interns);

        try {
            $pdf = Pdf::loadView('user.loa', [
                'intern' => null,
                'user' => $user,
                'loaSettings' => $loaSettings,
                'rows' => $rows,
                'openingGreeting' => $request->input('openingGreeting'),
                'closingGreeting' => $request->input('closingGreeting'),
                'logoBase64' => $this->maybeToPublicUrlOrAsset($loaSettings?->logo_path, 'storage/images/logos/logo_seveninc.png'),
                'stampData' => $this->maybeToPublicUrlOrAsset($loaSettings?->stamp_path, 'storage/images/signature/ttd_arisetiahusbana.png'),
            ])->setPaper('A4', 'portrait');

            $pdf->setOptions(['isRemoteEnabled' => true]);

            $fileName = 'LOA-BATCH-' . now()->format('Ymd_His') . '.pdf';
            $dir = 'documents/loa';
            $this->ensurePublicDir($dir);
            $path = $dir . '/' . $fileName;

            Storage::disk('public')->put($path, $pdf->output());
            $publicUrl = asset('storage/' . $path);

            // Simpan record ke document_downloads untuk setiap intern secara individual
            foreach ($interns as $intern) {
                $targetUserId = ($user->role === 'admin' && $intern->user_id)
                    ? $intern->user_id
                    : $user->id;

                DocumentDownload::create([
                    'user_id'                    => $targetUserId,
                    'internship_registration_id' => $intern->id,
                    'doc_type'                   => DocumentDownload::TYPE_LOA,
                    'file_path'                  => $path,
                    'file_url'                   => $publicUrl,
                    'downloaded_at'              => now(),
                    'ip_address'                 => $request->ip(),
                    'user_agent'                 => $request->userAgent(),
                    'status'                     => 'success',
                ]);
            }

            // Admin → simpan saja, jangan download
            if ($user->role === 'admin') {
                $names = $interns->pluck('fullname')->implode(', ');
                return back()->with('success',
                    "✅ LOA untuk <strong>{$interns->count()} pemagang</strong> ({$names}) berhasil dibuat dan sudah tersedia di halaman Dokumen masing-masing pemagang."
                );
            }

            return response()->download(storage_path("app/public/{$path}"));
        } catch (\Throwable $e) {
            Log::error('Gagal generate LOA (batch)', [
                'err' => $e->getMessage(),
                'intern_ids' => $validated['intern_ids'],
                'user_id' => $user->id,
            ]);
            return back()->with('error', 'Gagal membuat LOA batch. Silakan coba lagi.');
        }
    }

    // Preview cepat (tanpa fetch data)
    public function preview(Request $request)
    {
        $loaSettings = LoaSettings::first();
        return view('user.loa', [
            'intern' => null,
            'user' => $request->user(),
            'loaSettings' => $loaSettings,
            'rows' => [],
            'openingGreeting' => 'Contoh pra-tayang LOA.',
            'closingGreeting' => 'Contoh penutup pra-tayang.',
            'logoBase64' => $this->maybeToPublicUrlOrAsset($loaSettings?->logo_path, 'storage/images/logos/logo_seveninc.png'),
            'stampData' => $this->maybeToPublicUrlOrAsset($loaSettings?->stamp_path, 'storage/images/signature/ttd_arisetiahusbana.png'),
        ]);
    }

    // Daftar pemagang (CRUD ringkas: listing)
    public function indexInterns()
    {
        $registrations = IR::latest('id')->paginate(20);
        return view('admin.loa_interns', compact('registrations'));
    }

    protected function buildRows($interns): array
    {
        $rows = [];
        foreach ($interns as $intern) {
            $rows[] = [
                'nama_siswa' => $intern->fullname ?? 'Nama Tidak Diketahui',
                'nim_nis'    => ($intern->student_id ?? $intern->nim_nis ?? $intern->nim ?? null) ?: 'NIM/NIS Tidak Diketahui',
                'jurusan'    => ($intern->study_program ?? $intern->major ?? null) ?: 'Jurusan Tidak Diketahui',
                'instansi'   => $intern->institution_name ?? 'Instansi Tidak Diketahui',
                'periode'    => ($intern->start_date && $intern->end_date)
                    ? \Carbon\Carbon::parse($intern->start_date)->format('d F Y') . ' - ' . \Carbon\Carbon::parse($intern->end_date)->format('d F Y')
                    : 'Periode Tidak Diketahui',
                'kontak'     => ($intern->phone_number ?? $intern->contact_info ?? $intern->email ?? null) ?: 'Kontak Tidak Diketahui',
            ];
        }
        return $rows;
    }

    protected function ensureCanAccessCompletedDocs($user, $intern): void
    {
        $status = strtolower((string)($intern->internship_status ?? ''));

        // Pastikan data milik user yang login
        if ($intern->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak membuat/akses LOA untuk data ini.');
        }

        // LOA tersedia untuk pemagang yang sudah accepted, active, atau completed
        $allowed = ['accepted', 'active', 'completed'];
        if (!($user->role === 'pemagang' && in_array($status, $allowed))) {
            abort(403, 'LOA hanya tersedia setelah pendaftaran diterima.');
        }
    }

    protected function ensurePublicDir(string $dir): void
    {
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
    }

    // Resolve url/asset untuk logo & ttd
    protected function maybeToPublicUrlOrAsset(?string $path, string $fallbackAsset): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }
        return asset($fallbackAsset);
    }
}
