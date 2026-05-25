@extends('layouts.user_type.auth')
@section('title', 'DN System-Dashboard')

@section('css')
<style>
    /* ── Banner ─────────────────────────────────────── */
    .db-banner {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 70%, #533483 100%);
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        padding: 2rem 2rem 1.8rem;
        color: #fff;
    }
    .db-banner::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        background: rgba(255,255,255,.06);
        border-radius: 50%;
    }
    .db-banner::after {
        content: '';
        position: absolute;
        bottom: -40px; left: 30%;
        width: 160px; height: 160px;
        background: rgba(255,255,255,.04);
        border-radius: 50%;
    }
    .db-banner .badge-live {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 20px;
        padding: 4px 12px;
        font-size: .7rem; font-weight: 600;
        letter-spacing: .5px;
    }
    .db-banner .badge-live span.dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #4ade80;
        animation: pulse-dot 1.5s infinite;
    }
    @keyframes pulse-dot {
        0%,100% { opacity: 1; } 50% { opacity: .3; }
    }
    .db-banner h3 { font-size: 1.6rem; font-weight: 700; margin: .6rem 0 .3rem; }
    .db-banner p  { font-size: .85rem; opacity: .8; margin: 0; }
    .db-banner .clock-box {
        background: rgba(255,255,255,.12);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 12px;
        padding: .8rem 1.2rem;
        text-align: center;
    }
    .db-banner .clock-box #db-time {
        font-size: 1.8rem; font-weight: 700; letter-spacing: 2px;
        font-variant-numeric: tabular-nums;
    }
    .db-banner .clock-box #db-date {
        font-size: .75rem; opacity: .75; margin-top: 2px;
    }

    /* ── Stat cards ─────────────────────────────────── */
    .stat-card {
        border-radius: 14px;
        border: none;
        padding: 1.2rem 1.4rem;
        position: relative;
        overflow: hidden;
        transition: transform .2s, box-shadow .2s;
        cursor: default;
        height: 100%;
    }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,.15)!important; }
    .stat-card .stat-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; color: #fff;
        background: rgba(255,255,255,.25);
    }
    .stat-card .stat-label  { font-size: .72rem; font-weight: 600; letter-spacing: .5px; opacity: .85; margin-bottom: 2px; }
    .stat-card .stat-value  { font-size: 1.45rem; font-weight: 700; }
    .stat-card .stat-change { font-size: .72rem; margin-top: 2px; }
    .stat-card.s1 { background: linear-gradient(135deg,#667eea,#764ba2); color:#fff; }
    .stat-card.s2 { background: linear-gradient(135deg,#f093fb,#f5576c); color:#fff; }
    .stat-card.s3 { background: linear-gradient(135deg,#4facfe,#00f2fe); color:#fff; }
    .stat-card.s4 { background: linear-gradient(135deg,#43e97b,#38f9d7); color:#fff; }

    /* ── Quick-access cards ──────────────────────────── */
    .qa-card {
        border-radius: 14px;
        border: 1px solid #e9ecef;
        padding: 1.3rem 1rem;
        text-align: center;
        text-decoration: none;
        display: block;
        transition: transform .2s, box-shadow .2s, border-color .2s;
        background: #fff;
        color: #344767;
    }
    .qa-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 32px rgba(0,0,0,.12)!important;
        border-color: transparent;
        color: #344767;
        text-decoration: none;
    }
    .qa-card .qa-icon {
        width: 52px; height: 52px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; color: #fff;
        margin: 0 auto .75rem;
        transition: transform .2s;
    }
    .qa-card:hover .qa-icon { transform: scale(1.1) rotate(-5deg); }
    .qa-card .qa-title { font-size: .8rem; font-weight: 700; margin-bottom: 2px; }
    .qa-card .qa-sub   { font-size: .68rem; color: #8392a5; }

    /* Quick Access: force 2x4 only on 608x686 viewport */
    @media (width: 608px) and (height: 686px) {
        .qa-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .qa-grid > [class*="col-"] {
            width: auto !important;
            max-width: none !important;
            flex: initial !important;
            padding-right: 0 !important;
            padding-left: 0 !important;
        }
    }

    /* Fallback when JS class is applied due viewport rounding */
    body.qa-608x686 .qa-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    body.qa-608x686 .qa-grid > [class*="col-"] {
        width: auto !important;
        max-width: none !important;
        flex: initial !important;
        padding-right: 0 !important;
        padding-left: 0 !important;
    }

    /* Android phones (Pixel 7-like): force quick access 2x4 */
    body.qa-android-2x4 .qa-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    body.qa-android-2x4 .qa-grid > [class*="col-"] {
        width: auto !important;
        max-width: none !important;
        flex: initial !important;
        padding-right: 0 !important;
        padding-left: 0 !important;
    }

    /* ── Chart cards ─────────────────────────────────── */
    .chart-card { border-radius: 14px; border: none; }
    .chart-card .chart-inner {
        background: linear-gradient(135deg,#1a1a2e,#0f3460);
        border-radius: 10px; padding: 1rem .5rem;
    }
</style>
@endsection

@section('content')
<div class="row mt-4 g-3">

    {{-- ══════════════ BANNER ══════════════ --}}
    <div class="col-12">
        <div class="db-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="badge-live mb-2">
                        <span class="dot"></span> LIVE SYSTEM
                    </div>
                    <h3>Selamat Datang, {{ Auth::user()->name }} 👋</h3>
                    <p>DASHBOARD REPORT &mdash; Built by HGS IT Division. Stay productive, stay positive.</p>
                </div>
                <div class="col-md-4 mt-3 mt-md-0 text-center">
                    <div class="clock-box d-inline-block">
                        <div id="db-time">00:00:00</div>
                        <div id="db-date"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════ STAT CARDS ══════════════ --}}
    <div class="col-6 col-sm-6 col-md-3">
        <div class="card stat-card s1 shadow">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">TOTAL TRANSAKSI</div>
                    <div class="stat-value" id="stat-total-trx">—</div>
                    <div class="stat-change" id="stat-trx-pct"></div>
                </div>
                <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3">
        <div class="card stat-card s2 shadow">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">TOTAL NILAI</div>
                    <div class="stat-value" id="stat-total-val">—</div>
                    <div class="stat-change" id="stat-val-pct"></div>
                </div>
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3">
        <div class="card stat-card s3 shadow">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">BULAN BERJALAN</div>
                    <div class="stat-value" id="stat-cur-month">—</div>
                    <div class="stat-change" style="opacity:.85;">transaksi bulan ini</div>
                </div>
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3">
        <div class="card stat-card s4 shadow">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">MENU AKTIF</div>
                    <div class="stat-value">8</div>
                    <div class="stat-change" style="opacity:.85;">fitur tersedia</div>
                </div>
                <div class="stat-icon"><i class="fas fa-th-large"></i></div>
            </div>
        </div>
    </div>

    {{-- ══════════════ QUICK ACCESS ══════════════ --}}
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-body pb-2">
                <h6 class="text-uppercase text-xs font-weight-bolder text-secondary mb-3">
                    <i class="fas fa-bolt me-1 text-warning"></i> Akses Cepat
                </h6>
                <div class="row g-3 qa-grid">
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="/pod/summary" class="qa-card shadow-sm">
                            <div class="qa-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div class="qa-title">POD Summary</div>
                            <div class="qa-sub">Proof of Delivery</div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="/pod/detail" class="qa-card shadow-sm">
                            <div class="qa-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                                <i class="fas fa-search-location"></i>
                            </div>
                            <div class="qa-title">POD Report</div>
                            <div class="qa-sub">Detail per transaksi</div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="/lastmile" class="qa-card shadow-sm">
                            <div class="qa-icon" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="qa-title">Report Last Mile</div>
                            <div class="qa-sub">Laporan pengiriman</div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="/dispatch-track" class="qa-card shadow-sm">
                            <div class="qa-icon" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
                                <i class="fas fa-route"></i>
                            </div>
                            <div class="qa-title">Dispatch Track</div>
                            <div class="qa-sub">Tracking dispatch</div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="/gudang/rekap-stock-rack" class="qa-card shadow-sm">
                            <div class="qa-icon" style="background:linear-gradient(135deg,#fa709a,#fee140);">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="qa-title">Rekap Stock</div>
                            <div class="qa-sub">Stock per rack</div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="/gudang/track-in-out" class="qa-card shadow-sm">
                            <div class="qa-icon" style="background:linear-gradient(135deg,#a18cd1,#fbc2eb);">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="qa-title">Track In/Out</div>
                            <div class="qa-sub">Mutasi gudang</div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="/gudang/price-list" class="qa-card shadow-sm">
                            <div class="qa-icon" style="background:linear-gradient(135deg,#fccb90,#d57eeb);">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="qa-title">Price List</div>
                            <div class="qa-sub">Daftar harga</div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="/gudang/kartu-stock" class="qa-card shadow-sm">
                            <div class="qa-icon" style="background:linear-gradient(135deg,#30cfd0,#330867);">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="qa-title">Kartu Stock</div>
                            <div class="qa-sub">Mutasi per SKU</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════ CHARTS ══════════════ --}}
    <div class="col-lg-5 mb-lg-0 mb-3">
        <div class="card chart-card shadow h-100">
            <div class="card-body p-3">
                <div class="chart-inner">
                    <div class="chart">
                        <canvas id="chart-bars" class="chart-canvas" height="220"></canvas>
                    </div>
                </div>
                <div id="persen-transaksi" class="ms-1 mt-2"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card chart-card shadow h-100">
            <div class="card-body p-3">
                <div class="chart">
                    <canvas id="chart-line" class="chart-canvas" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
@include('harus_ada')
@push('dashboard')
    @if (session('success'))
        <script>
            new Noty({
                type: 'success',
                layout: 'topRight',
                text: "{{ session('success') }}",
                timeout: 3000,
                theme: 'mint'
            }).show();
        </script>
    @endif

    <script>
        // ── Live clock ──────────────────────────────────
        (function() {
            const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            function updateClock() {
                const now = new Date();
                const h = String(now.getHours()).padStart(2,'0');
                const m = String(now.getMinutes()).padStart(2,'0');
                const s = String(now.getSeconds()).padStart(2,'0');
                const el = document.getElementById('db-time');
                const ed = document.getElementById('db-date');
                if (el) el.textContent = h + ':' + m + ':' + s;
                if (ed) ed.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
            }
            updateClock();
            setInterval(updateClock, 1000);
        })();

        window.onload = function() {

            var ctx = document.getElementById("chart-bars").getContext("2d");

            var ctx2 = document.getElementById("chart-line").getContext("2d");

            var gradientStroke1 = ctx2.createLinearGradient(0, 230, 0, 50);

            gradientStroke1.addColorStop(1, 'rgba(203,12,159,0.2)');
            gradientStroke1.addColorStop(0.2, 'rgba(72,72,176,0.0)');
            gradientStroke1.addColorStop(0, 'rgba(203,12,159,0)'); //purple colors

            var gradientStroke2 = ctx2.createLinearGradient(0, 230, 0, 50);

            gradientStroke2.addColorStop(1, 'rgba(20,23,39,0.2)');
            gradientStroke2.addColorStop(0.2, 'rgba(72,72,176,0.0)');
            gradientStroke2.addColorStop(0, 'rgba(20,23,39,0)'); //purple colors


            get_client();

            function get_client() {
                $.ajax({
                    url: '/data_for_chart_1',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let transaksi = data.tol_transaction;

                        // Populate stat cards
                        const totalTrx = transaksi.reduce((a,b) => a + b, 0);
                        const totalVal = data.total.reduce((a,b) => a + parseFloat(b), 0);
                        const curMonthTrx = transaksi[transaksi.length - 1];

                        function fmtNum(n) {
                            if (n >= 1e9) return (n/1e9).toFixed(1)+' M';
                            if (n >= 1e6) return (n/1e6).toFixed(1)+' Jt';
                            if (n >= 1e3) return (n/1e3).toFixed(1)+' rb';
                            return n;
                        }
                        const elTrx = document.getElementById('stat-total-trx');
                        const elVal = document.getElementById('stat-total-val');
                        const elCur = document.getElementById('stat-cur-month');
                        if (elTrx) elTrx.textContent = fmtNum(totalTrx);
                        if (elVal) elVal.textContent = 'Rp ' + fmtNum(totalVal);
                        if (elCur) elCur.textContent = fmtNum(curMonthTrx);

                        if (transaksi.length >= 2) {
                            let bulanIni = transaksi[transaksi.length - 1];
                            let bulanLalu = transaksi[transaksi.length - 2];
                            let persen = bulanLalu !== 0 ? ((bulanIni - bulanLalu) / bulanLalu) * 100 : 0;
                            persen = persen.toFixed(2);
                            let sign = persen >= 0 ? '+' : '';
                            let clr = persen >= 0 ? 'text-success' : 'text-danger';
                            let ico = persen >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                            document.getElementById('persen-transaksi').innerHTML =
                                `<span class="text-sm ${clr}"><i class="fas ${ico} me-1"></i>${sign}${persen}% vs bulan lalu</span>`;
                            const trxPct = document.getElementById('stat-trx-pct');
                            if (trxPct) trxPct.innerHTML = `<i class="fas ${ico}"></i> ${sign}${persen}%`;
                        }

                        // Value trend for stat card
                        const vals = data.total;
                        if (vals.length >= 2) {
                            const vCur = parseFloat(vals[vals.length-1]);
                            const vPrv = parseFloat(vals[vals.length-2]);
                            if (vPrv !== 0) {
                                const vPct = ((vCur - vPrv) / vPrv * 100).toFixed(2);
                                const vSign = vPct >= 0 ? '+' : '';
                                const vClr = vPct >= 0 ? 'text-success' : 'text-danger';
                                const elVP = document.getElementById('stat-val-pct');
                                if (elVP) elVP.innerHTML = `<i class="fas ${vPct>=0?'fa-arrow-up':'fa-arrow-down'}"></i> ${vSign}${vPct}%`;
                            }
                        }
                        const totalArray = data.total;

                        const length = totalArray.length;
                        const prev = parseFloat(totalArray[length - 2]);
                        const current = parseFloat(totalArray[length - 1]);

                        change = ((current - prev) / prev) * 100;
                        isUp = change >= 0;
                        percentage = Math.abs(change).toFixed(2); // bulatkan jadi 2 desimal

                        const icon = isUp ?
                            '<i class="fa fa-arrow-up text-success"></i>' :
                            '<i class="fa fa-arrow-down text-danger"></i>';

                        const text = isUp ? 'more' : 'less';
                        const html = `${icon} <span class="font-weight-bold">${percentage}% ${text}</span>`;

                        $('#chart-line-change').html(html)
                        new Chart(ctx, {
                            type: "bar",
                            data: {
                                labels: data.month,
                                datasets: [{
                                    label: "DN Tagih",
                                    tension: 0.4,
                                    borderWidth: 0,
                                    borderRadius: 4,
                                    borderSkipped: false,
                                    backgroundColor: "#fff",
                                    data: data.tol_transaction,
                                    maxBarThickness: 6
                                }, ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false,
                                    }
                                },
                                interaction: {
                                    intersect: false,
                                    mode: 'index',
                                },
                                scales: {
                                    y: {
                                        grid: {
                                            drawBorder: false,
                                            display: false,
                                            drawOnChartArea: false,
                                            drawTicks: false,
                                        },
                                        ticks: {
                                            suggestedMin: 0,
                                            suggestedMax: 500,
                                            beginAtZero: true,
                                            padding: 15,
                                            font: {
                                                size: 14,
                                                family: "Open Sans",
                                                style: 'normal',
                                                lineHeight: 2
                                            },
                                            color: "#fff"
                                        },
                                    },
                                    x: {
                                        grid: {
                                            drawBorder: false,
                                            display: false,
                                            drawOnChartArea: false,
                                            drawTicks: false
                                        },
                                        ticks: {
                                            display: false
                                        },
                                    },
                                },
                            },
                        });

                        new Chart(ctx2, {
                            type: "line",
                            data: {
                                labels: data.month,
                                datasets: [{
                                    label: "Total",
                                    tension: 0.4,
                                    borderWidth: 0,
                                    pointRadius: 0,
                                    borderColor: "#cb0c9f",
                                    borderWidth: 3,
                                    backgroundColor: gradientStroke1,
                                    fill: true,
                                    data: data.total,
                                    maxBarThickness: 6,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false,
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let value = context.parsed.y;
                                                let formatted = new Intl.NumberFormat('id-ID', {
                                                    style: 'currency',
                                                    currency: 'IDR',
                                                    minimumFractionDigits: 0,
                                                    maximumFractionDigits: 2
                                                }).format(value);
                                                return formatted;
                                            }
                                        }
                                    }
                                },
                                interaction: {
                                    intersect: false,
                                    mode: 'index',
                                },
                                scales: {
                                    y: {
                                        grid: {
                                            drawBorder: false,
                                            display: true,
                                            drawOnChartArea: true,
                                            drawTicks: false,
                                            borderDash: [5, 5]
                                        },
                                        ticks: {
                                            display: true,
                                            padding: 10,
                                            color: '#b2b9bf',
                                            font: {
                                                size: 11,
                                                family: "Open Sans",
                                                style: 'normal',
                                                lineHeight: 2
                                            },
                                            // âœ… Tambahkan formatter di sini
                                            callback: function(value) {
                                                const isNegative = value < 0;
                                                const absValue = Math.abs(value);
                                                let formatted;

                                                if (absValue >= 1000000000) {
                                                    formatted = (absValue / 1000000000).toFixed(1) + ' M';
                                                } else if (absValue >= 1000000) {
                                                    formatted = (absValue / 1000000).toFixed(1) + ' Jt';
                                                } else if (absValue >= 1000) {
                                                    formatted = (absValue / 1000).toFixed(1) + ' rb';
                                                } else {
                                                    formatted = absValue;
                                                }

                                                return isNegative ? '-' + formatted : formatted;
                                            }
                                        }
                                    },
                                    x: {
                                        grid: {
                                            drawBorder: false,
                                            display: false,
                                            drawOnChartArea: false,
                                            drawTicks: false,
                                            borderDash: [5, 5]
                                        },
                                        ticks: {
                                            display: true,
                                            color: '#b2b9bf',
                                            padding: 20,
                                            font: {
                                                size: 11,
                                                family: "Open Sans",
                                                style: 'normal',
                                                lineHeight: 2
                                            },
                                        }
                                    },
                                },
                            },
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching chart data:', error);
                    }
                });
            }
        }
    </script>
@endpush

