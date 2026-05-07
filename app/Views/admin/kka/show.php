<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$statusKka   = $kka['status_kka'] ?? 'draft';
$kkaSelesai  = $kka['status'] === 'selesai';
$kkaApproved = $statusKka === 'approved';
$isEditable  = $canEdit; // passed from controller: canEditKka && status != selesai && !approved

// Progress: berapa prosedur yang sudah diisi (ada ikhtisar-nya)
$totalProsedur  = count($prosedurData);
$terisiCount    = count(array_filter($prosedurData, fn($p) => $p['ikhtisar'] !== null));
$temuanCount    = count(array_filter($prosedurData, fn($p) => $p['simpulan']  !== null));
?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-file-pen"></i> KKA — <?= esc($kka['nama']) ?></h1>
        <p>
            SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?>
            &nbsp;|&nbsp; Peran: <strong><?= esc($kka['peran_spt'] ?: 'Anggota Tim') ?></strong>
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

<?php
$skColors = [
    'draft'     => ['bg'=>'#f8fafc','border'=>'#cbd5e1','icon'=>'clock',         'iconColor'=>'#64748b','title'=>'Belum Dikirim ke Ketua Tim','titleColor'=>'#374151'],
    'submitted' => ['bg'=>'#fffbeb','border'=>'#fbbf24','icon'=>'paper-plane',   'iconColor'=>'#d97706','title'=>'Menunggu Review Ketua Tim','titleColor'=>'#92400e'],
    'approved'  => ['bg'=>'#f0fdf4','border'=>'#22c55e','icon'=>'circle-check',  'iconColor'=>'#16a34a','title'=>'Disetujui Ketua Tim','titleColor'=>'#166534'],
    'rejected'  => ['bg'=>'#fef2f2','border'=>'#ef4444','icon'=>'circle-xmark',  'iconColor'=>'#dc2626','title'=>'Dikembalikan — Perlu Perbaikan','titleColor'=>'#991b1b'],
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
                    <?php if ($statusKka === 'submitted' && !empty($kka['submitted_at'])): ?>
                        <div style="font-size:11px;color:#64748b;margin-top:2px">Dikirim <?= date('d M Y H:i', strtotime($kka['submitted_at'])) ?></div>
                    <?php elseif (in_array($statusKka,['approved','rejected']) && !empty($kka['reviewed_at'])): ?>
                    <div style="font-size:11px;color:#64748b;margin-top:2px">Direview <?= date('d M Y H:i', strtotime($kka['reviewed_at'])) ?></div>
                <?php endif; ?>
                <?php if (!empty($kka['catatan_review']) && in_array($statusKka,['approved','rejected'])): ?>
                <div style="margin-top:6px;font-size:12px;padding:6px 10px;border-radius:6px;background:rgba(0,0,0,.05)">
                    <strong>Catatan KT:</strong> <?= esc($kka['catatan_review']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <?php if ($isAt && $statusKka === 'rejected'): ?>
            <form action="/admin/kka/<?= $kka['id'] ?>/reopen" method="POST"
              data-confirm="Status KKA akan direset ke <b>Draft</b> agar Anda dapat mengedit dan mengirim ulang."
              data-confirm-title="Buka untuk Diperbaiki?"
              data-confirm-btn="Ya, Buka Kembali">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-rotate-left"></i> Buka untuk Diperbaiki</button>
          </form>
      <?php endif; ?>
      <?php if ($isAt && $kkaSelesai && in_array($statusKka,['draft','rejected'])): ?>
        <form action="/admin/kka/<?= $kka['id'] ?>/submit" method="POST"
          data-confirm="KKA ini akan dikirim ke <b>Ketua Tim</b> untuk direview. Pastikan semua isian sudah lengkap."
          data-confirm-title="Kirim ke Ketua Tim?"
          data-confirm-btn="<i class='fas fa-paper-plane'></i>&nbsp;Kirim Sekarang">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> Kirim ke Ketua Tim</button>
      </form>
  <?php endif; ?>
  <?php if ($isKt && $statusKka === 'submitted'): ?>
    <button class="btn btn-success btn-sm" onclick="togglePanel('panel-approve')"><i class="fas fa-check"></i> Setujui</button>
    <button class="btn btn-danger  btn-sm" onclick="togglePanel('panel-reject')"><i class="fas fa-rotate-left"></i> Kembalikan</button>
<?php endif; ?>
</div>
</div>
<?php if ($isKt && $statusKka === 'submitted'): ?>
    <div id="panel-approve" class="d-none box-success" style="margin-top:12px">
        <form action="/admin/kka/<?= $kka['id'] ?>/approve" method="POST">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom:8px">
                <label style="font-size:12px;font-weight:600;color:#166534">Catatan (opsional)</label>
                <textarea name="catatan_review" class="form-control" rows="2" placeholder="Tambahkan catatan persetujuan..."></textarea>
            </div>
            <button class="btn btn-success btn-sm"><i class="fas fa-circle-check"></i> Konfirmasi Setujui</button>
        </form>
    </div>
    <div id="panel-reject" class="d-none box-danger" style="margin-top:12px">
        <form action="/admin/kka/<?= $kka['id'] ?>/reject" method="POST">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom:8px">
                <label style="font-size:12px;font-weight:600;color:#991b1b">Alasan Pengembalian <span style="color:#ef4444">*</span></label>
                <textarea name="catatan_review" class="form-control" rows="2" required placeholder="Tuliskan hal yang perlu diperbaiki..."></textarea>
            </div>
            <button class="btn btn-danger btn-sm"><i class="fas fa-rotate-left"></i> Konfirmasi Kembalikan</button>
        </form>
    </div>
<?php endif; ?>
</div>
</div>

<div class="card mb-3" style="background:linear-gradient(135deg,#1e293b,#334155);color:#fff">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;opacity:.85">
                    <span><i class="fas fa-tasks"></i> Prosedur Terisi</span>
                    <span><strong><?= $terisiCount ?>/<?= $totalProsedur ?></strong></span>
                </div>
                <div style="background:rgba(255,255,255,.2);border-radius:99px;height:8px;overflow:hidden">
                    <?php $pct = $totalProsedur > 0 ? round(($terisiCount/$totalProsedur)*100) : 0; ?>
                    <div style="background:#34d399;height:100%;width:<?= $pct ?>%;border-radius:99px;transition:width .4s"></div>
                </div>
            </div>
            <div style="display:flex;gap:20px">
                <div style="text-align:center">
                    <div style="font-size:22px;font-weight:700;color:#34d399"><?= $terisiCount ?></div>
                    <div style="font-size:10px;opacity:.7">Terisi</div>
                </div>
                <div style="text-align:center">
                    <div style="font-size:22px;font-weight:700;color:#fbbf24"><?= $temuanCount ?></div>
                    <div style="font-size:10px;opacity:.7">Ada Temuan</div>
                </div>
                <div style="text-align:center">
                    <div style="font-size:22px;font-weight:700;color:#94a3b8"><?= $totalProsedur - $terisiCount ?></div>
                    <div style="font-size:10px;opacity:.7">Belum</div>
                </div>
            </div>
            <div>
                <?php
                $stLabel = $statusLabel[$kka['status']] ?? $kka['status'];
                $stColor = $statusColor[$kka['status']] ?? 'secondary';
                ?>
                <span class="badge badge-<?= $stColor ?>" style="font-size:12px;padding:5px 12px">
                    <?= $stLabel ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php if ($isDalnis || !empty($kka['catatan_dalnis'])): ?>
    <div class="card mb-3" style="border-left:4px solid #6366f1">
        <div class="card-body" style="padding:14px 20px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap">
                <div style="flex:1">
                    <div style="font-size:11px;font-weight:700;color:#6366f1;margin-bottom:6px;letter-spacing:.5px">
                        <i class="fas fa-comment-dots"></i> CATATAN DALNIS
                    </div>
                    <?php if (!empty($kka['catatan_dalnis'])): ?>
                        <div style="font-size:13px;white-space:pre-line"><?= esc($kka['catatan_dalnis']) ?></div>
                    <?php else: ?>
                        <div style="color:#94a3b8;font-style:italic;font-size:13px">Belum ada catatan.</div>
                    <?php endif; ?>
                </div>
                <?php if ($isDalnis): ?>
                    <button class="btn btn-sm btn-outline-primary" onclick="togglePanel('form-catatan-dalnis')">
                        <i class="fas fa-pen"></i> <?= !empty($kka['catatan_dalnis']) ? 'Edit' : 'Tambah' ?>
                    </button>
                <?php endif; ?>
            </div>
            <?php if ($isDalnis): ?>
                <form id="form-catatan-dalnis" class="d-none" action="/admin/kka/<?= $kka['id'] ?>/catatan-dalnis" method="POST" style="margin-top:12px">
                    <?= csrf_field() ?>
                    <textarea name="catatan_dalnis" class="form-control" rows="3" style="font-size:13px"><?= esc($kka['catatan_dalnis']) ?></textarea>
                    <div style="margin-top:8px;display:flex;gap:8px">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="togglePanel('form-catatan-dalnis')">Batal</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($prosedurData)): ?>
    <div class="card mb-3">
        <div class="card-body" style="text-align:center;padding:48px;color:#94a3b8">
            <i class="fas fa-clipboard-list" style="font-size:40px;display:block;margin-bottom:12px"></i>
            <p>Belum ada PKA (Program Kerja Audit) untuk SPT ini.</p>
            <p style="font-size:12px">Ketua Tim perlu membuat PKA terlebih dahulu.</p>
        </div>
    </div>
<?php else: ?>

    <?php foreach ($prosedurData as $idx => $pd): ?>
        <?php
        $pka      = $pd['pka'];
        $ikh      = $pd['ikhtisar'];  // existing kka_ikhtisar row or null
        $sp       = $pd['simpulan'];  // existing kka_simpulan row or null
        $filled   = $ikh !== null;
        $adaTemuan= $sp  !== null;
        $cardBg   = $filled ? ($adaTemuan ? '#fffbeb' : '#f0fdf4') : '#f8fafc';
        $cardBorder= $filled ? ($adaTemuan ? '#fbbf24' : '#22c55e') : '#e2e8f0';
        $formId   = 'form-pka-' . $pka['id'];
        ?>
        <div class="card mb-3" style="border-left:4px solid <?= $cardBorder ?>;background:<?= $cardBg ?>">

            <div class="card-header" style="background:transparent;border-bottom:1px solid <?= $cardBorder ?>44;cursor:pointer"
             onclick="toggleCard('body-pka-<?= $pka['id'] ?>')">
             <div style="display:flex;align-items:center;gap:12px">
                <div style="width:32px;height:32px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;
                background:<?= $filled ? $cardBorder : '#e2e8f0' ?>;color:<?= $filled ? '#fff' : '#94a3b8' ?>">
                <?= $idx + 1 ?>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:13px;color:#1e293b;line-height:1.5"><?= render_wysiwyg($pka['uraian_prosedur']) ?></div>
                <div style="font-size:11px;color:#64748b;margin-top:1px">
                    Rencana: <?= $pka['rencana_waktu'] ?? '—' ?> HP
                    <?php if (!empty($pka['pic_nama'])): ?>&nbsp;|&nbsp; PIC: <?= esc($pka['pic_nama']) ?><?php endif; ?>
                </div>
            </div>
            <?php if ($filled): ?>
                <?php if ($adaTemuan): ?>
                    <span style="font-size:11px;background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:99px;font-weight:600;flex-shrink:0">
                        <i class="fas fa-triangle-exclamation"></i> Ada Temuan
                    </span>
                <?php else: ?>
                    <span style="font-size:11px;background:#dcfce7;color:#166534;padding:3px 10px;border-radius:99px;font-weight:600;flex-shrink:0">
                        <i class="fas fa-check"></i> Selesai
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span style="font-size:11px;background:#f1f5f9;color:#94a3b8;padding:3px 10px;border-radius:99px;flex-shrink:0">
                    <i class="fas fa-pen"></i> Belum Diisi
                </span>
            <?php endif; ?>
            <i class="fas fa-chevron-down" id="chevron-pka-<?= $pka['id'] ?>"
               style="color:#94a3b8;font-size:12px;flex-shrink:0;transition:transform .2s"></i>
           </div>
       </div>

       <div id="body-pka-<?= $pka['id'] ?>" style="display:none">
        <div class="card-body" style="padding:16px 20px">

            <?php if ($isEditable): ?>
                <form id="<?= $formId ?>" action="/admin/kka/<?= $kka['id'] ?>/prosedur/save" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="pka_id" value="<?= $pka['id'] ?>">

                    <div class="form-group" style="margin-bottom:14px">
                        <label style="font-size:12px;font-weight:700;color:#475569;letter-spacing:.3px">
                            HASIL OBSERVASI <span style="color:#ef4444">*</span>
                            <span class="wysiwyg-badge">Rich Text</span>
                        </label>
                        <textarea name="hasil_observasi"
                        id="ta-obs-<?= $pka['id'] ?>"
                        data-wysiwyg data-wysiwyg-height="110px" class="wysiwyg-lazy"
                        placeholder="Fakta yang ditemukan di lapangan untuk prosedur ini..."><?= esc($ikh['hasil_observasi'] ?? '') ?></textarea>
                    </div>

                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px 14px;border-radius:8px;
                    background:rgba(255,255,255,.6);border:1px solid #e2e8f0;margin-bottom:12px;user-select:none">
                    <input type="checkbox" name="ada_temuan" value="1" id="chk-temuan-<?= $pka['id'] ?>"
                    style="width:18px;height:18px;flex-shrink:0;cursor:pointer"
                    <?= $adaTemuan ? 'checked' : '' ?>
                    onchange="toggleTemuan(<?= $pka['id'] ?>)">
                    <div>
                        <div style="font-weight:600;font-size:13px;color:#1e293b">Ada Temuan / Ketidaksesuaian</div>
                        <div style="font-size:11px;color:#64748b">Centang jika ditemukan penyimpangan yang perlu dicatat sebagai temuan audit</div>
                    </div>
                </label>

                <div id="blok-temuan-<?= $pka['id'] ?>" style="<?= $adaTemuan ? '' : 'display:none' ?>">
                    <div style="background:rgba(255,255,255,.7);border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin-bottom:12px">
                        <div style="font-size:11px;font-weight:700;color:#92400e;margin-bottom:12px;letter-spacing:.5px">
                            <i class="fas fa-triangle-exclamation"></i> DETAIL TEMUAN (KONDISI — KRITERIA — SEBAB — AKIBAT)
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <?php foreach(['kondisi'=>['Kondisi / Temuan','Fakta/kondisi yang menyimpang...'],
                               'kriteria'=>['Kriteria','Standar/aturan yang berlaku...'],
                               'sebab'   =>['Sebab','Penyebab terjadinya kondisi...'],
                               'akibat'  =>['Akibat','Dampak yang ditimbulkan...']] as $f => [$lbl, $ph]): ?>
                               <div class="form-group">
                                <label style="font-size:11px;font-weight:700;color:#64748b">
                                    <?= strtoupper($lbl) ?>
                                    <span class="wysiwyg-badge">Rich Text</span>
                                </label>
                                <textarea name="<?= $f ?>"
                                  id="ta-<?= $f ?>-<?= $pka['id'] ?>"
                                  data-wysiwyg data-wysiwyg-height="90px" class="wysiwyg-lazy"
                                  placeholder="<?= $ph ?>"><?= esc($sp[$f] ?? '') ?></textarea>
                              </div>
                          <?php endforeach; ?>
                      </div>
                      <div class="form-group" style="margin-top:12px">
                        <label style="font-size:11px;font-weight:700;color:#64748b">
                            REKOMENDASI AWAL
                            <span class="wysiwyg-badge">Rich Text</span>
                        </label>
                        <textarea name="rekomendasi_awal"
                        id="ta-rek-<?= $pka['id'] ?>"
                        data-wysiwyg data-wysiwyg-height="80px" class="wysiwyg-lazy"
                        placeholder="Saran tindak lanjut yang direkomendasikan..."><?= esc($sp['rekomendasi_awal'] ?? '') ?></textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px">
                        <div class="form-group">
                            <label style="font-size:11px;font-weight:700;color:#64748b">KODE TEMUAN</label>
                            <select name="kode_temuan_id" class="form-control">
                                <option value="">— Pilih kode —</option>
                                <?php foreach ($kodeTemuanList as $kt): ?>
                                    <option value="<?= $kt['id'] ?>" <?= ($sp['kode_temuan_id'] ?? '') == $kt['id'] ? 'selected' : '' ?>>
                                        <?= esc($kt['kode']) ?> — <?= esc(mb_strimwidth($kt['uraian'],0,55,'…')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="font-size:11px;font-weight:700;color:#64748b">NILAI TEMUAN (Rp)</label>
                            <input type="number" name="nilai_financial" class="form-control"
                            value="<?= $sp['nilai_financial'] ?? '' ?>"
                            placeholder="Kosongkan jika tidak ada">
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:14px;padding:12px 14px;background:rgba(255,255,255,.7);border:1px solid #e2e8f0;border-radius:8px">
                <div style="font-size:11px;font-weight:700;color:#475569;margin-bottom:8px;letter-spacing:.3px">
                    <i class="fas fa-paperclip"></i> DOKUMEN BUKTI / LAMPIRAN
                </div>

                <?php
                    // Tampilkan dokumen yang sudah diupload untuk prosedur ini
                $ikhtisarId = $ikh['id'] ?? null;
                $dokList    = $ikhtisarId ? ($dokumenByIkhtisar[$ikhtisarId] ?? []) : [];
                ?>
                <?php if (!empty($dokList)): ?>
                    <div style="margin-bottom:10px">
                        <?php foreach ($dokList as $dok): ?>
                            <div style="display:flex;align-items:center;gap:8px;padding:6px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:4px;font-size:12px">
                                <i class="fas fa-file" style="color:#64748b;flex-shrink:0"></i>
                                <span style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= esc($dok['nama_file']) ?>">
                                    <?= esc($dok['nama_file']) ?>
                                </span>
                                <span style="color:#94a3b8;flex-shrink:0"><?= $dok['ukuran'] ? number_format($dok['ukuran']/1024, 1) . ' KB' : '' ?></span>
                                <a href="/admin/kka/dokumen/<?= $dok['id'] ?>/download" target="_blank"
                                   class="btn-action btn-action-download icon-only">
                                   <i class="fas fa-download"></i>
                               </a>
                               <form action="/admin/kka/dokumen/<?= $dok['id'] ?>/hapus" method="POST" style="margin:0"
                                  onsubmit="return confirm('Hapus file ini?')">
                                  <?= csrf_field() ?>
                                  <button type="submit" class="btn-action btn-action-delete icon-only">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px">
                    Tambah file (PDF, Word, Excel, Gambar — maks. 10MB per file)
                </label>
                <input type="file" name="bukti_dokumen[]" multiple
                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                class="form-control" style="font-size:12px">
                <input type="text" name="keterangan_dokumen" class="form-control" style="margin-top:6px;font-size:12px"
                placeholder="Keterangan dokumen (opsional)">
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> Simpan Prosedur
            </button>
        </div>
    </form>

<?php else: ?>
    <?php if ($filled): ?>
        <div style="font-size:13px;white-space:pre-line;margin-bottom:<?= $adaTemuan ? '14px' : '0' ?>">
            <span style="font-size:10px;font-weight:700;color:#64748b;display:block;margin-bottom:4px">HASIL OBSERVASI</span>
            <?= esc($ikh['hasil_observasi'] ?? '—') ?>
        </div>
        <?php if ($adaTemuan): ?>
            <div class="box-warning">
                <div style="font-size:10px;font-weight:700;color:#92400e;margin-bottom:10px">DETAIL TEMUAN</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <?php foreach(['kondisi'=>'Kondisi','kriteria'=>'Kriteria','sebab'=>'Sebab','akibat'=>'Akibat','rekomendasi_awal'=>'Rekomendasi'] as $f => $lbl): ?>
                        <?php if (!empty($sp[$f])): ?>
                            <div>
                                <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:2px"><?= strtoupper($lbl) ?></div>
                                <div style="font-size:12px;white-space:pre-line"><?= esc($sp[$f]) ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!empty($sp['kode_temuan_kode'])): ?>
                        <div>
                            <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:2px">KODE TEMUAN</div>
                            <div style="font-size:12px"><?= esc($sp['kode_temuan_kode']) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($sp['nilai_financial'])): ?>
                        <div>
                            <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:2px">NILAI</div>
                            <div style="font-size:12px;color:#dc2626;font-weight:600">Rp <?= number_format($sp['nilai_financial'],0,',','.') ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
            // Tampilkan dokumen pada mode view-only
        $ikhtisarIdView = $ikh['id'] ?? null;
        $dokListView    = $ikhtisarIdView ? ($dokumenByIkhtisar[$ikhtisarIdView] ?? []) : [];
        if (!empty($dokListView)): ?>
            <div style="margin-top:12px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:6px;letter-spacing:.3px">
                    <i class="fas fa-paperclip"></i> DOKUMEN BUKTI
                </div>
                <?php foreach ($dokListView as $dok): ?>
                    <div style="display:flex;align-items:center;gap:8px;padding:5px 8px;border-radius:5px;background:#fff;border:1px solid #f1f5f9;margin-bottom:3px;font-size:12px">
                        <i class="fas fa-file" style="color:#64748b;flex-shrink:0"></i>
                        <span style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            <?= esc($dok['nama_file']) ?>
                        </span>
                        <a href="/admin/kka/dokumen/<?= $dok['id'] ?>/download" target="_blank"
                           class="btn-action btn-action-download" style="flex-shrink:0">
                           <i class="fas fa-download"></i> Unduh
                       </a>
                   </div>
               <?php endforeach; ?>
           </div>
       <?php endif; ?>

   <?php else: ?>
    <div style="text-align:center;padding:24px;color:#94a3b8;font-size:13px">
        <i class="fas fa-clock" style="display:block;font-size:24px;margin-bottom:6px"></i>
        Prosedur ini belum diisi.
    </div>
