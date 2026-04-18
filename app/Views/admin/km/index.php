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
// Tentukan peran user dalam SPT ini
$sptId     = $spt['id'];
$isAdm     = isAuditAdmin();
$isDalnis  = isDalnisInSpt($sptId);
$isKt      = isKtInSpt($sptId);
$isAt      = isAtInSpt($sptId);
$isPj      = isPjInSpt($sptId);

// KM yang relevan per peran:
// Admin / Dalnis / PJ → semua
// KT → semua kecuali KM-5 dan KM-11 (Dalnis only) — hanya lihat
// AT → hanya Independensi (KM-2 diisi KT, KM-3 auto dari SPT)
$atKeys = ['independensi'];

$requiredItems = array_filter($checklist, fn($c) => $c['required'] ?? true);
$doneRequired  = count(array_filter($requiredItems, fn($c) => $c['complete']));
$totalRequired = count($requiredItems);
$allDone       = $doneRequired === $totalRequired;
?>

<?php if ($isAt && !$isAdm && !$isDalnis && !$isKt && !$isPj): ?>
<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TAMPILAN KHUSUS ANGGOTA TIM                                    -->
<!-- ═══════════════════════════════════════════════════════════════ -->

<div style="background:#eff6ff;border-radius:10px;padding:14px 18px;margin-bottom:16px;font-size:13px;color:#1d4ed8;border-left:4px solid #3b82f6">
    <i class="fas fa-info-circle"></i>
    Anda login sebagai <strong>Anggota Tim</strong>.
    Tugas Anda: isi Pernyataan Independensi, lalu kerjakan KKA.
    Anggaran Waktu dan dokumen lain dikelola oleh Ketua Tim.
</div>

<?php
$urlMap = [
    'independensi' => '/admin/spt/'.$spt['id'].'/km/independensi',
];
?>

<div style="display:flex;flex-direction:column;gap:12px">
<?php foreach($checklist as $key => $item):
    if (!in_array($key, $atKeys)) continue;
    $url  = $urlMap[$key] ?? '#';
    $done = $item['complete'];
    $isAuto = $item['auto'] ?? false;
    $borderColor = $done ? '#22c55e' : '#e2e8f0';
    $iconBg = $done ? '#dcfce7' : '#f1f5f9';
    $iconColor = $done ? '#16a34a' : '#94a3b8';
?>
<div class="card" style="border-left:4px solid <?= $borderColor ?>;margin:0">
    <div class="card-body" style="display:flex;align-items:center;gap:14px;padding:14px 16px">
        <div style="width:42px;height:42px;border-radius:50%;background:<?= $iconBg ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-<?= esc($item['icon']) ?>" style="color:<?= $iconColor ?>;font-size:17px"></i>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:13px"><?= esc($item['label']) ?></div>
            <div style="font-size:12px;color:<?= $done ? '#16a34a' : '#f59e0b' ?>;margin-top:3px">
                <?php if($done): ?>
                    <i class="fas fa-check-circle"></i> <?= esc($item['detail']) ?>
                <?php else: ?>
                    <i class="fas fa-clock"></i> <?= esc($item['detail']) ?>
                <?php endif; ?>
            </div>
        </div>
        <a href="<?= $url ?>" class="btn btn-sm <?= $done ? 'btn-secondary' : 'btn-primary' ?>" style="white-space:nowrap;flex-shrink:0">
            <?= $isAuto ? '<i class="fas fa-eye"></i> Lihat' : ($done ? '<i class="fas fa-edit"></i> Edit' : '<i class="fas fa-plus"></i> Isi') ?>
        </a>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- KKA — Kerja utama AT -->
<div class="card mt-3" style="border-left:4px solid #6366f1;background:linear-gradient(135deg,#f5f3ff,#eff6ff)">
    <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:20px">
        <div style="font-size:32px;color:#6366f1"><i class="fas fa-file-pen"></i></div>
        <div style="flex:1">
            <div style="font-weight:700;font-size:15px;color:#1e293b">Kertas Kerja Audit (KKA)</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px">
                Ini adalah pekerjaan utama Anda — isi Ikhtisar → Simpulan → Rekomendasi.<br>
                KKA dibuat otomatis setelah KM-5 (Reviu PKA) disetujui Pengendali Teknis.
            </div>
        </div>
        <a href="/admin/spt/<?= $spt['id'] ?>/kka" class="btn btn-primary">
            <i class="fas fa-arrow-right"></i> Buka KKA Saya
        </a>
    </div>
</div>

