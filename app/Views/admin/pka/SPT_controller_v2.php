<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');

/**
 * SPT Controller — Versi perbaikan
 *
 * Perubahan:
 *  - index()       : kirim $slot_info ke view (untuk progress bar & antrian)
 *  - add()         : pakai canAjukanSPT() yang sudah diperbaiki
 *  - upload_lhp()  : pesan error lebih informatif + tampilkan SPT tertua yang harus diselesaikan
 */
class SPT extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        cek_login();
        $this->load->model('Admin_model', 'admin');
        $this->load->model('SPT_model',   'spt');
        $this->load->library(['form_validation', 'upload']);
    }

    private function _validasi()
    {
        $this->form_validation->set_rules('id_kode_klasifikasi', 'Kode Klasifikasi', 'required|trim');
        $this->form_validation->set_rules('id_mst_kegiatan',     'Kegiatan',         'required|trim');
    }

    public function index()
    {
        $id_irban = $this->session->userdata('login_session')['id_mst_unit_itda'];

        $data = [
            'title'       => 'Surat Tugas',
            'surat_tugas' => $this->spt->surat_tugas(),
            'can_add'     => $this->spt->canAjukanSPT($id_irban),
            'slot_info'   => $this->spt->getInfoSlot($id_irban),   // ← baru: untuk progress bar
        ];
        $this->template->load('tmpl/dashboard', 'spt/data', $data);
    }

    public function add()
    {
        $id_irban  = $this->session->userdata('login_session')['id_mst_unit_itda'];
        $canAjukan = $this->spt->canAjukanSPT($id_irban);

        if (!$canAjukan['boleh']) {
            $this->session->set_flashdata('pesan',
                '<script>Swal.fire({
                    icon:  "warning",
                    title: "SPT Dibatasi",
                    html:  "' . addslashes($canAjukan['pesan']) . '"
                });</script>'
            );
            redirect('spt');
            return;
        }

        $this->_validasi();

        if ($this->form_validation->run() == false) {
            $kode_terakhir = $this->admin->getMax('tbl_st', 'no_agenda');
            $kode_tambah   = (int)substr($kode_terakhir, -3) + 1;
            $number        = str_pad($kode_tambah, 3, '0', STR_PAD_LEFT);

            $data = [
                'title'       => 'Form Surat Tugas',
                'satker'      => $this->admin->get('mst_satker'),
                'kegiatan'    => $this->admin->get('mst_kegiatan'),
                'unit'        => $this->admin->get('mst_unit_itda'),
                'kode'        => $this->admin->get('mst_kode_klasifikasi'),
                'no_agenda'   => $number,
                'kode_dinas'  => '434.100/' . date('Y'),
                'pegawai'     => $this->spt->getPegawai(),
                'jabST'       => $this->admin->get('mst_jabatan_st'),
                'user'        => $this->admin->get('user'),
                'slot_info'   => $this->spt->getInfoSlot($id_irban),   // ← info slot di form
            ];
            $this->template->load('tmpl/dashboard', 'spt/add', $data);

        } else {
            $input      = $this->input->post(null, true);
            $tgl_start  = InggrisTgl($input['start']);
            $tgl_end    = InggrisTgl($input['end']);
            $hari_kerja = hitung_hari_kerja($tgl_start, $tgl_end);
            $tags       = implode(',', $input['id_mst_satker']);

            $kode_tambah = (int)substr($this->admin->getMax('tbl_st', 'no_agenda'), -3) + 1;
            $number      = str_pad($kode_tambah, 3, '0', STR_PAD_LEFT);
            $kodex       = $input['id_kode_klasifikasi'] . "/$number/434.100/" . date('Y');

            $data_insert = [
                'tahun'           => date('Y'),
                'id_mst_unit_itda'=> $id_irban,
                'no_agenda'       => $input['no_agenda'],
                'tgl_st'          => InggrisTgl($input['tgl_st']),
                'no_st'           => $kodex,
                'tujuan'          => $tags,
                'kegiatan'        => $input['kegiatan'],
                'jenis'           => $input['id_mst_kegiatan'],
                'start'           => $tgl_start,
                'end'             => $tgl_end,
                'jlh_st'          => $hari_kerja,
            ];

            $this->admin->insert('tbl_st', $data_insert);
            $id_st = $this->db->insert_id();

            $this->_simpan_dasar_penugasan($id_st, $input['dasar_penugasan']);
            $this->_simpan_tim_audit($id_st, $input['id_mst_pegawai'], $input['id_mst_jabatan_st'], $input['jlh_hp']);

            set_pesan('SPT berhasil disimpan.');
            redirect('spt');
        }
    }

    public function upload_lhp($id_st)
    {
        $id_irban = $this->session->userdata('login_session')['id_mst_unit_itda'];

        // ── Validasi urutan LHP ──────────────────────────────────────────
        if (!$this->spt->cekUrutanLHP($id_st, $id_irban)) {

            // Ambil SPT tertua yang harus diselesaikan terlebih dahulu
            $antrian = $this->spt->getInfoSlot($id_irban);
            $pertama = !empty($antrian['list_antrian']) ? $antrian['list_antrian'][0] : null;

            $pesan_antrian = $pertama
                ? " Selesaikan dahulu LHP untuk SPT <strong>{$pertama['no_st']}</strong> (tgl. " . IndonesiaTgl($pertama['tgl_st']) . ")."
                : '';

            $this->session->set_flashdata('pesan',
                '<div class="alert alert-danger">
                    <i class="bx bx-error"></i>
                    <strong>Upload LHP tidak dapat dilakukan.</strong>
                    LHP harus diselesaikan secara urut sesuai tanggal pengajuan SPT.' . $pesan_antrian . '
                </div>'
            );
            redirect('spt');
            return;
        }

        $data['title'] = 'Upload LHP';
        $data['st']    = $this->db->get_where('tbl_st', ['id_st' => $id_st])->row_array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $config = [
                'upload_path'   => './upload/lhp/',
                'allowed_types' => 'pdf|doc|docx',
                'max_size'      => 5120,       // 5 MB
                'encrypt_name'  => TRUE,
            ];

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if ($this->upload->do_upload('file_lhp')) {
                $file = $this->upload->data();
                $this->db->insert('laporan_hasil', [
                    'id_st'        => $id_st,
                    'nomor_lhp'    => $this->input->post('nomor_lhp',    true),
                    'tanggal_lhp'  => InggrisTgl($this->input->post('tanggal_lhp', true)),
                    'file_lhp'     => $file['file_name'],
                    'keterangan'   => $this->input->post('keterangan',   true),
                ]);

                $this->session->set_flashdata('pesan',
                    '<div class="alert alert-success">
                        <i class="bx bx-check-circle"></i> LHP berhasil diupload. Slot SPT tersedia kembali.
                    </div>'
                );
                redirect('spt');
            } else {
                $this->session->set_flashdata('pesan',
                    '<div class="alert alert-danger">' . $this->upload->display_errors() . '</div>'
                );
                redirect('spt/upload_lhp/' . $id_st);
            }

        } else {
            $this->template->load('tmpl/dashboard', 'spt/upload_lhp', $data);
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function _simpan_dasar_penugasan($id_st, $dasar_penugasan = [])
    {
        foreach ($dasar_penugasan as $dasar) {
            if (!empty($dasar)) {
                $this->admin->insert('tbl_dasar_st', [
                    'id_st'    => $id_st,
                    'dasar_st' => $dasar,
                ]);
            }
        }
    }

    private function _simpan_tim_audit($id_st, $pegawai = [], $jabatan = [], $hp = [])
    {
        foreach ($pegawai as $i => $id_peg) {
            if (!empty($id_peg)) {
                $this->admin->insert('tbl_st_pegawai', [
                    'id_st'              => $id_st,
                    'id_mst_pegawai'     => $id_peg,
                    'id_mst_jabatan_st'  => $jabatan[$i],
                    'jlh_hp'             => $hp[$i],
                ]);
            }
        }
    }
}