<?php endif; ?>
<?php endif; // isEditable ?>

</div>
</div>
</div>
<?php endforeach; ?>

<?php endif; // prosedurData ?>

<?php if ($isEditable && $terisiCount > 0): ?>
    <div style="text-align:right;margin-top:4px;margin-bottom:24px">
        <form action="/admin/kka/<?= $kka['id'] ?>/selesaikan" method="POST"
          data-confirm="Setelah selesai, Anda <b>tidak bisa mengedit</b> lagi dan dapat mengirimkan ke Ketua Tim."
          data-confirm-title="Selesaikan KKA?"
          data-confirm-btn="<i class='fas fa-check-double'></i>&nbsp;Ya, Selesaikan">
          <?= csrf_field() ?>
          <div style="font-size:12px;color:#64748b;margin-bottom:8px">
            <?= $terisiCount ?>/<?= $totalProsedur ?> prosedur sudah diisi
            <?php if ($terisiCount < $totalProsedur): ?>
                &nbsp;<span style="color:#f59e0b"><i class="fas fa-triangle-exclamation"></i> <?= $totalProsedur - $terisiCount ?> belum diisi</span>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-check-double"></i> Selesaikan KKA
        </button>
    </form>
</div>
<?php endif; ?>

<?php if ($isAt && $statusKka === 'rejected'): ?>
    <div style="padding:16px 20px;background:#fef3c7;border:1px solid #fbbf24;border-radius:12px;margin-bottom:20px;display:flex;gap:14px;align-items:flex-start">
        <i class="fas fa-triangle-exclamation" style="font-size:22px;color:#d97706;margin-top:2px"></i>
        <div>
            <div style="font-weight:700;color:#92400e;margin-bottom:4px">KKA Dikembalikan — Perlu Diperbaiki</div>
            <div style="font-size:12px;color:#78350f;line-height:1.6">
                Klik <strong>"Buka untuk Diperbaiki"</strong> di atas agar status KKA direset ke Draft,
                kemudian perbaiki isian prosedur sesuai catatan KT, lalu selesaikan dan kirim ulang.
            </div>
        </div>
    </div>
