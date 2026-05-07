<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SPT_model — Versi perbaikan
 *
 * Kebijakan yang diimplementasikan:
 *  1. Maksimal 3 SPT reguler aktif (belum ada LHP) per Irban pada waktu bersamaan.
 *  2. Pengecualian: jenis Investigasi dan ADTT tidak dihitung dalam kuota 3.
 *  3. Upload LHP harus URUT sesuai tanggal pengajuan SPT (SPT tertua dulu).
 *
 * Perbaikan dari versi lama:
 *  - canAjukanSPT() : diperbaiki (lama hanya cek 3 SPT tertua, seharusnya hitung semua)
 *  - cekUrutanLHP() : diperbaiki (lama tidak filter per id_irban → cross-data antar irban)
 *  - cek_batas_spt(): digabung ke canAjukanSPT(), tidak perlu duplikasi
 *  - Query N+1 dihapus, diganti single JOIN query yang efisien
 */
class SPT_model extends CI_Model
{
    /**
     * Kata kunci pada nm_kegiatan yang dikecualikan dari batas 3 SPT.
     * Bandingkan dengan LOWER() → huruf kecil semua.
     */
    private $jenis_pengecualian = ['investigasi', 'adtt'];

    // ────────────────────────────────────────────────────────────────────────
    // BAGIAN 1: PENGECEKAN KEBIJAKAN
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Cek apakah Irban boleh mengajukan SPT baru.
     *
     * Logika:
     *  - Hitung semua SPT milik Irban yang: (a) bukan jenis pengecualian,
     *    dan (b) belum memiliki LHP.
     *  - Jika jumlahnya >= 3 → tolak.
     *
     * @param  int   $id_irban
     * @return array ['boleh'=>bool, 'pesan'=>string, 'slot_terpakai'=>int, 'slot_maks'=>int]
     */
    public function canAjukanSPT($id_irban)
    {
        $slot_maks = 3;

        // Satu query: SPT reguler (bukan investigasi/ADTT) yang belum ada LHP
        $sql = "
            SELECT COUNT(s.id_st) AS jumlah
            FROM   tbl_st s
            JOIN   mst_kegiatan k ON s.jenis = k.id_mst_kegiatan
            LEFT   JOIN laporan_hasil l ON s.id_st = l.id_st
            WHERE  s.id_mst_unit_itda = ?
              AND  l.id_st IS NULL
              AND  LOWER(k.nm_kegiatan) NOT LIKE '%investigasi%'
              AND  LOWER(k.nm_kegiatan) NOT LIKE '%adtt%'
        ";
        $row = $this->db->query($sql, [$id_irban])->row();
        $slot_terpakai = (int)($row->jumlah ?? 0);

        if ($slot_terpakai >= $slot_maks) {
            return [
                'boleh'         => false,
                'slot_terpakai' => $slot_terpakai,
                'slot_maks'     => $slot_maks,
                'pesan'         => "Masih ada {$slot_terpakai} SPT yang belum menghasilkan LHP. "
                                 . "Pengajuan SPT reguler dibatasi maksimal {$slot_maks}. "
                                 . "Harap selesaikan LHP terlebih dahulu.",
            ];
        }

        $sisa = $slot_maks - $slot_terpakai;
        return [
            'boleh'         => true,
            'slot_terpakai' => $slot_terpakai,
            'slot_maks'     => $slot_maks,
            'pesan'         => "Sisa slot: {$sisa} dari {$slot_maks}.",
        ];
    }

    /**
     * Alias canAjukanSPT() — dipakai di view data.php agar kompatibel.
     */
    public function cek_batas_spt($id_irban)
    {
        return $this->canAjukanSPT($id_irban);
    }

