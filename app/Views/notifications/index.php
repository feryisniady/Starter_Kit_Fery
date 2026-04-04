<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Notifikasi</h1>
        <p>Semua pemberitahuan masuk untuk akun Anda</p>
    </div>
    <?php if(!empty($notifications)): ?>
    <div class="page-actions">
        <form method="POST" action="/notifications/read-all" style="display:inline">
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
            <div style="text-align:center;padding:60px 20px;color:#94a3b8">
                <i class="fas fa-bell-slash" style="font-size:48px;display:block;margin-bottom:16px;opacity:.3"></i>
                <div style="font-size:15px;font-weight:600;margin-bottom:6px;color:#64748b">Tidak ada notifikasi</div>
                <div style="font-size:13px">Notifikasi akan muncul di sini</div>
            </div>
        <?php else: ?>
            <?php
            $iconMap = [
                'info'    => ['icon' => 'fa-circle-info',        'class' => 'info'],
                'success' => ['icon' => 'fa-circle-check',       'class' => 'success'],
                'warning' => ['icon' => 'fa-triangle-exclamation','class' => 'warning'],
                'danger'  => ['icon' => 'fa-circle-xmark',       'class' => 'danger'],
            ];
            foreach($notifications as $n):
                $im = $iconMap[$n['type']] ?? $iconMap['info'];
            ?>
            <div class="notif-page-item <?= $n['is_read'] ? '' : 'unread' ?>">
                <div class="notif-icon <?= $im['class'] ?>">
                    <i class="fas <?= $im['icon'] ?>"></i>
                </div>
                <div class="notif-body">
                    <div class="notif-title"><?= esc($n['title']) ?></div>
                    <?php if($n['message']): ?>
                        <div class="notif-msg"><?= esc($n['message']) ?></div>
                    <?php endif; ?>
                    <div class="notif-time">
                        <i class="fas fa-clock" style="font-size:10px"></i>
                        <?= date('d M Y, H:i', strtotime($n['created_at'])) ?>
                    </div>
                </div>
                <?php if($n['url']): ?>
                <a href="<?= esc($n['url']) ?>" class="btn btn-sm btn-secondary" style="flex-shrink:0">
                    <i class="fas fa-arrow-right"></i> Lihat
                </a>
                <?php endif; ?>
                <?php if(!$n['is_read']): ?>
                    <span class="notif-unread-dot" style="align-self:center"></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.notif-page-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}
.notif-page-item:last-child { border-bottom: none; }
.notif-page-item.unread { background: #f0f9ff; }
.notif-page-item:hover { background: #f8fafc; }
.notif-page-item.unread:hover { background: #e0f2fe; }
.notif-page-item .notif-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.notif-page-item .notif-body { flex: 1; min-width: 0; }
.notif-page-item .notif-title {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 3px;
}
.notif-page-item .notif-msg {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 4px;
}
.notif-page-item .notif-time {
    font-size: 11px;
    color: #94a3b8;
}
</style>

<?= $this->endSection() ?>
