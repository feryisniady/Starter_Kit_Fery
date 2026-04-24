<?php
namespace App\Controllers\Auditi;

use App\Controllers\BaseController;

abstract class BaseAuditi extends BaseController
{
    protected array  $entitas;
    protected int    $entitasId;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $entitas = getCurrentEntitas();
        if (!$entitas) {
            // Seharusnya sudah dicegah AuditiFilter, ini fallback
            session()->setFlashdata('error', 'Sesi tidak valid.');
            redirect()->to('/login')->send();
            exit;
        }
        $this->entitas   = $entitas;
        $this->entitasId = (int)$entitas['id'];
    }

    protected function view(string $view, array $data = []): string
    {
        return view($view, array_merge(['entitas' => $this->entitas], $data));
    }
}
