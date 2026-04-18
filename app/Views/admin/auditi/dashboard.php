<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-building"></i> Dashboard Auditi</h1>
        <p><?= esc($entitas['nama']) ?></p>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Statistik -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px">
    <?php
    $statsCards = [
        ['label'=>'Total Temuan',       'value'=>$stats['total'],        'color'=>'#3b82f6','icon'=>'list-check'],
        ['label'=>'Belum Ditanggapi',   'value'=>$stats['pending'],      'color'=>'#f59e0b','icon'=>'clock'],
        ['label'=>'Sesuai (Ditutup)',   'value'=>$stats['sesuai'],       'color'=>'#10b981','icon'=>'circle-check'],
        ['label'=>'Tidak Sesuai (TL)',  'value'=>$stats['tidak_sesuai'], 'color'=>'#ef4444','icon'=>'triangle-exclamation'],
    ];
    foreach ($statsCards as $c):
    ?>
    <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);border-left:4px solid <?= $c['color'] ?>">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:42px;height:42px;border-radius:10px;background:<?= $c['color'] ?>22;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-<?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;font-size:18px"></i>
            </div>
            <div>
                <div style="font-size:28px;font-weight:700;color:#1e293b"><?= $c['value'] ?></div>
                <div style="font-size:12px;color:#64748b"><?= $c['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Daftar NHP -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-envelope-open-text"></i> Notisi Hasil Pemeriksaan (NHP)</h3>
    </div>
    <?php if (empty($nhpList)): ?>
    <div class="card-body" style="text-align:center;padding:48px;color:#94a3b8">
        <i class="fas fa-inbox" style="font-size:40px;display:block;margin-bottom:12px"></i>
        <p>Belum ada NHP yang ditujukan kepada <?= esc($entitas['nama']) ?>.</p>
        <p style="font-size:12px">NHP akan muncul di sini setelah tim audit mengirimkan temuan pemeriksaan.</p>
    </div>
    <?php else: ?>
    <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                    <th style="padding:10px 16px;text-align:left;font-size:12px;color:#64748b;font-weight:600">NOMOR NHP</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b;font-weight:600">TANGGAL</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b;font-weight:600">JUMLAH TEMUAN</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b;font-weight:600">PROGRES TANGGAPAN</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b;font-weight:600">AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($nhpList as $nhp): ?>
            <?php
            $total   = (int)$nhp['jumlah_item'];
            $selesai = (int)$nhp['jumlah_sesuai'] + (int)$nhp['jumlah_tidak_sesuai'];
            $pct     = $total > 0 ? round(($selesai / $total) * 100) : 0;
            $pending = (int)$nhp['jumlah_pending'];
            ?>
            <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:12px 16px">
                    <div style="font-weight:600;color:#1e293b"><?= esc($nhp['nomor_nhp'] ?: 'NHP #'.$nhp['id']) ?></div>
                    <div style="font-size:11px;color:#64748b">SPT: <?= esc($nhp['spt_nomor'] ?: '#'.$nhp['spt_id']) ?></div>
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:13px;color:#475569">
                    <?= $nhp['tanggal_nhp'] ? date('d M Y', strtotime($nhp['tanggal_nhp'])) : '—' ?>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <span style="font-size:18px;font-weight:700;color:#1e293b"><?= $total ?></span>
                    <?php if ($pending > 0): ?>
                    <div style="font-size:10px;color:#f59e0b;font-weight:600">
                        <i class="fas fa-clock"></i> <?= $pending ?> belum ditanggapi
                    </div>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 16px;text-align:center;min-width:120px">
                    <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;margin-bottom:4px">
                        <div style="background:<?= $pct === 100 ? '#10b981' : '#3b82f6' ?>;height:100%;width:<?= $pct ?>%;border-radius:99px"></div>
                    </div>
                    <div style="font-size:11px;color:#64748b"><?= $selesai ?>/<?= $total ?> ditanggapi (<?= $pct ?>%)</div>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <a href="/admin/auditi/nhp/<?= $nhp['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Lihat & Tanggapi
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
