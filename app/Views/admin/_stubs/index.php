<?php
/**
 * ============================================================
 * STUB VIEW — copy ke app/Views/admin/{modul}/index.php
 * Cari-ganti: stub  → {modul}   (url, id, nama variabel)
 *             Stub  → {NamaModul}
 * ============================================================
 */
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- ── PAGE HEADER ─────────────────────────────────────── -->
<div class="page-header">
    <div class="page-title">
        <h1>Judul Modul</h1>
        <p>Sub-judul / deskripsi singkat</p>
    </div>
    <div class="page-actions">
        <?php if(hasPermission('stub.create')): ?>  <!-- hapus baris ini jika tidak pakai permission -->
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ── FLASH MESSAGES ──────────────────────────────────── -->
<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3">
    <i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?>
</div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3">
    <i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?>
</div>
<?php endif; ?>

<!-- ── DATA TABLE ──────────────────────────────────────── -->
<!-- Tombol Excel + Print muncul otomatis via data-url -->
<div class="card">
    <div class="card-body">
        <table id="dt-stub" data-url="/admin/stub/data" class="w-100">
            <thead>
                <tr>
                    <!-- dt-nosort dt-nosearch = nonaktifkan sort & search untuk kolom ini -->
                    <th class="dt-nosort dt-nosearch" data-dt="no"     width="40">#</th>
                    <th                               data-dt="nama"             >Nama</th>
                    <!-- <th                           data-dt="kolom_lain"      >Kolom Lain</th> -->
                    <th class="dt-nosort"             data-dt="status"  width="90">Status</th>
                    <th class="dt-nosort dt-nosearch" data-dt="aksi"   width="110">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- ── MODAL TAMBAH / EDIT ─────────────────────────────── -->
<div id="modal-stub" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:540px;width:100%">

        <div class="modal-header">
            <h3 id="modal-title">Tambah Data</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>

        <!-- Scrollable body — tombol aksi di LUAR scroll area agar selalu terlihat -->
        <div style="max-height:calc(100vh - 180px);overflow-y:auto;padding:20px">
        <form id="form-stub">
            <?= csrf_field() ?>
            <input type="hidden" id="stub-id" name="_id">

            <!-- ── Row 1: 2 kolom ───────────────────────── -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">
                        Nama <span style="color:red">*</span>
                    </label>
                    <input type="text" name="nama" id="f-nama" class="form-control"
                           required placeholder="Nama...">
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">Kolom Lain</label>
                    <input type="text" name="kolom_lain" id="f-kolom-lain" class="form-control"
                           placeholder="...">
                </div>
            </div>

            <!-- ── Row 2: full width ─────────────────────── -->
            <div class="form-group mb-0" style="margin-bottom:12px">
                <label style="font-size:12px;font-weight:600">Keterangan</label>
                <textarea name="keterangan" id="f-keterangan" class="form-control"
                          rows="3" placeholder="Keterangan opsional..."></textarea>
            </div>

            <!-- ── Status (hanya tampil saat edit) ─────── -->
            <div class="form-group mb-0" id="wrap-status" style="display:none">
                <label style="font-size:12px;font-weight:600">Status</label>
                <select name="aktif" id="f-aktif" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </form>
        </div>

        <!-- Footer — selalu terlihat di bawah modal -->
        <div style="padding:12px 20px;border-top:1px solid #e2e8f0;
                    display:flex;justify-content:flex-end;gap:8px">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">
                Batal
            </button>
            <button type="button" class="btn btn-primary" onclick="$('#form-stub').submit()">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<!-- ── SCRIPTS ─────────────────────────────────────────── -->
<?= $this->section('scripts') ?>
<script>
$(function() {
    // CSRF dibaca dari meta tag (sudah di-inject layout/main.php)
    const csrfN = $('meta[name="csrf-token-name"]').attr('content');
    const csrfH = $('meta[name="csrf-token"]').attr('content');

    // ── Edit ──────────────────────────────────────────────
    $(document).on('click', '.btn-edit', function() {
        $.get('/admin/stub/' + $(this).data('id'), res => {
            if (res.error) return alert(res.error);

            $('#stub-id').val(res.id);
            $('#f-nama').val(res.nama);
            $('#f-kolom-lain').val(res.kolom_lain || '');
            $('#f-keterangan').val(res.keterangan || '');
            $('#f-aktif').val(res.aktif);
            $('#wrap-status').show();
            $('#modal-title').text('Edit — ' + res.nama);
            $('#modal-stub').show();
        });
    });

    // ── Hapus ─────────────────────────────────────────────
    $(document).on('click', '.btn-delete', function() {
        if (!confirm('Hapus data ini?')) return;
        $.post('/admin/stub/delete/' + $(this).data('id'), { [csrfN]: csrfH }, res => {
            if (res.success) dtReload('dt-stub');
            else alert(res.message || 'Gagal menghapus.');
        });
    });

    // ── Simpan (tambah & edit) ────────────────────────────
    $('#form-stub').on('submit', function(e) {
        e.preventDefault();
        const id  = $('#stub-id').val();
        const url = id ? '/admin/stub/update/' + id : '/admin/stub/store';
        $.post(url, $(this).serialize() + '&' + csrfN + '=' + csrfH, res => {
            if (res.success) {
                closeModal();
                dtReload('dt-stub');
            } else {
                alert(res.message || 'Gagal menyimpan.');
            }
        });
    });
});

// ── Modal helpers ──────────────────────────────────────────
function openModal() {
    $('#form-stub')[0].reset();
    $('#stub-id').val('');
    $('#wrap-status').hide();
    $('#modal-title').text('Tambah Data');
    $('#modal-stub').show();
}
function closeModal() { $('#modal-stub').hide(); }
</script>
<?= $this->endSection() ?>
