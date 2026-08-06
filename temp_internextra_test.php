<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$intern = App\Models\InternshipRegistration::where('internship_status','completed')->first();
if (!$intern) { echo "NO INTERN\n"; exit(0); }
$extra = App\Models\InternExtra::firstOrNew(['internship_registration_id' => $intern->id]);
$extra->alumni_group_url = 'https://example.com/test';
$extra->alumni_group_label = 'Test Label';
$extra->job_info_url = 'https://example.com/job';
$extra->job_info_description = 'Test desc';
$extra->save();
echo "Saved extra: " . ($extra->id ?? 'null') . "\n";
$re = App\Models\InternExtra::where('internship_registration_id', $intern->id)->first();
echo "Read back alumni_url=" . ($re->alumni_group_url ?? 'NULL') . "\n";
