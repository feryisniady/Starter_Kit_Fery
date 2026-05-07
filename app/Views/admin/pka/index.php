<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$awBudgetMap   = $awBudgetMap   ?? [];
$awFaseBudget  = $awFaseBudget  ?? ['persiapan' => 0, 'pelaksanaan' => 0, 'pelaporan' => 0];
$pkaFaseHp     = $pkaFaseHp     ?? ['persiapan' => 0, 'pelaksanaan' => 0, 'pelaporan' => 0];
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
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc(strip_tags($spt['tujuan'] ?? '')) ?></p>
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
<div style="display:flex;align-items:center;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0">
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
    <?php if($canEdit): ?>
    <div style="margin-left:auto;padding-bottom:6px">
        <button class="btn btn-sm btn-primary btn-tambah-pka" data-fase="pelaksanaan">
            <i class="fas fa-plus"></i> Tambah Prosedur
        </button>
    </div>
    <?php endif; ?>
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

    <!-- Info auto-status dari KKA -->
    <div class="box-info">
        <i class="fas fa-info-circle"></i>
        Status <strong>Dikerjakan</strong> otomatis berubah saat Anggota Tim menyimpan hasil observasi di KKA.
        Kolom <strong>Realisasi HP</strong> pada Formulir KM-6 juga terisi otomatis.
        <?php if ($canEdit): ?>Gunakan tombol <i class="fas fa-undo"></i> jika perlu membatalkan status secara manual.<?php endif; ?>
    </div>

    <!-- Prosedur — satu tabel, group row per fase -->
    <div class="card mb-3">
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="width:36px;text-align:center;padding:10px 8px;border-bottom:2px solid #e2e8f0">No</th>
                        <th style="padding:10px 10px;border-bottom:2px solid #e2e8f0">Uraian Prosedur</th>
                        <th style="width:80px;text-align:center;padding:10px 8px;border-bottom:2px solid #e2e8f0">HP</th>
                        <th style="width:90px;text-align:center;padding:10px 8px;border-bottom:2px solid #e2e8f0">Status</th>
                        <?php if($canEdit): ?>
                        <th style="width:100px;text-align:center;padding:10px 8px;border-bottom:2px solid #e2e8f0">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <?php foreach (['persiapan','pelaksanaan','pelaporan'] as $fase):
                    $rows  = $pkaGrouped[$fase] ?? [];
                    $color = $faseColor[$fase];
                    $icon  = $faseIcon[$fase];
                    $label = $faseLabel[$fase];
                ?>
                <tbody id="pka-tbody-<?= $fase ?>">
                    <!-- Fase group row -->
                    <?php
                    $faseBudget = (float)($awFaseBudget[$fase] ?? 0);
                    $faseUsed   = (float)($pkaFaseHp[$fase]    ?? 0);
                    $faseSisa   = $faseBudget - $faseUsed;
                    ?>
                    <tr style="background:<?= $color ?>12;border-top:2px solid <?= $color ?>40">
                        <td colspan="<?= $canEdit ? 5 : 4 ?>" style="padding:6px 12px">
                            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                                <span style="font-size:12px;font-weight:700;color:<?= $color ?>">
                                    <i class="fas fa-<?= $icon ?>"></i> <?= $label ?>
                                    <span id="pka-count-<?= $fase ?>"
                                          style="font-size:11px;font-weight:400;color:#64748b;margin-left:6px">
                                        <?= count($rows) ?> prosedur
                                    </span>
                                </span>
                                <?php if ($faseBudget > 0): ?>
                                <span id="pka-fase-hp-badge-<?= $fase ?>"
                                      style="font-size:11px;color:<?= $faseSisa < 0 ? '#ef4444' : ($faseSisa == 0 && $faseUsed > 0 ? '#22c55e' : '#64748b') ?>">
                                    <i class="fas fa-clock"></i>
                                    <span id="pka-fase-hp-used-<?= $fase ?>"><?= number_format($faseUsed,1) ?></span>
                                    / <?= number_format($faseBudget,1) ?> HP KM-2
                                    <?php if ($faseSisa < 0): ?>
                                    <span style="color:#ef4444;font-weight:700">
                                        <i class="fas fa-triangle-exclamation"></i> Lebih <?= number_format(abs($faseSisa),1) ?> HP
                                    </span>
                                    <?php elseif ($faseBudget > 0 && ($faseUsed / $faseBudget) >= 0.8): ?>
                                    <span style="color:#f59e0b;font-weight:600">
                                        <i class="fas fa-triangle-exclamation"></i> Mendekati batas
                                    </span>
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php if (empty($rows)): ?>
                    <tr class="pka-empty-row" id="pka-empty-<?= $fase ?>">
                        <td colspan="<?= $canEdit ? 5 : 4 ?>"
                            style="text-align:center;padding:14px;color:#94a3b8;font-size:13px">
                            <i class="fas fa-inbox"></i> Belum ada prosedur.
                            <?php if($canEdit): ?> Klik <strong>+ Tambah</strong> untuk menambah.<?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach($rows as $row): ?>
                    <tr id="pka-row-<?= $row['id'] ?>" style="border-bottom:1px solid #f1f5f9"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td style="padding:9px 10px;font-weight:600;color:<?= $color ?>;text-align:center"><?= $row['nomor_urut'] ?></td>
                        <td style="padding:9px 10px;font-size:13px;line-height:1.5"><?= render_wysiwyg($row['uraian_prosedur']) ?></td>
                        <td style="padding:9px 10px;text-align:center;font-size:13px">
                            <?= $row['rencana_waktu'] ? $row['rencana_waktu'].' HP' : '—' ?>
                        </td>
                        <td style="padding:9px 10px;text-align:center">
                            <span class="badge badge-<?= $row['status']==='selesai' ? 'success':'secondary' ?>"
                                  id="status-badge-<?= $row['id'] ?>">
                                <?= $row['status']==='selesai' ? 'Dikerjakan' : 'Belum' ?>
                            </span>
                        </td>
                        <?php if($canEdit): ?>
                        <td style="padding:9px 10px;text-align:center">
                            <?php if ($row['status'] !== 'selesai'): ?>
                            <button class="btn btn-xs btn-success btn-selesai"
                                    data-id="<?= $row['id'] ?>" style="font-size:11px;padding:2px 6px"
                                    title="Tandai dikerjakan">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn btn-xs btn-outline-secondary btn-selesai"
                                    data-id="<?= $row['id'] ?>" style="font-size:11px;padding:2px 6px"
                                    title="Batalkan (revert ke belum)">
                                <i class="fas fa-undo"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($row['status'] !== 'selesai'): ?>
                            <button class="btn btn-xs btn-primary btn-edit-pka"
                                    data-id="<?= $row['id'] ?>"
                                    data-uraian="<?= esc($row['uraian_prosedur']) ?>"
                                    data-fase="<?= $row['fase'] ?>"
                                    data-rencana="<?= $row['rencana_waktu'] ?? '' ?>"
                                    data-assigned='<?= json_encode(array_column($row['assigned_sdm'] ?? [], 'sdm_id')) ?>'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-xs btn-danger btn-del-pka" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
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
            <?php if (empty($atList)): ?>
            <div style="padding:24px;text-align:center;color:#94a3b8">
                <i class="fas fa-users-slash"></i> Belum ada Anggota Tim pada SPT ini.
            </div>
            <?php else: ?>
            <?php
            // Hitung HP terpakai per AT dari prosedur yang sudah ada
            $matrixHpMap = [];
            foreach ($atList as $at) $matrixHpMap[(int)$at['id']] = 0;
            if (!empty($pkaList)) {
                foreach ($pkaList as $row) {
                    $assignedIds = $assignmentMap[(int)$row['id']] ?? [];
                    foreach ($atList as $at) {
                        if (in_array((int)$at['id'], array_map('intval', $assignedIds))) {
                            $matrixHpMap[(int)$at['id']] += (float)($row['rencana_waktu'] ?? 0);
                        }
                    }
                }
            }
            $matrixColSpan = 2 + count($atList);
            ?>
            <table id="matrix-table" style="width:100%;border-collapse:collapse;font-size:12px">
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
                <tbody id="matrix-tbody">
                <?php if (empty($pkaList)): ?>
                    <!-- Empty state — akan diganti saat prosedur ditambah via AJAX -->
                    <tr id="matrix-empty-row">
                        <td colspan="<?= $matrixColSpan ?>"
                            style="padding:24px;text-align:center;color:#94a3b8;font-size:13px">
                            <i class="fas fa-inbox"></i> Belum ada prosedur.
                            Tambahkan prosedur di tab <strong>Prosedur</strong> terlebih dahulu.
                        </td>
                    </tr>
                <?php else: ?>
                <?php
                $lastFase = null;
                foreach ($pkaList as $row):
                    if ($row['fase'] !== $lastFase):
                        $lastFase = $row['fase'];
                ?>
                    <tr data-matrix-fase-header="<?= $row['fase'] ?>"
                        style="background:<?= $faseColor[$row['fase']] ?>15">
                        <td colspan="<?= $matrixColSpan ?>"
                            style="padding:5px 10px;font-size:11px;font-weight:700;color:<?= $faseColor[$row['fase']] ?>;border:1px solid #e2e8f0">
                            <i class="fas fa-<?= $faseIcon[$row['fase']] ?>"></i> <?= $faseLabel[$row['fase']] ?>
                        </td>
                    </tr>
                <?php endif; ?>
                    <?php
                    // Teks compact untuk matrix: list item dipisah · (misal "1. x · 2. y")
                    $mx_text  = pka_matrix_text($row['uraian_prosedur'] ?? '');
                    // Full plain text untuk tooltip
                    $mx_full  = strip_tags(preg_replace('/\s+/', ' ', trim($row['uraian_prosedur'] ?? '')));
                    ?>
                    <tr data-matrix-fase="<?= $row['fase'] ?>"
                        style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td style="padding:8px 10px;border:1px solid #e2e8f0">
                            <span style="color:<?= $faseColor[$row['fase']] ?>;font-weight:700;margin-right:6px"><?= $row['nomor_urut'] ?></span>
                            <span title="<?= esc($mx_full) ?>"><?= esc($mx_text) ?></span>
                        </td>
                        <td style="padding:8px 6px;text-align:center;border:1px solid #e2e8f0;color:#64748b;font-size:11px">
                            <?= $row['rencana_waktu'] ?? '—' ?>
                        </td>
                        <?php
                        $assignedIds = $assignmentMap[(int)$row['id']] ?? [];
                        foreach($atList as $at):
                            $checked = in_array((int)$at['id'], array_map('intval', $assignedIds));
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
                <?php endif; ?>
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

