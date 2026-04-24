<?= $this->extend('auditi/layouts/portal') ?>
<?= $this->section('content') ?>

<?php
$latestTl   = !empty($tls) ? $tls[0] : null;
$canKirim   = !$latestTl || $latestTl['status_verifikasi'] === 'revisi';
$batas      = $rek['batas_waktu'];
$overdue    = $batas && strtotime($batas) < time() && $latestTl?->['status_verifikasi'] !== 'diterima';
?>

<div class="page-header">
    <div style="display:flex;align-items:flex-start;gap:12px;justify-content:space-between;flex-wrap:wrap">
        <div>
            <h1><i class="fas fa-tasks" style="color:#7c3aed"></i> Tindak Lanjut Rekomendasi</h1>
            <p>SPT: <?= esc($rek['spt_nomor']) ?></p>
        </div>
        <a href="/auditi/tl" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<!-- Info Temuan & Rekomendasi -->
<div class="card" style="border-left:4px solid #7c3aed">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-exclamation-triangle" style="color:#7c3aed"></i> Temuan & Rekomendasi BPKP</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
                <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Judul Temuan</div>
                <div style="font-weight:600;font-size:14px"><?= esc($rek['judul']) ?></div>
            </div>
            <?php if ($rek['nilai_temuan']): ?>
            <div>
                <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Nilai Temuan</div>
                <div style="font-weight:700;font-size:16px;color:#ef4444">Rp <?= number_format($rek['nilai_temuan'],0,',','.') ?></div>
            </div>
            <?php endif; ?>
            <?php if ($rek['kondisi']): ?>
            <div>
                <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Kondisi</div>
                <div style="font-size:13px;line-height:1.6"><?= nl2br(esc($rek['kondisi'])) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($rek['sebab']): ?>
            <div>
                <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Sebab</div>
                <div style="font-size:13px;line-height:1.6"><?= nl2br(esc($rek['sebab'])) ?></div>
            </div>
            <?php endif; ?>
        </div>
        <div style="margin-top:14px;padding:14px;background:#f3f0ff;border-radius:10px;border-left:4px solid #7c3aed">
            <div style="font-size:10px;font-weight:700;color:#7c3aed;text-transform:uppercase;margin-bottom:4px">Rekomendasi BPKP</div>
            <div style="font-size:14px;line-height:1.7;font-weight:500"><?= nl2br(esc($rek['isi_rekomendasi'])) ?></div>
            <?php if ($batas): ?>
            <div style="margin-top:8px;font-size:12px;<?= $overdue?'color:#ef4444;font-weight:700':'color:#64748b' ?>">
                <i class="fas fa-calendar<?= $overdue?' fa-triangle-exclamation':'' ?>"></i>
                Batas Waktu: <?= date('d F Y', strtotime($batas)) ?>
                <?= $overdue ? ' — <strong>Melewati Batas!</strong>' : '' ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Form Kirim TL -->
