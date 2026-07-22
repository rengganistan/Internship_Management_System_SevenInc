<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\InternshipRegistration as IR;
use App\Services\CertificatePdf;
use App\Mail\InternAcceptedMail;
use App\Mail\InternRejectedMail;
use App\Mail\InternWaitingMail;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use Spatie\Browsershot\Browsershot;
use Carbon\Carbon;

class InternController extends Controller
{

    public function showSKL($internId)
    {
        $intern = \App\Models\InternshipRegistration::findOrFail($internId);
        $user = $intern->user ?? null;

        // Jika view skl.blade.php tidak ditemukan
        if (!view()->exists('user.skl')) {
            abort(404, 'File SKL tidak ditemukan di resources/views/user/skl.blade.php');
        }

        // Render surat SKL dengan data peserta
        return view('user.skl', compact('intern', 'user'));
    }


    private function sendAcceptedEmail(IR $intern): void
    {
        // Ambil email tujuan: prioritas ke kolom email pendaftar
        $to = $intern->email ?: optional($intern->user)->email;
        if (!$to) return;

        try {
            // Kirim email dengan queue
            Mail::to($to)->queue(new InternAcceptedMail($intern)); 
        } catch (\Exception $e) {
            // Log error jika ada masalah dengan pengiriman email
            Log::error("Email gagal terkirim ke {$to}: {$e->getMessage()}");
        }
    }


    public function certificatePdfDynamic(IR $intern, string $template)
    {
        if ($intern->internship_status !== IR::STATUS_COMPLETED) {
            abort(403, 'Sertifikat hanya tersedia untuk pemagang yang sudah selesai.');
        }

        $view = 'certificates.' . $template;
        if (!view()->exists($view)) {
            abort(404, "Template {$template} tidak ditemukan");
        }

        // Kirim hanya yang esensial
        $data = ['intern' => $intern, 'template' => $template];

        $filename = 'Sertifikat_' . \Illuminate\Support\Str::slug($intern->fullname ?: 'Pemagang', '_')
                . "_{$template}.pdf";

        return $this->downloadPdfFromView($view, $data, $filename);
    }

    private function downloadPdfFromView(string $view, array $data, string $downloadName)
    {
        $html = view($view, $data)->render();

        // ---- Embed <img src> ----
        $toPublicFile = function (string $src) {
            if (preg_match('~^https?://~i', $src)) {
                $path = parse_url($src, PHP_URL_PATH) ?: '';
            } else {
                $path = $src;
            }
            $path = ltrim($path, '/');

            // dukung public/storage/... dan public/images/...
            $candidates = [];
            if (stripos($path, 'storage/') === 0 || stripos($path, 'images/') === 0) {
                $candidates[] = public_path($path);
            }
            foreach ($candidates as $full) {
                if (is_file($full)) return $full;
            }
            return null;
        };

        $imgToDataUri = function (string $file) {
            $mime = FileFacade::mimeType($file) ?: 'image/png';
            $data = base64_encode(FileFacade::get($file));
            return "data:{$mime};base64,{$data}";
        };

        // DOM parse
        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        // <img src="...">
        $imgs = $dom->getElementsByTagName('img');
        $imgNodes = [];
        foreach ($imgs as $i) { $imgNodes[] = $i; }
        foreach ($imgNodes as $img) {
            if (!($img instanceof \DOMElement)) continue;
            $src = $img->getAttribute('src');
            if (!$src) continue;
            if ($file = $toPublicFile($src)) {
                $img->setAttribute('src', $imgToDataUri($file));
            }
        }

        // inline style url(...)
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//*[@style]') as $el) {
            if (!($el instanceof \DOMElement)) continue;
            $style = $el->getAttribute('style');
            $style = preg_replace_callback(
                '~url\((["\']?)([^)\'"]+)\1\)~i',
                function ($m) use ($toPublicFile, $imgToDataUri) {
                    $file = $toPublicFile($m[2]);
                    return $file ? 'url(' . $imgToDataUri($file) . ')' : $m[0];
                },
                $style
            );
            $el->setAttribute('style', $style);
        }

