@extends('layouts.user_type.auth')
@section('title', 'DN Tagih - Add New Tagih Sales DN')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            let tampunganData = [];
            let url_code;
            $('#loader_body').hide();
            $('#apply_filter').click(function() {
                tampunganData = [];
                initializeDataTable();
            });
            $('#reset_filter').click(function() {
                tampunganData = [];
                $('#select_client').trigger('change');
                $('#select_cabang').trigger('change');
                $('#start_date').val('');
                $('#end_date').val('');
                $('#div_table_add_tagih_sales_dn').hide();
                $('#table_add_tagih_sales_dn').DataTable().clear().destroy();
            });
            $('#btn_print_report').click(function() {
                var porduct = $('#select_product').val();
                var client_code = $('#select_client').val();
                                    
                if(porduct[0] === 'Water Tanker'){
                    var url = '/cetak-pdf/dn-tagih-inv-wt?code=' + encodeURIComponent(url_code) +
                    '&client_code=' + encodeURIComponent(client_code);
                    window.open(url, '_blank');
                }else{
                    var url = '/cetak-pdf/dn-tagih-inv?code=' + encodeURIComponent(url_code) +
                        '&client_code=' + encodeURIComponent(client_code);
                    window.open(url, '_blank');
                    if(client_code === 'TUA'){
                        // var url2 = '/cetak-pdf/dn-tagih-kwitansi?code=' + encodeURIComponent(url_code) +
                        //     '&client_code=' + encodeURIComponent(client_code);
                        // window.open(url2, '_blank');
                    }
                }
            });
            $('#btn_confirm').on('click', function() {
                if (tampunganData.length === 0) {
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
                        $.ajax({
                            url: '/dn-tagih/store',  
                            type: 'POST',
                            data: {
                                start_date: $('#start_date').val(),
                                end_date: $('#end_date').val(),
                                total_sales: parseFloat(
                                    $('#sales_text').text()
                                        .replace(/Rp\s?/i, '')
                                        .replace(/\./g, '')
                                        .replace(/,/g, '.')     
                                ),
                                cabang_code : $('#select_cabang').val(),
                                tampunganData: tampunganData,
                                _token: $('meta[name="csrf-token"]').attr('content') 
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Transaction Confirmed!',
                                    text: 'Your transaction has been successfully processed.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    $('#apply_filter').hide();
                                    $('#reset_filter').hide();
                                    $('#btn_confirm').hide();
                                    $('#btn_print_report').show();
                                    $('#new_transaction').show();
                                    $('.row-checkbox').prop('disabled', true);
                                    $('.tampungan-checkbox').prop('disabled', true);
                                    $('#select_client').prop('disabled', true);
                                    $('#select_cabang').prop('disabled', true);
                                    $('#select_business').prop('disabled', true);
                                    $('#select_vehicle').prop('disabled', true);
                                    $('#start_date').prop('disabled', true);
                                    $('#end_date').prop('disabled', true);
                                    $('#select_product').prop('disabled', true);
                                    tampunganData = [];
                                    url_code = response.details[0].salesdntagih_code_h;
                                    var client_code = $('#select_client').val();
                                    var porduct = $('#select_product').val();
                                    
                                    if(porduct[0] === 'Water Tanker'){
                                        var url = '/cetak-pdf/dn-tagih-inv-wt?code=' + encodeURIComponent(url_code) +
                                        '&client_code=' + encodeURIComponent(client_code);
                                        window.open(url, '_blank');
                                    }else{
                                        var url = '/cetak-pdf/dn-tagih-inv?code=' + encodeURIComponent(url_code) +
                                            '&client_code=' + encodeURIComponent(client_code);
                                        window.open(url, '_blank');
                                        if(client_code === 'TUA'){
                                            // var url2 = '/cetak-pdf/dn-tagih-kwitansi?code=' + encodeURIComponent(url_code) +
                                            //     '&client_code=' + encodeURIComponent(client_code);
                                            // window.open(url2, '_blank');
                                        }
                                    }
                                });
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
            function initializeDataTable() {
                $('#loader_body').show();
                var client = $('#select_client').val();
                var cabang = $('#select_cabang').val();
                var business = $('#select_business').val();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                if (client === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select a client!',
                    });
                    $('#loader_body').hide();
                    return;
                }
                if (client === "TUA") {
                    if (cabang === "") {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Please select a Branch!',
                        });
                        $('#loader_body').hide();
                        return;
                    }
                }
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
                if (start_date === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select a start date!',
                    });
                    $('#loader_body').hide();
                    return;
                }
                if (end_date === "") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select an end date!',
                    });
                    $('#loader_body').hide();
                    return;
                }
                if ($.fn.DataTable.isDataTable('#table_add_tagih_sales_dn')) {
                    $('#table_add_tagih_sales_dn').DataTable().clear().destroy();
                }
                if ($.fn.DataTable.isDataTable('#table_tampungan')) {
                    $('#table_tampungan').DataTable().clear().destroy();
                }
                $('#div_table_add_tagih_sales_dn').show();
                tableMain  = $('#table_add_tagih_sales_dn').DataTable({
                    serverSide: false,
                    ajax: {
                        url: '/dn_tagih/get_table_add_tagih_sales_dn',
                        type: 'GET',
                        dataSrc: '',
                        data: {
                            client: client,
                            cabang: cabang,
                            business: business,
                            register_date: $('#date_register_tagihan').val(),
                            vehicle: $('#select_vehicle').val(),
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val(),
                            product: $('#select_product').val(),
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
                    // dom: '<"d-flex justify-content-between align-items-start"<"d-flex"Bl><"d-flex justify-content-end"f>><"table-responsive"t><"d-flex justify-content-between align-items-center"ip>',
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

                tableTampungan = $('#table_tampungan').DataTable({
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
                    scrollX: true,
                    processing: false,
                });
            }
            function updateTotalSales() {
                const total = tampunganData.reduce((sum, item) => {
                    return sum + parseFloat(item.totalsales || 0);
                }, 0);
                $('#sales_text').text(
                    total.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })
                );
            }
            $('#table_add_tagih_sales_dn tbody').on('change', '.row-checkbox', function () {
                const tr = $(this).closest('tr');
                const row = tableMain.row(tr);
                const rowData = row.data();

                if (this.checked) {
                    const modifiedRow = $.extend({}, rowData); // Salin data
                    modifiedRow.note = ''; // kosongkan dulu nilai note-nya

                    // Simpan referensi data ke tampunganData
                    tampunganData.push(modifiedRow);
                    tableTampungan.row.add(modifiedRow).draw(); // tambahkan ke tabel tampungan
                    row.remove().draw();
                } else {
                    // Hapus dari tampunganData
                    const index = tampunganData.findIndex(item => item.Sales_DN_Code_d === rowData.Sales_DN_Code_d);
                    if (index > -1) {
                        tampunganData.splice(index, 1);
                    }

                    // Kembalikan ke tableMain
                    tableMain.row.add(rowData).draw();
                    row.remove().draw();
                }
                updateTotalSales();
            });

            $('#table_tampungan tbody').on('change', '.tampungan-checkbox', function () {
                const tr = $(this).closest('tr');
                const row = tableTampungan.row(tr);
                const rowData = row.data();

                if (!this.checked) {
                    const index = tampunganData.findIndex(item => item.Sales_DN_Code_d === rowData.Sales_DN_Code_d);
                    if (index > -1) {
                        tampunganData.splice(index, 1);
                    }
                    tableMain.row.add(rowData).draw();
                    row.remove().draw();
                } else {
                    tampunganData.push(rowData);
                    tableTampungan.row.add(rowData).draw();
                    row.remove().draw();
                }
                updateTotalSales();
            });
            
            $('#table_tampungan tbody').on('input', '.note-input', function () {
                const code = $(this).data('code');
                const value = $(this).val();
                const item = tampunganData.find(item => String(item.Sales_DN_Code_d) === String(code));
                if (item) {
                    item.note = value;
                }
            });

            function updateNomorUrut() {
                tableMain.rows().every(function (rowIdx) {
                    const row = this.node();  
                    $(row).find('td').eq(1).text(rowIdx + 1);  
                });

                tableTampungan.rows().every(function (rowIdx) {
                    const row = this.node(); 
                    $(row).find('td').eq(1).text(rowIdx + 1);  
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
            // get_business();
            function get_business(){
                $.ajax({
                url: '/get_business',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var branchSelect = $('#select_business');
                        branchSelect.empty();
                        branchSelect.append('<option value="">Business</option>');

                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        data.forEach(function(item) {
                            branchSelect.append('<option value="' + item.Gudang_code + '">' + item.Gudang_code + '</option>');
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
            $('#select_cabang').select2({
                theme: 'custom'
            });
            $('#select_business').select2({
                theme: 'custom'
            });
            $('#select_vehicle').select2({
                theme: 'custom'
            });
            const element = document.getElementById('select_product');
            const choices = new Choices(element, { removeItemButton: true });

            let today = new Date().toISOString().split('T')[0];
            $('#start_date').val(today);
            $('#end_date').val(today);
            // $('#date_register_tagihan').val(today);

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
                <div class="card  mb-3"> 
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
                                        <small>Cabang</small>
                                        <select class="form-select form-select-sm" aria-label="Large select example" id="select_cabang">
                                            <option value="">Cabang</option>
                                            <option value="0001">HGS-Sentul</option>
                                            <option value="0002">HGS-Ciherang</option>
                                            <option value="0003">HGS-Subang</option>
                                        </select>
                                        {{-- <small>Date Register Tagihan</small>
                                        <input class="form-control form-control-sm" type="date" id="date_register_tagihan"> --}}
                                        <small>Vehicle</small>
                                        <select class="form-select form-select-sm" aria-label="Large select example" id="select_vehicle">
                                            <option selected>Vehicle</option>
                                        </select>
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
                                            <button class="btn bg-gradient-primary btn-sm" id="apply_filter">Apply</button>
                                            <button class="btn bg-gradient-warning btn-sm" id="reset_filter">Reset</button>
                                            <button class="btn btn-sm bg-gradient-success" id="btn_print_report" style="display: none">Print Report</button>
                                            <button class="btn btn-sm bg-gradient-warning" id="new_transaction" style="display: none">New Transaction</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="total-sales-card">
                                            <div class="label">Total Tagihan</div>
                                            <div id="sales_text" class="value">Rp 0,00</div>
                                            <button class="btn btn-sm bg-gradient-success mt-4" id="btn_confirm">Confirm Transaction</button>
                                        </div>
                                        <style>
                                            .total-sales-card {
                                                background-color: #f8f9fa;
                                                border: 1px solid #dee2e6;
                                                border-radius: 12px;
                                                padding: 13px 18px;
                                                max-width: 250px;
                                                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
                                                margin: 10px 0;
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
                                        {{-- <input class="form-control form-control-sam" type="text" id="sales_text">
                                         --}}
                                        {{-- <small>DN Tagih</small>
                                        <input class="form-control mb-3" type="text"> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12" id="div_table_add_tagih_sales_dn" style="display: none;">
                <div class="card mb-3 ">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                
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
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="table_tampungan">
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
        </div>
    </div>
    <div id="modalButton"></div>
@endsection
@include('harus_ada')