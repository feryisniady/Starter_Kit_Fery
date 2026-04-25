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
	$routes->post('master/irban/data',           'Admin\MasterIrbanController::getData');
	$routes->get('master/irban/create',          'Admin\MasterIrbanController::create');
	$routes->post('master/irban/store',          'Admin\MasterIrbanController::store');
	$routes->get('master/irban/edit/(:num)',     'Admin\MasterIrbanController::edit/$1');
	$routes->post('master/irban/update/(:num)', 'Admin\MasterIrbanController::update/$1');
	$routes->post('master/irban/delete/(:num)', 'Admin\MasterIrbanController::delete/$1');

	// Master Entitas
	$routes->get('master/entitas',              'Admin\MasterEntitasController::index');
	$routes->post('master/entitas/data',        'Admin\MasterEntitasController::getData');
	$routes->post('master/entitas/store',       'Admin\MasterEntitasController::store');
	$routes->get('master/entitas/users',        'Admin\MasterEntitasController::getUsers');
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
	// Header PKPT (dokumen SK per tahun)
	$routes->get('pkpt/setting',                  'Admin\PkptSettingController::index');
	$routes->post('pkpt/setting/store',           'Admin\PkptSettingController::store');
	$routes->post('pkpt/setting/approve/(:num)',  'Admin\PkptSettingController::approve/$1');
	$routes->post('pkpt/setting/revok/(:num)',    'Admin\PkptSettingController::revok/$1');
	$routes->post('pkpt/setting/delete/(:num)',   'Admin\PkptSettingController::delete/$1');

	// Hari Libur
	$routes->get('pkpt/hari-libur',               'Admin\HariLiburController::index');
	$routes->post('pkpt/hari-libur/store',        'Admin\HariLiburController::store');
	$routes->post('pkpt/hari-libur/store-batch',  'Admin\HariLiburController::storeBatch');
	$routes->post('pkpt/hari-libur/delete/(:num)','Admin\HariLiburController::delete/$1');
	$routes->get('pkpt/hari-libur/hitung-hp',     'Admin\HariLiburController::hitungHp');
	$routes->post('pkpt/hari-libur/data',         'Admin\HariLiburController::getData');

	$routes->get('pkpt',                            'Admin\PkptController::index');
	$routes->get('pkpt/hp-monitor',                 'Admin\PkptController::hpMonitor');
	$routes->get('pkpt/monitoring-sdm',             'Admin\PkptController::monitoringSdm');
	$routes->get('pkpt/monitoring-sdm/detail',      'Admin\PkptController::monitoringSdmDetail');
	$routes->post('pkpt/data',                      'Admin\PkptController::getData');
	$routes->post('pkpt/buat',                      'Admin\PkptController::createOrGetPkpt');
	$routes->get('pkpt/(:num)',                     'Admin\PkptController::show/$1');
	$routes->post('pkpt/(:num)/status',             'Admin\PkptController::updateStatus/$1');
	$routes->get('pkpt/(:num)/kegiatan/create',    'Admin\PkptController::createKegiatan/$1');
	$routes->post('pkpt/(:num)/kegiatan/store',    'Admin\PkptController::storeKegiatan/$1');
	$routes->post('pkpt/(:num)/kegiatan/data',      'Admin\PkptController::getDataKegiatan/$1');
	$routes->get('pkpt/kegiatan/view/(:num)',       'Admin\PkptController::viewKegiatan/$1');
	$routes->get('pkpt/kegiatan/edit/(:num)',       'Admin\PkptController::editKegiatan/$1');
	$routes->post('pkpt/kegiatan/update/(:num)',    'Admin\PkptController::updateKegiatan/$1');
	$routes->post('pkpt/kegiatan/delete/(:num)',    'Admin\PkptController::deleteKegiatan/$1');
	$routes->get('pkpt/sisa-hp',                    'Admin\PkptController::sisaHpSdm');

	// =====================================================================
	// SPT
	// =====================================================================
	$routes->get('spt',                         'Admin\SptController::index');
	$routes->post('spt/data',                   'Admin\SptController::getData');
	$routes->get('spt/non-pkpt/create',         'Admin\SptController::createNonPkpt');
	$routes->post('spt/non-pkpt/store',         'Admin\SptController::storeNonPkpt');
	$routes->get('spt/create/(:num)',           'Admin\SptController::create/$1');
	$routes->post('spt/store/(:num)',           'Admin\SptController::store/$1');
	$routes->get('spt/(:num)',                  'Admin\SptController::show/$1');
	$routes->get('spt/(:num)/edit',             'Admin\SptController::edit/$1');
	$routes->post('spt/(:num)/update',          'Admin\SptController::update/$1');
	$routes->post('spt/(:num)/ajukan',          'Admin\SptController::ajukan/$1');
	$routes->post('spt/(:num)/approve',         'Admin\SptController::approve/$1');
	$routes->post('spt/(:num)/reject',          'Admin\SptController::reject/$1');
	$routes->post('spt/(:num)/revisi',          'Admin\SptController::revisi/$1');
	$routes->get('spt/(:num)/word',             'Admin\SptController::downloadWord/$1');

	// =====================================================================
	// PKA (Program Pengawasan) — per SPT
	// =====================================================================
	$routes->get('spt/(:num)/pka',                 'Admin\PkaController::index/$1');
	$routes->get('spt/(:num)/pka/print-km6',        'Admin\PkaController::printKm6/$1');
	$routes->post('spt/(:num)/pka/store',          'Admin\PkaController::store/$1');
	$routes->post('spt/(:num)/pka/apply-template', 'Admin\PkaController::applyTemplate/$1');
	$routes->post('spt/pka/update/(:num)',          'Admin\PkaController::update/$1');
	$routes->post('spt/pka/delete/(:num)',          'Admin\PkaController::delete/$1');
	$routes->post('spt/pka/selesai/(:num)',         'Admin\PkaController::selesai/$1');

	// =====================================================================
	// PKA Template Library
	// =====================================================================
	$routes->get('pka-template',                    'Admin\PkaTemplateController::index');
	$routes->get('pka-template/create',             'Admin\PkaTemplateController::create');
	$routes->post('pka-template/store',             'Admin\PkaTemplateController::store');
	$routes->get('pka-template/(:num)/edit',        'Admin\PkaTemplateController::edit/$1');
	$routes->post('pka-template/(:num)/update',     'Admin\PkaTemplateController::update/$1');
	$routes->post('pka-template/(:num)/delete',     'Admin\PkaTemplateController::delete/$1');

	// =====================================================================
	// TEMUAN — per SPT
	// =====================================================================
	$routes->get('spt/(:num)/temuan',           'Admin\TemuanController::index/$1');
	$routes->get('spt/(:num)/temuan/create',    'Admin\TemuanController::create/$1');
	$routes->post('spt/(:num)/temuan/store',    'Admin\TemuanController::store/$1');
	$routes->get('spt/temuan/(:num)',           'Admin\TemuanController::show/$1');
	$routes->get('spt/temuan/(:num)/edit',      'Admin\TemuanController::edit/$1');
	$routes->post('spt/temuan/(:num)/update',   'Admin\TemuanController::update/$1');
	$routes->post('spt/temuan/(:num)/delete',   'Admin\TemuanController::delete/$1');

	// =====================================================================
	// MASTER KODE TEMUAN
	// =====================================================================
	$routes->get('master/kode-temuan',          'Admin\KodetemuanController::index');
	$routes->post('master/kode-temuan/data',    'Admin\KodetemuanController::getData');

	// =====================================================================
	// KM — Kendali Mutu (per SPT)
	// =====================================================================
	$routes->get('spt/(:num)/km',                         'Admin\KmController::index/$1');
	// KM-1: Peta Pengawasan
	$routes->get('spt/(:num)/km/1',                       'Admin\KmController::km1/$1');
	$routes->post('spt/(:num)/km/1/save',                 'Admin\KmController::saveKm1/$1');
	// KM-2: Anggaran Waktu
	$routes->get('spt/(:num)/km/2',                       'Admin\KmController::anggaranWaktu/$1');
	$routes->get('spt/(:num)/km/2/print',                 'Admin\KmController::printKm4Aw/$1');
	$routes->post('spt/(:num)/km/2/save',                 'Admin\KmController::saveAnggaranWaktu/$1');
	$routes->post('spt/(:num)/km/2/verifikasi',           'Admin\KmController::verifikasiAw/$1');
	$routes->get('spt/(:num)/km/anggaran-waktu',          'Admin\KmController::anggaranWaktu/$1');   // backward compat
	$routes->post('spt/(:num)/km/anggaran-waktu/save',    'Admin\KmController::saveAnggaranWaktu/$1');
	// KM-3: Dokumen SPT (auto-prefill)
	$routes->get('spt/(:num)/km/3',                       'Admin\KmController::km3/$1');
	// KM-4: Lembar Perencanaan (form pendukung PKA)
	$routes->get('spt/(:num)/km/4',                       'Admin\KmController::km4/$1');
	$routes->post('spt/(:num)/km/4/save',                 'Admin\KmController::saveKm4/$1');
	// KM-5: Reviu PKA
	$routes->get('spt/(:num)/km/5',                       'Admin\KmController::km5/$1');
	$routes->post('spt/(:num)/km/5/save',                 'Admin\KmController::saveKm5/$1');
	// KM-5b: Entry Meeting
	$routes->get('spt/(:num)/km/5b',                      'Admin\KmController::km5b/$1');
	$routes->post('spt/(:num)/km/5b/save',                'Admin\KmController::saveKm5b/$1');
	$routes->get('spt/(:num)/km/6',                       'Admin\KmController::km5b/$1');   // backward compat
	$routes->post('spt/(:num)/km/6/save',                 'Admin\KmController::saveKm5b/$1');
	// Independensi
	$routes->get('spt/(:num)/km/independensi',            'Admin\KmController::independensi/$1');
	$routes->post('spt/(:num)/km/independensi/save',      'Admin\KmController::saveIndepensi/$1');
	// KM-10: Exit Meeting
	$routes->get('spt/(:num)/km/10',                      'Admin\KmController::km10/$1');
	$routes->post('spt/(:num)/km/10/save',                'Admin\KmController::saveKm10/$1');
	// KM-11: Reviu Laporan
	$routes->get('spt/(:num)/km/11',                      'Admin\KmController::km11/$1');
	$routes->post('spt/(:num)/km/11/save',                'Admin\KmController::saveKm11/$1');

	// =====================================================================
	// KKA — Kertas Kerja Audit
	// =====================================================================
	// Dashboard per SPT (KT/Dalnis lihat semua; AT redirect ke KKA-nya)
	$routes->get('spt/(:num)/kka',                        'Admin\KkaController::index/$1');

	// KT Compiled View — rekapitulasi semua simpulan AT
	$routes->get('spt/(:num)/kka/compiled',               'Admin\KkaController::compiled/$1');

	// Detail + isi KKA (show satu KKA per AT)
	$routes->get('kka/(:num)',                            'Admin\KkaController::show/$1');

	// Ikhtisar
	$routes->post('kka/(:num)/ikhtisar/store',            'Admin\KkaController::storeIkhtisar/$1');
	$routes->post('kka/ikhtisar/(:num)/update',           'Admin\KkaController::updateIkhtisar/$1');
	$routes->post('kka/ikhtisar/(:num)/delete',           'Admin\KkaController::deleteIkhtisar/$1');
	$routes->post('kka/(:num)/ikhtisar/selesai',          'Admin\KkaController::selesaiIkhtisar/$1');

	// Simpulan
	$routes->post('kka/(:num)/simpulan/store',            'Admin\KkaController::storeSimpulan/$1');
	$routes->post('kka/simpulan/(:num)/update',           'Admin\KkaController::updateSimpulan/$1');
	$routes->post('kka/simpulan/(:num)/delete',           'Admin\KkaController::deleteSimpulan/$1');
	$routes->post('kka/(:num)/simpulan/selesai',          'Admin\KkaController::selesaiSimpulan/$1');

	// Rekomendasi
	$routes->post('kka/(:num)/rekomendasi/store',         'Admin\KkaController::storeRekomendasi/$1');
	$routes->post('kka/rekomendasi/(:num)/update',        'Admin\KkaController::updateRekomendasi/$1');
	$routes->post('kka/rekomendasi/(:num)/delete',        'Admin\KkaController::deleteRekomendasi/$1');
	$routes->post('kka/(:num)/rekomendasi/selesai',       'Admin\KkaController::selesaiRekomendasi/$1');

	// Unified save per prosedur (alur baru)
	$routes->post('kka/(:num)/prosedur/save',             'Admin\KkaController::saveProsedur/$1');
	$routes->post('kka/(:num)/selesaikan',                'Admin\KkaController::selesaikanKka/$1');

	// Submit / Review KKA (AT → KT)
	$routes->post('kka/(:num)/submit',                    'Admin\KkaController::submitKka/$1');
	$routes->post('kka/(:num)/approve',                   'Admin\KkaController::approveKka/$1');
	$routes->post('kka/(:num)/reject',                    'Admin\KkaController::rejectKka/$1');
	$routes->post('kka/(:num)/reopen',                    'Admin\KkaController::reopenKka/$1');

	// Catatan Dalnis
	$routes->post('kka/(:num)/catatan-dalnis',            'Admin\KkaController::saveCatatanDalnis/$1');

	// KKA Dokumen Bukti per Prosedur
	$routes->get( 'kka/dokumen/(:num)/download',          'Admin\KkaController::downloadDokumen/$1');
	$routes->post('kka/dokumen/(:num)/hapus',             'Admin\KkaController::hapusDokumen/$1');

	// =====================================================================
	// NHP — Notisi Hasil Pemeriksaan
	// =====================================================================
	$routes->get( 'spt/(:num)/nhp',                                  'Admin\NhpController::index/$1');
	$routes->get( 'spt/(:num)/nhp/create',                           'Admin\NhpController::create/$1');
	$routes->post('spt/(:num)/nhp/store',                            'Admin\NhpController::store/$1');
	$routes->get( 'spt/(:num)/nhp/(:num)',                           'Admin\NhpController::show/$1/$2');
	$routes->post('spt/(:num)/nhp/(:num)/kirim',                     'Admin\NhpController::kirim/$1/$2');
	$routes->post('spt/(:num)/nhp/(:num)/selesai',                   'Admin\NhpController::selesai/$1/$2');
	$routes->post('spt/(:num)/nhp/(:num)/item/add',                  'Admin\NhpController::addItem/$1/$2');
	$routes->post('spt/(:num)/nhp/(:num)/item/(:num)/tanggapi',      'Admin\NhpController::tanggapi/$1/$2/$3');
	$routes->get( 'spt/(:num)/nhp/matriks',                         'Admin\NhpController::matriks/$1');
	$routes->get( 'nhp/item-dokumen/(:num)/download',                'Admin\NhpController::downloadItemDokumen/$1');

	// =====================================================================
	// Tindak Lanjut — Verifikasi oleh BPKP
	// =====================================================================
	$routes->get( 'tl',                                              'Admin\TlVerifikasiController::index');
	$routes->get( 'tl/(:num)',                                       'Admin\TlVerifikasiController::show/$1');
	$routes->post('tl/(:num)/verifikasi',                            'Admin\TlVerifikasiController::verifikasi/$1');
	$routes->get( 'tl/dokumen/(:num)/download',                      'Admin\TlVerifikasiController::downloadDokumen/$1');

	// Demo / Simulasi
	$routes->get('demo/km', 'Admin\DemoController::kmWorkflow');

});

