<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$awBudgetMap   = $awBudgetMap   ?? [];
$sdmInfoMap    = $sdmInfoMap    ?? [];
$atList        = $atList        ?? [];
$assignmentMap = $assignmentMap ?? [];
$faseLabel     = ['persiapan' => 'Persiapan', 'pelaksanaan' => 'Pelaksanaan', 'pelaporan' => 'Pelaporan'];
$faseColor     = ['persiapan' => '#6366f1', 'pelaksanaan' => '#0ea5e9', 'pelaporan' => '#22c55e'];
$faseIcon      = ['persiapan' => 'search', 'pelaksanaan' => 'tasks', 'pelaporan' => 'file-alt'];

// HP PKA per AT (untuk panel Tab 2)
$pkaHpMap = [];
foreach ($pkaList as $p) {
    foreach (($p['assigned_sdm'] ?? []) as $a) {
        $pid = (int)$a['sdm_id'];
        $pkaHpMap[$pid] = ($pkaHpMap[$pid] ?? 0) + (float)($p['rencana_waktu'] ?? 0);
    }
}
?>

<!-- ═══ Page Header ═══════════════════════════════════════════════════ -->
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

<!-- ═══ Statistik ═════════════════════════════════════════════════════ -->
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

<!-- ═══ Tab Switcher ══════════════════════════════════════════════════ -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0">
    <button class="tab-btn active" data-tab="prosedur"
            style="padding:10px 20px;border:none;background:none;font-size:13px;font-weight:600;color:#6366f1;border-bottom:2px solid #6366f1;margin-bottom:-2px;cursor:pointer">
        <i class="fas fa-list-check"></i> Prosedur
        <span style="background:#6366f1;color:#fff;font-size:11px;padding:1px 6px;border-radius:99px;margin-left:4px"><?= $stats['total'] ?></span>
    </button>
    <button class="tab-btn" data-tab="penugasan"
            style="padding:10px 20px;border:none;background:none;font-size:13px;font-weight:600;color:#64748b;cursor:pointer">
        <i class="fas fa-user-check"></i> Penugasan AT
        <span style="background:#e2e8f0;color:#64748b;font-size:11px;padding:1px 6px;border-radius:99px;margin-left:4px"><?= count($atList) ?></span>
    </button>
