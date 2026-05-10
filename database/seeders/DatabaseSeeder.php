<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\School;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\ParentStudent;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default school
        $school = School::create([
            'name' => 'Demo School',
            'slug' => 'demo_school',
            'code' => 'DEMO001',
            'description' => 'Demo school for testing',
        ]);

        // Create default teacher
        $teacher = User::create([
            'name' => 'ครูผู้สอน',
            'email' => 'teacher@school.com',
            'password' => Hash::make('12345'),
            'role' => 'teacher',
        ]);

        // Create classrooms
        $classroom1 = Classroom::create([
            'name' => 'ม.1/1',
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
        ]);

        $classroom2 = Classroom::create([
            'name' => 'ม.2/1',
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
        ]);

        // Create students
        $student1 = Student::create([
            'name' => 'สมชาย ใจดี',
            'code' => 'นร001',
            'classroom_id' => $classroom1->id,
        ]);

        $student2 = Student::create([
            'name' => 'สมหญิง รักเรียน',
            'code' => 'นร002',
            'classroom_id' => $classroom1->id,
        ]);

        $student3 = Student::create([
            'name' => 'ดำ รุ่งเรือง',
            'code' => 'นร003',
            'classroom_id' => $classroom2->id,
        ]);

        // Create student user accounts
        User::create([
            'name' => $student1->name,
            'email' => 'student1@school.com',
            'password' => Hash::make('12345'),
            'role' => 'student',
            'student_code' => $student1->code,
        ]);

        User::create([
            'name' => $student2->name,
            'email' => 'student2@school.com',
            'password' => Hash::make('12345'),
            'role' => 'student',
            'student_code' => $student2->code,
        ]);

        // Create parent
        $parent = User::create([
            'name' => 'นางสมหมาย ผู้ปกครอง',
            'email' => 'parent@school.com',
            'password' => Hash::make('12345'),
            'role' => 'parent',
        ]);

        // Create student account for demo
        User::create([
            'name' => 'นักเรียนทดสอบ',
            'email' => 'student@school.com',
            'password' => Hash::make('12345'),
            'role' => 'student',
            'student_code' => null,
        ]);

        // Link parent to students
        ParentStudent::create([
            'parent_id' => $parent->id,
            'student_id' => $student1->id,
        ]);

        ParentStudent::create([
            'parent_id' => $parent->id,
            'student_id' => $student2->id,
        ]);
    }
}
