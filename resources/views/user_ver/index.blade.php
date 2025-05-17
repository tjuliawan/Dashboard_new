@extends('layouts.user_type.auth')
@section('title', 'DN Tagih - User Activation')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            initializeDataTable();
            function initializeDataTable() {
                $('#loader_body').show();
                
                if ($.fn.DataTable.isDataTable('#table_users')) {
                    $('#table_users').DataTable().clear().destroy();
                }
                $('#div_table_users').show();
                table  = $('#table_users').DataTable({
                    serverSide: false,
                    ajax: {
                        url: '/user-activation/get_user',
                        type: 'GET',
                        dataSrc: '',
                    },
                    columns: [
                        {
                            data: null,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        },
                        {
                            data: null,
                            name: 'name',
                            render: function(data, type, row, meta) {
                                const name = row.name || '-';
                                const username = row.username || '-';
                                const imageJpg = `/storage/assets/img/${username}.jpg`;
                                const imagePng = `/storage/assets/img/${username}.png`;
                                const defaultAvatar = '/assets/img/user.png';

                                return `
                                    <div class="d-flex px-2 py-1">
                                        <div>
                                            <img src="${imageJpg}" 
                                                onerror="this.onerror=null;this.src='${imagePng}';this.onerror=function(){this.src='${defaultAvatar}'};" 
                                                class="avatar avatar-sm me-3" 
                                                alt="${name}">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">${name}</h6>
                                            <p class="text-xs text-secondary mb-0">${username}</p>
                                        </div>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'ms_divisi',
                            name: 'ms_divisi',
                            render: function(data, type, row, meta) {
                                const subDivisi = row.sub_divisi || '-'; // Mendapatkan sub divisi jika ada
                                return `
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">${data || '-'}</p>
                                        <p class="text-xs text-secondary mb-0">${subDivisi}</p>
                                    </td>
                                `;
                            }
                        },
                        {
                            data: 'ms_company',
                            name: 'ms_company',
                            render: function(data, type, row, meta) {
                                const branch = row.ms_branch || '-'; // Mendapatkan sub divisi jika ada
                                return `
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">${data || '-'}</p>
                                        <p class="text-xs text-secondary mb-0">${branch}</p>
                                    </td>
                                `;
                            }
                        },
                        { data : 'email' , name : 'email' },
                        {
                            data: 'activate',
                            name: 'activate',
                            render: function(data, type, row) {
                                const id = row.id;
                                const isChecked = data == 1 ? 'checked' : '';
                                const label = data == 1 ? 'Aktif' : 'Nonaktif';
                                return `
                                    <div class="form-check form-switch">
                                        <input class="form-check-input activate-toggle" type="checkbox" role="switch" id="switch_${id}" data-id="${id}" ${data == 1 ? 'checked' : ''}>
                                        <label class="form-check-label" for="switch_${id}">${label}</label>
                                    </div>
                                `;
                            }
                        }
                    ],
                    // responsive: true,
                    searching: true,
                    paging: false,
                    autoWidth: false,
                    scrollX: false,
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
                        infoFiltered: "(disaring dari _MAX_ total entri)", @include('layouts.emptytable') ,
                        processing: false,
                    },
                    initComplete: function(settings, json) {
                        $('#loader_body').hide();          
                    }
                });
            }
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
                <div class="card mb-3"> 
                    <div class="card-body mx-0 p-0">
                        <table class="table align-items-center mb-0 table-sm" id="table_users">
                            <thead>
                                <tr>
                                    <th></th>
                                    {{-- <th></th> --}}
                                    <th>Nama</th>
                                    <th>Divisi</th>
                                    <th>Company</th>
                                    <th>Email</th>
                                    <th>Activate</th>
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
    <div id="modalButton"></div>
@endsection
@include('harus_ada')