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

<!-- Modal -->
<div id="modal-entitas" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:520px">
        <div class="modal-header">
            <h3 id="modal-title">Tambah Entitas</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form id="form-entitas">
            <?= csrf_field() ?>
            <input type="hidden" id="entitas-id" name="_id">
            <div class="form-row-2">
                <div class="form-group">
                    <label>Kode</label>
                    <input type="text" name="kode" id="f-kode" class="form-control" placeholder="Opsional">
                </div>
                <div class="form-group">
                    <label>Nama Entitas / OPD <span style="color:red">*</span></label>
                    <input type="text" name="nama" id="f-nama" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>Kepala / Pimpinan</label>
                <input type="text" name="kepala" id="f-kepala" class="form-control">
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" id="f-alamat" class="form-control" rows="2"></textarea>
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
            $('#f-aktif').val(res.aktif);
            $('#wrap-aktif').show();
            $('#modal-title').text('Edit Entitas');
            $('#modal-entitas').show();
        });
    });

    $(document).on('click', '.btn-delete', function() {
        if (!confirm('Hapus entitas ini?')) return;
        $.post('/admin/master/entitas/delete/' + $(this).data('id'),
            { [csrfName]: csrfToken }, res => {
                if (res.success) $('#dt-entitas').DataTable().ajax.reload();
                else alert(res.message);
            });
    });

    $('#form-entitas').on('submit', function(e) {
        e.preventDefault();
        const id  = $('#entitas-id').val();
        const url = id ? '/admin/master/entitas/update/' + id : '/admin/master/entitas/store';
        $.post(url, $(this).serialize(), res => {
            if (res.success) { closeModal(); $('#dt-entitas').DataTable().ajax.reload(); }
            else alert(res.message);
        });
    });
});

function openModal() {
    $('#form-entitas')[0].reset();
    $('#entitas-id').val('');
    $('#wrap-aktif').hide();
    $('#modal-title').text('Tambah Entitas');
    $('#modal-entitas').show();
}
function closeModal() { $('#modal-entitas').hide(); }
</script>
<?= $this->endSection() ?>
