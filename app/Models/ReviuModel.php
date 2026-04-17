<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviuModel extends Model
{
    protected $table            = 't_reviu';
    protected $primaryKey       = 'id_reviu';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_kriteria', 'file_name', 'hasil_analisis'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
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


    public function getKriteria($id = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('m_kriteria');
        if ($id) {
            return $builder->getWhere(['id_kriteria' => $id])->getRow();
        }
        return $builder->get()->getResult();
    }



}