<!-- ═══ Modal PKA: Tambah / Edit (terpadu) ═══════════════════════════ -->
<?php if ($canEdit): ?>
<div id="modal-pka" class="modal-overlay">
    <div class="modal modal-lg">
        <form id="form-pka-modal">
            <?= csrf_field() ?>
            <input type="hidden" id="pka-modal-id">
            <input type="hidden" id="pka-modal-mode" value="add">

            <!-- Header -->
            <div class="modal-header" style="background:linear-gradient(135deg,#6366f1,#4f46e5)">
                <div class="modal-title" style="color:#fff">
                    <div style="width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,.2);
                                display:flex;align-items:center;justify-content:center">
                        <i id="pka-modal-icon" class="fas fa-plus" style="color:#fff;font-size:15px"></i>
                    </div>
                    <div>
                        <div id="pka-modal-title" style="font-size:15px;font-weight:700">Tambah Prosedur PKA</div>
                        <div style="font-size:11px;opacity:.75">Program Pengawasan Audit</div>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closePkaModal()"
                        style="color:#fff;background:rgba(255,255,255,.15)">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:18px">
                    <label style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;
                                  letter-spacing:.5px;margin-bottom:6px;display:block">
                        Uraian Prosedur <span style="color:#ef4444">*</span>
                    </label>
                    <textarea id="pka-modal-uraian" name="uraian_prosedur" class="form-control"
                              data-wysiwyg data-wysiwyg-height="130px"
                              placeholder="Tuliskan prosedur audit yang akan dilaksanakan..." required></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;
                                      letter-spacing:.5px;margin-bottom:6px;display:block">Fase</label>
                        <select id="pka-modal-fase" name="fase" class="form-control"
                                style="border-radius:8px;font-size:13px">
                            <option value="persiapan">📋 Persiapan</option>
                            <option value="pelaksanaan">🔍 Pelaksanaan</option>
                            <option value="pelaporan">📄 Pelaporan</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;
                                      letter-spacing:.5px;margin-bottom:6px;display:block">Rencana Waktu (HP)</label>
                        <div style="position:relative">
                            <input type="number" step="0.5" min="0" id="pka-modal-rencana" name="rencana_waktu"
                                   class="form-control" style="border-radius:8px;font-size:13px;padding-right:36px"
                                   placeholder="0">
                            <span style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                                         font-size:11px;color:#94a3b8;pointer-events:none">HP</span>
                        </div>
                    </div>
                </div>

                <!-- Soft warning anggaran KM-2 per fase -->
                <div id="pka-modal-budget-indicator"
                     style="border-radius:8px;padding:10px 14px;font-size:12px;margin-top:16px;
                            background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1;display:none">
                </div>
                <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;
                            padding:10px 14px;font-size:12px;color:#0369a1;margin-top:8px">
                    <i class="fas fa-info-circle"></i>
                    Penugasan Anggota Tim diatur di tab <strong>Penugasan AT</strong>.
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePkaModal()">Batal</button>
                <button type="submit" id="pka-modal-submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Prosedur
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<style>
/* ── Quill output di dalam tabel PKA Tab 1 ──────────────────────────── */
#tab-prosedur table td ol,
#tab-prosedur table td ul {
    margin: 2px 0 0 0;
    padding-left: 18px;
}
#tab-prosedur table td li  { margin: 1px 0; padding: 0; line-height: 1.45; }
#tab-prosedur table td p   { margin: 0; padding: 0; }
#tab-prosedur table td br  { display: none; }
</style>
<script>
const csrfToken    = '<?= csrf_hash() ?>';
const csrfName     = '<?= csrf_token() ?>';
const _sptId       = <?= $spt['id'] ?>;
const _canEdit     = <?= $canEdit ? 'true' : 'false' ?>;
const _atList      = <?= json_encode(array_values($atList)) ?>;
const awBudgetMap  = <?= json_encode($awBudgetMap) ?>;
const _faseLabel   = <?= json_encode($faseLabel) ?>;
const _faseIcon    = <?= json_encode($faseIcon) ?>;
// PKA soft warning data — budget KM-2 per fase & HP PKA terpakai
const _awFaseBudget = <?= json_encode($awFaseBudget) ?>;
const _pkaFaseHp    = <?= json_encode($pkaFaseHp) ?>;   // mutable — update saat add/edit

