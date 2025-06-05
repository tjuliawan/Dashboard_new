@extends('layouts.user_type.auth')
@section('title', 'DN tagih - Edit Tgaih Sales DN')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
        // inisialisasi varibel utama 
            let tampunganData = [];
            let tampunganData_add = [];
            let pruduct_code;
            let allowRowClick = 1;
            let total_tagihan = 0;            
            let client_code = "";
            let is_load_for_tabel_tampungan_add = 0;
            let akses_dari;
            let username_pemeberi_akses = "";
            let product = "";
            let code_head = "";
            $('#loader_body').hide();
        // fungsi print
            $('#btn_print_report').click(function() {
                var client_code = $('#select_client').val();
                                    
                if(product === 'Water Tanker'){
                    var url = '/cetak-pdf/dn-tagih-inv-wt?code=' + encodeURIComponent(code_head) +
                    '&client_code=' + encodeURIComponent(client_code);
                    window.open(url, '_blank');
                }else{
                    var url = '/cetak-pdf/dn-tagih-inv?code=' + encodeURIComponent(code_head) +
                        '&client_code=' + encodeURIComponent(client_code);
                    window.open(url, '_blank');
                    if(client_code === 'TUA'){
                        var url2 = '/cetak-pdf/dn-tagih-kwitansi?code=' + encodeURIComponent(code_head) +
                            '&client_code=' + encodeURIComponent(client_code);
                        window.open(url2, '_blank');
                    }
                }
            });
        // fungsi Pencarian kode dn
            $('#btn_search').click(function() {
                if($('#input_main_code').val() === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please Intert the code!',
                    });
                    return;
                }
                $('#loader_search').show();
                $.ajax({
                    url: '/get_header_dn_tagih',
                    type: 'get',
                    data: {
                        code: $('#input_main_code').val()
                    },
                    success: function (response) {
                        total_tagihan = response.salesdntagih_Total_tagihan;   
                        client_code = response.salesdntagih_client_code; 
                        code_head = response.salesdntagih_code_h;  
                        product = response.salesdntagih_product_code;
                        var not_allowed_edit = response.no_kwitansi;
                        if (not_allowed_edit === '1') {
                            new Noty({
                                text: `
                                    <div>
                                        <strong style="color: #dc3545;"><i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Kwitansi sudah dibuat</strong>
                                        <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">Transaksi ini sudah memiliki kwitansi. Silakan pilih tagihan lainnya untuk diproses.</p>
                                        <small style="color: #007bff; font-size: 11px; font-style: italic;">Klik disini untuk menutup pesan ini</small>
                                    </div>
                                `,
                                type: 'alert',
                                layout: 'center',
                                timeout: 8000,
                                theme: 'bootstrap-v4',
                                modal: true,
                                // killer: true, 
                            }).show();

                            $('#loader_search').hide();
                        } else {
                            // initializeDataTable();
                            $('#loader_search').hide();
                            $('.div_confirmation').show();
                            $('.div_add_more_data').show();
                        }
                    },
                    error: function (xhr, status, error) {
                        errormessage =  xhr.responseJSON.message;
                        $('.div_confirmation').hide();
                        $('#div_table_list_tr_tagih_sales_DN_d_date').hide();
                        if ($.fn.DataTable.isDataTable('#table_list_tr_tagih_sales_DN_d_date')) {
                            $('#table_list_tr_tagih_sales_DN_d_date').DataTable().clear().destroy();
                        }
                        if ($.fn.DataTable.isDataTable('#table_tampungan')) {
                            $('#table_tampungan').DataTable().clear().destroy();
                        }
                        $('#loader_search').hide();
                         new Noty({
                            text: `
                                <div>
                                    <strong style="color: #dc3545;"><i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Terjadi Masalah</strong>
                                    <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">${errormessage}</p>
                                    <small style="color: #007bff; font-size: 11px; font-style: italic;">Klik disini untuk menutup pesan ini</small>
                                </div>
                            `,
                            type: 'alert',
                            layout: 'center',
                            timeout: 8000,
                            theme: 'bootstrap-v4',
                            modal: true,
                            // killer: true, 
                        }).show();
                        console.error('Error:', error);
                    }
                });
            });
            $('#input_main_code').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#btn_search').click();
                }
            });
        // auth checking untuk konfirmasi
            $('#confirm_login').on('click', function () {
                var email = $('input[placeholder="Email/Username"]').val();
                var password = $('input[placeholder="Password"]').val();
                $('#loader_user_confirm').show();
                $.ajax({
                    url: '/check-login',
                    type: 'POST',
                    data: {
                        email: email,
                        password: password,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#loader_user_confirm').hide();
                            var bolehAkses = false;
                            akses_dari = response.name;
                            username_pemeberi_akses = response.username;

                            if (
                                response.role === 'Administrator' ||
                                (
                                    response.divisi === 'Finance' &&
                                    (response.sub_divisi === 'Manager' || response.sub_divisi === 'Supervisor')
                                )
                            ) {
                                bolehAkses = true;
                            }

                            if (bolehAkses) {
                                updateTable();
                            } else {
                                alert("⛔ Akses ditolak.\nAnda tidak memiliki izin untuk melakukan transaksi ini.\nSilakan hubungi administrator atau pihak yang berwenang untuk informasi lebih lanjut.");
                            }

                        } else {
                            alert(response.message);
                            $('#loader_user_confirm').hide();
                        }

                    },
                    error: function () {
                        alert('Terjadi kesalahan. Coba lagi nanti.');
                    }
                });
            });
            $('#btn_confirm_editing-').on('click', function() {
                var code_header = $('#header_code').val(); 
                var code_po = $('#input_po_code').val(); 
                if (code_po === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please insert a Po Code!',
                    });
                    $('#loader_body').hide();
                    return;
                }
                Swal.fire({
                    icon: 'question',
                    title: 'Confirm Transaction',
                    text: 'Are you sure you want to confirm this transaction?',
                    showCancelButton: true, 
                    confirmButtonText: 'Yes, Confirm',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/dn-tagih/update/details-data',  
                            type: 'POST',
                            data: {
                                tampunganData: tampunganData,
                                _token: $('meta[name="csrf-token"]').attr('content') 
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Transaction Confirmed!',
                                    text: 'Your transaction has been successfully processed.',
                                });
                                $('#input_po_code').hide();
                                $('#input_po_code').val('');
                                $('#btn_print').show();
                                $('#btn_add_po_code').show();
                                $('#btn_confirm_po_code').hide();
                                $('#btn_cancel_po_code').hide();
                                allowRowClick = 1;
                                initializeDataTable();
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'An Error Occurred',
                                    text: 'Failed to send data to the server. Please try again later.',
                                });
                                console.error('Error:', error);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cancelled',
                            text: 'The transaction has been cancelled.',
                        });
                    }
                });
            });
            $('#btn_confirm_editing').on('click', function() {
                if (tampunganData.length === 0 && tampunganData_add.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select a code and make changes to proceed with the transaction!',
                    });
                    return;
                }
                Swal.fire({
                    icon: 'question',
                    title: 'Confirm Transaction',
                    text: 'Are you sure you want to confirm this transaction?',
                    showCancelButton: true, 
                    confirmButtonText: 'Yes, Confirm',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#Modal_validate').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cancelled',
                            text: 'The transaction has been cancelled.',
                        });
                    }
                });
            });
        // fungsi store data
            $('#btn_add_more').on('click', function() {
                $('.div_add_more_data').show();
                $('#btn_cancel_add_more').show();
                $('#btn_add_more').hide();
            });
            $('#new_transaction').on('click', function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You are about to refresh the page!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, refresh it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            });
            $('#btn_cancel_add_more').on('click', function() {
                $('.div_add_more_data').hide();
                $('#btn_cancel_add_more').hide();
                $('#btn_add_more').show();
                $('#div_table_add_tagih_sales_dn').hide();
            });
            $('#btn_search_for_add_more').on('click', function() {
                if($('#input_add_search_dn').val() === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please Intert the code!',
                    });
                    return;
                }
                $('#div_table_add_tagih_sales_dn').show();
                initializeDataTable_add();
                if(is_load_for_tabel_tampungan_add === 0){
                    is_load_for_tabel_tampungan_add = 1;
                    initializeDataTable_add_tampungan();
                }
            });
            function updateTable() {
                $('#loader_user_confirm').show();
                $.ajax({
                    url: '/dn-tagih/update/details-data',  
                    type: 'POST',
                    data: {
                        tampunganData: tampunganData,
                        total_tagihan: total_tagihan,
                        tampunganData_add: tampunganData_add,
                        akses_dari: akses_dari,
                        username_pemeberi_akses: username_pemeberi_akses,
                        is_from_ba: 1,
                        header_code: $('#input_main_code').val(),
                        _token: $('meta[name="csrf-token"]').attr('content') 
                    },
                    success: function(response) {
                        $('#loader_user_confirm').hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Transaction Confirmed!',
                            text: 'Your transaction has been successfully processed.',
                        });
                            $('#Modal_validate').modal('hide');
                            let tampunganData = [];
                            let tampunganData_add = [];
                            if ($.fn.DataTable.isDataTable('#table_add_tagih_sales_dn')) {
                                $('#table_add_tagih_sales_dn').DataTable().clear().destroy();
                            }
                            if ($.fn.DataTable.isDataTable('#table_tampungan_add')) {
                                $('#table_tampungan_add').DataTable().clear().destroy();
                            }
                            $('.div_add_more_data').hide();
                            $('#btn_cancel_add_more').hide();
                            $('#btn_add_more').hide();
                            $('#new_transaction').show();
                            $('#btn_print_report').show();
                            $('#btn_confirm_editing').hide();
                            $('#div_tabel_tampungan').hide();
                            $('#div_table_add_tagih_sales_dn').hide();
                            initializeDataTable();
                            allowRowClick = 1;
                            $('input[placeholder="Email/Username"]').val('');
                            $('input[placeholder="Password"]').val('');
                            $('#btn_search').hide();
                            $('#input_main_code').prop('disabled', true);
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
        // inisialisasi tabel  
            // tabel add
                function initializeDataTable_add() {
                    $('#loader_body').show();
                    if ($.fn.DataTable.isDataTable('#table_add_tagih_sales_dn')) {
                        $('#table_add_tagih_sales_dn').DataTable().clear().destroy();
                    }
                    $('#div_table_add_tagih_sales_dn').show();
                    add_tableMain = $('#table_add_tagih_sales_dn').DataTable({
                        serverSide: false,
                        ajax: {
                            url: '/dn_tagih/get_table_add_tagih_sales_dn_from_ba',
                            type: 'GET',
                            dataSrc: '',
                            data: {
                                co_code: $('#input_add_search_dn').val(),
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
                            },
                            // {
                                //     data: null,
                                //     render: function(data, type, row, meta) {
                                    //         return meta.row + 1;
                                    //     }
                                    // },
                                    { data : 'Sales_DN_Code_d' , name : 'Sales_DN_Code_d' },
                                    { data : 'Sales_DN_date' , name : 'Sales_DN_date' },
                                    { data : 'Sales_DN_COno' , name : 'Sales_DN_COno' },
                                    { data : 'Sales_DN_Driver' , name : 'Sales_DN_Driver' },
                                    { data : 'client_code' , name : 'client_code' },
                                    { data : 'cab_desc' , name : 'cab_desc' },
                                    { data : 'sales_dn_productcode' , name : 'sales_dn_productcode' },
                                    { data : 'Sales_DN_vehicle' , name : 'Sales_DN_vehicle' },
                                    { data : 'Sales_DN_route_product_client_vehicle' , name : 'Sales_DN_route_product_client_vehicle' },
                                    {
                                        data: 'Sales_DN_Productcodeqty',
                                        name: 'Sales_DN_Productcodeqty',
                                        render: function(data, type, row, meta) {
                                            return parseInt(data, 10) || 0; 
                                        }
                                    },
                                    {
                                        data: 'routveh_salesbotol',
                                name: 'routveh_salesbotol',
                                render: function(data, type, row) {
                                    if (data === null || data === undefined || data === '') {
                                        return '';
                                    }
                                    
                                    if (type === 'display') {
                                        return parseFloat(data).toLocaleString('id-ID', {
                                            useGrouping: true,
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                    
                                    return data;
                                }
                            },
                            {
                                data: 'totalsales',
                                name: 'totalsales',
                                render: function(data, type, row) {
                                    if (data === null || data === undefined || data === '') {
                                        return '';
                                    }

                                    if (type === 'display') {
                                        return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                                            useGrouping: true,
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 2
                                        });
                                    }

                                    return data;
                                }
                            },
                        ],
                        // responsive: true,
                        searching: true,
                        paging: true,
                        autoWidth: false,
                        scrollX: true,
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
                            infoFiltered: "(disaring dari _MAX_ total entri)", @include('layouts.emptytable') ,
                            processing: false,
                        },
                        drawCallback: function(settings) {
                        },
                        initComplete: function(settings, json) {
                            $('#loader_body').hide();                        
                            $('#sales_text').text('0,00');
                        }
                    });
                }
                function initializeDataTable_add_tampungan() {
                    $('#loader_body').show();
                    if ($.fn.DataTable.isDataTable('#table_tampungan_add')) {
                        $('#table_tampungan_add').DataTable().clear().destroy();
                    }
                    add_tableTampungan = $('#table_tampungan_add').DataTable({
                        columns: [
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                className: 'text-center',
                                render: function(data, type, row, meta) {
                                    return `<input type="checkbox" class="form-check-input tampungan-checkbox" checked>`;
                                }
                            },
                            { data: 'Sales_DN_Code_d' },
                            { data: 'Sales_DN_date' },
                            { data: 'Sales_DN_COno' },
                            { data: 'Sales_DN_Driver' },
                            { data: 'client_code' },
                            { data: 'cab_desc' },
                            { data: 'sales_dn_productcode' },
                            { data: 'Sales_DN_vehicle' },
                            { data: 'Sales_DN_route_product_client_vehicle' },
                            {
                                data: 'Sales_DN_Productcodeqty',
                                render: function (data) {
                                    return parseInt(data, 10) || 0;
                                }
                            },
                            {
                                data: 'routveh_salesbotol',
                                render: function (data, type) {
                                    if (data === null || data === undefined || data === '') {
                                        return '';
                                    }

                                    if (type === 'display') {
                                        return parseFloat(data).toLocaleString('id-ID', {
                                            useGrouping: true,
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 2
                                        });
                                    }

                                    return data;
                                }
                            },
                            {
                                data: 'totalsales',
                                render: function (data, type) {
                                    if (data === null || data === undefined || data === '') {
                                        return '';
                                    }

                                    if (type === 'display') {
                                        return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                                            useGrouping: true,
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 2
                                        });
                                    }

                                    return data;
                                }
                            },
                            {
                                data: 'note',
                                render: function (data, type, row) {
                                    return `<input type="text" class="form-control form-control-sm note-input" data-code="${row.Sales_DN_Code_d}" value="${data || ''}" />`;
                                }
                            }
                        ],
                        scrollX: true,
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
                            infoFiltered: "(disaring dari _MAX_ total entri)", @include('layouts.emptytable') 
                        },
                        processing: false,
                    });
                }
                $('#table_add_tagih_sales_dn tbody').on('change', '.row-checkbox', function () {
                    const tr = $(this).closest('tr');
                    const row = add_tableMain.row(tr);
                    const rowData = row.data();

                    if (this.checked) {
                        const modifiedRow = $.extend({}, rowData); // Salin data
                        modifiedRow.note = ''; // kosongkan dulu nilai note-nya

                        // Simpan referensi data ke tampunganData
                        tampunganData_add.push(modifiedRow);
                        add_tableTampungan.row.add(modifiedRow).draw(); // tambahkan ke tabel tampungan
                        row.remove().draw();
                    } else {
                        // Hapus dari tampunganData
                        const index = tampunganData_add.findIndex(item => item.Sales_DN_Code_d === rowData.Sales_DN_Code_d);
                        if (index > -1) {
                            tampunganData_add.splice(index, 1);
                        }

                        // Kembalikan ke tableMain
                        add_tableMain.row.add(rowData).draw();
                        row.remove().draw();
                    }
                    updateTotalSales();
                });
                $('#table_tampungan_add tbody').on('change', '.tampungan-checkbox', function () {
                    const tr = $(this).closest('tr');
                    const row = add_tableTampungan.row(tr);
                    const rowData = row.data();

                    if (!this.checked) {
                        const index = tampunganData_add.findIndex(item => item.Sales_DN_Code_d === rowData.Sales_DN_Code_d);
                        if (index > -1) {
                            tampunganData_add.splice(index, 1);
                        }
                        add_tableMain.row.add(rowData).draw();
                        row.remove().draw();
                    } else {
                        tampunganData_add.push(rowData);
                        add_tableTampungan.row.add(rowData).draw();
                        row.remove().draw();
                    }
                    updateTotalSales();
                });
                $('#table_tampungan_add tbody').on('input', '.note-input', function () {
                    const code = $(this).data('code');
                    const value = $(this).val();
                    const item = tampunganData_add.find(item => String(item.Sales_DN_Code_d) === String(code));
                    if (item) {
                        item.note = value;
                    }
                });
        // fungsi get data untuk dropdown
        // fprm selsect
            $('#select_client').select2({
                theme: 'custom'
            });
            $('#select_vehicle').select2({
                theme: 'custom'
            });
            $('#select_business').select2({
                theme: 'custom'
            });
        // load tanggal
            let today = new Date().toISOString().split('T')[0];
            $('#start_date').val(today);
            $('#end_date').val(today);

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
            <div class="col-12 col-md-auto mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="form-group">
                            <div class="input-group">
                                <input class="form-control form-control-sm" autocomplete="off" placeholder="Search DN Tagih Code" type="text" id="input_main_code" data-bs-toggle="tooltip" data-bs-placement="top" title="ex : INV-TSD-202505-xxxx">
                                <span class="input-group-text"  id="btn_search" style="cursor: pointer"><i class="fa-solid fa-magnifying-glass"></i></span>
                            </div>
                            <small>Please input your dn tagih code here</small>
                            <div class="d-flex justify-content-center align-items-center mt-2">
                                <div class="loader" style="display: none" id="loader_search"></div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-auto mb-3">
                <div class="card h-100 text-center div_add_more_data" style="display: none;">
                    <div class="card-body">
                        <div>
                            <input class="form-control form-control-sm mb-3" type="text" id="input_add_search_dn" placeholder="input your CO code here" data-bs-toggle="tooltip" data-bs-placement="right" title="Please input your Co code here ex : S25041708878">
                        </div>
                        <button class="btn bg-gradient-info btn-sm mb-0" id="btn_search_for_add_more"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-auto mb-3">
                <div class="card h-100 div_confirmation" style="display: none;">
                    <div class="card-body">
                        <div>
                            <button class="btn bg-gradient-success btn-sm " id="btn_confirm_editing">Confirm Editing</button>
                            <button class="btn btn-sm bg-gradient-success" id="btn_print_report" style="display: none">Print Report</button>
                        </div>                      
                        <button class="btn btn-sm bg-gradient-warning" id="new_transaction" style="display: none">New Transaction</button>
                    </div>
                </div>
            </div>
            <div class="col-12" id="div_table_add_tagih_sales_dn" style="display: none;">
                <div class="card mb-3 ">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-center">
                            <div>
                                <h5 style="text-decoration: underline;">Add Code</h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="table_add_tagih_sales_dn">
                                <thead>
                                    <tr>
                                        <th></th>
                                        {{-- <th></th> --}}
                                        <th>DN</th>
                                        <th>Date</th>
                                        <th>CO/SO</th>
                                        <th>Driver</th>
                                        <th>Client</th>
                                        <th>Cabang</th>
                                        <th>Product</th>
                                        <th>Vehicle</th>
                                        <th>Route</th>
                                        <th>Qty</th>
                                        <th>Sales Botol/ Jasa</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card mb-3 ">
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="table_tampungan_add">
                                <thead>
                                    <tr>
                                        <th></th>
                                        {{-- <th></th> --}}
                                        <th>DN</th>
                                        <th>Date</th>
                                        <th>CO/SO</th>
                                        <th>Driver</th>
                                        <th>Client</th>
                                        <th>Cabang</th>                                        
                                        <th>Product</th>
                                        <th>Vehicle</th>
                                        <th>Route</th>
                                        <th>Qty</th>
                                        <th>Sales Botol/ Jasa</th>
                                        <th>Value</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>  
            <div class="col-12 mb-3" id="div_table_list_tr_tagih_sales_DN_d_date" style="display: none;">
                <div class="card mb-3">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-center">
                            <div>
                                <h5 style="text-decoration: underline;">Remove Code</h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="table_list_tr_tagih_sales_DN_d_date">
                                <thead>
                                    <tr>
                                        <th></th>
                                        {{-- <th>rec_comcode</th>
                                        <th>rec_areacode</th> --}}
                                        <th>Code</th>
                                        <th>DN</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Product</th>
                                        <th>Sales DN codeheader</th>
                                        <th>CO code header</th>
                                        <th>CO</th>
                                        <th>PO Code</th>
                                        <th>Driver</th>
                                        <th>Route</th>
                                        <th>Vehicle</th>
                                        <th>qty</th>
                                        <th>salesbotol</th>
                                        <th>value</th>
                                        <th>note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card"  id="div_tabel_tampungan">
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="table_tampungan">
                                <thead>
                                    <tr>
                                        <th></th>
                                        {{-- <th>rec_comcode</th>
                                        <th>rec_areacode</th> --}}
                                        <th>Code</th>
                                        <th>DN</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Product</th>
                                        <th>Sales DN codeheader</th>
                                        <th>CO code header</th>
                                        <th>CO</th>
                                        <th>PO Code</th>
                                        <th>Driver</th>
                                        <th>Route</th>
                                        <th>Vehicle</th>
                                        <th>qty</th>
                                        <th>salesbotol</th>
                                        <th>value</th>
                                        <th>note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modalButton"></div>
    <div class="modal fade" id="Modal_validate" tabindex="-1" role="dialog" aria-labelledby="exampleModalSignTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
          <div class="modal-content">
            <div class="modal-body p-0">
              <div class="card card-plain">
                <div class="card-header pb-0 text-left">
                    <p class="mb-0">Enter your email and password to confirm this transaction</p>
                </div>
                <div class="card-body pb-3">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Email/Username" aria-label="Email/Username" data-bs-toggle="tooltip" data-bs-placement="left" title="Email or Username" aria-describedby="email-addon">
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Password" aria-label="Password" aria-describedby="password-addon" data-bs-toggle="tooltip" data-bs-placement="left" title="Password">
                    </div>
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="loader" style="display: none" id="loader_user_confirm"></div>
                    </div>                    
                    <div class="text-center">
                        <button type="button" class="btn bg-gradient-success btn-lg btn-rounded w-100 mt-4 mb-0" id="confirm_login">Confirm</button>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn bg-gradient-danger btn-lg btn-rounded w-100 mt-4 mb-0" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
                <div class="card-footer text-center pt-0 px-sm-4 px-1">
                </div>
              </div>
            </div>
          </div>
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
            radial-gradient(farthest-side,orange 94%,#0000) left/20px 20px no-repeat
            lightblue;
            animation: l2 1s infinite linear;
        }
        @keyframes l2 {
            50% {background-position:right }
        }
  </style>
@endsection
@include('harus_ada')