</div>

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB 1: PROSEDUR                                                    -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<div id="tab-prosedur" class="tab-pane">

    <!-- Template auto-suggest -->
    <?php if ($canEdit && !empty($templateList)): ?>
    <div class="card mb-3" style="border:1px dashed #6366f1;background:#f8f7ff">
        <div class="card-body" style="padding:12px 16px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <div style="font-size:12px;font-weight:700;color:#6366f1;margin-bottom:2px">
                    <i class="fas fa-magic"></i> Gunakan Template Prosedur
                </div>
                <div style="font-size:11px;color:#64748b">
                    Tambah prosedur otomatis dari library
                    <?php if ($jenisAudit): ?>
                    — <strong style="color:#6366f1">Cocok: <?= esc($jenisAudit) ?></strong>
                    <?php endif; ?>
                </div>
            </div>
            <form action="/admin/spt/<?= $spt['id'] ?>/pka/apply-template" method="POST"
                  style="display:flex;gap:8px;align-items:center"
                  data-confirm="Prosedur dari template akan ditambahkan. Prosedur yang sudah ada <b>tidak akan terhapus</b>."
                  data-confirm-title="Terapkan Template?"
                  data-confirm-btn="<i class='fas fa-download'></i>&nbsp;Ya, Terapkan">
                <?= csrf_field() ?>
                <select name="template_id" class="form-control form-control-sm" style="min-width:220px" required>
                    <option value="">— Pilih Template —</option>
                    <?php foreach($templateList as $tpl): ?>
                    <option value="<?= $tpl['id'] ?>"
                        <?= ($jenisAudit && stripos($tpl['jenis_audit'] ?? '', $jenisAudit) !== false) ? 'style="font-weight:700;color:#6366f1"' : '' ?>>
                        <?= esc($tpl['nama']) ?>
                        <?php if($tpl['jenis_audit']): ?>(<?= esc($tpl['jenis_audit']) ?>)<?php endif; ?>
                        <?= ($jenisAudit && stripos($tpl['jenis_audit'] ?? '', $jenisAudit) !== false) ? ' ★' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-download"></i> Terapkan
                </button>
            </form>
            <a href="/admin/pka-template" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-cog"></i> Kelola Template
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Prosedur per Fase -->
    <?php foreach (['persiapan','pelaksanaan','pelaporan'] as $fase):
        $rows  = $pkaGrouped[$fase] ?? [];
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
            <div style="padding:14px 16px;color:#94a3b8;font-size:13px;text-align:center">
                <i class="fas fa-inbox"></i> Belum ada prosedur.
                <?php if($canEdit): ?> Tambah lewat form di bawah.<?php endif; ?>
            </div>
            <?php else: ?>
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                        <th style="padding:7px 10px;font-size:11px;color:#64748b;font-weight:600;width:36px">No</th>
                        <th style="padding:7px 10px;font-size:11px;color:#64748b;font-weight:600">Uraian Prosedur</th>
                        <th style="padding:7px 10px;font-size:11px;color:#64748b;font-weight:600;width:80px;text-align:center">HP</th>
                        <th style="padding:7px 10px;font-size:11px;color:#64748b;font-weight:600;width:90px;text-align:center">Status</th>
                        <?php if($canEdit): ?>
                        <th style="padding:7px 10px;font-size:11px;color:#64748b;font-weight:600;width:100px;text-align:center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($rows as $row): ?>
                <tr id="pka-row-<?= $row['id'] ?>" style="border-bottom:1px solid #f1f5f9"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:9px 10px;font-weight:600;color:<?= $color ?>"><?= $row['nomor_urut'] ?></td>
                    <td style="padding:9px 10px;font-size:13px"><?= esc($row['uraian_prosedur']) ?></td>
                    <td style="padding:9px 10px;text-align:center;font-size:13px">
                        <?= $row['rencana_waktu'] ? $row['rencana_waktu'].' HP' : '—' ?>
                    </td>
                    <td style="padding:9px 10px;text-align:center">
                        <span class="badge badge-<?= $row['status']==='selesai' ? 'success':'secondary' ?>" id="status-badge-<?= $row['id'] ?>">
                            <?= $row['status']==='selesai' ? 'Dikerjakan' : 'Belum' ?>
                        </span>
                    </td>
                    <?php if($canEdit): ?>
                    <td style="padding:9px 10px;text-align:center">
                        <button class="btn btn-xs btn-<?= $row['status']==='selesai' ? 'outline-secondary':'success' ?> btn-selesai"
                                data-id="<?= $row['id'] ?>" style="font-size:11px;padding:2px 6px">
                            <?= $row['status']==='selesai' ? '<i class="fas fa-undo"></i>':'<i class="fas fa-check"></i>' ?>
                        </button>
                        <button class="btn btn-xs btn-primary btn-edit-pka"
                                data-id="<?= $row['id'] ?>"
                                data-uraian="<?= esc($row['uraian_prosedur']) ?>"
                                data-fase="<?= $row['fase'] ?>"
                                data-rencana="<?= $row['rencana_waktu'] ?? '' ?>"
                                data-assigned='<?= json_encode(array_column($row['assigned_sdm'] ?? [], 'sdm_id')) ?>'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-xs btn-danger btn-del-pka" data-id="<?= $row['id'] ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- Inline Add Form per Fase -->
            <?php if ($canEdit): ?>
            <div style="padding:10px 12px;background:#f8fafc;border-top:1px solid #e2e8f0">
                <form action="/admin/spt/<?= $spt['id'] ?>/pka/store" method="POST"
                      style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
                    <?= csrf_field() ?>
                    <input type="hidden" name="fase" value="<?= $fase ?>">
                    <div style="flex:1;min-width:200px">
                        <input type="text" name="uraian_prosedur" class="form-control form-control-sm"
                               placeholder="Tambah prosedur <?= $label ?>..." required>
                    </div>
                    <div style="width:90px">
                        <input type="number" step="0.5" min="0" name="rencana_waktu"
                               class="form-control form-control-sm" placeholder="HP">
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:<?= $color ?>;color:#fff;white-space:nowrap">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div><!-- /tab-prosedur -->

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB 2: PENUGASAN                                                   -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<div id="tab-penugasan" class="tab-pane" style="display:none">

    <!-- HP Budget Tracker -->
    <?php if (!empty($awBudgetMap)): ?>
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title" style="margin:0"><i class="fas fa-chart-bar"></i> Rekap HP per Anggota Tim</h3>
        </div>
        <div class="card-body" style="padding:12px 16px">
        <?php foreach ($awBudgetMap as $sdmId => $budget):
            $used = $pkaHpMap[$sdmId] ?? 0;
            $sisa = $budget - $used;
            $pct  = $budget > 0 ? min(100, round($used/$budget*100)) : ($used > 0 ? 100 : 0);
            $info = $sdmInfoMap[$sdmId] ?? ['nama' => 'SDM #'.$sdmId, 'peran_spt' => ''];
            if ($sisa < 0)      { $barColor = '#ef4444'; $statusHtml = '<span style="color:#ef4444;font-weight:700"><i class="fas fa-triangle-exclamation"></i> Lebih '.number_format(abs($sisa),1).' HP</span>'; }
            elseif ($pct >= 80) { $barColor = '#f59e0b'; $statusHtml = '<span style="color:#d97706;font-weight:600"><i class="fas fa-triangle-exclamation"></i> Mendekati batas</span>'; }
            else                { $barColor = '#22c55e'; $statusHtml = '<span style="color:#16a34a;font-weight:600"><i class="fas fa-check"></i> Sisa '.number_format($sisa,1).' HP</span>'; }
        ?>
        <div style="display:flex;align-items:center;gap:12px;padding:7px 0;border-bottom:1px solid #f1f5f9;flex-wrap:wrap"
             data-hp-row="<?= $sdmId ?>">
            <div style="width:28px;height:28px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#6366f1;flex-shrink:0">
                <?= strtoupper(substr($info['nama'],0,1)) ?>
            </div>
            <div style="width:160px;min-width:0">
                <div style="font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($info['nama']) ?></div>
                <div style="font-size:10px;color:#94a3b8"><?= esc($info['peran_spt']) ?></div>
            </div>
            <div style="font-size:11px;color:#64748b;white-space:nowrap">Budget: <strong><?= number_format($budget,1) ?> HP</strong></div>
            <div style="font-size:11px;color:#64748b;white-space:nowrap">PKA: <strong class="hp-used-val" id="hp-used-<?= $sdmId ?>"><?= number_format($used,1) ?></strong> HP</div>
            <div style="flex:1;min-width:80px;max-width:160px;height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden">
                <div class="hp-bar-fill" id="hp-bar-<?= $sdmId ?>"
                     style="height:100%;width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:4px;transition:width .3s,background .3s"></div>
            </div>
            <div style="font-size:11px;min-width:130px" id="hp-status-<?= $sdmId ?>"><?= $statusHtml ?></div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Assignment Matrix -->
    <div class="card mb-3">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
            <h3 class="card-title" style="margin:0"><i class="fas fa-table"></i> Matriks Penugasan Prosedur</h3>
            <?php if($canEdit): ?>
            <button id="btn-save-assignments" class="btn btn-sm btn-primary">
                <i class="fas fa-save"></i> Simpan Semua Penugasan
            </button>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding:0;overflow-x:auto">
            <?php if (empty($pkaList)): ?>
            <div style="padding:24px;text-align:center;color:#94a3b8">
                <i class="fas fa-inbox"></i> Belum ada prosedur. Tambahkan prosedur di tab Prosedur terlebih dahulu.
            </div>
            <?php elseif (empty($atList)): ?>
            <div style="padding:24px;text-align:center;color:#94a3b8">
                <i class="fas fa-users-slash"></i> Belum ada Anggota Tim pada SPT ini.
            </div>
            <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                        <th style="padding:8px 10px;text-align:left;border:1px solid #e2e8f0;min-width:280px">Prosedur</th>
                        <th style="padding:8px 10px;text-align:center;border:1px solid #e2e8f0;width:60px">HP</th>
                        <?php foreach($atList as $at): ?>
                        <th style="padding:8px 6px;text-align:center;border:1px solid #e2e8f0;min-width:80px;max-width:100px">
                            <div style="font-size:11px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= esc($at['nama']) ?>">
                                <?= esc(explode(' ', $at['nama'])[0]) ?>
                            </div>
                            <div style="font-size:9px;color:#94a3b8"><?= esc($at['peran_spt']) ?></div>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $lastFase = null;
                $matrixHpMap = [];  // sdm_id => total HP from checked boxes
                foreach ($atList as $at) $matrixHpMap[(int)$at['id']] = 0;

                foreach ($pkaList as $row):
                    if ($row['fase'] !== $lastFase):
                        $lastFase = $row['fase'];
                ?>
                    <tr style="background:<?= $faseColor[$row['fase']] ?>15">
                        <td colspan="<?= 2 + count($atList) ?>"
                            style="padding:5px 10px;font-size:11px;font-weight:700;color:<?= $faseColor[$row['fase']] ?>;border:1px solid #e2e8f0">
                            <i class="fas fa-<?= $faseIcon[$row['fase']] ?>"></i> <?= $faseLabel[$row['fase']] ?>
                        </td>
                    </tr>
                <?php endif; ?>
                    <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td style="padding:8px 10px;border:1px solid #e2e8f0">
                            <span style="color:<?= $faseColor[$row['fase']] ?>;font-weight:700;margin-right:6px"><?= $row['nomor_urut'] ?></span>
                            <?= esc($row['uraian_prosedur']) ?>
                        </td>
                        <td style="padding:8px 6px;text-align:center;border:1px solid #e2e8f0;color:#64748b;font-size:11px">
                            <?= $row['rencana_waktu'] ?? '—' ?>
                        </td>
                        <?php
                        $assignedIds = $assignmentMap[(int)$row['id']] ?? [];
                        foreach($atList as $at):
                            $checked = in_array((int)$at['id'], array_map('intval', $assignedIds));
                            if ($checked) $matrixHpMap[(int)$at['id']] += (float)($row['rencana_waktu'] ?? 0);
                        ?>
                        <td style="padding:8px 6px;text-align:center;border:1px solid #e2e8f0">
                            <?php if($canEdit): ?>
                            <input type="checkbox"
                                   class="assign-cb"
                                   data-pka="<?= $row['id'] ?>"
                                   data-sdm="<?= $at['id'] ?>"
                                   data-hp="<?= (float)($row['rencana_waktu'] ?? 0) ?>"
                                   <?= $checked ? 'checked' : '' ?>
                                   style="width:16px;height:16px;cursor:pointer">
                            <?php else: ?>
                            <?= $checked ? '<i class="fas fa-check" style="color:#22c55e"></i>' : '<span style="color:#e2e8f0">—</span>' ?>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <!-- HP Summary row -->
                <tfoot>
                    <tr style="background:#f8fafc;font-weight:700;border-top:2px solid #e2e8f0">
                        <td colspan="2" style="padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;color:#64748b">Total HP ter-assign</td>
                        <?php foreach($atList as $at): ?>
                        <td style="padding:8px 6px;text-align:center;border:1px solid #e2e8f0;font-size:12px;color:#6366f1"
                            id="matrix-hp-<?= $at['id'] ?>">
                            <?= $matrixHpMap[(int)$at['id']] ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /tab-penugasan -->

