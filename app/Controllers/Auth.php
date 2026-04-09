<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoginServiceModel;
use App\Models\PasswordResetModel;

class Auth extends BaseController
{
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_MINUTES = 10;
    private const RESET_EXPIRE_MINUTES = 60;

    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // =========================================================
    // LOGIN
    // =========================================================

    public function login()
    {
        $serviceModel = new LoginServiceModel();

        return view('auth/login', [
            'services' => $serviceModel->getActive(),
        ]);
    }

    public function loginProcess()
    {
        $ip         = $this->request->getIPAddress();
        $attemptKey = 'login_attempts_' . md5($ip);
        $lockoutKey = 'login_lockout_' . md5($ip);

        $email    = strtolower(trim($this->request->getPost('email')));
        $password = $this->request->getPost('password');

        if ($this->isLockedOut($lockoutKey, $ip, $email)) {
            return redirect()->to('/login')->withInput()
                ->with('lockout_until', cache()->get($lockoutKey))
                ->with('error', $this->lockoutMessage($lockoutKey));
        }

        $user = $this->userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->handleFailedLogin($attemptKey, $lockoutKey, $email);
        }

        if (($user['status'] ?? 'active') !== 'active') {
            return redirect()->to('/login')->withInput()
                ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        cache()->delete($attemptKey);
        cache()->delete($lockoutKey);

        $this->setUserSession($user);

        logActivity('login', 'auth', "Login berhasil", $user['id'], $user['name']);

        return redirect()->to('/dashboard');
    }

    // =========================================================
    // REGISTER
    // =========================================================

    public function register()
    {
        $serviceModel = new LoginServiceModel();
        return view('auth/register', [
            'services' => $serviceModel->getActive(),
        ]);
    }

    public function registerProcess()
    {
        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
        ];

