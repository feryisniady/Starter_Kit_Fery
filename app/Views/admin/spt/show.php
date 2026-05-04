<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Detail SPT</h1>
        <p><?= esc($spt['nomor_naskah'] ?: 'Draft') ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>

        <?php if($canEdit): ?>
            <a href="/admin/spt/<?= $spt['id'] ?>/edit" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
            <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-info">
                <i class="fas fa-shield-check"></i> Kelengkapan KM
                <?php
                $kmDone  = count(array_filter($kmChecklist, fn($c) => $c['complete']));
                $kmTotal = count($kmChecklist);
                ?>
                <?php if ($kmDone < $kmTotal): ?>
                <span class="badge badge-warning" style="margin-left:4px"><?= $kmDone ?>/<?= $kmTotal ?></span>
                <?php else: ?>
                <span class="badge badge-success" style="margin-left:4px"><i class="fas fa-check"></i></span>
                <?php endif; ?>
            </a>
            <button class="btn btn-primary" onclick="$('#modal-ajukan').show()">
                <i class="fas fa-paper-plane"></i> Ajukan
            </button>
        <?php endif; ?>

        <?php if($spt['status'] === 'ditolak'): ?>
            <form action="/admin/spt/<?= $spt['id'] ?>/revisi" method="POST" style="display:inline"
                  data-confirm="Status SPT akan kembali ke <b>Draft</b> dan Anda dapat mengedit kembali."
                  data-confirm-title="Mulai Revisi SPT?"
                  data-confirm-btn="Ya, Mulai Revisi">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-rotate-left"></i> Revisi SPT
                </button>
            </form>
        <?php endif; ?>

        <?php if($canApproveNow): ?>
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

        <?php if($temuanSummary['total'] > 0): ?>
        <a href="/admin/spt/<?= $spt['id'] ?>/temuan" class="btn btn-outline-secondary">
            <i class="fas fa-exclamation-triangle"></i> Temuan
            <span class="badge badge-secondary" style="margin-left:4px"><?= $temuanSummary['total'] ?></span>
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

<?php if ($spt['status'] === 'ditolak'): ?>
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:16px 20px;margin-bottom:16px">
    <div style="display:flex;align-items:flex-start;gap:12px">
        <div style="width:40px;height:40px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-circle-xmark" style="color:#dc2626;font-size:18px"></i>
        </div>
        <div style="flex:1">
            <div style="font-weight:700;color:#991b1b;font-size:14px;margin-bottom:4px">
                SPT Ditolak — Perlu Perbaikan
            </div>
            <?php if(!empty($spt['catatan'])): ?>
            <div style="background:#fff;border:1px solid #fca5a5;border-radius:6px;padding:10px 14px;margin-top:8px;font-size:13px;color:#7f1d1d;line-height:1.6">
                <i class="fas fa-comment-dots" style="color:#dc2626;margin-right:4px"></i>
                <strong>Catatan Penolakan:</strong><br>
                <?= nl2br(esc($spt['catatan'])) ?>
            </div>
            <?php endif; ?>
            <div style="margin-top:10px;font-size:12px;color:#991b1b">
                <i class="fas fa-info-circle"></i>
                Klik <strong>"Revisi SPT"</strong> di kanan atas untuk memperbaiki dan mengajukan ulang.
            </div>
        </div>
    </div>
</div>
<?php elseif (in_array($spt['status'], ['diajukan','acc_irban','acc_evlap','acc_sekretaris'])): ?>
<div style="background:#fffbeb;border:1px solid #fbbf24;border-radius:8px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:13px;color:#92400e">
    <i class="fas fa-lock" style="font-size:16px;flex-shrink:0"></i>
    <div>
        <strong>SPT sedang dalam proses persetujuan</strong> — konten tidak dapat diedit sampai SPT terbit atau dikembalikan.
        <?php
        $progLabel = ['diajukan'=>'Menunggu Ka. Irban','acc_irban'=>'Menunggu Subbag Evlap','acc_evlap'=>'Menunggu Sekretaris','acc_sekretaris'=>'Menunggu Inspektur'];
        ?>
        <span style="margin-left:6px;background:#fef3c7;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600">
            <?= $progLabel[$spt['status']] ?? $spt['status'] ?>
        </span>
    </div>
