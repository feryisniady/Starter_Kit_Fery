<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-paper-plane"></i> NHP — Notisi Hasil Pemeriksaan</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['irban_nama'] ?? '') ?></p>
    </div>
    <div class="page-actions">
        <?php if ($canManage): ?>
        <a href="/admin/spt/<?= $spt['id'] ?>/nhp/create" class="btn btn-success">
            <i class="fas fa-plus"></i> Buat NHP Baru
        </a>
        <?php endif; ?>
        <a href="/admin/spt/<?= $spt['id'] ?>/kka/compiled" class="btn btn-info">
            <i class="fas fa-layer-group"></i> Rekapitulasi Simpulan
        </a>
        <a href="/admin/spt/<?= $spt['id'] ?>/nhp/matriks" class="btn btn-warning">
            <i class="fas fa-triangle-exclamation"></i> Matriks Temuan
        </a>
        <a href="/admin/spt/<?= $spt['id'] ?>/kka" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> KKA
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Statistik cepat -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:16px">
    <div class="card">
        <div class="card-body" style="padding:14px;text-align:center">
            <div style="font-size:28px;font-weight:700;color:#3b82f6"><?= count($nhpList) ?></div>
            <div style="font-size:12px;color:#64748b">Total NHP</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding:14px;text-align:center">
            <div style="font-size:28px;font-weight:700;color:#f59e0b"><?= count(array_filter($nhpList, fn($n) => $n['status'] === 'terkirim')) ?></div>
            <div style="font-size:12px;color:#64748b">NHP Terkirim</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding:14px;text-align:center">
            <div style="font-size:28px;font-weight:700;color:#ef4444"><?= count($matriks) ?></div>
            <div style="font-size:12px;color:#64748b">Temuan Terbuka</div>
            <div style="font-size:10px;color:#94a3b8">(masuk LHP)</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding:14px;text-align:center">
            <div style="font-size:28px;font-weight:700;color:#10b981"><?= count($allSimpulan) ?></div>
            <div style="font-size:12px;color:#64748b">Total Simpulan AT</div>
        </div>
    </div>
</div>

<!-- Daftar NHP -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Daftar NHP</h3>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($nhpList)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px"></i>
            Belum ada NHP untuk SPT ini.
            <?php if ($canManage): ?>
            <div style="margin-top:12px">
                <a href="/admin/spt/<?= $spt['id'] ?>/nhp/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat NHP Pertama
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                    <th style="padding:10px 16px;text-align:left;font-size:12px;color:#64748b">NOMOR NHP</th>
                    <th style="padding:10px 16px;text-align:left;font-size:12px;color:#64748b">PERIHAL</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">TANGGAL</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">ITEM</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">STATUS</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">TANGGAPAN</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($nhpList as $nhp): ?>
            <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:12px 16px">
                    <div style="font-weight:700;color:#1e293b"><?= esc($nhp['nomor_nhp']) ?></div>
                </td>
                <td style="padding:12px 16px;font-size:13px;color:#475569;max-width:280px">
                    <?= esc(mb_strimwidth($nhp['perihal'] ?? '—', 0, 80, '...')) ?>
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:13px;color:#64748b">
                    <?= $nhp['tanggal_nhp'] ? date('d/m/Y', strtotime($nhp['tanggal_nhp'])) : '—' ?>
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:14px;font-weight:600">
                    <?= $nhp['jumlah_item'] ?? 0 ?>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <?php $s = $nhp['status']; ?>
                    <span class="badge badge-<?= $statusColor[$s] ?? 'secondary' ?>"><?= $statusLabel[$s] ?? $s ?></span>
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:12px">
                    <?php if (($nhp['jumlah_item'] ?? 0) > 0): ?>
                    <span title="Sesuai" style="color:#10b981;font-weight:600">✓ <?= $nhp['jumlah_sesuai'] ?? 0 ?></span>
                    <span style="color:#e2e8f0;margin:0 4px">|</span>
                    <span title="Tidak Sesuai" style="color:#ef4444;font-weight:600">✗ <?= $nhp['jumlah_tidak_sesuai'] ?? 0 ?></span>
                    <span style="color:#e2e8f0;margin:0 4px">|</span>
                    <span title="Pending" style="color:#94a3b8">⏳ <?= $nhp['jumlah_pending'] ?? 0 ?></span>
                    <?php else: ?>
                    <span style="color:#94a3b8">—</span>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <a href="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Detail
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
