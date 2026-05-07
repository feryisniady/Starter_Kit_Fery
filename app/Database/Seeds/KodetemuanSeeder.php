<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

/**
 * Seed 89 kode temuan berdasarkan PermenpanRB No. 41 Tahun 2011.
 * php spark db:seed KodetemuanSeeder
 *
 * Jenis: 1 = Kerugian/Potensi Kerugian/Kekurangan Penerimaan/Adm/Pidana (kode 1.xx)
 *        2 = Kelemahan Sistem Pengendalian Intern (kode 2.xx)
 *        3 = Ketidakhematan / Ketidakefisienan / Ketidakefektifan (kode 3.xx)
 *
 * alternatif = kode rekomendasi relevan (comma-separated), merujuk tabel kode_rekomendasi
 *   R.01 Setor/kembalikan ke kas negara/daerah
 *   R.02 Kembalikan kelebihan pembayaran
 *   R.03 Proses tuntutan perbendaharaan/ganti kerugian
 *   R.04 Berikan sanksi administratif/teguran
 *   R.05 Limpahkan ke aparat penegak hukum
 *   R.06 Lengkapi/perbaiki dokumen pertanggungjawaban
 *   R.07 Susun/perbaiki SOP dan prosedur
 *   R.08 Perkuat Sistem Pengendalian Intern
 *   R.09 Perbaiki proses perencanaan kegiatan/anggaran
 *   R.10 Optimalkan penerimaan negara/daerah
 *   R.11 Efisienkan penggunaan anggaran/belanja
 *   R.12 Tingkatkan efektivitas program dan kegiatan
 *   R.13 Koordinasikan dengan instansi/pihak terkait
 *   R.14 Inventarisasi, sertifikasi, dan pengamanan aset
 */
class KodetemuanSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // ─── 1.01 — Kerugian Negara/Daerah ──────────────────────────────────
            ['kode'=>'1.01.01','uraian'=>'Belanja dan/atau pengadaan barang/jasa fiktif','jenis'=>1,'alternatif'=>'R.01,R.02,R.04,R.05'],
            ['kode'=>'1.01.02','uraian'=>'Rekanan pengadaan barang/jasa tidak menyelesaikan pekerjaan','jenis'=>1,'alternatif'=>'R.01,R.02,R.04'],
            ['kode'=>'1.01.03','uraian'=>'Kekurangan volume pekerjaan dan/atau barang','jenis'=>1,'alternatif'=>'R.01,R.02,R.04'],
            ['kode'=>'1.01.04','uraian'=>'Kelebihan pembayaran selain kekurangan volume pekerjaan dan/atau barang','jenis'=>1,'alternatif'=>'R.01,R.02,R.04'],
            ['kode'=>'1.01.05','uraian'=>'Pemahalan harga (Mark up)','jenis'=>1,'alternatif'=>'R.01,R.02,R.04,R.05'],
            ['kode'=>'1.01.06','uraian'=>'Penggunaan uang/barang untuk kepentingan pribadi','jenis'=>1,'alternatif'=>'R.01,R.03,R.04,R.05'],
            ['kode'=>'1.01.07','uraian'=>'Pembayaran honorarium dan/atau biaya perjalanan dinas ganda dan/atau melebihi standar yang ditetapkan','jenis'=>1,'alternatif'=>'R.01,R.02,R.04'],
            ['kode'=>'1.01.08','uraian'=>'Spesifikasi barang/jasa yang diterima tidak sesuai dengan kontrak','jenis'=>1,'alternatif'=>'R.02,R.04,R.06'],
            ['kode'=>'1.01.09','uraian'=>'Belanja tidak sesuai atau melebihi ketentuan','jenis'=>1,'alternatif'=>'R.01,R.02,R.04,R.06'],
            ['kode'=>'1.01.10','uraian'=>'Pengembalian pinjaman/piutang atau dana bergulir macet','jenis'=>1,'alternatif'=>'R.01,R.03,R.13'],
            ['kode'=>'1.01.11','uraian'=>'Kelebihan penetapan dan pembayaran restitusi pajak atau penetapan kompensasi kerugian','jenis'=>1,'alternatif'=>'R.01,R.02,R.04'],
            ['kode'=>'1.01.12','uraian'=>'Penjualan/pertukaran/penghapusan aset negara/daerah tidak sesuai ketentuan dan merugikan negara/daerah','jenis'=>1,'alternatif'=>'R.01,R.04,R.14'],
            ['kode'=>'1.01.13','uraian'=>'Pengenaan ganti kerugian negara belum/tidak dilaksanakan sesuai ketentuan','jenis'=>1,'alternatif'=>'R.03,R.04'],
            ['kode'=>'1.01.14','uraian'=>'Entitas belum/tidak melaksanakan tuntutan perbendaharaan (TP) sesuai ketentuan','jenis'=>1,'alternatif'=>'R.03,R.04'],
            ['kode'=>'1.01.15','uraian'=>'Penghapusan hak tagih tidak sesuai ketentuan','jenis'=>1,'alternatif'=>'R.04,R.06,R.13'],
            ['kode'=>'1.01.16','uraian'=>'Pelanggaran ketentuan pemberian diskon penjualan','jenis'=>1,'alternatif'=>'R.01,R.04,R.07'],
            ['kode'=>'1.01.17','uraian'=>'Penentuan HPP terlalu rendah sehingga penentuan harga jual lebih rendah dari yang seharusnya','jenis'=>1,'alternatif'=>'R.01,R.04,R.07'],
            ['kode'=>'1.01.18','uraian'=>'Jaminan pelaksanaan dalam pelaksanaan pekerjaan, pemanfaatan barang dan pemberian fasilitas tidak dapat dicairkan','jenis'=>1,'alternatif'=>'R.01,R.04,R.13'],
            ['kode'=>'1.01.19','uraian'=>'Penyetoran penerimaan negara/daerah dengan bukti fiktif','jenis'=>1,'alternatif'=>'R.01,R.04,R.05,R.06'],
            // ─── 1.02 — Potensi Kerugian Negara/Daerah ─────────────────────────
            ['kode'=>'1.02.01','uraian'=>'Kelebihan pembayaran dalam pengadaan barang/jasa tetapi pembayaran pekerjaan belum dilakukan sebagian atau seluruhnya','jenis'=>1,'alternatif'=>'R.02,R.04,R.06'],
            ['kode'=>'1.02.02','uraian'=>'Rekanan belum melaksanakan kewajiban pemeliharaan barang hasil pengadaan yang telah rusak selama masa pemeliharaan','jenis'=>1,'alternatif'=>'R.04,R.13'],
            ['kode'=>'1.02.03','uraian'=>'Aset dikuasai pihak lain','jenis'=>1,'alternatif'=>'R.13,R.14'],
            ['kode'=>'1.02.04','uraian'=>'Pembelian aset yang berstatus sengketa','jenis'=>1,'alternatif'=>'R.13,R.14'],
            ['kode'=>'1.02.05','uraian'=>'Aset tidak diketahui keberadaannya','jenis'=>1,'alternatif'=>'R.04,R.14'],
            ['kode'=>'1.02.06','uraian'=>'Pemberian jaminan pelaksanaan dalam pelaksanaan pekerjaan, pemanfaatan barang dan pemberian fasilitas tidak sesuai ketentuan','jenis'=>1,'alternatif'=>'R.04,R.06,R.07'],
            ['kode'=>'1.02.07','uraian'=>'Pihak ketiga belum melaksanakan kewajiban untuk menyerahkan aset kepada negara/daerah','jenis'=>1,'alternatif'=>'R.13,R.14'],
            ['kode'=>'1.02.08','uraian'=>'Piutang/pinjaman atau dana bergulir yang berpotensi tidak tertagih','jenis'=>1,'alternatif'=>'R.03,R.07,R.13'],
            ['kode'=>'1.02.09','uraian'=>'Penghapusan piutang tidak sesuai ketentuan','jenis'=>1,'alternatif'=>'R.04,R.06,R.07'],
            ['kode'=>'1.02.10','uraian'=>'Pencairan anggaran pada akhir tahun anggaran untuk pekerjaan yang belum selesai','jenis'=>1,'alternatif'=>'R.04,R.06,R.09'],
            // ─── 1.03 — Kekurangan Penerimaan ───────────────────────────────────
            ['kode'=>'1.03.01','uraian'=>'Penerimaan negara/daerah atau denda keterlambatan pekerjaan belum/tidak ditetapkan dipungut/diterima/disetor ke kas negara/daerah','jenis'=>1,'alternatif'=>'R.01,R.04,R.10'],
            ['kode'=>'1.03.02','uraian'=>'Penggunaan langsung penerimaan negara/daerah','jenis'=>1,'alternatif'=>'R.01,R.04,R.06'],
            ['kode'=>'1.03.03','uraian'=>'Dana Perimbangan yang telah ditetapkan belum masuk ke kas daerah','jenis'=>1,'alternatif'=>'R.01,R.13'],
            ['kode'=>'1.03.04','uraian'=>'Penerimaan negara/daerah diterima atau digunakan oleh instansi yang tidak berhak','jenis'=>1,'alternatif'=>'R.01,R.04,R.05'],
            ['kode'=>'1.03.05','uraian'=>'Pengenaan tarif pajak/PNBP lebih rendah dari ketentuan','jenis'=>1,'alternatif'=>'R.01,R.04,R.10'],
            ['kode'=>'1.03.06','uraian'=>'Koreksi perhitungan bagi hasil dengan KKKS','jenis'=>1,'alternatif'=>'R.01,R.13'],
            ['kode'=>'1.03.07','uraian'=>'Kelebihan pembayaran subsidi oleh pemerintah','jenis'=>1,'alternatif'=>'R.01,R.02,R.04'],
            // ─── 1.04 — Administrasi ─────────────────────────────────────────────
            ['kode'=>'1.04.01','uraian'=>'Pertanggungjawaban tidak akuntabel (bukti tidak lengkap/tidak valid)','jenis'=>1,'alternatif'=>'R.04,R.06'],
            ['kode'=>'1.04.02','uraian'=>'Pekerjaan dilaksanakan mendahului kontrak atau penetapan anggaran','jenis'=>1,'alternatif'=>'R.04,R.06,R.07'],
            ['kode'=>'1.04.03','uraian'=>'Proses pengadaan barang/jasa tidak sesuai ketentuan (tidak menimbulkan kerugian negara)','jenis'=>1,'alternatif'=>'R.04,R.07'],
            ['kode'=>'1.04.04','uraian'=>'Pemecahan kontrak untuk menghindari pelelangan','jenis'=>1,'alternatif'=>'R.04,R.07'],
            ['kode'=>'1.04.05','uraian'=>'Pelaksanaan lelang secara performa','jenis'=>1,'alternatif'=>'R.04,R.07'],
            ['kode'=>'1.04.06','uraian'=>'Penyimpangan terhadap peraturan perundang-undangan bidang pengelolaan perlengkapan atau barang milik negara/daerah/perusahaan','jenis'=>1,'alternatif'=>'R.04,R.06,R.14'],
            ['kode'=>'1.04.07','uraian'=>'Penyimpangan terhadap peraturan perundang-undangan bidang tertentu lainnya seperti kehutanan, pertambangan, perpajakan, dll','jenis'=>1,'alternatif'=>'R.04,R.06,R.13'],
            ['kode'=>'1.04.08','uraian'=>'Koreksi perhitungan subsidi/kewajiban pelayanan umum','jenis'=>1,'alternatif'=>'R.04,R.06'],
            ['kode'=>'1.04.09','uraian'=>'Pembentukan cadangan piutang, perhitungan penyusutan atau amortisasi tidak sesuai ketentuan','jenis'=>1,'alternatif'=>'R.04,R.06,R.07'],
            ['kode'=>'1.04.10','uraian'=>'Penyetoran penerimaan negara/daerah atau kas di bendaharawan ke kas negara/daerah melebihi batas waktu yang ditentukan','jenis'=>1,'alternatif'=>'R.01,R.04,R.07'],
            ['kode'=>'1.04.11','uraian'=>'Pertanggungjawaban/penyetoran uang persediaan melebihi batas waktu yang ditentukan','jenis'=>1,'alternatif'=>'R.04,R.06,R.07'],
            ['kode'=>'1.04.12','uraian'=>'Sisa kas di bendahara pengeluaran akhir tahun anggaran belum/tidak disetor ke kas negara/daerah','jenis'=>1,'alternatif'=>'R.01,R.04'],
            ['kode'=>'1.04.13','uraian'=>'Pengeluaran investasi pemerintah tidak didukung bukti yang sah','jenis'=>1,'alternatif'=>'R.04,R.06'],
            ['kode'=>'1.04.14','uraian'=>'Kepemilikan aset tidak/belum didukung bukti yang sah','jenis'=>1,'alternatif'=>'R.06,R.14'],
            ['kode'=>'1.04.15','uraian'=>'Pengalihan anggaran antar MAK tidak sah','jenis'=>1,'alternatif'=>'R.04,R.06'],
            ['kode'=>'1.04.16','uraian'=>'Pelampauan pagu anggaran','jenis'=>1,'alternatif'=>'R.04,R.06,R.09'],
            // ─── 1.05 — Indikasi Pidana ──────────────────────────────────────────
            ['kode'=>'1.05.01','uraian'=>'Indikasi tindak pidana korupsi','jenis'=>1,'alternatif'=>'R.05'],
            ['kode'=>'1.05.02','uraian'=>'Indikasi tindak pidana perbankan','jenis'=>1,'alternatif'=>'R.05'],
            ['kode'=>'1.05.03','uraian'=>'Indikasi tindak pidana perpajakan','jenis'=>1,'alternatif'=>'R.05'],
            ['kode'=>'1.05.04','uraian'=>'Indikasi tindak pidana kepabeanan','jenis'=>1,'alternatif'=>'R.05'],
            ['kode'=>'1.05.05','uraian'=>'Indikasi tindak pidana kehutanan','jenis'=>1,'alternatif'=>'R.05'],
            ['kode'=>'1.05.06','uraian'=>'Indikasi tindak pidana pasar modal','jenis'=>1,'alternatif'=>'R.05'],
            ['kode'=>'1.05.07','uraian'=>'Indikasi tindak pidana khusus lainnya','jenis'=>1,'alternatif'=>'R.05'],
            // ─── 2.01 — Kelemahan SPI — Pelaporan ───────────────────────────────
            ['kode'=>'2.01.01','uraian'=>'Pencatatan tidak/belum dilakukan atau tidak akurat','jenis'=>2,'alternatif'=>'R.06,R.07,R.08'],
            ['kode'=>'2.01.02','uraian'=>'Proses penyusunan laporan tidak sesuai ketentuan','jenis'=>2,'alternatif'=>'R.04,R.07,R.08'],
            ['kode'=>'2.01.03','uraian'=>'Entitas terlambat menyampaikan laporan','jenis'=>2,'alternatif'=>'R.04,R.07'],
            ['kode'=>'2.01.04','uraian'=>'Sistem informasi akuntansi dan pelaporan tidak memadai','jenis'=>2,'alternatif'=>'R.08,R.09'],
            ['kode'=>'2.01.05','uraian'=>'Sistem informasi akuntansi dan pelaporan belum didukung SDM yang memadai','jenis'=>2,'alternatif'=>'R.08,R.09'],
            // ─── 2.02 — Kelemahan SPI — Pelaksanaan Anggaran ────────────────────
            ['kode'=>'2.02.01','uraian'=>'Perencanaan kegiatan tidak memadai','jenis'=>2,'alternatif'=>'R.07,R.08,R.09'],
            ['kode'=>'2.02.02','uraian'=>'Mekanisme pemungutan, penyetoran dan pelaporan serta penggunaan penerimaan negara/daerah/perusahaan dan hibah tidak sesuai ketentuan','jenis'=>2,'alternatif'=>'R.07,R.08,R.10'],
            ['kode'=>'2.02.03','uraian'=>'Penyimpangan terhadap peraturan perundang-undangan bidang teknis tertentu atau ketentuan intern organisasi yang diperiksa tentang pendapatan dan belanja','jenis'=>2,'alternatif'=>'R.04,R.07,R.08'],
            ['kode'=>'2.02.04','uraian'=>'Pelaksanaan belanja di luar mekanisme APBN/APBD','jenis'=>2,'alternatif'=>'R.04,R.07,R.08'],
            ['kode'=>'2.02.05','uraian'=>'Penetapan/pelaksanaan kebijakan tidak tepat atau belum dilakukan berakibat hilangnya potensi penerimaan/pendapatan','jenis'=>2,'alternatif'=>'R.07,R.08,R.10'],
            ['kode'=>'2.02.06','uraian'=>'Penetapan/pelaksanaan kebijakan tidak tepat atau belum dilakukan berakibat peningkatan biaya/belanja','jenis'=>2,'alternatif'=>'R.07,R.08,R.11'],
            ['kode'=>'2.02.07','uraian'=>'Kelemahan pengelolaan fisik aset','jenis'=>2,'alternatif'=>'R.08,R.14'],
            // ─── 2.03 — Kelemahan SPI — Struktur ────────────────────────────────
            ['kode'=>'2.03.01','uraian'=>'Entitas tidak memiliki SOP yang formal untuk suatu prosedur atau keseluruhan prosedur','jenis'=>2,'alternatif'=>'R.07,R.08'],
            ['kode'=>'2.03.02','uraian'=>'SOP yang ada pada entitas tidak berjalan secara optimal atau tidak ditaati','jenis'=>2,'alternatif'=>'R.04,R.07,R.08'],
            ['kode'=>'2.03.03','uraian'=>'Entitas tidak memiliki satuan pengawas intern','jenis'=>2,'alternatif'=>'R.08'],
            ['kode'=>'2.03.04','uraian'=>'Satuan pengawas intern yang ada tidak memadai atau tidak berjalan optimal','jenis'=>2,'alternatif'=>'R.08'],
            ['kode'=>'2.03.05','uraian'=>'Tidak ada pemisahan tugas dan fungsi yang memadai','jenis'=>2,'alternatif'=>'R.07,R.08'],
            // ─── 3.01 — Ketidakhematan ───────────────────────────────────────────
            ['kode'=>'3.01.01','uraian'=>'Pengadaan barang/jasa melebihi kebutuhan','jenis'=>3,'alternatif'=>'R.09,R.11'],
            ['kode'=>'3.01.02','uraian'=>'Penetapan kualitas dan kuantitas barang/jasa yang digunakan tidak sesuai standar','jenis'=>3,'alternatif'=>'R.07,R.09,R.11'],
            ['kode'=>'3.01.03','uraian'=>'Pemborosan keuangan negara/daerah/perusahaan atau kemahalan harga','jenis'=>3,'alternatif'=>'R.04,R.09,R.11'],
            // ─── 3.02 — Ketidakefisienan ─────────────────────────────────────────
            ['kode'=>'3.02.01','uraian'=>'Penggunaan kuantitas input untuk satu satuan output lebih besar/tinggi dari yang seharusnya','jenis'=>3,'alternatif'=>'R.09,R.11'],
            ['kode'=>'3.02.02','uraian'=>'Penggunaan kualitas input untuk satu satuan output lebih tinggi dari seharusnya','jenis'=>3,'alternatif'=>'R.09,R.11'],
            // ─── 3.03 — Ketidakefektifan ─────────────────────────────────────────
            ['kode'=>'3.03.01','uraian'=>'Penggunaan anggaran tidak tepat sasaran/tidak sesuai peruntukan','jenis'=>3,'alternatif'=>'R.04,R.09,R.12'],
            ['kode'=>'3.03.02','uraian'=>'Pemanfaatan barang/jasa dilakukan tidak sesuai dengan rencana yang ditetapkan','jenis'=>3,'alternatif'=>'R.09,R.12'],
            ['kode'=>'3.03.03','uraian'=>'Barang yang dibeli belum/tidak dapat dimanfaatkan','jenis'=>3,'alternatif'=>'R.09,R.12,R.14'],
            ['kode'=>'3.03.04','uraian'=>'Pemanfaatan barang/jasa tidak berdampak terhadap pencapaian tujuan organisasi','jenis'=>3,'alternatif'=>'R.09,R.12'],
            ['kode'=>'3.03.05','uraian'=>'Pelaksanaan kegiatan terlambat/terhambat sehingga mempengaruhi pencapaian tujuan organisasi','jenis'=>3,'alternatif'=>'R.04,R.09,R.12'],
            ['kode'=>'3.03.06','uraian'=>'Pelayanan kepada masyarakat tidak optimal','jenis'=>3,'alternatif'=>'R.12,R.13'],
            ['kode'=>'3.03.07','uraian'=>'Fungsi atau tugas instansi yang diperiksa tidak diselenggarakan dengan baik termasuk target penerimaan tidak tercapai','jenis'=>3,'alternatif'=>'R.08,R.09,R.12'],
            ['kode'=>'3.03.08','uraian'=>'Penggunaan biaya promosi/pemasaran tidak efektif','jenis'=>3,'alternatif'=>'R.09,R.11,R.12'],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($data as &$row) {
            $exists = $this->db->table('kode_temuan')->where('kode', $row['kode'])->countAllResults();
            if (!$exists) {
                $this->db->table('kode_temuan')->insert(array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            } else {
                // Update alternatif pada baris yang sudah ada
                $this->db->table('kode_temuan')->where('kode', $row['kode'])->update([
                    'alternatif' => $row['alternatif'],
                    'updated_at' => $now,
                ]);
            }
        }

        echo "KodetemuanSeeder: " . count($data) . " kode temuan (PermenpanRB 41/2011) + alternatif rekomendasi siap.\n";
    }
}
