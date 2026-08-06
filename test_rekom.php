<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test: apakah generate berjalan tanpa error dengan DomPDF
$intern = App\Models\InternshipRegistration::where('internship_status','completed')->first();
if (!$intern) { echo "No completed intern found\n"; exit; }

echo "Testing generate for: " . $intern->fullname . " (ID: {$intern->id})\n";

$config = App\Models\RekomendasiSetting::first();
if (!$config) {
    echo "ERROR: No RekomendasiSetting found\n";
    exit;
}
echo "Config found: " . $config->company_name . "\n";

// Test DomPDF instead of Browsershot
$html = view('admin.rekomendasi_letter', [
    'companyName'         => $config->company_name,
    'companyAddress'      => $config->company_address,
    'companyCity'         => $config->company_city,
    'companyPhone'        => $config->company_phone,
    'companyPostalCode'   => $config->company_postal_code,
    'companyBrand'        => $config->company_brand,
    'leaderName'          => $config->leader_name,
    'leaderTitle'         => $config->leader_title,
    'letterNumber'        => '001/SR/TEST/2026',
    'letterDateStr'       => '5 Agustus 2026',
    'participantName'     => $intern->fullname,
    'participantId'       => $intern->student_id ?? '-',
    'participantMajor'    => $intern->study_program ?? '-',
    'participantInstitute'=> $intern->institution_name ?? '-',
    'bodyText'            => 'Test body text untuk ' . $intern->fullname,
    'logoData'            => null,
    'stampData'           => null,
])->render();

echo "HTML rendered successfully (" . strlen($html) . " chars)\n";

// Try DomPDF
try {
    $pdf = Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)->setPaper('A4', 'portrait');
    $output = $pdf->output();
    echo "DomPDF success: " . strlen($output) . " bytes\n";
    
    // Save test file
    $dir = storage_path('app/public/documents/rekomendasi');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $path = $dir . '/test_rekomendasi.pdf';
    file_put_contents($path, $output);
    echo "Saved to: " . $path . "\n";
    echo "Exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo "DomPDF ERROR: " . $e->getMessage() . "\n";
}
