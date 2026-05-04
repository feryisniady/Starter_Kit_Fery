# KERANGKA ACUAN KERJA (KAK)
## Pengembangan Sistem Informasi Manajemen Pengawasan (SIMPAWAN)
### Berbasis CI4 RBAC Starter Kit

---

**Nomor KAK:** KAK/SIMPAWAN/2026/001  
**Versi:** 1.0  
**Tanggal:** 27 April 2026  
**Unit Kerja:** Inspektorat  
**Status:** Draft  

---

## DAFTAR ISI

1. [Latar Belakang](#1-latar-belakang)
2. [Maksud dan Tujuan](#2-maksud-dan-tujuan)
3. [Sasaran](#3-sasaran)
4. [Ruang Lingkup Pekerjaan](#4-ruang-lingkup-pekerjaan)
5. [Metodologi Pengembangan](#5-metodologi-pengembangan)
6. [Keluaran yang Diharapkan](#6-keluaran-yang-diharapkan)
7. [Jangka Waktu Pelaksanaan](#7-jangka-waktu-pelaksanaan)
8. [Kebutuhan Sumber Daya](#8-kebutuhan-sumber-daya)
9. [Spesifikasi Teknis](#9-spesifikasi-teknis)
10. [Standar dan Kualitas](#10-standar-dan-kualitas)
11. [Risiko dan Penanganannya](#11-risiko-dan-penanganannya)
12. [Pembiayaan](#12-pembiayaan)
13. [Pelaporan dan Komunikasi](#13-pelaporan-dan-komunikasi)
14. [Ketentuan Lain-Lain](#14-ketentuan-lain-lain)

---

## 1. LATAR BELAKANG

### 1.1 Dasar Hukum

1. Undang-Undang No. 23 Tahun 2014 tentang Pemerintahan Daerah
2. Peraturan Pemerintah No. 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah (SPIP)
3. Peraturan Presiden No. 95 Tahun 2018 tentang Sistem Pemerintahan Berbasis Elektronik (SPBE)
4. Peraturan Menteri Dalam Negeri No. 23 Tahun 2007 tentang Pedoman Tata Cara Pengawasan
5. Standar Audit Intern Pemerintah Indonesia (SAIPI) — AAIPI 2013
6. Pedoman Kendali Mutu Pengawasan (KM-1 s.d. KM-11)

### 1.2 Gambaran Umum

Inspektorat sebagai Aparat Pengawasan Intern Pemerintah (APIP) memiliki peran strategis dalam memastikan akuntabilitas dan transparansi penyelenggaraan pemerintahan daerah. Namun, efektivitas pengawasan masih terkendala oleh proses manual yang memakan waktu, tidak ada sistem terintegrasi untuk mengelola siklus lengkap pengawasan, dan keterbatasan pemantauan tindak lanjut.

Pengembangan **Sistem Informasi Manajemen Pengawasan (SIMPAWAN)** bertujuan menjawab tantangan ini melalui digitalisasi seluruh siklus pengawasan dari perencanaan hingga pemantauan tindak lanjut.

Sistem ini dikembangkan di atas fondasi **CI4 RBAC Starter Kit** — framework berbasis CodeIgniter 4 yang telah memiliki fondasi RBAC (Role-Based Access Control), manajemen pengguna, logging aktivitas, dan modul-modul audit yang sebagian sudah diimplementasikan.

### 1.3 Kondisi Sistem Saat Ini

Berdasarkan evaluasi kondisi existing, sistem telah memiliki:

**Modul yang Sudah Berfungsi:**
- Manajemen RBAC (Pengguna, Peran, Izin, Menu)
- Logging aktivitas & notifikasi in-app
- Data master (Irban, Entitas, SDM)
- PKPT (Perencanaan Kegiatan Pengawasan Tahunan)
- SPT (Surat Perintah Tugas) dengan alur persetujuan
- PKA (Program Kegiatan Audit) & Template PKA
- Kendali Mutu KM-1 s.d. KM-11
- KKA (Kertas Kerja Audit) — Ikhtisar, Simpulan, Rekomendasi
- NHP (Notisi Hasil Pemeriksaan)
- Tindak Lanjut
- Portal Auditi (entitas yang diaudit)
- Reviu dokumen berbasis AI (Google Gemini)

**Modul yang Memerlukan Penyempurnaan:**
- Laporan dan rekapitulasi (belum komprehensif)
- Ekspor data ke format Excel/PDF
- Integrasi email dan WhatsApp (infrastruktur ada, perlu pengujian menyeluruh)
- Implementasi OAuth login
- Unit testing dan pengujian komprehensif
- Dokumentasi teknis dan panduan pengguna

---

## 2. MAKSUD DAN TUJUAN

### 2.1 Maksud

Kerangka Acuan Kerja ini dimaksudkan sebagai pedoman pelaksanaan pekerjaan pengembangan, penyempurnaan, pengujian, dan implementasi **Sistem Informasi Manajemen Pengawasan (SIMPAWAN)** pada Inspektorat.

### 2.2 Tujuan

1. Menyelesaikan dan menyempurnakan seluruh modul SIMPAWAN yang masih dalam tahap pengembangan
2. Memastikan integrasi antar modul berjalan dengan baik dan sesuai alur bisnis pengawasan
3. Melakukan pengujian menyeluruh terhadap seluruh fungsionalitas sistem
4. Mengimplementasikan sistem pada infrastruktur produksi
5. Menyediakan dokumentasi teknis, panduan pengguna, dan panduan administrator
6. Melatih pengguna untuk dapat mengoperasikan sistem secara mandiri

---

## 3. SASARAN

| No | Sasaran | Indikator |
|----|---------|-----------|
| 1 | Seluruh modul utama berfungsi dan terintegrasi | 0 critical bug pada saat go-live |
| 2 | Sistem telah diuji dengan skenario penggunaan nyata | Test coverage ≥ 80% untuk fitur utama |
| 3 | Pengguna mampu mengoperasikan sistem | Tingkat kemandirian pengguna ≥ 85% setelah pelatihan |
| 4 | Sistem terdokumentasi dengan lengkap | Panduan pengguna, panduan admin, dan dokumentasi teknis tersedia |
| 5 | Sistem berjalan stabil di lingkungan produksi | Uptime ≥ 99% pada jam kerja selama 30 hari pertama |

---

## 4. RUANG LINGKUP PEKERJAAN

### 4.1 Lingkup Teknis Pengembangan

#### A. Penyempurnaan Modul yang Ada

**A1 — Modul Pelaporan & Rekap**
- Dashboard eksekutif dengan KPI pengawasan (grafik, tabel ringkas)
- Rekap PKPT: realisasi kegiatan vs. rencana per Irban
- Rekap SPT: status dan durasi audit berjalan
- Rekap KKA: progress per anggota tim
- Rekap NHP: status tanggapan per entitas
- Rekap TL: tingkat penyelesaian tindak lanjut
- Ekspor semua rekap ke Excel dan PDF

**A2 — Penyempurnaan Modul Notifikasi**
- Email notification untuk persetujuan SPT (setiap tahap)
- Email notification untuk NHP yang dikirim ke auditi
- WhatsApp notification (via WaService yang sudah ada)
- Pengujian end-to-end MailService dan WaService

**A3 — Ekspor Dokumen**
- KKA cetak format standar (PDF)
- NHP cetak format resmi
- Laporan ringkas TL (PDF)
- PKA cetak

**A4 — Penyempurnaan UI/UX**
- Perbaikan alur navigasi antar modul yang terkait
- Validasi form yang komprehensif dengan pesan error yang jelas
- Loading state dan feedback aksi pengguna
- Responsivitas mobile untuk semua halaman utama

**A5 — Keamanan**
- Pemindahan API key Gemini dari kode ke .env
- Audit keamanan keseluruhan sistem (OWASP Top 10)
- Implementasi Content Security Policy (CSP)
- Review dan penguatan validasi input

#### B. Fitur Baru yang Dikembangkan

**B1 — Modul Laporan Hasil Audit (LHA)**
- Form penyusunan LHA dari data KKA & NHP yang ada
- Template LHA sesuai format standar
- Alur review dan persetujuan LHA
- Ekspor LHA ke Word dan PDF

**B2 — Modul Dashboard Pimpinan**
- Dashboard khusus Inspektur dengan KPI high-level
- Grafik trend pengawasan per tahun
- Peta sebaran pengawasan per wilayah/SKPD
- Alert untuk TL yang melewati batas waktu

**B3 — Fitur Import Data**
- Import data SDM dari Excel (template tersedia)
- Import data Entitas dari Excel
- Import hari libur nasional dari API BKN (opsional)

**B4 — Fitur Pencarian & Filter Lanjutan**
- Pencarian full-text di seluruh data audit
- Filter multi-kriteria untuk semua DataTables
- Pencarian global di navigation bar

**B5 — Manajemen Sesi & Keamanan Akun**
- Implementasi OAuth login (Google minimal)
- Two-factor authentication (opsional)
- Riwayat login pengguna

#### C. Pengujian Komprehensif

- **Unit Testing** — Coverage untuk model dan helper functions
- **Integration Testing** — Alur bisnis end-to-end
- **User Acceptance Testing (UAT)** — Pengujian bersama pengguna
- **Performance Testing** — Load test untuk 50 pengguna simultan
- **Security Testing** — Penetration test dasar (OWASP Top 10)

#### D. Implementasi & Deployment

- Setup lingkungan produksi (server, domain, SSL)
- Migrasi database dari environment pengembangan ke produksi
- Konfigurasi backup otomatis
- Setup monitoring uptime (UptimeRobot atau sejenisnya)

#### E. Dokumentasi

- **Panduan Pengguna** (per peran: Inspektur, Irban, KT, AT, Dalnis, Auditi)
- **Panduan Administrator Sistem**
- **Dokumentasi Teknis** (arsitektur, ERD, API reference)
- **SOP Operasional** (backup, restore, purge log)
- **Video tutorial** untuk modul utama (opsional)

#### F. Pelatihan

- Pelatihan administrator sistem (1 hari)
- Pelatihan pengguna internal — auditor (1 hari)
- Pelatihan entitas auditi (½ hari)
- Sesi Q&A dan bimbingan pasca pelatihan (2 minggu)

---

## 5. METODOLOGI PENGEMBANGAN

### 5.1 Pendekatan

Pengembangan menggunakan pendekatan **Agile-Waterfall Hybrid**:

- **Waterfall** untuk tahap perencanaan, arsitektur, dan dokumentasi
- **Agile (Sprint-based)** untuk pengembangan fitur baru dan penyempurnaan

### 5.2 Tahapan Pengembangan

```
FASE 1: ANALISIS & PERANCANGAN (Minggu 1-2)
  ├── Review kode dan sistem existing
  ├── Penyelarasan kebutuhan dengan stakeholder
  ├── Perancangan detail modul baru
  └── Pembuatan mockup/wireframe fitur baru

FASE 2: PENGEMBANGAN (Minggu 3-10)
  ├── Sprint 1: Penyempurnaan modul yang ada (A1-A5)
  ├── Sprint 2: Modul LHA & Dashboard Pimpinan (B1-B2)
  ├── Sprint 3: Fitur Import & Pencarian Lanjutan (B3-B4)
  └── Sprint 4: OAuth, 2FA, penyempurnaan final (B5)

FASE 3: PENGUJIAN (Minggu 11-13)
  ├── Unit & Integration Testing
  ├── UAT bersama stakeholder
  └── Security & Performance Testing

FASE 4: DEPLOYMENT (Minggu 14)
  ├── Setup produksi
  ├── Migrasi data
  └── Go-live

FASE 5: PELATIHAN & PENDAMPINGAN (Minggu 15-16)
  ├── Sesi pelatihan
  └── Pendampingan pasca go-live
```

### 5.3 Standar Pengembangan

- Mengikuti **PSR-12** PHP Coding Standard
- Menggunakan **Git** untuk version control dengan branching strategy (main/develop/feature)
- **Code review** wajib untuk setiap fitur sebelum merge
- Setiap bug fix/feature disertai **test case**

---

## 6. KELUARAN YANG DIHARAPKAN

| No | Keluaran | Format | Tenggat |
|----|---------|--------|---------|
| O-01 | Kode sumber sistem yang telah disempurnakan | Git repository | Minggu 13 |
| O-02 | Database schema final beserta migration scripts | SQL + PHP Migrations | Minggu 13 |
| O-03 | Panduan Pengguna per peran | PDF/Word | Minggu 15 |
| O-04 | Panduan Administrator Sistem | PDF/Word | Minggu 15 |
| O-05 | Dokumentasi Teknis (ERD, Arsitektur, API) | PDF/Markdown | Minggu 15 |
| O-06 | Laporan Pengujian (UAT, Test Result) | PDF | Minggu 14 |
| O-07 | Berita Acara Serah Terima Sistem | Dokumen resmi | Minggu 16 |
| O-08 | Laporan Pelaksanaan Pelatihan | PDF | Minggu 16 |

---

## 7. JANGKA WAKTU PELAKSANAAN

**Total Durasi:** 16 Minggu (4 Bulan)

| Fase | Kegiatan | Durasi | Minggu |
|------|---------|--------|--------|
| 1 | Analisis & Perancangan | 2 minggu | 1 – 2 |
| 2 | Pengembangan (4 Sprint) | 8 minggu | 3 – 10 |
| 3 | Pengujian | 3 minggu | 11 – 13 |
| 4 | Deployment | 1 minggu | 14 |
| 5 | Pelatihan & Pendampingan | 2 minggu | 15 – 16 |

### 7.1 Milestone Utama

| Milestone | Tanggal Target |
|-----------|---------------|
| Kick-off Meeting | Minggu 1 |
| Selesai Analisis & Rancangan | Minggu 2 |
| Sprint 1 Demo | Minggu 5 |
| Sprint 2 Demo | Minggu 7 |
| Sprint 3 Demo | Minggu 9 |
| Sprint 4 Demo (Feature Complete) | Minggu 10 |
| UAT Selesai | Minggu 13 |
| Go-Live | Minggu 14 |
| Selesai Pelatihan | Minggu 15 |
| Serah Terima | Minggu 16 |

---

## 8. KEBUTUHAN SUMBER DAYA

### 8.1 Tim Pengembang

| Posisi | Jumlah | Peran & Tanggung Jawab |
|--------|--------|------------------------|
| Project Manager | 1 | Koordinasi, progress monitoring, pelaporan |
| Lead Developer (Backend) | 1 | Arsitektur, pengembangan backend utama, code review |
| Developer Backend | 1 | Implementasi fitur, unit testing |
| Developer Frontend | 1 | UI/UX, implementasi tampilan, responsivitas |
| Quality Assurance (QA) | 1 | Test planning, pelaksanaan pengujian, laporan bug |
| Database Administrator | 1 (paruh waktu) | Desain schema, optimasi query, backup |
| Technical Writer | 1 (paruh waktu) | Dokumentasi pengguna dan teknis |

### 8.2 Tim Pengguna (dari Inspektorat)

| Peran | Tanggung Jawab |
|-------|---------------|
| Project Owner / Sponsor | Keputusan bisnis dan persetujuan anggaran |
| Koordinator UAT | Koordinasi pengujian pengguna, kumpulkan feedback |
| Key Users (3–5 orang) | Pelaksana UAT, early adopters, agent of change |
| Administrator Sistem Inspektorat | Calon admin sistem, ikut pelatihan intensif |

### 8.3 Infrastruktur

**Lingkungan Pengembangan:**
- Server lokal / cloud development environment
- Git repository (GitHub/GitLab)
- Database MySQL 5.7+
- PHP 8.1+

**Lingkungan Produksi:**
- Server VPS/Cloud dengan spesifikasi minimal:
  - CPU: 4 core
  - RAM: 8 GB
  - Storage: 100 GB SSD
  - OS: Ubuntu 22.04 LTS
- Domain dan SSL certificate
- Database server (MySQL 8.0)
- Email SMTP server
- Backup storage (minimal 30 hari retensi)

---

## 9. SPESIFIKASI TEKNIS

### 9.1 Technology Stack

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Framework Backend | CodeIgniter 4 | 4.x (dev-develop) |
| Language | PHP | 8.1+ |
| Database | MySQL / MariaDB | 5.7+ / 10.4+ |
| Frontend | Bootstrap, jQuery | Bootstrap 5, jQuery 3.x |
| Word Processing | PHPWord | 1.3+ |
| Email | PHPMailer | 6.9+ |
| AI Integration | Google Gemini API | Latest |
| Testing | PHPUnit | 10.5+ |
| Version Control | Git | - |

### 9.2 Browser Compatibility

- Google Chrome 110+
- Mozilla Firefox 110+
- Microsoft Edge 110+
- Safari 15+ (MacOS/iOS)
- Mobile browser (Chrome/Safari mobile)

### 9.3 Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────┐
│                    LOAD BALANCER / NGINX                  │
└─────────────────────┬───────────────────────────────────┘
                       │
┌─────────────────────▼───────────────────────────────────┐
│                   WEB SERVER (Apache/Nginx)               │
│                   PHP 8.1 + CodeIgniter 4                 │
│   ┌───────────┐  ┌──────────────┐  ┌──────────────────┐  │
│   │   Auth    │  │   Admin      │  │  Auditi Portal   │  │
│   │  Module   │  │   Modules    │  │    Module        │  │
│   └───────────┘  └──────────────┘  └──────────────────┘  │
└─────────────────────┬───────────────────────────────────┘
                       │
┌─────────────────────▼───────────────────────────────────┐
│                  DATABASE (MySQL 8.0)                     │
│   RBAC | Master | PKPT | SPT | KM | KKA | NHP | TL      │
└──────────────────────────────────────────────────────────┘
         │                    │                   │
┌────────▼──────┐   ┌─────────▼──────┐  ┌────────▼──────┐
│  Email/SMTP   │   │  WhatsApp API   │  │  Gemini API   │
│  (PHPMailer)  │   │  (WaService)    │  │  (AI Review)  │
└───────────────┘   └────────────────┘  └───────────────┘
```

### 9.4 Struktur Database (Tabel Utama)

| Kelompok | Tabel-Tabel |
|----------|-------------|
| Core RBAC | users, roles, permissions, user_roles, role_permissions, menus |
| Infrastruktur | activity_logs, notifications, app_settings, login_services, password_resets |
| Master Data | irban, entitas, sdm, kode_temuan, hari_libur |
| Perencanaan | pkpt_setting, pkpt, pkpt_kegiatan, pkpt_tim |
| Penugasan | spt, spt_tim, spt_approval, spt_km |
| Audit Program | pka, pka_assignment, pka_template |
| Kendali Mutu | spt_km_1, spt_anggaran_waktu, spt_km_3, spt_km_4, spt_km_5, spt_km_5b, spt_km_10, spt_km_11 |
| Kertas Kerja | kka, kka_ikhtisar, kka_simpulan, kka_rekomendasi |
| Temuan & Hasil | temuan, rekomendasi, nhp, nhp_item, nhp_item_dokumen |
| Tindak Lanjut | tindak_lanjut, tindak_lanjut_dokumen |
| AI Reviu | reviu |

---

## 10. STANDAR DAN KUALITAS

### 10.1 Standar Kode

- Mengikuti PSR-12 PHP Coding Standard
- Komentar kode dalam Bahasa Indonesia untuk business logic
- Penggunaan type hints PHP 8.1 (named arguments, enums, fibers where applicable)
- Penggunaan CodeIgniter 4 Query Builder — tidak ada raw SQL query tanpa prepared statements

### 10.2 Standar Pengujian

- **Unit Test:** Menggunakan PHPUnit 10.x
- **Test Coverage Minimum:** 80% untuk model, service, dan helper utama
- **UAT:** Harus disaksikan dan ditandatangani oleh Key Users
- **Regresi Test:** Setiap perubahan kode harus lolos regresi test sebelumnya

### 10.3 Standar Keamanan

- Mengikuti OWASP Top 10 Web Application Security Risks
- Tidak ada kredensial/API key yang di-hardcode di source code
- Semua form dilindungi CSRF token
- Input sanitization di semua titik masuk data
- Output encoding untuk mencegah XSS
- Penggunaan Prepared Statements untuk seluruh operasi database

### 10.4 Standar Dokumentasi

- Panduan pengguna menggunakan bahasa yang mudah dipahami oleh non-teknis
- Setiap endpoint/route yang baru didokumentasikan
- Perubahan database schema didokumentasikan dalam migration file
- Changelog diperbarui di setiap sprint

---

## 11. RISIKO DAN PENANGANANNYA

| No | Risiko | Dampak | Kemungkinan | Rencana Mitigasi |
|----|--------|--------|-------------|-----------------|
| 1 | Perubahan kebutuhan di tengah pengembangan | Tinggi | Sedang | Sprint review dua mingguan, change request terdokumentasi |
| 2 | Keterlambatan penyediaan infrastruktur produksi | Sedang | Rendah | Mulai persiapan server di minggu ke-8 |
| 3 | API pihak ketiga tidak tersedia (Gemini) | Sedang | Rendah | Fitur AI bersifat opsional, ada fallback manual |
| 4 | Data master tidak akurat saat migrasi | Tinggi | Sedang | Validasi data sebelum impor, uji di staging terlebih dahulu |
| 5 | Resistensi pengguna | Tinggi | Sedang | Libatkan key users dari fase analisis, pelatihan intensif |
| 6 | Kegagalan server saat go-live | Tinggi | Rendah | Rollback plan, backup sebelum migrasi, monitoring aktif |
| 7 | Kerentanan keamanan terlewat | Tinggi | Rendah | Security review terpisah oleh QA, penetration test |

---

## 12. PEMBIAYAAN

### 12.1 Komponen Biaya

| No | Komponen | Keterangan |
|----|---------|------------|
| 1 | Honorarium Tim Pengembang | PM, Developer (2), Frontend, QA, DBA, Technical Writer |
| 2 | Infrastruktur Produksi | VPS/Cloud server, domain, SSL (12 bulan) |
| 3 | Lisensi dan Tools | Tidak diperlukan (semua open-source) |
| 4 | Pelatihan | Konsumsi, materi pelatihan, ATK |
| 5 | Administrasi & Laporan | Cetak dokumen, penjilidan |
| 6 | Biaya tak terduga | Maksimum 10% dari total biaya |

### 12.2 Sumber Pembiayaan

Pembiayaan dibebankan pada Anggaran Pendapatan dan Belanja Daerah (APBD) tahun anggaran berjalan, pada program Inspektorat — Kegiatan Peningkatan Kapasitas dan Teknologi Pengawasan.

---

## 13. PELAPORAN DAN KOMUNIKASI

### 13.1 Jenis Laporan

| Laporan | Frekuensi | Penerima |
|---------|-----------|---------|
| Laporan Progress Mingguan | Setiap Senin | Project Owner, PM |
| Sprint Review Report | Setiap 2 minggu | Semua stakeholder |
| Bug/Issue Report | Real-time via issue tracker | Tim Pengembang |
| Laporan UAT | Setelah UAT selesai | Project Owner |
| Laporan Akhir | Saat serah terima | Pimpinan Inspektorat |

### 13.2 Media Komunikasi

- **Issue Tracker:** GitHub Issues / Jira
- **Komunikasi Tim:** WhatsApp Group / Teams
- **Penyimpanan Dokumen:** Google Drive / SharePoint
- **Rapat Rutin:** Zoom/Teams meeting setiap 2 minggu

### 13.3 Eskalasi

1. **Level 1:** Developer → Lead Developer (isu teknis, < 24 jam)
2. **Level 2:** Lead Developer → PM (blocker, < 48 jam)
3. **Level 3:** PM → Project Owner (keputusan bisnis, < 72 jam)

---

## 14. KETENTUAN LAIN-LAIN

### 14.1 Hak Kekayaan Intelektual

Seluruh kode sumber yang dikembangkan dalam proyek ini merupakan milik Inspektorat selaku pengguna/klien. Tim pengembang tidak berhak menggunakan atau mendistribusikan kode tersebut tanpa izin tertulis.

### 14.2 Kerahasiaan Data

Seluruh data yang diakses selama pengembangan (data SDM, data audit, data entitas) bersifat rahasia dan tidak boleh dibawa keluar dari lingkup proyek. Tim pengembang wajib menandatangani perjanjian kerahasiaan (NDA) sebelum memulai pekerjaan.

### 14.3 Garansi Pasca Implementasi

Tim pengembang wajib memberikan garansi **3 bulan** pasca go-live, meliputi:
- Perbaikan bug yang ditemukan pasca go-live tanpa biaya tambahan
- Dukungan teknis via WhatsApp/email pada jam kerja
- Maksimum 2 sesi remote support per bulan

### 14.4 Kode Sumber dan Versi

Kode sumber dikelola dengan Git dan diserahkan kepada Inspektorat beserta:
- Repository lengkap dengan history
- Petunjuk setup environment pengembangan
- Dokumentasi deployment

### 14.5 Perubahan Ruang Lingkup

Perubahan ruang lingkup (scope change) yang signifikan harus:
1. Didokumentasikan dalam Change Request Form
2. Disetujui oleh Project Owner
3. Dievaluasi dampaknya terhadap waktu dan biaya
4. Ditambahkan ke dalam addendum KAK ini

---

*Kerangka Acuan Kerja ini disusun sebagai acuan pelaksanaan pekerjaan pengembangan SIMPAWAN dan dapat diperbarui atas persetujuan bersama semua pihak yang terlibat.*

---

**Menyetujui:**

| Jabatan | Nama | Tanda Tangan | Tanggal |
|---------|------|--------------|---------|
| Inspektur (Project Sponsor) | | | |
| Pejabat Pengadaan / PPK | | | |
| Lead Developer | | | |
