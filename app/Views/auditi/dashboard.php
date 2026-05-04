<?= $this->extend('auditi/layouts/portal') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1><i class="fas fa-home" style="color:#2563eb"></i> Selamat Datang</h1>
    <p>Portal Pengawasan — <?= esc($entitas['nama']) ?></p>
</div>

<!-- Stat NHP -->
<div style="font-size:11px;font-weight:700;color:#64748b;letter-spacing:.5px;text-transform:uppercase;margin-bottom:10px">
    <i class="fas fa-file-alt"></i> Notisi Hasil Pemeriksaan (NHP)
</div>
<div class="stat-grid" style="margin-bottom:28px">
    <div class="stat-card amber">
        <div class="stat-num" style="color:#d97706"><?= $nhpPending ?></div>
        <div class="stat-label"><i class="fas fa-clock"></i> Perlu Ditanggapi</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-num" style="color:#2563eb"><?= $nhpDitanggapi ?></div>
        <div class="stat-label"><i class="fas fa-paper-plane"></i> Sudah Ditanggapi</div>
    </div>
    <div class="stat-card green">
        <div class="stat-num" style="color:#16a34a"><?= $nhpSelesai ?></div>
        <div class="stat-label"><i class="fas fa-circle-check"></i> Selesai</div>
    </div>
</div>

<!-- Stat TL -->
<div style="font-size:11px;font-weight:700;color:#64748b;letter-spacing:.5px;text-transform:uppercase;margin-bottom:10px">
    <i class="fas fa-tasks"></i> Tindak Lanjut Rekomendasi
</div>
<div class="stat-grid" style="margin-bottom:28px">
    <div class="stat-card red">
        <div class="stat-num" style="color:#dc2626"><?= $tlSummary['belum'] ?></div>
        <div class="stat-label"><i class="fas fa-hourglass"></i> Belum Ada TL</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-num" style="color:#d97706"><?= $tlSummary['menunggu'] ?></div>
        <div class="stat-label"><i class="fas fa-spinner"></i> Menunggu Verifikasi</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-num" style="color:#7c3aed"><?= $tlSummary['revisi'] ?></div>
        <div class="stat-label"><i class="fas fa-rotate-left"></i> Perlu Perbaikan</div>
    </div>
    <div class="stat-card green">
        <div class="stat-num" style="color:#16a34a"><?= $tlSummary['diterima'] ?></div>
        <div class="stat-label"><i class="fas fa-circle-check"></i> Diterima Inspektorat</div>
    </div>
</div>

<!-- NHP terbaru -->
<?php if (!empty($nhpList)): ?>
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-file-alt" style="color:#2563eb"></i> NHP Terbaru</span>
        <a href="/auditi/nhp" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    <div style="overflow-x:auto">
    <table class="tbl">
        <thead>
            <tr>
                <th>Nomor NHP</th>
                <th>Tanggal</th>
                <th>Perihal</th>
                <th>Status</th>
                <th>Temuan</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($nhpList as $nhp): ?>
        <tr>
            <td><strong><?= esc($nhp['nomor_nhp'] ?: '#'.$nhp['id']) ?></strong></td>
            <td><?= $nhp['tanggal_nhp'] ? date('d M Y', strtotime($nhp['tanggal_nhp'])) : '—' ?></td>
            <td style="max-width:280px"><?= esc($nhp['perihal']) ?></td>
            <td>
                <?php
                $sc = ['terkirim'=>'terkirim','ditanggapi'=>'ditanggapi','selesai'=>'selesai'];
                $cls = $sc[$nhp['status']] ?? 'draft';
                ?>
                <span class="badge badge-<?= $cls ?>"><?= $nhpStatusLabel[$nhp['status']] ?? $nhp['status'] ?></span>
                <?php if ((int)$nhp['jumlah_pending'] > 0): ?>
                <span class="badge badge-pending" style="margin-left:4px"><?= $nhp['jumlah_pending'] ?> belum</span>
                <?php endif; ?>
            </td>
            <td style="text-align:center"><?= $nhp['jumlah_item'] ?></td>
            <td>
                <a href="/auditi/nhp/<?= $nhp['id'] ?>" class="btn btn-primary btn-xs">
                    <i class="fas fa-eye"></i> Buka
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body" style="text-align:center;padding:48px;color:#94a3b8">
        <i class="fas fa-inbox" style="font-size:40px;display:block;margin-bottom:12px"></i>
        <div style="font-size:15px;font-weight:600;margin-bottom:6px">Belum Ada NHP</div>
        <p style="font-size:13px">NHP dari Inspektorat akan muncul di sini saat dikirimkan.</p>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
