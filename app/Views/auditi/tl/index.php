<?= $this->extend('auditi/layouts/portal') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1><i class="fas fa-tasks" style="color:#7c3aed"></i> Tindak Lanjut Rekomendasi</h1>
    <p>Daftar rekomendasi APIP yang perlu ditindaklanjuti oleh <?= esc($entitas['nama']) ?></p>
</div>

<?php if (empty($list)): ?>
<div class="card">
    <div class="card-body" style="text-align:center;padding:60px;color:#94a3b8">
        <i class="fas fa-clipboard-check" style="font-size:48px;display:block;margin-bottom:16px"></i>
        <div style="font-size:15px;font-weight:600;margin-bottom:8px">Belum ada rekomendasi</div>
        <p style="font-size:13px">Rekomendasi dari APIP akan muncul di sini setelah proses NHP selesai.</p>
    </div>
</div>
<?php else: ?>

<?php
// Kelompokkan: belum TL, perlu revisi, menunggu, diterima
$groups = [
    'revisi'   => ['label'=>'Perlu Perbaikan', 'color'=>'#ef4444', 'icon'=>'rotate-left',    'items'=>[]],
    'belum'    => ['label'=>'Belum Ditindaklanjuti', 'color'=>'#f59e0b', 'icon'=>'hourglass', 'items'=>[]],
    'menunggu' => ['label'=>'Menunggu Verifikasi APIP', 'color'=>'#3b82f6', 'icon'=>'spinner','items'=>[]],
    'diterima' => ['label'=>'Selesai / Diterima', 'color'=>'#22c55e', 'icon'=>'circle-check', 'items'=>[]],
];
foreach ($list as $rek) {
    if ($rek['tl_id'] === null) $groups['belum']['items'][] = $rek;
    elseif ($rek['status_verifikasi'] === 'revisi')   $groups['revisi']['items'][] = $rek;
    elseif ($rek['status_verifikasi'] === 'diterima') $groups['diterima']['items'][] = $rek;
    else $groups['menunggu']['items'][] = $rek;
}
?>

<?php foreach ($groups as $key => $group): ?>
<?php if (empty($group['items'])) continue; ?>
<div class="card" style="margin-bottom:16px;border-top:3px solid <?= $group['color'] ?>">
    <div class="card-header" style="background:<?= $group['color'] ?>0d">
        <span class="card-title" style="color:<?= $group['color'] ?>">
            <i class="fas fa-<?= $group['icon'] ?>"></i> <?= $group['label'] ?>
            <span style="font-weight:400;color:#64748b;font-size:12px">(<?= count($group['items']) ?>)</span>
        </span>
    </div>
    <div style="overflow-x:auto">
    <table class="tbl">
        <thead>
            <tr>
                <th>SPT</th>
                <th>Temuan</th>
                <th>Rekomendasi</th>
                <th style="text-align:center">Nilai (Rp)</th>
                <th style="text-align:center">Batas Waktu</th>
                <th>Status TL</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($group['items'] as $rek): ?>
        <?php
        $batas   = $rek['batas_waktu'];
        $overdue = $batas && strtotime($batas) < time() && $key !== 'diterima';
        ?>
        <tr <?= $overdue ? 'style="background:#fff5f5"' : '' ?>>
            <td style="font-size:12px;color:#64748b"><?= esc($rek['spt_nomor']) ?></td>
            <td style="max-width:200px">
                <div style="font-weight:600;font-size:13px;margin-bottom:2px"><?= esc($rek['judul']) ?></div>
                <?php if ($rek['nilai_temuan']): ?>
                <div style="font-size:11px;color:#64748b">Nilai: Rp <?= number_format($rek['nilai_temuan'],0,',','.') ?></div>
                <?php endif; ?>
            </td>
            <td style="max-width:260px;font-size:13px"><?= esc($rek['isi_rekomendasi']) ?></td>
            <td style="text-align:center;font-size:13px">
                <?= $rek['nilai_rekomendasi'] ? 'Rp '.number_format($rek['nilai_rekomendasi'],0,',','.') : '—' ?>
            </td>
            <td style="text-align:center">
                <?php if ($batas): ?>
                <span style="font-size:12px;<?= $overdue?'color:#ef4444;font-weight:700':'' ?>">
                    <?= $overdue ? '<i class="fas fa-triangle-exclamation"></i> ' : '' ?>
                    <?= date('d M Y', strtotime($batas)) ?>
                </span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <?php if ($rek['tl_id'] === null): ?>
                <span class="badge badge-pending"><i class="fas fa-hourglass"></i> Belum</span>
                <?php else: ?>
                <span class="badge badge-<?= $rek['status_verifikasi'] ?>">
                    <?= ($verifikasiLabel ?? [])[$rek['status_verifikasi']] ?? $rek['status_verifikasi'] ?>
                </span>
                <?php if ($rek['tl_tgl']): ?>
                <div style="font-size:10px;color:#94a3b8;margin-top:2px"><?= date('d M Y', strtotime($rek['tl_tgl'])) ?></div>
                <?php endif; ?>
                <?php endif; ?>
            </td>
            <td>
                <a href="/auditi/tl/<?= $rek['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-<?= $rek['tl_id']===null?'plus':'eye' ?>"></i>
                    <?= $rek['tl_id']===null ? 'Kirim TL' : 'Detail' ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
