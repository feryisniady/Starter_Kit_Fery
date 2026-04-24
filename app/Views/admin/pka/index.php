<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$awBudgetMap = $awBudgetMap ?? [];
$sdmInfoMap  = $sdmInfoMap  ?? [];
$atList      = $atList      ?? [];
$faseLabel   = ['persiapan' => 'Persiapan', 'pelaksanaan' => 'Pelaksanaan', 'pelaporan' => 'Pelaporan'];
$faseColor   = ['persiapan' => '#6366f1', 'pelaksanaan' => '#0ea5e9', 'pelaporan' => '#22c55e'];
$faseIcon    = ['persiapan' => 'search', 'pelaksanaan' => 'tasks', 'pelaporan' => 'file-alt'];

// Hitung total HP PKA per AT (server-side, untuk panel rekap)
$pkaHpMap = [];
foreach ($pkaList as $p) {
    foreach (($p['assigned_sdm'] ?? []) as $a) {
        $pid = (int)$a['sdm_id'];
        $pkaHpMap[$pid] = ($pkaHpMap[$pid] ?? 0) + (float)($p['rencana_waktu'] ?? 0);
    }
}
?>

<!-- ═══ Page Header ════════════════════════════════════════════════════ -->
<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-list-check"></i> Program Pengawasan (PKA)</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['tujuan']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>/pka/print-km6" target="_blank" class="btn btn-outline-primary">
            <i class="fas fa-print"></i> Cetak KM-6
        </a>
        <a href="/admin/spt/<?= $spt['id'] ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- ═══ Statistik ════════════════════════════════════════════════════ -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px">
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#6366f1"><?= $stats['total'] ?></div>
            <div style="font-size:12px;color:#64748b">Total Prosedur</div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#22c55e"><?= $stats['selesai'] ?></div>
            <div style="font-size:12px;color:#64748b">Dikerjakan</div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#f59e0b"><?= $stats['belum'] ?></div>
            <div style="font-size:12px;color:#64748b">Belum</div>
        </div>
    </div>
</div>

<!-- ═══ Panel Rekap HP per AT ════════════════════════════════════════ -->
<?php if (!empty($awBudgetMap)): ?>
<div class="card mb-3">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
        <h3 class="card-title" style="margin:0"><i class="fas fa-chart-bar"></i> Rekap HP per Anggota Tim</h3>
        <span style="font-size:11px;color:#94a3b8">Dibandingkan vs total Rencana KM-2</span>
    </div>
    <div class="card-body" style="padding:12px 16px">
    <div id="hp-summary-rows">
    <?php foreach ($awBudgetMap as $sdmId => $budget):
        $used = $pkaHpMap[$sdmId] ?? 0;
        $sisa = $budget - $used;
        $pct  = $budget > 0 ? min(100, round($used/$budget*100)) : ($used > 0 ? 100 : 0);
        $info = $sdmInfoMap[$sdmId] ?? ['nama' => 'SDM #'.$sdmId, 'peran_spt' => ''];
        if ($sisa < 0) { $barColor = '#ef4444'; $statusHtml = '<span style="color:#ef4444;font-weight:700"><i class="fas fa-triangle-exclamation"></i> Lebih '.number_format(abs($sisa),1).' HP</span>'; }
        elseif ($pct >= 80) { $barColor = '#f59e0b'; $statusHtml = '<span style="color:#d97706;font-weight:600"><i class="fas fa-triangle-exclamation"></i> Mendekati batas</span>'; }
        else { $barColor = '#22c55e'; $statusHtml = '<span style="color:#16a34a;font-weight:600"><i class="fas fa-check"></i> Sisa '.number_format($sisa,1).' HP</span>'; }
    ?>
    <div class="hp-summary-row" data-summary-sdm="<?= $sdmId ?>"
         style="display:flex;align-items:center;gap:12px;padding:7px 0;border-bottom:1px solid #f1f5f9;flex-wrap:wrap">
        <div style="width:28px;height:28px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#6366f1;flex-shrink:0">
            <?= strtoupper(substr($info['nama'],0,1)) ?>
        </div>
        <div style="width:160px;min-width:0">
            <div style="font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($info['nama']) ?></div>
            <div style="font-size:10px;color:#94a3b8"><?= esc($info['peran_spt']) ?></div>
        </div>
        <div style="font-size:11px;color:#64748b;white-space:nowrap">Budget KM-2: <strong style="color:#1e293b"><?= number_format($budget,1) ?> HP</strong></div>
        <div style="font-size:11px;color:#64748b;white-space:nowrap">PKA: <strong class="hp-used-val" style="color:#1e293b"><?= number_format($used,1) ?> HP</strong></div>
        <div style="flex:1;min-width:80px;max-width:160px;height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden">
            <div class="hp-bar-fill" style="height:100%;width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:4px;transition:width .3s,background .3s"></div>
        </div>
        <div style="font-size:11px;min-width:120px" class="hp-status-text"><?= $statusHtml ?></div>
    </div>
    <?php endforeach; ?>
    </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══ Gunakan Template ══════════════════════════════════════════════ -->