</div>
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
                <table id="dt-tim" class="w-100">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Nama</th>
                            <th>Jabatan / Peran</th>
                            <th width="80">On Desk</th>
                            <th width="80">On Field</th>
                            <th width="80">Total HP</th>
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

<!-- KM Checklist Summary -->
<?php
$kmDoneAll  = count(array_filter($kmChecklist, fn($c) => $c['complete']));
$kmTotalAll = count($kmChecklist);
$kmAllDone  = $kmDoneAll === $kmTotalAll;
?>
<div class="card" style="margin-top:24px;border-left:4px solid <?= $kmAllDone ? '#22c55e' : '#f59e0b' ?>">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3 class="card-title" style="margin:0"><i class="fas fa-shield-check"></i> Kelengkapan Kendali Mutu (KM)</h3>
        <div style="display:flex;align-items:center;gap:10px">
            <span class="badge badge-<?= $kmAllDone ? 'success' : 'warning' ?>" style="font-size:12px">
                <?= $kmDoneAll ?>/<?= $kmTotalAll ?> Lengkap
            </span>
            <a href="/admin/spt/<?= $spt['id'] ?>/km" class="btn btn-xs btn-<?= $kmAllDone ? 'secondary' : 'primary' ?>">
                <?= $kmAllDone ? 'Lihat KM' : 'Lengkapi KM' ?>
            </a>
        </div>
    </div>
    <div class="card-body" style="padding:12px 20px">
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px">
            <?php
            foreach($kmChecklist as $key => $item):
            ?>
            <div style="text-align:center;padding:10px 6px;border-radius:8px;background:<?= $item['complete'] ? '#f0fdf4' : '#fff7ed' ?>">
                <div style="font-size:20px;margin-bottom:4px">
                    <i class="fas fa-<?= esc($item['icon']) ?>" style="color:<?= $item['complete'] ? '#16a34a' : '#f59e0b' ?>"></i>
                </div>
                <div style="font-size:10px;font-weight:600;color:#475569;line-height:1.3">
                    <?= esc($item['label']) ?>
                </div>
                <div style="font-size:10px;margin-top:3px;color:<?= $item['complete'] ? '#16a34a' : '#f59e0b' ?>">
                    <?= $item['complete'] ? '<i class="fas fa-check-circle"></i> Lengkap' : '<i class="fas fa-clock"></i> Belum' ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if(!$kmAllDone && $spt['status'] === 'draft'): ?>
        <div style="margin-top:10px;font-size:12px;color:#f59e0b;text-align:center">
            <i class="fas fa-triangle-exclamation"></i>
            SPT tidak dapat diajukan sebelum semua dokumen KM dilengkapi.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Ajukan -->
