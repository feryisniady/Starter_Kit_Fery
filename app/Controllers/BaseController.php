<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
    }

    // =========================================================================
    // RBAC Helpers — tersedia di semua controller turunan
    // =========================================================================

    /**
     * Cek apakah user yang sedang login adalah admin/superadmin
     * atau memiliki permission spt.manage_all.
     */
    protected function isAdmin(): bool
    {
        return hasRole('superadmin') || hasRole('admin') || hasPermission('spt.manage_all');
    }

    /**
     * Ambil irban_id berdasarkan user_id (lewat tabel sdm).
     * Return null jika SDM belum terdaftar / tidak punya irban.
     */
    protected function getUserIrbanId(int $userId): ?int
    {
        $sdm = (new \App\Models\SdmModel())->where('user_id', $userId)->first();
        return $sdm ? (int) $sdm['irban_id'] : null;
    }

    /**
     * Cek apakah user saat ini boleh mengakses SPT tertentu.
     * Admin: semua SPT. Non-admin: hanya SPT milik irbannya sendiri.
     *
     * @param array $spt  Baris SPT yang sudah di-JOIN (harus ada kolom irban_id)
     */
    protected function canAccessSpt(array $spt): bool
    {
        if ($this->isAdmin()) return true;
        $irbanId = $this->getUserIrbanId((int) session()->get('user_id'));
        return $irbanId !== null && (int) $spt['irban_id'] === $irbanId;
    }
}
