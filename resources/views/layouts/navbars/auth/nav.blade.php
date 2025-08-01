<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                <li class="breadcrumb-item text-sm text-dark active text-capitalize" aria-current="page">{{ str_replace('-', ' ', Request::path()) }}</li>
            </ol>
            <h6 class="font-weight-bolder mb-0 text-capitalize">{{ str_replace('-', ' ', Request::path()) }}</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar">
            {{-- <div class="nav-item d-flex align-self-end">
                <a href="https://www.creative-tim.com/product/soft-ui-dashboard-laravel" target="_blank" class="btn btn-primary active mb-0 text-white" role="button" aria-pressed="true">
                    Download
                </a>
            </div> --}}
            {{-- <div class="ms-md-3 pe-md-3 d-flex align-items-center">
            <div class="input-group input-group-sm">
                <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                <input type="text" class="form-control" id="menuSearch" placeholder="Type here...">
            </div>
            </div> --}}
            <ul class="navbar-nav  justify-content-end">
                <li class="nav-item d-flex align-items-center">
                    <a href="{{ url('/logout') }}" class="nav-link text-body font-weight-bold px-0">
                        <i class="fa fa-user me-sm-1"></i>
                        <span class="d-sm-inline d-none">Sign Out</span>
                    </a>
                </li>
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>
                <li class="nav-item px-3 d-flex align-items-center" id="sidebarToggleBtn">
                    <a href="javascript:;" class="nav-link text-body p-0 font-weight-bold">
                        <i id="toggleSidebarIcon" class="fas fa-expand cursor-pointer me-1"></i>
                        <span id="toggleSidebarText">Full Screen</span>
                    </a>
                </li>

                {{-- <li class="nav-item dropdown pe-2 d-flex align-items-center">
                <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell cursor-pointer"></i>
                </a>
                <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                <li class="mb-2">
                    <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                        <div class="my-auto">
                        <img src="../assets/img/team-2.jpg" class="avatar avatar-sm  me-3 ">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                            <span class="font-weight-bold">New message</span> from Laur
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                            <i class="fa fa-clock me-1"></i>
                            13 minutes ago
                        </p>
                        </div>
                    </div>
                    </a>
                </li>
                <li class="mb-2">
                    <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                        <div class="my-auto">
                        <img src="../assets/img/small-logos/logo-spotify.svg" class="avatar avatar-sm bg-gradient-dark  me-3 ">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                            <span class="font-weight-bold">New album</span> by Travis Scott
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                            <i class="fa fa-clock me-1"></i>
                            1 day
                        </p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                        <div class="avatar avatar-sm bg-gradient-secondary  me-3  my-auto">
                        <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <title>credit-card</title>
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                <g transform="translate(1716.000000, 291.000000)">
                                <g transform="translate(453.000000, 454.000000)">
                                    <path class="color-background" d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z" opacity="0.593633743"></path>
                                    <path class="color-background" d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z"></path>
                                </g>
                                </g>
                            </g>
                            </g>
                        </svg>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                            Payment successfully completed
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                            <i class="fa fa-clock me-1"></i>
                            2 days
                        </p>
                        </div>
                    </div>
                    </a>
                </li>
                </ul>
            </li> --}}
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
{{-- <script>
  document.getElementById('menuSearch').addEventListener('keyup', function () {
    const searchTerm = this.value.toLowerCase();
    const menuItems = document.querySelectorAll('.menu-item');

    menuItems.forEach(function (item) {
      const text = item.innerText.toLowerCase();
      item.style.display = text.includes(searchTerm) ? '' : 'none';
    });
  });
</script> --}}
<script>
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const mySidebar = document.getElementById('sidenav-main');
    const mainContent = document.querySelector('.main-content');
    const toggleSidebarIcon = document.getElementById('toggleSidebarIcon');
    const toggleSidebarText = document.getElementById('toggleSidebarText');

    // Saat load
    if (localStorage.getItem('sidebarMini') === 'true') {
        mySidebar.classList.add('sidenav-mini');
        toggleSidebarIcon.classList.remove('fa-expand');
        toggleSidebarIcon.classList.add('fa-compress');
        toggleSidebarText.textContent = 'Normal View';
    }

    sidebarToggleBtn.addEventListener('click', () => {
        const isMini = mySidebar.classList.contains('sidenav-mini');

        if (isMini) {
            // Expand
            mySidebar.classList.remove('sidenav-mini');
            mySidebar.classList.add('sidebar-animating');

            const onTransitionEnd = (e) => {
                if (e.propertyName === 'width') {
                    mySidebar.classList.remove('sidebar-animating');
                    mySidebar.removeEventListener('transitionend', onTransitionEnd);
                }
            };
            mySidebar.addEventListener('transitionend', onTransitionEnd);

            toggleSidebarIcon.classList.remove('fa-compress');
            toggleSidebarIcon.classList.add('fa-expand');
            toggleSidebarText.textContent = 'Full Screen';

            localStorage.setItem('sidebarMini', false);
        } else {
            // Collapse
            mySidebar.classList.add('sidebar-animating');
            setTimeout(() => {
                mySidebar.classList.add('sidenav-mini');
                mySidebar.classList.remove('sidebar-animating');

                toggleSidebarIcon.classList.remove('fa-expand');
                toggleSidebarIcon.classList.add('fa-compress');
                toggleSidebarText.textContent = 'Normal View';

                localStorage.setItem('sidebarMini', true);
            }, 10);
        }
    });
