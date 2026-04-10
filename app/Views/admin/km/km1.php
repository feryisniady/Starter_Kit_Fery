<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-id-card"></i> KM1 — Kartu Penugasan</h1>
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

<div class="card" style="max-width:720px">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card"></i> Kartu Penugasan (KM1)</h3></div>
    <div class="card-body">
        <form action="/admin/spt/<?= $spt['id'] ?>/km/1/save" method="POST">
            <?= csrf_field() ?>

            <div class="form-row-2">
                <div class="form-group">
                    <label>Nomor Kartu Penugasan</label>
                    <input type="text" name="no_kartu" class="form-control"
                           value="<?= old('no_kartu', $row['no_kartu'] ?? '') ?>"
                           placeholder="KP-001/IRB1/2025">
                </div>
                <div class="form-group">
                    <label>Tujuan / Nama Satker</label>
                    <input type="text" name="tujuan_satker" class="form-control"
                           value="<?= old('tujuan_satker', $row['tujuan_satker'] ?? '') ?>"
                           placeholder="Dinas Pendidikan">
                </div>
            </div>

            <div class="form-group">
                <label>Uraian Kegiatan Pengawasan</label>
                <textarea name="kegiatan" class="form-control" rows="3"
                          placeholder="Audit Ketaatan Pengelolaan Keuangan Daerah..."><?= old('kegiatan', $row['kegiatan'] ?? '') ?></textarea>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>Rencana Mulai</label>
                    <input type="date" name="rencana_mulai" class="form-control"
                           value="<?= old('rencana_mulai', $row['rencana_mulai'] ?? $spt['tanggal_mulai'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Rencana Selesai</label>
                    <input type="date" name="rencana_selesai" class="form-control"
                           value="<?= old('rencana_selesai', $row['rencana_selesai'] ?? $spt['tanggal_selesai'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Rencana Kunjungan ke Lapangan</label>
                <input type="text" name="rencana_kunjungan" class="form-control"
                       value="<?= old('rencana_kunjungan', $row['rencana_kunjungan'] ?? '') ?>"
                       placeholder="Minggu ke-II Januari 2025">
            </div>

            <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" class="form-control" rows="2"><?= old('catatan', $row['catatan'] ?? '') ?></textarea>
            </div>

            <!-- Info Tim dari SPT -->
            <div style="background:#f8fafc;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px">
                <div style="font-weight:600;color:#475569;margin-bottom:8px">
                    <i class="fas fa-users"></i> Tim Pengawas (dari SPT)
                </div>
                <?php foreach($spt['tim'] as $t): ?>
                <div style="padding:4px 0;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between">
                    <span><?= esc($t['sdm_nama']) ?></span>
                    <span class="badge badge-primary"><?= esc($t['peran_spt']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="form-actions">
                <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan KM1</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
