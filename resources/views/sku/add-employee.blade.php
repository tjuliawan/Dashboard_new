@extends('layouts.user_type.auth')
@section('title', 'MC-Emp MGT')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            function initializeDataTable() {
                table = $('#tabel_user').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: '/Employee-management/table_user_data',
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
                        {
                            data: 'Ms_Emp_Name',
                            name: 'Ms_Emp_Name'
                        },
                        {
                            data: 'Cek_Aktif',
                            name: 'Cek_Aktif',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                if (data === "1" || data === 1) {
                                    return '<span class="badge badge-success" style="background-color: green; color: white; border-radius: 10px; padding: 5px;"> Aktif</span>';
                                } else if (data === "0" || data === 0) {
                                    return '<span class="badge badge-secondary" style="background-color: red; color: white; border-radius: 10px; padding: 5px;">Tidak</span>';
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            data: 'Ms_Company_Code',
                            name: 'Ms_Company_Code'
                        },
                        {
                            data: 'Cek_PKWT',
                            name: 'Cek_PKWT',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                if (data === "1" || data === 1) {
                                    return '<span class="badge badge-success" style="background-color: green; color: white; border-radius: 10px; padding: 5px;">Aktif</span>';
                                } else if (data === "0" || data === 0) {
                                    return '<span class="badge badge-secondary" style="background-color: red; color: white; border-radius: 10px; padding: 5px;">Tidak</span>';
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            data: 'HP',
                            name: 'HP'
                        },
                        {
                            data: 'Email',
                            name: 'Email'
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
                    responsive: true,
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
            $('#btn_add_emp').on('click', function() {
                var url = '/Employee-management/add-employee'
                window.location.href = url;
            });
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
                            <button class="btn bg-gradient-primary btn-sm mb-3" id="btn_add_emp">+ Add SKU</button>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <form class="m-3">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="exampleInputEmail1" class="form-label">Kode SKU</label>
                                        <input type="text" class="form-control form-control-sm" id="" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="exampleInputEmail1" class="form-label">Unit</label>
                                        <select class="form-control form-control-sm" name="" id="">
                                            <option value="">Pilih Unit</option>
                                            <option value="PCS">PCS</option>
                                            <option value="Kg">Kg</option>
                                            <option value="CTN">CTN</option> 
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="exampleInputPassword1" class="form-label">Nama SKU</label>
                                        <input type="text" class="form-control form-control-sm" id="" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="exampleInputEmail1" class="form-label">Unit</label>
                                        <select class="form-control form-control-sm" name="" id="">
                                            <option value="">Pilih Unit</option>
                                            <option value=""></option>
                                            <option value=""></option>
                                            <option value=""></option> 
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="">
                                        <label class="form-check-label" for="exampleCheck1">Check me out</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="">
                                        <label class="form-check-label" for="exampleCheck1">Check me out</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="">
                                        <label class="form-check-label" for="exampleCheck1">Check me out</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="">
                                        <label class="form-check-label" for="exampleCheck1">Check me out</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
