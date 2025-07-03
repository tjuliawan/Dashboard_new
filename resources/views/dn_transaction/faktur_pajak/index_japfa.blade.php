@extends('layouts.user_type.auth')
@section('title', 'DN System - Kwitansi')
@section('css')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('#loader_save').hide();
            let no_kwitansi;
            let value;
            let pajak_psl_23;
            let pajak_value_ppn;
            let pajak_bukti_Potong;
            let dataToSave = [];
            let borongan;
            let potongan;
            initializeDataTable();
            function initializeDataTable() {
                $('#loader_search').show();
                if ($.fn.DataTable.isDataTable('#list_kwitansi_table')) {
                    $('#list_kwitansi_table').DataTable().clear().destroy();
                }
                $('#div_list_kwitansi_table').show();
                table = $('#list_kwitansi_table').DataTable({
                    processing: false,
                    serverSide: false,
                    ajax: {
                        url: '/dn_tagih/get_list_pajak_japfa',
                        type: 'GET',
                        dataSrc: '',
                        data: {
                        }
                    },
                    columns: [{
                            data: null,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        },
                        { data : 'no_kwitansi', name : 'no_kwitansi'},
                        { data : 'tgl_kwitansi', name : 'tgl_kwitansi'},
                        { data : 'salesdntagih_client_code', name : 'salesdntagih_client_code'},
                        { data : 'note_kwitansi', name : 'note_kwitansi', className:'note-col'},
                        {
                            data: 'value_tagihan_dn',
                            name: 'value_tagihan_dn',
                            className: 'text-start value_taihan',
                            render: function(data, type, row) {
                                if (data === null || data === undefined || data === '') {
                                    return '';
                                }

                                if (type === 'display') {
                                    return parseFloat(data).toLocaleString('id-ID', {
                                        useGrouping: true,
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }

                                return data;
                            }
                        },
                        {
                            data: 'value_potongan',
                            name: 'value_potongan',
                            render: function(data, type, row, meta) {
                                if (type === 'display') {
                                    const formattedValue = data
                                        ? parseFloat(data).toLocaleString('id-ID', {
                                            useGrouping: true,
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        })
                                        : '';

                                    return `
                                        <input
                                            type="text"
                                            class="modern-input input_money faktur-input-save faktur-input-potongan"
                                            value="${formattedValue}"
                                            data-row-index="${meta.row}"
                                            data-id="${row.id}"
                                        disabled />
                                    `;
                                }

                                return data;
                            }
                        },
                        {
                            data: 'value_est_pph_4',
                            name: 'value_est_pph_4',
                            render: function(data, type, row, meta) {
                                if (type === 'display') {
                                    const formattedValue = data
                                        ? parseFloat(data).toLocaleString('id-ID', {
                                            useGrouping: true,
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        })
                                        : '';

                                    return `
                                        <input
                                            type="text"
                                            class="modern-input input_money faktur-input-save faktur-input-psl23"
                                            value="${formattedValue}"
                                            data-row-index="${meta.row}"
                                            data-id="${row.id}"
                                        disabled />
                                    `;
                                }

                                return data;
                            }
                        },
                        {
                            data: 'value_ppn',
                            name: 'value_ppn',
                            render: function(data, type, row, meta) {
                                if (type === 'display') {
                                    const formattedValue = data
                                        ? parseFloat(data).toLocaleString('id-ID', {
                                            useGrouping: true,
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        })
                                        : '';

                                    return `
                                        <input
                                            type="text"
                                            class="modern-input input_money faktur-input-save faktur-input-value_ppn"
                                            value="${formattedValue}"
                                            data-row-index="${meta.row}"
                                            data-id="${row.id}"
                                        disabled />
                                    `;
                                }

                                return data;
                            }
                        },
                        {
                            data: 'kode_faktur_pajak',
                            name: 'kode_faktur_pajak',
                            render: function(data, type, row, meta) {
                                if (type === 'display') {
                                    return `<input type="text"
                                                    class="modern-input faktur-input-save faktur-input"
                                                    value="${data ? data : ''}"
                                                    data-row-index="${meta.row}"
                                                    data-id="${row.id}"
                                                    disabled />`;
                                }
                                return data;
                            }
                        },
                        {
                            data: 'bukti_potong_pph_23',
                            name: 'bukti_potong_pph_23',
                            render: function(data, type, row, meta) {
                                if (type === 'display') {
                                    return `<input type="text"
                                                    class="modern-input faktur-input-save faktur-input-bukti_Potong"
                                                    value="${data ? data : ''}"
                                                    data-row-index="${meta.row}"
                                                    data-id="${row.id}"
                                                    disabled />`;
                                }
                                return data;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
                                return `
                                    <button class="btn btn-warning btn-sm btn-edit rounded-sm btn-icon mb-0 mx-2" data-row-index="${meta.row}" title="Edit" style=" height: 30px; padding: 5px;">
                                        <i class="fas fa-edit" style="font-size: 16px;"></i> Edit
                                    </button>
                                    <button class="btn btn-success btn-sm btn-save rounded-circle btn-icon mb-0 d-none" data-row-index="${meta.row}" title="Save" style="width: 30px; height: 30px; padding: 0;">
                                        <i class="fas fa-save" style="font-size: 16px;"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-cancel rounded-circle btn-icon mb-0 d-none" data-row-index="${meta.row}" title="Cancel" style="width: 30px; height: 30px; padding: 0;">
                                        <i class="fas fa-times" style="font-size: 16px;"></i>
                                    </button>
                                `;
                            }

                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                           render: function(data, type, row, meta) {
                                return `
                                    <button
                                        class="btn bg-gradient-primary btn-sm btn-confirm-edit rounded-circle btn-icon mb-0"
                                        title="Save Transaction"
                                        style="width: 30px; height: 30px; padding: 0;"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top">
                                        <i class="fas fa-floppy-disk" style="font-size: 16px;"></i>
                                    </button>
                                `;
                            }
                        }

                    ],
                    // responsive: true,
                    searching: true,
                    paging: false,
                    autoWidth: false,
                    dom: '<"d-flex justify-content-between align-items-start"<"d-flex"Bl><"d-flex justify-content-end"f>><"table-responsive"t><"d-flex justify-content-between align-items-center"ip>',
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
                        infoFiltered: "(disaring dari _MAX_ total entri)", @include('layouts.emptytable')
                    },
                    initComplete: function(settings, json) {
                        $('#loader_search').hide();
                        setTimeout(() => {
                            $('#rotateIcon').removeClass("rotate");
                        }, 1000);
                    }
                });
            }
            $('#list_kwitansi_table').on('click', '.btn-edit', function () {
                let rowIndex = $(this).data('row-index');
                let row = $('#list_kwitansi_table').DataTable().row(rowIndex).node();

                $(row).find('.faktur-input').each(function () {
                    $(this).attr('data-original', $(this).val());
                });

                $(row).find('.faktur-input-potongan').attr('data-original', $(row).find('.faktur-input-potongan').val());
                $(row).find('.faktur-input-value_ppn').attr('data-original', $(row).find('.faktur-input-value_ppn').val());
                $(row).find('.faktur-input-psl23').attr('data-original', $(row).find('.faktur-input-psl23').val());
                $(row).find('.faktur-input-bukti_Potong').attr('data-original', $(row).find('.faktur-input-bukti_Potong').val());
                $(row).find('.faktur-input').attr('data-original', $(row).find('.faktur-input').val());

                $(row).find('.faktur-input-potongan').prop('disabled', false);
                $(row).find('.faktur-input-value_ppn').prop('disabled', false);
                $(row).find('.faktur-input-psl23').prop('disabled', false);
                $(row).find('.faktur-input-bukti_Potong').prop('disabled', false);
                $(row).find('.faktur-input').prop('disabled', false);
                $(row).find('.btn-confirm-edit').prop('disabled', true);

                $(row).find('.btn-edit').addClass('d-none');
                $(row).find('.btn-save').removeClass('d-none');
                $(row).find('.btn-cancel').removeClass('d-none');

            });
            $(document).on('input', '.faktur-input-potongan', function () {
                let $this = $(this);
                let rowIndex = $this.data('row-index');
                let row = $('#list_kwitansi_table').DataTable().row(rowIndex).node();


                let value = $(row).find('.value_taihan').text();
                value = parseInt(value.replace(/\./g, ''));

                let value_potong = $(this).val().replace(/[^0-9]/g, '');
                value_potong = parseInt(value_potong || 0);

                let nilai_akhir_pph = (value - value_potong)/50 ;
                nilai_akhir_pph = parseInt(nilai_akhir_pph);
                nilai_akhir_pph = nilai_akhir_pph.toLocaleString('id-ID');

                let nilai_akhir_ppn = (value)*0.11 ;
                nilai_akhir_ppn = parseInt(nilai_akhir_ppn);
                nilai_akhir_ppn = nilai_akhir_ppn.toLocaleString('id-ID');

                let value_pph = $(row).find('.faktur-input-psl23');
                let value_ppn = $(row).find('.faktur-input-value_ppn');
                value_pph.val(nilai_akhir_pph);
                value_ppn.val(nilai_akhir_ppn);

            });
            $('#list_kwitansi_table').on('click', '.btn-save', function () {
                let rowIndex = $(this).data('row-index');
                let row = $('#list_kwitansi_table').DataTable().row(rowIndex).node();

                var baris = $(this).closest('tr');
                no_kwitansi = baris.find('td:eq(1)').text();

                let input = $(row).find('.faktur-input');

                potongan = $(row).find('.faktur-input-potongan');
                potongan = potongan.val();
                potongan = parseInt(potongan.replace(/\./g, ''));

                pajak_psl_23 = $(row).find('.faktur-input-psl23');
                pajak_psl_23 = pajak_psl_23.val();
                pajak_psl_23 = parseInt(pajak_psl_23.replace(/\./g, ''));

                pajak_value_ppn= $(row).find('.faktur-input-value_ppn');
                pajak_value_ppn = pajak_value_ppn.val();
                pajak_value_ppn = parseInt(pajak_value_ppn.replace(/\./g, ''));

                pajak_bukti_Potong= $(row).find('.faktur-input-bukti_Potong');
                pajak_bukti_Potong = pajak_bukti_Potong.val();
                let potongan_awal = $(row).find('.faktur-input-potongan').attr('data-original');
                potongan_awal = parseInt(potongan_awal.replace(/\./g, ''));
                // alert(potongan_awal);
                // return;

                // console.log(pajak_psl_23, pajak_value_ppn, pajak_bukti_Potong);
                // return;
                borongan = 0;
                value = input.val();
                let currentNoty = new Noty({
                    text: `
                        <div style="font-size: 15px; line-height: 1.5;">
                            <strong>Konfirmasi</strong><br>
                            Apakah Anda yakin ingin <b>melanjutkan</b> transaksi ini?<br>
                            <small style="color: red;">Pastikan data yang Anda masukkan benar.</small>
                        </div>
                    `,
                    type: 'alert',
                    layout: 'center',
                    theme: 'sunset',
                    modal: true,
                    killer: true,
                    closeWith: [],
                    buttons: [
                        Noty.button('Ya, Konfirmasi', 'btn bg-gradient-success btn-sm btn-rounded mt-3', function (notyInstance) {
                            $.ajax({
                                url: '/update-faktur-pajak-japfa',
                                method: 'POST',
                                data: {
                                    potongan: potongan,
                                    no_kwitansi: no_kwitansi,
                                    no_faktur_pajak: value,
                                    pajak_psl_23: pajak_psl_23,
                                    pajak_value_ppn: pajak_value_ppn,
                                    kode_bukti_Potong: pajak_bukti_Potong,
                                    dataToSave: dataToSave,
                                    potongan_awal: potongan_awal,
                                    borongan: borongan,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function () {
                                    $('#loader_save').hide();
                                    $(row).find('.faktur-input-potongan').prop('disabled', true);
                                    $(row).find('.faktur-input-value_ppn').prop('disabled', true);
                                    $(row).find('.faktur-input-psl23').prop('disabled', true);
                                    $(row).find('.faktur-input-bukti_Potong').prop('disabled', true);
                                    $(row).find('.faktur-input').prop('disabled', true);
                                    $(row).find('.btn-edit').removeClass('d-none');
                                    $(row).find('.btn-save').addClass('d-none');
                                    $(row).find('.btn-cancel').addClass('d-none');
                                    $(row).find('.btn-confirm-edit').prop('disabled', false);
                                    new Noty({
                                        text: '<i class="fas fa-check"></i> Data berhasil dikonfirmasi.',
                                        type: 'info',
                                        timeout: 3000,
                                        layout: 'topRight'
                                    }).show();
                                },
                                error: function (xhr) {
                                    $('#loader_save').hide();
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
                            notyInstance.close();
                        }),

                        Noty.button('Batal', 'btn bg-gradient-danger btn-sm btn-rounded mx-1 mt-3', function (notyInstance) {
                            notyInstance.close();
                            new Noty({
                                text: '<i class="fas fa-info-circle"></i> Konfirmasi dibatalkan.',
                                type: 'info',
                                timeout: 3000,
                                layout: 'topRight'
                            }).show();
                        })
                    ]
                }).show();
            });
            $('#list_kwitansi_table').on('click', '.btn-cancel', function () {
                let rowIndex = $(this).data('row-index');
                let row = $('#list_kwitansi_table').DataTable().row(rowIndex).node();
                let currentNoty = new Noty({
                    text: `
                        <div style="font-size: 15px; line-height: 1.5;">
                            <strong>Konfirmasi</strong><br>
                            Apakah Anda yakin ingin <b>membatalkan</b> transaksi ini?<br>
                            <small style="color: red;">Catatan: Tindakan ini akan mengembalikan data ke kondisi sebelumnya.</small>
                        </div>
                    `,
                    type: 'alert',
                    layout: 'center',
                    theme: 'sunset',
                    modal: true,
                    killer: true,
                    closeWith: [],
                    buttons: [
                        Noty.button('Ya, Konfirmasi', 'btn bg-gradient-success btn-sm btn-rounded mt-3', function (notyInstance) {
                            $(row).find('.faktur-input').each(function () {
                                $(this).val($(this).attr('data-original'));
                            });

                            $(row).find('.faktur-input-potongan').val($(row).find('.faktur-input-potongan').attr('data-original'));
                            $(row).find('.faktur-input-value_ppn').val($(row).find('.faktur-input-value_ppn').attr('data-original'));
                            $(row).find('.faktur-input-psl23').val($(row).find('.faktur-input-psl23').attr('data-original'));
                            $(row).find('.faktur-input-bukti_Potong').val($(row).find('.faktur-input-bukti_Potong').attr('data-original'));
                            $(row).find('.faktur-input').attr('data-original', $(row).find('.faktur-input').val());

                            $(row).find('.faktur-input-potongan').prop('disabled', true);
                            $(row).find('.faktur-input-value_ppn').prop('disabled', true);
                            $(row).find('.faktur-input-psl23').prop('disabled', true);
                            $(row).find('.faktur-input-bukti_Potong').prop('disabled', true);
                            $(row).find('.faktur-input').prop('disabled', true);
                            $(row).find('.btn-confirm-edit').prop('disabled', false);

                            $(row).find('.btn-edit').removeClass('d-none');
                            $(row).find('.btn-save').addClass('d-none');
                            $(row).find('.btn-cancel').addClass('d-none');
                            notyInstance.close();
                        }),

                        Noty.button('Batal', 'btn bg-gradient-danger btn-sm btn-rounded mx-1 mt-3', function (notyInstance) {
                            notyInstance.close();
                            new Noty({
                                text: '<i class="fas fa-info-circle"></i> Konfirmasi dibatalkan.',
                                type: 'info',
                                timeout: 3000,
                                layout: 'topRight'
                            }).show();
                        })
                    ]
                }).show();
            });
            $('#btn_save_all').on('click', function () {
                let table = $('#list_kwitansi_table').DataTable();
                borongan = 1;
                dataToSave = [];

                $('#list_kwitansi_table tbody tr').has('.btn-save:not(.d-none)').each(function () {
                    let row = $(this);
                    let no_kwitansi = row.find('td:eq(1)').text();
                    let pajak_psl_23 = row.find('.faktur-input-psl23').val().replace(/\./g, '');
                    let potongan = row.find('.faktur-input-potongan').val().replace(/\./g, '');
                    let pajak_value_ppn = row.find('.faktur-input-value_ppn').val().replace(/\./g, '');
                    let pajak_bukti_Potong = row.find('.faktur-input-bukti_Potong').val();
                    let no_faktur_pajak = row.find('.faktur-input').val();

                    dataToSave.push({
                        no_kwitansi: no_kwitansi,
                        pajak_psl_23: parseInt(pajak_psl_23),
                        pajak_value_ppn: parseInt(pajak_value_ppn),
                        pajak_bukti_Potong: pajak_bukti_Potong,
                        no_faktur_pajak: no_faktur_pajak,
                        potongan: potongan
                    });
                });

                if (dataToSave.length === 0) {
                    new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: 'Tidak ada data yang ditambahkan!',
                        timeout: 3000
                    }).show();
                    return;
                }

                new Noty({
                    text: `
                        <div style="font-size: 15px; line-height: 1.5;">
                            <strong>Konfirmasi</strong><br>
                            Apakah Anda yakin ingin <b>melanjutkan</b> transaksi ini?<br>
                            <small style="color: red;">Pastikan data yang Anda masukkan benar.</small>
                        </div>
                    `,
                    type: 'alert',
                    layout: 'center',
                    theme: 'sunset',
                    modal: true,
                    killer: true,
                    closeWith: [],
                    buttons: [
                        Noty.button('Ya, Konfirmasi', 'btn bg-gradient-success btn-sm btn-rounded mt-3', function (notyInstance) {
                            $.ajax({
                                url: '/update-faktur-pajak-japfa',
                                method: 'POST',
                                data: {
                                    dataToSave: dataToSave,
                                    borongan: borongan,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function () {
                                    $('#loader_save').hide();

                                    // Loop semua baris yang sebelumnya disimpan
                                    $('#list_kwitansi_table tbody tr').has('.btn-save:not(.d-none)').each(function () {
                                        let row = $(this);
                                        row.find('input').prop('disabled', true);
                                        row.removeClass('dirty');
                                        row.find('.btn-edit').removeClass('d-none');
                                        row.find('.btn-save, .btn-cancel').addClass('d-none');
                                    });

                                    new Noty({
                                        text: '<i class="fas fa-check"></i> Data berhasil dikonfirmasi.',
                                        type: 'info',
                                        timeout: 3000,
                                        layout: 'topRight'
                                    }).show();
                                },
                                error: function (xhr) {
                                    $('#loader_save').hide();
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
                            notyInstance.close();
                        }),

                        Noty.button('Batal', 'btn bg-gradient-danger btn-sm btn-rounded mx-1 mt-3', function (notyInstance) {
                            notyInstance.close();
                            new Noty({
                                text: '<i class="fas fa-info-circle"></i> Konfirmasi dibatalkan.',
                                type: 'info',
                                timeout: 3000,
                                layout: 'topRight'
                            }).show();
                        })
                    ]
                }).show();
            });
            $('#list_kwitansi_table').on('keydown', '.faktur-input-save', function (e) {
                if (e.ctrlKey && e.key.toLowerCase() === 's') {
                    e.preventDefault();
                    let rowIndex = $(this).data('row-index');
                    $('#list_kwitansi_table')
                        .find(`.btn-save[data-row-index="${rowIndex}"]`)
                        .trigger('click');
                }else if (e.key === 'Escape') {
                    $('#list_kwitansi_table').find('tr').each(function () {
                        let row = $(this);
                        let rowIndex = row.find('.btn-cancel').data('row-index');

                        if (row.find('.btn-cancel').is(':visible')) {
                            $(`.btn-cancel[data-row-index="${rowIndex}"]`).trigger('click');
                        }
                    });
                }
            });
            function storePajak() {
                $('#loader_save').show();
                $.ajax({
                    url: '/update-faktur-pajak-japfa',
                    method: 'POST',
                    data: {
                        potongan: potongan,
                        no_kwitansi: no_kwitansi,
                        no_faktur_pajak: value,
                        pajak_psl_23: pajak_psl_23,
                        pajak_value_ppn: pajak_value_ppn,
                        kode_bukti_Potong: pajak_bukti_Potong,
                        dataToSave: dataToSave,
                        borongan: borongan,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        $('#loader_save').hide();
                        new Noty({
                            text: '<i class="fas fa-check"></i> Data berhasil dikonfirmasi.',
                            type: 'info',
                            timeout: 3000,
                            layout: 'topRight'
                        }).show();
                    },
                    error: function (xhr) {
                        $('#loader_save').hide();
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
            }
            function storePajak_confirm(kode) {
                $('#loader_save').show();
                $.ajax({
                    url: '/update-faktur-pajak-confirm-japfa',
                    method: 'POST',
                    data: {
                        no_kwitansi: kode,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        initializeDataTable();
                        $('#loader_save').hide();
                        new Noty({
                            text: '<i class="fas fa-check"></i> Data berhasil dikonfirmasi.',
                            type: 'info',
                            timeout: 3000,
                            layout: 'topRight'
                        }).show();
                    },
                    error: function (xhr) {
                        $('#loader_save').hide();
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
            }
            function formatRupiah(angka) {
                return 'Rp ' + parseFloat(angka).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
            $(document).on('input', '.input_money', function () {
                let value = $(this).val();
                value = value.replace(/[^0-9,]/g, '');
                let parts = value.split(',');
                let intPart = parts[0];
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                if(parts.length > 1){
                    let decimalPart = parts[1].substring(0, 3);
                    value = intPart + ',' + decimalPart;
                } else {
                    value = intPart;
                }

                $(this).val(value);
            });
            $(document).on('click', '.btn-confirm-edit', function () {
                var row = $(this).closest('tr');
                kode = row.find('td:eq(1)').text();
                no_faktur = row.find('td:eq(9)').find('input').val();
                no_bukti_potong = row.find('td:eq(10)').find('input').val();
                if (no_faktur == '' ) {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Nomor faktur wajib diisi. Harap lengkapi terlebih dahulu sebelum melanjutkan proses.
                                </p>
                                <small style="color: #007bff; font-size: 11px; font-style: italic;">
                                    Klik di sini untuk menutup pesan ini
                                </small>
                            </div>
                        `,
                        type: 'alert',
                        layout: 'center',
                        timeout: 6000,
                        theme: 'bootstrap-v4',
                        modal: true,
                        killer: true,
                    }).show();
                    return;
                }
                if (no_bukti_potong == '' ) {
                    new Noty({
                        text: `
                            <div>
                                <strong style="color: #dc3545;">
                                    <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>Attention
                                </strong>
                                <p style="margin: 4px 0 8px 0; font-size: 14px; color: #333;">
                                    Nomor bukti potong wajib diisi. Harap lengkapi terlebih dahulu sebelum melanjutkan proses.
                                </p>
                                <small style="color: #007bff; font-size: 11px; font-style: italic;">
                                    Klik di sini untuk menutup pesan ini
                                </small>
                            </div>
                        `,
                        type: 'alert',
                        layout: 'center',
                        timeout: 6000,
                        theme: 'bootstrap-v4',
                        modal: true,
                        killer: true,
                    }).show();
                    return;
                }
                new Noty({
                    text: `
                        <div style="font-size: 15px; line-height: 1.5;">
                            <strong>Konfirmasi</strong><br>
                            Apakah Anda yakin ingin <b>melanjutkan</b> transaksi ini?<br>
                            <small style="color: red;">
                                <i class="fas fa-exclamation-triangle"></i> Pastikan data yang Anda masukkan benar. Data tidak dapat diubah lagi setelah transaksi dikonfirmasi.
                            </small>
                        </div>
                    `,
                    type: 'alert',
                    layout: 'center',
                    theme: 'sunset',
                    modal: true,
                    killer: true,
                    closeWith: [],
                    buttons: [
                        Noty.button('Ya, Konfirmasi', 'btn bg-gradient-success btn-sm btn-rounded mt-3', function (notyInstance) {
                            storePajak_confirm(kode);
                            notyInstance.close();
                        }),

                        Noty.button('Batal', 'btn bg-gradient-danger btn-sm btn-rounded mx-1 mt-3', function (notyInstance) {
                            notyInstance.close();
                            new Noty({
                                text: '<i class="fas fa-info-circle"></i> Konfirmasi dibatalkan.',
                                type: 'info',
                                timeout: 3000,
                                layout: 'topRight'
                            }).show();
                        })
                    ]
                }).show();
            });
            $("#rotateIcon").click(function() {
                $(this).addClass("rotate");
                initializeDataTable();
                // setTimeout(() => {
                //     $(this).removeClass("rotate");
                // }, 1000);
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
@endsection
@section('content')
    <div>
        <div class="row">
            <div class="col-12" id="div_table_list_kwitansi">
                <div class="card mb-4 ">
                    <div class="card-header pb-0">
                        <div class="d-flex gap-3">
                            <h4>List Pajak <i id="rotateIcon" class="fa-solid fa-arrows-rotate" data-bs-toggle="tooltip" data-bs-placement="top"data-bs-custom-class="custom-tooltip"data-bs-title="Refresh data."></i></h4>

                            <div class="loader_bulat" id="loader_save"></div>
                            <style>
                                .rotate {
                                    transition: transform 1s ease-in-out, color 1s ease-in-out;
                                    transform: rotate(360deg);
                                    color: rgb(24, 255, 112); /* Warna saat animasi */
                                    cursor: pointer;
                                }

                                .default-color {
                                    color: black; /* Warna awal */
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
                                    from { opacity: 0; }
                                    to { opacity: 1; }
                                }

                                @keyframes fadeout {
                                    from { opacity: 1; }
                                    to { opacity: 0; }
                                }
                                .bg-custom-danger{
                                    background-color: #ff4757;
                                }
                                .bg-custom-info{
                                    background-color: #44bd32;
                                }

                            </style>
                            <style>
                                .fa-rotate-right:hover{
                                    color: #00cec9;
                                }
                                .loader_bulat {
                                width: 30px;
                                height: 30px;
                                aspect-ratio: 1;
                                border-radius: 50%;
                                background:
                                    radial-gradient(farthest-side,#00cec9 94%,#0000) top/5px 5px no-repeat,
                                    conic-gradient(#0000 30%,#00cec9);
                                -webkit-mask: radial-gradient(farthest-side,#0000 calc(100% - 5px),#000 0);
                                animation: l13 1s infinite linear;
                                }
                                @keyframes l13{
                                100%{transform: rotate(1turn)}
                                }
                            </style>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                            <button class="btn bg-gradient-info btn-sm mb-0 mx-3 float-end" id="btn_save_all"><i class="fas fa-save" style="font-size: 16px;"></i> Save All</button>
                            <div>
                                <table class="table align-items-center mb-0 table-bordered table-striped" id="list_kwitansi_table">
                                    <thead class="table-info">
                                        <tr>
                                            <th></th>
                                            <th>No Kwitansi</th>
                                            <th>Tgl</th>
                                            <th>Client</th>
                                            <th>Note</th>
                                            <th>Value(Rp)</th>
                                            <th>Potongan</th>
                                            <th>pph PS 23(Rp)</th>
                                            <th>ppn 12%(Rp)</th>
                                            <th>No. Faktur Pajak</th>
                                            <th>Bukti Potong pph PS 23</th>
                                            <th></th>
                                            <th></th>
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
