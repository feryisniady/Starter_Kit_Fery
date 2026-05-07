<?php
namespace App\Controllers\Auditi;

use App\Models\NhpModel;
use App\Models\NhpItemDokumenModel;

class NhpController extends BaseAuditi
{
    private NhpModel            $nhpModel;
    private NhpItemDokumenModel $dokModel;

    public function __construct()
    {
        $this->nhpModel = new NhpModel();
        $this->dokModel = new NhpItemDokumenModel();
    }

    /** Daftar semua NHP untuk entitas ini */
    public function index()
    {
        $list = $this->nhpModel->getNhpByEntitas($this->entitasId);
        return $this->view('auditi/nhp/index', [
            'title'   => 'Notisi Hasil Pemeriksaan',
            'nhpList' => $list,
        ]);
    }

    /** Detail NHP + form tanggapan per item */
    public function show(int $nhpId)
    {
        $nhp = $this->getNhpOrFail($nhpId);

        $db    = \Config\Database::connect();
        $items = $db->table('nhp_item ni')
            ->select('ni.*')
            ->where('ni.nhp_id', $nhpId)
            ->orderBy('ni.nomor_urut')
            ->get()->getResultArray();

        // Lampirkan dokumen per item
        foreach ($items as &$item) {
            $item['dokumen'] = $this->dokModel->getByItem($item['id']);
        }

        return $this->view('auditi/nhp/show', [
            'title' => 'NHP — ' . ($nhp['nomor_nhp'] ?: '#'.$nhp['id']),
            'nhp'   => $nhp,
            'items' => $items,
        ]);
    }

