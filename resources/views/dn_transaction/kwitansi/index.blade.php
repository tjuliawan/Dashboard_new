@extends('layouts.user_type.auth')
@section('title', 'DN tagih - Kwitansi')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('#loader_search').hide();
            let url_code;
            let client_code;
            $('#btn_search').click(function() {
                $('#kwitansi_warning_badge').hide();
                $('#kwiwitansi_info_badge').hide();
                if ($('#input_main_code').val() === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please Intert the code!',
                    });
                    return;
                }
                $('#loader_search').show();
                getheaderdata();
            });
            $('#input_main_code').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#btn_search').click();
                }
            });
            $('#btn_print').click(function() {
                var url = '/cetak-pdf/dn-tagih-kwitansi?code=' + encodeURIComponent(url_code) +
                    '&client_code=' + encodeURIComponent(client_code);
                window.open(url, '_blank');
            });
            $('#btn_confirm').click(function() {
                if ($('#untuk_pembayaran').val() === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please Intert the note!',
                    });
                    return;
                }
                new Noty({
                    text: `
                        <div style="font-size: 15px;">
                            <strong>Konfirmasi</strong><br>
                            Apakah Anda yakin ingin <b>melanjutkan</b> transaksi ini?
                            <br><small style="color:red;">Setelah dikonfirmasi, data tidak dapat diubah lagi.</small>
                        </div>
                    `,
                    type: 'alert',
                    layout: 'center',
                    theme: 'sunset',
                    modal: true,
                    killer: true,
                    buttons: [
                        Noty.button('Ya, Konfirmasi', 'btn bg-gradient-success btn-sm btn-rounded mt-3', function(notyInstance) {
                            storeKwitansi();
                            notyInstance.close();
                        }),

                        Noty.button('Batal', 'btn bg-gradient-danger btn-sm btn-rounded mx-1 mt-3', function(notyInstance) {
                            notyInstance.close();
                            new Noty({
                                text: '<i class="fas fa-info-circle"></i> Konfirmasi dibatalkan.',
                                type: 'info',
                                timeout: 3000,
                                layout: 'topRight'
                            }).show();
                        })
                    ]
                }).show();
            });

            function getheaderdata() {
                $.ajax({
                    url: '/get_header_dn_tagih',
                    type: 'get',
                    data: {
                        code: $('#input_main_code').val()
                    },
                    success: function(response) {
                        total_tagihan = response.salesdntagih_Total_tagihan;
                        client_code = response.salesdntagih_client_code;
                        url_code = response.salesdntagih_code_h;
                        code_head = response.salesdntagih_code_h;
                        product = response.salesdntagih_product_code;
                        let total = parseFloat(response.salesdntagih_Total_tagihan);
                        $('#dn_tagih_code').text(response.salesdntagih_code_h);
                        $('#client_code').text(response.clien_desc);
                        $('#loader_search').hide();
                        $('#card_main').show();
                        let dalamHuruf = terbilang(total).trim() + " Rupiah";
                        $('#total_tagihan_terbilang').text(dalamHuruf);
                        $('#total_tagihan').text(formatRupiah(total));
                        if (response && response.note_kwitansi !== null) {
                            $('#untuk_pembayaran').val(response.note_kwitansi);
                            $('#untuk_pembayaran').prop('disabled', true);
                        } else {
                            $('#untuk_pembayaran').val('');
                            $('#untuk_pembayaran').prop('disabled', false);
                        }

                        var sudah_kwitansi = response.no_kwitansi;
                        if (sudah_kwitansi === "1") {
                            $('#btn_confirm').hide();
                            $('#btn_print').show();
                            $('#kwitansi_warning_badge').hide();
                            $('#kwiwitansi_info_badge').fadeIn(1000);
                        } else {
                            $('#btn_confirm').show();
                            $('#btn_print').hide();
                            $('#kwiwitansi_info_badge').hide();
                            $('#kwitansi_warning_badge').fadeIn(1000);
                        }
                    },
                    error: function() {
                        $('#card_main').hide();
                        $('#loader_search').hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Data is not available. Please try again!',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }

            function storeKwitansi() {
                $.ajax({
                    url: '/dn-tagih/store-kwitansi',
                    type: 'POST',
                    data: {
                        header_code: url_code,
                        note_kwitansi: $('#untuk_pembayaran').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        new Noty({
                            text: '<i class="fas fa-check"></i> Data berhasil dikonfirmasi dan tidak dapat diubah lagi.',
                            type: 'success',
                            timeout: 3000,
                            layout: 'topRight'
                        }).show();
                        $('#loader_user_confirm').hide();
                        getheaderdata();
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'An Error Occurred',
                            text: 'Failed to send data to the server. Please try again later.',
                        });
                        $('#loader_user_confirm').hide();
                        console.error('Error:', error);
                    }
                });
            }

            function terbilang(bilangan) {
                var angka = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];

                bilangan = Math.round(bilangan); // Pembulatan seperti PHP round()

                if (bilangan < 12) {
                    return angka[bilangan];
                } else if (bilangan < 20) {
                    return terbilang(bilangan - 10) + " Belas";
                } else if (bilangan < 100) {
                    return terbilang(Math.floor(bilangan / 10)) + " Puluh " + terbilang(bilangan % 10);
                } else if (bilangan < 200) {
                    return "Seratus " + terbilang(bilangan - 100);
                } else if (bilangan < 1000) {
                    return terbilang(Math.floor(bilangan / 100)) + " Ratus " + terbilang(bilangan % 100);
                } else if (bilangan < 2000) {
                    return "Seribu " + terbilang(bilangan - 1000);
                } else if (bilangan < 1000000) {
                    return terbilang(Math.floor(bilangan / 1000)) + " Ribu " + terbilang(bilangan % 1000);
                } else if (bilangan < 1000000000) {
                    return terbilang(Math.floor(bilangan / 1000000)) + " Juta " + terbilang(bilangan % 1000000);
                } else if (bilangan < 1000000000000) {
                    return terbilang(Math.floor(bilangan / 1000000000)) + " Milyar " + terbilang(bilangan % 1000000000);
                } else {
                    return "Angka terlalu besar";
                }
            }
            
            function formatRupiah(angka) {
                return 'Rp ' + parseFloat(angka).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });
    </script>
