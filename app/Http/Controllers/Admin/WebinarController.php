<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Models\WebinarAttendance;
use App\Models\InternshipRegistration as IR;
use App\Models\Certificate;
use App\Models\DocumentDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebinarController extends Controller
{
    // ===== CRUD WEBINAR =====

    public function index()
    {
        $webinars = Webinar::withCount(['attendances', 'approvedAttendances', 'pendingAttendances'])
            ->latest()
            ->paginate(20);

        return view('admin.webinars.index', compact('webinars'));
    }

    public function create()
    {
        $assetOptions = $this->loadAssets();
        $brands = $this->brandList();
        return view('admin.webinars.create', compact('assetOptions', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'nullable|string',
            'event_date'                 => 'required|date',
            'event_end_date'             => 'nullable|date|after_or_equal:event_date',
            'zoom_link'                  => 'nullable|url|max:500',
            'platform'                   => 'nullable|string|max:50',
            'is_active'                  => 'boolean',
            'certificate_background'     => 'nullable|string',
            'certificate_logo1'          => 'nullable|string',
            'certificate_logo2'          => 'nullable|string',
            'certificate_signature1'     => 'nullable|string',
            'certificate_signature2'     => 'nullable|string',
            'certificate_signatory1_name'=> 'required|string|max:255',
            'certificate_signatory1_role'=> 'required|string|max:255',
            'certificate_signatory2_name'=> 'nullable|string|max:255',
            'certificate_signatory2_role'=> 'nullable|string|max:255',
            'certificate_company'        => 'nullable|string|max:255',
            'certificate_city'           => 'nullable|string|max:255',
            'certificate_brand'          => 'nullable|string|max:10',
            'certificate_description'    => 'nullable|string|max:1000',
            'allowed_brands'             => 'nullable|array',
            'allowed_brands.*'           => 'string|max:10',
            // File upload langsung
            'upload_background'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
            'upload_logo1'               => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'upload_logo2'               => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'upload_signature1'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'upload_signature2'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active']  = $request->boolean('is_active', true);

        // Proses upload langsung — override pilihan dropdown jika file baru diupload
        $validated = $this->processAssetUploads($request, $validated);

        // allowed_brands: null = semua, array kosong atau mode=all = null
        $mode = $request->input('_allowed_brands_mode', 'all');
        $validated['allowed_brands'] = ($mode === 'specific' && !empty($validated['allowed_brands']))
            ? array_values(array_unique($validated['allowed_brands']))
            : null;

        // Sinkron certificate_company dengan nama brand
        if (!empty($validated['certificate_brand'])) {
            $validated['certificate_company'] = Webinar::brandLabel($validated['certificate_brand']);
        }

        Webinar::create($validated);

        return redirect()->route('admin.webinars.index')
            ->with('success', '✅ Webinar berhasil dibuat.');
    }

    public function edit(Webinar $webinar)
    {
        $assetOptions = $this->loadAssets();
        $brands = $this->brandList();
        return view('admin.webinars.edit', compact('webinar', 'assetOptions', 'brands'));
    }

    public function update(Request $request, Webinar $webinar)
    {
        $validated = $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'nullable|string',
            'event_date'                 => 'required|date',
            'event_end_date'             => 'nullable|date|after_or_equal:event_date',
            'zoom_link'                  => 'nullable|url|max:500',
            'platform'                   => 'nullable|string|max:50',
            'is_active'                  => 'boolean',
            'certificate_background'     => 'nullable|string',
            'certificate_logo1'          => 'nullable|string',
            'certificate_logo2'          => 'nullable|string',
            'certificate_signature1'     => 'nullable|string',
            'certificate_signature2'     => 'nullable|string',
            'certificate_signatory1_name'=> 'required|string|max:255',
            'certificate_signatory1_role'=> 'required|string|max:255',
            'certificate_signatory2_name'=> 'nullable|string|max:255',
            'certificate_signatory2_role'=> 'nullable|string|max:255',
            'certificate_company'        => 'nullable|string|max:255',
            'certificate_city'           => 'nullable|string|max:255',
            'certificate_brand'          => 'nullable|string|max:10',
            'certificate_description'    => 'nullable|string|max:1000',
            'allowed_brands'             => 'nullable|array',
            'allowed_brands.*'           => 'string|max:10',
            // File upload langsung
            'upload_background'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
            'upload_logo1'               => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'upload_logo2'               => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'upload_signature1'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'upload_signature2'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Proses upload langsung — override pilihan dropdown jika file baru diupload
        $validated = $this->processAssetUploads($request, $validated);

        // allowed_brands
        $mode = $request->input('_allowed_brands_mode', 'all');
        $validated['allowed_brands'] = ($mode === 'specific' && !empty($validated['allowed_brands']))
            ? array_values(array_unique($validated['allowed_brands']))
            : null;

        // Sinkron certificate_company dengan nama brand
        if (!empty($validated['certificate_brand'])) {
            $validated['certificate_company'] = Webinar::brandLabel($validated['certificate_brand']);
        }

        $webinar->update($validated);

        return redirect()->route('admin.webinars.index')
            ->with('success', 'Webinar berhasil diperbarui.');
    }

    public function destroy(Webinar $webinar)
    {
        $webinar->delete();
        return redirect()->route('admin.webinars.index')
            ->with('success', 'Webinar berhasil dihapus.');
    }

    // ===== REVIEW BUKTI KEHADIRAN =====

    /**
     * Daftar semua bukti kehadiran untuk satu webinar.
     */
    public function attendances(Webinar $webinar)
    {
        $attendances = WebinarAttendance::with('user')
            ->where('webinar_id', $webinar->id)
            ->latest()
            ->paginate(30);

        return view('admin.webinars.attendances', compact('webinar', 'attendances'));
    }

    /**
     * Generate sertifikat untuk SEMUA peserta approved webinar ini.
     * Skip peserta yang sudah punya sertifikat (idempoten).
     */
    public function generateCerts(Webinar $webinar)
    {
        $approvedAttendances = WebinarAttendance::with('user')
            ->where('webinar_id', $webinar->id)
            ->where('status', WebinarAttendance::STATUS_APPROVED)
            ->get();

        if ($approvedAttendances->isEmpty()) {
            return back()->with('error', 'Tidak ada peserta yang sudah diapprove untuk webinar ini.');
        }

        $generated = 0;
        $skipped   = 0;

        foreach ($approvedAttendances as $attendance) {
            // Skip kalau sudah punya sertifikat
            if ($attendance->certificate_id) {
                $skipped++;
                continue;
            }

            $cert = $this->generateWebinarCertificate($webinar, $attendance->user);

            $attendance->update([
                'certificate_id' => $cert?->id,
                'reviewed_by'    => $attendance->reviewed_by ?? auth()->id(),
                'reviewed_at'    => $attendance->reviewed_at ?? now(),
            ]);

            if ($cert) {
                // Simpan ke document_downloads agar muncul di Dokumen Saya pemagang
                DocumentDownload::firstOrCreate(
                    [
                        'user_id'  => $attendance->user_id,
                        'doc_type' => DocumentDownload::TYPE_SERTIFIKAT_WEBINAR,
                        // Gunakan file_url sebagai unique key per sertifikat
                        'file_url' => route('admin.certificate.pdf', $cert->id),
                    ],
                    [
                        'file_path'     => null,
                        'downloaded_at' => now(),
                        'status'        => 'success',
                    ]
                );
                $generated++;
            }
        }

        $msg = "✅ {$generated} sertifikat berhasil di-generate.";
        if ($skipped > 0) {
            $msg .= " {$skipped} peserta dilewati (sudah punya sertifikat).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Approve bukti kehadiran + generate sertifikat otomatis.
     */
    public function approve(Request $request, Webinar $webinar, WebinarAttendance $attendance)
    {
        if (!$attendance->isPending()) {
            return back()->with('error', 'Bukti kehadiran ini sudah diproses sebelumnya.');
        }

        // Generate sertifikat webinar
        $cert = $this->generateWebinarCertificate($webinar, $attendance->user);

        // Update attendance
        $attendance->update([
            'status'         => WebinarAttendance::STATUS_APPROVED,
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'certificate_id' => $cert?->id,
        ]);

        // Simpan ke document_downloads supaya muncul di Dokumen Saya pemagang
        if ($cert) {
            DocumentDownload::create([
                'user_id'       => $attendance->user_id,
                'doc_type'      => DocumentDownload::TYPE_SERTIFIKAT_WEBINAR,
                'file_path'     => null,
                'file_url'      => route('admin.certificate.pdf', $cert->id),
                'downloaded_at' => now(),
                'status'        => 'success',
            ]);
        }

        return back()->with('success',
            "✅ Bukti kehadiran <strong>{$attendance->user->name}</strong> disetujui. Sertifikat sudah tersedia di Dokumen Saya pemagang."
        );
    }

    /**
     * Reject bukti kehadiran.
     */
    public function reject(Request $request, Webinar $webinar, WebinarAttendance $attendance)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $attendance->update([
            'status'           => WebinarAttendance::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        return back()->with('success', "Bukti kehadiran {$attendance->user->name} ditolak.");
    }

    /**
     * Approve semua yang masih pending sekaligus.
     */
    public function approveAll(Webinar $webinar)
    {
        $pendings = WebinarAttendance::with('user')
            ->where('webinar_id', $webinar->id)
            ->where('status', WebinarAttendance::STATUS_PENDING)
            ->get();

        foreach ($pendings as $attendance) {
            $cert = $this->generateWebinarCertificate($webinar, $attendance->user);

            $attendance->update([
                'status'         => WebinarAttendance::STATUS_APPROVED,
                'reviewed_by'    => auth()->id(),
                'reviewed_at'    => now(),
                'certificate_id' => $cert?->id,
            ]);

            if ($cert) {
                DocumentDownload::create([
                    'user_id'       => $attendance->user_id,
                    'doc_type'      => DocumentDownload::TYPE_SERTIFIKAT_WEBINAR,
                    'file_path'     => null,
                    'file_url'      => route('admin.certificate.pdf', $cert->id),
                    'downloaded_at' => now(),
                    'status'        => 'success',
                ]);
            }
        }

        return back()->with('success',
            "✅ {$pendings->count()} bukti kehadiran disetujui. Sertifikat sudah tersedia untuk masing-masing pemagang."
        );
    }

    // ===== PRIVATE HELPERS =====

    /**
     * Generate sertifikat webinar untuk satu peserta.
     */
    private function generateWebinarCertificate(Webinar $webinar, $user): ?Certificate
    {
        $startDate = $webinar->event_date;
        $endDate   = $webinar->event_end_date ?? $webinar->event_date;

        $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        $monthRoman = $roman[$endDate->month];
        $year       = $endDate->year;

        $brandCode    = strtoupper($webinar->certificate_brand ?? 'SI');
        // Company = nama brand (bukan field terpisah)
        $companyName  = Webinar::brandLabel($brandCode);
        $companyCode  = $this->companyCode($companyName);
        $divisionCode = 'WBN';

        // Running number per bulan-tahun (atomic)
        $last = Certificate::whereYear('created_at', $year)
            ->whereMonth('created_at', $endDate->month)
            ->orderByDesc('id')->first();
        $seq = 1;
        if ($last && preg_match('/^(\d{3})\/SERT\//', $last->serial_number, $m)) {
            $seq = (int)$m[1] + 1;
        }
        $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
        $serial = "{$seqStr}/SERT/{$divisionCode}/{$companyCode}.{$brandCode}/{$monthRoman}/{$year}";

        // Normalisasi path
        $bg   = $webinar->certificate_background ? "images/backgrounds/{$webinar->certificate_background}" : null;
        $l1   = $webinar->certificate_logo1      ? "images/logos/{$webinar->certificate_logo1}"            : null;
        $l2   = $webinar->certificate_logo2      ? "images/logos/{$webinar->certificate_logo2}"            : null;
        $sig1 = $webinar->certificate_signature1 ? "images/signature/{$webinar->certificate_signature1}"   : null;
        $sig2 = $webinar->certificate_signature2 ? "images/signature/{$webinar->certificate_signature2}"   : null;

        // Judul webinar di-encode ke field company agar template bisa membacanya
        $companyEncoded = $webinar->title . '||' . $companyName;

        return Certificate::create([
            'name'              => $user->name,
            'division'          => $divisionCode,
            'company'           => $companyEncoded,
            'description'       => $webinar->certificate_description ?: null,
            'background_image'  => $bg,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'city'              => $webinar->certificate_city ?? 'Yogyakarta',
            'brand'             => $brandCode,
            'serial_number'     => $serial,
            'logo1'             => $l1,
            'logo2'             => $l2,
            'signature_image1'  => $sig1,
            'signature_image2'  => $sig2,
            'name_signatory1'   => $webinar->certificate_signatory1_name ?? 'Penandatangan',
            'name_signatory2'   => $webinar->certificate_signatory2_name,
            'role1'             => $webinar->certificate_signatory1_role ?? 'Penyelenggara',
            'role2'             => $webinar->certificate_signatory2_role,
        ]);
    }

    /**
     * Handle inline file uploads for certificate assets.
     * If a new file is uploaded, store it to the shared asset folder and
     * override the corresponding certificate_* field in $validated.
     */
    private function processAssetUploads(Request $request, array $validated): array
    {
        $map = [
            'upload_background' => [
                'disk'   => 'public/images/backgrounds',
                'prefix' => 'bg_',
                'field'  => 'certificate_background',
            ],
            'upload_logo1' => [
                'disk'   => 'public/images/logos',
                'prefix' => 'logo_',
                'field'  => 'certificate_logo1',
            ],
            'upload_logo2' => [
                'disk'   => 'public/images/logos',
                'prefix' => 'logo_',
                'field'  => 'certificate_logo2',
            ],
            'upload_signature1' => [
                'disk'   => 'public/images/signature',
                'prefix' => 'ttd_',
                'field'  => 'certificate_signature1',
            ],
            'upload_signature2' => [
                'disk'   => 'public/images/signature',
                'prefix' => 'ttd_',
                'field'  => 'certificate_signature2',
            ],
        ];

        foreach ($map as $inputName => $cfg) {
            if ($request->hasFile($inputName) && $request->file($inputName)->isValid()) {
                $file      = $request->file($inputName);
                $ext       = $file->getClientOriginalExtension();
                $filename  = $cfg['prefix'] . \Illuminate\Support\Str::slug(
                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                ) . '_' . time() . '.' . $ext;

                $file->storeAs($cfg['disk'], $filename);
                $validated[$cfg['field']] = $filename;
            }

            // Buang field upload_ dari validated agar tidak masuk fillable
            unset($validated[$inputName]);
        }

        return $validated;
    }

    private function companyCode(string $company): string
    {
        $t = strtoupper(preg_replace('/\b(PT|CV|CO\.?|LTD\.?|INC\.?|TBK|PERSERO)\b\.?/i', '', $company));
        $first = preg_split('/\s+/', trim($t))[0] ?? $t;
        return preg_replace('/[^A-Z0-9]/', '', $first) ?: 'COMP';
    }

    private function loadAssets(): array
    {
        $bg  = collect(Storage::files('public/images/backgrounds'))
            ->map(fn($f) => basename($f))->filter(fn($f) => str_starts_with($f, 'bg_'))->values();
        $logo = collect(Storage::files('public/images/logos'))
            ->map(fn($f) => basename($f))->filter(fn($f) => str_starts_with($f, 'logo_'))->values();
        $sig  = collect(Storage::files('public/images/signature'))
            ->map(fn($f) => basename($f))->filter(fn($f) => str_starts_with($f, 'ttd_'))->values();
        return compact('bg', 'logo', 'sig');
    }

    private function brandList(): array
    {
        return Webinar::brandList();
    }
}
