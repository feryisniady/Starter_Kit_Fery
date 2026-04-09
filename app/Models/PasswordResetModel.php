<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table      = 'password_resets';
    protected $primaryKey = 'email';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['email', 'token', 'created_at'];

    protected $useTimestamps  = false;
    protected $skipValidation = true;

    public function createToken(string $email): string
    {
        // Hapus token lama milik email ini
        $this->where('email', $email)->delete();

        $token = bin2hex(random_bytes(32));

        $this->insert([
            'email'      => $email,
            'token'      => $token,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    public function findByToken(string $token): ?array
    {
        return $this->where('token', $token)->first();
    }

    public function isExpired(string $createdAt, int $minutes = 60): bool
    {
        return (time() - strtotime($createdAt)) > ($minutes * 60);
    }

    public function deleteByEmail(string $email): void
    {
        $this->where('email', $email)->delete();
    }
}
