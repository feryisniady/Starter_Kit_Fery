<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-calendar-days"></i> KM-2 — Anggaran Waktu</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['irban_nama']) ?></p>
    </div>
    <div class="page-actions">
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
        <div style="background:#eff6ff;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#1d4ed8">
            <i class="fas fa-info-circle"></i>
            <?php if($isKtDal): ?>
            Isi rencana jadwal dan hari untuk setiap anggota tim. Kolom <strong>Realisasi Hari</strong> diisi setelah Entry Meeting (KM-5b) selesai.
            <?php else: ?>
            Isi rencana jadwal dan jumlah hari untuk setiap fase penugasan Anda.
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

        <form action="/admin/spt/<?= $spt['id'] ?>/km/2/save" method="POST">
            <?= csrf_field() ?>

            <?php foreach($displayList as $t): ?>
            <?php $aw = $awMap[$t['sdm_id']] ?? null; ?>
            <input type="hidden" name="sdm_id[]" value="<?= $t['sdm_id'] ?>">

            <div class="card mb-3" style="border-left:4px solid #6366f1">
                <div class="card-body">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap">
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
                    </div>

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
                        <form action="/admin/spt/<?= $spt['id'] ?>/km/2/verifikasi" method="POST" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="sdm_id" value="<?= $t['sdm_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-success"
                                    onclick="return confirm('Verifikasi realisasi anggaran waktu <?= esc($t['sdm_nama']) ?>?')">
                                <i class="fas fa-check-double"></i> Verifikasi Realisasi
                            </button>
                        </form>
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

        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
