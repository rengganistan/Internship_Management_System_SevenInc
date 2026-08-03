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
     * Daftar semua pemagang completed beserta status extras-nya.
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
            'rekomendasi_file'       => 'nullable|file|mimes:pdf|max:5120',
            'alumni_group_url'       => 'nullable|url|max:500',
            'alumni_group_label'     => 'nullable|string|max:100',
            'job_info_url'           => 'nullable|url|max:500',
            'job_info_description'   => 'nullable|string|max:500',
        ]);

        $extra = InternExtra::firstOrNew([
            'internship_registration_id' => $intern->id,
        ]);

        // Upload surat rekomendasi
        if ($request->hasFile('rekomendasi_file')) {
            // Hapus file lama
            if ($extra->rekomendasi_path) {
                Storage::disk('public')->delete($extra->rekomendasi_path);
            }
            $extra->rekomendasi_path = $request->file('rekomendasi_file')
                ->storeAs('documents/rekomendasi', 'rekomendasi-' . $intern->id . '-' . now()->format('Ymd') . '.pdf', 'public');
            $extra->rekomendasi_url  = asset('storage/' . $extra->rekomendasi_path);
            $extra->rekomendasi_granted_at = now();
        }

        // Alumni group
        if ($request->filled('alumni_group_url')) {
            $extra->alumni_group_url   = $request->alumni_group_url;
            $extra->alumni_group_label = $request->alumni_group_label ?: 'Grup Alumni Seveninc';
            if (!$extra->alumni_group_granted_at) {
                $extra->alumni_group_granted_at = now();
            }
        } elseif ($request->input('clear_alumni') === '1') {
            $extra->alumni_group_url        = null;
            $extra->alumni_group_label      = null;
            $extra->alumni_group_granted_at = null;
        }

        // Job info
        if ($request->filled('job_info_url')) {
            $extra->job_info_url         = $request->job_info_url;
            $extra->job_info_description = $request->job_info_description;
            if (!$extra->job_info_granted_at) {
                $extra->job_info_granted_at = now();
            }
        } elseif ($request->input('clear_job_info') === '1') {
            $extra->job_info_url         = null;
            $extra->job_info_description = null;
            $extra->job_info_granted_at  = null;
        }

        $extra->save();

        return redirect()->route('admin.intern_extras.index')
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