<!-- ═══ Modal Edit PKA ════════════════════════════════════════════════ -->
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
                    <label>Fase</label>
                    <select id="edit-fase" name="fase" class="form-control">
                        <option value="persiapan">Persiapan</option>
                        <option value="pelaksanaan">Pelaksanaan</option>
                        <option value="pelaporan">Pelaporan</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Rencana (HP)</label>
                    <input type="number" step="0.5" min="0" id="edit-rencana" name="rencana_waktu" class="form-control">
                </div>
            </div>
            <div style="font-size:11px;color:#94a3b8;margin-top:8px">
                <i class="fas fa-info-circle"></i> Penugasan AT diatur di tab <strong>Penugasan</strong>.
            </div>
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
const csrfToken    = '<?= csrf_hash() ?>';
const csrfName     = '<?= csrf_token() ?>';
const awBudgetMap  = <?= json_encode(array_map('floatval', $awBudgetMap)) ?>;
const sdmInfoMap   = <?= json_encode($sdmInfoMap) ?>;

// ── Tab switching ─────────────────────────────────────────────────────
$('.tab-btn').on('click', function() {
    const tab = $(this).data('tab');
    $('.tab-btn').each(function() {
        const active = $(this).data('tab') === tab;
        $(this).css({
            color: active ? '#6366f1' : '#64748b',
            borderBottom: active ? '2px solid #6366f1' : '2px solid transparent',
            marginBottom: active ? '-2px' : '0'
        });
    });
    $('.tab-pane').hide();
    $('#tab-' + tab).show();
});

