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

// ===================== DATATABLE =====================
class DataTable {
    constructor(tableId, options = {}) {
        this.table    = document.getElementById(tableId);
        this.options  = Object.assign({
            perPage: 10,
            searchable: true,
            sortable: true,
            exportable: true,
            pagination: true,
        }, options);

        if (!this.table) return;

        this.data       = [];
        this.filtered   = [];
        this.currentPage = 1;
        this.sortCol    = -1;
        this.sortDir    = 'asc';

        this._init();
    }

    _init() {
        // Ambil data dari tbody
        const rows = this.table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            this.data.push(row.cloneNode(true));
        });
        this.filtered = [...this.data];

        // Wrap table
        const wrapper = document.createElement('div');
        wrapper.className = 'dt-wrapper';
        this.table.parentNode.insertBefore(wrapper, this.table);

        // Toolbar
        wrapper.appendChild(this._buildToolbar());
        wrapper.appendChild(this.table);
        this.table.className += ' data-table';

        // Pagination
        if (this.options.pagination) {
            this.paginationEl = document.createElement('div');
            wrapper.appendChild(this.paginationEl);
        }

        // Sortable headers
        if (this.options.sortable) {
            this.table.querySelectorAll('thead th').forEach((th, i) => {
                th.classList.add('sortable');
                th.addEventListener('click', () => this._sort(i));
            });
        }

        this._render();
    }

    _buildToolbar() {
        const bar = document.createElement('div');
        bar.className = 'dt-toolbar';

        // Left: show per page
        const left = document.createElement('div');
        left.className = 'dt-toolbar-left';
        left.innerHTML = `
            <div class="dt-show">
                Tampilkan
                <select id="dt-perpage">
                    ${[5,10,25,50].map(n =>
                        `<option value="${n}" ${n===this.options.perPage?'selected':''}>${n}</option>`
                    ).join('')}
                </select>
                data
            </div>
        `;

        // Right: export + search
        const right = document.createElement('div');
        right.className = 'dt-toolbar-right';

        if (this.options.exportable) {
            right.innerHTML += `
                <div class="dt-export-btns">
                    <button class="btn-export excel" onclick="SIP.exportExcel()" data-tooltip="Export Excel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="btn-export pdf" onclick="window.print()" data-tooltip="Print">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            `;
        }

        if (this.options.searchable) {
            right.innerHTML += `
                <div class="dt-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="dt-search" placeholder="Cari...">
                </div>
            `;
        }

        bar.appendChild(left);
        bar.appendChild(right);

        // Events
        setTimeout(() => {
            const perpage = document.getElementById('dt-perpage');
            const search  = document.getElementById('dt-search');
            if (perpage) perpage.addEventListener('change', e => {
                this.options.perPage = parseInt(e.target.value);
                this.currentPage = 1;
                this._render();
            });
            if (search) search.addEventListener('input', e => {
                this._search(e.target.value);
            });
        }, 0);

        return bar;
    }

    _search(query) {
        const q = query.toLowerCase();
        this.filtered = q
            ? this.data.filter(row =>
                row.textContent.toLowerCase().includes(q))
            : [...this.data];
        this.currentPage = 1;
        this._render();
    }

    _sort(colIndex) {
        if (this.sortCol === colIndex) {
            this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortCol = colIndex;
            this.sortDir = 'asc';
        }

        this.filtered.sort((a, b) => {
            const aText = a.cells[colIndex]?.textContent.trim() || '';
            const bText = b.cells[colIndex]?.textContent.trim() || '';
            const aNum = parseFloat(aText);
            const bNum = parseFloat(bText);
            let cmp = isNaN(aNum) || isNaN(bNum)
                ? aText.localeCompare(bText)
                : aNum - bNum;
            return this.sortDir === 'asc' ? cmp : -cmp;
        });

        // Update header classes
        this.table.querySelectorAll('thead th').forEach((th, i) => {
            th.classList.remove('sort-asc', 'sort-desc');
            if (i === colIndex) th.classList.add(`sort-${this.sortDir}`);
        });

        this._render();
    }

    _render() {
        const start = (this.currentPage - 1) * this.options.perPage;
        const end   = start + this.options.perPage;
        const page  = this.filtered.slice(start, end);

        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';

        if (page.length === 0) {
            tbody.innerHTML = `
                <tr><td colspan="99" class="dt-empty">
                    <i class="fas fa-inbox"></i>
                    <p>Tidak ada data ditemukan</p>
                </td></tr>`;
        } else {
            page.forEach((row, i) => {
                const r = row.cloneNode(true);
                r.style.animation = `fadeIn .15s ease ${i * 30}ms both`;
                tbody.appendChild(r);
            });
        }

        if (this.options.pagination) this._renderPagination();
    }

    _renderPagination() {
        const total = this.filtered.length;
        const pages = Math.ceil(total / this.options.perPage);
        const start = Math.min((this.currentPage - 1) * this.options.perPage + 1, total);
        const end   = Math.min(this.currentPage * this.options.perPage, total);

        let pagesHtml = '';
        // Prev
        pagesHtml += `<button class="dt-page-btn" ${this.currentPage===1?'disabled':''} onclick="this.closest('.dt-wrapper')._dt.goPage(${this.currentPage-1})">
            <i class="fas fa-chevron-left" style="font-size:11px"></i></button>`;

        // Page numbers
        for (let i = 1; i <= pages; i++) {
            if (pages > 7 && i > 2 && i < pages - 1 && Math.abs(i - this.currentPage) > 1) {
                if (i === 3 || i === pages - 2) pagesHtml += `<span style="padding:0 4px;color:#94a3b8">...</span>`;
                continue;
            }
            pagesHtml += `<button class="dt-page-btn ${i===this.currentPage?'active':''}"
                onclick="this.closest('.dt-wrapper')._dt.goPage(${i})">${i}</button>`;
        }

        // Next
        pagesHtml += `<button class="dt-page-btn" ${this.currentPage===pages?'disabled':''} onclick="this.closest('.dt-wrapper')._dt.goPage(${this.currentPage+1})">
            <i class="fas fa-chevron-right" style="font-size:11px"></i></button>`;

        this.paginationEl.className = 'dt-pagination';
        this.paginationEl.innerHTML = `
            <div class="dt-info">
                Menampilkan ${total === 0 ? 0 : start}–${end} dari ${total} data
            </div>
            <div class="dt-pages">${pagesHtml}</div>
        `;

        // Attach instance ke wrapper
        this.table.closest('.dt-wrapper')._dt = this;
    }

    goPage(page) {
        const pages = Math.ceil(this.filtered.length / this.options.perPage);
        if (page < 1 || page > pages) return;
        this.currentPage = page;
        this._render();
    }

    reload() {
        this.filtered = [...this.data];
        this.currentPage = 1;
        this._render();
    }
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
    // Init semua DataTable otomatis
    document.querySelectorAll('[data-datatable]').forEach(table => {
        new DataTable(table.id);
    });

    // Init tabs otomatis
    initTabs();

    // Flash messages dari window variable
    if (window._flashSuccess) SIP.success(window._flashSuccess);
    if (window._flashError)   SIP.error(window._flashError);

    // AJAX Delete otomatis untuk .btn-delete
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const row = $(this).closest('tr');

        SIP.confirmDelete(null, function() {
            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        row.fadeOut(300, function() { $(this).remove(); });
                        SIP.success(res.message || 'Data berhasil dihapus!');
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
