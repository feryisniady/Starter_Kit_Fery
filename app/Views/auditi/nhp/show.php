<?= $this->extend('auditi/layouts/portal') ?>
<?= $this->section('content') ?>

<?php
$totalItems   = count($items);
$sudahItems   = count(array_filter($items, fn($i) => $i['status_tanggapan'] !== 'pending'));
$pct          = $totalItems > 0 ? round($sudahItems/$totalItems*100) : 0;
$canTanggapi  = $nhp['status'] !== 'selesai';
?>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:space-between">
        <div>
            <h1><i class="fas fa-file-alt" style="color:#2563eb"></i> <?= esc($nhp['nomor_nhp'] ?: 'NHP #'.$nhp['id']) ?></h1>
            <p><?= esc($nhp['perihal']) ?> &mdash; <?= $nhp['tanggal_nhp'] ? date('d F Y', strtotime($nhp['tanggal_nhp'])) : '' ?></p>
        </div>
        <a href="/auditi/nhp" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<!-- Progress -->
<div class="card" style="background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;margin-bottom:20px">
    <div class="card-body" style="padding:18px 24px">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div style="font-size:13px;opacity:.8;margin-bottom:4px">Progress Tanggapan</div>
                <div style="font-size:22px;font-weight:700"><?= $sudahItems ?> / <?= $totalItems ?> item ditanggapi</div>
            </div>
            <div style="display:flex;gap:16px;text-align:center">
                <div>
                    <div style="font-size:20px;font-weight:700;color:#34d399"><?= count(array_filter($items, fn($i) => $i['status_tanggapan']==='sesuai')) ?></div>
                    <div style="font-size:11px;opacity:.8">Sesuai</div>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:700;color:#f87171"><?= count(array_filter($items, fn($i) => $i['status_tanggapan']==='tidak_sesuai')) ?></div>
                    <div style="font-size:11px;opacity:.8">Tidak Sesuai</div>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:700;color:#fbbf24"><?= count(array_filter($items, fn($i) => $i['status_tanggapan']==='pending')) ?></div>
                    <div style="font-size:11px;opacity:.8">Belum</div>
                </div>
            </div>
        </div>
        <div class="progress-wrap" style="margin-top:12px;background:rgba(255,255,255,.2)">
            <div class="progress-bar" style="width:<?= $pct ?>%;background:#34d399"></div>
        </div>
    </div>
</div>

<?php if (!$canTanggapi): ?>
<div class="alert alert-success"><i class="fas fa-circle-check"></i> NHP ini sudah selesai dan dikunci. Tanggapan tidak dapat diubah.</div>
<?php endif; ?>

