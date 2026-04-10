<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title"><h1>Setting PKPT</h1><p>Konfigurasi per tahun anggaran</p></div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="row" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:flex-start">

    <!-- Form tambah/edit setting -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders"></i> Tambah / Update Setting Tahun</h3></div>
        <div class="card-body">
            <form action="/admin/pkpt/setting/store" method="POST">
                <?= csrf_field() ?>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Tahun <span style="color:red">*</span></label>
                        <input type="number" name="tahun" class="form-control" required
                               value="<?= date('Y') ?>" min="2020" max="2099">
                    </div>
                    <div class="form-group">
                        <label>Total HP Tahunan <span style="color:red">*</span></label>
                        <input type="number" name="total_hp_tahunan" class="form-control" required value="360" min="1">
                        <small class="text-muted">Hari kerja efektif setahun</small>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tarif HP per Hari (Rp) <span style="color:red">*</span></label>
                    <input type="number" name="tarif_hp" class="form-control" required value="160000" min="1">
                </div>
                <div class="form-group">
                    <label>Nomor SK PKPT</label>
                    <input type="text" name="nomor_pkpt" class="form-control"
                           placeholder="100.3.3.2/632/KEP/434.013/2025">
                    <small class="text-muted">Digunakan sebagai dasar otomatis di SPT</small>
                </div>
                <div class="form-group">
                    <label>Tanggal Penetapan PKPT</label>
                    <input type="date" name="tanggal_pkpt" class="form-control">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar setting -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list"></i> Riwayat Setting</h3></div>
        <div class="card-body">
            <table class="table-admin w-100">
                <thead><tr><th>Tahun</th><th>Total HP</th><th>Tarif/Hari</th><th>No. SK PKPT</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach($settings as $s): ?>
                <tr>
                    <td><strong><?= $s['tahun'] ?></strong></td>
                    <td><?= number_format($s['total_hp_tahunan']) ?> hari</td>
                    <td>Rp <?= number_format($s['tarif_hp']) ?></td>
                    <td><small><?= esc($s['nomor_pkpt'] ?? '-') ?></small></td>
                    <td>
                        <button class="btn btn-xs btn-danger btn-del" data-id="<?= $s['id'] ?>">Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($settings)): ?>
                <tr><td colspan="5" class="text-center text-muted">Belum ada setting</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken = '<?= csrf_hash() ?>';
const csrfName  = '<?= csrf_token() ?>';
$(document).on('click', '.btn-del', function() {
    if (!confirm('Hapus setting ini?')) return;
    $.post('/admin/pkpt/setting/delete/' + $(this).data('id'), { [csrfName]: csrfToken }, res => {
        if (res.success) location.reload();
    });
});
</script>
<?= $this->endSection() ?>
