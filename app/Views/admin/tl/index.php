<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-clipboard-check"></i> Verifikasi Tindak Lanjut</h1>
        <p>Daftar tindak lanjut rekomendasi yang dikirim OPD/Entitas kepada Inspektorat</p>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<!-- ══ Tab Filter ══════════════════════════════════════════════════════ -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
    <?php
    $tabDef = [
        'menunggu' => ['label'=>'Menunggu Verifikasi', 'icon'=>'clock',         'color'=>'#d97706','bg'=>'#fef3c7'],
        'revisi'   => ['label'=>'Perlu Perbaikan',     'icon'=>'rotate-left',   'color'=>'#dc2626','bg'=>'#fee2e2'],
        'diterima' => ['label'=>'Selesai / Diterima',  'icon'=>'circle-check',  'color'=>'#16a34a','bg'=>'#dcfce7'],
        'semua'    => ['label'=>'Semua',               'icon'=>'list',          'color'=>'#6366f1','bg'=>'#ede9fe'],
    ];
    foreach ($tabDef as $key => $t):
        $active = $tab === $key;
    ?>
    <a href="/admin/tl?tab=<?= $key ?>"
       style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;
              font-size:13px;font-weight:600;text-decoration:none;transition:all .15s;
              <?= $active
                ? "background:{$t['color']};color:#fff;box-shadow:0 2px 8px {$t['color']}55"
                : "background:{$t['bg']};color:{$t['color']};border:1.5px solid {$t['color']}55" ?>">
        <i class="fas fa-<?= $t['icon'] ?>" style="font-size:12px"></i>
        <?= $t['label'] ?>
        <span style="<?= $active ? 'background:rgba(255,255,255,.25)' : "background:{$t['color']}22;border:1px solid {$t['color']}33" ?>;
                     padding:1px 8px;border-radius:20px;font-size:11px;font-weight:700;min-width:20px;text-align:center">
            <?= $counts[$key] ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ══ Konten ═════════════════════════════════════════════════════════ -->
<?php if (empty($list)): ?>
<div class="card">
    <div class="card-body" style="text-align:center;padding:60px;color:#94a3b8">
        <i class="fas fa-inbox" style="font-size:48px;display:block;margin-bottom:16px;opacity:.5"></i>
        <div style="font-size:15px;font-weight:600;margin-bottom:6px">Tidak Ada Data</div>
        <p style="font-size:13px">Belum ada tindak lanjut dengan status ini.</p>
    </div>
</div>
<?php else: ?>

<div style="display:flex;flex-direction:column;gap:12px">
<?php foreach($list as $i => $row):
    $sv      = $row['status_verifikasi'];
    $overdue = $row['batas_waktu'] && strtotime($row['batas_waktu']) < time() && $sv !== 'diterima';
    $svConf  = [
        'menunggu' => ['label'=>'Menunggu Verifikasi', 'color'=>'#d97706','bg'=>'#fef3c7','icon'=>'clock'],
        'revisi'   => ['label'=>'Perlu Perbaikan',     'color'=>'#dc2626','bg'=>'#fee2e2','icon'=>'rotate-left'],
        'diterima' => ['label'=>'Diterima',            'color'=>'#16a34a','bg'=>'#dcfce7','icon'=>'circle-check'],
    ];
    $svStyle = $svConf[$sv] ?? ['label'=>$sv,'color'=>'#64748b','bg'=>'#f1f5f9','icon'=>'question'];
    $jumlahDok = (int)($row['jumlah_dokumen'] ?? 0);
