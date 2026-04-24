<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Hari Libur & Hari Kerja</h1>
        <p>Manajemen hari libur nasional / cuti bersama per tahun</p>
    </div>
    <div class="page-actions">
        <select id="sel-tahun" class="form-control" style="width:auto">
            <?php foreach($settings as $s): ?>
            <option value="<?= $s['tahun'] ?>" <?= $s['tahun'] == $tahun ? 'selected' : '' ?>><?= $s['tahun'] ?></option>
            <?php endforeach; ?>
        </select>
        <a href="/admin/pkpt/setting" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Summary HP -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px">
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#475569"><?= $ringkasan['total_hari'] ?></div>
            <div style="font-size:12px;color:#94a3b8">Total Hari dalam <?= $tahun ?></div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#6366f1"><?= $ringkasan['senin_jumat'] ?></div>
            <div style="font-size:12px;color:#94a3b8">Hari Senin–Jumat</div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#ef4444"><?= $ringkasan['libur_nasional'] ?></div>
            <div style="font-size:12px;color:#94a3b8">Hari Libur Tercatat</div>
        </div>
    </div>
    <div class="card" style="border:2px solid #22c55e;text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#22c55e"><?= $ringkasan['hari_kerja'] ?></div>
            <div style="font-size:12px;color:#94a3b8"><strong>Hari Kerja Efektif</strong></div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;align-items:flex-start">

    <!-- Form tambah -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-xmark"></i> Tambah Hari Libur</h3></div>
        <div class="card-body">
            <form action="/admin/pkpt/hari-libur/store" method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Tanggal <span style="color:red">*</span></label>
                    <input type="date" name="tanggal" class="form-control" required
                           min="<?= $tahun ?>-01-01" max="<?= $tahun ?>-12-31"
                           value="<?= old('tanggal', $tahun . '-01-01') ?>">
                </div>
                <div class="form-group">
                    <label>Keterangan <span style="color:red">*</span></label>
                    <input type="text" name="keterangan" class="form-control" required
                           value="<?= old('keterangan') ?>" placeholder="Tahun Baru, Lebaran, dll.">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</button>
                </div>
            </form>

            <hr style="margin:20px 0">

            <!-- Import cepat hari libur umum -->
            <div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:10px">
                <i class="fas fa-bolt"></i> Import Cepat (Libur Umum <?= $tahun ?>)
            </div>
            <div id="libur-default" style="display:flex;flex-wrap:wrap;gap:6px"></div>
            <button id="btn-import-all" class="btn btn-sm btn-outline-primary" style="margin-top:10px">
                <i class="fas fa-download"></i> Import Semua
            </button>
        </div>
    </div>

    <!-- Daftar hari libur -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-xmark"></i> Daftar Hari Libur <?= $tahun ?></h3></div>
        <div class="card-body">
            <table id="dt-libur" class="w-100">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th width="120">Tanggal</th>
                        <th width="80">Hari</th>
                        <th>Keterangan</th>
                        <th width="60">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken  = '<?= csrf_hash() ?>';
const csrfName   = '<?= csrf_token() ?>';
const tahunAktif = <?= $tahun ?>;

$(function() {
    const dtLibur = $('#dt-libur').DataTable({
        processing: true, serverSide: true,
        ajax: {
            url: '/admin/pkpt/hari-libur/data',
            type: 'POST',
            data: d => { d[csrfName] = csrfToken; d.tahun = tahunAktif; }
        },
        columns: [
            { data: null, render: (d,t,r,m) => m.row + m.settings._iDisplayStart + 1, orderable: false },
            { data: 'tanggal' },
            { data: 'hari', orderable: false },
            { data: 'keterangan' },
            { data: 'aksi', orderable: false },
        ],
        order: [[1, 'asc']],
        pageLength: 25,
    });

    $(document).on('click', '.btn-del-libur', function() {
        const id = $(this).data('id');
        swalConfirm({
            title: 'Hapus Hari Libur?',
            html: 'Hari libur ini akan dihapus dan perhitungan HP akan berubah.',
            icon: 'warning',
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="fas fa-trash"></i>&nbsp;Ya, Hapus',
        }, () => {
            $.post('/admin/pkpt/hari-libur/delete/' + id, { [csrfName]: csrfToken }, res => {
                if (res.success) dtLibur.ajax.reload(null, false);
            });
        });
    });
});

// Hari libur nasional default (bisa diedit/dikembangkan)
const liburDefault = [
    { tanggal: tahunAktif+'-01-01', keterangan: 'Tahun Baru Masehi' },
    { tanggal: tahunAktif+'-01-27', keterangan: 'Isra Mi\'raj' },
    { tanggal: tahunAktif+'-02-12', keterangan: 'Imlek' },
    { tanggal: tahunAktif+'-03-29', keterangan: 'Jumat Agung' },
    { tanggal: tahunAktif+'-03-31', keterangan: 'Nyepi' },
    { tanggal: tahunAktif+'-04-18', keterangan: 'Wafat Isa Al Masih' },
    { tanggal: tahunAktif+'-05-01', keterangan: 'Hari Buruh' },
    { tanggal: tahunAktif+'-05-12', keterangan: 'Hari Raya Waisak' },
    { tanggal: tahunAktif+'-05-29', keterangan: 'Kenaikan Isa Al Masih' },
    { tanggal: tahunAktif+'-06-01', keterangan: 'Hari Lahir Pancasila' },
    { tanggal: tahunAktif+'-06-06', keterangan: 'Idul Adha' },
    { tanggal: tahunAktif+'-06-27', keterangan: 'Tahun Baru Hijriah' },
    { tanggal: tahunAktif+'-08-17', keterangan: 'HUT RI' },
    { tanggal: tahunAktif+'-09-05', keterangan: 'Maulid Nabi' },
    { tanggal: tahunAktif+'-12-25', keterangan: 'Natal' },
];

// Tampilkan tombol import cepat
const container = $('#libur-default');
liburDefault.forEach(l => {
    const d = new Date(l.tanggal);
    const label = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short'});
    container.append(`<span class="badge badge-secondary" style="cursor:pointer;font-size:11px;padding:4px 8px" 
        data-tanggal="${l.tanggal}" data-ket="${l.keterangan}" title="${l.keterangan}">
        ${label}
    </span>`);
});

container.on('click', 'span', function() {
    const tanggal = $(this).data('tanggal');
    const ket     = $(this).data('ket');
    $('input[name=tanggal]').val(tanggal);
    $('input[name=keterangan]').val(ket);
});

$('#btn-import-all').on('click', function() {
    const self = this;
    swalConfirm({
        title: 'Import Hari Libur?',
        html: 'Semua <b>' + liburDefault.length + ' hari libur default</b> tahun ' + tahunAktif + ' akan diimport.',
        icon: 'question',
        confirmButtonText: '<i class="fas fa-download"></i>&nbsp;Ya, Import',
    }, () => {
        const btn = $(self).prop('disabled', true).text('Mengimport...');
        $.post('/admin/pkpt/hari-libur/store-batch', {
            [csrfName]: csrfToken,
            tahun: tahunAktif,
            rows: liburDefault
        }, function(res) {
            if (res.success) {
                location.reload();
            } else {
                btn.prop('disabled', false).text('Import Semua');
            }
        });
    });
});

$('#sel-tahun').on('change', function() {
    window.location.href = '/admin/pkpt/hari-libur?tahun=' + $(this).val();
});
</script>
<?= $this->endSection() ?>
