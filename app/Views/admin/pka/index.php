<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Program Kerja Audit (PKA)</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$spt['id']) ?> — <?= esc($spt['tujuan']) ?></p>
    </div>
    <div class="page-actions">
        <a href="/admin/spt/<?= $spt['id'] ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="/admin/spt/<?= $spt['id'] ?>/temuan" class="btn btn-primary"><i class="fas fa-exclamation-triangle"></i> Temuan</a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Statistik -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px">
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#6366f1"><?= $stats['total'] ?></div>
            <div style="font-size:12px;color:#64748b">Total Prosedur</div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#22c55e"><?= $stats['selesai'] ?></div>
            <div style="font-size:12px;color:#64748b">Selesai</div>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <div class="card-body" style="padding:16px">
            <div style="font-size:28px;font-weight:700;color:#f59e0b"><?= $stats['belum'] ?></div>
            <div style="font-size:12px;color:#64748b">Belum</div>
        </div>
    </div>
</div>

<!-- Tabel PKA -->
<div class="card mb-3">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-list-check"></i> Prosedur Audit</h3></div>
    <div class="card-body">
        <table id="dt-pka" class="w-100">
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Uraian Prosedur</th>
                    <th width="160">PIC</th>
                    <th width="80">Rencana (HP)</th>
                    <th width="80">Realisasi (HP)</th>
                    <th width="90">Status</th>
                    <th width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($pkaList as $row): ?>
            <tr id="pka-row-<?= $row['id'] ?>">
                <td><?= $row['nomor_urut'] ?></td>
                <td><?= esc($row['uraian_prosedur']) ?></td>
                <td><?= esc($row['pic_nama'] ?? '—') ?></td>
                <td style="text-align:center"><?= $row['rencana_waktu'] ?? '—' ?></td>
                <td style="text-align:center"><?= $row['realisasi_waktu'] ?? '—' ?></td>
                <td>
                    <span class="badge badge-<?= $row['status'] === 'selesai' ? 'success' : 'warning' ?>" id="status-badge-<?= $row['id'] ?>">
                        <?= $row['status'] === 'selesai' ? 'Selesai' : 'Belum' ?>
                    </span>
                </td>
                <td>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-xs btn-<?= $row['status'] === 'selesai' ? 'warning' : 'success' ?> btn-selesai"
                            data-id="<?= $row['id'] ?>" title="Toggle selesai">
                        <i class="fas fa-<?= $row['status'] === 'selesai' ? 'undo' : 'check' ?>"></i>
                    </button>
                    <button class="btn btn-xs btn-primary btn-edit-pka"
                            data-id="<?= $row['id'] ?>"
                            data-uraian="<?= esc($row['uraian_prosedur']) ?>"
                            data-pic="<?= $row['pic_sdm_id'] ?? '' ?>"
                            data-rencana="<?= $row['rencana_waktu'] ?? '' ?>"
                            data-realisasi="<?= $row['realisasi_waktu'] ?? '' ?>">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-xs btn-danger btn-del-pka" data-id="<?= $row['id'] ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php else: ?>
                    <span style="color:#94a3b8;font-size:12px"><i class="fas fa-lock"></i></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Form Tambah -->
