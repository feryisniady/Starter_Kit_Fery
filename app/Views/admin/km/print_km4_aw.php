<?php
// ── Helpers ────────────────────────────────────────────────────────────────
$fmtTgl = fn(?string $d): string => $d ? date('d M Y', strtotime($d)) : '....................';
$hp     = fn(string $role, string $phase): float => (float)($agg[$role][$phase]['rencana'] ?? 0);
$hpFmt  = fn(float $v): string => $v > 0 ? number_format($v, 1, ',', '.') : '';

// Jumlah per baris (sum 4 roles)
$rowTotal = fn(string $phase): float =>
    $hp('pm', $phase) + $hp('pt', $phase) + $hp('kt', $phase) + $hp('at', $phase);

// Total per kolom (sum 3 phases)
$colTotal = fn(string $role): float => array_sum(array_map(fn($ph) => $hp($role, $ph), $phases));

$grandTotal = $rowTotal('persiapan') + $rowTotal('pelaksanaan') + $rowTotal('penyelesaian');

$phaseLabel = [
    'persiapan'    => 'PERSIAPAN PENGAWASAN',
    'pelaksanaan'  => 'PELAKSANAAN PENGAWASAN',
    'penyelesaian' => 'PENYELESAIAN PENGAWASAN',
];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Formulir KM-4 Alokasi Waktu — <?= esc($spt['nomor_naskah'] ?: 'SPT #'.$spt['id']) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

@page {
    size: A4 landscape;
    margin: 1.5cm 2cm;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9pt;
    color: #000;
    background: #fff;
}

.page {
    width: 100%;
    max-width: 270mm;
    margin: 0 auto;
    padding: 0;
}