// ── Toggle dikerjakan ─────────────────────────────────────────────────
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
           .html(done ? '<i class="fas fa-undo"></i>' : '<i class="fas fa-check"></i>');
    });
});

// ── Buka modal edit ───────────────────────────────────────────────────
$(document).on('click', '.btn-edit-pka', function() {
    const rowId = $(this).data('id');
    $('#edit-pka-id').val(rowId);
    $('#edit-uraian').val($(this).data('uraian'));
    $('#edit-fase').val($(this).data('fase') || 'pelaksanaan');
    $('#edit-rencana').val($(this).data('rencana'));
    $('#modal-edit-pka').show();
});

// ── Submit edit via AJAX ──────────────────────────────────────────────
$('#form-edit-pka').on('submit', function(e) {
    e.preventDefault();
    const id   = $('#edit-pka-id').val();
    const data = $(this).serialize() + '&' + csrfName + '=' + csrfToken;
    $.post('/admin/spt/pka/update/' + id, data, res => {
        if (res.success) location.reload();
        else alert('Gagal menyimpan.');
    });
});

// ── Hapus prosedur ────────────────────────────────────────────────────
$(document).on('click', '.btn-del-pka', function() {
    const id = $(this).data('id');
    swalConfirm({
        title: 'Hapus Prosedur?',
        html: 'Prosedur ini akan dihapus dan tidak bisa dipulihkan.',
        icon: 'warning',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fas fa-trash"></i>&nbsp;Ya, Hapus',
    }, () => {
        $.post('/admin/spt/pka/delete/' + id, { [csrfName]: csrfToken }, res => {
            if (res.success) location.reload();
            else Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghapus prosedur.', timer: 2500 });
        });
    });
});

