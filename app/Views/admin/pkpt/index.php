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
        <button class="btn btn-primary" onclick="$('#modal-buat').show()">
            <i class="fas fa-plus"></i> Buat PKPT
        </button>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="row" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
<?php foreach($pkptList as $p): ?>
<div class="card">
    <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <span class="badge badge-primary"><?= esc($p['irban_kode']) ?></span>
                <h3 style="margin:8px 0 4px;font-size:15px"><?= esc($p['irban_nama']) ?></h3>
                <small class="text-muted"><?= $p['tahun'] ?></small>
            </div>
            <?php
                $sc = ['draft'=>'secondary','diajukan'=>'info','disetujui'=>'success'];
            ?>
            <span class="badge badge-<?= $sc[$p['status']] ?? 'secondary' ?>">
                <?= ucfirst($p['status']) ?>
            </span>
        </div>
        <div style="margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9">
            <span style="font-size:13px;color:#64748b">
                <i class="fas fa-list-check"></i> <?= $p['jumlah_kegiatan'] ?> Kegiatan
            </span>
        </div>
        <div class="form-actions" style="margin-top:12px">
            <a href="/admin/pkpt/<?= $p['id'] ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i> Detail
            </a>
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
