<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'classroom_id',
        'type',
        'original_name',
        'stored_name',
        'path',
        'thumbnail_path',
        'mime_type',
        'size',
        'uploaded_by',
        'uploaded_date',
    ];

    protected $casts = [
        'uploaded_date' => 'date',
        'size' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return '/uploads/' . $this->path;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        return '/uploads/' . $this->thumbnail_path;
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg']);
    }

    public function getIsVideoAttribute(): bool
    {
        return in_array($this->mime_type, ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm', 'video/mpeg']);
    }

    public static function generatePath(Classroom $classroom, Student $student, string $date = null): string
    {
        $date = $date ?? now();
        $dateStr = $date->format('dmy');
        
        $classroomName = preg_replace('/[^a-zA-Z0-9ก-๙]/', '', $classroom->name);
        
        return sprintf(
            '%s/%s/%s',
            $classroomName . $dateStr,
            $student->code,
            ''
        );
    }
}
