<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'classroom_id',
        'user_id',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class)->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parentStudents()
    {
        return $this->hasMany(ParentStudent::class);
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_students', 'student_id', 'parent_id');
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public static function generateCode(int $classroomId = null): string
    {
        $query = self::query();
        
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }
        
        $lastStudent = $query->orderBy('id', 'desc')->first();
        
        if ($lastStudent) {
            preg_match('/\d+/', $lastStudent->code, $matches);
            $nextNumber = $matches ? ((int) $matches[0] + 1) : 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'นร' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->code)) {
                $student->code = self::generateCode($student->classroom_id);
            }
        });
    }
}