        // <style> blocks url(...)
        $styleNodes = $dom->getElementsByTagName('style');
        for ($i = 0; $i < $styleNodes->length; $i++) {
            /** @var \DOMElement $styleEl */
            $styleEl = $styleNodes->item($i);
            $css = $styleEl->nodeValue ?? '';
            $css = preg_replace_callback(
                '~url\((["\']?)([^)\'"]+)\1\)~i',
                function ($m) use ($toPublicFile, $imgToDataUri) {
                    $file = $toPublicFile($m[2]);
                    return $file ? 'url(' . $imgToDataUri($file) . ')' : $m[0];
                },
                $css
            );
            while ($styleEl->firstChild) { $styleEl->removeChild($styleEl->firstChild); }
            $styleEl->appendChild($dom->createTextNode($css));
        }

        $html = $dom->saveHTML();

        // Hook ready
        $html .= <<<'HTML'
    <script>
    (function(){
    function imagesReady(){
        var imgs=[].slice.call(document.images||[]);
        if(!imgs.length) return Promise.resolve();
        return Promise.all(imgs.map(function(i){
        if(i.complete) return Promise.resolve();
        return new Promise(function(r){
            i.addEventListener('load', r, {once:true});
            i.addEventListener('error', r, {once:true});
        });
        }));
    }
    var timer=setTimeout(function(){window.__CERT_READY=true;}, 800);
    imagesReady().then(function(){ clearTimeout(timer); window.__CERT_READY=true; });
    })();
    </script>
    HTML;

        // Save PDF
        $safe = trim(preg_replace('/[^A-Za-z0-9_\- ]+/', '', pathinfo($downloadName, PATHINFO_FILENAME))) ?: 'Sertifikat';
        $filename = $safe . '.pdf';
        $dir  = storage_path('app/public/certificates');
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        $bs = Browsershot::html($html)
            ->showBackground()
            ->margins(0, 0, 0, 0)
            ->setOption('printBackground', true)
            ->setOption('preferCSSPageSize', true)
            ->emulateMedia('print')
            ->windowSize(1123, 794)
            ->deviceScaleFactor(2)
            ->waitForFunction('window.__CERT_READY === true')
            ->setOption('waitUntil', 'networkidle0')
            ->timeout(180);

        if ($chromePath = env('BROWSERSHOT_CHROME_PATH')) {
            $bs->setChromePath($chromePath);
        }
        // $bs->addChromiumArguments(['--no-sandbox','--disable-setuid-sandbox']);

        $bs->savePdf($path);

