<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$sv       = $tl['status_verifikasi'];
$colors   = $verifikasiColor;
$labels   = $verifikasiLabel;
$canVerif = $sv === 'menunggu';
?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-clipboard-check"></i> Detail Tindak Lanjut</h1>
        <p>
            <?= esc($tl['entitas_nama']) ?> —
            SPT: <?= esc($tl['spt_nomor']) ?>
        </p>
    </div>
    <div class="page-actions">
        <a href="/admin/tl" class="btn btn-secondary">
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

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
<div>

<!-- Info Temuan & Rekomendasi -->
<div class="card" style="border-left:4px solid #7c3aed;margin-bottom:20px">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle" style="color:#7c3aed"></i> Temuan & Rekomendasi</h3>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Judul Temuan</div>
                <div style="font-weight:600;font-size:14px"><?= esc($tl['temuan_judul']) ?></div>
            </div>
            <?php if ($tl['nilai_temuan']): ?>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Nilai Temuan</div>
                <div style="font-weight:700;font-size:15px;color:#ef4444">Rp <?= number_format($tl['nilai_temuan'],0,',','.') ?></div>
            </div>
            <?php endif; ?>
            <?php if ($tl['kondisi']): ?>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Kondisi</div>
                <div style="font-size:13px;line-height:1.6"><?= renderContent($tl['kondisi']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($tl['sebab']): ?>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Sebab</div>
                <div style="font-size:13px;line-height:1.6"><?= renderContent($tl['sebab']) ?></div>
            </div>
            <?php endif; ?>
        </div>
        <div style="margin-top:14px;padding:14px;background:#f3f0ff;border-radius:10px;border-left:4px solid #7c3aed">
            <div style="font-size:11px;font-weight:700;color:#7c3aed;text-transform:uppercase;margin-bottom:4px">Rekomendasi APIP</div>
            <div style="font-size:13px;line-height:1.7;font-weight:500"><?= renderContent($tl['isi_rekomendasi']) ?></div>
            <?php if ($tl['batas_waktu']): ?>
            <?php $overdue = strtotime($tl['batas_waktu']) < time() && $sv !== 'diterima'; ?>
            <div style="margin-top:8px;font-size:12px;<?= $overdue?'color:#ef4444;font-weight:700':'color:#64748b' ?>">
                <i class="fas fa-calendar"></i>
                Batas Waktu: <?= date('d F Y', strtotime($tl['batas_waktu'])) ?>
                <?= $overdue ? ' — <strong>Melewati Batas!</strong>' : '' ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Isi Tindak Lanjut dari Entitas -->
<div class="card" style="border-left:4px solid #2563eb;margin-bottom:20px">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3 class="card-title"><i class="fas fa-file-lines" style="color:#2563eb"></i> Tindak Lanjut dari Entitas</h3>
        <div style="display:flex;align-items:center;gap:8px">
            <span class="badge badge-<?= $colors[$sv] ?? 'secondary' ?>" style="font-size:12px">
                <?= $labels[$sv] ?? $sv ?>
            </span>
            <span style="font-size:12px;color:#64748b">
                <?= date('d M Y H:i', strtotime($tl['created_at'])) ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div style="font-size:14px;line-height:1.8;color:#1e293b;margin-bottom:16px">
            <?= renderContent($tl['uraian']) ?>
        </div>

        <!-- Dokumen Bukti Dukung -->
        <?php if (!empty($tl['dokumen'])): ?>
        <div style="border-top:1px solid #f1f5f9;padding-top:14px">
            <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:10px">
                <i class="fas fa-paperclip"></i> Bukti Dukung (<?= count($tl['dokumen']) ?> file)
            </div>
            <?php foreach($tl['dokumen'] as $dok): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#f8fafc;border-radius:8px;margin-bottom:6px">
                <?php
                $ext = strtolower(pathinfo($dok['nama_file'], PATHINFO_EXTENSION));
                $ico = $ext === 'pdf' ? 'file-pdf' : (in_array($ext,['jpg','jpeg','png']) ? 'file-image' : (in_array($ext,['xls','xlsx']) ? 'file-excel' : 'file-word'));
                $iconColor = ['file-pdf'=>'#ef4444','file-image'=>'#10b981','file-excel'=>'#16a34a','file-word'=>'#2563eb'][$ico] ?? '#6366f1';
                ?>
                <i class="fas fa-<?= $ico ?>" style="color:<?= $iconColor ?>;font-size:18px;flex-shrink:0"></i>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= esc($dok['nama_file']) ?>
                    </div>
                    <div style="font-size:11px;color:#94a3b8"><?= round($dok['ukuran']/1024) ?> KB</div>
                </div>
                <a href="/admin/tl/dokumen/<?= $dok['id'] ?>/download" target="_blank"
                   class="btn btn-sm btn-secondary" style="flex-shrink:0;font-size:12px">
                    <i class="fas fa-download"></i> Unduh
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="font-size:13px;color:#94a3b8;font-style:italic">Tidak ada dokumen yang dilampirkan.</div>
        <?php endif; ?>

        <!-- Catatan verifikasi sebelumnya -->
        <?php if ($tl['catatan_verifikasi'] && $sv !== 'menunggu'): ?>
        <div style="margin-top:16px;padding:12px 14px;background:#fef9c3;border-left:4px solid #eab308;border-radius:6px">
            <div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;margin-bottom:4px">
                Catatan Verifikasi APIP — <?= esc($tl['verified_by_nama'] ?? '') ?>
                <?= $tl['verified_at'] ? '(' . date('d M Y H:i', strtotime($tl['verified_at'])) . ')' : '' ?>
            </div>
            <div style="font-size:13px;color:#1e293b"><?= nl2br(esc($tl['catatan_verifikasi'])) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Riwayat TL untuk rekomendasi ini -->
<?php if (count($tl['riwayat']) > 1): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Pengiriman TL (<?= count($tl['riwayat']) ?>)</h3>
    </div>
    <div class="card-body" style="padding:0">
        <?php foreach($tl['riwayat'] as $idx => $hist): ?>
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;<?= $hist['id'] === $tl['id'] ? 'background:#f0f9ff' : '' ?>">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:24px;height:24px;border-radius:50%;background:<?= ['menunggu'=>'#f59e0b','diterima'=>'#22c55e','revisi'=>'#ef4444'][$hist['status_verifikasi']] ?? '#94a3b8' ?>;display:flex;align-items:center;justify-content:center;font-size:11px;color:#fff;font-weight:700">
                        <?= count($tl['riwayat']) - $idx ?>
                    </div>
                    <span style="font-size:12px;font-weight:600;color:#64748b">
                        <?= date('d M Y H:i', strtotime($hist['created_at'])) ?>
                    </span>
                    <?php if ($hist['id'] === $tl['id']): ?>
                    <span style="font-size:10px;background:#dbeafe;color:#1d4ed8;padding:1px 7px;border-radius:10px;font-weight:700">SEDANG DILIHAT</span>
                    <?php endif; ?>
                </div>
                <?php
                $hc = ['menunggu'=>'warning','diterima'=>'success','revisi'=>'danger'][$hist['status_verifikasi']] ?? 'secondary';
                ?>
                <span class="badge badge-<?= $hc ?>" style="font-size:11px">
                    <?= $verifikasiLabel[$hist['status_verifikasi']] ?? $hist['status_verifikasi'] ?>
                </span>
            </div>
            <div style="font-size:13px;color:#334155;line-height:1.6;margin-left:32px">
                <?= nl2br(esc(mb_substr($hist['uraian'], 0, 200))) ?>
                <?= mb_strlen($hist['uraian']) > 200 ? '…' : '' ?>
            </div>
            <?php if ($hist['catatan_verifikasi']): ?>
            <div style="margin-top:8px;margin-left:32px;font-size:12px;color:#92400e;background:#fef9c3;padding:6px 10px;border-radius:6px">
                <i class="fas fa-comment-dots"></i> <?= nl2br(esc($hist['catatan_verifikasi'])) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

</div><!-- end left column -->

<!-- Sidebar kanan: Form Verifikasi -->
<div>
<?php if ($canVerif): ?>
<div class="card" style="border-top:4px solid #4f46e5;position:sticky;top:20px">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-gavel" style="color:#4f46e5"></i> Verifikasi</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/tl/<?= $tl['id'] ?>/verifikasi"
              id="form-verifikasi">
            <?= csrf_field() ?>

            <div class="form-group" style="margin-bottom:16px">
                <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:8px">
                    Keputusan Verifikasi <span style="color:#ef4444">*</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 14px;border:2px solid #e2e8f0;border-radius:10px;margin-bottom:8px;transition:.15s"
                       id="lbl-diterima" onclick="setStatus('diterima')">
                    <input type="radio" name="status_verifikasi" value="diterima" id="r-diterima" style="display:none">
                    <div style="width:36px;height:36px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-circle-check" style="color:#16a34a;font-size:16px"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:13px;color:#15803d">Terima</div>
                        <div style="font-size:11px;color:#64748b">TL dianggap memadai, rekomendasi selesai</div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 14px;border:2px solid #e2e8f0;border-radius:10px;transition:.15s"
                       id="lbl-revisi" onclick="setStatus('revisi')">
                    <input type="radio" name="status_verifikasi" value="revisi" id="r-revisi" style="display:none">
                    <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-rotate-left" style="color:#dc2626;font-size:16px"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:13px;color:#dc2626">Minta Perbaikan</div>
                        <div style="font-size:11px;color:#64748b">TL belum memadai, entitas harus revisi</div>
                    </div>
                </label>
            </div>

            <div class="form-group" style="margin-bottom:16px">
                <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px">
                    Catatan Verifikasi
                    <span id="catatan-required" style="color:#ef4444;display:none">*</span>
                    <span style="font-weight:400;color:#94a3b8;font-size:11px">(wajib jika minta perbaikan)</span>
                </label>
                <textarea name="catatan_verifikasi" id="catatan" class="form-control" rows="5"
                          placeholder="Tulis catatan untuk entitas… Contoh: Bukti SPJ yang dilampirkan belum lengkap, perlu dilengkapi dengan bukti penerimaan dan tanda tangan pejabat berwenang."></textarea>
            </div>

            <button type="submit" id="btn-submit" class="btn btn-primary w-100" disabled
                    style="justify-content:center;font-size:14px">
                <i class="fas fa-gavel"></i> Simpan Keputusan Verifikasi
            </button>
        </form>
    </div>
</div>
<?php else: ?>
<div class="card" style="border-top:4px solid <?= ['diterima'=>'#22c55e','revisi'=>'#ef4444'][$sv] ?? '#94a3b8' ?>">
    <div class="card-body" style="text-align:center;padding:24px">
        <div style="width:56px;height:56px;border-radius:50%;background:<?= ['diterima'=>'#dcfce7','revisi'=>'#fee2e2'][$sv] ?? '#f1f5f9' ?>;margin:0 auto 12px;display:flex;align-items:center;justify-content:center">
            <i class="fas fa-<?= $sv==='diterima'?'circle-check':'rotate-left' ?>"
               style="font-size:24px;color:<?= ['diterima'=>'#16a34a','revisi'=>'#dc2626'][$sv] ?? '#94a3b8' ?>"></i>
        </div>
        <div style="font-weight:700;font-size:14px;margin-bottom:4px">
            <?= $verifikasiLabel[$sv] ?? $sv ?>
        </div>
        <?php if ($tl['verified_by_nama']): ?>
        <div style="font-size:12px;color:#64748b">
            oleh <?= esc($tl['verified_by_nama']) ?><br>
            <?= $tl['verified_at'] ? date('d M Y H:i', strtotime($tl['verified_at'])) : '' ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
</div><!-- end right column -->
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let selectedStatus = null;

function setStatus(val) {
    selectedStatus = val;
    document.getElementById('r-diterima').checked = val === 'diterima';
    document.getElementById('r-revisi').checked   = val === 'revisi';

    document.getElementById('lbl-diterima').style.borderColor = val === 'diterima' ? '#16a34a' : '#e2e8f0';
    document.getElementById('lbl-diterima').style.background  = val === 'diterima' ? '#f0fdf4' : '';
    document.getElementById('lbl-revisi').style.borderColor   = val === 'revisi'   ? '#dc2626' : '#e2e8f0';
    document.getElementById('lbl-revisi').style.background    = val === 'revisi'   ? '#fff5f5' : '';

    document.getElementById('catatan-required').style.display = val === 'revisi' ? 'inline' : 'none';
    document.getElementById('btn-submit').disabled = false;
    document.getElementById('btn-submit').style.background =
        val === 'diterima' ? '#16a34a' : (val === 'revisi' ? '#dc2626' : '');
}

document.getElementById('form-verifikasi').addEventListener('submit', function(e) {
    if (!selectedStatus) { e.preventDefault(); return; }
    const catatan = document.getElementById('catatan').value.trim();
    if (selectedStatus === 'revisi' && !catatan) {
        e.preventDefault();
        Swal.fire({ icon:'warning', title:'Catatan Wajib', text:'Isi catatan terlebih dahulu sebelum meminta perbaikan.', confirmButtonColor:'#4f46e5' });
        return;
    }
    const labels = { diterima: 'Terima Tindak Lanjut', revisi: 'Minta Perbaikan' };
    const icons  = { diterima: 'question', revisi: 'warning' };
    const colors = { diterima: '#16a34a', revisi: '#dc2626' };
    e.preventDefault();
    Swal.fire({
        title: labels[selectedStatus],
        html: selectedStatus === 'diterima'
            ? 'Tindak lanjut akan ditandai <b>Diterima</b> dan rekomendasi ditutup.'
            : 'Entitas akan diberitahu untuk memperbaiki TL sesuai catatan Anda.',
        icon: icons[selectedStatus],
        showCancelButton: true,
        confirmButtonColor: colors[selectedStatus],
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-gavel"></i>&nbsp;Ya, Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then(r => { if (r.isConfirmed) { this._swalOk = true; this.submit(); } });
});
</script>
<?= $this->endSection() ?>
