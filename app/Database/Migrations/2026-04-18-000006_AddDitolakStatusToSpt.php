<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDitolakStatusToSpt extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE spt MODIFY COLUMN status ENUM('draft','diajukan','ditolak','acc_irban','acc_evlap','acc_sekretaris','terbit') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE spt MODIFY COLUMN status ENUM('draft','diajukan','acc_irban','acc_evlap','acc_sekretaris','terbit') NOT NULL DEFAULT 'draft'");
    }
}
