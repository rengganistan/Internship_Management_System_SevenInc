<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Webinar;
use App\Models\WebinarAttendance;
use App\Models\User;

/**
 * Seed dummy webinar attendances:
 * - Ambil semua webinar yang ada
 * - Ambil semua user pemagang yang ada
 * - Buat attendance dengan status APPROVED (sudah upload bukti, sudah di-acc admin)
 * - Satu user bisa daftar ke semua webinar
 */
class DummyWebinarAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $webinars = Webinar::all();
        $users    = User::where('role', 'pemagang')->get();

        if ($webinars->isEmpty()) {
            $this->command->warn('Tidak ada webinar ditemukan. Buat webinar dulu dari panel admin.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('Tidak ada user pemagang ditemukan. Jalankan DummyPemagangSeeder dulu.');
            return;
        }

        $created = 0;
        $skipped = 0;

        // Gunakan file proof dummy (placeholder path — tidak perlu file beneran untuk test)
        $dummyProofPath = 'webinar-proofs/dummy-proof.jpg';

        foreach ($webinars as $webinar) {
            foreach ($users as $user) {
                // Skip kalau sudah ada attendance untuk kombinasi ini
                $exists = WebinarAttendance::where('webinar_id', $webinar->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                WebinarAttendance::create([
                    'webinar_id'    => $webinar->id,
                    'user_id'       => $user->id,
                    'proof_file'    => $dummyProofPath,
                    'proof_note'    => 'Dummy attendance untuk keperluan testing.',
                    'status'        => WebinarAttendance::STATUS_APPROVED,
                    'reviewed_by'   => User::where('role', 'admin')->value('id') ?? 1,
                    'reviewed_at'   => now(),
                    'certificate_id'=> null, // belum generate sertifikat
                ]);

                $created++;
            }
        }

        $this->command->info("✅ {$created} attendance dummy berhasil dibuat.");
        if ($skipped > 0) {
            $this->command->warn("⚠ {$skipped} kombinasi dilewati (sudah ada).");
        }

        $this->command->info('');
        $this->command->info('Sekarang buka: Admin → Kelola Webinar → pilih webinar → klik "Generate Sertifikat"');
        $this->command->info('Form akan otomatis terisi nama-nama peserta yang sudah approved.');
    }
}
