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

<!-- Info PKPT + HP Summary -->
<?php
$totalHpIrbanIni  = array_sum(array_column($kegiatan, 'total_hp'));     // HP yang dipakai irban ini
$hpEfektifShow    = $hpEfektif ?? ($setting ? (int)$setting['total_hp_tahunan'] : 0); // dinamis dari controller
$hpGlobalTerpakai = $hpGlobalTerpakai ?? $totalHpIrbanIni;              // dari controller (semua irban)
$hpSisaShow       = max(0, $hpEfektifShow - $hpGlobalTerpakai);         // sisa dari budget organisasi
$hpPctShow        = $hpEfektifShow > 0 ? min(100, round(($hpGlobalTerpakai / $hpEfektifShow) * 100)) : 0;
$hpBarColorShow   = $hpPctShow >= 90 ? '#ef4444' : ($hpPctShow >= 70 ? '#f59e0b' : '#22c55e');
?>
<div class="card mb-3" style="border-left:4px solid #6366f1">
    <div class="card-body" style="padding:14px 20px">
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:14px">
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Irban</div>
                <div style="font-size:14px;font-weight:600"><?= esc($pkpt['irban_nama']) ?></div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Total Kegiatan</div>
                <div style="font-size:20px;font-weight:700;color:#6366f1"><?= count($kegiatan) ?></div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">HP Efektif</div>
                <div style="font-size:20px;font-weight:700;color:#64748b"><?= $hpEfektifShow ?></div>
                <div style="font-size:11px;color:#94a3b8">hari/tahun</div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">HP Terpakai Irban Ini</div>
                <div style="font-size:20px;font-weight:700;color:#f59e0b"><?= $totalHpIrbanIni ?></div>
                <div style="font-size:11px;color:#94a3b8"><?= $hpPctShow ?>% utilisasi (total org)</div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Sisa HP (Semua Irban)</div>
                <div style="font-size:20px;font-weight:700;color:<?= $hpSisaShow <= 0 ? '#ef4444' : '#22c55e' ?>"><?= $hpSisaShow ?></div>
                <div style="font-size:11px;color:#94a3b8">
                    Rp <?= number_format(array_sum(array_column($kegiatan, 'total_anggaran')), 0, ',', '.') ?>
                </div>
            </div>
        </div>
        <div style="height:8px;background:#f1f5f9;border-radius:4px;overflow:hidden">
            <div style="height:100%;width:<?= $hpPctShow ?>%;background:<?= $hpBarColorShow ?>;border-radius:4px"></div>
        </div>
    </div>
</div>

<!-- Modal Detail Kegiatan -->
<div id="modal-detail-kegiatan" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:580px;padding:0;overflow:hidden">
        <div style="background:linear-gradient(135deg,#0369a1 0%,#0ea5e9 100%);padding:16px 20px 14px;position:relative">
            <button onclick="$('#modal-detail-kegiatan').hide()"
                    style="position:absolute;top:10px;right:12px;background:rgba(255,255,255,.2);border:none;color:#fff;width:26px;height:26px;border-radius:50%;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-times"></i>
            </button>
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:38px;height:38px;border-radius:9px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-clipboard-list" style="color:#fff;font-size:16px"></i>
                </div>
                <div>
                    <h3 id="dk-title" style="color:#fff;margin:0;font-size:14px;font-weight:700">Detail Kegiatan</h3>
                    <p id="dk-kode" style="color:rgba(255,255,255,.8);margin:2px 0 0;font-size:11px"></p>
                </div>
            </div>
        </div>
        <div id="dk-body" style="padding:16px 20px;font-size:13px"></div>
    </div>
</div>

<!-- Daftar Kegiatan — DataTable -->
<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
        <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Daftar Kegiatan PKPT</h3>
        <span class="badge badge-primary" id="count-kegiatan"><?= count($kegiatan) ?></span>
    </div>
    <div class="card-body">
        <table id="dt-kegiatan" class="w-100">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th width="80">Kode</th>
                    <th>Area &amp; Jenis Pengawasan</th>
                    <th width="80">Risiko</th>
                    <th width="140">Periode</th>
                    <th width="70">HP</th>
                    <th width="100">Status SPT</th>
                    <th width="130" class="dt-nosort dt-nosearch">Aksi</th>
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

$(function() {
    const dt = $('#dt-kegiatan').DataTable({
        processing: true,
        serverSide: true,
        language: DT_LANG_ID,
        ajax: {
            url: '/admin/pkpt/<?= $pkpt['id'] ?>/kegiatan/data',
            type: 'POST',
            data: d => { d[csrfName] = csrfToken; }
        },
        columns: [
            { data: 'no',      orderable: false },
            { data: 'kode',    orderable: false },
            { data: 'area',    orderable: false },
            { data: 'risiko',  orderable: false },
            { data: 'periode', orderable: false },
            { data: 'hp',      orderable: false },
            { data: 'spt',     orderable: false },
            { data: 'aksi',    orderable: false, searchable: false },
        ],
        drawCallback: function(s) {
            $('#count-kegiatan').text(s.json?.recordsTotal ?? '');
        },
    });

});