    /**
     * Validasi urutan upload LHP.
     *
     * Aturan: LHP harus diupload sesuai urutan pengajuan SPT (tgl_st ASC).
     * SPT tertua yang belum ada LHP-nya WAJIB diupload duluan.
     *
     * PERBAIKAN dari versi lama:
     *  - Filter laporan_hasil berdasarkan SPT milik id_irban (bukan semua irban)
     *  - Lebih aman karena tidak cross-data
     *
     * @param  int  $id_st    ID SPT yang akan diupload LHP-nya
     * @param  int  $id_irban
     * @return bool true = boleh upload, false = harus upload yang lebih lama dulu
     */
    public function cekUrutanLHP($id_st, $id_irban)
    {
        // Cari SPT tertua milik irban ini yang BELUM ada LHP-nya
        $sql = "
            SELECT s.id_st
            FROM   tbl_st s
            LEFT   JOIN laporan_hasil l ON s.id_st = l.id_st
            WHERE  s.id_mst_unit_itda = ?
              AND  l.id_st IS NULL
            ORDER  BY s.tgl_st ASC, s.id_st ASC
            LIMIT  1
        ";
        $tertua = $this->db->query($sql, [$id_irban])->row();

        // Jika semua sudah ada LHP → boleh upload siapa pun
        if (!$tertua) return true;

        // $id_st yang diupload harus merupakan SPT tertua yang belum ada LHP-nya
        return (int)$tertua->id_st === (int)$id_st;
    }

    /**
     * Ambil informasi slot untuk ditampilkan di view (badge / progress bar).
     *
     * @param  int  $id_irban
     * @return array ['terpakai'=>int, 'maks'=>int, 'sisa'=>int, 'persen'=>int, 'list_antrian'=>array]
     */
    public function getInfoSlot($id_irban)
    {
        $slot_maks = 3;

        // SPT reguler yang belum ada LHP, urut dari tertua
        $sql = "
            SELECT s.id_st, s.no_st, s.tgl_st, k.nm_kegiatan
            FROM   tbl_st s
            JOIN   mst_kegiatan k ON s.jenis = k.id_mst_kegiatan
            LEFT   JOIN laporan_hasil l ON s.id_st = l.id_st
            WHERE  s.id_mst_unit_itda = ?
              AND  l.id_st IS NULL
              AND  LOWER(k.nm_kegiatan) NOT LIKE '%investigasi%'
              AND  LOWER(k.nm_kegiatan) NOT LIKE '%adtt%'
            ORDER  BY s.tgl_st ASC
        ";
        $list = $this->db->query($sql, [$id_irban])->result_array();
        $terpakai = count($list);

        return [
            'terpakai'      => $terpakai,
            'maks'          => $slot_maks,
            'sisa'          => max(0, $slot_maks - $terpakai),
            'persen'        => min(100, round($terpakai / $slot_maks * 100)),
            'list_antrian'  => $list,  // SPT yang harus diselesaikan LHP-nya
        ];
    }

    // ────────────────────────────────────────────────────────────────────────
    // BAGIAN 2: QUERY DATA (tidak berubah dari versi lama)
    // ────────────────────────────────────────────────────────────────────────

    public function surat_tugas()
    {
        $sess = $this->session->userdata('login_session')['id_mst_unit_itda'];
        $whereClause = is_admin() ? "" : "WHERE a.id_mst_unit_itda = $sess";

        $q = "
            SELECT a.*, b.nm_mst_unit AS unit, c.nm_satker AS Tujuan,
                   d.nm_kegiatan AS nm_kegiatan,
                   CONCAT(start,' s/d ',end) AS Periode_ST,
                   jlh_st AS Total_ST, e.nama AS TTD,
                   DATE_ADD(end, INTERVAL 7 DAY)  AS NPH,
                   DATE_ADD(end, INTERVAL 15 DAY) AS LHP,
                   IF(LOWER(d.nm_kegiatan) LIKE '%investigasi%'
                      OR LOWER(d.nm_kegiatan) LIKE '%adtt%', 1, 0) AS is_pengecualian
            FROM   tbl_st a
            LEFT   JOIN mst_unit_itda  b ON a.id_mst_unit_itda = b.id_mst_unit_itda
            LEFT   JOIN mst_satker     c ON a.tujuan = c.id_mst_satker
            LEFT   JOIN mst_kegiatan   d ON a.jenis  = d.id_mst_kegiatan
            LEFT   JOIN mst_pegawai    e ON a.ttd    = e.id_mst_pegawai
            $whereClause
            ORDER  BY a.tgl_st ASC
        ";
        return $this->db->query($q)->result_array();
    }

