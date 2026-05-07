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

<?php
$existingSpts  = $existingSpts  ?? [];
$hpAllocated   = $hpAllocated   ?? [];
$suggestedNama = $suggestedNama ?? null;
$isMultiTim    = !empty($existingSpts);
$statusLabel   = \App\Models\SptModel::$statusLabel;
$statusColor   = \App\Models\SptModel::$statusColor;
?>

<?php if ($isMultiTim): ?>
<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:14px 18px;margin-bottom:20px">
    <div style="font-weight:700;color:#92400e;margin-bottom:10px;font-size:13px">
        <i class="fas fa-users-between-lines"></i>
        Kegiatan ini sudah memiliki <?= count($existingSpts) ?> SPT — Anda sedang membuat Tim Baru
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
        <?php foreach ($existingSpts as $es): ?>
        <a href="/admin/spt/<?= $es['id'] ?>" target="_blank"
           style="display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:5px 10px;font-size:12px;color:#1e293b;text-decoration:none">
            <i class="fas fa-file-signature" style="color:#6366f1"></i>
            <strong><?= esc($es['nama_tim'] ?: 'SPT #'.$es['id']) ?></strong>
            <span class="badge badge-<?= $statusColor[$es['status']] ?? 'secondary' ?>" style="font-size:10px">
                <?= $statusLabel[$es['status']] ?? $es['status'] ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($hpAllocated)): ?>
    <div style="font-size:11px;color:#92400e;font-weight:600;margin-bottom:6px">
        <i class="fas fa-clock"></i> HP per anggota — Budget PKPT vs sudah dialokasikan
    </div>
    <?php
    $pkptTim = \Config\Database::connect()
        ->table('pkpt_tim pt')
        ->select('pt.sdm_id, pt.peran, pt.hp_total, s.nama as sdm_nama')
        ->join('sdm s', 's.id = pt.sdm_id')
        ->where('pt.pkpt_kegiatan_id', $kegiatan['id'])
        ->orderBy('pt.urutan')
        ->get()->getResultArray();
    ?>
    <div style="display:flex;flex-wrap:wrap;gap:6px">
        <?php foreach ($pkptTim as $pt):
            $sdmId    = $pt['sdm_id'];
            $budget   = (int)$pt['hp_total'];
            $terpakai = (int)($hpAllocated[$sdmId] ?? 0);
            $sisa     = max(0, $budget - $terpakai);
            $pct      = $budget > 0 ? min(100, round($terpakai / $budget * 100)) : 0;
            $color    = $sisa <= 0 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#22c55e');
        ?>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;min-width:130px;font-size:11px">
            <div style="font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px">
                <?= esc($pt['sdm_nama']) ?>
            </div>
            <div style="color:#64748b;margin:2px 0">Budget: <strong><?= $budget ?> HP</strong></div>
            <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                <div style="flex:1;height:5px;background:#f1f5f9;border-radius:3px;overflow:hidden">
                    <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:3px"></div>
                </div>
                <span style="color:<?= $color ?>;font-weight:700;white-space:nowrap">
                    <?= $sisa ?> sisa
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<form action="<?= $spt ? '/admin/spt/'.$spt['id'].'/update' : '/admin/spt/store/'.$kegiatan['id'] ?>" method="POST">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:flex-start">

        <!-- Kolom kiri -->
        <div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-signature"></i> Data Surat Perintah</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Label Tim <span style="font-size:11px;color:#94a3b8">(opsional — isi jika satu kegiatan dibagi beberapa tim)</span></label>
                        <input type="text" name="nama_tim" class="form-control"
                               placeholder="cth: Tim A, Tim I, Tim Desa Makmur"
                               value="<?= old('nama_tim', $suggestedNama ?? ($spt['nama_tim'] ?? '')) ?>"
                               maxlength="50">
                    </div>
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
                        <textarea name="tujuan" class="form-control" rows="3" required
                                  data-wysiwyg data-wysiwyg-height="90px"><?= old('tujuan', $spt['tujuan'] ?? $kegiatan['tujuan_sasaran'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="spt-tgl-mulai" class="form-control"
                                   value="<?= old('tanggal_mulai', $spt['tanggal_mulai'] ?? $kegiatan['tanggal_mulai'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Selesai Penugasan Lap.
                                <small style="color:#94a3b8;font-size:10px" title="Rencana selesai penugasan lapangan (bukan RPL)"><i class="fas fa-info-circle"></i></small>
                            </label>
                            <input type="date" name="tanggal_selesai" id="spt-tgl-selesai" class="form-control"
                                   value="<?= old('tanggal_selesai', $spt['tanggal_selesai'] ?? $kegiatan['tanggal_selesai'] ?? '') ?>">
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
                               placeholder="Nama Pimpinan SKPD"
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
                                <th style="position:sticky;top:0;z-index:2;background:#f8fafc;padding:7px 6px;border-bottom:2px solid #e2e8f0;font-weight:600;text-align:center;width:54px" title="Auto-dihitung dari Total HP PKPT dikurangi Desk">Field <i class="fas fa-lock" style="font-size:9px;color:#94a3b8"></i></th>
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
                            <?php
                            // hp_total per orang dari PKPT — dipakai JS untuk auto-adjust Field
                            $hpDesk  = (int)($t['hp_desk']  ?? 1);
                            $hpField = (int)($t['hp_field'] ?? 0);
                            $hpTotal = ($t['from_pkpt'] ?? 1) ? $hpDesk + $hpField : 0;
                            ?>
                            <td style="padding:4px 6px;text-align:center">
                                <input type="number" name="tim_hp_desk[]" class="form-control form-control-sm hp-desk-spt"
                                       value="<?= $hpDesk ?>" min="0" max="<?= $hpTotal ?: 9999 ?>"
                                       data-hp-total="<?= $hpTotal ?>"
                                       style="width:48px;text-align:center;padding:3px 4px;height:28px;font-size:12px">
                            </td>
                            <td style="padding:4px 6px;text-align:center">
                                <input type="number" name="tim_hp_field[]" class="form-control form-control-sm hp-field-spt"
                                       value="<?= $hpField ?>" min="0"
                                       <?= $hpTotal > 0 ? 'readonly title="Auto-dihitung: Total HP − Desk"' : '' ?>
                                       style="width:48px;text-align:center;padding:3px 4px;height:28px;font-size:12px;<?= $hpTotal > 0 ? 'background:#f1f5f9;color:#64748b;cursor:not-allowed' : '' ?>">
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
                    <div class="mt-1"><strong>Tujuan:</strong> <?= wysiwyg_plain($kegiatan['tujuan_sasaran'], 120) ?></div>
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
    initSelect2($('#spt-tim-rows .spt-tim-row:last select.form-control'));
    updateTimSummary();
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

    // Delete button
    $(document).on('click', '.btn-del-tim', function() {
        $(this).closest('tr').remove();
        updateTimSummary();
    });

    // Desk berubah → Field auto-adjust agar Desk+Field = hp_total dari PKPT
    $(document).on('input', 'input.hp-desk-spt', function() {
        const hpTotal = parseInt($(this).data('hp-total')) || 0;
        if (hpTotal > 0) {
            const desk  = Math.min(parseInt($(this).val()) || 0, hpTotal);
            $(this).val(desk);
            $(this).closest('tr').find('.hp-field-spt').val(hpTotal - desk);
        }
        updateTimSummary();
    });

    // Field manual (baris non-PKPT)
    $(document).on('input', 'input.hp-field-spt:not([readonly])', function() {
        updateTimSummary();
    });

    // ── HP Calculator ─────────────────────────────────────────────────────
    $('#spt-tgl-mulai, #spt-tgl-selesai').on('change', function() {
        refreshHpCalc();
    });

    $('#btn-terapkan-hp').on('click', function() {
        const hp = parseInt($('#hp-calc-count').text()) || 0;
        if (hp <= 0) return;
        if (!confirm('Terapkan ' + hp + ' HP ke semua baris tim?\n(Desk = ' + hp + ', Field = 0 — sesuaikan split setelahnya)')) return;

        $('#spt-tim-rows .spt-tim-row').each(function() {
            const $desk  = $(this).find('input[name="tim_hp_desk[]"]');
            const $field = $(this).find('input[name="tim_hp_field[]"]');
            const hpPkpt = parseInt($desk.data('hp-total')) || 0;
            if (hpPkpt > 0) {
                $desk.data('hp-total', hp).attr('data-hp-total', hp).attr('max', hp);
            }
            $desk.val(hp);
            $field.val(0);
            if ($desk.hasClass('hp-desk-spt')) {
                $desk.trigger('input');
            }
        });
        updateTimSummary();

        $('#btn-terapkan-hp').html('<i class="fas fa-check"></i> Diterapkan!').addClass('btn-success').removeClass('btn-primary');
        setTimeout(function() {
            $('#btn-terapkan-hp').html('<i class="fas fa-magic"></i> Terapkan ke Tim').addClass('btn-primary').removeClass('btn-success');
        }, 2000);
    });

    refreshHpCalc();
});

