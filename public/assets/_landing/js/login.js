$(document).ready(function(){
    $('#login-form').submit(function(event){
        event.preventDefault();
        
        let username = $('#username').val().trim();
        let password = $('#password').val().trim();
        
        if (username === "" || password === "") {
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: 'Username dan Password wajib diisi!',
            });
            return;
        }

        let formData = $(this).serialize();

        $.ajax({
            url: baseURL + "sip_new/login",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response){
                if(response.status === 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        $('#loginModal').modal('hide'); // Tutup modal
                        window.location.href = baseURL + "sip_new/dashboard"; // Redirect ke dashboard
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal!',
                        text: response.message,
                    });
                }
            },
            error: function(xhr, status, error){
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan, coba lagi!'
                });
                console.log("Error: " + xhr.responseText);
            }
        });
    });


    $('#register-form').submit(function(e){
        e.preventDefault();

        let nama = $('#nama').val().trim();
        let username = $('#username').val().trim();
        let password = $('#password').val().trim();
        let no_hp = $('#no_hp').val().trim();
        
        if (nama === "" || username === "" || password === "" || no_hp === "") {
            Swal.fire({
                icon: 'error',
                title: 'Registrasi Gagal!',
                text: 'Semua Kolom wajib diisi!',
            });
            return;
        }

        let formData = $(this).serialize();

        $.ajax({
            url: baseURL + "sip_new/auth/register_ajax",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(res){
                if(res.status === 'success'){
                    Swal.fire('Berhasil!', res.message, 'success');
                    $('#register-form')[0].reset();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });

    
    // Logout menggunakan AJAX dengan Swal.fire
    $('#logout-btn').click(function(event){
        event.preventDefault();
        
        Swal.fire({
            // title: 'Konfirmasi Logout',
            text: 'Apakah Anda yakin ingin logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: baseURL + "sip_new/logout",
                    type: "POST",
                    dataType: "json",
                    success: function(response){
                        Swal.fire({
                            icon: 'success',
                            title: 'Logout Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            window.location.href = baseURL + "sip_new";
                        });
                    },
                    error: function(xhr, status, error){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Terjadi kesalahan, coba lagi!'
                        });
                        console.log("Error: " + xhr.responseText);
                    }
                });
            }
        });
    });
});