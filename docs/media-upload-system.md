# 📤 Media Upload System Documentation

## ภาพรวมระบบ (System Overview)

ระบบอัปโหลดไฟล์ Media สำหรับโรงเรียน รองรับ:
- **รูปภาพ**: JPG, PNG, GIF, WebP (บีบอัดเป็น WebP/JPEG)
- **วิดีโอ**: MP4, MOV, AVI, WebM (บีบอัดด้วย FFmpeg)
- **จัดเก็บ**: Local Storage + Cloudflare R2 (S3-compatible)
- **Progress Bar**: แสดงสถานะการอัปโหลดแบบ Real-time

---

## 🏗️ โครงสร้างระบบ (Architecture)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           CLIENT (Browser)                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────────────┐  │
│  │  Select Box   │  │  Drop Zone   │  │      Progress Bar           │  │
│  │  - Classroom  │  │  - Drag/Drop │  │  ┌────────────────────────┐  │  │
│  │  - Students   │  │  - File Pick │  │  │ ████████████░░░ 80%   │  │  │
│  │  - Upload Date│  │  - Preview   │  │  │ กำลังประมวลผล...       │  │  │
│  └──────────────┘  └──────────────┘  │  │ [✅] file1.webp        │  │  │
│                                       │  │ [⚙️] file2.jpg         │  │  │
│                                       │  │ [⏳] file3.mp4         │  │  │
│                                       │  └────────────────────────┘  │  │
│                                       └──────────────────────────────┘  │
└────────────────────────────────────────────┬────────────────────────────┘
                                             │ HTTP POST (XHR)
                                             │ Multipart Form Data
                                             ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        LARAVEL BACKEND                                   │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                    Teacher/MediaController                        │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐ │   │
│  │  │   validate   │─▶│   loop      │─▶│   MediaCompressor       │ │   │
│  │  │   request    │  │   files ×   │  │   .compress()           │ │   │
│  │  │              │  │   students  │  │                         │ │   │
│  │  └─────────────┘  └─────────────┘  └───────────┬─────────────┘ │   │
│  │                                                  │               │   │
│  │  ┌──────────────────────────────────────────────┘               │   │
│  │  │                                                              │   │
│  │  │  ┌───────────────────────────────────────────────────────┐   │   │
│  │  │  │              MediaCompressor Service                   │   │   │
│  │  │  │  ┌─────────────┐  ┌─────────────┐  ┌──────────────┐  │   │   │
│  │  │  │  │ compressImage│  │compressVideo│  │ uploadToR2  │  │   │   │
│  │  │  │  │  (GD/WebP)   │  │  (FFmpeg)   │  │ (S3Client)  │  │   │   │
│  │  │  │  └─────────────┘  └─────────────┘  └──────────────┘  │   │   │
│  │  │  └───────────────────────────────────────────────────────┘   │   │
│  │  │                            │                                  │   │
│  │  │                            ▼                                  │   │
│  │  │                    ┌─────────────┐                           │   │
│  │  │                    │ Media Model │                           │   │
│  │  │                    │   Create    │                           │   │
│  │  │                    └─────────────┘                           │   │
│  │  └───────────────────────────────────────────────────────────────┘   │
│  └─────────────────────────────────────────────────────────────────┘   │
└────────────────────────────────────────────┬────────────────────────────┘
                                             │
                         ┌───────────────────┼───────────────────┐
                         ▼                   ▼                   ▼
              ┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐
              │   Local Storage  │ │  Cloudflare R2   │ │    Database      │
              │  storage/app/   │ │   (S3-compatible)│ │     (MySQL)      │
              │    uploads/      │ │                  │ │                  │
              │                  │ │  ┌────────────┐  │ │  ┌────────────┐  │
              │  PrimarySchool/  │ │  │  Bucket    │  │ │  │   media    │  │
              │  ├── 010126/     │ │  │  uploa...  │  │ │  │  students  │  │
              │  │   ├── ST001/  │ │  └────────────┘  │ │  │ classrooms │  │
              │  │   │   └── *.webp  │                  │ │  └────────────┘  │
              │  │   └── ST002/  │ │                  │ │                  │
              │  └── 010226/     │ │                  │ │                  │
              └──────────────────┘ └──────────────────┘ └──────────────────┘
