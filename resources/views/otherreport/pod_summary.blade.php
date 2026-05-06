@extends('layouts.user_type.auth')

@section('title', 'POD Summary')

@section('css')
<style>
    .pod-stat-card{
        background:#fff;border-radius:12px;padding:20px 22px;
        box-shadow:0 2px 12px rgba(0,0,0,.06);height:100%;
        border-left:4px solid #5e72e4;
    }
    .pod-stat-card.green  { border-left-color:#2dce89; }
    .pod-stat-card.red    { border-left-color:#f5365c; }
    .pod-stat-card.orange { border-left-color:#fb6340; }
    .pod-stat-card.purple { border-left-color:#8965e0; }
    .pod-stat-label{
        font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;
        color:#8898aa;font-weight:600;margin-bottom:6px;
    }
    .pod-stat-value{
        font-size:1.5rem;font-weight:700;color:#32325d;line-height:1.2;
        word-break:break-all;
    }
    .pod-stat-sub{ font-size:.75rem;color:#8898aa;margin-top:4px; }
    .pod-filter-card{
        background:#fff;border-radius:12px;padding:18px 22px;
        box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:20px;
    }
</style>
@endsection

@section('content')
@php
    $fmt = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp

<div class="container-fluid py-4">
    <div class="row mt-2 g-3">
        {{-- Kolom kiri: Dispatch Status (kecil) di atas, Summary Dispatch di bawah --}}
        <div class="col-md-6 col-lg-5 col-xl-4 d-flex flex-column gap-3">

            {{-- Dispatch Status (filter sendiri) --}}
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                        <h6 class="mb-0">Dispatch Status</h6>
                        <form method="GET" action="{{ route('pod.summary.index') }}" class="m-0">
                            {{-- Pertahankan filter lain saat submit --}}
                            <input type="hidden" name="range" value="{{ $range }}">
                            <input type="hidden" name="date_from2"
                                   value="{{ \Carbon\Carbon::parse($dateFrom2)->format('Y-m-d') }}">
                            <input type="hidden" name="date_to2"
                                   value="{{ \Carbon\Carbon::parse($dateTo2)->format('Y-m-d') }}">
                            <input type="hidden" name="date_from3"
                                   value="{{ \Carbon\Carbon::parse($dateFrom3)->format('Y-m-d') }}">
                            <input type="hidden" name="date_to3"
                                   value="{{ \Carbon\Carbon::parse($dateTo3)->format('Y-m-d') }}">

                            <select name="range_status" class="form-select form-select-sm"
                                    style="width:auto;font-size:.75rem;padding:.2rem 1.5rem .2rem .5rem;"
                                    onchange="this.form.submit()">
                                <option value="today"      @selected($rangeStatus === 'today')>Hari Ini</option>
                                <option value="last3"      @selected($rangeStatus === 'last3')>3 Hari Terakhir</option>
                                <option value="last7"      @selected($rangeStatus === 'last7')>7 Hari Terakhir</option>
                                <option value="this_month" @selected($rangeStatus === 'this_month')>Bulan Ini</option>
                                <option value="this_year"  @selected($rangeStatus === 'this_year')>Tahun Ini</option>
                            </select>
                        </form>
                    </div>
                    @php
                        $statusItems = [
                            ['label' => 'OPEN',     'value' => $statusMap['open'],     'color' => '#11cdef'],
                            ['label' => 'CLOSE',    'value' => $statusMap['close'],    'color' => '#2dce89'],
                            ['label' => 'PLANNING', 'value' => $statusMap['planning'], 'color' => '#fb6340'],
                        ];
                    @endphp
                    <div class="row g-2 text-center">
                        @foreach ($statusItems as $s)
                            <div class="col-4">
                                <div style="border:1px solid #e9ecef;border-radius:8px;padding:8px 4px;">
                                    <div style="font-size:.65rem;font-weight:600;letter-spacing:.04em;color:{{ $s['color'] }};">
                                        {{ $s['label'] }}
                                    </div>
                                    <div style="font-size:1.1rem;font-weight:700;color:#32325d;line-height:1.2;">
                                        {{ number_format($s['value'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-2">
                        <span class="text-xs text-muted">
                            Total: <strong>{{ number_format($statusMap['total'], 0, ',', '.') }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Chart 1: Summary Dispatch --}}
            <div class="card flex-grow-1">
                <div class="card-header pb-0">
                    <h6 class="mb-1">Total Invoice</h6>
                    <p class="text-xs text-muted mb-2">
                        Periode:
                        <strong>{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</strong>
                        s/d
                        <strong>{{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</strong>
                    </p>

                    <form method="GET" action="{{ route('pod.summary.index') }}"
                          class="row g-2 align-items-end mb-2">
                        {{-- Pertahankan filter chart 2 & 3 + status saat submit chart 1 --}}
                        <input type="hidden" name="range_status" value="{{ $rangeStatus }}">
                        <input type="hidden" name="date_from2"
                               value="{{ \Carbon\Carbon::parse($dateFrom2)->format('Y-m-d') }}">
                        <input type="hidden" name="date_to2"
                               value="{{ \Carbon\Carbon::parse($dateTo2)->format('Y-m-d') }}">
                        <input type="hidden" name="date_from3"
                               value="{{ \Carbon\Carbon::parse($dateFrom3)->format('Y-m-d') }}">
                        <input type="hidden" name="date_to3"
                               value="{{ \Carbon\Carbon::parse($dateTo3)->format('Y-m-d') }}">

                        <div class="col-12">
                            <label class="form-label small text-muted mb-1">Periode</label>
                            <select name="range" class="form-select form-select-sm"
                                    onchange="this.form.submit()">
                                <option value="today"      @selected($range === 'today')>Hari Ini</option>
                                <option value="last3"      @selected($range === 'last3')>3 Hari Terakhir</option>
                                <option value="last7"      @selected($range === 'last7')>7 Hari Terakhir</option>
                                <option value="this_week"  @selected($range === 'this_week')>Minggu Ini</option>
                                <option value="this_month" @selected($range === 'this_month')>Bulan Ini</option>
                                <option value="this_year"  @selected($range === 'this_year')>Tahun Ini</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm mb-0">
                                <i class="fas fa-filter me-1"></i> Terapkan
                            </button>
                            <a href="{{ route('pod.summary.index', [
                                    'date_from2' => \Carbon\Carbon::parse($dateFrom2)->format('Y-m-d'),
                                    'date_to2'   => \Carbon\Carbon::parse($dateTo2)->format('Y-m-d'),
                               ]) }}"
                               class="btn btn-outline-secondary btn-sm mb-0">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-body pt-1 pb-3">
                    @php
                        $items = [
                            ['label' => 'TOTAL',     'value' => $summary['sum_total'],     'color' => '#5e72e4'],
                            ['label' => 'OPEN',      'value' => $summary['sum_open'],      'color' => '#11cdef'],
                            ['label' => 'DELIVERED', 'value' => $summary['sum_delivered'], 'color' => '#2dce89'],
                            ['label' => 'CANCEL',    'value' => $summary['sum_cancel'],    'color' => '#f5365c'],
                        ];
                        // Total di-exclude dari basis persen (komposisi = open + delivered + cancel)
                        $sumAll = $summary['sum_open'] + $summary['sum_delivered'] + $summary['sum_cancel'];
                    @endphp
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div style="position:relative;height:200px;width:200px;flex:0 0 auto;">
                            <canvas id="podSummaryChart"></canvas>
                        </div>
                        <ul class="list-unstyled mb-0 small" style="min-width:140px;">
                            @foreach ($items as $it)
                                @php
                                    $isTotal = $it['label'] === 'TOTAL';
                                    $pct = (!$isTotal && $sumAll > 0) ? ($it['value'] / $sumAll * 100) : 0;
                                @endphp
                                <li class="d-flex align-items-baseline mb-2">
                                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;
                                                 background:{{ $it['color'] }};margin-right:8px;flex:0 0 auto;"></span>
                                    <div>
                                        <div class="fw-bold" style="color:#32325d;line-height:1.1;">
                                            {{ number_format((float) $it['value'], 0, ',', '.') }}
                                            @unless ($isTotal)
                                                <span class="text-muted fw-normal">
                                                    ({{ number_format($pct, 1, ',', '.') }}%)
                                                </span>
                                            @endunless
                                        </div>
                                        <div class="text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.04em;">
                                            {{ $it['label'] }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Total Dispatch per Tanggal + filter sendiri --}}
        <div class="col-md-6 col-lg-7 col-xl-8">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-1">Total Dispatch</h6>
                    <p class="text-xs text-muted mb-2">
                        Periode:
                        <strong>{{ \Carbon\Carbon::parse($dateFrom2)->format('d/m/Y') }}</strong>
                        s/d
                        <strong>{{ \Carbon\Carbon::parse($dateTo2)->format('d/m/Y') }}</strong>
                    </p>

                    <form method="GET" action="{{ route('pod.summary.index') }}"
                          class="row g-2 align-items-end mb-2">
                        {{-- Pertahankan filter chart 1 (range) & chart 3 + status saat submit chart 2 --}}
                        <input type="hidden" name="range" value="{{ $range }}">
                        <input type="hidden" name="range_status" value="{{ $rangeStatus }}">
                        <input type="hidden" name="date_from3"
                               value="{{ \Carbon\Carbon::parse($dateFrom3)->format('Y-m-d') }}">
                        <input type="hidden" name="date_to3"
                               value="{{ \Carbon\Carbon::parse($dateTo3)->format('Y-m-d') }}">

                        <div class="col-sm-4">
                            <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from2" class="form-control form-control-sm"
                                   value="{{ \Carbon\Carbon::parse($dateFrom2)->format('Y-m-d') }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to2" class="form-control form-control-sm"
                                   value="{{ \Carbon\Carbon::parse($dateTo2)->format('Y-m-d') }}">
                        </div>
                        <div class="col-sm-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm mb-0">
                                <i class="fas fa-filter me-1"></i> Terapkan
                            </button>
                            <a href="{{ route('pod.summary.index', ['range' => $range]) }}"
                               class="btn btn-outline-secondary btn-sm mb-0">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-body pt-1 pb-3">
                    <div style="position:relative;height:340px;">
                        <canvas id="podPerDateChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart 3: Dispatch Value per Tanggal --}}
    <div class="row mt-3 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-1">Dispatch Value</h6>
                    <form method="GET" action="{{ route('pod.summary.index') }}"
                          class="row g-2 align-items-end mb-2">
                        {{-- Pertahankan filter chart 1 & 2 + status saat submit chart 3 --}}
                        <input type="hidden" name="range" value="{{ $range }}">
                        <input type="hidden" name="range_status" value="{{ $rangeStatus }}">
                        <input type="hidden" name="date_from2"
                               value="{{ \Carbon\Carbon::parse($dateFrom2)->format('Y-m-d') }}">
                        <input type="hidden" name="date_to2"
                               value="{{ \Carbon\Carbon::parse($dateTo2)->format('Y-m-d') }}">

                        <div class="col-sm-3 col-md-2">
                            <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from3" class="form-control form-control-sm"
                                   value="{{ \Carbon\Carbon::parse($dateFrom3)->format('Y-m-d') }}">
                        </div>
                        <div class="col-sm-3 col-md-2">
                            <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to3" class="form-control form-control-sm"
                                   value="{{ \Carbon\Carbon::parse($dateTo3)->format('Y-m-d') }}">
                        </div>
                        <div class="col-sm-6 col-md-8 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm mb-0">
                                <i class="fas fa-filter me-1"></i> Terapkan
                            </button>
                            <a href="{{ route('pod.summary.index', [
                                    'range'      => $range,
                                    'date_from2' => \Carbon\Carbon::parse($dateFrom2)->format('Y-m-d'),
                                    'date_to2'   => \Carbon\Carbon::parse($dateTo2)->format('Y-m-d'),
                               ]) }}"
                               class="btn btn-outline-secondary btn-sm mb-0">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-body pt-1 pb-3">
                    <div style="position:relative;height:340px;">
                        <canvas id="podValueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    var ctx = document.getElementById('podSummaryChart');
    if (!ctx) {
        console.warn('[POD Summary] canvas #podSummaryChart not found');
        return;
    }
    if (typeof Chart === 'undefined') {
        console.error('[POD Summary] Chart.js belum dimuat. Cek ../assets/js/plugins/chartjs.min.js');
        return;
    }

    var data = {
        total:     {{ (int) $summary['sum_total']     }},
        open:      {{ (int) $summary['sum_open']      }},
        delivered: {{ (int) $summary['sum_delivered'] }},
        cancel:    {{ (int) $summary['sum_cancel']    }}
    };

    var fmtNumber = function (v) {
        return Number(v || 0).toLocaleString('id-ID');
    };

    // Plugin: tampilkan nilai di atas tiap bar
    var dataLabelsPlugin = {
        id: 'barValueLabels',
        afterDatasetsDraw: function (chart) {
            var ctx2 = chart.ctx;
            chart.data.datasets.forEach(function (dataset, i) {
                var meta = chart.getDatasetMeta(i);
                meta.data.forEach(function (bar, idx) {
                    var value = dataset.data[idx];
                    ctx2.save();
                    ctx2.fillStyle = '#32325d';
                    ctx2.font = 'bold 11px Helvetica, Arial, sans-serif';
                    ctx2.textAlign = 'center';
                    ctx2.textBaseline = 'bottom';
                    ctx2.fillText(fmtNumber(value), bar.x, bar.y - 4);
                    ctx2.restore();
                });
            });
        }
    };

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['OPEN', 'DELIVERED', 'CANCEL'],
            datasets: [{
                label: 'Jumlah',
                data: [data.open, data.delivered, data.cancel],
                backgroundColor: [
                    'rgba(17, 205, 239, 0.85)',
                    'rgba(45, 206, 137, 0.85)',
                    'rgba(245, 54, 92, 0.85)'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (c) {
                            var total = (c.dataset.data || []).reduce(function (a, b) { return a + (Number(b) || 0); }, 0);
                            var val   = Number(c.parsed) || 0;
                            var pct   = total > 0 ? (val / total * 100).toFixed(1) : 0;
                            return c.label + ': ' + fmtNumber(val) + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });

    // ===== Chart 2: Total Dispatch per Tanggal =====
    var ctx2 = document.getElementById('podPerDateChart');
    if (!ctx2) return;

    var perDateLabels = @json($perDateLabels);
    var perDateCounts = @json($perDateCounts);

    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: perDateLabels,
            datasets: [{
                label: 'Total Dispatch',
                data: perDateCounts,
                backgroundColor: 'rgba(94, 114, 228, 0.15)',
                borderColor: 'rgba(94, 114, 228, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(94, 114, 228, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.35,
                fill: true
            }]
        },
        plugins: [dataLabelsPlugin],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 24 } },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (c) { return fmtNumber(c.parsed.y) + ' dispatch'; }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // ===== Chart 3: Dispatch Value per Tanggal =====
    var ctx3 = document.getElementById('podValueChart');
    if (!ctx3) return;

    var valueLabels = @json($valueLabels);
    var valueData   = @json($valueData);

    new Chart(ctx3, {
        type: 'line',
        data: {
            labels: valueLabels,
            datasets: [{
                label: 'Dispatch Value',
                data: valueData,
                backgroundColor: 'rgba(45, 206, 137, 0.15)',
                borderColor: 'rgba(45, 206, 137, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(45, 206, 137, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.35,
                fill: true
            }]
        },
        plugins: [dataLabelsPlugin],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 24 } },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (c) { return fmtNumber(c.parsed.y); }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            if (value >= 1e9) return (value / 1e9).toFixed(1) + ' M';
                            if (value >= 1e6) return (value / 1e6).toFixed(1) + ' jt';
                            if (value >= 1e3) return (value / 1e3).toFixed(0) + ' rb';
                            return value;
                        }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endsection
