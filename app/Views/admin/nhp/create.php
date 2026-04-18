<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-file-circle-plus"></i> Buat NHP Baru</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>/nhp" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/store">
    <?= csrf_field() ?>

    <!-- Header NHP -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-alt"></i> Data NHP</h3>
        </div>
        <div class="card-body">
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label">Tanggal NHP</label>
                    <input type="date" name="tanggal_nhp" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Perihal / Judul NHP</label>
                    <input type="text" name="perihal" class="form-control"
                           placeholder="Contoh: Notisi Hasil Pemeriksaan atas Pengelolaan Keuangan..."
                           value="<?= old('perihal') ?>">
                </div>
            </div>
            <div class="form-group mt-3">
                <label class="form-label">Catatan (opsional)</label>
                <textarea name="catatan" class="form-control" rows="2"
                          placeholder="Catatan tambahan untuk NHP ini..."><?= old('catatan') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Pilih Simpulan untuk dimasukkan ke NHP -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list-check"></i> Pilih Temuan dari Simpulan AT</h3>
            <p class="card-subtitle" style="font-size:12px;color:#64748b;margin-top:4px">
                Centang simpulan AT yang akan dimasukkan dalam NHP ini. Isi Judul Temuan untuk setiap item.
            </p>
        </div>
        <div class="card-body" style="padding:0">
            <?php if (empty($simpulanBelumNhp)): ?>
            <div style="text-align:center;padding:40px;color:#94a3b8">
                <i class="fas fa-circle-check" style="font-size:32px;display:block;margin-bottom:12px;color:#10b981"></i>
                Semua simpulan AT sudah dimasukkan ke NHP sebelumnya, atau belum ada simpulan AT.
                <div style="margin-top:8px;font-size:12px">
                    Gunakan fitur "Tambah Item" di halaman detail NHP untuk menambah item secara manual.
                </div>
            </div>
            <?php else: ?>
            <div style="padding:12px 16px;background:#f0f9ff;border-bottom:1px solid #bae6fd;font-size:12px;color:#0369a1">
                <i class="fas fa-info-circle"></i>
                Ditemukan <strong><?= count($simpulanBelumNhp) ?></strong> simpulan yang belum masuk NHP.
                <label style="margin-left:12px;cursor:pointer">
                    <input type="checkbox" id="selectAll" onchange="toggleAll(this)"> <strong>Pilih Semua</strong>
                </label>
            </div>
            <?php
            $prevAt = null;
            foreach ($simpulanBelumNhp as $s):
                $isNewAt = $s['at_nama'] !== $prevAt;
                $prevAt  = $s['at_nama'];
            ?>
            <?php if ($isNewAt): ?>
            <div style="background:#f8fafc;padding:8px 16px;font-size:12px;font-weight:600;color:#475569;border-bottom:1px solid #f1f5f9">
                <i class="fas fa-user"></i> <?= esc($s['at_nama']) ?> — <?= esc($s['peran_spt'] ?? '') ?>
            </div>
            <?php endif; ?>
            <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9" id="row-<?= $s['id'] ?>">
                <label style="display:flex;gap:12px;cursor:pointer;align-items:flex-start">
                    <input type="checkbox" name="simpulan_ids[]" value="<?= $s['id'] ?>"
                           class="simpulan-cb" style="margin-top:3px;flex-shrink:0"
                           onchange="toggleJudul(<?= $s['id'] ?>)">
                    <div style="flex:1">
                        <div style="font-size:13px;color:#1e293b;line-height:1.5">
                            <?= esc(mb_strimwidth($s['kondisi'] ?? '—', 0, 200, '...')) ?>
                        </div>
                        <?php if ($s['kode_temuan_kode']): ?>
                        <span class="badge badge-<?= ['keuangan'=>'success','kepatuhan'=>'warning','kinerja'=>'info'][$s['kode_temuan_jenis'] ?? ''] ?? 'secondary' ?>"
                              style="font-size:10px;margin-top:4px">
                            <?= esc($s['kode_temuan_kode']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($s['nilai_financial']): ?>
                        <span style="font-size:11px;color:#ef4444;margin-left:8px">
                            Rp <?= number_format((int)$s['nilai_financial'], 0, ',', '.') ?>
                        </span>
                        <?php endif; ?>
                        <div id="judul-field-<?= $s['id'] ?>" style="display:none;margin-top:8px">
                            <input type="text" name="judul_temuan[<?= $s['id'] ?>]" class="form-control"
                                   style="font-size:13px"
                                   placeholder="Judul temuan formal untuk NHP (wajib diisi)..."
                                   value="">
                        </div>
                    </div>
                </label>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="/admin/spt/<?= $spt['id'] ?>/nhp" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan NHP
        </button>
    </div>
</form>

<script>
function toggleAll(cb) {
    document.querySelectorAll('.simpulan-cb').forEach(el => {
        el.checked = cb.checked;
        toggleJudul(el.value);
    });
}

function toggleJudul(id) {
    const cb    = document.querySelector('input[name="simpulan_ids[]"][value="' + id + '"]');
    const field = document.getElementById('judul-field-' + id);
    if (cb && field) field.style.display = cb.checked ? 'block' : 'none';
}
</script>

<?= $this->endSection() ?>
