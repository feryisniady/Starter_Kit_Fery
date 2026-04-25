<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Formulir 7b — <?= esc($spt['nomor_naskah'] ?: 'SPT #'.$spt['id']) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
@page { size:A4 portrait; margin:1.8cm 2cm 2cm; }
body { font-family:Arial,Helvetica,sans-serif; font-size:10pt; color:#000; background:#fff; }
.page { width:100%; max-width:175mm; margin:0 auto; }
.header-top { display:flex; justify-content:space-between; font-size:10pt; }
.form-no { font-weight:bold; }
.title { text-align:center; font-weight:bold; font-size:11pt; margin:16px 0 14px; line-height:1.5; }
.section { margin-bottom:10px; }
.section-label { font-weight:bold; margin-bottom:4px; }
.meta-row { display:flex; gap:4px; margin-bottom:3px; font-size:9.5pt; }
.meta-label { min-width:140px; }
.meta-value { flex:1; border-bottom:1px solid #000; }
table { width:100%; border-collapse:collapse; margin-top:8px; font-size:9pt; }
th, td { border:1px solid #000; padding:3px 5px; text-align:center; vertical-align:middle; }
th { background:#f0f0f0; font-weight:bold; }
td.left { text-align:left; }
tfoot td { font-weight:bold; }
.sig-block { display:flex; justify-content:flex-end; margin-top:24px; }
.sig-col { text-align:center; min-width:160px; }
.sig-title { font-size:9pt; margin-bottom:52px; }
.sig-name { font-weight:bold; border-top:1px solid #000; padding-top:3px; font-size:9pt; }
.sig-nip { font-size:8.5pt; }
@media print { .no-print{display:none!important}; body{-webkit-print-color-adjust:exact;print-color-adjust:exact} }
@media screen { body{background:#e5e7eb;padding:20px} .page{background:#fff;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.15)}
.print-btn{position:fixed;top:16px;right:16px;background:#1d4ed8;color:#fff;border:none;padding:8px 18px;border-radius:6px;font-size:13px;cursor:pointer}}
</style>
</head>
<body>
<button class="print-btn no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

<div class="page">

    <div class="header-top">
        <div>
            Nama Kementerian/Lembaga/Pemda<br>
            Nama Unit Kerja Eselon I/II
        </div>
        <div class="form-no">Formulir 7 b</div>
    </div>

    <div class="title">
        LAPORAN REKAPITULASI PERTANGGUNGJAWABAN PENGGUNAAN<br>
        JAM PENUGASAN KEGIATAN PENGAWASAN
    </div>

    <!-- A. Data ST/ND -->
    <div class="section">
        <div class="section-label">A. &nbsp; Data Surat Tugas (ST)/Nota Dinas (ND) Penugasan</div>
        <?php
        $inspektur = '';
        foreach (($spt['tim'] ?? []) as $t) {
            if (in_array($t['peran_spt'], ['PJ','Inspektur'])) { $inspektur = $t['sdm_nama'] ?? $t['nama'] ?? ''; break; }
        }
        ?>
        <div class="meta-row"><span class="meta-label">1. &nbsp; Pejabat Penerbit</span><span>:</span><span class="meta-value">&nbsp;<?= esc($inspektur ?: ($spt['irban_nama'] ?? '')) ?></span></div>
        <div class="meta-row"><span class="meta-label">2. &nbsp; Nomor ST/ND</span><span>:</span><span class="meta-value">&nbsp;<?= esc($spt['nomor_naskah'] ?? '') ?></span></div>
        <div class="meta-row"><span class="meta-label">3. &nbsp; Tanggal</span><span>:</span><span class="meta-value">&nbsp;<?= !empty($spt['tanggal_mulai']) ? date('d F Y', strtotime($spt['tanggal_mulai'])) : '' ?></span></div>
        <div class="meta-row"><span class="meta-label">4. &nbsp; Uraian</span><span>:</span><span class="meta-value">&nbsp;<?= esc($km1['kegiatan'] ?? $spt['tujuan'] ?? '') ?></span></div>
    </div>

    <!-- B. Data Dokumen Hasil -->
    <div class="section">
        <div class="section-label">B. &nbsp; Data Dokumen Hasil</div>
        <div class="meta-row"><span class="meta-label">1. &nbsp; Pejabat Penerbit</span><span>:</span><span class="meta-value">&nbsp;<?= esc($inspektur) ?></span></div>
        <div class="meta-row"><span class="meta-label">2. &nbsp; Nomor Laporan</span><span>:</span><span class="meta-value">&nbsp;<?= esc($nhp['nomor_nhp'] ?? $nhp['judul'] ?? '') ?></span></div>
        <div class="meta-row"><span class="meta-label">3. &nbsp; Tanggal Laporan</span><span>:</span><span class="meta-value">&nbsp;<?= !empty($nhp['tanggal_nhp']) ? date('d F Y', strtotime($nhp['tanggal_nhp'])) : (!empty($nhp['created_at']) ? date('d F Y', strtotime($nhp['created_at'])) : '') ?></span></div>
        <div class="meta-row"><span class="meta-label">4. &nbsp; Uraian</span><span>:</span><span class="meta-value">&nbsp;<?= esc($nhp['judul'] ?? '') ?></span></div>
    </div>

    <!-- C. Tabel Rekapitulasi -->
    <div class="section">
        <div class="section-label">C. &nbsp; Rekapitulasi Jam yang Dipertanggungjawabkan oleh Auditor</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="3" style="width:28px">No.</th>
                    <th rowspan="3">Nama Auditor</th>
                    <th rowspan="3">Jabatan</th>
                    <th rowspan="3">Peran</th>
                    <th colspan="4">Pertanggungjawaban Jam Kerja</th>
                </tr>
                <tr>
                    <th rowspan="2">Anggaran Waktu</th>
                    <th colspan="3">Realisasi</th>
                </tr>
                <tr>
                    <th style="width:48px">Normal</th>
                    <th style="width:48px">Lembur</th>
                    <th style="width:48px">Jumlah</th>
                </tr>
                <tr style="background:#f0f0f0">
                    <td style="font-weight:bold">(1)</td>
                    <td style="font-weight:bold">(2)</td>
                    <td style="font-weight:bold">(3)</td>
                    <td style="font-weight:bold">(4)</td>
                    <td style="font-weight:bold">(5)</td>
                    <td style="font-weight:bold">(6)</td>
                    <td style="font-weight:bold">(7)</td>
                    <td style="font-weight:bold">(8)</td>
                </tr>
            </thead>
            <tbody>
            <?php
            $totalAw    = 0;
            $totalReal  = 0;
            $totalJml   = 0;
            foreach ($timRows as $no => $row):
                $aw   = (int)($awBySdm[$row['sdm_id']]   ?? 0);
                $real = (int)($realBySdm[$row['sdm_id']] ?? 0);
                $jml  = $real; // lembur tidak ditrack, jumlah = normal
                $totalAw   += $aw;
                $totalReal += $real;
                $totalJml  += $jml;
                $jabatan = $row['jabatan_fungsional'] ?: $row['jabatan_struktural'];
            ?>
                <tr>
                    <td><?= $no + 1 ?></td>
                    <td class="left"><?= esc($row['nama']) ?><br><span style="font-size:7.5pt;color:#555">NIP. <?= esc($row['nip']) ?></span></td>
                    <td class="left" style="font-size:8pt"><?= esc($jabatan) ?></td>
                    <td><?= esc($row['peran_spt']) ?></td>
                    <td><?= $aw   > 0 ? $aw   : '' ?></td>
                    <td><?= $real > 0 ? $real : '' ?></td>
                    <td></td><!-- Lembur -->
                    <td><?= $jml  > 0 ? $jml  : '' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:center;font-weight:bold">Jumlah</td>
                    <td><?= $totalAw   > 0 ? $totalAw   : '' ?></td>
                    <td><?= $totalReal > 0 ? $totalReal : '' ?></td>
                    <td></td>
                    <td><?= $totalJml  > 0 ? $totalJml  : '' ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Tanda Tangan -->
    <?php
    $pmName = $pmSdm['sdm_nama'] ?? ($pmSdm['nama'] ?? null);
    $pmNip  = $pmSdm['nip'] ?? null;
    $pmJab  = $pmSdm['jabatan_struktural'] ?? 'Pengendali Mutu/Pejabat Struktural Minimal Eselon III';
    ?>
    <div class="sig-block">
        <div class="sig-col">
            <div class="sig-title">
                (<?= esc($spt['irban_nama'] ?? '') ?>, ............................)<br>
                <?= esc($pmJab ?: 'Pengendali Mutu/Pejabat Struktural Minimal Eselon III') ?>
            </div>
            <div style="margin-bottom:8px;font-size:9pt">Ttd</div>
            <div class="sig-name"><?= esc($pmName ?: 'Nama') ?></div>
            <div class="sig-nip">NIP. <?= esc($pmNip ?: '.....................') ?></div>
        </div>
    </div>

</div>
</body>
</html>