<div id="modal-ajukan" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:460px">

        <!-- Hero area -->
        <div style="background:linear-gradient(135deg,#4f46e5 0%,#6366f1 100%);
                    border-radius:10px 10px 0 0;padding:28px 24px 20px;text-align:center">
            <div style="width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:50%;
                        display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px">
                <i class="fas fa-paper-plane" style="font-size:26px;color:#fff"></i>
            </div>
            <h3 style="color:#fff;margin:0 0 4px;font-size:18px;font-weight:700">Ajukan SPT</h3>
            <p style="color:rgba(255,255,255,.8);margin:0;font-size:13px">
                Kirim ke Kepala Irban untuk persetujuan
            </p>
        </div>

        <!-- Info ringkasan SPT -->
        <div style="padding:20px 24px 0">
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;font-size:13px">
                <div style="display:grid;grid-template-columns:auto 1fr;gap:6px 12px;align-items:baseline">
                    <span style="color:#64748b;font-size:11px;font-weight:600;white-space:nowrap">NOMOR</span>
                    <span style="font-weight:600;color:#1e293b">
                        <?= $spt['nomor_naskah'] ? esc($spt['nomor_naskah']) : '<em style="color:#94a3b8">Draft — belum bernomor</em>' ?>
                    </span>
                    <span style="color:#64748b;font-size:11px;font-weight:600;white-space:nowrap">KEGIATAN</span>
                    <span><span class="badge badge-primary"><?= esc($spt['kode_kegiatan']) ?></span></span>
                    <span style="color:#64748b;font-size:11px;font-weight:600;white-space:nowrap">IRBAN</span>
                    <span style="color:#475569"><?= esc($spt['irban_nama']) ?></span>
                    <span style="color:#64748b;font-size:11px;font-weight:600;white-space:nowrap">TUJUAN</span>
                    <span style="color:#475569;line-height:1.4"><?= esc($spt['tujuan']) ?></span>
                </div>
            </div>

            <!-- Alur persetujuan mini -->
            <div style="margin-top:14px;display:flex;align-items:center;gap:0;font-size:11px;color:#64748b">
                <div style="flex:0 0 auto;text-align:center;padding:0 8px">
                    <div style="width:28px;height:28px;border-radius:50%;background:#e0e7ff;color:#4f46e5;
                                display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:12px">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>Anda</div>
                </div>
                <div style="flex:1;height:2px;background:linear-gradient(90deg,#c7d2fe,#e2e8f0);margin-bottom:14px"></div>
                <div style="flex:0 0 auto;text-align:center;padding:0 8px">
                    <div style="width:28px;height:28px;border-radius:50%;background:#fef3c7;color:#d97706;
                                display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:11px">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div style="white-space:nowrap">Ka. Irban</div>
                </div>
                <div style="flex:1;height:2px;background:#e2e8f0;margin-bottom:14px"></div>
                <div style="flex:0 0 auto;text-align:center;padding:0 8px">
                    <div style="width:28px;height:28px;border-radius:50%;background:#f1f5f9;color:#94a3b8;
                                display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:11px">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>Evlap</div>
                </div>
                <div style="flex:1;height:2px;background:#e2e8f0;margin-bottom:14px"></div>
                <div style="flex:0 0 auto;text-align:center;padding:0 8px">
                    <div style="width:28px;height:28px;border-radius:50%;background:#f1f5f9;color:#94a3b8;
                                display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:11px">
                        <i class="fas fa-stamp"></i>
                    </div>
                    <div style="white-space:nowrap">Terbit</div>
                </div>
            </div>

            <p style="margin:10px 0 0;font-size:12px;color:#64748b;text-align:center">
                <i class="fas fa-info-circle" style="color:#6366f1"></i>
                Setelah diajukan, SPT tidak dapat diedit sampai ada penolakan.
            </p>
        </div>

        <!-- Footer -->
        <form action="/admin/spt/<?= $spt['id'] ?>/ajukan" method="POST">
            <?= csrf_field() ?>
            <div style="padding:16px 24px 20px;display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn btn-secondary" onclick="$('#modal-ajukan').hide()">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="background:#4f46e5;border-color:#4f46e5">
                    <i class="fas fa-paper-plane"></i> Ajukan Sekarang
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Modal Approve -->
<div id="modal-approve" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:460px;padding:0;overflow:hidden">
        <!-- Header gradient -->
        <div style="background:linear-gradient(135deg,#16a34a 0%,#22c55e 100%);padding:20px 24px 16px;position:relative">
            <button onclick="$('#modal-approve').hide()"
                    style="position:absolute;top:12px;right:14px;background:rgba(255,255,255,.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-times"></i>
            </button>
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-circle-check" style="color:#fff;font-size:20px"></i>
                </div>
                <div>
                    <h3 style="color:#fff;margin:0;font-size:16px;font-weight:700">Setujui SPT</h3>
                    <p style="color:rgba(255,255,255,.8);margin:2px 0 0;font-size:12px">
                        <?= esc($spt['nomor_naskah'] ?: 'Draft #'.$spt['id']) ?>
                    </p>
                </div>
            </div>
        </div>
        <!-- Body -->
        <form action="/admin/spt/<?= $spt['id'] ?>/approve" method="POST" style="padding:20px 24px">
            <?= csrf_field() ?>
            <?php
            $tahapLabel = ['diajukan'=>'Kepala Irban','acc_irban'=>'Subbag Evlap','acc_evlap'=>'Sekretaris','acc_sekretaris'=>'Inspektur'];
            $nextApprover = $tahapLabel[$spt['status']] ?? '-';
            ?>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#166534">
                <i class="fas fa-info-circle"></i>
                Anda akan menyetujui SPT ini sebagai <strong><?= $nextApprover ?></strong>.
                Tindakan ini akan mengubah status SPT ke tahap berikutnya.
            </div>
            <div class="form-group" style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;display:block">
                    <i class="fas fa-comment-dots"></i> Catatan Persetujuan
                    <span style="font-weight:400;color:#94a3b8">(opsional)</span>
                </label>
                <textarea name="catatan" class="form-control" rows="3"
                          style="border:1px solid #d1fae5;border-radius:8px;background:#f0fdf4;resize:none;font-size:13px"
                          placeholder="Catatan tambahan atau instruksi khusus..."></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" class="btn btn-secondary" onclick="$('#modal-approve').hide()">Batal</button>
                <button type="submit" class="btn btn-success" style="background:#16a34a;border-color:#16a34a;gap:6px">
                    <i class="fas fa-circle-check"></i> Setujui SPT
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak -->
<div id="modal-reject" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:460px;padding:0;overflow:hidden">
        <!-- Header gradient -->
        <div style="background:linear-gradient(135deg,#dc2626 0%,#ef4444 100%);padding:20px 24px 16px;position:relative">
            <button onclick="$('#modal-reject').hide()"
                    style="position:absolute;top:12px;right:14px;background:rgba(255,255,255,.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-times"></i>
            </button>
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-circle-xmark" style="color:#fff;font-size:20px"></i>
                </div>
                <div>
                    <h3 style="color:#fff;margin:0;font-size:16px;font-weight:700">Tolak SPT</h3>
                    <p style="color:rgba(255,255,255,.8);margin:2px 0 0;font-size:12px">
                        <?= esc($spt['nomor_naskah'] ?: 'Draft #'.$spt['id']) ?>
                    </p>
                </div>
            </div>
        </div>
        <!-- Body -->
        <form action="/admin/spt/<?= $spt['id'] ?>/reject" method="POST" style="padding:20px 24px">
            <?= csrf_field() ?>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#991b1b">
                <i class="fas fa-triangle-exclamation"></i>
                SPT akan berstatus <strong>Ditolak</strong>. Pengaju akan melihat catatan penolakan dan bisa memperbaiki dengan klik "Revisi SPT".
            </div>
            <div class="form-group" style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;display:block">
                    <i class="fas fa-comment-dots"></i> Alasan Penolakan
                    <span style="color:#ef4444">*</span>
                </label>
                <textarea name="catatan" class="form-control" rows="4"
                          style="border:1px solid #fecaca;border-radius:8px;background:#fef2f2;resize:none;font-size:13px"
                          placeholder="Jelaskan alasan penolakan dan perbaikan yang harus dilakukan..." required></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" class="btn btn-secondary" onclick="$('#modal-reject').hide()">Batal</button>
                <button type="submit" class="btn btn-danger" style="gap:6px">
                    <i class="fas fa-circle-xmark"></i> Tolak & Kembalikan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$(function() {
    $('#dt-tim').DataTable({
        paging: false, searching: false, info: false,
        language: DT_LANG_ID,
        order: [],
    });
});
</script>
<?= $this->endSection() ?>
