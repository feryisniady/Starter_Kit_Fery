<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Surat Perintah Tugas</h1>
        <p>Manajemen SPT Inspektorat</p>
    </div>
    <div class="page-actions">
        <select id="sel-tahun" class="form-control" style="width:auto">
            <?php foreach($settings as $s): ?>
            <option value="<?= $s['tahun'] ?>" <?= $s['tahun'] == $tahun ? 'selected' : '' ?>><?= $s['tahun'] ?></option>
            <?php endforeach; ?>
        </select>
        <select id="sel-status" class="form-control" style="width:auto">
            <option value="">Semua Status</option>
            <?php foreach($statusLabel as $k => $v): ?>
            <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
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
        <table class="table-admin w-100" id="tbl-spt">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>Nomor SPT</th>
                    <th>Kode Kegiatan</th>
                    <th>Irban</th>
                    <th>Tujuan</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th width="130">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($sptList as $i => $s): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td>
                    <strong><?= esc($s['nomor_naskah'] ?: '—') ?></strong>
                </td>
                <td><span class="badge badge-primary"><?= esc($s['kode_kegiatan']) ?></span></td>
                <td><?= esc($s['irban_nama']) ?></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= esc($s['tujuan']) ?>
                </td>
                <td style="font-size:12px">
                    <?= $s['tanggal_mulai'] ? date('d/m/Y', strtotime($s['tanggal_mulai'])) : '—' ?>
                </td>
                <td>
                    <span class="badge badge-<?= $statusColor[$s['status']] ?? 'secondary' ?>">
                        <?= $statusLabel[$s['status']] ?? $s['status'] ?>
                    </span>
                </td>
                <td>
                    <a href="/admin/spt/<?= $s['id'] ?>" class="btn btn-xs btn-primary">Detail</a>
                    <?php if($s['status'] === 'terbit'): ?>
                    <a href="/admin/spt/<?= $s['id'] ?>/word" class="btn btn-xs btn-success">
                        <i class="fas fa-file-word"></i>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($sptList)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding:32px">Belum ada SPT</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$('#sel-tahun, #sel-status').on('change', function() {
    const tahun  = $('#sel-tahun').val();
    const status = $('#sel-status').val();
    window.location.href = '/admin/spt?tahun=' + tahun + '&status=' + status;
});
</script>
<?= $this->endSection() ?>
