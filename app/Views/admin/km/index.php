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
$sptId    = $spt['id'];
$isAdm    = isAuditAdmin();
$isDalnis = isDalnisInSpt($sptId);
$isKt     = isKtInSpt($sptId);
$isAt     = isAtInSpt($sptId);
$isPj     = isPjInSpt($sptId);

$requiredItems = array_filter($checklist, fn($c) => $c['required'] ?? true);
$doneRequired  = count(array_filter($requiredItems, fn($c) => $c['complete']));
$totalRequired = count($requiredItems);
$allDone       = $doneRequired === $totalRequired;
?>

<?php if ($isAt && !$isAdm && !$isDalnis && !$isKt && !$isPj):
// ═══════════════════════════════════════════════════════════
// TAMPILAN ANGGOTA TIM
// ═══════════════════════════════════════════════════════════
?>

<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#1d4ed8">
    <i class="fas fa-circle-info"></i>
    Anda login sebagai <strong>Anggota Tim</strong>. Selesaikan 2 tugas utama Anda di bawah ini secara berurutan.
</div>

<!-- Step 1: Independensi -->
<?php
$indItem = $checklist['independensi'] ?? [];
$indDone = $indItem['complete'] ?? false;
?>
<div class="card mb-3" style="border-left:5px solid <?= $indDone ? '#22c55e' : '#f59e0b' ?>">
    <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:18px 20px">
        <div style="width:48px;height:48px;border-radius:50%;background:<?= $indDone ? '#dcfce7' : '#fef3c7' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;font-size:16px;color:<?= $indDone ? '#16a34a' : '#d97706' ?>">
            <?= $indDone ? '<i class="fas fa-check"></i>' : '1' ?>
        </div>
        <div style="flex:1">
            <div style="font-weight:700;font-size:14px;color:#1e293b">Pernyataan Independensi & Integritas</div>
            <div style="font-size:12px;color:#64748b;margin-top:3px">
                Wajib diisi sebelum mulai bekerja. Pernyataan bahwa Anda bebas dari konflik kepentingan.
            </div>
            <div style="font-size:12px;margin-top:4px;color:<?= $indDone ? '#16a34a' : '#d97706' ?>">
                <?= $indDone
                    ? '<i class="fas fa-check-circle"></i> Sudah diisi'
                    : '<i class="fas fa-clock"></i> Belum diisi — kerjakan ini dulu' ?>
            </div>
        </div>
        <a href="/admin/spt/<?= $sptId ?>/km/independensi"
           class="btn <?= $indDone ? 'btn-secondary' : 'btn-warning' ?>"
           style="white-space:nowrap;flex-shrink:0">
            <?= $indDone ? '<i class="fas fa-edit"></i> Edit' : '<i class="fas fa-pen"></i> Isi Sekarang' ?>
        </a>
    </div>
</div>

<!-- Step 2: KKA -->
<div class="card" style="border-left:5px solid <?= $indDone ? '#6366f1' : '#e2e8f0' ?>;<?= $indDone ? '' : 'opacity:.6' ?>">
    <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:18px 20px">
        <div style="width:48px;height:48px;border-radius:50%;background:<?= $indDone ? '#e0e7ff' : '#f1f5f9' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;font-size:16px;color:<?= $indDone ? '#4f46e5' : '#94a3b8' ?>">
            2
        </div>
        <div style="flex:1">
            <div style="font-weight:700;font-size:14px;color:#1e293b">Kertas Kerja Audit (KKA)</div>
            <div style="font-size:12px;color:#64748b;margin-top:3px">
                Pekerjaan utama Anda: isi Ikhtisar → Simpulan → Rekomendasi untuk setiap prosedur audit.
            </div>
            <?php if (!$indDone): ?>
            <div style="font-size:12px;margin-top:4px;color:#94a3b8">
                <i class="fas fa-lock"></i> Selesaikan Langkah 1 (Independensi) terlebih dahulu
            </div>
            <?php endif; ?>
        </div>
        <a href="<?= $indDone ? '/admin/spt/'.$sptId.'/kka' : '#' ?>"
           class="btn btn-primary <?= $indDone ? '' : 'disabled' ?>"
           style="white-space:nowrap;flex-shrink:0">
            <i class="fas fa-arrow-right"></i> Buka KKA Saya
        </a>
    </div>
</div>

<?php else:
// ═══════════════════════════════════════════════════════════
// TAMPILAN KT / DALNIS / ADMIN / PJ
// ═══════════════════════════════════════════════════════════

