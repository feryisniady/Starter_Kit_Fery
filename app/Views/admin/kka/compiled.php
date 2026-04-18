<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-layer-group"></i> Rekapitulasi Simpulan KKA</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['irban_nama'] ?? '') ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>/nhp" class="btn btn-primary">
            <i class="fas fa-file-export"></i> Kelola NHP
        </a>
        <a href="/admin/spt/<?= $spt['id'] ?>/kka" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke KKA
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Progress KKA -->
<div class="card mb-3">
    <div class="card-body" style="padding:14px 20px">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-bottom:6px">
                    <span><i class="fas fa-chart-bar"></i> Progress KKA Tim</span>
                    <span><strong><?= $progress['counts']['selesai'] ?>/<?= $progress['total'] ?></strong> selesai</span>
                </div>
                <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden">
                    <div style="background:linear-gradient(90deg,#10b981,#34d399);height:100%;width:<?= $progress['percent'] ?>%;border-radius:99px"></div>
                </div>
            </div>
            <div style="font-size:24px;font-weight:700;color:<?= $progress['percent'] == 100 ? '#10b981' : '#f59e0b' ?>">
                <?= $progress['percent'] ?>%
            </div>
            <?php if ($progress['percent'] < 100): ?>
            <div style="font-size:12px;color:#f59e0b;background:#fef3c7;padding:6px 12px;border-radius:8px">
                <i class="fas fa-clock"></i> Masih ada KKA yang belum selesai
            </div>
            <?php else: ?>
            <div style="font-size:12px;color:#10b981;background:#d1fae5;padding:6px 12px;border-radius:8px">
                <i class="fas fa-circle-check"></i> Semua KKA selesai — siap NHP
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Statistik Simpulan -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px">
    <div class="card" style="background:linear-gradient(135deg,#3b82f6,#6366f1);color:white">
        <div class="card-body" style="padding:16px;text-align:center">
            <div style="font-size:32px;font-weight:700"><?= count($allSimpulan) ?></div>
            <div style="font-size:12px;opacity:.85;margin-top:4px">Total Temuan Sementara</div>
        </div>
    </div>
    <div class="card" style="background:linear-gradient(135deg,#ef4444,#f97316);color:white">
        <div class="card-body" style="padding:16px;text-align:center">
            <div style="font-size:22px;font-weight:700">
                <?php
                $nilaiFormatted = $totalNilai > 0
                    ? 'Rp ' . number_format($totalNilai, 0, ',', '.')
                    : '—';
                echo $nilaiFormatted;
                ?>
            </div>
            <div style="font-size:12px;opacity:.85;margin-top:4px">Total Nilai Financial</div>
        </div>
    </div>
    <?php
    $jenisColors = ['keuangan' => '#10b981', 'kepatuhan' => '#f59e0b', 'kinerja' => '#8b5cf6', 'lainnya' => '#6b7280'];
    $jenisLabels = ['keuangan' => 'Keuangan', 'kepatuhan' => 'Kepatuhan', 'kinerja' => 'Kinerja', 'lainnya' => 'Lainnya'];
    foreach ($byJenis as $jenis => $stat):
    ?>
    <div class="card" style="border-left:4px solid <?= $jenisColors[$jenis] ?? '#6b7280' ?>">
        <div class="card-body" style="padding:14px">
            <div style="font-size:22px;font-weight:700;color:<?= $jenisColors[$jenis] ?? '#6b7280' ?>"><?= $stat['count'] ?></div>
            <div style="font-size:12px;color:#64748b"><?= $jenisLabels[$jenis] ?? ucfirst($jenis) ?></div>
            <?php if ($stat['nilai'] > 0): ?>
            <div style="font-size:11px;color:#94a3b8;margin-top:2px">Rp <?= number_format($stat['nilai'], 0, ',', '.') ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- NHP yang sudah ada -->
