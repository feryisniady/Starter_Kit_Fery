<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-file-circle-check"></i> KM-11 — Reviu Laporan</h1>
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
        <h3 class="card-title"><i class="fas fa-file-circle-check"></i> Lembar Reviu Laporan Hasil Pengawasan (KM-11)</h3>
    </div>
    <div class="card-body">
        <div style="background:#faf5ff;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#7c3aed">
            <i class="fas fa-info-circle"></i>
            KM-11 adalah lembar reviu draft LHP oleh Pengendali Teknis sebelum laporan final diterbitkan.
            Reviu memastikan kualitas, akurasi fakta, dan ketepatan rekomendasi.
        </div>

        <form action="/admin/spt/<?= $spt['id'] ?>/km/11/save" method="POST">
            <?= csrf_field() ?>

            <div class="form-row-2">
                <div class="form-group">
                    <label>Tanggal Reviu</label>
                    <input type="date" name="tanggal_reviu" class="form-control"
                           value="<?= old('tanggal_reviu', $row['tanggal_reviu'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="form-group">
                    <label>Hasil Reviu Laporan</label>
                    <select name="status" class="form-control" id="statusReviu11">
                        <option value="layak" <?= ($row['status'] ?? 'layak') === 'layak' ? 'selected' : '' ?>>
                            ✅ Layak Terbit
                        </option>
                        <option value="revisi" <?= ($row['status'] ?? '') === 'revisi' ? 'selected' : '' ?>>
                            🔄 Perlu Revisi
                        </option>
                    </select>
                </div>
            </div>

            <!-- Checklist Kualitas Laporan -->
            <div style="background:#f8fafc;border-radius:8px;padding:16px;margin-bottom:16px">
                <div style="font-weight:600;font-size:13px;color:#475569;margin-bottom:12px">
                    <i class="fas fa-list-check"></i> Checklist Kualitas Laporan
                </div>
                <div style="display:grid;gap:10px">
                    <?php
                    $cekItems = [
                        'cek_sistematika' => 'Sistematika laporan sudah sesuai format standar LHP',
                        'cek_fakta'       => 'Fakta, data, dan informasi sudah akurat dan terdukung bukti',
                        'cek_rekomendasi' => 'Rekomendasi sudah spesifik, terukur, dan dapat ditindaklanjuti',
                        'cek_bahasa'      => 'Bahasa laporan sudah baku, lugas, dan tidak ambigu',
                        'cek_lampiran'    => 'Lampiran dan kertas kerja sudah lengkap dan relevan',
                    ];
                    foreach ($cekItems as $name => $label):
                        $checked = ($row[$name] ?? 0) ? 'checked' : '';
                    ?>
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:8px 10px;border-radius:6px;background:#fff;border:1px solid #e2e8f0">
                        <input type="checkbox" name="<?= $name ?>" value="1" <?= $checked ?>
                               style="margin-top:2px;width:16px;height:16px;flex-shrink:0">
                        <span style="font-size:13px;color:#374151"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Catatan Reviu</label>
                <textarea name="catatan_reviu" class="form-control" rows="3"
                          placeholder="Catatan umum hasil reviu laporan..."><?= old('catatan_reviu', $row['catatan_reviu'] ?? '') ?></textarea>
            </div>

            <div class="form-group" id="saranBlock11" style="<?= ($row['status'] ?? '') !== 'revisi' ? 'display:none' : '' ?>">
                <label>Saran Perbaikan <span style="color:#ef4444">*</span></label>
                <textarea name="saran_perbaikan" class="form-control" rows="3"
                          placeholder="Tuliskan bagian laporan yang perlu direvisi dan saran perbaikannya..."><?= old('saran_perbaikan', $row['saran_perbaikan'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan KM-11
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('statusReviu11').addEventListener('change', function(){
    document.getElementById('saranBlock11').style.display =
        this.value === 'revisi' ? '' : 'none';
});
</script>

<?= $this->endSection() ?>
