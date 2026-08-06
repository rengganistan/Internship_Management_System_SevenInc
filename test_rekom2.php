<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulasi exact flow dari RekomendasiController::generate()
$internId = 3; // intern satuduatiga yang status completed
$intern = App\Models\InternshipRegistration::findOrFail($internId);

echo "Intern: " . $intern->fullname . " | status: " . $intern->internship_status . "\n";

// Test 1: cek InternExtra::firstOrNew
$extra = App\Models\InternExtra::firstOrNew(['internship_registration_id' => $intern->id]);
echo "Extra is new: " . ($extra->exists ? 'NO (exists)' : 'YES (new)') . "\n";
echo "Extra id: " . ($extra->id ?? 'null') . "\n";

// Test 2: set rekomendasi_path dan save
$relPath = "documents/rekomendasi/test_" . $intern->id . ".pdf";
$extra->internship_registration_id = $intern->id;
$extra->rekomendasi_path           = $relPath;
$extra->rekomendasi_url            = "http://test/" . $relPath;
$extra->rekomendasi_granted_at     = now();

try {
    $extra->save();
    echo "SAVE SUCCESS — ID: " . $extra->id . "\n";
    echo "rekomendasi_path: " . $extra->rekomendasi_path . "\n";
} catch (Exception $e) {
    echo "SAVE ERROR: " . $e->getMessage() . "\n";
}

// Test 3: verify DB
$check = App\Models\InternExtra::where('internship_registration_id', $intern->id)->first();
echo "DB check — path: " . ($check->rekomendasi_path ?? 'NULL') . "\n";

// Test 4: check pemagang login context
// Simulasi apa yang DocumentController->index() lihat
$user_id = $intern->user_id;
echo "user_id from registration: " . $user_id . "\n";

$regForUser = App\Models\InternshipRegistration::where('user_id', $user_id)->latest('id')->first();
echo "registration found for user: " . ($regForUser ? $regForUser->id . ' / ' . $regForUser->fullname : 'NULL') . "\n";

$extrasForUser = App\Models\InternExtra::where('internship_registration_id', $regForUser?->id)->first();
echo "extras found for user registration: " . ($extrasForUser ? "YES — path: " . $extrasForUser->rekomendasi_path : "NULL") . "\n";
