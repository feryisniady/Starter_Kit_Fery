<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    private NotificationModel $model;
    private int $userId;

    public function __construct()
    {
        $this->model  = new NotificationModel();
        $this->userId = (int) session()->get('user_id');
    }

    // TEST: kirim notif ke diri sendiri (hapus setelah selesai test)
    public function test()
    {
        notify($this->userId, 'Test Info',    'Ini notifikasi tipe info',    '/dashboard', 'info');
        notify($this->userId, 'Test Sukses',  'Upload dokumen berhasil',     '/dashboard', 'success');
        notify($this->userId, 'Test Warning', 'Laporan belum diverifikasi',  '/dashboard', 'warning');
        notify($this->userId, 'Test Bahaya',  'Login gagal 3x dari IP asing','/dashboard', 'danger');

        return redirect()->to('/notifications')->with('success', '4 notifikasi test terkirim!');
    }

    // Halaman semua notifikasi
    public function index()
    {
        $this->model->markAllRead($this->userId);

        return view('notifications/index', [
            'title'         => 'Notifikasi',
            'notifications' => $this->model->getForUser($this->userId, 50),
        ]);
    }

    // AJAX: ambil notif + jumlah unread (untuk bell dropdown)
    public function fetch()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $notifs = $this->model->getForUser($this->userId, 8);
        $unread = $this->model->countUnread($this->userId);

        // Format waktu relatif
        foreach ($notifs as &$n) {
            $n['time_ago'] = $this->timeAgo($n['created_at']);
        }

        return $this->response->setJSON([
            'unread'        => $unread,
            'notifications' => $notifs,
        ]);
    }

    // AJAX: tandai satu notif dibaca, redirect ke url-nya
    public function read(int $id)
    {
        $notif = $this->model->find($id);

        if ($notif && (int)$notif['user_id'] === $this->userId) {
            $this->model->markRead($id, $this->userId);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'ok']);
            }

            return redirect()->to($notif['url'] ?: '/notifications');
        }

        return redirect()->to('/notifications');
    }

    // AJAX / POST: tandai semua dibaca
    public function readAll()
    {
        $this->model->markAllRead($this->userId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'ok']);
        }

        return redirect()->back();
    }

    // Helper: waktu relatif (2 menit lalu, dsb.)
    private function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);

        if ($diff < 60)          return 'Baru saja';
        if ($diff < 3600)        return floor($diff / 60) . ' mnt lalu';
        if ($diff < 86400)       return floor($diff / 3600) . ' jam lalu';
        if ($diff < 2592000)     return floor($diff / 86400) . ' hari lalu';
        return date('d M Y', strtotime($datetime));
    }
}
