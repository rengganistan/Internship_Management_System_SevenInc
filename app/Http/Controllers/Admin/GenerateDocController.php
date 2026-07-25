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

        // Map jenis surat → URL generate yang sudah ada
        $url = match($jenis) {
            'loa'       => route('admin.loa.generate', ['intern_id' => $intern->id]),
            'skl'       => route('user.skl.download', ['user_id' => $intern->user_id]),
            'sertifikat'=> route('admin.certificate.create') . '?intern_id=' . $intern->id,
            'penilaian' => route('interns.assessment.create') . '?intern_id=' . $intern->id,
        };

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