$urlMap = [
    'km1'          => '/admin/spt/'.$sptId.'/km/1',
    'km2'          => '/admin/spt/'.$sptId.'/km/2',
    'km3'          => '/admin/spt/'.$sptId.'/km/3',
    'km4'          => '/admin/spt/'.$sptId.'/pka',
    'km5'          => '/admin/spt/'.$sptId.'/km/5',
    'km5b'         => '/admin/spt/'.$sptId.'/km/5b',
    'independensi' => '/admin/spt/'.$sptId.'/km/independensi',
    'km7'          => '/admin/spt/'.$sptId.'/kka',
    'km9'          => '/admin/spt/'.$sptId.'/nhp',
    'km10'         => '/admin/spt/'.$sptId.'/km/10',
    'km11'         => '/admin/spt/'.$sptId.'/km/11',
];

// Prasyarat soft-lock (key → prereq key yang harus complete dulu)
$prereqs = [
    'km5'  => ['km4'],
    'km5b' => ['km5'],
    'km7'  => ['km5'],
    'km9'  => ['km7'],
    'km10' => ['km5b'],
    'km11' => ['km10'],
];

// Penanggung jawab per langkah
$roleBadge = [
    'km1'          => ['KT / Dalnis', '#e0e7ff', '#3730a3'],
    'km2'          => ['KT + Semua AT', '#e0e7ff', '#3730a3'],
    'km3'          => ['Otomatis', '#f0fdf4', '#15803d'],
    'km4'          => ['KT', '#e0e7ff', '#3730a3'],
    'km5'          => ['Dalnis', '#fef3c7', '#b45309'],
    'km5b'         => ['KT / Dalnis', '#e0e7ff', '#3730a3'],
    'independensi' => ['Semua AT', '#f0f9ff', '#0369a1'],
    'km7'          => ['AT → KT', '#f5f3ff', '#4f46e5'],
    'km9'          => ['KT', '#e0e7ff', '#3730a3'],
    'km10'         => ['KT / Dalnis', '#e0e7ff', '#3730a3'],
    'km11'         => ['Dalnis', '#fef3c7', '#b45309'],
];

// Fase pengelompokan
$phases = [
    'FASE 1 — PERSIAPAN PENUGASAN' => ['km1','km2','km3','independensi','km4','km5','km5b'],
    'FASE 2 — PELAKSANAAN AUDIT'   => ['km7','km9'],
    'FASE 3 — PELAPORAN'           => ['km10','km11'],
];

$dalnisOnlyKeys = ['km5', 'km11'];

// Temukan langkah berikutnya untuk user ini
$nextKey = null;
foreach ($checklist as $key => $item) {
    if (!($item['required'] ?? true)) continue;
    if ($item['complete']) continue;
    if (isset($prereqs[$key])) {
        $allPrereqDone = true;
        foreach ($prereqs[$key] as $p) {
            if (!($checklist[$p]['complete'] ?? false)) { $allPrereqDone = false; break; }
        }
        if (!$allPrereqDone) continue;
    }
    if (in_array($key, $dalnisOnlyKeys) && !$isDalnis && !$isAdm) continue;
    $nextKey = $key;
    break;
}
?>

<!-- Progress + Status Header -->
<div class="card mb-3">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-bottom:6px">
                    <span><i class="fas fa-shield-check"></i> Kelengkapan KM Wajib</span>
                    <span><strong><?= $doneRequired ?>/<?= $totalRequired ?></strong> selesai</span>
                </div>
                <div style="background:#e2e8f0;border-radius:99px;height:10px;overflow:hidden">
                    <div style="background:<?= $allDone ? 'linear-gradient(90deg,#22c55e,#16a34a)' : 'linear-gradient(90deg,#f59e0b,#d97706)' ?>;
                                height:100%;width:<?= $totalRequired > 0 ? round($doneRequired/$totalRequired*100) : 0 ?>%;
                                transition:width .4s ease;border-radius:99px"></div>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <?php if ($allDone): ?>
                <span class="badge badge-success" style="font-size:14px;padding:6px 14px">
                    <i class="fas fa-circle-check"></i> Semua KM Lengkap
                </span>
                <?php else: ?>
                <div style="font-size:26px;font-weight:700;color:#f59e0b">
                    <?= $totalRequired > 0 ? round($doneRequired/$totalRequired*100) : 0 ?>%
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($peranSpt): ?>
        <div style="margin-top:10px;font-size:12px;color:#64748b">
            Peran Anda:
            <span style="background:#e0e7ff;color:#3730a3;padding:2px 10px;border-radius:99px;font-weight:600">
                <?= esc($peranSpt) ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Banner: Langkah Berikutnya -->
