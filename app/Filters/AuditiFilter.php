<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuditiFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userId  = (int) session()->get('user_id');
        $entitas = \Config\Database::connect()
            ->table('entitas')
            ->where('user_id', $userId)
            ->where('aktif', 1)
            ->get()->getRowArray();

        if (!$entitas) {
            session()->setFlashdata('error', 'Akun Anda tidak terhubung ke entitas manapun.');
            return redirect()->to('/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
