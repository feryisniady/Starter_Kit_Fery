<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Reviewer Live - Inspektorat Sampang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 15px; }
        .card-header { 
            background: linear-gradient(45deg, #007bff, #0056b3); 
            color: white; 
            font-weight: bold; 
            border-radius: 15px 15px 0 0 !important;
            padding: 1.2rem;
        }
        #loading { display: none; }
        .hasil-konten { 
            white-space: pre-wrap; 
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            border-left: 5px solid #198754;
            line-height: 1.6;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
        }
        .btn-proses { transition: all 0.3s; font-weight: 600; }
        .btn-proses:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,123,255,0.3); }
        #box_hasil { display: none; }
        .sticky-opsi { position: sticky; top: 20px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-robot me-2"></i> Auditor AI Digital - Inspektorat Sampang</span>
                    <span class="badge bg-light text-primary">v1.5 Beta</span>
                </div>
                <div class="card-body p-4">
                    <form id="formReviu" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jenis Dokumen (Master):</label>
                                <select name="id_kriteria" class="form-select shadow-sm" id="id_kriteria">
                                    <?php foreach($list_kriteria as $k): ?>
                                        <option value="<?=$k->id_kriteria?>"><?=$k->nama_dokumen?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Pilih Dokumen (PDF/Gambar):</label>
                                <input type="file" name="dokumen" class="form-control shadow-sm" id="fileInput" accept="application/pdf,image/*" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Instruksi Tambahan (Opsional):</label>
                            <textarea name="instruksi_tambahan" class="form-control shadow-sm" rows="3" placeholder="Contoh: Fokus cek apakah ada tanda tangan Kepala Dinas dan kesesuaian nominal..."></textarea>
                        </div>

                        <button type="button" class="btn btn-primary btn-proses w-100 py-3 mb-2" id="btnProses" onclick="jalankanReviu()">
                            <i class="fas fa-magnifying-glass me-2"></i> Mulai Reviu Otomatis
                        </button>
                    </form>

                    <div id="loading" class="text-center my-5">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                        <h5 class="mt-3 text-primary fw-bold">Auditor AI sedang bekerja...</h5>
                        <p class="text-muted italic">Menganalisis berdasarkan standar KONDISI, KRITERIA, SEBAB, dan AKIBAT</p>
                    </div>

                    <div id="box_hasil" class="mt-4 animate__animated animate__fadeIn">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-success fw-bold m-0"><i class="fas fa-check-circle me-2"></i> Hasil Analisis Auditor AI:</h5>
                            <div id="areaDownload"></div>
                        </div>
                        <div class="hasil-konten shadow-sm" id="isi_analisis"></div>
                    </div>
                </div>
            </div>
            <p class="text-center mt-4 text-muted small">Powered by Auditor AI - Inspektorat Daerah Kabupaten Sampang</p>
        </div>
    </div>
</div>

<script>
async function jalankanReviu() {
    const btn = document.getElementById('btnProses');
    const form = document.getElementById('formReviu');
    const loading = document.getElementById('loading');
    const boxHasil = document.getElementById('box_hasil');
    const isiAnalisis = document.getElementById('isi_analisis');
    const areaDownload = document.getElementById('areaDownload');

    if(!document.getElementById('fileInput').files[0]) {
        alert("Mas Fery, pilih filenya dulu dong! Hahaha.");
        return;
    }

    // UI Feedback
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Memproses...';
    loading.style.display = 'block';
    boxHasil.style.display = 'none';
    areaDownload.innerHTML = ''; 

    const formData = new FormData(form);

    try {
        const response = await fetch("<?= base_url('reviu/prosesAjax') ?>", {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();

        if(result.status === 'success') {
            // Tampilkan hasil analisis
            isiAnalisis.innerText = result.analisis;
            
            // Masukan tombol Export PDF secara dinamis
            areaDownload.innerHTML = `
                <a href="<?= base_url('reviu/cetak') ?>/${result.id_reviu}" 
                   target="_blank" 
                   class="btn btn-success btn-sm shadow-sm">
                   <i class="fas fa-print me-1"></i> Cetak Laporan
                </a>
            `;
            
            boxHasil.style.display = 'block';
            
            // Auto scroll ke hasil
            boxHasil.scrollIntoView({ behavior: 'smooth' });
        } else {
            alert("Waduh, Error: " + (result.message || "Gagal Reviu"));
            console.log(result.debug);
        }
    } catch (e) {
        alert("Koneksi bermasalah, cek Laragon Mas Fery.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magnifying-glass me-2"></i> Mulai Reviu Otomatis';
        loading.style.display = 'none';
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>