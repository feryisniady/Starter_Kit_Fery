<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$fmtRp  = fn($v) => 'Rp ' . number_format((int)$v, 0, ',', '.');
$pct    = fn($n, $d) => $d > 0 ? round($n / $d * 100) : 0;

// Status TL badge
$tlBadge = [
    'belum'   => ['label' => 'B — Belum',       'color' => '#ef4444', 'bg' => '#fee2e2'],
    'proses'  => ['label' => 'DP — Dalam Proses','color' => '#d97706', 'bg' => '#fef3c7'],
    'selesai' => ['label' => 'S — Selesai',      'color' => '#16a34a', 'bg' => '#dcfce7'],
];
$jenisBadge = [
    'pkpt'     => ['label' => 'PKPT',     'color' => '#6366f1'],
    'non_pkpt' => ['label' => 'Non-PKPT', 'color' => '#f59e0b'],
];
?>

<!-- ══ Page Header ═══════════════════════════════════════════════════════ -->
<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-table-list"></i> Ikhtisar Hasil Pengawasan</h1>
        <p>Matriks Temuan &amp; Tindak Lanjut — Tahun <?= $tahun ?> &nbsp;|&nbsp;
           <span style="font-size:11px;color:#94a3b8">Format: Permenpan No. 42 Tahun 2011</span>
        </p>
    </div>
    <div class="page-actions">
        <a href="/admin/laporan/export-ikhtisar-lhp?tahun=<?= $tahun ?>&irban=<?= urlencode($filterIrban) ?>&jenis=<?= urlencode($filterJenis) ?>&status_tl=<?= urlencode($filterStatusTl) ?>"
           class="btn btn-success">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <button id="btn-export-pdf" onclick="exportPdf()" class="btn btn-primary">
            <i class="fas fa-file-pdf"></i> PDF
        </button>
        <a href="/admin/laporan" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- ══ Filter Bar ════════════════════════════════════════════════════════ -->
