<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\ParentStudent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class TestAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('===========================================');
        $this->command->info('สร้างบัญชีทดสอบสำหรับทุก Role');
        $this->command->info('===========================================');
        $this->command->info('');

        // =============================================
        // SUPER ADMIN (Manager ระดับสูงสุด)
        // =============================================
        User::updateOrCreate(
            ['email' => 'superadmin@test.com'],
            [
                'name' => 'Super Admin (Manager)',
                'password' => Hash::make('12345'),
                'role' => 'admin',
                'school_id' => null,
            ]
        );
        $this->command->info('✓ Super Admin: superadmin@test.com / 12345');
        $this->command->info('  - สิทธิ์: จัดการทุกอย่าง ทุกโรงเรียน');
        $this->command->info('');

        // =============================================
        // SCHOOL 1 - สาขาบางรัก
        // =============================================
        $school1 = School::updateOrCreate(
            ['code' => 'BNK001'],
            [
                'name' => 'โรงเรียนบางรัก',
                'slug' => 'bangrak',
                'code' => 'BNK001',
                'description' => 'โรงเรียนสาขาบางรัก - ระดับประถมศึกษา',
            ]
        );

        // Manager (School Admin) ของ School 1
        User::updateOrCreate(
            ['email' => 'manager.bnk@test.com'],
            [
                'name' => 'ผู้จัดการ บางรัก',
                'password' => Hash::make('12345'),
                'role' => 'school_admin',
                'school_id' => $school1->id,
            ]
        );
        $this->command->info('✓ School Manager (BNK): manager.bnk@test.com / 12345');
        $this->command->info('  - สิทธิ์: จัดการโรงเรียนบางรัก');
        $this->command->info('');

        // Teacher ของ School 1
        $teacher1 = User::updateOrCreate(
            ['email' => 'teacher.bnk@test.com'],
            [
                'name' => 'ครูผู้สอน บางรัก',
                'password' => Hash::make('12345'),
                'role' => 'teacher',
                'school_id' => $school1->id,
            ]
        );
        $this->command->info('✓ Teacher (BNK): teacher.bnk@test.com / 12345');
        $this->command->info('  - สิทธิ์: อัพโหลดสื่อ, จัดการนักเรียนในชั้นเรียนตนเอง');
        $this->command->info('');

        // Classroom สำหรับ School 1
        $classroom1 = Classroom::updateOrCreate(
            ['name' => 'ป.1/1 - บางรัก'],
            [
                'name' => 'ป.1/1 - บางรัก',
                'teacher_id' => $teacher1->id,
                'school_id' => $school1->id,
            ]
        );

        // Student ของ School 1
        $student1 = Student::updateOrCreate(
            ['code' => 'BNK001-001'],
            [
                'name' => 'นักเรียน บางรัก 01',
                'code' => 'BNK001-001',
                'classroom_id' => $classroom1->id,
            ]
        );

        $student1User = User::updateOrCreate(
            ['email' => 'student.bnk@test.com'],
            [
                'name' => $student1->name,
                'password' => Hash::make('12345'),
                'role' => 'student',
                'student_code' => $student1->code,
                'school_id' => $school1->id,
            ]
        );
        $this->command->info('✓ Student (BNK): student.bnk@test.com / 12345');
        $this->command->info('  - สิทธิ์: ดูสื่อการเรียนของตนเอง');
        $this->command->info('');

        // Parent ของ Student 1
        $parent1 = User::updateOrCreate(
            ['email' => 'parent.bnk@test.com'],
            [
                'name' => 'ผู้ปกครอง บางรัก',
                'password' => Hash::make('12345'),
                'role' => 'parent',
                'school_id' => $school1->id,
            ]
        );

        ParentStudent::updateOrCreate(
            ['parent_id' => $parent1->id, 'student_id' => $student1->id],
            []
        );
        $this->command->info('✓ Parent (BNK): parent.bnk@test.com / 12345');
        $this->command->info('  - สิทธิ์: ดูสื่อการเรียนของบุตรหลาน');
        $this->command->info('');

        // =============================================
        // SCHOOL 2 - สาขาสระบุรี
        // =============================================
        $school2 = School::updateOrCreate(
            ['code' => 'SRB001'],
            [
                'name' => 'โรงเรียนสระบุรี',
                'slug' => 'saraburi',
                'code' => 'SRB001',
                'description' => 'โรงเรียนสาขาสระบุรี - ระดับมัธยมศึกษา',
            ]
        );

        // Manager (School Admin) ของ School 2
        User::updateOrCreate(
            ['email' => 'manager.srb@test.com'],
            [
                'name' => 'ผู้จัดการ สระบุรี',
                'password' => Hash::make('12345'),
                'role' => 'school_admin',
                'school_id' => $school2->id,
            ]
        );
        $this->command->info('✓ School Manager (SRB): manager.srb@test.com / 12345');
        $this->command->info('  - สิทธิ์: จัดการโรงเรียนสระบุรี');
        $this->command->info('');

        // Teacher ของ School 2
        $teacher2 = User::updateOrCreate(
            ['email' => 'teacher.srb@test.com'],
            [
                'name' => 'ครูผู้สอน สระบุรี',
                'password' => Hash::make('12345'),
                'role' => 'teacher',
                'school_id' => $school2->id,
            ]
        );
        $this->command->info('✓ Teacher (SRB): teacher.srb@test.com / 12345');
        $this->command->info('  - สิทธิ์: อัพโหลดสื่อ, จัดการนักเรียนในชั้นเรียนตนเอง');
        $this->command->info('');

        // Classroom สำหรับ School 2
        $classroom2 = Classroom::updateOrCreate(
            ['name' => 'ม.1/1 - สระบุรี'],
            [
                'name' => 'ม.1/1 - สระบุรี',
                'teacher_id' => $teacher2->id,
                'school_id' => $school2->id,
            ]
        );

        // Student ของ School 2
        $student2 = Student::updateOrCreate(
            ['code' => 'SRB001-001'],
            [
                'name' => 'นักเรียน สระบุรี 01',
                'code' => 'SRB001-001',
                'classroom_id' => $classroom2->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'student.srb@test.com'],
            [
                'name' => $student2->name,
                'password' => Hash::make('12345'),
                'role' => 'student',
                'student_code' => $student2->code,
                'school_id' => $school2->id,
            ]
        );
        $this->command->info('✓ Student (SRB): student.srb@test.com / 12345');
        $this->command->info('  - สิทธิ์: ดูสื่อการเรียนของตนเอง');
        $this->command->info('');

        // Parent ของ Student 2
        $parent2 = User::updateOrCreate(
            ['email' => 'parent.srb@test.com'],
            [
                'name' => 'ผู้ปกครอง สระบุรี',
                'password' => Hash::make('12345'),
                'role' => 'parent',
                'school_id' => $school2->id,
            ]
        );

        ParentStudent::updateOrCreate(
            ['parent_id' => $parent2->id, 'student_id' => $student2->id],
            []
        );
        $this->command->info('✓ Parent (SRB): parent.srb@test.com / 12345');
        $this->command->info('  - สิทธิ์: ดูสื่อการเรียนของบุตรหลาน');
        $this->command->info('');

        // =============================================
        // SCHOOL 3 - สาขานนทบุรี
        // =============================================
        $school3 = School::updateOrCreate(
            ['code' => 'NBR001'],
            [
                'name' => 'โรงเรียนนนทบุรี',
                'slug' => 'nonburi',
                'code' => 'NBR001',
                'description' => 'โรงเรียนสาขานนทบุรี - ระดับอนุบาล',
            ]
        );

        // Manager (School Admin) ของ School 3
        User::updateOrCreate(
            ['email' => 'manager.nbr@test.com'],
            [
                'name' => 'ผู้จัดการ นนทบุรี',
                'password' => Hash::make('12345'),
                'role' => 'school_admin',
                'school_id' => $school3->id,
            ]
        );
        $this->command->info('✓ School Manager (NBR): manager.nbr@test.com / 12345');
        $this->command->info('  - สิทธิ์: จัดการโรงเรียนนนทบุรี');
        $this->command->info('');

        // Teacher ของ School 3
        $teacher3 = User::updateOrCreate(
            ['email' => 'teacher.nbr@test.com'],
            [
                'name' => 'ครูผู้สอน นนทบุรี',
                'password' => Hash::make('12345'),
                'role' => 'teacher',
                'school_id' => $school3->id,
            ]
        );
        $this->command->info('✓ Teacher (NBR): teacher.nbr@test.com / 12345');
        $this->command->info('  - สิทธิ์: อัพโหลดสื่อ, จัดการนักเรียนในชั้นเรียนตนเอง');
        $this->command->info('');

        // Classroom สำหรับ School 3
        $classroom3 = Classroom::updateOrCreate(
            ['name' => 'อ.1 - นนทบุรี'],
            [
                'name' => 'อ.1 - นนทบุรี',
                'teacher_id' => $teacher3->id,
                'school_id' => $school3->id,
            ]
        );

        // Student ของ School 3
        $student3 = Student::updateOrCreate(
            ['code' => 'NBR001-001'],
            [
                'name' => 'นักเรียน นนทบุรี 01',
                'code' => 'NBR001-001',
                'classroom_id' => $classroom3->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'student.nbr@test.com'],
            [
                'name' => $student3->name,
                'password' => Hash::make('12345'),
                'role' => 'student',
                'student_code' => $student3->code,
                'school_id' => $school3->id,
            ]
        );
        $this->command->info('✓ Student (NBR): student.nbr@test.com / 12345');
        $this->command->info('  - สิทธิ์: ดูสื่อการเรียนของตนเอง');
        $this->command->info('');

        // Parent ของ Student 3
        $parent3 = User::updateOrCreate(
            ['email' => 'parent.nbr@test.com'],
            [
                'name' => 'ผู้ปกครอง นนทบุรี',
                'password' => Hash::make('12345'),
                'role' => 'parent',
                'school_id' => $school3->id,
            ]
        );

        ParentStudent::updateOrCreate(
            ['parent_id' => $parent3->id, 'student_id' => $student3->id],
            []
        );
        $this->command->info('✓ Parent (NBR): parent.nbr@test.com / 12345');
        $this->command->info('  - สิทธิ์: ดูสื่อการเรียนของบุตรหลาน');
        $this->command->info('');

        // =============================================
        // สรุปบัญชีทั้งหมด
        // =============================================
        $this->command->info('===========================================');
        $this->command->info('สรุปบัญชีทดสอบทั้งหมด (Password: 12345)');
        $this->command->info('===========================================');
        $this->command->info('');
        $this->command->info('👑 SUPER ADMIN:');
        $this->command->info('   superadmin@test.com');
        $this->command->info('');
        $this->command->info('🏫 SCHOOL 1 - บางรัก:');
        $this->command->info('   Manager:  manager.bnk@test.com');
        $this->command->info('   Teacher:  teacher.bnk@test.com');
        $this->command->info('   Student:  student.bnk@test.com');
        $this->command->info('   Parent:   parent.bnk@test.com');
        $this->command->info('');
        $this->command->info('🏫 SCHOOL 2 - สระบุรี:');
        $this->command->info('   Manager:  manager.srb@test.com');
        $this->command->info('   Teacher:  teacher.srb@test.com');
        $this->command->info('   Student:  student.srb@test.com');
        $this->command->info('   Parent:   parent.srb@test.com');
        $this->command->info('');
        $this->command->info('🏫 SCHOOL 3 - นนทบุรี:');
        $this->command->info('   Manager:  manager.nbr@test.com');
        $this->command->info('   Teacher:  teacher.nbr@test.com');
        $this->command->info('   Student:  student.nbr@test.com');
        $this->command->info('   Parent:   parent.nbr@test.com');
        $this->command->info('');
    }
}
