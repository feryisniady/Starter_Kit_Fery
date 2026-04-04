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
	// Profile
	$routes->get('/profile', 'ProfileController::index');
	$routes->post('/profile/update', 'ProfileController::update');
	$routes->post('/profile/change-password', 'ProfileController::changePassword');
	$routes->post('/profile/delete-avatar', 'ProfileController::deleteAvatar');
	// Notifikasi
	$routes->get('/notifications/test', 'NotificationController::test');
	$routes->get('/notifications', 'NotificationController::index');
	$routes->get('/notifications/fetch', 'NotificationController::fetch');
	$routes->get('/notifications/read/(:num)', 'NotificationController::read/$1');
	$routes->post('/notifications/read/(:num)', 'NotificationController::read/$1');
	$routes->post('/notifications/read-all', 'NotificationController::readAll');
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
	// Activity Log
	$routes->get('activity-logs', 'Admin\ActivityLogController::index', ['filter' => 'permission:activitylog.view']);
	$routes->get('activity-logs/export', 'Admin\ActivityLogController::export', ['filter' => 'permission:activitylog.view']);
	// App Settings
	$routes->get('settings', 'Admin\AppSettingController::index', ['filter' => 'permission:setting.manage']);
	$routes->post('settings/update', 'Admin\AppSettingController::update', ['filter' => 'permission:setting.manage']);
	$routes->get('settings/delete-image/(:segment)', 'Admin\AppSettingController::deleteImage/$1', ['filter' => 'permission:setting.manage']);
	// Login Services
	$routes->get('login-services', 'Admin\LoginServiceController::index', ['filter' => 'permission:setting.manage']);
	$routes->post('login-services/store', 'Admin\LoginServiceController::store', ['filter' => 'permission:setting.manage']);
	$routes->post('login-services/update/(:num)', 'Admin\LoginServiceController::update/$1', ['filter' => 'permission:setting.manage']);
	$routes->get('login-services/delete/(:num)', 'Admin\LoginServiceController::delete/$1', ['filter' => 'permission:setting.manage']);
});

