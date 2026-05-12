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
        // Use ASCII-safe characters only for R2/S3 compatibility
        // Replace non-ASCII with underscores, then clean up
        $clean = preg_replace('/[^a-zA-Z0-9]/', '_', $this->name);
        $clean = preg_replace('/_+/', '_', $clean);
        $clean = trim($clean, '_');
        $clean = substr($clean, 0, 30) ?: 'Class' . $this->id;
        return sprintf('CLS_%d_%s', $this->id, strtolower($clean));
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