</script>


<style>
    /* Sidebar dan konten transisi */
    #sidenav-main {
        width: 17.125rem;
        transition: width 0.3s ease;
    }

    .main-content {
        transition: margin-left 0.3s ease;
        margin-left: 17.125rem;
    }

    /* Jika sidebar mini */
    #sidenav-main.sidenav-mini {
        width: 5px !important;
        padding: 1px !important;
    }

    .sidenav.sidenav-mini+.main-content {
        margin-left: 10px !important;
    }

    .sidenav.sidenav-mini .navbar-brand {
        padding: 0.75rem 1rem;
        justify-content: center;
    }

    /* Logo tetap tampil */
    .navbar-brand-img {
        max-height: 40px;
        transition: all 0.3s ease;
    }

    /* Fade animation: elemen teks akan disembunyikan pakai opacity dan transform */
    #sidenav-main .nav-link-text,
    #sidenav-main .navbar-brand span,
    #sidenav-main .collapse,
    #sidenav-main .sidenav-footer,
    #sidenav-main .docs-info,
    #sidenav-main .card-body .avatar-wrapper {
        transition: opacity 0.3s ease, transform 0.3s ease;
        transform-origin: left;
    }

    /* Saat mini atau sedang transisi */
    #sidenav-main.sidenav-mini .nav-link-text,
    #sidenav-main.sidenav-mini .navbar-brand span,
    #sidenav-main.sidenav-mini .collapse,
    #sidenav-main.sidenav-mini .sidenav-footer,
    #sidenav-main.sidenav-mini .docs-info,
    #sidenav-main.sidenav-mini .card-body .avatar-wrapper,
    #sidenav-main.sidebar-animating .nav-link-text,
    #sidenav-main.sidebar-animating .navbar-brand span,
    #sidenav-main.sidebar-animating .collapse,
    #sidenav-main.sidebar-animating .sidenav-footer,
    #sidenav-main.sidebar-animating .docs-info,
    #sidenav-main.sidebar-animating .card-body .avatar-wrapper {
        opacity: 0;
        transform: translateX(-10px);
        pointer-events: none;
    }

    /* Saat sidebar aktif penuh (bukan mini & bukan animating), semua elemen muncul */
    #sidenav-main:not(.sidenav-mini):not(.sidebar-animating) .nav-link-text,
    #sidenav-main:not(.sidenav-mini):not(.sidebar-animating) .navbar-brand span,
    #sidenav-main:not(.sidenav-mini):not(.sidebar-animating) .collapse,
    #sidenav-main:not(.sidenav-mini):not(.sidebar-animating) .sidenav-footer,
    #sidenav-main:not(.sidenav-mini):not(.sidebar-animating) .docs-info,
    #sidenav-main:not(.sidenav-mini):not(.sidebar-animating) .card-body .avatar-wrapper {
        opacity: 1;
        transform: translateX(0);
        pointer-events: auto;
    }

    /* Icon tetap simetris saat mini */
    #sidenav-main.sidenav-mini .icon-shape {
        margin: 0 auto;
        justify-content: center;
    }

    #sidenav-main .nav-link .icon-shape {
        transition: all 0.3s ease;
    }
</style>

<style>
    .navbar-vertical {
        background: linear-gradient(180deg, #ffffff, #f9f9fb);
        border-right: 1px solid #e0dce4;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
        /* bayangan tegas tapi tidak menyebar luas */
        transition: all 0.3s ease-in-out;
        padding-top: 1rem;
    }

    .navbar-vertical:hover {
        background: linear-gradient(180deg, #ffffff, #f2f2f7);
        box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
    }

    .navbar-vertical a {
        color: #5a2b66;
        padding: 10px 20px;
        display: block;
        text-decoration: none;
        border-radius: 10px;
        transition: background 0.2s, color 0.2s;
        font-weight: 500;
    }

    .navbar-vertical a:hover {
        background-color: rgba(240, 240, 255, 0.8);
        color: #3d1e44;
    }

    .fixed-plugin {
        display: none !important
    }

</style>
{{-- TODO : style vertical menu --}}
