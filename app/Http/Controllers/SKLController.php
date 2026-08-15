<?php

namespace App\Http\Controllers;

use App\Models\SKLSetting;
use App\Models\User;
use App\Models\InternshipRegistration as IR;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use App\Models\DocumentDownload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SKLController extends Controller
{
    /**
     * Tampilkan form pengaturan SKL
     */
    public function edit()
    {
        $config = SKLSetting::first() ?? SKLSetting::create([
            'company_name'    => 'Seven Inc',
            'company_address' => 'Jl. Raya Janti Gg. Harjuna No.59, Jaranan, Karangjambe, Kec. Banguntapan, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55198',
            'company_city'    => 'Yogyakarta',
            'leader_name'     => 'Nama Pimpinan / HRD',
            'leader_title'    => 'Manajer HRD',
            'logo_path'       => 'storage/images/logos/logo_seveninc.png',
            'stamp_path'      => 'storage/images/signature/ttd_rekariodanny.png',
        ]);

        return view('admin.skl_editor', compact('config'));
    }

    /**
     * Update data SKL
     */
    public function update(Request $request)
    {
        // Validasi data yang dimasukkan
        $request->validate([
            'company_name'    => 'required|string|max:100',
            'company_address' => 'required|string|max:255',
            'company_city'    => 'required|string|max:100',
            'leader_name'     => 'required|string|max:100',
            'leader_title'    => 'required|string|max:100',
            'activity_description' => 'nullable|string|max:1000',
            'participant_achievement' => 'nullable|string|max:1000',
            'logo'            => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'stamp'           => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Ambil data konfigurasi SKL yang ada
        $config = SKLSetting::first();

        // Jika belum ada konfigurasi, buat default terlebih dahulu
        if (!$config) {
            $config = SKLSetting::create([
                'company_name'    => 'Seven Inc',
                'company_address' => 'Jl. Raya Janti Gg. Harjuna No.59, Jaranan, Karangjambe, Kec. Banguntapan, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55198',
                'company_city'    => 'Yogyakarta',
                'leader_name'     => 'Nama Pimpinan / HRD',
                'leader_title'    => 'Manajer HRD',
                'logo_path'       => 'storage/images/logos/logo_seveninc.png',
                'stamp_path'      => 'storage/images/signature/ttd_rekariodanny.png',
            ]);
        }

        // Perbarui data teks (nama perusahaan, alamat, dll)
        $config->company_name    = $request->company_name;
        $config->company_address = $request->company_address;
        $config->company_city    = $request->company_city;
        $config->leader_name     = $request->leader_name;
        $config->leader_title    = $request->leader_title;
        $config->activity_description = $request->activity_description;
        $config->participant_achievement = $request->participant_achievement;

        // Upload logo jika ada
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->storeAs('public/images/logos', 'logo_seveninc.png');
            $config->logo_path = 'storage/images/logos/logo_seveninc.png';
        }

        // Upload stempel jika ada
        if ($request->hasFile('stamp')) {
            $stampPath = $request->file('stamp')->storeAs('public/images/signature', 'ttd_rekariodanny.png');
            $config->stamp_path = 'storage/images/signature/ttd_rekariodanny.png';
        }

        // Simpan perubahan ke database
        $config->save();

        return back()->with('success', '✅ Data SKL berhasil diperbarui!');
    }



    /**
     * Preview SKL berdasarkan data dari database
     */
    public function preview(Request $request)
    {
        $config = SKLSetting::first() ?? new SKLSetting([
            'company_name'    => 'Seven Inc',
            'company_address' => 'Jl. Raya Janti Gg. Harjuna No.59, Jaranan, Karangjambe, Kec. Banguntapan, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55198',
            'company_city'    => 'Yogyakarta',
            'leader_name'     => 'Nama Pimpinan / HRD',
            'leader_title'    => 'Manajer HRD',
            'logo_path'       => 'storage/images/logos/logo_seveninc.png',
            'stamp_path'      => 'storage/images/signature/ttd_rekariodanny.png',
        ]);

        // Company block (boleh override dari query agar realtime di iframe)
        $companyName    = $request->get('company_name',    $config->company_name);
        $companyAddress = $request->get('company_address', $config->company_address);
        $companyCity    = $request->get('company_city',    $config->company_city);
        $leaderName     = $request->get('leader_name',     $config->leader_name);
        $leaderTitle    = $request->get('leader_title',    $config->leader_title);

        // Dummy peserta untuk preview
        $participantName      = $request->get('participant_name', 'Nama Pemagang (Preview)');
        $participantId        = $request->get('participant_id', '1234567890 (Preview)');
        $participantMajor     = $request->get('participant_major', 'Teknik Informatika (Preview)');
        $participantInstitute = $request->get('participant_institute', 'Universitas Contoh (Preview)');
        $divisionName         = $request->get('division_name', 'Divisi Teknologi (Preview)');

        // Periode (boleh override)
        $startAt  = $request->get('start_date', Carbon::now()->subMonths(1)->format('Y-m-d'));
        $endAt    = $request->get('end_date',   Carbon::now()->format('Y-m-d'));
        $startStr = Carbon::parse($startAt)->isoFormat('D MMMM Y');
        $endStr   = Carbon::parse($endAt)->isoFormat('D MMMM Y');

        // Letter meta
        $letterDateStr = Carbon::parse($endAt)->isoFormat('D MMMM Y');
        $letterNumber  = 'SKL/'.Carbon::parse($endAt)->format('Y').'/DEMO';

        // Assets
        $logoFile  = public_path('storage/images/logos/logo_seveninc.png');
        $stampFile = public_path('storage/images/signature/ttd_arisetiahusbana.png');

        // Fallback stamp ke file TTD lain yang ada
        if (!file_exists($stampFile)) {
            $candidates = glob(public_path('storage/images/signature/*.{png,jpg,jpeg}'), GLOB_BRACE);
            $stampFile  = !empty($candidates) ? $candidates[0] : null;
        }

        $logoPath  = $logoFile;
        $stampPath = $stampFile;

        // Mendapatkan data dari request atau menggunakan default value
        $activityDescription = $request->get('activity_description', $config->activity_description);
        $participantAchievement = $request->get('participant_achievement', $config->participant_achievement);


        return view('user.skl', compact(
            'companyName','companyAddress','companyCity','leaderName','leaderTitle',
            'letterNumber','logoPath','stampPath',
            'participantName','participantId','participantMajor','participantInstitute','divisionName',
            'startStr','endStr','letterDateStr','activityDescription', 'participantAchievement'
        ));
    }

    /**
     * GET /admin/skl/generate/{intern}
     * Form review sebelum generate SKL
     */
    public function generateForm(Request $request, IR $intern)
    {
        if ($intern->internship_status !== IR::STATUS_COMPLETED) {
            return redirect()->route('admin.interns.pemagang')
                ->with('error', 'SKL hanya bisa dibuat untuk pemagang yang sudah Selesai.');
        }

        $config = SKLSetting::first();

        // Override company name dengan brand pemagang jika ada
        $companyName    = $intern->brand ?: ($config->company_name    ?? 'Seven Inc');
        $companyAddress = $config->company_address ?? 'Jl. Raya Janti Gg. Harjuna No.59';
        $companyCity    = $config->company_city    ?? 'Yogyakarta';
        $leaderName     = $config->leader_name     ?? 'Nama Pimpinan / HRD';
        $leaderTitle    = $config->leader_title    ?? 'Manajer HRD';
        $activityDescription    = $config->activity_description     ?? '';
        $participantAchievement = $config->participant_achievement   ?? '';

        // Ambil daftar logo & stempel dari storage
        $logoFiles = collect(Storage::disk('public')->files('images/logos'))
            ->filter(fn($f) => preg_match('/\.(png|jpe?g)$/i', $f))
            ->values();
        $stampFiles = collect(Storage::disk('public')->files('images/signature'))
            ->filter(fn($f) => preg_match('/\.(png|jpe?g)$/i', $f))
            ->values();

        return view('admin.skl_generate_form', compact(
            'intern',
            'companyName', 'companyAddress', 'companyCity',
            'leaderName', 'leaderTitle',
            'activityDescription', 'participantAchievement',
            'logoFiles', 'stampFiles'
        ));
    }

    /**
     * POST /admin/skl/generate/{intern}
     * Proses generate & download SKL dari form
     */
    public function generateDownload(Request $request, IR $intern)
    {
        if ($intern->internship_status !== IR::STATUS_COMPLETED) {
            abort(403, 'SKL hanya tersedia untuk pemagang yang sudah Selesai.');
        }

        $request->validate([
            'company_name'           => 'required|string|max:100',
            'company_address'        => 'required|string|max:500',
            'company_city'           => 'required|string|max:100',
            'leader_name'            => 'required|string|max:150',
            'leader_title'           => 'required|string|max:100',
            'activity_description'   => 'nullable|string|max:2000',
            'participant_achievement'=> 'nullable|string|max:2000',
            'logo_select'            => 'nullable|string',
            'stamp_select'           => 'nullable|string',
        ]);

        Carbon::setLocale('id');

        $companyName    = $request->company_name;
        $companyAddress = $request->company_address;
        $companyCity    = $request->company_city;
        $leaderName     = $request->leader_name;
        $leaderTitle    = $request->leader_title;
        $activityDescription    = $request->activity_description    ?? '';
        $participantAchievement = $request->participant_achievement ?? '';

        // Resolve logo
        $logoSelect = $request->logo_select;
        $logoFile   = $logoSelect
            ? storage_path('app/public/' . $logoSelect)
            : storage_path('app/public/images/logos/logo_seveninc.png');
        $logoData = file_exists($logoFile)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
            : null;

        // Resolve stempel
        $stampSelect = $request->stamp_select;
        $stampFile   = $stampSelect
            ? storage_path('app/public/' . $stampSelect)
            : storage_path('app/public/images/signature/ttd_arisetiahusbana.png');
        if (!file_exists($stampFile)) {
            $candidates = glob(storage_path('app/public/images/signature/*.{png,jpg,jpeg}'), GLOB_BRACE);
            $stampFile  = !empty($candidates) ? $candidates[0] : null;
        }
        $stampExt  = $stampFile ? strtolower(pathinfo($stampFile, PATHINFO_EXTENSION)) : 'png';
        $stampMime = in_array($stampExt, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
        $stampData = $stampFile && file_exists($stampFile)
            ? "data:{$stampMime};base64," . base64_encode(file_get_contents($stampFile))
            : null;

        // Data peserta
        $user                 = $intern->user;
        $participantName      = $intern->fullname ?? ($user?->name ?? '-');
        $participantId        = $intern->student_id ?? '-';
        $participantMajor     = $intern->study_program ?? '-';
        $participantInstitute = $intern->institution_name ?? '-';
        $divisionName         = $intern->internship_interest ?? '-';

        Carbon::setLocale('id');
        $startStr      = $intern->start_date ? Carbon::parse($intern->start_date)->isoFormat('D MMMM Y') : '-';
        $endStr        = $intern->end_date   ? Carbon::parse($intern->end_date)->isoFormat('D MMMM Y')   : '-';
        $letterDateStr = $intern->end_date   ? Carbon::parse($intern->end_date)->isoFormat('D MMMM Y')   : now()->isoFormat('D MMMM Y');
        $running       = str_pad((string) $intern->id, 4, '0', STR_PAD_LEFT);
        $letterNumber  = 'SKL/' . ($intern->end_date ? Carbon::parse($intern->end_date)->format('Y') : now()->format('Y')) . '/' . $running;

        $data = compact(
            'companyName','companyAddress','companyCity','leaderName','leaderTitle',
            'letterNumber','logoData','stampData',
            'participantName','participantId','participantMajor','participantInstitute','divisionName',
            'startStr','endStr','letterDateStr','activityDescription','participantAchievement'
        );

        $html = view('user.skl', $data)->render();

        $safeName = preg_replace('/[^a-z0-9\-_]+/i', '_', $participantName);
        $fileName = "SKL_{$safeName}_" . now()->format('Ymd_His') . ".pdf";
        $relPath  = "documents/skl/{$fileName}";
        $fullDir  = storage_path('app/public/documents/skl');
        $fullPath = storage_path("app/public/{$relPath}");

        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0777, true);
        }

        Browsershot::html($html)
            ->setOption('no-sandbox', true)
            ->emulateMedia('print')
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(180)
            ->savePdf($fullPath);

        // Log download
        DocumentDownload::create([
            'user_id'                    => $intern->user_id,
            'internship_registration_id' => $intern->id,
            'doc_type'                   => DocumentDownload::TYPE_SKL,
            'file_path'                  => $relPath,
            'file_url'                   => asset('storage/' . $relPath),
            'downloaded_at'              => now(),
            'ip_address'                 => $request->ip(),
            'user_agent'                 => $request->userAgent(),
            'status'                     => 'success',
        ]);

        return response()->download($fullPath, $fileName, ['Content-Type' => 'application/pdf'])
            ->deleteFileAfterSend(false);
    }

    /**
     * GET /admin/skl/download/{user} — download SKL dari Data SKL (tetap ada)
     */
    public function download(Request $request, $userId = null)
    {
        try {
            $authUser = auth()->user();
            $targetUser = $authUser;

            // Support route param {user} dari admin route
            $resolvedUserId = $userId ?? $request->get('user_id');

            // Jika ada user_id (admin generate untuk pemagang)
            if ($resolvedUserId) {
                if (!in_array($authUser->role, ['admin','staff','hrd'])) {
                    abort(403, 'Hanya admin/staff yang dapat mengunduh SKL untuk user lain.');
                }
                $targetUser = User::findOrFail($resolvedUserId);
            }

            // Ambil data magang
            $ir = IR::where('user_id', $targetUser->id)->latest()->first();
            if (!$ir || $ir->internship_status !== 'completed') {
                abort(403, 'SKL hanya dapat diunduh setelah status magang completed.');
            }

            // Ambil config perusahaan
            $config = SKLSetting::first();
            // Override company name dengan brand pemagang jika ada
            $companyName    = $ir->brand ?: ($config->company_name ?? 'Seven Inc');
            $companyAddress = $config->company_address ?? 'Jl. Raya Janti Gg. Harjuna No.59, Jaranan, Karangjambe, Kec. Banguntapan, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55198';
            $companyCity    = $config->company_city ?? 'Yogyakarta';
            $leaderName     = $config->leader_name ?? 'Nama Pimpinan / HRD';
            $leaderTitle    = $config->leader_title ?? 'Manajer HRD';

            // Path logo dan stempel — encode ke Base64
            $logoFile = storage_path('app/public/images/logos/logo_seveninc.png');
            $logoData = file_exists($logoFile)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
                : null;

            $stampFile = storage_path('app/public/images/signature/ttd_arisetiahusbana.png');
            if (!file_exists($stampFile)) {
                $candidates = glob(storage_path('app/public/images/signature/*.{png,jpg,jpeg}'), GLOB_BRACE);
                $stampFile  = !empty($candidates) ? $candidates[0] : null;
            }
            $stampExt  = $stampFile ? strtolower(pathinfo($stampFile, PATHINFO_EXTENSION)) : 'png';
            $stampMime = in_array($stampExt, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
            $stampData = $stampFile && file_exists($stampFile)
                ? "data:{$stampMime};base64," . base64_encode(file_get_contents($stampFile))
                : null;

            // Data peserta magang
            $participantName      = $targetUser->name;
            $participantId        = $ir->student_id ?? '-';
            $participantMajor     = $ir->study_program ?? '-';
            $participantInstitute = $ir->institution_name ?? '-';
            $divisionName         = $ir->internship_interest ?? '-';

            // Periode magang
            $startStr = Carbon::parse($ir->start_date)->isoFormat('D MMMM Y');
            $endStr   = Carbon::parse($ir->end_date)->isoFormat('D MMMM Y');
            $letterDateStr = Carbon::parse($ir->end_date)->isoFormat('D MMMM Y');

            // Nomor surat dinamis
            $running = str_pad((string)$ir->id, 4, "0", STR_PAD_LEFT);
            $letterNumber = 'SKL/' . Carbon::parse($ir->end_date)->format('Y') . '/' . $running;

            // **TAMBAHKAN DEFINED activityDescription**
            $activityDescription = $ir->activity_description ?? $config->activity_description ?? 'Deskripsi tidak tersedia';
            $participantAchievement = $ir->participant_achievement ?? $config->participant_achievement ?? 'Prestasi tidak tersedia';

            // Data untuk dikirim ke view
            $data = compact(
                'companyName','companyAddress','companyCity','leaderName','leaderTitle',
                'letterNumber','logoData','stampData',
                'participantName','participantId','participantMajor','participantInstitute','divisionName',
                'startStr','endStr','letterDateStr', 'activityDescription', 'participantAchievement'
            );

            // Render HTML untuk halaman SKL
            $html = view('user.skl', $data)->render();

            // Buat path permanen di public disk (konsisten dengan LOA)
            $safeName = preg_replace('/[^a-z0-9\-_]+/i', '_', $participantName);
            $fileName = "SKL_{$safeName}_" . now()->format('Ymd_His') . ".pdf";
            $relPath  = "documents/skl/{$fileName}";
            $fullDir  = storage_path('app/public/documents/skl');
            $fullPath = storage_path("app/public/{$relPath}");

            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0777, true);
            }

            // Menggunakan Browsershot untuk merender HTML ke PDF
            Browsershot::html($html)
                ->setOption('no-sandbox', true)
                ->emulateMedia('print')
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->timeout(180)
                ->savePdf($fullPath);

            // Log Download — simpan relative path agar route serve bisa temukan file
            DocumentDownload::create([
                'user_id'                    => $targetUser->id,
                'internship_registration_id' => $ir->id,
                'doc_type'                   => DocumentDownload::TYPE_SKL,
                'file_path'                  => $relPath,
                'file_url'                   => asset('storage/' . $relPath),
                'downloaded_at'              => now(),
                'ip_address'                 => $request->ip(),
                'user_agent'                 => $request->userAgent(),
                'status'                     => 'success',
            ]);

            // Download langsung
            return response()->download($fullPath, $fileName, ['Content-Type' => 'application/pdf'])
                ->deleteFileAfterSend(false); // jangan hapus — biar bisa dilihat lagi dari riwayat
        }  catch (\Exception $e) {
            // Log error jika gagal
            DocumentDownload::create([
                'user_id'                    => $targetUser->id,
                'internship_registration_id' => $ir->id ?? null,
                'doc_type'                   => DocumentDownload::TYPE_SKL,
                'status'                     => 'failed',
                'error_message'              => $e->getMessage(),
                'downloaded_at'              => now(),
                'ip_address'                 => $request->ip(),
                'user_agent'                 => $request->userAgent(),
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat membuat SKL. Silakan coba lagi.');
        }
    }
}
