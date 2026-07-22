<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CertificateGeneratorController extends Controller
{
    // ---------- PUBLIC ----------

    // Tampilkan form
    public function showForm()
    {
        return view('certificates.generator_form');
    }

    // Preview sertifikat (tidak mengurangi counter)
    public function generatePreview(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string',
            'division'          => 'required|string',
            'company'           => 'required|string',
            'brand'             => 'required|string|min:2|max:6',
            'background_image'  => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'city'              => 'required|string',
            'logo1'             => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
            'logo2'             => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',      // opsional
            'role1'             => 'required|string',
            'signature_image1'  => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
            'role2'             => 'nullable|string',                                      // opsional
            'signature_image2'  => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',       // opsional
            'name_signatory1'   => 'required|string',
            'name_signatory2'   => 'nullable|string',                                      // opsional
        ]);

        // Simpan file ke storage/public
        $background       = $request->file('background_image')->store('backgrounds', 'public');
        $logo1            = $request->file('logo1')->store('logos', 'public');
        $logo2            = $request->file('logo2') ? $request->file('logo2')->store('logos', 'public') : null;
        $signatureImage1  = $request->file('signature_image1')->store('signatures', 'public');
        $signatureImage2  = $request->file('signature_image2') ? $request->file('signature_image2')->store('signatures', 'public') : null;

        // Durasi
        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $durationText = $this->formatDurationId($start, $end);

        // Serial number preview (run=0 → 000)
        $serial_number = $this->buildSerial(
            0, // Gantilah ini sesuai dengan run number atau ID yang sesuai
            $validated['division'],
            $validated['company'],
            $validated['brand'],
            Carbon::parse($validated['end_date']) // Menggunakan tanggal akhir
        );

        return view('certificates.generator_preview', [
            'name'               => $validated['name'],
            'division'           => $validated['division'],
            'company'            => $validated['company'],
            'brand'              => $validated['brand'],
            'background_image'   => $background,
            'start_date'         => $validated['start_date'],
            'end_date'           => $validated['end_date'],
            'city'               => $validated['city'],
            'logo1'              => $logo1,
            'logo2'              => $logo2,                 // boleh null
            'role1'              => $validated['role1'],
            'signature_image1'   => $signatureImage1,
            'role2'              => $validated['role2'] ?? null,           // boleh null
            'signature_image2'   => $signatureImage2,                       // boleh null
            'name_signatory1'    => $validated['name_signatory1'],
            'name_signatory2'    => $validated['name_signatory2'] ?? null, // boleh null
            'duration_text'      => $durationText,
            'serial_number'      => $serial_number,
        ]);
    }

    // Generate PDF (FINAL) — increment counter per-bulan
    public function generatePDF($id)
    {
        // Fetch the certificate record from the database
        // Menggunakan model Certificate (bukan InternshipRegistration)
        $certificate = Certificate::findOrFail($id);

        // Increment running number per-bulan
        $endDate = Carbon::parse($certificate->end_date);
        $run = $this->nextRunningNumber((int) $endDate->format('Y'), (int) $endDate->format('n'));

        // Build serial number
        $serialNumber = $this->buildSerial(
            $run,
            $certificate->division,
            $certificate->company,
            $certificate->brand,
            $endDate
        );

        // Update serial number di database
        $certificate->serial_number = $serialNumber;
        $certificate->save();

        // Prepare data for the PDF
        $data = [
            'certificate'      => $certificate,
            'name'             => $certificate->name,
            'division'         => $certificate->division,
            'company'          => $certificate->company,
            'brand'            => $certificate->brand,
            'background_image' => $certificate->background_image,
            'start_date'       => $certificate->start_date,
            'end_date'         => $certificate->end_date,
            'city'             => $certificate->city,
            'logo1'            => $certificate->logo1,
            'logo2'            => $certificate->logo2,
            'role1'            => $certificate->role1,
            'signature_image1' => $certificate->signature_image1,
            'role2'            => $certificate->role2,
            'signature_image2' => $certificate->signature_image2,
            'name_signatory1'  => $certificate->name_signatory1,
            'name_signatory2'  => $certificate->name_signatory2,
            'serial_number'    => $serialNumber,
        ];

        // Generate PDF menggunakan DomPDF (bukan Browsershot)
        $pdf = Pdf::loadView('certificates.generator_preview', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'Sertifikat_' . uniqid('cert_') . '.pdf';

        return $pdf->download($filename);
    }

    // ---------- PRIVATE HELPERS ----------

    // Counter atomic per-bulan
    protected function nextRunningNumber(int $year, int $month): int
    {
        return DB::transaction(function () use ($year, $month) {
            $row = DB::table('certificate_counters')
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('certificate_counters')->insert([
                    'year'        => $year,
                    'month'       => $month,
                    'last_number' => 0,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $row = DB::table('certificate_counters')
                    ->where('year', $year)
                    ->where('month', $month)
                    ->lockForUpdate()
                    ->first();
            }

            $next = (int) $row->last_number + 1;

            DB::table('certificate_counters')
                ->where('id', $row->id)
                ->update([
                    'last_number' => $next,
                    'updated_at'  => now(),
                ]);

            return $next;
        }, 5);
    }

    // Format: NNN/SERT/{DIV}/{COMP}.{BRAND}/{ROMAWI}/{TAHUN}
    protected function buildSerial(int $run, string $division, string $company, string $brand, Carbon $endDate): string
    {
        $nnn      = str_pad((string)$run, 3, '0', STR_PAD_LEFT);
        $divCode  = $this->mapDivisionCode($division);
        $compCode = $this->mapCompanyCode($company);
        $brandUp  = strtoupper(preg_replace('/[^A-Z0-9]/', '', $brand));
        $roman    = $this->monthToRoman((int)$endDate->format('n'));
        $year     = $endDate->format('Y');

        return "{$nnn}/SERT/{$divCode}/{$compCode}" . ($brandUp ? ".{$brandUp}" : "") . "/{$roman}/{$year}";
    }

    protected function monthToRoman(int $m): string
    {
        $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        return $map[$m] ?? '';
    }

    // Divisi → kode
    protected function mapDivisionCode(?string $division): string
    {
        $division = trim((string)$division);
        $map = ['Pemrogramman FrontEnd dan BackEnd' => 'PROG'];
        if (isset($map[$division])) return $map[$division];

        $abbr = collect(preg_split('/\s+/', $division))
            ->filter()
            ->map(fn ($w) => mb_substr($w, 0, 1))
            ->implode('');
        $abbr = strtoupper(preg_replace('/[^A-Z0-9]/', '', $abbr));
        return $abbr ?: 'DIV';
    }

    // Perusahaan → kata pertama bermakna
    protected function mapCompanyCode(?string $company): string
    {
        $company = strtoupper(trim((string)$company));
        $company = preg_replace('/\b(PT|CV|CO\.?|LTD\.?|INC\.?|TBK|PERSERO)\b\.?/i', '', $company);
        $company = trim($company);

        $first = preg_split('/\s+/', $company)[0] ?? $company;
        $first = strtoupper(preg_replace('/[^A-Z0-9]/', '', $first));
        return $first ?: 'COMP';
    }

    // Durasi (bulan + hari) berbahasa Indonesia
    private function formatDurationId(Carbon $startDate, Carbon $endDate): string
    {
        $interval = $startDate->diff($endDate);
        $months   = $interval->y * 12 + $interval->m;
        $days     = $interval->d;

        $parts = [];
        if ($months > 0) $parts[] = $months . ' bulan';
        if ($days > 0)   $parts[] = $days . ' hari';
        return $parts ? implode(' ', $parts) : '0 hari';
    }
}