?>
<div class="card" style="border-left:4px solid <?= $svStyle['color'] ?>;<?= $overdue ? 'background:#fff8f8' : '' ?>">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start">

            <!-- Nomor & OPD -->
            <div style="min-width:0;flex:1.2">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.5px;margin-bottom:4px">OPD / Entitas</div>
                <div style="font-weight:700;font-size:14px;color:#1e293b;margin-bottom:2px">
                    <?= esc($row['entitas_nama']) ?>
                </div>
                <div style="font-size:11px;color:#64748b;display:flex;align-items:center;gap:6px">
                    <i class="fas fa-file-signature" style="color:#6366f1"></i>
                    <?= esc($row['spt_nomor'] ?: 'SPT #'.$row['spt_id']) ?>
                </div>
            </div>

            <!-- Temuan & Rekomendasi -->
            <div style="min-width:0;flex:2">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.5px;margin-bottom:4px">Temuan & Rekomendasi</div>
                <div style="font-weight:600;font-size:13px;color:#1e293b;margin-bottom:4px;
                            display:flex;align-items:center;gap:6px">
                    <?php if ($row['nomor_temuan']): ?>
                    <span style="background:#e0e7ff;color:#4338ca;font-size:10px;padding:1px 6px;
                                 border-radius:4px;font-weight:700;flex-shrink:0">
                        <?= esc($row['nomor_temuan']) ?>
                    </span>
                    <?php endif; ?>
                    <?= esc($row['temuan_judul']) ?>
                </div>
                <div style="font-size:12px;color:#64748b;line-height:1.5;
                            display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                    <span style="color:#6366f1;font-size:10px;font-weight:700">Rek.<?= $row['rek_nomor'] ?>:</span>
                    <?= esc($row['isi_rekomendasi']) ?>
                </div>
                <?php if ($row['nilai_rekomendasi'] > 0): ?>
                <div style="font-size:11px;color:#ef4444;font-weight:600;margin-top:3px">
                    <i class="fas fa-coins"></i>
                    Rp <?= number_format($row['nilai_rekomendasi'], 0, ',', '.') ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Batas Waktu -->
            <div style="min-width:100px;text-align:center">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.5px;margin-bottom:4px">Batas Waktu</div>
                <?php if ($row['batas_waktu']): ?>
                <div style="font-size:13px;font-weight:600;<?= $overdue ? 'color:#ef4444' : 'color:#475569' ?>">
                    <?php if ($overdue): ?>
                    <i class="fas fa-triangle-exclamation"></i><br>
                    <span style="font-size:11px">TELAT</span><br>
                    <?php endif; ?>
                    <?= date('d M Y', strtotime($row['batas_waktu'])) ?>
                </div>
                <?php else: ?>
                <span style="color:#94a3b8;font-size:13px">—</span>
                <?php endif; ?>
            </div>

            <!-- Dokumen & Dikirim -->
            <div style="min-width:90px;text-align:center">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.5px;margin-bottom:4px">Bukti</div>
                <div style="font-size:13px;color:<?= $jumlahDok > 0 ? '#6366f1' : '#94a3b8' ?>;font-weight:600">
                    <i class="fas fa-paperclip"></i> <?= $jumlahDok ?>
                    <span style="font-size:10px;font-weight:400">file</span>
                </div>
                <div style="font-size:10px;color:#94a3b8;margin-top:4px">
                    <?= date('d M Y', strtotime($row['created_at'])) ?><br>
                    <?= date('H:i', strtotime($row['created_at'])) ?>
                </div>
            </div>

            <!-- Status & Aksi -->
            <div style="min-width:140px;display:flex;flex-direction:column;align-items:center;
                        justify-content:center;gap:8px">
                <span style="background:<?= $svStyle['bg'] ?>;color:<?= $svStyle['color'] ?>;
                             padding:5px 12px;border-radius:20px;font-size:11px;font-weight:700;
                             display:flex;align-items:center;gap:5px;white-space:nowrap">
                    <i class="fas fa-<?= $svStyle['icon'] ?>" style="font-size:10px"></i>
                    <?= $svStyle['label'] ?>
                </span>
                <?php if ($sv !== 'diterima' && !empty($row['verified_by_nama'])): ?>
                <div style="font-size:10px;color:#94a3b8">oleh <?= esc($row['verified_by_nama']) ?></div>
                <?php endif; ?>
                <a href="/admin/tl/<?= $row['id'] ?>"
                   style="padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;
                          text-decoration:none;display:flex;align-items:center;gap:6px;
                          <?= $sv === 'menunggu'
                            ? 'background:#4f46e5;color:#fff'
                            : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0' ?>">
                    <i class="fas fa-<?= $sv === 'menunggu' ? 'check-double' : 'eye' ?>"></i>
                    <?= $sv === 'menunggu' ? 'Verifikasi' : 'Detail' ?>
                </a>
            </div>

        </div><!-- /flex row -->
    </div>
</div>
<?php endforeach; ?>
</div><!-- /list -->

<?php endif; ?>

<?= $this->endSection() ?>
