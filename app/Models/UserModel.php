<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = ['name', 'email', 'password', 'status'];

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

     // Ambil semua user beserta role-nya
    public function getUsersWithRoles()
    {
        return $this->db->table('users u')
            ->select('u.id, u.name, u.email, u.status, GROUP_CONCAT(r.name) as roles')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->groupBy('u.id')
            ->get()
            ->getResultArray();
    }

    // Ambil role milik user
    public function getUserRoles(int $userId)
    {
        return $this->db->table('user_roles ur')
            ->select('r.id, r.name')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->getResultArray();
    }

    // Assign roles ke user
    public function syncRoles(int $userId, array $roleIds)
    {
        // Hapus role lama
        $this->db->table('user_roles')->where('user_id', $userId)->delete();

        // Insert role baru
        if (!empty($roleIds)) {
            $data = [];
            foreach ($roleIds as $roleId) {
                $data[] = ['user_id' => $userId, 'role_id' => $roleId];
            }
            $this->db->table('user_roles')->insertBatch($data);
        }
    }
}
