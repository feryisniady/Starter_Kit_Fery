<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Kode Temuan</h1>
        <p>Referensi kode temuan berdasarkan PermenpanRB No. 41 Tahun 2011</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap">
            <select id="sel-jenis" class="form-control" style="width:auto;min-width:260px">
                <option value="">-- Semua Jenis --</option>
                <?php foreach($jenisLabel as $k => $v): ?>
                <option value="<?= $k ?>"><?= esc($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <table id="dt-kode-temuan" class="w-100" data-url="/admin/master/kode-temuan/data">
            <thead>
                <tr>
                    <th width="120">Kode</th>
                    <th>Uraian Temuan</th>
                    <th width="220">Jenis</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfToken = '<?= csrf_hash() ?>';
const csrfName  = '<?= csrf_token() ?>';

let dtKt;
$(function() {
    dtKt = $('#dt-kode-temuan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/admin/master/kode-temuan/data',
            type: 'POST',
            data: function(d) {
                d[csrfName] = csrfToken;
                d.jenis = $('#sel-jenis').val();
            }
        },
        columns: [
            { data: 'kode' },
            { data: 'uraian' },
            { data: 'jenis', orderable: false },
        ],
        language: DT_LANG_ID,
        pageLength: 25,
    });
    $('#sel-jenis').on('change', function() { dtKt.ajax.reload(); });
});
</script>
<?= $this->endSection() ?>
