@extends('layouts.user_type.auth')
@section('title', 'DN System - DN Payment')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            let startDate = '';
            let endDate = '';
            let tampunganData = [];
            let url_code;
            // initializeDataTable();
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
            $('#loader_body').hide();
            function initializeDataTable() {
                $('#loader_body').show();

                if ($.fn.DataTable.isDataTable('#table_users')) {
                    $('#table_users').DataTable().clear().destroy();
                }
                $('#div_table_users').show();
                table = $('#table_users').DataTable({
                    serverSide: false,
                    ajax: {
                        url: '/get_data_dn_payment',
                        type: 'GET',
                        dataSrc: '',
                        data: {
                            startDate: startDate,
                            endDate: endDate,
                            client_code: $('#select_client').val(),
                            cabang_code: $('#select_cabang').val(),
                        }
                    },
                    columns: [
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                return `<input type="checkbox" class="form-check-input row-checkbox">`;
                            }
                        },{
                            data: null,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        },
                        { data: 'salesdntagih_code_h', name: 'salesdntagih_code_h' },
                        { data: 'salesdntagih_client_code', name: 'salesdntagih_client_code' },
                        { data: 'cab_desc', name: 'cab_desc' },
                        {
                            data: 'total',
                            name: 'total',
                            render: function(data, type, row) {
                                if (data === null || data === undefined || data === '') {
                                    return '';
                                }

                                if (type === 'display') {
                                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                                        useGrouping: true,
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }

                                return data;
                            }
                        },
                    ],
                    // responsive: true,
                    searching: true,
                    scrollY: '200px',
                    paging: false,
                    autoWidth: false,
                    scrollX: true,
                    info: false,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "Semua"]
                    ],
                    buttons: [{
                        extend: 'excel',
                        text: 'Download Excel',
                    }],
                    language: {
                        lengthMenu: "_MENU_",
                        search: "Pencarian:",
                        zeroRecords: "Tidak ada data yang ditemukan",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                        infoEmpty: "Tidak ada data",
                        infoFiltered: "(disaring dari _MAX_ total entri)",
                        @include('layouts.emptytable'),
                        processing: false,
                    },
                    initComplete: function(settings, json) {
                        $('.div_from_hide').show();
                        $('#loader_body').hide();
                    }
                });
            }
            $('#apply_filter_for_table').on('click',function(){
                if (
                    startDate == "" &&
                    endDate == "" &&
                    $('#select_client').val() == "" &&
                    $('#select_cabang').val() == ""
                ) {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Semua field masih kosong. Silakan isi minimal satu.
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
                if ($('#select_client').val() == "") {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Data tidak dapat diproses. Silakan pilih Client terlebih dahulu.
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
                $('.div_from_hide').show();
                $('#sales_text').text('Rp 0,00');
                initializeDataTable();
            });
            let total_payment_text;
            function updateTotalSales() {
                // const total = tampunganData.reduce((sum, item) => {
                //     return sum + parseFloat(item.salesdntagih_Total_sales || 0);
                // }, 0);
                // total_payment_text = total;
                // $('#sales_text').text(
                //     total.toLocaleString('id-ID', {
                //         style: 'currency',
                //         currency: 'IDR',
                //         minimumFractionDigits: 0,
                //         maximumFractionDigits: 3
                //     })
                // );
                const total = tampunganData.reduce((sum, item) => {
                    return sum + parseInt(item.total || 0);
                }, 0);
                total_payment_text = total;
                $('#sales_text').text(
                    total.toLocaleString('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    })
                );
            }
            function formatIndoNumber(number) {
                if (isNaN(number)) return '0';

                number = parseFloat(number);

                let parts = number.toFixed(0).split('.');
                let intPart = parts[0];
                let decimalPart = parts[1];

                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                return intPart;
                // return intPart + ',' + decimalPart;
            }
            $('#table_users tbody').on('change', '.row-checkbox', function () {
                const tr = $(this).closest('tr');
                const row = table.row(tr);
                const rowData = row.data();

                if (this.checked) {
                    const modifiedRow = $.extend({}, rowData);
                    tampunganData.push(modifiedRow);
                } else {
                    const index = tampunganData.findIndex(item => item.no_kwitansi === rowData.no_kwitansi);
                    if (index > -1) {
                        tampunganData.splice(index, 1);
                    }
                }
                updateTotalSales();
                total_kredit = (kredit_val_1 + kredit_val_2 + kredit_val_3 + kredit_val_4 + kredit_val_5 + kredit_val_6);
                total_debit = (debet_val_1 + debet_val_2 + debet_val_3 + debet_val_4 + debet_val_5 + debet_val_6);

                 if(total_debit > total_payment_text){
                    $('#alert_debit').show();
                }else{
                    $('#alert_debit').hide();
                }

                if(total_debit < total_payment_text){
                    $('#alert_debit2').show();
                }else{
                    $('#alert_debit2').hide();
                }
                if(total_kredit > total_payment_text){
                    $('#alert_kredit').show();
                }else{
                    $('#alert_kredit').hide();
                }
                if(total_kredit < total_payment_text){
                    $('#alert_kredit2').show();
                }else{
                    $('#alert_kredit2').hide();
                }
                console.log(tampunganData);
            });
            $(document).on('change', '.activate-toggle', function() {
                const id = $(this).data('id');
                const newState = $(this).prop('checked') ? 1 : 0;

                // Kirim request AJAX ke rute update-activation
                $.ajax({
                    url: '/update-activation/' + id,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF Token
                        activate: newState
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Tampilkan notifikasi atau pembaruan lainnya jika perlu
                            // console.log('State updated to ' + response.newState);
                        }
                    },
                    error: function() {
                        // Tangani jika ada kesalahan
                        alert('Terjadi kesalahan saat memperbarui status.');
                    }
                });
            });
            get_header_coa();
            function get_header_coa(){
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
                            branchSelect.append('<option value="' + item.transcoa_code + '"> '  + item.transcoa_code + ' | ' + item.transcoa_desc + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching branches:', error);
                    }
                });
            }
            function get_detail_coa(){
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
                        }else{
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
            function updateTable() {
                $('#loader_body').show();
                $.ajax({
                    url: '/dn-payment/store-payment',
                    type: 'POST',
                    data: {
                        startDate: startDate,
                        endDate: endDate,
                        total_payment_text: total_payment_text,
                        tampunganData: tampunganData,
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
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#loader_body').hide();
                        new Noty({
                            text: '<i class="fas fa-check"></i> Data berhasil dikonfirmasi.',
                            type: 'info',
                            timeout: 3000,
                            layout: 'topRight'
                        }).show();
                        $('.row-checkbox').prop('disabled', true);
                        $('.input_money').prop('disabled', true);
                        $('#btn_confirm').hide();
                        $('#new_transaction').show();
                        $('#btn_print').show();
                        $('.div_input').hide();
                        url_code = response.code_payment;
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'An Error Occurred',
                            text: 'Failed to send data to the server. Please try again later.',
                        });
                        $('#loader_body').hide();
                        console.error('Error:', error);
                    }
                });
            }
            // get_cabang();
            function get_cabang(){
                $.ajax({
                url: '/get_cabang',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var branchSelect = $('#select_cabang');
                        branchSelect.empty();
                        branchSelect.append('<option value="">Pilih Cabang</option>');

                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        data.forEach(function(item) {
                            branchSelect.append('<option value="' + item.cab_code + '">' + item.cab_desc + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching branches:', error);
                    }
                });
            }
            get_client();
            function get_client(){
                $.ajax({
                url: '/get_client',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var branchSelect = $('#select_client');
                        branchSelect.empty();
                        branchSelect.append('<option value="">Client</option>');

                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        data.forEach(function(item) {
                            branchSelect.append('<option value="' + item.clien_id + '">' + item.clien_id +' | '+ item.clien_desc + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching branches:', error);
                    }
                });
            }
            function parseIndoNumber(str) {
            if (!str) return 0;

                // Hapus titik ribuan
                let clean = str.replace(/\./g, '');

                // Ganti koma desimal jadi titik desimal
                clean = clean.replace(',', '.');

                // Parse ke float
                let num = parseFloat(clean);

                return isNaN(num) ? 0 : num;
            }
            $('#btn_confirm').click(function() {
                if (tampunganData.length === 0) {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Harap pilih setidaknya satu Kwitansi!
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
                // if (total_debit != total_payment_text && total_kredit != total_payment_text) {
                //     new Noty({
                //         text: `
                //             <div>
                //                 <strong style="color: #dc3545;">
                //                     <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                //                 </strong>
                //                 <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                //                     Jumlah input dan jumlah total pembayaran tidak sesuai. Pastikan keduanya sama sebelum melanjutkan.
                //                 </p>
                //                 <small style="color: #007bff; font-size: 11px; font-style: italic;">
                //                     Klik di sini untuk menutup pesan ini
                //                 </small>
                //             </div>
                //         `,
                //         type: 'alert',
                //         layout: 'center',
                //         timeout: 6000,
                //         theme: 'bootstrap-v4',
                //         modal: true,
                //         killer: true,
                //     }).show();
                //     return;
                // }
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
                            updateTable();
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
            $('#btn_print').click(function() {
                var url = '/cetak-pdf/payment?code=' + encodeURIComponent(url_code);
                window.open(url, '_blank');
            });
            $('#apply_filter').on('click', function() {
                get_detail_coa();
            });
            $('#new_transaction').on('click', function() {
                Swal.fire({
                    icon: 'question',
                    title: 'Start New Transaction?',
                    text: 'Are you sure you want to start a new transaction? All current data will be reset.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, start new',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cancelled',
                            text: 'The new transaction has been cancelled. Your current data remains unchanged.',
                        });
                    }
                });
            });
            $('#select_header_coa').select2({
                theme: 'custom'
            });
            $('#select_cabang').select2({
                theme: 'custom'
            });
            $('#select_client').select2({
                theme: 'custom'
            });
            // $('#select_paytipe').select2({
            //     theme: 'custom'
            // });
            $('.input_money').on('input', function() {
                let value = $(this).val();
                value = value.replace(/[^0-9,]/g, '');
                let parts = value.split(',');
                let intPart = parts[0];
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                if(parts.length > 1){
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

                if(total_debit > total_payment_text){
                    $('#alert_debit').show();
                }else{
                    $('#alert_debit').hide();
                }

                if(total_debit < total_payment_text){
                    $('#alert_debit2').show();
                }else{
                    $('#alert_debit2').hide();
                }
                if(total_kredit > total_payment_text){
                    $('#alert_kredit').show();
                }else{
                    $('#alert_kredit').hide();
                }
                if(total_kredit < total_payment_text){
                    $('#alert_kredit2').show();
                }else{
                    $('#alert_kredit2').hide();
                }
                console.log(total_payment_text);
                console.log(total_debit);
                console.log(total_kredit);
            });
            let dateRange_sales_biaya = $('#date_range').daterangepicker({
                autoUpdateInput: false,
                ranges: {
                    'Hari ini': [moment().subtract('days'), moment()],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                    'Last 3 Year': [moment().subtract(3, 'year').startOf('year'), moment().endOf('year')],
                    'Semester 1': [moment().startOf('year'), moment().month(5).endOf('month')],
                    'Semester 2': [moment().month(6).startOf('month'), moment().endOf('year')] ,
                    'all data': ['2012-01-01', moment().endOf('year')],
                },
                locale: {
                    format: 'YYYY-MM-DD'
                }
            }, function(start, end, label) {
                if (label === "Pilih Tanggal") {
                    $('#date_range').val('');
                    startDate = '';
                    endDate = '';
                } else {
                    $('#date_range').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
                    startDate = start.format('YYYY-MM-DD');
                    endDate = end.format('YYYY-MM-DD');
                }
            });

            $('#date_range').val('');
        });
    </script>
@endsection
@section('content')

    <div>
        <div class="lds-roller" id="loader_body">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
        <div class="row">
            <div class="col-12 col-md-5 mb-3">
                <div class="card mb-3 h-100">
                    <div class="card-body mx-0 p-0">
                        <div class="row px-4 pt-4 div_input">
                            <div class="col-12 col-md-3">
                                <input type="text" id="date_range" class="form-control form-control-sm" placeholder="Pilih Tanggal" />
                            </div>
                            <div class="col-12 col-md-3">
                                <select class="form-select form-select-sm" aria-label="Large select example" id="select_client">
                                    <option selected>Client</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <select class="form-select form-select-sm" aria-label="Large select example" id="select_cabang">
                                    <option value="">Cabang</option>
                                    <option value="0001">HGS-Sentul</option>
                                    <option value="0002">HGS-Ciherang</option>
                                    <option value="0003">HGS-Subang</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <button class="btn bg-gradient-primary btn-sm" id="apply_filter_for_table">Apply</button>
                            </div>
                        </div>
                        <div class="div_from_hide" style="display: none">
                            <table class="table align-items-center mb-0 mt-0 table-sm" id="table_users">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th>Kwitansi</th>
                                        <th>Client</th>
                                        <th>Cabang</th>
                                        <th>Tagihan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div class="col-12 mb-1 d-flex">
                                <div class="total-sales-card ms-auto mx-3">
                                    <div id="sales_text" class="value">Rp 0,00</div>
                                </div>
                                <style>
                                    .total-sales-card {
                                        background-color: #f8f9fa;
                                        border: 1px solid #dee2e6;
                                        border-radius: 12px;
                                        padding: 10px 15px;
                                        max-width: 250px;
                                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
                                        margin: 0;
                                    }

                                    .total-sales-card .label {
                                        font-size: 14px;
                                        color: #6c757d;
                                        margin-bottom: 6px;
                                    }

                                    .total-sales-card .value {
                                        font-size: 20px;
                                        font-weight: 600;
                                        color: #212529;
                                    }

                                </style>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .div_iner {
                    border: 3px solid #6f559e38;
                    padding: 10px;
                    border-radius: 15px;
                }
            </style>
            <div class="col-12 col-md-7 mb-3 div_from_hide" style="display: none">
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
                            <div class="col-12  mb-0 mt-3">
                                <hr>
                                <button class="btn bg-gradient-info btn-sm mb-0 float-end" id="btn_confirm">Confirm Transaction</button>
                                <button class="btn btn-sm bg-gradient-warning" id="new_transaction" style="display: none">New Transaction</button>
                                <button type="button" class="btn bg-gradient-info btn-sm mx-3" id="btn_print" style="display: none">Print</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modalButton"></div>
@endsection
@include('harus_ada')
