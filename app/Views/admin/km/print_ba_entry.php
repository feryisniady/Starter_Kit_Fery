<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Berita Acara Entry Meeting — <?= esc($spt['nomor_naskah'] ?: 'SPT #'.$spt['id']) ?></title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: "Times New Roman", Times, serif;
    font-size: 12pt;
    color: #000;
    background: #fff;
    padding: 0;
}

.page {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    padding: 25mm 25mm 20mm 30mm;
}

/* Header */
.header-title {
    text-align: center;
    font-size: 14pt;
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 2pt;
    letter-spacing: 1px;
}
.header-sub {
    text-align: center;
    font-size: 12pt;
    font-style: italic;
    font-weight: bold;
    margin-bottom: 16pt;
}

/* Body text */
p { line-height: 1.6; margin-bottom: 4pt; }

/* Info baris (Hari, Tanggal, dll) */
.info-table { margin-left: 10pt; margin-bottom: 10pt; border-collapse: collapse; }
.info-table td { padding: 1pt 0; vertical-align: top; font-size: 12pt; }
.info-table td:first-child { width: 60pt; }
.info-table td:nth-child(2) { width: 14pt; padding: 0 6pt; }
.info-table td:last-child { min-width: 300pt; }
.dotted { border-bottom: 1px dotted #000; display: inline-block; min-width: 250pt; }

/* Dua kolom tim */
.tim-wrapper { display: flex; gap: 0; margin-bottom: 10pt; }
.tim-col { flex: 1; }
.tim-col .tim-header { font-weight: bold; margin-bottom: 4pt; }
.tim-col .tim-row { margin-bottom: 3pt; display: flex; gap: 6pt; }
.tim-col .tim-no { width: 20pt; flex-shrink: 0; }
.tim-col .tim-name { flex: 1; border-bottom: 1px dotted #000; min-width: 140pt; }

/* Daftar kesepakatan */
.kesepakatan { margin-bottom: 10pt; }
.kesepakatan-title { font-weight: bold; margin-bottom: 6pt; }
ol.poin-list { padding-left: 18pt; }
ol.poin-list > li { margin-bottom: 8pt; line-height: 1.6; }
ul.bullet-list { list-style: disc; padding-left: 20pt; margin: 4pt 0; }
ul.bullet-list li { margin-bottom: 3pt; line-height: 1.6; }
.dotted-inline { border-bottom: 1px dotted #000; display: inline-block; min-width: 200pt; }
.dotted-short  { border-bottom: 1px dotted #000; display: inline-block; min-width: 100pt; }

/* Tanda tangan */
.ttd-wrapper { display: flex; justify-content: space-between; margin-top: 24pt; }
.ttd-col { text-align: center; width: 45%; }
.ttd-col .ttd-label { margin-bottom: 50pt; font-size: 12pt; }
.ttd-col .ttd-name { font-weight: bold; border-bottom: 1px solid #000; display: inline-block; min-width: 160pt; }
.ttd-col .ttd-nip  { margin-top: 3pt; font-size: 11pt; }
.ttd-kota { text-align: right; margin-top: 16pt; }

/* Print controls */
.print-bar {
    background: #1d4ed8;
    color: #fff;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: Arial, sans-serif;
    font-size: 13px;
    position: sticky;
    top: 0;
    z-index: 999;
}
.print-bar button, .print-bar a {
    background: #fff;
    color: #1d4ed8;
    border: none;
    padding: 6px 16px;
    border-radius: 4px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    font-size: 12px;
    margin-left: 8px;
}
.print-bar a.back { background: rgba(255,255,255,.2); color: #fff; }

@media print {
    .print-bar { display: none !important; }
    body { padding: 0; }
    .page { margin: 0; padding: 20mm 20mm 15mm 25mm; box-shadow: none; }
}
</style>
</head>
<body>

<!-- Tombol Print (tidak ikut cetak) -->
<div class="print-bar">
    <span><i>&#128247;</i> Preview: Berita Acara Entry Meeting — <?= esc($spt['nomor_naskah'] ?: 'SPT #'.$spt['id']) ?></span>
    <div>
        <a href="/admin/spt/<?= $spt['id'] ?>/km/5b" class="back">&#8592; Kembali</a>
        <button onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
    </div>
</div>

<div class="page">

<?php
// ─── Helpers ─────────────────────────────────────────────────────────────────
$dotted = fn(string $val, string $min = '250pt') => $val
    ? '<span style="display:inline-block;min-width:'.$min.';border-bottom:1px dotted #000">'. esc($val) .'</span>'
    : '<span style="display:inline-block;min-width:'.$min.';border-bottom:1px dotted #000">&nbsp;</span>';

// Waktu rapat
$waktuRapat = $row['waktu_rapat'] ?? '';
$hariStr    = $waktuRapat ? date('l', strtotime($waktuRapat)) : '';
$hariId     = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
               'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$hari       = $hariId[$hariStr] ?? $hariStr;
$tgl        = $waktuRapat ? date('d', strtotime($waktuRapat)) : '';
$bln        = $waktuRapat ? date('F', strtotime($waktuRapat)) : '';
$blnId      = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
               'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
               'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
$bulan      = $blnId[$bln] ?? $bln;
$tahun      = $waktuRapat ? date('Y', strtotime($waktuRapat)) : '';
$jam        = $waktuRapat ? date('H.i', strtotime($waktuRapat)).' WIB' : '';
$tanggalStr = $tgl ? "$tgl $bulan $tahun" : '';

// Nama auditi untuk kalimat pembuka
$namaAuditi = $row['nama_auditi'] ?? $spt['entitas_nama'] ?? '(nama auditi)';

// Tim Auditor — pisah per peran
$peranMap = ['pj'=>'Penanggung Jawab','wakil_pj'=>'Wakil Penanggung Jawab',
             'dalnis'=>'Pengendali Teknis','kt'=>'Ketua Tim','at'=>'Anggota Tim'];
$peranSort= ['pj'=>1,'wakil_pj'=>2,'dalnis'=>3,'kt'=>4,'at'=>5];
usort($timAuditor, fn($a,$b) => ($peranSort[$a['peran_spt']]??9) <=> ($peranSort[$b['peran_spt']]??9));

// Tanda tangan auditor — fallback ke penandatangan SPT
$ttdAuditorNama = $row['nama_auditor_ttd'] ?? $spt['penandatangan_nama'] ?? '';
$ttdAuditorNip  = $row['nip_auditor_ttd']  ?? $spt['penandatangan_nip']  ?? '';

// Waktu-waktu
$tglSp = '';
if (!empty($row['waktu_sp'])) {
    $d = date('d', strtotime($row['waktu_sp']));
    $m = $blnId[date('F', strtotime($row['waktu_sp']))] ?? date('F', strtotime($row['waktu_sp']));
    $y = date('Y', strtotime($row['waktu_sp']));
    $tglSp = "$d $m $y";
}
$tglLaporan = '';
if (!empty($row['rencana_laporan'])) {
    $d = date('d', strtotime($row['rencana_laporan']));
    $m = $blnId[date('F', strtotime($row['rencana_laporan']))] ?? date('F', strtotime($row['rencana_laporan']));
    $y = date('Y', strtotime($row['rencana_laporan']));
    $tglLaporan = "$d $m $y";
}

// PKA: tujuan & lingkup
$tujuan   = $spt['tujuan_sasaran']  ?? '';
$lingkup  = $spt['area_pengawasan'] ?? '';
$jenis    = $spt['jenis_pengawasan']?? '';
?>

    <!-- ═══ HEADER ═══════════════════════════════════════════════ -->
    <div class="header-title">Berita Acara</div>
    <div class="header-sub">Entry Meeting</div>

    <!-- ═══ KALIMAT PEMBUKA ══════════════════════════════════════ -->
    <p>
        Berdasarkan hasil rapat koordinasi antara tim audit dengan <?= esc($namaAuditi) ?>, maka pada:
    </p>

    <table class="info-table">
        <tr>
            <td>Hari</td><td>:</td>
            <td><?= $dotted($hari, '200pt') ?></td>
        </tr>
        <tr>
            <td>Tanggal</td><td>:</td>
            <td><?= $dotted($tanggalStr, '200pt') ?></td>
        </tr>
        <tr>
            <td>Waktu</td><td>:</td>
            <td><?= $dotted($jam, '200pt') ?></td>
        </tr>
        <tr>
            <td>Tempat</td><td>:</td>
            <td><?= $dotted($row['tempat'] ?? '', '200pt') ?></td>
        </tr>
    </table>

    <!-- ═══ DIHADIRI OLEH ════════════════════════════════════════ -->
    <p><strong>Dihadiri oleh:</strong></p>
    <div class="tim-wrapper">
        <!-- Tim Auditi -->
        <div class="tim-col">
            <div class="tim-header">Tim Auditi :</div>
            <?php if (!empty($timAuditi)): ?>
            <?php foreach($timAuditi as $i => $ta): ?>
            <div class="tim-row">
                <span class="tim-no"><?= $i+1 ?>.</span>
                <span class="tim-name"><?= esc($ta['nama']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <?php for($i=1;$i<=3;$i++): ?>
            <div class="tim-row">
                <span class="tim-no"><?= $i ?>.</span>
                <span class="tim-name">&nbsp;</span>
            </div>
            <?php endfor; ?>
            <?php endif; ?>
            <div style="margin-left:20pt;font-size:11pt">Dst</div>
        </div>
        <!-- Tim Auditor -->
        <div class="tim-col">
            <div class="tim-header">Tim Auditor :</div>
            <?php foreach($timAuditor as $i => $ta): ?>
            <div class="tim-row">
                <span class="tim-no"><?= $i+1 ?>.</span>
                <span class="tim-name"><?= esc($ta['sdm_nama']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if(empty($timAuditor)): ?>
            <?php for($i=1;$i<=3;$i++): ?>
            <div class="tim-row">
                <span class="tim-no"><?= $i ?>.</span>
                <span class="tim-name">&nbsp;</span>
            </div>
            <?php endfor; ?>
            <?php endif; ?>
            <div style="margin-left:20pt;font-size:11pt">Dst</div>
        </div>
    </div>

    <!-- ═══ KESEPAKATAN ══════════════════════════════════════════ -->
    <p>Diperoleh kesepakatan sebagai berikut :</p>
    <ol class="poin-list">

        <!-- 1. Tujuan -->
        <li>
            Tujuan audit:
            <ul class="bullet-list">
                <li><?= $tujuan ? esc($tujuan) : '<span class="dotted-inline">&nbsp;</span>' ?></li>
            </ul>
            Prosedur audit yang akan dilaksanakan sebagai berikut:
            <ul class="bullet-list">
                <li><span class="dotted-inline">&nbsp;</span></li>
            </ul>
            Ruang Lingkup Audit
            <ul class="bullet-list">
                <li><?= $lingkup ? esc($lingkup) : '<span class="dotted-inline">&nbsp;</span>' ?></li>
            </ul>
        </li>

        <!-- 2. Waktu -->
        <li>
            Waktu pelaksanaan audit
            <ul class="bullet-list">
                <li>Survei pendahuluan &nbsp;: <?= $dotted($tglSp, '150pt') ?></li>
                <li>Pelaksanaan audit &nbsp;&nbsp;&nbsp;: <?= $dotted($row['waktu_pelaksanaan'] ?? '', '150pt') ?></li>
                <li>Penyelesaian laporan : <?= $dotted($tglLaporan, '150pt') ?></li>
            </ul>
        </li>

        <!-- 3. Tim audit -->
        <li>
            Tim audit yang akan ditugaskan:
            <?php
            $peranBa = [
                'pj'      => 'Penanggung jawab',
                'wakil_pj'=> 'Wakil Penanggungjawab',
                'dalnis'  => 'Pengendali Teknis',
                'kt'      => 'Ketua Tim',
                'at'      => 'Anggota Tim',
            ];
            ?>
            <ul class="bullet-list">
                <?php foreach($peranBa as $key => $label):
                    $found = array_filter($timAuditor, fn($t) => $t['peran_spt'] === $key);
                    $names = implode(', ', array_map(fn($t) => $t['sdm_nama'], $found));
                ?>
                <li><?= $label ?> &nbsp;&nbsp;&nbsp;: <?= $dotted($names, '130pt') ?></li>
                <?php endforeach; ?>
            </ul>
        </li>

        <!-- 4. Kontak person -->
        <li>
            Dalam pelaksanaan survei dan audit, yang akan menjadi kontak person adalah
            <?= $dotted($row['cp'] ?? '', '100pt') ?>, telepon <?= $dotted($row['tlp_cp'] ?? '', '80pt') ?>.
            Survei pendahuluan akan dilakukan oleh tim auditor seperti audit biasa, namun tidak mendalam
            dan tidak rinci. Pelaksanaan audit akan dilakukan terhadap area yang telah difokuskan
            berdasarkan hasil survei pendahuluan.
        </li>

        <!-- 5. Poin tambahan (jika ada) -->
        <?php if (!empty($row['poin_5'])): ?>
        <li><?= nl2br(esc($row['poin_5'])) ?></li>
        <?php else: ?>
        <li><?= $dotted('', '350pt') ?></li>
        <?php endif; ?>

        <!-- 6. Pelaporan (teks baku) -->
        <li>
            Prosedur pelaporan dan tindak lanjut akan mengacu pada standar audit APIP dan
            tindakan koreksi terhadap rekomendasi temuan audit paling lambat akan dilakukan
            dalam waktu 60 hari setelah tanggal kesepakatan yang ditetapkan.
        </li>

        <!-- 7. Biaya (teks baku) -->
        <li>
            Seluruh biaya yang terjadi selama audit ditanggung oleh Inspektorat <?= esc($spt['kota_kabupaten'] ?? $namaKota) ?>.
        </li>

    </ol>

    <!-- ═══ TANDA TANGAN ═════════════════════════════════════════ -->
    <div class="ttd-kota">
        <?= esc($namaKota) ?>, <?= $tanggalStr ?: '&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;' ?>
    </div>

    <div class="ttd-wrapper">
        <div class="ttd-col">
            <div class="ttd-label">(Perwakilan Auditi)</div>
            <div class="ttd-name">(<?= esc($row['nama_auditi'] ?? '&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;') ?>)</div>
            <div class="ttd-nip">NIP. <?= esc($row['nip_auditi'] ?? '&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;') ?></div>
        </div>
        <div class="ttd-col">
            <div class="ttd-label">(Perwakilan Auditor)</div>
            <div class="ttd-name">(<?= esc($ttdAuditorNama ?: '&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;') ?>)</div>
            <div class="ttd-nip">NIP. <?= esc($ttdAuditorNip ?: '&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;') ?></div>
        </div>
    </div>

</div><!-- /page -->
</body>
</html>
