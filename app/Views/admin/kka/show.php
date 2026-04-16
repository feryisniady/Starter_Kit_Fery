<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// Warna per stage
$stageStatus = [
    'ikhtisar'    => in_array($kka['status'], ['ikhtisar_selesai','simpulan_selesai','selesai']),
    'simpulan'    => in_array($kka['status'], ['simpulan_selesai','selesai']),
    'rekomendasi' => $kka['status'] === 'selesai',
];
$canEditIkhtisar    = $canEdit && $kka['status'] === 'draft';
$canEditSimpulan    = $canEdit && $kka['status'] === 'ikhtisar_selesai';
$canEditRekomendasi = $canEdit && $kka['status'] === 'simpulan_selesai';
?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-file-pen"></i> KKA — <?= esc($kka['nama']) ?></h1>
        <p>
            SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> &nbsp;|&nbsp;
            Peran: <strong><?= esc($kka['peran_spt'] ?: 'Anggota Tim') ?></strong>
        </p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>/kka" class="btn btn-secondary">
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

<!-- Status Breadcrumb -->
<div class="card mb-3" style="border:none;background:transparent;box-shadow:none">
    <div style="display:flex;align-items:center;gap:0">
        <?php
        $stages = [
            ['key'=>'draft',            'icon'=>'pen',             'label'=>'1. Ikhtisar'],
            ['key'=>'ikhtisar_selesai', 'icon'=>'diagram-project', 'label'=>'2. Simpulan'],
            ['key'=>'simpulan_selesai', 'icon'=>'lightbulb',       'label'=>'3. Rekomendasi'],
            ['key'=>'selesai',          'icon'=>'check-circle',    'label'=>'Selesai'],
        ];
        $currentIdx = array_search($kka['status'], array_column($stages,'key'));
        foreach ($stages as $i => $stage):
            $done    = $i < $currentIdx || $kka['status'] === 'selesai';
            $current = $i === $currentIdx;
            $bg      = $done ? '#10b981' : ($current ? '#3b82f6' : '#e2e8f0');
            $color   = ($done || $current) ? '#fff' : '#94a3b8';
        ?>
        <div style="display:flex;align-items:center;flex:1">
            <div style="display:flex;align-items:center;gap:8px;background:<?= $bg ?>;color:<?= $color ?>;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;white-space:nowrap">
                <i class="fas fa-<?= $stage['icon'] ?>"></i>
                <?= $stage['label'] ?>
                <?php if ($done): ?><i class="fas fa-check" style="font-size:11px"></i><?php endif; ?>
            </div>
            <?php if ($i < count($stages) - 1): ?>
            <div style="flex:1;height:2px;background:<?= $done ? '#10b981' : '#e2e8f0' ?>;min-width:20px"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Catatan Dalnis (jika ada / jika user adalah Dalnis) -->
