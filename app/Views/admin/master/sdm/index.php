<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Master SDM Pengawas</h1>
        <p>Data Sumber Daya Manusia Inspektorat</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Tambah SDM
        </button>
    </div>
</div>

<?php
// Hitung SDM yang belum lengkap
$totalUnlinkedUser  = count(array_filter($users, fn($u) => false)); // dihitung via DataTable
?>

<div class="card">
    <div class="card-body">
        <table id="dt-sdm" class="w-100">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Irban</th>
                    <th width="130">Akun User</th>
                    <th width="70">Status</th>
                    <th width="90">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit SDM -->
<div id="modal-sdm" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:620px;width:100%">
        <div class="modal-header">
            <h3 id="modal-title">Tambah SDM</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div style="max-height:calc(100vh - 180px);overflow-y:auto;padding:20px">
        <form id="form-sdm">
            <?= csrf_field() ?>
            <input type="hidden" id="sdm-id" name="_id">
            <input type="hidden" id="sdm-current-user-id">

            <!-- NIP (kecil) + Nama (lebar) -->
            <div style="display:grid;grid-template-columns:160px 1fr;gap:12px;margin-bottom:12px">
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">NIP</label>
                    <input type="text" name="nip" id="f-nip" class="form-control" placeholder="19800101...">
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">Nama Lengkap <span style="color:red">*</span></label>
                    <input type="text" name="nama" id="f-nama" class="form-control" required placeholder="Nama, S.Sos., M.Si">
                </div>
            </div>

            <!-- Pangkat + Irban -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">Pangkat/Golongan</label>
                    <input type="text" name="pangkat_golongan" id="f-pangkat" class="form-control" placeholder="Pembina / IV.a">
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">
                        Irban
                        <span style="font-weight:normal;color:#94a3b8;font-size:10px">(akses data)</span>
                    </label>
                    <select name="irban_id" id="f-irban" class="form-control">
                        <option value="">— Tidak ada (Inspektur/Staf) —</option>
                        <?php foreach($irban as $k => $v): ?>
                            <option value="<?= $k ?>"><?= esc($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Jabatan Struktural + Fungsional -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">Jabatan Struktural</label>
                    <input type="text" name="jabatan_struktural" id="f-jabatan-st" class="form-control" placeholder="Kepala Irban / Inspektur ...">
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:12px;font-weight:600">Jabatan Fungsional</label>
                    <input type="text" name="jabatan_fungsional" id="f-jabatan-fn" class="form-control" placeholder="Auditor Muda / ...">
                </div>
            </div>

            <!-- Link ke Akun Login -->
            <div class="form-group mb-0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px;margin-bottom:12px">
                <label style="font-size:12px;font-weight:600;color:#4f46e5;margin-bottom:6px;display:block">
                    <i class="fas fa-link"></i> Link ke Akun Login
                    <span style="font-weight:normal;color:#94a3b8;font-size:10px"> — wajib agar pegawai bisa login & akses datanya</span>
                </label>
                <select name="user_id" id="f-user" class="form-control">
                    <option value="">— Tidak dihubungkan —</option>
                    <?php foreach($users as $u):
                        $link = $linkedMap[$u['id']] ?? null;
                        $displayName = esc($u['name'] ?: $u['email']) . ' (' . esc($u['email']) . ')';
                        $suffix = $link ? ' ← ' . esc($link['sdm_nama']) : '';
                    ?>
                    <option value="<?= $u['id'] ?>"
                            data-linked-sdm-id="<?= $link ? $link['sdm_id'] : '' ?>"
                            data-linked-sdm-nama="<?= $link ? esc($link['sdm_nama']) : '' ?>"
                            <?= $link ? 'style="color:#94a3b8"' : '' ?>>
                        <?= $displayName . $suffix ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div id="user-link-warning" style="display:none;margin-top:6px;padding:6px 10px;background:#fef3c7;border-radius:4px;font-size:12px;color:#92400e">
                    <i class="fas fa-exclamation-triangle"></i>
                    Sudah terhubung ke <strong id="user-link-other-sdm"></strong>.
                    Menyimpan akan memindahkan link ke SDM ini.
                </div>
            </div>

            <!-- Status (edit only) -->
            <div class="form-group mb-0" id="wrap-aktif" style="display:none">
                <label style="font-size:12px;font-weight:600">Status</label>
                <select name="aktif" id="f-aktif" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </form>
        </div>
        <!-- Footer actions di luar scroll area -->
        <div style="padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="$('#form-sdm').submit()">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfToken  = '<?= csrf_hash() ?>';
const csrfName   = '<?= csrf_token() ?>';

// Tidak dipakai langsung — info ada di data-attribute option

$(function() {
    $('#dt-sdm').DataTable({
        processing: true, serverSide: true,
        language: DT_LANG_ID,
        ajax: { url: '/admin/master/sdm/data', type: 'POST',
                data: d => { d[csrfName] = csrfToken; } },
        columns: [
            { data: null, render: (d,t,r,m) => m.row + m.settings._iDisplayStart + 1, orderable: false },
            { data: 'nip', width: '110px' },
            { data: 'nama' },
            { data: 'jabatan_struktural' },
            { data: 'irban', orderable: false },
            { data: 'user_linked', orderable: false },
            { data: 'aktif', orderable: false },
            { data: 'aksi', orderable: false },
        ]
    });

    // Warning saat pilih user yang sudah ter-link ke SDM LAIN
    $('#f-user').on('change', function() {
        const currentSdmId = parseInt($('#sdm-id').val()) || 0;
        const opt          = $(this).find('option:selected');
        const linkedSdmId  = parseInt(opt.data('linked-sdm-id')) || 0;
        const linkedSdmNama= opt.data('linked-sdm-nama') || '';

        if (linkedSdmId && linkedSdmId !== currentSdmId) {
            // Terhubung ke SDM yang berbeda — tampilkan peringatan
            $('#user-link-other-sdm').text(linkedSdmNama);
            $('#user-link-warning').show();
        } else {
            $('#user-link-warning').hide();
        }
    });

    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.get('/admin/master/sdm/' + id, res => {
            $('#sdm-id').val(res.id);
            $('#sdm-current-user-id').val(res.user_id || '');
            $('#f-nip').val(res.nip || '');
            $('#f-nama').val(res.nama);
            $('#f-pangkat').val(res.pangkat_golongan || '');
            $('#f-jabatan-st').val(res.jabatan_struktural || '');
            $('#f-jabatan-fn').val(res.jabatan_fungsional || '');
            $('#f-irban').val(res.irban_id || '');
            $('#f-aktif').val(res.aktif);
            $('#user-link-warning').hide();

            // Set user dropdown — semua user ada di dropdown, tinggal pilih
            $('#f-user').val(res.user_id || '').trigger('change');

            $('#wrap-aktif').show();
            $('#modal-title').text('Edit SDM — ' + res.nama);
            $('#modal-sdm').show();
        });
    });

    $(document).on('click', '.btn-delete', function() {
        if (!confirm('Hapus SDM ini?')) return;
        $.post('/admin/master/sdm/delete/' + $(this).data('id'),
            { [csrfName]: csrfToken }, res => {
                if (res.success) $('#dt-sdm').DataTable().ajax.reload();
                else alert(res.message);
            });
    });

    $('#form-sdm').on('submit', function(e) {
        e.preventDefault();
        const id  = $('#sdm-id').val();
        const url = id ? '/admin/master/sdm/update/' + id : '/admin/master/sdm/store';
        $.post(url, $(this).serialize() + '&' + csrfName + '=' + csrfToken, res => {
            if (res.success) {
                closeModal();
                $('#dt-sdm').DataTable().ajax.reload();
            } else {
                alert(res.message || 'Gagal menyimpan.');
            }
        });
    });
});

function openModal() {
    $('#form-sdm')[0].reset();
    $('#sdm-id').val('');
    $('#sdm-current-user-id').val('');
    $('#user-link-warning').hide();
    $('#wrap-aktif').hide();
    $('#modal-title').text('Tambah SDM');
    $('#modal-sdm').show();
}
function closeModal() { $('#modal-sdm').hide(); }
</script>
<?= $this->endSection() ?>
