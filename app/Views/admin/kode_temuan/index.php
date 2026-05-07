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
            <select id="sel-jenis" class="form-control" style="width:auto;min-width:260px" onchange="dtApplyFilter()">
                <option value="">-- Semua Jenis --</option>
                <?php foreach($jenisLabel as $k => $v): ?>
                <option value="<?= $k ?>"><?= esc($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <table id="dt-kode-temuan" data-url="/admin/master/kode-temuan/data" class="w-100">
            <thead>
                <tr>
                    <th data-dt="kode" width="120">Kode</th>
                    <th data-dt="uraian">Uraian Temuan</th>
                    <th class="dt-nosort" data-dt="jenis" width="220">Jenis</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function dtApplyFilter() {
    DT_EXTRA_PARAMS['dt-kode-temuan'] = {
        jenis: document.getElementById('sel-jenis').value,
    };
    dtReload('dt-kode-temuan');
}
</script>
<?= $this->endSection() ?>
