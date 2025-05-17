@extends('layouts.user_type.auth')

@section('content')
    <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
        <div class="container-fluid">
            <div class="page-header min-height-300 border-radius-xl mt-4" style="background-image: url('../assets/img/curved-images/curved0.jpg'); background-position-y: 50%;">
                <span class="mask bg-gradient-primary opacity-6"></span>
            </div>
            <div class="card card-body blur shadow-blur mx-4 mt-n6 overflow-hidden">
                <div class="row gx-4">
                    <div class="col-auto">
                        @php
                            $username = Auth::user()->username;
                            $imageJpg = "storage/assets/img/$username.jpg";
                            $imagePng = "storage/assets/img/$username.png";

                            if (file_exists(public_path($imageJpg))) {
                                $imageSrc = asset($imageJpg);
                            } elseif (file_exists(public_path($imagePng))) {
                                $imageSrc = asset($imagePng);
                            } else {
                                $imageSrc = null;
                            }
                        @endphp

                        <div class="avatar avatar-xl position-relative">
                            @if ($imageSrc)
                                <img src="{{ $imageSrc }}" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
                            @else
                                <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-light border-radius-lg shadow-sm" style="height: 100px;">
                                    <i class="ni ni-single-02 text-dark text-lg"></i>
                                </div>
                            @endif

                            <!-- Tombol edit di sudut kanan atas -->
                            <form action="{{ route('profile.updatePhoto') }}" method="POST" enctype="multipart/form-data" class="position-absolute" style="top: 0px; right: -15px;">
                                @csrf
                                <label for="profile_image" class="bg-white p-1 rounded-circle shadow" style="cursor: pointer;" title="Ubah Foto">
                                    <i class="fas fa-edit text-primary"></i>
                                    <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="this.form.submit()" style="display: none;">
                                </label>
                            </form>
                        </div>

                    </div>
                    <div class="col-auto my-auto">
                        <div class="h-100">
                            <h5 class="mb-1">
                                {{ Auth::user()->name }}
                            </h5>
                            <p class="mb-0 font-weight-bold text-sm">
                                {{ Auth::user()->ms_divisi }}
                            </p>
                        </div>
                    </div>
                    {{-- <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
                        <div class="nav-wrapper position-relative end-0">
                            <ul class="nav nav-pills nav-fill p-1 bg-transparent" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link mb-0 px-0 py-1 active " data-bs-toggle="tab" href="javascript:;" role="tab" aria-selected="true">
                                        <svg class="text-dark" width="16px" height="16px" viewBox="0 0 42 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(-2319.000000, -291.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                                    <g transform="translate(1716.000000, 291.000000)">
                                                        <g transform="translate(603.000000, 0.000000)">
                                                            <path class="color-background" d="M22.7597136,19.3090182 L38.8987031,11.2395234 C39.3926816,10.9925342 39.592906,10.3918611 39.3459167,9.89788265 C39.249157,9.70436312 39.0922432,9.5474453 38.8987261,9.45068056 L20.2741875,0.1378125 L20.2741875,0.1378125 C19.905375,-0.04725 19.469625,-0.04725 19.0995,0.1378125 L3.1011696,8.13815822 C2.60720568,8.38517662 2.40701679,8.98586148 2.6540352,9.4798254 C2.75080129,9.67332903 2.90771305,9.83023153 3.10122239,9.9269862 L21.8652864,19.3090182 C22.1468139,19.4497819 22.4781861,19.4497819 22.7597136,19.3090182 Z">
                                                            </path>
                                                            <path class="color-background" d="M23.625,22.429159 L23.625,39.8805372 C23.625,40.4328219 24.0727153,40.8805372 24.625,40.8805372 C24.7802551,40.8805372 24.9333778,40.8443874 25.0722402,40.7749511 L41.2741875,32.673375 L41.2741875,32.673375 C41.719125,32.4515625 42,31.9974375 42,31.5 L42,14.241659 C42,13.6893742 41.5522847,13.241659 41,13.241659 C40.8447549,13.241659 40.6916418,13.2778041 40.5527864,13.3472318 L24.1777864,21.5347318 C23.8390024,21.7041238 23.625,22.0503869 23.625,22.429159 Z" opacity="0.7"></path>
                                                            <path class="color-background" d="M20.4472136,21.5347318 L1.4472136,12.0347318 C0.953235098,11.7877425 0.352562058,11.9879669 0.105572809,12.4819454 C0.0361450918,12.6208008 6.47121774e-16,12.7739139 0,12.929159 L0,30.1875 L0,30.1875 C0,30.6849375 0.280875,31.1390625 0.7258125,31.3621875 L19.5528096,40.7750766 C20.0467945,41.0220531 20.6474623,40.8218132 20.8944388,40.3278283 C20.963859,40.1889789 21,40.0358742 21,39.8806379 L21,22.429159 C21,22.0503869 20.7859976,21.7041238 20.4472136,21.5347318 Z" opacity="0.7"></path>
                                                        </g>
                                                    </g>
                                                </g>
                                            </g>
                                        </svg>
                                        <span class="ms-1">App</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mb-0 px-0 py-1 " data-bs-toggle="tab" href="javascript:;" role="tab" aria-selected="false">
                                        <svg class="text-dark" width="16px" height="16px" viewBox="0 0 40 44" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                            <title>document</title>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(-1870.000000, -591.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                                    <g transform="translate(1716.000000, 291.000000)">
                                                        <g transform="translate(154.000000, 300.000000)">
                                                            <path class="color-background" d="M40,40 L36.3636364,40 L36.3636364,3.63636364 L5.45454545,3.63636364 L5.45454545,0 L38.1818182,0 C39.1854545,0 40,0.814545455 40,1.81818182 L40,40 Z" opacity="0.603585379"></path>
                                                            <path class="color-background" d="M30.9090909,7.27272727 L1.81818182,7.27272727 C0.814545455,7.27272727 0,8.08727273 0,9.09090909 L0,41.8181818 C0,42.8218182 0.814545455,43.6363636 1.81818182,43.6363636 L30.9090909,43.6363636 C31.9127273,43.6363636 32.7272727,42.8218182 32.7272727,41.8181818 L32.7272727,9.09090909 C32.7272727,8.08727273 31.9127273,7.27272727 30.9090909,7.27272727 Z M18.1818182,34.5454545 L7.27272727,34.5454545 L7.27272727,30.9090909 L18.1818182,30.9090909 L18.1818182,34.5454545 Z M25.4545455,27.2727273 L7.27272727,27.2727273 L7.27272727,23.6363636 L25.4545455,23.6363636 L25.4545455,27.2727273 Z M25.4545455,20 L7.27272727,20 L7.27272727,16.3636364 L25.4545455,16.3636364 L25.4545455,20 Z">
                                                            </path>
                                                        </g>
                                                    </g>
                                                </g>
                                            </g>
                                        </svg>
                                        <span class="ms-1">Messages</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mb-0 px-0 py-1 " data-bs-toggle="tab" href="javascript:;" role="tab" aria-selected="false">
                                        <svg class="text-dark" width="16px" height="16px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                            <title>settings</title>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(-2020.000000, -442.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                                    <g transform="translate(1716.000000, 291.000000)">
                                                        <g transform="translate(304.000000, 151.000000)">
                                                            <polygon class="color-background" opacity="0.596981957" points="18.0883333 15.7316667 11.1783333 8.82166667 13.3333333 6.66666667 6.66666667 0 0 6.66666667 6.66666667 13.3333333 8.82166667 11.1783333 15.315 17.6716667">
                                                            </polygon>
                                                            <path class="color-background" d="M31.5666667,23.2333333 C31.0516667,23.2933333 30.53,23.3333333 30,23.3333333 C29.4916667,23.3333333 28.9866667,23.3033333 28.48,23.245 L22.4116667,30.7433333 L29.9416667,38.2733333 C32.2433333,40.575 35.9733333,40.575 38.275,38.2733333 L38.275,38.2733333 C40.5766667,35.9716667 40.5766667,32.2416667 38.275,29.94 L31.5666667,23.2333333 Z" opacity="0.596981957"></path>
                                                            <path class="color-background" d="M33.785,11.285 L28.715,6.215 L34.0616667,0.868333333 C32.82,0.315 31.4483333,0 30,0 C24.4766667,0 20,4.47666667 20,10 C20,10.99 20.1483333,11.9433333 20.4166667,12.8466667 L2.435,27.3966667 C0.95,28.7083333 0.0633333333,30.595 0.00333333333,32.5733333 C-0.0583333333,34.5533333 0.71,36.4916667 2.11,37.89 C3.47,39.2516667 5.27833333,40 7.20166667,40 C9.26666667,40 11.2366667,39.1133333 12.6033333,37.565 L27.1533333,19.5833333 C28.0566667,19.8516667 29.01,20 30,20 C35.5233333,20 40,15.5233333 40,10 C40,8.55166667 39.685,7.18 39.1316667,5.93666667 L33.785,11.285 Z">
                                                            </path>
                                                        </g>
                                                    </g>
                                                </g>
                                            </g>
                                        </svg>
                                        <span class="ms-1">Settings</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12 col-md-5">
                    <div class="card h-100">
                        <div class="card-header pb-0 p-3">
                            <div class="row">
                                <div class="col-md-8 d-flex align-items-center">
                                    <h6 class="mb-0">Profile Information</h6>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="javascript:;">
                                        {{-- <i class="fas fa-user-edit text-secondary text-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Profile"></i> --}}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <p class="text-sm" id="profilInfoText" style="cursor:pointer; " data-bs-toggle="tooltip" data-bs-placement="top" title="Click to edit">
                                {{ Auth::user()->profil_info ?? 'Klik untuk menambahkan informasi profil...' }}
                            </p>
                            <textarea id="profilInfoInput" class="form-control d-none" rows="4">{{ Auth::user()->profil_info }}</textarea>
                            <hr class="horizontal gray-light my-4">
                            <ul class="list-group">
                                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Full Name:</strong> &nbsp; {{ Auth::user()->name }}</li>
                                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">EMP ID:</strong> &nbsp; <span id="text_emp_id"></span></li>
                                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Mobile:</strong> &nbsp; <span id="text_phone"></span></li>
                                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Email:</strong> &nbsp; {{ Auth::user()->email }}</li>
                                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Address:</strong> &nbsp;<span id="text_addresw"></span> </li>
                                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Last Edication:</strong> &nbsp;<span id="text_last_edu"></span> </li>
                                <li class="list-group-item border-0 ps-0 pb-0 mb-3">
                                    <a class="btn btn-instagram btn-simple mb-0 ps-1 pe-2 py-0" href="javascript:;" id="instagram-link">
                                        <i class="fab fa-instagram fa-lg"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-7">
                    <div class="card h-100">
                        <div class="card-header pb-0 p-3">
                            <div class="row">
                                <div class="col-md-8 d-flex align-items-center">
                                    <h6 class="mb-0">Reset Password</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 h-100">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3">
                                    <input type="text" class="form-control form-control-sm" id="username" name="username" required maxlength="50" placeholder="Masukkan Username" value="{{ Auth::user()->username }}" readonly data-bs-toggle="tooltip" data-bs-placement="top" title="Username" disabled>
                                    <div class="password-container mt-3">
                                        <input type="password" class="form-control form-control-sm" id="old_password" name="old_password" required maxlength="50" placeholder="Masukkan Password Lama" data-bs-toggle="tooltip" data-bs-placement="top" title="Password lama" required>
                                        <small class="fas fa-eye toggle-password" id="toggle_icon_old_password"></small>
                                    </div>
                                    <div class="password-container">
                                        <input type="password" class="form-control form-control-sm mt-3" id="new_password" name="new_password" required maxlength="50" placeholder="Masukkan Password Baru" data-bs-toggle="tooltip" data-bs-placement="top" title="Password baru" required>
                                        <small class="fas fa-eye toggle-password" id="toggle_icon_new_password"></small>
                                    </div>
                                    <small id="newPasswordHelp" class="form-text"></small>
                                    <div class="password-container">
                                        <input type="password" class="form-control form-control-sm mt-3" id="confirm_new_password" name="confirm_new_password" required maxlength="50" placeholder="Konfirmasi Password Baru" data-bs-toggle="tooltip" data-bs-placement="top" title="Konfirmasi password baru" required>
                                        <small class="fas fa-eye toggle-password" id="toggle_icon_confirm_new_password"></small>
                                    </div>
                                </div>
                                <div class="col-12 text-left">
                                    <button type="button" class="btn btn-sm bg-gradient-primary me-3" id="savePasswordBtn">Simpan</button>
                                    <button type="button" class="btn btn-sm bg-gradient-secondary" data-bs-dismiss="modal">Batal</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footers.auth.footer')
        </div>
    </div>
    <style>
        /* CSS untuk menempatkan ikon mata di dalam input box */
        .password-container {
            position: relative;
        }

        .password-container input {
            padding-right: 50px;
        }

        .password-container .toggle-password {
            position: absolute;
            top: 50%;
            right: 5px;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
    <script>
        // Function untuk password visibility
        function togglePasswordVisibility(inputId, iconId) {
            var passwordInput = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
        document.querySelectorAll('.toggle-password').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                var inputId = this.previousElementSibling.id;
                togglePasswordVisibility(inputId, this.id);
            });
        });
    </script>
    <script>
        function resetForms() {
            $('#old_password').val('');
            $('#new_password').val('');
            $('#confirm_new_password').val('');
            $('#newPasswordHelp').html('');
        }

        function handleResult() {
            resetForms();
        }
        $(document).ready(function() {
            var namaUser = @json(Auth::user()->name);

            // alert('Selamat datang, ' + namaUser + '!');
            function ubahHalaman(namaHalaman) {
                document.title = namaHalaman;
            }
            ubahHalaman('Profile - ' + namaUser);

            let instagramUsername;
            $('#savePasswordBtn').click(function() {
                $('#confirmationModal').modal('show');
            });

            $('#confirmSaveBtn').click(function() {
                var oldPassword = $('#old_password').val();
                var newPassword = $('#new_password').val();
                var confirmNewPassword = $('#confirm_new_password').val();

                $('#loader_user_confirm').show();

                $.ajax({
                    url: "{{ route('changePassword2') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        old_password: oldPassword,
                        password: newPassword,
                        password_confirmation: confirmNewPassword
                    },
                    success: function(response) {
                        $('#loader_user_confirm').hide();
                        // handleResult()
                        $('#confirmationModal').modal('hide');
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Password berhasil diubah',
                            }).then((result) => {});
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Gagal mengubah password: ' + response.error,
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.responseJSON.error || 'Terjadi kesalahan yang tidak terduga';

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: errorMessage,
                        });

                        console.error('Error:', errorMessage); 
                        $('#loader_user_confirm').hide(); 
                    }
                });

            });
            $('#instagram-link').click(function() {
                var cleanedUsername = instagramUsername.replace('@', '');
                window.open('https://instagram.com/' + cleanedUsername, '_blank');
            });
            $.ajax({
                url: "/profile/information",
                method: "GET",
                success: function(response) {
                    $('#text_addresw').html(response.emp_address);
                    $('#text_last_edu').html(response.emp_lastedu);
                    $('#text_phone').html(response.emp_telp);
                    instagramUsername = response.emp_akn_ig;
                    $('#text_emp_id').html(response.emp_id);
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: error,
                    });
                    console.error('Error:', error);
                    $('#loader_user_confirm').hide();
                }
            });
            $('#new_password').on('input', function() {
                var newPassword = $(this).val();
                var newPasswordHelp = $('#newPasswordHelp');
                var meetsLength = newPassword.length >= 8;
                var containsLowercase = /[a-z]/.test(newPassword);
                var containsUppercase = /[A-Z]/.test(newPassword);
                var containsNumber = /\d/.test(newPassword);


                if (meetsLength && containsLowercase && containsUppercase && containsNumber) {
                    newPasswordHelp.html('Password memenuhi semua persyaratan.');
                    newPasswordHelp.removeClass('text-danger').addClass('text-success');
                } else {
                    var errorMessages = [];
                    if (!meetsLength) {
                        errorMessages.push('Password harus terdiri dari minimal 8 karakter.');
                    }
                    if (!containsLowercase) {
                        errorMessages.push('Password harus mengandung setidaknya satu huruf kecil (a-z).');
                    }
                    if (!containsUppercase) {
                        errorMessages.push('Password harus mengandung setidaknya satu huruf besar (A-Z).');
                    }
                    if (!containsNumber) {
                        errorMessages.push('Password harus mengandung setidaknya satu angka (0-9).');
                    }

                    newPasswordHelp.html(errorMessages.join('<br>'));
                    newPasswordHelp.removeClass('text-success').addClass('text-danger');
                }
            });
        });
    </script>
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content text-center">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Konfirmasi Ganti Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Yakin ingin mengganti password?
                </div>
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <div class="loader" style="display: none" id="loader_user_confirm"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmSaveBtn">Ya Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .loader {
            width: 120px;
            height: 20px;
            border-radius: 20px;
            background:
                radial-gradient(farthest-side, orange 94%, #0000) left/20px 20px no-repeat lightblue;
            animation: l2 1s infinite linear;
        }

        @keyframes l2 {
            50% {
                background-position: right
            }
        }

        /* Styling untuk teks pesan sukses */
        .text-success {
            color: green;
        }

        /* Styling untuk teks pesan error */
        .text-danger {
            color: red;
        }

        /* Styling untuk container help text */
        #newPasswordHelp {
            margin-top: 10px;
            font-size: 11px;
        }
    </style>
    <script>
        $(document).ready(function() {
            const $text = $('#profilInfoText');
            const $textarea = $('#profilInfoInput');

            // Saat klik pada paragraf, ganti dengan textarea untuk edit
            $text.on('click', function() {
                $text.addClass('d-none');
                $textarea.removeClass('d-none').focus();
            });

            // Saat kehilangan fokus pada textarea (blur)
            $textarea.on('blur', function() {
                const newInfo = $textarea.val();

                // Kirim data ke server menggunakan AJAX
                $.ajax({
                    url: "{{ route('profil-info.update') }}",
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        profil_info: newInfo
                    },
                    success: function(response) {
                        $text.text(newInfo).removeClass('d-none');
                        $textarea.addClass('d-none');
                    },
                    error: function(err) {
                        alert('Gagal update profil!');
                        $text.removeClass('d-none');
                        $textarea.addClass('d-none');
                    }
                });
            });
        });
    </script>
@endsection
@include('harus_ada')
