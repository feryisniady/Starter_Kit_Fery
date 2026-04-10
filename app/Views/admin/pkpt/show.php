<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><?= esc($pkpt['irban_nama']) ?> — PKPT <?= $pkpt['tahun'] ?></h1>
        <p>Daftar Kegiatan Pengawasan</p>
    </div>
    <div class="page-actions">
        <a href="/admin/pkpt" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="/admin/pkpt/<?= $pkpt['id'] ?>/kegiatan/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Kegiatan
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Info PKPT -->
<div class="card mb-3">
    <div class="card-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
        <div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Irban</div>
            <div style="font-size:15px;font-weight:600"><?= esc($pkpt['irban_nama']) ?></div>
        </div>
        <div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Tahun</div>
            <div style="font-size:15px;font-weight:600"><?= $pkpt['tahun'] ?></div>
        </div>
        <div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Total Kegiatan</div>
            <div style="font-size:15px;font-weight:600"><?= count($kegiatan) ?></div>
        </div>
        <div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Total Anggaran</div>
            <div style="font-size:15px;font-weight:600">
                Rp <?= number_format(array_sum(array_column($kegiatan, 'total_anggaran')), 0, ',', '.') ?>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Kegiatan -->
<?php if(empty($kegiatan)): ?>
<div class="card">
    <div class="card-body text-center" style="padding:48px;color:#94a3b8">
        <i class="fas fa-clipboard-list" style="font-size:48px;margin-bottom:12px;display:block"></i>
        Belum ada kegiatan. Klik "Tambah Kegiatan" untuk mulai.
    </div>
</div>
<?php else: ?>
<?php foreach($kegiatan as $i => $k): ?>
<div class="card mb-3">
    <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                    <span class="badge badge-primary"><?= esc($k['kode_kegiatan']) ?></span>
                    <?php $rc = ['rendah'=>'success','sedang'=>'warning','tinggi'=>'danger']; ?>
                    <span class="badge badge-<?= $rc[$k['risiko_audit']] ?? 'secondary' ?>">
                        Risiko <?= ucfirst($k['risiko_audit']) ?>
                    </span>
                    <?php if($k['spt_terbit'] > 0): ?>
                    <span class="badge badge-success"><i class="fas fa-check"></i> SPT Terbit</span>
                    <?php elseif($k['jumlah_spt'] > 0): ?>
                    <span class="badge badge-info"><?= $k['jumlah_spt'] ?> SPT Proses</span>
                    <?php else: ?>
                    <span class="badge badge-secondary">Belum ada SPT</span>
                    <?php endif; ?>
                </div>
                <h3 style="font-size:14px;margin:0 0 4px;color:#1e293b"><?= esc($k['area_pengawasan']) ?> — <?= esc($k['jenis_pengawasan']) ?></h3>
                <p style="font-size:13px;color:#64748b;margin:0"><?= esc($k['tujuan_sasaran']) ?></p>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:12px;color:#94a3b8">Total HP</div>
                <div style="font-size:16px;font-weight:700;color:#6366f1"><?= $k['total_hp'] ?> Hari</div>
                <div style="font-size:12px;color:#94a3b8">Rp <?= number_format($k['total_anggaran'], 0, ',', '.') ?></div>
            </div>
        </div>

        <div style="display:flex;gap:8px;margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;flex-wrap:wrap">
            <?php if($k['tanggal_mulai']): ?>
            <span style="font-size:12px;color:#64748b">
                <i class="fas fa-calendar"></i>
                <?= date('d/m/Y', strtotime($k['tanggal_mulai'])) ?> s.d. <?= date('d/m/Y', strtotime($k['tanggal_selesai'])) ?>
            </span>
            <?php endif; ?>
            <div style="margin-left:auto;display:flex;gap:8px">
                <a href="/admin/spt/create/<?= $k['id'] ?>" class="btn btn-xs btn-success">
                    <i class="fas fa-file-signature"></i> Buat SPT
                </a>
                <a href="/admin/pkpt/kegiatan/edit/<?= $k['id'] ?>" class="btn btn-xs btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button class="btn btn-xs btn-danger btn-del-kegiatan" data-id="<?= $k['id'] ?>">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken = '<?= csrf_hash() ?>';
const csrfName  = '<?= csrf_token() ?>';
$(document).on('click', '.btn-del-kegiatan', function() {
    if (!confirm('Hapus kegiatan ini? Data tim juga akan dihapus.')) return;
    $.post('/admin/pkpt/kegiatan/delete/' + $(this).data('id'), { [csrfName]: csrfToken }, res => {
        if (res.success) location.reload();
    });
});
</script>
<?= $this->endSection() ?>
