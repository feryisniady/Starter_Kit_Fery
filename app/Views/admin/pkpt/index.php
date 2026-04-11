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
        <?php if($isAdmin): ?>
        <button class="btn btn-primary" id="btn-buat-pkpt">
            <i class="fas fa-plus"></i> Buat PKPT
        </button>
        <?php elseif($myPkpt): ?>
        <a href="/admin/pkpt/<?= $myPkpt['id'] ?>/kegiatan/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Kegiatan
        </a>
        <?php endif; ?>
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
        <?php if($isAdmin): ?>
        <div>
            <a href="/admin/pkpt/setting" class="btn btn-sm btn-outline-secondary" title="Edit Setting PKPT">
                <i class="fas fa-sliders"></i> Setting
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="alert-error-inline mb-3">
    <i class="fas fa-triangle-exclamation"></i>
    <strong>Header PKPT tahun <?= $tahun ?> belum diatur.</strong>
    <?php if($isAdmin): ?>
    Isi Nomor SK dan Tanggal Penetapan terlebih dahulu.
    <a href="/admin/pkpt/setting" class="btn btn-xs btn-primary" style="margin-left:12px"><i class="fas fa-sliders"></i> Atur Sekarang</a>
    <?php else: ?>
    Hubungi bagian Evlap untuk mengatur header PKPT tahun ini.
    <?php endif; ?>
</div>
<?php endif; ?>
</div>

<?php if($isAdmin): ?>
<!-- HP Real-time Monitor (Admin only) -->
<div class="card mb-3" id="hp-monitor-card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px">
        <h3 class="card-title" style="margin:0;display:flex;align-items:center;gap:8px">
            <span id="hp-live-dot" style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;box-shadow:0 0 0 0 rgba(34,197,94,.4);animation:pulse-dot 2s infinite"></span>
            Monitoring Sisa HP Real-time
        </h3>
        <div style="display:flex;align-items:center;gap:10px">
            <span id="hp-monitor-updated" style="font-size:11px;color:#94a3b8"></span>
            <button id="btn-refresh-hp" class="btn btn-sm btn-outline-secondary" title="Refresh sekarang">
                <i class="fas fa-rotate-right"></i>
            </button>
        </div>
    </div>
    <div class="card-body" id="hp-monitor-body" style="padding:16px 20px">
        <div style="text-align:center;padding:20px;color:#94a3b8">
            <i class="fas fa-spinner fa-spin"></i> Memuat data...
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(!$isAdmin && !$myPkpt): ?>
<div class="alert-error-inline mb-3">
    <i class="fas fa-clock"></i>
    <strong>PKPT irban Anda untuk tahun <?= $tahun ?> belum tersedia.</strong>
    Silakan hubungi bagian Evaluasi &amp; Pelaporan (Evlap) untuk membuat PKPT terlebih dahulu.
</div>
<?php endif; ?>

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
<style>
@keyframes pulse-dot {
    0%   { box-shadow: 0 0 0 0 rgba(34,197,94,.5); }
    70%  { box-shadow: 0 0 0 7px rgba(34,197,94,0); }
    100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
}
</style>
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

    // ── HP Monitor (admin only) ────────────────────────────────────
    if ($('#hp-monitor-card').length) {
        loadHpMonitor(currentTahun);
        const monitorInterval = setInterval(() => loadHpMonitor(currentTahun), 30000);

        $('#btn-refresh-hp').on('click', function() {
            const icon = $(this).find('i').addClass('fa-spin');
            loadHpMonitor(currentTahun, () => icon.removeClass('fa-spin'));
        });
    }
});

function loadHpMonitor(tahun, done) {
    $.get('/admin/pkpt/hp-monitor', { tahun }, function(d) {
        const pctColor = d.pct >= 90 ? '#ef4444' : (d.pct >= 70 ? '#f59e0b' : '#22c55e');
        const sisaPct  = Math.max(0, 100 - d.pct).toFixed(1);

        let html = `
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:14px;text-align:center">
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">HP Efektif</div>
                <div style="font-size:28px;font-weight:700;color:#64748b">${d.hp_efektif}</div>
                <div style="font-size:11px;color:#94a3b8">hari / tahun</div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">HP Terpakai</div>
                <div style="font-size:28px;font-weight:700;color:#f59e0b">${d.hp_terpakai_global}</div>
                <div style="font-size:11px;color:#94a3b8">${d.pct}% dari total</div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Sisa HP</div>
                <div style="font-size:28px;font-weight:700;color:${d.hp_sisa <= 0 ? '#ef4444' : '#22c55e'}">${d.hp_sisa}</div>
                <div style="font-size:11px;color:#94a3b8">${sisaPct}% tersisa</div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Irban Aktif</div>
                <div style="font-size:28px;font-weight:700;color:#6366f1">${d.irban_aktif}<span style="font-size:14px;color:#94a3b8"> / ${d.irbans.length}</span></div>
                <div style="font-size:11px;color:#94a3b8">memiliki kegiatan</div>
            </div>
        </div>
        <div style="height:10px;background:#f1f5f9;border-radius:5px;overflow:hidden;margin-bottom:16px" title="${d.pct}% terpakai">
            <div style="height:100%;width:${d.pct}%;background:${pctColor};border-radius:5px;transition:width .6s ease"></div>
        </div>
        <table style="width:100%;font-size:13px;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="text-align:left;padding:8px 10px;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Irban</th>
                    <th style="text-align:center;padding:8px 10px;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Kegiatan</th>
                    <th style="text-align:right;padding:8px 10px;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">HP Terpakai</th>
                    <th style="padding:8px 10px;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;min-width:160px">Utilisasi dari Total</th>
                </tr>
            </thead>
            <tbody>`;

        d.irbans.forEach(irban => {
            const bc = irban.pct >= 90 ? '#ef4444' : (irban.pct >= 70 ? '#f59e0b' : '#22c55e');
            html += `
            <tr style="border-top:1px solid #f1f5f9">
                <td style="padding:10px 10px">
                    <span class="badge badge-primary" style="margin-right:6px">${irban.kode}</span>
                    <span style="color:#1e293b">${irban.nama}</span>
                </td>
                <td style="text-align:center;padding:10px;font-weight:700;color:#6366f1">${irban.kegiatan}</td>
                <td style="text-align:right;padding:10px;font-weight:700;color:${irban.hp_terpakai > 0 ? '#f59e0b' : '#94a3b8'}">
                    ${irban.hp_terpakai} hari
                </td>
                <td style="padding:10px 10px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="flex:1;height:7px;background:#f1f5f9;border-radius:4px;overflow:hidden">
                            <div style="height:100%;width:${irban.pct}%;background:${bc};border-radius:4px;transition:width .5s ease"></div>
                        </div>
                        <span style="font-size:12px;color:#64748b;min-width:40px;text-align:right">${irban.pct}%</span>
                    </div>
                </td>
            </tr>`;
        });

        html += `</tbody></table>`;
        $('#hp-monitor-body').html(html);
        $('#hp-monitor-updated').text('Diperbarui: ' + d.updated_at);
        if (done) done();
    });
}
</script>
<?= $this->endSection() ?>
