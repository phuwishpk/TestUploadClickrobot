<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Classroom;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::firstOrCreate(
            ['code' => 'DEMO001'],
            [
                'name' => 'โรงเรียนทดสอบ',
                'description' => 'โรงเรียนสำหรับทดสอบระบบ',
            ]
        );

        $this->command->info("School '{$school->name}' created/updated.");
        $this->command->info("Slug: {$school->slug}");
        $this->command->info("Classrooms: " . $school->classrooms()->count());
    }
}
