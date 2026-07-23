<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentDownload;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InternshipRegistration as IR;
use App\Models\LoaSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoaController extends Controller
{
    public function edit()
    {
        $loaSettings = LoaSettings::first();
        // untuk dropdown/checkbox multiple pemagang
        $registrations = IR::query()
            ->latest('id')
            ->select(
                'id','user_id','fullname',
                'student_id',            // ← pakai ini
                'study_program',         // ← pakai ini
                'institution_name',
                'start_date','end_date',
                'phone_number',          // ← pakai ini
                'internship_status'
            )
            ->get();


        return view('admin.loa_editor', compact('loaSettings','registrations'));
    }

    public function update(Request $request)
    {
        $loaSettings = LoaSettings::firstOrCreate([]);

        $data = $request->only([
            'company_name','company_contact_email','signatory_name','signatory_position',
            'header_text','footer_text'
        ]);

        // upload optional: logo_path & stamp_path
        if ($request->hasFile('logo_path')) {
            $data['logo_path'] = $request->file('logo_path')->store('images/logos','public');
        }
        if ($request->hasFile('stamp_path')) {
            $data['stamp_path'] = $request->file('stamp_path')->store('images/signature','public');
        }

        $loaSettings->update($data);

        return redirect()->route('admin.loa.editor')->with('success', 'LOA Settings updated successfully');
    }

    /**
     * Generate LOA untuk single intern (PDF)
     */
    public function generate(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'intern_id' => ['required', 'integer', 'exists:internship_registrations,id'],
        ]);

        $user = $request->user();

        // Admin bisa generate LOA untuk intern siapapun
        // Pemagang hanya bisa generate untuk dirinya sendiri
        if ($user->role === 'admin') {
            $intern = IR::where('id', $validated['intern_id'])->firstOrFail();
        } else {
            $intern = IR::where('id', $validated['intern_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
            // Pastikan pemagang sudah completed
            $this->ensureCanAccessCompletedDocs($user, $intern);
        }

        // Get the LOA settings (e.g., logo, signature)
        $loaSettings = LoaSettings::first();

        // Prepare rows with intern data
        $rows = $this->buildRows([$intern]);

        // Check if user submitted the table or not
        $loaNamaSiswa = $request->input('loa_nama_siswa', []);
        $loaNimNis = $request->input('loa_nim_nis', []);
        $loaJurusan = $request->input('loa_jurusan', []);
        $loaInstansi = $request->input('loa_instansi', []);
        $loaPeriode = $request->input('loa_periode', []);
        $loaKontak = $request->input('loa_kontak', []);

        // Auto-fill the fields if they are empty, using internship_registration data
        $loaNamaSiswa = $this->autoFillData($loaNamaSiswa, $intern->fullname);
        $loaNimNis = $this->autoFillData($loaNimNis, $intern->student_id);
        $loaJurusan = $this->autoFillData($loaJurusan, $intern->study_program);
        $loaInstansi = $this->autoFillData($loaInstansi, $intern->institution_name);
        $loaPeriode = $this->autoFillData($loaPeriode, Carbon::parse($intern->start_date)->format('d F Y') . ' - ' . Carbon::parse($intern->end_date)->format('d F Y'));
        $loaKontak = $this->autoFillData($loaKontak, $intern->phone_number);

        // Prepare rows with the updated values
        $rows = array_map(function ($index) use ($loaNamaSiswa, $loaNimNis, $loaJurusan, $loaInstansi, $loaPeriode, $loaKontak) {
            return [
                'nama_siswa' => $loaNamaSiswa[$index] ?? '',
                'nim_nis' => $loaNimNis[$index] ?? '',
                'jurusan' => $loaJurusan[$index] ?? '',
                'instansi' => $loaInstansi[$index] ?? '',
                'periode' => $loaPeriode[$index] ?? '',
                'kontak' => $loaKontak[$index] ?? '',
            ];
        }, array_keys($loaNamaSiswa));

        try {
            // Read the image files and encode them to base64
            $logoFile = storage_path('app/public/images/logos/logo_seveninc.png');
            $logoData = file_exists($logoFile)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
                : null;

            $stampFile = storage_path('app/public/images/signature/ttd_arisetiahusbana.png');
            // fallback ke file TTD lain yang ada jika ttd_arisetiahusbana.png tidak ditemukan
            if (!file_exists($stampFile)) {
                $candidates = glob(storage_path('app/public/images/signature/*.{png,jpg,jpeg}'), GLOB_BRACE);
                $stampFile = !empty($candidates) ? $candidates[0] : null;
            }
            $stampData = $stampFile && file_exists($stampFile)
                ? 'data:image/' . (str_ends_with($stampFile, '.png') ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($stampFile))
                : null;

            // Generate the PDF from the view, passing the required data
            $pdf = Pdf::loadView('user.loa', [
                'intern' => $intern,
                'user' => $user,
                'loaSettings' => $loaSettings,
                'rows' => $rows,
                'openingGreeting' => $request->input('openingGreeting'),
                'closingGreeting' => $request->input('closingGreeting'),
                'logoData' => $logoData,
                'stampData' => $stampData,
            ])->setPaper('A4', 'portrait');

            $pdf->setOptions([
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'defaultPaperSize' => 'A4',
            ]);

            // Create the file name for the generated PDF
            $safeName = Str::slug($intern->fullname ?? $user->name, '-');
            $fileName = 'LOA-' . $intern->id . '-' . $safeName . '-' . now()->format('Ymd_His') . '.pdf';

            // Define the directory to store the PDF
            $dir = 'documents/loa';
            $this->ensurePublicDir($dir); // Ensure directory exists
            $path = $dir . '/' . $fileName;

            // Store the generated PDF in the public disk
            Storage::disk('public')->put($path, $pdf->output());
            $publicUrl = asset('storage/' . $path);

            // Log the document download
            DocumentDownload::create([
                'user_id' => $user->id,
                'doc_type' => DocumentDownload::TYPE_LOA,
                'file_path' => $path,
                'file_url' => $publicUrl,
                'downloaded_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
            ]);

            // Return the PDF for download
            return response()->download(storage_path("app/public/{$path}"));
        } catch (\Throwable $e) {
            // Handle error and log the exception
            Log::error('Gagal generate LOA (single)', [
                'err' => $e->getMessage(),
                'intern_id' => $intern->id,
                'user_id' => $user->id,
            ]);
            return back()->with('error', 'Gagal membuat LOA. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Function to auto-fill data if input is empty
     */
    protected function autoFillData($data, $defaultValue)
    {
        // If the input is empty, return an array filled with the default value
        return array_map(function ($item) use ($defaultValue) {
            return $item ?: $defaultValue;
        }, $data);
    }





    /**
     * Generate LOA untuk multiple interns (sesuai revisi mentor) (PDF)
     */
    public function generateBatch(Request $request)
    {
        $validated = $request->validate([
            'intern_ids' => ['required','array','min:1'],
            'intern_ids.*' => ['integer','exists:internship_registrations,id'],
        ]);

        $user = $request->user();

        // Admin bisa generate LOA untuk intern siapapun
        // Pemagang hanya bisa untuk intern miliknya sendiri
        if ($user->role === 'admin') {
            $interns = IR::whereIn('id', $validated['intern_ids'])->get();
        } else {
            $interns = IR::whereIn('id', $validated['intern_ids'])
                ->where('user_id', $user->id)
                ->get();
        }

        if ($interns->isEmpty()) {
            return back()->with('error', 'Data pemagang tidak ditemukan atau tidak berhak diakses.');
        }

        // Pemagang: pastikan semuanya completed & milik user
        if ($user->role !== 'admin') {
            foreach ($interns as $intern) {
                $this->ensureCanAccessCompletedDocs($user, $intern);
            }
        }

        $loaSettings = LoaSettings::first();
        $rows = $this->buildRows($interns);

        try {
            $pdf = Pdf::loadView('user.loa', [
                'intern' => null,
                'user' => $user,
                'loaSettings' => $loaSettings,
                'rows' => $rows,
                'openingGreeting' => $request->input('openingGreeting'),
                'closingGreeting' => $request->input('closingGreeting'),
                'logoBase64' => $this->maybeToPublicUrlOrAsset($loaSettings?->logo_path, 'storage/images/logos/logo_seveninc.png'),
                'stampData' => $this->maybeToPublicUrlOrAsset($loaSettings?->stamp_path, 'storage/images/signature/ttd_arisetiahusbana.png'),
            ])->setPaper('A4', 'portrait');

            $pdf->setOptions(['isRemoteEnabled' => true]);

            $fileName = 'LOA-BATCH-' . now()->format('Ymd_His') . '.pdf';
            $dir = 'documents/loa';
            $this->ensurePublicDir($dir);
            $path = $dir . '/' . $fileName;

            Storage::disk('public')->put($path, $pdf->output());
            $publicUrl = asset('storage/' . $path);

            DocumentDownload::create([
                'user_id' => $user->id,
                'doc_type' => DocumentDownload::TYPE_LOA,
                'file_path' => $path,
                'file_url' => $publicUrl,
                'downloaded_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
            ]);

            return response()->download(storage_path("app/public/{$path}"));
        } catch (\Throwable $e) {
            Log::error('Gagal generate LOA (batch)', [
                'err' => $e->getMessage(),
                'intern_ids' => $validated['intern_ids'],
                'user_id' => $user->id,
            ]);
            return back()->with('error', 'Gagal membuat LOA batch. Silakan coba lagi.');
        }
    }

    // Preview cepat (tanpa fetch data)
    public function preview(Request $request)
    {
        $loaSettings = LoaSettings::first();
        return view('user.loa', [
            'intern' => null,
            'user' => $request->user(),
            'loaSettings' => $loaSettings,
            'rows' => [],
            'openingGreeting' => 'Contoh pra-tayang LOA.',
            'closingGreeting' => 'Contoh penutup pra-tayang.',
            'logoBase64' => $this->maybeToPublicUrlOrAsset($loaSettings?->logo_path, 'storage/images/logos/logo_seveninc.png'),
            'stampData' => $this->maybeToPublicUrlOrAsset($loaSettings?->stamp_path, 'storage/images/signature/ttd_arisetiahusbana.png'),
        ]);
    }

    // Daftar pemagang (CRUD ringkas: listing)
    public function indexInterns()
    {
        $registrations = IR::latest('id')->paginate(20);
        return view('admin.loa_interns', compact('registrations'));
    }

    protected function buildRows($interns): array
    {
        $rows = [];
        foreach ($interns as $intern) {
            $rows[] = [
                'nama_siswa' => $intern->fullname ?? 'Nama Tidak Diketahui',
                'nim_nis'    => ($intern->student_id ?? $intern->nim_nis ?? $intern->nim ?? null) ?: 'NIM/NIS Tidak Diketahui',
                'jurusan'    => ($intern->study_program ?? $intern->major ?? null) ?: 'Jurusan Tidak Diketahui',
                'instansi'   => $intern->institution_name ?? 'Instansi Tidak Diketahui',
                'periode'    => ($intern->start_date && $intern->end_date)
                    ? \Carbon\Carbon::parse($intern->start_date)->format('d F Y') . ' - ' . \Carbon\Carbon::parse($intern->end_date)->format('d F Y')
                    : 'Periode Tidak Diketahui',
                'kontak'     => ($intern->phone_number ?? $intern->contact_info ?? $intern->email ?? null) ?: 'Kontak Tidak Diketahui',
            ];
        }
        return $rows;
    }

    protected function ensureCanAccessCompletedDocs($user, $intern): void
    {
        $status = strtolower((string)($intern->internship_status ?? ''));
        if (!($user->role === 'pemagang' && $intern->user_id === $user->id && $status === 'completed')) {
            abort(403, 'Anda tidak berhak membuat/akses LOA untuk data ini.');
        }
    }

    protected function ensurePublicDir(string $dir): void
    {
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
    }

    // Resolve url/asset untuk logo & ttd
    protected function maybeToPublicUrlOrAsset(?string $path, string $fallbackAsset): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }
        return asset($fallbackAsset);
    }
}
