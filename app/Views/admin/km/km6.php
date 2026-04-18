<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-handshake"></i> KM-5b — Entry Meeting</h1>
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
<?php if(!$canEdit): ?>
<div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#92400e">
    <i class="fas fa-lock"></i> <strong>SPT sedang dalam proses persetujuan.</strong> Data tidak dapat diubah.
</div>
<?php endif; ?>

<div class="card" style="max-width:720px">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-handshake"></i> Notulensi Kesepakatan (KM6)</h3></div>
    <div class="card-body">
        <form action="/admin/spt/<?= $spt['id'] ?>/km/6/save" method="POST">
            <?= csrf_field() ?>

            <div style="background:#eff6ff;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#1d4ed8">
                <i class="fas fa-info-circle"></i>
                KM6 mencatat hasil notulensi rapat koordinasi dan kesepakatan dengan pihak auditi sebelum pelaksanaan pengawasan dimulai.
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>Waktu Rapat Koordinasi</label>
                    <input type="datetime-local" name="waktu_rapat" class="form-control"
                           value="<?= old('waktu_rapat', $row ? date('Y-m-d\TH:i', strtotime($row['waktu_rapat'] ?? '')) : '') ?>">
                </div>
                <div class="form-group">
                    <label>Waktu Survey Pendahuluan</label>
                    <input type="date" name="waktu_sp" class="form-control"
                           value="<?= old('waktu_sp', $row['waktu_sp'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Target Penyelesaian Laporan</label>
                <input type="date" name="rencana_laporan" class="form-control"
                       value="<?= old('rencana_laporan', $row['rencana_laporan'] ?? $spt['tanggal_selesai'] ?? '') ?>">
            </div>

            <hr style="border-color:#f1f5f9;margin:16px 0">
            <div style="font-weight:600;font-size:13px;color:#475569;margin-bottom:12px">
                <i class="fas fa-user-tie"></i> Data Auditi (Pihak yang Diaudit)
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>Nama Auditi</label>
                    <input type="text" name="nama_auditi" class="form-control"
                           value="<?= old('nama_auditi', $row['nama_auditi'] ?? '') ?>"
                           placeholder="Nama pejabat/kepala satker">
                </div>
                <div class="form-group">
                    <label>Jabatan Auditi</label>
                    <input type="text" name="jabatan_auditi" class="form-control"
                           value="<?= old('jabatan_auditi', $row['jabatan_auditi'] ?? '') ?>"
                           placeholder="Kepala Dinas...">
                </div>
            </div>

            <div class="form-group">
                <label>NIP Auditi</label>
                <input type="text" name="nip_auditi" class="form-control"
                       value="<?= old('nip_auditi', $row['nip_auditi'] ?? '') ?>"
                       placeholder="19xxxxxxxxxxxxxxx">
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>Nama Narahubung (Contact Person)</label>
                    <input type="text" name="cp" class="form-control"
                           value="<?= old('cp', $row['cp'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Telepon Narahubung</label>
                    <input type="text" name="tlp_cp" class="form-control"
                           value="<?= old('tlp_cp', $row['tlp_cp'] ?? '') ?>"
                           placeholder="08xxxxxxxxx">
                </div>
            </div>

            <div class="form-group">
                <label>Catatan Tambahan</label>
                <textarea name="catatan" class="form-control" rows="2"><?= old('catatan', $row['catatan'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" <?= !$canEdit ? 'disabled' : '' ?>><i class="fas fa-save"></i> Simpan KM6</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
