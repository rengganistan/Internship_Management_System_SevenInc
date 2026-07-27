<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternAssessment;
use App\Models\InternshipRegistration as IR;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;


class InternAssessmentController extends Controller
{
    // Helper kecil untuk membentuk URL publik dari file di disk 'public'
    private function publicUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (!Storage::disk('public')->exists($path)) return null;
        // Storage::url() -> /storage/...
        return url(Storage::url($path));
    }

    // ===========================
    // PREVIEW (HTML biasa, siap dicetak)
    // ===========================
    public function previewPDF($id)
    {
        $assessment = InternAssessment::findOrFail($id);

        // Set the logo path
        $logoFile = public_path('storage/' . ($assessment->company_logo_path ?? 'images/logos/logo_seveninc.png'));
        $logoSrc = file_exists($logoFile)
            ? asset('storage/' . ($assessment->company_logo_path ?? 'images/logos/logo_seveninc.png'))
            : asset('storage/images/logos/logo_seveninc.png'); // Fallback to default logo if not found

        // Set the signature path
        $sigFile = public_path('storage/' . ($assessment->signature_image_path ?? 'images/signature/ttd_rekariodanny.png'));
        $sigSrc = file_exists($sigFile)
            ? asset('storage/' . ($assessment->signature_image_path ?? 'images/signature/ttd_rekariodanny.png'))
            : asset('storage/images/signature/ttd_rekariodanny.png'); // Fallback to default signature if not found


        // Render Blade template for PDF preview
        return view('admin.interns.pdf_assessment', [
            'assessment'   => $assessment,
            'logoSrc'      => $logoSrc,
            'sigSrc'       => $sigSrc,
            'autoPrint'    => false,  // Don't auto-print in preview
        ]);
    }
    
    public function downloadPDF($id)
    {
        $assessment = InternAssessment::findOrFail($id);

        // Set the logo path
        $logoFile = public_path('storage/' . ($assessment->company_logo_path ?? 'images/logos/logo_seveninc.png'));

        // Fallback to default logo if not found
        $logoSrc = file_exists($logoFile)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
            : 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('storage/images/logos/logo_seveninc.png')));

        // Set the signature path
        $sigFile = public_path('storage/' . ($assessment->signature_image_path ?? 'images/signature/ttd_rekariodanny.png'));
        $sigSrc = file_exists($sigFile)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigFile))
            : 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('storage/images/signature/ttd_rekariodanny.png')));

        // Prepare the filename
        $brandText = 'intern-assessment';
        $brandSlug = Str::slug($brandText, '-');
        $assessmentSlug = Str::slug($assessment->fullname, '-');
        $filename = "{$brandSlug}-{$assessmentSlug}.pdf";

        $pdfTitle = "Assessment for {$assessment->fullname}";

        // Render HTML for the PDF template
        $htmlContent = view('admin.interns.pdf_assessment', [
            'assessment' => $assessment,
            'logoSrc' => $logoSrc,
            'sigSrc' => $sigSrc,
            'autoPrint' => false,  // Don't auto-print on download
        ])->render();

        // Temporary path for saving the PDF
        $tmpPath = storage_path('app/tmp/'.$filename);
        if (!is_dir(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0775, true);
        }

        // Use Browsershot to generate PDF from HTML content
        Browsershot::html($htmlContent)
            ->emulateMedia('print')
            ->format('A4')
            ->landscape()  // Landscape orientation
            ->margins(0, 0, 0, 0) // Remove margins
            ->timeout(180)  // Set timeout for the process
            ->setOption('args', [
                '--no-sandbox',
                '--disable-setuid-sandbox',
            ])
            ->savePdf($tmpPath);

        // Return the generated PDF as a downloadable file
        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true); // Automatically delete the file after sending
    }



    // ===========================
    // INDEX
    // ===========================
    public function index()
    {
        $data = InternAssessment::latest()->get();
        return view('admin.interns.index_assessment', compact('data'));
    }

    // ===========================
    // CREATE
    // ===========================
    public function create(Request $request)
    {
        // ===== Divisi & daftar pilihan =====
        $division  = $request->get('div', 'Content Writer');
        $divisions = ['Content Writer', 'Programmer', 'UI/UX Designer', 'Graphic Designer', 'Digital Marketing', 'Lainnya'];

        // Normalisasi divisi: jika tidak ada di daftar, fallback ke Content Writer
        if (!in_array($division, $divisions, true)) {
            $division = 'Content Writer';
        }

        // ===== Default aspek per divisi =====
        $defaultAspects = [
            'Content Writer' => [
                ['aspek' => 'Copywriting',        'nilai' => 95],
                ['aspek' => 'Branding',           'nilai' => 95],
                ['aspek' => 'Riset Konten',       'nilai' => 95],
                ['aspek' => 'Kedisiplinan',       'nilai' => 95],
                ['aspek' => 'Kreativitas',        'nilai' => 95],
                ['aspek' => 'Kerjasama',          'nilai' => 95],
                ['aspek' => 'Kehadiran',          'nilai' => 95],
            ],
            'Programmer' => [
                ['aspek' => 'Coding Front/Backend','nilai' => 95],
                ['aspek' => 'Database',            'nilai' => 95],
                ['aspek' => 'Debugging',           'nilai' => 95],
                ['aspek' => 'Problem Solving',     'nilai' => 95],
                ['aspek' => 'Kedisiplinan',        'nilai' => 95],
                ['aspek' => 'Kerjasama',           'nilai' => 95],
                ['aspek' => 'Kehadiran',           'nilai' => 95],
            ],
            'UI/UX Designer' => [
                ['aspek' => 'User Research',              'nilai' => 95],
                ['aspek' => 'Wireframing & Prototyping',  'nilai' => 95],
                ['aspek' => 'Visual Design',              'nilai' => 95],
                ['aspek' => 'Layout & Typography',        'nilai' => 95],
                ['aspek' => 'Color Theory',               'nilai' => 95],
                ['aspek' => 'Kedisiplinan',               'nilai' => 95],
                ['aspek' => 'Kerjasama',                  'nilai' => 95],
                ['aspek' => 'Kehadiran',                  'nilai' => 95],
            ],
            'Graphic Designer' => [
                ['aspek' => 'Desain Visual',       'nilai' => 95],
                ['aspek' => 'Kreativitas & Inovasi','nilai' => 95],
                ['aspek' => 'Typography & Warna',  'nilai' => 95],
                ['aspek' => 'Infografis & Branding','nilai' => 95],
                ['aspek' => 'Kedisiplinan',        'nilai' => 95],
                ['aspek' => 'Kerjasama',           'nilai' => 95],
                ['aspek' => 'Kehadiran',           'nilai' => 95],
            ],
            'Digital Marketing' => [
                ['aspek' => 'Strategi Konten',         'nilai' => 95],
                ['aspek' => 'Analisis Data & Keyword', 'nilai' => 95],
                ['aspek' => 'Social Media Marketing',  'nilai' => 95],
                ['aspek' => 'Copywriting & Engagement','nilai' => 95],
                ['aspek' => 'Kedisiplinan',            'nilai' => 95],
                ['aspek' => 'Kreativitas',             'nilai' => 95],
                ['aspek' => 'Kerjasama',               'nilai' => 95],
                ['aspek' => 'Kehadiran',               'nilai' => 95],
            ],
            // Fallback untuk "Lainnya" atau divisi tanpa template khusus
            'Lainnya' => [
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kerjasama',    'nilai' => 95],
                ['aspek' => 'Kehadiran',    'nilai' => 95],
            ],
        ];

        // Pastikan selalu array (hindari error foreach)
        $aspects = $defaultAspects[$division] ?? $defaultAspects['Content Writer'];

        // ===== Data pemagang =====
        $interns = IR::whereIn('internship_status', [IR::STATUS_ACTIVE, IR::STATUS_COMPLETED])
            ->orderBy('fullname', 'asc')
            ->get(['id', 'fullname', 'student_id', 'study_program']);

        // ===== Koleksi logo & tanda tangan (aman jika folder belum ada) =====
        $logoFiles = Storage::disk('public')->exists('images/logos')
            ? Storage::disk('public')->files('images/logos')
            : [];
        $signatureFiles = Storage::disk('public')->exists('images/signature')
            ? Storage::disk('public')->files('images/signature')
            : [];

        $logos = collect($logoFiles)
            ->filter(fn ($file) => preg_match('/\.(png|jpe?g|gif)$/i', $file))
            ->values()
            ->toArray();

        $signatures = collect($signatureFiles)
            ->filter(fn ($file) => preg_match('/\.(png|jpe?g|gif)$/i', $file))
            ->values()
            ->toArray();

        // ===== Kirim ke view =====
        return view('admin.interns.create_assessment', compact(
            'aspects', 'division', 'divisions', 'interns', 'logos', 'signatures'
        ));
    }


    // ===========================
    // STORE & UPDATE
    // ===========================
    public function store(Request $request) 
    {
        // Validasi input
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'nim_or_nis' => 'nullable|string|max:50',
            'study_program' => 'nullable|string|max:255',
            'div' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'signature_name' => 'nullable|string|max:255',
            'signature_position' => 'nullable|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aspek' => 'required|array',
            'nilai' => 'required|array',
        ]);

        // =======================
        // Proses Penyimpanan Logo Perusahaan
        // =======================
        $companyLogoPath = null;
        if ($request->filled('company_logo_select')) {
            // Jika memilih logo dari dropdown
            $companyLogoPath = $request->company_logo_select;
        } elseif ($request->hasFile('company_logo')) {
            // Jika meng-upload logo baru
            $companyLogoPath = $request->file('company_logo')->store('images/logos', 'public');
        }

        // =======================
        // Proses Penyimpanan Tanda Tangan
        // =======================
        $signatureImagePath = null;
        if ($request->filled('signature_image_select')) {
            // Jika memilih tanda tangan dari dropdown
            $signatureImagePath = $request->signature_image_select;
        } elseif ($request->hasFile('signature_image')) {
            // Jika meng-upload tanda tangan baru
            $signatureImagePath = $request->file('signature_image')->store('images/signature', 'public');
        }

        // =======================
        // Menyusun Data Aspek Penilaian dan Menghitung Total Nilai
        // =======================
        $data = [];
        $total = 0;

        // Validasi untuk aspek dan nilai
        foreach ($validated['aspek'] as $i => $aspek) {
            $nilai = (float)($validated['nilai'][$i] ?? 0);
            
            // Jika nilai aspek tidak valid (misalnya 0 atau lebih dari 100), set default nilai 0
            if ($nilai < 0 || $nilai > 100) {
                $nilai = 0;
            }

            $data[] = ['aspek' => $aspek, 'nilai' => $nilai];
            $total += $nilai;
        }

        // Menghitung rata-rata
        $rataRata = (count($validated['aspek']) > 0) ? round($total / count($validated['aspek']), 2) : 0;

        // =======================
        // Menyimpan Data Penilaian ke Database
        // =======================
        $assessment = InternAssessment::create([
            'fullname' => $validated['fullname'],
            'nim_or_nis' => $validated['nim_or_nis'] ?? null,
            'study_program' => $validated['study_program'] ?? null,
            'div' => $validated['div'] ?? 'Content Writer',
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'signature_name' => $validated['signature_name'] ?? null,
            'signature_position' => $validated['signature_position'] ?? null,
            'company_logo_path' => $companyLogoPath,
            'signature_image_path' => $signatureImagePath,
            'aspek_penilaian' => json_encode($data),
            'rata_rata' => $rataRata,
            'intern_id' => $request->input('intern_id'),
        ]);

        // Simpan ke document_downloads supaya masuk ke halaman Dokumen pemagang
        if ($assessment->intern_id) {
            $intern = \App\Models\InternshipRegistration::find($assessment->intern_id);
            if ($intern?->user_id) {
                \App\Models\DocumentDownload::create([
                    'user_id'                    => $intern->user_id,
                    'internship_registration_id' => $intern->id,
                    'doc_type'                   => \App\Models\DocumentDownload::TYPE_PENILAIAN,
                    'file_path'                  => null,
                    'file_url'                   => route('interns.assessment.pdf', $assessment->id),
                    'downloaded_at'              => now(),
                    'status'                     => 'success',
                ]);
            }
        }

        return redirect()->route('interns.assessment.index')->with('success',
            '✅ Penilaian berhasil disimpan.' .
            ($assessment->intern_id ? ' Surat penilaian sudah tersedia di halaman Dokumen pemagang.' : '')
        );
    }


    public function getAspekByDivision(Request $request)
    {
        // Ambil divisi yang diminta dari request, default ke 'Content Writer'
        $division = $request->get('division', 'Content Writer');

        // Daftar aspek default berdasarkan divisi
        $defaultAspects = [
            'Content Writer' => [
                ['aspek' => 'Copywriting', 'nilai' => 95],
                ['aspek' => 'Branding', 'nilai' => 95],
                ['aspek' => 'Riset Konten', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kreativitas', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
            'Programmer' => [
                ['aspek' => 'Coding Front/Backend', 'nilai' => 95],
                ['aspek' => 'Database', 'nilai' => 95],
                ['aspek' => 'Debugging', 'nilai' => 95],
                ['aspek' => 'Problem Solving', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
            'UI/UX Designer' => [
                ['aspek' => 'User Research', 'nilai' => 95],
                ['aspek' => 'Wireframing & Prototyping', 'nilai' => 95],
                ['aspek' => 'Visual Design', 'nilai' => 95],
                ['aspek' => 'Layout & Typography', 'nilai' => 95],
                ['aspek' => 'Color Theory', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
            'Graphic Designer' => [
                ['aspek' => 'Desain Visual', 'nilai' => 95],
                ['aspek' => 'Kreativitas & Inovasi', 'nilai' => 95],
                ['aspek' => 'Typography & Warna', 'nilai' => 95],
                ['aspek' => 'Infografis & Branding', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
            'Digital Marketing' => [
                ['aspek' => 'Strategi Konten', 'nilai' => 95],
                ['aspek' => 'Analisis Data & Keyword', 'nilai' => 95],
                ['aspek' => 'Social Media Marketing', 'nilai' => 95],
                ['aspek' => 'Copywriting & Engagement', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kreativitas', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
        ];

        // Memeriksa apakah divisi yang diminta ada di dalam defaultAspects
        // Jika tidak ada, fallback ke divisi 'Content Writer' atau divisi yang relevan
        if (!array_key_exists($division, $defaultAspects)) {
            $division = 'Content Writer'; // Atau bisa set divisi lain sesuai keinginan
        }

        // Mengembalikan aspek berdasarkan divisi yang diminta
        return response()->json(['aspek' => $defaultAspects[$division]]);
    }


    public function edit($id)
    {
        $assessment = InternAssessment::findOrFail($id);

        // Divisi yang tersedia
        $divisions = ['Content Writer', 'Programmer', 'UI/UX Designer', 'Graphic Designer', 'Digital Marketing', 'Lainnya'];

        // Default aspek penilaian untuk setiap divisi
        $defaultAspects = [
            'Content Writer' => [
                ['aspek' => 'Copywriting', 'nilai' => 95],
                ['aspek' => 'Branding', 'nilai' => 95],
                ['aspek' => 'Riset Konten', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kreativitas', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
            'Programmer' => [
                ['aspek' => 'Coding Front/Backend', 'nilai' => 95],
                ['aspek' => 'Database', 'nilai' => 95],
                ['aspek' => 'Debugging', 'nilai' => 95],
                ['aspek' => 'Problem Solving', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
            'UI/UX Designer' => [
                ['aspek' => 'User Research', 'nilai' => 95],
                ['aspek' => 'Wireframing & Prototyping', 'nilai' => 95],
                ['aspek' => 'Visual Design', 'nilai' => 95],
                ['aspek' => 'Layout & Typography', 'nilai' => 95],
                ['aspek' => 'Color Theory', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
            'Graphic Designer' => [
                ['aspek' => 'Desain Visual', 'nilai' => 95],
                ['aspek' => 'Kreativitas & Inovasi', 'nilai' => 95],
                ['aspek' => 'Typography & Warna', 'nilai' => 95],
                ['aspek' => 'Infografis & Branding', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
            'Digital Marketing' => [
                ['aspek' => 'Strategi Konten', 'nilai' => 95],
                ['aspek' => 'Analisis Data & Keyword', 'nilai' => 95],
                ['aspek' => 'Social Media Marketing', 'nilai' => 95],
                ['aspek' => 'Copywriting & Engagement', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kreativitas', 'nilai' => 95],
                ['aspek' => 'Kerjasama', 'nilai' => 95],
                ['aspek' => 'Kehadiran', 'nilai' => 95],
            ],
        ];

        // Mengambil aspek penilaian dari database dan meng-decode JSON menjadi array
        $aspekPenilaian = json_decode($assessment->aspek_penilaian, true);

        // Memastikan bahwa $aspekPenilaian adalah array
        if (!is_array($aspekPenilaian)) {
            // Jika tidak, fallback ke default aspects sesuai divisi
            $aspekPenilaian = $defaultAspects[$assessment->div] ?? $defaultAspects['Content Writer'];
        }

        // Loop melalui aspek penilaian yang ada dan sesuaikan nilai berdasarkan data di database
        foreach ($aspekPenilaian as $item) {
            foreach ($defaultAspects[$assessment->div] as $index => $aspect) {
                if ($aspect['aspek'] === $item['aspek']) {
                    $defaultAspects[$assessment->div][$index]['nilai'] = $item['nilai'];
                }
            }
        }

        // Mengambil logo perusahaan dari storage
        $logos = collect(Storage::disk('public')->files('images/logos'))
            ->filter(fn($file) => preg_match('/\.(png|jpg|jpeg)$/i', $file))
            ->values()
            ->toArray();

        // Mengambil tanda tangan dari storage
        $signatures = collect(Storage::disk('public')->files('images/signature'))
            ->filter(fn($file) => preg_match('/\.(png|jpg|jpeg)$/i', $file))
            ->values()
            ->toArray();

        // Mengambil daftar peserta magang yang aktif dan selesai
        $interns = IR::whereIn('internship_status', [IR::STATUS_ACTIVE, IR::STATUS_COMPLETED])
            ->orderBy('fullname', 'asc')
            ->get(['id', 'fullname', 'student_id', 'study_program']);

        // Mengirim data ke view
        return view('admin.interns.edit_assessment', [
            'assessment' => $assessment,
            'aspekPenilaian' => $aspekPenilaian, 
            'divisions' => $divisions,
            'logos' => $logos,
            'signatures' => $signatures,
            'interns' => $interns
        ]);
    }


    public function update(Request $request, $id)
    {
        // Mengambil data penilaian yang ada
        $assessment = InternAssessment::findOrFail($id);

        // Validasi input
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'nim_or_nis' => 'nullable|string|max:50',
            'study_program' => 'nullable|string|max:255',
            'div' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'signature_name' => 'nullable|string|max:255',
            'signature_position' => 'nullable|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aspek' => 'required|array',
            'nilai' => 'required|array',
        ]);

        // =======================
        // Proses Penyimpanan Logo Perusahaan
        // =======================
        $companyLogoPath = $assessment->company_logo_path; // Default jika tidak ada perubahan
        if ($request->filled('company_logo_select')) {
            // Jika memilih logo dari dropdown
            $companyLogoPath = $request->company_logo_select;
        } elseif ($request->hasFile('company_logo')) {
            // Jika meng-upload logo baru, hapus logo lama
            if ($assessment->company_logo_path && Storage::disk('public')->exists($assessment->company_logo_path)) {
                Storage::disk('public')->delete($assessment->company_logo_path);
            }
            $companyLogoPath = $request->file('company_logo')->store('images/logos', 'public');
        }

        // =======================
        // Proses Penyimpanan Tanda Tangan
        // =======================
        $signatureImagePath = $assessment->signature_image_path; // Default jika tidak ada perubahan
        if ($request->filled('signature_image_select')) {
            // Jika memilih tanda tangan dari dropdown
            $signatureImagePath = $request->signature_image_select;
        } elseif ($request->hasFile('signature_image')) {
            // Jika meng-upload tanda tangan baru, hapus tanda tangan lama
            if ($assessment->signature_image_path && Storage::disk('public')->exists($assessment->signature_image_path)) {
                Storage::disk('public')->delete($assessment->signature_image_path);
            }
            $signatureImagePath = $request->file('signature_image')->store('images/signature', 'public');
        }

        // =======================
        // Menyusun Data Aspek Penilaian dan Menghitung Total Nilai
        // =======================
        $data = [];
        $total = 0;

        // Pastikan jumlah aspek dan nilai sesuai
        foreach ($validated['aspek'] as $i => $aspek) {
            // Pastikan nilai valid (antara 0 - 100)
            $nilai = (float)($validated['nilai'][$i] ?? 0);
            $nilai = max(0, min(100, $nilai)); // Membatasi nilai antara 0 dan 100
            
            // Tambahkan data aspek dan nilai ke array
            $data[] = ['aspek' => $aspek, 'nilai' => $nilai];
            $total += $nilai;
        }

        $aspekPenilaian = json_encode($data);

        // =======================
        // Memperbarui Data Penilaian
        // =======================
        $assessment->update([
            'fullname' => $validated['fullname'],
            'nim_or_nis' => $validated['nim_or_nis'] ?? null,
            'study_program' => $validated['study_program'] ?? null,
            'div' => $validated['div'] ?? 'Content Writer',
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'signature_name' => $validated['signature_name'] ?? null,
            'signature_position' => $validated['signature_position'] ?? null,
            'company_logo_path' => $companyLogoPath,
            'signature_image_path' => $signatureImagePath,
            'aspek_penilaian' => $aspekPenilaian,
            'rata_rata' => round($total / count($validated['aspek']), 2),
        ]);

        // Redirect ke halaman daftar penilaian dengan pesan sukses
        return redirect()->route('interns.assessment.index')->with('success', 'Penilaian berhasil diperbarui.');
    }




    // ===========================
    // DESTROY
    // ===========================
    public function destroy($id)
    {
        $assessment = InternAssessment::findOrFail($id);

        if ($assessment->company_logo_path && Storage::disk('public')->exists($assessment->company_logo_path)) {
            Storage::disk('public')->delete($assessment->company_logo_path);
        }

        if ($assessment->signature_image_path && Storage::disk('public')->exists($assessment->signature_image_path)) {
            Storage::disk('public')->delete($assessment->signature_image_path);
        }

        $assessment->delete();

        return redirect()->route('interns.assessment.index')->with('success', 'Penilaian berhasil dihapus.');
    }
}
