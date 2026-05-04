<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><?= esc($title) ?></h1>
        <p>Daftar SPT dengan status dan progress KKA/NHP</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="/admin/laporan/export-spt?tahun=<?= $tahun ?>&irban=<?= urlencode($filterIrban) ?>&status=<?= urlencode($filterStatus) ?>"
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
                <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Irban</label>
                <select name="irban" class="form-control" style="width:200px">
                    <option value="">Semua Irban</option>
                    <?php foreach($irbanList as $irb): ?>
                    <option value="<?= $irb['id'] ?>" <?= $filterIrban == $irb['id'] ? 'selected' : '' ?>><?= esc($irb['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Status</label>
                <select name="status" class="form-control" style="width:160px">
                    <option value="">Semua Status</option>
                    <?php foreach($statusLabel as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $filterStatus == $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Terapkan
            </button>
            <a href="/admin/laporan/rekap-spt?tahun=<?= $tahun ?>" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

<!-- Tabel -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-table"></i> Daftar SPT Tahun <?= $tahun ?>
            <span class="badge badge-secondary" style="margin-left:8px"><?= count($rows) ?> SPT</span>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if(empty($rows)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:32px;display:block;margin-bottom:8px;opacity:.4"></i>
            Tidak ada data SPT untuk filter yang dipilih.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="table table-striped" style="width:100%;font-size:13px">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Nomor SPT</th>
                    <th>Irban</th>
                    <th>Kegiatan / Jenis</th>
                    <th>Periode</th>
                    <th style="text-align:center">Durasi</th>
                    <th style="text-align:center">KKA</th>
                    <th style="text-align:center">NHP</th>
                    <th>Status</th>
                    <th class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($rows as $i => $row):
                $isOverdue = $row['status'] === 'terbit' && $row['tanggal_selesai'] && $row['tanggal_selesai'] < date('Y-m-d');
                $kkaProgress = $row['jumlah_kka'] > 0 ? round($row['kka_approved']/$row['jumlah_kka']*100) : 0;
            ?>
                <tr style="<?= $isOverdue ? 'background:#fff5f5' : '' ?>">
                    <td><?= $i + 1 ?></td>
                    <td>
                        <strong><?= esc($row['nomor_naskah'] ?: '-') ?></strong>
                        <?php if($isOverdue): ?>
                        <br><span style="font-size:11px;color:#ef4444"><i class="fas fa-triangle-exclamation"></i> Melewati jadwal</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px"><?= esc($row['irban_nama'] ?: '-') ?></td>
                    <td style="max-width:220px">
                        <?php if($row['kode_kegiatan']): ?>
                            <span style="font-size:11px;color:#64748b"><?= esc($row['kode_kegiatan']) ?></span><br>
                            <span><?= esc($row['tujuan_sasaran'] ?: $row['area_pengawasan']) ?></span>
                        <?php else: ?>
                            <span style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px"><?= esc($row['jenis_non_pkpt'] ?: 'Non-PKPT') ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;font-size:12px">
                        <?= $row['tanggal_mulai'] ? date('d/m/Y', strtotime($row['tanggal_mulai'])) : '-' ?>
                        <?php if($row['tanggal_selesai']): ?>
                        <br>s.d. <?= date('d/m/Y', strtotime($row['tanggal_selesai'])) ?>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <span style="font-weight:600"><?= $row['durasi_hari'] ?? '-' ?></span>
                        <span style="font-size:11px;color:#94a3b8"> hari</span>
                    </td>
                    <td style="text-align:center">
                        <?php if($row['jumlah_kka'] > 0): ?>
                        <div style="font-size:12px;margin-bottom:2px"><?= $row['kka_approved'] ?>/<?= $row['jumlah_kka'] ?> acc</div>
                        <div style="height:5px;background:#e2e8f0;border-radius:99px;overflow:hidden;width:60px;margin:0 auto">
                            <div style="width:<?= $kkaProgress ?>%;background:#22c55e;height:100%"></div>
                        </div>
                        <?php else: ?>
                        <span style="color:#94a3b8;font-size:12px">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php if($row['jumlah_nhp'] > 0): ?>
                        <span class="badge badge-info"><?= $row['jumlah_nhp'] ?> NHP</span>
                        <?php else: ?>
                        <span style="color:#94a3b8;font-size:12px">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= $statusColor[$row['status']] ?? 'secondary' ?>">
                            <?= $statusLabel[$row['status']] ?? $row['status'] ?>
                        </span>
                    </td>
                    <td class="no-print">
                        <a href="/admin/spt/<?= $row['id'] ?>" class="btn btn-sm btn-secondary" style="font-size:11px">
                            Detail <i class="fas fa-arrow-right"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="print-only" style="text-align:center;margin-bottom:16px;display:none">
    <div style="font-size:13pt;font-weight:bold;text-transform:uppercase">REKAP SURAT PERINTAH TUGAS (SPT)</div>
    <div style="font-size:10pt">INSPEKTORAT DAERAH — Tahun <?= $tahun ?></div>
    <hr style="border:1px solid #000;margin:6px 0">
</div>

<?= $this->endSection() ?>
