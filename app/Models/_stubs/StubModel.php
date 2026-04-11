<?php
/**
 * ============================================================
 * STUB MODEL — copy ke app/Models/{NamaModul}Model.php
 * Cari-ganti: StubModel → {NamaModul}Model
 *             stub       → {nama_tabel}
 * ============================================================
 */

namespace App\Models;

use CodeIgniter\Model;

class StubModel extends Model
{
    protected $table         = 'stub';          // ← ganti nama tabel
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        // ← daftarkan semua kolom yang boleh di-insert/update (JANGAN masukkan id, created_at, updated_at)
        'nama',
        'keterangan',
        'status',
        'aktif',
        'created_by',
    ];

    // ─────────────────────────────────────────────────────────
    // STATUS MAPS — untuk badge di tabel & dropdown filter
    // ─────────────────────────────────────────────────────────

    /**
     * Label status yang ditampilkan di UI.
     * Kunci = value di DB, nilai = teks tampilan.
     */
    public static array $statusLabel = [
        'aktif'    => 'Aktif',
        'nonaktif' => 'Nonaktif',
        'pending'  => 'Pending',
    ];

    /**
     * Warna badge Bootstrap untuk tiap status.
     * Nilai yang valid: primary | secondary | success | danger | warning | info
     */
    public static array $statusColor = [
        'aktif'    => 'success',
        'nonaktif' => 'secondary',
        'pending'  => 'warning',
    ];

    // ─────────────────────────────────────────────────────────
    // HELPER QUERIES
    // ─────────────────────────────────────────────────────────

    /**
     * Untuk dropdown pilihan di form lain.
     * Return: [id => nama]
     */
    public function getDropdown(): array
    {
        return array_column(
            $this->select('id, nama')->where('aktif', 1)->orderBy('nama')->findAll(),
            'nama', 'id'
        );
    }

    /**
     * Ambil semua record aktif (untuk select tim, dsb).
     */
    public function getAktif(): array
    {
        return $this->where('aktif', 1)->orderBy('nama')->findAll();
    }

    /**
     * Detail satu record dengan join tabel terkait.
     * Return null jika tidak ditemukan.
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table('stub s')
            // ->join('tabel_lain t', 't.id = s.foreign_id', 'left')
            ->select('s.*')
            ->where('s.id', $id)
            ->get()->getRowArray();
    }
}