// ── Kalkulator hari kerja (client-side, data dari server) ─────────────────
const HARI_LIBUR_SPT = <?= json_encode($hariLibur ?? []) ?>;

function hitungHariKerja(mulai, selesai) {
    if (!mulai || !selesai) return {hp: 0, kalender: 0, libur: 0};
    const d1 = new Date(mulai + 'T00:00:00');
    const d2 = new Date(selesai + 'T00:00:00');
    if (d2 < d1) return {hp: 0, kalender: 0, libur: 0};
    let hp = 0, kalender = 0, liburNasional = 0;
    const cur = new Date(d1);
    while (cur <= d2) {
        kalender++;
        const dow = cur.getDay();
        if (dow > 0 && dow < 6) {
            const iso = cur.toISOString().split('T')[0];
            if (HARI_LIBUR_SPT.includes(iso)) liburNasional++;
            else hp++;
        }
        cur.setDate(cur.getDate() + 1);
    }
    return {hp, kalender, libur: liburNasional};
}

function refreshHpCalc() {
    const mulai   = $('#spt-tgl-mulai').val();
    const selesai = $('#spt-tgl-selesai').val();
    const panel   = $('#hp-calc-panel');
    if (!mulai || !selesai) { panel.hide(); return; }
    const r = hitungHariKerja(mulai, selesai);
    if (r.kalender <= 0) { panel.hide(); return; }
    $('#hp-calc-count').text(r.hp);
    const sabmgu = r.kalender - r.hp - r.libur;
    let detail = r.kalender + ' hari kalender';
    if (sabmgu > 0) detail += ' − ' + sabmgu + ' Sab/Min';
    if (r.libur > 0) detail += ' − ' + r.libur + ' libur nasional';
    $('#hp-calc-detail').text('(' + detail + ')');
    panel.show();
}
</script>
<?= $this->endSection() ?>
