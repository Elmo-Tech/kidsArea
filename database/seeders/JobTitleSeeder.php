<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\JobTitle;
use Illuminate\Database\Seeder;

class JobTitleSeeder extends Seeder
{
    public function run(): void
    {
        $jobTitles = [
            'مدير',
            'مدرب سباحة',
            'معلمة',
            'كاشير',
        ];

        foreach ($jobTitles as $jobTitle) {
            JobTitle::query()->firstOrCreate([
                'name' => $jobTitle,
            ]);
        }
    }
}
