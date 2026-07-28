<?php

namespace App\Http\Controllers\Pemagang;

use App\Http\Controllers\Controller;
use App\Models\InternshipRegistration as IR;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
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

        // Kalau belum punya data registrasi → form kosong
        // Kalau sudah ada (apapun statusnya) → isi form dengan data yang ada, bisa diedit
        return view('pemagang.registration.form', compact('registration'));
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

        // Aturan validasi — draft boleh isi sebagian, submit wajib semua
        $rules = $isDraft
            ? $this->draftRules()
            : $this->submitRules();

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

        // Pastikan kolom NOT NULL yang tidak tampil di form selalu punya nilai
        $notNullDefaults = [
            'family_status'          => 'Tidak',
            'boarding_info'          => 'Tidak',
            'supervisor_contact'     => '-',
            'parent_wa_contact'      => '-',
            'social_media_instagram' => '-',
            'current_activities'     => '-',
            'design_software'        => $validated['design_software'] ?? '-',
            'video_software'         => $validated['video_software'] ?? '-',
            'programming_languages'  => $validated['programming_languages'] ?? '-',
            // Kolom NOT NULL yang bisa kosong saat draft
            'gender'                 => $validated['gender'] ?? 'Laki-laki',
            'internship_type'        => $validated['internship_type'] ?? 'Magang Mandiri',
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
        $isUpdate = IR::where('user_id', $user->id)->exists();

        if ($isUpdate) {
            return back()->with('success', 'Data pendaftaran berhasil diperbarui.');
        }

        return redirect()->route('pemagang.dashboard')
            ->with('success', 'Pendaftaran berhasil dikirim! Kami akan segera memproses data Anda.');
    }

    private function draftRules(): array
    {
        // Draft: semua field opsional
        return [
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
            'internship_interest'  => 'nullable|string|max:255',
            'start_date'         => 'nullable|string|max:255',
            'end_date'           => 'nullable|string|max:255',
            'cv_ktp_portofolio_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'portofolio_visual'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            // fields lainnya opsional
            'design_software'    => 'nullable|string|max:255',
            'video_software'     => 'nullable|string|max:255',
            'programming_languages' => 'nullable|string|max:255',
            'family_status'      => 'nullable|string|max:50',
            'boarding_info'      => 'nullable|string|max:50',
            'parent_wa_contact'  => 'nullable|regex:/^[0-9]{0,15}$/',
            'social_media_instagram' => 'nullable|string|max:255',
            'internship_info_sources' => 'nullable|array',
            'internship_info_sources.*' => 'nullable|string|max:100',
        ];
    }

    private function submitRules(): array
    {
        // Submit: field utama wajib diisi
        return array_merge($this->draftRules(), [
            'fullname'           => 'required|string|max:255',
            'born_date'          => 'required|string|max:255',
            'student_id'         => 'required|string|max:50',
            'email'              => 'required|string|max:255',
            'gender'             => 'required|string|max:50',
            'phone_number'       => 'required|regex:/^[0-9]{10,15}$/',
            'institution_name'   => 'required|string|max:255',
            'study_program'      => 'required|string|max:255',
            'faculty'            => 'required|string|max:255',
            'current_city'       => 'required|string|max:255',
            'internship_reason'  => 'required|string',
            'internship_type'    => 'required|string|max:50',
            'internship_arrangement' => 'required|string|max:50',
            'current_status'     => 'required|string|max:50',
            'english_book_ability'   => 'required|string|max:50',
            'internship_interest'    => 'required|string|max:255',
        ]);
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
