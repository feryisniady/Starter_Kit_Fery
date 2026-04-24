<?php
namespace App\Controllers\Auditi;

use App\Models\NhpModel;
use App\Models\TindakLanjutModel;

class DashboardController extends BaseAuditi
{
    public function index()
    {
        $nhpModel = new NhpModel();
        $tlModel  = new TindakLanjutModel();

        $nhpList = $nhpModel->getNhpByEntitas($this->entitasId);
        $tlSummary = $tlModel->getSummaryByEntitas($this->entitasId);

        $nhpPending   = count(array_filter($nhpList, fn($n) => (int)$n['jumlah_pending'] > 0));
        $nhpDitanggapi= count(array_filter($nhpList, fn($n) => $n['status'] === 'ditanggapi'));
        $nhpSelesai   = count(array_filter($nhpList, fn($n) => $n['status'] === 'selesai'));

        return $this->view('auditi/dashboard', [
            'title'         => 'Dashboard — ' . $this->entitas['nama'],
            'nhpPending'    => $nhpPending,
            'nhpDitanggapi' => $nhpDitanggapi,
            'nhpSelesai'    => $nhpSelesai,
            'nhpList'       => array_slice($nhpList, 0, 5),
            'tlSummary'     => $tlSummary,
        ]);
    }
}