<?php if ($canEdit): ?>
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-plus"></i> Tambah Prosedur</h3></div>
    <div class="card-body">
        <form action="/admin/spt/<?= $spt['id'] ?>/pka/store" method="POST">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:1fr 180px 100px 100px auto;gap:12px;align-items:end">
                <div class="form-group mb-0">
                    <label>Uraian Prosedur <span style="color:red">*</span></label>
                    <textarea name="uraian_prosedur" class="form-control" rows="2" required placeholder="Deskripsikan prosedur audit..."></textarea>
                </div>
                <div class="form-group mb-0">
                    <label>PIC</label>
                    <select name="pic_sdm_id" class="form-control">
                        <option value="">— Pilih SDM —</option>
                        <?php foreach($sdmList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= esc($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Rencana (HP)</label>
                    <input type="number" step="0.5" min="0" name="rencana_waktu" class="form-control" placeholder="0">
                </div>
                <div class="form-group mb-0">
                    <label style="visibility:hidden">_</label>
                </div>
                <div class="form-group mb-0">
                    <label style="visibility:hidden">.</label>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Edit PKA -->
<?php if ($canEdit): ?>
<div id="modal-edit-pka" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:540px">
        <div class="modal-header">
            <h3>Edit Prosedur PKA</h3>
            <button class="modal-close" onclick="$('#modal-edit-pka').hide()"><i class="fas fa-times"></i></button>
        </div>
        <form id="form-edit-pka">
            <?= csrf_field() ?>
            <input type="hidden" id="edit-pka-id">
            <div class="form-group">
                <label>Uraian Prosedur</label>
                <textarea id="edit-uraian" name="uraian_prosedur" class="form-control" rows="3"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>PIC</label>
                    <select id="edit-pic" name="pic_sdm_id" class="form-control">
                        <option value="">— Pilih SDM —</option>
                        <?php foreach($sdmList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= esc($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Rencana (HP)</label>
                    <input type="number" step="0.5" min="0" id="edit-rencana" name="rencana_waktu" class="form-control">
                </div>
                <div class="form-group">
                    <label>Realisasi (HP)</label>
                    <input type="number" step="0.5" min="0" id="edit-realisasi" name="realisasi_waktu" class="form-control">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="$('#modal-edit-pka').hide()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken = '<?= csrf_hash() ?>';
const csrfName  = '<?= csrf_token() ?>';

$(function() {
    const dt = $('#dt-pka').DataTable({
        paging: false, language: DT_LANG_ID, order: [],
        columnDefs: [{ orderable: false, targets: [5, 6] }],
    });

    // Toggle selesai
    $(document).on('click', '.btn-selesai', function() {
        const id  = $(this).data('id');
        const btn = $(this);
        $.post('/admin/spt/pka/selesai/' + id, { [csrfName]: csrfToken }, res => {
            if (!res.success) return;
            const done = res.status === 'selesai';
            $('#status-badge-' + id)
                .removeClass('badge-warning badge-success')
                .addClass(done ? 'badge-success' : 'badge-warning')
                .text(done ? 'Selesai' : 'Belum');
            btn.removeClass('btn-success btn-warning')
               .addClass(done ? 'btn-warning' : 'btn-success')
               .html('<i class="fas fa-' + (done ? 'undo' : 'check') + '"></i>');
        });
    });

    // Buka modal edit
    $(document).on('click', '.btn-edit-pka', function() {
        $('#edit-pka-id').val($(this).data('id'));
        $('#edit-uraian').val($(this).data('uraian'));
        $('#edit-pic').val($(this).data('pic'));
        $('#edit-rencana').val($(this).data('rencana'));
        $('#edit-realisasi').val($(this).data('realisasi'));
        $('#modal-edit-pka').show();
    });

    // Submit edit
    $('#form-edit-pka').on('submit', function(e) {
        e.preventDefault();
        const id   = $('#edit-pka-id').val();
        const data = $(this).serialize() + '&' + csrfName + '=' + csrfToken;
        $.post('/admin/spt/pka/update/' + id, data, res => {
            if (res.success) location.reload();
            else alert('Gagal menyimpan.');
        });
    });

    // Hapus PKA
    $(document).on('click', '.btn-del-pka', function() {
        if (!confirm('Hapus prosedur ini?')) return;
        const id  = $(this).data('id');
        const row = $(this).closest('tr');
        $.post('/admin/spt/pka/delete/' + id, { [csrfName]: csrfToken }, res => {
            if (res.success) dt.row(row).remove().draw();
            else alert('Gagal menghapus.');
        });
    });
});
</script>
<?= $this->endSection() ?>
