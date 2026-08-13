<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternExtra;
use App\Models\InternshipRegistration as IR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InternExtraController extends Controller
{
    /**
     * Daftar pemagang completed yang bisa dipilih admin untuk akses alumni.
     * Tidak semua pemagang otomatis mendapatkan akses; admin menentukan yang berhak.
     */
    public function index()
    {
        $interns = IR::where('internship_status', IR::STATUS_COMPLETED)
            ->with('user')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.intern_extras.index', compact('interns'));
    }

    /**
     * Form edit extras untuk satu intern.
     */
    public function edit(IR $intern)
    {
        $extra = InternExtra::firstOrNew([
            'internship_registration_id' => $intern->id,
        ]);

        return view('admin.intern_extras.edit', compact('intern', 'extra'));
    }

    /**
     * Simpan/update extras untuk satu intern.
     */
    public function update(Request $request, IR $intern)
    {
        $request->validate([
            'alumni_group_url'       => 'nullable|url|max:500',
            'alumni_group_label'     => 'nullable|string|max:100',
            'job_info_url'           => 'nullable|url|max:500',
            'job_info_description'   => 'nullable|string|max:500',
        ]);

        $extra = InternExtra::firstOrNew([
            'internship_registration_id' => $intern->id,
        ]);

        // Alumni group
        if ($request->input('clear_alumni') === '1') {
            $extra->alumni_group_url        = null;
            $extra->alumni_group_label      = null;
            $extra->alumni_group_granted_at = null;
        } elseif ($request->has('alumni_group_url')) {
            if ($request->filled('alumni_group_url')) {
                $extra->alumni_group_url   = $request->alumni_group_url;
                $extra->alumni_group_label = $request->alumni_group_label ?: 'Grup Alumni Seveninc';
                if (!$extra->alumni_group_granted_at) {
                    $extra->alumni_group_granted_at = now();
                }
            } else {
                $extra->alumni_group_url        = null;
                $extra->alumni_group_label      = null;
                $extra->alumni_group_granted_at = null;
            }
        }

        // Job info
        if ($request->input('clear_job_info') === '1') {
            $extra->job_info_url         = null;
            $extra->job_info_description = null;
            $extra->job_info_granted_at  = null;
        } elseif ($request->has('job_info_url')) {
            if ($request->filled('job_info_url')) {
                $extra->job_info_url         = $request->job_info_url;
                $extra->job_info_description = $request->job_info_description;
                if (!$extra->job_info_granted_at) {
                    $extra->job_info_granted_at = now();
                }
            } else {
                $extra->job_info_url         = null;
                $extra->job_info_description = null;
                $extra->job_info_granted_at  = null;
            }
        }

        $extra->save();

        return redirect()->route('admin.intern_extras.edit', $intern->id)
            ->with('success', "Akses eksklusif untuk <strong>{$intern->fullname}</strong> berhasil diperbarui.");
    }

    /**
     * Hapus surat rekomendasi.
     */
    public function destroyRekomendasi(IR $intern)
    {
        $extra = InternExtra::where('internship_registration_id', $intern->id)->first();
        if ($extra?->rekomendasi_path) {
            Storage::disk('public')->delete($extra->rekomendasi_path);
            $extra->update([
                'rekomendasi_path'       => null,
                'rekomendasi_url'        => null,
                'rekomendasi_granted_at' => null,
            ]);
        }

        return back()->with('success', 'Surat rekomendasi berhasil dihapus.');
    }
}