<?php if ($nextKey && !$allDone): ?>
<div style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-bolt" style="color:#fff;font-size:20px"></i>
    </div>
    <div style="flex:1;min-width:160px">
        <div style="color:rgba(255,255,255,.75);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Langkah Berikutnya</div>
        <div style="color:#fff;font-weight:700;font-size:15px;margin-top:2px"><?= esc($checklist[$nextKey]['label']) ?></div>
        <div style="color:rgba(255,255,255,.8);font-size:12px;margin-top:2px">
            <?php [$roleText] = $roleBadge[$nextKey] ?? ['—']; ?>
            Dikerjakan oleh: <?= esc($roleText) ?>
        </div>
    </div>
    <a href="<?= $urlMap[$nextKey] ?? '#' ?>"
       class="btn"
       style="background:#fff;color:#1d4ed8;font-weight:700;padding:10px 20px;white-space:nowrap;flex-shrink:0">
        <i class="fas fa-arrow-right"></i> Kerjakan Sekarang
    </a>
</div>
<?php elseif ($allDone): ?>
<div style="background:linear-gradient(135deg,#059669,#10b981);border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:16px">
    <i class="fas fa-trophy" style="color:#fff;font-size:32px;flex-shrink:0"></i>
    <div>
        <div style="color:#fff;font-weight:700;font-size:16px">Semua Kelengkapan KM Terpenuhi!</div>
        <div style="color:rgba(255,255,255,.85);font-size:13px;margin-top:3px">Penugasan siap untuk dilanjutkan ke tahap pelaporan akhir.</div>
    </div>
</div>
<?php endif; ?>

