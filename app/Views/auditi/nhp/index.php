<?= $this->extend('auditi/layouts/portal') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1><i class="fas fa-file-alt" style="color:#2563eb"></i> Notisi Hasil Pemeriksaan</h1>
    <p>Daftar seluruh NHP yang dikirimkan APIP kepada <?= esc($entitas['nama']) ?></p>
</div>

<?php if (empty($nhpList)): ?>
<div class="card">
    <div class="card-body" style="text-align:center;padding:60px;color:#94a3b8">
        <i class="fas fa-inbox" style="font-size:48px;display:block;margin-bottom:16px"></i>
        <div style="font-size:15px;font-weight:600;margin-bottom:8px">Belum ada NHP</div>
        <p style="font-size:13px">NHP dari APIP akan muncul di sini saat dikirimkan kepada instansi Anda.</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div style="overflow-x:auto">
    <table class="tbl">
        <thead>
            <tr>
                <th>#</th>
                <th>Nomor NHP</th>
                <th>Tanggal</th>
                <th>Perihal</th>
                <th style="text-align:center">Temuan</th>
                <th style="text-align:center">Belum Ditanggapi</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($nhpList as $i => $nhp): ?>
        <tr>
            <td style="color:#94a3b8"><?= $i+1 ?></td>
            <td><strong><?= esc($nhp['nomor_nhp'] ?: '#'.$nhp['id']) ?></strong><br>
                <span style="font-size:11px;color:#94a3b8">SPT: <?= esc($nhp['spt_nomor']) ?></span>
            </td>
            <td><?= $nhp['tanggal_nhp'] ? date('d M Y', strtotime($nhp['tanggal_nhp'])) : '—' ?></td>
            <td style="max-width:260px;font-size:13px"><?= esc($nhp['perihal']) ?></td>
            <td style="text-align:center">
                <span style="font-weight:700"><?= $nhp['jumlah_item'] ?></span>
            </td>
            <td style="text-align:center">
                <?php if ((int)$nhp['jumlah_pending'] > 0): ?>
                <span class="badge badge-pending"><i class="fas fa-clock"></i> <?= $nhp['jumlah_pending'] ?></span>
                <?php else: ?>
                <span style="color:#22c55e"><i class="fas fa-check-circle"></i> Selesai</span>
                <?php endif; ?>
            </td>
            <td>
                <?php
                $badges = ['terkirim'=>['terkirim','Terkirim'],'ditanggapi'=>['ditanggapi','Ditanggapi'],'selesai'=>['selesai','Selesai']];
                [$cls,$lbl] = $badges[$nhp['status']] ?? ['draft',$nhp['status']];
                ?>
                <span class="badge badge-<?= $cls ?>"><?= $lbl ?></span>
            </td>
            <td>
                <a href="/auditi/nhp/<?= $nhp['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-<?= (int)$nhp['jumlah_pending'] > 0 ? 'pen' : 'eye' ?>"></i>
                    <?= (int)$nhp['jumlah_pending'] > 0 ? 'Tanggapi' : 'Lihat' ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
