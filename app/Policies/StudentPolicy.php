<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function view(User $user, Student $student): bool
    {
        return $user->classrooms()
            ->whereHas('students', function ($q) use ($student) {
                $q->where('students.id', $student->id);
            })->exists();
    }

    public function update(User $user, Student $student): bool
    {
        return $user->classrooms()
            ->whereHas('students', function ($q) use ($student) {
                $q->where('students.id', $student->id);
            })->exists();
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->classrooms()
            ->whereHas('students', function ($q) use ($student) {
                $q->where('students.id', $student->id);
            })->exists();
    }
}
