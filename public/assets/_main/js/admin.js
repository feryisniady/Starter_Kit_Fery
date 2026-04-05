/**
 * SIP Inspektorat — Admin JS
 * Modal, DataTable, SweetAlert Helpers, Tabs, Dropdown
 */

// ===================== MODAL =====================
const Modal = {
    open(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    },
    close(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('show');
            document.body.style.overflow = '';
        }
    },
    closeAll() {
        document.querySelectorAll('.modal-overlay.show').forEach(m => {
            m.classList.remove('show');
        });
        document.body.style.overflow = '';
    }
};

// Close modal saat klik overlay
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// Close modal saat tekan ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') Modal.closeAll();
});

// ===================== DATATABLE SERVER-SIDE (DataTables.js) =====================

// Registry instance DT per table ID
const DT_INSTANCES   = {};
// Extra params per table (untuk filter tambahan)
const DT_EXTRA_PARAMS = {};

// Bahasa Indonesia untuk DataTables
const DT_LANG_ID = {
    processing:      '<span style="font-size:13px;color:#64748b"><i class="fas fa-spinner fa-spin"></i> Memuat data...</span>',
    search:          '',
    searchPlaceholder: 'Cari...',
    lengthMenu:      'Tampilkan _MENU_ data',
    zeroRecords:     '<div style="text-align:center;padding:32px;color:#94a3b8"><i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4"></i>Tidak ada data ditemukan</div>',
    emptyTable:      '<div style="text-align:center;padding:32px;color:#94a3b8"><i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4"></i>Belum ada data</div>',
    info:            'Menampilkan _START_\u2013_END_ dari _TOTAL_ data',
    infoEmpty:       'Menampilkan 0 data',
    infoFiltered:    '(difilter dari _MAX_ total)',
    paginate: {
        first:    '<i class="fas fa-angles-left"></i>',
        previous: '<i class="fas fa-chevron-left"></i>',
        next:     '<i class="fas fa-chevron-right"></i>',
        last:     '<i class="fas fa-angles-right"></i>',
    },
};

/**
 * Init semua tabel dengan atribut data-url sebagai DataTables server-side.
 * Dipanggil otomatis saat DOMContentLoaded.
 */
function initServerDT() {
    $('table[data-url]').each(function() {
        var $tbl = $(this);
        var id   = this.id;
        if (!id) return; // tabel harus punya id

        if ($.fn.DataTable && $.fn.DataTable.isDataTable($tbl)) return;

        var url  = this.getAttribute('data-url');
        var cols = [];
        $tbl.find('thead th').each(function() {
            cols.push({
                orderable:  !this.classList.contains('dt-nosort'),
                searchable: !this.classList.contains('dt-nosearch'),
            });
        });

        DT_INSTANCES[id] = $tbl.DataTable({
            processing:  true,
            serverSide:  true,
            ajax: {
                url:  url,
                type: 'POST',
                data: function(d) {
                    // CSRF token
                    var csrfName  = $('meta[name="csrf-token-name"]').attr('content')  || 'csrf_token';
                    var csrfHash  = $('meta[name="csrf-token"]').attr('content') || '';
                    d[csrfName]   = csrfHash;
                    // Extra filter params
                    var extra = DT_EXTRA_PARAMS[id] || {};
                    return $.extend({}, d, extra);
                },
                error: function(xhr) {
                    if (xhr.status === 403) SIP.error('Sesi habis, silakan login ulang.');
                    else SIP.error('Gagal memuat data. Silakan refresh halaman.');
                }
            },
            columns:    cols,
            language:   DT_LANG_ID,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom:        '<"dt-top"lf>rt<"dt-bottom"ip>',
            responsive: false,
        });
    });
}

/**
 * Reload DataTable tanpa reset ke halaman 1.
 */
function dtReload(tableId) {
    if (DT_INSTANCES[tableId]) {
        DT_INSTANCES[tableId].ajax.reload(null, false);
    }
}

/**
 * Print semua data (fetch ulang dengan length=-1) dari endpoint yang sama.
 */
