<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Detail SPT</h1>
        <p><?= esc($spt['nomor_naskah'] ?: 'Draft') ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        <?php if($spt['status'] === 'draft'): ?>
            <a href="/admin/spt/<?= $spt['id'] ?>/edit" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
            <button class="btn btn-primary" onclick="$('#modal-ajukan').show()">
                <i class="fas fa-paper-plane"></i> Ajukan
            </button>
        <?php endif; ?>
        <?php if(in_array($spt['status'], ['diajukan','acc_irban','acc_evlap','acc_sekretaris'])): ?>
            <button class="btn btn-success" onclick="$('#modal-approve').show()">
                <i class="fas fa-check"></i> Approve
            </button>
            <button class="btn btn-danger" onclick="$('#modal-reject').show()">
                <i class="fas fa-times"></i> Tolak
            </button>
        <?php endif; ?>
        <?php if($spt['status'] === 'terbit'): ?>
            <a href="/admin/spt/<?= $spt['id'] ?>/word" class="btn btn-success">
                <i class="fas fa-file-word"></i> Download Word
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:flex-start">

    <!-- Detail SPT -->
    <div>
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt"></i> Informasi SPT</h3></div>
            <div class="card-body">
                <table class="table-detail">
                    <tr><th width="180">Nomor Naskah</th><td><?= esc($spt['nomor_naskah'] ?: '—') ?></td></tr>
                    <tr><th>Tanggal Naskah</th><td><?= $spt['tanggal_naskah'] ? date('d F Y', strtotime($spt['tanggal_naskah'])) : '—' ?></td></tr>
                    <tr><th>Kode Kegiatan</th><td><span class="badge badge-primary"><?= esc($spt['kode_kegiatan']) ?></span></td></tr>
                    <tr><th>Irban</th><td><?= esc($spt['irban_nama']) ?></td></tr>
                    <tr><th>Dasar 1</th><td style="font-size:13px"><?= esc($spt['dasar_1']) ?></td></tr>
                    <?php if($spt['dasar_2']): ?>
                    <tr><th>Dasar 2</th><td style="font-size:13px"><?= esc($spt['dasar_2']) ?></td></tr>
                    <?php endif; ?>
                    <tr><th>Tujuan</th><td><?= esc($spt['tujuan']) ?></td></tr>
                    <tr><th>Periode</th><td>
                        <?= $spt['tanggal_mulai'] ? date('d/m/Y', strtotime($spt['tanggal_mulai'])) : '—' ?>
                        s.d.
                        <?= $spt['tanggal_selesai'] ? date('d/m/Y', strtotime($spt['tanggal_selesai'])) : '—' ?>
                    </td></tr>
                    <tr><th>Tembusan</th><td><?= esc($spt['tembusan']) ?></td></tr>
                    <tr><th>Penandatangan</th><td>
                        <?= esc($spt['penandatangan_nama'] ?? '—') ?><br>
                        <small class="text-muted"><?= esc($spt['penandatangan_jabatan'] ?? '') ?> | NIP <?= esc($spt['penandatangan_nip'] ?? '') ?></small>
                    </td></tr>
                    <tr><th>Status</th><td>
                        <span class="badge badge-<?= $statusColor[$spt['status']] ?? 'secondary' ?>" style="font-size:13px">
                            <?= $statusLabel[$spt['status']] ?? $spt['status'] ?>
                        </span>
                    </td></tr>
                </table>
            </div>
        </div>

        <!-- Tim SPT -->
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-users"></i> Susunan Tim</h3></div>
            <div class="card-body">
                <table class="table-admin w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jabatan / Peran</th>
                            <th>On Desk</th>
                            <th>On Field</th>
                            <th>Total HP</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($spt['tim'] as $i => $t): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td>
                            <?= esc($t['sdm_nama']) ?>
                            <?php if(!$t['from_pkpt']): ?>
                            <span class="badge badge-warning" style="font-size:10px">Tambahan</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($t['peran_spt']) ?></td>
                        <td><?= $t['hp_desk'] ?></td>
                        <td><?= $t['hp_field'] ?></td>
                        <td><strong><?= $t['hp_desk'] + $t['hp_field'] ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tracking Approval -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-tasks"></i> Progress Approval</h3></div>
            <div class="card-body">
                <?php
                $tahapLabel = ['irban'=>'Kepala Irban','evlap'=>'Subbag Evlap','sekretaris'=>'Sekretaris','inspektur'=>'Inspektur (TTE)'];
                $approvalMap = array_column($spt['approvals'], null, 'tahap');
                $statusOrder = ['draft'=>0,'diajukan'=>1,'acc_irban'=>2,'acc_evlap'=>3,'acc_sekretaris'=>4,'terbit'=>5];
                $currentStep = $statusOrder[$spt['status']] ?? 0;
                ?>
                <div class="approval-steps">
                <?php foreach($tahapLabel as $key => $label): ?>
                    <?php $ap = $approvalMap[$key] ?? null; ?>
                    <div class="approval-step <?= $ap && $ap['status'] === 'approved' ? 'done' : ($ap && $ap['status'] === 'rejected' ? 'rejected' : '') ?>">
                        <div class="step-icon">
                            <?php if($ap && $ap['status'] === 'approved'): ?>
                                <i class="fas fa-check"></i>
                            <?php elseif($ap && $ap['status'] === 'rejected'): ?>
                                <i class="fas fa-times"></i>
                            <?php else: ?>
                                <i class="fas fa-clock"></i>
                            <?php endif; ?>
                        </div>
                        <div class="step-info">
                            <div style="font-weight:600;font-size:13px"><?= $label ?></div>
                            <?php if($ap && $ap['approved_at']): ?>
                            <div style="font-size:11px;color:#94a3b8">
                                <?= $ap['approver_nama'] ?> · <?= date('d/m/Y H:i', strtotime($ap['approved_at'])) ?>
                            </div>
                            <?php if($ap['catatan']): ?>
                            <div style="font-size:11px;color:#ef4444"><?= esc($ap['catatan']) ?></div>
                            <?php endif; ?>
                            <?php else: ?>
                            <div style="font-size:11px;color:#94a3b8">Menunggu</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ringkasan PKA & Temuan -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:24px">

    <!-- PKA -->
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <h3 class="card-title" style="margin:0"><i class="fas fa-list-check"></i> Program Kerja Audit</h3>
            <a href="/admin/spt/<?= $spt['id'] ?>/pka" class="btn btn-xs btn-primary">Kelola PKA</a>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;text-align:center">
                <div>
                    <div style="font-size:24px;font-weight:700;color:#6366f1"><?= $pkaStats['total'] ?></div>
                    <div style="font-size:11px;color:#64748b">Total Prosedur</div>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:700;color:#22c55e"><?= $pkaStats['selesai'] ?></div>
                    <div style="font-size:11px;color:#64748b">Selesai</div>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:700;color:#f59e0b"><?= $pkaStats['belum'] ?></div>
                    <div style="font-size:11px;color:#64748b">Belum</div>
                </div>
            </div>
            <?php if($pkaStats['total'] > 0): ?>
            <div style="margin-top:12px;background:#f1f5f9;border-radius:8px;height:8px;overflow:hidden">
                <div style="width:<?= $pkaStats['total'] > 0 ? round($pkaStats['selesai']/$pkaStats['total']*100) : 0 ?>%;
                            height:100%;background:#22c55e;border-radius:8px"></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Temuan -->
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <h3 class="card-title" style="margin:0"><i class="fas fa-exclamation-triangle"></i> Temuan Audit</h3>
            <a href="/admin/spt/<?= $spt['id'] ?>/temuan" class="btn btn-xs btn-primary">Kelola Temuan</a>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;text-align:center">
                <div>
                    <div style="font-size:24px;font-weight:700;color:#6366f1"><?= $temuanSummary['total'] ?></div>
                    <div style="font-size:11px;color:#64748b">Total Temuan</div>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:700;color:#ef4444"><?= $temuanSummary['buka'] ?></div>
                    <div style="font-size:11px;color:#64748b">Terbuka</div>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:700;color:#22c55e"><?= $temuanSummary['tutup'] ?></div>
                    <div style="font-size:11px;color:#64748b">Tertutup</div>
                </div>
            </div>
            <?php if($temuanSummary['total_nilai'] > 0): ?>
            <div style="margin-top:12px;text-align:center;font-size:13px;color:#475569">
                Total nilai: <strong>Rp <?= number_format($temuanSummary['total_nilai'], 0, ',', '.') ?></strong>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal Ajukan -->