<?php elseif ($kkaSelesai && !$kkaApproved && in_array($statusKka, ['draft','rejected'])): ?>
    <div style="text-align:center;padding:20px;background:#f0fdf4;border:1px solid #86efac;border-radius:12px;margin-bottom:20px">
        <i class="fas fa-check-circle" style="font-size:28px;color:#16a34a;display:block;margin-bottom:8px"></i>
        <div style="font-weight:700;color:#166534">KKA Selesai</div>
        <div style="font-size:12px;color:#64748b;margin-top:4px">Klik tombol <strong>Kirim ke Ketua Tim</strong> di atas untuk meminta review.</div>
    </div>
<?php elseif ($kkaApproved): ?>
    <div style="text-align:center;padding:20px;background:linear-gradient(135deg,#059669,#10b981);border-radius:12px;color:#fff;margin-bottom:20px">
        <i class="fas fa-trophy" style="font-size:28px;display:block;margin-bottom:8px"></i>
        <div style="font-size:16px;font-weight:700">KKA Disetujui Ketua Tim</div>
        <div style="font-size:12px;opacity:.9;margin-top:4px">Kertas kerja audit telah selesai dan diverifikasi.</div>
    </div>
<?php endif; ?>

<script>
    function togglePanel(id) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('d-none');
    }

    function toggleCard(bodyId) {
        const body = document.getElementById(bodyId);
        const chevron = document.getElementById(bodyId.replace('body-pka-','chevron-pka-'));
        if (!body) return;
        const hidden = body.style.display === 'none';
        body.style.display = hidden ? '' : 'none';
        if (chevron) chevron.style.transform = hidden ? 'rotate(180deg)' : '';
        
        // Init Quill editor secara lazy saat card di-expand manual
        if (hidden && typeof initWysiwyg === 'function') {
            initWysiwyg(body);
        }
    }

    function toggleTemuan(pkaId) {
        const chk  = document.getElementById('chk-temuan-' + pkaId);
        const blok = document.getElementById('blok-temuan-' + pkaId);
        if (blok) blok.style.display = chk.checked ? '' : 'none';
    }

    // ⬇️ PERUBAHAN: Script Auto-expand HANYA untuk form yang sudah terisi ⬇️
    document.addEventListener('DOMContentLoaded', function() {
        
        <?php foreach ($prosedurData as $pd): ?>
            <?php if ($pd['ikhtisar'] !== null): ?>
                (function(){
                    var body = document.getElementById('body-pka-<?= $pd['pka']['id'] ?>');
                    var chev = document.getElementById('chevron-pka-<?= $pd['pka']['id'] ?>');
                    
                    if (body) { 
                        // Buka panel
                        body.style.display = ''; 
                        
                        // Load quill langsung untuk panel yang terbuka ini
                        if (typeof initWysiwyg === 'function') {
                            initWysiwyg(body);
                        }
                    }
                    if (chev) { 
                        chev.style.transform = 'rotate(180deg)'; 
                    }
                })();
            <?php endif; ?>
        <?php endforeach; ?>

    });
</script>

<?= $this->endSection() ?>