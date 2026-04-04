$(document).ready(function () {
    var baseURL = window.baseURL || ''; // kalau baseURL global

    // Tampilkan modal kosong
    $('#modalAssignMenu').on('show.bs.modal', function () {
        $('#formAssignMenu')[0].reset();
        $('#id_roles').val('').trigger('change');
        $('input[type="checkbox"]').prop('checked', false);
    });

    // Saat Role dipilih
    $('#id_roles').on('change', function () {
        var id_roles = $(this).val();
        if (id_roles) {
            $.ajax({
                url: baseURL + 'sip_new/role/get_permission_json',
                type: 'POST',
                data: { id_roles: id_roles },
                dataType: 'json',
                success: function (res) {
                    $('input[type="checkbox"]').prop('checked', false);
                    if (res) {
                        $.each(res, function (id_menu, akses) {
                            $.each(akses, function (perm, val) {
                                if (val == 1) {
                                    $(`input[name="permissions[${id_menu}][${perm}]"]`).prop('checked', true);
                                }
                            });
                        });
                    }
                }
            });
        } else {
            $('input[type="checkbox"]').prop('checked', false);
        }
    });

    // Tombol Edit klik
    $('.edit-akses').click(function () {
        var id_roles = $(this).data('id');
        $('#modalAssignMenu').modal('show');
        $('#id_roles').val(id_roles).trigger('change');
    });

    // Submit form assign menu
    $('#formAssignMenu').submit(function (e) {
        e.preventDefault();
        var id_roles = $('#id_roles').val();
        if (!id_roles) {
            Swal.fire('Oops!', 'Pilih role terlebih dahulu.', 'warning');
            return;
        }

        $.ajax({
            url: baseURL + 'sip_new/role/simpan_akses',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function () {
                $('#btnSimpan').html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            },
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire('Berhasil', res.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            complete: function () {
                $('#btnSimpan').html('Simpan').prop('disabled', false);
            },
            error: function () {
                Swal.fire('Error', 'Gagal menghubungi server.', 'error');
            }
        });
    });
});
