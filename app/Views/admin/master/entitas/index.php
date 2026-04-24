<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Master Entitas / OPD</h1>
        <p>Data Objek Pengawasan</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Tambah Entitas
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="dt-entitas" class="w-100">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Kode</th>
                    <th>Nama OPD / Entitas</th>
                    <th>Kepala</th>
                    <th width="80">Status</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Entitas -->
<div id="modal-entitas" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:540px;padding:0;overflow:hidden">
        <!-- Modal Header Gradient -->
        <div id="modal-header-bg" style="background:linear-gradient(135deg,#4f46e5 0%,#6366f1 100%);padding:18px 22px 14px;position:relative">
            <button onclick="closeModal()"
                    style="position:absolute;top:12px;right:14px;background:rgba(255,255,255,.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-times"></i>
            </button>
            <div style="display:flex;align-items:center;gap:12px">
                <div id="modal-icon-wrap" style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-building" style="color:#fff;font-size:18px"></i>
                </div>
                <div>
                    <h3 id="modal-title" style="color:#fff;margin:0;font-size:15px;font-weight:700">Tambah Entitas</h3>
                    <p style="color:rgba(255,255,255,.75);margin:2px 0 0;font-size:11px">Entitas / OPD Objek Pengawasan</p>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <form id="form-entitas" style="padding:20px 22px">
            <?= csrf_field() ?>
            <input type="hidden" id="entitas-id" name="_id">

            <div class="form-row-2">
                <div class="form-group">
                    <label style="font-size:12px;font-weight:600;color:#374151">Kode OPD</label>
                    <input type="text" name="kode" id="f-kode" class="form-control"
                           placeholder="Contoh: D01" style="border-radius:8px">
                </div>
                <div class="form-group">
                    <label style="font-size:12px;font-weight:600;color:#374151">
                        Nama Entitas / OPD <span style="color:#ef4444">*</span>
                    </label>
                    <input type="text" name="nama" id="f-nama" class="form-control" required
                           placeholder="Nama OPD lengkap..." style="border-radius:8px">
                </div>
            </div>

            <div class="form-group">
                <label style="font-size:12px;font-weight:600;color:#374151">
                    <i class="fas fa-user-tie" style="color:#6366f1"></i> Kepala / Pimpinan
                </label>
                <input type="text" name="kepala" id="f-kepala" class="form-control"
                       placeholder="Nama kepala/pimpinan OPD" style="border-radius:8px">
            </div>

            <div class="form-group">
                <label style="font-size:12px;font-weight:600;color:#374151">
                    <i class="fas fa-location-dot" style="color:#6366f1"></i> Alamat
                </label>
                <textarea name="alamat" id="f-alamat" class="form-control" rows="2"
                          placeholder="Alamat lengkap OPD..." style="border-radius:8px;resize:none"></textarea>
            </div>

            <div class="form-group">
                <label style="font-size:12px;font-weight:600;color:#374151">
                    <i class="fas fa-user-shield" style="color:#6366f1"></i>
                    Akun Portal Auditi
                    <span style="font-weight:400;color:#94a3b8">(opsional)</span>
                </label>
                <select name="user_id" id="f-user-id" class="form-control" style="border-radius:8px">
                    <option value="">— Tidak dihubungkan —</option>
                </select>
                <div style="font-size:11px;color:#94a3b8;margin-top:4px">
                    User ini yang akan login ke Portal Auditi dan menerima NHP / mengisi TL.
                </div>
            </div>

            <div class="form-group" id="wrap-aktif" style="display:none">
                <label style="font-size:12px;font-weight:600;color:#374151">Status</label>
                <div style="display:flex;gap:12px;margin-top:4px">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:#374151">
                        <input type="radio" name="aktif" id="f-aktif-1" value="1"> Aktif
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:#374151">
                        <input type="radio" name="aktif" id="f-aktif-0" value="0"> Nonaktif
                    </label>
                </div>
            </div>

            <div id="modal-err" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 12px;font-size:13px;color:#991b1b;margin-bottom:12px">
                <i class="fas fa-circle-exclamation"></i> <span id="modal-err-msg"></span>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:4px">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" id="btn-simpan" class="btn btn-primary" style="background:#4f46e5;border-color:#4f46e5">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfToken = '<?= csrf_hash() ?>';
