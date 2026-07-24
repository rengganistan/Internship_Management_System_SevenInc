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

        // Kalau sudah submit resmi (bukan draft), tidak bisa edit lagi
        if ($registration && !$registration->is_draft && $registration->internship_status !== IR::STATUS_WAITING) {
            return redirect()->route('pemagang.dashboard')
                ->with('info', 'Pendaftaran Anda sudah dikirim dan sedang diproses.');
        }

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

        $validated = $request->validate($rules);

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
            'phone_number'       => 'nullable|string|max:30',
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
            'parent_wa_contact'  => 'nullable|string|max:255',
            'social_media_instagram' => 'nullable|string|max:255',
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
            'phone_number'       => 'required|string|max:30',
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
        $ext      = $file->getClientOriginalExtension();
        $safe     = Str::slug($original, '_');
        $i = 0;

        do {
            $name = $i === 0 ? "{$safe}.{$ext}" : "{$safe}({$i}).{$ext}";
            $path = "{$dir}/{$name}";
            $i++;
        } while (Storage::disk('public')->exists($path));

        return $file->storeAs($dir, $name, 'public');
    }
}
