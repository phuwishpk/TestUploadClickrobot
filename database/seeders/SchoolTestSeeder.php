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
        // School 1: Primary School (โรงเรียนประถม)
        $school1 = School::create([
            'name' => 'Srisakda Primary School',
            'slug' => 'srisakda_primary',
            'code' => 'PRI001',
            'description' => 'Srisakda Primary School - Elementary education',
        ]);

        // School 2: Secondary School (โรงเรียนมัธยม)
        $school2 = School::create([
            'name' => 'Srisakda Secondary School',
            'slug' => 'srisakda_secondary',
            'code' => 'SEC001',
            'description' => 'Srisakda Secondary School - High school education',
        ]);

        // School 3: Kindergarten (โรงเรียนอนุบาล)
        $school3 = School::create([
            'name' => 'Srisakda Kindergarten',
            'slug' => 'srisakda_kindergarten',
            'code' => 'KIN001',
            'description' => 'Srisakda Kindergarten - Early childhood education',
        ]);

        $this->command->info('Created 3 schools');

        // Create school admin users
        $admin1 = User::create([
            'name' => 'School Admin Primary',
            'email' => 'admin_primary@school.com',
            'password' => Hash::make('12345'),
            'role' => 'school_admin',
            'school_id' => $school1->id,
        ]);

        $admin2 = User::create([
            'name' => 'School Admin Secondary',
            'email' => 'admin_secondary@school.com',
            'password' => Hash::make('12345'),
            'role' => 'school_admin',
            'school_id' => $school2->id,
        ]);

        $admin3 = User::create([
            'name' => 'School Admin Kindergarten',
            'email' => 'admin_kindergarten@school.com',
            'password' => Hash::make('12345'),
            'role' => 'school_admin',
            'school_id' => $school3->id,
        ]);

        $this->command->info('Created 3 school admins');

        // Create teachers for each school
        $teacher1 = User::create([
            'name' => 'Teacher Primary 1',
            'email' => 'teacher_primary@school.com',
            'password' => Hash::make('12345'),
            'role' => 'teacher',
            'school_id' => $school1->id,
        ]);

        $teacher2 = User::create([
            'name' => 'Teacher Secondary 1',
            'email' => 'teacher_secondary@school.com',
            'password' => Hash::make('12345'),
            'role' => 'teacher',
            'school_id' => $school2->id,
        ]);

        $teacher3 = User::create([
            'name' => 'Teacher Kindergarten 1',
            'email' => 'teacher_kindergarten@school.com',
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
        $this->command->info('School Admin Primary: admin_primary@school.com / 12345');
        $this->command->info('School Admin Secondary: admin_secondary@school.com / 12345');
        $this->command->info('Teacher Primary: teacher_primary@school.com / 12345');
        $this->command->info('Teacher Secondary: teacher_secondary@school.com / 12345');
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
