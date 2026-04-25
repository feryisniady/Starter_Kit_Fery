<?php
$fmtTgl  = fn(?string $d): string => $d ? date('d M Y', strtotime($d)) : '...................';
$faseLabel = ['persiapan' => 'Program Persiapan', 'pelaksanaan' => 'Program Pelaksanaan', 'pelaporan' => 'Pelaporan'];
$rowNum  = 0;
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Formulir KM-6 Program Pengawasan — <?= esc($spt['nomor_naskah'] ?: 'SPT #'.$spt['id']) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

@page { size: A4 portrait; margin: 1.8cm 2cm 2cm; }

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9.5pt;
    color: #000;
    background: #fff;
}

.page { width: 100%; max-width: 180mm; margin: 0 auto; }

/* ── Header ──────────────────────── */
.doc-header { display:flex; justify-content:space-between; margin-bottom:2px; font-weight:bold; font-size:9.5pt; }
.unit-kerja { font-size:9pt; margin-bottom:4px; }
.doc-subtitle { font-size:9pt; margin-bottom:12px; }
.doc-title { text-align:center; font-size:13pt; font-weight:bold; margin:10px 0 14px; }

/* ── Meta info ───────────────────── */
.meta { margin-bottom:12px; font-size:9.5pt; }
.meta-row { display:flex; gap:4px; margin-bottom:3px; }
.meta-label { min-width:160px; }
.meta-sep { }
.meta-value { flex:1; border-bottom:1px solid #000; min-width:80px; }

/* ── Sub-judul tabel ─────────────── */
.tbl-subtitle { font-size:9.5pt; font-weight:bold; margin-bottom:6px; }

/* ── Tabel utama ─────────────────── */
table { width:100%; border-collapse:collapse; font-size:8.5pt; }
th, td { border:1px solid #000; padding:3px 5px; vertical-align:middle; text-align:center; }
th { background:#f2f2f2; font-weight:bold; }
td.left { text-align:left; }
.phase-row td { background:#e8e8e8; font-weight:bold; text-align:left; padding:4px 6px; }
.data-row td { height:20px; }
.data-row td.left { text-align:left; }

/* ── Tanda tangan ────────────────── */
.sig-block { display:flex; justify-content:space-between; margin-top:20px; gap:40px; }
.sig-col { text-align:center; flex:1; }
.sig-col .sig-title { font-size:9pt; margin-bottom:52px; }
.sig-col .sig-name { font-weight:bold; border-top:1px solid #000; padding-top:3px; font-size:9pt; }
.sig-col .sig-nip { font-size:8.5pt; }
.sig-date { text-align:right; font-size:9pt; margin-bottom:4px; }

/* ── Print ─────────────────────────────── */
@media print {
    body { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .no-print { display:none !important; }
}
@media screen {
    body { background:#e5e7eb; padding:20px; }
    .page { background:#fff; padding:24px; box-shadow:0 2px 12px rgba(0,0,0,.15); }
    .print-btn {
        position:fixed; top:16px; right:16px;
        background:#1d4ed8; color:#fff; border:none;
        padding:8px 18px; border-radius:6px; font-size:13px; cursor:pointer;
    }
    .print-btn:hover { background:#1e40af; }
}
</style>
</head>
<body>

<button class="print-btn no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

<div class="page">

    <!-- ── Header ──────────────────────────────── -->
    <div class="doc-header">
        <div>BADAN PENGAWASAN KEUANGAN DAN PEMBANGUNAN</div>
        <div>FORMULIR KM 6</div>
    </div>
    <div class="doc-header" style="font-weight:bold">
        <div>UNIT KERJA :</div>
    </div>
    <div class="doc-subtitle"><?= esc($spt['irban_nama'] ?? '...............') ?></div>

    <div class="doc-title">PROGRAM PENGAWASAN</div>

    <!-- ── Meta ────────────────────────────────── -->
    <div class="meta">
        <div class="meta-row">
            <span class="meta-label">Nama Objek Pengawasan</span>
            <span class="meta-sep">:</span>
            <span class="meta-value">&nbsp;<?= esc($spt['area_pengawasan'] ?? $spt['tujuan'] ?? '') ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Kode Obyek</span>
            <span class="meta-sep">:</span>
            <span class="meta-value">&nbsp;<?= esc($spt['kode_kegiatan'] ?? '') ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Program / Kegiatan</span>
            <span class="meta-sep">:</span>
            <span class="meta-value">&nbsp;<?= esc($km1['kegiatan'] ?? $spt['tujuan'] ?? '') ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Lokasi</span>
            <span class="meta-sep">:</span>
            <span class="meta-value">&nbsp;</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Periode yang diawasi</span>
            <span class="meta-sep">:</span>
            <span class="meta-value">&nbsp;<?= esc($spt['tahun'] ?? '') ?></span>
        </div>
    </div>

    <!-- ── Sub-judul ───────────────────────────── -->
    <div class="tbl-subtitle">PROGRAM PERSIAPAN/PELAKSANAAN/PENYELESAIAN PENGAWASAN</div>

    <!-- ── Tabel PKA ───────────────────────────── -->
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:35px">No</th>
                <th rowspan="2">Tujuan dan Prosedur Pengawasan</th>
                <th colspan="2">Rencana</th>
                <th colspan="2">Realisasi</th>
                <th rowspan="2" style="width:45px">No KKP</th>
                <th rowspan="2" style="width:40px">Ket.</th>
            </tr>
            <tr>
                <th style="width:70px">Dilaksanakan oleh</th>
                <th style="width:45px">Waktu</th>
                <th style="width:70px">Dilaksanakan oleh</th>
                <th style="width:45px">Waktu</th>
            </tr>
            <tr style="background:#f2f2f2">
                <td style="font-weight:bold;text-align:center">1</td>
                <td style="font-weight:bold;text-align:center">2</td>
                <td style="font-weight:bold;text-align:center">3</td>
                <td style="font-weight:bold;text-align:center">4</td>
                <td style="font-weight:bold;text-align:center">5</td>
                <td style="font-weight:bold;text-align:center">6</td>
                <td style="font-weight:bold;text-align:center">7</td>
                <td style="font-weight:bold;text-align:center">8</td>
            </tr>
        </thead>
        <tbody>
        <?php
        $phaseNum = 0;
        foreach (['persiapan','pelaksanaan','pelaporan'] as $fase):
            $rows = $grouped[$fase] ?? [];
            $phaseNum++;
        ?>
            <!-- Header fase -->
            <tr class="phase-row">
                <td><?= $phaseNum ?>.</td>
                <td colspan="7"><?= $faseLabel[$fase] ?></td>
            </tr>

            <?php if (!empty($rows)): ?>
                <?php foreach($rows as $row):
                    $assignedNames = implode(', ', array_column($row['assigned_sdm'] ?? [], 'nama'));
                    $real = $realisasiByPka[(int)$row['id']] ?? null;
                    $realNames = $real ? implode(', ', $real['names']) : '';
                    $realWaktu = $real && $real['waktu'] > 0 ? $real['waktu'].' HP' : '';
                ?>
                <tr class="data-row">
                    <td><?= $row['nomor_urut'] ?></td>
                    <td class="left"><?= esc($row['uraian_prosedur']) ?></td>
                    <td style="font-size:7.5pt"><?= esc($assignedNames) ?></td>
                    <td><?= $row['rencana_waktu'] ? $row['rencana_waktu'].' HP' : '' ?></td>
                    <td style="font-size:7.5pt"><?= esc($realNames) ?></td>
                    <td><?= esc($realWaktu) ?></td>
                    <td></td><!-- No KKP -->
                    <td></td><!-- Ket -->
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Baris kosong jika tidak ada prosedur -->
                <tr class="data-row"><td></td><td class="left"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr class="data-row"><td></td><td class="left"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <?php endif; ?>

            <!-- Baris kosong tambahan untuk ruang tulis -->
            <tr class="data-row" style="height:16px"><td></td><td class="left"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>

        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ── Tanda Tangan ─────────────────────────── -->
    <?php
    $pmName = $pmSdm['sdm_nama'] ?? ($pmSdm['nama'] ?? null);
    $pmNip  = $pmSdm['nip']  ?? null;
    $pmJab  = $pmSdm['jabatan_struktural'] ?? 'Pengendali Mutu/ Pejabat Eselon III';
    $ktName = $ktSdm['sdm_nama'] ?? ($ktSdm['nama'] ?? null);
    $ktNip  = $ktSdm['nip']  ?? null;
    ?>
    <div class="sig-date">
        <?= esc($spt['irban_nama'] ?? '') ?>, ............................
    </div>
    <div class="sig-block">
        <div class="sig-col">
            <div class="sig-title">
                Menyetujui:<br>
                <?= esc($pmJab ?: 'Pengendali Mutu/ Pejabat Eselon III') ?>
            </div>
            <div class="sig-name"><?= esc($pmName ?: 'Nama') ?></div>
            <div class="sig-nip">NIP. <?= esc($pmNip ?: '.....................') ?></div>
        </div>
        <div class="sig-col">
            <div class="sig-title">
                Disusun oleh<br>
                Ketua Tim
            </div>
            <div class="sig-name"><?= esc($ktName ?: 'Nama') ?></div>
            <div class="sig-nip">NIP. <?= esc($ktNip ?: '.....................') ?></div>
        </div>
    </div>

</div><!-- /.page -->
</body>
</html>
