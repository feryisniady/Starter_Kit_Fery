<!DOCTYPE html>
<html>
<head>
    <title>Laporan Hasil Reviu AI</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; width: 150px; }
        .content-box { border: 1px solid #ccc; padding: 15px; background: #fafafa; white-space: pre-wrap; }
        h3 { border-left: 5px solid #007bff; padding-left: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Hasil Reviu Otomatis (Auditor AI)</div>
        <div>Inspektorat Kabupaten Sampang</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Jenis Dokumen</td>
            <td>: <?= $reviu['nama_dokumen']; ?></td>
        </tr>
        <tr>
            <td class="label">Tanggal Reviu</td>
            <td>: <?= $tanggal; ?></td>
        </tr>
        <tr>
            <td class="label">Instruksi Reviu</td>
            <td>: <?= $reviu['instruksi_ai']; ?></td>
        </tr>
    </table>

    <h3>Hasil Rekomendasi & Analisis:</h3>
    <div class="content-box">
        <?= nl2br(esc($reviu['hasil_analisis'])); ?>
    </div>

    <div style="margin-top: 50px; text-align: right;">
        <p>Dicetak secara otomatis oleh Sistem Auditor AI<br>Pada: <?= date('d/m/Y H:i'); ?></p>
    </div>
</body>
</html>