function dtPrint(tableId) {
    var dt = DT_INSTANCES[tableId];
    if (!dt) return;

    var url    = $('table#'+tableId).attr('data-url');
    var search = dt.search();
    var order  = dt.order();
    var headers = [];
    $('table#'+tableId+' thead th').each(function() {
        if (!this.classList.contains('dt-nosearch')) {
            headers.push(this.textContent.trim());
        }
    });

    SIP.loading('Menyiapkan data cetak...');

    var csrfName = $('meta[name="csrf-token-name"]').attr('content') || 'csrf_token';
    var csrfHash = $('meta[name="csrf-token"]').attr('content') || '';
    var postData = {
        draw: 1, start: 0, length: -1,
        'search[value]': search,
        'order[0][column]': order[0] ? order[0][0] : 0,
        'order[0][dir]':    order[0] ? order[0][1] : 'asc',
    };
    postData[csrfName] = csrfHash;

    $.post(url, postData, function(res) {
        SIP.close();
        var rows  = res.data || [];
        var title = document.title;

        var html = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>'+title+'</title>'
            +'<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;font-size:12px;color:#111;padding:20px}'
            +'h2{font-size:15px;margin-bottom:4px}.meta{font-size:11px;color:#555;margin-bottom:16px}'
            +'table{width:100%;border-collapse:collapse}thead{background:#1a3c6e;color:white}'
            +'thead th{padding:8px 10px;text-align:left;font-size:11px;font-weight:600}'
            +'tbody tr:nth-child(even){background:#f5f7fa}'
            +'tbody td{padding:7px 10px;border-bottom:1px solid #e2e8f0;vertical-align:top}'
            +'tfoot td{padding:8px 10px;font-size:11px;color:#555;border-top:2px solid #1a3c6e}'
            +'@media print{body{padding:0}}</style></head><body>'
            +'<h2>'+title+'</h2>'
            +'<div class="meta">Total data: '+res.recordsFiltered+'&nbsp;|&nbsp;Dicetak: '+new Date().toLocaleString('id-ID')+'</div>'
            +'<table><thead><tr>';

        // Semua header
        $('table#'+tableId+' thead th').each(function() {
            html += '<th>'+this.textContent.trim()+'</th>';
        });
        html += '</tr></thead><tbody>';

        if (rows.length === 0) {
            html += '<tr><td colspan="99" style="text-align:center;padding:20px;color:#888">Tidak ada data</td></tr>';
        } else {
            rows.forEach(function(row) {
                html += '<tr>';
                row.forEach(function(cell) {
                    // Strip HTML tags untuk print
                    var tmp = document.createElement('div');
                    tmp.innerHTML = cell;
                    html += '<td>'+(tmp.textContent||tmp.innerText||'')+'</td>';
                });
                html += '</tr>';
            });
        }
        html += '</tbody><tfoot><tr><td colspan="99">Total: '+rows.length+' data</td></tr></tfoot></table>'
             +'<script>window.onload=function(){window.print();window.onafterprint=function(){window.close();}}<\/script>'
             +'</body></html>';

        var win = window.open('', '_blank', 'width=900,height=650');
        win.document.write(html);
        win.document.close();
    }).fail(function() {
        SIP.close();
        SIP.error('Gagal memuat data untuk print.');
    });
}

// ===================== TABS =====================
function initTabs(containerSelector) {
    const containers = document.querySelectorAll(containerSelector || '.tabs-container');
    containers.forEach(container => {
        const tabs     = container.querySelectorAll('.tab-item');
        const contents = container.querySelectorAll('.tab-content');

        tabs.forEach((tab, i) => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                if (contents[i]) contents[i].classList.add('active');
            });
        });

        // Aktifkan tab pertama
        if (tabs[0]) tabs[0].click();
    });
}

// ===================== DROPDOWN =====================
document.addEventListener('click', function(e) {
    const trigger = e.target.closest('[data-dropdown]');
    if (trigger) {
        const menuId = trigger.dataset.dropdown;
        const menu   = document.getElementById(menuId);
        if (menu) {
            // Tutup semua dropdown lain
            document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
            e.stopPropagation();
        }
    } else {
        document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
    }
});