<!-- Kartu per item -->
<?php foreach($items as $idx => $item): ?>
<?php
$isPending = $item['status_tanggapan'] === 'pending';
$isSesuai  = $item['status_tanggapan'] === 'sesuai';
$borderClr = $isPending ? '#e2e8f0' : ($isSesuai ? '#86efac' : '#fca5a5');
$bgClr     = $isPending ? '#fff' : ($isSesuai ? '#f0fdf4' : '#fef2f2');
?>
<div class="card" id="item-<?= $item['id'] ?>" style="border-left:4px solid <?= $borderClr ?>;background:<?= $bgClr ?>">
    <div class="card-header">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:28px;height:28px;border-radius:50%;background:<?= $isPending?'#e2e8f0':($isSesuai?'#22c55e':'#ef4444') ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0">
                <?= $isPending ? $idx+1 : ($isSesuai ? '✓' : '!') ?>
            </div>
            <div>
                <div style="font-weight:700;font-size:14px"><?= esc($item['judul_temuan'] ?: 'Temuan #'.($idx+1)) ?></div>
                <?php if (!$isPending): ?>
                <span class="badge badge-<?= $isSesuai?'sesuai':'tidak-sesuai' ?>" style="margin-top:2px">
                    <?= $isSesuai ? 'Sesuai' : 'Tidak Sesuai' ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!$isPending && $canTanggapi): ?>
        <button class="btn btn-outline btn-xs btn-ubah" data-id="<?= $item['id'] ?>">
            <i class="fas fa-pen"></i> Ubah
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <!-- Detail temuan -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;background:#f8fafc;padding:14px;border-radius:10px">
            <?php if ($item['kondisi']): ?>
            <div>
                <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px">Kondisi</div>
                <div style="font-size:13px;line-height:1.6"><?= renderContent($item['kondisi']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($item['kriteria']): ?>
            <div>
                <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px">Kriteria</div>
                <div style="font-size:13px;line-height:1.6"><?= renderContent($item['kriteria']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($item['sebab']): ?>
            <div>
                <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px">Sebab</div>
                <div style="font-size:13px;line-height:1.6"><?= renderContent($item['sebab']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($item['akibat']): ?>
            <div>
                <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px">Akibat</div>
                <div style="font-size:13px;line-height:1.6"><?= renderContent($item['akibat']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($item['rekomendasi']): ?>
            <div style="grid-column:1/-1">
                <div style="font-size:10px;font-weight:700;color:#2563eb;text-transform:uppercase;margin-bottom:3px">Rekomendasi APIP</div>
                <div style="font-size:13px;line-height:1.6;color:#1e40af"><?= renderContent($item['rekomendasi']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tanggapan yang sudah ada -->
        <?php if (!$isPending && !empty($item['tanggapan_entitas'])): ?>
        <div style="margin-bottom:16px;padding:12px;background:rgba(255,255,255,.7);border-radius:8px;border:1px solid #e2e8f0">
            <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Tanggapan Anda</div>
            <div style="font-size:13px;line-height:1.6"><?= renderContent($item['tanggapan_entitas']) ?></div>
            <?php if ($item['tgl_tanggapan']): ?>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px"><i class="fas fa-clock"></i> <?= date('d M Y', strtotime($item['tgl_tanggapan'])) ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Form tanggapan (show jika pending atau ubah) -->
        <?php if ($canTanggapi): ?>
        <div class="form-tanggapan" id="form-<?= $item['id'] ?>" <?= !$isPending ? 'style="display:none"' : '' ?>>
            <div class="form-group">
                <label>Status Tanggapan <span style="color:#ef4444">*</span></label>
                <div style="display:flex;gap:12px;margin-top:4px">
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 16px;border:2px solid #e2e8f0;border-radius:8px;cursor:pointer;flex:1;transition:all .15s" class="radio-opt" data-val="sesuai">
                        <input type="radio" name="status_<?= $item['id'] ?>" value="sesuai" <?= $isSesuai?'checked':'' ?> style="display:none">
                        <i class="fas fa-circle-check" style="color:#22c55e;font-size:18px"></i>
                        <div><div style="font-weight:600;font-size:13px">Sesuai</div><div style="font-size:11px;color:#64748b">Temuan sudah ditindaklanjuti / tidak relevan</div></div>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 16px;border:2px solid #e2e8f0;border-radius:8px;cursor:pointer;flex:1;transition:all .15s" class="radio-opt" data-val="tidak_sesuai">
                        <input type="radio" name="status_<?= $item['id'] ?>" value="tidak_sesuai" <?= (!$isPending&&!$isSesuai)?'checked':'' ?> style="display:none">
                        <i class="fas fa-circle-xmark" style="color:#ef4444;font-size:18px"></i>
                        <div><div style="font-weight:600;font-size:13px">Tidak Sesuai</div><div style="font-size:11px;color:#64748b">Kami keberatan / ada penjelasan lain</div></div>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Uraian Tanggapan <span style="color:#ef4444">*</span></label>
                <textarea class="form-control tanggapan-text" id="uraian-<?= $item['id'] ?>" rows="4"
                          data-wysiwyg data-wysiwyg-height="110px"
                          placeholder="Jelaskan tanggapan Anda secara detail..."><?= esc($item['tanggapan_entitas']) ?></textarea>
            </div>

            <!-- Upload dokumen -->
            <div class="form-group">
                <label><i class="fas fa-paperclip"></i> Lampiran / Bukti Dokumen</label>
                <div class="upload-zone" id="zone-<?= $item['id'] ?>" onclick="document.getElementById('file-<?= $item['id'] ?>').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><strong>Klik atau seret file ke sini</strong></p>
                    <p style="margin-top:4px">PDF, Word, Excel, Gambar — maks. 10 MB per file</p>
                </div>
                <input type="file" id="file-<?= $item['id'] ?>" style="display:none" multiple
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                       onchange="uploadFiles(<?= $item['id'] ?>, <?= $nhp['id'] ?>, this.files)">

                <!-- Daftar dokumen yang sudah upload -->
                <div id="docs-<?= $item['id'] ?>" style="margin-top:8px">
                <?php foreach($item['dokumen'] as $dok): ?>
                <div class="file-item" id="dok-<?= $dok['id'] ?>">
                    <i class="fas <?= str_contains($dok['nama_file'],'pdf')?'fa-file-pdf':( str_contains($dok['nama_file'],'jpg')||str_contains($dok['nama_file'],'jpeg')||str_contains($dok['nama_file'],'png')?'fa-file-image':'fa-file-alt') ?>"></i>
                    <span class="file-name">
                        <a href="/auditi/nhp/dokumen/<?= $dok['id'] ?>/download" target="_blank" style="color:#1e293b;text-decoration:none">
                            <?= esc($dok['nama_file']) ?>
                        </a>
                    </span>
                    <span class="file-size"><?= round($dok['ukuran']/1024) ?> KB</span>
                    <?php if ($canTanggapi): ?>
                    <button type="button" class="btn btn-danger btn-xs" onclick="hapusDok(<?= $dok['id'] ?>)">
                        <i class="fas fa-times"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                </div>
            </div>

            <div style="display:flex;gap:8px;align-items:center">
                <button type="button" class="btn btn-primary btn-simpan" data-id="<?= $item['id'] ?>" data-nhp="<?= $nhp['id'] ?>">
                    <i class="fas fa-save"></i> Simpan Tanggapan
                </button>
                <?php if (!$isPending): ?>
                <button type="button" class="btn btn-secondary btn-batal" data-id="<?= $item['id'] ?>">Batal</button>
                <?php endif; ?>
                <span class="save-indicator" id="ind-<?= $item['id'] ?>" style="display:none;font-size:12px;color:#16a34a">
                    <i class="fas fa-circle-check"></i> Tersimpan
                </span>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /card-body -->
</div><!-- /card item -->
<?php endforeach; ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
// Radio option style
document.querySelectorAll('.radio-opt').forEach(label => {
    const radio = label.querySelector('input[type=radio]');
    function update() {
        label.style.borderColor = radio.checked ? (label.dataset.val==='sesuai'?'#22c55e':'#ef4444') : '#e2e8f0';
        label.style.background  = radio.checked ? (label.dataset.val==='sesuai'?'#f0fdf4':'#fef2f2') : '#fff';
    }
    update();
    label.addEventListener('click', () => {
        // Deselect siblings
        label.closest('.form-group').querySelectorAll('.radio-opt').forEach(l => {
            l.querySelector('input').checked = false;
            l.style.borderColor = '#e2e8f0'; l.style.background = '#fff';
        });
        radio.checked = true; update();
    });
});

// Simpan tanggapan (AJAX)
document.querySelectorAll('.btn-simpan').forEach(btn => {
    btn.addEventListener('click', function() {
        const id    = this.dataset.id;
        const nhpId = this.dataset.nhp;
        const radio = document.querySelector(`input[name="status_${id}"]:checked`);
        const text  = document.getElementById(`uraian-${id}`).value.trim();

        if (!radio) { Swal.fire({icon:'warning',title:'Pilih Status',text:'Pilih "Sesuai" atau "Tidak Sesuai" terlebih dahulu.',timer:2500}); return; }
        if (!text)  { Swal.fire({icon:'warning',title:'Isi Keterangan',text:'Uraian tanggapan wajib diisi.',timer:2500}); return; }

        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const body = new URLSearchParams();
        body.append('status_tanggapan', radio.value);
        body.append('tanggapan_entitas', text);
        body.append(CSRF_NAME, CSRF_HASH);

        fetch(`/auditi/nhp/${nhpId}/item/${id}/simpan`, {method:'POST', body})
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Simpan Tanggapan';
                    document.getElementById(`ind-${id}`).style.display = 'inline-flex';
                    setTimeout(() => { location.reload(); }, 800);
                } else {
                    Swal.fire({icon:'error',title:'Gagal',text:res.message,timer:3000});
                    btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Simpan Tanggapan';
                }
            });
    });
});

// Tombol Ubah tanggapan
document.querySelectorAll('.btn-ubah').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = document.getElementById('form-'+this.dataset.id);
        form.style.display = 'block';
        this.style.display = 'none';
        if (typeof initWysiwyg === 'function') initWysiwyg(form);
    });
});

