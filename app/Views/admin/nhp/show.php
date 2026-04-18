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
        <?php if ($canManage && $nhp['status'] === 'draft'): ?>
        <form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/kirim" style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-info"
                    onclick="return confirm('Tandai NHP ini sebagai terkirim ke entitas?')">
                <i class="fas fa-paper-plane"></i> Kirim ke Entitas
            </button>
        </form>
        <?php endif; ?>
        <?php if ($canManage && $nhp['status'] === 'ditanggapi'): ?>
        <form method="POST" action="/admin/spt/<?= $spt['id'] ?>/nhp/<?= $nhp['id'] ?>/selesai" style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-success"
                    onclick="return confirm('Selesaikan NHP? Temuan tidak sesuai akan masuk Matriks Temuan.')">
                <i class="fas fa-circle-check"></i> Selesaikan NHP
            </button>
        </form>
        <?php endif; ?>
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
        <button class="btn btn-sm btn-primary" onclick="document.getElementById('addItemForm').style.display='block';this.style.display='none'">
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
                    <textarea name="kondisi" class="form-control" rows="3" placeholder="Uraikan kondisi temuan..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Kriteria</label>
                    <textarea name="kriteria" class="form-control" rows="3" placeholder="Dasar hukum/kriteria yang dilanggar..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Sebab</label>
                    <textarea name="sebab" class="form-control" rows="3" placeholder="Sebab terjadinya temuan..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Akibat</label>
                    <textarea name="akibat" class="form-control" rows="3" placeholder="Akibat/dampak temuan..."></textarea>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px">
                <div class="form-group">
                    <label class="form-label">Rekomendasi</label>
                    <textarea name="rekomendasi" class="form-control" rows="2" placeholder="Saran rekomendasi..."></textarea>
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
                    <div style="font-size:13px;color:#334155;line-height:1.5"><?= nl2br(esc($item['kondisi'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($item['kriteria']): ?>
                <div>
                    <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px">Kriteria</div>
                    <div style="font-size:13px;color:#334155;line-height:1.5"><?= nl2br(esc($item['kriteria'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($item['sebab']): ?>
                <div>
                    <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px">Sebab</div>
                    <div style="font-size:13px;color:#334155;line-height:1.5"><?= nl2br(esc($item['sebab'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($item['akibat']): ?>
                <div>
                    <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px">Akibat</div>
                    <div style="font-size:13px;color:#334155;line-height:1.5"><?= nl2br(esc($item['akibat'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($item['rekomendasi']): ?>
            <div style="padding:10px 14px;background:#f0fdf4;border-left:3px solid #10b981;border-radius:4px;font-size:13px;color:#065f46;margin-bottom:12px">
                <strong>Rekomendasi:</strong> <?= esc($item['rekomendasi']) ?>
            </div>
            <?php endif; ?>

            <!-- Tanggapan entitas -->
            <?php if ($item['tanggapan_entitas']): ?>
            <div style="padding:10px 14px;background:#fff7ed;border-left:3px solid #f59e0b;border-radius:4px;font-size:13px;margin-bottom:12px">
                <div style="font-size:11px;font-weight:600;color:#92400e;margin-bottom:4px">
                    Tanggapan Entitas — <?= $item['tgl_tanggapan'] ? date('d/m/Y', strtotime($item['tgl_tanggapan'])) : '' ?>
                </div>
                <?= nl2br(esc($item['tanggapan_entitas'])) ?>
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

<script>
function toggleTanggapan(id) {
    const el = document.getElementById('tanggapan-form-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?= $this->endSection() ?>