<?php if ($canKirim): ?>
<div class="card" style="border-left:4px solid #2563eb">
    <div class="card-header">
        <span class="card-title">
            <i class="fas fa-paper-plane" style="color:#2563eb"></i>
            <?= $latestTl ? 'Kirim Perbaikan Tindak Lanjut' : 'Kirim Tindak Lanjut' ?>
        </span>
    </div>
    <div class="card-body">
        <?php if ($latestTl && $latestTl['status_verifikasi'] === 'revisi'): ?>
        <div class="alert alert-warning" style="margin-bottom:16px">
            <i class="fas fa-rotate-left"></i>
            <div>
                <strong>BPKP meminta perbaikan:</strong>
                <?= nl2br(esc($latestTl['catatan_verifikasi'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <form action="/auditi/tl/<?= $rek['id'] ?>/kirim" method="POST" enctype="multipart/form-data"
              data-confirm="Tindak lanjut ini akan dikirim ke BPKP untuk diverifikasi. Lanjutkan?"
              data-confirm-btn="<i class='fas fa-paper-plane'></i>&nbsp;Ya, Kirim">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Uraian Tindak Lanjut <span style="color:#ef4444">*</span></label>
                <textarea name="uraian" class="form-control" rows="5" required
                          placeholder="Jelaskan langkah-langkah yang telah dilakukan untuk menindaklanjuti rekomendasi ini. Contoh: &#10;1. Telah melakukan rekonsiliasi SPJ BBM dengan bendahara&#10;2. SPJ ditemukan dan dilampirkan (terlampir)&#10;3. Telah ditetapkan SOP pengelolaan BBM sebagai tindakan korektif"></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-paperclip"></i> Lampiran Bukti Dukung</label>
                <div class="upload-zone" id="upload-zone" onclick="document.getElementById('files-tl').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><strong>Klik atau seret file ke sini</strong></p>
                    <p style="margin-top:4px">PDF, Word, Excel, Gambar — maks. 10 MB per file, bisa multiple</p>
                </div>
                <input type="file" id="files-tl" name="dokumen[]" multiple style="display:none"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                       onchange="previewFiles(this.files)">
                <div id="preview-files" style="margin-top:8px"></div>
            </div>
            <button type="submit" class="btn btn-primary w-full" style="justify-content:center">
                <i class="fas fa-paper-plane"></i> Kirim Tindak Lanjut ke BPKP
            </button>
        </form>
    </div>
</div>
<?php elseif ($latestTl && $latestTl['status_verifikasi'] === 'menunggu'): ?>
<div class="alert alert-info" style="margin-bottom:20px">
    <i class="fas fa-clock" style="font-size:18px"></i>
    <div>
        <strong>Tindak lanjut sedang diverifikasi BPKP.</strong><br>
        Dikirim pada <?= date('d M Y H:i', strtotime($latestTl['created_at'])) ?>. Silakan tunggu hasil verifikasi.
    </div>
</div>
<?php elseif ($latestTl && $latestTl['status_verifikasi'] === 'diterima'): ?>
<div class="alert alert-success" style="margin-bottom:20px">
    <i class="fas fa-trophy" style="font-size:18px"></i>
    <div>
        <strong>Tindak lanjut diterima oleh BPKP!</strong><br>
        Rekomendasi ini telah diselesaikan. Terima kasih.
    </div>
</div>
<?php endif; ?>

<!-- Riwayat TL -->
<?php if (!empty($tls)): ?>
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-history"></i> Riwayat Tindak Lanjut</span>
    </div>
    <div class="card-body">
        <div class="timeline">
        <?php foreach($tls as $tl): ?>
        <div class="tl-item">
            <div class="tl-dot <?= $tl['status_verifikasi'] ?>"></div>
            <div class="tl-date"><?= date('d M Y H:i', strtotime($tl['created_at'])) ?></div>
            <div class="tl-body">
                <div class="tl-uraian"><?= nl2br(esc($tl['uraian'])) ?></div>

                <!-- Dokumen -->
                <?php if (!empty($tl['dokumen'])): ?>
                <div style="margin-bottom:10px">
                    <?php foreach($tl['dokumen'] as $dok): ?>
                    <div class="file-item">
                        <i class="fas fa-file-alt"></i>
                        <span class="file-name">
                            <a href="/auditi/tl/dokumen/<?= $dok['id'] ?>/download" target="_blank" style="color:#2563eb">
                                <?= esc($dok['nama_file']) ?>
                            </a>
                        </span>
                        <span class="file-size"><?= round($dok['ukuran']/1024) ?> KB</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <span class="tl-verif <?= $tl['status_verifikasi'] ?>">
                    <i class="fas fa-<?= $tl['status_verifikasi']==='diterima'?'check':($tl['status_verifikasi']==='revisi'?'rotate-left':'clock') ?>"></i>
                    <?= $verifikasiLabel[$tl['status_verifikasi']] ?? $tl['status_verifikasi'] ?>
                </span>
                <?php if (!empty($tl['catatan_verifikasi'])): ?>
                <div class="tl-catatan">
                    <strong>Catatan BPKP:</strong> <?= nl2br(esc($tl['catatan_verifikasi'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
// Drag & drop upload zone
const zone = document.getElementById('upload-zone');
if (zone) {
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('dragover');
        const input = document.getElementById('files-tl');
        // Merge files
        const dt = new DataTransfer();
        Array.from(input.files).forEach(f => dt.items.add(f));
        Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
        input.files = dt.files;
        previewFiles(input.files);
    });
}

function previewFiles(files) {
    const container = document.getElementById('preview-files');
    container.innerHTML = '';
    Array.from(files).forEach((f, i) => {
        const ext  = f.name.split('.').pop().toLowerCase();
        const ico  = ext==='pdf'?'fa-file-pdf':(['jpg','jpeg','png'].includes(ext)?'fa-file-image':'fa-file-alt');
        const size = f.size > 1024*1024 ? (f.size/(1024*1024)).toFixed(1)+' MB' : Math.round(f.size/1024)+' KB';
        const div  = document.createElement('div');
        div.className = 'file-item';
        div.innerHTML = `<i class="fas ${ico}"></i><span class="file-name">${f.name}</span><span class="file-size">${size}</span>`;
        container.appendChild(div);
    });
}
</script>
<?= $this->endSection() ?>
