<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><?= esc($title) ?></h1>
        <p><?= esc($kegiatan['kode_kegiatan']) ?> — <?= esc($pkpt['irban_nama']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/pkpt/<?= $pkpt['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke PKPT
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= $spt ? '/admin/spt/'.$spt['id'].'/update' : '/admin/spt/store/'.$kegiatan['id'] ?>" method="POST">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:flex-start">

        <!-- Kolom kiri -->
        <div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-signature"></i> Data Surat Perintah</h3></div>
                <div class="card-body">
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Nomor Naskah (SRIKANDI)</label>
                            <input type="text" name="nomor_naskah" class="form-control"
                                   placeholder="800.1.11.1/73/434.100/2026"
                                   value="<?= old('nomor_naskah', $spt['nomor_naskah'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Naskah <span style="color:red">*</span></label>
                            <input type="date" name="tanggal_naskah" class="form-control" required
                                   value="<?= old('tanggal_naskah', $spt['tanggal_naskah'] ?? date('Y-m-d')) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dasar Penugasan 1 <span style="color:red">*</span></label>
                        <textarea name="dasar_1" class="form-control" rows="3" required><?= old('dasar_1', $dasar1 ?? $spt['dasar_1'] ?? '') ?></textarea>
                        <small class="text-muted">Otomatis dari Setting PKPT. Bisa diedit sesuai kebutuhan.</small>
                    </div>
                    <div class="form-group">
                        <label>Dasar Penugasan 2 (opsional)</label>
                        <textarea name="dasar_2" class="form-control" rows="2"><?= old('dasar_2', $spt['dasar_2'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tujuan / Untuk <span style="color:red">*</span></label>
                        <textarea name="tujuan" class="form-control" rows="3" required><?= old('tujuan', $spt['tujuan'] ?? $kegiatan['tujuan_sasaran'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control"
                                   value="<?= old('tanggal_mulai', $spt['tanggal_mulai'] ?? $kegiatan['tanggal_mulai'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control"
                                   value="<?= old('tanggal_selesai', $spt['tanggal_selesai'] ?? $kegiatan['tanggal_selesai'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tembusan (Yth.)</label>
                        <input type="text" name="tembusan" class="form-control"
                               placeholder="DPMD Kabupaten Sampang"
                               value="<?= old('tembusan', $spt['tembusan'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Penandatangan (Inspektur)</label>
                        <select name="penandatangan_id" class="form-control">
                            <option value="">— Pilih —</option>
                            <?php foreach($sdmPenanda as $s): ?>
                            <option value="<?= $s['id'] ?>"
                                <?= ($spt['penandatangan_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                <?= esc($s['nama']) ?> — <?= esc($s['jabatan_struktural'] ?? '') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tim SPT -->
            <?php
            $peranSptOpts = ['Penanggung Jawab','Wakil Penanggung Jawab','Pengendali Teknis','Ketua Tim','Anggota Tim'];
            $sdmMap = array_column($sdmAll, null, 'id');
            ?>
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:10px 16px">
                    <h3 class="card-title" style="margin:0"><i class="fas fa-users"></i> Susunan Tim
                        <span id="tim-count-badge" class="badge badge-secondary" style="margin-left:6px;font-size:11px"><?= count($timDefault) ?></span>
                    </h3>
                    <button type="button" class="btn btn-sm btn-primary" onclick="tambahTim()">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
                <div style="max-height:280px;overflow-y:auto;overflow-x:hidden;border-bottom:1px solid #e2e8f0" id="tim-scroll-area">
                    <table style="width:100%;border-collapse:collapse;font-size:12.5px">
                        <thead>
                            <tr>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;padding:7px 10px;border-bottom:2px solid #e2e8f0;font-weight:600;text-align:left;white-space:nowrap">Nama</th>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;padding:7px 10px;border-bottom:2px solid #e2e8f0;font-weight:600;text-align:left">Peran SPT</th>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;padding:7px 6px;border-bottom:2px solid #e2e8f0;font-weight:600;text-align:center;width:54px">Desk</th>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;padding:7px 6px;border-bottom:2px solid #e2e8f0;font-weight:600;text-align:center;width:54px">Field</th>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;border-bottom:2px solid #e2e8f0;width:32px"></th>
                            </tr>
                        </thead>
                        <tbody id="spt-tim-rows">
                        <?php foreach($timDefault as $t):
                            $sdmNm = $sdmMap[$t['sdm_id']]['nama'] ?? '—';
                        ?>
                        <tr class="spt-tim-row" style="border-bottom:1px solid #f1f5f9">
                            <td style="padding:5px 10px;max-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= esc($sdmNm) ?>">
                                <input type="hidden" name="tim_sdm_id[]" value="<?= $t['sdm_id'] ?>">
                                <input type="hidden" name="tim_from_pkpt[]" value="<?= $t['from_pkpt'] ?? 1 ?>">
                                <span style="font-weight:600"><?= esc($sdmNm) ?></span>
                                <?php if(($t['from_pkpt'] ?? 1) == 0): ?>
                                <span class="badge badge-warning" style="font-size:10px;vertical-align:middle">+</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:4px 6px">
                                <select name="tim_peran_spt[]" class="form-control form-control-sm" style="font-size:12px;padding:3px 6px;height:28px">
                                    <?php foreach($peranSptOpts as $p): ?>
                                    <option value="<?= $p ?>" <?= ($t['peran_spt'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding:4px 6px;text-align:center">
                                <input type="number" name="tim_hp_desk[]" class="form-control form-control-sm" value="<?= $t['hp_desk'] ?? 1 ?>" min="0" style="width:48px;text-align:center;padding:3px 4px;height:28px;font-size:12px">
                            </td>
                            <td style="padding:4px 6px;text-align:center">
                                <input type="number" name="tim_hp_field[]" class="form-control form-control-sm" value="<?= $t['hp_field'] ?? 0 ?>" min="0" style="width:48px;text-align:center;padding:3px 4px;height:28px;font-size:12px">
                            </td>
                            <td style="padding:4px 6px;text-align:center">
                                <button type="button" class="btn btn-xs btn-danger btn-del-tim" style="padding:2px 6px">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(empty($timDefault)): ?>
                <div id="tim-empty" style="text-align:center;color:#94a3b8;padding:18px;font-size:13px">
                    <i class="fas fa-users-slash"></i> Belum ada anggota tim.
                </div>
                <?php else: ?>
                <div id="tim-empty" style="display:none;text-align:center;color:#94a3b8;padding:18px;font-size:13px">
                    <i class="fas fa-users-slash"></i> Belum ada anggota tim.
                </div>
                <?php endif; ?>
                <div style="padding:6px 12px;background:#f8fafc;border-top:1px solid #f1f5f9;font-size:11.5px;color:#64748b;display:flex;gap:16px">
                    <span>Total: <strong id="tim-total-desk">—</strong> Desk</span>
                    <span>+ <strong id="tim-total-field">—</strong> Field</span>
                    <span>= <strong id="tim-total-hp">—</strong> HP</span>
                </div>
            </div>
        </div>

        <!-- Kolom kanan: info kegiatan -->
        <div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> Info Kegiatan PKPT</h3></div>
                <div class="card-body" style="font-size:13px">
                    <div class="mb-2"><span class="badge badge-primary"><?= esc($kegiatan['kode_kegiatan']) ?></span></div>
                    <div><strong>Area:</strong> <?= esc($kegiatan['area_pengawasan']) ?></div>
                    <div class="mt-1"><strong>Jenis:</strong> <?= esc($kegiatan['jenis_pengawasan']) ?></div>
                    <div class="mt-1"><strong>Tujuan:</strong> <?= esc($kegiatan['tujuan_sasaran']) ?></div>
                    <?php if(!empty($kegiatan['entitas'])): ?>
                    <div class="mt-2"><strong>Entitas:</strong>
                        <?php foreach($kegiatan['entitas'] as $e): ?>
                        <span class="badge badge-secondary"><?= esc($e['entitas_nama']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Simpan SPT
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const sdmOptions = `<?php foreach($sdmAll as $s): ?><option value="<?= $s['id'] ?>"><?= esc($s['nama']) ?></option><?php endforeach; ?>`;

const peranOpts = ['Penanggung Jawab','Wakil Penanggung Jawab','Pengendali Teknis','Ketua Tim','Anggota Tim']
    .map(p => `<option value="${p}">${p}</option>`).join('');

const inputStyle = 'width:48px;text-align:center;padding:3px 4px;height:28px;font-size:12px';
const selectStyle = 'font-size:12px;padding:3px 6px;height:28px';

function tambahTim() {
    const row = `<tr class="spt-tim-row" style="border-bottom:1px solid #f1f5f9">
        <td style="padding:5px 10px">
            <select name="tim_sdm_id[]" class="form-control form-control-sm" required style="font-size:12px;padding:3px 6px;height:28px">
                <option value="">— Pilih SDM —</option>${sdmOptions}
            </select>
            <input type="hidden" name="tim_from_pkpt[]" value="0">
        </td>
        <td style="padding:4px 6px"><select name="tim_peran_spt[]" class="form-control form-control-sm" style="${selectStyle}">${peranOpts}</select></td>
        <td style="padding:4px 6px;text-align:center"><input type="number" name="tim_hp_desk[]"  class="form-control form-control-sm" value="1" min="0" style="${inputStyle}"></td>
        <td style="padding:4px 6px;text-align:center"><input type="number" name="tim_hp_field[]" class="form-control form-control-sm" value="0" min="0" style="${inputStyle}"></td>
        <td style="padding:4px 6px;text-align:center"><button type="button" class="btn btn-xs btn-danger btn-del-tim" style="padding:2px 6px"><i class="fas fa-times"></i></button></td>
    </tr>`;
    $('#spt-tim-rows').append(row);
    updateTimSummary();
    // Scroll to bottom after adding
    const area = document.getElementById('tim-scroll-area');
    area.scrollTop = area.scrollHeight;
}

function updateTimSummary() {
    const rows = $('#spt-tim-rows .spt-tim-row');
    const count = rows.length;
    $('#tim-count-badge').text(count);
    $('#tim-empty').toggle(count === 0);

    let desk = 0, field = 0;
    rows.find('input[name="tim_hp_desk[]"]').each(function() { desk += parseInt($(this).val()) || 0; });
    rows.find('input[name="tim_hp_field[]"]').each(function() { field += parseInt($(this).val()) || 0; });
    $('#tim-total-desk').text(desk);
    $('#tim-total-field').text(field);
    $('#tim-total-hp').text(desk + field);
}

$(function() {
    updateTimSummary();

    // Delete button (event delegation — works for existing + new rows)
    $(document).on('click', '.btn-del-tim', function() {
        $(this).closest('tr').remove();
        updateTimSummary();
    });

    // Recalculate on HP input change
    $(document).on('input', 'input[name="tim_hp_desk[]"], input[name="tim_hp_field[]"]', function() {
        updateTimSummary();
    });
});
</script>
<?= $this->endSection() ?>
