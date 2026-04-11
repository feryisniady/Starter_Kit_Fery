<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-file-contract"></i> KM-3 — Dokumen SPT</h1>
        <p>Data otomatis dari SPT · <?= esc($spt['nomor_naskah'] ?: 'Draft') ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke KM
        </a>
        <a href="/admin/spt/<?= $spt['id'] ?>/edit" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit SPT
        </a>
    </div>
</div>

<?php
$isComplete = !empty($spt['nomor_naskah']) && !empty($spt['tanggal_naskah']) && !empty($spt['penandatangan_id']);
?>

<!-- Status banner -->
<div class="card mb-3" style="border-left:4px solid <?= $isComplete ? '#22c55e' : '#f59e0b' ?>;margin-bottom:20px">
    <div class="card-body" style="padding:14px 20px;display:flex;align-items:center;gap:12px">
        <i class="fas fa-<?= $isComplete ? 'circle-check' : 'circle-exclamation' ?>"
           style="font-size:24px;color:<?= $isComplete ? '#22c55e' : '#f59e0b' ?>"></i>
        <div>
            <div style="font-weight:600;font-size:14px">
                <?= $isComplete ? 'KM-3 Lengkap — Data SPT sudah terisi' : 'KM-3 Belum Lengkap' ?>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:2px">
                <?php if(!$isComplete): ?>
                    Lengkapi: <?= empty($spt['nomor_naskah']) ? '· Nomor Naskah ' : '' ?>
                              <?= empty($spt['tanggal_naskah']) ? '· Tanggal Naskah ' : '' ?>
                              <?= empty($spt['penandatangan_id']) ? '· Penandatangan ' : '' ?>
                    — klik <strong>Edit SPT</strong> untuk melengkapi.
                <?php else: ?>
                    Data dokumen SPT lengkap dan siap sebagai dasar penugasan.
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    <!-- Identitas SPT -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-alt"></i> Identitas Penugasan</h3>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <tr style="border-bottom:1px solid #f1f5f9">
                    <th style="padding:10px 16px;color:#64748b;font-weight:500;width:160px;white-space:nowrap">Nomor Naskah</th>
                    <td style="padding:10px 16px">
                        <?php if($spt['nomor_naskah']): ?>
                            <strong><?= esc($spt['nomor_naskah']) ?></strong>
                        <?php else: ?>
                            <span style="color:#f59e0b;font-style:italic">Belum diisi</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9">
                    <th style="padding:10px 16px;color:#64748b;font-weight:500">Tanggal Naskah</th>
                    <td style="padding:10px 16px">
                        <?= $spt['tanggal_naskah'] ? date('d F Y', strtotime($spt['tanggal_naskah'])) : '<span style="color:#f59e0b;font-style:italic">Belum diisi</span>' ?>
                    </td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9">
                    <th style="padding:10px 16px;color:#64748b;font-weight:500">Kode Kegiatan</th>
                    <td style="padding:10px 16px">
                        <span class="badge badge-primary"><?= esc($spt['kode_kegiatan']) ?></span>
                    </td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9">
                    <th style="padding:10px 16px;color:#64748b;font-weight:500">Irban</th>
                    <td style="padding:10px 16px"><?= esc($spt['irban_nama']) ?></td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9">
                    <th style="padding:10px 16px;color:#64748b;font-weight:500">Tujuan</th>
                    <td style="padding:10px 16px"><?= esc($spt['tujuan']) ?></td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9">
                    <th style="padding:10px 16px;color:#64748b;font-weight:500">Periode</th>
                    <td style="padding:10px 16px">
                        <?= $spt['tanggal_mulai'] ? date('d/m/Y', strtotime($spt['tanggal_mulai'])) : '?' ?>
                        s.d.
                        <?= $spt['tanggal_selesai'] ? date('d/m/Y', strtotime($spt['tanggal_selesai'])) : '?' ?>
                    </td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9">
                    <th style="padding:10px 16px;color:#64748b;font-weight:500">Dasar Penugasan</th>
                    <td style="padding:10px 16px;font-size:12px;line-height:1.5"><?= esc($spt['dasar_1']) ?></td>
                </tr>
                <?php if($spt['dasar_2']): ?>
                <tr style="border-bottom:1px solid #f1f5f9">
                    <th style="padding:10px 16px;color:#64748b;font-weight:500">Dasar 2</th>
                    <td style="padding:10px 16px;font-size:12px;line-height:1.5"><?= esc($spt['dasar_2']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Penandatangan & Tim -->
    <div>
        <!-- Penandatangan -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-signature"></i> Penandatangan</h3>
            </div>
            <div class="card-body">
                <?php if($spt['penandatangan_id']): ?>
                <div style="display:flex;align-items:center;gap:14px">
                    <div style="width:44px;height:44px;border-radius:50%;background:#e0e7ff;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-user-tie" style="color:#4f46e5;font-size:18px"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:14px"><?= esc($spt['penandatangan_nama']) ?></div>
                        <div style="font-size:12px;color:#64748b"><?= esc($spt['penandatangan_jabatan']) ?></div>
                        <div style="font-size:12px;color:#94a3b8">NIP. <?= esc($spt['penandatangan_nip']) ?></div>
                    </div>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:16px;color:#f59e0b">
                    <i class="fas fa-exclamation-triangle" style="font-size:24px;margin-bottom:6px;display:block"></i>
                    <div style="font-size:13px">Penandatangan belum dipilih</div>
                    <a href="/admin/spt/<?= $spt['id'] ?>/edit" class="btn btn-sm btn-warning" style="margin-top:8px">
                        <i class="fas fa-edit"></i> Lengkapi di SPT
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Susunan Tim -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Susunan Tim
                    <span class="badge badge-primary" style="margin-left:6px"><?= count($spt['tim']) ?></span>
                </h3>
            </div>
            <div class="card-body" style="padding:0">
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                            <th style="padding:8px 12px;text-align:left;color:#64748b">Nama</th>
                            <th style="padding:8px 12px;text-align:left;color:#64748b">Peran</th>
                            <th style="padding:8px 12px;text-align:center;color:#64748b;width:60px">Desk</th>
                            <th style="padding:8px 12px;text-align:center;color:#64748b;width:60px">Field</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($spt['tim'] as $t): ?>
                    <tr style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:8px 12px;font-weight:500"><?= esc($t['sdm_nama']) ?></td>
                        <td style="padding:8px 12px;color:#64748b"><?= esc($t['peran_spt']) ?></td>
                        <td style="padding:8px 12px;text-align:center"><?= $t['hp_desk'] ?></td>
                        <td style="padding:8px 12px;text-align:center"><?= $t['hp_field'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($spt['tim'])): ?>
                    <tr><td colspan="4" style="padding:16px;text-align:center;color:#94a3b8">Belum ada anggota tim</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
