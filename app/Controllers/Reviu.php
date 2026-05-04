<?php

namespace App\Controllers;

use App\Models\ReviuModel;
use CodeIgniter\Controller;

class Reviu extends Controller
{

    public function index()
    {
        $model = new ReviuModel();
        $data['list_kriteria'] = $model->getKriteria();
        return view('reviu/v_reviu_live', $data);
    }

    public function prosesAjax()
    {

        // Tambahkan ini di baris pertama untuk bypass CORS saat dev
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-goog-api-key");

        // Jika browser kirim method OPTIONS (pre-flight), langsung stop di sini
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit;
        }


        // 1. Inisialisasi Global
        $apiKey = trim((string) env('gemini.apiKey', ''));
        if (empty($apiKey)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gemini API key belum dikonfigurasi. Tambahkan gemini.apiKey di file .env']);
        }
        $model = new ReviuModel();
        $file = $this->request->getFile('dokumen');
        $idKriteria = $this->request->getPost('id_kriteria');
        $instruksiTambahan = $this->request->getPost('instruksi_tambahan'); // Ambil dari textarea manual

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Dokumen tidak valid!']);
        }

        // 2. Ambil Kriteria Master dari DB
        $kriteria = $model->getKriteria($idKriteria);
        $namaDokumen = $kriteria ? $kriteria->nama_dokumen : "Dokumen Umum";
        $instruksiMaster = $kriteria ? $kriteria->instruksi_ai : "Lakukan reviu menyeluruh.";

        // 3. Persiapan Data File
        $mimeType = $file->getMimeType(); 
        $fileData = base64_encode(file_get_contents($file->getTempName()));
        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', $newName);

        // 4. KONSTRUKSI PROMPT GLOBAL (Kunci Format di Sini)
        $prompt = "Anda adalah Auditor Ahli di Inspektorat. Tugas Anda adalah mereviu dokumen berikut:\n";
        $prompt .= "--- NOMENKLATUR DOKUMEN: $namaDokumen ---\n";
        $prompt .= "--- INSTRUKSI MASTER: $instruksiMaster ---\n";

        if (!empty($instruksiTambahan)) {
            $prompt .= "--- FOKUS REVIU TAMBAHAN: $instruksiTambahan ---\n";
        }

        $prompt .= "\nWAJIB MEMBERIKAN HASIL DALAM FORMAT STANDAR AUDIT BERIKUT:\n";
        $prompt .= "1. KONDISI: Apa yang terjadi di dokumen tersebut?\n";
        $prompt .= "2. KRITERIA: Apa aturan/standar yang seharusnya dipenuhi?\n";
        $prompt .= "3. SEBAB: Mengapa kondisi tersebut terjadi?\n";
        $prompt .= "4. AKIBAT: Apa dampak dari kondisi tersebut?\n";
        $prompt .= "\nSajikan jawaban dalam bahasa Indonesia yang formal dan profesional.";

        // 5. Eksekusi API Gemini
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent";
        $client = \Config\Services::curlrequest();

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-goog-api-key' => $apiKey
                ],
                'json' => [
                    "contents" => [[
                        "parts" => [
                            ["text" => $prompt],
                            ["inline_data" => ["mime_type" => $mimeType, "data" => $fileData]]
                        ]
                    ]],
                    "safetySettings" => [
                        ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE"],
                        ["category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_NONE"],
                        ["category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_NONE"],
                        ["category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_NONE"]
                    ]
                ],
                'http_errors' => false
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $analisis = $result['candidates'][0]['content']['parts'][0]['text'];

                $model->save([  
                    'id_kriteria'    => $idKriteria,
                    'file_name'      => $newName,
                    'hasil_analisis' => $analisis
                ]);

                $insertID = $model->insertID();

                // HAPUS satu return yang duplikat di bawahnya, cukup pakai yang ini:
                return $this->response->setJSON([
                    'status' => 'success', 
                    'analisis' => $analisis,
                    'id_reviu' => $insertID 
                ]);
            }

            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal reviu.', 'debug' => $result]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function cetak($id)
    {
        $model = new ReviuModel();
        $dataReviu = $model->select('t_reviu.*, m_kriteria.nama_dokumen')
            ->join('m_kriteria', 'm_kriteria.id_kriteria = t_reviu.id_kriteria')
            ->where('t_reviu.id_reviu', $id)
            ->first();

        if (!$dataReviu) {
            return "Data tidak ditemukan!";
        }

        return view('reviu/v_cetak_reviu', ['reviu' => $dataReviu]);
    }

    // Tambahkan di bagian atas Controller
    // use Dompdf\Dompdf; 

    public function exportPdf($id)
    {
        $model = new ReviuModel();
        // Ambil data reviu beserta join ke tabel kriteria
        $dataReviu = $model->select('t_reviu.*, m_kriteria.nama_dokumen, m_kriteria.instruksi_ai')
        ->join('m_kriteria', 'm_kriteria.id_kriteria = t_reviu.id_kriteria')
        ->where('t_reviu.id_reviu', $id)
        ->first();

        if (!$dataReviu) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // Load Library Dompdf (Pastikan sudah install lewat composer: composer require dompdf/dompdf)
        $dompdf = new \Dompdf\Dompdf();

        // Siapkan HTML untuk PDF
        $html = view('reviu/pdf_template', [
            'reviu' => $dataReviu,
            'tanggal' => date('d F Y', strtotime($dataReviu['created_at']))
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Stream ke browser (Download)
        return $dompdf->stream("Hasil_Reviu_" . $dataReviu['id'] . ".pdf", ["Attachment" => false]);
    }
}