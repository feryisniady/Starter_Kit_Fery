<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Pengaturan Aplikasi</h1>
        <p>Konfigurasi identitas, tampilan, dan konten aplikasi</p>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success mb-4" style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1px solid #86efac;border-left:4px solid #16a34a;border-radius:10px;padding:14px 16px;">
    <i class="fas fa-circle-check" style="color:#16a34a;font-size:18px"></i>
    <span style="color:#15803d;font-size:14px"><?= esc(session()->getFlashdata('success')) ?></span>
</div>
<?php endif; ?>

<?php
// Flatten grouped untuk akses cepat
$flat = [];
foreach ($grouped as $group => $items) {
    foreach ($items as $item) {
        $flat[$item['key']] = $item;
    }
}

$tabs = [
    'general'    => ['label' => 'Umum',          'icon' => 'fa-sliders'],
    'appearance' => ['label' => 'Tampilan',       'icon' => 'fa-palette'],
    'login'      => ['label' => 'Halaman Login',  'icon' => 'fa-right-to-bracket'],
    'footer'     => ['label' => 'Footer',         'icon' => 'fa-rectangle-list'],
];
$activeTab = $_GET['tab'] ?? 'general';
?>

<!-- Tab Nav -->
<div class="card mb-0" style="border-bottom:none;border-radius:12px 12px 0 0">
    <div style="display:flex;gap:0;border-bottom:2px solid #e2e8f0;padding:0 24px">
        <?php foreach($tabs as $key => $tab): ?>
        <a href="?tab=<?= $key ?>"
           style="display:flex;align-items:center;gap:8px;padding:16px 20px;font-size:14px;font-weight:500;border-bottom:2px solid <?= $activeTab === $key ? '#6366f1' : 'transparent' ?>;margin-bottom:-2px;color:<?= $activeTab === $key ? '#6366f1' : '#64748b' ?>;text-decoration:none;transition:color .2s">
            <i class="fas <?= $tab['icon'] ?>" style="font-size:13px"></i>
            <?= $tab['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<form action="/admin/settings/update" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card" style="border-radius:0 0 12px 12px">
        <div class="card-body">

        <?php if ($activeTab === 'general'): ?>
        <!-- ================================================================ -->
        <!-- TAB: UMUM -->
        <!-- ================================================================ -->
        <div class="form-section">
            <h3 class="form-section-title"><i class="fas fa-building"></i> Identitas Aplikasi</h3>

            <?php foreach(($grouped['general'] ?? []) as $s): ?>
            <div class="form-group">
                <label for="s_<?= $s['key'] ?>"><?= esc($s['label']) ?> <?php if(in_array($s['key'], ['app_name','org_name'])): ?><span style="color:#ef4444">*</span><?php endif; ?></label>
                <?php if ($s['type'] === 'textarea'): ?>
                <textarea id="s_<?= $s['key'] ?>" name="settings[<?= $s['key'] ?>]" rows="3" class="form-control"><?= esc($s['value'] ?? '') ?></textarea>
                <?php else: ?>
                <input type="text" id="s_<?= $s['key'] ?>" name="settings[<?= $s['key'] ?>]"
                       value="<?= esc($s['value'] ?? '') ?>"
                       placeholder="<?= esc($s['description'] ?? '') ?>"
                       class="form-control">
                <?php endif; ?>
                <?php if($s['description']): ?>
                <small class="form-hint"><?= esc($s['description']) ?></small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php elseif ($activeTab === 'appearance'): ?>
        <!-- ================================================================ -->
        <!-- TAB: TAMPILAN -->
        <!-- ================================================================ -->
        <div class="form-section">
            <h3 class="form-section-title"><i class="fas fa-image"></i> Logo & Icon</h3>

            <?php foreach(($grouped['appearance'] ?? []) as $s): ?>
            <div class="form-group">
                <label><?= esc($s['label']) ?></label>

                <?php if($s['value']): ?>
                <div class="image-preview-wrap mb-2">
                    <img src="<?= base_url(esc($s['value'])) ?>"
                         alt="<?= esc($s['label']) ?>"
                         style="max-height:80px;max-width:200px;border-radius:8px;border:1px solid #e2e8f0;padding:4px;background:#f8fafc">
                    <span class="image-preview-name"><?= esc(basename($s['value'])) ?></span>
                </div>
                <?php else: ?>
                <div class="image-preview-wrap mb-2" style="color:#94a3b8;font-size:13px">
                    <i class="fas fa-image"></i> Belum ada gambar
                </div>
                <?php endif; ?>

                <input type="file" name="<?= $s['key'] ?>" accept="image/*"
                       class="form-control" style="max-width:400px">
                <?php if($s['description']): ?>
                <small class="form-hint"><?= esc($s['description']) ?></small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php elseif ($activeTab === 'login'): ?>
        <!-- ================================================================ -->
        <!-- TAB: HALAMAN LOGIN -->
        <!-- ================================================================ -->
        <div class="form-section">
            <h3 class="form-section-title"><i class="fas fa-key"></i> Konten Halaman Login</h3>
            <p style="color:#64748b;font-size:13px;margin-bottom:20px">
                Kosongkan field untuk menggunakan nilai dari tab <strong>Umum</strong> secara otomatis.
            </p>

            <?php foreach(($grouped['login'] ?? []) as $s): ?>
            <div class="form-group">
                <label for="s_<?= $s['key'] ?>"><?= esc($s['label']) ?></label>

                <?php if($s['type'] === 'image'): ?>
                    <?php if($s['value']): ?>
                    <div class="image-preview-wrap mb-2">
                        <img src="<?= base_url(esc($s['value'])) ?>"
                             alt="Background"
                             style="max-height:120px;max-width:300px;border-radius:8px;border:1px solid #e2e8f0">
                        <span class="image-preview-name"><?= esc(basename($s['value'])) ?></span>
                    </div>
                    <?php else: ?>
                    <div class="image-preview-wrap mb-2" style="color:#94a3b8;font-size:13px">
                        <i class="fas fa-image"></i> Belum ada gambar (menggunakan gradient CSS)
                    </div>
                    <?php endif; ?>
                    <input type="file" name="<?= $s['key'] ?>" accept="image/*"
                           class="form-control" style="max-width:400px">

                <?php elseif($s['type'] === 'textarea'): ?>
                    <textarea id="s_<?= $s['key'] ?>" name="settings[<?= $s['key'] ?>]"
                              rows="3" class="form-control"><?= esc($s['value'] ?? '') ?></textarea>
                <?php else: ?>
                    <input type="text" id="s_<?= $s['key'] ?>" name="settings[<?= $s['key'] ?>]"
                           value="<?= esc($s['value'] ?? '') ?>"
                           placeholder="<?= esc($s['description'] ?? '') ?>"
                           class="form-control">
                <?php endif; ?>

                <?php if($s['description']): ?>
                <small class="form-hint"><?= esc($s['description']) ?></small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php elseif ($activeTab === 'footer'): ?>
        <!-- ================================================================ -->
        <!-- TAB: FOOTER -->
        <!-- ================================================================ -->
        <div class="form-section">
            <h3 class="form-section-title"><i class="fas fa-rectangle-list"></i> Pengaturan Footer</h3>

            <?php foreach(($grouped['footer'] ?? []) as $s): ?>
            <div class="form-group">
                <label for="s_<?= $s['key'] ?>"><?= esc($s['label']) ?></label>
                <?php if($s['type'] === 'textarea'): ?>
                <textarea id="s_<?= $s['key'] ?>" name="settings[<?= $s['key'] ?>]"
                          rows="4" class="form-control" placeholder="Biarkan kosong untuk menggunakan teks otomatis: © [tahun] [nama org] — [nama app]"><?= esc($s['value'] ?? '') ?></textarea>
                <?php else: ?>
                <input type="text" id="s_<?= $s['key'] ?>" name="settings[<?= $s['key'] ?>]"
                       value="<?= esc($s['value'] ?? '') ?>"
                       class="form-control">
                <?php endif; ?>
                <?php if($s['description']): ?>
                <small class="form-hint"><?= esc($s['description']) ?></small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <!-- Preview footer -->
            <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:16px;margin-top:8px">
                <p style="font-size:12px;color:#94a3b8;margin:0 0 6px">Preview footer default:</p>
                <p style="font-size:13px;color:#475569;margin:0">
                    © <?= date('Y') ?>
                    <a href="<?= esc(app_setting('org_website','#')) ?>" style="color:#6366f1"><?= esc(app_setting('org_name','Nama Instansi')) ?></a>
                    — <?= esc(app_setting('app_name','RBAC Starter Kit')) ?> v<?= esc(app_setting('app_version','1.0')) ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

        </div><!-- card-body -->

        <!-- Footer form -->
        <div style="display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid #f1f5f9">
            <a href="?tab=<?= $activeTab ?>" class="btn btn-secondary">
                <i class="fas fa-rotate-left"></i> Reset
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-floppy-disk"></i> Simpan Perubahan
            </button>
        </div>
    </div><!-- card -->
</form>

<style>
.form-section { max-width: 640px; }
.form-section-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-section-title i { color: #6366f1; }
.form-group { margin-bottom: 20px; }
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}
.form-control {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #1e293b;
    background: #fff;
    transition: border .2s;
    box-sizing: border-box;
}
.form-control:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}
.form-hint {
    display: block;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 5px;
}
.image-preview-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.image-preview-name {
    font-size: 12px;
    color: #64748b;
    font-style: italic;
}
</style>

<?= $this->endSection() ?>