<div class="card mb-3 no-print">
    <div class="card-body" style="padding:12px 16px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Tahun</label>
                <select name="tahun" class="form-control form-control-sm" style="width:90px" onchange="this.form.submit()">
                    <?php foreach($tahunList as $t): ?>
                    <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Bidang / Irban</label>
                <select name="irban" class="form-control form-control-sm" style="width:160px">
                    <option value="">— Semua —</option>
                    <?php foreach($irbanList as $irb): ?>
                    <option value="<?= $irb['id'] ?>" <?= $filterIrban == $irb['id'] ? 'selected' : '' ?>><?= esc($irb['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">OPD / Entitas</label>
                <select name="entitas" class="form-control form-control-sm" style="width:180px">
                    <option value="">— Semua OPD —</option>
                    <?php foreach($entitasList as $ent): ?>
                    <option value="<?= $ent['id'] ?>" <?= $filterEntitas == $ent['id'] ? 'selected' : '' ?>><?= esc($ent['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Jenis</label>
                <select name="jenis" class="form-control form-control-sm" style="width:120px">
                    <option value="">— Semua —</option>
                    <option value="pkpt"     <?= $filterJenis === 'pkpt'     ? 'selected' : '' ?>>PKPT</option>
                    <option value="non_pkpt" <?= $filterJenis === 'non_pkpt' ? 'selected' : '' ?>>Non-PKPT</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Status TL</label>
                <select name="status_tl" class="form-control form-control-sm" style="width:140px">
                    <option value="">— Semua —</option>
                    <option value="belum"   <?= $filterStatusTl === 'belum'   ? 'selected' : '' ?>>B — Belum</option>
                    <option value="proses"  <?= $filterStatusTl === 'proses'  ? 'selected' : '' ?>>DP — Dalam Proses</option>
                    <option value="selesai" <?= $filterStatusTl === 'selesai' ? 'selected' : '' ?>>S — Selesai</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Terapkan</button>
                <a href="?tahun=<?= $tahun ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- ══ Konten PDF (semua di bawah ini masuk PDF) ═══════════════════════ -->
<div id="pdf-content">

<!-- ══ Header Dokumen (untuk cetak) ══════════════════════════════════════ -->
<div class="print-only" style="text-align:center;margin-bottom:16px">
    <div style="font-size:14pt;font-weight:bold;text-transform:uppercase">
        IKHTISAR LAPORAN HASIL PENGAWASAN
    </div>
    <div style="font-size:11pt">INSPEKTORAT DAERAH</div>
    <div style="font-size:10pt">Tahun <?= $tahun ?></div>
    <div style="font-size:9pt;color:#666;margin-top:4px">Berdasarkan Permenpan RB No. 42 Tahun 2011</div>
    <hr style="border:1px solid #000;margin:8px 0">
</div>

<!-- ══ BAGIAN I — Ringkasan Statistik ════════════════════════════════════ -->
<div style="margin-bottom:6px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px">
    <i class="fas fa-chart-bar"></i> Bagian I — Rekapitulasi Hasil Pengawasan Tahun <?= $tahun ?>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;text-align:center">
        <div style="font-size:28px;font-weight:700;color:#2563eb"><?= $jumlahTemuan ?></div>
        <div style="font-size:11px;color:#1d4ed8;font-weight:600">Total Temuan</div>
    </div>
    <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:14px;text-align:center">
        <div style="font-size:28px;font-weight:700;color:#d97706"><?= $jumlahRek ?></div>
        <div style="font-size:11px;color:#92400e;font-weight:600">Total Rekomendasi</div>
    </div>
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:14px;text-align:center">
        <div style="font-size:20px;font-weight:700;color:#16a34a"><?= $rekByStatus['selesai'] ?><span style="font-size:12px;color:#94a3b8"> / <?= $jumlahRek ?></span></div>
        <div style="font-size:11px;color:#166534;font-weight:600">TL Selesai</div>
        <div style="background:#e2e8f0;border-radius:99px;height:5px;margin-top:6px;overflow:hidden">
            <div style="background:#22c55e;height:100%;width:<?= $pct($rekByStatus['selesai'], $jumlahRek) ?>%;border-radius:99px"></div>
        </div>
    </div>
    <div style="background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;padding:14px;text-align:center">
        <div style="font-size:18px;font-weight:700;color:#7c3aed"><?= $fmtRp($totalNilai) ?></div>
        <div style="font-size:11px;color:#6b21a8;font-weight:600">Total Nilai Rekomendasi</div>
    </div>
</div>

<!-- Status TL bar -->
<?php if ($jumlahRek > 0): ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;margin-bottom:20px">
    <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:8px">Status Tindak Lanjut Rekomendasi</div>
    <div style="display:flex;gap:0;border-radius:6px;overflow:hidden;height:22px;margin-bottom:8px">
        <?php foreach (['selesai' => '#22c55e', 'proses' => '#f59e0b', 'belum' => '#ef4444'] as $st => $col): ?>
        <?php $p = $pct($rekByStatus[$st], $jumlahRek); if ($p > 0): ?>
        <div style="width:<?= $p ?>%;background:<?= $col ?>;display:flex;align-items:center;justify-content:center;font-size:10px;color:#fff;font-weight:700">
            <?= $p >= 8 ? $p.'%' : '' ?>
        </div>
        <?php endif; endforeach; ?>
    </div>
    <div style="display:flex;gap:16px;font-size:11px">
        <span><span style="color:#22c55e;font-weight:700">●</span> Selesai: <?= $rekByStatus['selesai'] ?> (<?= $pct($rekByStatus['selesai'], $jumlahRek) ?>%)</span>
        <span><span style="color:#f59e0b;font-weight:700">●</span> Dalam Proses: <?= $rekByStatus['proses'] ?> (<?= $pct($rekByStatus['proses'], $jumlahRek) ?>%)</span>
        <span><span style="color:#ef4444;font-weight:700">●</span> Belum: <?= $rekByStatus['belum'] ?> (<?= $pct($rekByStatus['belum'], $jumlahRek) ?>%)</span>
    </div>
</div>
<?php endif; ?>

<!-- ══ BAGIAN II — Rekap per OPD ═════════════════════════════════════════ -->
<?php if (!empty($rekapEntitas)): ?>
<div style="margin-bottom:6px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px">
    <i class="fas fa-building"></i> Bagian II — Rekap Temuan per OPD / Entitas
</div>
<div style="overflow-x:auto;margin-bottom:24px">
<table style="width:100%;border-collapse:collapse;font-size:12px">
    <thead>
        <tr style="background:#1e293b;color:#fff">
            <th style="padding:8px 10px;text-align:left;width:36px">No</th>
            <th style="padding:8px 10px;text-align:left">OPD / Instansi yang Diperiksa</th>
            <th style="padding:8px 10px;text-align:center;width:80px">Total Rek</th>
            <th style="padding:8px 10px;text-align:center;width:80px" title="Selesai">S</th>
            <th style="padding:8px 10px;text-align:center;width:80px" title="Dalam Proses">DP</th>
            <th style="padding:8px 10px;text-align:center;width:80px" title="Belum">B</th>
            <th style="padding:8px 10px;text-align:right;width:140px">Nilai (Rp)</th>
            <th style="padding:8px 10px;text-align:center;width:100px">% Selesai</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($rekapEntitas as $i => $re): ?>
    <?php $pctS = $pct($re['selesai'], $re['total']); ?>
    <tr style="background:<?= $i % 2 ? '#f8fafc' : '#fff' ?>;border-bottom:1px solid #e2e8f0">
        <td style="padding:7px 10px;color:#94a3b8;text-align:center"><?= $i + 1 ?></td>
        <td style="padding:7px 10px;font-weight:600"><?= esc($re['nama']) ?></td>
        <td style="padding:7px 10px;text-align:center;font-weight:700"><?= $re['total'] ?></td>
        <td style="padding:7px 10px;text-align:center;color:#16a34a;font-weight:600"><?= $re['selesai'] ?></td>
        <td style="padding:7px 10px;text-align:center;color:#d97706;font-weight:600"><?= $re['proses'] ?></td>
        <td style="padding:7px 10px;text-align:center;color:#ef4444;font-weight:600"><?= $re['belum'] ?></td>
        <td style="padding:7px 10px;text-align:right;font-size:11px"><?= $re['nilai'] > 0 ? $fmtRp($re['nilai']) : '—' ?></td>
        <td style="padding:7px 10px;text-align:center">
            <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden">
                <div style="background:<?= $pctS >= 80 ? '#22c55e' : ($pctS >= 40 ? '#f59e0b' : '#ef4444') ?>;height:100%;width:<?= $pctS ?>%;border-radius:99px"></div>
            </div>
            <div style="font-size:10px;margin-top:2px;color:#64748b"><?= $pctS ?>%</div>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr style="background:#f1f5f9;font-weight:700">
        <td colspan="2" style="padding:8px 10px;text-align:right">TOTAL</td>
        <td style="padding:8px 10px;text-align:center"><?= $jumlahRek ?></td>
        <td style="padding:8px 10px;text-align:center;color:#16a34a"><?= $rekByStatus['selesai'] ?></td>
        <td style="padding:8px 10px;text-align:center;color:#d97706"><?= $rekByStatus['proses'] ?></td>
        <td style="padding:8px 10px;text-align:center;color:#ef4444"><?= $rekByStatus['belum'] ?></td>
        <td style="padding:8px 10px;text-align:right"><?= $fmtRp($totalNilai) ?></td>
        <td style="padding:8px 10px;text-align:center"><?= $pct($rekByStatus['selesai'], $jumlahRek) ?>%</td>
    </tr>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- ══ BAGIAN III — Matriks Temuan Lengkap ═══════════════════════════════ -->
<div style="margin-bottom:8px;display:flex;align-items:center;justify-content:space-between">
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px">
        <i class="fas fa-table"></i> Bagian III — Matriks Temuan dan Tindak Lanjut
    </div>
    <div style="font-size:11px;color:#94a3b8">
        <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:4px;margin-right:4px">B = Belum</span>
        <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:4px;margin-right:4px">DP = Dalam Proses</span>
        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:4px">S = Selesai</span>
    </div>
</div>

<?php if (empty($rows)): ?>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;text-align:center;padding:48px;color:#94a3b8">
    <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px"></i>
    <div style="font-size:15px;font-weight:600;margin-bottom:6px">Belum Ada Temuan</div>
    <p style="font-size:13px">Temuan akan muncul otomatis setelah NHP diselesaikan dan ada item "Tidak Sesuai".</p>
</div>
<?php else: ?>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:11.5px;min-width:1100px">
    <thead>
        <tr style="background:#1e293b;color:#fff">
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;width:32px;text-align:center">No</th>
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;width:110px">No. SPT / LHP</th>
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;width:80px;text-align:center">Jenis Pengawasan</th>
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;width:130px">OPD / Instansi Diperiksa</th>
            <th colspan="3" style="padding:8px 6px;border:1px solid #334155;text-align:center">Temuan</th>
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;width:200px">Rekomendasi</th>
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;width:80px;text-align:center">Batas Waktu TL</th>
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;width:50px;text-align:center">Status TL</th>
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;width:110px;text-align:right">Nilai Rek (Rp)</th>
        </tr>
        <tr style="background:#334155;color:#fff">
            <th style="padding:6px;border:1px solid #334155;width:32px;text-align:center">No</th>
            <th style="padding:6px;border:1px solid #334155">Kondisi / Judul Temuan</th>
            <th style="padding:6px;border:1px solid #334155;width:80px;text-align:center">Nilai (Rp)</th>
        </tr>
        <tr style="background:#475569;color:#e2e8f0;font-size:10px">
            <td style="padding:4px 6px;border:1px solid #334155;text-align:center">1</td>
            <td style="padding:4px 6px;border:1px solid #334155">2</td>
            <td style="padding:4px 6px;border:1px solid #334155;text-align:center">3</td>
            <td style="padding:4px 6px;border:1px solid #334155">4</td>
            <td style="padding:4px 6px;border:1px solid #334155;text-align:center">5</td>
            <td style="padding:4px 6px;border:1px solid #334155">6</td>
            <td style="padding:4px 6px;border:1px solid #334155;text-align:center">7</td>
            <td style="padding:4px 6px;border:1px solid #334155">8</td>
            <td style="padding:4px 6px;border:1px solid #334155;text-align:center">9</td>
            <td style="padding:4px 6px;border:1px solid #334155;text-align:center">10</td>
            <td style="padding:4px 6px;border:1px solid #334155;text-align:right">11</td>
        </tr>
    </thead>
    <tbody>
    <?php
    $noUrut   = 0;
    $lastTemuan = null;
    $rowEven  = false;
    foreach ($rows as $r):
        $isNewTemuan = $r['temuan_id'] !== $lastTemuan;
        if ($isNewTemuan) { $noUrut++; $rowEven = !$rowEven; }
        $lastTemuan = $r['temuan_id'];
        $bg = $rowEven ? '#fff' : '#f8fafc';

        $stTl  = $r['status_tl'] ?? 'belum';
        $badge = $tlBadge[$stTl] ?? $tlBadge['belum'];
        $jenis = $r['jenis_spt'] ?? 'pkpt';
        $jBadge= $jenisBadge[$jenis] ?? $jenisBadge['pkpt'];
        $kodeJ = ($jenis === 'pkpt') ? ($r['jenis_pengawasan'] ?? 'Reguler') : ($r['jenis_non_pkpt'] ?? 'Mandatori');
    ?>
    <tr style="background:<?= $bg ?>;border-bottom:1px solid #e2e8f0;vertical-align:top">
        <?php if ($isNewTemuan): ?>
        <td style="padding:7px 6px;border:1px solid #e2e8f0;text-align:center;font-weight:700;color:#6366f1;vertical-align:middle"><?= $noUrut ?></td>
        <td style="padding:7px 6px;border:1px solid #e2e8f0;font-size:10.5px">
            <div style="font-weight:600;color:#1e293b"><?= esc($r['spt_nomor'] ?: '#'.$r['spt_id']) ?></div>
            <div style="color:#94a3b8;margin-top:2px"><?= esc($r['irban_nama'] ?? '—') ?></div>
        </td>
        <td style="padding:7px 6px;border:1px solid #e2e8f0;text-align:center">
            <span style="background:<?= $jBadge['color'] ?>20;color:<?= $jBadge['color'] ?>;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;display:block;margin-bottom:2px"><?= $jBadge['label'] ?></span>
            <span style="font-size:10px;color:#64748b"><?= esc(mb_strimwidth($kodeJ, 0, 30, '…')) ?></span>
        </td>
        <td style="padding:7px 6px;border:1px solid #e2e8f0;font-size:10.5px">
            <?php if ($r['entitas_nama']): ?>
            <div style="font-weight:600"><?= esc($r['entitas_nama']) ?></div>
            <?php if ($r['entitas_kode']): ?>
            <div style="color:#94a3b8;font-size:10px"><?= esc($r['entitas_kode']) ?></div>
            <?php endif; ?>
            <?php else: ?>
            <span style="color:#94a3b8;font-style:italic">Non-PKPT</span>
            <?php endif; ?>
        </td>
        <td style="padding:7px 6px;border:1px solid #e2e8f0;text-align:center;font-weight:700;color:#6366f1"><?= $noUrut ?></td>
        <td style="padding:7px 6px;border:1px solid #e2e8f0">
            <div style="font-weight:700;color:#1e293b;margin-bottom:3px"><?= esc($r['judul_temuan']) ?></div>
            <div style="color:#475569;margin-bottom:4px;line-height:1.4"><?= nl2br(esc($r['kondisi'] ?? '')) ?></div>
            <?php if (!empty($r['kode_temuan_kode'])): ?>
            <span style="background:#e0e7ff;color:#3730a3;font-size:9.5px;padding:1px 6px;border-radius:3px">
                <?= esc($r['kode_temuan_kode']) ?>
            </span>
            <?php endif; ?>
        </td>
        <td style="padding:7px 6px;border:1px solid #e2e8f0;text-align:right;font-size:10.5px">
            <?= $r['nilai_temuan'] > 0 ? $fmtRp($r['nilai_temuan']) : '—' ?>
        </td>
        <?php else: ?>
        <td colspan="7" style="padding:0;border:1px solid #e2e8f0;background:<?= $bg ?>"></td>
        <?php endif; ?>

        <!-- Rekomendasi + TL (setiap baris) -->
        <td style="padding:7px 6px;border:1px solid #e2e8f0;line-height:1.4">
            <?php if (!empty($r['rekomendasi_id'])): ?>
            <div style="font-size:10px;color:#94a3b8;margin-bottom:2px">Rek. <?= $r['rek_nomor'] ?>:</div>
            <?= nl2br(esc($r['isi_rekomendasi'])) ?>
            <?php else: ?>
            <span style="color:#94a3b8;font-style:italic">—</span>
            <?php endif; ?>
        </td>
        <td style="padding:7px 6px;border:1px solid #e2e8f0;text-align:center;font-size:10.5px">
            <?= $r['batas_waktu'] ? date('d/m/Y', strtotime($r['batas_waktu'])) : '—' ?>
            <?php if ($r['batas_waktu'] && $r['status_tl'] !== 'selesai' && $r['batas_waktu'] < date('Y-m-d')): ?>
            <div style="color:#ef4444;font-size:9.5px;font-weight:600"><i class="fas fa-triangle-exclamation"></i> Lewat</div>
            <?php endif; ?>
        </td>
        <td style="padding:7px 6px;border:1px solid #e2e8f0;text-align:center">
            <?php if (!empty($r['rekomendasi_id'])): ?>
            <span style="display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;
                         background:<?= $badge['bg'] ?>;color:<?= $badge['color'] ?>">
                <?= $stTl === 'belum' ? 'B' : ($stTl === 'proses' ? 'DP' : 'S') ?>
            </span>
            <?php else: ?>
            <span style="color:#94a3b8">—</span>
            <?php endif; ?>
        </td>
        <td style="padding:7px 6px;border:1px solid #e2e8f0;text-align:right;font-size:10.5px">
            <?= (!empty($r['rekomendasi_id']) && $r['nilai_rekomendasi'] > 0) ? $fmtRp($r['nilai_rekomendasi']) : '—' ?>
        </td>
    </tr>
    <?php endforeach; ?>

    <!-- Footer total -->
    <tr style="background:#1e293b;color:#fff;font-weight:700">
        <td colspan="6" style="padding:8px 10px;border:1px solid #334155;text-align:right">JUMLAH</td>
        <td style="padding:8px 10px;border:1px solid #334155;text-align:right">—</td>
        <td colspan="3" style="padding:8px 10px;border:1px solid #334155;text-align:center"><?= $jumlahRek ?> Rekomendasi</td>
        <td style="padding:8px 10px;border:1px solid #334155;text-align:right"><?= $fmtRp($totalNilai) ?></td>
    </tr>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- ══ Footer cetak ════════════════════════════════════════════════════ -->
<div class="print-only" style="margin-top:32px;font-size:10pt">
    <div style="display:flex;justify-content:space-between">
        <div>
            <div>Keterangan:</div>
            <div>B = Belum ditindaklanjuti</div>
            <div>DP = Dalam Proses tindak lanjut</div>
            <div>S = Sudah selesai ditindaklanjuti</div>
        </div>
        <div style="text-align:center;min-width:200px">
            <div>............, <?= date('d F Y') ?></div>
            <div style="margin-top:4px">Inspektur</div>
            <div style="margin-top:60px;border-top:1px solid #000;padding-top:4px">
                ________________________
            </div>
        </div>
    </div>
</div>

</div><!-- /pdf-content -->

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportPdf() {
    const btn = document.getElementById('btn-export-pdf');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    // Konten yang akan diekspor: semua section kecuali filter bar
    const element = document.getElementById('pdf-content');

    const opt = {
        margin:      [10, 10, 12, 10], // top, right, bottom, left (mm)
        filename:    'Ikhtisar-LHP-<?= $tahun ?>.pdf',
        image:       { type: 'jpeg', quality: 0.95 },
        html2canvas: { scale: 2, useCORS: true, letterRendering: true },
        jsPDF:       { unit: 'mm', format: 'a4', orientation: 'landscape' },
        pagebreak:   { mode: ['avoid-all', 'css', 'legacy'] },
    };

    html2pdf().set(opt).from(element).save()
        .then(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-pdf"></i> Download PDF';
        });
}
</script>
<?= $this->endSection() ?>
