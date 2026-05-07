<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

/**
 * Seed 14 kode rekomendasi berdasarkan PermenpanRB No. 42 Tahun 2011
 * tentang Pedoman Pemberian Rekomendasi Hasil Pengawasan APIP.
 *
 * php spark db:seed KodeRekomendasiSeeder
 */
class KodeRekomendasiSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['kode' => 'R.01', 'uraian' => 'Setor/kembalikan ke kas negara/daerah'],
            ['kode' => 'R.02', 'uraian' => 'Kembalikan kelebihan pembayaran kepada negara/daerah'],
            ['kode' => 'R.03', 'uraian' => 'Proses tuntutan perbendaharaan/ganti kerugian negara/daerah'],
            ['kode' => 'R.04', 'uraian' => 'Berikan sanksi administratif/teguran kepada pegawai yang bersalah'],
            ['kode' => 'R.05', 'uraian' => 'Limpahkan ke aparat penegak hukum untuk diproses secara hukum'],
            ['kode' => 'R.06', 'uraian' => 'Lengkapi/perbaiki dokumen pertanggungjawaban sesuai ketentuan'],
            ['kode' => 'R.07', 'uraian' => 'Susun/perbaiki SOP dan prosedur operasional yang memadai'],
            ['kode' => 'R.08', 'uraian' => 'Perkuat Sistem Pengendalian Intern (SPI) entitas'],
            ['kode' => 'R.09', 'uraian' => 'Perbaiki proses perencanaan kegiatan dan anggaran'],
            ['kode' => 'R.10', 'uraian' => 'Optimalkan pemungutan/penerimaan negara/daerah'],
            ['kode' => 'R.11', 'uraian' => 'Efisienkan penggunaan anggaran/belanja'],
            ['kode' => 'R.12', 'uraian' => 'Tingkatkan efektivitas pelaksanaan program dan kegiatan'],
            ['kode' => 'R.13', 'uraian' => 'Koordinasikan dengan instansi/pihak terkait untuk penyelesaian'],
            ['kode' => 'R.14', 'uraian' => 'Lakukan inventarisasi, sertifikasi, dan pengamanan aset'],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($data as $row) {
            $exists = $this->db->table('kode_rekomendasi')->where('kode', $row['kode'])->countAllResults();
            if (!$exists) {
                $this->db->table('kode_rekomendasi')->insert(array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        echo "KodeRekomendasiSeeder: " . count($data) . " kode rekomendasi (PermenpanRB 42/2011) siap.\n";
    }
}
