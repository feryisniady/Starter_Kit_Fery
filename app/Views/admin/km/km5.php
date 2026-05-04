<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-magnifying-glass-chart"></i> KM-5 — Reviu PKA</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['irban_nama']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke KM
        </a>
        <a href="/admin/spt/<?= $spt['id'] ?>/pka" class="btn btn-info">
            <i class="fas fa-clipboard-list"></i> Lihat PKA
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

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start">

    <!-- Form Reviu -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-magnifying-glass-chart"></i> Lembar Reviu Program Kerja Audit</h3>
        </div>
        <div class="card-body">
            <div style="background:#eff6ff;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#1d4ed8">
                <i class="fas fa-info-circle"></i>
                KM-5 merupakan lembar reviu PKA oleh Pengendali Teknis (Dalnis) sebelum pelaksanaan pengawasan dimulai.
                Pastikan PKA sudah tersedia di modul PKA sebelum mengisi formulir ini.
            </div>

            <form action="/admin/spt/<?= $spt['id'] ?>/km/5/save" method="POST">
                <?= csrf_field() ?>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Tanggal Reviu</label>
                        <input type="date" name="tanggal_reviu" class="form-control"
                               value="<?= old('tanggal_reviu', $row['tanggal_reviu'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Hasil Reviu</label>
                        <select name="status" class="form-control" id="statusReviu">
                            <option value="disetujui" <?= ($row['status'] ?? 'disetujui') === 'disetujui' ? 'selected' : '' ?>>
                                ✅ Disetujui
                            </option>
                            <option value="dikembalikan" <?= ($row['status'] ?? '') === 'dikembalikan' ? 'selected' : '' ?>>
                                🔄 Dikembalikan untuk Revisi
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Checklist Aspek Reviu -->
                <div style="background:#f8fafc;border-radius:8px;padding:16px;margin-bottom:16px">
                    <div style="font-weight:600;font-size:13px;color:#475569;margin-bottom:12px">
                        <i class="fas fa-list-check"></i> Checklist Aspek yang Direviu
                    </div>
                    <div style="display:grid;gap:10px">
                        <?php
                        $cekItems = [
                            'cek_tujuan'        => 'Tujuan pengawasan sudah sesuai PKPT dan arahan pimpinan',
                            'cek_sasaran'       => 'Sasaran pengawasan sudah tepat dan terukur',
                            'cek_ruang_lingkup' => 'Ruang lingkup sudah mencakup seluruh objek pemeriksaan',
                            'cek_metodologi'    => 'Metodologi dan prosedur audit sudah memadai',
                            'cek_tim'           => 'Komposisi tim sudah sesuai dengan kompleksitas penugasan',
                            'cek_waktu'         => 'Anggaran waktu sudah realistis dan proporsional',
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
                              data-wysiwyg data-wysiwyg-height="100px"
                              placeholder="Catatan umum hasil reviu PKA..."><?= old('catatan_reviu', $row['catatan_reviu'] ?? '') ?></textarea>
                </div>

                <div class="form-group" id="saranBlock" style="<?= ($row['status'] ?? '') !== 'dikembalikan' ? 'display:none' : '' ?>">
                    <label>Saran Perbaikan <span style="color:#ef4444">*</span></label>
                    <textarea name="saran_perbaikan" class="form-control" rows="3"
                              data-wysiwyg data-wysiwyg-height="100px"
                              placeholder="Tuliskan saran perbaikan yang harus dilakukan tim auditor..."><?= old('saran_perbaikan', $row['saran_perbaikan'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary" <?= !$canEdit ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Simpan KM-5
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan PKA -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Ringkasan PKA
                    <span class="badge badge-primary" style="margin-left:6px"><?= count($pkaList) ?></span>
                </h3>
            </div>
            <?php if(empty($pkaList)): ?>
            <div class="card-body" style="text-align:center;padding:24px;color:#94a3b8">
                <i class="fas fa-clipboard-list" style="font-size:28px;margin-bottom:8px;display:block"></i>
                <p style="font-size:13px">Belum ada PKA.<br>Isi PKA terlebih dahulu.</p>
                <a href="/admin/spt/<?= $spt['id'] ?>/pka" class="btn btn-sm btn-primary" style="margin-top:8px">
                    <i class="fas fa-plus"></i> Buka PKA
                </a>
            </div>
            <?php else: ?>
            <div class="card-body" style="padding:0">
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                            <th style="padding:8px 12px;text-align:left;color:#64748b">#</th>
                            <th style="padding:8px 12px;text-align:left;color:#64748b">Prosedur Audit</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($pkaList as $i => $pka): ?>
                    <tr style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:8px 12px;color:#94a3b8"><?= $i + 1 ?></td>
                        <td style="padding:8px 12px"><?= esc($pka['uraian_prosedur'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <?php if($row && $row['status'] === 'disetujui'): ?>
        <div class="card mt-3" style="border-left:4px solid #22c55e">
            <div class="card-body" style="padding:14px 16px;display:flex;align-items:center;gap:10px">
                <i class="fas fa-circle-check" style="color:#22c55e;font-size:20px"></i>
                <div>
                    <div style="font-weight:600;font-size:13px;color:#16a34a">PKA Disetujui</div>
                    <div style="font-size:11px;color:#64748b">
                        <?= $row['tanggal_reviu'] ? date('d F Y', strtotime($row['tanggal_reviu'])) : '' ?>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif($row && $row['status'] === 'dikembalikan'): ?>
        <div class="card mt-3" style="border-left:4px solid #f59e0b">
            <div class="card-body" style="padding:14px 16px;display:flex;align-items:center;gap:10px">
                <i class="fas fa-rotate-left" style="color:#f59e0b;font-size:20px"></i>
                <div>
                    <div style="font-weight:600;font-size:13px;color:#b45309">Dikembalikan untuk Revisi</div>
                    <div style="font-size:11px;color:#64748b;margin-top:3px"><?= esc($row['saran_perbaikan'] ?? '') ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
document.getElementById('statusReviu').addEventListener('change', function(){
    const block = document.getElementById('saranBlock');
    block.style.display = this.value === 'dikembalikan' ? '' : 'none';
    if (this.value === 'dikembalikan' && typeof initWysiwyg === 'function') initWysiwyg(block);
});
</script>

<?= $this->endSection() ?>
