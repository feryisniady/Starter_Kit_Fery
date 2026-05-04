<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><?= esc($title) ?></h1>
        <p>Ringkasan progress pengawasan tahun <strong><?= $tahun ?></strong></p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <form method="get" style="display:flex;gap:8px;align-items:center">
            <select name="tahun" class="form-control" style="width:100px" onchange="this.form.submit()">
                <?php foreach($tahunList as $t): ?>
                <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- ===== BREADCRUMB ===== -->
<nav style="font-size:13px;color:#94a3b8;margin-bottom:20px">
    <a href="/dashboard" style="color:#64748b">Dashboard</a>
    <span style="margin:0 6px">/</span>
    <span>Laporan</span>
</nav>

<!-- ===== NAVIGASI MODUL LAPORAN ===== -->
<div class="grid-3 mb-4" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">

    <!-- Ikhtisar LHP — Permenpan 42/2011 (paling penting, urutan pertama) -->
    <a href="/admin/laporan/ikhtisar-lhp?tahun=<?= $tahun ?>" class="card" style="text-decoration:none;color:inherit;cursor:pointer;transition:box-shadow .2s;border:2px solid #6366f1" onmouseover="this.style.boxShadow='0 4px 16px rgba(99,102,241,.3)'" onmouseout="this.style.boxShadow=''">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:20px">
            <div style="width:48px;height:48px;border-radius:12px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-table-list" style="font-size:22px;color:#6366f1"></i>
            </div>
            <div>
                <div style="font-weight:700;font-size:15px;color:#4f46e5">Ikhtisar LHP</div>
                <div style="color:#64748b;font-size:13px">Matriks Temuan &amp; TL — Permenpan 42/2011</div>
            </div>
            <i class="fas fa-arrow-right" style="margin-left:auto;color:#6366f1"></i>
        </div>
    </a>

    <a href="/admin/laporan/rekap-spt?tahun=<?= $tahun ?>" class="card" style="text-decoration:none;color:inherit;cursor:pointer;transition:box-shadow .2s" onmouseover="this.style.boxShadow='0 4px 16px rgba(59,130,246,.2)'" onmouseout="this.style.boxShadow=''">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:20px">
            <div style="width:48px;height:48px;border-radius:12px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-file-signature" style="font-size:22px;color:#3b82f6"></i>
            </div>
            <div>
                <div style="font-weight:600;font-size:15px">Rekap SPT</div>
                <div style="color:#64748b;font-size:13px">Status & progress Surat Perintah Tugas</div>
            </div>
            <i class="fas fa-arrow-right" style="margin-left:auto;color:#cbd5e1"></i>
        </div>
    </a>
    <!-- Matriks TL — Executive view (featured, border highlight) -->
    <a href="/admin/laporan/matriks-tl?tahun=<?= $tahun ?>" class="card" style="text-decoration:none;color:inherit;cursor:pointer;transition:box-shadow .2s;border:2px solid #22c55e" onmouseover="this.style.boxShadow='0 4px 16px rgba(34,197,94,.3)'" onmouseout="this.style.boxShadow=''">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:20px">
            <div style="width:48px;height:48px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-chart-bar" style="font-size:22px;color:#16a34a"></i>
            </div>
            <div>
                <div style="font-weight:700;font-size:15px;color:#15803d">Matriks TL</div>
                <div style="color:#64748b;font-size:13px">Ringkasan eksekutif per Irban · OPD · Tahun LHP</div>
            </div>
            <i class="fas fa-arrow-right" style="margin-left:auto;color:#16a34a"></i>
        </div>
    </a>
    <a href="/admin/laporan/rekap-tl?tahun=<?= $tahun ?>" class="card" style="text-decoration:none;color:inherit;cursor:pointer;transition:box-shadow .2s" onmouseover="this.style.boxShadow='0 4px 16px rgba(239,68,68,.2)'" onmouseout="this.style.boxShadow=''">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:20px">
            <div style="width:48px;height:48px;border-radius:12px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-list-check" style="font-size:22px;color:#ef4444"></i>
            </div>
            <div>
                <div style="font-weight:600;font-size:15px">Rekap TL (Detail)</div>
                <div style="color:#64748b;font-size:13px">Daftar rekomendasi & highlight overdue</div>
            </div>
            <i class="fas fa-arrow-right" style="margin-left:auto;color:#cbd5e1"></i>
        </div>
    </a>
    <a href="/admin/tl" class="card" style="text-decoration:none;color:inherit;cursor:pointer;transition:box-shadow .2s" onmouseover="this.style.boxShadow='0 4px 16px rgba(139,92,246,.2)'" onmouseout="this.style.boxShadow=''">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:20px">
            <div style="width:48px;height:48px;border-radius:12px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-magnifying-glass-check" style="font-size:22px;color:#8b5cf6"></i>
            </div>
            <div>
                <div style="font-weight:600;font-size:15px">Verifikasi TL</div>
                <div style="color:#64748b;font-size:13px">Verifikasi tindak lanjut oleh auditor</div>
            </div>
            <i class="fas fa-arrow-right" style="margin-left:auto;color:#cbd5e1"></i>
        </div>
    </a>

    <a href="/admin/laporan/rekap-temuan?tahun=<?= $tahun ?>" class="card" style="text-decoration:none;color:inherit;cursor:pointer;transition:box-shadow .2s" onmouseover="this.style.boxShadow='0 4px 16px rgba(239,68,68,.2)'" onmouseout="this.style.boxShadow=''">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:20px">
            <div style="width:48px;height:48px;border-radius:12px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-flag" style="font-size:22px;color:#ef4444"></i>
            </div>
            <div>
                <div style="font-weight:600;font-size:15px">Rekap Temuan</div>
                <div style="color:#64748b;font-size:13px">Daftar temuan audit lintas SPT & bidang</div>
            </div>
            <i class="fas fa-arrow-right" style="margin-left:auto;color:#cbd5e1"></i>
        </div>
    </a>
