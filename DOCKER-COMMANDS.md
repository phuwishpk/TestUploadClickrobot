# คำสั่ง Docker สำหรับรันระบบ (Local Development)

## Quick Start (3 ขั้นตอน)

```bash
# 1. Build และ Run
docker-compose up -d --build

# 2. รอให้พร้อม แล้วติดตั้ง
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed

# 3. เปิดเว็บ
# http://localhost:8080
```

---

## ข้อมูลเข้าสู่ระบบ (Demo Accounts)

| บทบาท | อีเมล | รหัสผ่าน |
|-------|-------|---------|
| Teacher | teacher@school.com | 12345 |
| Parent | parent@school.com | 12345 |
| Student | student1@school.com | 12345 |

---

## คำสั่งที่ใช้บ่อย

```bash
# ดูสถานะ
docker-compose ps

# ดู logs แบบ real-time
docker-compose logs -f app

# เข้า container
docker-compose exec app bash

# รีสตาร์ท
docker-compose restart

# หยุด container
docker-compose down

# ลบทั้งหมด (รวม database)
docker-compose down -v

# Rebuild
docker-compose up -d --build
```

---

## เริ่มใหม่ทั้งหมด

```bash
docker-compose down -v --remove-orphans
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate:fresh --seed
```

---

## Troubleshooting

### Permission Error
```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### 500 Error
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

### เข้า MySQL โดยตรง
```bash
docker-compose exec mysql mysql -u root -p
# Password: root_secret
```

### ดูไฟล์ที่อัปโหลด
```bash
docker-compose exec app ls -la storage/app/uploads
```

---

## การใช้ Cloudflare R2

1. แก้ไข `.env`:
```env
FILESYSTEM_DISK=r2
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your-bucket
AWS_ENDPOINT=https://xxx.r2.cloudflarestorage.com
```

2. Rebuild:
```bash
docker-compose up -d --build
```
