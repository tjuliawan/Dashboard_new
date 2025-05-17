@extends('layouts.user_type.guest')

@section('content')
    <div class="page-header section-height-75">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                    <div class="card card-plain mt-8">
                        <div class="card-header pb-0 text-left bg-transparent">
                            <h4 class="mb-0">Change password</h4>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="token" value="{{ $token }}">

                            <div>
                                <label for="email">Email</label>
                                <input id="email" type="email" class="form-control" placeholder="Email">
                                <p class="text-danger text-xs mt-2" id="error-email"></p>
                            </div>
                            <div>
                                <label for="password">New Password</label>
                                <input id="password" type="password" class="form-control" placeholder="Password">
                                <p class="text-danger text-xs mt-2" id="error-password"></p>
                            </div>
                            <div>
                                <label for="password_confirmation">Confirm Password</label>
                                <input id="password_confirmation" type="password" class="form-control" placeholder="Password-confirmation">
                                <p class="text-danger text-xs mt-2" id="error-password-confirmation"></p>
                            </div>

                            <div class="text-center">
                                <button id="submit-btn" class="btn bg-gradient-info w-100 mt-4 mb-0">Recover your password</button>
                            </div>

                            <p class="text-success text-xs mt-3" id="success-message"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                        <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('../assets/img/curved-images/curved6.jpg')"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#submit-btn').on('click', function(e) {
            e.preventDefault();

            // Bersihkan pesan error sebelumnya
            $('#error-email').text('');
            $('#error-password').text('');
            $('#error-password-confirmation').text('');
            $('#success-message').text('');

            const data = {
                _token: '{{ csrf_token() }}',
                email: $('#email').val(),
                password: $('#password').val(),
                password_confirmation: $('#password_confirmation').val(),
                token: $('#token').val()
            };

            $.ajax({
                url: '/reset-password',
                method: 'POST',
                data: data,
                success: function(response) {
                    $('#success-message').text('Password berhasil direset. Silakan login.');
                    $('#email, #password, #password_confirmation').val('');
                    window.location.href = '/login';
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.responseJSON.error || 'Terjadi kesalahan yang tidak terduga';

                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: errorMessage,
                    });
                    console.error('Error:', errorMessage);
                    $('.loader').hide();
                }
            });
        });
    </script>
    <script>
        function ubahHalaman(namaHalaman) {
            document.title = namaHalaman;
        }
        ubahHalaman('Change password - Page');
    </script>
@endsection
@include('harus_ada')
