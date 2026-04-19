<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-id-card"></i> KM-1 — Kartu Penugasan</h1>
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
<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if(!$canEdit): ?>
<div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#92400e">
    <i class="fas fa-lock"></i> <strong>SPT sedang dalam proses persetujuan.</strong> Data tidak dapat diubah.
</div>
<?php endif; ?>

<?php
$fmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$totalAnggaran = fn($t) => (float)($t['hp_desk'] ?? 0) + (float)($t['hp_field'] ?? 0);
$totalRealisasi = fn($t) => (float)($t['persiapan_realisasi_hari'] ?? 0)
                           + (float)($t['pelaksanaan_realisasi_hari'] ?? 0)
                           + (float)($t['penyelesaian_realisasi_hari'] ?? 0);
?>

<form action="/admin/spt/<?= $spt['id'] ?>/km/1/save" method="POST">
<?= csrf_field() ?>

<div class="card" style="max-width:860px">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-id-card"></i> Kartu Penugasan (Format KM5 BPKP)</h3>
    </div>
    <div class="card-body" style="padding:0">

        <table style="width:100%;border-collapse:collapse;font-size:13px">

            <!-- 1. Nomor SPT -->
            <tr>
                <td style="width:36px;padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569">1</td>
                <td style="width:220px;padding:10px 12px;border:1px solid #e2e8f0;color:#475569">Nomor Surat Perintah Tugas</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <strong><?= esc($spt['nomor_naskah'] ?: '—') ?></strong>
                    <div style="font-size:11px;color:#94a3b8">auto dari SPT</div>
                </td>
            </tr>

            <!-- 2. Nomor Kartu + Satker -->
            <tr>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569">2</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;color:#475569">Nomor Kartu / Tujuan Satker</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <div style="flex:0 0 180px">
                            <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">No. Kartu Penugasan</label>
                            <input type="text" name="no_kartu" class="form-control form-control-sm"
                                   value="<?= old('no_kartu', $row['no_kartu'] ?? '') ?>"
                                   placeholder="KP-001/IRB1/2025"
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                        </div>
                        <div style="flex:1;min-width:160px">
                            <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Tujuan / Nama Satker</label>
                            <input type="text" name="tujuan_satker" class="form-control form-control-sm"
                                   value="<?= old('tujuan_satker', $row['tujuan_satker'] ?? $spt['area_pengawasan'] ?? '') ?>"
                                   placeholder="Dinas Pendidikan"
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                        </div>
                    </div>
                </td>
            </tr>

            <!-- 3. Tingkat Risiko -->
            <tr>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569">3</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;color:#475569">Tingkat Risiko</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <select name="tingkat_risiko" class="form-control form-control-sm" style="max-width:200px" <?= !$canEdit ? 'disabled' : '' ?>>
                        <option value="">— Pilih —</option>
                        <?php foreach(['Sangat Tinggi','Tinggi','Sedang','Rendah'] as $opt): ?>
                        <option value="<?= $opt ?>" <?= ($row['tingkat_risiko'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- 4. Kegiatan Pengawasan -->
            <tr>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569">4</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;color:#475569">Uraian Kegiatan Pengawasan</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <textarea name="kegiatan" class="form-control form-control-sm" rows="2"
                              placeholder="Audit Ketaatan Pengelolaan Keuangan..."
                              <?= !$canEdit ? 'disabled' : '' ?>><?= old('kegiatan', $row['kegiatan'] ?? $spt['tujuan'] ?? '') ?></textarea>
                </td>
            </tr>

            <!-- 5. Laporan Kepada -->
            <tr>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569">5</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;color:#475569">Laporan Kepada</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <input type="text" name="laporan_kepada" class="form-control form-control-sm"
                           value="<?= old('laporan_kepada', $row['laporan_kepada'] ?? '') ?>"
                           placeholder="Kepala Inspektorat..."
                           <?= !$canEdit ? 'disabled' : '' ?>>
                </td>
            </tr>

            <!-- 6. Waktu Pelaksanaan -->
            <tr>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569">6</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;color:#475569">Waktu Pelaksanaan</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <div style="flex:1;min-width:130px">
                            <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Rencana Mulai</label>
                            <input type="date" name="rencana_mulai" class="form-control form-control-sm"
                                   value="<?= old('rencana_mulai', $row['rencana_mulai'] ?? $spt['tanggal_mulai'] ?? '') ?>"
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                        </div>
                        <div style="flex:1;min-width:130px">
                            <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Rencana Selesai</label>
                            <input type="date" name="rencana_selesai" class="form-control form-control-sm"
                                   value="<?= old('rencana_selesai', $row['rencana_selesai'] ?? $spt['tanggal_selesai'] ?? '') ?>"
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                        </div>
                    </div>
                </td>
            </tr>

            <!-- 7. Rencana Kunjungan Lapangan -->
            <tr>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569">7</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;color:#475569">Rencana Kunjungan ke Lapangan</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <input type="text" name="rencana_kunjungan" class="form-control form-control-sm"
                           value="<?= old('rencana_kunjungan', $row['rencana_kunjungan'] ?? '') ?>"
                           placeholder="Minggu ke-II Januari 2025"
                           <?= !$canEdit ? 'disabled' : '' ?>>
                </td>
            </tr>

            <!-- 8. Kunjungan Supervisi -->
            <tr>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569;vertical-align:top">8</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;color:#475569;vertical-align:top">Rencana Kunjungan Supervisi</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <div style="margin-bottom:10px">
                        <div style="font-size:11px;font-weight:600;color:#6366f1;margin-bottom:5px">
                            <i class="fas fa-user-tie"></i> Pengendali Mutu (PM)
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <?php for($i=1;$i<=3;$i++): $fld="kunjungan_pm_{$i}"; ?>
                            <div>
                                <label style="font-size:10px;color:#94a3b8;display:block">Ke-<?= $i ?></label>
                                <input type="date" name="<?= $fld ?>" class="form-control form-control-sm"
                                       style="width:140px"
                                       value="<?= old($fld, $row[$fld] ?? '') ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#0ea5e9;margin-bottom:5px">
                            <i class="fas fa-user-check"></i> Pengendali Teknis (PT)
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <?php for($i=1;$i<=3;$i++): $fld="kunjungan_pt_{$i}"; ?>
                            <div>
                                <label style="font-size:10px;color:#94a3b8;display:block">Ke-<?= $i ?></label>
                                <input type="date" name="<?= $fld ?>" class="form-control form-control-sm"
                                       style="width:140px"
                                       value="<?= old($fld, $row[$fld] ?? '') ?>"
                                       <?= !$canEdit ? 'disabled' : '' ?>>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </td>
            </tr>

            <!-- 9. RMP / RPL Bulan -->
            <tr>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569">9</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;color:#475569">RMP / RPL Bulan ke-</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <div style="display:flex;gap:16px;flex-wrap:wrap">
                        <div>
                            <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">RMP Bulan ke-</label>
                            <input type="number" name="rmp_bulan" class="form-control form-control-sm"
                                   style="width:80px" min="1" max="12" placeholder="1"
                                   value="<?= old('rmp_bulan', $row['rmp_bulan'] ?? '') ?>"
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                        </div>
                        <div>
                            <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">RPL Bulan ke-</label>
                            <input type="number" name="rpl_bulan" class="form-control form-control-sm"
                                   style="width:80px" min="1" max="12" placeholder="1"
                                   value="<?= old('rpl_bulan', $row['rpl_bulan'] ?? '') ?>"
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                        </div>
                    </div>
                </td>
            </tr>

            <!-- 10. Tanggal Konsep Laporan -->
            <tr>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;text-align:center;font-weight:700;background:#f8fafc;color:#475569">10</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0;color:#475569">Tanggal Konsep Laporan</td>
                <td style="padding:10px 12px;border:1px solid #e2e8f0">
                    <input type="date" name="tanggal_konsep_laporan" class="form-control form-control-sm"
                           style="max-width:200px"
                           value="<?= old('tanggal_konsep_laporan', $row['tanggal_konsep_laporan'] ?? '') ?>"
                           <?= !$canEdit ? 'disabled' : '' ?>>
                </td>
            </tr>

        </table>
    </div>
</div>

<!-- Tabel Tim Pengawas -->
<div class="card mt-3" style="max-width:860px">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Anggaran & Realisasi Waktu Tim</h3>
    </div>
    <div class="card-body" style="padding:0">
        <?php if(!$km5bAda): ?>
        <div style="background:#fef9c3;border-radius:0 0 8px 8px;padding:10px 16px;font-size:12px;color:#854d0e">
            <i class="fas fa-hourglass-half"></i> Realisasi hari akan tampil setelah Entry Meeting (KM-5b) diisi.
        </div>
        <?php endif; ?>
        <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12px">
            <thead>
                <tr style="background:#f1f5f9">
                    <th style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center" rowspan="2">No</th>
                    <th style="padding:8px 10px;border:1px solid #e2e8f0" rowspan="2">Nama / Jabatan</th>
                    <th style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center" rowspan="2">Peran</th>
                    <th style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#dbeafe" colspan="3">Anggaran Hari</th>
                    <?php if($km5bAda): ?>
                    <th style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#dcfce7" colspan="2">Realisasi Hari</th>
                    <?php endif; ?>
                </tr>
                <tr style="background:#f1f5f9">
                    <th style="padding:6px 10px;border:1px solid #e2e8f0;text-align:center;background:#dbeafe;font-size:11px">On Desk</th>
                    <th style="padding:6px 10px;border:1px solid #e2e8f0;text-align:center;background:#dbeafe;font-size:11px">On Field</th>
                    <th style="padding:6px 10px;border:1px solid #e2e8f0;text-align:center;background:#dbeafe;font-size:11px">Total</th>
                    <?php if($km5bAda): ?>
                    <th style="padding:6px 10px;border:1px solid #e2e8f0;text-align:center;background:#dcfce7;font-size:11px">Total</th>
                    <th style="padding:6px 10px;border:1px solid #e2e8f0;text-align:center;background:#dcfce7;font-size:11px">Selisih</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; $sumDesk=0; $sumField=0; $sumAng=0; $sumReal=0; ?>
            <?php foreach($timAw as $t): ?>
            <?php
                $desk  = (float)($t['hp_desk']  ?? 0);
                $field = (float)($t['hp_field'] ?? 0);
                $ang   = $desk + $field;
                $real  = $totalRealisasi($t);
                $selisih = $real - $ang;
                $sumDesk  += $desk;
                $sumField += $field;
                $sumAng   += $ang;
                $sumReal  += $real;
            ?>
            <tr style="<?= $no % 2 === 0 ? 'background:#f8fafc' : '' ?>">
                <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center"><?= $no++ ?></td>
                <td style="padding:8px 10px;border:1px solid #e2e8f0">
                    <div style="font-weight:600"><?= esc($t['sdm_nama']) ?></div>
                </td>
                <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center">
                    <span class="badge badge-primary" style="font-size:10px"><?= esc($t['peran_spt']) ?></span>
                </td>
                <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#eff6ff"><?= $desk > 0 ? $desk : '—' ?></td>
                <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#eff6ff"><?= $field > 0 ? $field : '—' ?></td>
                <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#eff6ff;font-weight:600"><?= $ang > 0 ? $ang : '—' ?></td>
                <?php if($km5bAda): ?>
                <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#f0fdf4;font-weight:600"><?= $real > 0 ? $real : '—' ?></td>
                <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;<?= $selisih > 0 ? 'color:#dc2626' : ($selisih < 0 ? 'color:#16a34a' : '') ?>">
                    <?php if($ang > 0 || $real > 0): ?>
                        <?= ($selisih > 0 ? '+' : '') . $selisih ?>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f1f5f9;font-weight:700">
                    <td colspan="3" style="padding:8px 10px;border:1px solid #e2e8f0;text-align:right">Total</td>
                    <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#dbeafe"><?= $sumDesk ?: '—' ?></td>
                    <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#dbeafe"><?= $sumField ?: '—' ?></td>
                    <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#dbeafe"><?= $sumAng ?: '—' ?></td>
                    <?php if($km5bAda): ?>
                    <?php $sumSel = $sumReal - $sumAng; ?>
                    <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;background:#dcfce7"><?= $sumReal ?: '—' ?></td>
                    <td style="padding:8px 10px;border:1px solid #e2e8f0;text-align:center;<?= $sumSel > 0 ? 'color:#dc2626' : ($sumSel < 0 ? 'color:#16a34a' : '') ?>">
                        <?= $sumAng > 0 || $sumReal > 0 ? (($sumSel > 0 ? '+' : '') . $sumSel) : '—' ?>
                    </td>
                    <?php endif; ?>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>

<!-- Catatan -->
<div class="card mt-3" style="max-width:860px">
    <div class="card-body">
        <div class="form-group" style="margin-bottom:0">
            <label>Catatan</label>
            <textarea name="catatan" class="form-control" rows="2"
                      <?= !$canEdit ? 'disabled' : '' ?>><?= old('catatan', $row['catatan'] ?? '') ?></textarea>
        </div>
    </div>
</div>

<div class="form-actions" style="max-width:860px">
    <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary" <?= !$canEdit ? 'disabled' : '' ?>>
        <i class="fas fa-save"></i> Simpan Kartu Penugasan
    </button>
</div>

</form>

<?= $this->endSection() ?>