```

---

## 📁 โครงสร้างไฟล์ (File Structure)

```
project/
├── app/
│   ├── Http/Controllers/
│   │   └── Teacher/
│   │       └── MediaController.php      # Handle upload requests
│   │
│   ├── Models/
│   │   ├── Media.php                   # Media model
│   │   ├── Classroom.php               # Classroom model
│   │   ├── Student.php                 # Student model
│   │   └── School.php                  # School model
│   │
│   └── Services/
│       ├── MediaCompressor.php         # Core compression logic
│       └── R2FolderService.php         # R2 folder management
│
├── config/
│   └── filesystems.php                 # Storage & R2 config
│
├── resources/views/
│   └── teacher/
│       └── upload.blade.php            # Upload UI with progress
│
├── routes/
│   └── web.php                        # Upload routes
│
└── database/
    └── migrations/                     # Database schema
```

---

## 🔄 การไหลของข้อมูล (Data Flow)

### 1. การเลือกไฟล์ (File Selection)

```
User Action                    JavaScript Event
─────────────                  ─────────────────
1. เลือกไฟล์จากคอมพิวเตอร์
   └─▶ onchange="handleFileSelect()"
       └─▶ อ่านไฟล์ที่เลือก (File API)
           └─▶ สร้าง Object URL (URL.createObjectURL)
               └─▶ แสดง Preview รูป/วิดีโอ
```

**Code ตัวอย่าง:**

```javascript
function handleFileSelect(input) {
    const container = document.getElementById('file_preview');
    container.innerHTML = '';

    Array.from(input.files).forEach(file => {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 rounded p-2 text-sm';

        if (file.type.startsWith('image/')) {
            const url = URL.createObjectURL(file);
            div.innerHTML = `
                <img src="${url}" class="w-full h-24 object-cover rounded mb-1">
                <span class="truncate">${file.name}</span>
                <span class="text-xs text-gray-400 block">${(file.size/1024).toFixed(1)} KB</span>
            `;
        } else {
            div.innerHTML = `
                <div class="w-full h-24 bg-red-50 rounded flex items-center justify-center mb-1">
                    <span class="text-2xl">🎬</span>
                </div>
                <span class="truncate">${file.name}</span>
                <span class="text-xs text-gray-400 block">${(file.size/1024).toFixed(1)} KB</span>
            `;
        }

        container.appendChild(div);
    });
}
```

### 2. การส่งข้อมูล (Data Submission)

```
Client (Browser)                Server (Laravel)
─────────────────              ────────────────
1. สร้าง FormData
   ┌─────────────────────────────────────┐
   │ FormData                            │
   │ ├── _csrf: token                   │
   │ ├── classroom_id: 1                │
   │ ├── student_ids[]: [1, 2, 3]      │
   │ ├── upload_date: 2026-01-10        │
   │ └── files[]: [file1, file2, ...]  │
   └─────────────────────────────────────┘

2. ส่งด้วย XHR (XMLHttpRequest)
   ┌─────────────────────────────────────┐
   │ XHR Request                         │
   │ Method: POST                        │
   │ URL: /teacher/upload                │
   │ Headers:                            │
   │   X-CSRF-TOKEN: xxx                │
   │   X-Requested-With: XMLHttpRequest │
   │ Body: multipart/form-data           │
   └─────────────────────────────────────┘
```

### 3. การประมวลผล (Processing)

```
Server Processing Flow:
═══════════════════════

1. MediaCompressor.compress(file, student, classroom, uploadDate)
   │
   ├─▶ กำหนด Type (image/video)
   │
   ├─▶ สร้าง Path: {classroom}/{date}/{student_code}/filename
   │   Example: PrimarySchool/010126/ST001/010126_143025_a1b2.webp
   │
   ├─▶ เรียก compressImage() หรือ compressVideo()
   │
   ├─▶ บันทึกไฟล์ลง Local Storage
   │
   ├─▶ อัปโหลดไฟล์ไป Cloudflare R2 (ถ้ามี config)
   │
   └─▶ สร้าง Video Thumbnail (ถ้าเป็นวิดีโอ)

2. Media::create()
   │
   └─▶ บันทึกข้อมูลลง Database
```

---

## 🖼️ การบีบอัดรูปภาพ (Image Compression)

### Flow Chart

```
┌─────────────────────────────┐
│   Uploaded Image File       │
└─────────────┬───────────────┘
              │
              ▼
┌─────────────────────────────┐
│   Read with GD/Imagick      │
└─────────────┬───────────────┘
              │
              ▼
┌─────────────────────────────┐
│   Check Dimensions          │
│   (max: 2048x2048)         │
└─────────────┬───────────────┘
              │
       ┌──────┴──────┐
       │ > 2048?     │
       └──────┬──────┘
         YES  │  NO
       ┌──────┴──────┐
       ▼             ▼
