<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Auth extends Controller
{
    public function login()
    {
        // kalau sudah login, langsung lempar ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->findByUsername((string) $username);

        if (! $user || ! password_verify((string) $password, $user['password'])) {
            log_aktivitas('login_gagal', 'auth', 'Percobaan login gagal dengan username "' . mb_substr((string) $username, 0, 50) . '"', [
                'id' => null, 'nama' => (string) $username, 'role' => null,
            ]);

            return redirect()->to('/login')->with('error', 'Username atau password salah.');
        }

        session()->set([
            'user_id'      => $user['id'],
            'username'     => $user['username'],
            'nama'         => $user['nama'],
            'role'         => $user['role'],
            'pelanggan_id' => $user['pelanggan_id'] ?? null,
            'logged_in'    => true,
        ]);

        log_aktivitas('login', 'auth', 'Login ke sistem sebagai ' . $user['role']);

        return redirect()->to('/dashboard')->with('success', 'Login berhasil, selamat datang ' . $user['nama'] . '!');
    }

    public function logout()
    {
        if (session()->get('logged_in')) {
            log_aktivitas('logout', 'auth', 'Logout dari sistem');
        }

        session()->destroy();

        return redirect()->to('/login')->with('success', 'Anda telah logout.');
    }
}
