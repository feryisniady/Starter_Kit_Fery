<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-sitemap"></i> Master Irban</h1>
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
    <div class="card-body" style="padding:0">
        <table id="dt-irban" class="w-100" style="width:100%">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th style="width:100px">Kode</th>
                    <th>Nama Inspektur Pembantu</th>
                    <th>Kepala / Pejabat</th>
                    <th style="width:130px;text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $i => $row): ?>
                <tr>
                    <td style="text-align:center;color:#94a3b8;font-weight:600"><?= $i + 1 ?></td>
                    <td>
                        <span style="background:#ede9fe;color:#6d28d9;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:.5px">
                            <?= esc($row['kode']) ?>
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:600;color:#1e293b"><?= esc($row['nama']) ?></div>
                    </td>
                    <td style="color:#475569;font-size:13px">
                        <?php if ($row['kepala_nama'] ?? null): ?>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:32px;height:32px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#6366f1;flex-shrink:0">
                                <?= strtoupper(substr($row['kepala_nama'], 0, 1)) ?>
                            </div>
                            <span><?= esc($row['kepala_nama']) ?></span>
                        </div>
                        <?php else: ?>
                        <span style="color:#94a3b8;font-style:italic">— Belum diisi —</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <a href="/admin/master/irban/edit/<?= $row['id'] ?>"
                           class="btn btn-sm btn-warning" style="margin-right:4px">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger btn-del"
                                data-id="<?= $row['id'] ?>"
                                data-nama="<?= esc($row['nama']) ?>">
                            <i class="fas fa-trash"></i>
                        </button>
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

// DataTable client-side
$(function() {
    $('#dt-irban').DataTable({
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan",
            paginate: { previous: "‹ Prev", next: "Next ›" }
        },
        pageLength: 25,
        order: [[2, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 4] },
            { searchable: false, targets: [0, 4] },
        ],
    });

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
            $.post('/admin/master/irban/delete/' + id, { [csrfName]: csrfToken }, function(res) {
                if (res.success) {
                    Swal.fire({ icon:'success', title:'Dihapus!', timer:1500, showConfirmButton:false, toast:true, position:'top-end' });
                    setTimeout(() => location.reload(), 1200);
                } else {
                    Swal.fire({ icon:'error', title:'Gagal', text: res.message || 'Gagal menghapus.' });
                }
            });
        });
    });
});
</script>
<?= $this->endSection() ?>
