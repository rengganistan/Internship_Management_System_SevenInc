<?php

namespace App\Http\Controllers\Pemagang;

use App\Http\Controllers\Controller;
use App\Models\FormSetting;
use App\Models\InternshipRegistration as IR;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tampilkan form pendaftaran.
     * Jika sudah ada data (termasuk draft), isi form dengan data tersebut.
     */
    public function showForm()
    {
        $user         = auth()->user();
        $registration = IR::where('user_id', $user->id)->latest('id')->first();
        $settings     = FormSetting::getInternshipFields();

        // Ambil divisi aktif dari DB; fallback ke list hardcode jika DB kosong
        $divisions = \App\Models\Division::active()->pluck('name');

        if ($divisions->isEmpty()) {
            $divisions = collect([
                'Administration',
                'Human Resources (HR)',
                'UI/UX Designer',
                'Programmer (Front End / Backend)',
                'Photographer',
                'Videographer',
                'Graphic Designer (Konten Kreatif)',
                'Social Media Specialist',
                'Content Writer',
                'Content Planner',
                'Sales & Marketing',
                'Public Relations (Marcomm)',
                'Digital Marketing',
                'TikTok Creator',
                'Project Manager',
                'Pengelasan',
                'Animasi',
                'Customer Service',
            ]);
        }

        return view('pemagang.registration.form', compact('registration', 'divisions', 'settings'));
    }

    /**
     * Kirim pendaftaran resmi (is_draft = false, status = waiting).
     */
    public function store(Request $request)
    {
        return $this->saveRegistration($request, isDraft: false);
    }

    /**
     * Simpan sebagai draft (is_draft = true, status tidak berubah).
     */
    public function saveDraft(Request $request)
    {
        return $this->saveRegistration($request, isDraft: true);
    }

    // ──────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────

    private function saveRegistration(Request $request, bool $isDraft): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        $settings = FormSetting::getInternshipFields();

        // Aturan validasi — draft boleh isi sebagian, submit wajib semua
        $rules = $isDraft
            ? $this->draftRules($settings)
            : $this->submitRules($settings);

        $validated = $request->validate($rules, [
            'phone_number.regex'   => 'No. HP hanya boleh berisi angka (10-15 digit).',
            'phone_number.required' => 'No. HP wajib diisi.',
            'fullname.required'    => 'Nama lengkap wajib diisi.',
            'student_id.required'  => 'NIM/NPM wajib diisi.',
            'email.required'       => 'Email wajib diisi.',
            'gender.required'      => 'Jenis kelamin wajib dipilih.',
            'institution_name.required' => 'Nama universitas wajib diisi.',
            'study_program.required'    => 'Program studi wajib diisi.',
            'faculty.required'          => 'Fakultas wajib diisi.',
            'current_city.required'     => 'Kota domisili wajib diisi.',
            'internship_reason.required' => 'Alasan magang wajib diisi.',
            'internship_interest.required' => 'Divisi yang diminati wajib dipilih.',
        ]);

        // Normalisasi tanggal
        foreach (['born_date', 'start_date', 'end_date'] as $field) {
            if (!empty($validated[$field])) {
                try {
                    $validated[$field] = Carbon::parse($validated[$field])->format('Y-m-d');
                } catch (\Throwable) { /* biarkan string asli */ }
            }
        }

        // Checkbox arrays → CSV
        $validated['owned_tools'] = $this->arrayToCsv($request->input('owned_tools', []));
        $validated['internship_info_sources'] = $this->arrayToCsv($request->input('internship_info_sources', []));

        // Upload file
        foreach (['cv_ktp_portofolio_pdf', 'portofolio_visual'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $validated[$fileField] = $this->storeFile($request->file($fileField), 'uploads');
            }
        }

        // Pastikan kolom NOT NULL yang tidak tampil di form selalu punya nilai.
        // Field yang dihapus/legacy seperti family_status tetap dipertahankan di DB,
        // tapi tidak lagi dipakai pada form aktif.
        $notNullDefaults = [
            'boarding_info'          => $validated['boarding_info'] ?? 'Tidak',
            'supervisor_contact'     => '-',
            'parent_wa_contact'      => $validated['parent_wa_contact'] ?? '-',
            'social_media_instagram' => $validated['social_media_instagram'] ?? '-',
            'current_activities'     => '-',
            'design_software'        => $validated['design_software'] ?? '-',
            'video_software'         => $validated['video_software'] ?? '-',
            'programming_languages'  => $validated['programming_languages'] ?? '-',
            'gender'                 => $validated['gender'] ?? 'Laki-laki',
            'family_status'          => $validated['family_status'] ?? 'Tidak',
            'internship_type'        => $validated['internship_type'] ?? 'Magang Reguler (Mandiri)',
            'internship_arrangement' => $validated['internship_arrangement'] ?? 'Onsite',
            'current_status'         => $validated['current_status'] ?? 'Mahasiswa/Pelajar',
            'english_book_ability'   => $validated['english_book_ability'] ?? 'Saya bisa',
            'internship_reason'      => $validated['internship_reason'] ?? '-',
            'fullname'               => $validated['fullname'] ?? '-',
            'born_date'              => $validated['born_date'] ?? '-',
            'student_id'             => $validated['student_id'] ?? '-',
            'email'                  => $validated['email'] ?? (auth()->user()->email ?? '-'),
            'phone_number'           => $validated['phone_number'] ?? '-',
            'institution_name'       => $validated['institution_name'] ?? '-',
            'study_program'          => $validated['study_program'] ?? '-',
            'faculty'                => $validated['faculty'] ?? '-',
            'current_city'           => $validated['current_city'] ?? '-',
            'internship_interest'    => $validated['internship_interest'] ?? '-',
        ];

        foreach ($notNullDefaults as $field => $default) {
            if (empty($validated[$field])) {
                $validated[$field] = $default;
            }
        }

        // Draft atau submit?
        $validated['is_draft']      = $isDraft;
        $validated['draft_saved_at'] = $isDraft ? now() : null;

        if (!$isDraft) {
            $validated['internship_status'] = IR::STATUS_WAITING;
            $validated['user_id']           = $user->id;
        }

        // Upsert — update kalau sudah ada, buat baru kalau belum
        $existing = IR::where('user_id', $user->id)->latest('id')->first();

        if ($existing) {
            // Jangan timpa status kalau submit (status dikelola admin)
            if (!$isDraft) {
                unset($validated['internship_status']);
            }
            $existing->fill($validated)->save();
        } else {
            $validated['user_id'] = $user->id;
            if (!$isDraft) {
                // Ubah role user saat submit resmi
                $user->role = 'pemagang';
                $user->save();
            }
            IR::create($validated);
        }

        if ($isDraft) {
            return back()->with('success', 'Draft berhasil disimpan.');
        }

        // Kalau data sudah ada sebelumnya (update) vs baru submit
        $isNewSubmission = !$existing;

        if (!$isNewSubmission) {
            return back()->with('success', 'Data pendaftaran berhasil diperbarui.');
        }

        // Logout setelah submit perdana — user harus login ulang untuk cek status
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('registration_success', true)
            ->with('success', '🎉 Pendaftaran berhasil dikirim! Silakan login untuk memantau status magangmu.');
    }

    private function draftRules(array $settings = []): array
    {
        $active = fn(string $key, bool $default = true) => (bool) ($settings[$key]['is_active'] ?? $default);

        $rules = [
            'fullname'           => 'nullable|string|max:255',
            'born_date'          => 'nullable|string|max:255',
            'student_id'         => 'nullable|string|max:50',
            'email'              => 'nullable|string|max:255',
            'gender'             => 'nullable|string|max:50',
            'phone_number'       => 'nullable|regex:/^[0-9]{0,15}$/',
            'institution_name'   => 'nullable|string|max:255',
            'study_program'      => 'nullable|string|max:255',
            'faculty'            => 'nullable|string|max:255',
            'current_city'       => 'nullable|string|max:255',
            'internship_reason'  => 'nullable|string',
            'internship_type'    => 'nullable|string|max:50',
            'internship_arrangement' => 'nullable|string|max:50',
            'current_status'     => 'nullable|string|max:50',
            'english_book_ability' => 'nullable|string|max:50',
            'internship_interest' => 'nullable|string|max:255',
            'start_date'         => 'nullable|string|max:255',
            'end_date'           => 'nullable|string|max:255',
            'cv_ktp_portofolio_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'portofolio_visual'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'design_software'    => 'nullable|string|max:255',
            'video_software'     => 'nullable|string|max:255',
            'programming_languages' => 'nullable|string|max:255',
            'boarding_info'      => 'nullable|string|max:50',
            'parent_wa_contact'  => 'nullable|regex:/^[0-9]{0,15}$/',
            'social_media_instagram' => 'nullable|string|max:255',
            'internship_info_sources' => 'nullable|array',
            'internship_info_sources.*' => 'nullable|string|max:100',
        ];

        foreach (['fullname','student_id','born_date','gender','email','phone_number','institution_name','study_program','faculty','current_city','internship_interest','internship_type','internship_arrangement','internship_reason','start_date','end_date','current_status','english_book_ability','design_software','programming_languages','video_software','cv_ktp_portofolio_pdf','portofolio_visual','boarding_info','parent_wa_contact','social_media_instagram','internship_info_sources'] as $field) {
            if (!$active($field, true) && isset($rules[$field])) {
                $rules[$field] = 'nullable';
            }
        }

        return $rules;
    }

    private function submitRules(array $settings = []): array
    {
        $rules = $this->draftRules($settings);

        foreach (['fullname','student_id','born_date','gender','email','phone_number','institution_name','study_program','faculty','current_city','internship_interest','internship_type','internship_reason'] as $field) {
            if (($settings[$field]['is_active'] ?? true) === false) {
                continue;
            }
            if (isset($rules[$field])) {
                $rules[$field] = preg_replace('/^nullable\|?/', '', $rules[$field]);
                $rules[$field] = trim((string) $rules[$field], '|');
                $rules[$field] = 'required|' . $rules[$field];
            }
        }

        if (($settings['cv_ktp_portofolio_pdf']['is_active'] ?? true) && (($settings['cv_ktp_portofolio_pdf']['is_required'] ?? false) === true)) {
            $rules['cv_ktp_portofolio_pdf'] = 'required|file|mimes:pdf|max:10240';
        } elseif (isset($rules['cv_ktp_portofolio_pdf'])) {
            $rules['cv_ktp_portofolio_pdf'] = 'nullable|file|mimes:pdf|max:10240';
        }

        if (($settings['portofolio_visual']['is_active'] ?? true) && (($settings['portofolio_visual']['is_required'] ?? false) === true)) {
            $rules['portofolio_visual'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:10240';
        } elseif (isset($rules['portofolio_visual'])) {
            $rules['portofolio_visual'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240';
        }

        return $rules;
    }

    private function arrayToCsv(mixed $input): ?string
    {
        if (!is_array($input)) return is_string($input) && $input !== '' ? $input : null;
        $vals = array_filter(array_map('trim', $input), fn($v) => $v !== '');
        return empty($vals) ? null : implode(', ', array_values($vals));
    }

    private function storeFile(\Illuminate\Http\UploadedFile $file, string $dir): string
    {
        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext      = strtolower($file->getClientOriginalExtension());
        // Gunakan slug dengan underscore, hindari karakter spesial termasuk tanda kurung
        $safe     = Str::slug($original, '_');
        $i = 0;

        do {
            $name = $i === 0 ? "{$safe}.{$ext}" : "{$safe}_{$i}.{$ext}";
            $path = "{$dir}/{$name}";
            $i++;
        } while (Storage::disk('public')->exists($path));

        return $file->storeAs($dir, $name, 'public');
    }
}
