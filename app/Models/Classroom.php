<?php

namespace App\Models;

use App\Services\R2FolderService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'teacher_id',
        'school_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class)->withTimestamps();
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }

    public function getFolderSlugAttribute(): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9ก-๙]/', '', $this->name);
        $classSlug = sprintf('CLS_%d_%s', $this->id, $clean);

        if ($this->school) {
            $schoolSlug = preg_replace('/[^a-zA-Z0-9ก-๙]/', '', $this->school->name);
            return sprintf('%s/%s', $schoolSlug, $classSlug);
        }

        return $classSlug;
    }

    protected static function booted(): void
    {
        static::created(function (Classroom $classroom) {
            if (app()->bound(R2FolderService::class)) {
                app(R2FolderService::class)->createClassroomFolder($classroom);
            }
        });

        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('name');
        });
    }
}