// Map sdmId → raw uraian — populated on page-load, kept up-to-date on add/edit
const pkaDataMap = <?= json_encode(
    array_combine(
        array_column($pkaList, 'id'),
        array_map(fn($p) => [
            'uraian' => $p['uraian_prosedur'],
            'fase'   => $p['fase'],
            'rencana'=> $p['rencana_waktu'],
        ], $pkaList)
    )
) ?>;

const faseColor = { persiapan: '#6366f1', pelaksanaan: '#0ea5e9', pelaporan: '#22c55e' };

// ── Tab switching ──────────────────────────────────────────────────────
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

// ── Toggle dikerjakan ──────────────────────────────────────────────────
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

// ── Modal: open / close ────────────────────────────────────────────────
function openPkaModal(mode, data) {
    data = data || {};
    $('#pka-modal-mode').val(mode);
    $('#pka-modal-id').val(data.id || '');

    if (mode === 'add') {
        $('#pka-modal-title').text('Tambah Prosedur PKA');
        $('#pka-modal-icon').attr('class', 'fas fa-plus');
        $('#pka-modal-submit').html('<i class="fas fa-plus"></i> Tambah Prosedur');
        $('#pka-modal-fase').val(data.fase || 'pelaksanaan');
        $('#pka-modal-rencana').val('');
        // Clear Quill
        const q = window._quillInstances && window._quillInstances['pka-modal-uraian'];
        if (q) { q.setContents([]); }
        $('#pka-modal-uraian').val('');
    } else {
        $('#pka-modal-title').text('Edit Prosedur PKA');
        $('#pka-modal-icon').attr('class', 'fas fa-pen');
        $('#pka-modal-submit').html('<i class="fas fa-save"></i> Simpan Perubahan');
        $('#pka-modal-fase').val(data.fase || 'pelaksanaan');
        $('#pka-modal-rencana').val(data.rencana || '');
        setTimeout(function() {
            const q = window._quillInstances && window._quillInstances['pka-modal-uraian'];
            const uraian = data.uraian || '';
            if (q) {
                q.root.innerHTML = uraian.match(/^\s*<[a-zA-Z]/) ? uraian
                    : (uraian ? '<p>' + uraian + '</p>' : '');
                $('#pka-modal-uraian').val(uraian);
            } else {
                $('#pka-modal-uraian').val(uraian);
            }
        }, 50);
    }

    const m = document.getElementById('modal-pka');
    m.classList.add('show');
    document.body.style.overflow = 'hidden';
    if (typeof initWysiwyg === 'function') initWysiwyg(m);
}

