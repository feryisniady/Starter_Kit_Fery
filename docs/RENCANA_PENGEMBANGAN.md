# RENCANA PENGEMBANGAN
## Sistem Informasi Manajemen Pengawasan (SIMPAWAN)
### Roadmap Jangka Pendek, Menengah, dan Panjang

---

**Versi:** 1.0  
**Tanggal:** 27 April 2026  
**Penyusun:** Tim Pengembang  
**Periode Roadmap:** 2026 – 2028  

---

## DAFTAR ISI

1. [Ringkasan Status Saat Ini](#1-ringkasan-status-saat-ini)
2. [Prioritas Perbaikan Segera (Hotfix/Pre-Release)](#2-prioritas-perbaikan-segera-hotfixpre-release)
3. [Roadmap Pengembangan](#3-roadmap-pengembangan)
4. [Backlog Fitur Terperinci](#4-backlog-fitur-terperinci)
5. [Technical Debt & Refactoring](#5-technical-debt--refactoring)
6. [Infrastruktur & DevOps](#6-infrastruktur--devops)
7. [Pengelolaan Dependensi](#7-pengelolaan-dependensi)
8. [Strategi Pengujian](#8-strategi-pengujian)
9. [Strategi Go-Live & Migrasi](#9-strategi-go-live--migrasi)
10. [Estimasi Sumber Daya](#10-estimasi-sumber-daya)

---

## 1. RINGKASAN STATUS SAAT INI

### 1.1 Status Modul per 27 April 2026

| Modul | Status | Kematangan | Catatan |
|-------|--------|-----------|---------|
| RBAC (Pengguna, Peran, Izin, Menu) | Selesai | Produksi | Lengkap dan stabil |
| Activity Logging | Selesai | Produksi | Lengkap |
| Notifikasi In-App | Selesai | Produksi | Lengkap |
| Data Master (Irban, SDM, Entitas) | Selesai | Produksi | Lengkap |
| Kalender Hari Libur | Selesai | Produksi | Lengkap |
| Kode Temuan | Selesai | Produksi | Lengkap |
| PKPT (Perencanaan Tahunan) | Selesai | Produksi | Lengkap dengan approval workflow |
| SPT (Surat Perintah Tugas) | Selesai | Produksi | Lengkap, ada ekspor Word |
| PKA (Program Audit) & Template | Selesai | Produksi | Lengkap termasuk perpustakaan template |
| Kendali Mutu (KM-1 s.d. KM-11) | Selesai | Produksi | Lengkap termasuk sinkronisasi realisasi |
| KKA (Kertas Kerja Audit) | Selesai | Produksi | Auto-create, alur review, KKSA |
| NHP (Notisi Hasil Pemeriksaan) | Selesai | Produksi | Termasuk upload dokumen tanggapan |
| Tindak Lanjut (TL) | Selesai | Produksi | Termasuk verifikasi auditor |
| Portal Auditi | Selesai | Produksi | Tampilan NHP & TL untuk entitas |
| Reviu Dokumen AI (Gemini) | Selesai | Beta | API key masih hardcoded |
| Laporan & Rekap | Tidak Ada | - | Belum dikembangkan |
| Dashboard Pimpinan (KPI) | Parsial | Alpha | Dashboard dasar ada, belum KPI |
| Email Notification | Parsial | Beta | Infrastruktur ada, belum teruji penuh |
| WhatsApp Notification | Parsial | Beta | Infrastruktur ada, belum teruji |
| OAuth Login | Parsial | Alpha | UI konfigurasi ada, implementasi belum |
| LHA (Laporan Hasil Audit) | Tidak Ada | - | Belum dikembangkan |
| Import Data (Excel) | Tidak Ada | - | Belum dikembangkan |
| Unit Testing | Minimal | - | PHPUnit terkonfigurasi, coverage rendah |

### 1.2 Technical Debt Teridentifikasi

| Item | Tingkat Urgensi | Deskripsi |
|------|----------------|-----------|
| API key Gemini hardcoded | **Kritis** | Harus dipindah ke .env sebelum go-live |
| Coverage unit test rendah | Tinggi | Risiko regresi tinggi saat pengembangan lanjut |
| Validasi form tidak konsisten | Tinggi | Beberapa controller kurang validasi input |
| Penanganan error tidak seragam | Sedang | Beberapa area tidak memiliki try-catch |
| Soft deletes tidak konsisten | Sedang | Beberapa model tidak memanfaatkan soft delete |
| Cache strategy terbatas | Rendah | Hanya menu dan settings yang di-cache |
| Dokumentasi kode minim | Rendah | Banyak method tanpa docblock |

---

## 2. PRIORITAS PERBAIKAN SEGERA (HOTFIX/PRE-RELEASE)

Perbaikan ini harus diselesaikan **sebelum sistem digunakan di produksi**.

### P0 — Kritis (Wajib Sebelum Go-Live)

| ID | Item | Estimasi | Assignee |
|----|------|---------|---------|
| P0-01 | Pindahkan API key Gemini dari kode ke .env | 0.5 hari | Dev Backend |
| P0-02 | Audit keamanan OWASP Top 10 (XSS, CSRF, SQL Injection, dll.) | 2 hari | QA |
| P0-03 | Validasi file upload (tipe, ukuran, nama file) | 1 hari | Dev Backend |
| P0-04 | Test integrasi MailService (email notification) | 1 hari | Dev Backend |
| P0-05 | Perbaiki foreign key constraint yang belum konsisten | 1 hari | DBA |
| P0-06 | Setup session timeout (8 jam inaktif) | 0.5 hari | Dev Backend |

### P1 — Tinggi (Idealnya Sebelum Go-Live)

| ID | Item | Estimasi | Assignee |
|----|------|---------|---------|
| P1-01 | Tambah validasi server-side yang komprehensif di semua form | 3 hari | Dev Backend |
| P1-02 | Error handling yang graceful (custom 404, 500 pages) | 1 hari | Dev Frontend |
| P1-03 | Responsivitas mobile untuk halaman-halaman utama | 2 hari | Dev Frontend |
| P1-04 | Pengujian alur bisnis end-to-end (PKPT→SPT→KKA→NHP→TL) | 3 hari | QA |
| P1-05 | Pengujian alur persetujuan multi-tahap SPT | 1 hari | QA |
| P1-06 | Review dan konsistensi pesan error/validasi (Bahasa Indonesia) | 1 hari | Dev Frontend |

---

## 3. ROADMAP PENGEMBANGAN

### Fase 1 — Stabilisasi & Penyelesaian (Minggu 1–4, Mei 2026)

**Tema:** Menyelesaikan semua yang sudah hampir selesai dan memperbaiki yang kritis

```
MINGGU 1-2: Hotfix & Security
  ✓ Selesaikan semua item P0 dan P1
  ✓ Setup CI/CD pipeline dasar (GitHub Actions)
  ✓ Setup staging environment

MINGGU 3-4: Modul Notifikasi & Ekspor
  ✓ Email notification untuk setiap tahap approval SPT
  ✓ Email notification NHP ke auditi
  ✓ WhatsApp notification (opsional, jika WaService ready)
  ✓ Cetak KKA ke PDF
  ✓ Cetak NHP ke PDF
  ✓ Ekspor PKA ke PDF
```

**Deliverable Fase 1:**
- Sistem aman untuk digunakan di lingkungan produksi
- Notifikasi email berjalan
- Format cetak tersedia untuk dokumen utama

---

### Fase 2 — Pelaporan & Dashboard (Minggu 5–8, Juni 2026)

**Tema:** Memberikan visibilitas dan insight bagi pimpinan

```
MINGGU 5-6: Dashboard Eksekutif
  ✓ KPI card: total PKPT, SPT berjalan, KKA pending review, TL overdue
  ✓ Grafik trend pengawasan per bulan/kuartal
  ✓ Progress bar PKPT per Irban
  ✓ Alert TL yang melewati batas waktu

MINGGU 7-8: Rekap & Laporan
  ✓ Rekap PKPT: realisasi kegiatan vs. target per Irban
  ✓ Rekap SPT: status keseluruhan, rata-rata durasi
  ✓ Rekap KKA: progress per anggota tim
  ✓ Rekap NHP: status tanggapan per entitas
  ✓ Rekap TL: tingkat penyelesaian per entitas
  ✓ Ekspor semua rekap ke Excel (PHPSpreadsheet)
  ✓ Ekspor semua rekap ke PDF (MPDF)
```

**Deliverable Fase 2:**
- Dashboard eksekutif lengkap
- Modul pelaporan komprehensif
- Ekspor Excel & PDF tersedia

---

### Fase 3 — Laporan Hasil Audit & Import Data (Minggu 9–12, Juli 2026)

**Tema:** Melengkapi siklus audit dan memudahkan onboarding data

```
MINGGU 9-10: Modul LHA (Laporan Hasil Audit)
  ✓ Form penyusunan LHA dengan tarik data dari KKA & NHP
  ✓ Template LHA sesuai format standar
  ✓ Alur review: AT → KT → Dalnis → Irban
  ✓ Persetujuan digital LHA
  ✓ Ekspor LHA ke Word dan PDF

MINGGU 11-12: Import Data & Fitur Pendukung
  ✓ Import SDM dari Excel (dengan template download)
  ✓ Import Entitas dari Excel
  ✓ Pencarian global di navigation bar
  ✓ Filter lanjutan di DataTables (multi-kriteria)
  ✓ Bulk action di tabel-tabel yang relevan
```

**Deliverable Fase 3:**
- Modul LHA lengkap
- Kemudahan import data awal
- Pencarian dan filter lebih powerful

---

### Fase 4 — Pengujian Komprehensif & Go-Live (Minggu 13–16, Agustus 2026)

```
MINGGU 13: Pengujian
  ✓ Unit testing: coverage ≥ 80% untuk core modules
  ✓ Integration testing: semua alur bisnis utama
  ✓ Performance testing: 50 pengguna simultan
  ✓ Security testing: penetration test dasar
  ✓ UAT bersama key users Inspektorat
  ✓ Bug fixing dari hasil UAT

MINGGU 14: Deployment
  ✓ Setup server produksi
  ✓ Konfigurasi domain, SSL, email
  ✓ Migrasi database & seed data awal
  ✓ Smoke test di produksi
  ✓ Go-live

MINGGU 15-16: Pelatihan & Serah Terima
  ✓ Pelatihan administrator (1 hari)
  ✓ Pelatihan pengguna internal (1 hari)
  ✓ Pelatihan auditi (½ hari)
  ✓ Pendampingan 2 minggu pasca go-live
  ✓ Berita Acara Serah Terima
```

---

### Fase 5 — Pengembangan Lanjutan (September 2026 – Maret 2027)

**Tema:** Peningkatan fitur berdasarkan feedback pengguna

| Sprint | Fitur | Estimasi |
|--------|-------|---------|
| Sprint 5 | OAuth Login (Google) + Two-Factor Authentication | 2 minggu |
| Sprint 6 | Real-time notification (Server-Sent Events atau WebSocket) | 2 minggu |
| Sprint 7 | Modul Evaluasi Tindak Lanjut (ETL) — scoring & aging | 2 minggu |
| Sprint 8 | Peta GIS auditi (visualisasi sebaran di peta) | 2 minggu |
| Sprint 9 | API REST untuk integrasi pihak ketiga | 3 minggu |
| Sprint 10 | Mobile-first progressive web app (PWA) | 3 minggu |

---

### Fase 6 — Multi-Instansi & Enterprise (2027–2028)

**Tema:** Skalabilitas untuk digunakan banyak Inspektorat

| Item | Estimasi | Keterangan |
|------|---------|------------|
| Multi-tenancy (satu sistem, banyak Inspektorat) | 2 bulan | Isolasi data per instansi |
| Dashboard provinsi (agregasi data dari kabupaten/kota) | 1 bulan | Supervisor view |
| Integrasi SIMDA/SIKD | 2 bulan | Data keuangan otomatis |
| Integrasi SIASN/BKN | 1 bulan | Data pegawai otomatis |
| Mobile app (Android/iOS native) | 3 bulan | React Native atau Flutter |
| Analytics & Business Intelligence | 2 bulan | Tableau/PowerBI connector |
| Machine Learning untuk klasifikasi risiko | 3 bulan | Berbasis data historis |

---

## 4. BACKLOG FITUR TERPERINCI

### 4.1 Modul Laporan & Dashboard

```
[ ] DASH-01: Card KPI di dashboard utama
    - Total PKPT tahun berjalan & % realisasi
    - SPT aktif / selesai / terlambat
    - KKA pending review oleh KT
    - TL yang jatuh tempo (overdue)
    Estimasi: 3 hari | Prioritas: Tinggi

[ ] DASH-02: Grafik trend pengawasan
    - Chart bar: kegiatan per bulan
    - Chart pie: status SPT
    - Chart line: tren temuan per tahun
    Estimasi: 2 hari | Prioritas: Tinggi

[ ] DASH-03: Dashboard pimpinan (Inspektur only)
    - Summary eksekutif one-page
    - KPI per Irban side by side
    - Top 5 entitas dengan TL terbanyak/tertunggak
    Estimasi: 3 hari | Prioritas: Sedang

[ ] LAPOR-01: Rekap PKPT
    - Tabel: kegiatan, target, realisasi, % progress
    - Filter: tahun, Irban
    - Ekspor Excel & PDF
    Estimasi: 2 hari | Prioritas: Tinggi

[ ] LAPOR-02: Rekap SPT
    - Tabel: nomor SPT, entitas, periode, status, durasi
    - Filter: tahun, Irban, status
    - Ekspor Excel & PDF
    Estimasi: 2 hari | Prioritas: Tinggi

[ ] LAPOR-03: Rekap Temuan & TL
    - Matriks: entitas x temuan x status TL
    - Aging TL: < 30 hari, 30-90 hari, > 90 hari
    - Ekspor Excel & PDF
    Estimasi: 3 hari | Prioritas: Tinggi
```

### 4.2 Modul LHA (Laporan Hasil Audit)

```
[ ] LHA-01: Penyusunan LHA dari data SPT/KKA
    - Auto-populate data tim, entitas, periode
    - Tarik simpulan KKA terpilih ke LHA
    - Tarik rekomendasi ke LHA
    Estimasi: 5 hari | Prioritas: Tinggi

[ ] LHA-02: Alur review LHA
    - Draft → Review KT → Review Dalnis → Selesai
    - Catatan review per revisi
    Estimasi: 2 hari | Prioritas: Tinggi

[ ] LHA-03: Ekspor LHA
    - Ekspor ke Word (PHPWord)
    - Ekspor ke PDF
    - Sesuai template standar
    Estimasi: 3 hari | Prioritas: Tinggi
```

### 4.3 Fitur Keamanan & Akun

```
[ ] SEC-01: OAuth Login (Google)
    - Konfigurasi Google OAuth 2.0
    - Link akun Google ke akun sistem
    Estimasi: 3 hari | Prioritas: Sedang

[ ] SEC-02: Two-Factor Authentication (TOTP)
    - Setup via Google Authenticator / Authy
    - Recovery codes
    Estimasi: 3 hari | Prioritas: Rendah

[ ] SEC-03: Riwayat Login
    - Log: tanggal, IP, device, lokasi (GeoIP opsional)
    - Alert login dari IP baru
    Estimasi: 2 hari | Prioritas: Sedang

[ ] SEC-04: Session management
    - List sesi aktif pengguna
    - Kemampuan terminate sesi dari device lain
    Estimasi: 2 hari | Prioritas: Rendah
```

### 4.4 Import & Integrasi Data

```
[ ] IMP-01: Import SDM dari Excel
    - Template Excel download
    - Validasi sebelum import (NIP unik, dll.)
    - Preview sebelum konfirmasi import
    Estimasi: 2 hari | Prioritas: Sedang

[ ] IMP-02: Import Entitas dari Excel
    - Template Excel download
    - Mapping ke akun pengguna existing
    Estimasi: 1 hari | Prioritas: Sedang

[ ] IMP-03: Import Hari Libur Nasional
    - Dari file Excel (manual)
    - Opsional: dari API pihak ketiga (BKN/data.go.id)
    Estimasi: 1 hari | Prioritas: Rendah
```

### 4.5 Penyempurnaan UX

```
[ ] UX-01: Wizard pembuatan SPT
    - Step-by-step form: Info Dasar → Tim → Review
    - Progress indicator
    Estimasi: 3 hari | Prioritas: Sedang

[ ] UX-02: Pencarian global
    - Searchbar di navbar
    - Cari di: SPT, KKA, Temuan, Entitas
    Estimasi: 2 hari | Prioritas: Sedang

[ ] UX-03: Breadcrumb yang lebih kontekstual
    - Tampilkan nama SPT/entitas, bukan hanya ID
    Estimasi: 1 hari | Prioritas: Rendah

[ ] UX-04: Dark mode
    - Toggle di navbar
    - Preferensi tersimpan di localStorage
    Estimasi: 2 hari | Prioritas: Rendah

[ ] UX-05: Bulk action di tabel
    - Select all, batch delete, batch export
    Estimasi: 2 hari | Prioritas: Rendah
```

### 4.6 Notifikasi Lanjutan

```
[ ] NOTIF-01: Email notification SPT approval
    - Kirim email ke approver berikutnya saat diajukan
    - Email konfirmasi ke pengaju saat disetujui/ditolak
    Estimasi: 1.5 hari | Prioritas: Tinggi

[ ] NOTIF-02: Email notification NHP
    - Kirim email ke auditi saat NHP dikirim
    - Reminder ke auditi jika belum ada tanggapan 7 hari
    Estimasi: 1 hari | Prioritas: Tinggi

[ ] NOTIF-03: Notifikasi TL overdue
    - Cron job harian: cek TL yang mendekati/melewati batas
    - Email & in-app notification ke auditi dan auditor
    Estimasi: 2 hari | Prioritas: Sedang

[ ] NOTIF-04: WhatsApp notification
    - Sama dengan email notification (via WaService)
    - Konfigurasi on/off per pengguna
    Estimasi: 2 hari | Prioritas: Sedang
```

---

## 5. TECHNICAL DEBT & REFACTORING

### 5.1 Prioritas Refactoring

| Item | Urgensi | Dampak | Estimasi |
|------|---------|--------|---------|
| Pindahkan Gemini API key ke .env | Kritis | Keamanan | 0.5 hari |
| Tambah unit test untuk core models | Tinggi | Stabilitas | 5 hari |
| Standarisasi error handling di semua controllers | Tinggi | Reliabilitas | 3 hari |
| Implementasi konsisten soft deletes | Sedang | Integritas data | 2 hari |
| Extract business logic ke Service layer | Sedang | Maintainability | 5 hari |
| Optimasi query N+1 yang teridentifikasi | Sedang | Performa | 2 hari |
| Tambah caching untuk query berat | Rendah | Performa | 2 hari |

### 5.2 Rencana Refactoring Service Layer

Beberapa logika bisnis yang sebaiknya dipisahkan ke layer Service:

```
app/Services/
├── AuditService.php         (Business rules audit workflow)
├── KkaService.php           (KKA auto-creation logic)
├── NhpService.php           (NHP compilation from KKA)
├── NotificationService.php  (Centralized notification dispatch)
├── ReportService.php        (Report data aggregation)
└── ImportService.php        (Data import logic)
```

### 5.3 Standarisasi Response API

Untuk konsistensi respons AJAX:

```php
// Struktur respons standar:
[
    'status'  => true/false,
    'message' => 'Pesan yang akan ditampilkan',
    'data'    => [...],     // Payload opsional
    'errors'  => [...],     // Validation errors opsional
]
```

---

## 6. INFRASTRUKTUR & DEVOPS

### 6.1 CI/CD Pipeline

```yaml
# GitHub Actions workflow
trigger: push ke branch develop atau main

jobs:
  1. Code Quality (PHP CS Fixer + PHPStan)
  2. Unit Tests (PHPUnit)
  3. Build & Deploy ke Staging (pada push develop)
  4. User Approval → Deploy ke Production (pada push main)
```

### 6.2 Environment Strategy

| Environment | Tujuan | Branch |
|-------------|--------|--------|
| Local Dev | Pengembangan harian | feature/* |
| Staging | QA & UAT | develop |
| Production | Live system | main |

### 6.3 Backup Strategy

```
Backup Harian:
  - Database: mysqldump → compressed → upload ke cloud storage
  - File uploads: rsync ke backup server
  - Retensi: 30 hari

Backup Mingguan:
  - Full backup (DB + files)
  - Retensi: 3 bulan

Backup Bulanan:
  - Full backup
  - Retensi: 1 tahun
```

### 6.4 Monitoring

| Tool | Fungsi |
|------|--------|
| UptimeRobot (free) | Monitoring uptime & alert downtime |
| Laravel Telescope / custom | Application performance monitoring |
| MySQL Slow Query Log | Database performance monitoring |
| Logrotate | Rotasi log file CodeIgniter |

### 6.5 Spesifikasi Server Produksi

```
Minimum:
  CPU: 4 Core (Intel/AMD)
  RAM: 8 GB
  Storage: 100 GB SSD
  OS: Ubuntu 22.04 LTS
  Web: Nginx + PHP-FPM 8.1
  DB: MySQL 8.0
  SSL: Let's Encrypt (auto-renew)

Recommended:
  CPU: 8 Core
  RAM: 16 GB
  Storage: 200 GB SSD + 1 TB backup
  Database: Dedicated DB server
```

---

## 7. PENGELOLAAN DEPENDENSI

### 7.1 Dependensi Saat Ini

```json
{
  "require": {
    "codeigniter4/framework": "dev-develop",
    "skuadron45/ci4adminrbac": "^1.0",
    "phpmailer/phpmailer": "^6.9",
    "phpoffice/phpword": "^1.3"
  },
  "require-dev": {
    "fakerphp/faker": "^1.9",
    "phpunit/phpunit": "^10.5.16"
  }
}
```

### 7.2 Dependensi yang Perlu Ditambahkan

| Library | Fungsi | Versi Target |
|---------|--------|-------------|
| phpoffice/phpspreadsheet | Ekspor/import Excel | ^2.0 |
| mpdf/mpdf | Ekspor PDF | ^8.2 |
| spatie/flysystem-dropbox | Backup storage cloud (opsional) | ^3.0 |
| league/oauth2-client | OAuth login (Google, dsb.) | ^2.7 |
| pragmarx/google2fa | Two-factor authentication | ^8.0 |

### 7.3 Rencana Upgrade Framework

```
Saat ini: CodeIgniter 4 (dev-develop)
Target Q3 2026: Stabilkan ke versi stabil terbaru CI4
Target 2027: Evaluasi migrasi ke versi CI4 dengan long-term support
```

---

## 8. STRATEGI PENGUJIAN

### 8.1 Hierarki Pengujian

```
Level 4: End-to-End / UAT
         (Alur bisnis lengkap bersama pengguna nyata)
         
Level 3: Integration Testing
         (Antar modul: SPT → KKA → NHP → TL)

Level 2: Unit Testing
         (Model, Helper, Service — isolasi)

Level 1: Code Quality
         (PHP CS, PHPStan static analysis)
```

### 8.2 Target Coverage Unit Test

| Komponen | Target Coverage |
|----------|----------------|
| Models (query methods) | 85% |
| Helpers (business logic) | 80% |
| Services (setelah refactoring) | 85% |
| Controllers | 60% (integration test covers) |

### 8.3 Skenario UAT Wajib

| No | Skenario | Aktor | Hasil yang Diharapkan |
|----|---------|-------|----------------------|
| UAT-01 | Login & logout semua peran | Admin, Irban, KT, AT, Auditi | Berhasil, menu sesuai peran |
| UAT-02 | Buat PKPT, tambah kegiatan, setujui | Admin, Irban | PKPT berstatus disetujui |
| UAT-03 | Buat SPT dari PKPT, jalani approval penuh | Admin, Irban, PJ | SPT berstatus Terbit |
| UAT-04 | Buat PKA, terapkan template, tugaskan ke AT | KT, Dalnis | PKA tersimpan dengan penugasan |
| UAT-05 | Jalani KM-1 s.d. KM-5 (approve) | KT, Dalnis | KM-5 disetujui, KKA auto-created |
| UAT-06 | AT isi KKA (Ikhtisar, Simpulan, Rekomendasi), submit | AT | KKA ter-submit ke KT |
| UAT-07 | KT review dan setujui KKA semua AT | KT | Semua KKA berstatus selesai |
| UAT-08 | Buat NHP, kirim ke auditi | KT, Irban | NHP terkirim, auditi bisa lihat |
| UAT-09 | Auditi respons NHP, upload dokumen | Auditi | Tanggapan tersimpan |
| UAT-10 | Auditor verifikasi tindak lanjut | AT, KT | TL berstatus selesai |
| UAT-11 | Lihat dashboard dan rekap | Inspektur | Data ditampilkan akurat |
| UAT-12 | Reset password via email | Semua peran | Email terkirim, password berubah |

---

## 9. STRATEGI GO-LIVE & MIGRASI

### 9.1 Pre-Go-Live Checklist

**Teknis:**
- [ ] Semua item P0 dan P1 selesai
- [ ] UAT selesai dan disetujui
- [ ] Performance test lulus (50 concurrent users, halaman < 3 detik)
- [ ] Security audit selesai (no critical/high severity)
- [ ] SSL certificate terpasang
- [ ] Backup otomatis berjalan
- [ ] Monitoring uptime aktif
- [ ] Environment variables di-set dengan benar di produksi
- [ ] Database migration telah diuji di staging

**Data Awal:**
- [ ] Data Irban (unit pengawasan) diinput
- [ ] Data SDM (minimal pengguna aktif) diinput
- [ ] Data Entitas auditi utama diinput
- [ ] Kode temuan diinput
- [ ] Hari libur tahun berjalan diinput
- [ ] Pengguna (semua peran) dibuat dan diuji login
- [ ] Menu dikonfigurasi per peran

**Dokumentasi & SDM:**
- [ ] Panduan pengguna tersedia dan didistribusikan
- [ ] Administrator sudah terlatih
- [ ] Kontak support sudah dikomunikasikan
- [ ] Prosedur rollback didokumentasikan

### 9.2 Rencana Rollback

Jika terjadi masalah kritis dalam 24 jam pertama go-live:

```
1. Aktifkan halaman maintenance mode
2. Restore database dari backup pre-migration
3. Rollback kode ke versi sebelumnya
4. Notifikasi pengguna melalui WhatsApp/email
5. Analisis penyebab masalah
6. Perbaikan dan rencana go-live ulang
```

### 9.3 Post-Go-Live Monitoring

**Minggu 1-2 pasca go-live:**
- Monitor error log PHP harian
- Monitor slow query MySQL
- Kumpulkan feedback pengguna setiap hari
- Daily standup tim pengembang

**Bulan 1:**
- Weekly bug triaging
- Pengukuran KPI: uptime, response time, user adoption
- Survey kepuasan pengguna di akhir bulan

---

## 10. ESTIMASI SUMBER DAYA

### 10.1 Fase 1–4 (Hingga Go-Live): Estimasi Workload

| Fase | Durasi | Beban Kerja |
|------|--------|------------|
| Fase 1 — Stabilisasi | 4 minggu | 2 Dev + 1 QA + 1 PM |
| Fase 2 — Pelaporan | 4 minggu | 2 Dev + 1 QA |
| Fase 3 — LHA & Import | 4 minggu | 2 Dev + 1 QA |
| Fase 4 — Test & Go-Live | 4 minggu | 1 Dev + 1 QA + 1 PM |
| **Total** | **16 minggu** | — |

### 10.2 Fase 5 (Pengembangan Lanjutan): Estimasi

| Sprint | Fitur | Durasi | Tim |
|--------|-------|--------|-----|
| 5 | OAuth & 2FA | 2 minggu | 1 Dev |
| 6 | Notifikasi real-time | 2 minggu | 1 Dev |
| 7 | Modul ETL & Scoring | 2 minggu | 1 Dev + 1 Domain Expert |
| 8 | Peta GIS | 2 minggu | 1 Dev Frontend |
| 9 | REST API | 3 minggu | 1 Dev Backend |
| 10 | PWA | 3 minggu | 1 Dev Full Stack |

### 10.3 Estimasi Biaya Infrastruktur (per Tahun)

| Item | Estimasi Biaya/Tahun |
|------|---------------------|
| VPS/Cloud Server (4 Core, 8GB RAM) | Rp 3.000.000 – Rp 6.000.000 |
| Domain .go.id | Rp 150.000 |
| SSL Certificate (Let's Encrypt) | Gratis |
| Email SMTP (Google Workspace / Zoho) | Rp 500.000 – Rp 2.000.000 |
| Backup Cloud Storage (50 GB) | Rp 600.000 |
| Monitoring (UptimeRobot) | Gratis |
| **Total Estimasi** | **Rp 4.250.000 – Rp 8.750.000** |

---

## LAMPIRAN: SPRINT BACKLOG TEMPLATE

Untuk setiap sprint, template berikut digunakan:

```markdown
## Sprint [N] — [Nama Sprint]
**Periode:** [Tanggal Mulai] – [Tanggal Selesai]
**Sprint Goal:** [Satu kalimat tujuan sprint]

### User Stories
| ID | As a... | I want to... | So that... | Points |
|----|---------|-------------|-----------|--------|

### Tasks
| ID | Task | Assignee | Est | Status |
|----|------|---------|-----|--------|

### Definition of Done
- [ ] Kode telah di-review
- [ ] Unit test ditulis dan lulus
- [ ] Fitur telah di-demo ke stakeholder
- [ ] Dokumentasi diperbarui
- [ ] Tidak ada critical bug

### Sprint Retrospective
- **Went well:** ...
- **To improve:** ...
- **Action items:** ...
```

---

*Dokumen ini merupakan rencana hidup yang diperbarui setiap awal sprint berdasarkan progress aktual, feedback pengguna, dan perubahan prioritas bisnis.*

**Versi Dokumen:**
| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 1.0 | 27 April 2026 | Versi awal |
