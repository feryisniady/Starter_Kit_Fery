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
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                    <h3 class="card-title"><i class="fas fa-users"></i> Susunan Tim</h3>
                    <button type="button" class="btn btn-sm btn-primary" onclick="tambahTim()">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
                <div class="card-body">
                    <table class="table-admin w-100">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Peran SPT</th>
                                <th>On Desk</th>
                                <th>On Field</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="spt-tim-rows">
                        <?php
                        $peranSptOpts = ['Penanggung Jawab','Wakil Penanggung Jawab','Pengendali Teknis','Ketua Tim','Anggota Tim'];
                        $sdmMap = array_column($sdmAll, null, 'id');
                        foreach($timDefault as $t):
                            $sdmNm = $sdmMap[$t['sdm_id']]['nama'] ?? '—';
                        ?>
                        <tr class="spt-tim-row">
                            <td>
                                <input type="hidden" name="tim_sdm_id[]" value="<?= $t['sdm_id'] ?>">
                                <input type="hidden" name="tim_from_pkpt[]" value="<?= $t['from_pkpt'] ?? 1 ?>">
                                <strong style="font-size:13px"><?= esc($sdmNm) ?></strong>
                                <?php if(($t['from_pkpt'] ?? 1) == 0): ?>
                                <span class="badge badge-warning" style="font-size:10px">Tambahan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <select name="tim_peran_spt[]" class="form-control form-control-sm">
                                    <?php foreach($peranSptOpts as $p): ?>
                                    <option value="<?= $p ?>" <?= ($t['peran_spt'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="tim_hp_desk[]" class="form-control form-control-sm" value="<?= $t['hp_desk'] ?? 1 ?>" min="0" style="width:60px"></td>
                            <td><input type="number" name="tim_hp_field[]" class="form-control form-control-sm" value="<?= $t['hp_field'] ?? 0 ?>" min="0" style="width:60px"></td>
                            <td>
                                <button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
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
const sdmOptions = `<?php foreach($sdmAll as $s): ?>
<option value="<?= $s['id'] ?>"><?= esc($s['nama']) ?></option>
<?php endforeach; ?>`;

const peranOpts = ['Penanggung Jawab','Wakil Penanggung Jawab','Pengendali Teknis','Ketua Tim','Anggota Tim']
    .map(p => `<option value="${p}">${p}</option>`).join('');

function tambahTim() {
    $('#spt-tim-rows').append(`<tr class="spt-tim-row">
        <td>
            <select name="tim_sdm_id[]" class="form-control form-control-sm" required>
                <option value="">— Pilih SDM —</option>${sdmOptions}
            </select>
            <input type="hidden" name="tim_from_pkpt[]" value="0">
        </td>
        <td><select name="tim_peran_spt[]" class="form-control form-control-sm">${peranOpts}</select></td>
        <td><input type="number" name="tim_hp_desk[]" class="form-control form-control-sm" value="1" min="0" style="width:60px"></td>
        <td><input type="number" name="tim_hp_field[]" class="form-control form-control-sm" value="0" min="0" style="width:60px"></td>
        <td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove()"><i class="fas fa-times"></i></button></td>
    </tr>`);
}
</script>
<?= $this->endSection() ?>
