<div class="main-wrapper main-wrapper-1">
	<div class="navbar-bg"></div>

	<nav class="navbar navbar-expand-lg main-navbar">
		<ul class="navbar-nav mr-3 mr-auto">
			<li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
		</ul>

		<ul class="navbar-nav navbar-right">
			<li class="dropdown">
				<a class="nav-link nav-link-user">
					<div class="d-sm-none d-lg-inline-block">
						<?= esc(session()->get('user_name')) ?>
					</div>
				</a>
			</li>
		</ul>
	</nav>
</div>