<?php if (!empty($nhpList)): ?>
<div class="card mb-4">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3 class="card-title"><i class="fas fa-paper-plane"></i> NHP yang Sudah Dibuat</h3>
        <a href="/admin/spt/<?= $spt['id'] ?>/nhp" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Buat NHP Baru
        </a>
    </div>
    <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                    <th style="padding:10px 16px;text-align:left;font-size:12px;color:#64748b">NOMOR NHP</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">TANGGAL</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">ITEM</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">STATUS</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">TANGGAPAN</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b">AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($nhpList as $nhp): ?>
            <tr style="border-bottom:1px solid #f1f5f9">
                <td style="padding:10px 16px;font-weight:600;color:#1e293b"><?= esc($nhp['nomor_nhp']) ?></td>
                <td style="padding:10px 16px;text-align:center;font-size:13px;color:#64748b">
                    <?= $nhp['tanggal_nhp'] ? date('d/m/Y', strtotime($nhp['tanggal_nhp'])) : '—' ?>
                </td>
                <td style="padding:10px 16px;text-align:center;font-size:13px"><?= $nhp['jumlah_item'] ?? 0 ?></td>
                <td style="padding:10px 16px;text-align:center">
                    <?php
                    $sc = ['draft'=>'secondary','terkirim'=>'info','ditanggapi'=>'warning','selesai'=>'success'];
                    $sl = ['draft'=>'Draft','terkirim'=>'Terkirim','ditanggapi'=>'Ditanggapi','selesai'=>'Selesai'];
                    $s = $nhp['status'];
                    ?>
                    <span class="badge badge-<?= $sc[$s] ?? 'secondary' ?>"><?= $sl[$s] ?? $s ?></span>
                </td>
                <td style="padding:10px 16px;text-align:center;font-size:12px">
                    <?php if (($nhp['jumlah_item'] ?? 0) > 0): ?>
                    <span style="color:#10b981">✓ <?= $nhp['jumlah_sesuai'] ?? 0 ?></span>
                    <span style="color:#94a3b8;margin:0 4px">|</span>
                    <span style="color:#ef4444">✗ <?= $nhp['jumlah_tidak_sesuai'] ?? 0 ?></span>
                    <span style="color:#94a3b8;margin:0 4px">|</span>
                    <span style="color:#94a3b8">⏳ <?= $nhp['jumlah_pending'] ?? 0 ?></span>
                    <?php else: ?>
                    <span style="color:#94a3b8">—</span>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 16px;text-align:center">
                    <a href="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Detail
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Semua Simpulan -->
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3 class="card-title"><i class="fas fa-table-list"></i> Semua Simpulan AT (<?= count($allSimpulan) ?> temuan)</h3>
        <?php if (!empty($allSimpulan)): ?>
        <a href="/admin/spt/<?= $spt['id'] ?>/nhp/create" class="btn btn-sm btn-success">
            <i class="fas fa-file-circle-plus"></i> Buat NHP dari Simpulan Ini
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($allSimpulan)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px"></i>
            Belum ada simpulan dari Anggota Tim. AT perlu menyelesaikan Tahap 2 (Simpulan) di KKA masing-masing.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;min-width:900px">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                    <th style="padding:10px 12px;text-align:left;font-size:11px;color:#64748b;font-weight:600;width:40px">#</th>
                    <th style="padding:10px 12px;text-align:left;font-size:11px;color:#64748b;font-weight:600">ANGGOTA TIM</th>
                    <th style="padding:10px 12px;text-align:left;font-size:11px;color:#64748b;font-weight:600">KONDISI / TEMUAN</th>
                    <th style="padding:10px 12px;text-align:left;font-size:11px;color:#64748b;font-weight:600">SEBAB</th>
                    <th style="padding:10px 12px;text-align:left;font-size:11px;color:#64748b;font-weight:600">AKIBAT</th>
                    <th style="padding:10px 12px;text-align:center;font-size:11px;color:#64748b;font-weight:600">KODE TEMUAN</th>
                    <th style="padding:10px 12px;text-align:right;font-size:11px;color:#64748b;font-weight:600">NILAI (Rp)</th>
                    <th style="padding:10px 12px;text-align:center;font-size:11px;color:#64748b;font-weight:600">AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            $prevAt = null;
            foreach ($allSimpulan as $s):
                $isNewAt = $s['at_nama'] !== $prevAt;
                $prevAt  = $s['at_nama'];
            ?>
            <?php if ($isNewAt): ?>
            <tr style="background:#f1f5f9">
                <td colspan="8" style="padding:8px 12px;font-size:12px;font-weight:600;color:#475569">
                    <i class="fas fa-user"></i> <?= esc($s['at_nama']) ?>
                    <span style="margin-left:8px;font-size:11px;font-weight:400;color:#94a3b8"><?= esc($s['peran_spt'] ?? '') ?></span>
                    <span style="margin-left:8px">
                        <?php
                        $kkaStatusColor = ['draft'=>'secondary','ikhtisar_selesai'=>'info','simpulan_selesai'=>'warning','selesai'=>'success'];
                        $kkaStatusLabel = ['draft'=>'Draft','ikhtisar_selesai'=>'Ikhtisar ✓','simpulan_selesai'=>'Simpulan ✓','selesai'=>'Selesai'];
                        $ks = $s['kka_status'];
                        ?>
                        <span class="badge badge-<?= $kkaStatusColor[$ks] ?? 'secondary' ?>" style="font-size:10px"><?= $kkaStatusLabel[$ks] ?? $ks ?></span>
                    </span>
                    <a href="/admin/kka/<?= $s['kka_id'] ?>" class="btn btn-xs btn-secondary" style="margin-left:8px;font-size:10px">
                        <i class="fas fa-eye"></i> Buka KKA
                    </a>
                </td>
            </tr>
            <?php endif; ?>
            <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:10px 12px;font-size:12px;color:#94a3b8;text-align:center"><?= $no++ ?></td>
                <td style="padding:10px 12px">
                    <span style="font-size:11px;color:#94a3b8">AT-<?= esc($s['at_nip'] ?: $s['kka_id']) ?></span>
                </td>
                <td style="padding:10px 12px;max-width:250px">
                    <div style="font-size:13px;color:#1e293b;line-height:1.4">
                        <?= esc(mb_strimwidth($s['kondisi'] ?? '—', 0, 120, '...')) ?>
                    </div>
                    <?php if ($s['kriteria']): ?>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">
                        <strong>Kriteria:</strong> <?= esc(mb_strimwidth($s['kriteria'], 0, 80, '...')) ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 12px;max-width:180px;font-size:12px;color:#475569">
                    <?= esc(mb_strimwidth($s['sebab'] ?? '—', 0, 100, '...')) ?>
                </td>
                <td style="padding:10px 12px;max-width:180px;font-size:12px;color:#475569">
                    <?= esc(mb_strimwidth($s['akibat'] ?? '—', 0, 100, '...')) ?>
                </td>
                <td style="padding:10px 12px;text-align:center">
                    <?php if ($s['kode_temuan_kode']): ?>
                    <?php
                    $jc = ['keuangan'=>'success','kepatuhan'=>'warning','kinerja'=>'info'];
                    $jenis = $s['kode_temuan_jenis'] ?? 'lainnya';
                    ?>
                    <span class="badge badge-<?= $jc[$jenis] ?? 'secondary' ?>" style="font-size:10px" title="<?= esc($s['kode_temuan_uraian']) ?>">
                        <?= esc($s['kode_temuan_kode']) ?>
                    </span>
                    <?php else: ?>
                    <span style="color:#94a3b8;font-size:12px">—</span>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 12px;text-align:right;font-size:12px;color:#ef4444;font-weight:600;white-space:nowrap">
                    <?= $s['nilai_financial'] ? 'Rp ' . number_format((int)$s['nilai_financial'], 0, ',', '.') : '—' ?>
                </td>
                <td style="padding:10px 12px;text-align:center;white-space:nowrap">
                    <a href="/admin/kka/<?= $s['kka_id'] ?>" class="btn btn-xs btn-secondary" title="Lihat KKA">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <?php if ($totalNilai > 0): ?>
            <tfoot>
                <tr style="background:#f8fafc;border-top:2px solid #e2e8f0">
                    <td colspan="6" style="padding:10px 12px;font-size:12px;font-weight:600;color:#475569;text-align:right">
                        Total Nilai Financial:
                    </td>
                    <td style="padding:10px 12px;text-align:right;font-size:13px;font-weight:700;color:#ef4444;white-space:nowrap">
                        Rp <?= number_format($totalNilai, 0, ',', '.') ?>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
