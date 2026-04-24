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
<<<<<<< HEAD
$(document).on('click', '.btn-del', function() {
    if (!confirm('Hapus irban ini?')) return;
    $.post('/admin/master/irban/delete/' + $(this).data('id'), { [csrfName]: csrfToken }, res => {
        if (res.success) location.reload();
        else alert(res.message);
=======

$(function() {
    const dt = $('#dt-irban').DataTable({
        processing: true, serverSide: true,
        language: DT_LANG_ID,
        ajax: {
            url: '/admin/master/irban/data',
            type: 'POST',
            data: d => { d[csrfName] = csrfToken; }
        },
        columns: [
            { data: 'no',     orderable: false },
            { data: 'kode',   orderable: false },
            { data: 'nama' },
            { data: 'kepala' },
            { data: 'aksi',   orderable: false, searchable: false },
        ],
        order: [[2, 'asc']],
    });

    $(document).on('click', '.btn-delete', function() {
        const url = $(this).data('url');
        swalConfirm({
            title: 'Hapus Irban?',
            html: 'Pastikan tidak ada <b>PKPT atau SDM</b> yang terkait sebelum menghapus.',
            icon: 'warning',
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="fas fa-trash"></i>&nbsp;Ya, Hapus',
        }, () => {
            $.post(url, { [csrfName]: csrfToken }, res => {
                if (res.success) dt.ajax.reload();
                else Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal menghapus.', timer: 3000 });
            });
        });
>>>>>>> 4e835667c542dfeabc22e2aac3a524ee072a4a0f
    });
});
</script>
<?= $this->endSection() ?>
