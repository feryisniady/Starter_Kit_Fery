<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-shield-check"></i> Kendali Mutu (KM)</h1>
        <p><?= esc($spt['nomor_naskah'] ?: 'Draft') ?> — <?= esc($spt['irban_nama']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke SPT
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php
$allComplete = !in_array(false, array_column($checklist, 'complete'), true);
$doneCount   = count(array_filter($checklist, fn($c) => $c['complete']));
$totalCount  = count($checklist);
?>

<!-- Status Banner -->
<div class="card mb-3" style="border-left:4px solid <?= $allComplete ? '#22c55e' : '#f59e0b' ?>">
    <div class="card-body" style="display:flex;align-items:center;gap:20px;padding:16px 20px">
        <div style="font-size:36px">
            <?= $allComplete ? '<i class="fas fa-circle-check" style="color:#22c55e"></i>' : '<i class="fas fa-circle-exclamation" style="color:#f59e0b"></i>' ?>
        </div>
        <div style="flex:1">
            <div style="font-weight:700;font-size:15px">
                <?= $allComplete ? 'Semua Kelengkapan KM Terpenuhi' : 'Kelengkapan KM Belum Lengkap' ?>
            </div>
            <div style="font-size:13px;color:#64748b;margin-top:2px">
                <?= $doneCount ?>/<?= $totalCount ?> dokumen KM selesai
            </div>
            <div style="margin-top:8px;background:#e2e8f0;border-radius:6px;height:6px;width:240px">
                <div style="width:<?= round($doneCount/$totalCount*100) ?>%;height:100%;background:<?= $allComplete ? '#22c55e' : '#f59e0b' ?>;border-radius:6px"></div>
            </div>
        </div>
        <?php if($allComplete && $spt['status'] === 'draft'): ?>
        <div>
            <span class="badge badge-success" style="font-size:13px;padding:8px 14px">
                <i class="fas fa-check"></i> Siap Diajukan
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- KM Checklist Cards -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    <?php foreach($checklist as $key => $item): ?>
    <?php
    $links = [
        'km1'            => '/admin/spt/'.$spt['id'].'/km/1',
        'km4'            => '/admin/spt/'.$spt['id'].'/km/4',
        'km6'            => '/admin/spt/'.$spt['id'].'/km/6',
        'anggaran_waktu' => '/admin/spt/'.$spt['id'].'/km/anggaran-waktu',
        'independensi'   => '/admin/spt/'.$spt['id'].'/km/independensi',
    ];
    $icons = [
        'km1'            => 'id-card',
        'km4'            => 'clipboard-list',
        'km6'            => 'handshake',
        'anggaran_waktu' => 'calendar-days',
        'independensi'   => 'user-shield',
    ];
    ?>
    <div class="card" style="border-left:4px solid <?= $item['complete'] ? '#22c55e' : '#e2e8f0' ?>">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:16px 20px">
            <div style="width:44px;height:44px;border-radius:50%;background:<?= $item['complete'] ? '#dcfce7' : '#f1f5f9' ?>;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-<?= $icons[$key] ?>" style="color:<?= $item['complete'] ? '#16a34a' : '#94a3b8' ?>;font-size:18px"></i>
            </div>
            <div style="flex:1">
                <div style="font-weight:600;font-size:13px"><?= esc($item['label']) ?></div>
                <div style="font-size:12px;color:<?= $item['complete'] ? '#16a34a' : '#f59e0b' ?>;margin-top:2px">
                    <?php if($item['complete']): ?>
                    <i class="fas fa-check-circle"></i> <?= esc($item['detail']) ?>
                    <?php else: ?>
                    <i class="fas fa-clock"></i> <?= esc($item['detail']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <a href="<?= $links[$key] ?>" class="btn btn-sm <?= $item['complete'] ? 'btn-secondary' : 'btn-primary' ?>">
                <?= $item['complete'] ? '<i class="fas fa-edit"></i> Edit' : '<i class="fas fa-plus"></i> Isi' ?>
            </a>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<?= $this->endSection() ?>
