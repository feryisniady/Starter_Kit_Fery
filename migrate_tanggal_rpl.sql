-- Jalankan di phpMyAdmin / Laragon Terminal
-- Tambah kolom tanggal_rpl ke tabel spt

ALTER TABLE `spt`
    ADD COLUMN `tanggal_rpl` DATE NULL DEFAULT NULL
    COMMENT 'Rencana Penerbitan Laporan'
    AFTER `tanggal_selesai`;
