<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AppSettingModel;
use App\Services\MailService;
use App\Services\WaService;

class AppSettingController extends BaseController
{
    protected AppSettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new AppSettingModel();
    }

    public function index()
    {
        return view('admin/app_settings/index', [
            'title'   => 'Pengaturan Aplikasi',
            'grouped' => $this->settingModel->getAllGrouped(),
        ]);
    }

    public function update()
    {
        $post     = $this->request->getPost();
        $settings = $post['settings'] ?? [];

        // Ambil semua tipe sekaligus dari DB (1 query)
        $dbRows  = \Config\Database::connect()->table('app_settings')
            ->select('key, type')->get()->getResultArray();
        $typeMap = array_column($dbRows, 'type', 'key');

        foreach ($settings as $key => $value) {
            $type = $typeMap[$key] ?? 'text';

            // Password/token: jangan overwrite kalau dikosongkan
            if ($type === 'password' && $value === '') {
                unset($settings[$key]);
                continue;
            }

            // Boolean: normalkan ke '1' / '0'
            if ($type === 'boolean') {
                $settings[$key] = $value ? '1' : '0';
            }
        }
        // Catatan: boolean yang TIDAK ada di form (tab berbeda) sengaja tidak disentuh
        // agar checkbox di tab lain tidak ikut ter-reset

        $this->settingModel->setMany($settings);
        $this->handleUploads();
        clear_setting_cache();

        logActivity('setting.update', 'settings', 'Pengaturan aplikasi diperbarui');

        return redirect()->to('/admin/settings?tab=' . ($post['_tab'] ?? 'general'))
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function deleteImage(string $key)
    {
        $setting = $this->settingModel->where('key', $key)->where('type', 'image')->first();
        if (!$setting) {
            return redirect()->back()->with('error', 'Setting tidak ditemukan.');
        }

        if ($setting['value']) {
            $filePath = FCPATH . ltrim($setting['value'], '/');
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $this->settingModel->setValue($key, null);
        clear_setting_cache();

        logActivity('setting.delete_image', 'settings', "Gambar '{$key}' dihapus");

        $tab = request()->getGet('tab') ?? 'appearance';
        return redirect()->to('/admin/settings?tab=' . $tab)->with('success', 'Gambar berhasil dihapus.');
    }

    // =========================================================
    // TEST EMAIL
    // =========================================================
    public function testEmail()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $to = trim($this->request->getPost('to') ?: session()->get('user_name'));
        if (empty($to)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Alamat email tujuan tidak boleh kosong.']);
        }

        $mail    = new MailService();
        $appName = app_setting('app_name') ?: 'Aplikasi';

        if (!$mail->isConfigured()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Konfigurasi email belum lengkap. Isi Host, Username, dan Password terlebih dahulu.',
            ]);
        }

        $sent = $mail->send(
            $to,
            'Test Email — ' . $appName,
            '<p>Ini adalah email uji coba dari <strong>' . esc($appName) . '</strong>.</p>'
            . '<p>Jika Anda menerima email ini, konfigurasi email sudah benar.</p>'
            . '<p style="color:#94a3b8;font-size:12px">Dikirim pada: ' . date('d M Y H:i:s') . '</p>'
        );

        logActivity('setting.test_email', 'settings', 'Test email ke ' . $to);

        if ($sent) {
            $message = 'Email berhasil dikirim ke ' . esc($to) . '. Periksa inbox Anda.';
        } else {
            $detail  = $mail->getLastError();
            $message = 'Gagal mengirim email.';
            if ($detail) {
                // Ambil baris yang mengandung error SMTP saja supaya tidak terlalu panjang
                $lines = array_filter(explode("\n", $detail), fn($l) => trim($l) !== '');
                $short = implode(' | ', array_slice($lines, -5));
                $message .= ' Detail: ' . htmlspecialchars(mb_substr($short, 0, 300));
            }
        }

        return $this->response->setJSON(['success' => $sent, 'message' => $message]);
    }

    // =========================================================
    // TEST WHATSAPP
    // =========================================================
    public function testWa()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $phone = preg_replace('/[^0-9]/', '', $this->request->getPost('phone') ?: '');
        if (empty($phone)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nomor WhatsApp tujuan tidak boleh kosong.']);
        }

        $wa = new WaService();

        if (!$wa->isConfigured()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'WhatsApp belum diaktifkan atau konfigurasi belum lengkap.',
            ]);
        }

        $appName = app_setting('app_name') ?: 'Aplikasi';
        $sent    = $wa->send($phone, "Halo! Ini pesan uji coba dari *{$appName}*.\n\nJika Anda menerima pesan ini, konfigurasi WhatsApp sudah benar.\n\n_Dikirim: " . date('d M Y H:i:s') . "_");

        logActivity('setting.test_wa', 'settings', 'Test WA ke ' . $phone);

        return $this->response->setJSON([
            'success' => $sent,
            'message' => $sent
                ? 'Pesan WhatsApp berhasil dikirim ke ' . $phone . '.'
                : 'Gagal mengirim WA. Periksa token dan nomor pengirim.',
        ]);
    }

    // =========================================================
    // PRIVATE: HANDLE FILE UPLOADS
    // =========================================================
    private function handleUploads(): void
    {
        $uploadPath = FCPATH . 'uploads/settings/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $imageKeys = ['logo_path', 'favicon_path', 'login_bg_path', 'landing_hero_path'];

        foreach ($imageKeys as $key) {
            $file = $this->request->getFile($key);
            if (!$file || !$file->isValid() || $file->hasMoved()) {
                continue;
            }

            $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'];
            $allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico'];
            if (!in_array($file->getMimeType(), $allowedMime)) continue;
            if (!in_array(strtolower($file->guessExtension()), $allowedExt)) continue;
            if ($file->getSize() > 2 * 1024 * 1024) continue;

            $existing = $this->settingModel->where('key', $key)->first();
            if ($existing && $existing['value']) {
                $oldFile = FCPATH . ltrim($existing['value'], '/');
                if (is_file($oldFile)) @unlink($oldFile);
            }

            $newName = $key . '_' . time() . '.' . $file->guessExtension();
            $file->move($uploadPath, $newName);
            $this->settingModel->setValue($key, 'uploads/settings/' . $newName);
        }
    }
}
