<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\ParentStudent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class SchoolTestSeeder extends Seeder
{
    public function run(): void
    {
        // School 1: Bangrak (BNK)
        $school1 = School::create([
            'name' => 'Bangrak School',
            'slug' => 'bnk',
            'code' => 'BNK001',
            'description' => 'Bangrak School - กรุงเทพ',
        ]);

        // School 2: Saraburi (SRB)
        $school2 = School::create([
            'name' => 'Saraburi School',
            'slug' => 'srb',
            'code' => 'SRB001',
            'description' => 'Saraburi School - สระบุรี',
        ]);

        // School 3: Nonthaburi (NBR)
        $school3 = School::create([
            'name' => 'Nonthaburi School',
            'slug' => 'nbr',
            'code' => 'NBR001',
            'description' => 'Nonthaburi School - นนทบุรี',
        ]);

        $this->command->info('Created 3 schools');

        // Create school admin users
        $admin1 = User::create([
            'name' => 'School Admin BNK',
            'email' => 'admin_bnk@school.com',
            'password' => Hash::make('12345'),
            'role' => 'school_admin',
            'school_id' => $school1->id,
        ]);

        $admin2 = User::create([
            'name' => 'School Admin SRB',
            'email' => 'admin_srb@school.com',
            'password' => Hash::make('12345'),
            'role' => 'school_admin',
            'school_id' => $school2->id,
        ]);

        $admin3 = User::create([
            'name' => 'School Admin NBR',
            'email' => 'admin_nbr@school.com',
            'password' => Hash::make('12345'),
            'role' => 'school_admin',
            'school_id' => $school3->id,
        ]);

        $this->command->info('Created 3 school admins');

        // Create teachers for each school
        $teacher1 = User::create([
            'name' => 'Teacher BNK',
            'email' => 'teacher_bnk@school.com',
            'password' => Hash::make('12345'),
            'role' => 'teacher',
            'school_id' => $school1->id,
        ]);

        $teacher2 = User::create([
            'name' => 'Teacher SRB',
            'email' => 'teacher_srb@school.com',
            'password' => Hash::make('12345'),
            'role' => 'teacher',
            'school_id' => $school2->id,
        ]);

        $teacher3 = User::create([
            'name' => 'Teacher NBR',
            'email' => 'teacher_nbr@school.com',
            'password' => Hash::make('12345'),
            'role' => 'teacher',
            'school_id' => $school3->id,
        ]);

        $this->command->info('Created 3 teachers');

        // Create classrooms for School 1 (Primary)
        $class1a = Classroom::create([
            'name' => 'Prathom 1/1',
            'teacher_id' => $teacher1->id,
            'school_id' => $school1->id,
        ]);
        $class1b = Classroom::create([
            'name' => 'Prathom 2/1',
            'teacher_id' => $teacher1->id,
            'school_id' => $school1->id,
        ]);

        // Create classrooms for School 2 (Secondary)
        $class2a = Classroom::create([
            'name' => 'Mattayom 1/1',
            'teacher_id' => $teacher2->id,
            'school_id' => $school2->id,
        ]);
        $class2b = Classroom::create([
            'name' => 'Mattayom 2/1',
            'teacher_id' => $teacher2->id,
            'school_id' => $school2->id,
        ]);

        // Create classroom for School 3 (Kindergarten)
        $class3a = Classroom::create([
            'name' => 'Anuban 1',
            'teacher_id' => $teacher3->id,
            'school_id' => $school3->id,
        ]);

        $this->command->info('Created 5 classrooms');

        // Create students for each school
        $this->createStudentsForClassroom($class1a, 'P', 5);
        $this->createStudentsForClassroom($class1b, 'P', 5);
        $this->createStudentsForClassroom($class2a, 'M', 8);
        $this->createStudentsForClassroom($class2b, 'M', 8);
        $this->createStudentsForClassroom($class3a, 'K', 10);

        $this->command->info('Created students for all classrooms');

        // Create super admin (for testing)
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@school.com',
            'password' => Hash::make('12345'),
            'role' => 'admin',
            'school_id' => null,
        ]);

        $this->command->info('Created super admin');
        $this->command->info('');
        $this->command->info('=== Test Accounts ===');
        $this->command->info('Super Admin: superadmin@school.com / 12345');
        $this->command->info('School Admin BNK: admin_bnk@school.com / 12345 (Bangrak)');
        $this->command->info('School Admin SRB: admin_srb@school.com / 12345 (Saraburi)');
        $this->command->info('School Admin NBR: admin_nbr@school.com / 12345 (Nonthaburi)');
        $this->command->info('Teacher BNK: teacher_bnk@school.com / 12345 (Bangrak)');
        $this->command->info('Teacher SRB: teacher_srb@school.com / 12345 (Saraburi)');
        $this->command->info('Teacher NBR: teacher_nbr@school.com / 12345 (Nonthaburi)');
    }

    private function createStudentsForClassroom(Classroom $classroom, string $prefix, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $code = $prefix . sprintf('%03d', $i);
            Student::create([
                'name' => "Student {$prefix}{$i}",
                'code' => $code,
                'classroom_id' => $classroom->id,
            ]);
        }
    }
}
