<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfileController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        return view('profile/index', [
            'title' => 'Profil Saya',
            'user'  => $user,
        ]);
    }

    public function update()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        $rules = [
            'name'  => 'required|min_length[3]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,{$userId}]",
            'phone' => 'permit_empty|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('error', implode('<br>', $this->validator->getErrors()))
                ->withInput();
        }

        $data = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
        ];

        // Handle avatar upload
        $avatar = $this->request->getFile('avatar');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($avatar->getMimeType(), $allowed)) {
                return redirect()->back()->with('error', 'Format gambar tidak didukung (gunakan JPG, PNG, WEBP).');
            }
            if ($avatar->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()->with('error', 'Ukuran foto maksimal 2MB.');
            }

            // Hapus avatar lama
            if ($user['avatar'] && is_file(FCPATH . $user['avatar'])) {
                @unlink(FCPATH . $user['avatar']);
            }

            $uploadPath = FCPATH . 'uploads/avatars/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

            $newName = 'avatar_' . $userId . '_' . time() . '.' . $avatar->guessExtension();
            $avatar->move($uploadPath, $newName);
            $data['avatar'] = 'uploads/avatars/' . $newName;
        }

        $this->userModel->update($userId, $data);

        // Update session
        session()->set('user_name', $data['name']);

        logActivity('profile.update', 'profile', 'Profil diperbarui');

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePassword()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        $oldPassword = $this->request->getPost('old_password');
        $newPassword = $this->request->getPost('new_password');
        $confirm     = $this->request->getPost('confirm_password');

        if (!password_verify($oldPassword, $user['password'])) {
            return redirect()->back()->with('error_pw', 'Password lama tidak sesuai.');
        }

        if (strlen($newPassword) < 6) {
            return redirect()->back()->with('error_pw', 'Password baru minimal 6 karakter.');
        }

        if ($newPassword !== $confirm) {
            return redirect()->back()->with('error_pw', 'Konfirmasi password tidak cocok.');
        }

        $this->userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        logActivity('profile.change_password', 'profile', 'Password berhasil diubah');

        return redirect()->to('/profile')->with('success', 'Password berhasil diubah.');
    }

    public function deleteAvatar()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        if ($user['avatar'] && is_file(FCPATH . $user['avatar'])) {
            @unlink(FCPATH . $user['avatar']);
        }

        $this->userModel->update($userId, ['avatar' => null]);
        logActivity('profile.delete_avatar', 'profile', 'Foto profil dihapus');

        return redirect()->to('/profile')->with('success', 'Foto profil dihapus.');
    }
}
