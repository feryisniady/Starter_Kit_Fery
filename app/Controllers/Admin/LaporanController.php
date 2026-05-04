<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * LaporanController — Rekap & Monitoring Pengawasan
 *
 * Menyediakan rekap lintas SPT/KKA/NHP/TL untuk keperluan monitoring pimpinan.
 * Semua halaman memerlukan permission spt.view.
 */
class LaporanController extends BaseController
{
    public function index()
    {
        $db    = \Config\Database::connect();
        $tahun = (int) ($this->request->getGet('tahun') ?: date('Y'));

        // === Summary cards ===
        $sptStats = $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(status = 'terbit' AND tanggal_selesai >= CURDATE()) AS berjalan,
                SUM(status = 'terbit' AND tanggal_selesai < CURDATE())  AS selesai,
                SUM(status IN ('diajukan','acc_irban','acc_evlap','acc_sekretaris')) AS pending
            FROM spt
            WHERE YEAR(COALESCE(created_at, NOW())) = ?
        ", [$tahun])->getRowArray();

        $kkaStats = $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(status_kka = 'submitted') AS pending_review,
                SUM(status_kka = 'approved')  AS approved,
                SUM(status_kka = 'rejected')  AS rejected
            FROM kka k
            JOIN spt s ON s.id = k.spt_id
            WHERE YEAR(s.created_at) = ?
        ", [$tahun])->getRowArray();

        $nhpStats = $db->query("
            SELECT
                COUNT(DISTINCT n.id) AS total_nhp,
                SUM(ni.status_tanggapan = 'pending') AS pending_tanggapan,
                SUM(ni.status_tanggapan = 'sesuai')  AS sesuai,
                SUM(ni.status_tanggapan = 'tidak_sesuai') AS tidak_sesuai
            FROM nhp n
            JOIN nhp_item ni ON ni.nhp_id = n.id
            JOIN spt s ON s.id = n.spt_id
            WHERE YEAR(s.created_at) = ?
        ", [$tahun])->getRowArray();

        $tlStats = $db->query("
            SELECT
                COUNT(*) AS total_rekomendasi,
                SUM(r.status = 'selesai')  AS selesai,
                SUM(r.status = 'proses')   AS proses,
                SUM(r.status = 'belum')    AS belum,
                SUM(r.batas_waktu IS NOT NULL AND r.batas_waktu < CURDATE() AND r.status != 'selesai') AS overdue
            FROM rekomendasi r
            JOIN temuan t ON t.id = r.temuan_id
            JOIN spt s ON s.id = t.spt_id
            WHERE YEAR(s.created_at) = ?
        ", [$tahun])->getRowArray();

        // Daftar tahun yang tersedia
        $tahunList = array_column(
            $db->query("SELECT DISTINCT YEAR(created_at) AS t FROM spt ORDER BY t DESC")->getResultArray(),
            't'
        );
        if (empty($tahunList)) $tahunList = [(int) date('Y')];

        return view('admin/laporan/index', [
            'title'     => 'Rekap & Laporan Pengawasan',
            'tahun'     => $tahun,
            'tahunList' => $tahunList,
            'sptStats'  => $sptStats,
            'kkaStats'  => $kkaStats,
            'nhpStats'  => $nhpStats,
            'tlStats'   => $tlStats,
        ]);
    }

    public function rekapSpt()
    {
        $db     = \Config\Database::connect();
        $tahun  = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $irban  = $this->request->getGet('irban') ?: '';
        $status = $this->request->getGet('status') ?: '';

        $builder = $db->table('spt s')
            ->select('s.id, s.nomor_naskah, s.jenis_spt, s.jenis_non_pkpt,
                      s.tanggal_mulai, s.tanggal_selesai, s.status, s.created_at,
                      COALESCE(i_pkpt.nama, i_spt.nama) AS irban_nama,
                      pk.kode_kegiatan, pk.tujuan_sasaran, pk.area_pengawasan,
                      DATEDIFF(COALESCE(s.tanggal_selesai, CURDATE()), COALESCE(s.tanggal_mulai, s.created_at)) AS durasi_hari,
                      (SELECT COUNT(*) FROM kka WHERE spt_id = s.id) AS jumlah_kka,
                      (SELECT COUNT(*) FROM kka WHERE spt_id = s.id AND status_kka = "approved") AS kka_approved,
                      (SELECT COUNT(*) FROM nhp WHERE spt_id = s.id) AS jumlah_nhp')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id', 'left')
            ->join('pkpt p',           'p.id = pk.pkpt_id',          'left')
            ->join('irban i_pkpt',     'i_pkpt.id = p.irban_id',     'left')
            ->join('irban i_spt',      'i_spt.id = s.irban_id',      'left')
            ->where('YEAR(s.created_at)', $tahun)
            ->orderBy('s.tanggal_mulai', 'ASC');

        if ($irban)  $builder->where('COALESCE(p.irban_id, s.irban_id)', $irban);
        if ($status) $builder->where('s.status', $status);

        $rows = $builder->get()->getResultArray();

        $irbanList  = $db->table('irban')->orderBy('nama')->get()->getResultArray();
        $tahunList  = array_column(
            $db->query("SELECT DISTINCT YEAR(created_at) AS t FROM spt ORDER BY t DESC")->getResultArray(),
            't'
        );
        if (empty($tahunList)) $tahunList = [(int) date('Y')];

        return view('admin/laporan/rekap_spt', [
            'title'      => 'Rekap SPT Tahun ' . $tahun,
            'rows'       => $rows,
            'tahun'      => $tahun,
            'tahunList'  => $tahunList,
            'irbanList'  => $irbanList,
            'filterIrban'  => $irban,
            'filterStatus' => $status,
            'statusLabel'  => \App\Models\SptModel::$statusLabel,
            'statusColor'  => \App\Models\SptModel::$statusColor,
        ]);
    }

    public function rekapTl()
    {
        $db    = \Config\Database::connect();
        $tahun = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $only  = $this->request->getGet('filter') ?: ''; // 'overdue' | ''

        $builder = $db->table('rekomendasi r')
            ->select('r.id AS rek_id, r.nomor_urut, r.isi_rekomendasi, r.batas_waktu, r.status,
                      t.judul AS judul_temuan, t.spt_id,
                      s.nomor_naskah AS spt_nomor,
                      e.nama AS entitas_nama,
                      tl.id AS tl_id, tl.status_verifikasi, tl.created_at AS tl_tgl,
                      CASE WHEN r.batas_waktu IS NOT NULL AND r.batas_waktu < CURDATE() AND r.status != "selesai"
                           THEN 1 ELSE 0 END AS is_overdue,
                      DATEDIFF(CURDATE(), r.batas_waktu) AS hari_lewat')
            ->join('temuan t',          't.id = r.temuan_id')
            ->join('spt s',             's.id = t.spt_id')
            ->join('pkpt_kegiatan pk',  'pk.id = s.pkpt_kegiatan_id', 'left')
            ->join('pkpt_entitas pe',   'pe.pkpt_kegiatan_id = pk.id', 'left')
            ->join('entitas e',         'e.id = pe.entitas_id', 'left')
            ->join('tindak_lanjut tl',
                   'tl.rekomendasi_id = r.id AND tl.id = (SELECT MAX(id) FROM tindak_lanjut WHERE rekomendasi_id = r.id)',
                   'left')
            ->where('YEAR(s.created_at)', $tahun)
            ->orderBy('is_overdue', 'DESC')
            ->orderBy('r.batas_waktu', 'ASC');

        if ($only === 'overdue') {
            $builder->where('r.batas_waktu IS NOT NULL')
                    ->where('r.batas_waktu <', date('Y-m-d'))
                    ->where('r.status !=', 'selesai');
        }

        $rows = $builder->get()->getResultArray();

        $tahunList = array_column(
            $db->query("SELECT DISTINCT YEAR(created_at) AS t FROM spt ORDER BY t DESC")->getResultArray(),
            't'
        );
        if (empty($tahunList)) $tahunList = [(int) date('Y')];

        $countOverdue  = count(array_filter($rows, fn($r) => (int)$r['is_overdue']));
        $countSelesai  = count(array_filter($rows, fn($r) => $r['status'] === 'selesai'));

        return view('admin/laporan/rekap_tl', [
            'title'         => 'Rekap Tindak Lanjut Tahun ' . $tahun,
            'rows'          => $rows,
            'tahun'         => $tahun,
            'tahunList'     => $tahunList,
            'filterOnly'    => $only,
            'countOverdue'  => $countOverdue,
            'countSelesai'  => $countSelesai,
            'statusLabel'   => \App\Models\RekomendasiModel::$statusLabel,
            'statusColor'   => \App\Models\RekomendasiModel::$statusColor,
            'verifikasiLabel' => \App\Models\TindakLanjutModel::$verifikasiLabel,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Export CSV
    // ──────────────────────────────────────────────────────────────────────

    private function csvRow(array $fields): string
    {
        return implode(';', array_map(
            fn($f) => '"' . str_replace('"', '""', strip_tags((string)$f)) . '"',
            $fields
        )) . "\r\n";
    }

    private function csvResponse(string $filename, string $body): \CodeIgniter\HTTP\Response
    {
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody("\xEF\xBB\xBF" . $body);
    }

    public function exportSptCsv()
    {
        $db     = \Config\Database::connect();
        $tahun  = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $irban  = $this->request->getGet('irban') ?: '';
        $status = $this->request->getGet('status') ?: '';

        $builder = $db->table('spt s')
            ->select('s.nomor_naskah, COALESCE(i_pkpt.nama, i_spt.nama) AS irban_nama,
                      pk.kode_kegiatan, pk.tujuan_sasaran, pk.area_pengawasan,
                      s.jenis_non_pkpt, s.tanggal_mulai, s.tanggal_selesai, s.status,
                      DATEDIFF(COALESCE(s.tanggal_selesai, CURDATE()), COALESCE(s.tanggal_mulai, s.created_at)) AS durasi_hari,
                      (SELECT COUNT(*) FROM kka WHERE spt_id = s.id) AS jumlah_kka,
                      (SELECT COUNT(*) FROM kka WHERE spt_id = s.id AND status_kka = "approved") AS kka_approved,
                      (SELECT COUNT(*) FROM nhp WHERE spt_id = s.id) AS jumlah_nhp')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id', 'left')
            ->join('pkpt p',           'p.id = pk.pkpt_id',          'left')
            ->join('irban i_pkpt',     'i_pkpt.id = p.irban_id',     'left')
            ->join('irban i_spt',      'i_spt.id = s.irban_id',      'left')
            ->where('YEAR(s.created_at)', $tahun)
            ->orderBy('s.tanggal_mulai', 'ASC');

        if ($irban)  $builder->where('COALESCE(p.irban_id, s.irban_id)', $irban);
        if ($status) $builder->where('s.status', $status);

        $rows = $builder->get()->getResultArray();
        $statusLabel = \App\Models\SptModel::$statusLabel;

        $csv = $this->csvRow(['No','Nomor SPT','Bidang/Irban','Kode Kegiatan','Uraian Kegiatan',
                              'Tanggal Mulai','Tanggal Selesai','Durasi (Hari)',
                              'KKA Total','KKA Approved','Jumlah NHP','Status']);
        foreach ($rows as $i => $r) {
            $uraian = $r['tujuan_sasaran'] ?: $r['area_pengawasan'] ?: $r['jenis_non_pkpt'] ?: '-';
            $csv .= $this->csvRow([
                $i + 1,
                $r['nomor_naskah']  ?: '-',
                $r['irban_nama']    ?: '-',
                $r['kode_kegiatan'] ?: '-',
                $uraian,
                $r['tanggal_mulai']   ? date('d/m/Y', strtotime($r['tanggal_mulai']))   : '-',
                $r['tanggal_selesai'] ? date('d/m/Y', strtotime($r['tanggal_selesai'])) : '-',
                $r['durasi_hari']   ?: '-',
                $r['jumlah_kka'],
                $r['kka_approved'],
                $r['jumlah_nhp'],
                $statusLabel[$r['status']] ?? $r['status'],
            ]);
        }

        return $this->csvResponse('Rekap_SPT_' . $tahun . '.csv', $csv);
    }

    public function exportTlCsv()
    {
        $db    = \Config\Database::connect();
        $tahun = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $only  = $this->request->getGet('filter') ?: '';

        $builder = $db->table('rekomendasi r')
            ->select('r.nomor_urut, r.isi_rekomendasi, r.batas_waktu, r.status AS status_tl,
                      t.judul AS judul_temuan,
                      s.nomor_naskah AS spt_nomor,
                      e.nama AS entitas_nama,
                      tl.status_verifikasi,
                      CASE WHEN r.batas_waktu IS NOT NULL AND r.batas_waktu < CURDATE() AND r.status != "selesai"
                           THEN 1 ELSE 0 END AS is_overdue,
                      DATEDIFF(CURDATE(), r.batas_waktu) AS hari_lewat')
            ->join('temuan t',         't.id = r.temuan_id')
            ->join('spt s',            's.id = t.spt_id')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id', 'left')
            ->join('pkpt_entitas pe',  'pe.pkpt_kegiatan_id = pk.id', 'left')
            ->join('entitas e',        'e.id = pe.entitas_id', 'left')
            ->join('tindak_lanjut tl',
                   'tl.rekomendasi_id = r.id AND tl.id = (SELECT MAX(id) FROM tindak_lanjut WHERE rekomendasi_id = r.id)',
                   'left')
            ->where('YEAR(s.created_at)', $tahun)
            ->orderBy('is_overdue', 'DESC')
            ->orderBy('r.batas_waktu', 'ASC');

        if ($only === 'overdue') {
            $builder->where('r.batas_waktu IS NOT NULL')
                    ->where('r.batas_waktu <', date('Y-m-d'))
                    ->where('r.status !=', 'selesai');
        }

        $rows = $builder->get()->getResultArray();
        $statusLabel     = \App\Models\RekomendasiModel::$statusLabel;
        $verifikasiLabel = \App\Models\TindakLanjutModel::$verifikasiLabel;

        $csv = $this->csvRow(['No','No. SPT','OPD/Entitas','Judul Temuan','Rekomendasi',
                              'Batas Waktu','Status TL','Verifikasi Auditor','Overdue (Hari)']);
        foreach ($rows as $i => $r) {
            $csv .= $this->csvRow([
                $i + 1,
                $r['spt_nomor']       ?: '-',
                $r['entitas_nama']    ?: '-',
                $r['judul_temuan']    ?: '-',
                $r['isi_rekomendasi'] ?: '-',
                $r['batas_waktu']     ? date('d/m/Y', strtotime($r['batas_waktu'])) : '-',
                $statusLabel[$r['status_tl']] ?? $r['status_tl'],
                $verifikasiLabel[$r['status_verifikasi'] ?? ''] ?? '-',
                (int)$r['is_overdue'] ? $r['hari_lewat'] : '-',
            ]);
        }

        return $this->csvResponse('Rekap_TL_' . $tahun . '.csv', $csv);
    }

    public function exportIkhtisarCsv()
    {
        $db      = \Config\Database::connect();
        $tahun   = (int) ($this->request->getGet('tahun')    ?: date('Y'));
        $irbanId = $this->request->getGet('irban')    ?: '';
        $jenis   = $this->request->getGet('jenis')    ?: '';
        $statusTl= $this->request->getGet('status_tl') ?: '';

        $rows = $db->query("
            SELECT
                t.nomor_temuan, t.judul AS judul_temuan, t.kondisi, t.nilai_temuan,
                r.nomor_urut AS rek_nomor, r.isi_rekomendasi, r.batas_waktu,
                r.status AS status_tl, r.nilai_rekomendasi,
                s.nomor_naskah AS spt_nomor, s.jenis_spt, s.jenis_non_pkpt,
                COALESCE(i_pkpt.nama, i_spt.nama) AS irban_nama,
                COALESCE(i_pkpt.id,   i_spt.id)   AS irban_id,
                e.nama AS entitas_nama,
                kt.kode AS kode_temuan
            FROM temuan t
            JOIN spt s ON s.id = t.spt_id
            LEFT JOIN rekomendasi r ON r.temuan_id = t.id
            LEFT JOIN pkpt_kegiatan pk ON pk.id = s.pkpt_kegiatan_id
            LEFT JOIN pkpt p           ON p.id  = pk.pkpt_id
            LEFT JOIN irban i_pkpt     ON i_pkpt.id = p.irban_id
            LEFT JOIN irban i_spt      ON i_spt.id  = s.irban_id
            LEFT JOIN pkpt_entitas pe  ON pe.pkpt_kegiatan_id = pk.id
            LEFT JOIN entitas e        ON e.id = pe.entitas_id
            LEFT JOIN kode_temuan kt   ON kt.id = t.kode_temuan_id
            WHERE YEAR(COALESCE(s.tanggal_mulai, s.created_at)) = ?
              AND t.status_temuan = 'buka'
            ORDER BY irban_nama, s.nomor_naskah, t.nomor_temuan, r.nomor_urut
        ", [$tahun])->getResultArray();

        if ($irbanId) $rows = array_filter($rows, fn($r) => (string)$r['irban_id'] === $irbanId);
        if ($jenis)   $rows = array_filter($rows, fn($r) => ($r['jenis_spt'] ?? 'pkpt') === $jenis);
        if ($statusTl) $rows = array_filter($rows, fn($r) => ($r['status_tl'] ?? 'belum') === $statusTl);
        $rows = array_values($rows);

        $tlLabel = ['belum' => 'Belum', 'proses' => 'Dalam Proses', 'selesai' => 'Selesai'];

        $csv = $this->csvRow(['No','Bidang/Irban','No. SPT','OPD/Entitas','Jenis Audit',
                              'No. Temuan','Judul Temuan','Kondisi','Kode Temuan','Nilai Temuan (Rp)',
                              'No. Rek.','Rekomendasi','Batas Waktu TL','Status TL','Nilai Rek. (Rp)']);
        foreach ($rows as $i => $r) {
            $csv .= $this->csvRow([
                $i + 1,
                $r['irban_nama']      ?: '-',
                $r['spt_nomor']       ?: '-',
                $r['entitas_nama']    ?: 'Non-PKPT',
                ($r['jenis_spt'] ?? 'pkpt') === 'pkpt' ? 'PKPT' : 'Non-PKPT',
                $r['nomor_temuan']    ?: '-',
                $r['judul_temuan']    ?: '-',
                $r['kondisi']         ?: '-',
                $r['kode_temuan']     ?: '-',
                (int)$r['nilai_temuan'],
                $r['rek_nomor']       ?: '-',
                $r['isi_rekomendasi'] ?: '-',
                $r['batas_waktu']     ? date('d/m/Y', strtotime($r['batas_waktu'])) : '-',
                $tlLabel[$r['status_tl'] ?? 'belum'] ?? '-',
                (int)$r['nilai_rekomendasi'],
            ]);
        }

        return $this->csvResponse('Ikhtisar_LHP_' . $tahun . '.csv', $csv);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Ikhtisar LHP — Format Permenpan No. 42 Tahun 2011
    // ──────────────────────────────────────────────────────────────────────

    public function ikhtisarLhp()
    {
        $db      = \Config\Database::connect();
        $tahun   = (int) ($this->request->getGet('tahun')   ?: date('Y'));
        $irbanId = $this->request->getGet('irban')   ?: '';
        $entitas = $this->request->getGet('entitas') ?: '';
        $jenis   = $this->request->getGet('jenis')   ?: ''; // pkpt | non_pkpt | ''
        $statusTl= $this->request->getGet('status_tl') ?: ''; // belum | proses | selesai | ''

        // ── Query utama: temuan + rekomendasi + TL terbaru + entitas ──────
        $rows = $db->query("
            SELECT
                t.id              AS temuan_id,
                t.nomor_temuan,
                t.judul           AS judul_temuan,
                t.kondisi,
                t.kriteria,
                t.sebab,
                t.akibat,
                t.nilai_temuan,
                t.status_temuan,
                t.created_at      AS temuan_tgl,

                r.id              AS rekomendasi_id,
                r.nomor_urut      AS rek_nomor,
                r.isi_rekomendasi,
                r.batas_waktu,
                r.status          AS status_tl,
                r.nilai_rekomendasi,

                tl.status_verifikasi,
                tl.created_at     AS tl_tgl,

                s.id              AS spt_id,
                s.nomor_naskah    AS spt_nomor,
                s.jenis_spt,
                s.jenis_non_pkpt,
                s.tanggal_mulai,
                s.tanggal_selesai,

                COALESCE(i_pkpt.nama, i_spt.nama) AS irban_nama,
                COALESCE(i_pkpt.id,   i_spt.id)   AS irban_id,

                pk.kode_kegiatan,
                pk.area_pengawasan,
                pk.jenis_pengawasan,

                e.id              AS entitas_id,
                e.nama            AS entitas_nama,
                e.kode            AS entitas_kode,

                kt.kode           AS kode_temuan_kode,
                kt.uraian         AS kode_temuan_uraian,
                kt.jenis          AS kode_temuan_jenis

            FROM temuan t
            JOIN spt s          ON s.id = t.spt_id
            LEFT JOIN rekomendasi r
                ON r.temuan_id = t.id
            LEFT JOIN (
                SELECT rekomendasi_id, status_verifikasi, created_at
                FROM tindak_lanjut
                WHERE id IN (SELECT MAX(id) FROM tindak_lanjut GROUP BY rekomendasi_id)
            ) tl ON tl.rekomendasi_id = r.id
            LEFT JOIN pkpt_kegiatan pk  ON pk.id = s.pkpt_kegiatan_id
            LEFT JOIN pkpt p            ON p.id  = pk.pkpt_id
            LEFT JOIN irban i_pkpt      ON i_pkpt.id = p.irban_id
            LEFT JOIN irban i_spt       ON i_spt.id  = s.irban_id
            LEFT JOIN pkpt_entitas pe   ON pe.pkpt_kegiatan_id = pk.id
            LEFT JOIN entitas e         ON e.id = pe.entitas_id
            LEFT JOIN kode_temuan kt    ON kt.id = t.kode_temuan_id
            WHERE YEAR(COALESCE(s.tanggal_mulai, s.created_at)) = ?
              AND t.status_temuan = 'buka'
            ORDER BY irban_nama, s.nomor_naskah, t.nomor_temuan, r.nomor_urut
        ", [$tahun])->getResultArray();

        // ── Apply filter client-side (di PHP agar tidak komplekskan query) ─
        if ($irbanId) {
            $rows = array_filter($rows, fn($r) => (string)$r['irban_id'] === $irbanId);
        }
        if ($entitas) {
            $rows = array_filter($rows, fn($r) => (string)$r['entitas_id'] === $entitas
                || stripos($r['entitas_nama'] ?? '', $entitas) !== false);
        }
        if ($jenis) {
            $rows = array_filter($rows, fn($r) => ($r['jenis_spt'] ?? 'pkpt') === $jenis);
        }
        if ($statusTl) {
            $rows = array_filter($rows, fn($r) => ($r['status_tl'] ?? 'belum') === $statusTl);
        }
        $rows = array_values($rows);

        // ── Summary header ─────────────────────────────────────────────────
        $temuanIds   = array_unique(array_column($rows, 'temuan_id'));
        $rekIds      = array_filter(array_unique(array_column($rows, 'rekomendasi_id')));
        $totalNilai  = array_sum(array_column(
            array_filter($rows, fn($r) => !empty($r['rekomendasi_id'])),
            'nilai_rekomendasi'
        ));

        $rekByStatus = ['belum' => 0, 'proses' => 0, 'selesai' => 0];
        foreach ($rows as $r) {
            if (empty($r['rekomendasi_id'])) continue;
            $st = $r['status_tl'] ?? 'belum';
            $rekByStatus[$st] = ($rekByStatus[$st] ?? 0) + 1;
        }
        $totalRek = count($rekIds);

        // ── Rekap per entitas ──────────────────────────────────────────────
        $rekapEntitas = [];
        foreach ($rows as $r) {
            $key = $r['entitas_nama'] ?: ($r['spt_nomor'] . ' (Non-PKPT)');
            if (!isset($rekapEntitas[$key])) {
                $rekapEntitas[$key] = [
                    'nama'     => $key,
                    'total'    => 0, 'belum' => 0, 'proses' => 0, 'selesai' => 0,
                    'nilai'    => 0,
                ];
            }
            if (empty($r['rekomendasi_id'])) continue;
            $rekapEntitas[$key]['total']++;
            $rekapEntitas[$key][$r['status_tl'] ?? 'belum']++;
            $rekapEntitas[$key]['nilai'] += (int)($r['nilai_rekomendasi'] ?? 0);
        }

        // ── Dropdown filter data ───────────────────────────────────────────
        $irbanList   = $db->table('irban')->orderBy('nama')->get()->getResultArray();
        $entitasList = $db->table('entitas')->where('aktif', 1)->orderBy('nama')->get()->getResultArray();
        $tahunList   = array_column(
            $db->query("SELECT DISTINCT YEAR(COALESCE(tanggal_mulai, created_at)) AS t FROM spt ORDER BY t DESC")->getResultArray(),
            't'
        );
        if (empty($tahunList)) $tahunList = [(int) date('Y')];

        return view('admin/laporan/ikhtisar_lhp', [
            'title'         => 'Ikhtisar Laporan Hasil Pengawasan — Tahun ' . $tahun,
            'tahun'         => $tahun,
            'rows'          => $rows,
            'tahunList'     => $tahunList,
            'irbanList'     => $irbanList,
            'entitasList'   => $entitasList,
            'filterIrban'   => $irbanId,
            'filterEntitas' => $entitas,
            'filterJenis'   => $jenis,
            'filterStatusTl'=> $statusTl,
            'jumlahTemuan'  => count($temuanIds),
            'jumlahRek'     => $totalRek,
            'totalNilai'    => $totalNilai,
            'rekByStatus'   => $rekByStatus,
            'rekapEntitas'  => array_values($rekapEntitas),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Matriks TL — Ringkasan Eksekutif per Irban × Entitas × Tahun LHP
    // ──────────────────────────────────────────────────────────────────────

    private function matriksQuery(int $tahun, string $irbanId): array
    {
        $db = \Config\Database::connect();

        $whereTahun = $tahun   ? "AND YEAR(COALESCE(s.tanggal_mulai, s.created_at)) = $tahun" : '';
        $whereIrban = $irbanId ? "AND COALESCE(i_pkpt.id, i_spt.id) = " . (int)$irbanId      : '';

        return $db->query("
            SELECT
                COALESCE(i_pkpt.id,   i_spt.id,   0)              AS irban_id,
                COALESCE(i_pkpt.nama, i_spt.nama, 'Tanpa Bidang') AS irban_nama,
                COALESCE(e.id,   0)                                AS entitas_id,
                COALESCE(e.nama, '—')                              AS entitas_nama,
                YEAR(COALESCE(s.tanggal_mulai, s.created_at))     AS tahun_lhp,
                COUNT(ra.temuan_id)     AS total_temuan,
                SUM(ra.total_rek)       AS total_rekomendasi,
                SUM(ra.total_nilai_rek) AS total_nilai_rek,
                SUM(ra.jml_selesai)     AS jml_selesai,
                SUM(ra.jml_proses)      AS jml_proses,
                SUM(ra.jml_belum)       AS jml_belum,
                SUM(ra.nilai_selesai)   AS nilai_selesai,
                SUM(ra.nilai_sisa)      AS nilai_sisa
            FROM (
                SELECT
                    t.id   AS temuan_id,
                    t.spt_id,
                    COUNT(r.id)                                                                                AS total_rek,
                    COALESCE(SUM(r.nilai_rekomendasi), 0)                                                     AS total_nilai_rek,
                    COALESCE(SUM(r.status = 'selesai'), 0)                                                    AS jml_selesai,
                    COALESCE(SUM(r.status = 'proses'),  0)                                                    AS jml_proses,
                    COALESCE(SUM(r.status = 'belum'),   0)                                                    AS jml_belum,
                    COALESCE(SUM(CASE WHEN r.status = 'selesai' THEN r.nilai_rekomendasi ELSE 0 END), 0)      AS nilai_selesai,
                    COALESCE(SUM(CASE WHEN r.status != 'selesai' THEN r.nilai_rekomendasi ELSE 0 END), 0)     AS nilai_sisa
                FROM temuan t
                LEFT JOIN rekomendasi r ON r.temuan_id = t.id
                WHERE t.status_temuan = 'buka'
                GROUP BY t.id, t.spt_id
            ) ra
            JOIN spt s ON s.id = ra.spt_id
            LEFT JOIN pkpt_kegiatan pk ON pk.id = s.pkpt_kegiatan_id
            LEFT JOIN pkpt p           ON p.id  = pk.pkpt_id
            LEFT JOIN irban i_pkpt     ON i_pkpt.id = p.irban_id
            LEFT JOIN irban i_spt      ON i_spt.id  = s.irban_id
            LEFT JOIN (
                SELECT pkpt_kegiatan_id, MIN(entitas_id) AS entitas_id
                FROM pkpt_entitas GROUP BY pkpt_kegiatan_id
            ) pe ON pe.pkpt_kegiatan_id = pk.id
            LEFT JOIN entitas e ON e.id = pe.entitas_id
            WHERE 1=1 $whereTahun $whereIrban
            GROUP BY
                COALESCE(i_pkpt.id, i_spt.id, 0),
                COALESCE(i_pkpt.nama, i_spt.nama, 'Tanpa Bidang'),
                COALESCE(e.id, 0),
                COALESCE(e.nama, '—'),
                YEAR(COALESCE(s.tanggal_mulai, s.created_at))
            ORDER BY irban_nama, entitas_nama, tahun_lhp
        ")->getResultArray();
    }

    private function groupMatriks(array $rows): array
    {
        $keys  = ['total_temuan','total_rekomendasi','total_nilai_rek','jml_selesai','jml_proses','jml_belum','nilai_selesai','nilai_sisa'];
        $zero  = array_fill_keys($keys, 0);
        $grouped    = [];
        $grandTotal = $zero;

        foreach ($rows as $r) {
            $ik = $r['irban_id'] . '|' . $r['irban_nama'];
            if (!isset($grouped[$ik])) {
                $grouped[$ik] = ['id' => $r['irban_id'], 'nama' => $r['irban_nama'], 'rows' => [], 'sub' => $zero];
            }
            $grouped[$ik]['rows'][] = $r;
            foreach ($keys as $k) {
                $grouped[$ik]['sub'][$k] += (float)($r[$k] ?? 0);
                $grandTotal[$k]          += (float)($r[$k] ?? 0);
            }
        }
        return ['grouped' => $grouped, 'grandTotal' => $grandTotal];
    }

    public function matriksTl()
    {
        $db      = \Config\Database::connect();
        $tahun   = (int) ($this->request->getGet('tahun')  ?: 0);
        $irbanId = $this->request->getGet('irban') ?: '';

        $rows = $this->matriksQuery($tahun, $irbanId);
        extract($this->groupMatriks($rows));

        $irbanList = $db->table('irban')->orderBy('nama')->get()->getResultArray();
        $tahunList = array_column(
            $db->query("SELECT DISTINCT YEAR(COALESCE(tanggal_mulai, created_at)) AS t FROM spt ORDER BY t DESC")->getResultArray(),
            't'
        );

        return view('admin/laporan/matriks_tl', [
            'title'       => 'Matriks Tindak Lanjut' . ($tahun ? ' — Tahun ' . $tahun : ' — Semua Tahun'),
            'tahun'       => $tahun,
            'tahunList'   => $tahunList,
            'irbanList'   => $irbanList,
            'filterIrban' => $irbanId,
            'grouped'     => $grouped,
            'grandTotal'  => $grandTotal,
        ]);
    }

    public function detailBelumTl()
    {
        $entitasId = (int)($this->request->getGet('entitas_id') ?? 0);
        $tahunLhp  = (int)($this->request->getGet('tahun_lhp')  ?? 0);
        $irbanId   = (int)($this->request->getGet('irban_id')   ?? 0);
        $status    = $this->request->getGet('status') ?: 'belum';

        $statusFilter = match($status) {
            'proses' => "r.status = 'proses'",
            default  => "r.status = 'belum'",
        };

        $db = \Config\Database::connect();

        $entitasFilter = "AND COALESCE(e.id, 0) = $entitasId";
        $irbanFilter   = $irbanId  ? "AND COALESCE(i_pkpt.id, i_spt.id, 0) = $irbanId" : '';
        $tahunFilter   = $tahunLhp ? "AND YEAR(COALESCE(s.tanggal_mulai, s.created_at)) = $tahunLhp" : '';

        $rows = $db->query("
            SELECT
                t.nomor_temuan, t.judul AS judul_temuan,
                r.id AS rek_id, r.nomor_urut, r.isi_rekomendasi,
                r.batas_waktu, r.status, r.nilai_rekomendasi,
                s.id AS spt_id, s.nomor_naskah AS spt_nomor,
                COALESCE(e.nama, '—') AS entitas_nama,
                DATEDIFF(CURDATE(), r.batas_waktu) AS hari_lewat
            FROM rekomendasi r
            JOIN temuan t ON t.id = r.temuan_id
            JOIN spt s    ON s.id = t.spt_id
            LEFT JOIN pkpt_kegiatan pk ON pk.id = s.pkpt_kegiatan_id
            LEFT JOIN pkpt p           ON p.id  = pk.pkpt_id
            LEFT JOIN irban i_pkpt     ON i_pkpt.id = p.irban_id
            LEFT JOIN irban i_spt      ON i_spt.id  = s.irban_id
            LEFT JOIN (
                SELECT pkpt_kegiatan_id, MIN(entitas_id) AS entitas_id
                FROM pkpt_entitas GROUP BY pkpt_kegiatan_id
            ) pe ON pe.pkpt_kegiatan_id = pk.id
            LEFT JOIN entitas e ON e.id = pe.entitas_id
            WHERE $statusFilter
              AND t.status_temuan = 'buka'
              $entitasFilter $irbanFilter $tahunFilter
            ORDER BY r.batas_waktu ASC, t.nomor_temuan
        ")->getResultArray();

        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode(array_values($rows)));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Rekap Temuan — Daftar semua temuan lintas SPT untuk monitoring
    // ──────────────────────────────────────────────────────────────────────

    public function rekapTemuan()
    {
        $db      = \Config\Database::connect();
        $tahun   = (int) ($this->request->getGet('tahun')   ?: date('Y'));
        $irbanId = $this->request->getGet('irban')   ?: '';
        $status  = $this->request->getGet('status')  ?: '';   // buka | tutup | ''
        $jenis   = $this->request->getGet('jenis')   ?: '';   // kode_temuan.jenis: finansial | non_finansial | ''

        $rows = $db->query("
            SELECT
                t.id             AS temuan_id,
                t.nomor_temuan,
                t.judul,
                t.nilai_temuan,
                t.status_temuan,
                t.created_at     AS temuan_tgl,

                kt.kode          AS kode_temuan,
                kt.jenis         AS kode_jenis,

                s.id             AS spt_id,
                s.nomor_naskah   AS spt_nomor,
                COALESCE(i_pkpt.id,   i_spt.id)   AS irban_id,
                COALESCE(i_pkpt.nama, i_spt.nama) AS irban_nama,

                e.nama           AS entitas_nama,

                (SELECT COUNT(*) FROM rekomendasi WHERE temuan_id = t.id)                                       AS jml_rek,
                (SELECT COUNT(*) FROM rekomendasi WHERE temuan_id = t.id AND status = 'selesai')                AS rek_selesai,
                (SELECT COUNT(*) FROM rekomendasi WHERE temuan_id = t.id AND status = 'proses')                 AS rek_proses,
                (SELECT COUNT(*) FROM rekomendasi WHERE temuan_id = t.id AND status = 'belum')                  AS rek_belum,
                (SELECT COALESCE(SUM(nilai_rekomendasi),0) FROM rekomendasi WHERE temuan_id = t.id)             AS total_nilai_rek

            FROM temuan t
            JOIN spt s              ON s.id = t.spt_id
            LEFT JOIN kode_temuan kt ON kt.id = t.kode_temuan_id
            LEFT JOIN pkpt_kegiatan pk ON pk.id = s.pkpt_kegiatan_id
            LEFT JOIN pkpt p           ON p.id  = pk.pkpt_id
            LEFT JOIN irban i_pkpt     ON i_pkpt.id = p.irban_id
            LEFT JOIN irban i_spt      ON i_spt.id  = s.irban_id
            LEFT JOIN pkpt_entitas pe  ON pe.pkpt_kegiatan_id = pk.id AND pe.id = (
                SELECT MIN(id) FROM pkpt_entitas WHERE pkpt_kegiatan_id = pk.id
            )
            LEFT JOIN entitas e        ON e.id = pe.entitas_id
            WHERE YEAR(COALESCE(s.tanggal_mulai, s.created_at)) = ?
            ORDER BY irban_nama, s.nomor_naskah, t.nomor_temuan
        ", [$tahun])->getResultArray();

        // Filter PHP
        if ($irbanId) $rows = array_values(array_filter($rows, fn($r) => (string)($r['irban_id'] ?? '') === $irbanId));
        if ($status)  $rows = array_values(array_filter($rows, fn($r) => $r['status_temuan'] === $status));
        if ($jenis)   $rows = array_values(array_filter($rows, fn($r) => ($r['kode_jenis'] ?? '') === $jenis));

        // Summary
        $totalBuka   = count(array_filter($rows, fn($r) => $r['status_temuan'] === 'buka'));
        $totalTutup  = count($rows) - $totalBuka;
        $totalNilai  = array_sum(array_column($rows, 'nilai_temuan'));
        $totalNilaiRek = array_sum(array_column($rows, 'total_nilai_rek'));

        $irbanList = $db->table('irban')->orderBy('nama')->get()->getResultArray();
        $tahunList = array_column(
            $db->query("SELECT DISTINCT YEAR(COALESCE(tanggal_mulai, created_at)) AS t FROM spt ORDER BY t DESC")->getResultArray(),
            't'
        );
        if (empty($tahunList)) $tahunList = [(int) date('Y')];

        return view('admin/laporan/rekap_temuan', [
            'title'        => 'Rekap Temuan Audit — Tahun ' . $tahun,
            'tahun'        => $tahun,
            'tahunList'    => $tahunList,
            'irbanList'    => $irbanList,
            'rows'         => $rows,
            'filterIrban'  => $irbanId,
            'filterStatus' => $status,
            'filterJenis'  => $jenis,
            'totalBuka'    => $totalBuka,
            'totalTutup'   => $totalTutup,
            'totalNilai'   => $totalNilai,
            'totalNilaiRek'=> $totalNilaiRek,
        ]);
    }

    public function exportTemuanCsv()
    {
        $db      = \Config\Database::connect();
        $tahun   = (int) ($this->request->getGet('tahun')  ?: date('Y'));
        $irbanId = $this->request->getGet('irban')  ?: '';
        $status  = $this->request->getGet('status') ?: '';
        $jenis   = $this->request->getGet('jenis')  ?: '';

        $rows = $db->query("
            SELECT
                t.nomor_temuan, t.judul, t.nilai_temuan, t.status_temuan,
                kt.kode AS kode_temuan, kt.jenis AS kode_jenis,
                s.nomor_naskah AS spt_nomor,
                COALESCE(i_pkpt.nama, i_spt.nama) AS irban_nama,
                e.nama AS entitas_nama,
                (SELECT COUNT(*) FROM rekomendasi WHERE temuan_id = t.id) AS jml_rek,
                (SELECT COUNT(*) FROM rekomendasi WHERE temuan_id = t.id AND status = 'selesai') AS rek_selesai,
                (SELECT COUNT(*) FROM rekomendasi WHERE temuan_id = t.id AND status = 'belum')  AS rek_belum,
                (SELECT COALESCE(SUM(nilai_rekomendasi),0) FROM rekomendasi WHERE temuan_id = t.id) AS total_nilai_rek,
                COALESCE(i_pkpt.id, i_spt.id) AS irban_id
            FROM temuan t
            JOIN spt s              ON s.id = t.spt_id
            LEFT JOIN kode_temuan kt ON kt.id = t.kode_temuan_id
            LEFT JOIN pkpt_kegiatan pk ON pk.id = s.pkpt_kegiatan_id
            LEFT JOIN pkpt p           ON p.id  = pk.pkpt_id
            LEFT JOIN irban i_pkpt     ON i_pkpt.id = p.irban_id
            LEFT JOIN irban i_spt      ON i_spt.id  = s.irban_id
            LEFT JOIN pkpt_entitas pe  ON pe.pkpt_kegiatan_id = pk.id AND pe.id = (
                SELECT MIN(id) FROM pkpt_entitas WHERE pkpt_kegiatan_id = pk.id
            )
            LEFT JOIN entitas e        ON e.id = pe.entitas_id
            WHERE YEAR(COALESCE(s.tanggal_mulai, s.created_at)) = ?
            ORDER BY irban_nama, s.nomor_naskah, t.nomor_temuan
        ", [$tahun])->getResultArray();

        if ($irbanId) $rows = array_values(array_filter($rows, fn($r) => (string)($r['irban_id'] ?? '') === $irbanId));
        if ($status)  $rows = array_values(array_filter($rows, fn($r) => $r['status_temuan'] === $status));
        if ($jenis)   $rows = array_values(array_filter($rows, fn($r) => ($r['kode_jenis'] ?? '') === $jenis));

        $csv = $this->csvRow(['No','Bidang/Irban','No. SPT','OPD/Entitas','No. Temuan','Judul Temuan',
                              'Kode Temuan','Jenis','Nilai Temuan (Rp)','Jml Rek','Selesai','Belum','Nilai Rek (Rp)','Status']);
        foreach ($rows as $i => $r) {
            $csv .= $this->csvRow([
                $i + 1,
                $r['irban_nama']    ?: '-',
                $r['spt_nomor']     ?: '-',
                $r['entitas_nama']  ?: '-',
                $r['nomor_temuan']  ?: '-',
                $r['judul']         ?: '-',
                $r['kode_temuan']   ?: '-',
                $r['kode_jenis']    ?: '-',
                (int)$r['nilai_temuan'],
                $r['jml_rek'],
                $r['rek_selesai'],
                $r['rek_belum'],
                (int)$r['total_nilai_rek'],
                $r['status_temuan'] === 'buka' ? 'Terbuka' : 'Tertutup',
            ]);
        }
        return $this->csvResponse('Rekap_Temuan_' . $tahun . '.csv', $csv);
    }

    public function exportMatriksTlCsv()
    {
        $tahun   = (int) ($this->request->getGet('tahun')  ?: 0);
        $irbanId = $this->request->getGet('irban') ?: '';

        $rows = $this->matriksQuery($tahun, $irbanId);
        extract($this->groupMatriks($rows));

        $pct = fn($n, $d) => $d > 0 ? round($n / $d * 100, 1) : 0;

        $csv  = $this->csvRow(['No','Bidang/Irban','OPD / Entitas','Tahun LHP',
                               'Total Temuan','Total Rekomendasi','Selesai (S)','Dalam Proses (DP)','Belum (B)','% TL',
                               'Nilai Rekomendasi (Rp)','Nilai TL Selesai (Rp)','Nilai Sisa TL (Rp)','% Nilai TL']);
        $no = 0;
        foreach ($grouped as $irban) {
            foreach ($irban['rows'] as $r) {
                $no++;
                $csv .= $this->csvRow([
                    $no,
                    $irban['nama'],
                    $r['entitas_nama'],
                    $r['tahun_lhp'],
                    $r['total_temuan'],
                    $r['total_rekomendasi'],
                    $r['jml_selesai'],
                    $r['jml_proses'],
                    $r['jml_belum'],
                    $pct($r['jml_selesai'], $r['total_rekomendasi']) . '%',
                    (int)$r['total_nilai_rek'],
                    (int)$r['nilai_selesai'],
                    (int)$r['nilai_sisa'],
                    $pct($r['nilai_selesai'], $r['total_nilai_rek']) . '%',
                ]);
            }
            $s = $irban['sub'];
            $csv .= $this->csvRow(['','SUBTOTAL ' . strtoupper($irban['nama']),'','',
                                   $s['total_temuan'],$s['total_rekomendasi'],$s['jml_selesai'],$s['jml_proses'],$s['jml_belum'],
                                   $pct($s['jml_selesai'],$s['total_rekomendasi']).'%',
                                   (int)$s['total_nilai_rek'],(int)$s['nilai_selesai'],(int)$s['nilai_sisa'],
                                   $pct($s['nilai_selesai'],$s['total_nilai_rek']).'%']);
        }
        $g = $grandTotal;
        $csv .= $this->csvRow(['','GRAND TOTAL','','',
                               $g['total_temuan'],$g['total_rekomendasi'],$g['jml_selesai'],$g['jml_proses'],$g['jml_belum'],
                               $pct($g['jml_selesai'],$g['total_rekomendasi']).'%',
                               (int)$g['total_nilai_rek'],(int)$g['nilai_selesai'],(int)$g['nilai_sisa'],
                               $pct($g['nilai_selesai'],$g['total_nilai_rek']).'%']);

        $suffix = $tahun ?: 'Semua';
        return $this->csvResponse('Matriks_TL_' . $suffix . '.csv', $csv);
    }
}
