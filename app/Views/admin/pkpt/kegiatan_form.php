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
                        <textarea name="tujuan_sasaran" class="form-control" rows="3" required
                                  data-wysiwyg data-wysiwyg-height="90px"
                                  placeholder="Tujuan dan sasaran pengawasan..."><?= old('tujuan_sasaran', $row['tujuan_sasaran'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Ruang Lingkup</label>
                        <textarea name="ruang_lingkup" class="form-control" rows="2"
                                  data-wysiwyg data-wysiwyg-height="80px"
                                  placeholder="Ruang lingkup pengawasan..."><?= old('ruang_lingkup', $row['ruang_lingkup'] ?? '') ?></textarea>
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
                    $bulanOpts = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    $mingguOpts = ['Mg-I','Mg-II','Mg-III','Mg-IV'];
                    // Rentang tanggal per minggu
                    $mingguStart = ['Mg-I'=>1,'Mg-II'=>8,'Mg-III'=>15,'Mg-IV'=>22];
                    $mingguEnd   = ['Mg-I'=>7,'Mg-II'=>14,'Mg-III'=>21,'Mg-IV'=>28];

                    // Parse nilai tersimpan → bulan & minggu
                    $curRmp = old('jadwal_rmp', $row['jadwal_rmp'] ?? '');
                    $curRpl = old('jadwal_rpl', $row['jadwal_rpl'] ?? '');
                    // Format tersimpan: "Mg-II Jan 2026" — parse kembali
                    $parseMinggu = function(string $val) use ($bulanOpts) {
                        if (!$val) return ['mg' => '', 'bln' => '', 'thn' => ''];
                        $parts = explode(' ', trim($val));
                        return [
                            'mg'  => $parts[0] ?? '',
                            'bln' => $parts[1] ?? '',
                            'thn' => $parts[2] ?? '',
                        ];
                    };
                    $rmpParts = $parseMinggu($curRmp);
                    $rplParts = $parseMinggu($curRpl);
                    $tahunOpts = [$pkpt['tahun'] - 1, $pkpt['tahun'], $pkpt['tahun'] + 1];
                    ?>
                    <!-- RMP & RPL — Compact Selector: Tahun + Bulan + Minggu -->
                    <div class="form-row-2">
                        <?php foreach ([
                            ['id'=>'rmp','label'=>'Jadwal RMP','sub'=>'Rencana Mulai Penugasan','parts'=>$rmpParts,'field'=>'jadwal_rmp','mulai_id'=>'inp-mulai'],
                            ['id'=>'rpl','label'=>'Jadwal RPL','sub'=>'Rencana Pelaksanaan Lapangan','parts'=>$rplParts,'field'=>'jadwal_rpl','mulai_id'=>'inp-selesai'],
                        ] as $sel): ?>
                        <div class="form-group">
                            <label><?= $sel['label'] ?>
                                <small style="color:#94a3b8;font-weight:400">(<?= $sel['sub'] ?>)</small>
                            </label>
                            <!-- Hidden field yang dikirim ke server -->
                            <input type="hidden" name="<?= $sel['field'] ?>" id="hid-<?= $sel['id'] ?>" value="<?= esc($sel['parts']['mg'] ? $sel['parts']['mg'].' '.$sel['parts']['bln'].' '.$sel['parts']['thn'] : '') ?>">
                            <!-- 3 komponen visual -->
                            <div style="display:flex;gap:6px">
                                <select id="sel-<?= $sel['id'] ?>-mg" class="form-control form-control-sm"
                                        style="width:90px" onchange="updateJadwal('<?= $sel['id'] ?>')">
                                    <option value="">Minggu</option>
                                    <?php foreach ($mingguOpts as $mg): ?>
                                    <option value="<?= $mg ?>" <?= $sel['parts']['mg'] === $mg ? 'selected':'' ?>><?= $mg ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select id="sel-<?= $sel['id'] ?>-bln" class="form-control form-control-sm"
                                        style="flex:1" onchange="updateJadwal('<?= $sel['id'] ?>')">
                                    <option value="">Bulan</option>
                                    <?php foreach ($bulanOpts as $bln): ?>
                                    <option value="<?= $bln ?>" <?= $sel['parts']['bln'] === $bln ? 'selected':'' ?>><?= $bln ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select id="sel-<?= $sel['id'] ?>-thn" class="form-control form-control-sm"
                                        style="width:82px" onchange="updateJadwal('<?= $sel['id'] ?>')">
                                    <option value="">Tahun</option>
                                    <?php foreach ($tahunOpts as $thn): ?>
                                    <option value="<?= $thn ?>" <?= $sel['parts']['thn'] == $thn ? 'selected':'' ?>><?= $thn ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Preview nilai terpilih -->
                            <div id="preview-<?= $sel['id'] ?>" style="font-size:11px;color:#6366f1;margin-top:4px;min-height:16px">
                                <?php if ($sel['parts']['mg']): ?>
                                <i class="fas fa-calendar-check"></i>
                                <?= $sel['parts']['mg'].' '.$sel['parts']['bln'].' '.$sel['parts']['thn'] ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
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
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
                    <h3 class="card-title"><i class="fas fa-building"></i> Entitas / OPD</h3>
                    <span id="entitas-counter" style="font-size:11px;color:#6366f1;font-weight:600"></span>
                </div>
                <div class="card-body" style="padding:10px 14px">
                    <!-- Search filter -->
                    <div style="position:relative;margin-bottom:8px">
                        <i class="fas fa-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:12px"></i>
                        <input type="text" id="cari-entitas" placeholder="Cari OPD..."
                               class="form-control form-control-sm"
                               style="padding-left:28px"
                               oninput="filterEntitas(this.value)">
                    </div>
                    <!-- Tombol pilih semua / hapus semua -->
                    <div style="display:flex;gap:8px;margin-bottom:8px">
                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="toggleSemuaEntitas(true)">
                            <i class="fas fa-check-square"></i> Semua
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleSemuaEntitas(false)">
                            <i class="fas fa-square"></i> Hapus Semua
                        </button>
                    </div>
                    <!-- Daftar entitas dengan scroll -->
                    <div id="entitas-list" style="max-height:280px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;padding:4px 0">
                    <?php
                    $selectedEntitas = array_column($row['entitas'] ?? [], 'entitas_id');
                    foreach($entitas as $e): ?>
                    <label class="entitas-item" style="display:flex;align-items:center;gap:10px;padding:7px 10px;font-size:13px;cursor:pointer;border-bottom:1px solid #f1f5f9;margin:0"
                           onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <input type="checkbox" name="entitas_ids[]" value="<?= $e['id'] ?>"
                               class="entitas-chk"
                               onchange="updateEntitasCounter()"
                               <?= in_array($e['id'], $selectedEntitas) ? 'checked' : '' ?>>
                        <div style="flex:1;min-width:0">
                            <div class="entitas-nama" style="font-weight:500;line-height:1.3"><?= esc($e['nama']) ?></div>
                            <?php if(!empty($e['kode'])): ?>
                            <div style="font-size:10px;color:#94a3b8"><?= esc($e['kode']) ?></div>
                            <?php endif; ?>
                        </div>
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
    const pct   = sisa > 0 ? Math.min(100, Math.round(hp / sisa * 100)) : (hp > 0 ? 100 : 0);

    let badge = '';
    if (over)      badge = ' <span style="background:#fee2e2;color:#991b1b;border-radius:10px;padding:0 7px;font-weight:700">+' + (hp-sisa) + ' melebihi!</span>';
    else if (warn) badge = ' <span style="background:#fef9c3;color:#854d0e;border-radius:10px;padding:0 7px">⚠ mendekati batas</span>';

    const html =
        '<div style="display:flex;align-items:center;gap:5px;margin-top:4px;font-size:11px;flex-wrap:nowrap">' +
        '<i class="fas ' + icon + '" style="color:' + color + ';font-size:10px;flex-shrink:0"></i>' +
        '<span style="color:#64748b;white-space:nowrap">Sisa:</span>' +
        '<strong style="color:' + color + ';white-space:nowrap">' + sisa + ' hr</strong>' +
        '<div style="height:4px;width:48px;background:#e2e8f0;border-radius:2px;flex-shrink:0;overflow:hidden">' +
        '<div style="height:100%;width:' + pct + '%;background:' + color + ';border-radius:2px;transition:width .3s"></div>' +
        '</div>' +
        badge +
        '</div>';
    $el.html(html);
}

$(document).ready(function() {
    hitungTotal();
    // Load sisa HP untuk semua baris yang sudah ada
    $('.sdm-select').each(function() {
        if ($(this).val()) updateSisaHp(this);
    });
});

// ── RMP / RPL: Compact 3-kolom selector ────────────────────────────────
const mingguStart = {'Mg-I':1,'Mg-II':8,'Mg-III':15,'Mg-IV':22};
const mingguEnd   = {'Mg-I':7,'Mg-II':14,'Mg-III':21,'Mg-IV':28};
const bulanNum    = {'Jan':1,'Feb':2,'Mar':3,'Apr':4,'Mei':5,'Jun':6,'Jul':7,'Agu':8,'Sep':9,'Okt':10,'Nov':11,'Des':12};

function updateJadwal(id) {
    const mg  = document.getElementById('sel-'+id+'-mg').value;
    const bln = document.getElementById('sel-'+id+'-bln').value;
    const thn = document.getElementById('sel-'+id+'-thn').value;

    const label = (mg && bln && thn) ? mg+' '+bln+' '+thn : '';
    document.getElementById('hid-'+id).value = label;

    // Update preview
    const prev = document.getElementById('preview-'+id);
    if (label) {
        prev.innerHTML = '<i class="fas fa-calendar-check" style="color:#6366f1"></i> <strong style="color:#6366f1">'+label+'</strong>';

        // Auto-fill tanggal mulai/selesai
        if (mg && bln && thn) {
            const m    = bulanNum[bln];
            const y    = parseInt(thn);
            const sDay = mingguStart[mg] || 1;
            const eDay = Math.min(mingguEnd[mg] || 7, new Date(y, m, 0).getDate());
            const pad  = n => String(n).padStart(2,'0');
            const dateStart = y+'-'+pad(m)+'-'+pad(sDay);
            const dateEnd   = y+'-'+pad(m)+'-'+pad(eDay);

            if (id === 'rmp') document.getElementById('inp-mulai').value   = dateStart;
            if (id === 'rpl') document.getElementById('inp-selesai').value = dateEnd;
        }
    } else {
        prev.innerHTML = '';
    }
}

// Inisialisasi preview saat load (untuk nilai yang sudah tersimpan)
document.addEventListener('DOMContentLoaded', function() {
    ['rmp','rpl'].forEach(id => {
        const hid = document.getElementById('hid-'+id);
        if (hid && hid.value) {
            const prev = document.getElementById('preview-'+id);
            prev.innerHTML = '<i class="fas fa-calendar-check" style="color:#6366f1"></i> <strong style="color:#6366f1">'+hid.value+'</strong>';
        }
    });
    updateEntitasCounter();
});

// ── Entitas search & filter ─────────────────────────────────────────────
function filterEntitas(q) {
    const keyword = q.toLowerCase().trim();
    document.querySelectorAll('#entitas-list .entitas-item').forEach(function(item) {
        const nama = item.querySelector('.entitas-nama').textContent.toLowerCase();
        item.style.display = (!keyword || nama.includes(keyword)) ? '' : 'none';
    });
}

function toggleSemuaEntitas(check) {
    document.querySelectorAll('#entitas-list .entitas-chk:not([style*="display:none"])').forEach(function(chk) {
        const item = chk.closest('.entitas-item');
        if (item.style.display !== 'none') chk.checked = check;
    });
    updateEntitasCounter();
}

function updateEntitasCounter() {
    const total   = document.querySelectorAll('.entitas-chk').length;
    const checked = document.querySelectorAll('.entitas-chk:checked').length;
    const el = document.getElementById('entitas-counter');
    if (el) {
        el.textContent = checked > 0 ? checked+' / '+total+' dipilih' : total+' OPD tersedia';
        el.style.color = checked > 0 ? '#6366f1' : '#94a3b8';
    }
}
</script>
<?= $this->endSection() ?>
