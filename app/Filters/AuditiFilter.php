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

        // Idle timeout: paksa logout setelah 8 jam tidak aktif
        $idleLimit    = 28800;
        $lastActivity = session()->get('last_activity');

        if ($lastActivity && (time() - (int)$lastActivity) > $idleLimit) {
            session()->destroy();
            return redirect()->to('/login')
                ->with('error', 'Sesi Anda berakhir karena tidak aktif selama 8 jam. Silakan login kembali.');
        }

        session()->set('last_activity', time());

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
