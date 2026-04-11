# Scaffold Template — Cara Membuat Modul Baru

## File yang perlu di-copy

| File stub | Copy ke |
|---|---|
| `app/Models/_stubs/StubModel.php` | `app/Models/{NamaModul}Model.php` |
| `app/Controllers/Admin/_stubs/StubController.php` | `app/Controllers/Admin/{NamaModul}Controller.php` |
| `app/Views/admin/_stubs/index.php` | `app/Views/admin/{modul}/index.php` |

Setelah copy, **cari-ganti** di ketiga file:

| Cari | Ganti dengan |
|---|---|
| `StubController` | `{NamaModul}Controller` |
| `StubModel` | `{NamaModul}Model` |
| `stub` (huruf kecil) | `{nama_modul}` |
| `Stub` (kapital) | `{NamaModul}` |

---

## Tambahkan Route di `app/Config/Routes.php`

```php
// Di dalam group /admin dengan filter auth
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'auth'], function($routes) {
    // ...route lain...

    // ── {NamaModul} ──
    $routes->get(  '{modul}',               '{NamaModul}Controller::index');
    $routes->post( '{modul}/data',           '{NamaModul}Controller::getData');
    $routes->post( '{modul}/store',          '{NamaModul}Controller::store');
    $routes->get(  '{modul}/(:num)',         '{NamaModul}Controller::show/$1');
    $routes->post( '{modul}/update/(:num)',  '{NamaModul}Controller::update/$1');
    $routes->post( '{modul}/delete/(:num)',  '{NamaModul}Controller::delete/$1');
});
```

---

## Konvensi Wajib

### Tombol Aksi (DataTable `aksi` column)
```php
// ✅ Urutan: Detail (primary) → Edit (warning) → Hapus (danger)
'<button class="btn btn-xs btn-primary  btn-detail" data-id="'.$r['id'].'" title="Detail"><i class="fas fa-eye"></i></button> '
.'<button class="btn btn-xs btn-warning  btn-edit"   data-id="'.$r['id'].'" title="Edit"><i class="fas fa-edit"></i></button> '
.'<button class="btn btn-xs btn-danger   btn-delete" data-id="'.$r['id'].'" title="Hapus"><i class="fas fa-trash"></i></button>'
```

### Warna Badge Status
| Status | Class |
|---|---|
| Aktif / Selesai / Terbit | `badge-success` |
| Pending / Proses / Draft | `badge-warning` |
| Nonaktif / Ditolak | `badge-secondary` |
| Informasi | `badge-info` |
| Utama / Kode | `badge-primary` |
| Error / Hapus | `badge-danger` |

### DataTable (View)
```html
<!-- Wajib: data-url + data-dt di setiap th -->
<table id="dt-{modul}" data-url="/admin/{modul}/data" class="w-100">
    <thead><tr>
        <th class="dt-nosort dt-nosearch" data-dt="no"     width="40">#</th>
        <th                               data-dt="nama"             >Nama</th>
        <th class="dt-nosort"             data-dt="status"  width="90">Status</th>
        <th class="dt-nosort dt-nosearch" data-dt="aksi"   width="110">Aksi</th>
    </tr></thead>
    <tbody></tbody>
</table>
```

### Controller `getData()` — field `no` wajib ada
```php
foreach ($rows as $i => $r) {
    $data[] = [
        'no'   => $start + $i + 1,   // ← wajib untuk kolom #
        'nama' => esc($r['nama']),
        // ...
    ];
}
return $this->dtResponse($draw, $total, $filtered, $data);
```

### CSRF di JS — baca dari meta tag (bukan inline PHP)
```js
const csrfN = $('meta[name="csrf-token-name"]').attr('content');
const csrfH = $('meta[name="csrf-token"]').attr('content');

$.post(url, data + '&' + csrfN + '=' + csrfH, callback);
```

### logActivity — wajib di store/update/delete
```php
logActivity('{modul}.create', '{modul}', 'Tambah: ' . $nama);
logActivity('{modul}.update', '{modul}', 'Update: ' . $nama);
logActivity('{modul}.delete', '{modul}', 'Hapus: '  . $nama);
```

### RBAC — cek permission di index/store/update/delete
```php
// Di view:
<?php if(hasPermission('{modul}.create')): ?>
    <button ...>Tambah</button>
<?php endif; ?>

// Di controller:
private function isAdmin(): bool {
    return hasRole('superadmin') || hasRole('admin') || hasPermission('{modul}.manage_all');
}
```

---

## Struktur Folder yang Dihasilkan

```
app/
├── Controllers/Admin/
│   └── {NamaModul}Controller.php
├── Models/
│   └── {NamaModul}Model.php
└── Views/admin/
    └── {modul}/
        └── index.php         ← bisa tambah show.php, form.php jika perlu
```
