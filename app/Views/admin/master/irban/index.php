<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-sitemap"></i> Master Irban</h1>
        <p>Inspektur Pembantu Wilayah &amp; Bidang</p>
    </div>
    <div class="page-actions">
        <?php if(hasPermission('master.create')): ?>
        <a href="/admin/master/irban/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Irban
        </a>
        <?php endif; ?>
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
        <table id="dt-irban" data-url="/admin/master/irban/data" class="w-100">
            <thead>
                <tr>
                    <th class="dt-nosort dt-nosearch" data-dt="no" width="50">#</th>
                    <th data-dt="kode" width="110">Kode</th>
                    <th data-dt="nama">Nama Inspektur Pembantu</th>
                    <th class="dt-nosort" data-dt="kepala">Kepala / Pejabat</th>
                    <th class="dt-nosort dt-nosearch" data-dt="aksi" width="110">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$(document).on('click', '.btn-del', function() {
    const id   = $(this).data('id');
    const nama = $(this).data('nama');
    swalConfirm({
        title: 'Hapus Irban?',
        html: 'Data <b>"' + nama + '"</b> akan dihapus.<br>Pastikan tidak ada PKPT atau SDM yang terkait.',
        icon: 'warning',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fas fa-trash"></i>&nbsp;Ya, Hapus',
    }, function() {
        var csrfN = $('meta[name="csrf-token-name"]').attr('content');
        var csrfH = $('meta[name="csrf-token"]').attr('content');
        $.post('/admin/master/irban/delete/' + id, { [csrfN]: csrfH }, function(res) {
            if (res.success) {
                SIP.success('Irban berhasil dihapus.');
                dtReload('dt-irban');
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal menghapus.' });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
