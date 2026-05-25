<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-white" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="align-items-center d-flex m-0 navbar-brand text-wrap" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/dashboard.png') }}" class="navbar-brand-img h-100" alt="...">
            <span class="ms-3 font-weight-bold">Report Dashboard
            </span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main">
        @php
            $menuPath = resource_path('json/menu.json');
            $menuItems = json_decode(file_get_contents($menuPath), true);
        @endphp
        <ul class="navbar-nav">
            @foreach ($menuItems as $item)
                {{-- SECTION HEADER --}}
                @if (isset($item['type']) && $item['type'] === 'section')
                    <li class="nav-item mt-2 menu-item">
                        <hr class="horizontal dark my-1 mx-3">
                    </li>

                    {{-- DROPDOWN MENU --}}
                @elseif (isset($item['submenu']))
                    @php
                        $isOpen = false;
                        foreach ($item['submenu'] as $child) {
                            if (Request::is(ltrim($child['url'], '/'))) {
                                $isOpen = true;
                                break;
                            }
                        }
                    @endphp
                    <li class="nav-item pb-2 menu-item">
                        <a class="nav-link {{ $isOpen ? 'active2' : 'collapsed' }}" data-bs-toggle="collapse" href="#menu-{{ Str::slug($item['title']) }}" role="button" aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="menu-{{ Str::slug($item['title']) }}">
                            <div class="icon icon-shape {{ $isOpen ? 'active2' : '' }} icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="{{ $item['icon'] ?? 'fas fa-circle' }} {{ $isOpen ? 'text-white' : 'text-dark' }}" style="font-size: 1rem;"></i>
                            </div>
                            <span class="nav-link-text ms-1 {{ $isOpen ? 'fw-bold' : '' }}">{{ $item['title'] }}</span>
                        </a>
                        <div class="collapse {{ $isOpen ? 'show' : '' }}" id="menu-{{ Str::slug($item['title']) }}">
                            <ul class="nav flex-column ps-5">
                                @foreach ($item['submenu'] as $child)
                                    @if (($child['title'] ?? '') === 'POD Summary')
                                        @php
                                            $reportDb = session('report_db', 'hgs');
                                            $btnBase = 'font-size:.82rem;padding:.3rem .9rem;line-height:1.2;min-width:62px;font-weight:700;border-radius:8px;';
                                            $hgsStyle = $reportDb === 'hgs'
                                                ? 'background:#e6f0ff;border:1px solid #93c5fd;color:#1e3a8a;'
                                                : 'background:#ffffff;border:1px solid #d1d5db;color:#4b5563;';
                                            $tguStyle = $reportDb === 'tgu'
                                                ? 'background:#fff7d6;border:1px solid #facc15;color:#92400e;'
                                                : 'background:#ffffff;border:1px solid #d1d5db;color:#4b5563;';
                                        @endphp
                                        <li class="nav-item mb-2">
                                            <div class="d-flex align-items-center gap-2" style="padding:.15rem .25rem;">
                                                <span style="font-size:.74rem;font-weight:700;color:#4b5563;min-width:24px;">DB</span>
                                                <a href="{{ url('/set-report-db/hgs') }}"
                                                   class="btn"
                                                   style="{{ $btnBase . $hgsStyle }}">HGS</a>
                                                <a href="{{ url('/set-report-db/tgu') }}"
                                                   class="btn"
                                                   style="{{ $btnBase . $tguStyle }}">TGU</a>
                                            </div>
                                        </li>
                                    @endif
                                    <li class="nav-item">
                                        <a class="nav-link {{ Request::is(ltrim($child['url'], '/')) ? 'active' : '' }}" href="{{ url($child['url']) }}">
                                            {{ $child['title'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>

                    {{-- SINGLE MENU --}}
                @else
                    @php
                        $hasRole = true;
                        if (isset($item['roles'])) {
                            $hasRole = auth()->check() && in_array(auth()->user()->role, $item['roles']);
                        }
                    @endphp

                    @if ($hasRole)
                        <li class="nav-item pb-2 menu-item">
                            <a class="nav-link {{ Request::is(ltrim($item['url'], '/')) ? 'active' : '' }}" href="{{ url($item['url']) }}">
                                <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="{{ $item['icon'] ?? 'fas fa-circle' }} {{ Request::is(ltrim($item['url'], '/')) ? 'text-white' : 'text-dark' }}" style="font-size: 1rem;"></i>
                                </div>
                                <span class="nav-link-text ms-1">{{ $item['title'] }}</span>
                            </a>
                        </li>
                    @endif
                @endif
            @endforeach
        </ul>
    </div>
    <style>
        #sidenav-main {
            position: fixed;
            overflow: hidden;
            background-color: #ffffff;
            background-image:
                radial-gradient(circle at 1px 1px, rgba(59, 130, 246, 0.16) 1.2px, transparent 0),
                linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(245, 250, 255, 0.98));
            background-size: 16px 16px, 100% 100%;
        }

        #sidenav-main::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 12% 10%, rgba(59, 130, 246, 0.12) 0, rgba(59, 130, 246, 0) 22%),
                radial-gradient(circle at 85% 20%, rgba(16, 185, 129, 0.10) 0, rgba(16, 185, 129, 0) 20%);
            z-index: 0;
        }

        #sidenav-main .navbar-collapse,
        #sidenav-main .sidenav-footer {
            position: relative;
            z-index: 1;
        }

        .nav-link {
            white-space: normal !important;
            word-break: break-word;
            position: relative;
            padding-left: 0.75rem;
        }

        .navbar-vertical.bg-white .navbar-nav .menu-item > .nav-link::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: linear-gradient(135deg, #94a3b8, #64748b);
            box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.18);
            position: absolute;
            left: -0.25rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .navbar-vertical.bg-white .navbar-nav .menu-item > .nav-link.active::before,
        .navbar-vertical.bg-white .navbar-nav .menu-item > .nav-link.active2::before {
            background: linear-gradient(135deg, #38bdf8, #6366f1);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.22);
        }

        .icon-shape.active2 {
            background-color: red;
        }

        .navbar-vertical.bg-white .navbar-nav .nav-link .icon.icon-shape.active2 {
            background: linear-gradient(135deg, #7b61ff, #3ebdf0, #4be1c4);
            color: white !important;
        }

        .navbar-vertical.bg-white .navbar-nav .nav-link .icon.icon-shape {
            color: darkblue !important;
        }

        .modal-xl {
            max-width: 80%;
        }
    </style>
    <style>
        #sidenavCard {
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        #sidenavCard:hover {
            transform: scale(1.01);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        #sidenavCard .card-body {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(7px);
            -webkit-backdrop-filter: blur(7px);
            border-radius: 16px;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        #sidenavCard .btn {
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.1);
        }

        #sidenavCard .btn:hover {
            font-weight: 600 !important;
        }

        #sidenavCard h6,
        #sidenavCard p {
            margin-bottom: 0.5rem;
        }

        .avatar-wrapper {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            margin: 0 auto 1rem auto;
        }

        .avatar-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .fallback-icon {
            font-size: 2rem;
            color: #333;
        }
    </style>

    <div class="sidenav-footer mx-3 mb-3">
        <div class="card card-background card-background-mask-secondary" id="sidenavCard">
            @php
                $username = Auth::user()->username;
                $imageJpg = public_path("storage/assets/img/$username.jpg");
                $imagePng = public_path("storage/assets/img/$username.png");

                if (file_exists($imageJpg)) {
                    $bgImage = asset("storage/assets/img/$username.jpg");
                } elseif (file_exists($imagePng)) {
                    $bgImage = asset("storage/assets/img/$username.png");
                } else {
                    $bgImage = asset('assets/img/curved-images/white-curved.jpeg');
                }
            @endphp

            <div class="full-background" style="background-image: url('{{ $bgImage }}'); background-size: cover; background-position: center;"></div>

            <div class="card-body text-center p-3 w-100">
                <div class="avatar-wrapper">
                    @php
                        if (file_exists($imageJpg)) {
                            $userImage = asset("storage/assets/img/$username.jpg");
                        } elseif (file_exists($imagePng)) {
                            $userImage = asset("storage/assets/img/$username.png");
                        } else {
                            $userImage = null;
                        }
                    @endphp

                    @if ($userImage)
                        <img src="{{ $userImage }}" alt="user-avatar">
                    @else
                        <div class="d-flex align-items-center justify-content-center w-100 h-100">
                            <i class="ni ni-single-02 fallback-icon"></i>
                        </div>
                    @endif
                </div>

                <div class="docs-info">
                    <h6 class="text-white up mb-0">{{ Auth::user()->name }}</h6>
                    <p class="text-xs font-weight-bold">{{ Auth::user()->sub_divisi }}</p>
                    <a href="/profile" class="btn btn-white btn-sm w-100 mb-0">Profile</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Lokasi (opsional, masih disembunyikan) --}}
    {{--
                <p class="text-xs font-weight-bold d-flex align-items-center bg-white text-primary p-2 rounded-3">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <span id="location"></span>
                </p>
                --}}
    {{-- <script>
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var latitude = position.coords.latitude;
                var longitude = position.coords.longitude;

                // Menggunakan API untuk mencari nama lokasi berdasarkan koordinat
                fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=en`)
                    .then(response => response.json())
                    .then(data => {
                        var location = data.locality || 'Unknown Location';
                        if (location.toLowerCase() !== 'jakarta') {
                            document.getElementById('location').textContent = location;
                        } else {
                            document.getElementById('location').textContent = 'Not available';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching location:', error);
                        document.getElementById('location').textContent = 'Location not found';
                    });
            });
        } else {
            document.getElementById('location').textContent = 'Geolocation is not supported by this browser.';
        }
    </script> --}}
</aside>
