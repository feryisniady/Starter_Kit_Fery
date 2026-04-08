<?php


if (!function_exists('buildMenuTree')) {
    function buildMenuTree(array $menus, $parentId = null): array
    {
        $branch = [];

        foreach ($menus as $menu) {
            if ((int)$menu['parent_id'] === (int)$parentId) {

                $children = buildMenuTree($menus, $menu['id']);
                $menu['children'] = $children ?: [];

                $branch[] = $menu;
            }
        }

        return $branch;
    }
}


if (!function_exists('renderMenuTree')) {
    function renderMenuTree(array $menus, string $currentUrl, int $level = 0): string
    {
        $html = '';

        foreach ($menus as $menu) {

            $hasChildren = !empty($menu['children']);

            // =============================
            // ACTIVE DETECTION (RECURSIVE)
            // =============================
            $isActive = isMenuActive($menu, $currentUrl);

            // =============================
            // START ITEM
            // =============================
            $html .= '<div class="nav-item">';

            // =============================
            // PARENT WITH CHILDREN
            // =============================
            if ($hasChildren) {

                // ===== Parent LINK / TOGGLE =====
                if ($menu['url'] === '#' || empty($menu['url'])) {

                    $html .= '
                    <div class="nav-link ' . ($isActive ? 'open' : '') . '" 
                    onclick="toggleSubmenu(this)">
                    <div class="nav-icon">
                    <i class="' . esc($menu['icon']) . '"></i>
                    </div>
                    <span class="nav-label">' . esc($menu['label']) . '</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                    </div>';

                } else {

                    $html .= '
                    <div class="nav-link ' . ($isActive ? 'open' : '') . '">
                    <a href="' . esc($menu['url']) . '" 
                    style="display:flex;align-items:center;gap:12px;flex:1;color:inherit;text-decoration:none">
                    <div class="nav-icon">
                    <i class="' . esc($menu['icon']) . '"></i>
                    </div>
                    <span class="nav-label">' . esc($menu['label']) . '</span>
                    </a>
                    <i class="fas fa-chevron-right nav-arrow"
                    onclick="toggleSubmenu(this.closest(\'.nav-link\'))"
                    style="padding:6px;cursor:pointer"></i>
                    </div>';
                }

                // ===== SUBMENU =====
                $html .= '<div class="submenu ' . ($isActive ? 'open' : '') . '">';

                // 🔥 recursive call (LEVEL +1)
                $html .= renderMenuTree($menu['children'], $currentUrl, $level + 1);

                $html .= '</div>';
            }

            // =============================
            // CHILD / SINGLE MENU
            // =============================
            else {

                // 🔥 LEVEL HANDLING (INI YG BIKIN GAK SEJAJAR)
                if ($level === 0) {

                    // ROOT MENU (PAKE ICON)
                    $html .= '
                    <a href="' . esc($menu['url']) . '" 
                    class="nav-link ' . ($isActive ? 'active' : '') . '">
                    <div class="nav-icon">
                    <i class="' . esc($menu['icon']) . '"></i>
                    </div>
                    <span class="nav-label">' . esc($menu['label']) . '</span>
                    </a>';

                } else {

                    // SUBMENU (PAKE STYLE KHUSUS)
                    $html .= '
                    <a href="' . esc($menu['url']) . '" 
                    class="nav-link submenu-link ' . ($isActive ? 'active' : '') . '">
                    
                    <div class="nav-icon">
                    <i class="' . esc($menu['icon'] ?? 'fas fa-circle') . '"></i>
                    </div>

                    <span class="nav-label">' . esc($menu['label']) . '</span>
                    </a>';
                }
            }

            $html .= '</div>';
        }

        return $html;
    }
}


/**
 * Cek apakah menu aktif (termasuk child)
 */
if (!function_exists('isMenuActive')) {
    function isMenuActive(array $menu, string $currentUrl): bool
    {
        // cek diri sendiri
        if (!empty($menu['url'])) {
            if ($currentUrl === $menu['url'] || str_starts_with($currentUrl, $menu['url'] . '/')) {
                return true;
            }
        }

        // cek children (recursive)
        if (!empty($menu['children'])) {
            foreach ($menu['children'] as $child) {
                if (isMenuActive($child, $currentUrl)) {
                    return true;
                }
            }
        }

        return false;
    }
}