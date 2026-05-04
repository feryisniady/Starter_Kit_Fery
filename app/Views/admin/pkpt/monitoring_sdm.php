<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$totalSdm      = count($sdmRows);
$totalAman     = count(array_filter($sdmRows, fn($r) => $r['status'] === 'aman'));
$totalPeringatan = count(array_filter($sdmRows, fn($r) => $r['status'] === 'peringatan'));
$totalMelebihi = count(array_filter($sdmRows, fn($r) => $r['status'] === 'melebihi'));
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-chart-bar"></i> Monitoring Anggaran Waktu SDM</h1>
        <p>Pemantauan HP PKPT, Alokasi SPT, dan Realisasi per SDM — Tahun <?= $tahun ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/pkpt" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke PKPT
        </a>
    </div>
</div>

<!-- Filter Form -->
<div class="card mb-3">
    <div class="card-body" style="padding:14px 20px">
        <form method="GET" action="/admin/pkpt/monitoring-sdm" id="form-filter" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div>
                <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px">Tahun</label>
                <select name="tahun" class="form-control" style="min-width:100px">
                    <?php foreach($tahunList as $t): ?>
                    <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                    <?php if(empty($tahunList)): ?>
                    <option value="<?= $tahun ?>" selected><?= $tahun ?></option>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px">Irban</label>
                <select name="irban_id" class="form-control" style="min-width:180px">
                    <option value="0" <?= $filterIrban == 0 ? 'selected' : '' ?>>Semua Irban</option>
                    <?php foreach($irbanList as $ir): ?>
                    <option value="<?= $ir['id'] ?>" <?= $filterIrban == $ir['id'] ? 'selected' : '' ?>>
                        <?= esc($ir['kode']) ?> — <?= esc($ir['nama']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="/admin/pkpt/monitoring-sdm?tahun=<?= $tahun ?>" class="btn btn-secondary" style="margin-left:6px">
                    <i class="fas fa-rotate-right"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px">
    <div class="monitor-stat-card" style="border-left:4px solid #6366f1">
        <div class="stat-label">Total SDM Aktif</div>
        <div class="stat-value" style="color:#6366f1"><?= $totalSdm ?></div>
        <div class="stat-sub">ditampilkan</div>
    </div>
    <div class="monitor-stat-card" style="border-left:4px solid #22c55e">
        <div class="stat-label">SDM Aman</div>
        <div class="stat-value" style="color:#22c55e"><?= $totalAman ?></div>
        <div class="stat-sub">HP &lt; 80% efektif</div>
    </div>
    <div class="monitor-stat-card" style="border-left:4px solid #f59e0b">
        <div class="stat-label">SDM Peringatan</div>
        <div class="stat-value" style="color:#f59e0b"><?= $totalPeringatan ?></div>
        <div class="stat-sub">HP 80–99% efektif</div>
    </div>
    <div class="monitor-stat-card" style="border-left:4px solid #ef4444">
        <div class="stat-label">SDM Melebihi</div>
        <div class="stat-value" style="color:#ef4444"><?= $totalMelebihi ?></div>
        <div class="stat-sub">HP &gt; 100% efektif</div>
    </div>
</div>

<!-- HP Efektif Info Box -->
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#1d4ed8;display:flex;align-items:center;gap:10px">
    <i class="fas fa-circle-info" style="font-size:16px;flex-shrink:0"></i>
    <span>
        <strong>HP Efektif Tahun <?= $tahun ?>: <?= number_format($hpEfektif) ?> hari</strong>
        &mdash; Dihitung dari hari kerja Senin–Jumat dikurangi hari libur nasional/cuti bersama.
        Batas <em>Peringatan</em> = <?= round($hpEfektif * 0.8) ?> hari (80%).
    </span>
</div>

<!-- Main Table -->
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3 class="card-title"><i class="fas fa-table"></i> Data Monitoring Per SDM</h3>
        <span style="font-size:12px;color:#94a3b8"><?= count($sdmRows) ?> SDM ditampilkan</span>
    </div>
    <div class="card-body" style="padding:0;overflow-x:auto">
        <?php if(empty($sdmRows)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8">
            <i class="fas fa-users" style="font-size:36px;margin-bottom:10px;display:block"></i>
            <p style="margin:0">Tidak ada data SDM untuk filter yang dipilih.</p>
        </div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                    <th style="padding:10px 12px;text-align:center;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;width:40px">#</th>
                    <th style="padding:10px 12px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">SDM</th>
                    <th style="padding:10px 12px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;min-width:160px">HP PKPT</th>
                    <th style="padding:10px 12px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;min-width:140px">HP Alokasi SPT</th>
                    <th style="padding:10px 12px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;min-width:140px">HP Realisasi</th>
                    <th style="padding:10px 12px;text-align:center;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;width:90px">Sisa HP</th>
                    <th style="padding:10px 12px;text-align:center;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;width:100px">Status</th>
                    <th style="padding:10px 12px;text-align:center;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;width:70px">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($sdmRows as $i => $row):
                $pctPkpt     = $hpEfektif > 0 ? min(100, round($row['hp_pkpt'] / $hpEfektif * 100)) : 0;
                $pctAlokasi  = $hpEfektif > 0 ? min(100, round($row['hp_alokasi'] / $hpEfektif * 100)) : 0;
                $pctRealisasi= $row['hp_alokasi'] > 0 ? min(100, round($row['hp_realisasi'] / $row['hp_alokasi'] * 100)) : 0;

                if ($row['status'] === 'melebihi') {
                    $barColor = '#ef4444';
                } elseif ($row['status'] === 'peringatan') {
                    $barColor = '#f59e0b';
                } else {
                    $barColor = '#22c55e';
                }
                $alokasiColor   = $pctAlokasi >= 100 ? '#ef4444' : ($pctAlokasi >= 80 ? '#f59e0b' : '#0ea5e9');
                $realisasiColor = $pctRealisasi >= 100 ? '#22c55e' : ($pctRealisasi >= 50 ? '#0ea5e9' : '#94a3b8');
            ?>
            <tr style="border-bottom:1px solid #f1f5f9" class="sdm-row" data-irban="<?= $row['irban_id'] ?>">
                <td style="padding:10px 12px;text-align:center;color:#94a3b8;font-size:12px"><?= $i + 1 ?></td>
                <td style="padding:10px 12px">
                    <div style="font-weight:600;color:#1e293b;font-size:13px"><?= esc($row['sdm_nama']) ?></div>
                    <div style="margin-top:3px">
                        <span class="badge badge-primary" style="font-size:10px"><?= esc($row['irban_kode']) ?></span>
                        <span style="font-size:11px;color:#64748b;margin-left:4px"><?= esc($row['irban_nama']) ?></span>
                    </div>
                </td>
                <td style="padding:10px 12px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <span style="font-weight:700;color:<?= $barColor ?>;font-size:13px"><?= $row['hp_pkpt'] ?></span>
                        <span style="font-size:11px;color:#94a3b8">/ <?= $hpEfektif ?> hari</span>
                        <span style="font-size:11px;color:<?= $barColor ?>;margin-left:auto"><?= $pctPkpt ?>%</span>
                    </div>
                    <div class="hp-bar-wrap">
                        <div class="hp-bar-inner" style="width:<?= $pctPkpt ?>%;background:<?= $barColor ?>"></div>
                    </div>
                </td>
                <td style="padding:10px 12px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <span style="font-weight:700;color:<?= $alokasiColor ?>;font-size:13px"><?= $row['hp_alokasi'] ?></span>
                        <span style="font-size:11px;color:#94a3b8">hari</span>
                        <span style="font-size:11px;color:<?= $alokasiColor ?>;margin-left:auto"><?= $pctAlokasi ?>%</span>
                    </div>
                    <div class="hp-bar-wrap">
                        <div class="hp-bar-inner" style="width:<?= $pctAlokasi ?>%;background:<?= $alokasiColor ?>"></div>
                    </div>
                </td>
                <td style="padding:10px 12px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <span style="font-weight:700;color:<?= $realisasiColor ?>;font-size:13px"><?= $row['hp_realisasi'] ?></span>
                        <span style="font-size:11px;color:#94a3b8">hari</span>
                        <span style="font-size:11px;color:<?= $realisasiColor ?>;margin-left:auto"><?= $pctRealisasi ?>%</span>
                    </div>
                    <div class="hp-bar-wrap">
                        <div class="hp-bar-inner" style="width:<?= $pctRealisasi ?>%;background:<?= $realisasiColor ?>"></div>
                    </div>
                </td>
                <td style="padding:10px 12px;text-align:center">
                    <span style="font-weight:700;font-size:14px;color:<?= $row['sisa_hp'] < 0 ? '#ef4444' : '#22c55e' ?>">
                        <?= $row['sisa_hp'] < 0 ? $row['sisa_hp'] : '+' . $row['sisa_hp'] ?>
                    </span>
                    <div style="font-size:10px;color:#94a3b8">hari</div>
                </td>
                <td style="padding:10px 12px;text-align:center">
                    <?php if($row['status'] === 'melebihi'): ?>
                    <span class="badge-melebihi"><i class="fas fa-circle-exclamation"></i> Melebihi</span>
                    <?php elseif($row['status'] === 'peringatan'): ?>
                    <span class="badge-peringatan"><i class="fas fa-triangle-exclamation"></i> Peringatan</span>
                    <?php else: ?>
                    <span class="badge-aman"><i class="fas fa-circle-check"></i> Aman</span>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 12px;text-align:center">
                    <button class="btn btn-sm btn-info btn-detail"
                            data-sdm-id="<?= $row['id'] ?>"
                            data-sdm-nama="<?= esc($row['sdm_nama']) ?>"
                            data-tahun="<?= $tahun ?>"
                            title="Detail per kegiatan">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail SDM -->
<div id="modal-detail-sdm" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:820px;padding:0;overflow:hidden">
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg,#4f46e5 0%,#6366f1 100%);padding:16px 20px 14px;position:relative">
            <button id="btn-close-detail"
                    style="position:absolute;top:10px;right:12px;background:rgba(255,255,255,.2);border:none;color:#fff;width:26px;height:26px;border-radius:50%;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-times"></i>
            </button>
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:38px;height:38px;border-radius:9px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-user-tie" style="color:#fff;font-size:16px"></i>
                </div>
                <div>
                    <h3 id="modal-sdm-title" style="color:#fff;margin:0;font-size:14px;font-weight:700">Detail SDM</h3>
                    <p id="modal-sdm-sub" style="color:rgba(255,255,255,.8);margin:2px 0 0;font-size:11px">Rincian per Kegiatan</p>
                </div>
            </div>
        </div>
        <!-- Modal Body -->
        <div id="modal-detail-body" style="padding:18px 20px;max-height:70vh;overflow-y:auto">
            <div style="text-align:center;padding:30px;color:#94a3b8">
                <i class="fas fa-spinner fa-spin"></i> Memuat data...
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$(function() {
    // Auto-submit filter on select change
    $('#form-filter select').on('change', function() {
        $('#form-filter').submit();
    });

    // Detail button click
    $(document).on('click', '.btn-detail', function() {
        const sdmId   = $(this).data('sdm-id');
        const sdmNama = $(this).data('sdm-nama');
        const tahun   = $(this).data('tahun');

        $('#modal-sdm-title').text('Detail — ' + sdmNama);
        $('#modal-sdm-sub').text('Tahun ' + tahun + ' · Rincian per Kegiatan PKPT');
        $('#modal-detail-body').html('<div style="text-align:center;padding:30px;color:#94a3b8"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
        $('#modal-detail-sdm').show();

        $.get('/admin/pkpt/monitoring-sdm/detail', { sdm_id: sdmId, tahun: tahun }, function(resp) {
            if (!resp.success) {
                $('#modal-detail-body').html('<div style="text-align:center;padding:20px;color:#ef4444"><i class="fas fa-circle-exclamation"></i> ' + (resp.message || 'Gagal memuat data.') + '</div>');
                return;
            }

            const d = resp;
            let html = '';

            if (!d.kegiatan || d.kegiatan.length === 0) {
                html = '<div style="text-align:center;padding:30px;color:#94a3b8"><i class="fas fa-folder-open" style="font-size:32px;margin-bottom:8px;display:block"></i><p style="margin:0">Tidak ada kegiatan PKPT untuk SDM ini pada tahun ' + tahun + '.</p></div>';
            } else {
                html = '<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:12px">';
                html += '<thead><tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">';
                html += '<th style="padding:8px 10px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;width:100px">Kode</th>';
                html += '<th style="padding:8px 10px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Nama Kegiatan</th>';
                html += '<th style="padding:8px 10px;text-align:center;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;width:80px">HP PKPT</th>';
                html += '<th style="padding:8px 10px;text-align:center;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;width:80px">HP SPT</th>';
                html += '<th style="padding:8px 10px;text-align:center;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;width:90px">HP Realisasi</th>';
                html += '<th style="padding:8px 10px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">SPT / Rekomendasi</th>';
                html += '</tr></thead><tbody>';

                d.kegiatan.forEach(function(k) {
                    const pct = k.hp_pkpt > 0 ? Math.min(100, Math.round(k.hp_realisasi / k.hp_pkpt * 100)) : 0;
                    let barColor = pct >= 100 ? '#22c55e' : (pct >= 50 ? '#0ea5e9' : '#94a3b8');

                    html += '<tr style="border-bottom:1px solid #f1f5f9">';
                    html += '<td style="padding:8px 10px"><span class="badge badge-primary" style="font-size:10px">' + k.kode_kegiatan + '</span></td>';
                    html += '<td style="padding:8px 10px">';
                    html += '<div style="font-weight:500;color:#1e293b">' + escHtml(k.area_pengawasan || k.jenis_pengawasan) + '</div>';
                    if (k.area_pengawasan && k.jenis_pengawasan) {
                        html += '<div style="font-size:11px;color:#64748b">' + escHtml(k.jenis_pengawasan) + '</div>';
                    }
                    html += '</td>';
                    html += '<td style="padding:8px 10px;text-align:center;font-weight:700;color:#6366f1">' + k.hp_pkpt + '<span style="font-size:10px;color:#94a3b8;font-weight:400"> hr</span></td>';
                    html += '<td style="padding:8px 10px;text-align:center;font-weight:700;color:#0ea5e9">' + k.hp_alokasi + '<span style="font-size:10px;color:#94a3b8;font-weight:400"> hr</span></td>';
                    html += '<td style="padding:8px 10px;text-align:center">';
                    html += '<div style="font-weight:700;color:' + barColor + '">' + k.hp_realisasi + '<span style="font-size:10px;color:#94a3b8;font-weight:400"> hr</span></div>';
                    if (k.hp_pkpt > 0) {
                        html += '<div style="height:4px;background:#f1f5f9;border-radius:2px;margin-top:3px"><div style="height:100%;width:' + pct + '%;background:' + barColor + ';border-radius:2px"></div></div>';
                    }
                    html += '</td>';
                    // SPT sebagai link ke halaman KM
                    let sptHtml = '<span style="color:#94a3b8">—</span>';
                    if (k.spt_ids && k.spt_list) {
                        const ids    = k.spt_ids.split(',');
                        const labels = k.spt_list.split('|');
                        const links  = ids.map((id, i) =>
                            '<a href="/admin/spt/' + id.trim() + '/km" target="_blank" style="color:#6366f1;font-size:11px;white-space:nowrap">' +
                            escHtml(labels[i] ? labels[i].trim() : '#' + id.trim()) + '</a>'
                        );
                        sptHtml = links.join('<br>');
                    }

                    // Rekomendasi KM
                    let rekomHtml = '';
                    if (parseInt(k.hp_pkpt) > 0 && parseInt(k.jumlah_spt) === 0) {
                        rekomHtml = '<div style="margin-top:4px;font-size:10px;background:#fef9c3;color:#854d0e;border-radius:4px;padding:2px 6px;display:inline-block"><i class="fas fa-triangle-exclamation"></i> SPT belum dibuat</div>';
                    } else if (parseInt(k.hp_alokasi) === 0 && parseInt(k.jumlah_spt) > 0) {
                        rekomHtml = '<div style="margin-top:4px;font-size:10px;background:#fef9c3;color:#854d0e;border-radius:4px;padding:2px 6px;display:inline-block"><i class="fas fa-triangle-exclamation"></i> KM-2 belum diisi</div>';
                    } else if (parseInt(k.hp_realisasi) === 0 && parseInt(k.hp_alokasi) > 0) {
                        rekomHtml = '<div style="margin-top:4px;font-size:10px;background:#eff6ff;color:#1d4ed8;border-radius:4px;padding:2px 6px;display:inline-block"><i class="fas fa-info-circle"></i> Realisasi KM-2 belum diisi</div>';
                    }

                    html += '<td style="padding:8px 10px;font-size:11px;color:#475569">' + sptHtml + rekomHtml + '</td>';
                    html += '</tr>';
                });

                // Totals row
                const totPkpt     = d.kegiatan.reduce((s,k) => s + parseInt(k.hp_pkpt || 0), 0);
                const totAlokasi  = d.kegiatan.reduce((s,k) => s + parseInt(k.hp_alokasi || 0), 0);
                const totRealisasi= d.kegiatan.reduce((s,k) => s + parseInt(k.hp_realisasi || 0), 0);
                html += '<tr style="background:#f8fafc;font-weight:700;border-top:2px solid #e2e8f0">';
                html += '<td colspan="2" style="padding:8px 10px;font-size:12px;color:#374151">TOTAL</td>';
                html += '<td style="padding:8px 10px;text-align:center;color:#6366f1">' + totPkpt + ' hr</td>';
                html += '<td style="padding:8px 10px;text-align:center;color:#0ea5e9">' + totAlokasi + ' hr</td>';
                html += '<td style="padding:8px 10px;text-align:center;color:#22c55e">' + totRealisasi + ' hr</td>';
                html += '<td></td>';
                html += '</tr>';

                html += '</tbody></table></div>';
            }

            $('#modal-detail-body').html(html);
        }).fail(function() {
            $('#modal-detail-body').html('<div style="text-align:center;padding:20px;color:#ef4444"><i class="fas fa-circle-exclamation"></i> Terjadi kesalahan saat memuat data.</div>');
        });
    });

    // Close modal
    $('#btn-close-detail').on('click', function() {
        $('#modal-detail-sdm').hide();
    });
    $('#modal-detail-sdm').on('click', function(e) {
        if ($(e.target).is('#modal-detail-sdm')) $(this).hide();
    });
});

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
<?= $this->endSection() ?>
