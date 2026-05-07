<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1><?= esc($title) ?></h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>/temuan" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php
$isEdit     = !empty($temuan);
$action     = $isEdit ? '/admin/spt/temuan/' . $temuan['id'] . '/update' : '/admin/spt/' . $spt['id'] . '/temuan/store';
$old        = fn($f, $def='') => old($f, $isEdit ? ($temuan[$f] ?? $def) : $def);
?>

<form action="<?= $action ?>" method="POST" id="form-temuan">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:flex-start">

        <!-- Kiri: isi temuan -->
        <div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Identitas Temuan</h3></div>
                <div class="card-body">

                    <div class="form-group">
                        <label>Judul Temuan <span style="color:red">*</span></label>
                        <input type="text" name="judul" class="form-control" value="<?= esc($old('judul')) ?>" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div class="form-group">
                            <label>Terkait PKA</label>
                            <select name="pka_id" class="form-control">
                                <option value="">— Tidak terkait —</option>
                                <?php foreach($pkaList as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $old('pka_id') == $p['id'] ? 'selected' : '' ?>>
                                    #<?= $p['nomor_urut'] ?> — <?= esc(mb_substr($p['uraian_prosedur'], 0, 60)) ?>...
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nilai Temuan (Rp)</label>
                            <input type="number" name="nilai_temuan" class="form-control" min="0"
                                   value="<?= esc($old('nilai_temuan', '0')) ?>" placeholder="0">
                            <small class="text-muted">Isi 0 jika tidak ada nilai finansial</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Kode Temuan (PermenpanRB 41/2011)</label>
                        <select name="kode_temuan_id" class="form-control" id="sel-kode"
                                data-rekomen-url="/admin/master/kode-temuan">
                            <option value="">— Pilih kode temuan —</option>
                            <?php foreach($grouped as $jenis => $items): ?>
                            <optgroup label="<?= esc($jenisLabel[$jenis] ?? 'Jenis '.$jenis) ?>">
                                <?php foreach($items as $kt): ?>
                                <option value="<?= $kt['id'] ?>" <?= $old('kode_temuan_id') == $kt['id'] ? 'selected' : '' ?>>
                                    <?= esc($kt['kode']) ?> — <?= esc(mb_substr($kt['uraian'], 0, 80)) ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Panel saran rekomendasi (muncul saat kode temuan dipilih) -->
                    <div id="panel-saran-rekom" style="display:none;margin-top:4px;margin-bottom:8px">
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px 12px">
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px">
                                <i class="fas fa-wand-magic-sparkles" style="color:#15803d;font-size:12px"></i>
                                <span style="font-size:11px;font-weight:600;color:#15803d">Saran Rekomendasi otomatis</span>
                                <span style="font-size:10px;color:#6b7280;margin-left:auto">Klik untuk menambahkan</span>
                            </div>
                            <div id="saran-rekom-list" style="display:flex;flex-wrap:wrap;gap:6px"></div>
                        </div>
                    </div>

                    <?php if($isEdit): ?>
                    <div class="form-group">
                        <label>Status Temuan</label>
                        <select name="status_temuan" class="form-control">
                            <?php foreach($statusLabel as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $old('status_temuan', 'buka') === $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt"></i> Isi Temuan (5W)</h3></div>
                <div class="card-body">

                    <div class="form-group">
                        <label>Kondisi <span style="color:red">*</span></label>
                        <small class="text-muted d-block mb-1">Fakta/keadaan yang ditemukan di lapangan</small>
                        <textarea name="kondisi" class="form-control" rows="4" required><?= esc($old('kondisi')) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Kriteria</label>
                        <small class="text-muted d-block mb-1">Standar, aturan, atau ketentuan yang seharusnya dipenuhi</small>
                        <textarea name="kriteria" class="form-control" rows="3"><?= esc($old('kriteria')) ?></textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div class="form-group">
                            <label>Sebab</label>
                            <textarea name="sebab" class="form-control" rows="3"><?= esc($old('sebab')) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Akibat</label>
                            <textarea name="akibat" class="form-control" rows="3"><?= esc($old('akibat')) ?></textarea>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Kanan: rekomendasi -->
        <div>
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                    <h3 class="card-title" style="margin:0"><i class="fas fa-lightbulb"></i> Rekomendasi</h3>
                    <button type="button" class="btn btn-xs btn-primary" id="btn-add-rekom">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
                <div class="card-body" id="rekom-container">
                    <?php if(empty($rekomendasi)): ?>
                    <div id="rekom-placeholder" style="text-align:center;color:#94a3b8;padding:16px;font-size:13px">
                        Pilih kode temuan untuk saran otomatis,<br>atau klik "+ Tambah" untuk input manual
                    </div>
                    <?php else: ?>
                    <?php foreach($rekomendasi as $i => $r): ?>
                    <div class="rekom-row card mb-2" style="border:1px solid #e2e8f0">
                        <div class="card-body" style="padding:12px">
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                                <strong style="font-size:12px">Rekomendasi <?= $i+1 ?></strong>
                                <button type="button" class="btn btn-xs btn-danger btn-del-rekom"><i class="fas fa-times"></i></button>
                            </div>
                            <textarea name="rekomendasi[<?= $i ?>][isi_rekomendasi]" class="form-control mb-1" rows="2"
                                      placeholder="Isi rekomendasi..."><?= esc($r['isi_rekomendasi']) ?></textarea>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px">
                                <div>
                                    <label style="font-size:11px;margin-bottom:2px">Batas Waktu</label>
                                    <input type="date" name="rekomendasi[<?= $i ?>][batas_waktu]" class="form-control form-control-sm"
                                           value="<?= $r['batas_waktu'] ?? '' ?>">
                                </div>
                                <div>
                                    <label style="font-size:11px;margin-bottom:2px">Nilai (Rp)</label>
                                    <input type="number" name="rekomendasi[<?= $i ?>][nilai_rekomendasi]" class="form-control form-control-sm"
                                           value="<?= $r['nilai_rekomendasi'] ?? 0 ?>" min="0">
                                </div>
                            </div>
                            <?php if($isEdit): ?>
                            <div style="margin-top:6px">
                                <label style="font-size:11px;margin-bottom:2px">Status TL</label>
                                <select name="rekomendasi[<?= $i ?>][status]" class="form-control form-control-sm">
                                    <?php foreach(['belum'=>'Belum','proses'=>'Dalam Proses','selesai'=>'Selesai'] as $k=>$v): ?>
                                    <option value="<?= $k ?>" <?= ($r['status'] ?? 'belum') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top:16px;display:flex;gap:12px;justify-content:flex-end">
                <a href="/admin/spt/<?= $spt['id'] ?>/temuan" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Temuan</button>
            </div>
        </div>

    </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
let rekomIdx = <?= max(count($rekomendasi ?? []) - 1, -1) ?>;

// ── Buat baris rekomendasi baru ──────────────────────────────
function makeRekomRow(idx, prefill) {
    const isi = prefill ? prefill.replace(/`/g, '\\`') : '';
    return `<div class="rekom-row card mb-2" style="border:1px solid #e2e8f0">
        <div class="card-body" style="padding:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <strong style="font-size:12px">Rekomendasi ${idx+1}</strong>
                <button type="button" class="btn btn-xs btn-danger btn-del-rekom"><i class="fas fa-times"></i></button>
            </div>
            <textarea name="rekomendasi[${idx}][isi_rekomendasi]" class="form-control mb-1" rows="2" placeholder="Isi rekomendasi...">${isi}</textarea>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px">
                <div>
                    <label style="font-size:11px;margin-bottom:2px">Batas Waktu</label>
                    <input type="date" name="rekomendasi[${idx}][batas_waktu]" class="form-control form-control-sm">
                </div>
                <div>
                    <label style="font-size:11px;margin-bottom:2px">Nilai (Rp)</label>
                    <input type="number" name="rekomendasi[${idx}][nilai_rekomendasi]" class="form-control form-control-sm" value="0" min="0">
                </div>
            </div>
            <input type="hidden" name="rekomendasi[${idx}][status]" value="belum">
        </div>
    </div>`;
}

// ── Tombol tambah manual ─────────────────────────────────────
$('#btn-add-rekom').on('click', function() {
    rekomIdx++;
    $('#rekom-placeholder').remove();
    $('#rekom-container').append(makeRekomRow(rekomIdx, ''));
});

// ── Hapus baris ──────────────────────────────────────────────
$(document).on('click', '.btn-del-rekom', function() {
    $(this).closest('.rekom-row').remove();
    if ($('#rekom-container .rekom-row').length === 0) {
        $('#rekom-container').prepend(
            '<div id="rekom-placeholder" style="text-align:center;color:#94a3b8;padding:16px;font-size:13px">' +
            'Pilih kode temuan untuk saran otomatis,<br>atau klik "+ Tambah" untuk input manual</div>'
        );
    }
});

// ── Auto-suggest rekomendasi saat kode temuan berubah ────────
$('#sel-kode').on('change', function() {
    const id  = $(this).val();
    const $panel = $('#panel-saran-rekom');
    const $list  = $('#saran-rekom-list');

    if (!id) {
        $panel.hide();
        $list.empty();
        return;
    }

    const url = $(this).data('rekomen-url') + '/' + id + '/rekomen';

    $.ajax({
        url: url,
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
            $list.empty();
            if (!res.success || !res.data.length) {
                $panel.hide();
                return;
            }

            res.data.forEach(function(r) {
                const $badge = $('<button type="button"></button>')
                    .addClass('btn btn-xs')
                    .css({
                        background: '#dcfce7',
                        color: '#166534',
                        border: '1px solid #86efac',
                        borderRadius: '4px',
                        fontSize: '11px',
                        padding: '3px 8px',
                        cursor: 'pointer',
                        transition: 'opacity .15s',
                    })
                    .html('<i class="fas fa-plus" style="margin-right:4px"></i><code style="color:inherit;font-size:10px">' + r.kode + '</code> ' + r.uraian)
                    .on('click', function() {
                        rekomIdx++;
                        $('#rekom-placeholder').remove();
                        $('#rekom-container').append(makeRekomRow(rekomIdx, r.uraian));
                        // Tandai badge sudah dipakai
                        $(this).css({ opacity: '0.4', pointerEvents: 'none' })
                               .find('i').removeClass('fa-plus').addClass('fa-check');
                    });
                $list.append($badge);
            });

            $panel.slideDown(150);
        },
        error: function() {
            $panel.hide();
        }
    });
});

// ── Auto-trigger jika form edit dengan kode sudah dipilih ────
<?php if($isEdit && !empty($old('kode_temuan_id'))): ?>
$(document).ready(function() {
    $('#sel-kode').trigger('change');
});
<?php endif; ?>
</script>
<?= $this->endSection() ?>