function closePkaModal() {
    const m = document.getElementById('modal-pka');
    m.classList.remove('show');
    document.body.style.overflow = '';
}

// ── PKA Soft Warning: indikator anggaran KM-2 per fase ────────────────
function updatePkaBudgetIndicator() {
    const fase      = $('#pka-modal-fase').val();
    const hp        = parseFloat($('#pka-modal-rencana').val()) || 0;
    const editId    = $('#pka-modal-id').val();
    const budget    = parseFloat(_awFaseBudget[fase]) || 0;
    const indicator = $('#pka-modal-budget-indicator');

    if (!budget) { indicator.hide(); return; }

    // HP terpakai saat ini (sudah ada di array, dikurangi nilai lama jika edit)
    let used = parseFloat(_pkaFaseHp[fase]) || 0;
    if (editId && pkaDataMap[editId] && pkaDataMap[editId].fase === fase) {
        used -= parseFloat(pkaDataMap[editId].rencana) || 0;  // kurangi nilai lama
    }
    const projected = used + hp;   // proyeksi setelah tambah/edit ini
    const sisa      = budget - projected;
    const pct       = Math.min(100, budget > 0 ? Math.round(projected / budget * 100) : 0);

    let bgColor, borderColor, textColor, icon, statusText;
    if (sisa < 0) {
        bgColor = '#fef2f2'; borderColor = '#fca5a5'; textColor = '#991b1b';
        icon = 'triangle-exclamation';
        statusText = `<strong>Melebihi anggaran ${Math.abs(sisa).toFixed(1)} HP!</strong> Data tetap bisa disimpan, tapi perlu revisi KM-2.`;
    } else if (pct >= 80) {
        bgColor = '#fffbeb'; borderColor = '#fde68a'; textColor = '#92400e';
        icon = 'triangle-exclamation';
        statusText = `Mendekati batas — sisa <strong>${sisa.toFixed(1)} HP</strong> dari anggaran KM-2.`;
    } else {
        bgColor = '#f0fdf4'; borderColor = '#86efac'; textColor = '#166534';
        icon = 'check-circle';
        statusText = `Sisa <strong>${sisa.toFixed(1)} HP</strong> dari anggaran KM-2.`;
    }

    indicator
        .css({ background: bgColor, border: `1px solid ${borderColor}`, color: textColor })
        .html(`<i class="fas fa-${icon}"></i>
               Fase <strong>${_faseLabel[fase]}</strong>:
               <strong>${projected.toFixed(1)}</strong> / ${budget.toFixed(1)} HP KM-2 (${pct}%) —
               ${statusText}`)
        .show();
}

