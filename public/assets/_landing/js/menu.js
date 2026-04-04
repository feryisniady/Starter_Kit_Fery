const table = $('#menuTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: baseURL + "sip_new/menu/get_data_menu", // ⬅️ Ganti ini!!
        type: "POST"
    },
    columns: [
        { data: 'no', className: 'text-center', orderable: false },
        { data: "nama_menu" },
        { data: "url" },
        { data: "icon" },
        { data: "is_active", className: "text-center" },
        { data: "aksi", orderable: false, searchable: false, className: "text-center" }
        ],
        order: [[0, 'asc']] // urut berdasarkan id_menu DESC
});


// TOMBOL TAMBAH MENU
$("#formTambahMenu").submit(function(e) {
        e.preventDefault(); // Mencegah reload halaman

        if (typeof Swal === "undefined") {
            console.error("SweetAlert2 belum dimuat!");
            alert("SweetAlert2 tidak ditemukan. Pastikan script sudah dimuat.");
            return;
        }

        const formData = new FormData(this); // Ambil data form termasuk file

        // Ambil nilai input untuk validasi
        const nama_menu   = $("#nama_menu").val();
        const url    = $("#url").val();
        const icon   = $("#icon").val();
        const status = $("select[name='status']").val();

        // Ambil nilai status dari radio button
        // var status = $("input[name='status']:checked").val();

        // Validasi input sebelum submit
        if (!nama_menu || !url || !icon || !status) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: 'Semua kolom wajib diisi sebelum menyimpan!',
                timer: 2500
            });
            return; // Hentikan proses jika ada input kosong
        }

        
        $.ajax({
            url: baseURL + "sip_new/menu/simpan",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            beforeSend: function() {
                $("#btnSimpan").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                if (response.status === "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data berhasil disimpan!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $("#modalMenu").modal("hide"); // Tutup modal
                        $("#formTambahMenu")[0].reset(); // Reset form
                        $("#menuTable").DataTable().ajax.reload(null, false);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText); // Debugging error response
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan, coba lagi! (' + error + ')'
                });
            },
            complete: function() {
                $("#btnSimpan").prop("disabled", false).html("Simpan");
            }
        });
    });

// Tombol Edit klik
/*$('.edit-akses').click(function () {
    var is_active = $(this).data('id');
    $('#modalMenuEdit').modal('show');
    $('#is_active').val(is_active).trigger('change');
});*/

// TOMBOL EDIT
$('#menuTable').on('click', '.edit-menu', function () {
    let id = $(this).data('id');
    $.getJSON(baseURL + 'sip_new/edit/' + id, function (data) {
        $('#modalMenuEdit input[name="id_menu"]').val(data.id_menu);
        $('#modalMenuEdit input[name="nama_menu"]').val(data.nama_menu);
        $('#modalMenuEdit input[name="url"]').val(data.url);
        $('#modalMenuEdit input[name="icon"]').val(data.icon);
        $('#modalMenuEdit select[name="is_active"]').val(data.is_active);
        $('#modalMenuEdit').modal('show');
    });
});

$('#formEditMenu').submit(function (e) {
    e.preventDefault();
    $.ajax({
        url: baseURL + 'sip_new/update',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (res) {
            if (res.status === true) {
                $('#modalMenuEdit').modal('hide');
                $('#menuTable').DataTable().ajax.reload(null, false);
                Swal.fire('Sukses', 'Menu berhasil diupdate!', 'success');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
});

// TOMBOL HAPUS
$('#menuTable').on('click', '.hapus-menu', function () {
    let id = $(this).data('id');
    Swal.fire({
        title: 'Hapus Menu?',
        text: 'Menu akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!',
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(baseURL + 'sip_new/hapus/' + id, function (res) {
                let result = JSON.parse(res);
                if (result.status === true) {
                    $('#menuTable').DataTable().ajax.reload(null, false);
                    Swal.fire('Dihapus!', result.message, 'success');
                } else {
                    Swal.fire('Gagal!', result.message, 'error');
                }
            });
        }
    });
});

/*$('#menu_url').select2({
        dropdownParent: $('#formTambahMenu'), // kalau modal
        placeholder: "Pilih URL controller",
        ajax: {
            url: baseURL + 'menu/get-controller-methods',
            type: "GET",
            dataType: "json",
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return { id: item, text: item };
                    })
                };
            }
        }
    });
*/


/*$("#formAssignMenu").submit(function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: baseURL + "superadmin/menu/assign_role_menu",
        type: "POST",
        data: formData,
        dataType: "json",
        beforeSend: function () {
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        },
        success: function (res) {
            if (res.status === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Hak akses berhasil disimpan!'
                });
                $('#modalAssignMenu').modal('hide');
            }
        },
        error: function () {
            Swal.fire("Error", "Terjadi kesalahan saat menyimpan", "error");
        }
    });
});*/