</div>

<!-- ===== STAT CARDS PENGAWASAN ===== -->
<h3 style="font-size:14px;font-weight:600;color:#475569;margin:0 0 12px">
    <i class="fas fa-file-signature" style="color:#3b82f6"></i> Surat Perintah Tugas (SPT)
</h3>
<div class="grid-4 mb-4">
    <div class="stat-card" style="border-left:3px solid #3b82f6">
        <div class="stat-icon blue"><i class="fas fa-file-contract"></i></div>
        <div class="stat-info">
            <div class="label">Total SPT</div>
            <div class="value"><?= number_format((int)($sptStats['total'] ?? 0)) ?></div>
            <div class="sub">Tahun <?= $tahun ?></div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #22c55e">
        <div class="stat-icon green"><i class="fas fa-play-circle"></i></div>
        <div class="stat-info">
            <div class="label">Sedang Berjalan</div>
            <div class="value"><?= number_format((int)($sptStats['berjalan'] ?? 0)) ?></div>
            <div class="sub">Status terbit & aktif</div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #f59e0b">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="label">Pending Approval</div>
            <div class="value"><?= number_format((int)($sptStats['pending'] ?? 0)) ?></div>
            <div class="sub">Menunggu persetujuan</div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #64748b">
        <div class="stat-icon" style="background:#f1f5f9;color:#64748b"><i class="fas fa-flag-checkered"></i></div>
        <div class="stat-info">
            <div class="label">Selesai</div>
            <div class="value"><?= number_format((int)($sptStats['selesai'] ?? 0)) ?></div>
            <div class="sub">Sudah melewati tanggal selesai</div>
        </div>
    </div>
</div>

<h3 style="font-size:14px;font-weight:600;color:#475569;margin:0 0 12px">
    <i class="fas fa-file-pen" style="color:#f59e0b"></i> Kertas Kerja Audit (KKA)
</h3>
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-files"></i></div>
        <div class="stat-info">
            <div class="label">Total KKA</div>
            <div class="value"><?= number_format((int)($kkaStats['total'] ?? 0)) ?></div>
            <div class="sub">Dari semua AT tahun ini</div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #f59e0b">
        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <div class="label">Pending Review KT</div>
            <div class="value"><?= number_format((int)($kkaStats['pending_review'] ?? 0)) ?></div>
            <div class="sub">Menunggu Ketua Tim mereview</div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #22c55e">
        <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
        <div class="stat-info">
            <div class="label">Disetujui KT</div>
            <div class="value"><?= number_format((int)($kkaStats['approved'] ?? 0)) ?></div>
            <div class="sub">KKA sudah disetujui</div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #ef4444">
        <div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div>
        <div class="stat-info">
            <div class="label">Dikembalikan KT</div>
            <div class="value"><?= number_format((int)($kkaStats['rejected'] ?? 0)) ?></div>
            <div class="sub">Perlu perbaikan AT</div>
        </div>
    </div>
</div>