<?php else: ?>
<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TAMPILAN KT / DALNIS / ADMIN / PJ                              -->
<!-- ═══════════════════════════════════════════════════════════════ -->

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
                <?php if ($peranSpt): ?>
                <span style="margin-left:12px;background:#e0e7ff;color:#4f46e5;padding:2px 8px;border-radius:99px;font-size:11px">
                    <?= esc($peranSpt) ?>
                </span>
                <?php endif; ?>
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
$urlMap = [
    'km1'          => '/admin/spt/'.$spt['id'].'/km/1',
    'km2'          => '/admin/spt/'.$spt['id'].'/km/2',
    'km3'          => '/admin/spt/'.$spt['id'].'/km/3',
    'km4'          => '/admin/spt/'.$spt['id'].'/pka',
    'km5'          => '/admin/spt/'.$spt['id'].'/km/5',
    'km5b'         => '/admin/spt/'.$spt['id'].'/km/5b',
    'independensi' => '/admin/spt/'.$spt['id'].'/km/independensi',
    'km7'          => '/admin/spt/'.$spt['id'].'/kka',
    'km9'          => '/admin/spt/'.$spt['id'].'/nhp',
    'km10'         => '/admin/spt/'.$spt['id'].'/km/10',
    'km11'         => '/admin/spt/'.$spt['id'].'/km/11',
];

// KT tidak bisa edit KM-5 & KM-11 (Dalnis only) — tampilkan tapi disable tombol isi
$dalnisOnlyKeys = ['km5', 'km11'];
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<?php foreach($checklist as $key => $item):
    $url      = $urlMap[$key] ?? '#';
    $isInfo   = $item['info']     ?? false;
    $isLink   = $item['link']     ?? false;
    $isAuto   = $item['auto']     ?? false;
    $required = $item['required'] ?? true;
    $done     = $item['complete'];
    $isDalnisOnly = in_array($key, $dalnisOnlyKeys);

    if ($isInfo)        { $borderColor = '#6366f1'; $iconBg = '#e0e7ff'; $iconColor = '#4f46e5'; }
    elseif ($done)      { $borderColor = '#22c55e'; $iconBg = '#dcfce7'; $iconColor = '#16a34a'; }
    else                { $borderColor = '#e2e8f0'; $iconBg = '#f1f5f9'; $iconColor = '#94a3b8'; }
?>
<div class="card" style="border-left:4px solid <?= $borderColor ?>;margin:0<?= ($isDalnisOnly && $isKt && !$isAdm && !$isDalnis) ? ';opacity:.75' : '' ?>">
    <div class="card-body" style="display:flex;align-items:center;gap:14px;padding:14px 16px">
        <div style="width:42px;height:42px;border-radius:50%;background:<?= $iconBg ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-<?= esc($item['icon']) ?>" style="color:<?= $iconColor ?>;font-size:17px"></i>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:13px;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                <?= esc($item['label']) ?>
                <?php if($isInfo): ?>
                <span class="badge badge-info" style="font-size:10px;padding:2px 6px">Info</span>
                <?php elseif(!$required): ?>
                <span class="badge badge-secondary" style="font-size:10px;padding:2px 6px">Opsional</span>
                <?php endif; ?>
                <?php if($isAuto): ?>
                <span class="badge badge-primary" style="font-size:10px;padding:2px 6px">Auto</span>
                <?php endif; ?>
                <?php if($isDalnisOnly): ?>
                <span class="badge badge-warning" style="font-size:10px;padding:2px 6px">Dalnis</span>
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
        <a href="<?= $url ?>"
           class="btn btn-sm <?= $done ? 'btn-secondary' : ($isInfo ? 'btn-info' : 'btn-primary') ?>"
           style="white-space:nowrap;flex-shrink:0">
            <?php if($isLink || $isAuto): ?>
                <i class="fas fa-eye"></i> Lihat
            <?php elseif($done): ?>
                <i class="fas fa-edit"></i> Edit
            <?php else: ?>
                <i class="fas fa-<?= $isDalnisOnly && !$isDalnis && !$isAdm ? 'eye' : 'plus' ?>"></i>
                <?= $isDalnisOnly && !$isDalnis && !$isAdm ? 'Lihat' : 'Isi' ?>
            <?php endif; ?>
        </a>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- KKA Quick Access -->
<div class="card mt-3" style="border-left:4px solid #6366f1">
    <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:16px 20px">
        <div style="font-size:28px;color:#6366f1"><i class="fas fa-file-pen"></i></div>
        <div style="flex:1">
            <div style="font-weight:700;font-size:14px;color:#1e293b">Kertas Kerja Audit (KKA)</div>
            <div style="font-size:12px;color:#64748b">
                Dibuat otomatis setelah KM-5 (Reviu PKA) disetujui Dalnis.
                Alur: Ikhtisar → Simpulan → Rekomendasi.
            </div>
        </div>
        <a href="/admin/spt/<?= $spt['id'] ?>/kka" class="btn btn-primary btn-sm">
            <i class="fas fa-arrow-right"></i> Buka KKA
        </a>
    </div>
</div>

<?php endif; ?>

<?= $this->endSection() ?>