        $messages = [
            'name'             => ['required' => 'Nama wajib diisi.', 'min_length' => 'Nama minimal 3 karakter.'],
            'email'            => ['required' => 'Email wajib diisi.', 'valid_email' => 'Format email tidak valid.', 'is_unique' => 'Email sudah terdaftar.'],
            'password'         => ['required' => 'Password wajib diisi.', 'min_length' => 'Password minimal 8 karakter.'],
            'confirm_password' => ['required' => 'Konfirmasi password wajib diisi.', 'matches' => 'Konfirmasi password tidak cocok.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $password = $this->request->getPost('password');

        // Validasi kekuatan password server-side
        if (!preg_match('/[A-Z]/', $password)) {
            return redirect()->back()->withInput()->with('error', 'Password harus mengandung minimal 1 huruf besar.');
        }
        if (!preg_match('/[a-z]/', $password)) {
            return redirect()->back()->withInput()->with('error', 'Password harus mengandung minimal 1 huruf kecil.');
        }
        if (!preg_match('/[0-9]/', $password)) {
            return redirect()->back()->withInput()->with('error', 'Password harus mengandung minimal 1 angka.');
        }

        $userId = $this->userModel->insert([
            'name'     => $this->request->getPost('name'),
            'email'    => strtolower(trim($this->request->getPost('email'))),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'status'   => 'active',
        ]);

        // Auto-assign role default 'user'
        $db      = \Config\Database::connect();
        $role    = $db->table('roles')->where('name', 'user')->get()->getRowArray();
        if ($role) {
            $db->table('user_roles')->insert(['user_id' => $userId, 'role_id' => $role['id']]);
        }

        logActivity('register', 'auth', "Registrasi akun baru: " . $this->request->getPost('email'), $userId, $this->request->getPost('name'));

        return redirect()->to('/login')->with('success', 'Akun berhasil dibuat. Silakan login.');
    }

    // =========================================================
    // LUPA PASSWORD
    // =========================================================

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function forgotPasswordProcess()
    {
        $email = strtolower(trim($this->request->getPost('email')));

        if (!$this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('error', 'Format email tidak valid.');
        }

        $user = $this->userModel->where('email', $email)->first();

        // Selalu tampilkan pesan sukses (cegah user enumeration)
        if (!$user) {
            return redirect()->to('/forgot-password')
                ->with('success', 'Jika email terdaftar, link reset akan dikirim ke ' . esc($email));
        }

        $resetModel = new PasswordResetModel();
        $token      = $resetModel->createToken($email);
        $resetUrl   = base_url('/reset-password/' . $token);

        // Kirim email
        $sent = $this->sendResetEmail($email, $user['name'], $resetUrl);

        logActivity('forgot_password', 'auth', "Request reset password: {$email}", $user['id'], $user['name']);

        $msg = 'Link reset password telah dikirim ke ' . esc($email) . '. Berlaku ' . self::RESET_EXPIRE_MINUTES . ' menit.';

        // Di mode development, tampilkan link langsung jika email gagal
        if (!$sent && ENVIRONMENT !== 'production') {
            $msg .= '<br><br><strong>Dev mode:</strong> <a href="' . $resetUrl . '">' . $resetUrl . '</a>';
        }

        return redirect()->to('/forgot-password')->with('success', $msg);
    }

    public function resetPassword(string $token)
    {
        $resetModel = new PasswordResetModel();
        $reset      = $resetModel->findByToken($token);

        if (!$reset || $resetModel->isExpired($reset['created_at'], self::RESET_EXPIRE_MINUTES)) {
            return redirect()->to('/forgot-password')
                ->with('error', 'Link reset tidak valid atau sudah kadaluarsa. Silakan request ulang.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    public function resetPasswordProcess()
    {
        $token   = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirm  = $this->request->getPost('confirm_password');

        $resetModel = new PasswordResetModel();
        $reset      = $resetModel->findByToken($token);

        if (!$reset || $resetModel->isExpired($reset['created_at'], self::RESET_EXPIRE_MINUTES)) {
            return redirect()->to('/forgot-password')
                ->with('error', 'Link reset tidak valid atau sudah kadaluarsa.');
        }

        if (strlen($password) < 8) {
            return redirect()->back()->withInput()->with('error', 'Password minimal 8 karakter.');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return redirect()->back()->withInput()->with('error', 'Password harus mengandung minimal 1 huruf besar.');
        }
        if (!preg_match('/[a-z]/', $password)) {
            return redirect()->back()->withInput()->with('error', 'Password harus mengandung minimal 1 huruf kecil.');
        }
        if (!preg_match('/[0-9]/', $password)) {
            return redirect()->back()->withInput()->with('error', 'Password harus mengandung minimal 1 angka.');
        }
        if ($password !== $confirm) {
            return redirect()->back()->withInput()->with('error', 'Konfirmasi password tidak cocok.');
        }

        $user = $this->userModel->where('email', $reset['email'])->first();
        if (!$user) {
            return redirect()->to('/login')->with('error', 'Akun tidak ditemukan.');
        }

        $this->userModel->update($user['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $resetModel->deleteByEmail($reset['email']);

        logActivity('reset_password', 'auth', "Password berhasil direset", $user['id'], $user['name']);

        return redirect()->to('/login')->with('success', 'Password berhasil diubah. Silakan login.');
    }

    // =========================================================
    // LOGOUT
    // =========================================================

    public function logout()
    {
        logActivity('logout', 'auth', 'Logout');
        session()->destroy();
        return redirect()->to('/login');
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function setUserSession(array $user): void
    {
        $permissionsRaw = $this->userModel->getUserPermissions($user['id']);
        $permissions    = array_column($permissionsRaw, 'name');

        $rolesRaw      = $this->userModel->getUserRoles($user['id']);
        $roles         = array_column($rolesRaw, 'name');
        $firstRole     = $rolesRaw[0] ?? [];
        $userRoleLabel = !empty($firstRole['label']) ? $firstRole['label'] : ucfirst($firstRole['name'] ?? 'User');

        session()->regenerate(true);
        session()->set([
            'user_id'          => $user['id'],
            'user_name'        => $user['name'],
            'logged_in'        => true,
            'user_permissions' => $permissions,
            'roles'            => $roles,
            'user_avatar'      => $user['avatar'] ?? null,
            'user_role_label'  => $userRoleLabel,
        ]);
    }

    private function sendResetEmail(string $to, string $name, string $resetUrl): bool
    {
        $appName = app_setting('app_name') ?: 'Aplikasi';
        $body    = '<p>Halo <strong>' . esc($name) . '</strong>,</p>'
            . '<p>Anda menerima email ini karena ada permintaan reset password untuk akun Anda.</p>'
            . '<p><a href="' . $resetUrl . '" style="display:inline-block;padding:10px 20px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none">Reset Password</a></p>'
            . '<p>Link ini berlaku selama <strong>' . self::RESET_EXPIRE_MINUTES . ' menit</strong>.</p>'
            . '<p>Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.</p>'
            . '<hr><small>' . esc($appName) . '</small>';

        return send_mail($to, 'Reset Password — ' . $appName, $body);
    }

    private function handleFailedLogin($attemptKey, $lockoutKey, $email)
    {
        $attempts    = (int) cache()->get($attemptKey) + 1;
        $lockoutSecs = self::LOCKOUT_MINUTES * 60;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockoutUntil = time() + $lockoutSecs;
            cache()->save($lockoutKey, $lockoutUntil, $lockoutSecs);
            cache()->delete($attemptKey);

            logActivity('login_lockout', 'auth',
                "Akun diblokir setelah {$attempts}x gagal (email: {$email})", null, $email);

            return redirect()->to('/login')->withInput()
                ->with('lockout_until', $lockoutUntil)
                ->with('error', 'Akun diblokir sementara. Coba lagi dalam ' . self::LOCKOUT_MINUTES . ' menit.');
        }

        cache()->save($attemptKey, $attempts, $lockoutSecs);
        $sisa = self::MAX_ATTEMPTS - $attempts;

        logActivity('login_failed', 'auth',
            "Login gagal percobaan ke-{$attempts} (email: {$email})", null, $email);

        return redirect()->to('/login')->withInput()
            ->with('attempts', $attempts)
            ->with('error', "Email atau password salah! Sisa percobaan: {$sisa}x");
    }

    private function isLockedOut($lockoutKey, $ip, $email): bool
    {
        $lockoutUntil = cache()->get($lockoutKey);
        if (!$lockoutUntil) return false;

        $remaining = $lockoutUntil - time();
        if ($remaining > 0) {
            logActivity('login_blocked', 'auth', "Login diblokir untuk IP {$ip}", null, $email);
            return true;
        }

        cache()->delete($lockoutKey);
        return false;
    }

    private function lockoutMessage($lockoutKey): string
    {
        $lockoutUntil = cache()->get($lockoutKey);
        $minutes = ceil(($lockoutUntil - time()) / 60);
        return "Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit.";
    }
}
