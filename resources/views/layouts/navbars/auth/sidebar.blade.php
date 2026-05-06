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
                        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">{{ $item['title'] }}</h6>
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
        .nav-link {
            white-space: normal !important;
            word-break: break-word;
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
