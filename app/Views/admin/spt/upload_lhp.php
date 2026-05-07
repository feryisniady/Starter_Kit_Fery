<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Upload LHP</h1>
        <p><?= esc($spt['nomor_naskah'] ?: '#' . $spt['id']) ?> — <?= esc($spt['tujuan']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3">
    <i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<?php if (!empty($slotInfo)): ?>
<?php
$terpakai = $slotInfo['terpakai'];
$maks     = $slotInfo['maks'];
$persen   = $slotInfo['persen'];
$barClass = $persen >= 100 ? 'danger' : ($persen >= 66 ? 'warning' : 'success');
?>
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:13px">
            <span class="fw-semibold text-secondary">Slot SPT Aktif (belum ada LHP)</span>
            <span class="fw-bold text-<?= $barClass ?>">
                <?= $terpakai ?> / <?= $maks ?> terpakai
            </span>
        </div>
        <div class="progress" style="height:8px;border-radius:6px">
            <div class="progress-bar bg-<?= $barClass ?>" role="progressbar"
                 style="width:<?= $persen ?>%"
                 aria-valuenow="<?= $persen ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <?php if (!empty($slotInfo['list_antrian'])): ?>
        <div class="mt-2" style="font-size:12px;color:#64748b">
            <strong>Urutan antrian LHP:</strong>
            <?php foreach ($slotInfo['list_antrian'] as $i => $item): ?>
                <span class="badge badge-<?= $i === 0 ? 'danger' : 'secondary' ?> ms-1">
                    <?= ($i + 1) ?>. <?= esc($item['nomor_naskah'] ?: '#' . $item['id']) ?>
                    <?= $i === 0 ? ' ← harus selesai duluan' : '' ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-file-upload text-success me-2"></i>
            Form Upload Laporan Hasil Pengawasan (LHP)
        </h5>
    </div>
    <div class="card-body">

        <!-- Info SPT -->
        <div class="alert alert-info mb-4" style="font-size:13px">
            <div class="fw-bold mb-1"><i class="fas fa-info-circle me-1"></i> Informasi SPT</div>
            <div>Nomor SPT &nbsp;: <strong><?= esc($spt['nomor_naskah'] ?: '—') ?></strong></div>
            <div>Kegiatan &nbsp;&nbsp;: <?= esc($spt['kode_kegiatan'] ?? $spt['jenis_non_pkpt'] ?? '—') ?></div>
            <div>Tujuan &nbsp;&nbsp;&nbsp;&nbsp;: <?= esc($spt['tujuan'] ?? '—') ?></div>
            <?php if (!empty($spt['tanggal_selesai'])): ?>
            <div>Selesai &nbsp;&nbsp;&nbsp;: <?= tgl_indo($spt['tanggal_selesai']) ?></div>
            <?php endif; ?>
        </div>

        <form action="/admin/spt/<?= $spt['id'] ?>/lhp/store" method="post"
              enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nomor LHP</label>
                    <input type="text" name="nomor_lhp" class="form-control"
                           value="<?= old('nomor_lhp') ?>"
                           placeholder="Contoh: 700/XX/LHP/434.201/2026"
                           maxlength="100" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Tanggal LHP</label>
                    <input type="date" name="tanggal_lhp" class="form-control"
                           value="<?= old('tanggal_lhp') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label required">File LHP</label>
                <input type="file" name="file_lhp" class="form-control"
                       accept=".pdf,.doc,.docx" required>
                <div class="form-text">Format: PDF, DOC, atau DOCX. Maks 10 MB.</div>
            </div>

            <div class="mb-4">
                <label class="form-label">Keterangan (opsional)</label>
                <textarea name="keterangan" class="form-control" rows="3"
                          placeholder="Catatan atau keterangan tambahan..."><?= old('keterangan') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-upload me-1"></i> Upload LHP
                </button>
                <a href="/admin/spt/<?= $spt['id'] ?>" class="btn btn-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