@endsection
@section('content')
    <div>
        <div class="row">
            <div class="col-12 col-md-auto mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="form-group">
                            <div class="input-group">
                                <input class="form-control form-control-sm" placeholder="Search DN Tagih Code" type="text" id="input_main_code" data-bs-toggle="tooltip" data-bs-placement="top" title="ex : INV-TSD-202505-xxxx">
                                <span class="input-group-text" id="btn_search" style="cursor: pointer"><i class="fa-solid fa-magnifying-glass"></i></span>
                            </div>
                            <small>Please input your dn tagih code here</small>
                            <div class="d-flex justify-content-center align-items-center mt-2">
                                <div class="loader" style="display: none" id="loader_search"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3" id="card_main" style="display: none">
                <div class="card ">
                    <div class="card-body">
                        <style>
                            .info-table {
                                font-size: 12px;
                                width: 100%;
                                border-collapse: collapse;
                            }

                            .info-table td {
                                padding: 6px 8px;
                                vertical-align: top;
                            }

                            .info-table td:first-child {
                                font-weight: bold;
                                color: #333;
                            }

                            .info-table td:nth-child(2) {
                                width: 10px;
                                font-weight: bold;
                            }

                            .info-table textarea {
                                width: 100%;
                                resize: vertical;
                                font-size: 12px;
                                padding: 4px;
                            }

                            .div_iner {
                                border: 3px solid #6f559e38;
                                padding: 10px;
                                border-radius: 15px;
                            }
                        </style>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="div_iner mb-3">
                                    <table class="info-table">
                                        <tr>
                                            <td>DN Tagih Code</td>
                                            <td>:</td>
                                            <td id="dn_tagih_code"></td>
                                        </tr>
                                        <tr>
                                            <td>Sudah Diterima Dari</td>
                                            <td>:</td>
                                            <td id="client_code"></td>
                                        </tr>
                                        <tr>
                                            <td>Uang Sejumlah</td>
                                            <td>:</td>
                                            <td id="total_tagihan_terbilang" style="font-style: italic;"></td>
                                        </tr>
                                        <tr>
                                            <td>Untuk Pembayaran</td>
                                            <td>:</td>
                                            <td>
                                                <textarea class="form-control form-control-sm" id="untuk_pembayaran" placeholder="Tulis keterangan pembayaran..." rows="3"></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Jumlah</td>
                                            <td>:</td>
                                            <td id="total_tagihan" style="font-weight: bold;"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <button type="button" class="btn bg-gradient-success btn-sm btn-rounded" id="btn_confirm" style="display: none">Confirm</button>
                                <button type="button" class="btn bg-gradient-info btn-sm btn-rounded" id="btn_print" style="display: none">Print</button>
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div id="kwiwitansi_info_badge" class="kwitansi-badge-animate alert shadow p-3 rounded-3" role="alert" style="display: none; background-color: #f5f6fa">
                                            <div class="ms-3">
                                                <i class="fas fa-check-circle fa-lg text-success rounded-circle mb-3" style="min-width: 40px;"></i>
                                                <strong class="text-success">Kwitansi Sudah Dibuat</strong>
                                                <p class="mb-0" style="font-size: 14px; color: #444;">
                                                    Kwitansi untuk DN ini sudah tersedia. Silakan tekan tombol <strong>Print</strong> untuk mencetak kwitansi.
                                                </p>
                                            </div>
                                        </div>
                                        <div id="kwitansi_warning_badge" class="kwitansi-badge-animate alert shadow-sm p-3 rounded-3" role="alert" style="display: none; background-color: #fff8e1;">
                                            <div class="ms-2">
                                                <i class="fas fa-exclamation-triangle fa-lg text-warning rounded-circle mb-3" style="min-width: 40px;"></i>
                                                <strong class="text-warning">Periksa Kembali Data Anda</strong>
                                                <p class="mb-0" style="font-size: 14px; color: #555;">
                                                    Pastikan semua data DN dan barang sudah benar sebelum mengonfirmasi. Setelah kwitansi dibuat, data tidak dapat diubah kembali.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modalButton"></div>
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
    </style>
@endsection
@include('harus_ada')
