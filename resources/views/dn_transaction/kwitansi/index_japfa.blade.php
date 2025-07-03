@extends('layouts.user_type.auth')
@section('title', 'DN System - Kwitansi')
@section('css')
    <style>
        #table_info .note-col,
        #table_info td.note-col {
            max-width: 350px !important;
            white-space: normal;
            /* word-break: break-word; */
        }
    </style>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('#loader_search').hide();
            let url_code;
            let client_code;
            let tahun;
            let bulan;
            let total_tagihan;
            let debet_code_1 = "";
            let debet_code_2 = "";
            let debet_code_3 = "";
            let debet_code_4 = "";
            let debet_code_5 = "";
            let debet_code_6 = "";
            let kredit_code_1 = "";
            let kredit_code_2 = "";
            let kredit_code_3 = "";
            let kredit_code_4 = "";
            let kredit_code_5 = "";
            let kredit_code_6 = "";

            let debet_val_1 = "";
            let debet_val_2 = "";
            let debet_val_3 = "";
            let debet_val_4 = "";
            let debet_val_5 = "";
            let debet_val_6 = "";
            let kredit_val_1 = "";
            let kredit_val_2 = "";
            let kredit_val_3 = "";
            let kredit_val_4 = "";
            let kredit_val_5 = "";
            let kredit_val_6 = "";

            let total_payment_text;
            $('#btn_search').click(function() {
                $('#kwitansi_warning_badge').hide();
                $('#kwiwitansi_info_badge').hide();
                if ($('#lokasi_input').val() === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Silahkan Pilih Lokasi!',
                    });
                    return;
                }
                if ($('#input_main_code').val() === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Silahkan Masukan tanggal    ',
                    });
                    return;
                }
                $('#loader_search').show();
                date = $('#input_main_code').val();
                [tahun, bulan] = date.split('-');
                getheaderdata();
            });
            $('#input_main_code').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#btn_search').click();
                }
            });
            $('#btn_print').click(function() {
                var url = '/cetak-pdf/kwitansi-japfa?code=' + encodeURIComponent(url_code);
                window.open(url, '_blank');
            });
            $('#btn_inv').click(function() {
                var url = '/cetak-pdf/inv-japfa?code=' + encodeURIComponent(url_code);
                window.open(url, '_blank');
            });
            $('#btn_confirm_').click(function() {

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
            $('#btn_confirm').click(function() {
                if ($('#untuk_pembayaran').val() === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please Intert the note!',
                    });
                    return;
                }
                if (
                    $('#total_val_1').val() == "" &&
                    $('#total_val_2').val() == "" &&
                    $('#total_val_3').val() == "" &&
                    $('#total_val_4').val() == "" &&
                    $('#total_val_5').val() == "" &&
                    $('#total_val_6').val() == "" &&
                    $('#total_val_kredit_1').val() == "" &&
                    $('#total_val_kredit_2').val() == "" &&
                    $('#total_val_kredit_3').val() == "" &&
                    $('#total_val_kredit_4').val() == "" &&
                    $('#total_val_kredit_5').val() == "" &&
                    $('#total_val_kredit_6').val() == ""
                ) {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Harap isi setidaknya satu kode COA debit atau kredit!
                                </p>
                                <small style="color: #007bff; font-size: 11px; font-style: italic;">
                                    Klik di sini untuk menutup pesan ini
                                </small>
                            </div>
                        `,
                        type: 'alert',
                        layout: 'center',
                        timeout: 3000,
                        theme: 'bootstrap-v4',
                        modal: true,
                        killer: true,
                    }).show();
                    return;
                }
                if (total_debit != total_payment_text && total_kredit != total_payment_text) {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Jumlah input dan jumlah total pembayaran tidak sesuai. Pastikan keduanya sama sebelum melanjutkan.
                                </p>
                                <small style="color: #007bff; font-size: 11px; font-style: italic;">
                                    Klik di sini untuk menutup pesan ini
                                </small>
                            </div>
                        `,
                        type: 'alert',
                        layout: 'center',
                        timeout: 6000,
                        theme: 'bootstrap-v4',
                        modal: true,
                        killer: true,
                    }).show();
                    return;
                }
                if ($('#payment_date').val() == "") {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Payment Date tidak boleh kosong. Pastikan form payment date di isi terlebih dahulu sebelum melanjutkan.
                                </p>
                                <small style="color: #007bff; font-size: 11px; font-style: italic;">
                                    Klik di sini untuk menutup pesan ini
                                </small>
                            </div>
                        `,
                        type: 'alert',
                        layout: 'center',
                        timeout: 6000,
                        theme: 'bootstrap-v4',
                        modal: true,
                        killer: true,
                    }).show();
                    return;
                }
                if ($('#select_paytipe').val() == "") {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Payment model tidak boleh kosong. Pastikan form payment model di isi terlebih dahulu sebelum melanjutkan.
                                </p>
                                <small style="color: #007bff; font-size: 11px; font-style: italic;">
                                    Klik di sini untuk menutup pesan ini
                                </small>
                            </div>
                        `,
                        type: 'alert',
                        layout: 'center',
                        timeout: 6000,
                        theme: 'bootstrap-v4',
                        modal: true,
                        killer: true,
                    }).show();
                    return;
                }
                debet_val_1 = parseIndoNumber($('#total_val_1').val());
                debet_val_2 = parseIndoNumber($('#total_val_2').val());
                debet_val_3 = parseIndoNumber($('#total_val_3').val());
                debet_val_4 = parseIndoNumber($('#total_val_4').val());
                debet_val_5 = parseIndoNumber($('#total_val_5').val());
                debet_val_6 = parseIndoNumber($('#total_val_6').val());

                kredit_val_1 = parseIndoNumber($('#total_val_kredit_1').val());
                kredit_val_2 = parseIndoNumber($('#total_val_kredit_2').val());
                kredit_val_3 = parseIndoNumber($('#total_val_kredit_3').val());
                kredit_val_4 = parseIndoNumber($('#total_val_kredit_4').val());
                kredit_val_5 = parseIndoNumber($('#total_val_kredit_5').val());
                kredit_val_6 = parseIndoNumber($('#total_val_kredit_6').val());

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
                    url: '/get_header_dn_tagih_japfa',
                    type: 'get',
                    data: {
                        tahun: tahun,
                        bulan: bulan,
                        lokasi: $('#lokasi_input').val()
                    },
                    success: function(response) {
                        total_tagihan = response.biaya;
                        total_payment_text = response.biaya;
                        client_code = response.salesdntagih_client_code;
                        url_code = response.salesdntagih_code_h;
                        code_head = response.salesdntagih_code_h;
                        product = response.salesdntagih_product_code;
                        let total = parseFloat(response.biaya);
                        $('#dn_tagih_code').text(response.salesdntagih_code_h);
                        $('#client_code').text('PT. SANTOSA UTAMA LESATARI');
                        $('#loader_search').hide();
                        $('#card_main').fadeIn(1000);
                        let dalamHuruf = terbilang(total).trim() + " Rupiah";
                        $('#total_tagihan_terbilang').text(dalamHuruf);
                        $('#total_tagihan').text(formatRupiah(total));

                        $('#untuk_pembayaran').val('');
                        $('#untuk_pembayaran').prop('disabled', false);
                        total_kredit = (kredit_val_1 + kredit_val_2 + kredit_val_3 + kredit_val_4 + kredit_val_5 + kredit_val_6);
                        total_debit = (debet_val_1 + debet_val_2 + debet_val_3 + debet_val_4 + debet_val_5 + debet_val_6);
                        $('#text_debit').text(formatIndoNumber(total_debit));
                        $('#text_kredit').text(formatIndoNumber(total_kredit));

                        if (total_debit > total_payment_text) {
                            $('#alert_debit').show();
                        } else {
                            $('#alert_debit').hide();
                        }

                        if (total_debit < total_payment_text) {
                            $('#alert_debit2').show();
                        } else {
                            $('#alert_debit2').hide();
                        }
                        if (total_kredit > total_payment_text) {
                            $('#alert_kredit').show();
                        } else {
                            $('#alert_kredit').hide();
                        }
                        if (total_kredit < total_payment_text) {
                            $('#alert_kredit2').show();
                        } else {
                            $('#alert_kredit2').hide();
                        }
                        getheaderdata2();

                    },
                    error: function(xhr) {
                        $('#card_main').hide();
                        $('#loader_search').hide();

                        let message = 'Data is not available. Please try again!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }

            function getheaderdata2() {
                $.ajax({
                    url: '/get_header_dn_tagih_japfa2',
                    type: 'get',
                    data: {
                        tahun: tahun,
                        bulan: bulan,
                        lokasi: $('#lokasi_input').val()
                    },
                    success: function(response) {
                        if (!response || response.length === 0) {
                            // Jika data kosong, tampilkan pesan error atau lakukan reset field
                            $('#dn_tagih_code').text('');
                            $('#untuk_pembayaran').val('');
                            $('#untuk_pembayaran').prop('disabled', false);
                            $('#btn_confirm').fadeIn(1000);
                            $('.tgl_kwitansi').fadeIn(1000);
                            $('#btn_print').hide();
                            $('#btn_inv').hide();
                            $('#kwiwitansi_info_badge').hide();
                            $('#kwitansi_warning_badge').fadeIn(1000);
                            $('.div_from_hide').fadeIn(1000);
                            url_code = '';

                        } else {
                            // Data tersedia, lanjut proses
                            response = response[0];

                            $('#dn_tagih_code').text(response.no_kwitansi);
                            url_code = response.no_kwitansi;

                            if (response.note_kwitansi !== null) {
                                $('#untuk_pembayaran').val(response.note_kwitansi);
                                $('#untuk_pembayaran').prop('disabled', true);
                            } else {
                                $('#untuk_pembayaran').val('');
                                $('#untuk_pembayaran').prop('disabled', false);
                            }
                            $('#btn_confirm').hide();
                            $('.tgl_kwitansi').hide();
                            $('#btn_print').fadeIn(1000);
                            $('#btn_inv').fadeIn(1000);
                            $('#kwitansi_warning_badge').hide();
                            $('#kwiwitansi_info_badge').fadeIn(1000);
                            $('.div_from_hide').fadeOut(1000);
                        }

                    },
                    error: function(xhr) {
                        $('#card_main').hide();
                        $('#loader_search').hide();

                        let message = 'Data is not available. Please try again!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }

            function storeKwitansi() {
                $.ajax({
                    url: '/dn-tagih/store-kwitansi-japfa',
                    type: 'POST',
                    data: {
                        tahun: tahun,
                        bulan: bulan,
                        total_payment_text: total_payment_text,
                        debet_code_1: debet_code_1,
                        debet_code_2: debet_code_2,
                        debet_code_3: debet_code_3,
                        debet_code_4: debet_code_4,
                        debet_code_5: debet_code_5,
                        debet_code_6: debet_code_6,
                        kredit_code_1: kredit_code_1,
                        kredit_code_2: kredit_code_2,
                        kredit_code_3: kredit_code_3,
                        kredit_code_4: kredit_code_4,
                        kredit_code_5: kredit_code_5,
                        kredit_code_6: kredit_code_6,
                        debet_val_1: debet_val_1,
                        debet_val_2: debet_val_2,
                        debet_val_3: debet_val_3,
                        debet_val_4: debet_val_4,
                        debet_val_5: debet_val_5,
                        debet_val_6: debet_val_6,
                        kredit_val_1: kredit_val_1,
                        kredit_val_2: kredit_val_2,
                        kredit_val_3: kredit_val_3,
                        kredit_val_4: kredit_val_4,
                        kredit_val_5: kredit_val_5,
                        kredit_val_6: kredit_val_6,
                        area_code: $('#select_cabang').val(),
                        coa_main: $('#select_header_coa').val(),
                        paytipe: $('#select_paytipe').val(),
                        payment_date: $('#payment_date').val(),
                        total_tagihan: total_tagihan,
                        lokasi: $('#lokasi_input').val(),
                        note_kwitansi: $('#untuk_pembayaran').val(),
                        tgl_kwitansi: $('#tgl_kwitansi').val(),
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
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tgl_kwitansi').value = today;
            get_header_coa();

            function get_header_coa() {
                $.ajax({
                    url: '/get_header_coa_transaksi',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var branchSelect = $('#select_header_coa');
                        branchSelect.empty();
                        branchSelect.append('<option value="">Pilih Coa Transaksi</option>');

                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        data.forEach(function(item) {
                            branchSelect.append('<option value="' + item.transcoa_code + '"> ' + item.transcoa_code + ' | ' + item.transcoa_desc + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching branches:', error);
                    }
                });
            }

            function get_detail_coa() {
                if ($('#select_header_coa').val() === "") {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Harap pilih kode COA terlebih dahulu!
                                </p>
                                <small style="color: #007bff; font-size: 11px; font-style: italic;">
                                    Klik di sini untuk menutup pesan ini
                                </small>
                            </div>
                        `,
                        type: 'alert',
                        layout: 'center',
                        timeout: 3000,
                        theme: 'bootstrap-v4',
                        modal: true,
                        killer: true,
                    }).show();
                    return;
                }
                $('#loader_body').show();
                $.ajax({
                    url: '/get_detail_coa_transaksi',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        code: $('#select_header_coa').val()
                    },
                    success: function(data) {
                        $('#debet_code_1').val('');
                        $('#debet_code_2').val('');
                        $('#debet_code_3').val('');
                        $('#debet_code_4').val('');
                        $('#debet_code_5').val('');
                        $('#debet_code_6').val('');
                        $('#kredit_code_1').val('');
                        $('#kredit_code_2').val('');
                        $('#kredit_code_3').val('');
                        $('#kredit_code_4').val('');
                        $('#kredit_code_5').val('');
                        $('#kredit_code_6').val('');
                        $('#total_val_1').val('');
                        $('#total_val_2').val('');
                        $('#total_val_3').val('');
                        $('#total_val_4').val('');
                        $('#total_val_5').val('');
                        $('#total_val_6').val('');
                        $('#total_val_kredit_1').val('');
                        $('#total_val_kredit_2').val('');
                        $('#total_val_kredit_3').val('');
                        $('#total_val_kredit_4').val('');
                        $('#total_val_kredit_5').val('');
                        $('#total_val_kredit_6').val('');
                        $('#text_debit').text('0,00');
                        $('#text_kredit').text('0,00');

                        debet_code_1 = data.transcoa_debetcode;
                        if (debet_code_1 && debet_code_1 !== 'NONE' && debet_code_1 !== '0') {
                            $('#debet_code_1').val(data.transcoa_debetcode + ' - ' + data.debet1_desc);
                            $('#total_val_1').prop('disabled', false);
                        } else {
                            $('#total_val_1').prop('disabled', true);
                        }
                        debet_code_2 = data.transcoa_debet2code;
                        if (debet_code_2 && debet_code_2 !== 'NONE' && debet_code_2 !== '0') {
                            $('#debet_code_2').val(data.transcoa_debet2code + ' - ' + data.debet2_desc);
                            $('#total_val_2').prop('disabled', false);
                        } else {
                            $('#total_val_2').prop('disabled', true);
                        }
                        debet_code_3 = data.transcoa_debet3code;
                        if (debet_code_3 && debet_code_3 !== 'NONE' && debet_code_3 !== '0') {
                            $('#debet_code_3').val(data.transcoa_debet3code + ' - ' + data.debet3_desc);
                            $('#total_val_3').prop('disabled', false);
                        } else {
                            $('#total_val_3').prop('disabled', true);
                        }
                        debet_code_4 = data.transcoa_debet4code;
                        if (debet_code_4 && debet_code_4 !== 'NONE' && debet_code_4 !== '0') {
                            $('#debet_code_4').val(data.transcoa_debet4code + ' - ' + data.debet4_desc);
                            $('#total_val_4').prop('disabled', false);
                        } else {
                            $('#total_val_4').prop('disabled', true);
                        }
                        debet_code_5 = data.transcoa_debet5code;
                        if (debet_code_5 && debet_code_5 !== 'NONE' && debet_code_5 !== '0') {
                            $('#debet_code_5').val(data.transcoa_debet5code + ' - ' + data.debet5_desc);
                            $('#total_val_5').prop('disabled', false);
                        } else {
                            $('#total_val_5').prop('disabled', true);
                        }
                        debet_code_6 = data.transcoa_debet6code;
                        if (debet_code_6 && debet_code_6 !== 'NONE' && debet_code_6 !== '0') {
                            $('#debet_code_6').val(data.transcoa_debet6code + ' - ' + data.debet6_desc);
                            $('#total_val_6').prop('disabled', false);
                        } else {
                            $('#total_val_6').prop('disabled', true);
                        }
                        kredit_code_1 = data.transcoa_kreditcode;
                        if (kredit_code_1 && kredit_code_1 !== 'NONE' && kredit_code_1 !== '0') {
                            $('#kredit_code_1').val(data.transcoa_kreditcode + ' - ' + data.kredit1_desc);
                            $('#total_val_kredit_1').prop('disabled', false);
                        } else {
                            $('#total_val_kredit_1').prop('disabled', true);
                        }
                        kredit_code_2 = data.transcoa_kredit2code;
                        if (kredit_code_2 && kredit_code_2 !== 'NONE' && kredit_code_2 !== '0') {
                            $('#kredit_code_2').val(data.transcoa_kredit2code + ' - ' + data.kredit2_desc);
                            $('#total_val_kredit_2').prop('disabled', false);
                        } else {
                            $('#total_val_kredit_2').prop('disabled', true);
                        }
                        kredit_code_3 = data.transcoa_kredit3code;
                        if (kredit_code_3 && kredit_code_3 !== 'NONE' && kredit_code_3 !== '0') {
                            $('#kredit_code_3').val(data.transcoa_kredit3code + ' - ' + data.kredit3_desc);
                            $('#total_val_kredit_3').prop('disabled', false);
                        } else {
                            $('#total_val_kredit_3').prop('disabled', true);
                        }
                        kredit_code_4 = data.transcoa_kredit4code;
                        if (kredit_code_4 && kredit_code_4 !== 'NONE' && kredit_code_4 !== '0') {
                            $('#kredit_code_4').val(data.transcoa_kredit4code + ' - ' + data.kredit4_desc);
                            $('#total_val_kredit_4').prop('disabled', false);
                        } else {
                            $('#total_val_kredit_4').prop('disabled', true);
                        }
                        kredit_code_5 = data.transcoa_kredit5code;
                        if (kredit_code_5 && kredit_code_5 !== 'NONE' && kredit_code_5 !== '0') {
                            $('#kredit_code_5').val(data.transcoa_kredit5code + ' - ' + data.kredit5_desc);
                            $('#total_val_kredit_5').prop('disabled', false);
                        } else {
                            $('#total_val_kredit_5').prop('disabled', true);
                        }
                        kredit_code_6 = data.transcoa_kredit6code;
                        if (kredit_code_6 && kredit_code_6 !== 'NONE' && kredit_code_6 !== '0') {
                            $('#kredit_code_6').val(data.transcoa_kredit6code + ' - ' + data.kredit6_desc);
                            $('#total_val_kredit_6').prop('disabled', false);
                        } else {
                            $('#total_val_kredit_6').prop('disabled', true);
                        }
                        $('#loader_body').hide();
                    },
                    error: function(xhr, status, error) {
                        $('#loader_body').hide();
                        console.error('Error fetching data:', error);
                    }
                });
            }
            $('#apply_filter').on('click', function() {
                get_detail_coa();
            });

            $('.input_money').on('input', function() {
                let value = $(this).val();
                value = value.replace(/[^0-9,]/g, '');
                let parts = value.split(',');
                let intPart = parts[0];
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                if (parts.length > 1) {
                    let decimalPart = parts[1].substring(0, 3);
                    value = intPart + ',' + decimalPart;
                } else {
                    value = intPart;
                }

                $(this).val(value);


                debet_val_1 = parseIndoNumber($('#total_val_1').val());
                debet_val_2 = parseIndoNumber($('#total_val_2').val());
                debet_val_3 = parseIndoNumber($('#total_val_3').val());
                debet_val_4 = parseIndoNumber($('#total_val_4').val());
                debet_val_5 = parseIndoNumber($('#total_val_5').val());
                debet_val_6 = parseIndoNumber($('#total_val_6').val());
                kredit_val_1 = parseIndoNumber($('#total_val_kredit_1').val());
                kredit_val_2 = parseIndoNumber($('#total_val_kredit_2').val());
                kredit_val_3 = parseIndoNumber($('#total_val_kredit_3').val());
                kredit_val_4 = parseIndoNumber($('#total_val_kredit_4').val());
                kredit_val_5 = parseIndoNumber($('#total_val_kredit_5').val());
                kredit_val_6 = parseIndoNumber($('#total_val_kredit_6').val());

                total_kredit = (kredit_val_1 + kredit_val_2 + kredit_val_3 + kredit_val_4 + kredit_val_5 + kredit_val_6);
                total_debit = (debet_val_1 + debet_val_2 + debet_val_3 + debet_val_4 + debet_val_5 + debet_val_6);
                $('#text_debit').text(formatIndoNumber(total_debit));
                $('#text_kredit').text(formatIndoNumber(total_kredit));

                if (total_debit > total_payment_text) {
                    $('#alert_debit').show();
                } else {
                    $('#alert_debit').hide();
                }

                if (total_debit < total_payment_text) {
                    $('#alert_debit2').show();
                } else {
                    $('#alert_debit2').hide();
                }
                if (total_kredit > total_payment_text) {
                    $('#alert_kredit').show();
                } else {
                    $('#alert_kredit').hide();
                }
                if (total_kredit < total_payment_text) {
                    $('#alert_kredit2').show();
                } else {
                    $('#alert_kredit2').hide();
                }
                console.log(total_payment_text);
                console.log(total_debit);
                console.log(total_kredit);
            });

            function parseIndoNumber(str) {
                if (!str) return 0;
                let clean = str.replace(/\./g, '');
                clean = clean.replace(',', '.');
                let num = parseFloat(clean);
                return isNaN(num) ? 0 : num;
            }

            function formatIndoNumber(number) {
                if (isNaN(number)) return '0';

                number = parseFloat(number);

                let parts = number.toFixed(0).split('.');
                let intPart = parts[0];
                let decimalPart = parts[1];

                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                return intPart;
            }
            $('#select_header_coa').select2({
                theme: 'custom'
            });
            $('#btn_info').on('click', function() {
                $('#modalinfo').modal('show');
                initializeDataTable();
            });

            function initializeDataTable() {
                $('#loader_search').show();
                if ($.fn.DataTable.isDataTable('#table_info')) {
                    $('#table_info').DataTable().clear().destroy();
                }
                $('#table_info').show();
                table = $('#table_info').DataTable({
                    processing: false,
                    serverSide: false,
                    ajax: {
                        url: '/get_detail_japfa',
                        type: 'GET',
                        dataSrc: '',
                        data: {
                            tahun: tahun,
                            bulan: bulan,
                            lokasi: $('#lokasi_input').val()
                        },
                    },
                    columns: [{
                            data: null,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        },
                        {
                            data: 'invimp_code',
                            name: 'invimp_code'
                        },
                        {
                            data: 'retailer_name',
                            name: 'retailer_name',
                            className: 'note-col'
                        },
                        {
                            data: 'Invoice_date',
                            name: 'Invoice_date'
                        },
                        {
                            data: 'invoice_number',
                            name: 'invoice_number'
                        },
                        {
                            data: 'total_price',
                            name: 'total_price'
                        },
                        {
                            data: 'SKU_description',
                            name: 'SKU_description'
                        },
                        {
                            data: 'qty',
                            name: 'qty'
                        },
                        // {
                        //     data: 'value_tagihan_dn',
                        //     name: 'value_tagihan_dn',
                        //     render: function(data, type, row) {
                        //         if (data === null || data === undefined || data === '') {
                        //             return '';
                        //         }

                        //         if (type === 'display') {
                        //             return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                        //                 useGrouping: true,
                        //                 minimumFractionDigits: 0,
                        //                 maximumFractionDigits: 0
                        //             });
                        //         }

                        //         return data;
                        //     }
                        // },
                    ],
                    // responsive: true,
                    searching: true,
                    paging: true,
                    autoWidth: false,
                    dom: '<"d-flex justify-content-between align-items-start"<"d-flex"Bl><"d-flex justify-content-end"f>><"table-responsive"t><"d-flex justify-content-between align-items-center"ip>',
                    scrollX: false,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "Semua"]
                    ],
                    buttons: [{
                        extend: 'excelHtml5',
                        text: 'Download Excel',
                        title: '--', // Nama file Excel (tanpa spasi)
                        title: 'Japfa ' + $('#lokasi_input').val() + ' ' + tahun + ' - ' + bulan,
                        sheetName: '--', // Nama sheet dalam Excel
                        exportOptions: {
                            columns: ':visible' // Kolom yang terlihat saja yang diekspor
                        }
                    }],
                    language: {
                        lengthMenu: "_MENU_",
                        search: "Pencarian:",
                        zeroRecords: "Tidak ada data yang ditemukan",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                        infoEmpty: "Tidak ada data",
                        infoFiltered: "(disaring dari _MAX_ total entri)",
                        @include('layouts.emptytable')
                    },
                    initComplete: function(settings, json) {
                        $('#loader_search').hide();
                    },
                    footerCallback: function(row, data, start, end, display) {
                        let api = this.api();

                        const intVal = function(i) {
                            return typeof i === 'string' ?
                                parseFloat(i.replace(/[^0-9.-]+/g, '')) || 0 :
                                typeof i === 'number' ? i : 0;
                        };

                        const totalqty = api.column(7, {
                            page: 'all'
                        }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                        const totalqtycur = api.column(7, {
                            page: 'current'
                        }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                        const formatRupiah = function(angka) {
                            return angka.toLocaleString('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 2
                            });
                        };
                        // $('#totalqtycur').html(formatRupiah(totalqtycur));
                        // $('#totalqty').html(formatRupiah(totalqty));
                        $('#totalqtycur').html((totalqtycur));
                        $('#totalqty').html((totalqty));
                    },
                });
            }

            function formatRupiah(angka) {
                const number = parseFloat(angka);
                return isNaN(number) ?
                    '0' :
                    number.toLocaleString('id-ID', {
                        style: 'decimal',
                        minimumFractionDigits: 0
                    });
            }
        });
    </script>
@endsection
@section('content')
    <div>
        <div class="row">
            <div class="col-12 col-md-auto mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <select name="" id="lokasi_input" class="form-control form-control-sm mb-3">
                            <option value="">Pilih Lokasi</option>
                            <option value="JKT">Cipinang</option>
                            <option value="TNG">Tanggerang</option>
                        </select>
                        <div class="form-group mb-0">
                            <div class="input-group mb-0">
                                <input class="form-control form-control-sm mb-0" type="month" id="input_main_code">
                                <span class="input-group-text" id="btn_search" style="cursor: pointer"><i class="fa-solid fa-magnifying-glass"></i></span>
                            </div>
                            <div class="d-flex justify-content-center align-items-center mt-2">
                                <div class="loader" style="display: none" id="loader_search"></div>
                            </div>
                        </div>
                        {{-- <button type="button" class="btn bg-gradient-info btn-sm btn-rounded mb-0" id="btn_list_kwitansi">Tampilkan List Kwitansi</button> --}}
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
                                <div class="row">
                                    <div class="col-auto tgl_kwitansi">
                                        <small>Tgl Kwitansi:</small>
                                    </div>
                                    <div class="col-auto tgl_kwitansi">
                                        <input type="date" class="form-control form-control-sm" id="tgl_kwitansi">
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn bg-gradient-success btn-sm btn-rounded" id="btn_confirm" style="display: none">Confirm</button>
                                        <button type="button" class="btn bg-gradient-success btn-sm btn-rounded" id="btn_info" style="">Tampilkan data</button>
                                    </div>
                                </div>
                                <button type="button" class="btn bg-gradient-info btn-sm btn-rounded" id="btn_print" style="display: none">Print Kwitansi</button>
                                <button type="button" class="btn bg-gradient-warning btn-sm btn-rounded" id="btn_inv" style="display: none">Print Invoice</button>
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
            <div class="col-12 col-md-12 mb-3 div_from_hide" style="display: none">
                <div class="card mb-3 h-100">
                    <div class="card-body mb-0">
                        <div class="row">
                            <div class="col-auto mb-3 div_input">
                                <select class="form-select form-select-sm" aria-label="Large select example" id="select_header_coa">
                                    <option selected>Pilih Coa Transaksi</option>
                                </select>
                            </div>
                            <div class="col-auto mb-1 border-end div_input">
                                <button class="btn bg-gradient-primary btn-sm" id="apply_filter">Apply</button>
                            </div>
                            <div class="col-auto border-end div_input">
                                <input type="date" class="form-control form-control-sm" id="payment_date">
                                <label>Payment date</label>
                            </div>
                            <div class="col-auto div_input">
                                <select class="form-control form-control-sm" id="select_paytipe">
                                    <option value="">Payment model</option>
                                    <option value="1">Cash</option>
                                    <option value="2">Transfer</option>
                                    <option value="3">Giro</option>
                                    <option value="4">Cheque</option>
                                </select>
                                <label>Payment model</label>
                            </div>
                            {{-- <div class="col-12 col-md-3 mb-3">
                                <select class="form-select form-select-sm" aria-label="Large select example" id="select_cabang">
                                    <option value="">Pilih Cabang</option>
                                </select>
                            </div> --}}
                            <div class="col-12"></div>
                            <div class="col-12 col-md-6 border-end">
                                <div class="row">
                                    <h6 class="text-center">Debit (Rp. <span id="text_debit">0,00</span>)</h6>
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="debet_code_1" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_1" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="debet_code_2" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_2" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="debet_code_3" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_3" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="debet_code_4" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_4" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="debet_code_5" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_5" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="debet_code_6" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_6" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <small id="alert_debit2" class="text-warning" style="display: none"><i class="fa-solid fa-circle-exclamation"></i> Pastikan jumlah yang diinputkan sama dengan total yang ditetapkan.</small>
                                        <small id="alert_debit" class="text-danger" style="display: none"><i class="fa-solid fa-circle-exclamation"></i> Jumlah yang diinputkan melebihi total yang ditetapkan.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row">
                                    <h6 class="text-center">Kredit (Rp. <span id="text_kredit">0,00</span>)</h6>
                                    <!-- Kredit 1 -->
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="kredit_code_1" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_kredit_1" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>

                                    <!-- Kredit 2 -->
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="kredit_code_2" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_kredit_2" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>

                                    <!-- Kredit 3 -->
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="kredit_code_3" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_kredit_3" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>

                                    <!-- Kredit 4 -->
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="kredit_code_4" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_kredit_4" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>

                                    <!-- Kredit 5 -->
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="kredit_code_5" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_kredit_5" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>

                                    <!-- Kredit 6 -->
                                    <div class="col-7">
                                        <input type="text" class="form-control form-control-sm mb-1" id="kredit_code_6" placeholder="...." disabled>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input_money" id="total_val_kredit_6" placeholder="Total Payment" disabled>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <small id="alert_kredit2" class="text-warning" style="display: none"><i class="fa-solid fa-circle-exclamation"></i> Pastikan jumlah yang diinputkan sama dengan total yang ditetapkan.</small>
                                        <small id="alert_kredit" class="text-danger" style="display: none"><i class="fa-solid fa-circle-exclamation"></i> Jumlah yang diinputkan melebihi total yang ditetapkan.</small>
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
    <div class="modal fade" id="modalinfo" tabindex="-1" aria-labelledby="modalinfo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <table class="table text-wraped" id="table_info">
                        <thead>
                            <th></th>
                            <th>Invoice Number</th>
                            <th>Invoice Partner Display Name</th>
                            <th>Inv Date</th>
                            <th>Sale Order</th>
                            <th>Total Signed</th>
                            <th>Product </th>
                            <th>Qty </th>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-end">Total:</th>
                                <th id="totalqtycur"></th>
                            </tr>
                            <tr>
                                <th colspan="7" class="text-end">Grand Total:</th>
                                <th id="totalqty"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('harus_ada')
