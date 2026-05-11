<?php

namespace App\Models;

use App\Services\R2FolderService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'is_active',
        'domain',
        'database_name',
        'r2_bucket',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getDatabaseName(): string
    {
        return $this->database_name ?? "school_{$this->id}";
    }

    public function getR2Bucket(): string
    {
        return $this->r2_bucket ?? "school{$this->id}-{$this->slug}";
    }

    public function getSlugAttribute(?string $value): ?string
    {
        return $value;
    }

    public function setSlugAttribute(string $value): void
    {
        $clean = preg_replace('/[^a-zA-Z0-9ก-๙]/', '', $value);
        $this->attributes['slug'] = strtolower($clean);
    }

    public static function generateSlug(string $name): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9ก-๙]/', '', $name);
        return strtolower($clean);
    }

    protected static function booted(): void
    {
        static::creating(function (School $school) {
            if (empty($school->slug)) {
                $school->slug = School::generateSlug($school->name);
            }
        });

        static::created(function (School $school) {
            if (app()->bound(R2FolderService::class)) {
                app(R2FolderService::class)->createSchoolFolder($school);
            }
        });
    }
}
