<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Master Irban</h1>
        <p>Inspektur Pembantu Wilayah &amp; Bidang</p>
    </div>
    <div class="page-actions">
        <a href="/admin/master/irban/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Irban
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <table class="table-admin w-100">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Kode</th>
                    <th>Nama Irban</th>
                    <th>Kepala</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $i => $row): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><span class="badge badge-primary"><?= esc($row['kode']) ?></span></td>
                    <td><?= esc($row['nama']) ?></td>
                    <td><?= esc($row['kepala_nama'] ?? '-') ?></td>
                    <td>
                        <a href="/admin/master/irban/edit/<?= $row['id'] ?>" class="btn btn-xs btn-warning">Edit</a>
                        <button class="btn btn-xs btn-danger btn-del" data-id="<?= $row['id'] ?>">Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken = '<?= csrf_hash() ?>';
const csrfName  = '<?= csrf_token() ?>';
$(document).on('click', '.btn-del', function() {
    if (!confirm('Hapus irban ini?')) return;
    $.post('/admin/master/irban/delete/' + $(this).data('id'), { [csrfName]: csrfToken }, res => {
        if (res.success) location.reload();
        else alert(res.message);
    });
});
</script>
<?= $this->endSection() ?>
