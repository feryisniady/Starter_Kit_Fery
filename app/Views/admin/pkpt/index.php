<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>PKPT <?= $tahun ?></h1>
        <p>Program Kerja Pengawasan Tahunan</p>
    </div>
    <div class="page-actions">
        <select id="sel-tahun" class="form-control" style="width:auto">
            <?php foreach($settings as $s): ?>
            <option value="<?= $s['tahun'] ?>" <?= $s['tahun'] == $tahun ? 'selected' : '' ?>><?= $s['tahun'] ?></option>
            <?php endforeach; ?>
        </select>
        <a href="/admin/pkpt/setting" class="btn btn-secondary"><i class="fas fa-sliders"></i> Setting</a>
        <button class="btn btn-primary" onclick="$('#modal-buat').show()">
            <i class="fas fa-plus"></i> Buat PKPT
        </button>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Header PKPT / Dokumen SK -->
<?php if($currentSetting): ?>
<div class="card mb-3" style="border-left:4px solid #6366f1">
    <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:16px;align-items:center">
        <div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Nomor SK PKPT</div>
            <div style="font-weight:600;font-size:14px"><?= esc($currentSetting['nomor_pkpt'] ?: '—') ?></div>
        </div>
        <div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Tanggal Penetapan</div>
            <div style="font-weight:600;font-size:14px">
                <?= $currentSetting['tanggal_pkpt'] ? date('d F Y', strtotime($currentSetting['tanggal_pkpt'])) : '—' ?>
            </div>
        </div>
        <div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Total HP Tahunan</div>
            <div style="font-weight:600;font-size:14px"><?= number_format($currentSetting['total_hp_tahunan']) ?> Hari</div>
        </div>
        <div>
            <a href="/admin/pkpt/setting" class="btn btn-xs btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert-error-inline mb-3">
    <i class="fas fa-triangle-exclamation"></i>
    <strong>Header PKPT tahun <?= $tahun ?> belum diatur.</strong>
    Isi Nomor SK dan Tanggal Penetapan PKPT terlebih dahulu sebelum membuat PKPT per Irban.
    <a href="/admin/pkpt/setting" class="btn btn-xs btn-primary" style="margin-left:12px"><i class="fas fa-sliders"></i> Atur Sekarang</a>
</div>
<?php endif; ?>

<div class="row" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
<?php
$statusColor = ['draft'=>'secondary','diajukan'=>'info','disetujui'=>'success'];
$statusLabel = ['draft'=>'Draft','diajukan'=>'Diajukan','disetujui'=>'Disetujui'];
$nextStatus  = ['draft'=>'diajukan','diajukan'=>'disetujui'];
$nextLabel   = ['draft'=>'Ajukan','diajukan'=>'Setujui'];
foreach($pkptList as $p):
?>
<div class="card">
    <div class="card-body">
        <!-- Header card: kode + status -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
            <span class="badge badge-primary" style="font-size:13px"><?= esc($p['irban_kode']) ?></span>
            <span class="badge badge-<?= $statusColor[$p['status']] ?? 'secondary' ?>" style="font-size:12px;padding:4px 10px">
                <?= $statusLabel[$p['status']] ?? $p['status'] ?>
            </span>
        </div>
        <!-- Nama irban -->
        <h3 style="margin:0 0 4px;font-size:15px;font-weight:600"><?= esc($p['irban_nama']) ?></h3>
        <small class="text-muted">PKPT Tahun <?= $p['tahun'] ?></small>

        <!-- Statistik -->
        <div style="margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:13px;color:#64748b">
                <i class="fas fa-list-check"></i> <?= $p['jumlah_kegiatan'] ?> Kegiatan
            </span>
        </div>

        <!-- Aksi -->
        <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
            <a href="/admin/pkpt/<?= $p['id'] ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i> Detail
            </a>
            <?php if(isset($nextStatus[$p['status']])): ?>
            <form action="/admin/pkpt/<?= $p['id'] ?>/status" method="POST" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="<?= $nextStatus[$p['status']] ?>">
                <button type="submit" class="btn btn-sm btn-<?= $p['status'] === 'draft' ? 'warning' : 'success' ?>">
                    <i class="fas fa-<?= $p['status'] === 'draft' ? 'paper-plane' : 'check' ?>"></i>
                    <?= $nextLabel[$p['status']] ?>
                </button>
            </form>
            <?php endif; ?>
            <?php if($p['status'] === 'diajukan'): ?>
            <form action="/admin/pkpt/<?= $p['id'] ?>/status" method="POST" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="draft">
                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Kembalikan ke Draft">
                    <i class="fas fa-undo"></i>
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php if(empty($pkptList)): ?>
<div class="card" style="grid-column:1/-1">
    <div class="card-body text-center" style="padding:40px;color:#94a3b8">
        <i class="fas fa-folder-open" style="font-size:48px;margin-bottom:12px;display:block"></i>
        Belum ada PKPT untuk tahun <?= $tahun ?>.<br>Klik "Buat PKPT" untuk memulai.
    </div>
</div>
<?php endif; ?>
</div>

<!-- Modal Buat PKPT -->
<div id="modal-buat" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-header">
            <h3>Buat PKPT Baru</h3>
            <button class="modal-close" onclick="$('#modal-buat').hide()"><i class="fas fa-times"></i></button>
        </div>
        <form action="/admin/pkpt/buat" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Tahun</label>
                <select name="tahun" class="form-control">
                    <?php foreach($settings as $s): ?>
                    <option value="<?= $s['tahun'] ?>" <?= $s['tahun'] == $tahun ? 'selected' : '' ?>><?= $s['tahun'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Irban</label>
                <select name="irban_id" class="form-control" required>
                    <option value="">— Pilih Irban —</option>
                    <?php
                    $irbanList = (new \App\Models\IrbanModel())->orderBy('kode')->findAll();
                    foreach($irbanList as $ir): ?>
                    <option value="<?= $ir['id'] ?>"><?= esc($ir['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="$('#modal-buat').hide()">Batal</button>
                <button type="submit" class="btn btn-primary">Buat</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$('#sel-tahun').on('change', function() {
    window.location.href = '/admin/pkpt?tahun=' + $(this).val();
});
</script>
<?= $this->endSection() ?>