<?php if ($canEdit && !empty($templateList)): ?>
<div class="card mb-3" style="border:1px dashed #6366f1;background:#f8f7ff">
    <div class="card-body" style="padding:12px 16px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
        <div style="flex:1;min-width:200px">
            <div style="font-size:12px;font-weight:700;color:#6366f1;margin-bottom:2px">
                <i class="fas fa-magic"></i> Gunakan Template Prosedur
            </div>
            <div style="font-size:11px;color:#64748b">Isi prosedur PKA otomatis dari library template sesuai jenis audit</div>
        </div>
        <form action="/admin/spt/<?= $spt['id'] ?>/pka/apply-template" method="POST" style="display:flex;gap:8px;align-items:center">
            <?= csrf_field() ?>
            <select name="template_id" class="form-control form-control-sm" style="min-width:220px" required>
                <option value="">— Pilih Template —</option>
                <?php foreach($templateList as $tpl): ?>
                <option value="<?= $tpl['id'] ?>">
                    <?= esc($tpl['nama']) ?>
                    <?php if($tpl['jenis_audit']): ?>(<?= esc($tpl['jenis_audit']) ?>)<?php endif; ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary"
                    onclick="return confirm('Prosedur dari template akan ditambahkan ke PKA. Lanjutkan?')">
                <i class="fas fa-download"></i> Terapkan
            </button>
        </form>
        <a href="/admin/pka-template" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-cog"></i> Kelola Template
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ═══ PKA per Fase ════════════════════════════════════════════════ -->
<?php foreach (['persiapan','pelaksanaan','pelaporan'] as $fase):
    $rows = $pkaGrouped[$fase] ?? [];
    $color = $faseColor[$fase];
    $icon  = $faseIcon[$fase];
    $label = $faseLabel[$fase];