        return response()->download($path, $filename, ['Content-Type' => 'application/pdf'])
                        ->deleteFileAfterSend(true);
    }



    /**
     * Baca file dari storage:public lalu ubah jadi data URI (base64).
     * Return null bila file tidak ada.
     */
    private function dataUriPublic(string $relPath): ?string
    {
        if (!Storage::disk('public')->exists($relPath)) {
            return null;
        }

        $bytes = Storage::disk('public')->get($relPath);
        $ext   = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));

        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',
            default       => 'application/octet-stream',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($bytes);
    }

    /**
     * Simpan file memakai nama asli; bila bentrok, beri (n).
     */
    private function storeWithOriginalName($file, string $directory = 'uploads'): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension    = $file->getClientOriginalExtension();

        $base = preg_replace('/[^A-Za-z0-9_\- ]+/', '', $originalName);
        $base = preg_replace('/\s+/', ' ', trim($base));
        $base = str_replace(' ', '-', $base);

        $filename = "{$base}.{$extension}";
        $path     = "{$directory}/{$filename}";
        $i = 1;

        while (Storage::disk('public')->exists($path)) {
            $filename = "{$base}({$i}).{$extension}";
            $path     = "{$directory}/{$filename}";
            $i++;
        }

        $file->storeAs($directory, $filename, 'public');
        return $path;
    }

    /**
     * Helper list tabel + pencarian ringan.
     */
    private function table(Request $request, $query, string $title, string $scope)
    {
        // Jika ada pencarian, hanya ambil data tanpa pagination
        if ($request->filled('q')) {
            $s = trim($request->get('q'));
            $query->where(function ($qq) use ($s) {
                $qq->where('fullname', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('student_id', 'like', "%{$s}%");
            });

            // Ambil semua data yang sesuai dengan pencarian
            $interns = $query->get();
        } else {
            // Jika tidak ada pencarian, gunakan pagination
            $interns = $query->paginate(25)->withQueryString();
        }

        return view('interns.index', [
            'interns' => $interns,
            'title'   => $title,
            'scope'   => $scope,
        ]);
    }

    public function index(Request $request)
    {
        return $this->table(
            $request,
            IR::query()->orderByDesc('created_at'),
            'Semua Pemagang',
            'all'
        );
        
    }

    public function active(Request $request)
    {
        return $this->table(
            $request,
            IR::where('internship_status', IR::STATUS_ACTIVE)->orderByDesc('updated_at'),
            'Pemagang Aktif',
            'active'
        );
    }

    public function completed(Request $request)
    {
        return $this->table(
            $request,
            IR::where('internship_status', IR::STATUS_COMPLETED)->orderByDesc('updated_at'),
            'Pemagang Selesai',
            'completed'
        );
    }

    public function exited(Request $request)
    {
        return $this->table(
            $request,
            IR::where('internship_status', IR::STATUS_EXITED)->orderByDesc('updated_at'),
            'Pemagang Keluar',
            'exited'
        );
    }

    public function pending(Request $request)
    {
        return $this->table(
            $request,
            IR::where('internship_status', IR::STATUS_PENDING)->orderByDesc('created_at'),
            'Pemagang Pending',
            'pending'
        );
    }

    /**
     * PATCH /admin/interns/{intern}/status
     */
    public function update(Request $request, $id)
    {
        // Menemukan data berdasarkan ID yang diberikan
        $intern = IR::findOrFail($id);

        // Validasi data yang diterima
        $validatedData = $request->validate([
            'fullname' => 'required|string|max:255',
            'born_date' => 'nullable|string|regex:/\d{4}-\d{2}-\d{2}/', // Validasi format yyyy-mm-dd
            'student_id' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'institution_name' => 'nullable|string|max:255',
            'study_program' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'current_city' => 'nullable|string|max:255',
            'internship_reason' => 'nullable|string|max:255',
            'internship_type' => 'nullable|string|max:50',
            'start_date' => 'nullable|string|regex:/\d{4}-\d{2}-\d{2}/', // Validasi format yyyy-mm-dd
            'end_date' => 'nullable|string|regex:/\d{4}-\d{2}-\d{2}/', // Validasi format yyyy-mm-dd
        ]);

        // Memperbarui data yang sudah divalidasi
        $intern->update($validatedData);

        // Mengembalikan response berupa data yang telah diperbarui
        return response()->json($intern, 200);
    }




    public function destroy($id)
    {
        $intern = IR::findOrFail($id);
        $intern->delete();
        return response()->json(['message' => 'Data berhasil dihapus'], 200);
    }

    // ===== helper: tetapkan / cabut role "pemagang" sesuai status & kondisi =====
    private function syncPemagangRole(IR $intern): void
    {
        $user = $intern->user;
        if (!$user) return;

        // syarat role "pemagang": user sudah submit (punya record IR) & status "accepted"
        if ($intern->internship_status === IR::STATUS_ACCEPTED) {
            // beri role pemagang (jaga idempotensi)
            if (mb_strtolower($user->role ?? '') !== 'pemagang') {
                $user->role = 'pemagang';
                $user->save();
            }
        } else {
            // kebijakan saat status berubah dari accepted ke selainnya:
            // Kalau mau cabut role, tentukan fallback (mis: 'user').
            // Kalau TIDAK mau dicabut (tetap pemagang), hapus blok ini.
            if (mb_strtolower($user->role ?? '') === 'pemagang') {
                $user->role = 'user'; // sesuaikan fallback default project kamu
                $user->save();
            }
        }
    }

    public function updateStatus(Request $request, $id)
    {
        // Menemukan data berdasarkan ID yang diberikan
        $intern = IR::findOrFail($id);

        // Validasi status yang diterima
        $validated = $request->validate([
            'internship_status' => 'required|in:waiting,active,completed,exited,pending,accepted,rejected',
        ]);

        $oldStatus = $intern->internship_status; // Menyimpan status lama
        $newStatus = $validated['internship_status']; // Status baru yang diterima

        // Mengupdate status internship
        $intern->internship_status = $newStatus;

        // Pastikan tidak ada batasan untuk admin mengubah status
        // Admin bebas mengubah status apapun tanpa perlu pengecekan status sebelumnya

        // Simpan perubahan status
        $intern->save();
        if (in_array($intern->internship_status, ['active', 'completed', 'accepted'])) {
            $intern->user?->createMemberCard();
        }

        // Redirect dengan pesan sukses
        return redirect()->route('admin.interns.index')->with('success', 'Status berhasil diperbarui!');
    }


    /**
     * PATCH /admin/interns/bulk/status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids'               => 'required|array|min:1',
            'ids.*'             => 'integer|exists:internship_registrations,id',
            'internship_status' => 'required|in:waiting,active,completed,exited,pending,accepted,rejected',
        ]);

        $affected = 0;
        $mailCount = 0;
        $mailList  = [];

        DB::transaction(function () use ($validated, &$affected, &$mailCount, &$mailList) {
            $interns = IR::whereIn('id', $validated['ids'])->lockForUpdate()->get();

            foreach ($interns as $intern) {
                if ($intern->internship_status === $validated['internship_status']) {
                    continue;
                }

                $old = $intern->internship_status;
                $intern->internship_status = $validated['internship_status'];
                $intern->save();
                if (in_array($intern->internship_status, ['active', 'completed', 'accepted'])) {
                    $intern->user?->createMemberCard();
                }
                $this->syncPemagangRole($intern);

                if ($old !== IR::STATUS_ACCEPTED && $intern->internship_status === IR::STATUS_ACCEPTED) {
                    if ($to = $this->sendAcceptedEmail($intern)) {
                        $mailCount++;
                        $mailList[] = ['to' => $to, 'name' => $intern->fullname];
                    }
                }

                $affected++;
            }
        });

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'ok'       => true,
                'affected' => $affected,
                'mail'     => [
                    'count' => $mailCount,
                    'list'  => $mailList,
                ],
            ]);
        }

        return back()
            ->with('success', "Status {$affected} pemagang diperbarui.")
            ->with('mail_info', $mailCount ? [
                'title' => "Email notifikasi terkirim ({$mailCount})",
                'list'  => $mailList,
            ] : null);
    }


    /**
     * Admin unggah/ganti file untuk satu pemagang.
     */
    public function updateFiles(Request $request, IR $intern)
    {
        $request->validate([
            'cv_ktp_portofolio_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'portofolio_visual'     => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
        ]);

        $data = [];

        if ($request->hasFile('cv_ktp_portofolio_pdf')) {
            $data['cv_ktp_portofolio_pdf'] = $this->storeWithOriginalName(
                $request->file('cv_ktp_portofolio_pdf'),
                'uploads'
            );
        }

        if ($request->hasFile('portofolio_visual')) {
            $data['portofolio_visual'] = $this->storeWithOriginalName(
                $request->file('portofolio_visual'),
                'uploads'
            );
        }

        if (!empty($data)) {
            $intern->fill($data)->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'paths' => $data]);
        }

        return back()->with('success', 'Berkas berhasil diperbarui.');
    }

        /**
     * Spatie Browsershot — render JS/canvas → PDF 1 halaman (full-bleed).
     * GET /admin/interns/{intern}/certificate.pdf
     */
    public function certificatePdf(IR $intern, CertificatePdf $pdf)
    {
        if ($intern->internship_status !== IR::STATUS_COMPLETED) {
            abort(403, 'Sertifikat hanya tersedia untuk pemagang yang sudah selesai.');
        }

        Carbon::setLocale('id');
        $start = $intern->start_date ? Carbon::parse($intern->start_date) : null;
        $end   = $intern->end_date   ? Carbon::parse($intern->end_date)   : null;

        $startDateStr = $start ? $start->translatedFormat('j F Y') : '';
        $endDateStr   = $end   ? $end->translatedFormat('j F Y')   : '';

        $durationText = 'beberapa bulan';
        if ($start && $end) {
            $months = round($start->diffInDays($end) / 30, 1);
            $durationText = str_replace('.', ',', (string) $months) . ' bulan';
        }

        $data = [
            'title'          => 'Sertifikat',
            'name'           => (string) $intern->fullname,
            'role'           => (string) ($intern->internship_interest ?: 'Programmer'),
            'company'        => 'Seven Inc.',
            'duration'       => $durationText,
            'start_date'     => $startDateStr,
            'end_date'       => $endDateStr,
            'city'           => (string) ($intern->current_city ?: 'Yogyakarta'),

            // label & penandatangan
            'hr_label'       => 'HR Department',
            'owner_label'    => 'Owner Seven Inc.',
            'hr_name'        => 'Ari Setia Husbana',
            'owner_name'     => 'Rekario Danny',

        ];

        $safe = trim(preg_replace('/[^A-Za-z0-9_\- ]+/', '', (string) $intern->fullname)) ?: 'Pemagang';
        $downloadName = 'Sertifikat_' . Str::slug($safe, '_') . '.pdf';

        // Ganti view sesuai template yang ingin dipakai
        return $pdf->download('certificates.certmagangjogjacom', $data, $downloadName);
    }

    /**
     * PREVIEW HTML — certareakerjacom (opsional)
     */
    public function certificateAreaKerjaCom(IR $intern)
    {
        if ($intern->internship_status !== IR::STATUS_COMPLETED) {
            abort(403, 'Sertifikat hanya tersedia untuk pemagang yang sudah selesai.');
        }

        Carbon::setLocale('id');
        $start = $intern->start_date ? Carbon::parse($intern->start_date) : null;
        $end   = $intern->end_date   ? Carbon::parse($intern->end_date)   : null;

        $startDate = $start ? $start->translatedFormat('j F Y') : '';
        $endDate   = $end   ? $end->translatedFormat('j F Y')   : '';

        $durationText = 'beberapa bulan';
        if ($start && $end) {
            $months = round($start->diffInDays($end) / 30, 1);
            $durationText = str_replace('.', ',', (string)$months) . ' bulan';
        }

        // >>> embed aset jadi data URI supaya 100% ter-render
        $bg     = $this->dataUriPublic('images/bg_areakerja.png');
        $logo   = $this->dataUriPublic('images/logo_areakerja.png');
        $ttdHr  = $this->dataUriPublic('images/ttd_arisetiahusbana.png');
        $ttdDir = $this->dataUriPublic('images/ttd_pipitdamayanti.png');

        return view('certificates.certareakerjacom', [
            'title'        => 'SERTIFIKAT',
            'recipient'    => (string) $intern->fullname,
            'deptText'     => (string) $intern->internship_interest,
            'durationText' => $durationText,
            'startDate'    => $startDate,
            'endDate'      => $endDate,

            'hrRole'       => 'HR Departement',
            'hrName'       => 'Ari Setia Husbana',
            'dirRole'      => 'Direktur',
            'dirName'      => 'Pipit Damayanti',

            'bg'     => $bg,
            'logo'   => $logo,
            'ttdHr'  => $ttdHr,
            'ttdDir' => $ttdDir,
        ]);
    }

    /**
     * PDF DOWNLOAD — certareakerjacom
     */
    public function certificateAreaKerjaComPdf(IR $intern)
    {
        if ($intern->internship_status !== IR::STATUS_COMPLETED) {
            abort(403, 'Sertifikat hanya tersedia untuk pemagang yang sudah selesai.');
        }

        @set_time_limit(180);
        @ini_set('max_execution_time', '180');

        Carbon::setLocale('id');
        $start = $intern->start_date ? Carbon::parse($intern->start_date) : null;
        $end   = $intern->end_date   ? Carbon::parse($intern->end_date)   : null;

        $startDate = $start ? $start->translatedFormat('j F Y') : '';
        $endDate   = $end   ? $end->translatedFormat('j F Y')   : '';

        $durationText = 'beberapa bulan';
        if ($start && $end) {
            $months = round($start->diffInDays($end) / 30, 1);
            $durationText = str_replace('.', ',', (string)$months) . ' bulan';
        }

        // >>> data URI (base64)
        $bg     = $this->dataUriPublic('images/bg_areakerja.png');
        $logo   = $this->dataUriPublic('images/logo_areakerja.png');
        $ttdHr  = $this->dataUriPublic('images/ttd_arisetiahusbana.png');
        $ttdDir = $this->dataUriPublic('images/ttd_pipitdamayanti.png');

        $html = view('certificates.certareakerjacom', [
            'title'        => 'SERTIFIKAT',
            'recipient'    => (string) $intern->fullname,
            'deptText'     => (string) $intern->internship_interest,
            'durationText' => $durationText,
            'startDate'    => $startDate,
            'endDate'      => $endDate,

            'hrRole'       => 'HR Departement',
            'hrName'       => 'Ari Setia Husbana',
            'dirRole'      => 'Direktur',
            'dirName'      => 'Pipit Damayanti',

            'bg'     => $bg,
            'logo'   => $logo,
            'ttdHr'  => $ttdHr,
            'ttdDir' => $ttdDir,
        ])->render();

        // Fallback: set flag siap render (tanpa nunggu Google Fonts)
        $html .= <<<HTML
    <script>
    (function(){
    function imagesReady(){
        var imgs=[].slice.call(document.images||[]);
        if(!imgs.length) return Promise.resolve();
        return Promise.all(imgs.map(function(i){
        if(i.complete) return Promise.resolve();
        return new Promise(function(r){
            i.addEventListener('load', r, {once:true});
            i.addEventListener('error', r, {once:true});
        });
        }));
    }
    var timer=setTimeout(function(){window.__CERT_READY=true;}, 800);
    imagesReady().then(function(){ clearTimeout(timer); window.__CERT_READY=true; });
    })();
    </script>
    HTML;

        $safeName = trim(preg_replace('/[^A-Za-z0-9_\- ]+/', '', (string) $intern->fullname)) ?: 'Pemagang';
        $filename = 'Sertifikat_AreaKerja_' . Str::slug($safeName, '_') . '.pdf';
        $dir  = storage_path('app/public/certificates');
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        $bs = Browsershot::html($html)
            ->showBackground()
            ->margins(0, 0, 0, 0)
            ->setOption('printBackground', true)
            ->setOption('preferCSSPageSize', true)
            ->emulateMedia('print')
            ->windowSize(1123, 794)
            ->deviceScaleFactor(2)
            ->waitForFunction('document.readyState === "complete" || window.__CERT_READY === true')
            ->setOption('waitUntil', 'domcontentloaded')
            ->timeout(180);

        if ($chromePath = env('BROWSERSHOT_CHROME_PATH')) {
            $bs->setChromePath($chromePath);
        }
        // $bs->addChromiumArguments(['--no-sandbox','--disable-setuid-sandbox']); // bila perlu (Linux)

        $bs->savePdf($path);

        return response()->download($path, $filename, ['Content-Type' => 'application/pdf'])
            ->deleteFileAfterSend(true);
    }

}
