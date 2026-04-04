<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AppSettingModel;

class AppSettingController extends BaseController
{
    protected AppSettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new AppSettingModel();
    }

    public function index()
    {
        $data = [
            'title'   => 'Pengaturan Aplikasi',
            'grouped' => $this->settingModel->getAllGrouped(),
        ];
        return view('admin/app_settings/index', $data);
    }

    public function update()
    {
        $post     = $this->request->getPost();
        $settings = $post['settings'] ?? [];

        // Simpan semua field teks/textarea/boolean
        $this->settingModel->setMany($settings);

        // Handle upload file (type = image)
        $this->handleUploads();

        // Hapus cache agar perubahan langsung terlihat
        clear_setting_cache();

        logActivity('setting.update', 'settings', 'Pengaturan aplikasi diperbarui');

        return redirect()->to('/admin/settings')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function deleteImage(string $key)
    {
        // Validasi key hanya boleh image type
        $setting = $this->settingModel->where('key', $key)->where('type', 'image')->first();
        if (!$setting) {
            return redirect()->back()->with('error', 'Setting tidak ditemukan.');
        }

        // Hapus file fisik
        if ($setting['value']) {
            $filePath = FCPATH . ltrim($setting['value'], '/');
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        // Kosongkan nilai di DB
        $this->settingModel->setValue($key, null);
        clear_setting_cache();

        logActivity('setting.delete_image', 'settings', "Gambar '{$key}' dihapus");

        $tab = request()->getGet('tab') ?? 'appearance';
        return redirect()->to('/admin/settings?tab=' . $tab)->with('success', 'Gambar berhasil dihapus.');
    }

    // -------------------------------------------------------------------------

    private function handleUploads(): void
    {
        $uploadPath = FCPATH . 'uploads/settings/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Key-key yang bertipe image
        $imageKeys = ['logo_path', 'favicon_path', 'login_bg_path', 'landing_hero_path'];

        foreach ($imageKeys as $key) {
            $file = $this->request->getFile($key);
            if (!$file || !$file->isValid() || $file->hasMoved()) {
                continue;
            }

            // Validasi mime & ukuran (maks 2MB)
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'];
            if (!in_array($file->getMimeType(), $allowedMime)) {
                continue;
            }
            if ($file->getSize() > 2 * 1024 * 1024) {
                continue;
            }

            // Hapus file lama
            $existing = $this->settingModel->where('key', $key)->first();
            if ($existing && $existing['value']) {
                $oldFile = FCPATH . ltrim($existing['value'], '/');
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }

            // Simpan file baru dengan nama aman
            $newName = $key . '_' . time() . '.' . $file->guessExtension();
            $file->move($uploadPath, $newName);

            $this->settingModel->setValue($key, 'uploads/settings/' . $newName);
        }
    }
}