/* ── Header Surat ──────────────────────────────── */
.doc-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 4px;
}
.doc-header .left { font-size: 9pt; font-weight: bold; line-height: 1.5; }
.doc-header .right { font-size: 9pt; font-weight: bold; text-align: right; }
.unit-kerja { font-size: 9pt; margin-bottom: 10px; }
.doc-title {
    text-align: center;
    font-size: 12pt;
    font-weight: bold;
    text-decoration: underline;
    margin: 8px 0 10px;
    letter-spacing: 1px;
}
.meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 16px;
    margin-bottom: 10px;
    font-size: 9pt;
}
.meta-row { display: flex; gap: 4px; }
.meta-label { white-space: nowrap; }
.meta-value { border-bottom: 1px solid #000; flex: 1; min-width: 80px; }

/* ── Tabel Utama ────────────────────────────────── */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5pt;
}
th, td {
    border: 1px solid #000;
    padding: 3px 5px;
    text-align: center;
    vertical-align: middle;
}
td.left { text-align: left; }
th { background: #d4c89a; font-weight: bold; }
.phase-header td {
    background: #f0ebe0;
    font-weight: bold;
    text-align: left;
    padding: 4px 6px;
}
.sub-row td { height: 18px; }
.subjumlah td {
    background: #f8f5ee;
    font-weight: bold;
}
.grand-total td {
    background: #e8e2d0;
    font-weight: bold;
}

/* ── Tanda Tangan ──────────────────────────────── */
.signature-block {
    display: flex;
    justify-content: space-between;
    margin-top: 16px;
    gap: 40px;
}
.sig-col { text-align: center; flex: 1; }
.sig-col .sig-title { margin-bottom: 48px; font-size: 9pt; }
.sig-col .sig-name { font-weight: bold; border-top: 1px solid #000; padding-top: 3px; font-size: 9pt; }
.sig-col .sig-nip { font-size: 8.5pt; }
.sig-date { text-align: right; font-size: 9pt; margin-bottom: 4px; }

/* ── Print ─────────────────────────────────────── */
@media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
}
@media screen {
    body { background: #e5e7eb; padding: 20px; }
    .page { background: #fff; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
    .print-btn {
        position: fixed; top: 16px; right: 16px;
        background: #1d4ed8; color: #fff; border: none;
        padding: 8px 18px; border-radius: 6px; font-size: 13px;
        cursor: pointer; display: flex; align-items: center; gap: 6px;
    }
    .print-btn:hover { background: #1e40af; }
}
</style>
</head>
<body>

<!-- Tombol Cetak (hanya tampil di layar) -->
<button class="print-btn no-print" onclick="window.print()">
    🖨️ Cetak / Simpan PDF
</button>

<div class="page">

    <!-- ── Header Dokumen ─────────────────────────── -->
    <div class="doc-header">
        <div class="left">
            BADAN PENGAWASAN KEUANGAN DAN PEMBANGUNAN
        </div>
        <div class="right">FORMULIR KM 4</div>
    </div>
    <div class="unit-kerja">
        UNIT KERJA &nbsp;: <?= esc($spt['irban_nama'] ?? '...............') ?>
    </div>

    <div class="doc-title">ALOKASI WAKTU PENGAWASAN</div>

    <!-- ── Info Dokumen ───────────────────────────── -->
    <div class="meta-grid">
        <div class="meta-row">
            <span class="meta-label">Nama Objek Pengawasan &nbsp;:</span>
            <span class="meta-value">&nbsp;<?= esc($spt['area_pengawasan'] ?? $spt['tujuan'] ?? '') ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Sasaran Pengawasan &nbsp;:</span>
            <span class="meta-value">&nbsp;<?= esc($spt['tujuan_sasaran'] ?? $spt['tujuan'] ?? '') ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Nomor Kartu Penugasan &nbsp;:</span>
            <span class="meta-value">&nbsp;<?= esc($km1['no_kartu'] ?? $spt['nomor_naskah'] ?? '') ?></span>
        </div>
        <div></div>
    </div>

    <!-- ── Tabel Alokasi Waktu ─────────────────────── -->
    <table>
        <thead>
            <!-- Row 1: Fase + rentang tanggal -->
            <tr>
                <th rowspan="4" style="width:35px">No.</th>
                <th rowspan="2" style="text-align:left;width:180px">
                    PERSIAPAN PENGAWASAN<br>
                    <span style="font-weight:normal;font-size:8pt">
                        dari tgl <?= $fmtTgl($dates['persiapan']['start']) ?>
                        &nbsp;s.d. tgl <?= $fmtTgl($dates['persiapan']['end']) ?>
                    </span>
                </th>
                <th colspan="2" style="width:80px">
                    PELAKSANAAN PENGAWASAN<br>
                    <span style="font-weight:normal;font-size:8pt">
                        dari tgl <?= $fmtTgl($dates['pelaksanaan']['start']) ?>
                        &nbsp;s.d. tgl <?= $fmtTgl($dates['pelaksanaan']['end']) ?>
                    </span>
                </th>
                <th colspan="3">
                    PENYELESAIAN PENGAWASAN<br>
                    <span style="font-weight:normal;font-size:8pt">
                        dari tgl <?= $fmtTgl($dates['penyelesaian']['start']) ?>
                        &nbsp;s.d. tgl <?= $fmtTgl($dates['penyelesaian']['end']) ?>
                    </span>
                </th>
            </tr>
            <!-- Row 2: "Anggaran Waktu" label -->
            <tr>
                <th colspan="5">Anggaran Waktu</th>
            </tr>
            <!-- Row 3: Nama kolom -->
            <tr>
                <th rowspan="2" style="text-align:left">Jenis Pekerjaan</th>
                <th style="width:55px">Pengendali<br>Mutu (PM)</th>
                <th style="width:55px">Pengendali<br>Teknis (PT)</th>
                <th style="width:55px">Ketua Tim<br>(KT)</th>
                <th style="width:55px">Anggota<br>Tim (AT)</th>
                <th style="width:60px">Jumlah<br>Anggaran<br>Waktu (JA)</th>
            </tr>
            <!-- Row 4: Nomor kolom -->
            <tr>
                <th>3</th><th>4</th><th>5</th><th>6</th><th>7</th>
            </tr>
            <tr style="background:#d4c89a">
                <td style="text-align:center;font-weight:bold">1</td>
                <td style="text-align:center;font-weight:bold">2</td>
                <td style="text-align:center;font-weight:bold">3</td>
                <td style="text-align:center;font-weight:bold">4</td>
                <td style="text-align:center;font-weight:bold">5</td>
                <td style="text-align:center;font-weight:bold">6</td>
                <td style="text-align:center;font-weight:bold">7</td>
            </tr>
        </thead>
        <tbody>
        <?php
        $romanNum = ['persiapan' => 'I', 'pelaksanaan' => 'II', 'penyelesaian' => 'III'];
        foreach ($phases as $phase):
            $phRow = $rowTotal($phase);
        ?>
            <!-- Header fase -->
            <tr class="phase-header">
                <td><?= $romanNum[$phase] ?></td>
                <td><?= $phaseLabel[$phase] ?></td>
                <td></td><td></td><td></td><td></td><td></td>
            </tr>
            <!-- Sub-baris aktivitas (kosong untuk diisi manual jika dicetak) -->
            <tr class="sub-row">
                <td></td><td class="left">&nbsp;</td>
                <td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="sub-row">
                <td></td><td class="left">&nbsp;</td>
                <td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="sub-row">
                <td></td><td class="left">&nbsp;</td>
                <td></td><td></td><td></td><td></td><td></td>
            </tr>
            <!-- Subjumlah -->
            <tr class="subjumlah">
                <td></td>
                <td class="left">Subjumlah</td>
                <td><?= $hpFmt($hp('pm', $phase)) ?></td>
                <td><?= $hpFmt($hp('pt', $phase)) ?></td>
                <td><?= $hpFmt($hp('kt', $phase)) ?></td>
                <td><?= $hpFmt($hp('at', $phase)) ?></td>
                <td><?= $hpFmt($phRow) ?></td>
            </tr>
        <?php endforeach; ?>

            <!-- Grand Total -->
            <tr class="grand-total">
                <td colspan="2" class="left">JUMLAH JAM PENGAWASAN (JP) YANG DIANGGARKAN</td>
                <td><?= $hpFmt($colTotal('pm')) ?></td>
                <td><?= $hpFmt($colTotal('pt')) ?></td>
                <td><?= $hpFmt($colTotal('kt')) ?></td>
                <td><?= $hpFmt($colTotal('at')) ?></td>
                <td><?= $hpFmt($grandTotal) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- ── Tanda Tangan ───────────────────────────── -->
    <?php
    // Cari PM dari spt.tim jika tidak ketemu dari awRows
    $pmName = $pmSdm['nama'] ?? null;
    $pmNip  = $pmSdm['nip']  ?? null;
    $pmJab  = $pmSdm['jabatan_struktural'] ?? 'Pengendali Mutu/ Pejabat Eselon III';
    $ktName = $ktSdm['nama'] ?? ($spt['penandatangan_nama'] ?? null);
    $ktNip  = $ktSdm['nip']  ?? ($spt['penandatangan_nip']  ?? null);

    // Jika PM tidak ada di AW, ambil dari tim SPT
    if (!$pmName) {
        foreach (($spt['tim'] ?? []) as $t) {
            if (in_array($t['peran_spt'], ['PJ','WPJ','Pengendali Mutu'])) {
                $pmName = $t['sdm_nama'];
                $pmNip  = $t['nip'];
                $pmJab  = $t['jabatan_struktural'] ?? 'Pengendali Mutu/ Pejabat Eselon III';
                break;
            }
        }
    }
    if (!$ktName) {
        foreach (($spt['tim'] ?? []) as $t) {
            if ($t['peran_spt'] === 'Ketua Tim') {
                $ktName = $t['sdm_nama'];
                $ktNip  = $t['nip'];
                break;
            }
        }
    }
    ?>

    <div class="sig-date">
        <?= esc($spt['irban_nama'] ?? '') ?>, ............................
    </div>
    <div class="signature-block">
        <div class="sig-col">
            <div class="sig-title">
                Disetujui oleh<br>
                <?= esc($pmJab ?: 'Pengendali Mutu/ Pejabat Eselon III') ?>
            </div>
            <div class="sig-name"><?= esc($pmName ?: 'Nama lengkap') ?></div>
            <div class="sig-nip">NIP. <?= esc($pmNip ?: '.....................') ?></div>
        </div>
        <div class="sig-col">
            <div class="sig-title">
                Disusun oleh<br>
                Ketua Tim
            </div>
            <div class="sig-name"><?= esc($ktName ?: 'Nama lengkap') ?></div>
            <div class="sig-nip">NIP. <?= esc($ktNip ?: '.....................') ?></div>
        </div>
    </div>

</div><!-- /.page -->

<script>
// Auto-print jika dibuka dari tombol cetak
if (window.location.search.includes('autoprint')) window.print();
</script>
</body>
</html>
