<?php
namespace App\Controllers\Auditi;

use App\Models\TindakLanjutModel;
use App\Models\TindakLanjutDokumenModel;

class TlController extends BaseAuditi
{
    private TindakLanjutModel        $tlModel;
    private TindakLanjutDokumenModel $dokModel;

    public function __construct()
    {
        $this->tlModel  = new TindakLanjutModel();
        $this->dokModel = new TindakLanjutDokumenModel();
    }

    /** Daftar semua rekomendasi + status TL */
    public function index()
    {
        $list = $this->tlModel->getRekomendasiByEntitas($this->entitasId);
        return $this->view('auditi/tl/index', [
            'title' => 'Tindak Lanjut Rekomendasi',
            'list'  => $list,
        ]);
    }

    /** Detail 1 rekomendasi + history TL + form kirim TL baru */
    public function show(int $rekId)
    {
        $rek = $this->getRekOrFail($rekId);
        $tls = $this->tlModel->getByRekomendasi($rekId);

        return $this->view('auditi/tl/show', [
            'title' => 'Tindak Lanjut — Rekomendasi #' . $rek['nomor_urut'],
            'rek'   => $rek,
            'tls'   => $tls,
            'verifikasiLabel' => TindakLanjutModel::$verifikasiLabel,
            'verifikasiColor' => TindakLanjutModel::$verifikasiColor,
        ]);
    }

    /** POST: kirim TL baru untuk satu rekomendasi */
    public function kirim(int $rekId)
    {
        $rek    = $this->getRekOrFail($rekId);
        $uraian = trim($this->request->getPost('uraian') ?? '');

        if (empty($uraian)) {
            return redirect()->back()->with('error', 'Uraian tindak lanjut wajib diisi.');
        }

        // Cek tidak ada TL yang masih menunggu verifikasi
        $existing = $this->tlModel->where('rekomendasi_id', $rekId)
                                  ->where('status_verifikasi', 'menunggu')
                                  ->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Masih ada tindak lanjut yang menunggu verifikasi BPKP. Tunggu hasilnya terlebih dahulu.');
        }

        $tlId = $this->tlModel->insert([
            'rekomendasi_id'    => $rekId,
            'entitas_id'        => $this->entitasId,
            'uraian'            => $uraian,
            'status_verifikasi' => 'menunggu',
        ]);

        // Upload dokumen (bisa multiple)
        $files = $this->request->getFileMultiple('dokumen');
        if ($files) {
            foreach ($files as $file) {
                if (!$file || !$file->isValid() || $file->hasMoved()) continue;
                $this->simpanFileTl($tlId, $file, $this->request->getPost('keterangan'));
            }
        }

        // Update status rekomendasi → proses
        \Config\Database::connect()->table('rekomendasi')
            ->where('id', $rekId)->update(['status' => 'proses', 'updated_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/auditi/tl/' . $rekId)
            ->with('success', 'Tindak lanjut berhasil dikirim. Menunggu verifikasi BPKP.');
    }

    /** AJAX: upload tambahan dokumen ke TL yang sudah ada */
    public function uploadDokumen(int $tlId)
    {
        $tl = $this->tlModel->find($tlId);
        if (!$tl || (int)$tl['entitas_id'] !== $this->entitasId) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $file = $this->request->getFile('dokumen');
        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'File tidak valid.']);
        }

        $id = $this->simpanFileTl($tlId, $file, $this->request->getPost('keterangan'));
        return $this->response->setJSON(['success' => true, 'id' => $id, 'nama_file' => $file->getClientName()]);
    }

    /** Download dokumen TL */
    public function downloadDokumen(int $dokId)
    {
        $dok = $this->dokModel->find($dokId);
        if (!$dok) return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');

        // Verifikasi kepemilikan
        $tl = $this->tlModel->find($dok['tindak_lanjut_id']);
        if (!$tl || (int)$tl['entitas_id'] !== $this->entitasId) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $path = WRITEPATH . 'uploads/' . $dok['path_file'];
        if (!file_exists($path)) return redirect()->back()->with('error', 'File tidak ditemukan.');

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Content-Disposition', 'inline; filename="' . $dok['nama_file'] . '"')
            ->setBody(file_get_contents($path));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getRekOrFail(int $rekId): array
    {
        $rek = \Config\Database::connect()->table('rekomendasi r')
            ->select('r.*, t.judul, t.kondisi, t.sebab, t.akibat, t.nilai_temuan, t.spt_id,
                      sp.nomor_naskah as spt_nomor')
            ->join('temuan t', 't.id = r.temuan_id')
            ->join('spt sp', 'sp.id = t.spt_id')
            ->join('pkpt_kegiatan pk', 'pk.id = sp.pkpt_kegiatan_id')
            ->join('pkpt_entitas pe', 'pe.pkpt_kegiatan_id = pk.id')
            ->where('r.id', $rekId)
            ->where('pe.entitas_id', $this->entitasId)
            ->get()->getRowArray();

        if (!$rek) {
            session()->setFlashdata('error', 'Rekomendasi tidak ditemukan.');
            redirect()->to('/auditi/tl')->send(); exit;
        }
        return $rek;
    }

    private function simpanFileTl(int $tlId, $file, ?string $keterangan): int
    {
        $allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];
        if (!in_array(strtolower($file->getClientExtension()), $allowed)) return 0;
        if ($file->getSizeByUnit('mb') > 10) return 0;

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/tindak-lanjut/', $newName);

        return (int) $this->dokModel->insert([
            'tindak_lanjut_id' => $tlId,
            'nama_file'        => $file->getClientName(),
            'path_file'        => 'tindak-lanjut/' . $newName,
            'ukuran'           => $file->getSize(),
            'keterangan'       => $keterangan,
            'uploaded_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
