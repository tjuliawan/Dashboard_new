@extends('layouts.user_type.guest')
@section('script')
    @include('config_pihakketiga')
    <script>
        $(document).ready(function() {
            $('.navbar ').hide();
            $('#email_btn').on('click', function() {
                $('.loader').show();
                var email = $('#email_input').val();
                if (email == '') {
                    $('#mail_no_empty').show();
                    $('.loader').hide();
                    return false;
                } else {
                    $('#mail_no_empty').hide();
                }
                $.ajax({
                    url: '/forgot-password',
                    type: 'POST',
                    data: {
                        email_input: $('#email_input').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('.loader').hide();
                        $('#email_input').prop('disabled', true);
                        $('#email_btn').prop('disabled', true);
                        new Noty({
                            text: '<i class="fas fa-check"></i>  Email verifikasi berhasil dikirim.',
                            type: 'success',
                            timeout: 3000,
                            layout: 'topLeft',
                        }).show();
                        $('#pesan_sukses').fadeIn(1000);
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.responseJSON.message || 'Terjadi kesalahan yang tidak terduga';

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
        });
    </script>
    <script>
        function ubahHalaman(namaHalaman) {
            document.title = namaHalaman; 
        }
        ubahHalaman('Send Mail - Page');
    </script>
@endsection
@section('content')
    <div class="page-header section-height-75">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                    <div class="card card-plain card_main mt-8">
                        <div class="card-header pb-0 text-left bg-transparent">
                            <h4 class="mb-0">Forgot your password? Enter your email here</h4>
                        </div>
                        <div class="card-body">
                            <div>
                                <label for="email">Email</label>
                                <div class="">
                                    <input type="email" class="form-control" placeholder="Email" id="email_input">
                                </div>
                                <small class="text-danger" id="mail_no_empty" style="display: none">Email is required</small>
                            </div>
                            <div class="text-center">
                                <button type="" class="btn bg-gradient-info w-100 mt-4 mb-0" id="email_btn">Recover your password</button>
                            </div>
                            <span class="loader mt-3" style="display: none"></span>
                            <div id="pesan_sukses" class="alert shadow p-3 rounded-3 mt-4" role="alert" style="display: none; background-color: #f5f6fa">
                                <div class="ms-3">
                                    <i class="fas fa-check-circle fa-lg text-success rounded-circle mb-3" style="min-width: 40px;"></i>
                                    <strong class="text-success">Email Verifikasi Terkirim</strong>
                                    <p class="mb-0" style="font-size: 14px; color: #444;">
                                        Email verifikasi untuk penggantian password telah berhasil dikirim. Silakan cek email Anda, kemudian <a href="/login" class="text-primary"><strong>kembali ke halaman login</strong></a>.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center pt-0 px-lg-2 px-1">
                            <p class="text-sm mt-3 mb-0">Already have an account? <a href="/" class="text-info text-gradient font-weight-bolder">Sign in</a></p>
                            <p class="mb-4 text-sm mx-auto">
                                Don't have an account?
                                <a href="{{ url('/register') }}" class="text-info text-gradient font-weight-bold">Sign up</a>
                            </p>
                        </div>
                    </div>
                </div>
                <style>
                    .loader {
                        width: 100%;
                        height: 4.8px;
                        display: inline-block;
                        position: relative;
                        background: rgba(255, 255, 255, 0.15);
                        overflow: hidden;
                    }

                    .loader::after {
                        content: '';
                        width: 192px;
                        height: 4.8px;
                        background: #1e47ffec;
                        position: absolute;
                        top: 0;
                        left: 0;
                        box-sizing: border-box;
                        border-radius: 4px;
                        animation: animloader 2s linear infinite;
                    }

                    @keyframes animloader {
                        0% {
                            left: 0;
                            transform: translateX(-100%);
                        }

                        100% {
                            left: 100%;
                            transform: translateX(0%);
                        }
                    }
                </style>
                <div class="col-md-6">
                    <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                        <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('../assets/img/curved-images/curved6.jpg')"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('harus_ada')