<?php if ($isDalnis || $kka['catatan_dalnis']): ?>
<div class="card mb-3" style="border-left:4px solid #6366f1">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
            <div style="flex:1">
                <div style="font-size:12px;font-weight:600;color:#6366f1;margin-bottom:6px">
                    <i class="fas fa-comment-dots"></i> CATATAN DALNIS
                </div>
                <?php if ($kka['catatan_dalnis']): ?>
                <div style="color:#1e293b;white-space:pre-line"><?= esc($kka['catatan_dalnis']) ?></div>
                <?php else: ?>
                <div style="color:#94a3b8;font-style:italic">Belum ada catatan.</div>
                <?php endif; ?>
            </div>
            <?php if ($isDalnis): ?>
            <button class="btn btn-sm btn-outline-primary" onclick="document.getElementById('form-catatan-dalnis').classList.toggle('d-none')">
                <i class="fas fa-edit"></i> <?= $kka['catatan_dalnis'] ? 'Edit' : 'Tambah' ?>
            </button>
            <?php endif; ?>
        </div>

        <?php if ($isDalnis): ?>
        <form id="form-catatan-dalnis" class="d-none" action="/admin/kka/<?= $kka['id'] ?>/catatan-dalnis" method="POST" style="margin-top:12px">
            <?= csrf_field() ?>
            <textarea name="catatan_dalnis" class="form-control" rows="3" style="font-size:13px"><?= esc($kka['catatan_dalnis']) ?></textarea>
            <div style="margin-top:8px;display:flex;gap:8px">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Simpan Catatan</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('form-catatan-dalnis').classList.add('d-none')">Batal</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- TAHAP 1 — IKHTISAR                                     -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="card mb-3">
    <div class="card-header" style="background:<?= $stageStatus['ikhtisar'] ? 'linear-gradient(135deg,#059669,#10b981)' : 'linear-gradient(135deg,#2563eb,#3b82f6)' ?>;color:#fff">
        <h3 class="card-title" style="color:#fff">
            <i class="fas fa-pen"></i> Tahap 1 — Ikhtisar
        </h3>
        <?php if ($stageStatus['ikhtisar']): ?>
        <span style="margin-left:auto;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:99px;font-size:12px">
            <i class="fas fa-check"></i> Selesai
        </span>
        <?php endif; ?>
    </div>
    <div class="card-body">

        <?php if (empty($ikhtisar)): ?>
        <div style="text-align:center;padding:24px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>
            Belum ada item ikhtisar. Klik "+ Tambah" untuk memulai.
        </div>
        <?php else: ?>
        <?php foreach ($ikhtisar as $idx => $ikh): ?>
        <div class="mb-3" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
            <div style="background:#f8fafc;padding:10px 16px;display:flex;justify-content:space-between;align-items:center">
                <strong style="font-size:13px;color:#1e293b">Item #<?= $ikh['nomor_urut'] ?></strong>
                <?php if ($canEditIkhtisar): ?>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-xs btn-outline-primary" onclick="toggleEditIkhtisar(<?= $ikh['id'] ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="/admin/kka/ikhtisar/<?= $ikh['id'] ?>/delete" method="POST" style="display:inline"
                          onsubmit="return confirm('Hapus item ini?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <!-- View Mode -->
            <div id="view-ikh-<?= $ikh['id'] ?>" style="padding:14px 16px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px">PROGRAM KERJA / PROSEDUR</div>
                        <div style="font-size:13px;white-space:pre-line"><?= esc($ikh['program_kerja'] ?: '—') ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px">LANGKAH AUDIT</div>
                        <div style="font-size:13px;white-space:pre-line"><?= esc($ikh['langkah_audit'] ?: '—') ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px">HASIL OBSERVASI</div>
                        <div style="font-size:13px;white-space:pre-line"><?= esc($ikh['hasil_observasi'] ?: '—') ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px">SIMPULAN IKHTISAR</div>
                        <div style="font-size:13px;white-space:pre-line"><?= esc($ikh['simpulan'] ?: '—') ?></div>
                    </div>
                </div>
            </div>
            <!-- Edit Mode -->
            <?php if ($canEditIkhtisar): ?>
            <div id="edit-ikh-<?= $ikh['id'] ?>" style="display:none;padding:14px 16px;background:#fffbeb;border-top:1px solid #fde68a">
                <form action="/admin/kka/ikhtisar/<?= $ikh['id'] ?>/update" method="POST">
                    <?= csrf_field() ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label class="form-label">Program Kerja / Prosedur</label>
                            <textarea name="program_kerja" class="form-control" rows="3"><?= esc($ikh['program_kerja']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Langkah Audit</label>
                            <textarea name="langkah_audit" class="form-control" rows="3"><?= esc($ikh['langkah_audit']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hasil Observasi</label>
                            <textarea name="hasil_observasi" class="form-control" rows="3"><?= esc($ikh['hasil_observasi']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Simpulan Ikhtisar</label>
                            <textarea name="simpulan" class="form-control" rows="3"><?= esc($ikh['simpulan']) ?></textarea>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:8px">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditIkhtisar(<?= $ikh['id'] ?>)">Batal</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Tambah Ikhtisar -->
        <?php if ($canEditIkhtisar): ?>
        <button class="btn btn-outline-primary btn-sm mb-2" onclick="document.getElementById('form-add-ikhtisar').classList.toggle('d-none')">
            <i class="fas fa-plus"></i> Tambah Item Ikhtisar
        </button>
        <div id="form-add-ikhtisar" class="d-none" style="border:1px dashed #93c5fd;border-radius:8px;padding:16px;background:#eff6ff;margin-top:8px">
            <form action="/admin/kka/<?= $kka['id'] ?>/ikhtisar/store" method="POST">
                <?= csrf_field() ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group">
                        <label class="form-label">Program Kerja / Prosedur <span class="text-danger">*</span></label>
                        <textarea name="program_kerja" class="form-control" rows="3" placeholder="Uraian prosedur/program kerja yang dilaksanakan..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Langkah Audit</label>
                        <textarea name="langkah_audit" class="form-control" rows="3" placeholder="Langkah-langkah pengujian yang dilakukan..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hasil Observasi <span class="text-danger">*</span></label>
                        <textarea name="hasil_observasi" class="form-control" rows="3" placeholder="Fakta/temuan yang ditemukan di lapangan..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Simpulan Ikhtisar</label>
                        <textarea name="simpulan" class="form-control" rows="3" placeholder="Simpulan dari ikhtisar ini..."></textarea>
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:8px">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Tambahkan</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('form-add-ikhtisar').classList.add('d-none')">Batal</button>
                </div>
            </form>
        </div>

        <!-- Tombol Selesaikan Ikhtisar -->
        <?php if (!empty($ikhtisar)): ?>
        <div style="margin-top:16px;padding-top:16px;border-top:1px dashed #e2e8f0;text-align:right">
            <form action="/admin/kka/<?= $kka['id'] ?>/ikhtisar/selesai" method="POST"
                  onsubmit="return confirm('Tandai ikhtisar sebagai selesai?\n\nSetelah ini Anda tidak bisa mengubah ikhtisar lagi.')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle"></i> Selesaikan Ikhtisar — Lanjut ke Simpulan
                </button>
            </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- TAHAP 2 — SIMPULAN (KKSA)                              -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="card mb-3" <?php if (!in_array($kka['status'], ['ikhtisar_selesai','simpulan_selesai','selesai'])): ?>style="opacity:.5;pointer-events:none"<?php endif; ?>>
    <div class="card-header" style="background:<?= $stageStatus['simpulan'] ? 'linear-gradient(135deg,#059669,#10b981)' : 'linear-gradient(135deg,#d97706,#f59e0b)' ?>;color:#fff">
        <h3 class="card-title" style="color:#fff">
            <i class="fas fa-diagram-project"></i> Tahap 2 — Simpulan (Kondisi–Kriteria–Sebab–Akibat)
        </h3>
        <?php if (!in_array($kka['status'], ['ikhtisar_selesai','simpulan_selesai','selesai'])): ?>
        <span style="margin-left:auto;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:99px;font-size:12px">
            <i class="fas fa-lock"></i> Selesaikan ikhtisar dulu
        </span>
        <?php elseif ($stageStatus['simpulan']): ?>
        <span style="margin-left:auto;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:99px;font-size:12px">
            <i class="fas fa-check"></i> Selesai
        </span>
        <?php endif; ?>
    </div>
    <div class="card-body">

        <?php if (empty($simpulan)): ?>
        <div style="text-align:center;padding:24px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>
            Belum ada simpulan.
        </div>
        <?php else: ?>
        <?php foreach ($simpulan as $s): ?>
        <div class="mb-3" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
            <div style="background:#fffbeb;padding:10px 16px;display:flex;justify-content:space-between;align-items:center">
                <strong style="font-size:13px;color:#1e293b">Simpulan #<?= $s['nomor_urut'] ?></strong>
                <?php if ($canEditSimpulan): ?>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-xs btn-outline-primary" onclick="toggleEditSimpulan(<?= $s['id'] ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="/admin/kka/simpulan/<?= $s['id'] ?>/delete" method="POST" style="display:inline"
                          onsubmit="return confirm('Hapus simpulan ini?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <div id="view-sp-<?= $s['id'] ?>" style="padding:14px 16px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <?php foreach (['kondisi'=>'Kondisi','kriteria'=>'Kriteria','sebab'=>'Sebab','akibat'=>'Akibat','rekomendasi_awal'=>'Rekomendasi Awal'] as $field => $label): ?>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px"><?= strtoupper($label) ?></div>
                        <div style="font-size:13px;white-space:pre-line"><?= esc($s[$field] ?: '—') ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($canEditSimpulan): ?>
            <div id="edit-sp-<?= $s['id'] ?>" style="display:none;padding:14px 16px;background:#fffbeb;border-top:1px solid #fde68a">
                <form action="/admin/kka/simpulan/<?= $s['id'] ?>/update" method="POST">
                    <?= csrf_field() ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <?php foreach (['kondisi'=>'Kondisi','kriteria'=>'Kriteria','sebab'=>'Sebab','akibat'=>'Akibat','rekomendasi_awal'=>'Rekomendasi Awal'] as $field => $label): ?>
                        <div class="form-group">
                            <label class="form-label"><?= $label ?></label>
                            <textarea name="<?= $field ?>" class="form-control" rows="3"><?= esc($s[$field]) ?></textarea>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:8px">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditSimpulan(<?= $s['id'] ?>)">Batal</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($canEditSimpulan): ?>
        <button class="btn btn-outline-warning btn-sm mb-2" onclick="document.getElementById('form-add-simpulan').classList.toggle('d-none')">
            <i class="fas fa-plus"></i> Tambah Simpulan
        </button>
        <div id="form-add-simpulan" class="d-none" style="border:1px dashed #fcd34d;border-radius:8px;padding:16px;background:#fffbeb;margin-top:8px">
            <form action="/admin/kka/<?= $kka['id'] ?>/simpulan/store" method="POST">
                <?= csrf_field() ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <?php foreach (['kondisi'=>['Kondisi','Fakta/kondisi yang ditemukan...'],'kriteria'=>['Kriteria','Standar/aturan yang berlaku...'],'sebab'=>['Sebab','Penyebab terjadinya kondisi...'],'akibat'=>['Akibat','Dampak yang ditimbulkan...'],'rekomendasi_awal'=>['Rekomendasi Awal','Saran perbaikan awal...']] as $field => [$label, $ph]): ?>
                    <div class="form-group">
                        <label class="form-label"><?= $label ?></label>
                        <textarea name="<?= $field ?>" class="form-control" rows="3" placeholder="<?= $ph ?>"></textarea>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;gap:8px;margin-top:8px">
                    <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-plus"></i> Tambahkan</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('form-add-simpulan').classList.add('d-none')">Batal</button>
                </div>
            </form>
        </div>

        <?php if (!empty($simpulan)): ?>
        <div style="margin-top:16px;padding-top:16px;border-top:1px dashed #e2e8f0;text-align:right">
            <form action="/admin/kka/<?= $kka['id'] ?>/simpulan/selesai" method="POST"
                  onsubmit="return confirm('Tandai simpulan sebagai selesai?\n\nSetelah ini Anda tidak bisa mengubah simpulan lagi.')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-check-circle"></i> Selesaikan Simpulan — Lanjut ke Rekomendasi
                </button>
            </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- TAHAP 3 — REKOMENDASI                                  -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="card mb-3" <?php if (!in_array($kka['status'], ['simpulan_selesai','selesai'])): ?>style="opacity:.5;pointer-events:none"<?php endif; ?>>
    <div class="card-header" style="background:<?= $stageStatus['rekomendasi'] ? 'linear-gradient(135deg,#059669,#10b981)' : 'linear-gradient(135deg,#7c3aed,#8b5cf6)' ?>;color:#fff">
        <h3 class="card-title" style="color:#fff">
            <i class="fas fa-lightbulb"></i> Tahap 3 — Rekomendasi
        </h3>
        <?php if (!in_array($kka['status'], ['simpulan_selesai','selesai'])): ?>
        <span style="margin-left:auto;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:99px;font-size:12px">
            <i class="fas fa-lock"></i> Selesaikan simpulan dulu
        </span>
        <?php elseif ($stageStatus['rekomendasi']): ?>
        <span style="margin-left:auto;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:99px;font-size:12px">
            <i class="fas fa-check"></i> Selesai
        </span>
        <?php endif; ?>
    </div>
    <div class="card-body">

        <?php if (empty($rekomendasi)): ?>
        <div style="text-align:center;padding:24px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>
            Belum ada rekomendasi.
        </div>
        <?php else: ?>
        <?php foreach ($rekomendasi as $r): ?>
        <div class="mb-3" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
            <div style="background:#f5f3ff;padding:10px 16px;display:flex;justify-content:space-between;align-items:center">
                <strong style="font-size:13px;color:#1e293b">Rekomendasi #<?= $r['nomor_urut'] ?></strong>
                <?php if ($canEditRekomendasi): ?>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-xs btn-outline-primary" onclick="toggleEditRek(<?= $r['id'] ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="/admin/kka/rekomendasi/<?= $r['id'] ?>/delete" method="POST" style="display:inline"
                          onsubmit="return confirm('Hapus rekomendasi ini?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <div id="view-rek-<?= $r['id'] ?>" style="padding:14px 16px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px">URAIAN REKOMENDASI</div>
                        <div style="font-size:13px;white-space:pre-line"><?= esc($r['uraian_rekomendasi'] ?: '—') ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px">PIHAK BERTANGGUNG JAWAB</div>
                        <div style="font-size:13px"><?= esc($r['pihak_bertanggung_jawab'] ?: '—') ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px">TARGET PENYELESAIAN</div>
                        <div style="font-size:13px"><?= $r['target_penyelesaian'] ? date('d M Y', strtotime($r['target_penyelesaian'])) : '—' ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px">TANGGAPAN AUDITI</div>
                        <div style="font-size:13px;white-space:pre-line"><?= esc($r['tanggapan_auditi'] ?: '—') ?></div>
                    </div>
                </div>
            </div>
            <?php if ($canEditRekomendasi): ?>
            <div id="edit-rek-<?= $r['id'] ?>" style="display:none;padding:14px 16px;background:#f5f3ff;border-top:1px solid #ddd6fe">
                <form action="/admin/kka/rekomendasi/<?= $r['id'] ?>/update" method="POST">
                    <?= csrf_field() ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label class="form-label">Uraian Rekomendasi <span class="text-danger">*</span></label>
                            <textarea name="uraian_rekomendasi" class="form-control" rows="3" required><?= esc($r['uraian_rekomendasi']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pihak Bertanggung Jawab</label>
                            <input type="text" name="pihak_bertanggung_jawab" class="form-control" value="<?= esc($r['pihak_bertanggung_jawab']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Target Penyelesaian</label>
                            <input type="date" name="target_penyelesaian" class="form-control" value="<?= esc($r['target_penyelesaian']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggapan Auditi</label>
                            <textarea name="tanggapan_auditi" class="form-control" rows="3"><?= esc($r['tanggapan_auditi']) ?></textarea>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:8px">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditRek(<?= $r['id'] ?>)">Batal</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($canEditRekomendasi): ?>
        <button class="btn btn-outline-secondary btn-sm mb-2" style="border-color:#8b5cf6;color:#7c3aed" onclick="document.getElementById('form-add-rek').classList.toggle('d-none')">
            <i class="fas fa-plus"></i> Tambah Rekomendasi
        </button>
        <div id="form-add-rek" class="d-none" style="border:1px dashed #c4b5fd;border-radius:8px;padding:16px;background:#f5f3ff;margin-top:8px">
            <form action="/admin/kka/<?= $kka['id'] ?>/rekomendasi/store" method="POST">
                <?= csrf_field() ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group">
                        <label class="form-label">Uraian Rekomendasi <span class="text-danger">*</span></label>
                        <textarea name="uraian_rekomendasi" class="form-control" rows="3" placeholder="Tindak lanjut yang direkomendasikan..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pihak Bertanggung Jawab</label>
                        <input type="text" name="pihak_bertanggung_jawab" class="form-control" placeholder="Nama/jabatan...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Target Penyelesaian</label>
                        <input type="date" name="target_penyelesaian" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggapan Auditi</label>
                        <textarea name="tanggapan_auditi" class="form-control" rows="3" placeholder="Tanggapan dari pihak auditi..."></textarea>
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:8px">
                    <button type="submit" class="btn btn-sm btn-primary" style="background:#7c3aed;border-color:#7c3aed"><i class="fas fa-plus"></i> Tambahkan</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('form-add-rek').classList.add('d-none')">Batal</button>
                </div>
            </form>
        </div>

        <?php if (!empty($rekomendasi)): ?>
        <div style="margin-top:16px;padding-top:16px;border-top:1px dashed #e2e8f0;text-align:right">
            <form action="/admin/kka/<?= $kka['id'] ?>/rekomendasi/selesai" method="POST"
                  onsubmit="return confirm('Selesaikan KKA ini?\n\nSeluruh tahap (Ikhtisar, Simpulan, Rekomendasi) akan ditandai selesai.')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success" style="background:#7c3aed;border-color:#7c3aed">
                    <i class="fas fa-check-double"></i> Selesaikan KKA
                </button>
            </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<?php if ($kka['status'] === 'selesai'): ?>
<div style="text-align:center;padding:24px;background:linear-gradient(135deg,#059669,#10b981);border-radius:12px;color:#fff;margin-bottom:20px">
    <i class="fas fa-trophy" style="font-size:36px;display:block;margin-bottom:8px"></i>
    <div style="font-size:18px;font-weight:700">KKA Selesai!</div>
    <div style="font-size:13px;opacity:.9;margin-top:4px">Semua tahap (Ikhtisar, Simpulan, Rekomendasi) telah diselesaikan.</div>
</div>
<?php endif; ?>

<script>
function toggleEditIkhtisar(id) {
    document.getElementById('view-ikh-'+id).style.display === 'none'
        ? (document.getElementById('view-ikh-'+id).style.display='', document.getElementById('edit-ikh-'+id).style.display='none')
        : (document.getElementById('view-ikh-'+id).style.display='none', document.getElementById('edit-ikh-'+id).style.display='');
}
function toggleEditSimpulan(id) {
    document.getElementById('view-sp-'+id).style.display === 'none'
        ? (document.getElementById('view-sp-'+id).style.display='', document.getElementById('edit-sp-'+id).style.display='none')
        : (document.getElementById('view-sp-'+id).style.display='none', document.getElementById('edit-sp-'+id).style.display='');
}
function toggleEditRek(id) {
    document.getElementById('view-rek-'+id).style.display === 'none'
        ? (document.getElementById('view-rek-'+id).style.display='', document.getElementById('edit-rek-'+id).style.display='none')
        : (document.getElementById('view-rek-'+id).style.display='none', document.getElementById('edit-rek-'+id).style.display='');
}
</script>

<?= $this->endSection() ?>
