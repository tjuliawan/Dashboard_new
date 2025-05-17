@extends('layouts.user_type.auth')
@section('title', 'MC-Emp MGT')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            function initializeDataTable() {
                if ($.fn.DataTable.isDataTable('#tabel_user')) {
                    $('#tabel_user').DataTable().clear().destroy();
                }
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
                            data: 'Ms_Emp_Central_Code',
                            name: 'Ms_Emp_Central_Code'
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
                                return `
                                    <td class="text-center">
                                        <span>
                                            <i class="cursor-pointer fas fa-user-edit text-secondary mx-3 emp_edit"></i>
                                        </span>
                                        <span>
                                            <i class="cursor-pointer fas fa-trash text-secondary emp_dell"></i>
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
            $('#btn_add_emp').on('click', function(){
                var url = '/Employee-management/add-employee'
                window.location.href = url;
            });
            $(document).on('click', '.emp_dell', function(){
                var row = $(this).closest('tr');
                var emp = row.find('td:eq(1)').text();
                Swal.fire({
                    title: 'Data akan dihapus!',
                    text: "Apakah anda yakin untuk melanjutkan transaksi?",
                    imageUrl: 'https://media2.giphy.com/media/v1.Y2lkPTc5MGI3NjExaGQ1bWQ5bDNkcWc3ZG01NTI2a3RkbzZsMjdvbnY2ZG5wbHZyZTQxNSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/LpRUYmsvkm1rjbFilS/giphy.webp',
                    // imageWidth: 100,
                    imageHeight: 100,
                    // imageAlt: 'Loading Animation',
                    showCancelButton: true,
                    confirmButtonText: 'Yakin',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: '/Employee-management/delete-employe',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                emp: emp,
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Berhasil',
                                    text: 'Data telah berhasil sihapus',
                                    icon: 'success',
                                    timer: 2000, 
                                    showConfirmButton: false, 
                                    willClose: () => {
                                        initializeDataTable();
                                    }
                                });
                            },
                            error: function(xhr) {
                                let errorMessage =
                                    'Terjadi kesalahan saat mengirim data';
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    let errors = xhr.responseJSON.errors;
                                    errorMessage = '';
                                    for (let field in errors) {
                                        errorMessage += errors[field].join('\n') + '\n';
                                    }
                                }

                                Swal.fire({
                                    title: 'Error',
                                    text: errorMessage,
                                    icon: 'error'
                                });
                                console.log("Error mengirim data", xhr);
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.emp_edit', function(){
                var row = $(this).closest('tr');
                var emp = row.find('td:eq(1)').text();
                Swal.fire({
                    title: 'Anda akan mengedit data',
                    text: "Anda akan dialihkan ke halaman edit data",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = "/Employee-management/edit-employee" +
                            "?emp=" + encodeURIComponent(emp); 
                        window.location.href = url;
                    }
                });
            });
            initializeDataTable();
        });
    </script>
@endsection
@section('content')
    <div>
    {{-- <div class="alert alert-secondary mx-4" role="alert">
                <span class="text-white">
                    <strong>Add, Edit, Delete features are not functional!</strong> This is a
                    <strong>PRO</strong> feature! Click <strong>
                        <a href="https://www.creative-tim.com/live/soft-ui-dashboard-pro-laravel" target="_blank"
                            class="text-white">here</a></strong>
                    to see the PRO product!
                </span>
            </div> --}}

    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Employee</h5>
                        </div>
                        <button class="btn bg-gradient-primary btn-sm mb-3" id="btn_add_emp">+ Add Employee</button>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    {{-- <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                ID
                                            </th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Photo
                                            </th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Name
                                            </th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Email
                                            </th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                role
                                            </th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Creation Date
                                            </th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0">1</p>
                                            </td>
                                            <td>
                                                <div>
                                                    <img src="../assets/img/team-2.jpg" class="avatar avatar-sm me-3">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Admin</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">admin@softui.com</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Admin</p>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-secondary text-xs font-weight-bold">16/06/18</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="#" class="mx-3" data-bs-toggle="tooltip"
                                                    data-bs-original-title="Edit user">
                                                    <i class="fas fa-user-edit text-secondary"></i>
                                                </a>
                                                <span>
                                                    <i class="cursor-pointer fas fa-trash text-secondary"></i>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0">2</p>
                                            </td>
                                            <td>
                                                <div>
                                                    <img src="/assets/img/team-1.jpg" class="avatar avatar-sm me-3">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Creator</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">creator@softui.com</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Creator</p>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-secondary text-xs font-weight-bold">05/05/20</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="#" class="mx-3" data-bs-toggle="tooltip"
                                                    data-bs-original-title="Edit user">
                                                    <i class="fas fa-user-edit text-secondary"></i>
                                                </a>
                                                <span>
                                                    <i class="cursor-pointer fas fa-trash text-secondary"></i>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0">3</p>
                                            </td>
                                            <td>
                                                <div>
                                                    <img src="/assets/img/team-3.jpg" class="avatar avatar-sm me-3">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Member</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">member@softui.com</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Member</p>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-secondary text-xs font-weight-bold">23/06/20</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="#" class="mx-3" data-bs-toggle="tooltip"
                                                    data-bs-original-title="Edit user">
                                                    <i class="fas fa-user-edit text-secondary"></i>
                                                </a>
                                                <span>
                                                    <i class="cursor-pointer fas fa-trash text-secondary"></i>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0">4</p>
                                            </td>
                                            <td>
                                                <div>
                                                    <img src="/assets/img/team-4.jpg" class="avatar avatar-sm me-3">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Peterson</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">peterson@softui.com</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Member</p>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-secondary text-xs font-weight-bold">26/10/17</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="#" class="mx-3" data-bs-toggle="tooltip"
                                                    data-bs-original-title="Edit user">
                                                    <i class="fas fa-user-edit text-secondary"></i>
                                                </a>
                                                <span>
                                                    <i class="cursor-pointer fas fa-trash text-secondary"></i>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0">5</p>
                                            </td>
                                            <td>
                                                <div>
                                                    <img src="/assets/img/marie.jpg" class="avatar avatar-sm me-3">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Marie</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">marie@softui.com</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">Creator</p>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-secondary text-xs font-weight-bold">23/01/21</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="#" class="mx-3" data-bs-toggle="tooltip"
                                                    data-bs-original-title="Edit user">
                                                    <i class="fas fa-user-edit text-secondary"></i>
                                                </a>
                                                <span>
                                                    <i class="cursor-pointer fas fa-trash text-secondary"></i>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> --}}
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="tabel_user">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Com</th>
                                    <th>PKWT</th>
                                    <th>HP</th>
                                    <th>Email</th>
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
