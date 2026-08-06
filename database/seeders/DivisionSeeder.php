<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            'Administration',
            'Human Resources (HR)',
            'UI/UX Designer',
            'Programmer (Front End / Backend)',
            'Photographer',
            'Videographer',
            'Graphic Designer (Konten Kreatif)',
            'Social Media Specialist',
            'Content Writer',
            'Content Planner',
            'Sales & Marketing',
            'Public Relations (Marcomm)',
            'Digital Marketing',
            'TikTok Creator',
            'Project Manager',
            'Pengelasan',
            'Animasi',
            'Customer Service',
        ];

        foreach ($divisions as $i => $name) {
            Division::firstOrCreate(
                ['name' => $name],
                [
                    'is_active'  => true,
                    'sort_order' => $i + 1,
                ]
            );
        }
    }
}
