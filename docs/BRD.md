# BUSINESS REQUIREMENTS DOCUMENT (BRD)
## Sistem Informasi Manajemen Pengawasan (SIMPAWAN)
### Berbasis CI4 RBAC Starter Kit

---

**Versi:** 1.0  
**Tanggal:** 27 April 2026  
**Disusun Oleh:** Tim Pengembang — Inspektorat  
**Status:** Draft  

---

## DAFTAR ISI

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Latar Belakang](#2-latar-belakang)
3. [Tujuan Bisnis](#3-tujuan-bisnis)
4. [Ruang Lingkup](#4-ruang-lingkup)
5. [Pemangku Kepentingan (Stakeholder)](#5-pemangku-kepentingan-stakeholder)
6. [Kebutuhan Bisnis Saat Ini](#6-kebutuhan-bisnis-saat-ini)
7. [Kebutuhan Fungsional](#7-kebutuhan-fungsional)
8. [Kebutuhan Non-Fungsional](#8-kebutuhan-non-fungsional)
9. [Asumsi dan Batasan](#9-asumsi-dan-batasan)
10. [Risiko Bisnis](#10-risiko-bisnis)
11. [Kriteria Keberhasilan](#11-kriteria-keberhasilan)
12. [Glosarium](#12-glosarium)

---

## 1. RINGKASAN EKSEKUTIF

Dokumen ini merupakan Business Requirements Document (BRD) untuk pengembangan **Sistem Informasi Manajemen Pengawasan (SIMPAWAN)** — sebuah platform berbasis web yang dirancang khusus untuk mendukung seluruh siklus kegiatan pengawasan internal pada Inspektorat/Badan Pengawas pemerintah daerah di Indonesia.

Sistem ini dibangun di atas kerangka **CodeIgniter 4** dengan arsitektur **Role-Based Access Control (RBAC)** yang mengimplementasikan standar **Kendali Mutu (KM)** audit pemerintahan Indonesia, mulai dari perencanaan tahunan hingga pemantauan tindak lanjut hasil pengawasan.

---

## 2. LATAR BELAKANG

### 2.1 Kondisi Saat Ini

Kegiatan pengawasan di lingkungan Inspektorat/Badan Pengawas saat ini masih banyak dilakukan secara manual atau dengan alat bantu yang tidak terintegrasi (spreadsheet, dokumen Word/PDF terpisah). Kondisi ini menimbulkan berbagai permasalahan:

- **Duplikasi data** — Data auditor, entitas auditi, dan temuan dicatat di berbagai tempat secara terpisah
- **Keterlambatan pelaporan** — Kompilasi hasil audit memerlukan waktu lama karena data tidak terpusat
- **Minimnya keterlacakan** — Sulit melacak status tindak lanjut rekomendasi hasil audit
- **Tidak ada audit trail** — Perubahan data tidak tercatat dengan baik, menyulitkan pertanggungjawaban
- **Hambatan kolaborasi** — Tim auditor tidak dapat berkolaborasi secara real-time pada dokumen yang sama
- **Inkonsistensi format** — Kertas kerja, NHP, dan laporan tidak memiliki format yang seragam

### 2.2 Regulasi yang Mendasari

- Peraturan Pemerintah No. 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah (SPIP)
- Peraturan Menteri Dalam Negeri No. 23 Tahun 2007 tentang Pedoman Tata Cara Pengawasan Atas Penyelenggaraan Pemerintahan Daerah
- Standar Audit Intern Pemerintah Indonesia (SAIPI) — AAIPI 2013
- Pedoman Kendali Mutu Pengawasan (KM-1 s.d. KM-11)

### 2.3 Urgensi Digitalisasi

Tekanan digitalisasi layanan publik (Perpres No. 95 Tahun 2018 tentang Sistem Pemerintahan Berbasis Elektronik / SPBE) mengharuskan setiap instansi pemerintah — termasuk fungsi pengawasan internal — untuk memanfaatkan teknologi informasi dalam operasionalnya.

---

## 3. TUJUAN BISNIS

| No | Tujuan | Indikator Keberhasilan |
|----|--------|------------------------|
| B-01 | Mengintegrasikan seluruh siklus pengawasan dalam satu platform digital | 100% kegiatan pengawasan tercatat di sistem |
| B-02 | Meningkatkan efisiensi penyusunan dokumen pengawasan | Pengurangan waktu penyusunan dokumen ≥ 40% |
| B-03 | Meningkatkan akuntabilitas dan keterlacakan hasil pengawasan | 100% rekomendasi hasil audit terlacak status tindak lanjutnya |
| B-04 | Menyediakan dashboard pemantauan real-time bagi pimpinan | Dashboard menampilkan KPI pengawasan secara real-time |
| B-05 | Memfasilitasi komunikasi antara auditor dan auditi secara formal | 100% NHP tersampaikan melalui sistem |
| B-06 | Meningkatkan kualitas kertas kerja audit melalui standarisasi | Semua KKA menggunakan format KM yang standar |
| B-07 | Mendukung perencanaan pengawasan berbasis risiko | PKPT tersusun dengan referensi data historis dan profil risiko |

---

## 4. RUANG LINGKUP

### 4.1 Dalam Ruang Lingkup

1. **Manajemen Pengguna & Hak Akses (RBAC)**
   - Manajemen pengguna, peran, dan izin akses
   - Hierarki menu dinamis berdasarkan peran
   - Activity log untuk audit trail sistem

2. **Data Master**
   - Inspektur/Irban (unit pengawasan)
   - Sumber Daya Manusia (SDM/auditor)
   - Entitas Auditi (organisasi yang diaudit)
   - Kode Temuan & Kalender Hari Kerja

3. **Perencanaan Pengawasan Tahunan (PKPT)**
   - Penyusunan dan persetujuan PKPT
   - Alokasi tim dan anggaran hari kerja
   - Monitoring ketersediaan SDM

4. **Surat Perintah Tugas (SPT)**
   - Pembuatan SPT dari PKPT maupun non-PKPT
   - Alur persetujuan multi-tahap
   - Penugasan tim dengan peran spesifik
   - Cetak dokumen SPT (format Word)

5. **Program Kegiatan Audit (PKA)**
   - Penyusunan prosedur audit per SPT
   - Penugasan prosedur ke anggota tim
   - Perpustakaan template PKA

6. **Kendali Mutu (KM-1 s.d. KM-11)**
   - Implementasi seluruh tahap kendali mutu
   - Alur persetujuan antar tahap

7. **Kertas Kerja Audit (KKA)**
   - Pembuatan otomatis per anggota tim
   - Ikhtisar, Simpulan (KKSA), dan Rekomendasi
   - Alur review Ketua Tim

8. **Notisi Hasil Pemeriksaan (NHP)**
   - Penyusunan NHP dari simpulan KKA
   - Pengiriman ke entitas auditi
   - Pencatatan tanggapan

9. **Tindak Lanjut Hasil Pengawasan**
   - Pemantauan dan verifikasi tindak lanjut
   - Upload dokumen bukti

10. **Portal Auditi**
    - Tampilan NHP & tindak lanjut untuk entitas yang diaudit
    - Upload dokumen tanggapan dan bukti

11. **Reviu Dokumen Berbasis AI**
    - Analisis dokumen menggunakan Google Gemini
    - Output CKSA otomatis

### 4.2 Di Luar Ruang Lingkup (Versi 1.0)

- Integrasi dengan sistem keuangan daerah (SIKD/SIPKD)
- Integrasi dengan e-Procurement (SIRUP, SiRUP)
- Manajemen kepegawaian terintegrasi (SIASN/BKN)
- Laporan hasil audit final (LHA) dalam format resmi
- Sistem scoring/penilaian risiko otomatis
- Aplikasi mobile (iOS/Android native)

---

## 5. PEMANGKU KEPENTINGAN (STAKEHOLDER)

### 5.1 Stakeholder Internal

| Peran | Tanggung Jawab dalam Sistem | Level Akses |
|-------|-----------------------------|-------------|
| **Inspektur** | Menyetujui PKPT, memantau dashboard | Dashboard + Persetujuan tertinggi |
| **Sekretaris Inspektorat** | Koordinasi administrasi, terbit SPT | Persetujuan SPT final |
| **Inspektur Pembantu (Irban)** | Menyetujui SPT, memantau tim | Persetujuan SPT tingkat Irban |
| **Pengendali Teknis (Dalnis)** | Review PKA, review KKA, kendali mutu | KM & Review teknis |
| **Ketua Tim (KT)** | Koordinasi tim, review KKA anggota | KKA review |
| **Anggota Tim (AT)** | Menyusun KKA, input temuan | KKA input |
| **Penanggung Jawab (PJ)** | Memantau dan menyetujui keseluruhan | Pengawasan umum |
| **Administrator Sistem** | Konfigurasi sistem, kelola pengguna | Full admin access |

### 5.2 Stakeholder Eksternal

| Peran | Tanggung Jawab dalam Sistem | Level Akses |
|-------|-----------------------------|-------------|
| **Auditi (Entitas yang Diaudit)** | Melihat NHP, merespons temuan, upload bukti TL | Portal Auditi (terbatas) |

---

## 6. KEBUTUHAN BISNIS SAAT INI

### 6.1 Proses Bisnis Utama

**Siklus Pengawasan (Audit Cycle):**
```
PKPT (Perencanaan) 
  → SPT (Penugasan) 
    → PKA (Program Audit) 
      → KM (Kendali Mutu: KM-1 s.d. KM-11) 
        → KKA (Kertas Kerja) 
          → NHP (Notisi Hasil) 
            → TL (Tindak Lanjut)
```

### 6.2 Pain Points yang Harus Diselesaikan

| ID | Pain Point | Prioritas |
|----|-----------|-----------|
| PP-01 | Data auditor dan alokasi jam kerja tidak terkonsolidasi | Tinggi |
| PP-02 | Status persetujuan SPT tidak terlacak secara real-time | Tinggi |
| PP-03 | Format kertas kerja tidak seragam antar anggota tim | Tinggi |
| PP-04 | NHP harus dikirim manual via surat fisik/email | Tinggi |
| PP-05 | Tindak lanjut rekomendasi tidak terpantau secara sistematis | Tinggi |
| PP-06 | Tidak ada rekap otomatis hari kerja vs. hari libur | Sedang |
| PP-07 | Auditi tidak memiliki akses real-time atas hasil audit | Sedang |
| PP-08 | Reviu dokumen masih manual dan memakan waktu | Rendah |

---

## 7. KEBUTUHAN FUNGSIONAL

### 7.1 Modul Manajemen Sistem (SYS)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| SYS-01 | Sistem harus menyediakan login dengan email dan password | Wajib |
| SYS-02 | Sistem harus mendukung reset password melalui email | Wajib |
| SYS-03 | Sistem harus menerapkan rate limiting (maks. 5 percobaan login gagal) | Wajib |
| SYS-04 | Sistem harus mencatat semua aktivitas pengguna (audit trail) | Wajib |
| SYS-05 | Administrator dapat mengelola pengguna, peran, dan izin akses | Wajib |
| SYS-06 | Menu navigasi harus disesuaikan dengan peran pengguna | Wajib |
| SYS-07 | Sistem harus menampilkan notifikasi in-app | Wajib |
| SYS-08 | Administrator dapat mengkonfigurasi branding dan pengaturan aplikasi | Penting |
| SYS-09 | Sistem harus mendukung login melalui layanan eksternal (OAuth) | Opsional |

### 7.2 Modul Data Master (MASTER)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| MASTER-01 | Administrator dapat mengelola data Irban (unit pengawasan) | Wajib |
| MASTER-02 | Administrator dapat mengelola data SDM (auditor) termasuk NIP, jabatan, dan jam tersedia | Wajib |
| MASTER-03 | Administrator dapat mengelola data Entitas Auditi dan menghubungkannya ke akun pengguna | Wajib |
| MASTER-04 | Administrator dapat mengelola kode dan kategori temuan | Wajib |
| MASTER-05 | Administrator dapat mengelola kalender hari libur nasional dan lokal | Wajib |

### 7.3 Modul Perencanaan (PKPT)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| PKPT-01 | Sistem harus mendukung penyusunan PKPT per Irban per tahun | Wajib |
| PKPT-02 | PKPT harus memiliki alur persetujuan (draft → diajukan → disetujui) | Wajib |
| PKPT-03 | Sistem harus memungkinkan perencanaan kegiatan (judul, tujuan, sasaran, area, jenis) | Wajib |
| PKPT-04 | Sistem harus mendukung alokasi tim per kegiatan dengan anggaran hari kerja | Wajib |
| PKPT-05 | Sistem harus menampilkan monitoring ketersediaan hari kerja SDM | Penting |
| PKPT-06 | Sistem harus menghitung hari kerja efektif mempertimbangkan hari libur | Penting |

### 7.4 Modul Penugasan (SPT)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| SPT-01 | Sistem harus mendukung pembuatan SPT dari kegiatan PKPT | Wajib |
| SPT-02 | Sistem harus mendukung pembuatan SPT mandiri (non-PKPT) | Wajib |
| SPT-03 | SPT harus melalui alur persetujuan multi-tahap (draft → Irban → Evlap → Sekretaris → Terbit) | Wajib |
| SPT-04 | Sistem harus mendukung penugasan tim dengan peran spesifik (KT, AT, Dalnis, PJ) | Wajib |
| SPT-05 | Sistem harus dapat mengekspor SPT ke format dokumen Word | Wajib |
| SPT-06 | Pengguna hanya dapat melihat SPT sesuai keterlibatannya, kecuali admin | Wajib |

### 7.5 Modul Program Audit (PKA)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| PKA-01 | Sistem harus mendukung penyusunan prosedur audit per SPT | Wajib |
| PKA-02 | Prosedur audit harus dapat dikelompokkan berdasarkan fase (Persiapan, Pelaksanaan, Penyelesaian) | Wajib |
| PKA-03 | Prosedur harus dapat ditugaskan ke anggota tim tertentu | Wajib |
| PKA-04 | Sistem harus menyediakan perpustakaan template PKA berdasarkan jenis pengawasan | Penting |
| PKA-05 | Template PKA harus dapat diterapkan langsung ke SPT yang sedang berjalan | Penting |

### 7.6 Modul Kendali Mutu (KM)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| KM-01 | Sistem harus mengimplementasikan KM-1 (Peta Pengawasan) | Wajib |
| KM-02 | Sistem harus mengimplementasikan KM-2 (Anggaran Waktu) dengan kalkulasi otomatis | Wajib |
| KM-03 | Sistem harus mengimplementasikan KM-3 (Dokumen SPT, auto-fill) | Wajib |
| KM-04 | Sistem harus mengimplementasikan KM-4 (Lembar Perencanaan) | Wajib |
| KM-05 | Sistem harus mengimplementasikan KM-5 (Reviu PKA oleh Dalnis) | Wajib |
| KM-06 | Sistem harus mengimplementasikan KM-5b (Entry Meeting) | Wajib |
| KM-07 | Sistem harus mengimplementasikan KM-10 (Exit Meeting) | Wajib |
| KM-08 | Sistem harus mengimplementasikan KM-11 (Reviu Laporan) | Wajib |
| KM-09 | Sistem harus memvalidasi bahwa tahap KM sebelumnya selesai sebelum lanjut ke tahap berikutnya | Penting |

### 7.7 Modul Kertas Kerja Audit (KKA)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| KKA-01 | Sistem harus membuat KKA secara otomatis untuk setiap AT setelah KM-5 disetujui | Wajib |
| KKA-02 | KKA harus memiliki seksi Ikhtisar (prosedur yang dilaksanakan & simpulan per prosedur) | Wajib |
| KKA-03 | KKA harus memiliki seksi Simpulan dengan format KKSA (Kondisi-Kriteria-Sebab-Akibat) | Wajib |
| KKA-04 | KKA harus memiliki seksi Rekomendasi yang terhubung ke simpulan | Wajib |
| KKA-05 | AT harus dapat mengajukan KKA ke KT untuk direviu | Wajib |
| KKA-06 | KT harus dapat menyetujui atau menolak KKA dengan catatan | Wajib |
| KKA-07 | KT harus dapat melihat tampilan kompilasi seluruh KKA tim | Penting |
| KKA-08 | Sistem harus mendukung cetak KKA per AT | Penting |
| KKA-09 | Realisasi hari kerja di KM-2 harus tersinkronisasi otomatis dari KKA | Penting |

### 7.8 Modul Temuan & NHP

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| NHP-01 | Sistem harus mendukung pencatatan temuan per SPT | Wajib |
| NHP-02 | Sistem harus mendukung penyusunan NHP dari simpulan KKA | Wajib |
| NHP-03 | NHP harus dapat dikirimkan ke entitas auditi melalui sistem | Wajib |
| NHP-04 | Auditi harus dapat memberikan tanggapan (sesuai/tidak sesuai) per item NHP | Wajib |
| NHP-05 | Auditi harus dapat mengupload dokumen pendukung tanggapan | Wajib |
| NHP-06 | Sistem harus menampilkan matriks status tanggapan NHP | Penting |

### 7.9 Modul Tindak Lanjut (TL)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| TL-01 | Sistem harus memungkinkan pemantauan tindak lanjut per rekomendasi | Wajib |
| TL-02 | Auditi harus dapat mengupload bukti tindak lanjut | Wajib |
| TL-03 | Auditor harus dapat melakukan verifikasi tindak lanjut dengan status selesai/belum | Wajib |
| TL-04 | Sistem harus menampilkan dashboard status tindak lanjut | Penting |

### 7.10 Modul Reviu Dokumen AI (REVIU)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| REVIU-01 | Sistem harus mendukung upload dokumen untuk direviu AI | Opsional |
| REVIU-02 | Pengguna harus dapat memilih kriteria reviu dari master data | Opsional |
| REVIU-03 | Sistem harus mengintegrasikan Google Gemini API untuk analisis dokumen | Opsional |
| REVIU-04 | Hasil reviu harus dapat diekspor ke PDF | Opsional |

---

## 8. KEBUTUHAN NON-FUNGSIONAL

### 8.1 Performa

| ID | Kebutuhan |
|----|-----------|
| NF-01 | Halaman utama harus termuat dalam < 3 detik pada koneksi 10 Mbps |
| NF-02 | Query DataTables server-side harus merespons dalam < 2 detik untuk 10.000 baris data |
| NF-03 | Sistem harus mampu menangani 50 pengguna simultan tanpa degradasi performa signifikan |

### 8.2 Keamanan

| ID | Kebutuhan |
|----|-----------|
| NF-04 | Semua kata sandi harus disimpan dengan hashing bcrypt (PHP PASSWORD_DEFAULT) |
| NF-05 | Sistem harus menerapkan CSRF protection pada semua form |
| NF-06 | Sistem harus mencegah SQL Injection melalui query builder/prepared statements |
| NF-07 | Sistem harus mencegah XSS melalui output escaping |
| NF-08 | API key eksternal (Gemini, dsb.) harus disimpan di variabel lingkungan (.env), tidak hardcoded |
| NF-09 | File upload harus divalidasi tipe dan ukurannya |
| NF-10 | Sesi pengguna harus berakhir setelah 8 jam tidak aktif |

### 8.3 Ketersediaan & Reliabilitas

| ID | Kebutuhan |
|----|-----------|
| NF-11 | Uptime sistem minimal 99% pada jam kerja (07.00–17.00 WIB) |
| NF-12 | Data harus dibackup harian secara otomatis |
| NF-13 | Sistem harus memiliki mekanisme penanganan error yang graceful |

### 8.4 Skalabilitas

| ID | Kebutuhan |
|----|-----------|
| NF-14 | Arsitektur harus mendukung pertumbuhan data hingga 5 tahun ke depan |
| NF-15 | Sistem harus dapat dikonfigurasi untuk organisasi yang berbeda (multi-instansi potensial) |

### 8.5 Usabilitas

| ID | Kebutuhan |
|----|-----------|
| NF-16 | Antarmuka harus responsif (mobile-friendly) |
| NF-17 | Sistem harus berbahasa Indonesia sepenuhnya |
| NF-18 | Navigasi harus intuitif dengan breadcrumb dan hierarki menu yang jelas |

### 8.6 Pemeliharaan

| ID | Kebutuhan |
|----|-----------|
| NF-19 | Kode harus mengikuti standar PSR-12 (PHP Coding Standards) |
| NF-20 | Sistem harus memiliki mekanisme purge activity log otomatis (default: 90 hari) |
| NF-21 | Konfigurasi aplikasi harus dapat diubah melalui UI admin, bukan hanya file konfigurasi |

---

## 9. ASUMSI DAN BATASAN

### 9.1 Asumsi

- Semua pengguna memiliki akses internet yang stabil
- Entitas memiliki minimal satu penanggung jawab yang ditunjuk sebagai kontak sistem
- Data SDM dikelola oleh administrator sistem, bukan diimpor dari SIASN/BKN
- Struktur kendali mutu (KM-1 s.d. KM-11) mengikuti pedoman AAIPI yang berlaku

### 9.2 Batasan

- Sistem dibangun untuk satu instansi Inspektorat (single-tenant) pada versi 1.0
- Laporan Hasil Audit (LHA) final belum termasuk dalam versi 1.0
- Tidak ada integrasi real-time dengan sistem keuangan daerah
- Fitur real-time collaboration (WebSocket) belum tersedia

---

## 10. RISIKO BISNIS

| ID | Risiko | Dampak | Kemungkinan | Mitigasi |
|----|--------|--------|-------------|---------|
| R-01 | Resistensi pengguna terhadap sistem baru | Tinggi | Sedang | Pelatihan intensif + pendampingan implementasi |
| R-02 | Data master (SDM, entitas) tidak akurat | Tinggi | Sedang | Validasi data sebelum go-live + mekanisme koreksi |
| R-03 | Gangguan jaringan/server menyebabkan data hilang | Tinggi | Rendah | Backup harian + UPS + hosting yang andal |
| R-04 | Perubahan regulasi kendali mutu | Sedang | Rendah | Modular design memungkinkan adaptasi cepat |
| R-05 | Keamanan data audit yang sensitif | Tinggi | Rendah | Enkripsi, RBAC ketat, audit trail |
| R-06 | Ketergantungan pada API pihak ketiga (Gemini) | Sedang | Sedang | Fitur AI bersifat opsional, fallback ke manual |

---

## 11. KRITERIA KEBERHASILAN

| No | Kriteria | Cara Pengukuran | Target |
|----|---------|-----------------|--------|
| KK-01 | Seluruh kegiatan pengawasan tercatat di sistem | % kegiatan terinput vs. total PKPT | ≥ 95% |
| KK-02 | Waktu penyusunan KKA berkurang | Rata-rata hari dari SPT ke KKA selesai | Turun ≥ 30% |
| KK-03 | Tindak lanjut terpantau | % rekomendasi dengan status terkini | ≥ 90% |
| KK-04 | Kepuasan pengguna | Survei kuesioner pasca implementasi | Skor ≥ 4/5 |
| KK-05 | Sistem berjalan stabil | Insiden downtime jam kerja | < 2 per bulan |
| KK-06 | NHP tersampaikan digital | % NHP dikirim via sistem | ≥ 80% |

---

## 12. GLOSARIUM

| Istilah | Definisi |
|---------|----------|
| **PKPT** | Perencanaan Kegiatan Pengawasan Tahunan — rencana audit tahunan per unit pengawasan |
| **SPT** | Surat Perintah Tugas — dokumen formal penugasan tim audit |
| **PKA** | Program Kegiatan Audit — daftar prosedur audit yang akan dilaksanakan |
| **KKA** | Kertas Kerja Audit — dokumentasi pelaksanaan prosedur audit per anggota tim |
| **KM** | Kendali Mutu — tahapan quality control pengawasan (KM-1 s.d. KM-11) |
| **NHP** | Notisi Hasil Pemeriksaan — pemberitahuan resmi temuan audit kepada auditi |
| **TL** | Tindak Lanjut — tindakan perbaikan yang dilakukan auditi atas rekomendasi audit |
| **KKSA** | Kondisi-Kriteria-Sebab-Akibat — format standar dokumentasi simpulan audit |
| **Irban** | Inspektur Pembantu — kepala unit pengawasan di bawah Inspektur |
| **SDM** | Sumber Daya Manusia — data personel/auditor |
| **AT** | Anggota Tim — anggota tim audit biasa |
| **KT** | Ketua Tim — pemimpin tim audit |
| **Dalnis** | Pengendali Teknis — pengawas teknis pelaksanaan audit |
| **PJ** | Penanggung Jawab — pejabat yang bertanggung jawab atas kegiatan pengawasan |
| **RBAC** | Role-Based Access Control — sistem hak akses berbasis peran |
| **Auditi** | Entitas/organisasi yang menjadi objek pemeriksaan |

---

*Dokumen ini disusun berdasarkan analisis sistem SIMPAWAN versi saat ini dan merupakan acuan kebutuhan bisnis yang bersifat hidup (living document) yang dapat diperbarui sesuai perkembangan kebutuhan organisasi.*