function viewKegiatan(id) {
    $.get('/admin/pkpt/kegiatan/view/' + id, res => {
        if (!res.success) return;
        const d  = res.data;
        const rc = {rendah:'#16a34a', sedang:'#d97706', tinggi:'#dc2626'};
        const sc = {draft:'secondary',diajukan:'info',acc_irban:'primary',acc_evlap:'primary',acc_sekretaris:'primary',terbit:'success'};
        const sl = {draft:'Draft',diajukan:'Diajukan',acc_irban:'ACC Irban',acc_evlap:'ACC Evlap',acc_sekretaris:'ACC Sekretaris',terbit:'Terbit'};

        $('#dk-title').text(d.area_pengawasan + ' — ' + d.jenis_pengawasan);
        $('#dk-kode').text(d.kode_kegiatan);

        // SPT list block
        let sptHtml = '';
        if (d.spts && d.spts.length > 0) {
            sptHtml = `<div style="margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:8px">
                    <i class="fas fa-file-signature"></i> SPT yang Sudah Ada (${d.spts.length} Tim)
                </div>
                <div style="display:flex;flex-direction:column;gap:6px">`;
            d.spts.forEach(s => {
                const nama    = s.nama_tim || ('SPT #' + s.id);
                const noNaskah = s.nomor_naskah || '— belum ada nomor';
                const badge   = `<span class="badge badge-${sc[s.status]||'secondary'}" style="font-size:10px">${sl[s.status]||s.status}</span>`;
                sptHtml += `<div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:7px 10px;font-size:12px">
                    <i class="fas fa-users" style="color:#6366f1;flex-shrink:0"></i>
                    <span style="font-weight:600;flex:1">${nama}</span>
                    <span style="color:#64748b;font-size:11px">${noNaskah}</span>
                    ${badge}
                    <a href="/admin/spt/${s.id}" class="btn btn-xs btn-primary" style="white-space:nowrap">Buka</a>
                </div>`;
            });
            sptHtml += `</div></div>`;
        }

        $('#dk-body').html(`
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <tr style="border-bottom:1px solid #f1f5f9"><th style="padding:7px 10px;color:#64748b;font-weight:500;width:130px;white-space:nowrap">Kode</th><td style="padding:7px 10px"><span class="badge badge-primary">${d.kode_kegiatan}</span></td></tr>
                <tr style="border-bottom:1px solid #f1f5f9"><th style="padding:7px 10px;color:#64748b;font-weight:500">Area Pengawasan</th><td style="padding:7px 10px;font-weight:600">${d.area_pengawasan}</td></tr>
                <tr style="border-bottom:1px solid #f1f5f9"><th style="padding:7px 10px;color:#64748b;font-weight:500">Jenis Pengawasan</th><td style="padding:7px 10px">${d.jenis_pengawasan}</td></tr>
                <tr style="border-bottom:1px solid #f1f5f9"><th style="padding:7px 10px;color:#64748b;font-weight:500">Tujuan / Sasaran</th><td style="padding:7px 10px;font-size:12px;line-height:1.5">${d.tujuan_sasaran || '—'}</td></tr>
                <tr style="border-bottom:1px solid #f1f5f9"><th style="padding:7px 10px;color:#64748b;font-weight:500">Risiko</th><td style="padding:7px 10px"><span style="color:${rc[d.risiko_audit]||'#64748b'};font-weight:600">${(d.risiko_audit||'').charAt(0).toUpperCase()+(d.risiko_audit||'').slice(1)}</span></td></tr>
                <tr style="border-bottom:1px solid #f1f5f9"><th style="padding:7px 10px;color:#64748b;font-weight:500">Periode</th><td style="padding:7px 10px">${d.tanggal_mulai ? d.tanggal_mulai + ' s.d. ' + d.tanggal_selesai : '—'}</td></tr>
                <tr style="border-bottom:1px solid #f1f5f9"><th style="padding:7px 10px;color:#64748b;font-weight:500">Ruang Lingkup</th><td style="padding:7px 10px;font-size:12px">${d.ruang_lingkup || '—'}</td></tr>
                <tr><th style="padding:7px 10px;color:#64748b;font-weight:500">Jml Laporan</th><td style="padding:7px 10px">${d.jumlah_laporan || 1}</td></tr>
            </table>
            ${sptHtml}
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px;padding-top:10px;border-top:1px solid #f1f5f9">
                <a href="/admin/pkpt/kegiatan/edit/${d.id}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                <a href="/admin/spt/create/${d.id}" class="btn btn-sm btn-success">
                    <i class="fas fa-${d.spts && d.spts.length > 0 ? 'users-between-lines' : 'file-signature'}"></i>
                    ${d.spts && d.spts.length > 0 ? 'Buat Tim Baru' : 'Buat SPT'}
                </a>
            </div>
        `);
        $('#modal-detail-kegiatan').show();
    });
}

function delKegiatan(id) {
    Swal.fire({
        title: 'Hapus Kegiatan?',
        text: 'Data tim pada kegiatan ini juga akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.post('/admin/pkpt/kegiatan/delete/' + id, { [csrfName]: csrfToken }, res => {
            if (res.success) {
                $('#dt-kegiatan').DataTable().ajax.reload();
                Swal.fire({ icon: 'success', title: 'Dihapus!', timer: 1200, showConfirmButton: false });
            }
        });
    });
}

</script>
<?= $this->endSection() ?>
