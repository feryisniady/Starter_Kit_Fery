<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TindakLanjutModel;
use App\Models\TindakLanjutDokumenModel;

class TlVerifikasiController extends BaseController
{
    protected TindakLanjutModel $tlModel;

    public function __construct()
    {
        $this->tlModel = new TindakLanjutModel();
    }

    public function index()
    {
        $tab    = $this->request->getGet('tab') ?? 'menunggu';
        $validTabs = ['menunggu', 'revisi', 'diterima', 'semua'];
        if (!in_array($tab, $validTabs)) $tab = 'menunggu';

        $all = $this->tlModel->getAllForAdmin();

        $counts = [
            'menunggu' => count(array_filter($all, fn($r) => $r['status_verifikasi'] === 'menunggu')),
            'revisi'   => count(array_filter($all, fn($r) => $r['status_verifikasi'] === 'revisi')),
            'diterima' => count(array_filter($all, fn($r) => $r['status_verifikasi'] === 'diterima')),
            'semua'    => count($all),
        ];

        $list = $tab === 'semua'
            ? $all
            : array_values(array_filter($all, fn($r) => $r['status_verifikasi'] === $tab));

        return view('admin/tl/index', [
            'title'           => 'Verifikasi Tindak Lanjut',
            'list'            => $list,
            'tab'             => $tab,
            'counts'          => $counts,
            'verifikasiLabel' => TindakLanjutModel::$verifikasiLabel,
            'verifikasiColor' => TindakLanjutModel::$verifikasiColor,
        ]);
    }

    public function show(int $tlId)
    {
        $tl = $this->tlModel->getDetailForAdmin($tlId);
        if (!$tl) return redirect()->to('/admin/tl')->with('error', 'Tindak lanjut tidak ditemukan.');

        return view('admin/tl/show', [
            'title'          => 'Detail Tindak Lanjut',
            'tl'             => $tl,
            'verifikasiLabel'=> TindakLanjutModel::$verifikasiLabel,
            'verifikasiColor'=> TindakLanjutModel::$verifikasiColor,
        ]);
    }

    public function verifikasi(int $tlId)
    {
        $tl = $this->tlModel->find($tlId);
        if (!$tl) return redirect()->back()->with('error', 'Tindak lanjut tidak ditemukan.');

        if ($tl['status_verifikasi'] !== 'menunggu') {
            return redirect()->back()->with('error', 'Tindak lanjut ini sudah diverifikasi sebelumnya.');
        }

        $status = $this->request->getPost('status_verifikasi');
        if (!in_array($status, ['diterima', 'revisi'])) {
            return redirect()->back()->with('error', 'Status verifikasi tidak valid.');
        }

        $catatan = trim($this->request->getPost('catatan_verifikasi') ?? '');
        if ($status === 'revisi' && empty($catatan)) {
            return redirect()->back()->with('error', 'Catatan wajib diisi saat meminta perbaikan.');
        }

        $userId = (int) session()->get('user_id');
        $now    = date('Y-m-d H:i:s');

        $this->tlModel->update($tlId, [
            'status_verifikasi'  => $status,
            'catatan_verifikasi' => $catatan ?: null,
            'verified_by'        => $userId,
            'verified_at'        => $now,
        ]);

        // Tutup rekomendasi jika TL diterima
        if ($status === 'diterima') {
            \Config\Database::connect()
                ->table('rekomendasi')
                ->where('id', $tl['rekomendasi_id'])
                ->update(['status' => 'selesai', 'updated_at' => $now]);
        }

        $labelLog = $status === 'diterima' ? 'Diterima' : 'Dikembalikan untuk Perbaikan';
        logActivity(
            'tl.verifikasi',
            'tindak_lanjut',
            "Verifikasi TL id={$tlId} → {$status}" . ($catatan ? " | Catatan: {$catatan}" : '')
        );

        $msg = $status === 'diterima'
            ? 'Tindak lanjut berhasil diterima. Rekomendasi ditandai selesai.'
            : 'Tindak lanjut dikembalikan untuk diperbaiki. Entitas akan melihat catatan Anda.';

        return redirect()->to('/admin/tl/' . $tlId)->with('success', $msg);
    }

    public function downloadDokumen(int $dokId)
    {
        $dokModel = new TindakLanjutDokumenModel();
        $dok = $dokModel->find($dokId);
        if (!$dok) return redirect()->back()->with('error', 'File tidak ditemukan.');

        // Verifikasi bahwa TL ini memang ada
        $tl = $this->tlModel->find($dok['tindak_lanjut_id']);
        if (!$tl) return redirect()->back()->with('error', 'Akses ditolak.');

        $path = WRITEPATH . 'uploads/tindak-lanjut/' . $dok['path_file'];
        if (!file_exists($path)) return redirect()->back()->with('error', 'File tidak tersedia di server.');

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Content-Disposition', 'inline; filename="' . $dok['nama_file'] . '"')
            ->setBody(file_get_contents($path));
    }
}
