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
$requiredItems = array_filter($checklist, fn($c) => $c['required'] ?? true);
$doneRequired  = count(array_filter($requiredItems, fn($c) => $c['complete']));
$totalRequired = count($requiredItems);
$allDone       = $doneRequired === $totalRequired;
?>

<!-- Status Banner -->
<div class="card mb-3" style="border-left:4px solid <?= $allDone ? '#22c55e' : '#f59e0b' ?>">
    <div class="card-body" style="display:flex;align-items:center;gap:20px;padding:16px 20px">
        <div style="font-size:36px">
            <?= $allDone
                ? '<i class="fas fa-circle-check" style="color:#22c55e"></i>'
                : '<i class="fas fa-circle-exclamation" style="color:#f59e0b"></i>' ?>
        </div>
        <div style="flex:1">
            <div style="font-weight:700;font-size:15px">
                <?= $allDone ? 'Semua Kelengkapan KM Terpenuhi' : 'Kelengkapan KM Belum Lengkap' ?>
            </div>
            <div style="font-size:13px;color:#64748b;margin-top:2px">
                <?= $doneRequired ?>/<?= $totalRequired ?> dokumen wajib selesai
            </div>
            <div style="margin-top:8px;background:#e2e8f0;border-radius:6px;height:6px;width:240px">
                <div style="width:<?= $totalRequired > 0 ? round($doneRequired/$totalRequired*100) : 0 ?>%;
                            height:100%;background:<?= $allDone ? '#22c55e' : '#f59e0b' ?>;border-radius:6px;
                            transition:width .4s"></div>
            </div>
        </div>
        <?php if($allDone && $spt['status'] === 'draft'): ?>
        <div>
            <span class="badge badge-success" style="font-size:13px;padding:8px 14px">
                <i class="fas fa-check"></i> Siap Diajukan
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// URL per item
$urlMap = [
    'km1'          => '/admin/spt/'.$spt['id'].'/km/1',
    'km2'          => '/admin/spt/'.$spt['id'].'/km/2',
    'km3'          => '/admin/spt/'.$spt['id'].'/km/3',
    'km4'          => '/admin/spt/'.$spt['id'].'/pka',       // link ke modul PKA
    'km5b'         => '/admin/spt/'.$spt['id'].'/km/5b',
    'km7'          => '/admin/spt/'.$spt['id'].'/temuan',    // link ke modul Temuan
    'independensi' => '/admin/spt/'.$spt['id'].'/km/independensi',
];
?>

<!-- KM Cards -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<?php foreach($checklist as $key => $item):
    $url      = $urlMap[$key] ?? '#';
    $isInfo   = $item['info']     ?? false;
    $isLink   = $item['link']     ?? false;
    $isAuto   = $item['auto']     ?? false;
    $required = $item['required'] ?? true;
    $done     = $item['complete'];

    // Warna border
    if ($isInfo)        $borderColor = '#6366f1';
    elseif ($done)      $borderColor = '#22c55e';
    else                $borderColor = '#e2e8f0';

    // Warna icon bg
    if ($isInfo)        $iconBg = '#e0e7ff'; $iconColor = '#4f46e5';
    if ($done)        { $iconBg = '#dcfce7'; $iconColor = '#16a34a'; }
    if (!$done && !$isInfo) { $iconBg = '#f1f5f9'; $iconColor = '#94a3b8'; }
?>
<div class="card" style="border-left:4px solid <?= $borderColor ?>;margin:0">
    <div class="card-body" style="display:flex;align-items:center;gap:14px;padding:14px 16px">

        <!-- Icon -->
        <div style="width:42px;height:42px;border-radius:50%;background:<?= $iconBg ?>;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-<?= esc($item['icon']) ?>"
               style="color:<?= $iconColor ?>;font-size:17px"></i>
        </div>

        <!-- Info -->
        <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:13px;display:flex;align-items:center;gap:6px">
                <?= esc($item['label']) ?>
                <?php if($isInfo): ?>
                <span class="badge badge-info" style="font-size:10px;padding:2px 6px">Info</span>
                <?php elseif(!$required): ?>
                <span class="badge badge-secondary" style="font-size:10px;padding:2px 6px">Opsional</span>
                <?php endif; ?>
                <?php if($isAuto): ?>
                <span class="badge badge-primary" style="font-size:10px;padding:2px 6px">Auto</span>
                <?php endif; ?>
                <?php if($isLink): ?>
                <span class="badge badge-secondary" style="font-size:10px;padding:2px 6px">Modul</span>
                <?php endif; ?>
            </div>
            <div style="font-size:12px;color:<?= $done ? '#16a34a' : ($isInfo ? '#6366f1' : '#f59e0b') ?>;margin-top:3px">
                <?php if($done && !$isInfo): ?>
                    <i class="fas fa-check-circle"></i> <?= esc($item['detail']) ?>
                <?php elseif($isInfo): ?>
                    <i class="fas fa-info-circle"></i> <?= esc($item['detail']) ?>
                <?php else: ?>
                    <i class="fas fa-clock"></i> <?= esc($item['detail']) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action button -->
        <a href="<?= $url ?>"
           class="btn btn-sm <?= $done ? 'btn-secondary' : ($isInfo ? 'btn-info' : 'btn-primary') ?>"
           style="white-space:nowrap;flex-shrink:0"
           <?= $isLink ? 'target="_self"' : '' ?>>
            <?php if($isLink): ?>
                <i class="fas fa-external-link-alt"></i> Buka
            <?php elseif($isAuto): ?>
                <i class="fas fa-eye"></i> Lihat
            <?php elseif($done): ?>
                <i class="fas fa-edit"></i> Edit
            <?php else: ?>
                <i class="fas fa-plus"></i> Isi
            <?php endif; ?>
        </a>

    </div>
</div>
<?php endforeach; ?>
</div>

<?= $this->endSection() ?>
