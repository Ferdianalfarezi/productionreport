# Production Report System

## Requirements
- PHP 8.2+
- MySQL 8.0+
- Composer

## Cara Setup

### 1. Install dependencies
```bash
composer install
```

### 2. Setup .env
```bash
cp .env.example .env
php artisan key:generate
```
Edit DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env

### 3. Buat database MySQL
```sql
CREATE DATABASE productionreport CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Migrate & seed
```bash
php artisan migrate
php artisan db:seed
```

Default users:
- superadmin / password
- admin / password

### 5. Storage link
```bash
php artisan storage:link
```

### 6. Jalankan
```bash
php artisan serve
```
Buka: http://localhost:8000

---

## Import Excel Mesin
Format file: .xlsx/.xls
- Row 2: Header (LINE Machine, MACHINE_NO, TONAGE, LINE, GSPH_THEORY, REMARKS, UPDATE_BY, UPDATE_TIME)  
- Row 3+: Data mesin

Machine_no yang sudah ada di DB akan di-skip (tidak duplikat).