┌──────────────┐ ┌─────────────────────┐
│ Resize Image │ │ Continue (no resize)│
│ maintain     │ │                     │
│ aspect ratio │ └─────────────────────┘
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────────────────────────┐
│                   Convert to WebP (quality 80%)              │
└─────────────────────────────────┬───────────────────────────┘
                                  │
                          ┌───────┴───────┐
                          │ Smaller than  │
                          │ Original?     │
                          └───────┬───────┘
                           YES   │   NO
                         ┌───────┴───────┐
                         ▼               ▼
              ┌────────────────┐ ┌─────────────────┐
              │ Use WebP        │ │ Try JPEG (75%)  │
              │ Save + Log      │ │ Save + Log      │
              └────────────────┘ └─────────────────┘
```

### Code Implementation

```php
protected function compressImage(UploadedFile $file, string $absolutePath, string $relativePath): array
{
    // 1. อ่านไฟล์ต้นฉบับ
    $tempFile = $file->getPathname();
    $image = $this->imageManager->read($tempFile);

    // 2. Resize ถ้าขนาดเกิน
    $maxWidth = 2048;
    $maxHeight = 2048;

    if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
        $image->resize($maxWidth, $maxHeight, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }

    // 3. แปลงเป็น WebP (quality 80%)
    $webpPath = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $absolutePath);
    $originalSize = filesize($tempFile);

    $image->toWebp(80)->save($webpPath);
    $compressedSize = filesize($webpPath);

    // 4. ถ้า WebP ไม่เล็กกว่าเดิม ลอง JPEG
    if ($compressedSize >= $originalSize) {
        @unlink($webpPath);

        $jpgPath = preg_replace('/\.(png|gif)$/i', '.jpg', $absolutePath);
        $image->toJpeg(75)->save($jpgPath);
        $compressedSize = filesize($jpgPath);

        // อัปโหลดไป R2
        if ($this->s3Client) {
            $this->uploadToR2($jpgPath, $jpgRelativePath);
        }

        return [
            'type' => 'image',
            'filename' => basename($jpgPath),
            'path' => $relativePath,
            'mime_type' => 'image/jpeg',
            'size' => $compressedSize,
            'original_size' => $originalSize,
            'compression_saved_bytes' => $originalSize - $compressedSize,
            'compression_reduction_percent' => round((1 - $compressedSize / $originalSize) * 100, 1),
        ];
    }

    // 5. อัปโหลดไป R2
    if ($this->s3Client) {
        $this->uploadToR2($webpPath, $webpRelativePath);
    }

    return [
        'type' => 'image',
        'filename' => basename($webpPath),
        'path' => $webpRelativePath,
        'mime_type' => 'image/webp',
        'size' => $compressedSize,
        'original_size' => $originalSize,
        'compression_saved_bytes' => $originalSize - $compressedSize,
        'compression_reduction_percent' => round((1 - $compressedSize / $originalSize) * 100, 1),
    ];
}
```

---

## 🎬 การบีบอัดวิดีโอ (Video Compression)

### Flow Chart

```
┌─────────────────────────────┐
│   Uploaded Video File       │
└─────────────┬───────────────┘
              │
              ▼
┌─────────────────────────────┐
│   Check FFmpeg Available?   │
└─────────────┬───────────────┘
              │
       ┌──────┴──────┐
       │             │
      YES            NO
       │             │
       ▼             ▼
┌──────────────┐ ┌──────────────┐
│ Probe Video │ │ Copy File    │
│ (get info)  │ │ (no compress)│
└──────┬───────┘ └──────┬───────┘
       │                │
       ▼                ▼
┌─────────────────────────────┐
│   Check if Compression      │
│   is Needed                 │
│   - Size < 10MB? Skip      │
│   - Already small? Skip    │
│   - Resolution <= 1280?    │
│     Bitrate <= 1800k? Skip │
└─────────────┬───────────────┘
              │
       ┌──────┴──────┐
       │ Need to     │
       │ Compress?   │
       └──────┬──────┘
         YES  │  NO
       ┌──────┴──────┐
       ▼             ▼
┌─────────────────┐ ┌─────────────────┐
│ 2-Pass FFmpeg   │ │ Copy Original   │
│ Encoding        │ │                 │
│ - Scale to 1280 │ │                 │
│ - Target 1500k  │ │                 │
│ - AAC Audio 48k │ │                 │
└────────┬────────┘ └─────────────────┘
         │
         ▼
┌─────────────────────────────┐
│   Compressed < Original?    │
└─────────────┬───────────────┘
              │
       ┌──────┴──────┐
       │             │
      YES            NO
       │             │
       ▼             ▼
┌──────────────┐ ┌──────────────┐
│ Use Compressed│ │ Use Original│
└──────┬───────┘ └──────┬───────┘
       │                │
       └────────┬───────┘
                │
                ▼
