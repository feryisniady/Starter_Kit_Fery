<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-book"></i> Library Template PKA</h1>
        <p>Kelola template prosedur audit yang dapat diterapkan ke PKA per SPT</p>
    </div>
    <div class="page-actions">
        <a href="/admin/pka-template/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Template Baru
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (empty($templates)): ?>
<div class="card">
    <div class="card-body" style="text-align:center;padding:60px;color:#94a3b8">
        <i class="fas fa-book-open" style="font-size:48px;display:block;margin-bottom:16px"></i>
        <div style="font-size:15px;font-weight:600;margin-bottom:8px">Belum ada template PKA</div>
        <p style="font-size:13px">Buat template prosedur audit sesuai jenis pengawasan untuk mempercepat penyusunan PKA.</p>
        <a href="/admin/pka-template/create" class="btn btn-primary" style="margin-top:12px">
            <i class="fas fa-plus"></i> Buat Template Pertama
        </a>
    </div>
</div>
<?php else: ?>

<?php
// Kelompokkan per jenis_audit
$grouped = [];
foreach ($templates as $t) {
    $key = $t['jenis_audit'] ?: 'Umum / Lainnya';
    $grouped[$key][] = $t;
}
ksort($grouped);
?>

<?php foreach ($grouped as $jenisAudit => $tpls): ?>
<div class="card mb-3">
    <div class="card-header" style="background:#f8fafc">
        <h3 class="card-title" style="margin:0">
            <i class="fas fa-tag" style="color:#6366f1"></i>
            <?= esc($jenisAudit) ?>
            <span style="font-size:12px;font-weight:400;color:#64748b">(<?= count($tpls) ?> template)</span>
        </h3>
    </div>
    <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                    <th style="padding:8px 16px;font-size:11px;color:#64748b;text-align:left">NAMA TEMPLATE</th>
                    <th style="padding:8px 16px;font-size:11px;color:#64748b;text-align:left">DESKRIPSI</th>
                    <th style="padding:8px 16px;font-size:11px;color:#64748b;text-align:center;width:80px">PROSEDUR</th>
                    <th style="padding:8px 16px;font-size:11px;color:#64748b;text-align:center;width:120px">AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($tpls as $tpl): ?>
            <tr style="border-bottom:1px solid #f1f5f9">
                <td style="padding:12px 16px;font-weight:600;font-size:13px"><?= esc($tpl['nama']) ?></td>
                <td style="padding:12px 16px;font-size:12px;color:#64748b"><?= esc($tpl['deskripsi'] ?: '—') ?></td>
                <td style="padding:12px 16px;text-align:center">
                    <span class="badge badge-info"><?= $tpl['item_count'] ?></span>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <a href="/admin/pka-template/<?= $tpl['id'] ?>/edit" class="btn btn-xs btn-primary">
                        <i class="fas fa-pen"></i>
                    </a>
                    <form action="/admin/pka-template/<?= $tpl['id'] ?>/delete" method="POST" style="display:inline"
                          data-confirm="Template <b><?= esc($tpl['nama']) ?></b> akan dihapus permanen beserta semua prosedurnya."
                          data-confirm-type="delete">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<?= $this->endSection() ?>
