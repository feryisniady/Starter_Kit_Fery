<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-file-pen"></i> Kertas Kerja Audit (KKA)</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['irban_nama'] ?? '') ?></p>
    </div>
    <div class="page-actions">
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

<!-- Progress Bar -->
<div class="card mb-3">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;align-items:center;gap:20px">
            <div style="flex:1">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-bottom:6px">
                    <span><i class="fas fa-chart-bar"></i> Progress KKA Tim</span>
                    <span><strong><?= $progress['counts']['selesai'] ?>/<?= $progress['total'] ?></strong> selesai</span>
                </div>
                <div style="background:#e2e8f0;border-radius:99px;height:10px;overflow:hidden">
                    <div style="background:linear-gradient(90deg,#10b981,#34d399);height:100%;width:<?= $progress['percent'] ?>%;transition:width .4s ease;border-radius:99px"></div>
                </div>
            </div>
            <div style="font-size:28px;font-weight:700;color:#10b981;min-width:55px;text-align:right">
                <?= $progress['percent'] ?>%
            </div>
        </div>

        <!-- Status breakdown -->
        <div style="display:flex;gap:16px;margin-top:12px;flex-wrap:wrap">
            <?php
            $badges = [
                'draft'            => ['secondary', 'Draft'],
                'ikhtisar_selesai' => ['info',      'Ikhtisar ✓'],
                'simpulan_selesai' => ['warning',   'Simpulan ✓'],
                'selesai'          => ['success',   'Selesai'],
            ];
            foreach ($badges as $key => [$color, $label]):
                $n = $progress['counts'][$key] ?? 0;
                if ($n === 0) continue;
            ?>
            <span class="badge badge-<?= $color ?>" style="font-size:12px;padding:4px 10px">
                <?= $label ?>: <?= $n ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Daftar KKA per AT -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Daftar KKA Per Anggota Tim</h3>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($kkaList)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px"></i>
            KKA belum dibuat. Pastikan Dalnis sudah menyetujui KM-5 (Reviu PKA).
        </div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                    <th style="padding:10px 16px;text-align:left;font-size:12px;color:#64748b;font-weight:600">ANGGOTA TIM</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b;font-weight:600">NIP</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b;font-weight:600">PERAN</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b;font-weight:600">STATUS KKA</th>
                    <th style="padding:10px 16px;text-align:center;font-size:12px;color:#64748b;font-weight:600">AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($kkaList as $k): ?>
            <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:12px 16px">
                    <div style="font-weight:600;color:#1e293b"><?= esc($k['nama']) ?></div>
                    <?php if ($k['catatan_dalnis']): ?>
                    <div style="font-size:11px;color:#6366f1;margin-top:2px">
                        <i class="fas fa-comment-dots"></i> Ada catatan Dalnis
                    </div>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:12px;color:#64748b"><?= esc($k['nip'] ?: '—') ?></td>
                <td style="padding:12px 16px;text-align:center">
                    <span style="font-size:11px;background:#eff6ff;color:#3b82f6;padding:2px 8px;border-radius:99px">
                        <?= esc($k['peran_spt'] ?: '—') ?>
                    </span>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <?php
                    $color = $statusColor[$k['status']] ?? 'secondary';
                    $label = $statusLabel[$k['status']] ?? $k['status'];
                    ?>
                    <span class="badge badge-<?= $color ?>"><?= $label ?></span>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <a href="/admin/kka/<?= $k['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Buka KKA
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