┌─────────────────────────────┐
│   Extract Thumbnail         │
│   (FFmpeg -ss 10)          │
│   Scale: 480px width       │
└─────────────┬───────────────┘
              │
              ▼
┌─────────────────────────────┐
│   Upload to R2             │
│   (if configured)          │
└─────────────┬───────────────┘
              │
              ▼
┌─────────────────────────────┐
│   Return Result Array       │
│   {type, path, size, ...}  │
└─────────────────────────────┘
```

### FFmpeg Commands

```bash
# Pass 1: Analyze video
ffmpeg -i input.mp4 -vf "scale=1280:-2" -c:v libx264 -preset fast -b:v 1500k -pass 1 -an -f null -

# Pass 2: Encode video
ffmpeg -i input.mp4 -vf "scale=1280:-2" -c:v libx264 -preset fast -b:v 1500k -pass 2 -c:a aac -b:a 48k -movflags +faststart -y output.mp4

# Extract Thumbnail
ffmpeg -ss 10 -i input.mp4 -vframes 1 -q:v 1 -vf "scale=480:-2" -update 1 thumbnail.jpg
```

### Code Implementation

```php
protected function compressVideo(UploadedFile $file, string $absolutePath, string $relativePath): array
{
    $tempInput = $file->getPathname();
    $originalSize = filesize($tempInput);
    $compressionSavedBytes = 0;
    $compressionReductionPercent = 0;

    $ffmpegPath = '/usr/bin/ffmpeg';

    if (!file_exists($ffmpegPath)) {
        // ไม่มี FFmpeg - คัดลอกไฟล์โดยไม่บีบอัด
        copy($tempInput, $absolutePath);
    } else {
        // ข้ามถ้าไฟล์เล็กอยู่แล้ว (< 10MB)
        if ($originalSize < 10 * 1024 * 1024) {
            copy($tempInput, $absolutePath);
        } else {
            // 2-pass compression
            $tempOutput = '/tmp/' . uniqid() . '_compressed.mp4';

            // Get video info
            $probeCmd = sprintf('%s -i %s 2>&1', $ffmpegPath, escapeshellarg($tempInput));
            exec($probeCmd, $probeOutput, $probeCode);

            // Check resolution
            preg_match('/([0-9]{2,4})x([0-9]{2,4})/', implode("\n", $probeOutput), $matches);
            $originalWidth = (int)($matches[1] ?? 1920);

            // 2-pass encoding
            $pass1 = sprintf(
                '%s -i %s -c:v libx264 -preset fast -b:v 1500k -pass 1 -an -f null - 2>&1',
                $ffmpegPath, escapeshellarg($tempInput)
            );
            $pass2 = sprintf(
                '%s -i %s -c:v libx264 -preset fast -b:v 1500k -pass 2 -c:a aac -b:a 48k -movflags +faststart -y %s 2>&1',
                $ffmpegPath, escapeshellarg($tempInput), escapeshellarg($tempOutput)
            );

            exec($pass1);
            exec($pass2);

            // ใช้ไฟล์ที่บีบอัดถ้าเล็กกว่าเดิม
            if (file_exists($tempOutput) && filesize($tempOutput) < $originalSize) {
                $compressionSavedBytes = $originalSize - filesize($tempOutput);
                $compressionReductionPercent = round((1 - filesize($tempOutput) / $originalSize) * 100, 1);
                copy($tempOutput, $absolutePath);
            } else {
                copy($tempInput, $absolutePath);
            }
        }
    }

    // Extract thumbnail
    $thumbnailResult = $this->extractVideoThumbnail($absolutePath, $relativePath);

    // Upload to R2
    if ($this->s3Client) {
        $this->uploadToR2($absolutePath, $relativePath);
        if ($thumbnailResult) {
            $this->uploadToR2($thumbnailResult['absolute_path'], $thumbnailResult['relative_path']);
        }
    }

    return [
        'type' => 'video',
        'filename' => basename($relativePath),
        'path' => $relativePath,
        'mime_type' => 'video/mp4',
        'thumbnail_path' => $thumbnailResult['relative_path'] ?? null,
        'size' => filesize($absolutePath),
        'original_size' => $originalSize,
        'compression_saved_bytes' => $compressionSavedBytes,
        'compression_reduction_percent' => $compressionReductionPercent,
    ];
}
```

---

## ☁️ Cloudflare R2 Integration

### Configuration

```php
// config/filesystems.php

'r2' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'auto'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),                    // Public URL ของ R2
    'endpoint' => env('AWS_ENDPOINT'),          // R2 endpoint
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'visibility' => 'public',
],
```

### Environment Variables

```env
# .env

