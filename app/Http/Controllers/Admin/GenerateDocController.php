<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternshipRegistration as IR;
use App\Models\DocumentDownload;
use Illuminate\Http\Request;

class GenerateDocController extends Controller
{
    /**
     * POST /admin/interns/generate-doc
     *
     * Menerima: intern_id, jenis_surat (loa | skl | sertifikat | penilaian)
     * Mengembalikan redirect ke endpoint generate yang sudah ada,
     * atau JSON {redirect_url} jika request AJAX.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'intern_id'   => 'required|integer|exists:internship_registrations,id',
            'jenis_surat' => 'required|in:loa,skl,sertifikat,penilaian',
        ]);

        $intern = IR::findOrFail($validated['intern_id']);
        $jenis  = $validated['jenis_surat'];

        // Validasi status sesuai jenis surat
        $statusRules = [
            'loa'        => [IR::STATUS_ACCEPTED, IR::STATUS_ACTIVE, IR::STATUS_COMPLETED],
            'skl'        => [IR::STATUS_COMPLETED],
            'sertifikat' => [IR::STATUS_COMPLETED],
            'penilaian'  => [IR::STATUS_COMPLETED],
        ];

        $allowedStatuses = $statusRules[$jenis] ?? [];
        if (!empty($allowedStatuses) && !in_array($intern->internship_status, $allowedStatuses)) {
            $statusLabel = match($jenis) {
                'loa'        => 'diterima (accepted)',
                'skl'        => 'selesai (completed)',
                'sertifikat' => 'selesai (completed)',
                'penilaian'  => 'selesai (completed)',
                default      => 'memenuhi syarat',
            };
            $errMsg = "Dokumen '{$jenis}' hanya bisa digenerate jika status pemagang sudah {$statusLabel}. Status saat ini: {$intern->internship_status}.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => $errMsg], 422);
            }
            return back()->with('error', $errMsg);
        }

        // Map jenis surat → URL generate yang sudah ada
        $url = match($jenis) {
            'loa'       => route('admin.loa.generate', ['intern_id' => $intern->id]),
            'skl'       => $intern->user_id
                            ? route('admin.skl.download.for_user', ['user' => $intern->user_id])
                            : null,
            'sertifikat'=> route('admin.certificate.create') . '?intern_id=' . $intern->id,
            'penilaian' => route('interns.assessment.create') . '?intern_id=' . $intern->id,
        };

        if (!$url) {
            return back()->with('error', 'User pemagang tidak ditemukan untuk intern ini.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'           => true,
                'redirect_url' => $url,
                'jenis'        => $jenis,
                'intern_name'  => $intern->fullname,
            ]);
        }

        return redirect($url);
    }
}
