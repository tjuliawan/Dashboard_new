@extends('layouts.user_type.auth')
@section('title', 'DN System - List DN Paymen Japfa')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('#loader_save').hide();
            let startDate = '';
            let endDate = '';
            initializeDataTable();

            function initializeDataTable() {

                // console.log("All:", val_allChecked);
                // console.log("Selesai:", val_selesaiChecked);
                // console.log("Belum:", val_belumChecked);
                $('#loader_save').show();
                if ($.fn.DataTable.isDataTable('#list_kwitansi_table')) {
                    $('#list_kwitansi_table').DataTable().clear().destroy();
                }
                $('#div_list_kwitansi_table').show();
                table = $('#list_kwitansi_table').DataTable({
                    processing: false,
                    serverSide: false,
                    ajax: {
                        url: '/report/get/list_dn_payment',
                        type: 'GET',
                        dataSrc: '',
                        data: {
                            startDate: startDate,
                            endDate: endDate,
                            client: $('#select_client').val()
                        }
                    },
                    columns: [{
                            data: null,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        },
                        {
                            data: 'salesdnpay_code_h',
                            name: 'salesdnpay_code_h'
                        },
                        {
                            data: 'salesdnpay_Date',
                            name: 'salesdnpay_Date',
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    const tanggal = new Date(data);
                                    return tanggal.toLocaleDateString('id-ID', {
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric'
                                    });
                                }
                                return data;
                            }
                        },
                        {
                            data: 'salesdnpay_Client_code',
                            name: 'salesdnpay_Client_code'
                        },
                        {
                            data: 'salesdnpay_operator',
                            name: 'salesdnpay_operator'
                        },
                        {
                            data: 'salesdnpay_valuesalesdntagih',
                            name: 'salesdnpay_valuesalesdntagih',
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
                            data: null,
                            name: 'pay',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                return `
                                    <span class="badge bg-gradient-info btn_print" title="Sudah dibayar" style=" color: #ffffff; border-radius: 10px; padding: 5px 8px; font-size: 11px; cursor:pointer;">
                                         Print Inv
                                    </span>`;
                            }
                        },
                    ],
                    // responsive: true,
                    searching: true,
                    paging: false,
                    autoWidth: false,
                    // dom: '<"d-flex justify-content-between align-items-start"<"d-flex"Bl><"d-flex justify-content-end"f>><"table-responsive"t><"d-flex justify-content-between align-items-center"ip>',
                    scrollX: true,
                    scrollY: '400px',
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
                        @include('layouts.emptytable')
                    },
                    initComplete: function(settings, json) {
                        $('#loader_save').hide();
                    }
                });
            }

            function formatRupiah(angka) {
                if (!angka || isNaN(angka)) {
                    return 'Rp 0';
                }
                return 'Rp ' + parseFloat(angka).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function animateValue(id, start, end, duration) {
                const obj = $(id);
                let startTime = null;

                function step(timestamp) {
                    if (!startTime) startTime = timestamp;
                    const progress = timestamp - startTime;
                    const value = Math.floor(start + (end - start) * (progress / duration));

                    obj.text(value.toLocaleString("id-ID"));
                    if (progress < duration) {
                        requestAnimationFrame(step);
                    } else {
                        obj.text(end.toLocaleString("id-ID"));
                    }
                }

                requestAnimationFrame(step);
            }

            $("#btn_appy").click(function() {
                initializeDataTable();
                initializeChart();
                // setTimeout(() => {
                //     $(this).removeClass("rotate");
                // }, 1000);
            });
            let dateRange = $('#date_range').daterangepicker({
                autoUpdateInput: false,
                ranges: {
                    'Pilih Tanggal': [moment(), moment()],
                    'Hari Ini': [moment().subtract(0, 'days'), moment()],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                    'Tahun Lalu': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                    'Semua Data': [moment('2012-01-01'), moment().endOf('year')]
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
            $(document).on('click', '.btn_print', function() {
                var row = $(this).closest('tr');
                kode = row.find('td:eq(1)').text();
                // alert(kode);
                var url = '/cetak-pdf/payment?code=' + encodeURIComponent(kode);
                window.open(url, '_blank');
            });

            $('#date_range').val('');
            get_client();

            function get_client() {
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
                            branchSelect.append('<option value="' + item.clien_id + '">' + item.clien_id + ' | ' + item.clien_desc + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching branches:', error);
                    }
                });
            }
            $('#select_client').select2({
                theme: 'custom'
            });
        });
    </script>
    <style>
        .modern-input {
            border: none;
            border-bottom: 2px solid #00cec9;
            border-radius: 0;
            outline: none;
            padding: 0px 0;
            width: 100%;
            background-color: transparent;
            font-size: 12px;
            transition: border-color 0.2s;
        }

        .modern-input:focus {
            border-bottom: 2px solid #00cec9;
        }

        .modern-input:disabled {
            /* border-bottom: 1px solid #ccc; */
            border-bottom: none;
            background-color: transparent;
            color: #707070f6;
        }

        #list_kwitansi_table .note-col,
        #list_kwitansi_table td.note-col {
            max-width: 350px;
            white-space: normal;
            /* word-break: break-word; */
        }
    </style>
    <style>
        .rotate {
            transition: transform 1s ease-in-out, color 1s ease-in-out;
            transform: rotate(360deg);
            color: rgb(24, 255, 112);
            /* Warna saat animasi */
            cursor: pointer;
        }

        .default-color {
            color: black;
            /* Warna awal */
            transition: transform 0.3s ease, color 0.3s ease;
        }

        hr {
            border: none;
            height: 2px;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            /* Adjust this value for the width you want */
            margin: 20px auto;
            width: 100%;
            border-radius: 5px;
        }

        /* Add pointer cursor on hover */
        tr.clickable {
            cursor: pointer;
        }

        /* Style for the toast */
        #toast {
            visibility: hidden;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
        }

        #toast.show {
            visibility: visible;
            animation: fadein 1s, fadeout 2s 3s;
        }

        @keyframes fadein {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeout {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }

        .bg-custom-danger {
            background-color: #ff4757;
        }

        .bg-custom-info {
            background-color: #44bd32;
        }
    </style>

@endsection
@section('content')
    <div>
        <div class="row">
            <div class="col-12 col-md-3 mb-3" id="div_table_list_kwitansi">
                <div class="card" style="height: 175px">
                    <div class="card-body p-3">
                        <input type="text" id="date_range" class="form-control form-control-sm mb-3" style="width: 75%" placeholder="Pilih Tanggal" />
                        <select class="form-select form-select-sm " aria-label="Large select example " id="select_client" style="width: 75%">
                            <option value="">Client</option>
                        </select>
                        <div class="d-flex gap-3">
                            <button class="btn bg-gradient-primary btn-sm mt-3" id="btn_appy">Apply</button>
                            <div class="loader_bulat mt-3" id="loader_save"></div>
                        </div>
                    </div>
                    <div style="position: absolute; bottom: 5px; right: 10px; font-size: 12px; color: #888;">
                        <div class="loader_add"></div>
                        <style>
                            .loader_add {
                                width: 40px;
                                height: 26px;
                                --c: no-repeat linear-gradient(#b2bec3 0 0);
                                background:
                                    var(--c) 0 100%,
                                    var(--c) 50% 100%,
                                    var(--c) 100% 100%;
                                background-size: 8px calc(100% - 4px);
                                position: relative;
                            }

                            .loader_add:before {
                                content: "";
                                position: absolute;
                                width: 8px;
                                height: 8px;
                                border-radius: 50%;
                                background: #b2bec3;
                                left: 0;
                                top: 0;
                                animation:
                                    l3-1 1.5s linear infinite alternate,
                                    l3-2 0.75s cubic-bezier(0, 200, .8, 200) infinite;
                            }

                            @keyframes l3-1 {
                                100% {
                                    left: calc(100% - 8px)
                                }
                            }

                            @keyframes l3-2 {
                                100% {
                                    top: -0.1px
                                }
                            }
                        </style>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3" id="div_table_list_kwitansi">
                <div class="card ">
                    <div class="card-header pb-0">
                        <div class="d-flex gap-3">
                            <style>
                                .fa-rotate-right:hover {
                                    color: #00cec9;
                                }

                                .loader_bulat {
                                    width: 30px;
                                    height: 30px;
                                    aspect-ratio: 1;
                                    border-radius: 50%;
                                    background:
                                        radial-gradient(farthest-side, #00cec9 94%, #0000) top/5px 5px no-repeat,
                                        conic-gradient(#0000 30%, #00cec9);
                                    -webkit-mask: radial-gradient(farthest-side, #0000 calc(100% - 5px), #000 0);
                                    animation: l13 1s infinite linear;
                                }

                                @keyframes l13 {
                                    100% {
                                        transform: rotate(1turn)
                                    }
                                }
                            </style>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <table class="table align-items-center mb-0 table-striped" id="list_kwitansi_table">
                            <thead class="table-secondary">
                                <th></th>
                                <th>Kode Payment</th>
                                <th>Tgl</th>
                                <th>Client</th>
                                <th>Operator</th>
                                <th>Value</th>
                                <th></th>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
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
