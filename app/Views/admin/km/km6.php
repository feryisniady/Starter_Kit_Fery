<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-handshake"></i> KM-5b — Entry Meeting</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['irban_nama']) ?></p>
    </div>
    <div class="page-actions">
        <?php if(!empty($row)): ?>
        <a href="/admin/spt/<?= $spt['id'] ?>/km/5b/print" target="_blank" class="btn btn-success">
            <i class="fas fa-print"></i> Cetak BA
        </a>
        <?php endif; ?>
        <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke KM
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if(!$canEdit): ?>
<div class="box-warning mb-3" style="font-size:13px">
    <i class="fas fa-lock"></i> <strong>SPT sedang dalam proses persetujuan.</strong> Data tidak dapat diubah.
</div>
<?php endif; ?>

<?php
$timAuditiSaved = [];
if (!empty($row['tim_auditi_json'])) {
    $timAuditiSaved = json_decode($row['tim_auditi_json'], true) ?: [];
}
if (empty($timAuditiSaved)) {
    $timAuditiSaved = [['nama'=>'','jabatan'=>'']];
}
$peranLabel = ['pj'=>'Penanggung Jawab','wakil_pj'=>'Wakil PJ',
               'dalnis'=>'Pengendali Teknis','kt'=>'Ketua Tim','at'=>'Anggota Tim'];
?>

