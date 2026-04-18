<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-triangle-exclamation"></i> Matriks Temuan</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['irban_nama'] ?? '') ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>/nhp" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke NHP
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<!-- Info banner -->
<div style="background:#fef3c7;border:1px solid #fbbf24;border-radius:10px;padding:14px 18px;margin-bottom:16px;display:flex;gap:12px;align-items:flex-start">
    <i class="fas fa-circle-info" style="color:#d97706;margin-top:2px"></i>
    <div style="font-size:13px;color:#92400e">
        <strong>Matriks Temuan</strong> berisi temuan dari NHP yang tanggapan entitasnya dinyatakan <strong>Tidak Sesuai</strong>.
        Temuan-temuan ini akan dimasukkan dalam <strong>Laporan Hasil Pemeriksaan (LHP)</strong>.
    </div>
</div>

<!-- Statistik -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:16px">
    <div class="card" style="border-left:4px solid #ef4444">
        <div class="card-body" style="padding:14px;text-align:center">
            <div style="font-size:32px;font-weight:700;color:#ef4444"><?= count($matriks) ?></div>
            <div style="font-size:12px;color:#64748b">Total Temuan Terbuka</div>
        </div>
    </div>
    <div class="card" style="border-left:4px solid #f97316">
        <div class="card-body" style="padding:14px;text-align:center">
            <div style="font-size:18px;font-weight:700;color:#f97316">
                <?= $totalNilai > 0 ? 'Rp ' . number_format($totalNilai, 0, ',', '.') : '—' ?>
            </div>
            <div style="font-size:12px;color:#64748b">Total Nilai Temuan</div>
        </div>
    </div>
    <?php
    $byJenis = [];
    foreach ($matriks as $m) {
        $jenis = $m['kode_temuan_jenis'] ?? 'lainnya';
        if (!isset($byJenis[$jenis])) $byJenis[$jenis] = 0;
        $byJenis[$jenis]++;
    }
    $jenisColors = ['keuangan'=>'#10b981','kepatuhan'=>'#f59e0b','kinerja'=>'#8b5cf6','lainnya'=>'#6b7280'];
    $jenisLabels = ['keuangan'=>'Keuangan','kepatuhan'=>'Kepatuhan','kinerja'=>'Kinerja','lainnya'=>'Lainnya'];
    foreach ($byJenis as $jenis => $count):
    ?>
    <div class="card" style="border-left:4px solid <?= $jenisColors[$jenis] ?? '#6b7280' ?>">
        <div class="card-body" style="padding:14px;text-align:center">
            <div style="font-size:28px;font-weight:700;color:<?= $jenisColors[$jenis] ?? '#6b7280' ?>"><?= $count ?></div>
            <div style="font-size:12px;color:#64748b"><?= $jenisLabels[$jenis] ?? ucfirst($jenis) ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Tabel Matriks Temuan -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table"></i> Daftar Temuan Terbuka (untuk LHP)</h3>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($matriks)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8">
            <i class="fas fa-circle-check" style="font-size:36px;display:block;margin-bottom:12px;color:#10b981"></i>
            <div style="font-size:15px;font-weight:600;color:#475569">Tidak ada temuan terbuka</div>
            <div style="font-size:13px;margin-top:8px">
                Semua temuan dalam NHP sudah ditanggapi dengan "Sesuai", atau belum ada NHP yang diselesaikan.
            </div>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;min-width:900px">
            <thead>
                <tr style="background:#fef2f2;border-bottom:2px solid #fca5a5">
                    <th style="padding:10px 14px;text-align:center;font-size:11px;color:#64748b;width:40px">#</th>
                    <th style="padding:10px 14px;text-align:left;font-size:11px;color:#64748b">JUDUL TEMUAN</th>
                    <th style="padding:10px 14px;text-align:left;font-size:11px;color:#64748b">KONDISI</th>
                    <th style="padding:10px 14px;text-align:left;font-size:11px;color:#64748b">REKOMENDASI</th>
                    <th style="padding:10px 14px;text-align:center;font-size:11px;color:#64748b">KODE</th>
                    <th style="padding:10px 14px;text-align:center;font-size:11px;color:#64748b">NOMOR NHP</th>
                    <th style="padding:10px 14px;text-align:center;font-size:11px;color:#64748b">AT</th>
                    <th style="padding:10px 14px;text-align:right;font-size:11px;color:#64748b">NILAI (Rp)</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; foreach ($matriks as $m): ?>
            <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">
                <td style="padding:12px 14px;text-align:center;font-size:13px;color:#94a3b8"><?= $no++ ?></td>
                <td style="padding:12px 14px;max-width:200px">
                    <div style="font-size:13px;font-weight:600;color:#1e293b;line-height:1.4">
                        <?= esc($m['judul_temuan'] ?: 'Temuan #'.$m['nomor_urut']) ?>
                    </div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px">
                        NHP: <?= esc($m['nomor_nhp']) ?>
                        <?= $m['tanggal_nhp'] ? ' — ' . date('d/m/Y', strtotime($m['tanggal_nhp'])) : '' ?>
                    </div>
                </td>
                <td style="padding:12px 14px;max-width:220px;font-size:12px;color:#475569;line-height:1.5">
                    <?= esc(mb_strimwidth($m['kondisi'] ?? '—', 0, 150, '...')) ?>
                </td>
                <td style="padding:12px 14px;max-width:200px;font-size:12px;color:#475569;line-height:1.5">
                    <?= esc(mb_strimwidth($m['rekomendasi'] ?? '—', 0, 120, '...')) ?>
                </td>
                <td style="padding:12px 14px;text-align:center">
                    <?php if ($m['kode_temuan_kode']): ?>
                    <?php
                    $jc = ['keuangan'=>'success','kepatuhan'=>'warning','kinerja'=>'info'];
                    $jenis = $m['kode_temuan_jenis'] ?? 'lainnya';
                    ?>
                    <span class="badge badge-<?= $jc[$jenis] ?? 'secondary' ?>" style="font-size:10px"
                          title="<?= esc($m['kode_temuan_uraian']) ?>">
                        <?= esc($m['kode_temuan_kode']) ?>
                    </span>
                    <?php else: ?>
                    <span style="color:#94a3b8;font-size:12px">—</span>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 14px;text-align:center">
                    <a href="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $m['nhp_id'] ?>"
                       style="font-size:12px;color:#3b82f6;text-decoration:none;font-weight:600">
                        <?= esc($m['nomor_nhp']) ?>
                    </a>
                </td>
                <td style="padding:12px 14px;text-align:center;font-size:12px;color:#64748b">
                    <?= esc($m['at_nama'] ?: '—') ?>
                </td>
                <td style="padding:12px 14px;text-align:right;font-size:13px;font-weight:600;color:#ef4444;white-space:nowrap">
                    <?= $m['nilai_temuan'] ? 'Rp ' . number_format((int)$m['nilai_temuan'], 0, ',', '.') : '—' ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <?php if ($totalNilai > 0): ?>
            <tfoot>
                <tr style="background:#fef2f2;border-top:2px solid #fca5a5">
                    <td colspan="7" style="padding:10px 14px;font-size:12px;font-weight:700;color:#dc2626;text-align:right">
                        Total Nilai Temuan:
                    </td>
                    <td style="padding:10px 14px;text-align:right;font-size:14px;font-weight:700;color:#dc2626;white-space:nowrap">
                        Rp <?= number_format($totalNilai, 0, ',', '.') ?>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
