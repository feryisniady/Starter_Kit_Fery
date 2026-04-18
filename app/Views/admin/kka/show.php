<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// Warna per stage
$stageStatus = [
    'ikhtisar'    => in_array($kka['status'], ['ikhtisar_selesai','simpulan_selesai','selesai']),
    'simpulan'    => in_array($kka['status'], ['simpulan_selesai','selesai']),
    'rekomendasi' => $kka['status'] === 'selesai',
];
$statusKka = $kka['status_kka'] ?? 'draft';
// AT hanya bisa edit jika KKA belum di-lock (belum approved) dan status_kka bukan submitted
$kkaApproved  = $statusKka === 'approved';
$kkaSubmitted = $statusKka === 'submitted';
$canEditIkhtisar    = $canEdit && $kka['status'] === 'draft' && !$kkaApproved;
$canEditSimpulan    = $canEdit && $kka['status'] === 'ikhtisar_selesai' && !$kkaApproved;
$canEditRekomendasi = $canEdit && $kka['status'] === 'simpulan_selesai' && !$kkaApproved;
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

<!-- ═══ Panel Pengiriman KKA ke KT ═══ -->
<?php
$skColors = [
    'draft'     => ['bg'=>'#f8fafc','border'=>'#cbd5e1','icon'=>'clock','iconColor'=>'#64748b','title'=>'Belum Dikirim ke Ketua Tim','titleColor'=>'#374151'],
    'submitted' => ['bg'=>'#fffbeb','border'=>'#fbbf24','icon'=>'paper-plane','iconColor'=>'#d97706','title'=>'Menunggu Review Ketua Tim','titleColor'=>'#92400e'],
    'approved'  => ['bg'=>'#f0fdf4','border'=>'#22c55e','icon'=>'circle-check','iconColor'=>'#16a34a','title'=>'Disetujui Ketua Tim','titleColor'=>'#166534'],
    'rejected'  => ['bg'=>'#fef2f2','border'=>'#ef4444','icon'=>'circle-xmark','iconColor'=>'#dc2626','title'=>'Dikembalikan — Perlu Perbaikan','titleColor'=>'#991b1b'],
];
$sc = $skColors[$statusKka] ?? $skColors['draft'];
?>
<div class="card mb-3" style="border-left:4px solid <?= $sc['border'] ?>;background:<?= $sc['bg'] ?>">
    <div class="card-body" style="padding:14px 20px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:12px">
                <i class="fas fa-<?= $sc['icon'] ?>" style="font-size:22px;color:<?= $sc['iconColor'] ?>"></i>
                <div>
                    <div style="font-weight:700;font-size:14px;color:<?= $sc['titleColor'] ?>"><?= $sc['title'] ?></div>
                    <?php if ($statusKka === 'submitted' && $kka['submitted_at']): ?>
                    <div style="font-size:11px;color:#64748b;margin-top:2px">Dikirim <?= date('d M Y H:i', strtotime($kka['submitted_at'])) ?></div>
                    <?php elseif (in_array($statusKka, ['approved','rejected']) && $kka['reviewed_at']): ?>
                    <div style="font-size:11px;color:#64748b;margin-top:2px">Direview <?= date('d M Y H:i', strtotime($kka['reviewed_at'])) ?></div>
                    <?php endif; ?>
                    <?php if ($kka['catatan_review'] && in_array($statusKka, ['approved','rejected'])): ?>
                    <div style="margin-top:6px;font-size:12px;padding:6px 10px;border-radius:6px;background:rgba(0,0,0,.05)">
                        <strong>Catatan KT:</strong> <?= esc($kka['catatan_review']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <?php if ($isAt && $kka['status'] === 'selesai' && in_array($statusKka, ['draft','rejected'])): ?>
                <!-- AT: tombol kirim ke KT -->
                <form action="/admin/kka/<?= $kka['id'] ?>/submit" method="POST"
                      onsubmit="return confirm('Kirim KKA ini ke Ketua Tim untuk direview?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-paper-plane"></i> Kirim ke Ketua Tim
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($isKt && $statusKka === 'submitted'): ?>
                <!-- KT: panel review (setujui / kembalikan) -->
                <button class="btn btn-success btn-sm" onclick="document.getElementById('panel-approve').classList.toggle('d-none')">
                    <i class="fas fa-check"></i> Setujui
                </button>
                <button class="btn btn-danger btn-sm" onclick="document.getElementById('panel-reject').classList.toggle('d-none')">
                    <i class="fas fa-rotate-left"></i> Kembalikan
                </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isKt && $statusKka === 'submitted'): ?>
        <!-- Form Setujui -->
        <div id="panel-approve" class="d-none" style="margin-top:12px;padding:12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px">
            <form action="/admin/kka/<?= $kka['id'] ?>/approve" method="POST">
                <?= csrf_field() ?>
                <div class="form-group" style="margin-bottom:8px">
                    <label style="font-size:12px;font-weight:600;color:#166534">Catatan (opsional)</label>
                    <textarea name="catatan_review" class="form-control" rows="2" placeholder="Tambahkan catatan persetujuan..."></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-circle-check"></i> Konfirmasi Setujui</button>
            </form>
        </div>
        <!-- Form Kembalikan -->
        <div id="panel-reject" class="d-none" style="margin-top:12px;padding:12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px">
            <form action="/admin/kka/<?= $kka['id'] ?>/reject" method="POST">
                <?= csrf_field() ?>
                <div class="form-group" style="margin-bottom:8px">
                    <label style="font-size:12px;font-weight:600;color:#991b1b">Catatan Pengembalian <span style="color:#ef4444">*</span></label>
                    <textarea name="catatan_review" class="form-control" rows="2" placeholder="Tuliskan alasan atau hal yang perlu diperbaiki..." required></textarea>
                </div>
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-rotate-left"></i> Konfirmasi Kembalikan</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

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
<!-- TAHAP 1 — IKHTISAR (berbasis Prosedur PKA)             -->
<!-- ═══════════════════════════════════════════════════════ -->
<?php
// Group ikhtisar yang sudah ada berdasarkan pka_id
$ikhtisarByPka  = [];
$ikhtisarBebas  = [];
foreach ($ikhtisar as $ikh) {
    if ($ikh['pka_id']) $ikhtisarByPka[$ikh['pka_id']][] = $ikh;
    else                $ikhtisarBebas[] = $ikh;
}
$totalIkhtisar = count($ikhtisar);
?>
<div class="card mb-3">
    <div class="card-header" style="background:<?= $stageStatus['ikhtisar'] ? 'linear-gradient(135deg,#059669,#10b981)' : 'linear-gradient(135deg,#2563eb,#3b82f6)' ?>;color:#fff">
        <h3 class="card-title" style="color:#fff"><i class="fas fa-pen"></i> Tahap 1 — Ikhtisar</h3>
        <?php if ($stageStatus['ikhtisar']): ?>
        <span style="margin-left:auto;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:99px;font-size:12px"><i class="fas fa-check"></i> Selesai</span>
        <?php endif; ?>
    </div>
    <div class="card-body">

        <?php if (empty($pkaList)): ?>
        <div style="background:#fef3c7;border-radius:8px;padding:12px 16px;font-size:12px;color:#92400e;margin-bottom:12px">
            <i class="fas fa-triangle-exclamation"></i> PKA (Program Kerja Audit) belum dibuat oleh Ketua Tim.
            Anda masih bisa tambah observasi bebas di bawah.
        </div>
        <?php else: ?>

        <!-- Prosedur PKA sebagai sumber ikhtisar -->
        <?php foreach ($pkaList as $pka): ?>
        <div style="border:1px solid #e0f2fe;border-radius:8px;margin-bottom:12px;overflow:hidden">
            <!-- Header PKA -->
            <div style="background:#f0f9ff;padding:10px 16px;display:flex;align-items:center;gap:10px">
                <div style="background:#0ea5e9;color:#fff;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0">
                    <?= $pka['nomor_urut'] ?>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;font-size:13px;color:#0c4a6e"><?= esc($pka['uraian_prosedur']) ?></div>
                    <div style="font-size:11px;color:#64748b">
                        Rencana: <?= $pka['rencana_waktu'] ?? '—' ?> JP
                        <?php if ($pka['pic_nama']): ?> &nbsp;|&nbsp; PIC: <?= esc($pka['pic_nama']) ?><?php endif; ?>
                    </div>
                </div>
                <?php if ($canEditIkhtisar): ?>
                <button class="btn btn-xs btn-outline-primary" onclick="toggleAddIkh(<?= $pka['id'] ?>)">
                    <i class="fas fa-plus"></i> Tambah Hasil
                </button>
                <?php endif; ?>
            </div>

            <!-- Existing ikhtisar untuk PKA ini -->
            <?php foreach ($ikhtisarByPka[$pka['id']] ?? [] as $ikh): ?>
            <div style="padding:12px 16px;border-top:1px solid #e0f2fe">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
                    <div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div>
                            <div style="font-size:10px;font-weight:600;color:#64748b;margin-bottom:2px">HASIL OBSERVASI</div>
                            <div style="font-size:13px;white-space:pre-line"><?= esc($ikh['hasil_observasi'] ?: '—') ?></div>
                        </div>
                        <div>
                            <div style="font-size:10px;font-weight:600;color:#64748b;margin-bottom:2px">SIMPULAN</div>
                            <div style="font-size:13px;white-space:pre-line"><?= esc($ikh['simpulan'] ?: '—') ?></div>
                        </div>
                    </div>
                    <?php if ($canEditIkhtisar): ?>
                    <div style="display:flex;gap:4px;flex-shrink:0">
                        <button class="btn btn-xs btn-outline-primary" onclick="toggleEditIkhtisar(<?= $ikh['id'] ?>)"><i class="fas fa-edit"></i></button>
                        <form action="/admin/kka/ikhtisar/<?= $ikh['id'] ?>/delete" method="POST" style="display:inline" onsubmit="return confirm('Hapus?')">
                            <?= csrf_field() ?><button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <!-- Edit inline -->
                <?php if ($canEditIkhtisar): ?>
                <div id="edit-ikh-<?= $ikh['id'] ?>" style="display:none;margin-top:10px;padding:12px;background:#fffbeb;border-radius:6px">
                    <form action="/admin/kka/ikhtisar/<?= $ikh['id'] ?>/update" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="pka_id" value="<?= $ikh['pka_id'] ?>">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                            <div class="form-group"><label class="form-label">Hasil Observasi</label>
                                <textarea name="hasil_observasi" class="form-control" rows="3"><?= esc($ikh['hasil_observasi']) ?></textarea></div>
                            <div class="form-group"><label class="form-label">Simpulan</label>
                                <textarea name="simpulan" class="form-control" rows="3"><?= esc($ikh['simpulan']) ?></textarea></div>
                        </div>
                        <div style="display:flex;gap:6px;margin-top:6px">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Simpan</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditIkhtisar(<?= $ikh['id'] ?>)">Batal</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php if (empty($ikhtisarByPka[$pka['id']])): ?>
            <div style="padding:8px 16px;font-size:11px;color:#94a3b8;border-top:1px solid #e0f2fe">Belum ada hasil observasi untuk prosedur ini.</div>
            <?php endif; ?>

            <!-- Form tambah ikhtisar untuk PKA ini -->
            <?php if ($canEditIkhtisar): ?>
            <div id="add-ikh-<?= $pka['id'] ?>" class="d-none" style="padding:12px 16px;background:#eff6ff;border-top:1px solid #bae6fd">
                <form action="/admin/kka/<?= $kka['id'] ?>/ikhtisar/store" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="pka_id" value="<?= $pka['id'] ?>">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div class="form-group"><label class="form-label">Hasil Observasi <span class="req">*</span></label>
                            <textarea name="hasil_observasi" class="form-control" rows="3" placeholder="Fakta yang ditemukan di lapangan..." required></textarea></div>
                        <div class="form-group"><label class="form-label">Simpulan Ikhtisar</label>
                            <textarea name="simpulan" class="form-control" rows="3" placeholder="Simpulan dari observasi ini..."></textarea></div>
                    </div>
                    <div style="display:flex;gap:6px;margin-top:6px">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Tambahkan</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAddIkh(<?= $pka['id'] ?>)">Batal</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; // end pkaList ?>

        <!-- Observasi Bebas (tidak terikat prosedur PKA) -->
        <?php foreach ($ikhtisarBebas as $ikh): ?>
        <div style="border:1px solid #e2e8f0;border-radius:8px;margin-bottom:10px;overflow:hidden">
            <div style="background:#f8fafc;padding:8px 16px;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:12px;color:#64748b"><i class="fas fa-pen-to-square"></i> Observasi Tambahan #<?= $ikh['nomor_urut'] ?></span>
                <?php if ($canEditIkhtisar): ?>
                <div style="display:flex;gap:4px">
                    <button class="btn btn-xs btn-outline-primary" onclick="toggleEditIkhtisar(<?= $ikh['id'] ?>)"><i class="fas fa-edit"></i></button>
                    <form action="/admin/kka/ikhtisar/<?= $ikh['id'] ?>/delete" method="POST" style="display:inline" onsubmit="return confirm('Hapus?')">
                        <?= csrf_field() ?><button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <div style="padding:12px 16px;display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div><div style="font-size:10px;font-weight:600;color:#64748b;margin-bottom:2px">PROGRAM KERJA</div>
                    <div style="font-size:13px;white-space:pre-line"><?= esc($ikh['program_kerja'] ?: '—') ?></div></div>
                <div><div style="font-size:10px;font-weight:600;color:#64748b;margin-bottom:2px">HASIL OBSERVASI</div>
                    <div style="font-size:13px;white-space:pre-line"><?= esc($ikh['hasil_observasi'] ?: '—') ?></div></div>
            </div>
            <?php if ($canEditIkhtisar): ?>
            <div id="edit-ikh-<?= $ikh['id'] ?>" style="display:none;padding:12px 16px;background:#fffbeb">
                <form action="/admin/kka/ikhtisar/<?= $ikh['id'] ?>/update" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="pka_id" value="">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div class="form-group"><label class="form-label">Program Kerja</label>
                            <textarea name="program_kerja" class="form-control" rows="2"><?= esc($ikh['program_kerja']) ?></textarea></div>
                        <div class="form-group"><label class="form-label">Hasil Observasi</label>
                            <textarea name="hasil_observasi" class="form-control" rows="2"><?= esc($ikh['hasil_observasi']) ?></textarea></div>
                        <div class="form-group"><label class="form-label">Simpulan</label>
                            <textarea name="simpulan" class="form-control" rows="2"><?= esc($ikh['simpulan']) ?></textarea></div>
                    </div>
                    <div style="display:flex;gap:6px;margin-top:6px">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditIkhtisar(<?= $ikh['id'] ?>)">Batal</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <!-- Tambah Observasi Bebas -->
        <?php if ($canEditIkhtisar): ?>
        <button class="btn btn-outline-secondary btn-sm mt-1" onclick="document.getElementById('form-add-bebas').classList.toggle('d-none')">
            <i class="fas fa-plus"></i> Tambah Observasi Bebas
        </button>
        <div id="form-add-bebas" class="d-none" style="border:1px dashed #94a3b8;border-radius:8px;padding:14px;background:#f8fafc;margin-top:8px">
            <form action="/admin/kka/<?= $kka['id'] ?>/ikhtisar/store" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="pka_id" value="">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div class="form-group"><label class="form-label">Program Kerja / Prosedur</label>
                        <textarea name="program_kerja" class="form-control" rows="2" placeholder="Uraian kegiatan..."></textarea></div>
                    <div class="form-group"><label class="form-label">Hasil Observasi <span class="req">*</span></label>
                        <textarea name="hasil_observasi" class="form-control" rows="2" placeholder="Fakta di lapangan..." required></textarea></div>
                    <div class="form-group"><label class="form-label">Simpulan</label>
                        <textarea name="simpulan" class="form-control" rows="2"></textarea></div>
                </div>
                <div style="display:flex;gap:6px;margin-top:6px">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Tambahkan</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('form-add-bebas').classList.add('d-none')">Batal</button>
                </div>
            </form>
        </div>

        <?php if ($totalIkhtisar > 0): ?>
        <div style="margin-top:16px;padding-top:16px;border-top:1px dashed #e2e8f0;text-align:right">
            <form action="/admin/kka/<?= $kka['id'] ?>/ikhtisar/selesai" method="POST"
                  onsubmit="return confirm('Tandai ikhtisar selesai? Ikhtisar tidak bisa diubah lagi.')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Selesaikan Ikhtisar</button>
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
                <div>
                    <strong style="font-size:13px;color:#1e293b">Temuan #<?= $s['nomor_urut'] ?></strong>
                    <?php if ($s['kode_temuan_kode']): ?>
                    <span class="badge badge-warning" style="margin-left:6px;font-size:10px"><?= esc($s['kode_temuan_kode']) ?></span>
                    <?php endif; ?>
                    <?php if ($s['nilai_financial']): ?>
                    <span style="margin-left:6px;font-size:11px;color:#dc2626">Rp <?= number_format($s['nilai_financial'],0,',','.') ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($canEditSimpulan): ?>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-xs btn-outline-primary" onclick="toggleEditSimpulan(<?= $s['id'] ?>)"><i class="fas fa-edit"></i></button>
                    <form action="/admin/kka/simpulan/<?= $s['id'] ?>/delete" method="POST" style="display:inline" onsubmit="return confirm('Hapus?')">
                        <?= csrf_field() ?><button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <div id="view-sp-<?= $s['id'] ?>" style="padding:14px 16px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <?php foreach (['kondisi'=>'Kondisi/Temuan','kriteria'=>'Kriteria','sebab'=>'Sebab','akibat'=>'Akibat','rekomendasi_awal'=>'Rekomendasi Awal'] as $field => $label): ?>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px"><?= strtoupper($label) ?></div>
                        <div style="font-size:13px;white-space:pre-line"><?= esc($s[$field] ?: '—') ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($s['kode_temuan_uraian']): ?>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px">KODE TEMUAN</div>
                        <div style="font-size:13px"><?= esc($s['kode_temuan_kode']) ?> — <?= esc($s['kode_temuan_uraian']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($canEditSimpulan): ?>
            <div id="edit-sp-<?= $s['id'] ?>" style="display:none;padding:14px 16px;background:#fffbeb;border-top:1px solid #fde68a">
                <form action="/admin/kka/simpulan/<?= $s['id'] ?>/update" method="POST">
                    <?= csrf_field() ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <?php foreach (['kondisi'=>'Kondisi/Temuan','kriteria'=>'Kriteria','sebab'=>'Sebab','akibat'=>'Akibat','rekomendasi_awal'=>'Rekomendasi Awal'] as $field => $label): ?>
                        <div class="form-group"><label class="form-label"><?= $label ?></label>
                            <textarea name="<?= $field ?>" class="form-control" rows="3"><?= esc($s[$field]) ?></textarea></div>
                        <?php endforeach; ?>
                        <div class="form-group"><label class="form-label">Kode Temuan</label>
                            <select name="kode_temuan_id" class="form-control">
                                <option value="">— Pilih —</option>
                                <?php foreach ($kodeTemuanList as $kt): ?>
                                <option value="<?= $kt['id'] ?>" <?= $s['kode_temuan_id'] == $kt['id'] ? 'selected' : '' ?>>
                                    <?= esc($kt['kode']) ?> | <?= esc(mb_strimwidth($kt['uraian'],0,60,'...')) ?>
                                </option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="form-group"><label class="form-label">Nilai Financial (Rp)</label>
                            <input type="number" name="nilai_financial" class="form-control" value="<?= $s['nilai_financial'] ?>" placeholder="0 jika tidak ada"></div>
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
                    <?php foreach (['kondisi'=>['Kondisi/Temuan','Fakta/kondisi yang ditemukan...'],'kriteria'=>['Kriteria','Standar/aturan yang berlaku...'],'sebab'=>['Sebab','Penyebab terjadinya kondisi...'],'akibat'=>['Akibat','Dampak yang ditimbulkan...'],'rekomendasi_awal'=>['Rekomendasi Awal','Saran perbaikan awal...']] as $field => [$label, $ph]): ?>
                    <div class="form-group"><label class="form-label"><?= $label ?></label>
                        <textarea name="<?= $field ?>" class="form-control" rows="3" placeholder="<?= $ph ?>"></textarea></div>
                    <?php endforeach; ?>
                    <div class="form-group"><label class="form-label">Kode Temuan</label>
                        <select name="kode_temuan_id" class="form-control">
                            <option value="">— Pilih Kode Temuan —</option>
                            <?php foreach ($kodeTemuanList as $kt): ?>
                            <option value="<?= $kt['id'] ?>"><?= esc($kt['kode']) ?> | <?= esc(mb_strimwidth($kt['uraian'],0,60,'...')) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="form-group"><label class="form-label">Nilai Financial (Rp)</label>
                        <input type="number" name="nilai_financial" class="form-control" placeholder="Kosongkan jika tidak ada"></div>
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
                    <div class="form-group"><label class="form-label">Uraian Rekomendasi <span class="req">*</span></label>
                        <textarea name="uraian_rekomendasi" class="form-control" rows="3" placeholder="Tindak lanjut yang direkomendasikan..." required></textarea></div>
                    <div class="form-group"><label class="form-label">Kode Rekomendasi</label>
                        <input type="text" name="kode_rekomendasi" class="form-control" placeholder="Contoh: 01"></div>
                    <div class="form-group"><label class="form-label">Nilai Rekomendasi (Rp)</label>
                        <input type="number" name="nilai_rekomendasi_financial" class="form-control" placeholder="Kosongkan jika tidak ada"></div>
                    <div class="form-group"><label class="form-label">Pihak Bertanggung Jawab</label>
                        <input type="text" name="pihak_bertanggung_jawab" class="form-control" placeholder="Nama/jabatan..."></div>
                    <div class="form-group"><label class="form-label">Target Penyelesaian</label>
                        <input type="date" name="target_penyelesaian" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Tanggapan Auditi</label>
                        <textarea name="tanggapan_auditi" class="form-control" rows="3" placeholder="Tanggapan dari pihak auditi..."></textarea></div>
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
function toggleAddIkh(pkaId) {
    var el = document.getElementById('add-ikh-'+pkaId);
    if (el) el.classList.toggle('d-none');
}
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
