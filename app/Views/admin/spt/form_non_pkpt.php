<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit      = !empty($spt);
$formAction  = $isEdit ? '/admin/spt/' . $spt['id'] . '/update' : '/admin/spt/non-pkpt/store';
$peranOpts   = ['Penanggung Jawab','Wakil Penanggung Jawab','Pengendali Teknis','Ketua Tim','Anggota Tim'];
$timDefault  = $spt['tim'] ?? [];
?>

<div class="page-header">
    <div class="page-title">
        <h1><?= esc($title) ?></h1>
        <p>Penugasan Mandatori / Non-PKPT</p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke SPT
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="box-info" style="font-size:13px">
    <i class="fas fa-info-circle"></i>
    <strong>SPT Non-PKPT</strong> — untuk penugasan mandatori yang tidak tercantum dalam PKPT Kegiatan,
    seperti Reviu LKPD, Evaluasi SPIP, Evaluasi SAKIP, dan lainnya.
</div>

<form action="<?= $formAction ?>" method="POST">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:flex-start">

        <!-- Kolom kiri -->
        <div>
            <!-- Identitas Non-PKPT -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-tag"></i> Identitas Penugasan</h3></div>
                <div class="card-body">
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Irban <span style="color:red">*</span></label>
                            <select name="irban_id" class="form-control" required>
                                <option value="">— Pilih Irban —</option>
                                <?php foreach($irbanList as $ir): ?>
                                <option value="<?= $ir['id'] ?>"
                                    <?= old('irban_id', $spt['irban_id'] ?? '') == $ir['id'] ? 'selected' : '' ?>>
                                    <?= esc($ir['kode']) ?> — <?= esc($ir['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tahun <span style="color:red">*</span></label>
                            <input type="number" name="tahun" class="form-control" required
                                   min="2020" max="2040"
                                   value="<?= old('tahun', $spt['tahun'] ?? $tahunAktif) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jenis Penugasan Mandatori <span style="color:red">*</span></label>
                        <select name="jenis_non_pkpt" class="form-control" required>
                            <option value="">— Pilih Jenis —</option>
                            <?php foreach($jenisOpts as $k => $v): ?>
                            <option value="<?= esc($k) ?>"
                                <?= old('jenis_non_pkpt', $spt['jenis_non_pkpt'] ?? '') === $k ? 'selected' : '' ?>>
                                <?= esc($v) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>OPD / Entitas yang Diperiksa</label>
                        <select name="entitas_id" class="form-control">
                            <option value="">— Pilih OPD (opsional) —</option>
                            <?php foreach($entitasList ?? [] as $ent): ?>
                            <option value="<?= $ent['id'] ?>"
                                <?= old('entitas_id', $spt['entitas_id'] ?? '') == $ent['id'] ? 'selected' : '' ?>>
                                <?= esc($ent['nama']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div style="font-size:11px;color:#94a3b8;margin-top:3px">
                            Pilih OPD agar entitas dapat melihat tindak lanjut & NHP di portal auditi
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data SPT -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-signature"></i> Data Surat Perintah</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Label Tim <span style="font-size:11px;color:#94a3b8">(opsional)</span></label>
                        <input type="text" name="nama_tim" class="form-control"
                               placeholder="cth: Tim Reviu LKPD 2026"
                               value="<?= old('nama_tim', $spt['nama_tim'] ?? '') ?>"
                               maxlength="50">
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Nomor Naskah (SRIKANDI)</label>
                            <input type="text" name="nomor_naskah" class="form-control"
                                   placeholder="800.1.11.1/073/434.100/2026"
                                   value="<?= old('nomor_naskah', $spt['nomor_naskah'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Naskah <span style="color:red">*</span></label>
                            <input type="date" name="tanggal_naskah" class="form-control" required
                                   value="<?= old('tanggal_naskah', $spt['tanggal_naskah'] ?? date('Y-m-d')) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dasar Penugasan 1</label>
                        <textarea name="dasar_1" class="form-control" rows="3"><?= old('dasar_1', $spt['dasar_1'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Dasar Penugasan 2 (opsional)</label>
                        <textarea name="dasar_2" class="form-control" rows="2"><?= old('dasar_2', $spt['dasar_2'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tujuan / Untuk <span style="color:red">*</span></label>
                        <textarea name="tujuan" class="form-control" rows="3" required
                                  data-wysiwyg data-wysiwyg-height="90px"><?= old('tujuan', $spt['tujuan'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="spt-tgl-mulai" class="form-control"
                                   value="<?= old('tanggal_mulai', $spt['tanggal_mulai'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Selesai Penugasan</label>
                            <input type="date" name="tanggal_selesai" id="spt-tgl-selesai" class="form-control"
                                   value="<?= old('tanggal_selesai', $spt['tanggal_selesai'] ?? '') ?>">
                        </div>
                    </div>
                    <!-- HP Calculator Panel -->
                    <div id="hp-calc-panel" style="display:none;margin-top:-6px;margin-bottom:14px;padding:10px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:13px">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                            <div>
                                <i class="fas fa-calendar-check" style="color:#3b82f6;margin-right:4px"></i>
                                <strong id="hp-calc-count" style="color:#1d4ed8;font-size:18px;margin-right:4px">0</strong>
                                <span style="color:#475569">hari kerja</span>
                                <small id="hp-calc-detail" style="color:#94a3b8;margin-left:6px"></small>
                            </div>
                            <button type="button" id="btn-terapkan-hp" class="btn btn-sm btn-primary" style="font-size:12px;white-space:nowrap">
                                <i class="fas fa-magic"></i> Terapkan ke Tim
                            </button>
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
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:10px 16px">
                    <h3 class="card-title" style="margin:0"><i class="fas fa-users"></i> Susunan Tim
                        <span id="tim-count-badge" class="badge badge-secondary" style="margin-left:6px;font-size:11px"><?= count($timDefault) ?></span>
                    </h3>
                    <button type="button" class="btn btn-sm btn-primary" onclick="tambahTim()">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
                <div style="max-height:300px;overflow-y:auto;overflow-x:hidden;border-bottom:1px solid #e2e8f0" id="tim-scroll-area">
                    <table style="width:100%;border-collapse:collapse;font-size:12.5px">
                        <thead>
                            <tr>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;padding:7px 10px;border-bottom:2px solid #e2e8f0;font-weight:600;text-align:left">Nama SDM</th>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;padding:7px 10px;border-bottom:2px solid #e2e8f0;font-weight:600;text-align:left">Peran SPT</th>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;padding:7px 6px;border-bottom:2px solid #e2e8f0;font-weight:600;text-align:center;width:54px">Desk</th>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;padding:7px 6px;border-bottom:2px solid #e2e8f0;font-weight:600;text-align:center;width:54px">Field</th>
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;border-bottom:2px solid #e2e8f0;width:32px"></th>
                            </tr>
                        </thead>
                        <tbody id="spt-tim-rows">
                        <?php
                        $sdmMap = array_column($sdmAll, null, 'id');
                        foreach($timDefault as $t):
                            $sdmNm = $sdmMap[$t['sdm_id']]['nama'] ?? '—';
                        ?>
                        <tr class="spt-tim-row" style="border-bottom:1px solid #f1f5f9">
                            <td style="padding:5px 10px">
                                <select name="tim_sdm_id[]" class="form-control form-control-sm" required style="font-size:12px;padding:3px 6px;height:28px">
                                    <option value="">— Pilih SDM —</option>
                                    <?php foreach($sdmAll as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $t['sdm_id'] ? 'selected' : '' ?>><?= esc($s['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="tim_from_pkpt[]" value="0">
                            </td>
                            <td style="padding:4px 6px">
                                <select name="tim_peran_spt[]" class="form-control form-control-sm" style="font-size:12px;padding:3px 6px;height:28px">
                                    <?php foreach($peranOpts as $p): ?>
                                    <option value="<?= $p ?>" <?= ($t['peran_spt'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding:4px 6px;text-align:center">
                                <input type="number" name="tim_hp_desk[]" class="form-control form-control-sm"
                                       value="<?= $t['hp_desk'] ?? 1 ?>" min="0"
                                       style="width:48px;text-align:center;padding:3px 4px;height:28px;font-size:12px">
                            </td>
                            <td style="padding:4px 6px;text-align:center">
                                <input type="number" name="tim_hp_field[]" class="form-control form-control-sm"
                                       value="<?= $t['hp_field'] ?? 0 ?>" min="0"
                                       style="width:48px;text-align:center;padding:3px 4px;height:28px;font-size:12px">
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

        <!-- Kolom kanan -->
        <div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-list-check"></i> Jenis Non-PKPT</h3></div>
                <div class="card-body" style="font-size:12.5px;color:#475569">
                    <?php foreach($jenisOpts as $k => $v): ?>
                    <div style="padding:3px 0;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:6px">
                        <i class="fas fa-circle" style="font-size:6px;color:#94a3b8"></i>
                        <?= esc($v) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Buat SPT Non-PKPT' ?>
                    </button>
                    <?php if ($isEdit): ?>
                    <a href="/admin/spt/<?= $spt['id'] ?>" class="btn btn-secondary w-100 mt-2">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const sdmOptions = `<?php foreach($sdmAll as $s): ?><option value="<?= $s['id'] ?>"><?= esc($s['nama']) ?></option><?php endforeach; ?>`;
const peranOpts  = ['Penanggung Jawab','Wakil Penanggung Jawab','Pengendali Teknis','Ketua Tim','Anggota Tim']
    .map(p => `<option value="${p}">${p}</option>`).join('');
const inputStyle = 'width:48px;text-align:center;padding:3px 4px;height:28px;font-size:12px';
const selStyle   = 'font-size:12px;padding:3px 6px;height:28px';

function tambahTim() {
    const row = `<tr class="spt-tim-row" style="border-bottom:1px solid #f1f5f9">
        <td style="padding:5px 10px">
            <select name="tim_sdm_id[]" class="form-control form-control-sm" required style="${selStyle}">
                <option value="">— Pilih SDM —</option>${sdmOptions}
            </select>
            <input type="hidden" name="tim_from_pkpt[]" value="0">
        </td>
        <td style="padding:4px 6px"><select name="tim_peran_spt[]" class="form-control form-control-sm" style="${selStyle}">${peranOpts}</select></td>
        <td style="padding:4px 6px;text-align:center"><input type="number" name="tim_hp_desk[]"  class="form-control form-control-sm" value="1" min="0" style="${inputStyle}"></td>
        <td style="padding:4px 6px;text-align:center"><input type="number" name="tim_hp_field[]" class="form-control form-control-sm" value="0" min="0" style="${inputStyle}"></td>
        <td style="padding:4px 6px;text-align:center"><button type="button" class="btn btn-xs btn-danger btn-del-tim" style="padding:2px 6px"><i class="fas fa-times"></i></button></td>
    </tr>`;
    $('#spt-tim-rows').append(row);
    initSelect2($('#spt-tim-rows .spt-tim-row:last select.form-control'));
    updateTimSummary();
    const area = document.getElementById('tim-scroll-area');
    area.scrollTop = area.scrollHeight;
}

function updateTimSummary() {
    const rows = $('#spt-tim-rows .spt-tim-row');
    $('#tim-count-badge').text(rows.length);
    $('#tim-empty').toggle(rows.length === 0);
    let desk = 0, field = 0;
    rows.find('input[name="tim_hp_desk[]"]').each(function() { desk += parseInt($(this).val()) || 0; });
    rows.find('input[name="tim_hp_field[]"]').each(function() { field += parseInt($(this).val()) || 0; });
    $('#tim-total-desk').text(desk);
    $('#tim-total-field').text(field);
    $('#tim-total-hp').text(desk + field);
}

$(function() {
    updateTimSummary();
    $(document).on('click', '.btn-del-tim', function() {
        $(this).closest('tr').remove();
        updateTimSummary();
    });
    $(document).on('input', 'input[name="tim_hp_desk[]"], input[name="tim_hp_field[]"]', function() {
        updateTimSummary();
    });

    // ── HP Calculator ──────────────────────────────────────────────────────
    $('#spt-tgl-mulai, #spt-tgl-selesai').on('change', function() {
        refreshHpCalc();
    });

    $('#btn-terapkan-hp').on('click', function() {
        const hp = parseInt($('#hp-calc-count').text()) || 0;
        if (hp <= 0) return;
        if (!confirm('Terapkan ' + hp + ' HP ke semua baris tim?\n(Desk = ' + hp + ', Field = 0)')) return;
        $('#spt-tim-rows .spt-tim-row').each(function() {
            $(this).find('input[name="tim_hp_desk[]"]').val(hp);
            $(this).find('input[name="tim_hp_field[]"]').val(0);
        });
        updateTimSummary();
        $('#btn-terapkan-hp').html('<i class="fas fa-check"></i> Diterapkan!').addClass('btn-success').removeClass('btn-primary');
        setTimeout(function() {
            $('#btn-terapkan-hp').html('<i class="fas fa-magic"></i> Terapkan ke Tim').addClass('btn-primary').removeClass('btn-success');
        }, 2000);
    });

    refreshHpCalc();
});

// ── Kalkulator hari kerja ─────────────────────────────────────────────────
const HARI_LIBUR_SPT = <?= json_encode($hariLibur ?? []) ?>;

function hitungHariKerja(mulai, selesai) {
    if (!mulai || !selesai) return {hp: 0, kalender: 0, libur: 0};
    const d1 = new Date(mulai + 'T00:00:00'), d2 = new Date(selesai + 'T00:00:00');
    if (d2 < d1) return {hp: 0, kalender: 0, libur: 0};
    let hp = 0, kalender = 0, libur = 0;
    const cur = new Date(d1);
    while (cur <= d2) {
        kalender++;
        const dow = cur.getDay();
        if (dow > 0 && dow < 6) {
            const iso = cur.toISOString().split('T')[0];
            if (HARI_LIBUR_SPT.includes(iso)) libur++; else hp++;
        }
        cur.setDate(cur.getDate() + 1);
    }
    return {hp, kalender, libur};
}

function refreshHpCalc() {
    const mulai = $('#spt-tgl-mulai').val(), selesai = $('#spt-tgl-selesai').val();
    if (!mulai || !selesai) { $('#hp-calc-panel').hide(); return; }
    const r = hitungHariKerja(mulai, selesai);
    if (r.kalender <= 0) { $('#hp-calc-panel').hide(); return; }
    $('#hp-calc-count').text(r.hp);
    const sabmgu = r.kalender - r.hp - r.libur;
    let d = r.kalender + ' hari kalender';
    if (sabmgu > 0) d += ' − ' + sabmgu + ' Sab/Min';
    if (r.libur > 0) d += ' − ' + r.libur + ' libur nasional';
    $('#hp-calc-detail').text('(' + d + ')');
    $('#hp-calc-panel').show();
}
</script>
<?= $this->endSection() ?>
