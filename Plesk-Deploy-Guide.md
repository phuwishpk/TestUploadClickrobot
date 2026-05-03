# ระบบ Upload รูป/วีดีโอ สำหรับโรงเรียน

## ติดตั้งบน Plesk

### ข้อกำหนดเบื้องต้น (Server Requirements)

- PHP 8.2+ พร้อม extensions:
  - `pdo_mysql`
  - `fileinfo`
  - `mbstring`
  - `openssl`
  - `zip`
  - `gd`
  - `exif` (สำหรับรูปภาพ)
- MySQL 8.0+ หรือ MariaDB 10.3+
- FFmpeg (สำหรับบีบอัดวีดีโอ)
- Composer 2.x

### ขั้นตอนการติดตั้ง

#### 1. อัปโหลดไฟล์

อัปโหลดไฟล์ทั้งหมดไปยัง `httpdocs/` หรือโฟลเดอร์ของ subdomain ที่ต้องการ

#### 2. สร้าง Database

1. เข้า Plesk Panel
2. ไปที่ **Databases** > **Create Database**
3. ตั้งชื่อ database (เช่น `school_upload`)
4. สร้าง database user

#### 3. ตั้งค่า .env

สร้างไฟล์ `.env` ในโฟลเดอร์โปรเจค:

```env
APP_NAME="School Media Upload"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
SESSION_LIFETIME=120

FILESYSTEM_DISK=r2

# Cloudflare R2
AWS_ACCESS_KEY_ID=your_r2_key
AWS_SECRET_ACCESS_KEY=your_r2_secret
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-account.r2.dev
AWS_ENDPOINT=https://xxxxxxxxxxxx.r2.cloudflarestorage.com
```

#### 4. รันคำสั่งติดตั้ง (ผ่าน SSH)

```bash
cd /var/www/vhosts/your-domain.com/httpdocs

# ติดตั้ง dependencies
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# ตั้งค่า permissions
chmod -R 775 storage bootstrap/cache
chown -R plesk:psacln storage bootstrap/cache
```

#### 5. ตั้งค่า Nginx ใน Plesk

ไปที่ **Domains** > **your-domain.com** > **Hosting & DNS** > **Apache & Nginx Settings**

ติ๊ก **Proxy mode** และเพิ่มใน **Additional Nginx directives**:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Deny access to .env
location ~ /\.env {
    deny all;
}

# Deny access to storage
location ^~ /storage {
    deny all;
}
```

#### 6. ติดตั้ง FFmpeg (ถ้ายังไม่มี)

```bash
# Ubuntu/Debian
apt update && apt install ffmpeg

# CentOS/AlmaLinux
yum install epel-release
yum install ffmpeg
```

### สร้างบัญชี Teacher เริ่มต้น

หลังจากติดตั้งเสร็จ รันคำสั่งนี้เพื่อสร้างบัญชีครู:

```bash
php artisan tinker
```

จากนั้นพิมพ์:

```php
\App\Models\User::create([
    'name' => 'ครูผู้สอน',
    'email' => 'teacher@school.com',
    'password' => \Illuminate\Support\Facades\Hash::make('12345'),
    'role' => 'teacher'
]);
```

### การอัปเดต

เมื่อมีการอัปเดตโค้ด:

```bash
cd /var/www/vhosts/your-domain.com/httpdocs

# Pull latest code
git pull

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate

# Clear cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Troubleshooting

#### Permission Denied
```bash
chmod -R 775 storage bootstrap/cache
chown -R plesk:psacln storage bootstrap/cache
```

#### 500 Error
- ตรวจสอบ `.env` file
- รัน `php artisan config:clear`
- ดู error log ใน Plesk

#### Upload ไฟล์ไม่ได้
- ตรวจสอบ `storage/app` permissions
- ตรวจสอบ R2 credentials
- ตรวจสอบ PHP `upload_max_filesize` และ `post_max_size`

### การสำรองข้อมูล

```bash
# Backup database
mysqldump -u your_db_user -p your_database_name > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf backup_files_$(date +%Y%m%d).tar.gz storage/
```