// Trigger saat fase atau HP berubah di modal
$('#pka-modal-fase, #pka-modal-rencana').on('change input', updatePkaBudgetIndicator);

// Tutup modal klik backdrop / Escape
document.getElementById('modal-pka').addEventListener('click', function(e) {
    if (e.target === this) closePkaModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePkaModal();
});

// ── Buka modal: tombol + Tambah per fase ──────────────────────────────
$(document).on('click', '.btn-tambah-pka', function() {
    openPkaModal('add', { fase: $(this).data('fase') });
});

// ── Buka modal: tombol Edit ────────────────────────────────────────────
$(document).on('click', '.btn-edit-pka', function() {
    const id   = $(this).data('id');
    const data = pkaDataMap[id] || {};
    openPkaModal('edit', { id, uraian: data.uraian, fase: data.fase, rencana: data.rencana });
});

// ── Submit modal: Add & Edit ───────────────────────────────────────────
$('#form-pka-modal').on('submit', function(e) {
    e.preventDefault();
    const mode      = $('#pka-modal-mode').val();
    const id        = $('#pka-modal-id').val();
    const submitBtn = $('#pka-modal-submit');
    const origHtml  = submitBtn.html();
    const data      = $(this).serialize() + '&' + csrfName + '=' + csrfToken;

    const url = mode === 'add'
        ? '/admin/spt/' + _sptId + '/pka/store'
        : '/admin/spt/pka/update/' + id;

    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

    $.post(url, data, res => {
        submitBtn.prop('disabled', false).html(origHtml);
        if (!res.success) {
            Swal.fire({ icon: 'error', title: 'Gagal', html: res.message || 'Terjadi kesalahan.', timer: 3000 });
            return;
        }

        if (mode === 'add') {
            appendPkaRow(res);
            appendMatrixRow(res);   // realtime update penugasan matrix
            updateFaseCount(res.fase, 1);
            updateStatCounter(1);
            // Update data map
            pkaDataMap[res.id] = { uraian: res.uraian_raw, fase: res.fase, rencana: res.rencana };
            // Update HP tracker per fase (untuk soft warning berikutnya)
            _pkaFaseHp[res.fase] = (_pkaFaseHp[res.fase] || 0) + (parseFloat(res.rencana) || 0);
            // Update group row badge
            $('#pka-fase-hp-used-' + res.fase).text((_pkaFaseHp[res.fase]).toFixed(1));
            closePkaModal();
            Swal.fire({ icon: 'success', title: 'Ditambahkan!', timer: 1200, showConfirmButton: false });
        } else {
            if (res.fase_changed) {
                // Fase berubah — reload untuk regroup
                location.reload();
                return;
            }
            // Update row in-place
            const tr = $('#pka-row-' + id);
            tr.find('td:nth-child(2)').html(res.uraian_html);
            tr.find('td:nth-child(3)').text(res.rencana ? res.rencana + ' HP' : '—');
            // Update edit button data
            tr.find('.btn-edit-pka').data('rencana', res.rencana || '');
            // Update data map
            pkaDataMap[id] = { uraian: res.uraian_raw, fase: res.fase, rencana: res.rencana };
            closePkaModal();
            Swal.fire({ icon: 'success', title: 'Tersimpan!', timer: 1200, showConfirmButton: false });
        }
    }).fail(() => {
        submitBtn.prop('disabled', false).html(origHtml);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
    });
});

