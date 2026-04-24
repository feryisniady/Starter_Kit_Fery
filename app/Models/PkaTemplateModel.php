<?php
namespace App\Models;
use CodeIgniter\Model;

class PkaTemplateModel extends Model
{
    protected $table      = 'pka_template';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama','jenis_audit','deskripsi','created_by'];
    protected $useTimestamps = true;

    /** Semua template, diurutkan berdasarkan jenis_audit */
    public function getAll(): array
    {
        $db   = \Config\Database::connect();
        $rows = $this->orderBy('jenis_audit')->orderBy('nama')->findAll();

        foreach ($rows as &$r) {
            $r['item_count'] = $db->table('pka_template_item')
                ->where('template_id', $r['id'])->countAllResults();
        }
        return $rows;
    }

    /** Satu template beserta semua item-nya */
    public function getWithItems(int $id): ?array
    {
        $tpl = $this->find($id);
        if (!$tpl) return null;

        $tpl['items'] = \Config\Database::connect()
            ->table('pka_template_item')
            ->where('template_id', $id)
            ->orderBy('fase')->orderBy('nomor_urut')
            ->get()->getResultArray();

        return $tpl;
    }

    /** Items per template dikelompokkan per fase */
    public function getItemsGrouped(int $templateId): array
    {
        $items = \Config\Database::connect()
            ->table('pka_template_item')
            ->where('template_id', $templateId)
            ->orderBy('fase')->orderBy('nomor_urut')
            ->get()->getResultArray();

        $grouped = ['persiapan' => [], 'pelaksanaan' => [], 'pelaporan' => []];
        foreach ($items as $item) {
            $grouped[$item['fase']][] = $item;
        }
        return $grouped;
    }

    /** Salin template ke PKA untuk 1 SPT */
    public function applyToSpt(int $templateId, int $sptId, int $createdBy): int
    {
        $tpl = $this->getWithItems($templateId);
        if (!$tpl) return 0;

        $db    = \Config\Database::connect();
        $now   = date('Y-m-d H:i:s');
        $count = 0;

        // Nomor urut terakhir per fase
        $nomorMap = [];
        foreach (['persiapan','pelaksanaan','pelaporan'] as $fase) {
            $row = $db->table('pka')->where('spt_id', $sptId)->where('fase', $fase)
                ->selectMax('nomor_urut')->get()->getRowArray();
            $nomorMap[$fase] = (int)($row['nomor_urut'] ?? 0);
        }

        foreach ($tpl['items'] as $item) {
            $fase = $item['fase'];
            $nomorMap[$fase]++;
            $db->table('pka')->insert([
                'spt_id'          => $sptId,
                'fase'            => $fase,
                'nomor_urut'      => $nomorMap[$fase],
                'uraian_prosedur' => $item['uraian_prosedur'],
                'status'          => 'belum',
                'created_by'      => $createdBy,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
            $count++;
        }
        return $count;
    }
}
