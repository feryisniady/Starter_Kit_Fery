<?php
$fmtTgl = fn(?string $d): string => $d ? date('d F Y', strtotime($d)) : '—';
$tanggapanLabel = ['pending' => 'Pending', 'sesuai' => 'Sesuai', 'tidak_sesuai' => 'Tidak Sesuai'];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>NHP — <?= esc($nhp['nomor_nhp'] ?? '#'.$nhp['id']) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
@page { size:A4 portrait; margin:1.8cm 2cm 2cm; }
body { font-family:Arial,Helvetica,sans-serif; font-size:9.5pt; color:#000; background:#fff; }
.page { width:100%; max-width:180mm; margin:0 auto; }

/* Header instansi */
.doc-header { text-align:center; margin-bottom:10px; }
.doc-header .instansi { font-size:11pt; font-weight:bold; text-transform:uppercase; letter-spacing:.5px; }
.doc-header .sub      { font-size:9pt; }
.doc-title { text-align:center; font-size:13pt; font-weight:bold;
             border-top:3px solid #000; border-bottom:1px solid #000;
             padding:5px 0; margin:8px 0 12px; }

/* Surat-style header */
.surat-meta { margin-bottom:10px; font-size:9.5pt; }
.surat-row  { display:flex; gap:4px; margin-bottom:3px; }
.surat-label{ min-width:120px; }
.surat-sep  { margin-right:4px; }

/* Tabel temuan */
table { width:100%; border-collapse:collapse; font-size:8pt; margin-bottom:14px; }
th,td { border:1px solid #000; padding:3px 5px; vertical-align:top; }
th { background:#f0f0f0; font-weight:bold; text-align:center; font-size:8pt; }
td.center { text-align:center; }
td.right  { text-align:right; }
td.no     { text-align:center; width:26px; }
.val-nil  { color:#c00; font-weight:bold; }

/* Finding card (alternatif tampilan per-item) */
.finding { border:1px solid #000; margin-bottom:10px; page-break-inside:avoid; }
.finding-header { background:#e8e8e8; padding:4px 8px; font-weight:bold; font-size:9pt;
                  border-bottom:1px solid #000; display:flex; justify-content:space-between; }
.finding-body   { padding:5px 8px; }
.f-row  { display:flex; gap:4px; margin-bottom:3px; font-size:8.5pt; }
.f-label{ min-width:130px; font-weight:500; }
.f-value{ flex:1; border-bottom:1px solid #ddd; min-height:12px; }
.badge-sesuai       { background:#d1fae5; color:#065f46; padding:1px 6px; border-radius:3px; font-size:7.5pt; }
.badge-tidak_sesuai { background:#fee2e2; color:#991b1b; padding:1px 6px; border-radius:3px; font-size:7.5pt; }
.badge-pending      { background:#fef9c3; color:#713f12; padding:1px 6px; border-radius:3px; font-size:7.5pt; }

/* Signature */
.sig-block { display:flex; justify-content:space-between; margin-top:24px; gap:20px; }
.sig-col   { text-align:center; flex:1; }
.sig-col .sig-title { font-size:9pt; margin-bottom:52px; }
.sig-col .sig-name  { font-weight:bold; border-top:1px solid #000; padding-top:3px; font-size:9pt; }
.sig-col .sig-nip   { font-size:8pt; }
.sig-date { text-align:right; font-size:9pt; margin:6px 0 4px; }

/* Print/screen */
@media print {
    body { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .no-print { display:none !important; }
}
@media screen {
    body { background:#e5e7eb; padding:20px; }
    .page { background:#fff; padding:24px; box-shadow:0 2px 12px rgba(0,0,0,.15); }
    .print-btn { position:fixed; top:16px; right:16px; background:#1d4ed8; color:#fff;
                 border:none; padding:8px 18px; border-radius:6px; font-size:13px; cursor:pointer; }
    .print-btn:hover { background:#1e40af; }
}
</style>
</head>
<body>

<button class="print-btn no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

<div class="page">

    <!-- Header Instansi -->
    <div class="doc-header">
        <div class="instansi">Inspektorat <?= esc($spt['irban_nama'] ?? 'Daerah') ?></div>
        <div class="sub">Pemerintah Daerah</div>
    </div>
    <div class="doc-title">NOTISI HASIL PEMERIKSAAN</div>

    <!-- Meta surat -->
    <div class="surat-meta">
        <div class="surat-row">
            <span class="surat-label">Nomor</span>
            <span class="surat-sep">:</span>
            <span><?= esc($nhp['nomor_nhp'] ?? '—') ?></span>
        </div>
        <div class="surat-row">
            <span class="surat-label">Tanggal</span>
            <span class="surat-sep">:</span>
            <span><?= $fmtTgl($nhp['tanggal_nhp']) ?></span>
        </div>
        <div class="surat-row">
            <span class="surat-label">Kepada</span>
            <span class="surat-sep">:</span>
            <span>
                <?php if (!empty($entitasList)): ?>
                    <?php foreach($entitasList as $ent): ?>
                        Kepala <?= esc($ent['nama']) ?>
                        <?php if ($ent['kepala']): ?>(<?= esc($ent['kepala']) ?>)<?php endif; ?><br>
                    <?php endforeach; ?>
                <?php else: ?>
                    (entitas belum diset)
                <?php endif; ?>
            </span>
        </div>
        <div class="surat-row">
            <span class="surat-label">Perihal</span>
            <span class="surat-sep">:</span>
            <span><?= esc($nhp['perihal'] ?: 'Notisi Hasil Pemeriksaan') ?></span>
        </div>
        <div class="surat-row">
            <span class="surat-label">SPT</span>
            <span class="surat-sep">:</span>
            <span><?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?></span>
        </div>
    </div>

    <?php if ($nhp['catatan']): ?>
    <div style="margin-bottom:10px;font-size:9pt;border-left:3px solid #ccc;padding-left:8px;color:#333">
        <em><?= esc($nhp['catatan']) ?></em>
    </div>
    <?php endif; ?>

    <!-- Daftar Temuan / Item NHP -->
    <div style="font-size:10pt;font-weight:bold;margin-bottom:8px;border-bottom:1px solid #000;padding-bottom:3px">
        DAFTAR TEMUAN HASIL PEMERIKSAAN
    </div>

    <?php if (empty($items)): ?>
    <p style="font-style:italic;color:#666;font-size:9pt">Tidak ada item temuan dalam NHP ini.</p>
    <?php else: ?>

    <?php foreach($items as $i => $item):
        $badgeClass = 'badge-' . ($item['status_tanggapan'] ?: 'pending');
        $badgeText  = $tanggapanLabel[$item['status_tanggapan']] ?? 'Pending';
    ?>
    <div class="finding">
        <div class="finding-header">
            <span>Temuan <?= $i + 1 ?>. <?= esc($item['judul_temuan'] ?: '(tanpa judul)') ?></span>
            <span class="<?= $badgeClass ?>"><?= $badgeText ?></span>
        </div>
        <div class="finding-body">
            <?php if ($item['kondisi']): ?>
            <div class="f-row">
                <span class="f-label">Kondisi</span>
                <span class="surat-sep">:</span>
                <span class="f-value"><?= wysiwyg_display($item['kondisi']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($item['kriteria']): ?>
            <div class="f-row">
                <span class="f-label">Kriteria</span>
                <span class="surat-sep">:</span>
                <span class="f-value"><?= wysiwyg_display($item['kriteria']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($item['sebab']): ?>
            <div class="f-row">
                <span class="f-label">Sebab</span>
                <span class="surat-sep">:</span>
                <span class="f-value"><?= wysiwyg_display($item['sebab']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($item['akibat']): ?>
            <div class="f-row">
                <span class="f-label">Akibat</span>
                <span class="surat-sep">:</span>
                <span class="f-value"><?= wysiwyg_display($item['akibat']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($item['rekomendasi']): ?>
            <div class="f-row">
                <span class="f-label">Rekomendasi</span>
                <span class="surat-sep">:</span>
                <span class="f-value" style="font-weight:500"><?= wysiwyg_display($item['rekomendasi']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ((int)$item['nilai_temuan'] > 0): ?>
            <div class="f-row">
                <span class="f-label">Nilai Temuan</span>
                <span class="surat-sep">:</span>
                <span class="f-value val-nil">Rp <?= number_format((int)$item['nilai_temuan'], 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <?php if ($item['tanggapan_entitas']): ?>
            <div class="f-row" style="border-top:1px dashed #ccc;padding-top:4px;margin-top:4px">
                <span class="f-label">Tanggapan Entitas</span>
                <span class="surat-sep">:</span>
                <span class="f-value"><?= wysiwyg_display($item['tanggapan_entitas']) ?></span>
            </div>
            <?php if ($item['tgl_tanggapan']): ?>
            <div class="f-row">
                <span class="f-label">Tanggal Tanggapan</span>
                <span class="surat-sep">:</span>
                <span class="f-value"><?= $fmtTgl($item['tgl_tanggapan']) ?></span>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Ringkasan nilai -->
    <?php
    $totalNilai = array_sum(array_column($items, 'nilai_temuan'));
    $jmlTidakSesuai = count(array_filter($items, fn($i) => $i['status_tanggapan'] === 'tidak_sesuai'));
    ?>
    <?php if ($totalNilai > 0 || $jmlTidakSesuai > 0): ?>
    <table style="margin-top:4px;width:auto;float:right">
        <tr>
            <th style="text-align:left">Jumlah Item</th>
            <td class="center"><?= count($items) ?></td>
        </tr>
        <?php if ($jmlTidakSesuai > 0): ?>
        <tr>
            <th style="text-align:left">Tidak Sesuai</th>
            <td class="center" style="color:#c00"><?= $jmlTidakSesuai ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($totalNilai > 0): ?>
        <tr>
            <th style="text-align:left">Total Nilai Temuan</th>
            <td class="right val-nil">Rp <?= number_format((int)$totalNilai, 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
    </table>
    <div style="clear:both"></div>
    <?php endif; ?>

    <?php endif; ?>

    <!-- Tanda Tangan -->
    <div class="sig-date">
        <?= esc($spt['irban_nama'] ?? 'Inspektorat') ?>, <?= $fmtTgl($nhp['tanggal_nhp']) ?>
    </div>
    <div class="sig-block">
        <?php if ($dalnisSdm): ?>
        <div class="sig-col">
            <div class="sig-title">Pengendali Teknis,</div>
            <div class="sig-name"><?= esc($dalnisSdm['nama_sdm'] ?? $dalnisSdm['nama'] ?? '...') ?></div>
            <div class="sig-nip">NIP. <?= esc($dalnisSdm['nip'] ?? '—') ?></div>
        </div>
        <?php endif; ?>
        <?php if ($ktSdm): ?>
        <div class="sig-col">
            <div class="sig-title">Ketua Tim,</div>
            <div class="sig-name"><?= esc($ktSdm['nama_sdm'] ?? $ktSdm['nama'] ?? '...') ?></div>
            <div class="sig-nip">NIP. <?= esc($ktSdm['nip'] ?? '—') ?></div>
        </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