// ── Hapus prosedur ─────────────────────────────────────────────────────
$(document).on('click', '.btn-del-pka', function() {
    const id  = $(this).data('id');
    const fas = $(this).data('fase') || (pkaDataMap[id] && pkaDataMap[id].fase) || '';
    swalConfirm({
        title: 'Hapus Prosedur?',
        html: 'Prosedur ini akan dihapus dan tidak bisa dipulihkan.',
        icon: 'warning',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fas fa-trash"></i>&nbsp;Ya, Hapus',
    }, () => {
        $.post('/admin/spt/pka/delete/' + id, { [csrfName]: csrfToken }, res => {
            if (res.success) {
                $('#pka-row-' + id).fadeOut(200, function() {
                    $(this).remove();
                    updateFaseCount(fas, -1);
                    updateStatCounter(-1);
                    // Show empty row if no more data rows remain in this fase
                    const tbody = $('#pka-tbody-' + fas);
                    if (tbody.find('tr[id^="pka-row-"]').length === 0) {
                        tbody.append(`<tr class="pka-empty-row" id="pka-empty-${fas}">
                            <td colspan="${_canEdit ? 5 : 4}" style="text-align:center;padding:14px;color:#94a3b8;font-size:13px">
                                <i class="fas fa-inbox"></i> Belum ada prosedur.
                                ${_canEdit ? ' Klik <strong>+ Tambah</strong> untuk menambah.' : ''}
                            </td>
                        </tr>`);
                    }
                });
                delete pkaDataMap[id];
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal menghapus prosedur.', timer: 2500 });
            }
        });
    });
});

