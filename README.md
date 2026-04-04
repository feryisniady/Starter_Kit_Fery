# CI4 RBAC Starter Kit

Starter kit berbasis **CodeIgniter 4** dengan sistem RBAC (Role-Based Access Control) lengkap, siap pakai ulang untuk berbagai proyek.

## Fitur Utama

- **Autentikasi** — Login, logout, manajemen sesi
- **RBAC** — Role, permission, dan helper `hasRole()` / `hasPermission()`
- **Rate Limiting Login** — Pembatasan percobaan login dengan lockout & countdown timer
- **Activity Log** — Audit trail lengkap semua aksi CRUD + login/logout
- **Manajemen Menu** — Menu dinamis berbasis database dengan sub-menu
- **Manajemen User** — CRUD user, assign role
- **Branding Terpusat** — Semua identitas aplikasi dikonfigurasi via `.env`, tanpa ubah kode view

## Persyaratan

- PHP 8.1+
- MySQL / MariaDB
- Composer
- PHP extensions: `intl`, `mbstring`, `mysqlnd`, `json`

## Instalasi

### 1. Clone & Install

```bash
git clone <url-repo> nama-proyek
cd nama-proyek
composer install
```

### 2. Konfigurasi Environment

```bash
cp .env.example .env
```

Edit `.env` sesuai kebutuhan:

```env
# Aplikasi
CI_ENVIRONMENT = development
app.baseURL     = 'http://localhost:8080/'

# Branding (sesuaikan tanpa ubah kode apapun)
brand.appName    = 'Nama Aplikasi Anda'
brand.appVersion = '1.0'
brand.orgName    = 'Nama Instansi / Perusahaan'
brand.orgShort   = 'Nama Singkat'
brand.orgWebsite = 'https://example.com'
brand.appTagline = 'Tagline aplikasi Anda'

# Database
database.default.hostname = localhost
database.default.database = nama_database
database.default.username = root
database.default.password =
```

### 3. Migrasi & Seeder

```bash
php spark migrate
php spark db:seed RoleSeeder
php spark db:seed UserSeeder
php spark db:seed PermissionSeeder
php spark db:seed MenuSeeder
php spark db:seed ActivityLogSeeder
```

### 4. Jalankan

```bash
php spark serve
```

Buka `http://localhost:8080` di browser.

**Default login:**
- Email: `admin@example.com`
- Password: `password`

## Konfigurasi Branding

Semua identitas aplikasi terpusat di `app/Config/Brand.php` dan dapat di-override via `.env`:

| Key `.env`          | Keterangan                          | Default               |
|---------------------|-------------------------------------|-----------------------|
| `brand.appName`     | Nama lengkap aplikasi               | `RBAC Starter Kit`    |
| `brand.appVersion`  | Versi aplikasi                      | `1.0`                 |
| `brand.orgName`     | Nama organisasi/instansi lengkap    | `Nama Instansi`       |
| `brand.orgShort`    | Nama singkat (sidebar, header)      | `Instansi`            |
| `brand.orgWebsite`  | URL website organisasi              | `#`                   |
| `brand.appTagline`  | Tagline / deskripsi singkat         | `Sistem Informasi...` |

## Struktur RBAC

```
Roles        → memiliki banyak Permissions
Users        → memiliki banyak Roles
Permissions  → digunakan sebagai gate di controller & view
```

**Helper functions:**
```php
hasRole('admin')              // cek role aktif
hasPermission('user.create')  // cek permission
```

**Di view (sembunyikan tombol jika tidak ada permission):**
```php
<?php if(hasPermission('user.create')): ?>
  <a href="/admin/users/create" class="btn btn-primary">Tambah</a>
<?php endif; ?>
```

## Activity Log

Semua aksi CRUD dan autentikasi otomatis tercatat. Untuk mencatat aksi custom:

```php
logActivity('modul.create', 'modul', 'Deskripsi aksi', $userId, $userName, [
    'after' => $data,
]);
```

**Purge log lama via CLI:**
```bash
php spark activitylog:purge 90   # hapus log lebih dari 90 hari
```

## Git Workflow

```bash
# Ambil perubahan terbaru
git pull origin Main

# Buat branch fitur baru
git checkout -b fitur/nama-fitur

# Setelah selesai, push
git push -u origin fitur/nama-fitur

# Merge ke Main (via PR atau langsung)
git checkout Main
git merge fitur/nama-fitur
git push origin Main
```

## Persyaratan Server

- Web server (Apache/Nginx) diarahkan ke folder `public/`
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.3+
