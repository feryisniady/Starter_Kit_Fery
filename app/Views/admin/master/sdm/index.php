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
    <div class="modal-box" style="max-width:580px">
        <div class="modal-header">
            <h3 id="modal-title">Tambah SDM</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form id="form-sdm">
            <?= csrf_field() ?>
            <input type="hidden" id="sdm-id" name="_id">
            <input type="hidden" id="sdm-current-user-id"> <!-- untuk restore dropdown saat edit -->
            <div class="form-row-2">
                <div class="form-group">
                    <label>NIP</label>
                    <input type="text" name="nip" id="f-nip" class="form-control" placeholder="NIP pegawai">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap (dengan gelar) <span style="color:red">*</span></label>
                    <input type="text" name="nama" id="f-nama" class="form-control" required placeholder="Nama, S.Sos.,M.Si">
                </div>
            </div>
            <div class="form-row-2">
                <div class="form-group">
                    <label>Pangkat/Golongan</label>
                    <input type="text" name="pangkat_golongan" id="f-pangkat" class="form-control" placeholder="Pembina Tingkat I / IV.b">
                </div>
                <div class="form-group">
                    <label>Irban <span style="font-weight:normal;color:#94a3b8;font-size:11px">(menentukan akses data)</span></label>
                    <select name="irban_id" id="f-irban" class="form-control">
                        <option value="">— Tidak ada (Inspektur/Staf Umum) —</option>
                        <?php foreach($irban as $k => $v): ?>
                            <option value="<?= $k ?>"><?= esc($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Jabatan Struktural</label>
                <input type="text" name="jabatan_struktural" id="f-jabatan-st" class="form-control" placeholder="Inspektur / Kepala Irban / ...">
            </div>
            <div class="form-group">
                <label>Jabatan Fungsional</label>
                <input type="text" name="jabatan_fungsional" id="f-jabatan-fn" class="form-control" placeholder="Auditor Muda / ...">
            </div>

            <!-- Link ke Akun User -->
            <div class="form-group">
                <label>
                    <i class="fas fa-link" style="color:#6366f1"></i>
                    Link ke Akun Login
                    <span style="font-weight:normal;color:#94a3b8;font-size:11px"> — wajib agar pegawai bisa login & akses datanya</span>
                </label>
                <select name="user_id" id="f-user" class="form-control">
                    <option value="">— Tidak dihubungkan —</option>
                    <?php foreach($users as $u):
                        $link = $linkedMap[$u['id']] ?? null;
                        $displayName = esc($u['name'] ?: $u['email']) . ' (' . esc($u['email']) . ')';
                        $suffix = $link ? ' ← terhubung ke: ' . esc($link['sdm_nama']) : '';
                    ?>
                    <option value="<?= $u['id'] ?>"
                            data-linked-sdm-id="<?= $link ? $link['sdm_id'] : '' ?>"
                            data-linked-sdm-nama="<?= $link ? esc($link['sdm_nama']) : '' ?>"
                            <?= $link ? 'style="color:#94a3b8"' : '' ?>>
                        <?= $displayName . $suffix ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div id="user-link-warning" style="display:none;margin-top:4px;padding:6px 10px;background:#fef3c7;border-radius:4px;font-size:12px;color:#92400e">
                    <i class="fas fa-exclamation-triangle"></i>
                    User ini sudah terhubung ke SDM lain (<strong id="user-link-other-sdm"></strong>).
                    Menyimpan akan memindahkan link ke SDM ini.
                </div>
            </div>

            <div class="form-group" id="wrap-aktif" style="display:none">
                <label>Status</label>
                <select name="aktif" id="f-aktif" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
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