// ── Append row baru ke matriks penugasan (realtime) ────────────────────
function appendMatrixRow(data) {
    if (!_atList || !_atList.length) return;
    const fase  = data.fase;
    const color = faseColor[fase] || '#6366f1';
    const hp    = data.rencana || 0;
    const hpTxt = data.rencana || '—';

    // Hapus empty-state row jika ada (saat prosedur pertama kali ditambah)
    $('#matrix-empty-row').remove();

    // Konversi Quill HTML ke inline compact text (sama logikanya dengan pka_matrix_text PHP)
    function quillToMatrix(html, max) {
        max = max || 120;
        if (!html) return '';
        // ol → "1. item · 2. item"
        html = html.replace(/<ol[^>]*>([\s\S]*?)<\/ol>/gi, function(_, inner) {
            const items = [];
            let n = 1;
            inner.replace(/<li[^>]*>([\s\S]*?)<\/li>/gi, function(_, c) {
                items.push(n++ + '. ' + c.replace(/<[^>]*>/g, '').trim());
            });
            return items.join(' · ');
        });
        // ul → "• item · • item"
        html = html.replace(/<ul[^>]*>([\s\S]*?)<\/ul>/gi, function(_, inner) {
            const items = [];
            inner.replace(/<li[^>]*>([\s\S]*?)<\/li>/gi, function(_, c) {
                items.push('• ' + c.replace(/<[^>]*>/g, '').trim());
            });
            return items.join(' · ');
        });
        // Strip sisa tag, rapikan spasi
        const text = html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        return text.length > max ? text.slice(0, max) + '…' : text;
    }
    const rawText   = quillToMatrix(data.uraian_raw || '');
    const shortText = rawText; // sudah ditangani di dalam quillToMatrix

    // Buat kolom checkbox per AT
    const tdAt = _atList.map(at => `
        <td style="padding:8px 6px;text-align:center;border:1px solid #e2e8f0">
            ${_canEdit
                ? `<input type="checkbox" class="assign-cb"
                       data-pka="${data.id}" data-sdm="${at.id}" data-hp="${hp}"
                       style="width:16px;height:16px;cursor:pointer">`
                : '<span style="color:#e2e8f0">—</span>'}
        </td>`).join('');

    const tr = `<tr data-matrix-fase="${fase}"
        style="border-bottom:1px solid #f1f5f9"
        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:8px 10px;border:1px solid #e2e8f0">
            <span style="color:${color};font-weight:700;margin-right:6px">${data.nomor}</span>
            <span title="${rawText.replace(/"/g,'&quot;')}">${shortText}</span>
        </td>
        <td style="padding:8px 6px;text-align:center;border:1px solid #e2e8f0;color:#64748b;font-size:11px">
            ${hpTxt}
        </td>
        ${tdAt}
    </tr>`;

    // Cari baris terakhir untuk fase ini
    const existingRows = $(`[data-matrix-fase="${fase}"]`);
    if (existingRows.length) {
        existingRows.last().after(tr);
    } else {
        // Fase belum ada di matrix — buat header row dulu lalu insert data row
        const icon    = _faseIcon[fase]  || 'tasks';
        const label   = _faseLabel[fase] || fase;
        const colSpan = 2 + _atList.length;
        const headerTr = `<tr data-matrix-fase-header="${fase}"
            style="background:${color}15">
            <td colspan="${colSpan}"
                style="padding:5px 10px;font-size:11px;font-weight:700;color:${color};border:1px solid #e2e8f0">
                <i class="fas fa-${icon}"></i> ${label}
            </td>
        </tr>`;

        // Urutan fase: persiapan → pelaksanaan → pelaporan
        const faseOrder = ['persiapan', 'pelaksanaan', 'pelaporan'];
        const myIdx     = faseOrder.indexOf(fase);
        let inserted    = false;

        // Cari fase setelahnya yang sudah ada di matrix
        for (let i = myIdx + 1; i < faseOrder.length; i++) {
            const nextHeader = $(`[data-matrix-fase-header="${faseOrder[i]}"]`);
            if (nextHeader.length) {
                nextHeader.before(headerTr);
                $(`[data-matrix-fase-header="${fase}"]`).after(tr);
                inserted = true;
                break;
            }
        }

        // Kalau tidak ada fase setelahnya, append ke #matrix-tbody
        if (!inserted) {
            $('#matrix-tbody').append(headerTr + tr);
        }
    }
}

