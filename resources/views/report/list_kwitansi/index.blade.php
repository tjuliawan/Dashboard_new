@extends('layouts.user_type.auth')
@section('title', 'DN System - List Kwitansi')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('#loader_save').hide();
            let selesaiChecked;
            let belumChecked;
            let checked;
            let chart;
            let chartBar;
            let val_allChecked;
            let val_selesaiChecked;
            let val_belumChecked;
            let startDate = '';
            let endDate = '';
            var ctx = document.getElementById("chart-bars").getContext("2d");

            var gradientStroke1 = ctx.createLinearGradient(0, 230, 0, 50);

            gradientStroke1.addColorStop(1, 'rgba(203,12,159,0.2)');
            gradientStroke1.addColorStop(0.2, 'rgba(72,72,176,0.0)');
            gradientStroke1.addColorStop(0, 'rgba(203,12,159,0)');
            initializeDataTable();
            initializeChart();

            function initializeDataTable() {
                const val_allChecked = $('#checkAll').is(':checked');
                const val_selesaiChecked = $('#checkSelesai').is(':checked');
                const val_belumChecked = $('#checkBelum').is(':checked');

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
                        url: '/report/get-list-kwitansi',
                        type: 'GET',
                        dataSrc: '',
                        data: {
                            startDate: startDate,
                            endDate: endDate,
                            allChecked: val_allChecked,
                            selesaiChecked: val_selesaiChecked,
                            belumChecked: val_belumChecked,
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
                            data: 'no_kwitansi',
                            name: 'no_kwitansi'
                        },
                        {
                            data: 'tgl_kwitansi',
                            name: 'tgl_kwitansi',
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
                            data: 'salesdntagih_client_code',
                            name: 'salesdntagih_client_code'
                        },
                        {
                            data: 'note_kwitansi',
                            name: 'note_kwitansi',
                            className: 'note-col'
                        },
                        {
                            data: 'value_tagihan_dn',
                            name: 'value_tagihan_dn',
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
                            data: 'value_est_pph_4',
                            name: 'value_est_pph_4',
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
                            data: 'value_ppn',
                            name: 'value_ppn',
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
                            data: 'kode_faktur_pajak',
                            name: 'kode_faktur_pajak'
                        },
                        {
                            data: 'bukti_potong_pph_23',
                            name: 'bukti_potong_pph_23'
                        },
                        {
                            data: 'status',
                            name: 'status',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                if (data === "1" || data === 1) {
                                    return `
                                        <span class="badge bg-gradient-success" title="Sudah dibayar" style=" color: #ffffff; border-radius: 10px; padding: 5px 8px; font-size: 11px;">
                                            <i class="fa-solid fa-circle-check"></i> Dibayar
                                        </span>`;
                                } else if (data === "0" || data === 0) {
                                    return `
                                        <span class="badge bg-gradient-warning" title="Belum dibayar" style="color: #ffffff; border-radius: 10px; padding: 5px 8px; font-size: 11px;">
                                            <i class="fa-solid fa-clock"></i> Belum
                                        </span>`;
                                } else {
                                    return '';
                                }
                            }
                        }
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

            function initializeChart() {
                $.ajax({
                    url: '/report/get/list_kwitansi-chart',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        startDate: startDate,
                        endDate: endDate,
                        client: $('#select_client').val()
                    },
                    success: function(data) {
                        var data_sudah = parseInt(data.persen_sudah) || 0;
                        var value_sudah = parseInt(data.value_sudah) || 0;
                        animateValue("#value_sudah", 0, value_sudah, 800);
                        var value_belum = parseInt(data.value_belum) || 0;
                        animateValue("#value_belum", 0, value_belum, 800);
                        var value_pph_sudah = parseInt(data.value_pph_sudah) || 0;
                        animateValue("#value_pph_sudah", 0, value_pph_sudah, 800);
                        var value_pph_belum = parseInt(data.value_pph_belum) || 0;
                        animateValue("#value_pph_belum", 0, value_pph_belum, 800);

                        var sudah = parseFloat(data.sudah) || 0;
                        var belum = parseFloat(data.belum) || 0;
                        if (chart) {
                            chart.destroy();
                        }
                        var options = {
                            series: [data_sudah],
                            chart: {
                                height: 172,
                                type: 'radialBar',
                            },
                            plotOptions: {
                                radialBar: {
                                    hollow: {
                                        size: '70%',
                                    },
                                    dataLabels: {
                                        show: true,
                                        name: {
                                            offsetY: -10,
                                            show: true,
                                            color: '#fff',
                                            fontSize: '13px'
                                        },
                                        value: {
                                            offsetY: 16,
                                            color: '#ffff',
                                            fontSize: '16px',
                                            show: true,
                                            formatter: (val) => {
                                                return `${val}%`;
                                            }
                                        }
                                    }
                                },
                            },
                            labels: ['Completed'],
                            colors: ['#00cec9'],
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'light',
                                    type: 'horizontal',
                                    shadeIntensity: 0.25,
                                    gradientToColors: ['#81ecec'], // Warna gradasi pastel hijau muda
                                    inverseColors: true,
                                    opacityFrom: 1,
                                    opacityTo: 1,
                                    stops: [0, 100]
                                },
                            },
                            stroke: {
                                lineCap: 'round' // Ujung garis yang membulat
                            },
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        height: 200
                                    }
                                }
                            }]
                        };

                        chart = new ApexCharts(document.querySelector("#chart"), options);
                        chart.render();
                        if (chartBar) {
                            chartBar.destroy();
                        }

                        chartBar = new Chart(ctx, {
                            type: "bar",
                            data: {
                                labels: ["Dibayar", "Belum"],
                                datasets: [{
                                    label: "DN Tagih",
                                    data: [parseInt(data.sudah) || 0, parseInt(data.belum) || 0],
                                    backgroundColor: ["#01a3a4", "#ff7f50"],
                                    tension: 0.4,
                                    borderWidth: 0,
                                    borderRadius: 8,
                                    borderSkipped: false,
                                    maxBarThickness: 10
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let value = context.raw || 0;
                                                return value; // tanpa Rp
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        display: false,
                                        beginAtZero: true,
                                        ticks: {
                                            color: "#fff",
                                            callback: function(value) {
                                                return value; // tanpa Rp
                                            }
                                        },
                                        grid: {
                                            display: false
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            color: "#fff"
                                        },
                                        grid: {
                                            display: false
                                        }
                                    }
                                }
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }
            $("#btn_appy").click(function() {
                initializeDataTable();
                initializeChart();
                // setTimeout(() => {
                //     $(this).removeClass("rotate");
                // }, 1000);
            });
            $('#checkAll').change(function() {
                checked = $(this).is(':checked');
                $('#checkSelesai, #checkBelum').prop('checked', checked);
            });

            $('#checkSelesai, #checkBelum').change(function() {
                selesaiChecked = $('#checkSelesai').is(':checked');
                belumChecked = $('#checkBelum').is(':checked');

                $('#checkAll').prop('checked', selesaiChecked && belumChecked);

                if (!(selesaiChecked && belumChecked)) {
                    $('#checkAll').prop('checked', false);
                }

            });
            $('#checkAll, #checkSelesai, #checkBelum').on('change', function() {
                initializeDataTable();
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
            <div class="col-auto">
                <div class="card mb-3 bg-gradient-secondary" style="height: 175px; width: 250px;">
                    <div class="card-body p-0">
                        <div id="chart" style="height: 175px"></div>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="bg-gradient-dark border-radius-lg py-3 pe-1 mb-3">
                    <div class="chart">
                        <canvas id="chart-bars" class="chart-canvas" height="144px"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-auto mb-3">
                <div class="card mb-3" style="width: 250px">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Sudah Dibayar</p>
                                    <h6 class="font-weight-bolder mb-0">
                                        Rp <span id="value_sudah"></span>
                                    </h6>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                    <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Belum Dibayar</p>
                                    <h6 class="font-weight-bolder mb-0">
                                        Rp <span id="value_belum"></span>
                                    </h6>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                    <i class="ni ni-time-alarm text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto mb-3">
                <div class="card mb-3" style="width: 250px">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">PPH Sudah Dibayar</p>
                                    <h6 class="font-weight-bolder mb-0">
                                        Rp <span id="value_pph_sudah"></span>
                                    </h6>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                    <i class="ni ni-tag text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">PPH Belum Dibayar</p>
                                    <h6 class="font-weight-bolder mb-0">
                                        Rp <span id="value_pph_belum"></span>
                                    </h6>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                    <i class="ni ni-tag text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
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
                        <div class="container d-flex justify-content-center align-items-center mb-0">
                            <div class="d-flex align-items-center">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" id="checkAll" checked>
                                    <label class="form-check-label" for="checkAll">All</label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" id="checkSelesai" checked>
                                    <label class="form-check-label" for="checkSelesai">Selesai</label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" id="checkBelum" checked>
                                    <label class="form-check-label" for="checkBelum">Belum</label>
                                </div>
                            </div>
                        </div>
                        <table class="table align-items-center mb-0 table-striped" id="list_kwitansi_table">
                            <thead class="table-secondary">
                                <th></th>
                                <th>No Kwitansi</th>
                                <th>Tgl</th>
                                <th>Client</th>
                                <th>Note</th>
                                <th>Value(Rp)</th>
                                <th>pph PS 23(Rp)</th>
                                <th>ppn 12%(Rp)</th>
                                <th>No. Faktur Pajak</th>
                                <th>Bukti Potong pph PS 23</th>
                                <th>Status</th>
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
