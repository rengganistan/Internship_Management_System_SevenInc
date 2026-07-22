<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class SuratPenilaianController extends Controller
{
    // Menampilkan form untuk mengisi data dan mengenerate PDF
    public function showForm()
    {
        return view('admin.form-penilaian');
    }

    // Fungsi untuk menghasilkan PDF berdasarkan inputan admin
    public function generatePdf(Request $request)
    {
        // Validasi data inputan
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nim' => 'required|string|max:255',
            'program_studi' => 'required|string|max:255',
            'kompetensi' => 'required|string|max:255',
            'aspects' => 'required|array',
            'aspects.*' => 'required|integer|between:0,100', // Nilai aspek penilaian
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // Logo opsional
        ]);

        // Upload logo jika ada
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // Siapkan data untuk tampilan PDF
        $data = [
            'name' => $validated['name'],
            'nim' => $validated['nim'],
            'program_studi' => $validated['program_studi'],
            'kompetensi' => $validated['kompetensi'],
            'aspects' => $validated['aspects'],
            'logo' => isset($logoPath) ? Storage::url($logoPath) : null, // Path logo
            'date' => now()->toFormattedDateString(),
        ];

        // Render PDF dengan tampilan yang telah disiapkan
        $pdf = Pdf::loadView('admin.pdf-template', $data);

        // Mengunduh PDF yang sudah digenerate
        return $pdf->download('form_penilaian_magang.pdf');
    }
}
