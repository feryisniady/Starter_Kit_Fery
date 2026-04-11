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

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-days"></i> Anggaran Waktu Per Anggota Tim</h3>
    </div>
    <div class="card-body">
        <div style="background:#eff6ff;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#1d4ed8">
            <i class="fas fa-info-circle"></i>
            Isi rencana jadwal untuk setiap anggota tim pada tiga fase: Persiapan, Pelaksanaan, dan Penyelesaian.
        </div>

        <form action="/admin/spt/<?= $spt['id'] ?>/km/anggaran-waktu/save" method="POST">
            <?= csrf_field() ?>

            <?php foreach($spt['tim'] as $t): ?>
            <?php $aw = $awMap[$t['sdm_id']] ?? null; ?>
            <input type="hidden" name="sdm_id[]" value="<?= $t['sdm_id'] ?>">

            <div class="card mb-3" style="border-left:4px solid #6366f1">
                <div class="card-body">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                        <div style="width:36px;height:36px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#6366f1">
                            <?= strtoupper(substr($t['sdm_nama'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:13px"><?= esc($t['sdm_nama']) ?></div>
                            <div style="font-size:11px;color:#64748b"><?= esc($t['peran_spt']) ?></div>
                        </div>
                        <?php if($aw): ?>
                        <span class="badge badge-success" style="margin-left:auto;font-size:11px">
                            <i class="fas fa-check"></i> Sudah diisi
                        </span>
                        <?php endif; ?>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">

                        <!-- Persiapan -->
                        <div style="background:#f8fafc;border-radius:8px;padding:12px">
                            <div style="font-size:11px;font-weight:600;color:#6366f1;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">
                                <i class="fas fa-search"></i> Persiapan
                            </div>
                            <div class="form-group" style="margin-bottom:8px">
                                <label style="font-size:11px;color:#64748b">Mulai</label>
                                <input type="date" name="persiapan_start_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['persiapan_start'] ?? $spt['tanggal_mulai'] ?? '' ?>">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label style="font-size:11px;color:#64748b">Selesai</label>
                                <input type="date" name="persiapan_end_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['persiapan_end'] ?? '' ?>">
                            </div>
                        </div>

                        <!-- Pelaksanaan -->
                        <div style="background:#f8fafc;border-radius:8px;padding:12px">
                            <div style="font-size:11px;font-weight:600;color:#0ea5e9;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">
                                <i class="fas fa-tasks"></i> Pelaksanaan
                            </div>
                            <div class="form-group" style="margin-bottom:8px">
                                <label style="font-size:11px;color:#64748b">Mulai</label>
                                <input type="date" name="pelaksanaan_start_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['pelaksanaan_start'] ?? '' ?>">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label style="font-size:11px;color:#64748b">Selesai</label>
                                <input type="date" name="pelaksanaan_end_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['pelaksanaan_end'] ?? '' ?>">
                            </div>
                        </div>

                        <!-- Penyelesaian -->
                        <div style="background:#f8fafc;border-radius:8px;padding:12px">
                            <div style="font-size:11px;font-weight:600;color:#22c55e;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">
                                <i class="fas fa-file-alt"></i> Penyelesaian
                            </div>
                            <div class="form-group" style="margin-bottom:8px">
                                <label style="font-size:11px;color:#64748b">Mulai</label>
                                <input type="date" name="penyelesaian_start_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['penyelesaian_start'] ?? '' ?>">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label style="font-size:11px;color:#64748b">Selesai</label>
                                <input type="date" name="penyelesaian_end_<?= $t['sdm_id'] ?>" class="form-control form-control-sm"
                                       value="<?= $aw['penyelesaian_end'] ?? $spt['tanggal_selesai'] ?? '' ?>">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if(empty($spt['tim'])): ?>
            <div style="text-align:center;padding:32px;color:#94a3b8">
                <i class="fas fa-users" style="font-size:32px;margin-bottom:8px"></i>
                <p>Belum ada tim yang ditambahkan pada SPT ini.</p>
            </div>
            <?php else: ?>
            <div class="form-actions">
                <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Anggaran Waktu
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
