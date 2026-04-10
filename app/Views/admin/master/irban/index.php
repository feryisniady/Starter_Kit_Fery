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
        <table id="dt-irban" class="w-100">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th width="80">Kode</th>
                    <th>Nama Irban</th>
                    <th>Kepala Irban</th>
                    <th width="100" class="dt-nosort dt-nosearch">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken = '<?= csrf_hash() ?>';
const csrfName  = '<?= csrf_token() ?>';

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
        if (!confirm('Hapus irban ini? Pastikan tidak ada PKPT atau SDM yang terkait.')) return;
        const url = $(this).data('url');
        $.post(url, { [csrfName]: csrfToken }, res => {
            if (res.success) dt.ajax.reload();
            else alert(res.message || 'Gagal menghapus.');
        });
    });
});
</script>
<?= $this->endSection() ?>