# Cloudflare R2
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-account.r2.dev
AWS_ENDPOINT=https://abc123def456.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Upload Flow to R2

```
┌─────────────────────────────────────────────────────────────────┐
│                    R2 Upload Flow                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐      ┌──────────────┐      ┌──────────────┐   │
│  │ Local File   │ ───▶ │ S3Client     │ ───▶ │ R2 Bucket    │   │
│  │ (temp)       │      │ .putObject() │      │ (Cloudflare) │   │
│  └──────────────┘      └──────────────┘      └──────────────┘   │
│                              │                                    │
│                              ▼                                    │
│                      ┌──────────────┐                            │
│                      │ ACL: public  │                            │
│                      │ ContentType  │                            │
│                      └──────────────┘                            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Code Implementation

```php
protected function uploadToR2(string $localPath, string $r2Path): bool
{
    if (!$this->s3Client) {
        return false;
    }

    try {
        $bucket = config('filesystems.disks.r2.bucket');

        $this->s3Client->putObject([
            'Bucket' => $bucket,
            'Key' => $r2Path,
            'SourceFile' => $localPath,
            'ContentType' => $this->getMimeType($r2Path),
            'ACL' => 'public-read',
        ]);

        \Log::info('Uploaded to R2', ['path' => $r2Path]);
        return true;

    } catch (\Exception $e) {
        \Log::error('R2 upload failed', [
            'path' => $r2Path,
            'error' => $e->getMessage(),
        ]);
        return false;
    }
}
```

### R2 vs Local Storage

| Aspect | Local Storage | Cloudflare R2 |
|--------|---------------|---------------|
| **Storage Location** | Server disk | Cloud (CDN) |
| **Access Speed** | Fast (local) | Fast (global CDN) |
| **Cost** | Server cost | R2 pricing ($0/100GB egress) |
| **Scalability** | Limited by disk | Unlimited |
| **Backup** | Manual | Automatic |
| **Recommended For** | Small projects | Production/High traffic |

### R2 CORS Policy

ต้องตั้งค่า CORS ใน Cloudflare Dashboard:

```json
[
    {
        "AllowedOrigins": ["https://your-domain.com"],
        "AllowedMethods": ["GET", "HEAD"],
        "AllowedHeaders": ["*"],
        "MaxAgeSeconds": 3600
    }
]
```

### R2 Custom Domain (Optional)

ตั้งค่า Custom Domain ใน Cloudflare Dashboard เพื่อใช้ URL ของตัวเอง:

```
# แทน
https://abc123.r2.dev/uploads/file.webp

# ใช้
https://cdn.your-domain.com/uploads/file.webp
```

---

## 📊 Progress Bar Implementation

### Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    Progress Bar Flow                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  User clicks "อัปโหลด"                                           │
│          │                                                      │
│          ▼                                                      │
│  ┌─────────────────┐                                            │
│  │ XHR Upload     │ ◀─── Real upload progress event             │
│  │ (XMLHttpRequest)│                                           │
│  └────────┬────────┘                                            │
│           │                                                     │
│  ┌────────┴────────┐                                           │
│  │ upload.progress  │ ──▶ Update uploadTargetPercent           │
│  │ event           │     (max 80%)                             │
│  └────────┬────────┘                                            │
│           │                                                     │
│           ▼                                                     │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │               JavaScript Animation Loop                  │   │
│  │                                                          │   │
│  │  ┌────────────────┐    ┌────────────────┐               │   │
│  │  │ Upload Display │ ──▶ │ Processing     │ ──▶ Complete │   │
│  │  │ (10% → 80%)   │    │ (80% → 99%)    │     (100%)   │   │
│  │  │                │    │                │              │   │
│  │  │ Progress:      │    │ Progress:      │              │   │
│  │  │ ████████░░ 60%│    │ ██████████░ 95%│              │   │
│  │  │ กำลังอัปโหลด   │    │ กำลังประมวลผล  │              │   │
│  │  └────────────────┘    └────────────────┘              │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Progress States

```
┌────────────────────────────────────────────────────────────────────┐
│                      Progress Timeline                              │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  0% ─────── 10% ────────────── 80% ─────────── 99% ─────── 100%    │
│   │         │                    │              │           │      │
│   │         │                    │              │           │      │
│   │         ▼                    ▼              ▼           ▼      │
│   │    ┌─────────┐         ┌──────────┐   ┌──────────┐ ┌──────┐   │
│   │    │ Preparing│         │ Uploading │   │Processing│ │ Done │   │
│   │    │         │         │          │   │          │ │      │   │
│   │    │ Initial │         │ Real XHR │   │ Server   │ │  ✅  │   │
│   │    │ State   │         │ Progress │   │ Side     │ │      │   │
│   │    └─────────┘         └──────────┘   └──────────┘ └──────┘   │
│   │                                                               │
│   │                                                               │
│   └─── Fake progress (CSS animation) ────────────────────────────►│
│                                                                     │
└────────────────────────────────────────────────────────────────────┘
```

### JavaScript Implementation

```javascript
// ===============================
// 1. Initialize Variables
// ===============================
const studentIds = Array.from(studentCheckboxes).map(cb => cb.value);
const totalOperations = files.length * studentIds.length;
let completedOperations = 0;
let uploadTargetPercent = 10;
let uploadComplete = false;
let currentPercent = 10;

