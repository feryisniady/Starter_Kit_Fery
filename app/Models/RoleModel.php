<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
     protected $allowedFields = ['name'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    // Ambil semua role beserta jumlah permission
    public function getRolesWithPermissions()
    {
        return $this->db->table('roles r')
            ->select('r.id, r.name, COUNT(rp.permission_id) as total_permissions')
            ->join('role_permissions rp', 'rp.role_id = r.id', 'left')
            ->groupBy('r.id')
            ->get()
            ->getResultArray();
    }

    // Ambil permission milik role
    public function getRolePermissions(int $roleId)
    {
        return $this->db->table('role_permissions rp')
            ->select('p.id, p.name')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()
            ->getResultArray();
    }

    // Sync permissions ke role
    public function syncPermissions(int $roleId, array $permissionIds)
    {
        // Hapus permission lama
        $this->db->table('role_permissions')->where('role_id', $roleId)->delete();

        // Insert permission baru
        if (!empty($permissionIds)) {
            $data = [];
            foreach ($permissionIds as $permId) {
                $data[] = ['role_id' => $roleId, 'permission_id' => $permId];
            }
            $this->db->table('role_permissions')->insertBatch($data);
        }
    }

}
