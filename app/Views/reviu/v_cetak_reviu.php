<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Hasil Reviu AI - <?= $reviu['id_reviu'] ?></title>
    <style>
        /* Pengaturan Kertas A4 */
        @page { size: A4; margin: 20mm; }
        body { 
            font-family: 'Times New Roman', Times, serif; 
            line-height: 1.5; 
            color: #333; 
        }
        
        /* Header Kop Surat (Opsional) */
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .kop-surat h2 { margin: 0; text-transform: uppercase; font-size: 18pt; }
        .kop-surat p { margin: 5px 0; font-size: 10pt; }

        .judul-laporan {
            text-align: center;
            text-decoration: underline;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 25px;
        }

        /* Styling Tabel Data */
        .tabel-info { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .tabel-info td { padding: 5px; vertical-align: top; }
        .label { width: 30%; font-weight: bold; }
        .pemisah { width: 3%; }

        /* Styling Konten Hasil AI */
        .box-hasil {
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #f9f9f9;
            min-height: 300px;
            white-space: pre-wrap; /* Biar ganti baris dari AI tetap terjaga */
        }

        /* Footer Tanda Tangan */
        .footer-ttd {
            margin-top: 50px;
            float: right;
            width: 300px;
            text-align: center;
        }
        .space-ttd { height: 70px; }

        /* Sembunyikan tombol cetak saat diprint */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="background: #fff3cd; padding: 10px; text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Pencet Sini Untuk Cetak (Print)</button>
        <button onclick="window.history.back()" style="padding: 10px 20px; cursor: pointer;">Kembali</button>
    </div>

    <div class="kop-surat">
        <h2>Pemerintah Kabupaten Sampang</h2>
        <h2>Inspektorat Daerah</h2>
        <p>Jl. Wahid Hasyim No. 123, Sampang - Jawa Timur</p>
    </div>

    <div class="judul-laporan">
        Laporan Hasil Reviu Dokumen (AI Assistant)
    </div>

    <table class="tabel-info">
        <tr>
            <td class="label">ID Reviu</td>
            <td class="pemisah">:</td>
            <td>#<?= $reviu['id_reviu'] ?></td>
        </tr>
        <tr>
            <td class="label">Jenis Dokumen</td>
            <td class="pemisah">:</td>
            <td><?= $reviu['nama_dokumen'] ?></td>
        </tr>
        <tr>
            <td class="label">Tanggal Reviu</td>
            <td class="pemisah">:</td>
            <td><?= date('d/m/Y H:i', strtotime($reviu['created_at'])) ?></td>
        </tr>
    </table>

    <div style="font-weight: bold; margin-bottom: 10px;">Hasil Analisis:</div>
    <div class="box-hasil">
<?= $reviu['hasil_analisis'] ?>
    </div>

    <div class="footer-ttd">
        <p>Sampang, <?= date('d F Y') ?></p>
        <p>Auditor Ahli,</p>
        <div class="space-ttd"></div>
        <p><strong>( ________________________ )</strong></p>
        <p>NIP. .................................</p>
    </div>

</body>
</html>