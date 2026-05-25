<style>
    /* ===== Tema global formal — palet corporate (slate + steel blue) =====
       Diterapkan ke seluruh halaman (auth & guest) via layouts/app.blade.php
    */
    body.g-sidenav-show,
    body.bg-gray-100,
    body {
        background: #7491ad !important;
        min-height: 100vh;
        background-attachment: fixed !important;
    }
    /* Override Soft UI bg-gray-100 utility */
    .bg-gray-100 { background-color: #7491ad !important; }

    /* Card formal: putih solid + border tipis + shadow halus */
    main .card,
    .container-fluid .card {
        background: #ffffff !important;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06) !important;
    }
    main .card .card-header,
    .container-fluid .card .card-header { background: transparent !important; }

    /* Brand gradient menggantikan bg-gradient-* dengan nuansa formal */
    main .bg-gradient-primary,
    .container-fluid .bg-gradient-primary {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;
    }
    main .bg-gradient-dark,
    .container-fluid .bg-gradient-dark {
        background: linear-gradient(135deg, #1f2937 0%, #334155 100%) !important;
    }
    main .bg-gradient-info {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 100%) !important;
    }
    main .bg-gradient-success {
        background: linear-gradient(135deg, #14532d 0%, #166534 100%) !important;
    }
    main .bg-gradient-warning {
        background: linear-gradient(135deg, #92400e 0%, #b45309 100%) !important;
    }
    main .bg-gradient-danger {
        background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%) !important;
    }

    /* Sidebar formal: navy gelap solid */
    .sidenav {
        background: #1e293b !important;
        box-shadow: 2px 0 8px rgba(15, 23, 42, .15);
        border: none;
        font-size: 1.05em;
    }
    /* Brand / logo area */
    .sidenav .navbar-brand,
    .sidenav .navbar-brand-img + span,
    .sidenav .navbar-brand .ms-1,
    .sidenav .navbar-brand-img,
    .sidenav hr.horizontal {
        background-color: transparent !important;
    }
    .sidenav .sidenav-header {
        padding: 18px 16px 12px;
        min-height: 80px;
    }
    .sidenav .navbar-brand {
        display: flex !important;
        align-items: center;
        gap: 12px;
        padding: 8px 12px !important;
        background: rgba(255, 255, 255, .08);
        border-radius: 8px;
        box-shadow: none;
    }
    .sidenav .navbar-brand-img {
        height: 44px !important;
        width: auto !important;
        max-width: 56px;
        object-fit: contain;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,.25));
    }
    .sidenav .navbar-brand,
    .sidenav .navbar-brand span,
    .sidenav .navbar-brand .ms-1,
    .sidenav .navbar-brand .ms-3 {
        color: #f1f5f9 !important;
        text-shadow: none;
        font-size: .95rem;
    }
    .sidenav hr.horizontal.dark,
    .sidenav hr.horizontal.light {
        background-image: linear-gradient(90deg, transparent, rgba(241,245,249,.25), transparent) !important;
        opacity: 1 !important;
    }
    /* Tombol close di sidebar (mobile) */
    .sidenav .text-secondary { color: #cbd5e1 !important; opacity: .9; }

    /* Menu items — teks slate terang di atas navy */
    .sidenav .navbar-nav .nav-link,
    .sidenav .navbar-nav .nav-link span,
    .sidenav .navbar-nav .nav-link .nav-link-text,
    .sidenav .navbar-nav .nav-link .sidenav-mini-icon,
    .sidenav .navbar-nav .nav-link .sidenav-normal,
    .sidenav .navbar-nav .nav-link i,
    .sidenav .navbar-nav .nav-link svg,
    .sidenav .navbar-nav .nav-link svg path,
    .sidenav .navbar-nav .nav-link svg g {
        color: #cbd5e1 !important;
        fill: #cbd5e1 !important;
    }
    .sidenav .navbar-nav .nav-link {
        opacity: 1 !important;
        font-weight: 500;
        text-shadow: none;
        border-radius: 6px;
        margin: 2px 8px;
        transition: background .15s, color .15s;
    }
    .sidenav .navbar-nav .nav-link:hover {
        background: rgba(255, 255, 255, .06) !important;
        color: #ffffff !important;
    }
    .sidenav .navbar-nav .nav-link.active {
        background: #ffffff !important;
        color: #1e293b !important;
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(0,0,0,.15);
        text-shadow: none;
    }
    .sidenav .navbar-nav .nav-link.active span,
    .sidenav .navbar-nav .nav-link.active .nav-link-text,
    .sidenav .navbar-nav .nav-link.active i,
    .sidenav .navbar-nav .nav-link.active svg,
    .sidenav .navbar-nav .nav-link.active svg path,
    .sidenav .navbar-nav .nav-link.active svg g {
        color: #1e293b !important;
        fill: #1e293b !important;
    }

    /* Sub-heading / kategori (mis. "ACCOUNT PAGES") */
    .sidenav .navbar-nav h6,
    .sidenav .navbar-nav .text-uppercase,
    .sidenav .navbar-nav .ms-2.text-uppercase {
        color: #94a3b8 !important;
        letter-spacing: .8px;
        font-weight: 600;
        opacity: 1 !important;
        text-shadow: none;
    }

    /* Icon box (icon-shape) di samping nav-link (Soft UI) */
    .sidenav .navbar-nav .nav-link .icon-shape {
        background: rgba(255, 255, 255, .08) !important;
        box-shadow: none !important;
    }
    .sidenav .navbar-nav .nav-link .icon-shape svg path,
    .sidenav .navbar-nav .nav-link .icon-shape svg g {
        fill: #cbd5e1 !important;
    }
    .sidenav .navbar-nav .nav-link.active .icon-shape {
        background: #1e40af !important;
    }
    .sidenav .navbar-nav .nav-link.active .icon-shape svg path,
    .sidenav .navbar-nav .nav-link.active .icon-shape svg g {
        fill: #ffffff !important;
    }

    /* Card promo / user-card di footer sidebar */
    .sidenav .card { background: rgba(255,255,255,.05) !important; border: 1px solid rgba(255,255,255,.10) !important; }
    .sidenav .card *:not(.avatar-wrapper):not(.avatar-wrapper *):not(.btn-white) { color: #e2e8f0 !important; }
    /* Avatar profile harus tetap kontras */
    .sidenav .avatar-wrapper { background-color: #ffffff !important; }
    .sidenav .avatar-wrapper .fallback-icon,
    .sidenav .avatar-wrapper i { color: #1e293b !important; }
    .sidenav .avatar-wrapper img { filter: none !important; }
    /* Tombol "Profile" tetap putih dengan teks gelap */
    .sidenav .btn-white { color: #1e293b !important; background-color: #ffffff !important; }

    /* ===== Top navbar teks — putih di atas background #7491ad ===== */
    .navbar-main .breadcrumb-item a,
    .navbar-main .breadcrumb-item.active,
    .navbar-main h6,
    .navbar-main .nav-link,
    .navbar-main .nav-link span,
    .navbar-main .nav-link i,
    .navbar-main .sidenav-toggler-line,
    #toggleSidebarText {
        color: #ffffff !important;
        opacity: 1 !important;
    }
    .navbar-main .breadcrumb-item a.opacity-5 { opacity: .75 !important; }
    .navbar-main .sidenav-toggler-line {
        background: #ffffff !important;
    }

    /* ===== Footer teks — putih di atas background #7491ad ===== */
    .footer .copyright,
    .footer .copyright .text-muted,
    .footer .text-muted {
        color: rgba(255,255,255,.85) !important;
    }
    .footer .copyright .font-weight-bold {
        color: #ffffff !important;
    }

    /* ===== Dashboard: Active Users & chart area ===== */
    /* h6 label dan teks persen di bawah chart bar */
    .card h6.ms-2 {
        color: #1e293b !important;
    }
    #persen-transaksi p,
    #persen-transaksi span {
        color: #334155 !important;
    }
    /* Teks dalam bg-gradient-dark (chart bar bg) tetap putih */
    .bg-gradient-dark h6,
    .bg-gradient-dark p,
    .bg-gradient-dark span {
        color: #ffffff !important;
    }

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

    .navbar-vertical .navbar-nav>.nav-item .nav-link:hover {
        background: linear-gradient(135deg, #e0f2f196, #d9ecead3) !important;
    }

    .navbar-vertical .navbar-nav>.nav-item .nav-link.active:hover {
        background: linear-gradient(135deg, #e0f2f1, #c7e4e1) !important;
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
