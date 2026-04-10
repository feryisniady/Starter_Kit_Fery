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
            <option value="<?= $s['tahun'] ?>" <?= $s['tahun'] == $tahunAktif ? 'selected' : '' ?>><?= $s['tahun'] ?></option>
            <?php endforeach; ?>
        </select>
        <select id="sel-status" class="form-control" style="width:auto">
            <option value="">Semua Status</option>
            <?php foreach($statusLabel as $k => $v): ?>
            <option value="<?= $k ?>"><?= $v ?></option>
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
        <table id="dt-spt" class="w-100">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>Nomor SPT</th>
                    <th>Kode Kegiatan</th>
                    <th>Irban</th>
                    <th>Tujuan</th>
                    <th>Tanggal</th>
                    <th width="110">Status</th>
                    <th width="130">Aksi</th>
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

var dtSpt = null;

$(function() {
    dtSpt = $('#dt-spt').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url:  '/admin/spt/data',
            type: 'POST',
            data: function(d) {
                d[csrfName] = csrfToken;
                d.tahun     = $('#sel-tahun').val();
                d.status    = $('#sel-status').val();
            }
        },
        columns: [
            { data: null, render: (d,t,r,m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false },
            { data: 'nomor_naskah' },
            { data: 'kode_kegiatan', orderable: false },
            { data: 'irban_nama' },
            { data: 'tujuan' },
            { data: 'tanggal_mulai', orderable: false, searchable: false },
            { data: 'status', orderable: false, searchable: false },
            { data: 'aksi', orderable: false, searchable: false },
        ],
        language: DT_LANG_ID,
        pageLength: 25,
    });

    $('#sel-tahun, #sel-status').on('change', function() {
        dtSpt.ajax.reload();
    });
});
</script>
<?= $this->endSection() ?>
