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

<div class="card">
    <div class="card-body">
        <table id="dt-sdm" class="w-100">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Jabatan Struktural</th>
                    <th>Pangkat/Gol</th>
                    <th>Irban</th>
                    <th width="80">Status</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit SDM -->
<div id="modal-sdm" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:560px">
        <div class="modal-header">
            <h3 id="modal-title">Tambah SDM</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form id="form-sdm">
            <?= csrf_field() ?>
            <input type="hidden" id="sdm-id" name="_id">
            <div class="form-row-2">
                <div class="form-group">
                    <label>NIP</label>
                    <input type="text" name="nip" id="f-nip" class="form-control" placeholder="NIP pegawai">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap (dengan gelar) <span style="color:red">*</span></label>
                    <input type="text" name="nama" id="f-nama" class="form-control" required placeholder="Nama S.Sos.,M.Si">
                </div>
            </div>
            <div class="form-row-2">
                <div class="form-group">
                    <label>Pangkat/Golongan</label>
                    <input type="text" name="pangkat_golongan" id="f-pangkat" class="form-control" placeholder="Pembina Tingkat I / IV.b">
                </div>
                <div class="form-group">
                    <label>Irban</label>
                    <select name="irban_id" id="f-irban" class="form-control">
                        <option value="">— Tidak ada —</option>
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
            <div class="form-group" id="wrap-aktif" style="display:none">
                <label>Status</label>
                <select name="aktif" id="f-aktif" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfToken  = '<?= csrf_hash() ?>';
const csrfName   = '<?= csrf_token() ?>';

$(function() {
    $('#dt-sdm').DataTable({
        processing: true, serverSide: true,
        ajax: { url: '/admin/master/sdm/data', type: 'POST',
                data: d => { d[csrfName] = csrfToken; } },
        columns: [
            { data: null, render: (d,t,r,m) => m.row + m.settings._iDisplayStart + 1, orderable: false },
            { data: 'nip' }, { data: 'nama' },
            { data: 'jabatan_struktural' }, { data: 'pangkat_golongan' },
            { data: 'irban' }, { data: 'aktif', orderable: false },
            { data: 'aksi', orderable: false },
        ]
    });

    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.get('/admin/master/sdm/' + id, res => {
            $('#sdm-id').val(res.id);
            $('#f-nip').val(res.nip);
            $('#f-nama').val(res.nama);
            $('#f-pangkat').val(res.pangkat_golongan);
            $('#f-jabatan-st').val(res.jabatan_struktural);
            $('#f-jabatan-fn').val(res.jabatan_fungsional);
            $('#f-irban').val(res.irban_id);
            $('#f-aktif').val(res.aktif);
            $('#wrap-aktif').show();
            $('#modal-title').text('Edit SDM');
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
        $.post(url, $(this).serialize(), res => {
            if (res.success) { closeModal(); $('#dt-sdm').DataTable().ajax.reload(); }
            else alert(res.message);
        });
    });
});

function openModal() {
    $('#form-sdm')[0].reset();
    $('#sdm-id').val('');
    $('#wrap-aktif').hide();
    $('#modal-title').text('Tambah SDM');
    $('#modal-sdm').show();
}
function closeModal() { $('#modal-sdm').hide(); }
</script>
<?= $this->endSection() ?>