<div id="modal-ajukan" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-header">
            <h3>Ajukan SPT</h3>
            <button class="modal-close" onclick="$('#modal-ajukan').hide()"><i class="fas fa-times"></i></button>
        </div>
        <p style="margin:16px 0">Yakin ingin mengajukan SPT ini untuk persetujuan Kepala Irban?</p>
        <form action="/admin/spt/<?= $spt['id'] ?>/ajukan" method="POST">
            <?= csrf_field() ?>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="$('#modal-ajukan').hide()">Batal</button>
                <button type="submit" class="btn btn-primary">Ajukan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Approve -->
<div id="modal-approve" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-header">
            <h3>Setujui SPT</h3>
            <button class="modal-close" onclick="$('#modal-approve').hide()"><i class="fas fa-times"></i></button>
        </div>
        <form action="/admin/spt/<?= $spt['id'] ?>/approve" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="catatan" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="$('#modal-approve').hide()">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Setujui</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak -->
<div id="modal-reject" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-header">
            <h3>Tolak SPT</h3>
            <button class="modal-close" onclick="$('#modal-reject').hide()"><i class="fas fa-times"></i></button>
        </div>
        <form action="/admin/spt/<?= $spt['id'] ?>/reject" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Alasan Penolakan <span style="color:red">*</span></label>
                <textarea name="catatan" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="$('#modal-reject').hide()">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Tolak</button>
            </div>
        </form>
    </div>
</div>

<style>
.table-detail { width:100%;border-collapse:collapse; }
.table-detail th,.table-detail td { padding:8px 12px;border-bottom:1px solid #f1f5f9;font-size:13px; }
.table-detail th { color:#64748b;font-weight:500;white-space:nowrap;vertical-align:top; }
.approval-steps { display:flex;flex-direction:column;gap:12px; }
.approval-step { display:flex;align-items:flex-start;gap:12px;padding:10px;border-radius:8px;background:#f8fafc; }
.approval-step.done { background:#f0fdf4; }
.approval-step.rejected { background:#fef2f2; }
.step-icon { width:28px;height:28px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0; }
.done .step-icon { background:#16a34a;color:#fff; }
.rejected .step-icon { background:#dc2626;color:#fff; }
</style>

<?= $this->endSection() ?>
