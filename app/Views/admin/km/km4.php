<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-clipboard-list"></i> KM4 — Lembar Perencanaan Pengawasan</h1>
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

<?php
$cekFields = [
    'cek_kp'            => 'Sudahkah dibuat Kartu Penugasan (KM1)?',
    'cek_jenis_tujuan'  => 'Sudahkah ditetapkan jenis, tujuan, sasaran penugasan serta penaksiran risiko segmen kegiatan?',
    'cek_misi_tujuan'   => 'Apakah misi, tujuan dan rencana obyek pengawasan sudah diperoleh?',
    'cek_informasi'     => 'Apakah informasi organisasi obyek pengawasan sudah diperoleh?',
    'cek_lhp_terakhir'  => 'Apakah LHP terakhir (intern) sudah diperoleh?',
    'cek_lhp_ekstern'   => 'Apakah LHP auditor ekstern sudah diperoleh?',
    'cek_perundangan'   => 'Apakah ketentuan perundangan terkait obyek pengawasan sudah diperoleh?',
    'cek_kertas_kerja'  => 'Apakah sudah dibuat kertas kerja perencanaan penugasan pengawasan?',
    'cek_tao_fao'       => 'Apakah identifikasi risiko organisasi sebagai TAO dan fokus audit sudah dilakukan?',
    'cek_program'       => 'Apakah Program Kerja Pengawasan sudah disusun?',
    'cek_anggaran_waktu'=> 'Apakah Anggaran Waktu Penugasan sudah dibuat?',
];
$doneCount = 0;
if ($row) {
    foreach (array_keys($cekFields) as $f) {
        if (!empty($row[$f])) $doneCount++;
    }
}
?>

<form action="/admin/spt/<?= $spt['id'] ?>/km/4/save" method="POST">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:flex-start">

        <!-- Kiri: form isian -->
        <div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Informasi Penugasan</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Dasar Penugasan</label>
                        <textarea name="dasar_penugasan" class="form-control" rows="2"
                              data-wysiwyg data-wysiwyg-height="80px"
                              placeholder="Dasar penugasan pengawasan..."><?= old('dasar_penugasan', $row['dasar_penugasan'] ?? $spt['dasar_1'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Jenis Penugasan</label>
                        <input type="text" name="jenis_penugasan" class="form-control"
                               value="<?= old('jenis_penugasan', $row['jenis_penugasan'] ?? $spt['jenis_pengawasan'] ?? '') ?>"
                               list="list-jenis">
                        <datalist id="list-jenis">
                            <option>Audit Ketaatan</option>
                            <option>Audit Kinerja</option>
                            <option>Audit Keuangan</option>
                            <option>Reviu</option>
                            <option>Evaluasi</option>
                            <option>Monitoring</option>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Tujuan Pengawasan</label>
                        <textarea name="tujuan_pengawasan" class="form-control" rows="3"
                              data-wysiwyg data-wysiwyg-height="90px"
                              placeholder="Tujuan pengawasan..."><?= old('tujuan_pengawasan', $row['tujuan_pengawasan'] ?? $spt['tujuan_sasaran'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Sasaran</label>
                        <textarea name="sasaran" class="form-control" rows="2"
                              data-wysiwyg data-wysiwyg-height="80px"
                              placeholder="Sasaran pengawasan..."><?= old('sasaran', $row['sasaran'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Catatan Pengendali Teknis</label>
                        <textarea name="catatan_dalnis" class="form-control" rows="2"
                              data-wysiwyg data-wysiwyg-height="80px"
                              placeholder="Catatan pengendali teknis..."><?= old('catatan_dalnis', $row['catatan_dalnis'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100" <?= !$canEdit ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Simpan KM4
                    </button>
                </div>
            </div>
        </div>

        <!-- Kanan: checklist -->
        <div class="card">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                <h3 class="card-title" style="margin:0"><i class="fas fa-check-square"></i> Checklist Perencanaan</h3>
                <span class="badge badge-<?= $doneCount === 11 ? 'success' : 'warning' ?>">
                    <?= $doneCount ?>/11
                </span>
            </div>
            <div class="card-body" style="padding:12px">
                <?php foreach($cekFields as $field => $label): ?>
                <label style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;font-size:12px;cursor:pointer;border-bottom:1px solid #f1f5f9">
                    <input type="checkbox" name="<?= $field ?>" value="1" style="margin-top:2px;flex-shrink:0"
                           <?= !empty($row[$field]) ? 'checked' : '' ?>>
                    <span><?= esc($label) ?></span>
                </label>
                <?php endforeach; ?>
                <div style="margin-top:10px;font-size:11px;color:#94a3b8">
                    <i class="fas fa-info-circle"></i> Semua item harus diceklis untuk KM4 dinyatakan lengkap.
                </div>
            </div>
        </div>

    </div>
</form>

<?= $this->endSection() ?>
