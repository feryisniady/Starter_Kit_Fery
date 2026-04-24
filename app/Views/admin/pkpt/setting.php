<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Header PKPT</h1>
        <p>Dokumen dasar Program Kerja Pengawasan Tahunan per tahun anggaran</p>
    </div>
    <div class="page-actions">
        <a href="/admin/pkpt/hari-libur" class="btn btn-secondary"><i class="fas fa-calendar-xmark"></i> Hari Libur</a>
        <a href="/admin/pkpt" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke PKPT</a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:24px;align-items:flex-start">

    <!-- Form Input Header PKPT -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-file-contract"></i> Input Header PKPT</h3></div>
        <div class="card-body">
            <form action="/admin/pkpt/setting/store" method="POST" id="form-header">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Tahun Anggaran <span style="color:red">*</span></label>
                    <input type="number" name="tahun" id="inp-tahun" class="form-control" required
                           value="<?= date('Y') ?>" min="2020" max="2099">
                </div>

                <!-- Info HP Dinamis -->
                <div id="hp-info" style="background:#f1f5f9;border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px">
                    <div style="font-weight:600;margin-bottom:6px;color:#475569"><i class="fas fa-calculator"></i> Estimasi Hari Kerja Efektif</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center">
                        <div>
                            <div id="hp-senin-jumat" style="font-size:20px;font-weight:700;color:#6366f1">—</div>
                            <div style="font-size:10px;color:#94a3b8">Senin–Jumat</div>
                        </div>
                        <div>
                            <div id="hp-libur" style="font-size:20px;font-weight:700;color:#ef4444">—</div>
                            <div style="font-size:10px;color:#94a3b8">Hari Libur</div>
                        </div>
                        <div>
                            <div id="hp-kerja" style="font-size:20px;font-weight:700;color:#22c55e">—</div>
                            <div style="font-size:10px;color:#94a3b8">Hari Kerja</div>
                        </div>
                    </div>
                    <div style="margin-top:8px;font-size:11px;color:#94a3b8;text-align:center">
                        Otomatis dari data hari libur. Isi override manual jika perlu.
                    </div>
                </div>

                <div class="form-group">
                    <label>Override Total HP (opsional)</label>
                    <input type="number" name="total_hp_tahunan" class="form-control" min="1"
                           placeholder="Kosongkan = otomatis dari hari libur">
                    <small class="text-muted">Isi jika ingin mengganti perhitungan otomatis</small>
                </div>

                <div class="form-group">
                    <label>Tarif HP per Hari (Rp) <span style="color:red">*</span></label>
                    <input type="number" name="tarif_hp" class="form-control" required value="160000" min="1">
                </div>

                <div class="form-group">
                    <label>Nomor SK PKPT</label>
                    <input type="text" name="nomor_pkpt" class="form-control"
                           placeholder="100.3.3.2/632/KEP/434.013/2025">
                    <small class="text-muted">Wajib diisi sebelum PKPT bisa disetujui</small>
                </div>

                <div class="form-group">
                    <label>Tanggal Penetapan PKPT</label>
                    <input type="date" name="tanggal_pkpt" class="form-control">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Header PKPT</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Header PKPT -->
    <div>
    <?php foreach($settings as $s):
        $hp = $hpRingkasan[$s['tahun']] ?? ['senin_jumat'=>0,'libur_nasional'=>0,'hari_kerja'=>0];
        $sc = $statusColor[$s['status']] ?? 'secondary';
        $sl = $statusLabel[$s['status']] ?? $s['status'];
    ?>
    <div class="card mb-3" style="border-left:4px solid <?= $s['status']==='disetujui' ? '#22c55e' : '#f59e0b' ?>">
        <div class="card-body">
            <!-- Baris atas: tahun + status -->
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
                <div>
                    <h3 style="margin:0;font-size:18px;font-weight:700">PKPT Tahun <?= $s['tahun'] ?></h3>
                    <?php if($s['nomor_pkpt']): ?>
                    <div style="font-size:12px;color:#64748b;margin-top:2px"><?= esc($s['nomor_pkpt']) ?></div>
                    <?php endif; ?>
                    <?php if($s['tanggal_pkpt']): ?>
                    <div style="font-size:12px;color:#64748b"><?= date('d F Y', strtotime($s['tanggal_pkpt'])) ?></div>
                    <?php endif; ?>
                </div>
                <span class="badge badge-<?= $sc ?>" style="font-size:13px;padding:6px 14px"><?= $sl ?></span>
            </div>

            <!-- HP Stats -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px">
                <div style="text-align:center;background:#f8fafc;border-radius:6px;padding:8px">
                    <div style="font-size:18px;font-weight:700;color:#6366f1"><?= $hp['senin_jumat'] ?></div>
                    <div style="font-size:10px;color:#94a3b8">Senin–Jumat</div>
                </div>
                <div style="text-align:center;background:#f8fafc;border-radius:6px;padding:8px">
                    <div style="font-size:18px;font-weight:700;color:#ef4444"><?= $hp['libur_nasional'] ?></div>
                    <div style="font-size:10px;color:#94a3b8">Hari Libur</div>
                </div>
                <div style="text-align:center;background:#f8fafc;border-radius:6px;padding:8px">
                    <div style="font-size:18px;font-weight:700;color:#22c55e"><?= $hp['hari_kerja'] ?></div>
                    <div style="font-size:10px;color:#94a3b8">HP Efektif</div>
                </div>
                <div style="text-align:center;background:#f8fafc;border-radius:6px;padding:8px">
                    <div style="font-size:16px;font-weight:700;color:#0ea5e9">Rp<?= number_format($s['tarif_hp']/1000) ?>rb</div>
                    <div style="font-size:10px;color:#94a3b8">Tarif/HP</div>
                </div>
            </div>

            <!-- Aksi -->
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="/admin/pkpt?tahun=<?= $s['tahun'] ?>" class="btn btn-xs btn-primary">
                    <i class="fas fa-list-check"></i> Lihat PKPT
                </a>
                <?php if($s['status'] === 'draft'): ?>
                    <?php if($s['nomor_pkpt'] && $s['tanggal_pkpt']): ?>
                    <form action="/admin/pkpt/setting/approve/<?= $s['id'] ?>" method="POST" style="display:inline"
                          data-confirm="Setujui PKPT tahun <b><?= $s['tahun'] ?></b>? Pastikan nomor dan tanggal sudah benar."
                          data-confirm-title="Setujui PKPT?"
                          data-confirm-btn="<i class='fas fa-check'></i>&nbsp;Ya, Setujui">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-xs btn-success">
                            <i class="fas fa-check"></i> Setujui (Bupati)
                        </button>
                    </form>
                    <?php else: ?>
                    <span style="font-size:12px;color:#f59e0b"><i class="fas fa-triangle-exclamation"></i> Isi nomor & tanggal untuk disetujui</span>
                    <?php endif; ?>
                <?php else: ?>
                    <form action="/admin/pkpt/setting/revok/<?= $s['id'] ?>" method="POST" style="display:inline"
                          data-confirm="Persetujuan PKPT tahun <b><?= $s['tahun'] ?></b> akan dibatalkan. Status kembali ke Draft."
                          data-confirm-title="Batalkan Persetujuan?"
                          data-confirm-btn="<i class='fas fa-undo'></i>&nbsp;Ya, Batalkan">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-xs btn-outline-secondary">
                            <i class="fas fa-undo"></i> Revok
                        </button>
                    </form>
                <?php endif; ?>
                <button class="btn btn-xs btn-danger btn-del" data-id="<?= $s['id'] ?>" style="margin-left:auto">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($settings)): ?>
    <div class="card">
        <div class="card-body" style="text-align:center;color:#94a3b8;padding:32px">
            <i class="fas fa-file-contract" style="font-size:36px;display:block;margin-bottom:8px"></i>
            Belum ada Header PKPT. Isi form di kiri untuk membuat.
        </div>
    </div>
    <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken = '<?= csrf_hash() ?>';
const csrfName  = '<?= csrf_token() ?>';

function loadHpInfo(tahun) {
    $.get('/admin/pkpt/hari-libur/hitung-hp?tahun=' + tahun, function(res) {
        $('#hp-senin-jumat').text(res.senin_jumat || '—');
        $('#hp-libur').text(res.libur_nasional || '—');
        $('#hp-kerja').text(res.hari_kerja || '—');
    });
}

$(function() {
    loadHpInfo($('#inp-tahun').val());
    $('#inp-tahun').on('change', function() { loadHpInfo($(this).val()); });
});

$(document).on('click', '.btn-del', function() {
    const id = $(this).data('id');
    swalConfirm({
        title: 'Hapus Header PKPT?',
        html: 'Semua PKPT per irban pada tahun ini akan <b>terpengaruh</b>. Tindakan ini tidak bisa dibatalkan.',
        icon: 'warning',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fas fa-trash"></i>&nbsp;Ya, Hapus',
    }, () => {
        $.post('/admin/pkpt/setting/delete/' + id, { [csrfName]: csrfToken }, res => {
            if (res.success) location.reload();
        });
    });
});
</script>
<?= $this->endSection() ?>
