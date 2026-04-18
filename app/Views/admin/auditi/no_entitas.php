<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div style="text-align:center;padding:80px 20px">
    <i class="fas fa-building-circle-exclamation" style="font-size:56px;color:#e2e8f0;display:block;margin-bottom:20px"></i>
    <h2 style="color:#1e293b;margin-bottom:8px">Akun Belum Dikaitkan ke Entitas</h2>
    <p style="color:#64748b;max-width:480px;margin:0 auto 24px">
        Akun Anda belum dihubungkan dengan entitas (unit/dinas/badan) manapun.
        Silakan hubungi Administrator Inspektorat untuk mengaitkan akun Anda.
    </p>
    <a href="/dashboard" class="btn btn-primary">
        <i class="fas fa-home"></i> Kembali ke Beranda
    </a>
</div>

<?= $this->endSection() ?>
