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
        'original_size',
        'compression_saved_bytes',
        'compression_reduction_percent',
        'uploaded_by',
        'uploaded_date',
    ];

    protected $casts = [
        'uploaded_date' => 'date',
        'size' => 'integer',
        'original_size' => 'integer',
        'compression_saved_bytes' => 'integer',
        'compression_reduction_percent' => 'decimal:1',
    ];

    public function getFormattedSizeAttribute(): string
    {
        return $this->formatBytes($this->size);
    }

    public function getFormattedCompressionChangeAttribute(): ?string
    {
        if ($this->original_size === null || $this->compression_saved_bytes === null) {
            return null;
        }

        $changedBytes = abs($this->compression_saved_bytes);
        $percent = abs((float) $this->compression_reduction_percent);

        if ($this->compression_saved_bytes > 0) {
            return "ลดลง {$percent}% (" . $this->formatBytes($changedBytes) . ')';
        }

        if ($this->compression_saved_bytes < 0) {
            return "เพิ่มขึ้น {$percent}% (" . $this->formatBytes($changedBytes) . ')';
        }

        return 'ไม่เปลี่ยนแปลง';
    }

    protected function formatBytes(?int $bytes): string
    {
        if ($bytes === null) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = max($bytes, 0);
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return ($unitIndex === 0 ? number_format($size, 0) : number_format($size, 1)) . ' ' . $units[$unitIndex];
    }

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

    public static function generatePath(Classroom $classroom, Student $student, ?\DateTimeInterface $date = null): string
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
