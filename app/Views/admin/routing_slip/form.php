<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $sptId = $spt['id']; ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-route"></i> <?= $isEdit ? 'Edit & Resubmit' : 'Buat' ?> Routing Slip</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$sptId) ?> — <?= esc($spt['irban_nama']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $sptId ?>/routing-slip" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<?php if(!empty(session()->getFlashdata('errors'))): ?>
<div class="alert-error-inline mb-3">
    <i class="fas fa-circle-exclamation"></i>
    <?php foreach(session()->getFlashdata('errors') as $e): ?>
        <div><?= esc($e) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if($isEdit && $row['status'] === 'dikembalikan'): ?>
<div class="box-warning mb-3" style="font-size:13px">
    <i class="fas fa-rotate-left"></i>
    <strong>Routing slip ini dikembalikan.</strong>
    Perbaiki dokumen dan simpan — slip akan otomatis dikirim ulang ke Ketua Tim.
    <?php
    $cRet = '';
    foreach(['pj','dalnis','kt'] as $s) {
        if(($row["{$s}_status"] ?? '') === 'dikembalikan' && ($row["{$s}_catatan"] ?? '')) {
            $cRet = $row["{$s}_catatan"]; break;
        }
    }
    ?>
    <?php if($cRet): ?>
    <div style="margin-top:6px;padding:8px 10px;background:#fff1f2;border-radius:6px;font-size:12px;color:#9f1239">
        <strong>Catatan reviewer:</strong> <?= esc($cRet) ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div style="max-width:640px">
<form action="/admin/spt/<?= $sptId ?>/routing-slip/<?= $isEdit ? $row['id'].'/update' : 'store' ?>"
      method="POST">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-lines"></i> Informasi Dokumen</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Judul Dokumen <span style="color:#ef4444">*</span></label>
                <input type="text" name="judul" class="form-control"
                    value="<?= old('judul', $row['judul'] ?? '') ?>"
                    placeholder="cth: KKA Pemeriksaan Belanja Pegawai, Program Pengawasan..."
                    required>
                <small class="text-muted">Jelaskan nama/topik dokumen yang dikirimkan untuk direview.</small>
            </div>

            <div class="form-group">
                <label>Jenis Dokumen</label>
                <select name="jenis_dokumen" class="form-control">
                    <option value="">— Pilih Jenis —</option>
                    <?php foreach(\App\Models\RoutingSlipModel::JENIS_LABEL as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= old('jenis_dokumen', $row['jenis_dokumen'] ?? '') === $val ? 'selected' : '' ?>>
                        <?= $lbl ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Keterangan / Pokok Isi Dokumen</label>
                <textarea name="keterangan" class="form-control" rows="3"
                    placeholder="Uraian singkat isi dokumen, hal yang perlu diperhatikan reviewer, dll..."><?= old('keterangan', $row['keterangan'] ?? '') ?></textarea>
                <small class="text-muted">Opsional — membantu reviewer memahami konteks dokumen.</small>
            </div>

        </div>
    </div>

    <div class="box-info mb-3" style="font-size:12px">
        <i class="fas fa-info-circle"></i>
        Setelah disimpan, routing slip akan <strong>langsung dikirim ke Ketua Tim</strong> untuk direview.
        Alur: <strong>Ketua Tim → Dalnis → PJ</strong>.
    </div>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i>
            <?= $isEdit ? 'Simpan & Kirim Ulang' : 'Kirim ke Ketua Tim' ?>
        </button>
        <a href="/admin/spt/<?= $sptId ?>/routing-slip" class="btn btn-secondary">Batal</a>
    </div>
</form>
</div>

<?= $this->endSection() ?>
