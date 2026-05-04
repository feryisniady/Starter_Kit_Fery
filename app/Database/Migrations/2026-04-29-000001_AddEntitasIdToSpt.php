<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEntitasIdToSpt extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE spt ADD COLUMN entitas_id INT UNSIGNED NULL DEFAULT NULL AFTER jenis_non_pkpt');
        $this->db->query('ALTER TABLE spt ADD CONSTRAINT fk_spt_entitas FOREIGN KEY (entitas_id) REFERENCES entitas(id) ON DELETE SET NULL');
        $this->db->query('CREATE INDEX idx_spt_entitas_id ON spt(entitas_id)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE spt DROP FOREIGN KEY fk_spt_entitas');
        $this->db->query('ALTER TABLE spt DROP INDEX idx_spt_entitas_id');
        $this->db->query('ALTER TABLE spt DROP COLUMN entitas_id');
    }
}
