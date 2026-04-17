<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KriteriaSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama_dokumen' => 'Nota Dinas',
                'instruksi_ai' => 'Analisis format penomoran, tanggal, dan perihal sesuai tata naskah dinas. Gunakan format Kondisi, Kriteria, Sebab, Akibat.',
                'created_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'nama_dokumen' => 'Laporan LRA',
                'instruksi_ai' => 'Reviu kesesuaian nilai realisasi dengan anggaran. Gunakan format Kondisi, Kriteria, Sebab, Akibat.',
                'created_at'   => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('m_kriteria')->insertBatch($data);
    }
}