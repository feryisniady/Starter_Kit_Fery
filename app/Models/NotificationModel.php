<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table      = 'notifications';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id', 'type', 'title', 'message', 'url', 'is_read',
    ];

    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $createdField  = 'created_at';

    // Ambil notif milik user, terbaru duluan
    public function getForUser(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit);
    }

    // Jumlah notif belum dibaca
    public function countUnread(int $userId): int
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    // Tandai satu notif sebagai dibaca
    public function markRead(int $id, int $userId): void
    {
        $this->where('id', $id)->where('user_id', $userId)->set(['is_read' => 1])->update();
    }

    // Tandai semua notif user sebagai dibaca
    public function markAllRead(int $userId): void
    {
        $this->where('user_id', $userId)->where('is_read', 0)->set(['is_read' => 1])->update();
    }

    // Kirim notif ke satu atau banyak user
    public function send(int|array $userIds, string $title, string $message = '', string $url = '', string $type = 'info'): void
    {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        $rows    = [];
        $now     = date('Y-m-d H:i:s');

        foreach ($userIds as $uid) {
            $rows[] = [
                'user_id'    => $uid,
                'type'       => $type,
                'title'      => $title,
                'message'    => $message,
                'url'        => $url ?: null,
                'is_read'    => 0,
                'created_at' => $now,
            ];
        }

        $this->insertBatch($rows);
    }
}
