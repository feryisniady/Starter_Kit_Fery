<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Routing Slip — <?= esc($slip['judul']) ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body {
    font-family:'Times New Roman',Times,serif;
    font-size:12pt;
    color:#000;
    background:#fff;
    padding:20mm 25mm 20mm 30mm;
    line-height:1.5;
}
.print-bar {
    position:fixed;top:0;left:0;right:0;
    background:#1e40af;color:#fff;
    display:flex;align-items:center;gap:12px;
    padding:10px 20px;z-index:999;font-family:Arial,sans-serif;font-size:13px;
}
.print-bar a, .print-bar button {
    background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);
    color:#fff;padding:5px 14px;border-radius:5px;cursor:pointer;
    font-size:12px;text-decoration:none;font-family:Arial,sans-serif;
}
.print-bar button:hover, .print-bar a:hover { background:rgba(255,255,255,.3); }
.print-spacer { height:44px; }

h2.doc-title {
    font-size:14pt;font-weight:bold;text-align:center;
    text-transform:uppercase;letter-spacing:.5px;
    margin-bottom:4px;
}
.doc-sub { text-align:center;font-size:11pt;margin-bottom:20px; }
.divider { border:none;border-top:2px solid #000;margin:10px 0 16px; }
.divider-thin { border:none;border-top:1px solid #000;margin:8px 0 12px; }

table.info { width:100%;border-collapse:collapse;margin-bottom:16px; }
table.info td { padding:4px 6px;font-size:11pt;vertical-align:top; }
table.info td:first-child { width:35%;font-weight:normal; }
table.info td:nth-child(2) { width:5%;text-align:center; }

table.pipeline {
    width:100%;border-collapse:collapse;
    margin-bottom:20px;
    border:1px solid #000;
}
table.pipeline th {
    background:#f0f0f0;
    border:1px solid #000;
    padding:6px 10px;
    font-size:11pt;
    text-align:center;
}
table.pipeline td {
    border:1px solid #000;
    padding:8px 10px;
    font-size:11pt;
    vertical-align:top;
}
table.pipeline td.stage-label {
    font-weight:bold;text-align:center;width:20%;
    background:#fafafa;
}
table.pipeline td.ttd-box {
    text-align:center;vertical-align:bottom;
    padding-bottom:6px;
}
.ttd-area {
    height:55px;
    border-bottom:1px solid #000;
    display:block;
    width:120px;
    margin:0 auto;
}
.status-badge {
    display:inline-block;
    border:1px solid #000;
    padding:1px 8px;
    border-radius:3px;
    font-size:10pt;
    font-weight:bold;
}
.status-diterima   { background:#d4edda; }
.status-kembalikan { background:#f8d7da; }
.status-pending    { background:#fff3cd; }

.keterangan-box {
    border:1px solid #000;
    padding:10px 12px;
    min-height:50px;
    margin-bottom:16px;
    font-size:11pt;
}

@media print {
    .print-bar, .print-spacer { display:none !important; }
    body { padding:15mm 20mm 15mm 25mm; }
    @page { size:A4 portrait; margin:0; }
}
</style>
</head>
<body>

<div class="print-bar">
    <span><i>&#128206;</i> Routing Slip — <?= esc($slip['judul']) ?></span>
    <button onclick="window.print()">&#128424; Cetak</button>
    <a href="/admin/spt/<?= $spt['id'] ?>/routing-slip">&#8592; Kembali</a>
</div>
<div class="print-spacer"></div>

<?php
$orgNama   = app_setting('org_nama')   ?: 'INSPEKTORAT KABUPATEN SAMPANG';
$orgAlamat = app_setting('org_alamat') ?: 'Jl. Jaksa Agung Suprapto No. 1, Sampang';

$hariId = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
           'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$blnId  = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
           7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

$tglCetak = date('d') . ' ' . $blnId[(int)date('n')] . ' ' . date('Y');

$jenisLabel = \App\Models\RoutingSlipModel::JENIS_LABEL;
$statusLabel = \App\Models\RoutingSlipModel::STATUS_LABEL;

// Helper: status badge HTML
$badgeHtml = function(string $status): string {
    $cls = match($status) {
        'diterima'     => 'diterima',
        'dikembalikan' => 'kembalikan',
        default        => 'pending',
    };
    $label = match($status) {
        'diterima'     => '✓ Diterima',
        'dikembalikan' => '✗ Dikembalikan',
        default        => '— Menunggu —',
    };
    return "<span class=\"status-badge status-{$cls}\">{$label}</span>";
};

$stages = [
    ['key'=>'kt',     'label'=>'Ketua Tim',          'nama'=>$slip['kt_nama']     ?? '',  'nip'=>$slip['kt_nip']     ?? '', 'status'=>$slip['kt_status'],     'tanggal'=>$slip['kt_tanggal'],     'catatan'=>$slip['kt_catatan']],
    ['key'=>'dalnis', 'label'=>'Pengendali Teknis',  'nama'=>$slip['dalnis_nama'] ?? '',  'nip'=>$slip['dalnis_nip'] ?? '', 'status'=>$slip['dalnis_status'], 'tanggal'=>$slip['dalnis_tanggal'], 'catatan'=>$slip['dalnis_catatan']],
    ['key'=>'pj',     'label'=>'PJ / Pengendali Mutu','nama'=>$slip['pj_nama']    ?? '',  'nip'=>$slip['pj_nip']     ?? '', 'status'=>$slip['pj_status'],     'tanggal'=>$slip['pj_tanggal'],     'catatan'=>$slip['pj_catatan']],
];
?>

<!-- HEADER -->
<h2 class="doc-title"><?= esc($orgNama) ?></h2>
<p class="doc-sub"><?= esc($orgAlamat) ?></p>
<hr class="divider">

<h2 class="doc-title" style="font-size:13pt;margin-bottom:2px">ROUTING SLIP</h2>
<p class="doc-sub" style="font-size:10pt">Lembar Pengantar Review Dokumen Pengawasan</p>
<hr class="divider-thin">

<!-- INFO DOKUMEN -->
<table class="info">
    <tr>
        <td>Nomor SPT</td><td>:</td>
        <td><strong><?= esc($spt['nomor_naskah'] ?: '-') ?></strong></td>
    </tr>
    <tr>
        <td>Obyek Pengawasan</td><td>:</td>
        <td><?= esc($spt['entitas_nama'] ?? $spt['irban_nama'] ?? '-') ?></td>
    </tr>
    <tr>
        <td>Judul Dokumen</td><td>:</td>
        <td><strong><?= esc($slip['judul']) ?></strong></td>
    </tr>
    <tr>
        <td>Jenis Dokumen</td><td>:</td>
        <td><?= esc($jenisLabel[$slip['jenis_dokumen'] ?? ''] ?? ($slip['jenis_dokumen'] ?: '-')) ?></td>
    </tr>
    <tr>
        <td>Dikirim oleh</td><td>:</td>
        <td><?= esc($slip['pengirim_nama'] ?? '-') ?>
            <?php if($slip['pengirim_jabatan'] ?? ''): ?>
            <br><small style="color:#555;font-size:10pt"><?= esc($slip['pengirim_jabatan']) ?></small>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td>Tanggal Kirim</td><td>:</td>
        <td><?= $slip['tanggal_kirim'] ? date('d M Y H:i', strtotime($slip['tanggal_kirim'])) : '-' ?></td>
    </tr>
    <tr>
        <td>Status Akhir</td><td>:</td>
        <td><?= $badgeHtml($slip['status'] === 'selesai' ? 'diterima' : ($slip['status'] === 'dikembalikan' ? 'dikembalikan' : 'pending')) ?>
            &nbsp;<span style="font-size:10pt">(<?= esc($statusLabel[$slip['status']] ?? $slip['status']) ?>)</span>
        </td>
    </tr>
</table>

<?php if($slip['keterangan']): ?>
<div style="margin-bottom:14px">
    <div style="font-size:10pt;font-weight:bold;margin-bottom:4px">Keterangan / Pokok Isi:</div>
    <div class="keterangan-box"><?= nl2br(esc($slip['keterangan'])) ?></div>
</div>
<?php endif; ?>

<!-- ALUR REVIEW -->
<div style="font-size:10pt;font-weight:bold;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">
    Alur Review Dokumen
</div>
<table class="pipeline">
    <thead>
        <tr>
            <th style="width:18%">Tahap</th>
            <th style="width:24%">Reviewer</th>
            <th style="width:16%">Tanggal</th>
            <th style="width:14%">Status</th>
            <th style="width:28%">Catatan</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($stages as $i => $s): ?>
        <tr>
            <td class="stage-label">
                <div style="font-size:10pt"><?= $i+1 ?></div>
                <div><?= esc($s['label']) ?></div>
            </td>
            <td>
                <?php if($s['nama']): ?>
                <strong><?= esc($s['nama']) ?></strong>
                <?php if($s['nip']): ?>
                <br><span style="font-size:10pt;color:#555">NIP <?= esc($s['nip']) ?></span>
                <?php endif; ?>
                <?php else: ?>
                <span style="color:#999;font-size:10pt;font-style:italic">—</span>
                <?php endif; ?>
            </td>
            <td style="text-align:center">
                <?= $s['tanggal'] ? date('d/m/Y<br\>H:i', strtotime($s['tanggal'])) : '—' ?>
            </td>
            <td style="text-align:center">
                <?= $badgeHtml($s['status']) ?>
            </td>
            <td style="font-size:10pt">
                <?= nl2br(esc($s['catatan'] ?? '')) ?: '<span style="color:#999;font-style:italic">—</span>' ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- TANDA TANGAN -->
<div style="margin-top:24px">
    <div style="font-size:10pt;font-weight:bold;margin-bottom:12px">Tanda Tangan</div>
    <div style="display:flex;gap:0;justify-content:space-between">

        <!-- Pengirim -->
        <div style="text-align:center;width:22%">
            <div style="font-size:10pt;margin-bottom:55px">Pengirim,</div>
            <div style="border-top:1px solid #000;padding-top:4px">
                <div style="font-size:10pt;font-weight:bold"><?= esc($slip['pengirim_nama'] ?? '.....................') ?></div>
                <?php if($slip['pengirim_jabatan'] ?? ''): ?>
                <div style="font-size:9pt;color:#555"><?= esc($slip['pengirim_jabatan']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- KT -->
        <div style="text-align:center;width:22%">
            <div style="font-size:10pt;margin-bottom:4px">Ketua Tim,</div>
            <?php if(($slip['kt_status'] ?? '') === 'diterima'): ?>
            <div style="font-size:9pt;color:#555;margin-bottom:4px"><?= $slip['kt_tanggal'] ? date('d/m/Y', strtotime($slip['kt_tanggal'])) : '' ?></div>
            <?php else: ?>
            <div style="height:20px"></div>
            <?php endif; ?>
            <div style="height:35px"></div>
            <div style="border-top:1px solid #000;padding-top:4px">
                <div style="font-size:10pt;font-weight:bold"><?= esc($slip['kt_nama'] ?: '.....................') ?></div>
                <?php if($slip['kt_nip'] ?? ''): ?>
                <div style="font-size:9pt;color:#555">NIP <?= esc($slip['kt_nip']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dalnis -->
        <div style="text-align:center;width:22%">
            <div style="font-size:10pt;margin-bottom:4px">Pengendali Teknis,</div>
            <?php if(($slip['dalnis_status'] ?? '') === 'diterima'): ?>
            <div style="font-size:9pt;color:#555;margin-bottom:4px"><?= $slip['dalnis_tanggal'] ? date('d/m/Y', strtotime($slip['dalnis_tanggal'])) : '' ?></div>
            <?php else: ?>
            <div style="height:20px"></div>
            <?php endif; ?>
            <div style="height:35px"></div>
            <div style="border-top:1px solid #000;padding-top:4px">
                <div style="font-size:10pt;font-weight:bold"><?= esc($slip['dalnis_nama'] ?: '.....................') ?></div>
                <?php if($slip['dalnis_nip'] ?? ''): ?>
                <div style="font-size:9pt;color:#555">NIP <?= esc($slip['dalnis_nip']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PJ -->
        <div style="text-align:center;width:22%">
            <div style="font-size:10pt;margin-bottom:4px">Penanggung Jawab,</div>
            <?php if(($slip['pj_status'] ?? '') === 'diterima'): ?>
            <div style="font-size:9pt;color:#555;margin-bottom:4px"><?= $slip['pj_tanggal'] ? date('d/m/Y', strtotime($slip['pj_tanggal'])) : '' ?></div>
            <?php else: ?>
            <div style="height:20px"></div>
            <?php endif; ?>
            <div style="height:35px"></div>
            <div style="border-top:1px solid #000;padding-top:4px">
                <div style="font-size:10pt;font-weight:bold"><?= esc($slip['pj_nama'] ?: '.....................') ?></div>
                <?php if($slip['pj_nip'] ?? ''): ?>
                <div style="font-size:9pt;color:#555">NIP <?= esc($slip['pj_nip']) ?></div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<div style="margin-top:24px;border-top:1px solid #ccc;padding-top:8px;font-size:9pt;color:#777;text-align:right">
    Dicetak: <?= $tglCetak ?> — SIMPAWAN <?= esc($orgNama) ?>
</div>

</body>
</html>
