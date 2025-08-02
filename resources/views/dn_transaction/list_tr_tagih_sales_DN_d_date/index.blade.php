@extends('layouts.user_type.auth')
@section('title', 'DN System - List Tgaih Sales DN')
@section('css')
@endsection
@section('script')
    <style>
        .selected-row {
            background-color: #eaa4f8 !important;
            color: #fff !important;
            /* font-weight: bold !important; */
        }
        #table_list_tr_tagih_sales_DN_d_date tbody {
            cursor: pointer;
        }

    </style>
    <style>
        .div_in_card {
            background-color: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.158); /* soft, modern look */
            transition: box-shadow 0.3s ease;
        }

        .div_in_card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.281); /* sedikit lebih tajam saat hover */
        }
    </style>
    <script>
        $(document).ready(function() {
            let pruduct_code;
            let allowRowClick = 1;
            $('#loader_body').hide();
            $('#apply_filter').click(function() {
                if (allowRowClick === 0){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please complete or cancel the transaction first before accessing the bottom section.'
                    });
                    return;
                }
                initializeDataTable();
            });
            var client_code = "";
            $('#reset_filter').click(function() {
                if (allowRowClick === 0){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please complete or cancel the transaction first before accessing the bottom section.'
                    });
                    return;
                }
                $('#input_po_code').val('');
                $('#div_table_list_tr_tagih_sales_DN_d_date').hide();
                $('#table_list_tr_tagih_sales_DN_d_date').DataTable().clear().destroy();
            });
            $('#table_list_tr_tagih_sales_DN_d_date tbody').on('click', 'tr', function() {
                if (allowRowClick === 0){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please complete or cancel the transaction first before selecting a row!',
                    });
                    return;
                }
                var $this = $(this);
                var code = $this.find('td:eq(1)').text();
                var tanggal = $this.find('td:eq(3)').text();
                pruduct_code = $this.find('td:eq(5)').text();
                var po_code = $this.find('td:eq(9)').text();
                if(po_code != ""){
                    $('#btn_add_po_code').text('Edit Po Code');
                    $('#input_po_code').val(po_code);
                }else{
                    $('#btn_add_po_code').text('+ Add Po Code');
                }
                client_code = $this.find('td:eq(4)').text();

                if ($this.hasClass('selected-row')) {
                    $this.removeClass('selected-row');
                    $('#header_code').val('');
                    $('#date_register_tagihan').val('');
                } else {
                    $('#table_list_tr_tagih_sales_DN_d_date tbody tr').removeClass('selected-row');
                    $this.addClass('selected-row');
                    $('#header_code').val(code);
                    $('#date_register_tagihan').val(tanggal);
                }
            });
            $('#btn_print').click(function () {
                var code = $('#header_code').val();
                // alert(code);
                // var client_code = $('#select_client').val();
                if (code === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select a row!',
                    });
                    return;
                }

                if(pruduct_code === 'Water Tanker'){
                    var url = '/cetak-pdf/dn-tagih-inv-wt?code=' + encodeURIComponent(code) +
                    '&client_code=' + encodeURIComponent(client_code);
                    window.open(url, '_blank');
                }else{
                    var url = '/cetak-pdf/dn-tagih-inv?code=' + encodeURIComponent(code) +
                        '&client_code=' + encodeURIComponent(client_code);
                    window.open(url, '_blank');
                    if(client_code === 'TUA'){
                        // var url2 = '/cetak-pdf/dn-tagih-kwitansi?code=' + encodeURIComponent(code) +
                        //     '&client_code=' + encodeURIComponent(client_code);
                        // window.open(url2, '_blank');
                    }
                }
            });
            $('#btn_add_po_code').click(function () {
                var code = $('#header_code').val();
                if (code === "" && client_code === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select a row!',
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
                        allowRowClick = 0;
                        $('#input_po_code').show();
                        $('#btn_print').hide();
                        $('#btn_add_po_code').hide();
                        $('#btn_confirm_po_code').show();
                        $('#btn_cancel_po_code').show();
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cancelled',
                            text: 'The transaction has been cancelled.',
                        });
                    }
                });
            });
            $('#btn_cancel_po_code').click(function () {
                Swal.fire({
                    icon: 'question',
                    title: 'Confirm Transaction',
                    text: 'Are you sure you want to confirm this transaction?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Confirm',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        allowRowClick = 1;
                        $('#input_po_code').hide();
                        $('#input_po_code').val('');
                        $('#btn_print').show();
                        $('#btn_add_po_code').show();
                        $('#btn_confirm_po_code').hide();
                        $('#btn_cancel_po_code').hide();
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cancelled',
                            text: 'The transaction has been cancelled.',
                        });
                    }
                });
            });
            $('#btn_confirm_po_code').on('click', function() {
                var code_header = $('#header_code').val();
                // var code_po = $('#input_po_code').val();
                alert(code_po);
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
                            url: '/dn-tagih/update/po-code',
                            type: 'POST',
                            data: {
                                code_header: code_header,
                                code_po : code_po,
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
            function initializeDataTable() {
                $('#loader_body').show();
                var client = $('#select_client').val();
                var vehicle = $('#select_vehicle').val();
                var business = $('#select_business').val();
                var staert_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var input_main_code = $('#input_main_code').val();

                $('#header_code').val('');
                $('#date_register_tagihan').val('');
                // if (client === "") {
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Oops...',
                //         text: 'Please select a client!',
                //     });
                //     $('#loader_body').hide();
                //     return;
                // }
                // if (vehicle === "") {
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Oops...',
                //         text: 'Please select a vehicle!',
                //     });
                //     $('#loader_body').hide();
                //     return;
                // }
                // if (business === "") {
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Oops...',
                //         text: 'Please select a business!',
                //     });
                //     $('#loader_body').hide();
                //     return;
                // }
                // if (staert_date === "") {
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Oops...',
                //         text: 'Please select a start date!',
                //     });
                //     $('#loader_body').hide();
                //     return;
                // }
                // if (end_date === "") {
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Oops...',
                //         text: 'Please select an end date!',
                //     });
                //     $('#loader_body').hide();
                //     return;
                // }
                if(input_main_code != ""){
                    client = "";
                    vehicle = "";
                    business = "";
                    staert_date = "";
                    end_date = "";
                }
                if ($.fn.DataTable.isDataTable('#table_list_tr_tagih_sales_DN_d_date')) {
                    $('#table_list_tr_tagih_sales_DN_d_date').DataTable().clear().destroy();
                }
                $('#div_table_list_tr_tagih_sales_DN_d_date').show();
                table = $('#table_list_tr_tagih_sales_DN_d_date').DataTable({
                    processing: false,
                    serverSide: false,
                    ajax: {
                        url: '/dn_tagih/get_table_list_tr_tagih_sales_DN_d_date',
                        type: 'GET',
                        dataSrc: '',
                        data: {
                            client: client,
                            vehicle: vehicle,
                            business: business,
                            start_date: staert_date,
                            end_date: end_date,
                            input_main_code: input_main_code,
                            product: $('#select_product').val(),
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading data:', error);
                            $('#loader_body').hide();

                            let errorMessage = 'Gagal mengambil data. Silakan coba lagi.';

                            new Noty({
                                text: `<i class="fas fa-exclamation-triangle"></i> ${errorMessage}`,
                                type: 'error',
                                timeout: 3000,
                                layout: 'topRight'
                            }).show();
                        }
                    },
                    columns: [{
                            data: null,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        },
                        // { data : 'rec_comcode', name : 'rec_comcode'},
                        // { data : 'rec_areacode', name : 'rec_areacode'},
                        { data : 'salesdntagih_code_h', name : 'salesdntagih_code_h'},
                        { data : 'salesdntagih_Sales_dn_code', name : 'salesdntagih_Sales_dn_code'},
                        { data : 'salesdntagih_Sales_dn_date', name : 'salesdntagih_Sales_dn_date'},
                        { data : 'salesdntagih_client_code', name : 'salesdntagih_client_code'},
                        { data : 'sales_dn_productcode', name : 'sales_dn_productcode'},
                        { data : 'salesdntagih_Sales_dn_codeheader', name : 'salesdntagih_Sales_dn_codeheader'},
                        { data : 'salesdntagih_cocode_header', name : 'salesdntagih_cocode_header'},
                        { data : 'salesdntagih_cocode', name : 'salesdntagih_cocode'},
                        { data : 'salesdntagih_no_po', name : 'salesdntagih_no_po' },
                        { data : 'salesdntagih_drivercode', name : 'salesdntagih_drivercode'},
                        { data : 'salesdntagih_routevhcode', name : 'salesdntagih_routevhcode'},
                        { data : 'salesdntagih_vhcode', name : 'salesdntagih_vhcode'},
                        {
                            data: 'salesdntagih_qty',
                            name: 'salesdntagih_qty',
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
                            data: 'salesdntagih_salesbotol',
                            name: 'salesdntagih_salesbotol',
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
                        {
                            data: 'salesdntagih_Tagih_value',
                            name: 'salesdntagih_Tagih_value',
                            render: function(data, type, row) {
                                if (data === null || data === undefined || data === '') {
                                    return '';
                                }

                                if (type === 'display') {
                                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                                        useGrouping: true,
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 3
                                    });
                                }

                                return data;
                            }
                        },
                        { data : 'salesdntagih_note', name : 'salesdntagih_note'},
                    ],
                    // responsive: true,
                    searching: true,
                    paging: true,
                    autoWidth: false,
                    dom: '<"d-flex justify-content-between align-items-start"<"d-flex"Bl><"d-flex justify-content-end"f>><"table-responsive"t><"d-flex justify-content-between align-items-center"ip>',
                    scrollX: true,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "Semua"]
                    ],
                    buttons: [
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            exportOptions: {
                                footer: true,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (column === 14 | column === 15| column === 13) {
                                            return data.replace(/[^\d,-]/g, '').replace(',', '.');
                                        }
                                        return data;
                                    }
                                }
                            },
                            customize: function (xlsx) {
                                let sheet = xlsx.xl.worksheets['sheet1.xml'];
                            }
                        }
                    ],
                    language: {
                        lengthMenu: "_MENU_",
                        search: "Pencarian:",
                        zeroRecords: "Tidak ada data yang ditemukan",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                        infoEmpty: "Tidak ada data",
                        infoFiltered: "(disaring dari _MAX_ total entri)", @include('layouts.emptytable')
                    },
                    initComplete: function(settings, json) {
                        $('#loader_body').hide();
                    }
                });
            }
            get_client();
            function get_client(){
                $.ajax({
                    url: '/get_client',
                    type: 'GET',
                    headers: {
                        'X-API-KEY': 'hgsjkt205'
                    },
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
            get_vehicle();
            function get_vehicle(){
                $.ajax({
                url: '/get_vehicle',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var branchSelect = $('#select_vehicle');
                        branchSelect.empty();
                        branchSelect.append('<option value="">Vehicle</option>');

                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        data.forEach(function(item) {
                            branchSelect.append('<option value="' + item.Vh_Code + '">' + item.Vh_Code + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching branches:', error);
                    }
                });
            }
        // select2
            $('#select_client').select2({
                theme: 'custom'
            });
            $('#select_vehicle').select2({
                theme: 'custom'
            });
            $('#select_business').select2({
                theme: 'custom'
            });

            const element = document.getElementById('select_product');
            const choices = new Choices(element, { removeItemButton: true });

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
            <div class="col-12">
                <div class="card mb-4 ">

                    <div class="card-body">
                        <div class="row">
                            <div class="col-auto">
                                <h5 class="mb-0" style="border-bottom: 2px solid #344767c9;">Choose Your Option</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <small>Client</small>
                                        <select class="form-select form-select-sm" aria-label="Large select example" id="select_client">
                                            <option selected>Client</option>
                                        </select>
                                        <small>Vehicle</small>
                                        <select class="form-select form-select-sm" aria-label="Large select example" id="select_vehicle">
                                            <option selected>Vehicle</option>
                                        </select>
                                        <small>Code</small>
                                        <input class="form-control form-control-sm" type="text" id="input_main_code" placeholder="Code ex: INV-TSD-202505-0001">

                                        <small>Product</small>
                                        <select class="form-control form-control-sm" aria-label="Large select example" id="select_product" multiple>
                                            <option value="">Product</option>
                                            <option value="ABC">ABC</option>
                                            <option value="Agriaku">Agriaku</option>
                                            <option value="Aqua Isi">Aqua Isi</option>
                                            <option value="Bukalapak">Bukalapak</option>
                                            <option value="Empty Bottle Only">Empty Bottle Only</option>
                                            <option value="Frisian Flag">Frisian Flag</option>
                                            <option value="Gaga">Gaga</option>
                                            <option value="HVS">HVS</option>
                                            <option value="Jugrack Only">Jugrack Only</option>
                                            <option value="Kapal Api">Kapal Api</option>
                                            <option value="Karton Only">Karton Only</option>
                                            <option value="Lassah">Lassah</option>
                                            <option value="Mutasi Returan">Mutasi Returan</option>
                                            <option value="Nutricia">Nutricia</option>
                                            <option value="Pallet">Pallet</option>
                                            <option value="Resin">Resin</option>
                                            <option value="SPS">SPS</option>
                                            <option value="VIT">VIT</option>
                                            <option value="Water Tanker">Water Tanker</option>
                                            <option value="Wings">Wings</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <small>Start Date</small>
                                        <input class="form-control form-control-sm" type="date" id="start_date">
                                        <small>End Date</small>
                                        <input class="form-control form-control-sm" type="date" id="end_date">
                                        <small style="color: #696cff00">.</small>
                                        <div class="col-auto">
                                            <button class="btn bg-gradient-primary btn-sm " id="apply_filter">Apply</button>
                                            <button class="btn bg-gradient-warning btn-sm " id="reset_filter">Reset</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row">
                                    <div class="col-12 col-md-6" >
                                        <div class="div_in_card">
                                            <input class="form-control form-control-sm " type="text" id="header_code" placeholder="Code" readonly>
                                            <small style="color: #696cff00">.</small>
                                            <input class="form-control form-control-sm" type="date" id="date_register_tagihan" readonly>
                                            <small style="color: #696cff00">.</small>
                                            <input class="form-control form-control-sm" type="text" id="input_po_code" placeholder="Insert PO Code here" style="display: none">
                                            <small style="color: #696cff00">.</small>
                                            <div class="col-auto">
                                                <button class="btn bg-gradient-success btn-sm" id="btn_print">Print</button>
                                                <button class="btn bg-gradient-info btn-sm" id="btn_add_po_code">+ Add PO Code</button>
                                                <button class="btn bg-gradient-success btn-sm" id="btn_confirm_po_code" style="display: none">Confirm</button>
                                                <button class="btn bg-gradient-danger btn-sm" id="btn_cancel_po_code" style="display: none">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12" id="div_table_list_tr_tagih_sales_DN_d_date" style="display: none;">
                <div class="card mb-4 ">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>

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
            </div>
        </div>
    </div>
    <div id="modalButton"></div>
@endsection
@include('harus_ada')