<div class="grid-2 mb-4">
    <!-- NHP Stats -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-envelope-open-text" style="color:#8b5cf6"></i> Notisi Hasil Pemeriksaan (NHP)</div>
        </div>
        <div class="card-body">
            <?php
            $totalItem   = (int)($nhpStats['pending_tanggapan'] ?? 0) + (int)($nhpStats['sesuai'] ?? 0) + (int)($nhpStats['tidak_sesuai'] ?? 0);
            $totalNhp    = (int)($nhpStats['total_nhp'] ?? 0);
            $pending     = (int)($nhpStats['pending_tanggapan'] ?? 0);
            $sesuai      = (int)($nhpStats['sesuai'] ?? 0);
            $tidakSesuai = (int)($nhpStats['tidak_sesuai'] ?? 0);
            ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                <div style="text-align:center;padding:12px;background:#f8fafc;border-radius:8px">
                    <div style="font-size:28px;font-weight:700;color:#8b5cf6"><?= $totalNhp ?></div>
                    <div style="font-size:12px;color:#64748b">Total NHP Diterbitkan</div>
                </div>
                <div style="text-align:center;padding:12px;background:#fff7ed;border-radius:8px">
                    <div style="font-size:28px;font-weight:700;color:#f59e0b"><?= $pending ?></div>
                    <div style="font-size:12px;color:#64748b">Item Belum Ditanggapi</div>
                </div>
            </div>
            <?php if($totalItem > 0): ?>
            <div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                    <span style="color:#22c55e">Sesuai: <?= $sesuai ?></span>
                    <span style="color:#ef4444">Tidak Sesuai: <?= $tidakSesuai ?></span>
                    <span style="color:#f59e0b">Pending: <?= $pending ?></span>
                </div>
                <div style="height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden;display:flex">
                    <div style="width:<?= $totalItem ? round($sesuai/$totalItem*100) : 0 ?>%;background:#22c55e"></div>
                    <div style="width:<?= $totalItem ? round($tidakSesuai/$totalItem*100) : 0 ?>%;background:#ef4444"></div>
                    <div style="width:<?= $totalItem ? round($pending/$totalItem*100) : 0 ?>%;background:#f59e0b"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TL Stats -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-list-check" style="color:#ef4444"></i> Tindak Lanjut</div>
            <?php if((int)($tlStats['overdue'] ?? 0) > 0): ?>
            <a href="/admin/laporan/rekap-tl?tahun=<?= $tahun ?>&filter=overdue" class="btn btn-sm" style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;font-size:12px">
                <i class="fas fa-triangle-exclamation"></i> <?= $tlStats['overdue'] ?> Overdue
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php
            $totalTl   = (int)($tlStats['total_rekomendasi'] ?? 0);
            $tlSelesai = (int)($tlStats['selesai'] ?? 0);
            $tlProses  = (int)($tlStats['proses'] ?? 0);
            $tlBelum   = (int)($tlStats['belum'] ?? 0);
            $tlOverdue = (int)($tlStats['overdue'] ?? 0);
            ?>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px">
                <div style="text-align:center;padding:10px;background:#f0fdf4;border-radius:8px">
                    <div style="font-size:24px;font-weight:700;color:#22c55e"><?= $tlSelesai ?></div>
                    <div style="font-size:11px;color:#64748b">Selesai</div>
                </div>
                <div style="text-align:center;padding:10px;background:#fff7ed;border-radius:8px">
                    <div style="font-size:24px;font-weight:700;color:#f59e0b"><?= $tlProses ?></div>
                    <div style="font-size:11px;color:#64748b">Proses</div>
                </div>
                <div style="text-align:center;padding:10px;background:#fef2f2;border-radius:8px">
                    <div style="font-size:24px;font-weight:700;color:#ef4444"><?= $tlBelum ?></div>
                    <div style="font-size:11px;color:#64748b">Belum</div>
                </div>
            </div>
            <?php if($totalTl > 0): ?>
            <div style="margin-bottom:8px">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                    <span>Progress Penyelesaian TL</span>
                    <span style="font-weight:600"><?= round($tlSelesai/$totalTl*100) ?>%</span>
                </div>
                <div style="height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden">
                    <div style="width:<?= round($tlSelesai/$totalTl*100) ?>%;background:#22c55e;height:100%"></div>
                </div>
            </div>
            <?php if($tlOverdue > 0): ?>
            <div style="padding:8px 12px;background:#fef2f2;border-radius:6px;display:flex;align-items:center;gap:8px;font-size:13px;color:#ef4444">
                <i class="fas fa-triangle-exclamation"></i>
                <strong><?= $tlOverdue ?> rekomendasi melewati batas waktu!</strong>
                <a href="/admin/laporan/rekap-tl?tahun=<?= $tahun ?>&filter=overdue" style="margin-left:auto;color:#ef4444;font-weight:600">Lihat »</a>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