?>
<div class="card mb-3">
    <div class="card-header" style="background:<?= $color ?>10;border-bottom:2px solid <?= $color ?>">
        <div style="display:flex;align-items:center;justify-content:space-between">
            <h3 class="card-title" style="margin:0;color:<?= $color ?>">
                <i class="fas fa-<?= $icon ?>"></i> <?= $label ?>
                <span style="font-size:12px;font-weight:400;color:#64748b;margin-left:8px"><?= count($rows) ?> prosedur</span>
            </h3>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($rows)): ?>
        <div style="padding:20px;text-align:center;color:#94a3b8;font-size:13px">
            <i class="fas fa-inbox"></i> Belum ada prosedur pada fase ini.
            <?php if($canEdit): ?> Tambah menggunakan form di bawah.<?php endif; ?>
        </div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                    <th style="padding:8px 12px;font-size:11px;color:#64748b;font-weight:600;width:40px">No</th>
                    <th style="padding:8px 12px;font-size:11px;color:#64748b;font-weight:600">Uraian Prosedur</th>
                    <th style="padding:8px 12px;font-size:11px;color:#64748b;font-weight:600;width:90px;text-align:center">Rencana (HP)</th>
                    <th style="padding:8px 12px;font-size:11px;color:#64748b;font-weight:600;width:180px">Assigned AT</th>
                    <th style="padding:8px 12px;font-size:11px;color:#64748b;font-weight:600;width:100px;text-align:center">Status</th>
                    <th style="padding:8px 12px;font-size:11px;color:#64748b;font-weight:600;width:110px;text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($rows as $row): ?>
            <tr id="pka-row-<?= $row['id'] ?>" style="border-bottom:1px solid #f1f5f9"
                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:10px 12px;font-weight:600;color:#6366f1"><?= $row['nomor_urut'] ?></td>
                <td style="padding:10px 12px;font-size:13px"><?= esc($row['uraian_prosedur']) ?></td>
                <td style="padding:10px 12px;text-align:center;font-size:13px">
                    <?= $row['rencana_waktu'] ? $row['rencana_waktu'].' HP' : '—' ?>
                </td>
                <td style="padding:10px 12px">
                    <?php if (!empty($row['assigned_sdm'])): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:4px">
                        <?php foreach($row['assigned_sdm'] as $a): ?>
                        <span style="font-size:10px;background:#e0e7ff;color:#4338ca;padding:2px 7px;border-radius:99px;font-weight:600">
                            <?= esc($a['nama']) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <span style="font-size:11px;color:#94a3b8"><i class="fas fa-user-slash"></i> Belum di-assign</span>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 12px;text-align:center">
                    <span class="badge badge-<?= $row['status'] === 'selesai' ? 'success' : 'secondary' ?>" id="status-badge-<?= $row['id'] ?>">
                        <?= $row['status'] === 'selesai' ? 'Dikerjakan' : 'Belum' ?>
                    </span>
                </td>
                <td style="padding:10px 12px;text-align:center">
                    <?php if ($canEdit): ?>
                    <button class="btn btn-xs btn-<?= $row['status']==='selesai' ? 'outline-secondary':'success' ?> btn-selesai"
                            data-id="<?= $row['id'] ?>"
                            title="<?= $row['status']==='selesai' ? 'Batal tandai':'Tandai dikerjakan' ?>"
                            style="font-size:11px;padding:2px 7px">
                        <?= $row['status']==='selesai' ? '<i class="fas fa-undo"></i> Batal' : '<i class="fas fa-check"></i> Dikerjakan' ?>
                    </button>
                    <button class="btn btn-xs btn-primary btn-edit-pka"
                            data-id="<?= $row['id'] ?>"
                            data-uraian="<?= esc($row['uraian_prosedur']) ?>"
                            data-fase="<?= $row['fase'] ?>"
                            data-rencana="<?= $row['rencana_waktu'] ?? '' ?>"
                            data-assigned='<?= json_encode(array_column($row['assigned_sdm'], 'sdm_id')) ?>'>
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-xs btn-danger btn-del-pka" data-id="<?= $row['id'] ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php else: ?>
                    <span style="color:#94a3b8;font-size:12px"><i class="fas fa-lock"></i></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- ═══ Form Tambah Prosedur ══════════════════════════════════════════ -->
