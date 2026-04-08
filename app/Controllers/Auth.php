<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoginServiceModel;

class Auth extends BaseController
{
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_MINUTES = 10;

    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // Form login
    public function login()
    {
        $serviceModel = new LoginServiceModel();

        return view('auth/login', [
            'services' => $serviceModel->getActive(),
        ]);
    }

    // Proses login
    public function loginProcess()
    {
        $ip         = $this->request->getIPAddress();
        $attemptKey = 'login_attempts_' . md5($ip);
        $lockoutKey = 'login_lockout_' . md5($ip);

        $email    = strtolower(trim($this->request->getPost('email')));
        $password = $this->request->getPost('password');

        // =========================
        // 1. CEK LOCKOUT
        // =========================
        if ($this->isLockedOut($lockoutKey, $ip, $email)) {
            return redirect()->to('/login')->withInput()
                ->with('lockout_until', cache()->get($lockoutKey))
                ->with('error', $this->lockoutMessage($lockoutKey));
        }

        // =========================
        // 2. VALIDASI USER
        // =========================
        $user = $this->userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->handleFailedLogin($attemptKey, $lockoutKey, $email);
        }

        // =========================
        // 3. CEK STATUS
        // =========================
        if (($user['status'] ?? 'active') !== 'active') {
            return redirect()->to('/login')->withInput()
                ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // =========================
        // 4. LOGIN SUCCESS
        // =========================
        cache()->delete($attemptKey);
        cache()->delete($lockoutKey);



        $userModel = new \App\Models\UserModel();

        // permission
        $permissionsRaw = $userModel->getUserPermissions($user['id']);
        $permissions    = array_column($permissionsRaw, 'name');

        // role
        $rolesRaw = $userModel->getUserRoles($user['id']);
        $roles    = array_column($rolesRaw, 'name');

        session()->set([
            'user_id'     => $user['id'],
            'user_name'   => $user['name'],
            'logged_in'   => true,
            'user_permissions' => $permissions,
            'roles'       => $roles
        ]);


        logActivity('login', 'auth', "Login berhasil", $user['id'], $user['name']);

        return redirect()->to('/dashboard');
    }

    // =========================
    // HANDLE LOGIN GAGAL
    // =========================
    private function handleFailedLogin($attemptKey, $lockoutKey, $email)
    {
        $attempts    = (int) cache()->get($attemptKey) + 1;
        $lockoutSecs = self::LOCKOUT_MINUTES * 60;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockoutUntil = time() + $lockoutSecs;

            cache()->save($lockoutKey, $lockoutUntil, $lockoutSecs);
            cache()->delete($attemptKey);

            logActivity('login_lockout', 'auth',
                "Akun diblokir sementara setelah {$attempts}x percobaan gagal (email: {$email})",
                null,
                $email
            );

            return redirect()->to('/login')->withInput()
                ->with('lockout_until', $lockoutUntil)
                ->with('error', 'Akun diblokir sementara karena terlalu banyak percobaan login. Coba lagi dalam ' . self::LOCKOUT_MINUTES . ' menit.');
        }

        cache()->save($attemptKey, $attempts, $lockoutSecs);
        $sisa = self::MAX_ATTEMPTS - $attempts;

        logActivity('login_failed', 'auth',
            "Login gagal percobaan ke-{$attempts} (email: {$email})",
            null,
            $email
        );

        return redirect()->to('/login')->withInput()
            ->with('attempts', $attempts)
            ->with('error', "Email atau password salah! Sisa percobaan: {$sisa}x");
    }

    // =========================
    // CEK LOCKOUT
    // =========================
    private function isLockedOut($lockoutKey, $ip, $email): bool
    {
        $lockoutUntil = cache()->get($lockoutKey);

        if (!$lockoutUntil) {
            return false;
        }

        $remaining = $lockoutUntil - time();

        if ($remaining > 0) {
            logActivity('login_blocked', 'auth',
                "Login diblokir (lockout aktif) untuk IP {$ip}",
                null,
                $email
            );
            return true;
        }

        // expired → reset
        cache()->delete($lockoutKey);
        return false;
    }

    private function lockoutMessage($lockoutKey): string
    {
        $lockoutUntil = cache()->get($lockoutKey);
        $remaining = $lockoutUntil - time();
        $minutes = ceil($remaining / 60);

        return "Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit.";
    }

    // Logout
    public function logout()
    {
        logActivity('logout', 'auth', 'Logout');

        session()->destroy();

        return redirect()->to('/login');
    }
}