<!-- Stepper per Fase -->
<?php foreach ($phases as $phaseLabel => $phaseKeys): ?>
<div class="card mb-3">
    <div class="card-header" style="padding:10px 16px;background:#f8fafc;border-bottom:1px solid #e2e8f0">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#64748b">
            <i class="fas fa-layer-group" style="margin-right:4px"></i><?= $phaseLabel ?>
        </span>
    </div>
    <div style="padding:0">
    <?php foreach ($phaseKeys as $idx => $key):
        if (!isset($checklist[$key])) continue;
        $item     = $checklist[$key];
        $done     = $item['complete'];
        $isInfo   = $item['info']     ?? false;
        $isLink   = $item['link']     ?? false;
        $isAuto   = $item['auto']     ?? false;
        $required = $item['required'] ?? true;
        $url      = $urlMap[$key] ?? '#';
        $isDalnisOnly = in_array($key, $dalnisOnlyKeys);
        $isNext   = ($key === $nextKey);

        // Cek prasyarat
        $locked = false;
        $lockedBy = '';
        if (isset($prereqs[$key])) {
            foreach ($prereqs[$key] as $p) {
                if (!($checklist[$p]['complete'] ?? false)) {
                    $locked = true;
                    $lockedBy = $checklist[$p]['label'] ?? $p;
                    break;
                }
            }
        }

        // Role badge
        [$roleText, $roleBg, $roleColor] = $roleBadge[$key] ?? ['—', '#f1f5f9', '#64748b'];

        // Warna state
        if ($done) {
            $numBg = '#dcfce7'; $numColor = '#16a34a'; $numIcon = 'check';
            $rowBg = '#fff'; $borderLeft = '4px solid #22c55e';
        } elseif ($isNext) {
            $numBg = '#dbeafe'; $numColor = '#1d4ed8'; $numIcon = null;
            $rowBg = '#f0f7ff'; $borderLeft = '4px solid #3b82f6';
        } elseif ($locked) {
            $numBg = '#f1f5f9'; $numColor = '#cbd5e1'; $numIcon = 'lock';
            $rowBg = '#fff'; $borderLeft = '4px solid #e2e8f0';
        } elseif ($isInfo) {
            $numBg = '#e0e7ff'; $numColor = '#4f46e5'; $numIcon = 'info';
            $rowBg = '#fafafa'; $borderLeft = '4px solid #6366f1';
        } else {
            $numBg = '#fef3c7'; $numColor = '#d97706'; $numIcon = null;
            $rowBg = '#fff'; $borderLeft = '4px solid #fcd34d';
        }

        // Nomor tampilan
        $displayNum = match($key) {
            'km1' => '1', 'km2' => '2', 'km3' => '3', 'km4' => '4',
            'km5' => '5', 'km5b' => '6', 'independensi' => '★',
            'km7' => '7', 'km9' => '9', 'km10' => '10', 'km11' => '11',
        };

        // Tombol aksi
        $canEdit = !($isDalnisOnly && !$isDalnis && !$isAdm);
        $btnClass = $done ? 'btn-secondary' : ($isNext ? 'btn-primary' : ($locked ? 'btn-secondary' : 'btn-outline-primary'));
        if ($locked && !$isAdm) { $btnClass = 'btn-secondary'; $url = '#'; }
    ?>
    <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-bottom:1px solid #f1f5f9;background:<?= $rowBg ?>;border-left:<?= $borderLeft ?>;transition:background .15s"
         <?= ($isNext) ? 'id="next-step"' : '' ?>>

        <!-- Step Number -->
        <div style="width:38px;height:38px;border-radius:50%;background:<?= $numBg ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;font-size:13px;color:<?= $numColor ?>">
            <?php if ($numIcon === 'check'): ?>
                <i class="fas fa-check"></i>
            <?php elseif ($numIcon === 'lock'): ?>
                <i class="fas fa-lock" style="font-size:11px"></i>
            <?php elseif ($numIcon === 'info'): ?>
                <i class="fas fa-info" style="font-size:11px"></i>
            <?php else: ?>
                <?= $displayNum ?>
            <?php endif; ?>
        </div>

        <!-- Icon -->
        <div style="width:36px;height:36px;border-radius:8px;background:<?= $numBg ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-<?= esc($item['icon']) ?>" style="color:<?= $numColor ?>;font-size:15px"></i>
        </div>

        <!-- Info -->
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:3px">
                <span style="font-weight:600;font-size:13px;color:<?= $locked ? '#94a3b8' : '#1e293b' ?>">
                    <?= esc($item['label']) ?>
                </span>
                <?php if ($isNext): ?>
                <span style="background:#dbeafe;color:#1d4ed8;font-size:10px;padding:2px 7px;border-radius:99px;font-weight:700">← SEKARANG</span>
                <?php endif; ?>
                <?php if (!$required): ?>
                <span style="background:#f1f5f9;color:#64748b;font-size:10px;padding:2px 6px;border-radius:99px">Opsional</span>
                <?php endif; ?>
                <?php if ($isAuto): ?>
                <span style="background:#dcfce7;color:#15803d;font-size:10px;padding:2px 6px;border-radius:99px">Otomatis</span>
                <?php endif; ?>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <!-- Role -->
                <span style="background:<?= $roleBg ?>;color:<?= $roleColor ?>;font-size:10px;padding:2px 8px;border-radius:99px;font-weight:600">
                    <i class="fas fa-user" style="font-size:9px"></i> <?= esc($roleText) ?>
                </span>
                <!-- Detail -->
                <span style="font-size:11px;color:<?= $done ? '#16a34a' : ($locked ? '#94a3b8' : '#f59e0b') ?>">
                    <?php if ($done && !$isInfo): ?>
                        <i class="fas fa-circle-check"></i> <?= esc($item['detail']) ?>
                    <?php elseif ($locked): ?>
                        <i class="fas fa-lock"></i> Tersedia setelah: <?= esc($lockedBy) ?>
                    <?php elseif ($isInfo): ?>
                        <i class="fas fa-info-circle"></i> <?= esc($item['detail']) ?>
                    <?php else: ?>
                        <i class="fas fa-clock"></i> <?= esc($item['detail']) ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <!-- Action Button -->
        <div style="flex-shrink:0">
            <?php if ($locked && !$isAdm): ?>
            <button class="btn btn-sm btn-secondary" disabled style="opacity:.5;cursor:not-allowed">
                <i class="fas fa-lock"></i> Terkunci
            </button>
            <?php elseif ($isDalnisOnly && !$isDalnis && !$isAdm && !$done): ?>
            <a href="<?= $url ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-eye"></i> Lihat
            </a>
            <?php elseif ($isLink || $isAuto): ?>
            <a href="<?= $url ?>" class="btn btn-sm btn-info">
                <i class="fas fa-arrow-right"></i> Buka
            </a>
            <?php elseif ($done): ?>
            <a href="<?= $url ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <?php else: ?>
            <a href="<?= $url ?>" class="btn btn-sm <?= $isNext ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="fas fa-<?= $isNext ? 'arrow-right' : 'plus' ?>"></i>
                <?= $isNext ? 'Kerjakan' : 'Isi' ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<?= $this->endSection() ?>
