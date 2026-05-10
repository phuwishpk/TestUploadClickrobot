<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_code',
        'school_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isSchoolAdmin(): bool
    {
        return $this->role === 'school_admin';
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function parentStudents()
    {
        return $this->hasMany(ParentStudent::class, 'parent_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function getAccessibleStudentIds(): array
    {
        if ($this->isTeacher()) {
            return Student::whereIn('classroom_id', $this->classrooms()->pluck('id'))->pluck('id')->toArray();
        }

        if ($this->isParent()) {
            return $this->parentStudents()->pluck('student_id')->toArray();
        }

        if ($this->isStudent()) {
            $student = $this->student;
            return $student ? [$student->id] : [];
        }

        return [];
    }
}
