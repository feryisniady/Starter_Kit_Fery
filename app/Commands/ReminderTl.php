<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\MailService;
use App\Services\WaService;

/**
 * Kirim pengingat batas waktu Tindak Lanjut ke OPD/Entitas.
 *
 * Sistem pre-scheduled: jadwal sudah dihitung saat batas_waktu di-set.
 * Command hanya memproses jadwal yang trigger_date <= hari ini & belum terkirim.
 *
 * Cara jalan: php spark reminder:tl
 * Dry run:    php spark reminder:tl --dry-run
 *
 * Jadwalkan harian via Task Scheduler (Windows) atau crontab (Linux):
 *   0 8 * * * /usr/bin/php /var/www/html/spark reminder:tl >> /tmp/reminder.log 2>&1
 */
class ReminderTl extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'reminder:tl';
    protected $description = 'Kirim notifikasi pengingat batas waktu Tindak Lanjut ke OPD (pre-scheduled).';
    protected $usage       = 'reminder:tl [--dry-run]';
    protected $options     = [
        '--dry-run' => 'Simulasi — tampilkan yang akan dikirim tanpa benar-benar mengirim',
    ];

    public function run(array $params)
    {
        $isDryRun = array_key_exists('dry-run', $params);
        $today    = date('Y-m-d');
        $db       = \Config\Database::connect();
        $mail     = new MailService();
        $wa       = new WaService();

        CLI::write('╔══════════════════════════════════════════╗', 'yellow');
        CLI::write('║  Reminder Tindak Lanjut — ' . $today . ($isDryRun ? ' [DRY RUN]' : '') . '  ║', 'yellow');
        CLI::write('╚══════════════════════════════════════════╝', 'yellow');

        // ── Query utama: ambil jadwal yang sudah jatuh tempo & belum semua terkirim ──
        // trigger_date <= hari ini → tangkap meski cron skip beberapa hari
        $jadwals = $db->query("
            SELECT
                rr.id           AS jadwal_id,
                rr.rekomendasi_id AS rek_id,
                rr.trigger_date,
                rr.hari_trigger,
                rr.sent_inapp,
                rr.sent_email,
                rr.sent_wa,

                r.isi_rekomendasi,
                r.batas_waktu,
                r.status        AS rek_status,

                t.judul         AS temuan_judul,
                t.nomor_temuan,
                sp.nomor_naskah AS spt_nomor,

                e.id            AS entitas_id,
                e.nama          AS entitas_nama,
                u.id            AS user_id,
                u.name          AS user_nama,
                u.email         AS user_email,
                u.phone         AS user_phone
            FROM rekomendasi_reminder rr
            JOIN rekomendasi r  ON r.id  = rr.rekomendasi_id
            JOIN temuan t       ON t.id  = r.temuan_id
            JOIN spt sp         ON sp.id = t.spt_id
            LEFT JOIN pkpt_kegiatan pk  ON pk.id = sp.pkpt_kegiatan_id
            LEFT JOIN pkpt_entitas pe   ON pe.pkpt_kegiatan_id = pk.id
            LEFT JOIN entitas e         ON e.id  = pe.entitas_id
            LEFT JOIN users u           ON u.id  = e.user_id
            WHERE rr.trigger_date <= ?
              AND r.status != 'selesai'
              AND (rr.sent_inapp = 0 OR rr.sent_email = 0 OR rr.sent_wa = 0)
              AND e.id IS NOT NULL
            ORDER BY rr.trigger_date ASC
        ", [$today])->getResultArray();

        if (empty($jadwals)) {
            CLI::write('✓ Tidak ada pengingat yang perlu dikirim hari ini.', 'green');
            return;
        }

        CLI::write('Jadwal ditemukan: ' . count($jadwals) . ' reminder perlu diproses.', 'cyan');
        CLI::write(str_repeat('─', 60), 'dark_gray');

        $totalKirim = 0;

        foreach ($jadwals as $j) {
            $rekId    = (int)$j['rek_id'];
            $jadwalId = (int)$j['jadwal_id'];

            [$subjek, $pesanTeks] = $this->buatPesan($j);

            CLI::write(sprintf(
                "\n[%s] H%+d | %s | %s",
                $j['trigger_date'],
                -$j['hari_trigger'],
                mb_strimwidth($j['temuan_judul'], 0, 35, '…'),
                $j['entitas_nama']
            ), 'white');

            $updateFields = [];

            // ── In-app notification ──────────────────────────────────────
            if (!$j['sent_inapp'] && !empty($j['user_id'])) {
                if (!$isDryRun) {
                    $this->kirimInApp((int)$j['user_id'], $subjek, $pesanTeks, $rekId);
                    $updateFields['sent_inapp'] = 1;
                    $totalKirim++;
                }
                CLI::write('  ✓ In-app → ' . $j['user_nama'], $isDryRun ? 'dark_gray' : 'light_cyan');
            }

            // ── Email ────────────────────────────────────────────────────
            if (!$j['sent_email'] && !empty($j['user_email'])) {
                if ($mail->isConfigured()) {
                    if (!$isDryRun) {
                        $ok = $mail->send($j['user_email'], $subjek, $this->wrapEmail($pesanTeks, $j));
                        if ($ok) {
                            $updateFields['sent_email'] = 1;
                            $totalKirim++;
                            CLI::write('  ✓ Email  → ' . $j['user_email'], 'light_cyan');
                        } else {
                            CLI::write('  ✗ Email GAGAL → ' . $j['user_email'], 'red');
                        }
                    } else {
                        CLI::write('  ~ Email  → ' . $j['user_email'] . ' [dry run]', 'dark_gray');
                    }
                } else {
                    CLI::write('  - Email  → tidak dikonfigurasi', 'dark_gray');
                }
            }

            // ── WhatsApp ─────────────────────────────────────────────────
            if (!$j['sent_wa'] && !empty($j['user_phone'])) {
                if ($wa->isConfigured()) {
                    if (!$isDryRun) {
                        $ok = $wa->send($j['user_phone'], $pesanTeks);
                        if ($ok) {
                            $updateFields['sent_wa'] = 1;
                            $totalKirim++;
                            CLI::write('  ✓ WA     → ' . $j['user_phone'], 'light_cyan');
                        } else {
                            CLI::write('  ✗ WA GAGAL → ' . $j['user_phone'], 'red');
                        }
                    } else {
                        CLI::write('  ~ WA     → ' . $j['user_phone'] . ' [dry run]', 'dark_gray');
                    }
                } else {
                    CLI::write('  - WA     → tidak dikonfigurasi', 'dark_gray');
                }
            }

            // Update status pengiriman
            if (!$isDryRun && !empty($updateFields)) {
                $updateFields['sent_at'] = date('Y-m-d H:i:s');
                $db->table('rekomendasi_reminder')
                    ->where('id', $jadwalId)
                    ->update($updateFields);
            }
        }

        CLI::write(str_repeat('─', 60), 'dark_gray');
        CLI::write("\n✓ Selesai. Total notifikasi terkirim: {$totalKirim}", 'green');

        if (!$isDryRun) {
            logActivity('system.reminder_tl', 'system',
                "Reminder TL: {$totalKirim} notif, " . count($jadwals) . " jadwal diproses, {$today}");
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function buatPesan(array $j): array
    {
        $appName = app_setting('app_name') ?: 'Inspektorat';
        $hari    = (int)$j['hari_trigger'];
        $tgl     = date('d M Y', strtotime($j['batas_waktu']));
        $entitas = $j['entitas_nama'];
        $temuan  = $j['temuan_judul'];
        $rek     = mb_strimwidth($j['isi_rekomendasi'], 0, 150, '…');
        $spt     = $j['spt_nomor'];

        if ($hari > 0) {
            $subjek = "⏰ Pengingat TL: {$hari} hari lagi — {$temuan}";
            $pesan  = "Yth. {$entitas},\n\n"
                    . "Pengingat: tindak lanjut rekomendasi berikut jatuh tempo dalam *{$hari} hari* (tanggal {$tgl}).\n\n"
                    . "📋 SPT    : {$spt}\n"
                    . "🔎 Temuan : {$temuan}\n"
                    . "📝 Rekomendasi: {$rek}\n\n"
                    . "Silakan kirimkan tindak lanjut melalui portal auditi sebelum tanggal {$tgl}.\n\n"
                    . "— {$appName}";
        } elseif ($hari === 0) {
            $subjek = "🚨 HARI INI Batas Akhir TL — {$temuan}";
            $pesan  = "Yth. {$entitas},\n\n"
                    . "*HARI INI* ({$tgl}) adalah batas akhir pengiriman tindak lanjut untuk:\n\n"
                    . "📋 SPT    : {$spt}\n"
                    . "🔎 Temuan : {$temuan}\n"
                    . "📝 Rekomendasi: {$rek}\n\n"
                    . "Segera kirimkan tindak lanjut sebelum hari ini berakhir!\n\n"
                    . "— {$appName}";
        } else {
            $telat   = abs($hari);
            $subjek  = "❗ TERLAMBAT {$telat} Hari — TL: {$temuan}";
            $pesan   = "Yth. {$entitas},\n\n"
                     . "Tindak lanjut rekomendasi berikut *TELAH MELEWATI BATAS WAKTU* {$tgl} ({$telat} hari yang lalu):\n\n"
                     . "📋 SPT    : {$spt}\n"
                     . "🔎 Temuan : {$temuan}\n"
                     . "📝 Rekomendasi: {$rek}\n\n"
                     . "Mohon segera kirimkan tindak lanjut dan hubungi Inspektorat jika ada kendala.\n\n"
                     . "— {$appName}";
        }

        return [$subjek, $pesan];
    }

    private function wrapEmail(string $pesan, array $j): string
    {
        $appName  = esc(app_setting('app_name') ?: 'Inspektorat');
        $deadline = esc($j['batas_waktu']);
        $html     = nl2br(esc($pesan));
        $hari     = (int)$j['hari_trigger'];

        $alertColor = $hari > 0 ? '#16a34a' : ($hari === 0 ? '#d97706' : '#dc2626');
        $alertBg    = $hari > 0 ? '#f0fdf4' : ($hari === 0 ? '#fef9c3' : '#fee2e2');

        return <<<HTML
        <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#f8fafc;padding:20px">
          <div style="background:#1e293b;color:#fff;padding:20px 28px;border-radius:12px 12px 0 0">
            <div style="font-size:20px;font-weight:700">{$appName}</div>
            <div style="font-size:12px;opacity:.65;margin-top:4px">Notifikasi Pengingat Tindak Lanjut Rekomendasi</div>
          </div>
          <div style="background:#fff;border:1px solid #e2e8f0;border-top:none;
                      padding:28px;border-radius:0 0 12px 12px">
            <p style="color:#374151;line-height:1.8;font-size:14px;margin:0 0 20px">{$html}</p>
            <div style="padding:14px 18px;background:{$alertBg};border-radius:8px;
                        border-left:4px solid {$alertColor};margin-bottom:20px">
              <strong style="color:{$alertColor};font-size:13px">
                Batas Waktu Tindak Lanjut: {$deadline}
              </strong>
            </div>
            <a href="#" style="display:inline-block;padding:11px 22px;background:{$alertColor};
                               color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px">
              Buka Portal Auditi
            </a>
            <p style="margin-top:24px;font-size:11px;color:#94a3b8;border-top:1px solid #f1f5f9;padding-top:16px">
              Email ini dikirim otomatis oleh sistem {$appName}. Jangan membalas email ini.
            </p>
          </div>
        </div>
        HTML;
    }

    private function kirimInApp(int $userId, string $title, string $pesan, int $rekId): void
    {
        try {
            \Config\Database::connect()->table('notifications')->insert([
                'user_id'    => $userId,
                'title'      => $title,
                'message'    => mb_strimwidth(strip_tags($pesan), 0, 200, '…'),
                'url'        => '/auditi/tl/' . $rekId,
                'type'       => 'warning',
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[ReminderTl] in-app insert error: ' . $e->getMessage());
        }
    }
}
