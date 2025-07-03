@extends('layouts.user_type.auth')
@section('title', 'DN System - Kwitansi')
@section('css')
    <style>

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
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function() {
            jsonData = [];
            renderTable(jsonData);

            // Fungsi untuk isi data kosong dari baris sebelumnya
            function fillEmptyRows(data) {
                let lastFilled = {};

                return data.map(row => {
                    let newRow = {};
                    const keys = Object.keys(row);
                    const firstKey = keys[0];
                    const isFirstEmpty = row[firstKey] === "";

                    for (let key of keys) {
                        let value = row[key];
                        if (typeof value === 'string') {
                            value = normalizeText(value); // <-- bersihkan di sini
                        }

                        if (isFirstEmpty) {
                            newRow[key] = value !== "" ? value : (lastFilled[key] || "");
                            if (value !== "") {
                                lastFilled[key] = value;
                            }
                        } else {
                            newRow[key] = value;
                            if (value !== "") {
                                lastFilled[key] = value;
                            }
                        }
                    }

                    return newRow;
                });
            }

            function normalizeText(text) {
                if (typeof text !== 'string') return text;
                return text
                    .replace(/\u2013|\u2014|\u2012|\u2010/g, '-') // berbagai jenis dash → '-'
                    .replace(/[^\x20-\x7E]/g, ''); // buang karakter aneh
            }
            $('#uploadExcel').on('change', function(e) {
                const file = e.target.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {
                        type: 'array'
                    });

                    const firstSheet = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheet];

                    const rawData = XLSX.utils.sheet_to_json(worksheet, {
                        defval: ""
                    });

                    jsonData = fillEmptyRows(rawData);

                    renderTable(jsonData);
                };

                reader.readAsArrayBuffer(file);
            });

            function renderTable(data) {

                // Hapus DataTable sebelumnya jika ada
                if ($.fn.DataTable.isDataTable('#excelTable')) {
                    $('#excelTable').DataTable().destroy();
                }

                $('#excelTable thead').empty();
                $('#excelTable tbody').empty();

                // Jika data kosong atau tidak valid
                if (!Array.isArray(data) || data.length === 0 || typeof data[0] !== 'object') {
                    $('#excelTable thead').append('<tr><th>Data Kosong</th></tr>');
                    $('#excelTable tbody').append('<tr><td class="text-center">Tidak ada data yang ditampilkan</td></tr>');
                    $('#loader_save').fadeOut(1000);
                    return;
                }
                $('#loader_save').fadeIn(1000);

                // Lanjut render
                let columns = Object.keys(data[0]);
                let theadRow = '<tr>' + columns.map(col => `<th>${col}</th>`).join('') + '</tr>';
                $('#excelTable thead').append(theadRow);

                let tbodyHtml = data.map(row => {
                    return '<tr>' + columns.map(col => `<td>${row[col]}</td>`).join('') + '</tr>';
                }).join('');
                $('#excelTable tbody').append(tbodyHtml);

                $('#excelTable').DataTable({
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
                        infoFiltered: "(disaring dari _MAX_ total entri)",
                        @include('layouts.emptytable')
                    },
                    initComplete: function(settings, json) {
                        $('#loader_save').fadeOut(1000);
                    }
                });
            }

            $('#simpan').on('click', function() {
                if (jsonData.length === 0) {
                    alert('Silakan unggah file Excel terlebih dahulu.');
                    return;
                }
                $('#loader_save').fadeIn(1000);

                $.ajax({
                    url: '{{ route('import.excel.japfa') }}', // Ganti sesuai route kamu
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        data: jsonData
                    }),
                    success: function(res) {
                        $('#loader_save').fadeOut(1000);
                        new Noty({
                            text: '<i class="fas fa-check"></i> Berhasil import!',
                            type: 'success',
                            timeout: 3000,
                            layout: 'topRight'
                        }).show();
                        jsonData = [];
                        renderTable(jsonData);
                        $('#uploadExcel').val('');
                        $('#output').text('');
                    },
                    error: function(xhr) {
                        $('#loader_save').fadeOut(1000);
                        let errorMessage = 'Gagal menyimpan data.';

                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }

                        new Noty({
                            text: `<i class="fas fa-exclamation-triangle"></i> ${errorMessage}`,
                            type: 'error',
                            timeout: 3000,
                            layout: 'topRight'
                        }).show();
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    <div class="col-12 mb-3" id="">
        <div class="card ">
            <div class="card-body">
                <div class="row">
                    <div class="col-auto">
                        <div class="">
                            <input type="file" id="uploadExcel" class="form-control form-control-sm" />
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn bg-gradient-primary btn-sm btn-rounded" id="simpan">Import Data</button>
                        {{-- <pre id="output" class="mt-3" style="max-height: 300px; overflow-y: scroll;"></pre> --}}
                    </div>
                    <div class="loader_bulat" id="loader_save" style="display: none"></div>
                    <div class="col-12">
                        <div class="col-12">
                            <div class="row">
                                <style>
                                    .link-hover:hover {
                                        text-decoration: underline;
                                    }
                                </style>

                                <div class="col-auto d-flex align-items-center gap-2" style="font-size: 12px">
                                    <i class="fas fa-info-circle text-info"></i>
                                    <span>
                                        Pastikan data yang diimpor memiliki format yang <strong>identik</strong> dengan
                                        <a href="{{ asset('download/contoh-format.xlsx') }}" download class="text-primary link-hover" style="cursor: pointer;">
                                            contoh data berikut
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <table id="excelTable" class="table" style="width:100%">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@include('harus_ada')
