<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><?= esc($title) ?></h1>
        <p><?= esc($pkpt['irban_nama']) ?> — <?= $pkpt['tahun'] ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/pkpt/<?= $pkpt['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- Info Header PKPT + HP Tersedia -->
<?php
$hpPct = $hpEfektif > 0 ? min(100, round(($hpTerpakai / $hpEfektif) * 100)) : 0;
$hpBarColor = $hpPct >= 90 ? '#ef4444' : ($hpPct >= 70 ? '#f59e0b' : '#22c55e');
?>
<div class="card mb-3" style="border-left:4px solid #6366f1">
    <div class="card-body" style="padding:14px 20px">
        <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr 1fr;gap:16px;align-items:center">
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Header PKPT</div>
                <div style="font-weight:600;font-size:13px"><?= esc($setting['nomor_pkpt'] ?? '—') ?></div>
                <div style="font-size:11px;color:#64748b">
                    <?= ($setting['tanggal_pkpt'] ?? '') ? date('d/m/Y', strtotime($setting['tanggal_pkpt'])) : '—' ?>
                    <?php if($setting): ?>
                    &nbsp;<span class="badge badge-<?= $setting['status']==='disetujui' ? 'success' : 'warning' ?>" style="font-size:10px">
                        <?= $setting['status']==='disetujui' ? 'Disetujui' : 'Draft' ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">HP Efektif</div>
                <div style="font-weight:700;font-size:20px;color:#6366f1"><?= $hpEfektif ?? '—' ?></div>
                <div style="font-size:11px;color:#94a3b8">hari/tahun</div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">HP Terpakai</div>
                <div style="font-weight:700;font-size:20px;color:#f59e0b"><?= $hpTerpakai ?></div>
                <div style="font-size:11px;color:#94a3b8">dari kegiatan lain</div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Sisa HP</div>
                <div style="font-weight:700;font-size:20px;color:<?= $hpSisa <= 0 ? '#ef4444' : '#22c55e' ?>" id="disp-sisa-hp"><?= $hpSisa ?></div>
                <div style="font-size:11px;color:#94a3b8">tersedia</div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">Tarif HP</div>
                <div style="font-weight:600;font-size:14px">Rp <?= number_format($setting['tarif_hp'] ?? 160000, 0, ',', '.') ?></div>
                <div style="font-size:11px;color:#94a3b8">per hari</div>
            </div>
        </div>
        <!-- Progress Bar HP -->
        <div style="margin-top:12px">
            <div style="font-size:11px;color:#94a3b8;margin-bottom:4px">Utilisasi HP PKPT: <?= $hpPct ?>%</div>
            <div style="height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden">
                <div id="hp-progress-bar" style="height:100%;width:<?= $hpPct ?>%;background:<?= $hpBarColor ?>;border-radius:3px;transition:width .3s"></div>
            </div>
        </div>
    </div>
</div>

<form action="<?= $row ? '/admin/pkpt/kegiatan/update/'.$row['id'] : '/admin/pkpt/'.$pkpt['id'].'/kegiatan/store' ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row" style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:flex-start">

        <!-- Kolom kiri -->
        <div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Kegiatan</h3></div>
                <div class="card-body">
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Area Pengawasan <span style="color:red">*</span></label>
                            <input type="text" name="area_pengawasan" class="form-control" required
                                   value="<?= old('area_pengawasan', $row['area_pengawasan'] ?? '') ?>"
                                   placeholder="Audit Pekerjaan Fisik">
                        </div>
                        <div class="form-group">
                            <label>Jenis Pengawasan <span style="color:red">*</span></label>
                            <input type="text" name="jenis_pengawasan" class="form-control" required
                                   list="list-jenis"
                                   value="<?= old('jenis_pengawasan', $row['jenis_pengawasan'] ?? '') ?>">
                            <datalist id="list-jenis">
                                <option>Audit Ketaatan</option>
                                <option>Audit Kinerja</option>
                                <option>Audit Keuangan</option>
                                <option>Reviu</option>
                                <option>Evaluasi</option>
                                <option>Monitoring</option>
                                <option>Pemantauan</option>
                            </datalist>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tujuan / Sasaran <span style="color:red">*</span></label>
                        <textarea name="tujuan_sasaran" class="form-control" rows="3" required><?= old('tujuan_sasaran', $row['tujuan_sasaran'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Ruang Lingkup</label>
                        <textarea name="ruang_lingkup" class="form-control" rows="2"><?= old('ruang_lingkup', $row['ruang_lingkup'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Risiko Audit</label>
                            <select name="risiko_audit" class="form-control">
                                <?php foreach(['rendah','sedang','tinggi'] as $r): ?>
                                <option value="<?= $r ?>" <?= ($row['risiko_audit'] ?? 'sedang') === $r ? 'selected' : '' ?>>
                                    <?= ucfirst($r) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Laporan</label>
                            <input type="number" name="jumlah_laporan" class="form-control"
                                   value="<?= old('jumlah_laporan', $row['jumlah_laporan'] ?? 1) ?>" min="1">
                        </div>
                    </div>
                    <?php
                    // Generate opsi minggu per bulan (tahun PKPT ± 1 tahun)
                    $bulanSingkat = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    $weekDefs = [
                        'Mg-I'  => [1,  7],
                        'Mg-II' => [8,  14],
                        'Mg-III'=> [15, 21],
                        'Mg-IV' => [22, 28],
                    ];
                    $weekOptions = [];
                    for ($y = $pkpt['tahun'] - 1; $y <= $pkpt['tahun'] + 1; $y++) {
                        for ($m = 1; $m <= 12; $m++) {
                            $lastDay = (int) date('t', mktime(0,0,0,$m,1,$y));
                            foreach ($weekDefs as $wk => [$wStart, $wEnd]) {
                                $actualEnd = min($wEnd, $lastDay);
                                $weekOptions[] = [
                                    'label' => $wk . ' ' . $bulanSingkat[$m-1] . ' ' . $y,
                                    'start' => sprintf('%04d-%02d-%02d', $y, $m, $wStart),
                                    'end'   => sprintf('%04d-%02d-%02d', $y, $m, $actualEnd),
                                ];
                            }
                            if ($lastDay >= 29) {
                                $weekOptions[] = [
                                    'label' => 'Mg-V ' . $bulanSingkat[$m-1] . ' ' . $y,
                                    'start' => sprintf('%04d-%02d-29', $y, $m),
                                    'end'   => sprintf('%04d-%02d-%02d', $y, $m, $lastDay),
                                ];
                            }
                        }
                    }
                    $curRmp = old('jadwal_rmp', $row['jadwal_rmp'] ?? '');
                    $curRpl = old('jadwal_rpl', $row['jadwal_rpl'] ?? '');
                    ?>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Jadwal RMP <small style="color:#94a3b8;font-weight:400">(Rencana Mulai Penugasan)</small></label>
                            <select name="jadwal_rmp" id="sel-rmp" class="form-control" onchange="onRmpChange(this)">
                                <option value="">— Pilih Minggu —</option>
                                <?php foreach ($weekOptions as $wo): ?>
                                <option value="<?= $wo['label'] ?>"
                                        data-start="<?= $wo['start'] ?>"
                                        data-end="<?= $wo['end'] ?>"
                                        <?= $curRmp === $wo['label'] ? 'selected' : '' ?>>
                                    <?= $wo['label'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jadwal RPL <small style="color:#94a3b8;font-weight:400">(Rencana Pelaksanaan Lapangan)</small></label>
                            <select name="jadwal_rpl" id="sel-rpl" class="form-control" onchange="onRplChange(this)">
                                <option value="">— Pilih Minggu —</option>
                                <?php foreach ($weekOptions as $wo): ?>
                                <option value="<?= $wo['label'] ?>"
                                        data-start="<?= $wo['start'] ?>"
                                        data-end="<?= $wo['end'] ?>"
                                        <?= $curRpl === $wo['label'] ? 'selected' : '' ?>>
                                    <?= $wo['label'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Tanggal Mulai
                                <small style="color:#6366f1;font-size:10px"><i class="fas fa-bolt"></i> Auto dari RMP</small>
                            </label>
                            <input type="date" name="tanggal_mulai" id="inp-mulai" class="form-control"
                                   value="<?= old('tanggal_mulai', $row['tanggal_mulai'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Selesai
                                <small style="color:#6366f1;font-size:10px"><i class="fas fa-bolt"></i> Auto dari RPL</small>
                            </label>
                            <input type="date" name="tanggal_selesai" id="inp-selesai" class="form-control"
                                   value="<?= old('tanggal_selesai', $row['tanggal_selesai'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sarana &amp; Prasarana</label>
                        <input type="text" name="sarana_prasarana" class="form-control"
                               placeholder="Laptop, ATK"
                               value="<?= old('sarana_prasarana', $row['sarana_prasarana'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Tim Pengawas -->
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                    <h3 class="card-title"><i class="fas fa-users"></i> Susunan Tim</h3>
                    <button type="button" class="btn btn-sm btn-primary" onclick="tambahBarisTim()">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
                <div class="card-body">
                    <div id="hp-kegiatan-counter" style="display:flex;align-items:center;gap:16px;padding:10px 14px;background:#f8fafc;border-radius:8px;margin-bottom:12px;font-size:13px">
                        <span>HP kegiatan ini: <strong id="hp-kegiatan-val" style="color:#6366f1">0</strong> hari</span>
                        <span style="color:#94a3b8">|</span>
                        <span>Sisa setelah simpan: <strong id="hp-after-val" style="color:#22c55e">—</strong> hari</span>
                        <span id="hp-warning" style="color:#ef4444;display:none"><i class="fas fa-triangle-exclamation"></i> Melebihi sisa HP!</span>
                    </div>
                    <table class="table-admin w-100" id="tbl-tim">
                        <thead>
                            <tr>
                                <th>Peran</th>
                                <th>SDM</th>
                                <th>HP (Hari)</th>
                                <th>Anggaran</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="tim-rows">
                        <?php
                        $peranOptions = ['PJ','WPJ','Dalnis','KT','AT'];
                        $sdmMap = array_column($sdm, null, 'id');
                        $existingTim = $timRows ?? [];
                        foreach($existingTim as $t): ?>
                        <tr class="tim-row">
                            <td>
                                <select name="tim_peran[]" class="form-control form-control-sm" required>
                                    <?php foreach($peranOptions as $p): ?>
                                    <option value="<?= $p ?>" <?= $t['peran'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="tim_sdm_id[]" class="form-control form-control-sm sdm-select" required
                                        onchange="updateSisaHp(this)">
                                    <option value="">— Pilih —</option>
                                    <?php foreach($sdm as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= $t['sdm_id'] == $s['id'] ? 'selected' : '' ?>>
                                        <?= esc($s['nama']) ?> (<?= esc($s['irban_nama'] ?? '-') ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="sisa-hp-info"></div>
                            </td>
                            <td>
                                <input type="number" name="tim_hp[]" class="form-control form-control-sm hp-input"
                                       value="<?= $t['hp_total'] ?>" min="1"
                                       onchange="hitungTotal();refreshSisaHp(this)" style="width:70px">
                            </td>
                            <td class="anggaran-cell" style="font-size:12px;white-space:nowrap">
                                Rp <?= number_format($t['anggaran'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove();hitungTotal()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight:700;background:#f8fafc">
                                <td colspan="2" style="text-align:right">Total</td>
                                <td id="total-hp">0</td>
                                <td id="total-anggaran" colspan="2">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom kanan -->
        <div>
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-building"></i> Entitas / OPD</h3></div>
                <div class="card-body">
                    <div style="max-height:300px;overflow-y:auto">
                    <?php
                    $selectedEntitas = array_column($row['entitas'] ?? [], 'entitas_id');
                    foreach($entitas as $e): ?>
                    <label style="display:flex;align-items:center;gap:8px;padding:6px 0;font-size:13px;cursor:pointer">
                        <input type="checkbox" name="entitas_ids[]" value="<?= $e['id'] ?>"
                               <?= in_array($e['id'], $selectedEntitas) ? 'checked' : '' ?>>
                        <?= esc($e['nama']) ?>
                    </label>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:16px">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Simpan Kegiatan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const tarif    = <?= ($setting['tarif_hp'] ?? 160000) ?>;
const tahun    = <?= $pkpt['tahun'] ?>;
const hpSisa   = <?= (int)($hpSisa ?? 0) ?>;   // sisa HP PKPT sebelum kegiatan ini
const hpEfektif = <?= (int)($hpEfektif ?? 0) ?>;
const hpTerpakai = <?= (int)($hpTerpakai ?? 0) ?>;

const sdmOptions = `<?php foreach($sdm as $s): ?><option value="<?= $s['id'] ?>"><?= esc($s['nama']) ?> (<?= esc($s['irban_nama'] ?? '-') ?>)</option><?php endforeach; ?>`;
const peranOptions = ['PJ','WPJ','Dalnis','KT','AT'];

function tambahBarisTim() {
    const peranSel = peranOptions.map(p => `<option value="${p}">${p}</option>`).join('');
    const tr = `<tr class="tim-row">
        <td><select name="tim_peran[]" class="form-control form-control-sm" required>${peranSel}</select></td>
        <td>
            <select name="tim_sdm_id[]" class="form-control form-control-sm sdm-select" required onchange="updateSisaHp(this)">
                <option value="">— Pilih —</option>${sdmOptions}
            </select>
            <div class="sisa-hp-info"></div>
        </td>
        <td><input type="number" name="tim_hp[]" class="form-control form-control-sm hp-input" value="1" min="1" onchange="hitungTotal();refreshSisaHp(this)" style="width:70px"></td>
        <td class="anggaran-cell" style="font-size:12px">Rp 160.000</td>
        <td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove();hitungTotal()"><i class="fas fa-times"></i></button></td>
    </tr>`;
    $('#tim-rows').append(tr);
    hitungTotal();
}

function hitungTotal() {
    let totalHp = 0, totalAng = 0;
    $('.hp-input').each(function() {
        const hp  = parseInt($(this).val()) || 0;
        const ang = hp * tarif;
        totalHp  += hp;
        totalAng += ang;
        $(this).closest('tr').find('.anggaran-cell').text('Rp ' + ang.toLocaleString('id-ID'));
    });
    $('#total-hp').text(totalHp);
    $('#total-anggaran').text('Rp ' + totalAng.toLocaleString('id-ID'));

    // Live HP counter
    const sisaSetelah = hpSisa - totalHp;
    $('#hp-kegiatan-val').text(totalHp);
    $('#hp-after-val').text(sisaSetelah >= 0 ? sisaSetelah : 0);
    if (sisaSetelah < 0) {
        $('#hp-after-val').css('color', '#ef4444');
        $('#hp-warning').show();
    } else if (sisaSetelah <= Math.ceil(hpEfektif * 0.1)) {
        $('#hp-after-val').css('color', '#f59e0b');
        $('#hp-warning').hide();
    } else {
        $('#hp-after-val').css('color', '#22c55e');
        $('#hp-warning').hide();
    }

    // Update progress bar
    if (hpEfektif > 0) {
        const pct = Math.min(100, Math.round(((hpTerpakai + totalHp) / hpEfektif) * 100));
        const color = pct >= 90 ? '#ef4444' : (pct >= 70 ? '#f59e0b' : '#22c55e');
        $('#hp-progress-bar').css({'width': pct + '%', 'background': color});
        $('#disp-sisa-hp').text(Math.max(0, hpSisa - totalHp)).css('color', sisaSetelah <= 0 ? '#ef4444' : '#22c55e');
    }
}

function updateSisaHp(sel) {
    const sdmId = $(sel).val();
    const $info = $(sel).closest('td').find('.sisa-hp-info');
    if (!sdmId) { $info.html(''); return; }

    $info.html('<span style="font-size:11px;color:#94a3b8"><i class="fas fa-spinner fa-spin"></i></span>');

    $.get('/admin/pkpt/sisa-hp?sdm_id=' + sdmId + '&tahun=' + tahun, res => {
        const sisa = parseInt(res.sisa_hp) || 0;
        const hp   = parseInt($(sel).closest('tr').find('.hp-input').val()) || 0;
        $(sel).attr('data-sisa-hp', sisa);
        renderSisaHp($info, sisa, hp);
    });
}

function refreshSisaHp(input) {
    const $sel  = $(input).closest('tr').find('.sdm-select');
    const sisa  = parseInt($sel.attr('data-sisa-hp'));
    if (isNaN(sisa)) return;
    const hp    = parseInt($(input).val()) || 0;
    const $info = $sel.closest('td').find('.sisa-hp-info');
    renderSisaHp($info, sisa, hp);
}

function renderSisaHp($el, sisa, hp) {
    const over  = hp > sisa;
    const warn  = !over && sisa > 0 && hp >= Math.ceil(sisa * 0.8);
    const color = over ? '#ef4444' : (warn ? '#f59e0b' : '#22c55e');
    const icon  = over ? 'fa-circle-xmark' : (warn ? 'fa-triangle-exclamation' : 'fa-circle-check');
    const maxV  = Math.max(sisa, hp, 1);
    const pct   = Math.min(100, Math.round(hp / maxV * 100));

    let html = '<div style="margin-top:5px">';
    html += '<div style="display:flex;align-items:center;gap:5px;font-size:11px;flex-wrap:wrap">';
    html += '<i class="fas ' + icon + '" style="color:' + color + '"></i>';
    html += '<span style="color:#64748b">Sisa HP:</span>';
    html += '<strong style="color:' + color + '">' + sisa + ' hari</strong>';
    if (over) {
        html += '<span style="background:#fee2e2;color:#991b1b;border-radius:4px;padding:1px 6px;font-size:10px;font-weight:700">';
        html += '<i class="fas fa-triangle-exclamation"></i> +' + (hp - sisa) + ' hari melebihi!</span>';
    } else if (warn) {
        html += '<span style="background:#fef9c3;color:#854d0e;border-radius:4px;padding:1px 6px;font-size:10px;font-weight:600">Mendekati batas</span>';
    }
    html += '</div>';
    html += '<div style="margin-top:3px;height:5px;background:#e2e8f0;border-radius:3px;overflow:hidden">';
    html += '<div style="height:100%;width:' + pct + '%;background:' + color + ';border-radius:3px;transition:width .3s"></div>';
    html += '</div>';
    html += '<div style="font-size:10px;color:#94a3b8;margin-top:1px">' + hp + ' dari ' + Math.max(sisa, hp) + ' hari digunakan</div>';
    html += '</div>';
    $el.html(html);
}

$(document).ready(function() {
    hitungTotal();
    // Load sisa HP untuk semua baris yang sudah ada
    $('.sdm-select').each(function() {
        if ($(this).val()) updateSisaHp(this);
    });
});

function onRmpChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.start) {
        document.getElementById('inp-mulai').value = opt.dataset.start;
    }
}

function onRplChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.end) {
        document.getElementById('inp-selesai').value = opt.dataset.end;
    }
}
</script>
<?= $this->endSection() ?>
