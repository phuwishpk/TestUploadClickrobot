<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    public static function boot()
    {
        parent::boot();

        static::created(function ($student) {
            if ($student->code === 'temp' || empty($student->code)) {
                $student->updateQuietly(['code' => $student->id . '_' . $student->name]);
            }
        });
    }
}
