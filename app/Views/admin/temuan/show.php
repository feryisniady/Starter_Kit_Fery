<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Detail Temuan — <?= esc($temuan['nomor_temuan']) ?></h1>
        <p>SPT: <?= esc($temuan['spt_nomor'] ?? '#') ?> | Kegiatan: <?= esc($temuan['kode_kegiatan'] ?? '—') ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $temuan['spt_id'] ?>/temuan" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="/admin/spt/temuan/<?= $temuan['id'] ?>/edit" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:flex-start">

    <!-- Kiri: isi temuan -->
    <div>
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> <?= esc($temuan['nomor_temuan']) ?> — <?= esc($temuan['judul']) ?></h3>
            </div>
            <div class="card-body">
                <table class="table-detail">
                    <tr>
                        <th width="140">Status</th>
                        <td>
                            <span class="badge badge-<?= $statusColor[$temuan['status_temuan']] ?? 'secondary' ?>" style="font-size:13px">
                                <?= $statusLabel[$temuan['status_temuan']] ?? $temuan['status_temuan'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php if($temuan['kode_temuan_kode']): ?>
                    <tr>
                        <th>Kode Temuan</th>
                        <td>
                            <code><?= esc($temuan['kode_temuan_kode']) ?></code>
                            <span class="badge badge-<?= $jenisColor[$temuan['jenis']] ?? 'secondary' ?>" style="margin-left:6px">
                                <?= $jenisLabel[$temuan['jenis']] ?? '' ?>
                            </span>
                            <br><small><?= esc($temuan['kode_temuan_uraian']) ?></small>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if($temuan['nilai_temuan'] > 0): ?>
                    <tr>
                        <th>Nilai Temuan</th>
                        <td><strong>Rp <?= number_format($temuan['nilai_temuan'], 0, ',', '.') ?></strong></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt"></i> Isi Temuan</h3></div>
            <div class="card-body">
                <div style="margin-bottom:16px">
                    <div style="font-weight:600;color:#475569;margin-bottom:4px;font-size:13px">KONDISI</div>
                    <div style="background:#f8fafc;border-left:3px solid #6366f1;padding:12px;border-radius:0 4px 4px 0;font-size:14px;line-height:1.6">
                        <?= render_wysiwyg($temuan['kondisi']) ?: '<span style="color:#94a3b8">—</span>' ?>
                    </div>
                </div>
                <?php if($temuan['kriteria']): ?>
                <div style="margin-bottom:16px">
                    <div style="font-weight:600;color:#475569;margin-bottom:4px;font-size:13px">KRITERIA</div>
                    <div style="background:#f8fafc;border-left:3px solid #0ea5e9;padding:12px;border-radius:0 4px 4px 0;font-size:14px;line-height:1.6">
                        <?= render_wysiwyg($temuan['kriteria']) ?>
                    </div>
                </div>
                <?php endif; ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <?php if($temuan['sebab']): ?>
                    <div>
                        <div style="font-weight:600;color:#475569;margin-bottom:4px;font-size:13px">SEBAB</div>
                        <div style="background:#f8fafc;border-left:3px solid #f59e0b;padding:12px;border-radius:0 4px 4px 0;font-size:14px;line-height:1.6">
                            <?= render_wysiwyg($temuan['sebab']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if($temuan['akibat']): ?>
                    <div>
                        <div style="font-weight:600;color:#475569;margin-bottom:4px;font-size:13px">AKIBAT</div>
                        <div style="background:#f8fafc;border-left:3px solid #ef4444;padding:12px;border-radius:0 4px 4px 0;font-size:14px;line-height:1.6">
                            <?= render_wysiwyg($temuan['akibat']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanan: rekomendasi -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-lightbulb"></i> Rekomendasi (<?= count($temuan['rekomendasi']) ?>)</h3></div>
            <div class="card-body" style="padding:0">
                <?php if(empty($temuan['rekomendasi'])): ?>
                <div style="text-align:center;color:#94a3b8;padding:24px;font-size:13px">Belum ada rekomendasi</div>
                <?php else: ?>
                <?php foreach($temuan['rekomendasi'] as $i => $r): ?>
                <div style="padding:14px;<?= $i > 0 ? 'border-top:1px solid #f1f5f9' : '' ?>">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
                        <strong style="font-size:12px;color:#475569">R-<?= str_pad($r['nomor_urut'], 2, '0', STR_PAD_LEFT) ?></strong>
                        <span class="badge badge-<?= $rekomendasiColor[$r['status']] ?? 'secondary' ?>" style="font-size:10px">
                            <?= $rekomendasiLabel[$r['status']] ?? $r['status'] ?>
                        </span>
                    </div>
                    <div style="font-size:13px;margin-bottom:6px;white-space:pre-wrap"><?= esc($r['isi_rekomendasi']) ?></div>
                    <?php if($r['batas_waktu']): ?>
                    <div style="font-size:11px;color:#94a3b8"><i class="fas fa-calendar"></i> Batas: <?= date('d/m/Y', strtotime($r['batas_waktu'])) ?></div>
                    <?php endif; ?>
                    <?php if($r['nilai_rekomendasi'] > 0): ?>
                    <div style="font-size:11px;color:#94a3b8"><i class="fas fa-coins"></i> Rp <?= number_format($r['nilai_rekomendasi'], 0, ',', '.') ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
