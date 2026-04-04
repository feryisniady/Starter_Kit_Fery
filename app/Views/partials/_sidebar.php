<div class="main-sidebar sidebar-style-2">
	<aside id="sidebar-wrapper">
		<div class="sidebar-brand">
			<a class="app-brand-link">
				<span class="app-brand-logo">
					<a href="/dashboard">🔐 RBAC Starter</a>
				</span>
				<!-- <span class="app-brand-text menu-text fw-bolder ms-2"></span> -->
			</a>
		</div>
		<ul class="sidebar-menu">
			<?php foreach(getMenus() as $menu): ?>
				<li class="<?= isActiveMenu($menu['url']) ? 'active' : '' ?>">
					<a href="<?= $menu['url'] ?>" class="nav-link">
						<i class="<?= $menu['icon'] ?>"></i>
						<span><?= $menu['label'] ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="mt-4 mb-4 p-3 hide-sidebar-mini">
			<a href="/logout" class="btn btn-primary btn-lg btn-block btn-icon-split text-white fw-bold" id="logout-btn">
				<i class="fas fa-sign-out-alt"></i> Logout
			</a>
		</div>
	</aside>
</div>