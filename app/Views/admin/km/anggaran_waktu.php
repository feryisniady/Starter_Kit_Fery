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
                    <div class="aw-hp-widget" data-sdm="<?= $t['sdm_id'] ?>" data-budget="<?= $pkptHp ?>"
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

        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const _sptId    = <?= $spt['id'] ?>;
const _csrfName = '<?= csrf_token() ?>';
const _csrfHash = '<?= csrf_hash() ?>';

function verifikasiAw(sdmId, sdmNama) {
    if (!confirm('Verifikasi realisasi anggaran waktu ' + sdmNama + '?\n\nSetelah diverifikasi, data realisasi SDM ini tidak bisa diubah kembali.')) return;

    const btn = document.querySelector(`.btn-verifikasi-aw[data-sdm-id="${sdmId}"]`);
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...'; }

    const body = new URLSearchParams();
    body.append('sdm_id', sdmId);
    body.append(_csrfName, _csrfHash);

    fetch(`/admin/spt/${_sptId}/km/2/verifikasi`, { method: 'POST', body })
        .then(r => r.redirected ? r.url : r.text())
        .then(() => {
            // Refresh halaman agar badge "Terverifikasi KT" muncul
            location.reload();
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-double"></i> Verifikasi Realisasi'; }
            alert('Gagal menghubungi server. Coba lagi.');
        });
}

function updateAwHpWidget(sdmId) {
    const get = (name) => parseFloat($(`input[name="${name}"]`).val()) || 0;
    const total = get(`persiapan_rencana_${sdmId}`)
                + get(`pelaksanaan_rencana_${sdmId}`)
                + get(`penyelesaian_rencana_${sdmId}`);

    const widget = $(`.aw-hp-widget[data-sdm="${sdmId}"]`);
    if (!widget.length) return;

    const budget = parseFloat(widget.data('budget')) || 0;
    const pct    = budget > 0 ? Math.min(100, Math.round(total / budget * 100)) : (total > 0 ? 100 : 0);
    const sisa   = Math.round((budget - total) * 10) / 10;

    widget.find('.aw-rencana-val').text(total + ' HP');
    widget.find('.aw-bar-fill').css('width', pct + '%');

    let color, html;
    if (sisa < 0) {
        color = '#ef4444';
        html  = `<span style="color:#ef4444;font-weight:700"><i class="fas fa-triangle-exclamation"></i> Melebihi ${Math.abs(sisa)} HP!</span>`;
    } else if (pct >= 80) {
        color = '#f59e0b';
        html  = `<span style="color:#d97706;font-weight:600"><i class="fas fa-triangle-exclamation"></i> Mendekati batas</span>`;
    } else {
        color = '#22c55e';
        html  = `<span style="color:#16a34a;font-weight:600"><i class="fas fa-check"></i> Sisa ${sisa} HP</span>`;
    }
    widget.find('.aw-bar-fill').css('background', color);
    widget.find('.aw-hp-status').html(html);
}

$(function() {
    // Live update saat nilai rencana diubah
    $(document).on('input change', 'input[name^="persiapan_rencana_"], input[name^="pelaksanaan_rencana_"], input[name^="penyelesaian_rencana_"]', function() {
        const name  = $(this).attr('name');
        const sdmId = name.split('_').pop();
        updateAwHpWidget(sdmId);
    });
});
</script>
<?= $this->endSection() ?>
