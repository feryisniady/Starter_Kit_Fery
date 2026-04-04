<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');

/*$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::loginProcess');
$routes->get('/logout', 'Auth::logout');
$routes->get('/dashboard', 'Dashboard::index');*/


// Guest only (belum login)
$routes->group('', ['filter' => 'guest'], function($routes) {
	$routes->get('/login', 'Auth::login');
	$routes->post('/login', 'Auth::loginProcess');
});

// Auth only (harus sudah login)
$routes->group('', ['filter' => 'auth'], function($routes) {
	$routes->get('/dashboard', 'Dashboard::index');
	$routes->get('/users', 'Dashboard::index', ['filter' => 'permission:user.view']);
});


// Public
$routes->get('/logout', 'Auth::logout');


$routes->group('admin', ['filter' => 'auth'], function($routes) {
    // Users
	$routes->get('users', 'Admin\UserController::index', ['filter' => 'permission:user.view']);
	$routes->get('users/create', 'Admin\UserController::create', ['filter' => 'permission:user.create']);
	$routes->post('users/store', 'Admin\UserController::store', ['filter' => 'permission:user.create']);
	$routes->get('users/edit/(:num)', 'Admin\UserController::edit/$1', ['filter' => 'permission:user.edit']);
	$routes->post('users/update/(:num)', 'Admin\UserController::update/$1', ['filter' => 'permission:user.edit']);
	$routes->get('users/delete/(:num)', 'Admin\UserController::delete/$1', ['filter' => 'permission:user.delete']);
	// Roles
	$routes->get('roles', 'Admin\RoleController::index', ['filter' => 'permission:role.view']);
	$routes->get('roles/create', 'Admin\RoleController::create', ['filter' => 'permission:role.create']);
	$routes->post('roles/store', 'Admin\RoleController::store', ['filter' => 'permission:role.create']);
	$routes->get('roles/edit/(:num)', 'Admin\RoleController::edit/$1', ['filter' => 'permission:role.edit']);
	$routes->post('roles/update/(:num)', 'Admin\RoleController::update/$1', ['filter' => 'permission:role.edit']);
	$routes->get('roles/delete/(:num)', 'Admin\RoleController::delete/$1', ['filter' => 'permission:role.delete']);
	// Menus
	$routes->get('menus', 'Admin\MenuController::index', ['filter' => 'permission:menu.view']);
	$routes->get('menus/create', 'Admin\MenuController::create', ['filter' => 'permission:menu.create']);
	$routes->post('menus/store', 'Admin\MenuController::store', ['filter' => 'permission:menu.create']);
	$routes->get('menus/edit/(:num)', 'Admin\MenuController::edit/$1', ['filter' => 'permission:menu.edit']);
	$routes->post('menus/update/(:num)', 'Admin\MenuController::update/$1', ['filter' => 'permission:menu.edit']);
	$routes->get('menus/delete/(:num)', 'Admin\MenuController::delete/$1', ['filter' => 'permission:menu.delete']);
	// Permissions
	$routes->get('permissions', 'Admin\PermissionController::index', ['filter' => 'permission:permission.view']);
	$routes->post('permissions/store', 'Admin\PermissionController::store', ['filter' => 'permission:permission.create']);
	$routes->post('permissions/store-batch', 'Admin\PermissionController::storeBatch', ['filter' => 'permission:permission.create']);
	$routes->get('permissions/delete/(:num)', 'Admin\PermissionController::delete/$1', ['filter' => 'permission:permission.delete']);
});