    /** AJAX: simpan tanggapan satu item */
    public function simpanItem(int $nhpId, int $itemId)
    {
        $nhp  = $this->getNhpOrFail($nhpId);
        $item = $this->getItemOrFail($itemId, $nhpId);

        if ($nhp['status'] === 'selesai') {
            return $this->jsonError('NHP sudah selesai, tidak dapat mengubah tanggapan.');
        }

        $status    = $this->request->getPost('status_tanggapan');
        $tanggapan = trim($this->request->getPost('tanggapan_entitas') ?? '');

        if (!in_array($status, ['sesuai', 'tidak_sesuai'])) {
            return $this->jsonError('Pilih status tanggapan terlebih dahulu.');
        }
        if (empty($tanggapan)) {
            return $this->jsonError('Isi keterangan / uraian tanggapan.');
        }

        $db = \Config\Database::connect();
        $db->table('nhp_item')->where('id', $itemId)->update([
            'tanggapan_entitas' => $tanggapan,
            'status_tanggapan'  => $status,
            'tgl_tanggapan'     => date('Y-m-d'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $this->autoUpdateNhpStatus($nhpId);

        return $this->response->setJSON(['success' => true, 'message' => 'Tanggapan berhasil disimpan.']);
    }

    /** AJAX: upload dokumen untuk satu nhp_item */
    public function uploadDokumen(int $nhpId, int $itemId)
    {
        $this->getNhpOrFail($nhpId);
        $this->getItemOrFail($itemId, $nhpId);

        $file = $this->request->getFile('dokumen');
        if (!$file || !$file->isValid()) {
            return $this->jsonError('File tidak valid atau tidak ditemukan.');
        }

        $allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];
        if (!in_array(strtolower($file->getClientExtension()), $allowed)) {
            return $this->jsonError('Format file tidak didukung. Gunakan PDF, Word, Excel, atau gambar.');
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return $this->jsonError('Ukuran file maksimal 10 MB.');
        }

        $newName = $file->getRandomName();
        $dir     = WRITEPATH . 'uploads/nhp-dokumen/';
        $file->move($dir, $newName);

        $id = $this->dokModel->insert([
            'nhp_item_id' => $itemId,
            'nama_file'   => $file->getClientName(),
            'path_file'   => 'nhp-dokumen/' . $newName,
            'ukuran'      => $file->getSize(),
            'keterangan'  => $this->request->getPost('keterangan'),
            'uploaded_by' => session()->get('user_id'),
            'uploaded_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'dokumen' => [
                'id'        => $id,
                'nama_file' => $file->getClientName(),
                'ukuran'    => $file->getSize(),
            ],
        ]);
    }

    /** AJAX: hapus dokumen */
    public function hapusDokumen(int $dokId)
    {
        $dok = $this->dokModel->find($dokId);
        if (!$dok) return $this->jsonError('Dokumen tidak ditemukan.');

        // Pastikan milik entitas ini
        $eid  = $this->entitasId;
        $item = \Config\Database::connect()->table('nhp_item ni')
            ->join('nhp n', 'n.id = ni.nhp_id')
            ->join('spt sp', 'sp.id = n.spt_id')
            ->join('pkpt_kegiatan pk', 'pk.id = sp.pkpt_kegiatan_id', 'left')
            ->join('pkpt_entitas pe', 'pe.pkpt_kegiatan_id = pk.id', 'left')
            ->where('ni.id', $dok['nhp_item_id'])
            ->where("(pe.entitas_id = $eid OR sp.entitas_id = $eid)")
            ->get()->getRowArray();

        if (!$item) return $this->jsonError('Akses ditolak.');

        $path = WRITEPATH . 'uploads/' . $dok['path_file'];
        if (file_exists($path)) unlink($path);
        $this->dokModel->delete($dokId);

        return $this->response->setJSON(['success' => true]);
    }

    /** Download dokumen — hanya dokumen milik entitas ini */
    public function downloadDokumen(int $dokId)
    {
        $dok = $this->dokModel->find($dokId);
        if (!$dok) return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');

        // Verifikasi kepemilikan: dokumen harus terkait NHP entitas ini
        $eid  = $this->entitasId;
        $owns = \Config\Database::connect()->table('nhp_item_dokumen d')
            ->join('nhp_item ni', 'ni.id = d.nhp_item_id')
            ->join('nhp n',       'n.id = ni.nhp_id')
            ->join('spt sp',      'sp.id = n.spt_id')
            ->join('pkpt_kegiatan pk', 'pk.id = sp.pkpt_kegiatan_id', 'left')
            ->join('pkpt_entitas pe',  'pe.pkpt_kegiatan_id = pk.id', 'left')
            ->where('d.id', $dokId)
            ->where("(pe.entitas_id = $eid OR sp.entitas_id = $eid)")
            ->countAllResults();

        if (!$owns) return redirect()->back()->with('error', 'Akses ditolak.');

        $path = WRITEPATH . 'uploads/' . $dok['path_file'];
        if (!file_exists($path)) return redirect()->back()->with('error', 'File tidak ditemukan di server.');

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Content-Disposition', 'inline; filename="' . $dok['nama_file'] . '"')
            ->setHeader('Content-Length', filesize($path))
            ->setBody(file_get_contents($path));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getNhpOrFail(int $nhpId): array
    {
        $eid = $this->entitasId;
        $db  = \Config\Database::connect();
        $nhp = $db->table('nhp n')
            ->select('n.*, sp.nomor_naskah as spt_nomor, sp.id as spt_id')
            ->join('spt sp', 'sp.id = n.spt_id')
            ->join('pkpt_kegiatan pk', 'pk.id = sp.pkpt_kegiatan_id', 'left')
            ->join('pkpt_entitas pe', 'pe.pkpt_kegiatan_id = pk.id', 'left')
            ->where('n.id', $nhpId)
            ->where("(pe.entitas_id = $eid OR sp.entitas_id = $eid)")
            ->where('n.status !=', 'draft')
            ->get()->getRowArray();

        if (!$nhp) {
            session()->setFlashdata('error', 'NHP tidak ditemukan.');
            redirect()->to('/auditi/nhp')->send(); exit;
        }
        return $nhp;
    }

    private function getItemOrFail(int $itemId, int $nhpId): array
    {
        $item = \Config\Database::connect()->table('nhp_item')
            ->where('id', $itemId)->where('nhp_id', $nhpId)->get()->getRowArray();
        if (!$item) {
            return $this->jsonError('Item tidak ditemukan.'); exit;
        }
        return $item;
    }

    private function autoUpdateNhpStatus(int $nhpId): void
    {
        $db    = \Config\Database::connect();
        $items = $db->table('nhp_item')->where('nhp_id', $nhpId)->get()->getResultArray();
        $allDone = count(array_filter($items, fn($i) => $i['status_tanggapan'] === 'pending')) === 0;
        if ($allDone) {
            $db->table('nhp')->where('id', $nhpId)->update(['status' => 'ditanggapi', 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }

    private function jsonError(string $msg)
    {
        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $msg]);
    }
}
