<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternAssessment;
use App\Models\AssessmentSignatorySetting;
use App\Models\InternshipRegistration as IR;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Str;

class InternAssessmentController extends Controller
{
    // =========================================================================
    // Helpers
    // =========================================================================

    private function publicUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (!Storage::disk('public')->exists($path)) return null;
        return url(Storage::url($path));
    }

    private function imageDataUri(string $path): ?string
    {
        if (!file_exists($path)) return null;
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    private function resolveLogoAndSignature(Request $request, ?string $savedLogoPath, ?string $savedSigPath): array
    {
        // Logo
        $logoPath = $savedLogoPath;
        if ($request->filled('company_logo_select')) {
            $logoPath = $request->company_logo_select;
        } elseif ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store('images/logos', 'public');
        }

        // Tanda tangan
        $sigPath = $savedSigPath;
        if ($request->filled('signature_image_select')) {
            $sigPath = $request->signature_image_select;
        } elseif ($request->hasFile('signature_image')) {
            $sigPath = $request->file('signature_image')->store('images/signature', 'public');
        }

        return [$logoPath, $sigPath];
    }

    private function buildAspekData(array $aspeks, array $nilais): array
    {
        $data  = [];
        $total = 0;
        foreach ($aspeks as $i => $aspek) {
            $nilai  = max(0, min(100, (float) ($nilais[$i] ?? 0)));
            $data[] = ['aspek' => $aspek, 'nilai' => $nilai];
            $total += $nilai;
        }
        $avg = count($aspeks) > 0 ? round($total / count($aspeks), 2) : 0;
        return [$data, $avg];
    }

    private function getDivisionOptions(): array
    {
        return [
            'Project Manager',
            'Administration',
            'Human Resources (HR)',
            'UI/UX',
            'Programmer (Front End / Backend)',
            'Photographer',
            'Videographer',
            'Graphic Designer',
            'Social Media Specialist',
            'Content Writer',
            'Content Planner',
            'Sales & Marketing',
            'Public Relations (Marcomm)',
            'Digital Marketing',
            'TikTok Creator',
            'Welding',
            'Customer Service',
            'Lainnya',
        ];
    }

    private function getDefaultAspects(): array
    {
        return [
            'Content Writer' => [
                ['aspek' => 'Copywriting',    'nilai' => 95],
                ['aspek' => 'Branding',        'nilai' => 95],
                ['aspek' => 'Riset Konten',    'nilai' => 95],
                ['aspek' => 'Kedisiplinan',    'nilai' => 95],
                ['aspek' => 'Kreativitas',     'nilai' => 95],
                ['aspek' => 'Kerjasama',       'nilai' => 95],
                ['aspek' => 'Kehadiran',       'nilai' => 95],
            ],
            'Programmer' => [
                ['aspek' => 'Coding Front/Backend', 'nilai' => 95],
                ['aspek' => 'Database',              'nilai' => 95],
                ['aspek' => 'Debugging',             'nilai' => 95],
                ['aspek' => 'Problem Solving',       'nilai' => 95],
                ['aspek' => 'Kedisiplinan',          'nilai' => 95],
                ['aspek' => 'Kerjasama',             'nilai' => 95],
                ['aspek' => 'Kehadiran',             'nilai' => 95],
            ],
            'UI/UX' => [
                ['aspek' => 'User Research',          'nilai' => 95],
                ['aspek' => 'Wireframing & Prototyping','nilai' => 95],
                ['aspek' => 'Visual Design',          'nilai' => 95],
                ['aspek' => 'Layout & Typography',    'nilai' => 95],
                ['aspek' => 'Color Theory',           'nilai' => 95],
                ['aspek' => 'Kedisiplinan',           'nilai' => 95],
                ['aspek' => 'Kerjasama',              'nilai' => 95],
                ['aspek' => 'Kehadiran',              'nilai' => 95],
            ],
            'UI/UX Designer' => [
                ['aspek' => 'User Research',          'nilai' => 95],
                ['aspek' => 'Wireframing & Prototyping','nilai' => 95],
                ['aspek' => 'Visual Design',          'nilai' => 95],
                ['aspek' => 'Layout & Typography',    'nilai' => 95],
                ['aspek' => 'Color Theory',           'nilai' => 95],
                ['aspek' => 'Kedisiplinan',           'nilai' => 95],
                ['aspek' => 'Kerjasama',              'nilai' => 95],
                ['aspek' => 'Kehadiran',              'nilai' => 95],
            ],
            'Programmer (Front End / Backend)' => [
                ['aspek' => 'Coding Front/Backend', 'nilai' => 95],
                ['aspek' => 'Database',              'nilai' => 95],
                ['aspek' => 'Debugging',             'nilai' => 95],
                ['aspek' => 'Problem Solving',       'nilai' => 95],
                ['aspek' => 'Kedisiplinan',          'nilai' => 95],
                ['aspek' => 'Kerjasama',             'nilai' => 95],
                ['aspek' => 'Kehadiran',             'nilai' => 95],
            ],
            'Graphic Designer' => [
                ['aspek' => 'Desain Visual',          'nilai' => 95],
                ['aspek' => 'Kreativitas & Inovasi',  'nilai' => 95],
                ['aspek' => 'Typography & Warna',     'nilai' => 95],
                ['aspek' => 'Infografis & Branding',  'nilai' => 95],
                ['aspek' => 'Kedisiplinan',           'nilai' => 95],
                ['aspek' => 'Kerjasama',              'nilai' => 95],
                ['aspek' => 'Kehadiran',              'nilai' => 95],
            ],
            'Digital Marketing' => [
                ['aspek' => 'Strategi Konten',           'nilai' => 95],
                ['aspek' => 'Analisis Data & Keyword',   'nilai' => 95],
                ['aspek' => 'Social Media Marketing',    'nilai' => 95],
                ['aspek' => 'Copywriting & Engagement',  'nilai' => 95],
                ['aspek' => 'Kedisiplinan',              'nilai' => 95],
                ['aspek' => 'Kreativitas',               'nilai' => 95],
                ['aspek' => 'Kerjasama',                 'nilai' => 95],
                ['aspek' => 'Kehadiran',                 'nilai' => 95],
            ],
            'Project Manager' => [
                ['aspek' => 'Perencanaan Proyek', 'nilai' => 95],
                ['aspek' => 'Koordinasi Tim',     'nilai' => 95],
                ['aspek' => 'Manajemen Waktu',    'nilai' => 95],
                ['aspek' => 'Komunikasi',         'nilai' => 95],
                ['aspek' => 'Kedisiplinan',       'nilai' => 95],
                ['aspek' => 'Kerjasama',          'nilai' => 95],
                ['aspek' => 'Kehadiran',          'nilai' => 95],
            ],
            'Administration' => [
                ['aspek' => 'Administrasi',  'nilai' => 95],
                ['aspek' => 'Pengarsipan',   'nilai' => 95],
                ['aspek' => 'Komunikasi',    'nilai' => 95],
                ['aspek' => 'Koordinasi',    'nilai' => 95],
                ['aspek' => 'Kedisiplinan',  'nilai' => 95],
                ['aspek' => 'Kerjasama',     'nilai' => 95],
                ['aspek' => 'Kehadiran',     'nilai' => 95],
            ],
            'Human Resources (HR)' => [
                ['aspek' => 'Rekrutmen',    'nilai' => 95],
                ['aspek' => 'Pelatihan',    'nilai' => 95],
                ['aspek' => 'Komunikasi',   'nilai' => 95],
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kerjasama',    'nilai' => 95],
                ['aspek' => 'Kehadiran',    'nilai' => 95],
            ],
            'Photographer' => [
                ['aspek' => 'Pemotretan',        'nilai' => 95],
                ['aspek' => 'Komposisi Visual',  'nilai' => 95],
                ['aspek' => 'Editing Foto',      'nilai' => 95],
                ['aspek' => 'Kreativitas',       'nilai' => 95],
                ['aspek' => 'Kedisiplinan',      'nilai' => 95],
                ['aspek' => 'Kehadiran',         'nilai' => 95],
            ],
            'Videographer' => [
                ['aspek' => 'Pengambilan Video', 'nilai' => 95],
                ['aspek' => 'Editing Video',     'nilai' => 95],
                ['aspek' => 'Storytelling',      'nilai' => 95],
                ['aspek' => 'Kreativitas',       'nilai' => 95],
                ['aspek' => 'Kedisiplinan',      'nilai' => 95],
                ['aspek' => 'Kehadiran',         'nilai' => 95],
            ],
            'Social Media Specialist' => [
                ['aspek' => 'Strategi Media Sosial', 'nilai' => 95],
                ['aspek' => 'Content Engagement',    'nilai' => 95],
                ['aspek' => 'Analisis Data',         'nilai' => 95],
                ['aspek' => 'Kedisiplinan',          'nilai' => 95],
                ['aspek' => 'Kerjasama',             'nilai' => 95],
                ['aspek' => 'Kehadiran',             'nilai' => 95],
            ],
            'Content Planner' => [
                ['aspek' => 'Perencanaan Konten', 'nilai' => 95],
                ['aspek' => 'Riset Audience',     'nilai' => 95],
                ['aspek' => 'Kalender Konten',    'nilai' => 95],
                ['aspek' => 'Kolaborasi',         'nilai' => 95],
                ['aspek' => 'Kedisiplinan',       'nilai' => 95],
                ['aspek' => 'Kehadiran',          'nilai' => 95],
            ],
            'Sales & Marketing' => [
                ['aspek' => 'Strategi Penjualan', 'nilai' => 95],
                ['aspek' => 'Negosiasi',          'nilai' => 95],
                ['aspek' => 'Analisis Pasar',     'nilai' => 95],
                ['aspek' => 'Komunikasi',         'nilai' => 95],
                ['aspek' => 'Kedisiplinan',       'nilai' => 95],
                ['aspek' => 'Kehadiran',          'nilai' => 95],
            ],
            'Public Relations (Marcomm)' => [
                ['aspek' => 'Hubungan Media',    'nilai' => 95],
                ['aspek' => 'Komunikasi Publik', 'nilai' => 95],
                ['aspek' => 'Event Support',     'nilai' => 95],
                ['aspek' => 'Kedisiplinan',      'nilai' => 95],
                ['aspek' => 'Kerjasama',         'nilai' => 95],
                ['aspek' => 'Kehadiran',         'nilai' => 95],
            ],
            'TikTok Creator' => [
                ['aspek' => 'Pembuatan Video Pendek', 'nilai' => 95],
                ['aspek' => 'Kreativitas',            'nilai' => 95],
                ['aspek' => 'Editing Singkat',        'nilai' => 95],
                ['aspek' => 'Engagement',             'nilai' => 95],
                ['aspek' => 'Kedisiplinan',           'nilai' => 95],
                ['aspek' => 'Kehadiran',              'nilai' => 95],
            ],
            'Welding' => [
                ['aspek' => 'Keterampilan Las', 'nilai' => 95],
                ['aspek' => 'Keamanan Kerja',   'nilai' => 95],
                ['aspek' => 'Kualitas Jahitan', 'nilai' => 95],
                ['aspek' => 'Kedisiplinan',     'nilai' => 95],
                ['aspek' => 'Kerjasama',        'nilai' => 95],
                ['aspek' => 'Kehadiran',        'nilai' => 95],
            ],
            'Customer Service' => [
                ['aspek' => 'Pelayanan Pelanggan',  'nilai' => 95],
                ['aspek' => 'Komunikasi',           'nilai' => 95],
                ['aspek' => 'Penyelesaian Masalah', 'nilai' => 95],
                ['aspek' => 'Empati',               'nilai' => 95],
                ['aspek' => 'Kedisiplinan',         'nilai' => 95],
                ['aspek' => 'Kehadiran',            'nilai' => 95],
            ],
            'Lainnya' => [
                ['aspek' => 'Kedisiplinan', 'nilai' => 95],
                ['aspek' => 'Kerjasama',    'nilai' => 95],
                ['aspek' => 'Kehadiran',    'nilai' => 95],
            ],
        ];
    }

    /** Map internship_interest slug → division option label */
    private function mapInterestToDivision(string $raw): ?string
    {
        $map = [
            'project-manager'                   => 'Project Manager',
            'administration'                    => 'Administration',
            'administrasi'                      => 'Administration',
            'hr'                                => 'Human Resources (HR)',
            'human resources (hr)'              => 'Human Resources (HR)',
            'uiux'                              => 'UI/UX',
            'ui/ux'                             => 'UI/UX',
            'programmer'                        => 'Programmer (Front End / Backend)',
            'programmer (front end / backend)'  => 'Programmer (Front End / Backend)',
            'photographer'                      => 'Photographer',
            'fotografer'                        => 'Photographer',
            'videographer'                      => 'Videographer',
            'videografer'                       => 'Videographer',
            'graphic-designer'                  => 'Graphic Designer',
            'desainer grafis'                   => 'Graphic Designer',
            'social-media-specialist'           => 'Social Media Specialist',
            'content-writer'                    => 'Content Writer',
            'content-planner'                   => 'Content Planner',
            'marketing-and-sales'               => 'Sales & Marketing',
            'sales & marketing'                 => 'Sales & Marketing',
            'public-relation'                   => 'Public Relations (Marcomm)',
            'public relations (marcomm)'        => 'Public Relations (Marcomm)',
            'digital-marketing'                 => 'Digital Marketing',
            'tiktok-creator'                    => 'TikTok Creator',
            'welding'                           => 'Welding',
            'pengelasan'                        => 'Welding',
            'customer-service'                  => 'Customer Service',
        ];
        $key = strtolower(trim($raw));
        return $map[$key] ?? (in_array($raw, $this->getDivisionOptions()) ? $raw : null);
    }

    // =========================================================================
    // API: pemagang selesai berdasarkan brand
    // =========================================================================

    /**
     * GET /admin/interns/assessment/interns-by-brand?brand=XXX
     */
    public function getInternsByBrand(Request $request)
    {
        $brand = $request->query('brand');
        if (!$brand) {
            return response()->json(['interns' => [], 'signatory' => null]);
        }

        $interns = IR::query()
            ->where('internship_status', IR::STATUS_COMPLETED)
            ->where('brand', $brand)
            ->select('id', 'fullname', 'student_id', 'study_program', 'institution_name',
                     'start_date', 'end_date', 'internship_interest', 'brand')
            ->latest('id')
            ->get()
            ->map(fn ($r) => [
                'id'                  => $r->id,
                'fullname'            => $r->fullname,
                'student_id'          => $r->student_id ?? '',
                'study_program'       => $r->study_program ?? '',
                'institution_name'    => $r->institution_name ?? '',
                'start_date'          => $r->start_date ?? '',
                'end_date'            => $r->end_date ?? '',
                'internship_interest' => $r->internship_interest ?? '',
                'brand'               => $r->brand ?? '',
                'has_assessment'      => InternAssessment::where('intern_id', $r->id)->exists(),
            ]);

        // Kembalikan juga setting penandatangan yang sudah tersimpan untuk brand ini
        $signatory = AssessmentSignatorySetting::where('brand', $brand)->first();

        return response()->json([
            'interns'   => $interns,
            'signatory' => $signatory ? [
                'company_name'         => $signatory->company_name,
                'company_address'      => $signatory->company_address,
                'signature_name'       => $signatory->signature_name,
                'signature_position'   => $signatory->signature_position,
                'signature_image_path' => $signatory->signature_image_path,
                'company_logo_path'    => $signatory->company_logo_path,
            ] : null,
        ]);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index()
    {
        $data = InternAssessment::latest()->get();
        return view('admin.interns.index_assessment', compact('data'));
    }

    // =========================================================================
    // CREATE (step 1: pilih brand + pemagang)
    // =========================================================================

    public function create(Request $request)
    {
        $divisions = $this->getDivisionOptions();

        // Ambil semua brand yang punya pemagang completed
        $brands = IR::query()
            ->where('internship_status', IR::STATUS_COMPLETED)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->values();

        // Koleksi logo & tanda tangan
        $logos = collect(
            Storage::disk('public')->exists('images/logos')
                ? Storage::disk('public')->files('images/logos')
                : []
        )->filter(fn($f) => preg_match('/\.(png|jpe?g|gif)$/i', $f))->values()->toArray();

        $signatures = collect(
            Storage::disk('public')->exists('images/signature')
                ? Storage::disk('public')->files('images/signature')
                : []
        )->filter(fn($f) => preg_match('/\.(png|jpe?g|gif)$/i', $f))->values()->toArray();

        // Kalau ada intern_id di URL, kita langsung skip ke mode single
        $selectedIntern  = null;
        $selectedBrand   = null;
        $signatory       = null;
        $aspects         = $this->getDefaultAspects()['Content Writer'];
        $division        = 'Content Writer';

        if ($request->filled('intern_id')) {
            $selectedIntern = IR::where('internship_status', IR::STATUS_COMPLETED)
                ->find((int) $request->get('intern_id'));

            if ($selectedIntern) {
                $selectedBrand = $selectedIntern->brand;
                $signatory     = $selectedBrand
                    ? AssessmentSignatorySetting::where('brand', $selectedBrand)->first()
                    : null;

                // Map interest → division
                $mapped = $this->mapInterestToDivision($selectedIntern->internship_interest ?? '');
                if ($mapped) {
                    $division = $mapped;
                    $aspects  = $this->getDefaultAspects()[$division] ?? $this->getDefaultAspects()['Content Writer'];
                }
            }
        }

        return view('admin.interns.create_assessment', compact(
            'brands', 'divisions', 'logos', 'signatures',
            'selectedIntern', 'selectedBrand', 'signatory',
            'aspects', 'division'
        ));
    }

    // =========================================================================
    // STORE (simpan 1 penilaian)
    // =========================================================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            'intern_id'           => 'nullable|integer|exists:internship_registrations,id',
            'fullname'            => 'required|string|max:255',
            'nim_or_nis'          => 'nullable|string|max:50',
            'study_program'       => 'nullable|string|max:255',
            'div'                 => 'nullable|string|max:255',
            'company_name'        => 'nullable|string|max:255',
            'company_address'     => 'nullable|string|max:1000',
            'signature_name'      => 'nullable|string|max:255',
            'signature_position'  => 'nullable|string|max:255',
            'company_logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aspek'               => 'required|array',
            'nilai'               => 'required|array',
            'save_signatory'      => 'nullable|boolean',
        ]);

        // Resolve logo & tanda tangan
        [$logoPath, $sigPath] = $this->resolveLogoAndSignature($request, null, null);

        // Susun aspek penilaian
        [$data, $avg] = $this->buildAspekData($validated['aspek'], $validated['nilai']);

        $assessment = InternAssessment::create([
            'intern_id'             => $request->input('intern_id'),
            'fullname'              => $validated['fullname'],
            'nim_or_nis'            => $validated['nim_or_nis'] ?? null,
            'study_program'         => $validated['study_program'] ?? null,
            'div'                   => $validated['div'] ?? 'Content Writer',
            'company_name'          => $validated['company_name'] ?? null,
            'company_address'       => $validated['company_address'] ?? null,
            'signature_name'        => $validated['signature_name'] ?? null,
            'signature_position'    => $validated['signature_position'] ?? null,
            'company_logo_path'     => $logoPath,
            'signature_image_path'  => $sigPath,
            'aspek_penilaian'       => json_encode($data),
            'rata_rata'             => $avg,
        ]);

        // Simpan setting penandatangan per brand kalau diminta
        if ($request->input('save_signatory') && $request->filled('brand')) {
            $this->saveSignatorySetting($request, $request->input('brand'), $logoPath, $sigPath);
        }

        // Tulis ke document_downloads
        if ($assessment->intern_id) {
            $intern = IR::find($assessment->intern_id);
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

        return redirect()->route('interns.assessment.index')
            ->with('success', '✅ Penilaian berhasil disimpan.' .
                ($assessment->intern_id ? ' Surat penilaian tersedia di halaman Dokumen pemagang.' : ''));
    }

    // =========================================================================
    // STORE BULK — simpan banyak penilaian sekaligus (1 per pemagang)
    // =========================================================================

    /**
     * POST /admin/interns/assessment/store-bulk
     * Form dikirim sebagai array per-intern: interns[0][fullname], interns[0][aspek][], dst.
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'brand'          => 'required|string',
            'interns'        => 'required|array|min:1',
            'interns.*.intern_id'          => 'required|integer|exists:internship_registrations,id',
            'interns.*.fullname'           => 'required|string|max:255',
            'interns.*.nim_or_nis'         => 'nullable|string|max:50',
            'interns.*.study_program'      => 'nullable|string|max:255',
            'interns.*.div'                => 'nullable|string|max:255',
            'interns.*.aspek'              => 'required|array',
            'interns.*.nilai'              => 'required|array',
            // Penandatangan (sama untuk semua dalam brand)
            'company_name'       => 'nullable|string|max:255',
            'company_address'    => 'nullable|string|max:1000',
            'signature_name'     => 'nullable|string|max:255',
            'signature_position' => 'nullable|string|max:255',
            'company_logo'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'save_signatory'     => 'nullable|boolean',
        ]);

        // Resolve logo & tanda tangan (shared untuk semua pemagang dalam 1 brand)
        [$logoPath, $sigPath] = $this->resolveLogoAndSignature($request, null, null);

        $brand       = $request->input('brand');
        $companyName = $request->input('company_name', $brand);
        $companyAddr = $request->input('company_address');
        $sigName     = $request->input('signature_name');
        $sigPos      = $request->input('signature_position');

        $saved  = 0;
        $errors = [];

        foreach ($request->input('interns') as $idx => $item) {
            try {
                [$data, $avg] = $this->buildAspekData($item['aspek'] ?? [], $item['nilai'] ?? []);

                $assessment = InternAssessment::create([
                    'intern_id'             => $item['intern_id'],
                    'fullname'              => $item['fullname'],
                    'nim_or_nis'            => $item['nim_or_nis'] ?? null,
                    'study_program'         => $item['study_program'] ?? null,
                    'div'                   => $item['div'] ?? 'Content Writer',
                    'company_name'          => $companyName,
                    'company_address'       => $companyAddr,
                    'signature_name'        => $sigName,
                    'signature_position'    => $sigPos,
                    'company_logo_path'     => $logoPath,
                    'signature_image_path'  => $sigPath,
                    'aspek_penilaian'       => json_encode($data),
                    'rata_rata'             => $avg,
                ]);

                // Tulis ke document_downloads
                $intern = IR::find($item['intern_id']);
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

                $saved++;
            } catch (\Throwable $e) {
                $errors[] = ($item['fullname'] ?? "Pemagang #{$idx}") . ': ' . $e->getMessage();
                \Log::error('Bulk assessment store gagal', ['idx' => $idx, 'err' => $e->getMessage()]);
            }
        }

        // Simpan setting penandatangan per brand
        if ($request->input('save_signatory')) {
            $this->saveSignatorySetting($request, $brand, $logoPath, $sigPath);
        }

        $msg = "✅ {$saved} penilaian berhasil disimpan.";
        if (!empty($errors)) {
            $msg .= ' Gagal: ' . implode('; ', $errors);
        }

        return redirect()->route('interns.assessment.index')->with('success', $msg);
    }

    // =========================================================================
    // SAVE SIGNATORY SETTING PER BRAND
    // =========================================================================

    /**
     * POST /admin/interns/assessment/save-signatory
     * AJAX — simpan setting penandatangan untuk brand tertentu.
     */
    public function saveSignatoryAjax(Request $request)
    {
        $request->validate([
            'brand'              => 'required|string',
            'company_name'       => 'nullable|string|max:255',
            'company_address'    => 'nullable|string|max:1000',
            'signature_name'     => 'nullable|string|max:255',
            'signature_position' => 'nullable|string|max:255',
            'company_logo'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $brand    = $request->input('brand');
        $existing = AssessmentSignatorySetting::where('brand', $brand)->first() ?? new AssessmentSignatorySetting(['brand' => $brand]);

        [$logoPath, $sigPath] = $this->resolveLogoAndSignature(
            $request,
            $existing->company_logo_path,
            $existing->signature_image_path
        );

        $existing->fill([
            'brand'                => $brand,
            'company_name'         => $request->input('company_name', $existing->company_name),
            'company_address'      => $request->input('company_address', $existing->company_address),
            'signature_name'       => $request->input('signature_name', $existing->signature_name),
            'signature_position'   => $request->input('signature_position', $existing->signature_position),
            'company_logo_path'    => $logoPath,
            'signature_image_path' => $sigPath,
        ])->save();

        return response()->json(['success' => true, 'message' => 'Setting penandatangan berhasil disimpan.']);
    }

    // =========================================================================
    // EDIT & UPDATE
    // =========================================================================

    public function edit($id)
    {
        $assessment  = InternAssessment::findOrFail($id);
        $divisions   = $this->getDivisionOptions();
        $defaultAspects = $this->getDefaultAspects();

        $aspekPenilaian = json_decode($assessment->aspek_penilaian, true);
        if (!is_array($aspekPenilaian)) {
            $aspekPenilaian = $defaultAspects[$assessment->div] ?? $defaultAspects['Content Writer'];
        }

        $logos = collect(Storage::disk('public')->files('images/logos'))
            ->filter(fn($f) => preg_match('/\.(png|jpe?g)$/i', $f))->values()->toArray();
        $signatures = collect(Storage::disk('public')->files('images/signature'))
            ->filter(fn($f) => preg_match('/\.(png|jpe?g)$/i', $f))->values()->toArray();

        $interns = IR::whereIn('internship_status', [IR::STATUS_ACTIVE, IR::STATUS_COMPLETED])
            ->orderBy('fullname', 'asc')
            ->get(['id', 'fullname', 'student_id', 'study_program']);

        return view('admin.interns.edit_assessment', [
            'assessment'     => $assessment,
            'aspekPenilaian' => $aspekPenilaian,
            'divisions'      => $divisions,
            'logos'          => $logos,
            'signatures'     => $signatures,
            'interns'        => $interns,
        ]);
    }

    public function update(Request $request, $id)
    {
        $assessment = InternAssessment::findOrFail($id);

        $validated = $request->validate([
            'fullname'           => 'required|string|max:255',
            'nim_or_nis'         => 'nullable|string|max:50',
            'study_program'      => 'nullable|string|max:255',
            'div'                => 'nullable|string|max:255',
            'company_name'       => 'nullable|string|max:255',
            'company_address'    => 'nullable|string|max:1000',
            'signature_name'     => 'nullable|string|max:255',
            'signature_position' => 'nullable|string|max:255',
            'company_logo'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aspek'              => 'required|array',
            'nilai'              => 'required|array',
        ]);

        [$logoPath, $sigPath] = $this->resolveLogoAndSignature(
            $request,
            $assessment->company_logo_path,
            $assessment->signature_image_path
        );

        [$data, $avg] = $this->buildAspekData($validated['aspek'], $validated['nilai']);

        $assessment->update([
            'fullname'              => $validated['fullname'],
            'nim_or_nis'            => $validated['nim_or_nis'] ?? null,
            'study_program'         => $validated['study_program'] ?? null,
            'div'                   => $validated['div'] ?? 'Content Writer',
            'company_name'          => $validated['company_name'] ?? null,
            'company_address'       => $validated['company_address'] ?? null,
            'signature_name'        => $validated['signature_name'] ?? null,
            'signature_position'    => $validated['signature_position'] ?? null,
            'company_logo_path'     => $logoPath,
            'signature_image_path'  => $sigPath,
            'aspek_penilaian'       => json_encode($data),
            'rata_rata'             => $avg,
        ]);

        return redirect()->route('interns.assessment.index')->with('success', 'Penilaian berhasil diperbarui.');
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy($id)
    {
        $assessment = InternAssessment::findOrFail($id);

        // Hapus file aset kalau upload khusus (bukan shared)
        if ($assessment->company_logo_path && Storage::disk('public')->exists($assessment->company_logo_path)) {
            // Hanya hapus kalau tidak ada assessment lain yang pakai file yang sama
            $count = InternAssessment::where('company_logo_path', $assessment->company_logo_path)
                ->where('id', '!=', $assessment->id)->count();
            if ($count === 0) {
                Storage::disk('public')->delete($assessment->company_logo_path);
            }
        }

        if ($assessment->signature_image_path && Storage::disk('public')->exists($assessment->signature_image_path)) {
            $count = InternAssessment::where('signature_image_path', $assessment->signature_image_path)
                ->where('id', '!=', $assessment->id)->count();
            if ($count === 0) {
                Storage::disk('public')->delete($assessment->signature_image_path);
            }
        }

        $assessment->delete();

        return redirect()->route('interns.assessment.index')->with('success', 'Penilaian berhasil dihapus.');
    }

    // =========================================================================
    // PDF PREVIEW & DOWNLOAD
    // =========================================================================

    public function previewPDF($id)
    {
        $assessment = InternAssessment::findOrFail($id);

        $logoFile = public_path('storage/' . ($assessment->company_logo_path ?? 'images/logos/logo_seveninc.png'));
        $logoSrc  = file_exists($logoFile)
            ? asset('storage/' . ($assessment->company_logo_path ?? 'images/logos/logo_seveninc.png'))
            : asset('storage/images/logos/logo_seveninc.png');

        $sigFile = public_path('storage/' . ($assessment->signature_image_path ?? 'images/signature/ttd_rekariodanny.png'));
        $sigSrc  = file_exists($sigFile)
            ? asset('storage/' . ($assessment->signature_image_path ?? 'images/signature/ttd_rekariodanny.png'))
            : asset('storage/images/signature/ttd_rekariodanny.png');

        return view('admin.interns.pdf_assessment', [
            'assessment' => $assessment,
            'logoSrc'    => $logoSrc,
            'sigSrc'     => $sigSrc,
            'autoPrint'  => false,
        ]);
    }

    public function downloadPDF($id)
    {
        $assessment = InternAssessment::findOrFail($id);

        $logoFile     = public_path('storage/' . ($assessment->company_logo_path ?? 'images/logos/logo_seveninc.png'));
        $fallbackLogo = public_path('storage/images/logos/logo_seveninc.png');
        $logoSrc      = $this->imageDataUri($logoFile) ?: $this->imageDataUri($fallbackLogo);

        $sigFile          = public_path('storage/' . ($assessment->signature_image_path ?? 'images/signature/ttd_rekariodanny.png'));
        $fallbackSignature = public_path('storage/images/signature/ttd_rekariodanny.png');
        $sigSrc           = $this->imageDataUri($sigFile) ?: $this->imageDataUri($fallbackSignature);

        $empty     = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAn0B9WYaBZMAAAAASUVORK5CYII=';
        $logoSrc   = $logoSrc   ?: $empty;
        $sigSrc    = $sigSrc    ?: $empty;

        $assessmentSlug = Str::slug($assessment->fullname, '-');
        $filename       = "intern-assessment-{$assessmentSlug}.pdf";

        $htmlContent = view('admin.interns.pdf_assessment', [
            'assessment' => $assessment,
            'logoSrc'    => $logoSrc,
            'sigSrc'     => $sigSrc,
            'autoPrint'  => false,
        ])->render();

        $tmpPath = storage_path('app/tmp/' . $filename);
        if (!is_dir(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0775, true);
        }

        Browsershot::html($htmlContent)
            ->emulateMedia('print')
            ->format('A4')
            ->landscape()
            ->margins(0, 0, 0, 0)
            ->timeout(180)
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
            ->savePdf($tmpPath);

        return response()->download($tmpPath, $filename, ['Content-Type' => 'application/pdf'])
            ->deleteFileAfterSend(true);
    }

    // =========================================================================
    // AJAX — aspek berdasarkan divisi
    // =========================================================================

    public function getAspekByDivision(Request $request)
    {
        $division = $request->get('division', 'Content Writer');
        $all = $this->getDefaultAspects();
        $division = array_key_exists($division, $all) ? $division : 'Lainnya';
        return response()->json(['aspek' => $all[$division]]);
    }

    // =========================================================================
    // Private helper: simpan setting penandatangan per brand
    // =========================================================================

    private function saveSignatorySetting(Request $request, string $brand, ?string $logoPath, ?string $sigPath): void
    {
        $existing = AssessmentSignatorySetting::where('brand', $brand)->first()
            ?? new AssessmentSignatorySetting(['brand' => $brand]);

        $existing->fill([
            'brand'                => $brand,
            'company_name'         => $request->input('company_name', $brand),
            'company_address'      => $request->input('company_address', $existing->company_address),
            'signature_name'       => $request->input('signature_name', $existing->signature_name),
            'signature_position'   => $request->input('signature_position', $existing->signature_position),
            'company_logo_path'    => $logoPath ?? $existing->company_logo_path,
            'signature_image_path' => $sigPath  ?? $existing->signature_image_path,
        ])->save();
    }
}
