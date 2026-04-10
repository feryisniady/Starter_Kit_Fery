<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');

// Guest only (belum login)
$routes->group('', ['filter' => 'guest'], function($routes) {
	$routes->get('/login',             'Auth::login');
	$routes->post('/login',            'Auth::loginProcess');
	$routes->get('/register',          'Auth::register');
	$routes->post('/register',         'Auth::registerProcess');
	$routes->get('/forgot-password',   'Auth::forgotPassword');
	$routes->post('/forgot-password',  'Auth::forgotPasswordProcess');
	$routes->get('/reset-password/(:hash)',  'Auth::resetPassword/$1');
	$routes->post('/reset-password',   'Auth::resetPasswordProcess');
});

// Auth only (harus sudah login)
$routes->group('', ['filter' => 'auth'], function($routes) {
	$routes->get('/dashboard', 'Dashboard::index');
	// Profile
	$routes->get('/profile', 'ProfileController::index');
	$routes->post('/profile/update', 'ProfileController::update');
	$routes->post('/profile/change-password', 'ProfileController::changePassword');
	$routes->post('/profile/delete-avatar', 'ProfileController::deleteAvatar');
	// Notifikasi
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
	$routes->get('users',              'Admin\UserController::index',    ['filter' => 'permission:user.view']);
	$routes->post('users/data',        'Admin\UserController::getData',  ['filter' => 'permission:user.view']);
	$routes->get('users/create',       'Admin\UserController::create',   ['filter' => 'permission:user.create']);
	$routes->post('users/store',       'Admin\UserController::store',    ['filter' => 'permission:user.create']);
	$routes->get('users/edit/(:num)',   'Admin\UserController::edit/$1',  ['filter' => 'permission:user.edit']);
	$routes->post('users/update/(:num)','Admin\UserController::update/$1',['filter' => 'permission:user.edit']);
	$routes->get('users/delete/(:num)', 'Admin\UserController::delete/$1',['filter' => 'permission:user.delete']);
	// Roles
	$routes->get('roles',              'Admin\RoleController::index',    ['filter' => 'permission:role.view']);
	$routes->post('roles/data',        'Admin\RoleController::getData',  ['filter' => 'permission:role.view']);
	$routes->get('roles/create',       'Admin\RoleController::create',   ['filter' => 'permission:role.create']);
	$routes->post('roles/store',       'Admin\RoleController::store',    ['filter' => 'permission:role.create']);
	$routes->get('roles/edit/(:num)',   'Admin\RoleController::edit/$1',  ['filter' => 'permission:role.edit']);
	$routes->post('roles/update/(:num)','Admin\RoleController::update/$1',['filter' => 'permission:role.edit']);
	$routes->get('roles/delete/(:num)', 'Admin\RoleController::delete/$1',['filter' => 'permission:role.delete']);
	// Menus
	$routes->get('menus',              'Admin\MenuController::index',    ['filter' => 'permission:menu.view']);
	$routes->post('menus/data',        'Admin\MenuController::getData',  ['filter' => 'permission:menu.view']);
	$routes->get('menus/create',       'Admin\MenuController::create',   ['filter' => 'permission:menu.create']);
	$routes->post('menus/store',       'Admin\MenuController::store',    ['filter' => 'permission:menu.create']);
	$routes->get('menus/edit/(:num)',   'Admin\MenuController::edit/$1',  ['filter' => 'permission:menu.edit']);
	$routes->post('menus/update/(:num)','Admin\MenuController::update/$1',['filter' => 'permission:menu.edit']);
	$routes->get('menus/delete/(:num)', 'Admin\MenuController::delete/$1',['filter' => 'permission:menu.delete']);
	// Permissions
	$routes->get('permissions',               'Admin\PermissionController::index',      ['filter' => 'permission:permission.view']);
	$routes->post('permissions/store',        'Admin\PermissionController::store',      ['filter' => 'permission:permission.create']);
	$routes->post('permissions/store-batch',  'Admin\PermissionController::storeBatch', ['filter' => 'permission:permission.create']);
	$routes->get('permissions/delete/(:num)', 'Admin\PermissionController::delete/$1',  ['filter' => 'permission:permission.delete']);
	// Activity Log
	$routes->get('activity-logs',        'Admin\ActivityLogController::index',   ['filter' => 'permission:activitylog.view']);
	$routes->post('activity-logs/data',  'Admin\ActivityLogController::getData', ['filter' => 'permission:activitylog.view']);
	$routes->get('activity-logs/export', 'Admin\ActivityLogController::export',  ['filter' => 'permission:activitylog.view']);
	// App Settings
	$routes->get('settings',                           'Admin\AppSettingController::index',        ['filter' => 'permission:setting.manage']);
	$routes->post('settings/update',                   'Admin\AppSettingController::update',       ['filter' => 'permission:setting.manage']);
	$routes->get('settings/delete-image/(:segment)',   'Admin\AppSettingController::deleteImage/$1',['filter' => 'permission:setting.manage']);
	$routes->post('settings/test-email',               'Admin\AppSettingController::testEmail',    ['filter' => 'permission:setting.manage']);
	$routes->post('settings/test-wa',                  'Admin\AppSettingController::testWa',       ['filter' => 'permission:setting.manage']);
	// Login Services
	$routes->get('login-services',              'Admin\LoginServiceController::index',     ['filter' => 'permission:setting.manage']);
	$routes->post('login-services/data',        'Admin\LoginServiceController::getData',   ['filter' => 'permission:setting.manage']);
	$routes->post('login-services/store',       'Admin\LoginServiceController::store',     ['filter' => 'permission:setting.manage']);
	$routes->post('login-services/update/(:num)','Admin\LoginServiceController::update/$1',['filter' => 'permission:setting.manage']);
	$routes->get('login-services/delete/(:num)','Admin\LoginServiceController::delete/$1', ['filter' => 'permission:setting.manage']);

	// =====================================================================
	// MASTER DATA PENGAWASAN
	// =====================================================================
	// Master Irban
	$routes->get('master/irban',                 'Admin\MasterIrbanController::index');
	$routes->get('master/irban/create',          'Admin\MasterIrbanController::create');
	$routes->post('master/irban/store',          'Admin\MasterIrbanController::store');
	$routes->get('master/irban/edit/(:num)',     'Admin\MasterIrbanController::edit/$1');
	$routes->post('master/irban/update/(:num)', 'Admin\MasterIrbanController::update/$1');
	$routes->post('master/irban/delete/(:num)', 'Admin\MasterIrbanController::delete/$1');

	// Master Entitas
	$routes->get('master/entitas',              'Admin\MasterEntitasController::index');
	$routes->post('master/entitas/data',        'Admin\MasterEntitasController::getData');
	$routes->post('master/entitas/store',       'Admin\MasterEntitasController::store');
	$routes->get('master/entitas/(:num)',       'Admin\MasterEntitasController::show/$1');
	$routes->post('master/entitas/update/(:num)','Admin\MasterEntitasController::update/$1');
	$routes->post('master/entitas/delete/(:num)','Admin\MasterEntitasController::delete/$1');

	// Master SDM
	$routes->get('master/sdm',                  'Admin\MasterSdmController::index');
	$routes->post('master/sdm/data',            'Admin\MasterSdmController::getData');
	$routes->post('master/sdm/store',           'Admin\MasterSdmController::store');
	$routes->get('master/sdm/(:num)',           'Admin\MasterSdmController::show/$1');
	$routes->post('master/sdm/update/(:num)',   'Admin\MasterSdmController::update/$1');
	$routes->post('master/sdm/delete/(:num)',   'Admin\MasterSdmController::delete/$1');
	$routes->get('master/sdm/sisa-hp/(:num)',   'Admin\MasterSdmController::sisaHp/$1');

	// =====================================================================
	// PKPT
	// =====================================================================
	$routes->get('pkpt/setting',                'Admin\PkptSettingController::index');
	$routes->post('pkpt/setting/store',         'Admin\PkptSettingController::store');
	$routes->post('pkpt/setting/delete/(:num)', 'Admin\PkptSettingController::delete/$1');

	$routes->get('pkpt',                            'Admin\PkptController::index');
	$routes->post('pkpt/buat',                      'Admin\PkptController::createOrGetPkpt');
	$routes->get('pkpt/(:num)',                     'Admin\PkptController::show/$1');
	$routes->get('pkpt/(:num)/kegiatan/create',    'Admin\PkptController::createKegiatan/$1');
	$routes->post('pkpt/(:num)/kegiatan/store',    'Admin\PkptController::storeKegiatan/$1');
	$routes->get('pkpt/kegiatan/edit/(:num)',       'Admin\PkptController::editKegiatan/$1');
	$routes->post('pkpt/kegiatan/update/(:num)',    'Admin\PkptController::updateKegiatan/$1');
	$routes->post('pkpt/kegiatan/delete/(:num)',    'Admin\PkptController::deleteKegiatan/$1');
	$routes->get('pkpt/sisa-hp',                    'Admin\PkptController::sisaHpSdm');

	// =====================================================================
	// SPT
	// =====================================================================
	$routes->get('spt',                         'Admin\SptController::index');
	$routes->get('spt/create/(:num)',           'Admin\SptController::create/$1');
	$routes->post('spt/store/(:num)',           'Admin\SptController::store/$1');
	$routes->get('spt/(:num)',                  'Admin\SptController::show/$1');
	$routes->get('spt/(:num)/edit',             'Admin\SptController::edit/$1');
	$routes->post('spt/(:num)/update',          'Admin\SptController::update/$1');
	$routes->post('spt/(:num)/ajukan',          'Admin\SptController::ajukan/$1');
	$routes->post('spt/(:num)/approve',         'Admin\SptController::approve/$1');
	$routes->post('spt/(:num)/reject',          'Admin\SptController::reject/$1');
	$routes->get('spt/(:num)/word',             'Admin\SptController::downloadWord/$1');
});
