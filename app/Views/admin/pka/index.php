<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$awBudgetMap = $awBudgetMap ?? [];
$sdmInfoMap  = $sdmInfoMap  ?? [];
// Hitung total HP PKA per PIC (server-side, untuk render awal)
$pkaHpMap = [];
foreach ($pkaList as $p) {
    $pid = (int)($p['pic_sdm_id'] ?? 0);
    if ($pid) $pkaHpMap[$pid] = ($pkaHpMap[$pid] ?? 0) + (float)($p['rencana_waktu'] ?? 0);
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>Program Kerja Audit (PKA)</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['tujuan']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="/admin/spt/<?= $spt['id'] ?>/temuan" class="btn btn-primary"><i class="fas fa-exclamation-triangle"></i> Temuan</a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Statistik -->
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
            <div style="font-size:12px;color:#64748b">Selesai</div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#f59e0b"><?= $stats['belum'] ?></div>
            <div style="font-size:12px;color:#64748b">Belum</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     PANEL REKAP HP PER PIC
     ═══════════════════════════════════════════════════════ -->
<div class="card mb-3">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
        <h3 class="card-title" style="margin:0"><i class="fas fa-chart-bar"></i> Rekap Alokasi HP per PIC</h3>
        <span style="font-size:11px;color:#94a3b8"><i class="fas fa-info-circle"></i> Dibandingkan vs total Rencana KM-2</span>
    </div>
    <div class="card-body" style="padding:12px 16px">
        <?php if (empty($awBudgetMap)): ?>
        <div style="font-size:12px;color:#94a3b8;padding:6px 0">
            <i class="fas fa-info-circle"></i>
            KM-2 Anggaran Waktu belum diisi. Isi terlebih dahulu untuk menampilkan monitoring HP per PIC.
        </div>
        <?php else: ?>
        <div id="hp-summary-rows">
        <?php foreach ($awBudgetMap as $sdmId => $budget):
            $used = $pkaHpMap[$sdmId] ?? 0;
            $sisa = $budget - $used;
            $pct  = $budget > 0 ? min(100, round($used / $budget * 100)) : ($used > 0 ? 100 : 0);
            $info = $sdmInfoMap[$sdmId] ?? ['nama' => 'SDM #'.$sdmId, 'peran_spt' => ''];
            if ($sisa < 0) {
                $barColor = '#ef4444';
                $statusHtml = '<span style="color:#ef4444;font-weight:700"><i class="fas fa-triangle-exclamation"></i> Lebih '.number_format(abs($sisa),1).' HP</span>';
            } elseif ($pct >= 80) {
                $barColor = '#f59e0b';
                $statusHtml = '<span style="color:#d97706;font-weight:600"><i class="fas fa-triangle-exclamation"></i> Mendekati batas</span>';
            } else {
                $barColor = '#22c55e';
                $statusHtml = '<span style="color:#16a34a;font-weight:600"><i class="fas fa-check"></i> Sisa '.number_format($sisa,1).' HP</span>';
            }
        ?>
        <div class="hp-summary-row" data-summary-sdm="<?= $sdmId ?>"
             style="display:flex;align-items:center;gap:12px;padding:7px 0;border-bottom:1px solid #f1f5f9;flex-wrap:wrap">
            <div style="width:28px;height:28px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#6366f1;flex-shrink:0">
                <?= strtoupper(substr($info['nama'], 0, 1)) ?>
            </div>
            <div style="width:150px;min-width:0">
                <div style="font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($info['nama']) ?></div>
                <div style="font-size:10px;color:#94a3b8"><?= esc($info['peran_spt']) ?></div>
            </div>
            <div style="font-size:11px;color:#64748b;white-space:nowrap">
                Budget KM-2: <strong style="color:#1e293b"><?= number_format($budget, 1) ?> HP</strong>
            </div>
            <div style="font-size:11px;color:#64748b;white-space:nowrap">
                PKA: <strong class="hp-used-val" style="color:#1e293b"><?= number_format($used, 1) ?> HP</strong>
            </div>
            <div style="flex:1;min-width:80px;max-width:160px;height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden">
                <div class="hp-bar-fill" style="height:100%;width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:4px;transition:width .3s,background .3s"></div>
            </div>
            <div style="font-size:11px;min-width:110px" class="hp-status-text"><?= $statusHtml ?></div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TABEL PKA
     ═══════════════════════════════════════════════════════ -->
<div class="card mb-3">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-list-check"></i> Prosedur Audit</h3></div>
    <div class="card-body">
        <table id="dt-pka" class="w-100">
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Uraian Prosedur</th>
                    <th width="160">PIC</th>
                    <th width="90">Rencana (HP)</th>
                    <th width="90">Realisasi</th>
                    <th width="100">Status</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody id="pka-tbody">
            <?php foreach($pkaList as $row): ?>
            <tr id="pka-row-<?= $row['id'] ?>"
                data-pic-id="<?= (int)($row['pic_sdm_id'] ?? 0) ?>"
                data-rencana="<?= (float)($row['rencana_waktu'] ?? 0) ?>">
                <td><?= $row['nomor_urut'] ?></td>
                <td><?= esc($row['uraian_prosedur']) ?></td>
                <td><?= esc($row['pic_nama'] ?? '—') ?></td>
                <td style="text-align:center"><?= $row['rencana_waktu'] ?? '—' ?></td>
                <td style="text-align:center;color:#94a3b8;font-size:12px">
                    <?php if ($row['realisasi_waktu'] !== null): ?>
                    <span title="Diperbarui melalui KKA"><?= $row['realisasi_waktu'] ?> HP</span>
                    <?php else: ?>
                    <span title="Belum ada realisasi">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge badge-<?= $row['status'] === 'selesai' ? 'success' : 'secondary' ?>" id="status-badge-<?= $row['id'] ?>">
                        <?= $row['status'] === 'selesai' ? 'Dikerjakan' : 'Belum' ?>
                    </span>
                </td>
                <td>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-xs btn-<?= $row['status'] === 'selesai' ? 'outline-secondary' : 'success' ?> btn-selesai"
                            data-id="<?= $row['id'] ?>"
                            title="<?= $row['status'] === 'selesai' ? 'Batal tandai dikerjakan' : 'Tandai sudah dikerjakan' ?>"
                            style="font-size:11px;padding:2px 7px">
                        <?php if ($row['status'] === 'selesai'): ?>
                        <i class="fas fa-undo"></i> Batal
                        <?php else: ?>
                        <i class="fas fa-check"></i> Dikerjakan
                        <?php endif; ?>
                    </button>
                    <button class="btn btn-xs btn-primary btn-edit-pka"
                            data-id="<?= $row['id'] ?>"
                            data-uraian="<?= esc($row['uraian_prosedur']) ?>"
                            data-pic="<?= $row['pic_sdm_id'] ?? '' ?>"
                            data-rencana="<?= $row['rencana_waktu'] ?? '' ?>">
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
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     FORM TAMBAH PROSEDUR
     ═══════════════════════════════════════════════════════ -->
<?php if ($canEdit): ?>
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-plus"></i> Tambah Prosedur</h3></div>
    <div class="card-body">
        <form action="/admin/spt/<?= $spt['id'] ?>/pka/store" method="POST">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:1fr 200px 110px auto;gap:12px;align-items:start">
                <div class="form-group mb-0">
                    <label>Uraian Prosedur <span style="color:red">*</span></label>
                    <textarea name="uraian_prosedur" class="form-control" rows="2" required placeholder="Deskripsikan prosedur audit..."></textarea>
                </div>
                <div class="form-group mb-0">
                    <label>PIC</label>
                    <select name="pic_sdm_id" id="add-pic-sdm" class="form-control">
                        <option value="">— Pilih SDM —</option>
                        <?php foreach($sdmList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= esc($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="add-pic-widget" style="display:none;margin-top:4px"></div>
                </div>
                <div class="form-group mb-0">
                    <label>Rencana (HP)</label>
                    <input type="number" step="0.5" min="0" name="rencana_waktu" id="add-rencana-hp"
                           class="form-control" placeholder="0">
                    <div style="font-size:10px;color:#94a3b8;margin-top:2px">Hari Pemeriksaan</div>
                </div>
                <div class="form-group mb-0">
                    <label style="visibility:hidden">.</label>
                    <button type="submit" class="btn btn-primary" style="display:block;width:100%">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     MODAL EDIT PKA
     ═══════════════════════════════════════════════════════ -->
<?php if ($canEdit): ?>
<div id="modal-edit-pka" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:560px">
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
                    <label>PIC</label>
                    <select id="edit-pic" name="pic_sdm_id" class="form-control">
                        <option value="">— Pilih SDM —</option>
                        <?php foreach($sdmList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= esc($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Rencana (HP) <span style="font-size:10px;color:#94a3b8;font-weight:400">— Hari Pemeriksaan</span></label>
                    <input type="number" step="0.5" min="0" id="edit-rencana" name="rencana_waktu" class="form-control">
                </div>
            </div>
            <div style="font-size:11px;color:#94a3b8;margin-top:6px">
                <i class="fas fa-info-circle"></i> Realisasi waktu dicatat otomatis melalui KKA oleh Anggota Tim.
            </div>
            <!-- Widget HP di modal edit -->
            <div id="edit-pic-widget" style="display:none;margin-top:10px"></div>
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
const csrfToken  = '<?= csrf_hash() ?>';
const csrfName   = '<?= csrf_token() ?>';
const awBudgetMap = <?= json_encode(array_map('floatval', $awBudgetMap)) ?>;
const sdmInfoMap  = <?= json_encode($sdmInfoMap) ?>;

// ── Hitung total HP PKA per PIC dari baris tabel ─────────────────────────
function getPkaHpMap(excludeRowId) {
    const map = {};
    $('#pka-tbody tr[data-pic-id]').each(function() {
        if (excludeRowId && $(this).attr('id') === 'pka-row-' + excludeRowId) return;
        const picId = parseInt($(this).data('pic-id'));
        const hp    = parseFloat($(this).data('rencana')) || 0;
        if (picId) map[picId] = (map[picId] || 0) + hp;
    });
    return map;
}

// ── Render panel summary HP per PIC ──────────────────────────────────────
function refreshSummaryPanel() {
    const hpMap = getPkaHpMap();
    $('.hp-summary-row').each(function() {
        const sdmId  = parseInt($(this).data('summary-sdm'));
        const budget = awBudgetMap[sdmId] || 0;
        const used   = hpMap[sdmId] || 0;
        const sisa   = budget - used;
        const pct    = budget > 0 ? Math.min(100, Math.round(used / budget * 100)) : (used > 0 ? 100 : 0);

        let barColor, statusHtml;
        if (sisa < 0) {
            barColor   = '#ef4444';
            statusHtml = `<span style="color:#ef4444;font-weight:700"><i class="fas fa-triangle-exclamation"></i> Lebih ${Math.abs(sisa).toFixed(1)} HP</span>`;
        } else if (pct >= 80) {
            barColor   = '#f59e0b';
            statusHtml = `<span style="color:#d97706;font-weight:600"><i class="fas fa-triangle-exclamation"></i> Mendekati batas</span>`;
        } else {
            barColor   = '#22c55e';
            statusHtml = `<span style="color:#16a34a;font-weight:600"><i class="fas fa-check"></i> Sisa ${sisa.toFixed(1)} HP</span>`;
        }

        $(this).find('.hp-used-val').text(used.toFixed(1) + ' HP');
        $(this).find('.hp-bar-fill').css({ width: pct + '%', background: barColor });
        $(this).find('.hp-status-text').html(statusHtml);
    });
}

// ── Mini widget di form tambah / modal edit ───────────────────────────────
function renderMiniWidget(containerId, picId, newHp, excludeRowId) {
    const container = $(containerId);
    picId = parseInt(picId);
    if (!picId || !awBudgetMap[picId]) { container.hide(); return; }

    const budget = awBudgetMap[picId] || 0;
    const prev   = getPkaHpMap(excludeRowId)[picId] || 0;
    const total  = prev + newHp;
    const sisa   = budget - total;
    const pct    = budget > 0 ? Math.min(100, Math.round(total / budget * 100)) : (total > 0 ? 100 : 0);

    let color, msg;
    if (sisa < 0) {
        color = '#ef4444';
        msg   = `<i class="fas fa-triangle-exclamation"></i> Melebihi ${Math.abs(sisa).toFixed(1)} HP!`;
    } else if (pct >= 80) {
        color = '#f59e0b';
        msg   = `<i class="fas fa-triangle-exclamation"></i> Mendekati batas`;
    } else {
        color = '#22c55e';
        msg   = `<i class="fas fa-check"></i> Sisa ${sisa.toFixed(1)} HP`;
    }

    container.show().html(`
        <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;flex-wrap:wrap">
            <span style="font-size:11px;color:#64748b;white-space:nowrap">Budget KM-2: <strong>${budget.toFixed(1)} HP</strong></span>
            <span style="color:#e2e8f0">|</span>
            <span style="font-size:11px;color:#64748b;white-space:nowrap">Total PKA: <strong style="color:#1e293b">${total.toFixed(1)} HP</strong></span>
            <div style="flex:1;min-width:50px;max-width:120px;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden">
                <div style="height:100%;width:${pct}%;background:${color};border-radius:3px;transition:width .2s"></div>
            </div>
            <span style="font-size:11px;color:${color};font-weight:600;white-space:nowrap">${msg}</span>
        </div>`);
}

// ── DataTable ─────────────────────────────────────────────────────────────
let dt;
$(function() {
    dt = $('#dt-pka').DataTable({
        paging: false, language: DT_LANG_ID, order: [],
        columnDefs: [{ orderable: false, targets: [5, 6] }],
    });

    // Toggle dikerjakan
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
               .attr('title', done ? 'Batal tandai dikerjakan' : 'Tandai sudah dikerjakan')
               .html(done
                   ? '<i class="fas fa-undo"></i> Batal'
                   : '<i class="fas fa-check"></i> Dikerjakan');
        });
    });

    // Buka modal edit
    $(document).on('click', '.btn-edit-pka', function() {
        const rowId = $(this).data('id');
        $('#edit-pka-id').val(rowId);
        $('#edit-uraian').val($(this).data('uraian'));
        $('#edit-pic').val($(this).data('pic'));
        $('#edit-rencana').val($(this).data('rencana'));
        $('#edit-pic-widget').hide();
        renderMiniWidget('#edit-pic-widget', $(this).data('pic'), parseFloat($(this).data('rencana')) || 0, rowId);
        $('#modal-edit-pka').show();
    });

    // Live widget saat PIC/HP berubah di modal edit
    $(document).on('change input', '#edit-pic, #edit-rencana', function() {
        const rowId = $('#edit-pka-id').val();
        renderMiniWidget('#edit-pic-widget', $('#edit-pic').val(), parseFloat($('#edit-rencana').val()) || 0, rowId);
    });

    // Submit edit via AJAX — reload agar panel summary ter-refresh
    $('#form-edit-pka').on('submit', function(e) {
        e.preventDefault();
        const id   = $('#edit-pka-id').val();
        const data = $(this).serialize() + '&' + csrfName + '=' + csrfToken;
        $.post('/admin/spt/pka/update/' + id, data, res => {
            if (res.success) location.reload();
            else alert('Gagal menyimpan.');
        });
    });

    // Hapus PKA — update panel summary setelah row dihapus
    $(document).on('click', '.btn-del-pka', function() {
        if (!confirm('Hapus prosedur ini?')) return;
        const id  = $(this).data('id');
        const row = $(this).closest('tr');
        $.post('/admin/spt/pka/delete/' + id, { [csrfName]: csrfToken }, res => {
            if (res.success) {
                dt.row(row).remove().draw();
                refreshSummaryPanel();
            } else alert('Gagal menghapus.');
        });
    });

    // Live widget form tambah — saat PIC atau HP berubah
    $('#add-pic-sdm, #add-rencana-hp').on('change input', function() {
        renderMiniWidget('#add-pic-widget', $('#add-pic-sdm').val(), parseFloat($('#add-rencana-hp').val()) || 0, null);
    });
});
</script>
<?= $this->endSection() ?>
