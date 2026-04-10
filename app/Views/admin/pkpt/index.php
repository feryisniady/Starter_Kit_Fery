<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>PKPT <span id="lbl-tahun"><?= $tahun ?></span></h1>
        <p>Program Kerja Pengawasan Tahunan</p>
    </div>
    <div class="page-actions">
        <select id="sel-tahun" class="form-control" style="width:auto">
            <?php foreach($settings as $s): ?>
            <option value="<?= $s['tahun'] ?>" <?= $s['tahun'] == $tahun ? 'selected' : '' ?>><?= $s['tahun'] ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" id="btn-buat-pkpt">
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

<!-- Info bar: Header SK PKPT -->
<div id="info-setting">
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
            <a href="/admin/pkpt/setting" class="btn btn-sm btn-outline-secondary" title="Edit Setting PKPT">
                <i class="fas fa-sliders"></i> Setting
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert-error-inline mb-3">
    <i class="fas fa-triangle-exclamation"></i>
    <strong>Header PKPT tahun <?= $tahun ?> belum diatur.</strong>
    Isi Nomor SK dan Tanggal Penetapan terlebih dahulu.
    <a href="/admin/pkpt/setting" class="btn btn-xs btn-primary" style="margin-left:12px"><i class="fas fa-sliders"></i> Atur Sekarang</a>
</div>
<?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <table id="dt-pkpt" class="w-100">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th width="80">Kode</th>
                    <th>Nama Irban</th>
                    <th width="120">Kegiatan</th>
                    <th width="110">Status</th>
                    <th width="80" class="dt-nosort dt-nosearch">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Buat PKPT -->
<div id="modal-buat" class="modal-overlay">
    <div class="modal-box" style="max-width:380px">
        <div class="modal-header">
            <h3>Buat PKPT</h3>
            <button class="modal-close" onclick="Modal.close('modal-buat')"><i class="fas fa-times"></i></button>
        </div>
        <form action="/admin/pkpt/buat" method="POST" style="padding:22px">
            <?= csrf_field() ?>
            <input type="hidden" name="tahun" id="modal-tahun" value="<?= $tahun ?>">
            <div class="form-group">
                <label style="font-size:12px;font-weight:600;color:#34395e">Tahun</label>
                <input type="text" class="form-control" value="<?= $tahun ?>" readonly style="background:#f8fafc;color:#64748b">
            </div>
            <?php if(empty($irbanList)): ?>
            <div style="padding:14px;background:#fef9ec;border:1px solid #f59e0b;border-radius:8px;font-size:13px;color:#92400e;margin-bottom:16px">
                <i class="fas fa-triangle-exclamation"></i>
                Belum ada data Irban. <a href="/admin/master/irban/create" style="font-weight:600">Tambah Irban</a> terlebih dahulu.
            </div>
            <?php else: ?>
            <div class="form-group">
                <label style="font-size:12px;font-weight:600;color:#34395e">Irban <span class="text-danger">*</span></label>
                <select name="irban_id" class="form-control" required>
                    <option value="">— Pilih Irban —</option>
                    <?php foreach($irbanList as $ir): ?>
                    <option value="<?= $ir['id'] ?>"><?= esc($ir['kode']) ?> — <?= esc($ir['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-actions" style="margin-top:8px;padding-top:16px;border-top:1px solid #f1f5f9">
                <button type="button" class="btn btn-secondary" onclick="Modal.close('modal-buat')">Batal</button>
                <?php if(!empty($irbanList)): ?>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Buat PKPT</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken = '<?= csrf_hash() ?>';
const csrfName  = '<?= csrf_token() ?>';
let currentTahun = <?= (int)$tahun ?>;

$(function() {
    const dt = $('#dt-pkpt').DataTable({
        processing: true, serverSide: true,
        language: DT_LANG_ID,
        ajax: {
            url: '/admin/pkpt/data',
            type: 'POST',
            data: d => { d[csrfName] = csrfToken; d.tahun = currentTahun; }
        },
        columns: [
            { data: 'no',       orderable: false },
            { data: 'kode',     orderable: false },
            { data: 'irban' },
            { data: 'kegiatan', orderable: false },
            { data: 'status',   orderable: false },
            { data: 'aksi',     orderable: false, searchable: false },
        ],
        order: [[2, 'asc']],
    });

    $('#sel-tahun').on('change', function() {
        currentTahun = parseInt($(this).val());
        $('#lbl-tahun').text(currentTahun);
        $('#modal-tahun').val(currentTahun);
        $('#modal-tahun-display').val(currentTahun);
        // Reload page to refresh setting info bar
        window.location.href = '/admin/pkpt?tahun=' + currentTahun;
    });

    $('#btn-buat-pkpt').on('click', function() {
        Modal.open('modal-buat');
    });
});
</script>
<?= $this->endSection() ?>
