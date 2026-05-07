<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Surat Perintah Tugas</h1>
        <p>Manajemen SPT Inspektorat</p>
    </div>
    <div class="page-actions">
        <?php
        $slotPenuh = !empty($slotInfo) && $slotInfo['terpakai'] >= $slotInfo['maks'];
        ?>
        <?php if ($slotPenuh): ?>
        <button type="button" class="btn btn-warning" disabled
                title="Slot penuh — selesaikan LHP terlebih dahulu">
            <i class="fas fa-lock me-1"></i> SPT Non-PKPT
        </button>
        <?php else: ?>
        <a href="/admin/spt/non-pkpt/create" class="btn btn-warning">
            <i class="fas fa-star"></i> SPT Non-PKPT
        </a>
        <?php endif; ?>
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
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i>
    <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<?php if (!empty($slotInfo)): ?>
<?php
$terpakai = $slotInfo['terpakai'];
$maks     = $slotInfo['maks'];
$sisa     = $slotInfo['sisa'];
$persen   = $slotInfo['persen'];
$barClass = $persen >= 100 ? 'danger' : ($persen >= 66 ? 'warning' : 'success');
?>
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div style="min-width:260px;flex:1">
                <div class="d-flex justify-content-between mb-1" style="font-size:13px">
                    <span class="fw-semibold text-secondary">
                        <i class="fas fa-clipboard-list me-1"></i> Slot SPT Aktif
                    </span>
                    <span class="fw-bold text-<?= $barClass ?>">
                        <?= $terpakai ?> / <?= $maks ?> terpakai
                        <?php if ($sisa > 0): ?>
                        <span class="text-success fw-normal">(sisa <?= $sisa ?>)</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="progress-bar-wrap" style="height:10px;border-radius:6px;background:#e2e8f0">
                    <div style="width:<?= $persen ?>%;height:100%;border-radius:6px;background:var(--<?= $barClass === 'danger' ? 'danger' : ($barClass === 'warning' ? 'warning' : 'success') ?>)"></div>
                </div>
                <div style="font-size:11px;color:#94a3b8;margin-top:3px">
                    Investigasi &amp; Kasus/Khusus tidak dihitung dalam kuota
                </div>
            </div>
            <div class="d-flex align-items-center">
                <?php if ($persen >= 100): ?>
                <span class="badge badge-danger" style="font-size:12px;padding:6px 12px">
                    <i class="fas fa-lock me-1"></i> Slot Penuh
                </span>
                <?php elseif ($persen >= 66): ?>
                <span class="badge badge-warning" style="font-size:12px;padding:6px 12px">
                    <i class="fas fa-exclamation-triangle me-1"></i> Hampir Penuh
                </span>
                <?php else: ?>
                <span class="badge badge-success" style="font-size:12px;padding:6px 12px">
                    <i class="fas fa-check-circle me-1"></i> Tersedia
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($slotInfo['list_antrian'])): ?>
<div class="card mb-3" style="border-left:4px solid #f59e0b">
    <div class="card-body py-3">
        <div class="d-flex align-items-start gap-2" style="font-size:13px">
            <i class="fas fa-clock text-warning mt-1 flex-shrink-0"></i>
            <div class="w-100">
                <div class="fw-bold mb-2">Antrian LHP — harus diselesaikan secara urut:</div>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($slotInfo['list_antrian'] as $i => $item): ?>
                    <div class="d-flex align-items-center gap-2 p-2 rounded"
                         style="background:<?= $i === 0 ? '#fef2f2' : '#f8fafc' ?>;border:1px solid <?= $i === 0 ? '#fca5a5' : '#e2e8f0' ?>">
                        <span class="badge badge-<?= $i === 0 ? 'danger' : 'secondary' ?>"><?= $i + 1 ?></span>
                        <div>
                            <div class="fw-semibold" style="font-size:12px">
                                <?= esc($item['nomor_naskah'] ?: '#' . $item['id']) ?>
                            </div>
                            <div style="font-size:11px;color:#64748b">
                                <?= esc($item['label_kegiatan'] ?? '—') ?>
                            </div>
                        </div>
                        <?php if ($i === 0): ?>
                        <a href="/admin/spt/<?= $item['id'] ?>/lhp"
                           class="btn btn-xs btn-danger ms-1" title="Upload LHP sekarang">
                            <i class="fas fa-upload"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
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
                    <th width="80">LHP</th>
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
            { data: 'lhp_status',   orderable: false, searchable: false },
            { data: 'status',       orderable: false, searchable: false },
            { data: 'aksi',         orderable: false, searchable: false },
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
