<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginServiceModel extends Model
{
    protected $table      = 'login_services';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name', 'description', 'url', 'icon', 'require_login', 'sort_order', 'is_active',
    ];

    protected $useTimestamps = true;

    public function getActive(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
}