// ===============================
// 2. XHR Upload with Progress
// ===============================
const uploadXhr = new XMLHttpRequest();

// Track real upload progress
uploadXhr.upload.addEventListener('progress', function(event) {
    if (event.lengthComputable) {
        // Real progress: 10% → 80%
        uploadTargetPercent = 10 + (event.loaded / event.total) * 70;
    } else {
        // Fallback: increment slowly
        uploadTargetPercent = Math.min(uploadTargetPercent + 1, 80);
    }
});

uploadXhr.upload.addEventListener('load', function() {
    uploadComplete = true;
    uploadTargetPercent = 80;
});

// ===============================
// 3. Display Animation Loop
// ===============================
function startUploadDisplay() {
    uploadDisplayInterval = setInterval(() => {
        const cappedTarget = Math.min(uploadTargetPercent, 80);

        if (currentPercent < cappedTarget) {
            // Increment towards target
            currentPercent = Math.min(currentPercent + 1, cappedTarget);
            updateProgressUI(currentPercent, `กำลังอัปโหลด ${Math.round(currentPercent)}%`);
            return;
        }

        if (uploadComplete && currentPercent >= 80) {
            // Switch to processing animation
            stopUploadDisplay();
            startProcessingCreep();
        }
    }, 1000); // Update every 1 second
}

// ===============================
// 4. Processing Animation
// ===============================
function startProcessingCreep() {
    statusText.textContent = 'กำลังประมวลผล...';

    // Estimate processing time
    const processingEstimateMs = calculateProcessingEstimateMs(files, studentIds.length);
    const processingStartTime = Date.now();

    processingInterval = setInterval(() => {
        const elapsed = Date.now() - processingStartTime;
        const estimatedProgress = Math.min(elapsed / processingEstimateMs, 1);

        let targetPercent;
        if (estimatedProgress <= 0.6) {
            // First 60%: 80% → 95%
            targetPercent = 80 + (estimatedProgress / 0.6) * 15;
        } else {
            // Last 40%: 95% → 99%
            targetPercent = 95 + ((estimatedProgress - 0.6) / 0.4) * 4;
        }

        if (targetPercent >= 99) {
            updateProgressUI(99, 'กำลังประมวลผล... รอเซิร์ฟเวอร์ทำงานให้เสร็จ');
            return;
        }

        updateProgressUI(targetPercent, `กำลังประมวลผล ${Math.round(targetPercent)}%`);
    }, 900);
}

// ===============================
// 5. Update UI
// ===============================
function updateProgressUI(percent, text) {
    progressBar.style.width = percent + '%';
    percentage.textContent = Math.round(percent) + '%';
    if (text) {
        statusText.textContent = text;
    }
}

// ===============================
// 6. Calculate Processing Estimate
// ===============================
function calculateProcessingEstimateMs(selectedFiles, selectedStudentCount) {
    const bytesPerMb = 1024 * 1024;
    let estimate = 0;

    Array.from(selectedFiles).forEach(file => {
        const fileMb = Math.max(file.size / bytesPerMb, 0.1);
        const isVideo = file.type.startsWith('video/');

        if (isVideo) {
            // Videos take longer: ~8s base + ~1.1s per MB per student
            estimate += (8000 + fileMb * 1100) * selectedStudentCount;
        } else {
            // Images: ~0.8s base + ~0.6s per MB per student
            estimate += (800 + fileMb * 600) * selectedStudentCount;
        }
    });

    // Clamp between 6s and 180s
    return Math.min(180000, Math.max(6000, estimate));
}
```

### Individual File Status

```javascript
// สร้าง status item สำหรับแต่ละไฟล์
studentIds.forEach((studentId) => {
    Array.from(files).forEach((file, fileIdx) => {
        const itemId = `${studentId}_${fileIdx}`;
        const statusItem = document.createElement('div');
        statusItem.id = `status_${itemId}`;
        statusItem.innerHTML = `
            <span class="file-icon w-6 h-6 flex items-center justify-center text-gray-400">⏳</span>
            <span class="file-name truncate flex-1 text-xs">${file.name}</span>
            <span class="file-size text-xs text-gray-400">${(file.size/1024).toFixed(1)} KB</span>
            <span class="file-status text-xs px-2 py-1 rounded bg-gray-50">รอ...</span>
        `;
        fileStatusContainer.appendChild(statusItem);
    });
});

