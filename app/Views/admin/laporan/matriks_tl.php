<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$fmtRp = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');
$pct   = fn($n, $d) => $d > 0 ? round($n / $d * 100, 1) : 0;

$g = $grandTotal;
$pctTlGrand    = $pct($g['jml_selesai'],    $g['total_rekomendasi']);
$pctNilaiGrand = $pct($g['nilai_selesai'],  $g['total_nilai_rek']);
?>

<!-- ══ Page Header ═══════════════════════════════════════════════════════ -->
<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-chart-bar"></i> Matriks Tindak Lanjut</h1>
        <p>Rekapitulasi per Bidang · OPD · Tahun LHP &nbsp;|&nbsp;
           <?= $tahun ? '<strong>Tahun ' . $tahun . '</strong>' : '<strong>Semua Tahun</strong>' ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/laporan/export-matriks-tl?tahun=<?= $tahun ?>&irban=<?= urlencode($filterIrban) ?>"
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
    <div style="font-size:14pt;font-weight:bold;text-transform:uppercase">MATRIKS TINDAK LANJUT HASIL PENGAWASAN</div>
    <div style="font-size:10pt">INSPEKTORAT DAERAH<?= $tahun ? ' — TAHUN ' . $tahun : ' — SEMUA TAHUN' ?></div>
    <hr style="border:1px solid #000;margin:6px 0">
</div>