    public function det_st_row($getId)
    {
        $this->db->select('*');
        $this->db->join('tbl_st b',         'a.id_st = b.id_st',                       'LEFT');
        $this->db->join('mst_unit_itda c',  'b.id_mst_unit_itda = c.id_mst_unit_itda', 'LEFT');
        $this->db->join('mst_satker d',     'b.tujuan = d.id_mst_satker',              'LEFT');
        $this->db->join('mst_kegiatan e',   'b.jenis = e.id_mst_kegiatan',             'LEFT');
        $this->db->join('mst_pegawai f',    'a.id_mst_pegawai = f.id_mst_pegawai',     'LEFT');
        $this->db->join('mst_jabatan_st g', 'a.id_mst_jabatan_st = g.id_mst_jabatan_st', 'LEFT');
        return $this->db->get_where('tbl_st_pegawai a', ['a.id_st' => $getId])->row_array();
    }

    public function det_st($getId)
    {
        $this->db->select('*, f.*');
        $this->db->join('tbl_st b',         'a.id_st = b.id_st',                       'LEFT');
        $this->db->join('mst_unit_itda c',  'b.id_mst_unit_itda = c.id_mst_unit_itda', 'LEFT');
        $this->db->join('mst_satker d',     'b.tujuan = d.id_mst_satker',              'LEFT');
        $this->db->join('mst_kegiatan e',   'b.jenis = e.id_mst_kegiatan',             'LEFT');
        $this->db->join('mst_pegawai f',    'a.id_mst_pegawai = f.id_mst_pegawai',     'LEFT');
        $this->db->join('mst_jabatan_st g', 'a.id_mst_jabatan_st = g.id_mst_jabatan_st', 'LEFT');
        return $this->db->get_where('tbl_st_pegawai a', ['a.id_st' => $getId])->result_array();
    }

    public function getPegawai()
    {
        return $this->db->query("SELECT * FROM mst_pegawai ORDER BY level ASC")->result_array();
    }

    public function penandatangan()
    {
        return $this->db->get_where('mst_pegawai', [
            'jabatan_pegawai' => 'Inspektur Daerah',
            'status'          => 1
        ])->result_array();
    }

    public function cekPegawai($getId)
    {
        return $this->db->get_where('mst_pegawai', ['nip' => $getId])->row_array();
    }

    public function getUser($keyword)
    {
        $this->db->like('username', $keyword);
        return $this->db->get('user')->result_array();
    }

    public function cetakSPT($getId)
    {
        $this->db->select('*');
        $this->db->join('tbl_st b',         'a.id_st = b.id_st',                       'LEFT');
        $this->db->join('mst_unit_itda c',  'b.id_mst_unit_itda = c.id_mst_unit_itda', 'LEFT');
        $this->db->join('mst_satker d',     'b.tujuan = d.id_mst_satker',              'LEFT');
        $this->db->join('mst_kegiatan e',   'b.jenis = e.id_mst_kegiatan',             'LEFT');
        $this->db->join('mst_pegawai f',    'a.id_mst_pegawai = f.id_mst_pegawai',     'LEFT');
        $this->db->join('mst_jabatan_st g', 'a.id_mst_jabatan_st = g.id_mst_jabatan_st', 'LEFT');
        return $this->db->get_where('tbl_st_pegawai a', ['a.id_st' => $getId])->result_array();
    }

    public function sptFile($getId)
    {
        return $this->db->get_where('tbl_st', ['id_st' => $getId])->row_array();
    }
}
