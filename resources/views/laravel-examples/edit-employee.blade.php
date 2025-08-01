@extends('layouts.user_type.auth')
@section('title', 'MC-Emp MGT')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                var results = regex.exec(location.search);
                return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
            }
            var emp_code = getUrlParameter('emp');
            $.ajax({
                type: "GET",
                url: '/Employee-management/get-employe-data',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    emp_code: emp_code,
                },
                success: function(response) {
                    $('#com_code').val(response[0].Ms_Company_Code).trigger('change');
                    $('#nama').val(response[0].Ms_Emp_Name);
                    $('#email').val(response[0].Email);
                    $('#hp').val(response[0].HP);
                    $('#pkwt').prop('checked', response[0].Cek_PKWT == 1);
                    $('#aktif').prop('checked', response[0].Cek_Aktif == 1);

                },
                error: function(xhr) {
                    $('#loadingOverlay').hide();
                    let errorMessage =
                        'Terjadi kesalahan saat mengirim data';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        errorMessage = '';
                        for (let field in errors) {
                            errorMessage += errors[field].join('\n') + '\n';
                        }
                    }

                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error'
                    });
                    console.log("Error mengirim data", xhr);
                }
            });
            $("#save_emp").click(function() {
                Swal.fire({
                    title: 'Konfirmasi Transaksi?',
                    text: "YAKIN?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#loadingOverlay').show();
                        $.ajax({
                            type: "POST",
                            url: '/Employee-management/add-employe',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                com_code: $('#com_code').val(),
                                nama: $('#nama').val(),
                                email: $('#email').val(),
                                hp: $('#hp').val(),
                                pkwt: $('#pkwt').is(':checked'),
                                aktif: $('#aktif').is(':checked'),
                                emp_code: emp_code,
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Berhasil',
                                    text: 'Data telah berhasil dikirim',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    willClose: () => {
                                        var url = '/Employee-management'
                                        window.location.href = url;
                                    }
                                });
                            },
                            error: function(xhr) {
                                $('#loadingOverlay').hide();
                                let errorMessage =
                                    'Terjadi kesalahan saat mengirim data';
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    let errors = xhr.responseJSON.errors;
                                    errorMessage = '';
                                    for (let field in errors) {
                                        errorMessage += errors[field].join('\n') + '\n';
                                    }
                                }

                                Swal.fire({
                                    title: 'Error',
                                    text: errorMessage,
                                    icon: 'error'
                                });
                                console.log("Error mengirim data", xhr);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
@section('content')
    <div>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                <h5 class="mb-0">Edit Employee</h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="row m-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="exampleInputEmail1" class="form-label">Nama</label>
                                    <input type="text" class="form-control form-control-sm" id="nama"
                                        placeholder="Masukkan Nama">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="exampleInputEmail1" class="form-label">Company Code</label>
                                    <select class="form-control form-control-sm" name="" id="com_code">
                                        <option value="">Pilih Company</option>
                                        <option value="HGS">HGS</option>
                                        <option value="TGF">TGF</option>
                                        <option value="TGU">TGU</option>
                                        <option value="TGM">TGM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="exampleInputPassword1" class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-sm" id="email">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="exampleInputPassword1" class="form-label">Nomor Hp.</label>
                                    <input type="number" class="form-control form-control-sm" id="hp">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="pkwt">
                                    <label class="form-check-label" for="exampleCheck1">PKWT</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="aktif">
                                    <label class="form-check-label" for="exampleCheck1">Aktif</label>
                                </div>
                            </div>
                            <div class="col-12 ">
                                <button type="submit" class="btn btn-sm btn-primary" id="save_emp">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
