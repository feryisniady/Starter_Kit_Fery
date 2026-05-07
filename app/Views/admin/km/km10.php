<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-handshake-angle"></i> KM-10 — Exit Meeting</h1>
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

<div class="card" style="max-width:760px">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-handshake-angle"></i> Notulensi Exit Meeting (KM-10)</h3>
    </div>
    <div class="card-body">
        <div class="box-success">
            <i class="fas fa-info-circle"></i>
            KM-10 mencatat hasil Exit Meeting dengan pihak auditi di akhir penugasan — penyampaian temuan dan kesepakatan tindak lanjut.
        </div>

        <form action="/admin/spt/<?= $spt['id'] ?>/km/10/save" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Waktu Exit Meeting</label>
                <input type="datetime-local" name="waktu_meeting" class="form-control"
                       value="<?= old('waktu_meeting', $row ? date('Y-m-d\TH:i', strtotime($row['waktu_meeting'] ?? '')) : '') ?>">
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

            <hr style="border-color:#f1f5f9;margin:16px 0">
            <div style="font-weight:600;font-size:13px;color:#475569;margin-bottom:12px">
                <i class="fas fa-file-alt"></i> Hasil & Kesepakatan
            </div>

            <div class="form-group">
                <label>Hasil Exit Meeting</label>
                <textarea name="hasil_meeting" class="form-control" rows="4"
                          data-wysiwyg data-wysiwyg-height="120px"
                          placeholder="Uraikan hasil rapat, temuan pokok yang disampaikan, dan tanggapan auditi..."><?= old('hasil_meeting', $row['hasil_meeting'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Kesepakatan Tindak Lanjut</label>
                <textarea name="kesepakatan" class="form-control" rows="3"
                          data-wysiwyg data-wysiwyg-height="100px"
                          placeholder="Kesepakatan dan komitmen tindak lanjut dari pihak auditi..."><?= old('kesepakatan', $row['kesepakatan'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Catatan Tambahan</label>
                <textarea name="catatan" class="form-control" rows="2"
                          data-wysiwyg data-wysiwyg-height="80px"
                          placeholder="Catatan tambahan..."><?= old('catatan', $row['catatan'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan KM-10</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
