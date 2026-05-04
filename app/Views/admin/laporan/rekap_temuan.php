<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-flag"></i> Rekap Temuan Audit</h1>
        <p>Daftar semua temuan lintas SPT — Tahun <strong><?= $tahun ?></strong></p>
    </div>
    <div class="page-actions">
        <a href="/admin/laporan/export-temuan?tahun=<?= $tahun ?>&irban=<?= urlencode($filterIrban) ?>&status=<?= urlencode($filterStatus) ?>&jenis=<?= urlencode($filterJenis) ?>"
           class="btn btn-success">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i> Cetak
        </button>
        <a href="/admin/laporan" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Print header -->
<div class="print-only" style="text-align:center;margin-bottom:16px;display:none">
    <div style="font-size:14pt;font-weight:bold;text-transform:uppercase">REKAP TEMUAN AUDIT</div>
    <div style="font-size:10pt">INSPEKTORAT DAERAH — TAHUN <?= $tahun ?></div>
    <hr style="border:1px solid #000;margin:6px 0">
</div>

<!-- Filter -->
<div class="card mb-3 no-print">
    <div class="card-body" style="padding:12px 16px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Tahun</label>
                <select name="tahun" class="form-control form-control-sm" style="width:100px">
                    <?php foreach($tahunList as $t): ?>
                    <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Bidang / Irban</label>
                <select name="irban" class="form-control form-control-sm" style="width:180px">
                    <option value="">— Semua Bidang —</option>
                    <?php foreach($irbanList as $irb): ?>
                    <option value="<?= $irb['id'] ?>" <?= $filterIrban == $irb['id'] ? 'selected' : '' ?>><?= esc($irb['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Status</label>
                <select name="status" class="form-control form-control-sm" style="width:130px">
                    <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua Status</option>
                    <option value="buka"  <?= $filterStatus === 'buka'  ? 'selected' : '' ?>>Terbuka</option>
                    <option value="tutup" <?= $filterStatus === 'tutup' ? 'selected' : '' ?>>Tertutup</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Jenis Temuan</label>
                <select name="jenis" class="form-control form-control-sm" style="width:150px">
                    <option value="" <?= $filterJenis === '' ? 'selected' : '' ?>>Semua Jenis</option>
                    <option value="finansial"     <?= $filterJenis === 'finansial'     ? 'selected' : '' ?>>Finansial</option>
                    <option value="non_finansial" <?= $filterJenis === 'non_finansial' ? 'selected' : '' ?>>Non-Finansial</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Terapkan</button>
                <a href="/admin/laporan/rekap-temuan?tahun=<?= $tahun ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid-4 mb-3 no-print">
    <div class="stat-card" style="border-left:3px solid #6366f1">
        <div class="stat-icon" style="background:#ede9fe;color:#6366f1"><i class="fas fa-flag"></i></div>
        <div class="stat-info">
            <div class="label">Total Temuan</div>
            <div class="value"><?= count($rows) ?></div>
            <div class="sub">Tahun <?= $tahun ?></div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #ef4444">
        <div class="stat-icon" style="background:#fef2f2;color:#ef4444"><i class="fas fa-folder-open"></i></div>
        <div class="stat-info">
            <div class="label">Terbuka</div>
            <div class="value"><?= $totalBuka ?></div>
            <div class="sub">Belum selesai ditindaklanjuti</div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #22c55e">
        <div class="stat-icon" style="background:#f0fdf4;color:#22c55e"><i class="fas fa-folder-closed"></i></div>
        <div class="stat-info">
            <div class="label">Tertutup</div>
            <div class="value"><?= $totalTutup ?></div>
            <div class="sub">Semua rekomendasi selesai</div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #7c3aed">
        <div class="stat-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-coins"></i></div>
        <div class="stat-info">
            <div class="label">Total Nilai Temuan</div>
            <div class="value" style="font-size:14px">Rp <?= number_format($totalNilai, 0, ',', '.') ?></div>
            <div class="sub">Rek: Rp <?= number_format($totalNilaiRek, 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<!-- Tabel Temuan -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-table"></i> Daftar Temuan Tahun <?= $tahun ?>
            <span class="badge badge-secondary" style="margin-left:8px"><?= count($rows) ?> temuan</span>
            <?php if($totalBuka > 0): ?>
            <span class="badge badge-danger" style="margin-left:4px"><?= $totalBuka ?> terbuka</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if(empty($rows)): ?>
        <div style="text-align:center;padding:48px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px;opacity:.4"></i>
            <div style="font-size:15px;font-weight:600;margin-bottom:4px">Tidak Ada Data</div>
            <p>Tidak ada temuan untuk filter yang dipilih.</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="table table-striped" style="width:100%;font-size:12.5px">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Bidang</th>
                    <th>No. SPT</th>
                    <th>OPD / Entitas</th>
                    <th style="width:60px;text-align:center">No.</th>
                    <th>Judul Temuan</th>
                    <th style="width:80px;text-align:center">Kode</th>
                    <th style="text-align:right">Nilai (Rp)</th>
                    <th style="text-align:center;width:80px">Rekomendasi</th>
                    <th style="text-align:center;width:80px">Status</th>
                    <th class="no-print" style="width:70px">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($rows as $i => $row):
                $allDone = $row['jml_rek'] > 0 && (int)$row['rek_belum'] === 0 && (int)$row['rek_proses'] === 0;
                $isBuka  = $row['status_temuan'] === 'buka';
            ?>
            <tr>
                <td style="color:#94a3b8"><?= $i + 1 ?></td>
                <td style="font-size:11px;color:#64748b"><?= esc($row['irban_nama'] ?: '—') ?></td>
                <td style="font-size:11px">
                    <a href="/admin/spt/<?= $row['spt_id'] ?>" class="no-print" style="color:#2563eb">
                        <?= esc($row['spt_nomor'] ?: '#'.$row['spt_id']) ?>
                    </a>
                    <span class="print-only"><?= esc($row['spt_nomor'] ?: '#'.$row['spt_id']) ?></span>
                </td>
                <td style="font-size:11px"><?= esc($row['entitas_nama'] ?: '—') ?></td>
                <td style="text-align:center;font-weight:700;color:#6366f1"><?= esc($row['nomor_temuan'] ?: '—') ?></td>
                <td style="max-width:280px">
                    <div style="font-weight:600;color:#1e293b;line-height:1.4"><?= esc($row['judul']) ?></div>
                    <?php if($row['kode_jenis'] === 'finansial' && $row['total_nilai_rek'] > 0): ?>
                    <div style="font-size:10px;color:#7c3aed;margin-top:2px">
                        <i class="fas fa-coins"></i> Nilai Rek: Rp <?= number_format((int)$row['total_nilai_rek'], 0, ',', '.') ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td style="text-align:center">
                    <?php if($row['kode_temuan']): ?>
                    <span style="background:#e0e7ff;color:#3730a3;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:700">
                        <?= esc($row['kode_temuan']) ?>
                    </span>
                    <?php else: ?>
                    <span style="color:#cbd5e1">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;color:<?= $row['nilai_temuan'] > 0 ? '#374151' : '#cbd5e1' ?>">
                    <?= $row['nilai_temuan'] > 0 ? 'Rp '.number_format((int)$row['nilai_temuan'], 0, ',', '.') : '—' ?>
                </td>
                <td style="text-align:center">
                    <?php if($row['jml_rek'] > 0): ?>
                    <div style="font-size:11px;font-weight:600"><?= $row['jml_rek'] ?> rek</div>
                    <div style="font-size:10px;color:#94a3b8">
                        <span style="color:#22c55e"><?= $row['rek_selesai'] ?>S</span> ·
                        <span style="color:#f59e0b"><?= $row['rek_proses'] ?>DP</span> ·
                        <span style="color:#ef4444"><?= $row['rek_belum'] ?>B</span>
                    </div>
                    <?php else: ?>
                    <span style="color:#cbd5e1;font-size:11px">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center">
                    <?php if($isBuka): ?>
                    <span class="badge badge-danger">Terbuka</span>
                    <?php else: ?>
                    <span class="badge badge-success">Tertutup</span>
                    <?php endif; ?>
                </td>
                <td class="no-print">
                    <a href="/admin/spt/temuan/<?= $row['temuan_id'] ?>"
                       class="btn btn-xs btn-secondary" title="Detail Temuan">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- Ringkasan bawah -->
        <div style="padding:12px 16px;border-top:1px solid #f1f5f9;display:flex;gap:20px;font-size:12px;color:#64748b;flex-wrap:wrap">
            <span>Total temuan: <strong><?= count($rows) ?></strong></span>
            <span><span class="badge badge-danger">Terbuka</span> <?= $totalBuka ?></span>
            <span><span class="badge badge-success">Tertutup</span> <?= $totalTutup ?></span>
            <?php if($totalNilai > 0): ?>
            <span>Total nilai: <strong>Rp <?= number_format($totalNilai, 0, ',', '.') ?></strong></span>
            <?php endif; ?>
            <span style="margin-left:auto">
                <a href="/admin/laporan/ikhtisar-lhp?tahun=<?= $tahun ?>" style="color:#6366f1;font-weight:600">
                    <i class="fas fa-table-list"></i> Lihat Ikhtisar LHP →
                </a>
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
