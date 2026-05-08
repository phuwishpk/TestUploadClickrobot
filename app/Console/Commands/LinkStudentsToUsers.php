<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\User;

class LinkStudentsToUsers extends Command
{
    protected $signature = 'students:link';
    protected $description = 'Link student records to user accounts';

    public function handle(): int
    {
        $students = Student::all();
        $users = User::where('role', 'student')->get();

        $linked = 0;
        foreach ($students as $student) {
            $user = $users->firstWhere('student_code', $student->code);
            if ($user) {
                $student->update(['user_id' => $user->id]);
                $this->info("Linked: {$student->name} ({$student->code}) => {$user->email}");
                $linked++;
            } else {
                $this->warn("No user for: {$student->name} ({$student->code})");
            }
        }

        $this->info("Done! Linked {$linked} students.");
        return 0;
    }
}