// อัปเดตสถานะเมื่อเสร็จ
function updateStatusItem(itemId, status) {
    const statusEl = document.getElementById(`status_${itemId}`);
    if (!statusEl) return;

    const itemIcon = statusEl.querySelector('.file-icon');
    const itemStatus = statusEl.querySelector('.file-status');

    if (status === 'complete') {
        itemIcon.textContent = '✅';
        itemIcon.className = 'file-icon w-6 h-6 flex items-center justify-center text-green-500';
        itemStatus.textContent = 'เสร็จ';
        itemStatus.className = 'file-status text-xs px-2 py-1 rounded bg-green-100 text-green-700';
    } else if (status === 'processing') {
        itemIcon.textContent = '⚙️';
        itemIcon.className = 'file-icon w-6 h-6 flex items-center justify-center text-blue-500 animate-spin';
        itemStatus.textContent = 'กำลังประมวลผล';
        itemStatus.className = 'file-status text-xs px-2 py-1 rounded bg-blue-100 text-blue-700';
    } else if (status === 'error') {
        itemIcon.textContent = '❌';
        itemIcon.className = 'file-icon w-6 h-6 flex items-center justify-center text-red-500';
        itemStatus.textContent = 'ผิดพลาด';
        itemStatus.className = 'file-status text-xs px-2 py-1 rounded bg-red-100 text-red-700';
    }
}
```

### Progress Bar UI (Tailwind CSS)

```html
<!-- Progress Section -->
<div id="upload_progress_section" class="hidden mb-6">
    <div class="bg-gray-50 rounded-lg p-4">
        <!-- Header -->
        <div class="flex justify-between items-center mb-2">
            <span id="upload_status_text" class="text-sm font-medium text-gray-700">
                กำลังอัปโหลด...
            </span>
            <span id="upload_percentage" class="text-sm font-bold text-indigo-600">
                0%
            </span>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div id="upload_progress_bar"
                 class="bg-indigo-600 h-3 rounded-full transition-all duration-300"
                 style="width: 0%">
            </div>
        </div>

        <!-- Individual File Status -->
        <div id="file_status_container" class="mt-3 space-y-2 max-h-80 overflow-y-auto pr-2">
            <!-- Dynamic content -->
        </div>
    </div>
</div>
```

---

## 🗄️ Database Schema

### Media Table

```sql
CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    classroom_id BIGINT UNSIGNED NOT NULL,
    type ENUM('image', 'video') NOT NULL,
    original_name VARCHAR(255) NOT NULL COMMENT 'ชื่อไฟล์เดิม',
    stored_name VARCHAR(255) NOT NULL COMMENT 'ชื่อไฟล์ที่เก็บ',
    path VARCHAR(500) NOT NULL COMMENT 'path บน storage',
    thumbnail_path VARCHAR(500) NULL COMMENT 'path ของ thumbnail (video)',
    mime_type VARCHAR(100) NOT NULL,
    size BIGINT NOT NULL COMMENT 'ขนาดหลังบีบอัด (bytes)',
    original_size BIGINT NULL COMMENT 'ขนาดต้นฉบับ (bytes)',
    compression_saved_bytes BIGINT NULL COMMENT 'จำนวน bytes ที่ประหยัดได้',
    compression_reduction_percent DECIMAL(5,2) NULL COMMENT 'เปอร์เซ็นต์ที่บีบอัดได้',
    uploaded_by BIGINT UNSIGNED NOT NULL COMMENT 'ผู้อัปโหลด',
    uploaded_date DATE NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_classroom (classroom_id),
    INDEX idx_student (student_id),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_uploaded_date (uploaded_date),

    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
```

### Supporting Tables

```sql
-- Schools
CREATE TABLE schools (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Classrooms
CREATE TABLE classrooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    folder_slug VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school (school_id)
);

-- Students
CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    classroom_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE COMMENT 'รหัสนักเรียน',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
    INDEX idx_classroom (classroom_id),
    UNIQUE KEY unique_student_code (code)
);

-- Users
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'school_admin', 'teacher', 'parent', 'student') NOT NULL,
    school_id BIGINT UNSIGNED NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL,
    INDEX idx_role (role),
    INDEX idx_school (school_id)
);
```

---

## 📝 การใช้งาน (Usage)

### 1. ติดตั้ง Dependencies

```bash
# PHP
composer install