// ── Append row baru ke tabel fase ──────────────────────────────────────
function appendPkaRow(data) {
    const fase  = data.fase;
    const color = faseColor[fase] || '#6366f1';
    const hp    = data.rencana ? data.rencana + ' HP' : '—';

    // Hapus empty row jika ada
    $('#pka-empty-' + fase).remove();

    const actionHtml = _canEdit ? `
        <button class="btn btn-xs btn-success btn-selesai" data-id="${data.id}"
                style="font-size:11px;padding:2px 6px" title="Tandai dikerjakan">
            <i class="fas fa-check"></i>
        </button>
        <button class="btn btn-xs btn-primary btn-edit-pka" data-id="${data.id}">
            <i class="fas fa-pen"></i>
        </button>
        <button class="btn btn-xs btn-danger btn-del-pka" data-id="${data.id}" data-fase="${fase}">
            <i class="fas fa-trash"></i>
        </button>` : '';

    const tr = `<tr id="pka-row-${data.id}" style="border-bottom:1px solid #f1f5f9"
        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:9px 10px;font-weight:600;color:${color};text-align:center">${data.nomor}</td>
        <td style="padding:9px 10px;font-size:13px;line-height:1.5">${data.uraian_html}</td>
        <td style="padding:9px 10px;text-align:center;font-size:13px">${hp}</td>
        <td style="padding:9px 10px;text-align:center">
            <span class="badge badge-secondary" id="status-badge-${data.id}">Belum</span>
        </td>
        ${_canEdit ? `<td style="padding:9px 10px;text-align:center">${actionHtml}</td>` : ''}
    </tr>`;

    $('#pka-tbody-' + fase).append(tr);
}

// ── Update counter badge fase ──────────────────────────────────────────
function updateFaseCount(fase, delta) {
    const el = $('#pka-count-' + fase);
    if (!el.length) return;
    const cur = parseInt(el.text()) || 0;
    const nxt = Math.max(0, cur + delta);
    el.text(nxt + ' prosedur');
}

// ── Update stat cards di atas ──────────────────────────────────────────
function updateStatCounter(delta) {
    // Total prosedur (card index 0), belum (card index 2)
    const cards = $('.card .card-body [style*="font-size:28px"]');
    if (cards.length >= 1) {
        const total = parseInt($(cards[0]).text()) || 0;
        $(cards[0]).text(Math.max(0, total + delta));
    }
    if (cards.length >= 3) {
        const belum = parseInt($(cards[2]).text()) || 0;
        $(cards[2]).text(Math.max(0, belum + delta));
    }
    // Update tab badge
    const tabBadge = $('.tab-btn[data-tab="prosedur"] span');
    if (tabBadge.length) {
        const cur = parseInt(tabBadge.text()) || 0;
        tabBadge.text(Math.max(0, cur + delta));
    }
}

// ── Assignment matrix ──────────────────────────────────────────────────
$(document).on('change', '.assign-cb', function() {
    const sdmId = $(this).data('sdm');
    let total = 0;
    $(`.assign-cb[data-sdm="${sdmId}"]:checked`).each(function() {
        total += parseFloat($(this).data('hp')) || 0;
    });
    $('#matrix-hp-' + sdmId).text(total);

    const budget   = awBudgetMap[sdmId] || 0;
    const pct      = budget > 0 ? Math.min(100, Math.round(total / budget * 100)) : (total > 0 ? 100 : 0);
    const sisa     = budget - total;
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

// ── Save all assignments ───────────────────────────────────────────────
$('#btn-save-assignments').on('click', function() {
    const btn  = $(this);
    const data = { [csrfName]: csrfToken };

    $('.assign-cb:checked').each(function() {
        const pkaId = $(this).data('pka');
        const sdmId = $(this).data('sdm');
        const key   = `assignments[${pkaId}][]`;
        if (!data[key]) data[key] = [];
        data[key].push(sdmId);
    });
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
