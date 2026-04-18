<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-user-shield"></i> Pernyataan Independensi & Integritas</h1>
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
// AT: hanya lihat baris miliknya; KT/Dalnis/Admin: semua dari timList
$displayList = $isKtDal ? $timList : array_filter($timList, fn($t) => (int)$t['sdm_id'] === $mySdmId);
if (!$isKtDal && empty($displayList) && isset($indMap[$mySdmId])) {
    $ind = $indMap[$mySdmId];
    $displayList[] = ['sdm_id' => $ind['sdm_id'], 'sdm_nama' => $ind['sdm_nama'], 'peran_spt' => $ind['peran_spt'] ?? ''];
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-shield"></i>
            <?= $isKtDal ? 'Independensi Seluruh Tim' : 'Pernyataan Independensi Saya' ?>
        </h3>
    </div>
    <div class="card-body">
        <?php if(!$canEdit): ?>
        <div style="background:#fef3c7;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#92400e">
            <i class="fas fa-lock"></i> Anda hanya dapat melihat data — tidak dapat mengubah.
        </div>
        <?php else: ?>
        <div style="background:#fef9c3;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#854d0e">
            <i class="fas fa-triangle-exclamation"></i>
            <strong>Penting:</strong> Jika terdapat salah satu kondisi yang berlaku, auditor yang bersangkutan
            dinyatakan <strong>tidak independen</strong> dan harus dipertimbangkan untuk diganti atau dikeluarkan dari tim.
        </div>
        <?php endif; ?>

        <?php if(empty($displayList)): ?>
        <div style="text-align:center;padding:32px;color:#94a3b8">
            <i class="fas fa-users" style="font-size:32px;margin-bottom:8px"></i>
            <p><?= $isKtDal ? 'Belum ada tim yang ditambahkan pada SPT ini.' : 'Anda belum terdaftar dalam tim SPT ini.' ?></p>
        </div>
        <?php else: ?>

        <form action="/admin/spt/<?= $spt['id'] ?>/km/independensi/save" method="POST">
            <?= csrf_field() ?>

            <?php foreach($displayList as $t): ?>
            <?php $ind = $indMap[$t['sdm_id']] ?? null; ?>
            <input type="hidden" name="sdm_id[]" value="<?= $t['sdm_id'] ?>">

            <div class="card mb-3" style="border-left:4px solid <?= ($ind && !$ind['is_independent']) ? '#ef4444' : '#6366f1' ?>">
                <div class="card-body">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap">
                        <div style="width:36px;height:36px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#6366f1;flex-shrink:0">
                            <?= strtoupper(substr($t['sdm_nama'], 0, 1)) ?>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;font-size:13px"><?= esc($t['sdm_nama']) ?></div>
                            <div style="font-size:11px;color:#64748b"><?= esc($t['peran_spt']) ?></div>
                        </div>
                        <?php if($ind): ?>
                        <span class="badge badge-<?= $ind['is_independent'] ? 'success' : 'danger' ?>" style="font-size:11px">
                            <?= $ind['is_independent']
                                ? '<i class="fas fa-check"></i> Independen'
                                : '<i class="fas fa-times"></i> Tidak Independen' ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <div style="font-size:12px;color:#475569;margin-bottom:10px;font-weight:600">
                        Apakah auditor ini memiliki kondisi berikut? (centang jika ADA)
                    </div>

                    <label style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;font-size:12px;cursor:pointer;border-bottom:1px solid #f1f5f9">
                        <input type="checkbox" name="hubungan_keluarga_<?= $t['sdm_id'] ?>" value="1" style="margin-top:2px;flex-shrink:0"
                               <?= !empty($ind['ada_hubungan_keluarga']) ? 'checked' : '' ?>
                               <?= !$canEdit ? 'disabled' : '' ?>>
                        <span>
                            <strong>Hubungan keluarga</strong> dengan pimpinan atau pejabat kunci pada obyek yang diawasi
                            (suami/istri, orang tua, anak, saudara kandung, dll.)
                        </span>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;font-size:12px;cursor:pointer;border-bottom:1px solid #f1f5f9">
                        <input type="checkbox" name="kepentingan_finansial_<?= $t['sdm_id'] ?>" value="1" style="margin-top:2px;flex-shrink:0"
                               <?= !empty($ind['ada_kepentingan_finansial']) ? 'checked' : '' ?>
                               <?= !$canEdit ? 'disabled' : '' ?>>
                        <span>
                            <strong>Kepentingan finansial / keuangan</strong> pada obyek yang diawasi
                            (investasi, utang-piutang, bisnis, dll.)
                        </span>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;font-size:12px;cursor:pointer">
                        <input type="checkbox" name="hubungan_sebelumnya_<?= $t['sdm_id'] ?>" value="1" style="margin-top:2px;flex-shrink:0"
                               <?= !empty($ind['ada_hubungan_sebelumnya']) ? 'checked' : '' ?>
                               <?= !$canEdit ? 'disabled' : '' ?>>
                        <span>
                            <strong>Pernah bekerja / menjabat</strong> pada obyek yang diawasi dalam 2 tahun terakhir
                        </span>
                    </label>

                    <div class="form-group" style="margin-top:10px;margin-bottom:0">
                        <label style="font-size:12px">Catatan (jika ada)</label>
                        <input type="text" name="catatan_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                               value="<?= esc($ind['catatan'] ?? '') ?>"
                               placeholder="Keterangan tambahan jika diperlukan"
                               <?= !$canEdit ? 'disabled' : '' ?>>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if($canEdit): ?>
            <div class="form-actions">
                <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pernyataan Independensi
                </button>
            </div>
            <?php endif; ?>
        </form>

        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
