<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-book"></i> <?= esc($title) ?></h1>
    </div>
    <div class="page-actions">
        <a href="/admin/pka-template" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<form action="<?= $action ?>" method="POST" id="form-template">
<?= csrf_field() ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

<!-- Kolom kiri: form utama -->
<div>
    <!-- Info Template -->
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Informasi Template</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label>Nama Template <span style="color:red">*</span></label>
                <input type="text" name="nama" class="form-control" required
                       value="<?= esc($tpl['nama'] ?? '') ?>"
                       placeholder="cth: Audit Keuangan Satker — Prosedur Standar">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group mb-0">
                    <label>Jenis Audit</label>
                    <input type="text" name="jenis_audit" class="form-control"
                           value="<?= esc($tpl['jenis_audit'] ?? '') ?>"
                           placeholder="cth: Audit Keuangan, Audit Kinerja">
                    <div style="font-size:10px;color:#94a3b8;margin-top:3px">Digunakan sebagai filter saat memilih template di PKA</div>
                </div>
                <div class="form-group mb-0">
                    <label>Deskripsi</label>
                    <input type="text" name="deskripsi" class="form-control"
                           value="<?= esc($tpl['deskripsi'] ?? '') ?>"
                           placeholder="Keterangan singkat template">
                </div>
            </div>
        </div>
    </div>

    <!-- Prosedur per Fase -->
    <?php
    $faseLabel = ['persiapan' => 'Persiapan', 'pelaksanaan' => 'Pelaksanaan', 'pelaporan' => 'Pelaporan'];
    $faseColor = ['persiapan' => '#6366f1', 'pelaksanaan' => '#0ea5e9', 'pelaporan' => '#22c55e'];
    $faseIcon  = ['persiapan' => 'search', 'pelaksanaan' => 'tasks', 'pelaporan' => 'file-alt'];
    foreach (['persiapan','pelaksanaan','pelaporan'] as $fase):
        $existingItems = $items[$fase] ?? [];
    ?>
    <div class="card mb-3" style="border-top:3px solid <?= $faseColor[$fase] ?>">
        <div class="card-header" style="background:<?= $faseColor[$fase] ?>10">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <h3 class="card-title" style="margin:0;color:<?= $faseColor[$fase] ?>">
                    <i class="fas fa-<?= $faseIcon[$fase] ?>"></i> <?= $faseLabel[$fase] ?>
                </h3>
                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="addRow('<?= $fase ?>')">
                    <i class="fas fa-plus"></i> Tambah Baris
                </button>
            </div>
        </div>
        <div class="card-body" id="rows-<?= $fase ?>" style="padding:12px">
            <?php if (empty($existingItems)): ?>
            <!-- Akan ada minimal 1 baris kosong via JS saat halaman load -->
            <?php else: ?>
                <?php foreach($existingItems as $item): ?>
                <div class="prosedur-row" style="display:flex;gap:8px;align-items:flex-start;margin-bottom:8px">
                    <input type="hidden" name="item_fase[]" value="<?= $fase ?>">
                    <div style="flex:1">
                        <textarea name="uraian[]" class="form-control form-control-sm" rows="2"
                                  data-wysiwyg data-wysiwyg-height="80px"
                                  placeholder="Uraian prosedur untuk fase <?= $faseLabel[$fase] ?>..."><?= esc($item['uraian_prosedur']) ?></textarea>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeRow(this)" style="margin-top:2px">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="form-actions">
        <a href="/admin/pka-template" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Template</button>
    </div>
</div>

<!-- Kolom kanan: panduan -->
<div>
    <div class="card" style="position:sticky;top:20px">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-lightbulb"></i> Panduan</h3></div>
        <div class="card-body" style="font-size:12px;color:#374151;line-height:1.6">
            <p><strong>Struktur Fase:</strong></p>
            <ul style="padding-left:16px;margin-bottom:12px">
                <li><strong style="color:#6366f1">Persiapan</strong> — Review dokumen, survey, identifikasi risiko</li>
                <li><strong style="color:#0ea5e9">Pelaksanaan</strong> — Prosedur audit utama di lapangan</li>
                <li><strong style="color:#22c55e">Pelaporan</strong> — Finalisasi temuan, penyusunan laporan</li>
            </ul>
            <p><strong>Tips:</strong></p>
            <ul style="padding-left:16px">
                <li>Tulis prosedur secara spesifik dan terukur</li>
                <li>Gunakan kata kerja aktif: <em>Verifikasi, Bandingkan, Telusuri, Konfirmasi</em></li>
                <li>Satu prosedur = satu tindakan audit</li>
                <li>Template yang ada bisa dimodifikasi saat diterapkan ke PKA</li>
            </ul>
            <hr style="border:none;border-top:1px solid #e2e8f0;margin:12px 0">
            <p><strong>Cara Pakai:</strong></p>
            <ol style="padding-left:16px">
                <li>Buat template di sini</li>
                <li>Buka PKA di SPT yang relevan</li>
                <li>Klik "Gunakan Template" → pilih template ini</li>
                <li>Semua prosedur akan masuk sebagai draft PKA</li>
                <li>KT edit/hapus prosedur sesuai kebutuhan</li>
                <li>KT assign AT ke setiap prosedur</li>
            </ol>
        </div>
    </div>
</div>

</div><!-- /grid -->
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const fasePlaceholder = {
    persiapan:   'Uraian prosedur untuk fase Persiapan...',
    pelaksanaan: 'Uraian prosedur untuk fase Pelaksanaan...',
    pelaporan:   'Uraian prosedur untuk fase Pelaporan...',
};

function addRow(fase) {
    const container = document.getElementById('rows-' + fase);
    const div = document.createElement('div');
    div.className = 'prosedur-row';
    div.style.cssText = 'display:flex;gap:8px;align-items:flex-start;margin-bottom:8px';
    div.innerHTML = `
        <input type="hidden" name="item_fase[]" value="${fase}">
        <div style="flex:1">
            <textarea name="uraian[]" class="form-control form-control-sm" rows="2"
                      data-wysiwyg data-wysiwyg-height="80px"
                      placeholder="${fasePlaceholder[fase]}"></textarea>
        </div>
        <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeRow(this)" style="margin-top:2px;flex-shrink:0">
            <i class="fas fa-times"></i>
        </button>`;
    container.appendChild(div);
    if (typeof initWysiwyg === 'function') initWysiwyg(div);
}

function removeRow(btn) {
    const row = btn.closest('.prosedur-row');
    const ta  = row.querySelector('textarea[data-wysiwyg]');
    if (ta && ta.id && window._quillInstances) delete window._quillInstances[ta.id];
    row.remove();
}

// Tambah 1 baris kosong per fase jika belum ada (untuk form baru)
document.addEventListener('DOMContentLoaded', function() {
    ['persiapan','pelaksanaan','pelaporan'].forEach(fase => {
        const container = document.getElementById('rows-' + fase);
        if (container && container.querySelectorAll('.prosedur-row').length === 0) {
            addRow(fase);
        }
    });
});
</script>
<?= $this->endSection() ?>