// =====================================================================
// PORTAL AUDITI — Akses entitas/OPD yang diaudit
// =====================================================================
$routes->group('auditi', ['filter' => 'auditi', 'namespace' => 'App\Controllers\Auditi'], function($routes) {
	// Dashboard
	$routes->get('dashboard',  'DashboardController::index');

	// NHP — Notisi Hasil Pemeriksaan
	$routes->get( 'nhp',                                     'NhpController::index');
	$routes->get( 'nhp/(:num)',                              'NhpController::show/$1');
	$routes->post('nhp/(:num)/item/(:num)/simpan',           'NhpController::simpanItem/$1/$2');
	$routes->post('nhp/(:num)/item/(:num)/upload',           'NhpController::uploadDokumen/$1/$2');
	$routes->post('nhp/dokumen/(:num)/hapus',                'NhpController::hapusDokumen/$1');
	$routes->get( 'nhp/dokumen/(:num)/download',             'NhpController::downloadDokumen/$1');

	// Tindak Lanjut
	$routes->get( 'tl',                                      'TlController::index');
	$routes->get( 'tl/(:num)',                               'TlController::show/$1');
	$routes->post('tl/(:num)/kirim',                         'TlController::kirim/$1');
	$routes->get( 'tl/dokumen/(:num)/download',              'TlController::downloadDokumen/$1');
});
