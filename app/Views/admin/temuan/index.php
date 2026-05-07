<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Temuan Audit</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['tujuan']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="/admin/spt/<?= $spt['id'] ?>/pka" class="btn btn-outline-primary"><i class="fas fa-list-check"></i> PKA</a>
        <a href="/admin/spt/<?= $spt['id'] ?>/temuan/create" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Temuan</a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Ringkasan -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px">
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#6366f1"><?= $summary['total'] ?></div>
            <div style="font-size:12px;color:#64748b">Total Temuan</div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#ef4444"><?= $summary['buka'] ?></div>
            <div style="font-size:12px;color:#64748b">Terbuka</div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#22c55e"><?= $summary['tutup'] ?></div>
            <div style="font-size:12px;color:#64748b">Tertutup</div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:22px;font-weight:700;color:#f59e0b">
                <?= $summary['total_nilai'] > 0 ? 'Rp ' . number_format($summary['total_nilai'], 0, ',', '.') : '—' ?>
            </div>
            <div style="font-size:12px;color:#64748b">Total Nilai Temuan</div>
        </div>
    </div>
</div>

<!-- Daftar Temuan -->
<div class="card">
    <div class="card-body">
        <table id="dt-temuan" class="w-100">
            <thead>
                <tr>
                    <th width="80">No. Temuan</th>
                    <th>Judul Temuan</th>
                    <th width="120">Nilai (Rp)</th>
                    <th width="80">Rekomendasi</th>
                    <th width="90">Status</th>
                    <th width="110">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($temuanList as $t): ?>
            <tr>
                <td><strong><?= esc($t['nomor_temuan']) ?></strong></td>
                <td>
                    <?= esc($t['judul']) ?>
                    <?php if($t['kode_temuan']): ?>
                    <br><small><code><?= esc($t['kode_temuan']) ?></code>
                    <span class="badge badge-<?= $jenisColor[$t['jenis_temuan']] ?? 'secondary' ?>" style="font-size:10px">
                        <?= $jenisLabel[$t['jenis_temuan']] ?? '' ?>
                    </span></small>
                    <?php endif; ?>
                </td>
                <td style="text-align:right">
                    <?= $t['nilai_temuan'] > 0 ? number_format($t['nilai_temuan'], 0, ',', '.') : '—' ?>
                </td>
                <td style="text-align:center">
                    <span class="badge badge-secondary"><?= $t['jumlah_rekomendasi'] ?></span>
                </td>
                <td>
                    <span class="badge badge-<?= $statusColor[$t['status_temuan']] ?? 'secondary' ?>">
                        <?= $statusLabel[$t['status_temuan']] ?? $t['status_temuan'] ?>
                    </span>
                </td>
                <td>
                    <a href="/admin/spt/temuan/<?= $t['id'] ?>" class="btn btn-xs btn-primary" title="Detail"><i class="fas fa-eye"></i></a>
                    <a href="/admin/spt/temuan/<?= $t['id'] ?>/edit" class="btn btn-xs btn-warning" title="Edit"><i class="fas fa-pen"></i></a>
                    <button class="btn btn-xs btn-danger btn-del-temuan" data-id="<?= $t['id'] ?>" title="Hapus"><i class="fas fa-trash"></i></button>
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

$(function() {
    const dt = $('#dt-temuan').DataTable({
        paging: false, language: DT_LANG_ID, order: [],
    });

    $(document).on('click', '.btn-del-temuan', function() {
        const id  = $(this).data('id');
        const row = $(this).closest('tr');
        swalConfirm({
            title: 'Hapus Temuan?',
            html: 'Temuan ini beserta seluruh <b>rekomendasinya</b> akan dihapus permanen.',
            icon: 'warning',
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="fas fa-trash"></i>&nbsp;Ya, Hapus',
        }, () => {
            $.post('/admin/spt/temuan/' + id + '/delete', { [csrfName]: csrfToken }, res => {
                if (res.success) dt.row(row).remove().draw();
                else Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghapus temuan.', timer: 2500 });
            });
        });
    });
});
</script>
<?= $this->endSection() ?>
