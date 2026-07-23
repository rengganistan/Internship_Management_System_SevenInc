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

    public function download(Request $request)
    {
        try {
            $authUser = auth()->user();
            $targetUser = $authUser;

            // Jika admin / staff download untuk user lain
            if ($request->filled('user_id')) {
                if (!in_array($authUser->role, ['admin','staff','hrd'])) {
                    abort(403, 'Hanya admin/staff yang dapat mengunduh SKL untuk user lain.');
                }
                $targetUser = User::findOrFail($request->user_id);
            }

            // Ambil data magang
            $ir = IR::where('user_id', $targetUser->id)->latest()->first();
            if (!$ir || $ir->internship_status !== 'completed') {
                abort(403, 'SKL hanya dapat diunduh setelah status magang completed.');
            }

            // Ambil config perusahaan
            $config = SKLSetting::first();
            $companyName    = $config->company_name ?? 'Seven Inc';
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

            // Buat path sementara untuk menyimpan PDF
            $safeName = preg_replace('/[^a-z0-9\-_]+/i','_',$participantName);
            $fileName = "SKL_{$safeName}.pdf";
            $tempPath = storage_path("app/tmp/{$fileName}");

            if (!file_exists(storage_path('app/tmp'))) {
                mkdir(storage_path('app/tmp'), 0777, true);
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
                ->savePdf($tempPath);
            
            
            // Log Download Sukses
            DocumentDownload::create([
                'user_id' => $targetUser->id,
                'doc_type' => DocumentDownload::TYPE_SKL,
                'file_path' => $tempPath,
                'file_url' => asset('storage/tmp/'.$fileName),
                'downloaded_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
            ]);

            // Unduh file PDF dan hapus file setelah pengunduhan
            return response()->download($tempPath)->deleteFileAfterSend(true);
        }  catch (\Exception $e) {
            // Log error jika gagal
            DocumentDownload::create([
                'user_id' => $targetUser->id,
                'doc_type' => DocumentDownload::TYPE_SKL,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'downloaded_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat membuat SKL. Silakan coba lagi.');
        }
    }
}
