<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-paper-plane"></i> <?= esc($nhp['nomor_nhp']) ?></h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?>
           — Tanggal: <?= $nhp['tanggal_nhp'] ? date('d F Y', strtotime($nhp['tanggal_nhp'])) : 'belum diisi' ?>
        </p>
    </div>
    <div class="page-actions">
        <?php
        $entitasNama  = !empty($entitasList) ? implode(', ', array_column($entitasList, 'nama')) : '(entitas belum diset)';
        $canApproveNhp = isAuditAdmin() || isDalnisInSpt($spt['id']);
        $nhpStatus    = $nhp['status'];
        ?>

        <?php /* — DRAFT: KT → Ajukan; Dalnis/Admin → Kirim Langsung — */ ?>
        <?php if ($canManage && $nhpStatus === 'draft'): ?>
            <?php if ($canApproveNhp): ?>
            <form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/kirim" style="display:inline"
                  data-confirm="NHP akan dikirim langsung ke:<br><b><?= esc($entitasNama) ?></b>"
                  data-confirm-title="Kirim ke Entitas?" data-confirm-btn="<i class='fas fa-paper-plane'></i>&nbsp;Ya, Kirim">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-info"><i class="fas fa-paper-plane"></i> Kirim ke Entitas</button>
            </form>
            <?php endif; ?>
            <form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/ajukan" style="display:inline"
                  data-confirm="NHP akan diajukan ke Pengendali Teknis/Dalnis untuk direview sebelum dikirim ke entitas."
                  data-confirm-title="Ajukan NHP?" data-confirm-btn="<i class='fas fa-share'></i>&nbsp;Ya, Ajukan">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning"><i class="fas fa-share"></i> Ajukan ke Dalnis/PJ</button>
            </form>
        <?php endif; ?>

        <?php /* — DIAJUKAN: Dalnis/Admin → Setujui & Kirim atau Kembalikan — */ ?>
        <?php if ($nhpStatus === 'diajukan'): ?>
        <div class="box-warning" style="display:flex;align-items:center;gap:10px;font-size:13px;margin-bottom:0">
            <i class="fas fa-clock"></i>
            <span>Menunggu persetujuan Dalnis/PJ</span>
            <?php if ($canApproveNhp): ?>
            <form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/kirim" style="display:inline"
                  data-confirm="Setujui dan kirim NHP ke:<br><b><?= esc($entitasNama) ?></b>"
                  data-confirm-title="Setujui & Kirim?" data-confirm-btn="<i class='fas fa-check'></i>&nbsp;Setujui & Kirim">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Setujui & Kirim</button>
            </form>
            <button type="button" class="btn btn-sm btn-danger" onclick="document.getElementById('form-kembalikan').style.display='block'">
                <i class="fas fa-undo"></i> Kembalikan
            </button>
            <?php endif; ?>
        </div>
        <?php if ($canApproveNhp): ?>
        <div id="form-kembalikan" class="box-danger" style="display:none;margin-top:8px">
            <form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/kembalikan">
                <?= csrf_field() ?>
                <div class="form-group" style="margin-bottom:8px">
                    <label style="font-size:12px;font-weight:600;color:#dc2626">Catatan untuk KT (opsional):</label>
                    <textarea name="catatan_kembalikan" class="form-control form-control-sm" rows="2"
                              placeholder="Jelaskan alasan pengembalian..."></textarea>
                </div>
                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-undo"></i> Kembalikan ke Draft</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="this.closest('#form-kembalikan').style.display='none'">Batal</button>
            </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php /* — DITANGGAPI: Selesaikan — */ ?>
        <?php if ($canManage && $nhpStatus === 'ditanggapi'): ?>
        <?php $itemsTidakSesuai = array_values(array_filter($items, fn($i) => $i['status_tanggapan'] === 'tidak_sesuai')); ?>
        <button type="button" class="btn btn-success" onclick="bukaModalSelesai()">
            <i class="fas fa-circle-check"></i> Selesaikan NHP
        </button>
        <?php endif; ?>

        <a href="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/print" target="_blank" class="btn btn-secondary">
            <i class="fas fa-print"></i> Cetak NHP
        </a>
        <a href="/admin/spt/<?= $spt['id'] ?>/nhp" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke NHP
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Info Header NHP -->
<div class="card mb-3">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:center">
            <div>
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Status</div>
                <?php $s = $nhp['status']; ?>
                <span class="badge badge-<?= $statusColor[$s] ?? 'secondary' ?>" style="font-size:13px;padding:4px 12px;margin-top:4px">
                    <?= $statusLabel[$s] ?? $s ?>
                </span>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Tanggal NHP</div>
                <div style="font-size:14px;font-weight:600;margin-top:4px">
                    <?= $nhp['tanggal_nhp'] ? date('d F Y', strtotime($nhp['tanggal_nhp'])) : '—' ?>
                </div>
            </div>
            <div style="flex:1;min-width:200px">
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Perihal</div>
                <div style="font-size:14px;margin-top:4px"><?= esc($nhp['perihal'] ?: '—') ?></div>
            </div>
            <div style="min-width:180px">
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Entitas / OPD</div>
                <div style="font-size:13px;margin-top:4px">
                    <?php if (!empty($entitasList)): ?>
                        <?php foreach($entitasList as $ent): ?>
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px">
                            <i class="fas fa-building" style="color:#6366f1;font-size:11px"></i>
                            <span style="font-weight:600"><?= esc($ent['nama']) ?></span>
                            <?php if($ent['kode']): ?><span style="font-size:10px;color:#94a3b8">(<?= esc($ent['kode']) ?>)</span><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color:#f59e0b;font-size:12px"><i class="fas fa-triangle-exclamation"></i> Belum diset di PKPT Kegiatan</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="text-align:center">
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Item Temuan</div>
                <div style="font-size:28px;font-weight:700;color:#3b82f6;margin-top:4px"><?= count($items) ?></div>
            </div>
            <div style="text-align:center">
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Tanggapan</div>
                <div style="font-size:13px;margin-top:6px">
                    <?php
                    $sesuai       = count(array_filter($items, fn($i) => $i['status_tanggapan'] === 'sesuai'));
                    $tidakSesuai  = count(array_filter($items, fn($i) => $i['status_tanggapan'] === 'tidak_sesuai'));
                    $pending      = count(array_filter($items, fn($i) => $i['status_tanggapan'] === 'pending'));
                    ?>
                    <span style="color:#10b981;font-weight:700">✓ <?= $sesuai ?></span>
                    <span style="color:#e2e8f0;margin:0 4px">|</span>
                    <span style="color:#ef4444;font-weight:700">✗ <?= $tidakSesuai ?></span>
                    <span style="color:#e2e8f0;margin:0 4px">|</span>
                    <span style="color:#94a3b8">⏳ <?= $pending ?></span>
                </div>
            </div>
        </div>
        <?php if ($nhp['catatan']): ?>
        <div style="margin-top:12px;padding:10px 14px;background:#f8fafc;border-radius:8px;font-size:13px;color:#475569">
            <i class="fas fa-sticky-note" style="color:#94a3b8"></i> <?= esc($nhp['catatan']) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Daftar Item Temuan -->
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3 class="card-title"><i class="fas fa-list-check"></i> Item Temuan dalam NHP</h3>
        <?php if ($canManage && $nhp['status'] === 'draft'): ?>
        <button class="btn btn-sm btn-primary" onclick="document.getElementById('addItemForm').style.display='block';this.style.display='none';if(typeof initWysiwyg==='function')initWysiwyg(document.getElementById('addItemForm'))">
            <i class="fas fa-plus"></i> Tambah Item
        </button>
        <?php endif; ?>
    </div>

    <!-- Form Tambah Item (tersembunyi) -->
    <?php if ($canManage && $nhp['status'] === 'draft'): ?>
    <div id="addItemForm" style="display:none;padding:16px 20px;border-bottom:2px solid #e2e8f0;background:#f8fafc">
        <form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/item/add">
            <?= csrf_field() ?>
            <div class="form-group mb-3">
                <label class="form-label" style="font-weight:600">Judul Temuan <span style="color:#ef4444">*</span></label>
                <input type="text" name="judul_temuan" class="form-control" required
                       placeholder="Judul temuan formal...">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label class="form-label">Kondisi</label>
                    <textarea name="kondisi" class="form-control" rows="3" placeholder="Uraikan kondisi temuan..."
                              data-wysiwyg data-wysiwyg-height="90px"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Kriteria</label>
                    <textarea name="kriteria" class="form-control" rows="3" placeholder="Dasar hukum/kriteria yang dilanggar..."
                              data-wysiwyg data-wysiwyg-height="90px"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Sebab</label>
                    <textarea name="sebab" class="form-control" rows="3" placeholder="Sebab terjadinya temuan..."
                              data-wysiwyg data-wysiwyg-height="90px"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Akibat</label>
                    <textarea name="akibat" class="form-control" rows="3" placeholder="Akibat/dampak temuan..."
                              data-wysiwyg data-wysiwyg-height="90px"></textarea>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px">
                <div class="form-group">
                    <label class="form-label">Rekomendasi</label>
                    <textarea name="rekomendasi" class="form-control" rows="2" placeholder="Saran rekomendasi..."
                              data-wysiwyg data-wysiwyg-height="80px"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Nilai Financial (Rp)</label>
                    <input type="number" name="nilai_temuan" class="form-control" placeholder="0" min="0">
                </div>
            </div>
            <div style="display:flex;gap:8px;margin-top:12px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Item</button>
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('addItemForm').style.display='none';document.querySelector('.btn-sm.btn-primary').style.display='inline-flex'">
                    Batal
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="card-body" style="padding:0">
        <?php if (empty($items)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8">
            <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px"></i>
            NHP ini belum memiliki item temuan.
        </div>
        <?php else: ?>
        <?php foreach ($items as $i => $item): ?>
        <div style="padding:20px;border-bottom:1px solid #f1f5f9" id="item-<?= $item['id'] ?>">
            <!-- Header item -->
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:32px;height:32px;border-radius:50%;background:#3b82f6;color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0">
                        <?= $item['nomor_urut'] ?>
                    </div>
                    <div>
                        <div style="font-size:15px;font-weight:700;color:#1e293b">
                            <?= esc($item['judul_temuan'] ?: 'Temuan #' . $item['nomor_urut']) ?>
                        </div>
                        <?php if ($item['at_nama']): ?>
                        <div style="font-size:11px;color:#94a3b8;margin-top:2px">
                            <i class="fas fa-user"></i> AT: <?= esc($item['at_nama']) ?>
                            <?php if ($item['kode_temuan_kode']): ?>
                            — <span class="badge badge-<?= ['keuangan'=>'success','kepatuhan'=>'warning','kinerja'=>'info'][$item['kode_temuan_jenis'] ?? ''] ?? 'secondary' ?>" style="font-size:10px">
                                <?= esc($item['kode_temuan_kode']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <?php $ts = $item['status_tanggapan']; ?>
                    <span class="badge badge-<?= $tanggapanColor[$ts] ?? 'secondary' ?>" style="font-size:12px">
                        <?= $tanggapanLabel[$ts] ?? $ts ?>
                    </span>
                    <?php if ($item['nilai_temuan']): ?>
                    <span style="font-size:12px;color:#ef4444;font-weight:600;white-space:nowrap">
                        Rp <?= number_format((int)$item['nilai_temuan'], 0, ',', '.') ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Detail temuan -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <?php if ($item['kondisi']): ?>
                <div>
                    <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px">Kondisi</div>
                    <div style="font-size:13px;color:#334155;line-height:1.5"><?= renderContent($item['kondisi']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($item['kriteria']): ?>
                <div>
                    <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px">Kriteria</div>
                    <div style="font-size:13px;color:#334155;line-height:1.5"><?= renderContent($item['kriteria']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($item['sebab']): ?>
                <div>
                    <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px">Sebab</div>
                    <div style="font-size:13px;color:#334155;line-height:1.5"><?= renderContent($item['sebab']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($item['akibat']): ?>
                <div>
                    <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px">Akibat</div>
                    <div style="font-size:13px;color:#334155;line-height:1.5"><?= renderContent($item['akibat']) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($item['rekomendasi']): ?>
            <div style="padding:10px 14px;background:#f0fdf4;border-left:3px solid #10b981;border-radius:4px;font-size:13px;color:#065f46;margin-bottom:12px">
                <strong>Rekomendasi:</strong> <?= renderContent($item['rekomendasi']) ?>
            </div>
            <?php endif; ?>

            <!-- Tanggapan entitas -->
            <?php if ($item['tanggapan_entitas']): ?>
            <div style="padding:10px 14px;background:#fff7ed;border-left:3px solid #f59e0b;border-radius:4px;font-size:13px;margin-bottom:12px">
                <div style="font-size:11px;font-weight:600;color:#92400e;margin-bottom:4px">
                    Tanggapan Entitas — <?= $item['tgl_tanggapan'] ? date('d/m/Y', strtotime($item['tgl_tanggapan'])) : '' ?>
                </div>
                <?= renderContent($item['tanggapan_entitas']) ?>
            </div>
            <?php endif; ?>

            <!-- Bukti dukung yang diupload entitas -->
            <?php if (!empty($item['dokumen'])): ?>
            <div style="margin-bottom:12px;padding:10px 14px;background:#f0fdf4;border-left:3px solid #10b981;border-radius:4px">
                <div style="font-size:11px;font-weight:700;color:#065f46;text-transform:uppercase;margin-bottom:8px">
                    <i class="fas fa-paperclip"></i> Bukti Dukung Entitas (<?= count($item['dokumen']) ?> file)
                </div>
                <?php foreach($item['dokumen'] as $dok): ?>
                <?php
                $ext = strtolower(pathinfo($dok['nama_file'], PATHINFO_EXTENSION));
                $ico = $ext === 'pdf' ? 'file-pdf' : (in_array($ext,['jpg','jpeg','png']) ? 'file-image' : (in_array($ext,['xls','xlsx']) ? 'file-excel' : 'file-word'));
                $iconColor = ['file-pdf'=>'#ef4444','file-image'=>'#10b981','file-excel'=>'#16a34a','file-word'=>'#2563eb'][$ico] ?? '#6366f1';
                ?>
                <div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid #d1fae5">
                    <i class="fas fa-<?= $ico ?>" style="color:<?= $iconColor ?>;font-size:15px;flex-shrink:0"></i>
                    <span style="flex:1;font-size:12px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= esc($dok['nama_file']) ?>
                    </span>
                    <span style="font-size:11px;color:#94a3b8;flex-shrink:0"><?= round($dok['ukuran']/1024) ?> KB</span>
                    <a href="/admin/nhp/item-dokumen/<?= $dok['id'] ?>/download" target="_blank"
                       class="btn btn-sm" style="font-size:11px;padding:3px 8px;flex-shrink:0;background:#10b981;color:#fff;border:none">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Form catat tanggapan -->
            <?php if ($canManage && in_array($nhp['status'], ['terkirim', 'ditanggapi'])): ?>
            <div>
                <button class="btn btn-sm btn-<?= $ts === 'pending' ? 'warning' : 'secondary' ?>"
                        style="font-size:12px"
                        onclick="toggleTanggapan(<?= $item['id'] ?>)">
                    <i class="fas fa-comment-dots"></i>
                    <?= $ts === 'pending' ? 'Catat Tanggapan' : 'Ubah Tanggapan' ?>
                </button>
                <div id="tanggapan-form-<?= $item['id'] ?>" style="display:none;margin-top:12px;padding:16px;background:#f8fafc;border-radius:8px">
                    <form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/item/<?= $item['id'] ?>/tanggapi">
                        <?= csrf_field() ?>
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-size:13px;font-weight:600">Status Tanggapan</label>
                            <div style="display:flex;gap:12px;margin-top:6px">
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 14px;border:2px solid #e2e8f0;border-radius:8px">
                                    <input type="radio" name="status_tanggapan" value="sesuai" <?= $ts === 'sesuai' ? 'checked' : '' ?>>
                                    <span style="color:#10b981;font-weight:600"><i class="fas fa-circle-check"></i> Sesuai (Tutup)</span>
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 14px;border:2px solid #e2e8f0;border-radius:8px">
                                    <input type="radio" name="status_tanggapan" value="tidak_sesuai" <?= $ts === 'tidak_sesuai' ? 'checked' : '' ?>>
                                    <span style="color:#ef4444;font-weight:600"><i class="fas fa-circle-xmark"></i> Tidak Sesuai (Masuk LHP)</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-size:13px">Tanggal Tanggapan</label>
                            <input type="date" name="tgl_tanggapan" class="form-control"
                                   value="<?= $item['tgl_tanggapan'] ?: date('Y-m-d') ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-size:13px">Uraian Tanggapan Entitas</label>
                            <textarea name="tanggapan_entitas" class="form-control" rows="3"
                                      placeholder="Uraikan tanggapan/jawaban entitas atas temuan ini..."><?= esc($item['tanggapan_entitas'] ?? '') ?></textarea>
                        </div>
                        <div style="display:flex;gap:8px">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Simpan Tanggapan
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm"
                                    onclick="document.getElementById('tanggapan-form-<?= $item['id'] ?>').style.display='none'">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ══ Modal Selesaikan NHP + Input Batas Waktu ══════════════════════ -->
<?php if ($canManage && $nhp['status'] === 'ditanggapi' && !empty($itemsTidakSesuai)): ?>
<div id="modal-selesai-nhp" style="display:none;position:fixed;inset:0;z-index:1050;
     background:rgba(15,23,42,.6);backdrop-filter:blur(3px);
     align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:620px;max-height:90vh;
                overflow:hidden;display:flex;flex-direction:column;
                box-shadow:0 20px 60px rgba(0,0,0,.3)">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,#16a34a,#15803d);padding:18px 24px;
                    display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.2);
                            display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-circle-check" style="color:#fff;font-size:18px"></i>
                </div>
                <div>
                    <div style="color:#fff;font-weight:700;font-size:15px">Selesaikan NHP</div>
                    <div style="color:rgba(255,255,255,.75);font-size:11px">
                        <?= count($itemsTidakSesuai) ?> temuan akan masuk Matriks LHP
                    </div>
                </div>
            </div>
            <button onclick="tutupModalSelesai()"
                    style="background:rgba(255,255,255,.2);border:none;border-radius:8px;
                           width:32px;height:32px;color:#fff;cursor:pointer;font-size:14px">✕</button>
        </div>

        <!-- Penjelasan -->
        <div style="background:#f0fdf4;border-bottom:1px solid #bbf7d0;padding:12px 24px;flex-shrink:0">
            <div style="font-size:12px;color:#166534;display:flex;align-items:flex-start;gap:8px">
                <i class="fas fa-info-circle" style="margin-top:1px;flex-shrink:0"></i>
                <span>Tetapkan <strong>batas waktu tindak lanjut</strong> untuk setiap temuan di bawah.
                Batas waktu digunakan untuk pengiriman notifikasi pengingat otomatis ke OPD.</span>
            </div>
        </div>

        <!-- Daftar temuan + input batas waktu -->
        <form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/selesai"
              style="flex:1;overflow-y:auto;display:flex;flex-direction:column">
            <?= csrf_field() ?>

            <div style="padding:20px 24px;flex:1;overflow-y:auto">
                <?php foreach ($itemsTidakSesuai as $idx => $item): ?>
                <div style="padding:14px 16px;background:<?= $idx % 2 ? '#f8fafc' : '#fff' ?>;
                            border:1px solid #e2e8f0;border-radius:10px;margin-bottom:10px">
                    <div style="display:flex;align-items:flex-start;gap:12px">
                        <div style="width:26px;height:26px;border-radius:50%;background:#fee2e2;
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:11px;font-weight:700;color:#dc2626;flex-shrink:0;margin-top:1px">
                            <?= $idx + 1 ?>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;font-size:13px;color:#1e293b;margin-bottom:3px">
                                <?= esc($item['judul_temuan'] ?: 'Temuan '.($idx+1)) ?>
                            </div>
                            <?php if ($item['kondisi']): ?>
                            <div style="font-size:11px;color:#64748b;line-height:1.4;margin-bottom:8px;
                                        display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                <?= render_wysiwyg($item['kondisi']) ?>
                            </div>
                            <?php endif; ?>
                            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                                <label style="font-size:11px;font-weight:700;color:#dc2626;
                                              white-space:nowrap;display:flex;align-items:center;gap:4px">
                                    <i class="fas fa-calendar-xmark"></i> Batas Waktu TL
                                    <span style="color:#94a3b8;font-weight:400">(opsional)</span>
                                </label>
                                <input type="date"
                                       name="batas_waktu[<?= $item['id'] ?>]"
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                       style="border:1px solid #fca5a5;border-radius:7px;padding:5px 10px;
                                              font-size:12px;color:#374151;flex:1;min-width:140px;max-width:180px">
                                <span style="font-size:10px;color:#94a3b8">Kosongkan jika belum ditentukan</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer -->
            <div style="padding:16px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;
                        display:flex;gap:10px;justify-content:flex-end;flex-shrink:0">
                <button type="button" onclick="tutupModalSelesai()"
                        style="padding:9px 20px;border:1px solid #e2e8f0;border-radius:8px;
                               background:#fff;color:#475569;font-size:13px;font-weight:600;cursor:pointer">
                    Batal
                </button>
                <button type="submit"
                        style="padding:9px 24px;border:none;border-radius:8px;
                               background:linear-gradient(135deg,#16a34a,#15803d);
                               color:#fff;font-size:13px;font-weight:600;cursor:pointer;
                               display:flex;align-items:center;gap:8px">
                    <i class="fas fa-circle-check"></i> Selesaikan & Masukkan ke LHP
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function toggleTanggapan(id) {
    const el = document.getElementById('tanggapan-form-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function bukaModalSelesai() {
    const m = document.getElementById('modal-selesai-nhp');
    if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
    else {
        // Jika tidak ada temuan tidak_sesuai, langsung submit tanpa batas waktu
        if (confirm('Selesaikan NHP? Proses ini tidak dapat dibatalkan.')) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = '/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/selesai';
            f.innerHTML = '<?= csrf_field() ?>';
            document.body.appendChild(f); f.submit();
        }
    }
}

function tutupModalSelesai() {
    const m = document.getElementById('modal-selesai-nhp');
    if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupModalSelesai(); });
</script>

<?= $this->endSection() ?>
