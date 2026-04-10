<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Notifikasi</h1>
        <p>Semua pemberitahuan masuk untuk akun Anda</p>
    </div>
    <?php if(!empty($notifications)): ?>
    <div class="page-actions">
        <form method="POST" action="/notifications/read-all">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-check-double"></i> Tandai Semua Dibaca
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if(empty($notifications)): ?>
            <div class="notif-empty" style="padding:60px 20px">
                <i class="fas fa-bell-slash" style="font-size:48px"></i>
                <div style="font-size:15px;font-weight:600;color:#64748b">Tidak ada notifikasi</div>
                <div>Notifikasi akan muncul di sini</div>
            </div>
        <?php else: ?>
            <?php
            $iconMap = [
                'info'    => 'fa-circle-info',
                'success' => 'fa-circle-check',
                'warning' => 'fa-triangle-exclamation',
                'danger'  => 'fa-circle-xmark',
            ];
            foreach($notifications as $n):
                $icon = $iconMap[$n['type']] ?? 'fa-circle-info';
                $type = in_array($n['type'], ['info','success','warning','danger']) ? $n['type'] : 'info';
            ?>
            <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>" style="padding:14px 20px;cursor:default">
                <div class="notif-icon <?= $type ?>">
                    <i class="fas <?= $icon ?>"></i>
                </div>
                <div class="notif-body">
                    <div class="notif-title" style="white-space:normal"><?= esc($n['title']) ?></div>
                    <?php if($n['message']): ?>
                        <div class="notif-msg" style="white-space:normal"><?= esc($n['message']) ?></div>
                    <?php endif; ?>
                    <div class="notif-time">
                        <i class="fas fa-clock" style="font-size:10px"></i>
                        <?= date('d M Y, H:i', strtotime($n['created_at'])) ?>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                    <?php if($n['url']): ?>
                    <a href="<?= esc($n['url']) ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                    <?php if(!$n['is_read']): ?>
                    <span class="notif-unread-dot"></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
