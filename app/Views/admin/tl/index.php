<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-clipboard-check"></i> Verifikasi Tindak Lanjut</h1>
        <p>Daftar tindak lanjut rekomendasi yang dikirim entitas/OPD</p>
    </div>
</div>

<!-- Tab Filter -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php
    $tabs = [
        'menunggu' => ['label'=>'Menunggu Verifikasi', 'color'=>'#f59e0b', 'icon'=>'clock'],
        'revisi'   => ['label'=>'Perlu Perbaikan',     'color'=>'#ef4444', 'icon'=>'rotate-left'],
        'diterima' => ['label'=>'Diterima',            'color'=>'#22c55e', 'icon'=>'circle-check'],
        'semua'    => ['label'=>'Semua',               'color'=>'#6366f1', 'icon'=>'list'],
    ];
    foreach ($tabs as $key => $t):
        $active = $tab === $key;
    ?>
    <a href="/admin/tl?tab=<?= $key ?>"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;border:2px solid <?= $t['color'] ?>;
              <?= $active ? "background:{$t['color']};color:#fff" : "color:{$t['color']};background:transparent" ?>">
        <i class="fas fa-<?= $t['icon'] ?>"></i>
        <?= $t['label'] ?>
        <span style="<?= $active ? 'background:rgba(255,255,255,.3)' : "background:{$t['color']}22" ?>;padding:1px 7px;border-radius:10px;font-size:11px">
            <?= $counts[$key] ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (empty($list)): ?>
<div class="card">
    <div class="card-body" style="text-align:center;padding:60px;color:#94a3b8">
        <i class="fas fa-inbox" style="font-size:48px;display:block;margin-bottom:16px"></i>
        <div style="font-size:15px;font-weight:600;margin-bottom:8px">Tidak ada data</div>
        <p style="font-size:13px">Belum ada tindak lanjut dengan status ini.</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div style="overflow-x:auto">
    <table class="tbl">
        <thead>
            <tr>
                <th>#</th>
                <th>Entitas / OPD</th>
                <th>SPT</th>
                <th>Temuan & Rekomendasi</th>
                <th style="text-align:center">Batas Waktu</th>
                <th style="text-align:center">Dokumen</th>
                <th>Dikirim</th>
                <th style="text-align:center">Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($list as $i => $row):
            $overdue = $row['batas_waktu'] && strtotime($row['batas_waktu']) < time() && $row['status_verifikasi'] !== 'diterima';
        ?>
        <tr <?= $overdue ? 'style="background:#fff5f5"' : '' ?>>
            <td style="color:#94a3b8;font-size:12px"><?= $i + 1 ?></td>
            <td>
                <div style="font-weight:600;font-size:13px"><?= esc($row['entitas_nama']) ?></div>
            </td>
            <td style="font-size:12px;color:#64748b"><?= esc($row['spt_nomor']) ?></td>
            <td style="max-width:280px">
                <div style="font-size:12px;font-weight:600;color:#334155;margin-bottom:2px">
                    <?= esc($row['temuan_judul']) ?>
                </div>
                <div style="font-size:11px;color:#64748b;line-height:1.4">
                    <?= esc(mb_substr($row['isi_rekomendasi'], 0, 100)) ?>…
                </div>
            </td>
            <td style="text-align:center">
                <?php if ($row['batas_waktu']): ?>
                <span style="font-size:12px;<?= $overdue ? 'color:#ef4444;font-weight:700' : 'color:#64748b' ?>">
                    <?= $overdue ? '<i class="fas fa-triangle-exclamation"></i> ' : '' ?>
                    <?= date('d M Y', strtotime($row['batas_waktu'])) ?>
                </span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td style="text-align:center">
                <?php $doks = (int)($row['jumlah_dokumen'] ?? 0); ?>
                <span style="font-size:12px;color:#64748b">
                    <i class="fas fa-paperclip"></i>
                    <?= $doks ?> file
                </span>
            </td>
            <td style="font-size:12px;color:#64748b;white-space:nowrap">
                <?= date('d M Y', strtotime($row['created_at'])) ?><br>
                <span style="font-size:11px"><?= date('H:i', strtotime($row['created_at'])) ?></span>
            </td>
            <td style="text-align:center">
                <?php
                $colors = ['menunggu'=>'warning','diterima'=>'success','revisi'=>'danger'];
                $labels = TindakLanjutModel::$verifikasiLabel;
                $sv     = $row['status_verifikasi'];
                ?>
                <span class="badge badge-<?= $colors[$sv] ?? 'secondary' ?>" style="font-size:11px">
                    <?= $labels[$sv] ?? $sv ?>
                </span>
                <?php if ($sv === 'diterima' && $row['verified_by_nama']): ?>
                <div style="font-size:10px;color:#94a3b8;margin-top:2px"><?= esc($row['verified_by_nama']) ?></div>
                <?php endif; ?>
            </td>
            <td>
                <a href="/admin/tl/<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-<?= $sv === 'menunggu' ? 'check-double' : 'eye' ?>"></i>
                    <?= $sv === 'menunggu' ? 'Verifikasi' : 'Detail' ?>
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