// Tombol Batal
document.querySelectorAll('.btn-batal').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('form-'+this.dataset.id).style.display = 'none';
        document.querySelector(`.btn-ubah[data-id="${this.dataset.id}"]`).style.display = 'inline-flex';
    });
});

// Upload files
function uploadFiles(itemId, nhpId, files) {
    Array.from(files).forEach(file => {
        const fd = new FormData();
        fd.append('dokumen', file);
        fd.append(CSRF_NAME, CSRF_HASH);

        // Tambah placeholder sementara
        const placeholder = document.createElement('div');
        placeholder.className = 'file-item';
        placeholder.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span class="file-name">${file.name}</span><span class="file-size" style="color:#3b82f6">Mengupload...</span>`;
        document.getElementById(`docs-${itemId}`).appendChild(placeholder);

        fetch(`/auditi/nhp/${nhpId}/item/${itemId}/upload`, {method:'POST', body:fd})
            .then(r => r.json())
            .then(res => {
                placeholder.remove();
                if (res.success) {
                    const ext = file.name.split('.').pop().toLowerCase();
                    const ico = ext==='pdf'?'fa-file-pdf':(['jpg','jpeg','png'].includes(ext)?'fa-file-image':'fa-file-alt');
                    const div = document.createElement('div');
                    div.className = 'file-item';
                    div.id = `dok-${res.dokumen.id}`;
                    div.innerHTML = `<i class="fas ${ico}"></i><span class="file-name">${esc(res.dokumen.nama_file)}</span><span class="file-size">${Math.round(res.dokumen.ukuran/1024)} KB</span><button type="button" class="btn btn-danger btn-xs" onclick="hapusDok(${res.dokumen.id})"><i class="fas fa-times"></i></button>`;
                    document.getElementById(`docs-${itemId}`).appendChild(div);
                } else {
                    Swal.fire({icon:'error',title:'Upload Gagal',text:res.message,timer:3000});
                }
            });
    });
    // Reset input
    document.getElementById(`file-${itemId}`).value = '';
}

function hapusDok(dokId) {
    swalConfirm({title:'Hapus Dokumen?',html:'File ini akan dihapus permanen.',icon:'warning',confirmButtonColor:'#ef4444',confirmButtonText:'Ya, Hapus'}, () => {
        const body = new URLSearchParams();
        body.append(CSRF_NAME, CSRF_HASH);
        fetch(`/auditi/nhp/dokumen/${dokId}/hapus`, {method:'POST', body})
            .then(r => r.json())
            .then(res => {
                if (res.success) document.getElementById(`dok-${dokId}`)?.remove();
                else Swal.fire({icon:'error',title:'Gagal',text:res.message,timer:2500});
            });
    });
}

// Drag & drop
document.querySelectorAll('.upload-zone').forEach(zone => {
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('dragover');
        const id = zone.id.replace('zone-','');
        const nhpId = '<?= $nhp['id'] ?>';
        uploadFiles(id, nhpId, e.dataTransfer.files);
    });
});

function esc(str) { return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
<?= $this->endSection() ?>
