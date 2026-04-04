<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_MINUTES = 10;

    // Form login
    public function login()
    {
        return view('auth/login');
    }

    // Proses login
    public function loginProcess()
    {
        $ip         = $this->request->getIPAddress();
        $attemptKey = 'login_attempts_' . md5($ip);
        $lockoutKey = 'login_lockout_' . md5($ip);

        // --- Cek apakah sedang lockout ---
        $lockoutUntil = cache()->get($lockoutKey);
        if ($lockoutUntil) {
            $remaining = $lockoutUntil - time();
            if ($remaining > 0) {
                $minutes = ceil($remaining / 60);
                return redirect()->to('/login')->withInput()
                    ->with('lockout_until', $lockoutUntil)
                    ->with('error', "Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit.");
            }
            // Lockout sudah kadaluarsa, reset
            cache()->delete($lockoutKey);
            cache()->delete($attemptKey);
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

        // --- Login gagal ---
        if (!$user || !password_verify($password, $user['password'])) {
            $attempts    = (int) cache()->get($attemptKey) + 1;
            $lockoutSecs = self::LOCKOUT_MINUTES * 60;

            if ($attempts >= self::MAX_ATTEMPTS) {
                // Kunci akun sementara
                $lockoutUntil = time() + $lockoutSecs;
                cache()->save($lockoutKey, $lockoutUntil, $lockoutSecs);
                cache()->delete($attemptKey);

                return redirect()->to('/login')->withInput()
                    ->with('lockout_until', $lockoutUntil)
                    ->with('error', 'Akun diblokir sementara karena terlalu banyak percobaan login. Coba lagi dalam ' . self::LOCKOUT_MINUTES . ' menit.');
            }

            // Simpan attempt & info sisa percobaan
            cache()->save($attemptKey, $attempts, $lockoutSecs);
            $sisaPercobaan = self::MAX_ATTEMPTS - $attempts;

            return redirect()->to('/login')->withInput()
                ->with('attempts', $attempts)
                ->with('error', "Email atau password salah! Sisa percobaan: {$sisaPercobaan}x");
        }

        // --- Cek status user aktif ---
        if (($user['status'] ?? 'active') !== 'active') {
            return redirect()->to('/login')->withInput()
                ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // --- Login berhasil, reset rate limit ---
        cache()->delete($attemptKey);
        cache()->delete($lockoutKey);

        session()->set([
            'user_id'   => $user['id'],
            'user_name' => $user['name'],
            'logged_in' => true,
        ]);

        return redirect()->to('/dashboard');
    }

    // Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
