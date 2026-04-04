<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Brand Configuration
 * -------------------
 * Semua konfigurasi branding/identitas aplikasi terpusat di sini.
 * Sesuaikan via file .env agar starter kit ini bisa dipakai ulang
 * tanpa harus mengubah kode di views.
 *
 * Contoh .env:
 *   brand.appName    = "Nama Aplikasi"
 *   brand.appVersion = "1.0"
 *   brand.orgName    = "Nama Instansi / Perusahaan"
 *   brand.orgShort   = "Nama Singkat"
 *   brand.orgWebsite = "https://example.com"
 *   brand.appTagline = "Tagline aplikasi"
 */
class Brand extends BaseConfig
{
    /** Nama lengkap aplikasi */
    public string $appName = 'RBAC Starter Kit';

    /** Versi aplikasi */
    public string $appVersion = '1.0';

    /** Nama organisasi / instansi lengkap */
    public string $orgName = 'Nama Instansi';

    /** Nama singkat organisasi (untuk sidebar, header) */
    public string $orgShort = 'Instansi';

    /** Website organisasi */
    public string $orgWebsite = '#';

    /** Tagline / deskripsi singkat aplikasi */
    public string $appTagline = 'Sistem Informasi berbasis CodeIgniter 4';
}
