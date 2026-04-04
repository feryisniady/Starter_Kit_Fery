<?php

namespace Config;

class Menus
{
    public array $menus = [
        [
            'label'      => 'Dashboard',
            'url'        => '/dashboard',
            'icon'       => '🏠',
            'permission' => null, // semua user bisa lihat
        ],
        [
            'label'      => 'Users',
            'url'        => '/admin/users',
            'icon'       => '👥',
            'permission' => 'user.view',
        ],
        [
            'label'      => 'Roles',
            'url'        => '/admin/roles',
            'icon'       => '🛡️',
            'permission' => 'role.view',
        ],
    ];
}