<!-- ══ Filter ════════════════════════════════════════════════════════════ -->
<div class="card mb-3 no-print">
    <div class="card-body" style="padding:12px 16px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Tahun LHP</label>
                <select name="tahun" class="form-control form-control-sm" style="width:120px">
                    <option value="0" <?= $tahun == 0 ? 'selected' : '' ?>>Semua Tahun</option>
                    <?php foreach($tahunList as $t): ?>
                    <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Bidang / Irban</label>
                <select name="irban" class="form-control form-control-sm" style="width:200px">
                    <option value="">— Semua Bidang —</option>
                    <?php foreach($irbanList as $irb): ?>
                    <option value="<?= $irb['id'] ?>" <?= $filterIrban == $irb['id'] ? 'selected' : '' ?>><?= esc($irb['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Terapkan</button>
                <a href="/admin/laporan/matriks-tl" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- ══ Summary Cards ═════════════════════════════════════════════════════ -->
<div class="grid-4 mb-4 no-print">
    <div class="stat-card" style="border-left:3px solid #6366f1">
        <div class="stat-icon" style="background:#ede9fe;color:#6366f1"><i class="fas fa-flag"></i></div>
        <div class="stat-info">
            <div class="label">Total Temuan</div>
            <div class="value"><?= number_format((int)$g['total_temuan']) ?></div>
            <div class="sub">Temuan aktif / terbuka</div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #0ea5e9">
        <div class="stat-icon" style="background:#e0f2fe;color:#0ea5e9"><i class="fas fa-list-check"></i></div>
        <div class="stat-info">
            <div class="label">Total Rekomendasi</div>
            <div class="value"><?= number_format((int)$g['total_rekomendasi']) ?></div>
            <div class="sub">
                <span style="color:#22c55e"><?= (int)$g['jml_selesai'] ?> selesai</span> ·
                <span style="color:#f59e0b"><?= (int)$g['jml_proses'] ?> proses</span> ·
                <span style="color:#ef4444"><?= (int)$g['jml_belum'] ?> belum</span>
            </div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #22c55e">
        <div class="stat-icon" style="background:#dcfce7;color:#22c55e"><i class="fas fa-percent"></i></div>
        <div class="stat-info">
            <div class="label">% TL (Jumlah)</div>
            <div class="value"><?= $pctTlGrand ?>%</div>
            <div class="sub">Rekomendasi yang selesai</div>
        </div>
    </div>
    <div class="stat-card" style="border-left:3px solid #7c3aed">
        <div class="stat-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-info">
            <div class="label">Nilai Sisa TL</div>
            <div class="value" style="font-size:15px"><?= $fmtRp($g['nilai_sisa']) ?></div>
            <div class="sub"><?= $pctNilaiGrand ?>% nilai terlaksana</div>
        </div>
    </div>
</div>

<!-- ══ Matriks Utama ════════════════════════════════════════════════════ -->
<?php if (empty($grouped)): ?>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;text-align:center;padding:48px;color:#94a3b8">
    <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px"></i>
    <div style="font-size:15px;font-weight:600;margin-bottom:6px">Belum Ada Data</div>
    <p>Data akan muncul otomatis setelah NHP diselesaikan dan ada rekomendasi tindak lanjut.</p>
</div>
<?php else: ?>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:12px;min-width:1100px">
    <thead>
        <!-- Header utama -->
        <tr style="background:#1e293b;color:#fff;font-size:11px">
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;width:32px;text-align:center">No</th>
            <th rowspan="2" style="padding:8px 10px;border:1px solid #334155;text-align:left;min-width:180px">OPD / Instansi</th>
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;text-align:center;width:55px">Thn LHP</th>
            <th rowspan="2" style="padding:8px 6px;border:1px solid #334155;text-align:center;width:60px">Total Temuan</th>
            <!-- Non-finansial -->
            <th colspan="5" style="padding:8px 6px;border:1px solid #334155;text-align:center;background:#1e3a5f">
                MATRIKS NON-FINANSIAL (Jumlah)
            </th>
            <!-- Finansial -->
            <th colspan="4" style="padding:8px 6px;border:1px solid #334155;text-align:center;background:#1a3a2f">
                MATRIKS FINANSIAL (Nilai Rp)
            </th>
        </tr>
        <tr style="background:#334155;color:#e2e8f0;font-size:10px">
            <!-- Non-finansial subs -->
            <th style="padding:6px;border:1px solid #475569;text-align:center;width:55px">Total Rek</th>
            <th style="padding:6px;border:1px solid #475569;text-align:center;width:40px;color:#86efac">S</th>
            <th style="padding:6px;border:1px solid #475569;text-align:center;width:40px;color:#fcd34d">DP</th>
            <th style="padding:6px;border:1px solid #475569;text-align:center;width:40px;color:#fca5a5">B</th>
            <th style="padding:6px;border:1px solid #475569;text-align:center;width:60px">% TL</th>
            <!-- Finansial subs -->
            <th style="padding:6px;border:1px solid #475569;text-align:right;min-width:120px">Nilai Rek (Rp)</th>
            <th style="padding:6px;border:1px solid #475569;text-align:right;min-width:120px;color:#86efac">Nilai Selesai</th>
            <th style="padding:6px;border:1px solid #475569;text-align:right;min-width:120px;color:#fca5a5">Nilai Sisa TL</th>
            <th style="padding:6px;border:1px solid #475569;text-align:center;width:60px">% Nilai</th>
        </tr>
        <!-- Nomor kolom -->
        <tr style="background:#475569;color:#cbd5e1;font-size:9.5px;font-style:italic">
            <?php foreach(['(1)','(2)','(3)','(4)','(5)','(6)','(7)','(8)','(9)','(10)','(11)','(12)','(13)'] as $n): ?>
            <td style="padding:3px 6px;border:1px solid #334155;text-align:center"><?= $n ?></td>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <?php
    $noUrut = 0;
    foreach ($grouped as $irban):
        $sub = $irban['sub'];
        $pctTlSub    = $pct($sub['jml_selesai'],   $sub['total_rekomendasi']);
        $pctNilaiSub = $pct($sub['nilai_selesai'],  $sub['total_nilai_rek']);
    ?>
    <!-- ═══ Section Header Irban ═══ -->
    <tr style="background:#e0e7ff">
        <td colspan="13" style="padding:7px 12px;border:1px solid #c7d2fe;font-weight:700;font-size:11.5px;color:#3730a3">
            <i class="fas fa-building-columns" style="margin-right:6px"></i>
            <?= esc($irban['nama']) ?>
        </td>
    </tr>

    <!-- ═══ Baris per OPD×Tahun ═══ -->
    <?php foreach ($irban['rows'] as $i => $r):
        $pctTl    = $pct($r['jml_selesai'],   $r['total_rekomendasi']);
        $pctNilai = $pct($r['nilai_selesai'],  $r['total_nilai_rek']);
        $noUrut++;
    ?>
    <tr style="background:<?= $noUrut % 2 ? '#f8fafc' : '#fff' ?>;border-bottom:1px solid #e2e8f0;vertical-align:middle">
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;color:#94a3b8;font-size:11px"><?= $noUrut ?></td>
        <td style="padding:6px 10px;border:1px solid #e2e8f0;font-weight:500"><?= esc($r['entitas_nama']) ?></td>
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;font-weight:600;color:#6366f1"><?= $r['tahun_lhp'] ?></td>
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;font-weight:700"><?= (int)$r['total_temuan'] ?></td>

        <!-- Non-finansial -->
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center"><?= (int)$r['total_rekomendasi'] ?></td>
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;color:#16a34a;font-weight:600"><?= (int)$r['jml_selesai'] ?></td>

        <?php /* DP — clickable jika > 0 */ ?>
        <?php if ((int)$r['jml_proses'] > 0): ?>
        <td onclick="openDetail(<?= (int)$r['entitas_id'] ?>,<?= (int)$r['tahun_lhp'] ?>,<?= (int)$irban['id'] ?>,'proses',<?= htmlspecialchars(json_encode($r['entitas_nama']), ENT_QUOTES) ?>)"
            style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;color:#d97706;font-weight:700;cursor:pointer;background:#fffbeb"
            title="Klik lihat <?= (int)$r['jml_proses'] ?> rekomendasi Dalam Proses">
            <?= (int)$r['jml_proses'] ?> <i class="fas fa-magnifying-glass" style="font-size:9px;opacity:.6"></i>
        </td>
        <?php else: ?>
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;color:#94a3b8">0</td>
        <?php endif; ?>

        <?php /* B — clickable jika > 0 */ ?>
        <?php if ((int)$r['jml_belum'] > 0): ?>
        <td onclick="openDetail(<?= (int)$r['entitas_id'] ?>,<?= (int)$r['tahun_lhp'] ?>,<?= (int)$irban['id'] ?>,'belum',<?= htmlspecialchars(json_encode($r['entitas_nama']), ENT_QUOTES) ?>)"
            style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;color:#dc2626;font-weight:700;cursor:pointer;background:#fff5f5"
            title="Klik lihat <?= (int)$r['jml_belum'] ?> rekomendasi Belum TL">
            <?= (int)$r['jml_belum'] ?> <i class="fas fa-magnifying-glass" style="font-size:9px;opacity:.6"></i>
        </td>
        <?php else: ?>
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;color:#94a3b8">0</td>
        <?php endif; ?>
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center">
            <div style="font-weight:700;font-size:12px;color:<?= $pctTl >= 80 ? '#16a34a' : ($pctTl >= 40 ? '#d97706' : '#dc2626') ?>"><?= $pctTl ?>%</div>
            <div style="background:#e2e8f0;border-radius:99px;height:4px;margin-top:3px;overflow:hidden">
                <div style="background:<?= $pctTl >= 80 ? '#22c55e' : ($pctTl >= 40 ? '#f59e0b' : '#ef4444') ?>;height:100%;width:<?= $pctTl ?>%;border-radius:99px"></div>
            </div>
        </td>

        <!-- Finansial -->
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;font-size:11px;color:#475569">
            <?= $r['total_nilai_rek'] > 0 ? $fmtRp($r['total_nilai_rek']) : '<span style="color:#cbd5e1">—</span>' ?>
        </td>
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;font-size:11px;color:#16a34a;font-weight:<?= $r['nilai_selesai'] > 0 ? '600' : '400' ?>">
            <?= $r['nilai_selesai'] > 0 ? $fmtRp($r['nilai_selesai']) : '<span style="color:#cbd5e1">—</span>' ?>
        </td>
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;font-size:11px;color:<?= $r['nilai_sisa'] > 0 ? '#dc2626' : '#94a3b8' ?>;font-weight:<?= $r['nilai_sisa'] > 0 ? '600' : '400' ?>">
            <?= $r['nilai_sisa'] > 0 ? $fmtRp($r['nilai_sisa']) : '<span style="color:#cbd5e1">—</span>' ?>
        </td>
        <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center">
            <?php if ($r['total_nilai_rek'] > 0): ?>
            <div style="font-weight:700;font-size:12px;color:<?= $pctNilai >= 80 ? '#16a34a' : ($pctNilai >= 40 ? '#d97706' : '#dc2626') ?>"><?= $pctNilai ?>%</div>
            <div style="background:#e2e8f0;border-radius:99px;height:4px;margin-top:3px;overflow:hidden">
                <div style="background:<?= $pctNilai >= 80 ? '#22c55e' : ($pctNilai >= 40 ? '#f59e0b' : '#ef4444') ?>;height:100%;width:<?= $pctNilai ?>%;border-radius:99px"></div>
            </div>
            <?php else: ?>
            <span style="color:#cbd5e1;font-size:11px">—</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>

    <!-- ═══ Subtotal Irban ═══ -->
    <tr style="background:#1e293b;color:#fff;font-weight:700;font-size:11.5px">
        <td colspan="2" style="padding:7px 12px;border:1px solid #334155;text-align:right;font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px">
            Subtotal <?= esc($irban['nama']) ?>
        </td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:center;color:#94a3b8">—</td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:center"><?= (int)$sub['total_temuan'] ?></td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:center"><?= (int)$sub['total_rekomendasi'] ?></td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:center;color:#86efac"><?= (int)$sub['jml_selesai'] ?></td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:center;color:#fcd34d"><?= (int)$sub['jml_proses'] ?></td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:center;color:#fca5a5"><?= (int)$sub['jml_belum'] ?></td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:center;color:<?= $pctTlSub >= 80 ? '#86efac' : ($pctTlSub >= 40 ? '#fcd34d' : '#fca5a5') ?>">
            <?= $pctTlSub ?>%
        </td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:right;font-size:11px"><?= $sub['total_nilai_rek'] > 0 ? $fmtRp($sub['total_nilai_rek']) : '—' ?></td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:right;font-size:11px;color:#86efac"><?= $sub['nilai_selesai'] > 0 ? $fmtRp($sub['nilai_selesai']) : '—' ?></td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:right;font-size:11px;color:#fca5a5"><?= $sub['nilai_sisa'] > 0 ? $fmtRp($sub['nilai_sisa']) : '—' ?></td>
        <td style="padding:7px 8px;border:1px solid #334155;text-align:center;color:<?= $pctNilaiSub >= 80 ? '#86efac' : ($pctNilaiSub >= 40 ? '#fcd34d' : '#fca5a5') ?>">
            <?= $sub['total_nilai_rek'] > 0 ? $pctNilaiSub . '%' : '—' ?>
        </td>
    </tr>
    <?php endforeach; ?>

    <!-- ═══ Grand Total ═══ -->
    <tr style="background:#0f172a;color:#fff;font-weight:800;font-size:12px">
        <td colspan="2" style="padding:10px 12px;border:1px solid #1e293b;text-align:right;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#94a3b8">
            Grand Total
        </td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:center;color:#94a3b8">—</td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:center"><?= number_format((int)$g['total_temuan']) ?></td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:center"><?= number_format((int)$g['total_rekomendasi']) ?></td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:center;color:#86efac"><?= number_format((int)$g['jml_selesai']) ?></td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:center;color:#fcd34d"><?= number_format((int)$g['jml_proses']) ?></td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:center;color:#fca5a5"><?= number_format((int)$g['jml_belum']) ?></td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:center;color:<?= $pctTlGrand >= 80 ? '#86efac' : ($pctTlGrand >= 40 ? '#fcd34d' : '#fca5a5') ?>;font-size:14px">
            <?= $pctTlGrand ?>%
        </td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:right;font-size:11px"><?= $g['total_nilai_rek'] > 0 ? $fmtRp($g['total_nilai_rek']) : '—' ?></td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:right;font-size:11px;color:#86efac"><?= $g['nilai_selesai'] > 0 ? $fmtRp($g['nilai_selesai']) : '—' ?></td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:right;font-size:11px;color:#fca5a5"><?= $g['nilai_sisa'] > 0 ? $fmtRp($g['nilai_sisa']) : '—' ?></td>
        <td style="padding:10px 8px;border:1px solid #1e293b;text-align:center;color:<?= $pctNilaiGrand >= 80 ? '#86efac' : ($pctNilaiGrand >= 40 ? '#fcd34d' : '#fca5a5') ?>;font-size:14px">
            <?= $g['total_nilai_rek'] > 0 ? $pctNilaiGrand . '%' : '—' ?>
        </td>
    </tr>
    </tbody>
</table>
</div>

<!-- Legenda -->
<div style="margin-top:12px;display:flex;gap:20px;font-size:11px;color:#64748b;flex-wrap:wrap" class="no-print">
    <span><strong>S</strong> = Selesai</span>
    <span><strong>DP</strong> = Dalam Proses</span>
    <span><strong>B</strong> = Belum ditindaklanjuti</span>
    <span style="color:#22c55e">■ ≥ 80% baik</span>
    <span style="color:#f59e0b">■ 40–79% sedang</span>
    <span style="color:#ef4444">■ &lt; 40% perlu perhatian</span>
    <a href="/admin/laporan/rekap-tl?tahun=<?= $tahun ?>" style="margin-left:auto;color:#6366f1;font-weight:600">
        <i class="fas fa-table-list"></i> Lihat Detail Rekomendasi →
    </a>
</div>

<!-- Footer cetak -->
<div class="print-only" style="margin-top:32px;font-size:10pt;display:none">
    <div style="display:flex;justify-content:space-between">
        <div>
            Keterangan: S = Selesai · DP = Dalam Proses · B = Belum
        </div>
        <div style="text-align:center;min-width:200px">
            <div>............, <?= date('d F Y') ?></div>
            <div style="margin-top:4px">Inspektur</div>
            <div style="margin-top:60px;border-top:1px solid #000;padding-top:4px">________________________</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ══ Modal Drill-down Detail TL ════════════════════════════════════════ -->
<div id="modal-detail-tl" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:9000;background:rgba(15,23,42,.55);align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:900px;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.3)">
        <!-- Header modal -->
        <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:flex-start;gap:12px">
            <div id="modal-icon" style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px"></div>
            <div style="flex:1;min-width:0">
                <h3 id="modal-title" style="margin:0;font-size:15px;font-weight:700;color:#1e293b"></h3>
                <p id="modal-sub" style="margin:3px 0 0;font-size:12px;color:#64748b"></p>
            </div>
            <button onclick="closeDetailTl()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#94a3b8;line-height:1;padding:0;flex-shrink:0" title="Tutup">×</button>
        </div>
        <!-- Body modal -->
        <div id="modal-body-tl" style="overflow-y:auto;flex:1"></div>
        <!-- Footer modal -->
        <div id="modal-footer-tl" style="padding:10px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#64748b">
            <span id="modal-count"></span>
            <button onclick="closeDetailTl()" class="btn btn-sm btn-secondary">Tutup</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function openDetail(entitasId, tahunLhp, irbanId, status, entitasNama) {
    const modal  = document.getElementById('modal-detail-tl');
    const title  = document.getElementById('modal-title');
    const sub    = document.getElementById('modal-sub');
    const body   = document.getElementById('modal-body-tl');
    const footer = document.getElementById('modal-count');
    const icon   = document.getElementById('modal-icon');

    const cfg = {
        belum : { label: 'Belum Ditindaklanjuti', color: '#dc2626', bg: '#fef2f2', ico: 'triangle-exclamation' },
        proses: { label: 'Dalam Proses',          color: '#d97706', bg: '#fffbeb', ico: 'clock'                },
    };
    const c = cfg[status] || cfg.belum;

    icon.style.background = c.bg;
    icon.style.color      = c.color;
    icon.innerHTML        = `<i class="fas fa-${c.ico}"></i>`;
    title.textContent     = entitasNama + (tahunLhp ? ' — ' + tahunLhp : '');
    sub.innerHTML         = `<span style="background:${c.bg};color:${c.color};padding:1px 8px;border-radius:4px;font-weight:600">${c.label}</span>`;
    body.innerHTML        = '<div style="text-align:center;padding:40px;color:#94a3b8"><i class="fas fa-spinner fa-spin fa-2x"></i><div style="margin-top:8px">Memuat data...</div></div>';
    footer.textContent    = '';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    fetch(`/admin/laporan/detail-belum-tl?entitas_id=${entitasId}&tahun_lhp=${tahunLhp}&irban_id=${irbanId}&status=${status}`)
        .then(r => r.json())
        .then(rows => renderDetailRows(rows, c))
        .catch(() => {
            body.innerHTML = '<div style="text-align:center;padding:40px;color:#ef4444"><i class="fas fa-circle-exclamation fa-2x"></i><div style="margin-top:8px">Gagal memuat data. Coba lagi.</div></div>';
        });
}

function renderDetailRows(rows, c) {
    const body   = document.getElementById('modal-body-tl');
    const footer = document.getElementById('modal-count');

    footer.textContent = rows.length + ' rekomendasi ditemukan';

    if (!rows.length) {
        body.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8"><i class="fas fa-circle-check fa-2x" style="color:#22c55e"></i><div style="margin-top:8px">Tidak ada rekomendasi untuk filter ini.</div></div>';
        return;
    }

    let html = `<table style="width:100%;border-collapse:collapse;font-size:12px">
        <thead>
            <tr style="background:#f1f5f9;position:sticky;top:0">
                <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e2e8f0;width:32px">#</th>
                <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e2e8f0">Temuan</th>
                <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e2e8f0">Rekomendasi</th>
                <th style="padding:8px 12px;text-align:center;border-bottom:2px solid #e2e8f0;white-space:nowrap">Batas Waktu</th>
                <th style="padding:8px 12px;text-align:right;border-bottom:2px solid #e2e8f0">Nilai (Rp)</th>
                <th style="padding:8px 12px;text-align:center;border-bottom:2px solid #e2e8f0" class="no-print">Aksi</th>
            </tr>
        </thead>
        <tbody>`;

    const today = new Date(); today.setHours(0,0,0,0);

    rows.forEach((r, i) => {
        const bg       = i % 2 ? '#f8fafc' : '#fff';
        const isOverdue= r.batas_waktu && new Date(r.batas_waktu) < today;
        const batas    = r.batas_waktu
            ? new Date(r.batas_waktu).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'})
            : '—';
        const nilai    = r.nilai_rekomendasi > 0
            ? 'Rp ' + parseInt(r.nilai_rekomendasi).toLocaleString('id-ID')
            : '—';

        html += `<tr style="background:${bg};border-bottom:1px solid #e2e8f0;vertical-align:top">
            <td style="padding:8px 12px;color:#94a3b8;font-size:11px">${i + 1}</td>
            <td style="padding:8px 12px">
                <div style="font-weight:700;color:#6366f1;font-size:11px">${r.nomor_temuan || 'Temuan'}</div>
                <div style="color:#1e293b;margin-top:2px;line-height:1.4">${r.judul_temuan || '—'}</div>
                <div style="color:#94a3b8;font-size:10px;margin-top:3px"><i class="fas fa-file-contract"></i> ${r.spt_nomor || ''}</div>
            </td>
            <td style="padding:8px 12px;line-height:1.5;color:#374151">
                <span style="font-size:10px;color:#94a3b8;display:block;margin-bottom:2px">Rek. ${r.nomor_urut || ''}</span>
                ${r.isi_rekomendasi || '—'}
            </td>
            <td style="padding:8px 12px;text-align:center;white-space:nowrap">
                <div style="color:${isOverdue ? '#dc2626' : '#374151'};font-weight:${isOverdue ? '700' : '400'}">${batas}</div>
                ${isOverdue ? `<div style="color:#dc2626;font-size:10px"><i class="fas fa-triangle-exclamation"></i> +${r.hari_lewat} hari</div>` : ''}
            </td>
            <td style="padding:8px 12px;text-align:right;color:${r.nilai_rekomendasi > 0 ? '#374151' : '#cbd5e1'};font-size:11px">${nilai}</td>
            <td style="padding:8px 12px;text-align:center">
                <a href="/admin/spt/${r.spt_id}" target="_blank" style="font-size:11px;color:#6366f1;white-space:nowrap">
                    <i class="fas fa-arrow-up-right-from-square"></i> Buka SPT
                </a>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    body.innerHTML = html;
}

function closeDetailTl() {
    document.getElementById('modal-detail-tl').style.display = 'none';
    document.body.style.overflow = '';
}

// Tutup modal jika klik di backdrop
document.getElementById('modal-detail-tl').addEventListener('click', function(e) {
    if (e.target === this) closeDetailTl();
});

// Tutup dengan Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDetailTl();
});
</script>
<?= $this->endSection() ?>
