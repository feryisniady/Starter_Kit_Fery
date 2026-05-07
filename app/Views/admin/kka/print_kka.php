<?php
$fmtTgl    = fn(?string $d): string => $d ? date('d M Y', strtotime($d)) : '..................';
$faseLabel = ['persiapan' => 'Persiapan', 'pelaksanaan' => 'Pelaksanaan', 'pelaporan' => 'Pelaporan'];
$rowNum    = 0;
$totalRencana   = 0;
$totalRealisasi = 0;
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>KKA — <?= esc($kka['nama'] ?? ('KKA #'.$kka['id'])) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
@page { size:A4 portrait; margin:1.8cm 2cm 2cm; }
body { font-family:Arial,Helvetica,sans-serif; font-size:9.5pt; color:#000; background:#fff; }
.page { width:100%; max-width:180mm; margin:0 auto; }
.doc-header { text-align:center; margin-bottom:10px; }
.doc-header .instansi { font-size:11pt; font-weight:bold; text-transform:uppercase; letter-spacing:.5px; }
.doc-header .unit     { font-size:9.5pt; }
.doc-title { text-align:center; font-size:13pt; font-weight:bold; border-top:3px solid #000; border-bottom:1px solid #000; padding:5px 0; margin:8px 0 10px; }
.meta { margin-bottom:10px; }
.meta-row { display:flex; gap:4px; margin-bottom:3px; font-size:9.5pt; }
.meta-label { min-width:150px; font-weight:500; }
.meta-sep   { margin-right:4px; }
.meta-value { flex:1; border-bottom:1px solid #000; }
table { width:100%; border-collapse:collapse; font-size:8.5pt; margin-bottom:12px; }
th,td { border:1px solid #000; padding:3px 5px; vertical-align:top; }
th { background:#f0f0f0; font-weight:bold; text-align:center; }
td.center { text-align:center; }
td.left   { text-align:left; }
.fase-row td { background:#e8e8e8; font-weight:bold; font-size:9pt; }
.section-title { font-size:10pt; font-weight:bold; margin:10px 0 6px; border-bottom:1px solid #000; padding-bottom:3px; }
.simpulan-box { border:1px solid #000; padding:6px 8px; margin-bottom:8px; font-size:8.5pt; }
.simpulan-box .sp-header { font-weight:bold; margin-bottom:4px; }
.sp-row { display:flex; gap:4px; margin-bottom:3px; }
.sp-label { min-width:130px; font-weight:500; }
.sp-value { flex:1; border-bottom:1px solid #ccc; min-height:12px; }
.sig-block { display:flex; justify-content:space-between; margin-top:20px; gap:30px; }
.sig-col   { text-align:center; flex:1; }
.sig-col .sig-title { font-size:9pt; margin-bottom:48px; }
.sig-col .sig-name  { font-weight:bold; border-top:1px solid #000; padding-top:3px; font-size:9pt; }
.sig-col .sig-nip   { font-size:8.5pt; }
.sig-date { text-align:right; font-size:9pt; margin-bottom:4px; }
.no-data  { text-align:center; color:#666; font-style:italic; }
/* ── Quill content di dalam sel tabel ───── */
td ol, td ul { margin:0; padding-left:14px; }
td li { margin:0; padding:0; line-height:1.4; }
td p  { margin:0; padding:0; }
@media print { body{-webkit-print-color-adjust:exact;print-color-adjust:exact;} .no-print{display:none!important;} }
@media screen {
    body { background:#e5e7eb; padding:20px; }
    .page { background:#fff; padding:24px; box-shadow:0 2px 12px rgba(0,0,0,.15); }
    .print-btn { position:fixed; top:16px; right:16px; background:#1d4ed8; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-size:13px; cursor:pointer; }
    .print-btn:hover { background:#1e40af; }
}
</style>
</head>
<body>

<button class="print-btn no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

<div class="page">

    <!-- Header -->
    <div class="doc-header">
        <div class="instansi">Inspektorat <?= esc($spt['irban_nama'] ?? '') ?></div>
        <div class="unit">Pemerintah Daerah</div>
    </div>

    <div class="doc-title">KERTAS KERJA AUDIT</div>

    <!-- Meta Info -->
    <div class="meta">
        <div class="meta-row">
            <span class="meta-label">No. SPT</span>
            <span class="meta-sep">:</span>
            <span class="meta-value"><?= esc($spt['nomor_naskah'] ?: '—') ?></span>
            <span class="meta-label" style="margin-left:20px">Tahun</span>
            <span class="meta-sep">:</span>
            <span class="meta-value"><?= esc($spt['tahun'] ?? date('Y')) ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Nama Auditor</span>
            <span class="meta-sep">:</span>
            <span class="meta-value"><?= esc($sdm['nama'] ?? '—') ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">NIP</span>
            <span class="meta-sep">:</span>
            <span class="meta-value"><?= esc($sdm['nip'] ?? '—') ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Objek Pemeriksaan</span>
            <span class="meta-sep">:</span>
            <span class="meta-value"><?= esc($spt['area_pengawasan'] ?? $spt['tujuan'] ?? '—') ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Periode Pemeriksaan</span>
            <span class="meta-sep">:</span>
            <span class="meta-value">
                <?= $fmtTgl($km1['tanggal_mulai'] ?? $spt['tanggal_mulai'] ?? null) ?>
                s.d.
                <?= $fmtTgl($km1['tanggal_selesai'] ?? $spt['tanggal_selesai'] ?? null) ?>
            </span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Ketua Tim</span>
            <span class="meta-sep">:</span>
            <span class="meta-value"><?= esc($ktSdm['nama'] ?? '—') ?></span>
        </div>
    </div>

    <!-- Tabel Prosedur & Hasil -->
    <div class="section-title">I. PROGRAM DAN HASIL PENGUJIAN</div>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="200">Uraian Prosedur</th>
                <th width="60">Fase</th>
                <th width="50">Rencana (HP)</th>
                <th>Hasil Observasi / Pengujian</th>
                <th width="50">Realisasi (HP)</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $lastFase = null;
        if (empty($prosedurData)):
        ?>
            <tr><td colspan="6" class="no-data">Belum ada prosedur yang di-assign ke auditor ini.</td></tr>
        <?php else: ?>
        <?php foreach ($prosedurData as $pd):
            $pka = $pd['pka'];
            $ikh = $pd['ikhtisar'];
            if ($pka['fase'] !== $lastFase):
                $lastFase = $pka['fase'];
        ?>
            <tr class="fase-row">
                <td colspan="6">Fase: <?= $faseLabel[$pka['fase']] ?? ucfirst($pka['fase']) ?></td>
            </tr>
        <?php endif; $rowNum++; ?>
            <tr>
                <td class="center"><?= $rowNum ?></td>
                <td class="left"><?= wysiwyg_display($pka['uraian_prosedur']) ?></td>
                <td class="center" style="font-size:8pt"><?= $faseLabel[$pka['fase']] ?? ucfirst($pka['fase']) ?></td>
                <td class="center"><?php $r = (float)($pka['rencana_waktu'] ?? 0); $totalRencana += $r; echo $r ?: '—'; ?></td>
                <td class="left" style="min-height:18px"><?= esc($ikh['hasil_observasi'] ?? '') ?></td>
                <td class="center"><?php $rw = (float)($ikh['realisasi_waktu'] ?? 0); $totalRealisasi += $rw; echo $rw ?: '—'; ?></td>
            </tr>
        <?php endforeach; ?>
            <tr style="font-weight:bold;background:#f9f9f9">
                <td colspan="3" style="text-align:right">Total</td>
                <td class="center"><?= $totalRencana ?></td>
                <td></td>
                <td class="center"><?= $totalRealisasi ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Simpulan Temuan -->
    <?php if (!empty($simpulan)): ?>
    <div class="section-title">II. SIMPULAN DAN TEMUAN</div>
    <?php foreach ($simpulan as $i => $sp): ?>
    <div class="simpulan-box">
        <div class="sp-header">Temuan <?= $i+1 ?>
            <?php if (!empty($sp['kode_temuan_kode'])): ?>
            <span style="font-weight:normal;font-size:8pt">(Kode: <?= wysiwyg_display($sp['kode_temuan_kode']) ?>)</span>
            <?php endif; ?>
        </div>
        <div class="sp-row"><span class="sp-label">Kondisi</span><span class="meta-sep">:</span><span class="sp-value"><?= wysiwyg_display($sp['kondisi']) ?></span></div>
        <div class="sp-row"><span class="sp-label">Kriteria</span><span class="meta-sep">:</span><span class="sp-value"><?= wysiwyg_display($sp['kriteria']) ?></span></div>
        <div class="sp-row"><span class="sp-label">Sebab</span><span class="meta-sep">:</span><span class="sp-value"><?= wysiwyg_display($sp['sebab']) ?></span></div>
        <div class="sp-row"><span class="sp-label">Akibat</span><span class="meta-sep">:</span><span class="sp-value"><?= wysiwyg_display($sp['akibat']) ?></span></div>
        <div class="sp-row"><span class="sp-label">Rekomendasi Awal</span><span class="meta-sep">:</span><span class="sp-value"><?= wysiwyg_display($sp['rekomendasi_awal']) ?></span></div>
        <?php if (!empty($sp['nilai_financial'])): ?>
        <div class="sp-row"><span class="sp-label">Nilai Temuan (Rp)</span><span class="meta-sep">:</span><span class="sp-value"><?= number_format($sp['nilai_financial'], 0, ',', '.') ?></span></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Tanda Tangan -->
    <div class="sig-date"><?= esc($spt['irban_nama'] ?? '') ?>, <?= date('d F Y') ?></div>
    <div class="sig-block">
        <div class="sig-col">
            <div class="sig-title">Mengetahui,<br>Ketua Tim</div>
            <div class="sig-name"><?= esc($ktSdm['nama'] ?? '..............................') ?></div>
            <div class="sig-nip">NIP. <?= esc($ktSdm['nip'] ?? '..............................') ?></div>
        </div>
        <div class="sig-col">
            <div class="sig-title">Dibuat oleh,<br>Anggota Tim</div>
            <div class="sig-name"><?= esc($sdm['nama'] ?? '..............................') ?></div>
            <div class="sig-nip">NIP. <?= esc($sdm['nip'] ?? '..............................') ?></div>
        </div>
    </div>

</div>
</body>
</html>