// ── Assignment matrix: update HP tally on checkbox change ─────────────
$(document).on('change', '.assign-cb', function() {
    const sdmId = $(this).data('sdm');
    const hp    = parseFloat($(this).data('hp')) || 0;

    // Recalculate total HP for this AT column
    let total = 0;
    $(`.assign-cb[data-sdm="${sdmId}"]:checked`).each(function() {
        total += parseFloat($(this).data('hp')) || 0;
    });
    $('#matrix-hp-' + sdmId).text(total);

    // Update tracker panel if visible
    const budget = awBudgetMap[sdmId] || 0;
    const pct    = budget > 0 ? Math.min(100, Math.round(total / budget * 100)) : (total > 0 ? 100 : 0);
    const sisa   = budget - total;
    const barColor = sisa < 0 ? '#ef4444' : (pct >= 80 ? '#f59e0b' : '#22c55e');

    $('#hp-used-' + sdmId).text(total.toFixed(1));
    $('#hp-bar-'  + sdmId).css({ width: pct + '%', background: barColor });

    if (sisa < 0) {
        $('#hp-status-' + sdmId).html(`<span style="color:#ef4444;font-weight:700"><i class="fas fa-triangle-exclamation"></i> Lebih ${Math.abs(sisa).toFixed(1)} HP</span>`);
    } else if (pct >= 80) {
        $('#hp-status-' + sdmId).html(`<span style="color:#d97706;font-weight:600"><i class="fas fa-triangle-exclamation"></i> Mendekati batas</span>`);
    } else {
        $('#hp-status-' + sdmId).html(`<span style="color:#16a34a;font-weight:600"><i class="fas fa-check"></i> Sisa ${sisa.toFixed(1)} HP</span>`);
    }
});

// ── Save all assignments ──────────────────────────────────────────────
$('#btn-save-assignments').on('click', function() {
    const btn  = $(this);
    const data = { [csrfName]: csrfToken };

    // Build assignments object: assignments[pka_id][] = sdm_id
    $('.assign-cb:checked').each(function() {
        const pkaId = $(this).data('pka');
        const sdmId = $(this).data('sdm');
        const key   = `assignments[${pkaId}][]`;
        if (!data[key]) data[key] = [];
        data[key].push(sdmId);
    });

    // Also send pka_ids with no assignments (empty array) to clear them
    $('.assign-cb').each(function() {
        const pkaId = $(this).data('pka');
        const key   = `assignments[${pkaId}][]`;
        if (!data[key]) data[key] = [];
    });

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

    $.post('/admin/spt/<?= $spt['id'] ?>/pka/save-assignments', data, res => {
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Semua Penugasan');
        if (res.success) {
            Swal.fire({ icon: 'success', title: 'Tersimpan!', text: res.message, timer: 1800, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal menyimpan penugasan.' });
        }
    }).fail(() => {
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Semua Penugasan');
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
    });
});
</script>
<?= $this->endSection() ?>
