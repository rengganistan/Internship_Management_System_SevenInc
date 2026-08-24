<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\InternshipRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MembercardController extends Controller
{
    // Daftar brand yang tersedia (konsisten dengan User::getBrandPrefix)
    private array $brandList = [
        'magangjogja.com'  => 'Magangjogja',
        'areakerja.com'    => 'Areakerja',
        'republikweb.net'  => 'Republikweb',
        'titipsini.com'    => 'Titipsini',
        'ambilpaket.com'   => 'Ambilpaket',
        'bikinkepo.com'    => 'Bikinkepo',
        'bimbelcerdas.com' => 'Bimbelcerdas.com',
        'latihankerja.com' => 'Latihankerja.com',
        'lowkerjateng.com' => 'Lowkerjateng.com',
        'lowkerjogja.com'  => 'Lowkerjogja.com',
        'pijatjogja.com'   => 'Pijatjogja.com',
        'sayabantu.com'    => 'Sayabantu.com',
        'titikvisual.com'  => 'Titikvisual',
        'tuantanah.com'    => 'Tuantanah',
        'tukanglas.org'    => 'Tukanglas.org',
        'adakamarid'       => 'Adakamar.id',
        'seven inc'        => 'Seven Inc',
    ];

    public function index(Request $request)
    {
        $brandFilter = $request->get('brand');

        $query = Download::orderByDesc('created_at');

        if ($brandFilter) {
            $query->where('brand', $brandFilter);
        }

        $downloads = $query->get();

        // Daftar brand unik yang sudah ada di tabel downloads
        $availableBrands = Download::select('brand')
            ->whereNotNull('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return view('admin.membercards.index', compact('downloads', 'availableBrands', 'brandFilter'));
    }

    public function logDownload(Request $request)
    {
        $data = $request->validate([
            'model_url' => 'required|string',
            'name' => 'required|string',
            'id' => 'required|string', // this is the code
            'angkatan' => 'nullable|string',
            'instansi' => 'nullable|string',
            'brand' => 'nullable|string',
            'filename' => 'nullable|string',
        ]);

        // Find existing record ONLY
        $download = Download::where('code', $data['id'])->first();

        // If not found, DO NOT create a new record
        if (!$download) {
            return response()->json([
                'message' => 'Download record not found — please contact admin.',
                'status' => false
            ], 404);
        }

        if (!$download->has_downloaded) {
            $download->has_downloaded = true;
            $download->downloaded_at = now();
            $download->save();
        }

        return response()->json([
            'message' => 'Download status updated successfully.',
            'status' => true
        ]);
    }

    public function show($code)
    {
        $download = Download::where('code', $code)->firstOrFail();
        return view('admin.membercards.show', compact('download'));
    }

    public function edit($code)
    {
        $download = Download::where('code', $code)->firstOrFail();
        return view('admin.membercards.edit', compact('download'));
    }

    public function update(Request $request, $code)
    {
        $download = Download::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'angkatan' => 'nullable|string|max:10',
            'instansi' => 'nullable|string|max:255',
            'brand'    => 'nullable|string|max:100',
        ]);

        // Update code jika brand berubah
        if (!empty($validated['brand']) && $validated['brand'] !== $download->brand) {
            $prefix    = (new User)->getBrandPrefix($validated['brand']);
            $oldPrefix = (new User)->getBrandPrefix($download->brand ?? '');
            $numericPart = substr($download->code, strlen($oldPrefix));
            $validated['code'] = $prefix . $numericPart;
        } else {
            $validated['code'] = $download->code;
        }

        $download->update($validated);

        return redirect()->route('admin.membercards.show', $validated['code'])
            ->with('success', 'Data membercard berhasil diperbarui.');
    }

    public function destroy($code)
    {
        Download::where('code', $code)->delete();
        return redirect()->route('admin.membercards.index')
            ->with('success', 'Membercard deleted successfully.');
    }

    // ===== GENERATE MEMBERCARD =====

    /**
     * Generate membercard untuk satu pemagang berdasarkan code.
     * Hanya bisa untuk pemagang dengan status 'completed'.
     */
    public function generateOne(Request $request, $code)
    {
        $download = Download::where('code', $code)->firstOrFail();

        // Cek user dan status magang
        $user = User::find($download->user_id);
        if (!$user) {
            return back()->with('error', "User tidak ditemukan untuk membercard kode {$code}.");
        }

        $registration = InternshipRegistration::where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (!$registration || $registration->internship_status !== InternshipRegistration::STATUS_COMPLETED) {
            return back()->with('error', "Membercard hanya bisa digenerate untuk pemagang yang sudah selesai. Status {$user->name}: " . ($registration?->internship_status ?? 'tidak ada data'));
        }

        // Re-generate (refresh data terbaru ke record download)
        $user->createMemberCard();

        return back()->with('success', "✅ Membercard untuk <strong>{$user->name}</strong> berhasil digenerate dan sudah tersedia di Dokumen Saya pemagang.");
    }

    /**
     * Generate membercard bulk — semua atau filter berdasarkan brand.
     * Hanya untuk pemagang dengan status 'completed'.
     */
    public function generateBulk(Request $request)
    {
        $request->validate([
            'brand' => 'nullable|string|max:100',
        ]);

        $brandFilter = $request->input('brand');

        // Ambil semua registrasi dengan status completed
        $query = InternshipRegistration::where('internship_status', InternshipRegistration::STATUS_COMPLETED)
            ->with('user');

        // Filter brand jika dipilih
        if ($brandFilter) {
            $query->where('brand', $brandFilter);
        }

        $registrations = $query->get();

        if ($registrations->isEmpty()) {
            $msg = $brandFilter
                ? "Tidak ada pemagang selesai dengan brand '{$brandFilter}'."
                : "Tidak ada pemagang dengan status selesai.";
            return back()->with('error', $msg);
        }

        $generated = 0;
        $skipped   = 0;

        foreach ($registrations as $reg) {
            if (!$reg->user) {
                $skipped++;
                continue;
            }
            $reg->user->createMemberCard();
            $generated++;
        }

        $brandLabel = $brandFilter ? " untuk brand '{$brandFilter}'" : '';
        $msg = "✅ {$generated} membercard berhasil digenerate{$brandLabel}.";
        if ($skipped > 0) {
            $msg .= " {$skipped} dilewati (user tidak ditemukan).";
        }

        return back()->with('success', $msg);
    }
}
