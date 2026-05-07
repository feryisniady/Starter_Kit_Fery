<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-calendar-days"></i> KM-2 — Anggaran Waktu</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['irban_nama']) ?></p>
    </div>
    <div class="page-actions">
        <?php if($isKtDal): ?>
        <form action="/admin/spt/<?= $spt['id'] ?>/km/2/sync-from-kka" method="POST" style="display:inline"
              onsubmit="return confirm('Sinkronkan realisasi AW dari data KKA seluruh AT?\n(Nilai realisasi akan dihitung ulang dari KKA yang sudah diisi)')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-success">
                <i class="fas fa-rotate"></i> Sinkronkan dari KKA
            </button>
        </form>
        <?php endif; ?>
        <a href="/admin/spt/<?= $spt['id'] ?>/km/2/print" target="_blank" class="btn btn-outline-primary">
            <i class="fas fa-print"></i> Cetak Formulir KM-4
        </a>
        <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke KM
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if(!$canEdit): ?>
<div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#92400e">
    <i class="fas fa-lock"></i> <strong>SPT sedang dalam proses persetujuan.</strong> Data tidak dapat diubah.
</div>
<?php endif; ?>

<?php
$displayList = $isKtDal ? $timList : array_filter($timList, fn($t) => (int)$t['sdm_id'] === $mySdmId);
if (!$isKtDal && empty($displayList)) {
    foreach ($awMap as $aw) {
        if ((int)$aw['sdm_id'] === $mySdmId) {
            $displayList[] = ['sdm_id' => $aw['sdm_id'], 'sdm_nama' => $aw['sdm_nama'], 'peran_spt' => $aw['peran_spt'] ?? ''];
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-days"></i>
            <?= $isKtDal ? 'Anggaran Waktu Seluruh Tim' : 'Anggaran Waktu Saya' ?>
        </h3>
    </div>
    <div class="card-body">
        <?php if(!$canEdit): ?>
        <div style="background:#fef3c7;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#92400e">
            <i class="fas fa-lock"></i> Anda hanya dapat melihat data — tidak dapat mengubah.
        </div>
        <?php else: ?>
        <div class="box-info">
            <i class="fas fa-info-circle"></i>
            <?php if($isKtDal): ?>
            Isi rencana jadwal dan hari untuk setiap anggota tim. Kolom <strong>Realisasi Hari</strong> otomatis terisi dari KKA saat AT menyimpan prosedur, atau gunakan tombol <strong>Sinkronkan dari KKA</strong>.
            <?php else: ?>
            Isi rencana jadwal dan jumlah hari untuk setiap fase penugasan Anda. <strong>Realisasi HP akan otomatis terisi</strong> dari data yang Anda isi di KKA (Realisasi Waktu per prosedur).
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if($isKtDal && $canEdit): ?>
            <?php if($km10Ada): ?>
            <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#991b1b">
                <i class="fas fa-lock"></i> <strong>Realisasi terkunci.</strong> Exit Meeting (KM-10) telah selesai — kolom realisasi tidak dapat diubah.
            </div>
            <?php elseif(!$km5bAda): ?>
            <div style="background:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#854d0e">
                <i class="fas fa-hourglass-half"></i> <strong>Realisasi belum dapat diisi.</strong> Isi Entry Meeting (KM-5b) terlebih dahulu untuk membuka kolom realisasi.
            </div>
            <?php else: ?>
            <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#166534">
                <i class="fas fa-unlock"></i> <strong>Realisasi dapat diisi.</strong> Entry Meeting telah selesai. Isi realisasi hari setiap fase.
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(empty($displayList)): ?>
        <div style="text-align:center;padding:32px;color:#94a3b8">
            <i class="fas fa-users" style="font-size:32px;margin-bottom:8px"></i>
            <p><?= $isKtDal ? 'Belum ada tim yang ditambahkan pada SPT ini.' : 'Anda belum terdaftar dalam tim SPT ini.' ?></p>
        </div>
        <?php else: ?>

        <!-- Global over-budget warning banner — ditampilkan via JS -->
        <div id="aw-global-warning" style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:16px;font-size:13px;color:#991b1b;animation:aw-shake .4s">
            <div style="display:flex;align-items:flex-start;gap:10px">
                <i class="fas fa-circle-exclamation" style="font-size:18px;margin-top:1px;flex-shrink:0;color:#dc2626"></i>
                <div>
                    <strong style="font-size:14px">Rencana HP Melebihi Anggaran PKPT!</strong>
                    <div id="aw-global-warning-text" style="margin-top:4px;line-height:1.5"></div>
                </div>
            </div>
        </div>

        <form id="form-aw" action="/admin/spt/<?= $spt['id'] ?>/km/2/save" method="POST">
            <?= csrf_field() ?>

            <?php
$pkptTimMap = $pkptTimMap ?? [];
foreach($displayList as $t):
    $aw         = $awMap[$t['sdm_id']] ?? null;
    $pkptHp     = isset($pkptTimMap[$t['sdm_id']]) ? (float)$pkptTimMap[$t['sdm_id']]['hp_total'] : null;
    $awRencana  = (float)($aw['persiapan_rencana_hari'] ?? 0)
                + (float)($aw['pelaksanaan_rencana_hari'] ?? 0)
                + (float)($aw['penyelesaian_rencana_hari'] ?? 0);
?>
            <input type="hidden" name="sdm_id[]" value="<?= $t['sdm_id'] ?>">

            <div class="card mb-3" style="border-left:4px solid #6366f1">
                <div class="card-body">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap">
                        <div style="width:36px;height:36px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#6366f1;flex-shrink:0">
                            <?= strtoupper(substr($t['sdm_nama'], 0, 1)) ?>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;font-size:13px"><?= esc($t['sdm_nama']) ?></div>
                            <div style="font-size:11px;color:#64748b"><?= esc($t['peran_spt']) ?></div>
                        </div>
                        <?php if($aw && !empty($aw['kt_verified'])): ?>
                        <span class="badge badge-success" style="font-size:11px">
                            <i class="fas fa-check-double"></i> Terverifikasi KT
                        </span>
                        <?php elseif($aw): ?>
                        <span class="badge badge-info" style="font-size:11px">
                            <i class="fas fa-check"></i> Sudah diisi
                        </span>
                        <?php endif; ?>
                        <?php if($canEdit && $pkptHp !== null && $pkptHp > 0): ?>
                        <button type="button"
                                class="btn btn-xs btn-outline-secondary btn-rot-prefill"
                                data-sdm="<?= $t['sdm_id'] ?>"
                                data-hp="<?= $pkptHp ?>"
                                title="Prefill rencana hari menggunakan pola <?= $rotPola ?> dari anggaran PKPT">
                            <i class="fas fa-wand-magic-sparkles"></i>
                            Prefill <?= $rotPola ?>
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php if($pkptHp !== null): ?>
                    <?php
                    $pct   = $pkptHp > 0 ? min(100, round($awRencana / $pkptHp * 100)) : ($awRencana > 0 ? 100 : 0);
                    $sisa  = $pkptHp - $awRencana;
                    $barColor   = $sisa < 0 ? '#ef4444' : ($pct >= 80 ? '#f59e0b' : '#22c55e');
                    $statusHtml = $sisa < 0
                        ? '<span style="color:#ef4444;font-weight:700"><i class="fas fa-triangle-exclamation"></i> Melebihi '.abs($sisa).' HP!</span>'
                        : ($pct >= 80
                            ? '<span style="color:#d97706;font-weight:600"><i class="fas fa-triangle-exclamation"></i> Mendekati batas</span>'
                            : '<span style="color:#16a34a;font-weight:600"><i class="fas fa-check"></i> Sisa '.$sisa.' HP</span>');
                    ?>
                    <div class="aw-hp-widget" data-sdm="<?= $t['sdm_id'] ?>" data-budget="<?= $pkptHp ?>" data-nama="<?= esc($t['sdm_nama']) ?>"
                         style="display:flex;align-items:center;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:7px 12px;margin-bottom:12px;flex-wrap:wrap">
                        <span style="font-size:11px;color:#64748b;white-space:nowrap">
                            <i class="fas fa-bullseye" style="color:#6366f1"></i> Budget PKPT:
                            <strong style="color:#1e293b"><?= $pkptHp ?> HP</strong>
                        </span>
                        <span style="color:#cbd5e1">|</span>
                        <span style="font-size:11px;color:#64748b;white-space:nowrap">
                            Rencana KM-2:
                            <strong class="aw-rencana-val" style="color:#1e293b"><?= $awRencana ?> HP</strong>
                        </span>
                        <div style="flex:1;min-width:80px;max-width:160px;height:7px;background:#e2e8f0;border-radius:4px;overflow:hidden">
                            <div class="aw-bar-fill" style="height:100%;width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:4px;transition:width .25s,background .25s"></div>
                        </div>
                        <span class="aw-hp-status" style="font-size:11px;white-space:nowrap"><?= $statusHtml ?></span>
                    </div>
                    <!-- Inline alert — hidden by default, shown via JS when over budget -->
                    <div class="aw-hp-alert" data-sdm="<?= $t['sdm_id'] ?>" style="display:none;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px;line-height:1.5"></div>
                    <?php endif; ?>


                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">

                        <!-- Persiapan -->
                        <div style="background:#f8fafc;border-radius:8px;padding:12px">
                            <div style="font-size:11px;font-weight:600;color:#6366f1;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">
                                <i class="fas fa-search"></i> Persiapan
                            </div>
                            <div class="form-group" style="margin-bottom:6px">
                                <label style="font-size:11px;color:#64748b">Mulai</label>
                                <input type="date" name="persiapan_start_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['persiapan_start'] ?? $spt['tanggal_mulai'] ?? '' ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <div class="form-group" style="margin-bottom:6px">
                                <label style="font-size:11px;color:#64748b">Selesai</label>
                                <input type="date" name="persiapan_end_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['persiapan_end'] ?? '' ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <div class="form-group" style="margin-bottom:6px">
                                <label style="font-size:11px;color:#0ea5e9">Rencana Hari</label>
                                <input type="number" name="persiapan_rencana_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       step="0.5" min="0" placeholder="0"
                                       value="<?= $aw['persiapan_rencana_hari'] ?? '' ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <?php if($isKtDal): ?>
                            <div class="form-group" style="margin-bottom:0">
                                <label style="font-size:11px;color:#22c55e">Realisasi Hari</label>
                                <input type="number" name="persiapan_realisasi_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       step="0.5" min="0" placeholder="0"
                                       value="<?= $aw['persiapan_realisasi_hari'] ?? '' ?>"
                                       <?= !$canEditRealisasi ? 'disabled' : '' ?>>
                            </div>
                            <?php elseif($aw && $aw['persiapan_realisasi_hari'] !== null): ?>
                            <div style="font-size:11px;color:#22c55e;margin-top:4px">
                                <i class="fas fa-check"></i> Realisasi: <strong><?= $aw['persiapan_realisasi_hari'] ?></strong> hari
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pelaksanaan -->
                        <div style="background:#f8fafc;border-radius:8px;padding:12px">
                            <div style="font-size:11px;font-weight:600;color:#0ea5e9;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">
                                <i class="fas fa-tasks"></i> Pelaksanaan
                            </div>
                            <div class="form-group" style="margin-bottom:6px">
                                <label style="font-size:11px;color:#64748b">Mulai</label>
                                <input type="date" name="pelaksanaan_start_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['pelaksanaan_start'] ?? '' ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <div class="form-group" style="margin-bottom:6px">
                                <label style="font-size:11px;color:#64748b">Selesai</label>
                                <input type="date" name="pelaksanaan_end_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['pelaksanaan_end'] ?? '' ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <div class="form-group" style="margin-bottom:6px">
                                <label style="font-size:11px;color:#0ea5e9">Rencana Hari</label>
                                <input type="number" name="pelaksanaan_rencana_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       step="0.5" min="0" placeholder="0"
                                       value="<?= $aw['pelaksanaan_rencana_hari'] ?? '' ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <?php if($isKtDal): ?>
                            <div class="form-group" style="margin-bottom:0">
                                <label style="font-size:11px;color:#22c55e">Realisasi Hari</label>
                                <input type="number" name="pelaksanaan_realisasi_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       step="0.5" min="0" placeholder="0"
                                       value="<?= $aw['pelaksanaan_realisasi_hari'] ?? '' ?>"
                                       <?= !$canEditRealisasi ? 'disabled' : '' ?>>
                            </div>
                            <?php elseif($aw && $aw['pelaksanaan_realisasi_hari'] !== null): ?>
                            <div style="font-size:11px;color:#22c55e;margin-top:4px">
                                <i class="fas fa-check"></i> Realisasi: <strong><?= $aw['pelaksanaan_realisasi_hari'] ?></strong> hari
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Penyelesaian -->
                        <div style="background:#f8fafc;border-radius:8px;padding:12px">
                            <div style="font-size:11px;font-weight:600;color:#22c55e;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">
                                <i class="fas fa-file-alt"></i> Penyelesaian
                            </div>
                            <div class="form-group" style="margin-bottom:6px">
                                <label style="font-size:11px;color:#64748b">Mulai</label>
                                <input type="date" name="penyelesaian_start_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['penyelesaian_start'] ?? '' ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <div class="form-group" style="margin-bottom:6px">
                                <label style="font-size:11px;color:#64748b">Selesai</label>
                                <input type="date" name="penyelesaian_end_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['penyelesaian_end'] ?? $spt['tanggal_selesai'] ?? '' ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <div class="form-group" style="margin-bottom:6px">
                                <label style="font-size:11px;color:#0ea5e9">Rencana Hari</label>
                                <input type="number" name="penyelesaian_rencana_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       step="0.5" min="0" placeholder="0"
                                       value="<?= $aw['penyelesaian_rencana_hari'] ?? '' ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <?php if($isKtDal): ?>
                            <div class="form-group" style="margin-bottom:0">
                                <label style="font-size:11px;color:#22c55e">Realisasi Hari</label>
                                <input type="number" name="penyelesaian_realisasi_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       step="0.5" min="0" placeholder="0"
                                       value="<?= $aw['penyelesaian_realisasi_hari'] ?? '' ?>"
                                       <?= !$canEditRealisasi ? 'disabled' : '' ?>>
                            </div>
                            <?php elseif($aw && $aw['penyelesaian_realisasi_hari'] !== null): ?>
                            <div style="font-size:11px;color:#22c55e;margin-top:4px">
                                <i class="fas fa-check"></i> Realisasi: <strong><?= $aw['penyelesaian_realisasi_hari'] ?></strong> hari
                            </div>
                            <?php endif; ?>
                        </div>

                    </div><!-- end grid -->

                    <?php if($isKtDal && $aw && $canEditRealisasi && empty($aw['kt_verified'])): ?>
                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end">
                        <button type="button" class="btn btn-sm btn-success btn-verifikasi-aw"
                                data-sdm-id="<?= $t['sdm_id'] ?>"
                                data-sdm-nama="<?= esc($t['sdm_nama']) ?>"
                                onclick="verifikasiAw(<?= $t['sdm_id'] ?>, '<?= esc($t['sdm_nama']) ?>')">
                            <i class="fas fa-check-double"></i> Verifikasi Realisasi
                        </button>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>

            <?php if($canEdit): ?>
            <div class="form-actions">
                <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Anggaran Waktu
                </button>
            </div>
            <?php endif; ?>
        </form>

        <?php /* ── Silent Role section — PJ / WPJ ── */ ?>
        <?php if (!empty($timSilent ?? [])): ?>
        <div style="margin-top:20px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
                <span style="font-size:12px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:0.5px">
                    <i class="fas fa-shield-halved"></i> Pimpinan Tim — Silent Role
                </span>
                <span style="font-weight:400;color:#94a3b8;font-size:11px">(nilai simbolik · otomatis oleh sistem)</span>
            </div>

            <?php foreach (($timSilent ?? []) as $ts):
                $awS = $awMap[$ts['sdm_id']] ?? null;
            ?>
            <div class="card mb-2" style="border-left:4px solid #7c3aed">
                <div class="card-body" style="padding:14px 16px">
                    <!-- Header baris -->
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                        <div style="width:34px;height:34px;border-radius:50%;background:#f3e8ff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#7c3aed;flex-shrink:0">
                            <?= strtoupper(substr($ts['sdm_nama'], 0, 1)) ?>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;font-size:13px"><?= esc($ts['sdm_nama']) ?></div>
                            <div style="font-size:11px;color:#64748b"><?= esc($ts['peran_spt']) ?></div>
                        </div>
                        <span style="background:#f3e8ff;color:#7c3aed;border:1px solid #d8b4fe;border-radius:20px;padding:3px 11px;font-size:11px;font-weight:600;white-space:nowrap">
                            <i class="fas fa-shield-halved"></i> Supervisi — 1 HP
                        </span>
                        <span style="background:#f0fdf4;color:#15803d;border:1px solid #86efac;border-radius:20px;padding:3px 10px;font-size:11px;white-space:nowrap">
                            <i class="fas fa-bolt"></i> Otomatis
                        </span>
                    </div>

                    <!-- Info box -->
                    <div style="margin-top:10px;background:#faf5ff;border:1px dashed #c4b5fd;border-radius:8px;padding:10px 14px;font-size:12px;color:#5b21b6;line-height:1.6">
                        <i class="fas fa-circle-info"></i>
                        Peran <strong><?= esc($ts['peran_spt']) ?></strong> bertugas sebagai pimpinan supervisi.
                        Nilai HP simbolik <strong>1 HP</strong> pada fase pelaksanaan di-set otomatis oleh sistem saat KT/Dalnis menyimpan formulir ini — tidak diperlukan pengisian manual.
                        <?php if ($awS && ($awS['pelaksanaan_rencana_hari'] ?? 0) > 0): ?>
                        <br><span style="color:#6d28d9;margin-top:4px;display:inline-block">
                            <i class="fas fa-database"></i> Tersimpan:
                            Persiapan <strong><?= (float)($awS['persiapan_rencana_hari'] ?? 0) ?></strong> ·
                            Pelaksanaan <strong><?= (float)($awS['pelaksanaan_rencana_hari'] ?? 0) ?></strong> ·
                            Penyelesaian <strong><?= (float)($awS['penyelesaian_rencana_hari'] ?? 0) ?></strong> HP
                        </span>
                        <?php else: ?>
                        <br><span style="color:#9ca3af;margin-top:4px;display:inline-block">
                            <i class="fas fa-clock"></i> Belum tersimpan. Nilai akan otomatis disisipkan saat KT/Dalnis menyimpan formulir ini.
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Readonly grid (visual only) -->
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:12px">
                        <?php
                        $silentCols = [
                            ['label'=>'Persiapan',   'icon'=>'fas fa-search',   'color'=>'#6366f1', 'val'=>(float)($awS['persiapan_rencana_hari']   ?? 0)],
                            ['label'=>'Pelaksanaan', 'icon'=>'fas fa-tasks',    'color'=>'#0ea5e9', 'val'=>(float)($awS['pelaksanaan_rencana_hari']  ?? 1)],
                            ['label'=>'Penyelesaian','icon'=>'fas fa-file-alt', 'color'=>'#22c55e', 'val'=>(float)($awS['penyelesaian_rencana_hari'] ?? 0)],
                        ];
                        foreach ($silentCols as $sc): ?>
                        <div style="background:#f8f4ff;border:1px solid #e9d5ff;border-radius:8px;padding:10px 12px">
                            <div style="font-size:10px;font-weight:600;color:<?= $sc['color'] ?>;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">
                                <i class="<?= $sc['icon'] ?>"></i> <?= $sc['label'] ?>
                            </div>
                            <div style="font-size:12px;color:#64748b;margin-bottom:4px">Rencana Hari</div>
                            <div style="font-size:20px;font-weight:700;color:#7c3aed"><?= $sc['val'] ?></div>
                            <div style="font-size:10px;color:#a78bfa;margin-top:2px"><i class="fas fa-lock"></i> readonly</div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<style>
@keyframes aw-shake {
    0%,100%{transform:translateX(0)}
    20%{transform:translateX(-5px)}
    40%{transform:translateX(5px)}
    60%{transform:translateX(-4px)}
    80%{transform:translateX(4px)}
}
.aw-input-over {
    border-color: #ef4444 !important;
    background: #fff5f5 !important;
    box-shadow: 0 0 0 3px rgba(239,68,68,.15) !important;
}
.aw-input-warn {
    border-color: #f59e0b !important;
    background: #fffbeb !important;
    box-shadow: 0 0 0 3px rgba(245,158,11,.15) !important;
}
.aw-card-over {
    border-left-color: #ef4444 !important;
    box-shadow: 0 0 0 2px rgba(239,68,68,.12) !important;
}
.aw-card-warn {
    border-left-color: #f59e0b !important;
}
</style>
<script>
const _sptId    = <?= $spt['id'] ?>;
const _csrfName = '<?= csrf_token() ?>';
const _csrfHash = '<?= csrf_hash() ?>';

// ── Rule of Thumb prefill ─────────────────────────────────────────────
// Pola dipilih otomatis dari jenis_pengawasan SPT
const _rotPola = '<?= $rotPola ?? '15-70-15' ?>';

// Bulatkan ke 0.5 terdekat
function roundHalf(n) {
    return Math.round(n * 2) / 2;
}

// Hitung distribusi HP dengan formula yang bebas rounding error
// Pelaksanaan = total − Persiapan − Penyelesaian (absorb sisa)
function calcRot(totalHp, pola) {
    const [p1, , p3] = pola.split('-').map(Number);   // e.g. [15, 70, 15]
    const prep   = roundHalf(totalHp * p1 / 100);
    const penye  = roundHalf(totalHp * p3 / 100);
    const pelaks = Math.round((totalHp - prep - penye) * 10) / 10;  // sisa = bebas rounding error
    return { prep, pelaks, penye };
}

$(document).on('click', '.btn-rot-prefill', function() {
    const sdmId  = $(this).data('sdm');
    const hp     = parseFloat($(this).data('hp')) || 0;
    if (!hp) return;

    const { prep, pelaks, penye } = calcRot(hp, _rotPola);

    // Periksa apakah sudah ada data
    const hasFilled = parseFloat($(`input[name="persiapan_rencana_${sdmId}"]`).val()) > 0
                   || parseFloat($(`input[name="pelaksanaan_rencana_${sdmId}"]`).val()) > 0
                   || parseFloat($(`input[name="penyelesaian_rencana_${sdmId}"]`).val()) > 0;

    const doFill = () => {
        $(`input[name="persiapan_rencana_${sdmId}"]`).val(prep).addClass('aw-auto-filled');
        $(`input[name="pelaksanaan_rencana_${sdmId}"]`).val(pelaks).addClass('aw-auto-filled');
        $(`input[name="penyelesaian_rencana_${sdmId}"]`).val(penye).addClass('aw-auto-filled');
        updateAwHpWidget(sdmId);
        // Flash border hijau
        ['persiapan','pelaksanaan','penyelesaian'].forEach(f => {
            const inp = $(`input[name="${f}_rencana_${sdmId}"]`);
            inp.css('border-color', '#22c55e');
            setTimeout(() => inp.css('border-color', ''), 2000);
        });
        Swal.fire({
            icon: 'success',
            title: `Pola ${_rotPola} diterapkan`,
            html: `<table style="margin:0 auto;font-size:13px;line-height:2">
                     <tr><td style="text-align:right;padding-right:8px;color:#64748b">Persiapan</td>
                         <td><strong>${prep} HP</strong></td></tr>
                     <tr><td style="text-align:right;padding-right:8px;color:#64748b">Pelaksanaan</td>
                         <td><strong>${pelaks} HP</strong></td></tr>
                     <tr><td style="text-align:right;padding-right:8px;color:#64748b">Penyelesaian</td>
                         <td><strong>${penye} HP</strong></td></tr>
                     <tr><td style="text-align:right;padding-right:8px;color:#6366f1;font-weight:600">Total</td>
                         <td><strong style="color:#6366f1">${hp} HP</strong></td></tr>
                   </table>
                   <p style="font-size:11px;color:#94a3b8;margin-top:8px">Anda masih bisa mengubah nilai ini secara manual.</p>`,
            timer: 4000,
            showConfirmButton: false,
        });
    };

    if (hasFilled) {
        Swal.fire({
            icon: 'question',
            title: 'Timpa Nilai yang Ada?',
            html: `Rencana hari akan di-prefill menggunakan pola <strong>${_rotPola}</strong>:<br>
                   <b>${prep}</b> / <b>${pelaks}</b> / <b>${penye}</b> HP<br>
                   <span style="font-size:11px;color:#94a3b8">(Persiapan / Pelaksanaan / Penyelesaian)</span>`,
            showCancelButton: true,
            confirmButtonText: 'Ya, Timpa',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#6366f1',
        }).then(r => { if (r.isConfirmed) doFill(); });
    } else {
        doFill();
    }
});

// Hari libur nasional + cuti bersama dari DB (format YYYY-MM-DD)
const HOLIDAYS = new Set(<?= json_encode($hariLibur ?? []) ?>);

function countWorkingDays(startStr, endStr) {
    if (!startStr || !endStr) return 0;
    const start = new Date(startStr + 'T00:00:00');
    const end   = new Date(endStr   + 'T00:00:00');
    if (end < start) return 0;
    let count = 0;
    const cur = new Date(start);
    while (cur <= end) {
        const dow = cur.getDay();
        const ymd = cur.toISOString().slice(0, 10);
        if (dow !== 0 && dow !== 6 && !HOLIDAYS.has(ymd)) count++;
        cur.setDate(cur.getDate() + 1);
    }
    return count;
}

function verifikasiAw(sdmId, sdmNama) {
    swalConfirm({
        title: 'Verifikasi Realisasi?',
        html: 'Verifikasi anggaran waktu <b>' + sdmNama + '</b>.<br><span style="color:#ef4444;font-size:12px">Setelah diverifikasi, data tidak bisa diubah kembali.</span>',
        icon: 'question',
        confirmButtonText: '<i class="fas fa-check-double"></i>&nbsp;Ya, Verifikasi',
    }, () => {
        const btn = document.querySelector(`.btn-verifikasi-aw[data-sdm-id="${sdmId}"]`);
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...'; }

        const body = new URLSearchParams();
        body.append('sdm_id', sdmId);
        body.append(_csrfName, _csrfHash);

        fetch(`/admin/spt/${_sptId}/km/2/verifikasi`, { method: 'POST', body })
            .then(r => r.redirected ? r.url : r.text())
            .then(() => { location.reload(); })
            .catch(() => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-double"></i> Verifikasi Realisasi'; }
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghubungi server. Coba lagi.', timer: 3000 });
            });
    });
}

// State global: sdmId → { total, budget, nama }
const awBudgetState = {};

function updateAwHpWidget(sdmId) {
    const getVal = (name) => parseFloat($(`input[name="${name}"]`).val()) || 0;
    const persiapan    = getVal(`persiapan_rencana_${sdmId}`);
    const pelaksanaan  = getVal(`pelaksanaan_rencana_${sdmId}`);
    const penyelesaian = getVal(`penyelesaian_rencana_${sdmId}`);
    const total = Math.round((persiapan + pelaksanaan + penyelesaian) * 10) / 10;

    const widget = $(`.aw-hp-widget[data-sdm="${sdmId}"]`);
    const alert  = $(`.aw-hp-alert[data-sdm="${sdmId}"]`);
    const card   = widget.closest('.card.mb-3');

    if (!widget.length) return;

    const budget = parseFloat(widget.data('budget')) || 0;
    const pct    = budget > 0 ? Math.min(100, Math.round(total / budget * 100)) : (total > 0 ? 100 : 0);
    const sisa   = Math.round((budget - total) * 10) / 10;

    // Simpan state untuk validasi global
    const nama = widget.data('nama') || '';
    awBudgetState[sdmId] = { total, budget, sisa, nama };

    // --- Update progress bar ---
    widget.find('.aw-rencana-val').text(total + ' HP');
    widget.find('.aw-bar-fill').css('width', pct + '%');

    // --- Highlight input fields ---
    const rencanaInputs = [
        $(`input[name="persiapan_rencana_${sdmId}"]`),
        $(`input[name="pelaksanaan_rencana_${sdmId}"]`),
        $(`input[name="penyelesaian_rencana_${sdmId}"]`)
    ];

    let barColor, statusHtml;

    if (sisa < 0) {
        // Over budget
        barColor   = '#ef4444';
        statusHtml = `<span style="color:#ef4444;font-weight:700"><i class="fas fa-triangle-exclamation"></i> Melebihi ${Math.abs(sisa)} HP!</span>`;

        card.addClass('aw-card-over').removeClass('aw-card-warn');
        rencanaInputs.forEach(inp => { if (parseFloat(inp.val()) > 0) inp.addClass('aw-input-over').removeClass('aw-input-warn'); });

        if (alert.length) {
            alert.css({ display:'block', background:'#fef2f2', border:'1px solid #fca5a5', color:'#991b1b' });
            alert.html(
                `<div style="display:flex;align-items:flex-start;gap:8px">
                    <i class="fas fa-circle-exclamation" style="color:#dc2626;font-size:16px;margin-top:1px;flex-shrink:0"></i>
                    <div>
                        <strong>Rencana melebihi anggaran PKPT sebesar ${Math.abs(sisa)} HP!</strong>
                        <div style="margin-top:4px;font-size:11px">
                            Budget PKPT: <b>${budget} HP</b>&nbsp;&middot;&nbsp;Total rencana: <b>${total} HP</b>
                            &nbsp;&middot;&nbsp;Selisih: <b style="color:#dc2626">+${Math.abs(sisa)} HP</b>
                        </div>
                        <div style="margin-top:6px;font-size:11px;color:#b91c1c">
                            <i class="fas fa-lightbulb"></i> Kurangi hari rencana pada salah satu fase, atau minta revisi anggaran PKPT ke KT/Dalnis.
                        </div>
                    </div>
                </div>`
            );
        }
    } else if (pct >= 80) {
        // Mendekati batas
        barColor   = '#f59e0b';
        statusHtml = `<span style="color:#d97706;font-weight:600"><i class="fas fa-triangle-exclamation"></i> Mendekati batas (${pct}%)</span>`;

        card.addClass('aw-card-warn').removeClass('aw-card-over');
        rencanaInputs.forEach(inp => inp.removeClass('aw-input-over').addClass('aw-input-warn'));

        if (alert.length) {
            alert.css({ display:'block', background:'#fffbeb', border:'1px solid #fcd34d', color:'#92400e' });
            alert.html(
                `<div style="display:flex;align-items:flex-start;gap:8px">
                    <i class="fas fa-triangle-exclamation" style="color:#d97706;font-size:15px;margin-top:1px;flex-shrink:0"></i>
                    <div>
                        <strong>Mendekati batas anggaran PKPT (${pct}% terpakai)</strong>
                        <div style="margin-top:4px;font-size:11px">
                            Budget PKPT: <b>${budget} HP</b>&nbsp;&middot;&nbsp;Sisa: <b>${sisa} HP</b>
                        </div>
                    </div>
                </div>`
            );
        }
    } else {
        // Normal / OK
        barColor   = '#22c55e';
        statusHtml = `<span style="color:#16a34a;font-weight:600"><i class="fas fa-check"></i> Sisa ${sisa} HP</span>`;

        card.removeClass('aw-card-over aw-card-warn');
        rencanaInputs.forEach(inp => inp.removeClass('aw-input-over aw-input-warn'));

        if (alert.length) alert.hide();
    }

    widget.find('.aw-bar-fill').css('background', barColor);
    widget.find('.aw-hp-status').html(statusHtml);

    // Update global warning banner
    updateGlobalWarning();
}

function updateGlobalWarning() {
    const overList = Object.values(awBudgetState).filter(s => s.budget > 0 && s.sisa < 0);
    const banner   = $('#aw-global-warning');
    const text     = $('#aw-global-warning-text');

    if (overList.length === 0) {
        banner.hide();
        return;
    }

    const lines = overList.map(s =>
        `<span style="display:inline-flex;align-items:center;gap:4px;margin-right:12px">
            <i class="fas fa-user" style="font-size:10px"></i>
            <b>${s.nama || 'SDM'}</b>: rencana <b>${s.total} HP</b> dari budget <b>${s.budget} HP</b>
            (lebih <b style="color:#dc2626">${Math.abs(s.sisa)} HP</b>)
         </span>`
    ).join('');

    text.html(
        `${overList.length} anggota tim melebihi anggaran PKPT:<br>
         <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px">${lines}</div>
         <div style="margin-top:8px;font-size:12px;font-weight:400">
             <i class="fas fa-info-circle"></i> Data tetap bisa disimpan, namun akan ditandai sebagai melebihi anggaran.
         </div>`
    );

    banner.css('animation', 'none');
    setTimeout(() => banner.css({ display:'block', animation:'aw-shake .4s' }), 10);
}

$(function() {
    // Live update saat nilai rencana diubah manual
    $(document).on('input change', 'input[name^="persiapan_rencana_"], input[name^="pelaksanaan_rencana_"], input[name^="penyelesaian_rencana_"]', function() {
        const sdmId = $(this).attr('name').split('_').pop();
        updateAwHpWidget(sdmId);
    });

    // Auto-hitung rencana hari saat tanggal mulai/selesai dipilih
    $(document).on('change', 'input[type="date"][name^="persiapan_"], input[type="date"][name^="pelaksanaan_"], input[type="date"][name^="penyelesaian_"]', function() {
        const name = $(this).attr('name');
        if (!name.includes('_start_') && !name.includes('_end_')) return;

        const isStart  = name.includes('_start_');
        const sdmId    = name.split(isStart ? '_start_' : '_end_').pop();
        const fase     = name.split(isStart ? '_start_' : '_end_')[0];
        const startVal = $(`input[name="${fase}_start_${sdmId}"]`).val();
        const endVal   = $(`input[name="${fase}_end_${sdmId}"]`).val();
        if (!startVal || !endVal) return;

        const days         = countWorkingDays(startVal, endVal);
        const rencanaInput = $(`input[name="${fase}_rencana_${sdmId}"]`);
        rencanaInput.val(days).addClass('aw-auto-filled');
        updateAwHpWidget(sdmId);

        rencanaInput.css('border-color', '#22c55e');
        setTimeout(() => rencanaInput.css('border-color', ''), 2000);
    });

    // Guard form submit: konfirmasi SweetAlert jika ada yang over budget
    $('#form-aw').on('submit', function(e) {
        $('.aw-hp-widget').each(function() {
            const sdmId = $(this).data('sdm');
            if (sdmId) updateAwHpWidget(sdmId);
        });

        const overList = Object.values(awBudgetState).filter(s => s.budget > 0 && s.sisa < 0);
        if (overList.length === 0) return true;

        e.preventDefault();
        const form = this;

        const names = overList.map(s =>
            `<li><b>${s.nama || 'SDM'}</b>: melebihi <b style="color:#dc2626">${Math.abs(s.sisa)} HP</b></li>`
        ).join('');

        Swal.fire({
            icon: 'warning',
            title: 'Rencana Melebihi Anggaran!',
            html: `<div style="text-align:left;font-size:13px">
                       <p>Beberapa anggota tim memiliki rencana HP yang melebihi anggaran PKPT:</p>
                       <ul style="margin:8px 0 12px 16px;padding:0;line-height:1.8">${names}</ul>
                       <p style="color:#64748b;font-size:12px">Data tetap dapat disimpan, namun pastikan sudah dikomunikasikan dengan KT/Dalnis.</p>
                   </div>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i>&nbsp; Tetap Simpan',
            cancelButtonText: '<i class="fas fa-edit"></i>&nbsp; Perbaiki Dulu',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6366f1',
            reverseButtons: true,
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    });

    // Inisialisasi: cek state awal semua SDM di halaman saat load
    $('.aw-hp-widget').each(function() {
        const sdmId = $(this).data('sdm');
        if (sdmId) updateAwHpWidget(sdmId);
    });
});
</script>
<?= $this->endSection() ?>