const csrfName  = '<?= csrf_token() ?>';

$(function() {
    $('#dt-entitas').DataTable({
        processing: true, serverSide: true,
        ajax: { url: '/admin/master/entitas/data', type: 'POST',
                data: d => { d[csrfName] = csrfToken; } },
        columns: [
            { data: null, render: (d,t,r,m) => m.row + m.settings._iDisplayStart + 1, orderable: false },
            { data: 'kode' }, { data: 'nama' }, { data: 'kepala' },
            { data: 'aktif', orderable: false },
            { data: 'aksi', orderable: false },
        ]
    });

    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.get('/admin/master/entitas/' + id, res => {
            $('#entitas-id').val(res.id);
            $('#f-kode').val(res.kode);
            $('#f-nama').val(res.nama);
            $('#f-kepala').val(res.kepala);
            $('#f-alamat').val(res.alamat);
            $('input[name="aktif"][value="' + res.aktif + '"]').prop('checked', true);
            $('#wrap-aktif').show();
            $('#modal-title').text('Edit Entitas');
            $('#modal-header-bg').css('background', 'linear-gradient(135deg,#d97706 0%,#f59e0b 100%)');
            $('#modal-err').hide();
            // Load users untuk dropdown, sertakan user yang saat ini sudah terhubung
            loadUsers(id, res.user_id);
            $('#modal-entitas').show();
        });
    });

    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Entitas?',
            text: 'Data entitas ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.post('/admin/master/entitas/delete/' + id, { [csrfName]: csrfToken }, res => {
                if (res.success) {
                    $('#dt-entitas').DataTable().ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Dihapus!', text: 'Entitas berhasil dihapus.', timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            });
        });
    });

    $('#form-entitas').on('submit', function(e) {
        e.preventDefault();
        $('#modal-err').hide();
        const id  = $('#entitas-id').val();
        const url = id ? '/admin/master/entitas/update/' + id : '/admin/master/entitas/store';
        const btn = $('#btn-simpan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        $.post(url, $(this).serialize(), res => {
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            if (res.success) {
                closeModal();
                $('#dt-entitas').DataTable().ajax.reload();
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data entitas berhasil disimpan.', timer: 1500, showConfirmButton: false });
            } else {
                $('#modal-err-msg').text(res.message);
                $('#modal-err').show();
            }
        });
    });
});

function openModal() {
    $('#form-entitas')[0].reset();
    $('#entitas-id').val('');
    $('#wrap-aktif').hide();
    $('#modal-title').text('Tambah Entitas');
    $('#modal-header-bg').css('background', 'linear-gradient(135deg,#4f46e5 0%,#6366f1 100%)');
    $('#modal-err').hide();
    loadUsers(null, null);
    $('#modal-entitas').show();
}
function closeModal() { $('#modal-entitas').hide(); }

function loadUsers(entitasId, selectedUserId) {
    const params = entitasId ? '?entitas_id=' + entitasId : '';
    $.get('/admin/master/entitas/users' + params, users => {
        const sel = $('#f-user-id');
        sel.html('<option value="">— Tidak dihubungkan —</option>');
        users.forEach(u => {
            const opt = $('<option>').val(u.id).text(u.name + ' (' + u.email + ')');
            if (String(u.id) === String(selectedUserId)) opt.prop('selected', true);
            sel.append(opt);
        });
        // Jika user yang terpilih tidak ada di list (sudah linked entitas lain), tambahkan manual
        if (selectedUserId && sel.val() != selectedUserId) {
            sel.prepend($('<option>').val(selectedUserId).text('[User saat ini]').prop('selected', true));
        }
    });
}
</script>
<?= $this->endSection() ?>
