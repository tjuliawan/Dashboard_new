<style>
    .dt-search {
        text-align: right;
        margin-right: 1rem;
    }

    .buttons-excel {
        margin-right: 1rem;
        margin-left: 1rem;
        padding: 0.38rem 1rem;
        /* Tambahkan padding agar tombol lebih besar */
        height: auto;
        /* Sesuaikan tinggi dengan isi */
        background-color: #55efc4;
        /* Warna biru modern */
        color: white;
        /* Warna teks putih */
        border: none;
        /* Hilangkan border bawaan */
        /* Tambahkan sudut melengkung */
        font-size: 0.9rem;
        /* Ukuran teks yang sesuai */
        cursor: pointer;
        /* Ubah cursor menjadi pointer */
        transition: all 0.3s ease;
        /* Animasi transisi untuk hover */
    }

    .buttons-excel {
        background-color: #55efc4 !important;
    }

    /* .buttons-excel:hover {
                background-color: #0056b3;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            }

            .buttons-excel:focus {
                outline: none;
                background-color: #004085;
            } */
</style>
<style>
    .table th {
        font-size: 12px;
        /* Ukuran font untuk header tabel */
        text-align: center;
        /* Rata tengah horizontal untuk header */
        vertical-align: middle;
        /* Rata tengah vertikal untuk header */
        padding: 6px 8px;
        /* Padding untuk header tabel */
    }

    .table td {
        font-size: 12px;
        /* Ukuran font untuk sel tabel */
        padding: 4px 8px;
        /* Padding untuk sel tabel */
        line-height: 1.2;
        /* Tinggi baris untuk keterbacaan */
        vertical-align: middle;
        /* Rata tengah vertikal untuk sel tabel */
        /* text-align: center;       Uncomment untuk rata tengah horizontal */
    }

    #dataTable2 th,
    #salesTable,
    #dataTable2 td,
    #dataTable_po,
    #dataTable_po_detail,
    #dataTable_d_a_karyawan,
    #dataTable_selling,
    #dataTable_selling_drink,
    #dataTable th,
    #dataTable_ff,
    #dataTable_df,
    #Tabel_bbm,
    #table_detail_spatepart,
    #Tabel_km,
    #Tabel_detail_bbm,
    #Tabel_detail_km,
    #dataTable td {
        white-space: nowrap;
    }

    .table td {
        font-size: 12px;
    }
</style>

<style>
    .btn-custom-green {
        background: linear-gradient(135deg, #00ff87, #60efff);
        border: none;
        color: #fff;
        transition: background 0.3s ease;
    }

    .btn-custom-green:hover {
        color: #fff;
    }

    /* navbar Vertikal */
    .navbar-vertical .navbar-nav>.nav-item .nav-link.active {
        background: linear-gradient(135deg, #e0f2f1, #c7e4e1);
        /* gradasi hijau kebiruan yang soft */
        color: #004d40;
        /* warna teks gelap agar kontras */
        border-radius: 8px;
        /* sedikit rounded untuk kesan modern */
        font-weight: 500;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.3s ease;
    }


    /* .navbar-vertical .navbar-nav>.nav-item .nav-link.active .icon {
    background-image: linear-gradient(310deg, #2ad8cf, #cb0c9f) !important;
} */

    /* body */
    .bg-gray-100 {
        background: linear-gradient(135deg, #f8f9fa, #e0f1ed, #e8f7ef) !important;
        background-size: 400% 400%;
        animation: bgFlow 15s ease infinite;
    }

    @keyframes bgFlow {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    /* CSS Choices.js  */
    /* tabel */
    .choices {
        position: relative;
        overflow: hidden;
        margin-bottom: 0px;
        font-size: 10px !important;
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, #1ccb0c, #8ee46d);
        border-color: #1ccb0c00;
        color: #fff;
        z-index: 3;
    }

    .form-check-input.row-checkbox {
        box-shadow: 0 0 4px rgba(37, 37, 37, 0.432);
    }

    /* Wrapper utama */
    .choices__inner {
        background-color: #f4f6f900 !important;
        border: 1px solid #c3c6cadc;
        border-radius: 8px !important;
        padding: 7px;
        font-size: 13px;
        color: #292f38c9;
        height: auto;
        min-height: calc(1.88rem + 2px);
        box-shadow: none;
        transition: border-color 0.3s ease;
    }

    /* Hover effect */
    .choices__inner:hover {
        border-color: #f866f89d;
    }

    /* Focused effect */
    .is-focused .choices__inner,
    .is-open .choices__inner {
        border: 2.5px solid #f866f89d !important;
    }

    /* Item terpilih (tags) */
    .choices__item--selectable {
        /* background-color: #f866f89d; */
        color: rgb(65, 61, 61);
        border-radius: 6px;
        padding: 3px 3px;
        margin: 0;
        font-size: 11px;
    }


    /* Dropdown menu */
    .choices__list--dropdown {
        background-color: #ffffff;
        border: 1px solid #c3c6cadc;
        border-radius: 8px;
        max-height: 300px;
        overflow-y: auto;
        font-size: 11px !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    /* Opsi yang bisa dipilih */
    .choices__list--dropdown .choices__item {
        padding: 10px 15px;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }

    /* Opsi disorot (highlight) */
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: #f866f89d !important;
        color: #fff;
    }

    /* Hover di opsi */
    .choices__list--dropdown .choices__item:hover {
        background-color: #e0e0e0;
    }

    /* Placeholder style */
    .choices__placeholder {
        color: #999;
        opacity: 0.7;
        font-size: 11px !important;
    }

    /* Custom Select2 Theme */
    .select2-container--custom .select2-selection--single {
        background-color: #f4f6f900 !important;
        border: 1px solid #c3c6cadc !important;
        border-radius: 8px !important;
        padding: 7px !important;
        font-size: 13px !important;
        color: #292f38c9 !important;
        height: calc(1.88rem + 2px) !important;
    }

    .select2-container--custom .select2-selection--single:hover {
        border-color: #f866f89d !important;
    }

    .select2-container--custom .select2-selection--single:focus {
        outline: none;
        border: 2.5px solid #f866f89d !important;
    }

    .select2-container--custom .select2-selection__rendered {
        line-height: 1.5;
        /* Sesuaikan dengan tinggi input Bootstrap */
    }

    /* Dropdown styling */
    .select2-container--custom .select2-dropdown {
        background-color: #fff !important;
        border-radius: 8px !important;
        max-height: 300px !important;
        overflow-y: auto !important;
        font-size: 13px !important;
    }

    /* Highlight selected items */
    .select2-container--custom .select2-results__option--highlighted {
        background-color: #f866f89d !important;
        color: white !important;
    }

    .select2-container--custom .select2-results__option {
        padding: 10px 15px;
        cursor: pointer;
    }

    .select2-container--custom .select2-results__option:hover {
        background-color: #e0e0e0;
    }
</style>
{{-- noty js --}}
<style>
    .noty_type__alert {
        background: #ffffff !important;
        color: #212529 !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 20px;
    }
    .noty_type__info {
        background: #ffffff !important;
        color: #212529 !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 20px;
    }
    .noty_type__success {
        background: #55efc4 !important;
        color: #f5faff !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 20px;
    }
    .noty_type__error {
        background: #eb3b5a !important;
        color: #f5faff !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 20px;
    }
</style>
