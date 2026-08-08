<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\InternshipRegistration as IR;

/**
 * Seed 10 akun pemagang dummy dengan berbagai status.
 * Password semua akun: password123
 *
 * Email login:
 *  pemagang1@demo.com  → waiting   (baru submit, belum diproses)
 *  pemagang2@demo.com  → waiting
 *  pemagang3@demo.com  → accepted  (sudah diterima)
 *  pemagang4@demo.com  → accepted
 *  pemagang5@demo.com  → active    (sedang magang)
 *  pemagang6@demo.com  → active
 *  pemagang7@demo.com  → completed (sudah selesai magang)
 *  pemagang8@demo.com  → completed
 *  pemagang9@demo.com  → rejected  (ditolak)
 *  pemagang10@demo.com → waiting   (draft — belum submit)
 */
class DummyPemagangSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $data = [
            // [ name, email, status, is_draft, interest, city, institution, start, end ]
            ['Budi Santoso',          'pemagang1@demo.com',  IR::STATUS_WAITING,   false, 'UI/UX Designer',                    'Yogyakarta', 'Universitas Gadjah Mada',       '2026-09-01', '2026-11-30'],
            ['Siti Rahayu',           'pemagang2@demo.com',  IR::STATUS_WAITING,   false, 'Programmer (Front End / Backend)', 'Sleman',     'Universitas Negeri Yogyakarta',  '2026-09-01', '2026-11-30'],
            ['Ahmad Fauzi',           'pemagang3@demo.com',  IR::STATUS_ACCEPTED,  false, 'Graphic Designer (Konten Kreatif)','Bantul',     'ISI Yogyakarta',                 '2026-08-01', '2026-10-31'],
            ['Dewi Kurniawati',       'pemagang4@demo.com',  IR::STATUS_ACCEPTED,  false, 'Digital Marketing',                'Yogyakarta', 'Universitas Atma Jaya',          '2026-08-01', '2026-10-31'],
            ['Rizky Pratama',         'pemagang5@demo.com',  IR::STATUS_ACTIVE,    false, 'Social Media Specialist',          'Yogyakarta', 'Universitas Muhammadiyah Yogya', '2026-07-01', '2026-09-30'],
            ['Nur Indah Lestari',     'pemagang6@demo.com',  IR::STATUS_ACTIVE,    false, 'Content Writer',                   'Sleman',     'Universitas Islam Indonesia',    '2026-07-01', '2026-09-30'],
            ['Bambang Subambang',     'pemagang7@demo.com',  IR::STATUS_COMPLETED, false, 'Administration',                   'Yogyakarta', 'AMKOM Yogyakarta',               '2026-01-06', '2026-04-06'],
            ['Fitria Anggraeni',      'pemagang8@demo.com',  IR::STATUS_COMPLETED, false, 'Human Resources (HR)',             'Bantul',     'Universitas Mercu Buana',        '2026-02-01', '2026-04-30'],
            ['Dimas Eko Prayogo',     'pemagang9@demo.com',  IR::STATUS_REJECTED,  false, 'Photographer',                    'Kulonprogo', 'SMK N 2 Yogyakarta',             '2026-09-01', '2026-11-30'],
            ['Anisa Putri Handayani', 'pemagang10@demo.com', IR::STATUS_WAITING,   true,  'Project Manager',                  'Yogyakarta', 'Universitas Sanata Dharma',      '2026-10-01', '2026-12-31'],
        ];

        foreach ($data as $i => [$name, $email, $status, $isDraft, $interest, $city, $institution, $start, $end]) {
            // Buat user kalau belum ada
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $name,
                    'password' => $password,
                    'role'     => 'pemagang',
                ]
            );

            // Buat registrasi kalau belum ada
            if (!IR::where('user_id', $user->id)->exists()) {
                IR::create([
                    'user_id'                => $user->id,
                    'fullname'               => $name,
                    'born_date'              => '2000-0' . ($i + 1) . '-15',
                    'student_id'             => '2021' . str_pad($i + 1, 8, '0', STR_PAD_LEFT),
                    'email'                  => $email,
                    'gender'                 => $i % 2 === 0 ? 'Laki-laki' : 'Perempuan',
                    'phone_number'           => '0812345678' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'institution_name'       => $institution,
                    'study_program'          => 'Informatika',
                    'faculty'                => 'Teknik',
                    'current_city'           => $city,
                    'internship_reason'      => 'Ingin mendapatkan pengalaman kerja nyata dan mengaplikasikan ilmu dari kampus.',
                    'internship_type'        => 'Magang Mandiri',
                    'internship_arrangement' => 'Onsite',
                    'current_status'         => 'Mahasiswa/Pelajar',
                    'english_book_ability'   => 'Saya bisa',
                    'internship_interest'    => $interest,
                    'start_date'             => $start,
                    'end_date'               => $end,
                    'internship_status'      => $isDraft ? IR::STATUS_WAITING : $status,
                    'is_draft'               => $isDraft,
                    'draft_saved_at'         => $isDraft ? now() : null,
                    // kolom NOT NULL lainnya
                    'family_status'          => 'Tidak',
                    'boarding_info'          => 'Tidak',
                    'supervisor_contact'     => '-',
                    'parent_wa_contact'      => '-',
                    'social_media_instagram' => '@' . strtolower(str_replace(' ', '_', $name)),
                    'current_activities'     => 'Mahasiswa aktif semester ' . (6 + $i % 3),
                    'design_software'        => '-',
                    'video_software'         => '-',
                    'programming_languages'  => in_array($interest, ['Programmer (Front End / Backend)', 'UI/UX Designer']) ? 'JavaScript, PHP' : '-',
                    'owned_tools'            => 'Laptop',
                    'internship_info_sources'=> 'Instagram',
                ]);
            }
        }

        $this->command->info('✅ 10 data dummy pemagang berhasil dibuat!');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('  AKUN DUMMY PEMAGANG — password semua: password123');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('  pemagang1@demo.com   → Budi Santoso       [Waiting]');
        $this->command->info('  pemagang2@demo.com   → Siti Rahayu        [Waiting]');
        $this->command->info('  pemagang3@demo.com   → Ahmad Fauzi        [Accepted]');
        $this->command->info('  pemagang4@demo.com   → Dewi Kurniawati    [Accepted]');
        $this->command->info('  pemagang5@demo.com   → Rizky Pratama      [Active]');
        $this->command->info('  pemagang6@demo.com   → Nur Indah Lestari  [Active]');
        $this->command->info('  pemagang7@demo.com   → Bambang Subambang  [Completed]');
        $this->command->info('  pemagang8@demo.com   → Fitria Anggraeni   [Completed]');
        $this->command->info('  pemagang9@demo.com   → Dimas Eko Prayogo  [Rejected]');
        $this->command->info('  pemagang10@demo.com  → Anisa Putri H.     [Draft]');
        $this->command->info('═══════════════════════════════════════════════════════════');
    }
}
