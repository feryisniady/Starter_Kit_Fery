<?php

namespace App\Models;

use CodeIgniter\Model;

class AppSettingModel extends Model
{
    protected $table      = 'app_settings';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'key', 'value', 'type', 'group', 'label', 'description', 'sort_order',
    ];

    protected $useTimestamps = true;

    /**
     * Ambil semua setting sebagai array key => value
     */
    public function getAllAsMap(): array
    {
        $rows = $this->findAll();
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['key']] = $row['value'];
        }
        return $map;
    }

    /**
     * Ambil semua setting per group, terstruktur
     */
    public function getByGroup(string $group): array
    {
        return $this->where('group', $group)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil semua group beserta settingnya
     */
    public function getAllGrouped(): array
    {
        $rows   = $this->orderBy('sort_order', 'ASC')->findAll();
        $groups = [];
        foreach ($rows as $row) {
            $groups[$row['group']][] = $row;
        }
        return $groups;
    }

    /**
     * Simpan satu setting
     */
    public function setValue(string $key, ?string $value): void
    {
        $existing = $this->where('key', $key)->first();
        if ($existing) {
            $this->update($existing['id'], ['value' => $value]);
        }
    }

    /**
     * Simpan banyak setting sekaligus
     */
    public function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->setValue($key, $value);
        }
    }
}