# ต้องมี FFmpeg สำหรับบีบอัดวิดีโอ
sudo apt install ffmpeg

# GD หรือ Imagick สำหรับประมวลผลรูป
sudo apt install php-gd
# หรือ
sudo apt install php-imagick

# AWS SDK for R2
composer require aws/aws-sdk-php
```

### 2. ตั้งค่า Environment

```env
# .env
FILESYSTEM_DISK=local

# Cloudflare R2 (Optional)
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your-bucket
AWS_URL=https://your-domain.r2.dev
AWS_ENDPOINT=https://xxx.r2.cloudflarestorage.com
```

### 3. สร้าง Symlink

```bash
php artisan storage:link
```

### 4. ตั้งค่า Cloudflare R2

1. ไปที่ Cloudflare Dashboard
2. สร้าง R2 Bucket ใหม่
3. สร้าง API Token (ต้องมี Edit permission)
4. เพิ่ม Custom Domain (ถ้าต้องการ)

### 5. ทดสอบ

```bash
php artisan serve
# ไปที่ http://localhost:8000/teacher/upload
```

---

## 🔧 Troubleshooting

### ปัญหาที่พบบ่อย

| ปัญหา | สาเหตุ | วิธีแก้ |
|--------|--------|---------|
| อัปโหลดไฟล์ไม่ได้ | Storage path ไม่ถูกต้อง | ตรวจสอบ `storage/app/uploads` permission |
| WebP ไม่ทำงาน | GD ไม่รองรับ WebP | ติดตั้ง `php-gd` ที่มี WebP support |
| วิดีโอไม่บีบอัด | FFmpeg ไม่มี | ติดตั้ง `ffmpeg` |
| R2 อัปโหลดไม่ได้ | Credentials ผิด | ตรวจสอบ AWS_* env variables |
| Progress bar ไม่ขยับ | XHR progress ไม่ทำงาน | ใช้ browser ที่รองรับ XHR progress |

### ตรวจสอบ Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Docker logs
docker logs -f school-upload-app
```

### เพิ่มประสิทธิภาพ

```php
// เปลี่ยนเป็น Queue Job สำหรับไฟล์ใหญ่
// app/Jobs/CompressMedia.php

class CompressMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(MediaCompressor $compressor)
    {
        // Process file in background
        $result = $compressor->compress($this->file, $this->student, $this->classroom, $this->uploadDate);
        // Update media record...
    }
}
```

---

## 📈 Performance Tips

1. **Video Compression**: ควรใช้ Queue Jobs สำหรับไฟล์ใหญ่
2. **Parallel Processing**: ใช้ Supervisor หรือ Redis สำหรับ queue workers
3. **CDN**: ใช้ Cloudflare CDN สำหรับ R2 เพื่อความเร็ว
4. **Image Format**: WebP ให้ขนาดเล็กกว่า JPEG ~30%
5. **Lazy Loading**: โหลดรูปเมื่อ scroll ถึง

---

## 📋 API Summary

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/teacher/upload` | แสดงหน้าอัปโหลด |
| POST | `/teacher/upload` | อัปโหลดไฟล์ |
| DELETE | `/teacher/media/{id}` | ลบไฟล์ |

### Response Format

```json
{
    "success": true,
    "message": "อัปโหลดสำเร็จ 6 ไฟล์",
    "count": 6,
    "errors": [],
    "processed_files": 6,
    "total_files": 6
}
```

### Error Response

```json
{
    "success": false,
    "message": "อัปโหลดสำเร็จ 4 ไฟล์ มีข้อผิดพลาด 2 รายการ",
    "count": 4,
    "errors": [
        "ไฟล์ video1.mp4 สำหรับ สมชาย: ไฟล์ใหญ่เกิน",
        "ไฟล์ image2.png สำหรับ สมหญิง: รูปแบบไม่ถูกต้อง"
    ],
    "processed_files": 4,
    "total_files": 6
}
```

---

## 🔐 Security Considerations

1. **File Validation**: ตรวจสอบ MIME type ทั้ง client และ server
2. **File Size Limit**: จำกัดขนาดไฟล์ที่ 200MB
3. **Allowed Extensions**: อนุญาตเฉพาะ jpg, png, gif, webp, mp4, mov, avi, webm
4. **CSRF Protection**: ใช้ CSRF token ใน form
5. **Authorization**: ตรวจสอบสิทธิ์ก่อนอัปโหลด/ลบ

---

**Document Version:** 1.0
**Last Updated:** May 2026