// ===================== SWEETALERT HELPERS =====================
const SIP = {
    // Toast sukses
    success(msg) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: msg,
            toast: true,
            position: 'top-end',
            timer: 2500,
            showConfirmButton: false,
            timerProgressBar: true,
        });
    },

    // Toast error
    error(msg) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: msg,
        });
    },

    // Toast warning
    warning(msg) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian!',
            text: msg,
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false,
        });
    },

    // Konfirmasi hapus
    confirmDelete(url, callback) {
        Swal.fire({
            icon: 'warning',
            title: 'Yakin hapus?',
            text: 'Data yang dihapus tidak bisa dikembalikan!',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) {
                if (typeof callback === 'function') {
                    callback();
                } else if (url) {
                    window.location.href = url;
                }
            }
        });
    },

    // Konfirmasi aksi custom
    confirm(options) {
        return Swal.fire({
            icon: options.icon || 'question',
            title: options.title || 'Konfirmasi',
            text: options.text || 'Lanjutkan?',
            showCancelButton: true,
            confirmButtonColor: options.confirmColor || '#1a3c6e',
            cancelButtonColor: '#64748b',
            confirmButtonText: options.confirmText || 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
        });
    },

    // Loading
    loading(msg) {
        Swal.fire({
            title: msg || 'Memproses...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
    },

    // Close
    close() { Swal.close(); },

    // Export table to Excel (simple)
    exportExcel() {
        const table = document.querySelector('.data-table');
        if (!table) return;
        let csv = '';
        table.querySelectorAll('tr').forEach(row => {
            const cols = [...row.querySelectorAll('th, td')].map(c =>
                '"' + c.textContent.trim().replace(/"/g, '""') + '"'
            );
            csv += cols.join(',') + '\n';
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url;
        a.download = 'data-export.csv';
        a.click();
        URL.revokeObjectURL(url);
        SIP.success('Data berhasil diekspor!');
    }
};

// ===================== SWITCH TOGGLE =====================
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('switch-input')) {
        const label = e.target.closest('.switch');
        if (label) {
            const track = label.querySelector('.switch-track');
            if (track) {
                track.style.background = e.target.checked ? '#2563eb' : '#cbd5e1';
            }
        }
    }
});

// ===================== FORM VALIDATION =====================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let valid = true;
    form.querySelectorAll('[required]').forEach(field => {
        field.classList.remove('is-invalid');
        const errorEl = field.nextElementSibling;
        if (errorEl && errorEl.classList.contains('invalid-feedback')) {
            errorEl.style.display = 'none';
        }

        if (!field.value.trim()) {
            valid = false;
            field.classList.add('is-invalid');
            if (errorEl && errorEl.classList.contains('invalid-feedback')) {
                errorEl.style.display = 'block';
            }
        }
    });

    if (!valid) {
        SIP.error('Harap lengkapi semua field yang wajib diisi!');
        // Focus ke field pertama yang error
        const first = form.querySelector('.is-invalid');
        if (first) first.focus();
    }
    return valid;
}

// ===================== INIT =====================
document.addEventListener('DOMContentLoaded', function() {
    // Init DataTables.js server-side
    initServerDT();

    // Init tabs
    initTabs();

    // Flash messages
    if (window._flashSuccess) SIP.success(window._flashSuccess);
    if (window._flashError)   SIP.error(window._flashError);

    // AJAX Delete — reload DataTable jika ada, fallback fadeOut
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var url   = $(this).data('url');
        var $row  = $(this).closest('tr');
        var dtId  = $row.closest('table').attr('id');

        SIP.confirmDelete(null, function() {
            $.ajax({
                url:  url,
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        SIP.success(res.message || 'Data berhasil dihapus!');
                        if (dtId && DT_INSTANCES[dtId]) {
                            DT_INSTANCES[dtId].ajax.reload(null, false);
                        } else {
                            $row.fadeOut(300, function() { $(this).remove(); });
                        }
                    } else {
                        SIP.error(res.message || 'Gagal menghapus data.');
                    }
                },
                error: function() {
                    SIP.error('Terjadi kesalahan pada server.');
                }
            });
        });
    });
});
