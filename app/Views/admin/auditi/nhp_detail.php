<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-envelope-open-text"></i> <?= esc($nhp['nomor_nhp'] ?: 'NHP #'.$nhp['id']) ?></h1>
        <p><?= esc($entitas['nama']) ?> &nbsp;|&nbsp; SPT: <?= esc($nhp['spt_nomor'] ?: '#'.$nhp['spt_id']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/auditi/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div style="background:#eff6ff;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:12px;color:#1d4ed8">
    <i class="fas fa-info-circle"></i>
    Berikan tanggapan atas setiap temuan di bawah. Temuan yang ditanggapi <strong>Sesuai</strong> akan ditutup.
    Temuan <strong>Tidak Sesuai</strong> akan diproses lebih lanjut dalam Laporan Hasil Pemeriksaan.
    Upload bukti tindak lanjut (PDF / gambar) bila tersedia.
</div>

<?php if (empty($items)): ?>
<div class="card">
    <div class="card-body" style="text-align:center;padding:48px;color:#94a3b8">
        <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px"></i>
        NHP ini belum memiliki item temuan.
    </div>
</div>
<?php else: ?>

<?php foreach ($items as $idx => $item): ?>
<?php
$stColors = [
    'pending'       => ['bg'=>'#fffbeb','border'=>'#fbbf24','badgeClass'=>'warning',  'icon'=>'clock',         'label'=>'Belum Ditanggapi'],
    'sesuai'        => ['bg'=>'#f0fdf4','border'=>'#22c55e','badgeClass'=>'success',  'icon'=>'circle-check',  'label'=>'Sesuai — Ditutup'],
    'tidak_sesuai'  => ['bg'=>'#fef2f2','border'=>'#ef4444','badgeClass'=>'danger',   'icon'=>'triangle-exclamation','label'=>'Tidak Sesuai — Proses TL'],
];
$stColor = $stColors[$item['status_tanggapan']] ?? $stColors['pending'];
?>
<div class="card mb-3" style="border-left:4px solid <?= $stColor['border'] ?>;background:<?= $stColor['bg'] ?>">
    <div class="card-header" style="background:transparent;border-bottom:1px solid <?= $stColor['border'] ?>33">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <div style="width:30px;height:30px;border-radius:50%;background:<?= $stColor['border'] ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0">
                <?= $idx + 1 ?>
            </div>
            <div style="flex:1">
                <div style="font-weight:700;font-size:14px;color:#1e293b"><?= esc($item['judul_temuan'] ?: 'Temuan #'.($idx+1)) ?></div>
                <?php if ($item['kode_temuan_kode']): ?>
                <span class="badge badge-secondary" style="font-size:10px"><?= esc($item['kode_temuan_kode']) ?></span>
                <?php endif; ?>
            </div>
            <span class="badge badge-<?= $stColor['badgeClass'] ?>" style="font-size:11px">
                <i class="fas fa-<?= $stColor['icon'] ?>"></i> <?= $stColor['label'] ?>
            </span>
            <?php if ($item['nilai_temuan']): ?>
            <span style="font-size:12px;color:#dc2626;font-weight:600">
                Rp <?= number_format($item['nilai_temuan'], 0, ',', '.') ?>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body" style="padding:16px 20px">
        <!-- Detail temuan -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <?php foreach (['kondisi'=>'Kondisi / Temuan','kriteria'=>'Kriteria','sebab'=>'Sebab','akibat'=>'Akibat','rekomendasi'=>'Rekomendasi Tim Audit'] as $field => $label): ?>
            <?php if ($item[$field]): ?>
            <div>
                <div style="font-size:10px;font-weight:700;color:#64748b;letter-spacing:.5px;margin-bottom:4px"><?= strtoupper($label) ?></div>
                <div style="font-size:13px;color:#1e293b;white-space:pre-line"><?= esc($item[$field]) ?></div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Tanggapan yang sudah ada -->
        <?php if ($item['tanggapan_entitas']): ?>
        <div style="background:rgba(255,255,255,.6);border-radius:8px;padding:12px 16px;margin-bottom:12px;border:1px solid <?= $stColor['border'] ?>55">
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:6px"><i class="fas fa-reply"></i> TANGGAPAN ANDA</div>
            <div style="font-size:13px;white-space:pre-line"><?= esc($item['tanggapan_entitas']) ?></div>
            <?php if ($item['tgl_tanggapan']): ?>
            <div style="font-size:11px;color:#94a3b8;margin-top:6px">
                Tanggal: <?= date('d M Y', strtotime($item['tgl_tanggapan'])) ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($item['bukti_tl'])): ?>
            <div style="margin-top:8px">
                <a href="/<?= esc($item['bukti_tl']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-paperclip"></i> Lihat Bukti TL
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Form tanggapan (hanya jika belum sesuai/tidak_sesuai atau mau ubah) -->
        <?php if ($item['status_tanggapan'] !== 'sesuai'): ?>
        <button class="btn btn-sm btn-outline-secondary mb-2"
                onclick="document.getElementById('form-tanggapan-<?= $item['id'] ?>').classList.toggle('d-none')">
            <i class="fas fa-<?= $item['tanggapan_entitas'] ? 'edit' : 'plus' ?>"></i>
            <?= $item['tanggapan_entitas'] ? 'Edit Tanggapan' : 'Tambah Tanggapan' ?>
        </button>

        <div id="form-tanggapan-<?= $item['id'] ?>" class="d-none"
             style="border:1px dashed #cbd5e1;border-radius:8px;padding:16px;background:rgba(255,255,255,.8);margin-top:4px">
            <form action="/admin/auditi/nhp-item/<?= $item['id'] ?>/tanggapi" method="POST"
                  enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-row-2" style="margin-bottom:10px">
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600">Status Tanggapan <span style="color:#ef4444">*</span></label>
                        <select name="status_tanggapan" class="form-control" required>
                            <option value="">— Pilih —</option>
                            <option value="sesuai"       <?= $item['status_tanggapan'] === 'sesuai'       ? 'selected' : '' ?>>✅ Sesuai — Temuan dapat ditutup</option>
                            <option value="tidak_sesuai" <?= $item['status_tanggapan'] === 'tidak_sesuai' ? 'selected' : '' ?>>⚠️ Tidak Sesuai — Masih perlu tindak lanjut</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600">Tanggal Tanggapan</label>
                        <input type="date" name="tgl_tanggapan" class="form-control"
                               value="<?= $item['tgl_tanggapan'] ?: date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:10px">
                    <label style="font-size:12px;font-weight:600">Uraian Tanggapan <span style="color:#ef4444">*</span></label>
                    <textarea name="tanggapan_entitas" class="form-control" rows="4" required
                              placeholder="Jelaskan tanggapan Anda atas temuan ini..."><?= esc($item['tanggapan_entitas']) ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom:12px">
                    <label style="font-size:12px;font-weight:600">Upload Bukti Tindak Lanjut <span style="font-size:11px;color:#64748b;font-weight:400">(PDF, JPG, PNG — opsional)</span></label>
                    <input type="file" name="bukti_tl" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if (!empty($item['bukti_tl'])): ?>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">
                        File sebelumnya: <a href="/<?= esc($item['bukti_tl']) ?>" target="_blank"><?= basename($item['bukti_tl']) ?></a>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> Simpan Tanggapan
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm"
                            onclick="document.getElementById('form-tanggapan-<?= $item['id'] ?>').classList.add('d-none')">
                        Batal
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div style="font-size:12px;color:#16a34a">
            <i class="fas fa-circle-check"></i> Tanggapan telah diterima — temuan ini ditutup.
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
