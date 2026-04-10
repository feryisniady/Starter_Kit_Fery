<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><?= esc($title) ?></h1>
    </div>
</div>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card" style="max-width:520px">
    <div class="card-body">
        <form action="<?= $row ? '/admin/master/irban/update/'.$row['id'] : '/admin/master/irban/store' ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-row-2">
                <div class="form-group">
                    <label>Kode <span style="color:red">*</span></label>
                    <input type="text" name="kode" class="form-control" required
                           value="<?= old('kode', $row['kode'] ?? '') ?>" placeholder="IW1">
                </div>
                <div class="form-group">
                    <label>Nama Irban <span style="color:red">*</span></label>
                    <input type="text" name="nama" class="form-control" required
                           value="<?= old('nama', $row['nama'] ?? '') ?>" placeholder="Irban Wilayah 1">
                </div>
            </div>
            <div class="form-group">
                <label>Kepala Irban</label>
                <select name="kepala_sdm_id" class="form-control">
                    <option value="">— Pilih SDM —</option>
                    <?php foreach($sdm as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($row['kepala_sdm_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                            <?= esc($s['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <a href="/admin/master/irban" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