<?php if ($canEdit): ?>
<div class="card mb-3">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-plus"></i> Tambah Prosedur</h3></div>
    <div class="card-body">
        <form action="/admin/spt/<?= $spt['id'] ?>/pka/store" method="POST">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:1fr 130px 130px auto;gap:12px;align-items:start">
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">Uraian Prosedur <span style="color:red">*</span></label>
                    <textarea name="uraian_prosedur" class="form-control" rows="2" required
                              placeholder="Deskripsikan prosedur audit..."></textarea>
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">Fase</label>
                    <select name="fase" class="form-control">
                        <option value="persiapan">Persiapan</option>
                        <option value="pelaksanaan" selected>Pelaksanaan</option>
                        <option value="pelaporan">Pelaporan</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">Rencana (HP)</label>
                    <input type="number" step="0.5" min="0" name="rencana_waktu"
                           class="form-control" placeholder="0">
                    <div style="font-size:10px;color:#94a3b8;margin-top:2px">Hari Pemeriksaan</div>
                </div>
                <div class="form-group mb-0">
                    <label style="visibility:hidden;font-size:12px">.</label>
                    <button type="submit" class="btn btn-primary" style="display:block;width:100%">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </div>

            <?php if (!empty($atList)): ?>
            <div style="margin-top:12px;padding:12px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0">
                <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:8px">
                    <i class="fas fa-user-check"></i> Assign ke Anggota Tim
                    <span style="font-size:11px;font-weight:400;color:#94a3b8">(centang AT yang mengerjakan prosedur ini)</span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:10px">
                    <?php foreach($atList as $at): ?>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:5px 10px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;font-size:12px">
                        <input type="checkbox" name="assign_sdm_ids[]" value="<?= $at['id'] ?>"
                               style="width:14px;height:14px">
                        <?= esc($at['nama']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ═══ Modal Edit PKA ════════════════════════════════════════════════ -->
<?php if ($canEdit): ?>
<div id="modal-edit-pka" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:600px">
        <div class="modal-header">
            <h3>Edit Prosedur PKA</h3>
            <button class="modal-close" onclick="$('#modal-edit-pka').hide()"><i class="fas fa-times"></i></button>
        </div>
        <form id="form-edit-pka">
            <?= csrf_field() ?>
            <input type="hidden" id="edit-pka-id">
            <div class="form-group">
                <label>Uraian Prosedur</label>
                <textarea id="edit-uraian" name="uraian_prosedur" class="form-control" rows="3"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group mb-0">
                    <label>Fase</label>
                    <select id="edit-fase" name="fase" class="form-control">
                        <option value="persiapan">Persiapan</option>
                        <option value="pelaksanaan">Pelaksanaan</option>
                        <option value="pelaporan">Pelaporan</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Rencana (HP) <span style="font-size:10px;color:#94a3b8;font-weight:400">— Hari Pemeriksaan</span></label>
                    <input type="number" step="0.5" min="0" id="edit-rencana" name="rencana_waktu" class="form-control">
                </div>
            </div>
            <div style="font-size:11px;color:#94a3b8;margin-top:6px;margin-bottom:12px">
                <i class="fas fa-info-circle"></i> Realisasi dicatat melalui KKA oleh Anggota Tim.
            </div>

            <?php if (!empty($atList)): ?>
            <div style="padding:12px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0">
                <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:8px">
                    <i class="fas fa-user-check"></i> Assign ke Anggota Tim
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:10px">
                    <?php foreach($atList as $at): ?>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:5px 10px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;font-size:12px">
                        <input type="checkbox" class="edit-assign-cb" name="assign_sdm_ids[]"
                               value="<?= $at['id'] ?>" style="width:14px;height:14px">
                        <?= esc($at['nama']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="$('#modal-edit-pka').hide()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken   = '<?= csrf_hash() ?>';
const csrfName    = '<?= csrf_token() ?>';
const awBudgetMap = <?= json_encode(array_map('floatval', $awBudgetMap)) ?>;
const sdmInfoMap  = <?= json_encode($sdmInfoMap) ?>;

// ── Toggle dikerjakan ─────────────────────────────────────────────────────
$(document).on('click', '.btn-selesai', function() {
    const id  = $(this).data('id');
    const btn = $(this);
    $.post('/admin/spt/pka/selesai/' + id, { [csrfName]: csrfToken }, res => {
        if (!res.success) return;
        const done = res.status === 'selesai';
        $('#status-badge-' + id)
            .removeClass('badge-secondary badge-success')
            .addClass(done ? 'badge-success' : 'badge-secondary')
            .text(done ? 'Dikerjakan' : 'Belum');
        btn.removeClass('btn-success btn-outline-secondary')
           .addClass(done ? 'btn-outline-secondary' : 'btn-success')
           .html(done ? '<i class="fas fa-undo"></i> Batal' : '<i class="fas fa-check"></i> Dikerjakan');
    });
});

// ── Buka modal edit ───────────────────────────────────────────────────────
$(document).on('click', '.btn-edit-pka', function() {
    const rowId    = $(this).data('id');
    const assigned = $(this).data('assigned') || [];
    $('#edit-pka-id').val(rowId);
    $('#edit-uraian').val($(this).data('uraian'));
    $('#edit-fase').val($(this).data('fase') || 'pelaksanaan');
    $('#edit-rencana').val($(this).data('rencana'));
    // Set checkbox assignment
    $('.edit-assign-cb').prop('checked', false);
    assigned.forEach(sdmId => {
        $(`.edit-assign-cb[value="${sdmId}"]`).prop('checked', true);
    });
    $('#modal-edit-pka').show();
});

// ── Submit edit via AJAX ──────────────────────────────────────────────────
$('#form-edit-pka').on('submit', function(e) {
    e.preventDefault();
    const id   = $('#edit-pka-id').val();
    const data = $(this).serialize() + '&' + csrfName + '=' + csrfToken;
    $.post('/admin/spt/pka/update/' + id, data, res => {
        if (res.success) location.reload();
        else alert('Gagal menyimpan.');
    });
});

// ── Hapus prosedur ────────────────────────────────────────────────────────
$(document).on('click', '.btn-del-pka', function() {
    if (!confirm('Hapus prosedur ini?')) return;
    const id  = $(this).data('id');
    const row = $(this).closest('tr');
    $.post('/admin/spt/pka/delete/' + id, { [csrfName]: csrfToken }, res => {
        if (res.success) location.reload();
        else alert('Gagal menghapus.');
    });
});
</script>
<?= $this->endSection() ?>