<form action="/admin/spt/<?= $spt['id'] ?>/km/5b/save" method="POST" id="form-km6">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:flex-start">

        <!-- Kiri -->
        <div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-check"></i> A. Informasi Rapat Koordinasi</h3></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div class="form-group">
                            <label>Hari / Tanggal &amp; Waktu</label>
                            <input type="datetime-local" name="waktu_rapat" class="form-control"
                                   value="<?= old('waktu_rapat', !empty($row['waktu_rapat']) ? date('Y-m-d\TH:i', strtotime($row['waktu_rapat'])) : '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Tempat</label>
                            <input type="text" name="tempat" class="form-control"
                                   value="<?= old('tempat', $row['tempat'] ?? '') ?>"
                                   placeholder="Ruang Rapat / Kantor Dinas...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-clock"></i> B. Waktu Pelaksanaan Audit</h3></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
                        <div class="form-group">
                            <label>Survei Pendahuluan</label>
                            <input type="date" name="waktu_sp" class="form-control"
                                   value="<?= old('waktu_sp', $row['waktu_sp'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Pelaksanaan Audit</label>
                            <input type="text" name="waktu_pelaksanaan" class="form-control"
                                   value="<?= old('waktu_pelaksanaan', $row['waktu_pelaksanaan'] ?? '') ?>"
                                   placeholder="mis: 2 – 20 Juni 2026">
                            <small class="text-muted">Rentang tanggal teks bebas</small>
                        </div>
                        <div class="form-group">
                            <label>Target Penyelesaian Laporan</label>
                            <input type="date" name="rencana_laporan" class="form-control"
                                   value="<?= old('rencana_laporan', $row['rencana_laporan'] ?? $spt['tanggal_selesai'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-building"></i> C. Perwakilan &amp; Tim Auditi</h3></div>
                <div class="card-body">
                    <div class="box-info mb-3" style="font-size:12px">
                        <i class="fas fa-info-circle"></i>
                        <strong>Perwakilan Auditi</strong> adalah pejabat yang menandatangani BA. Tim di bawah adalah daftar hadir anggota auditi.
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div class="form-group">
                            <label>Nama Perwakilan Auditi</label>
                            <input type="text" name="nama_auditi" class="form-control"
                                   value="<?= old('nama_auditi', $row['nama_auditi'] ?? '') ?>"
                                   placeholder="Nama pejabat penandatangan">
                        </div>
                        <div class="form-group">
                            <label>Jabatan</label>
                            <input type="text" name="jabatan_auditi" class="form-control"
                                   value="<?= old('jabatan_auditi', $row['jabatan_auditi'] ?? '') ?>"
                                   placeholder="Kepala Dinas ...">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>NIP Perwakilan Auditi</label>
                        <input type="text" name="nip_auditi" class="form-control"
                               value="<?= old('nip_auditi', $row['nip_auditi'] ?? '') ?>"
                               placeholder="19xxxxxxxxxxxxxxx" style="max-width:260px">
                    </div>

                    <hr style="border-color:#f1f5f9;margin:14px 0">

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <label style="margin:0;font-weight:600;font-size:13px">
                            <i class="fas fa-users"></i> Daftar Hadir Tim Auditi
                        </label>
                        <button type="button" class="btn btn-xs btn-primary" id="btn-add-auditi">
                            <i class="fas fa-plus"></i> Tambah Baris
                        </button>
                    </div>
                    <small class="text-muted d-block mb-2" style="font-size:11px">Anggota tim auditi yang hadir dalam rapat entry meeting.</small>

                    <div id="tim-auditi-container">
                        <?php foreach($timAuditiSaved as $i => $ta): ?>
                        <div class="auditi-row" style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;margin-bottom:6px;align-items:center">
                            <input type="text" name="tim_auditi[<?= $i ?>][nama]"
                                   class="form-control form-control-sm"
                                   value="<?= esc($ta['nama']) ?>" placeholder="Nama">
                            <input type="text" name="tim_auditi[<?= $i ?>][jabatan]"
                                   class="form-control form-control-sm"
                                   value="<?= esc($ta['jabatan']) ?>" placeholder="Jabatan / NIP">
                            <button type="button" class="btn btn-xs btn-danger btn-del-auditi" title="Hapus">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-phone"></i> D. Narahubung &amp; Kesepakatan Tambahan</h3></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div class="form-group">
                            <label>Nama Narahubung (Contact Person)</label>
                            <input type="text" name="cp" class="form-control"
                                   value="<?= old('cp', $row['cp'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="text" name="tlp_cp" class="form-control"
                                   value="<?= old('tlp_cp', $row['tlp_cp'] ?? '') ?>"
                                   placeholder="08xxxxxxxxx">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Poin Kesepakatan Tambahan (akan muncul sebagai Poin 5 dalam BA)</label>
                        <textarea name="poin_5" class="form-control" rows="2"
                                  placeholder="Contoh: Dokumen pendukung diserahkan selambatnya H+3 pelaksanaan audit..."><?= old('poin_5', $row['poin_5'] ?? '') ?></textarea>
                        <small class="text-muted">Kosongkan jika tidak ada.</small>
                    </div>
                    <div class="form-group">
                        <label>Catatan Internal (tidak ditampilkan dalam BA)</label>
                        <textarea name="catatan" class="form-control" rows="2"
                                  placeholder="Catatan tambahan untuk keperluan internal tim..."><?= old('catatan', $row['catatan'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

        </div>

        <!-- Kanan -->
        <div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-signature"></i> Tanda Tangan Auditor</h3></div>
                <div class="card-body">
                    <div class="box-info mb-3" style="font-size:12px">
                        <i class="fas fa-info-circle"></i>
                        Kosongkan jika menggunakan penandatangan SPT secara default.
                    </div>
                    <div class="form-group">
                        <label>Nama Perwakilan Auditor</label>
                        <input type="text" name="nama_auditor_ttd" class="form-control"
                               value="<?= old('nama_auditor_ttd', $row['nama_auditor_ttd'] ?? '') ?>"
                               placeholder="<?= esc($spt['penandatangan_nama'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" name="nip_auditor_ttd" class="form-control"
                               value="<?= old('nip_auditor_ttd', $row['nip_auditor_ttd'] ?? '') ?>"
                               placeholder="<?= esc($spt['penandatangan_nip'] ?? '') ?>">
                    </div>
                    <hr style="border-color:#f1f5f9">
                    <div class="form-group">
                        <label>Kota (baris tanda tangan)</label>
                        <input type="text" name="nama_kota" class="form-control"
                               value="<?= old('nama_kota', $row['nama_kota'] ?? 'Sampang') ?>">
                    </div>
                </div>
            </div>

            <?php if (!empty($spt['tim'])): ?>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-users"></i> Tim Auditor (dari SPT)</h3></div>
                <div class="card-body" style="padding:0">
                    <?php foreach($spt['tim'] as $t): ?>
                    <div style="display:flex;align-items:center;gap:10px;padding:7px 14px;border-bottom:1px solid #f1f5f9">
                        <span style="background:#e0e7ff;color:#3730a3;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;white-space:nowrap;flex-shrink:0">
                            <?= esc($peranLabel[$t['peran_spt']] ?? $t['peran_spt']) ?>
                        </span>
                        <span style="font-size:12px;color:#374151"><?= esc($t['sdm_nama']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div style="display:flex;flex-direction:column;gap:8px">
                <button type="submit" class="btn btn-primary" <?= !$canEdit ? 'disabled' : '' ?>>
                    <i class="fas fa-save"></i> Simpan KM-5b
                </button>
                <?php if(!empty($row)): ?>
                <a href="/admin/spt/<?= $spt['id'] ?>/km/5b/print" target="_blank" class="btn btn-success">
                    <i class="fas fa-print"></i> Cetak Berita Acara
                </a>
                <?php endif; ?>
                <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">
                    Kembali ke KM
                </a>
            </div>
        </div>

    </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
let auditIdx = <?= count($timAuditiSaved) - 1 ?>;

function makeAuditiRow(idx) {
    return `<div class="auditi-row" style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;margin-bottom:6px;align-items:center">
        <input type="text" name="tim_auditi[${idx}][nama]" class="form-control form-control-sm" placeholder="Nama">
        <input type="text" name="tim_auditi[${idx}][jabatan]" class="form-control form-control-sm" placeholder="Jabatan / NIP">
        <button type="button" class="btn btn-xs btn-danger btn-del-auditi" title="Hapus"><i class="fas fa-times"></i></button>
    </div>`;
}

$('#btn-add-auditi').on('click', function() {
    auditIdx++;
    $('#tim-auditi-container').append(makeAuditiRow(auditIdx));
});

$(document).on('click', '.btn-del-auditi', function() {
    $(this).closest('.auditi-row').remove();
});
</script>
<?= $this->endSection() ?>
