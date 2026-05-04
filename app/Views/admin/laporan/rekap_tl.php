<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><?= esc($title) ?></h1>
        <p>Monitoring rekomendasi dan tindak lanjut — <?= $countOverdue > 0 ? '<span style="color:#ef4444"><i class="fas fa-triangle-exclamation"></i> ' . $countOverdue . ' item overdue</span>' : '<span style="color:#22c55e">Tidak ada overdue</span>' ?></p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="/admin/laporan/export-tl?tahun=<?= $tahun ?>&filter=<?= urlencode($filterOnly) ?>"
           class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <button onclick="window.print()" class="btn btn-secondary btn-sm">
            <i class="fas fa-print"></i> Cetak
        </button>
        <a href="/admin/laporan" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="get" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Tahun</label>
                <select name="tahun" class="form-control" style="width:100px">
                    <?php foreach($tahunList as $t): ?>
                    <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Filter</label>
                <select name="filter" class="form-control" style="width:180px">
                    <option value="" <?= $filterOnly === '' ? 'selected' : '' ?>>Semua Rekomendasi</option>
                    <option value="overdue" <?= $filterOnly === 'overdue' ? 'selected' : '' ?>>Hanya Overdue</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Terapkan</button>
            <a href="/admin/laporan/rekap-tl?tahun=<?= $tahun ?>" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

<?php if($filterOnly === 'overdue' && $countOverdue > 0): ?>
<div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
    <i class="fas fa-triangle-exclamation" style="font-size:20px;color:#ef4444"></i>
    <div>
        <strong style="color:#ef4444"><?= $countOverdue ?> rekomendasi melewati batas waktu!</strong>
        <p style="margin:0;font-size:13px;color:#64748b">Segera koordinasikan dengan entitas terkait untuk penyelesaian tindak lanjut.</p>
    </div>
</div>
<?php endif; ?>

<!-- Tabel -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-table"></i> Daftar Rekomendasi & TL Tahun <?= $tahun ?>
            <span class="badge badge-secondary" style="margin-left:8px"><?= count($rows) ?> item</span>
            <?php if($countOverdue > 0): ?>
            <span class="badge badge-danger" style="margin-left:4px"><?= $countOverdue ?> overdue</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if(empty($rows)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:32px;display:block;margin-bottom:8px;opacity:.4"></i>
            Tidak ada data untuk filter yang dipilih.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="table table-striped" style="width:100%;font-size:13px">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>SPT</th>
                    <th>Temuan</th>
                    <th>Rekomendasi</th>
                    <th>Entitas</th>
                    <th style="text-align:center">Batas Waktu</th>
                    <th style="text-align:center">Status Rek.</th>
                    <th style="text-align:center">Verifikasi TL</th>
                    <th class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($rows as $i => $row):
                $isOverdue    = (int)$row['is_overdue'];
                $hariLewat    = (int)($row['hari_lewat'] ?? 0);
                $statusColor  = ['selesai'=>'success','proses'=>'warning','belum'=>'danger'][$row['status']] ?? 'secondary';
                $vColor       = ['diterima'=>'success','revisi'=>'warning','menunggu'=>'secondary'][$row['status_verifikasi'] ?? ''] ?? 'secondary';
            ?>
                <tr style="<?= $isOverdue ? 'background:#fff5f5' : '' ?>">
                    <td><?= $i + 1 ?></td>
                    <td style="font-size:12px">
                        <a href="/admin/spt/<?= $row['spt_id'] ?>"><?= esc($row['spt_nomor'] ?: 'SPT#'.$row['spt_id']) ?></a>
                    </td>
                    <td style="max-width:180px;font-size:12px"><?= esc($row['judul_temuan'] ?: '-') ?></td>
                    <td style="max-width:220px">
                        <?= esc(mb_strimwidth($row['isi_rekomendasi'] ?? '-', 0, 120, '...')) ?>
                    </td>
                    <td style="font-size:12px"><?= esc($row['entitas_nama'] ?: '-') ?></td>
                    <td style="text-align:center;white-space:nowrap">
                        <?php if($row['batas_waktu']): ?>
                            <span style="font-size:12px"><?= date('d/m/Y', strtotime($row['batas_waktu'])) ?></span>
                            <?php if($isOverdue): ?>
                            <br><span style="font-size:11px;color:#ef4444;font-weight:600">
                                <i class="fas fa-triangle-exclamation"></i> +<?= $hariLewat ?> hari
                            </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#94a3b8">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <span class="badge badge-<?= $statusColor ?>">
                            <?= $statusLabel[$row['status']] ?? $row['status'] ?>
                        </span>
                    </td>
                    <td style="text-align:center">
                        <?php if($row['status_verifikasi']): ?>
                        <span class="badge badge-<?= $vColor ?>">
                            <?= $verifikasiLabel[$row['status_verifikasi']] ?? $row['status_verifikasi'] ?>
                        </span>
                        <?php if($row['tl_tgl']): ?>
                        <div style="font-size:11px;color:#94a3b8"><?= date('d/m/Y', strtotime($row['tl_tgl'])) ?></div>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="badge badge-secondary">Belum Ada TL</span>
                        <?php endif; ?>
                    </td>
                    <td class="no-print">
                        <?php if (!empty($row['tl_id'])): ?>
                        <a href="/admin/tl/<?= $row['tl_id'] ?>" class="btn btn-sm btn-primary" style="font-size:11px">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <?php else: ?>
                        <span style="font-size:11px;color:#94a3b8">Belum ada TL</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- Ringkasan bawah -->
        <div style="padding:16px;border-top:1px solid #f1f5f9;display:flex;gap:20px;font-size:13px">
            <span><span class="badge badge-success">Selesai</span> <?= $countSelesai ?></span>
            <?php if($countOverdue > 0): ?>
            <span><span class="badge badge-danger">Overdue</span> <?= $countOverdue ?></span>
            <?php endif; ?>
            <span style="color:#94a3b8;margin-left:auto">Total <?= count($rows) ?> rekomendasi</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="print-only" style="text-align:center;margin-bottom:16px;display:none">
    <div style="font-size:13pt;font-weight:bold;text-transform:uppercase">REKAP TINDAK LANJUT REKOMENDASI</div>
    <div style="font-size:10pt">INSPEKTORAT DAERAH — Tahun <?= $tahun ?></div>
    <?php if($filterOnly === 'overdue'): ?>
    <div style="font-size:9pt;color:#ef4444">(Filter: Hanya Overdue)</div>
    <?php endif; ?>
    <hr style="border:1px solid #000;margin:6px 0">
</div>

<?= $this->endSection() ?>
