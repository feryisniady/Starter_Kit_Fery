<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    // Form login
    public function login()
    {
        return view('auth/login');
    }

    // Proses login
    public function loginProcess()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->to('/login')->with('error', 'Email atau password salah!');
        }

        // Simpan session
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