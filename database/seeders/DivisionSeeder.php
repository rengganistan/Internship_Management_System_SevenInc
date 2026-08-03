<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            'Project Manager',
            'Administration',
            'Human Resources (HR)',
            'UI/UX',
            'Programmer (Front End / Backend)',
            'Photographer',
            'Videographer',
            'Graphic Designer',
            'Social Media Specialist',
            'Content Writer',
            'Content Planner',
            'Sales & Marketing',
            'Public Relations (Marcomm)',
            'Digital Marketing',
            'TikTok Creator',
            'Welding',
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
