@extends('layouts.user_type.auth')
@section('title', 'MC-SKU Mgt')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            function initializeDataTable() {
                table = $('#tabel_sku').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: '/SKU-management/table_sku',
                        type: 'GET',
                        dataSrc: '',
                        data: function(d) {
                            d.Branch = $('#Branch').val();
                            d.startDate = $('#startDate').val();
                            d.endDate = $('#endDate').val();
                        }
                    },
                    columns: [{
                            data: null,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        },
                        { data : 'Ms_SKU_Central_Code', name : 'Ms_SKU_Central_Code' },
                        { data : 'Ms_SKU_Central_Desc', name : 'Ms_SKU_Central_Desc' },
                        { data : 'Ms_Unit', name : 'Ms_Unit' },
                        {
                            data: 'Cek_SN',
                            name: 'Cek_SN',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                if (data === "1" || data === 1) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: green; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-check"></i></span>';
                                } else if (data === "0" || data === 0) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: red; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-xmark"></i></span>';
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            data: 'Cek_Barcode',
                            name: 'Cek_Barcode',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                if (data === "1" || data === 1) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: green; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-check"></i></span>';
                                } else if (data === "0" || data === 0) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: red; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-xmark"></i></span>';
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            data: 'Cek_Unik',
                            name: 'Cek_Unik',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                if (data === "1" || data === 1) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: green; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-check"></i></span>';
                                } else if (data === "0" || data === 0) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: red; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-xmark"></i></span>';
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            data: 'Cek_Document',
                            name: 'Cek_Document',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                if (data === "1" || data === 1) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: green; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-check"></i></span>';
                                } else if (data === "0" || data === 0) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: red; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-xmark"></i></span>';
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            data: 'Cek_Tradeable',
                            name: 'Cek_Tradeable',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                if (data === "1" || data === 1) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: green; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-check"></i></span>';
                                } else if (data === "0" || data === 0) {
                                    return '<span class="badge badge-success" style="background-color: transparent; color: red; border-radius: 10px; padding: 0px;font-size: 16px;"><i class="fa-solid fa-circle-xmark"></i></span>';
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                // Menambahkan kolom dengan tombol edit dan delete
                                return `
                                    <td class="text-center">
                                        <a href="#" class="mx-3" data-bs-toggle="tooltip"
                                            data-bs-original-title="Edit user">
                                            <i class="fas fa-user-edit text-secondary"></i>
                                        </a>
                                        <span>
                                            <i class="cursor-pointer fas fa-trash text-secondary"></i>
                                        </span>
                                    </td>
                                `;
                            }
                        }
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
                        infoFiltered: "(disaring dari _MAX_ total entri)"
                    }
                });
            }
            initializeDataTable();
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
                                <h5 class="mb-0">All SKU</h5>
                            </div>
                            <button class="btn bg-gradient-primary btn-sm mb-3" id="btn_add_sku">+ Add SKU</button>
                            {{-- <a href="#" class="btn bg-gradient-primary btn-sm mb-3" type="button">+&nbsp;</a> --}}
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="tabel_sku">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Code</th>
                                        <th>SKU</th>
                                        <th>Unit</th>
                                        <th>SN</th>
                                        <th>Barcode</th>
                                        <th>Unik</th>
                                        <th>Document</th>
                                        <th>Tradeable</th>
                                        <th>Action</th>
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
@endsection
