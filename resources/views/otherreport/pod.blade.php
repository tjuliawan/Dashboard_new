@extends('layouts.user_type.auth')

@section('title', 'POD Report')

@section('css')
<style>
    .widget-card {
        background: #fff;
        border-radius: 12px;
        padding: 22px 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        margin-bottom: 24px;
    }
    .widget-title {
        font-size: 0.82rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .5px; color: #6b7280; margin-bottom: 16px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .badge-count {
        display: inline-block; background: #eff6ff; color: #2563eb;
        padding: 1px 8px; border-radius: 10px; font-size: 0.72rem; font-weight: 600; margin-left: 6px;
    }

    .filter-bar {
        display: flex; flex-wrap: wrap; gap: 12px;
        align-items: flex-end; margin-bottom: 18px;
        padding: 14px 16px; background: #f8fafc;
        border: 1px solid #e5e7eb; border-radius: 9px;
    }
    .filter-group { display: flex; flex-direction: column; gap: 4px; }
    .filter-label { font-size: 0.70rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }
    .filter-input {
        padding: 7px 10px; border: 1.5px solid #d1d5db; border-radius: 7px;
        font-size: 0.82rem; color: #111827; outline: none; background: #fff;
    }
    .filter-input:focus { border-color: #2c5364; }
    .btn-filter {
        background: #2c5364; color: #fff; border: none;
        padding: 8px 18px; border-radius: 7px; font-size: 0.82rem;
        font-weight: 600; cursor: pointer; align-self: flex-end;
    }
    .btn-filter:hover { background: #203a43; }
    .btn-reset {
        background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb;
        padding: 8px 14px; border-radius: 7px; font-size: 0.82rem;
        cursor: pointer; align-self: flex-end; text-decoration: none;
        display: inline-block;
    }
    .btn-reset:hover { background: #e5e7eb; }

    .data-table-wrap { overflow-x: auto; }
    table.data-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
    table.data-table thead th {
        background: #f9fafb; padding: 10px 14px;
        text-align: left; font-weight: 600; color: #374151;
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: .4px;
        border-bottom: 2px solid #e5e7eb; white-space: nowrap;
    }
    table.data-table tbody tr { border-bottom: 1px solid #f3f4f6; }
    table.data-table tbody tr:hover { background: #f9fafb; }
    table.data-table tbody td { padding: 9px 14px; color: #4b5563; white-space: nowrap; }

    .agg-result-box {
        display: none; background: #f0f9ff; border: 1.5px solid #bae6fd;
        border-radius: 10px; padding: 16px 22px;
        align-items: center; gap: 20px; margin-top: 16px;
    }
    .agg-result-box.visible { display: flex; }
    .agg-result-label { font-size: 1.9rem; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; }
    .agg-result-value { font-size: 3rem; font-weight: 800; color: #0c4a6e; line-height: 1; }
    .agg-result-desc  { font-size: 0.9rem; color: #0369a1; align-self: flex-end; padding-bottom: 4px; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">POD Report</h4>

        {{-- ── DATA TABLE WIDGET ── --}}
        <div class="widget-card">
            <div class="widget-title">
                <span>
                    Data Tabel — POD Report
                    <span class="badge-count">{{ $rows->total() }} baris</span>
                </span>
                <button onclick="openExportModal()"
                        style="display:inline-flex;align-items:center;gap:6px;background:#16a34a;color:#fff;border:none;border-radius:7px;padding:6px 14px;font-size:0.8rem;font-weight:600;cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                    &#x2193; Export CSV
                </button>
            </div>

            <form method="GET" action="{{ route('pod.detail.index') }}" class="filter-bar">
                <div class="filter-group">
                    <label class="filter-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="filter-input"
                           value="{{ request('date_from') }}">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Sampai Tanggal <span style="font-weight:400;color:#9ca3af;">(default: hari ini)</span></label>
                    <input type="date" name="date_to" class="filter-input"
                           value="{{ request('date_to', $dateTo->format('Y-m-d')) }}">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Baris per halaman</label>
                    <div style="display:flex;gap:4px;">
                        @foreach([20, 25, 50, 100] as $opt)
                        <a href="{{ route('pod.detail.index') }}?{{ http_build_query(array_merge(request()->query(), ['per_page' => $opt, 'page' => 1])) }}"
                           style="padding:6px 11px;border:1.5px solid {{ request('per_page', 25) == $opt ? '#2c5364' : '#d1d5db' }};border-radius:7px;font-size:0.78rem;
                                  background:{{ request('per_page', 25) == $opt ? '#2c5364' : '#fff' }};color:{{ request('per_page', 25) == $opt ? '#fff' : '#374151' }};
                                  text-decoration:none;line-height:1.4;">{{ $opt }}</a>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 25) }}">
                <input type="hidden" name="view"     value="{{ $view ?? 'pod' }}">
                <button type="submit" class="btn-filter">Filter</button>
                <a href="{{ route('pod.detail.index') }}" class="btn-reset">Reset</a>
            </form>

            <form method="GET" action="{{ route('pod.detail.index') }}" class="filter-bar" style="margin-bottom:14px;">
                <input type="hidden" name="date_from"  value="{{ request('date_from') }}">
                <input type="hidden" name="date_to"    value="{{ request('date_to', $dateTo->format('Y-m-d')) }}">
                <input type="hidden" name="per_page"   value="{{ request('per_page', 25) }}">
                <input type="hidden" name="view"       value="{{ $view ?? 'pod' }}">
                <div class="filter-group">
                    <label class="filter-label">Filter Kolom</label>
                    <select name="filter_col" class="filter-input" style="min-width:160px;">
                        <option value="">— Semua —</option>
                        @foreach([
                            'dptch_date'                 => 'Dispatch Date',
                            'dptch_vhcl_code'            => 'Vehicle Code',
                            'dptch_drv_code'             => 'Driver Code',
                            'dptch_code_h'               => 'Dispatch Code',
                            'dpch_type'                  => 'Type',
                            'dpch_status'                => 'Status',
                            'dpch_dispach_inv_total'      => 'Total Invoice',
                            'dpch_dispach_inv_cash'       => 'Invoice Terkirim',
                            'dpch_dispach_inv_cancel'     => 'Invoice Tidak Terkirim',
                            'dpch_resaon'                 => 'Reason',
                        ] as $key => $label)
                        <option value="{{ $key }}" {{ ($filterCol ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Nilai Filter</label>
                    <input type="text" name="filter_val" class="filter-input" style="min-width:180px;"
                           value="{{ $filterVal ?? '' }}" placeholder="Cari nilai...">
                </div>
                <button type="submit" class="btn-filter">Terapkan</button>
                @if(($filterCol ?? '') !== '' || ($filterVal ?? '') !== '')
                <a href="{{ route('pod.detail.index') }}?{{ http_build_query(array_filter(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'per_page' => request('per_page')])) }}"
                   class="btn-reset">Reset Filter</a>
                @endif

                @php
                    $currentView = $view ?? 'pod';
                    $toggleView  = $currentView === 'invoice' ? 'pod' : 'invoice';
                    $toggleLabel = $currentView === 'invoice' ? 'POD' : 'Invoice';
                    $toggleUrl   = route('pod.detail.index') . '?' . http_build_query(array_filter([
                        'date_from'  => request('date_from'),
                        'date_to'    => request('date_to'),
                        'per_page'   => request('per_page'),
                        'filter_col' => request('filter_col'),
                        'filter_val' => request('filter_val'),
                        'view'       => $toggleView,
                    ]));
                @endphp
                <a href="{{ $toggleUrl }}"
                   style="margin-left:auto;display:inline-flex;align-items:center;gap:6px;background:#0ea5e9;color:#fff;border:none;border-radius:7px;padding:8px 18px;font-size:0.83rem;font-weight:600;cursor:pointer;text-decoration:none;align-self:flex-end;"
                   onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='#0ea5e9'">
                    {{ $toggleLabel }}
                </a>
            </form>

            <div class="data-table-wrap">
                @if($currentView === 'invoice')
                <table class="data-table" id="pod-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Area</th>
                            <th>Vehicle Code</th>
                            <th>Driver Code</th>
                            <th>Status</th>
                            <th>Kasir</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                        <tr>
                            <td data-col="dptch_date">{{ $row->dptch_date }}</td>
                            <td data-col="rec_comcode">{{ $row->rec_comcode }}</td>
                            <td data-col="dpcth_vhcl_code">{{ $row->dpcth_vhcl_code }}</td>
                            <td data-col="dpcth_driver_code">{{ $row->dpcth_driver_code }}</td>
                            <td data-col="dpch_status">{{ $row->dpch_status }}</td>
                            <td data-col="dpch_salesresentatip">{{ $row->dpch_salesresentatip }}</td>
                            <td data-col="dpch_value">{{ $row->dpch_value }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; color:#9ca3af; padding:32px;">Tidak ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @else
                <table class="data-table" id="pod-table">
                    <thead>
                        <tr>
                            <th>Dispatch Date</th>
                            <th>Vehicle Code</th>
                            <th>Driver Code</th>
                            <th>Dispatch Code</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th data-col="dpch_dispach_inv_total">Total Invoice</th>
                            <th data-col="dpch_dispach_inv_cash">Invoice Terkirim</th>
                            <th data-col="dpch_dispach_inv_cancel">Invoice Tidak Terkirim</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                        <tr>
                            <td data-col="dptch_date">{{ $row->dptch_date }}</td>
                            <td data-col="dptch_vhcl_code">{{ $row->dptch_vhcl_code }}</td>
                            <td data-col="dptch_drv_code">{{ $row->dptch_drv_code }}</td>
                            <td data-col="dptch_code_h">{{ $row->dptch_code_h }}</td>
                            <td data-col="dpch_type">{{ $row->dpch_type }}</td>
                            <td data-col="dpch_status">{{ $row->dpch_status }}</td>
                            <td data-col="dpch_dispach_inv_total">{{ $row->dpch_dispach_inv_total }}</td>
                            <td data-col="dpch_dispach_inv_cash">{{ $row->dpch_dispach_inv_cash }}</td>
                            <td data-col="dpch_dispach_inv_cancel">{{ $row->dpch_dispach_inv_cancel }}</td>
                            <td data-col="dpch_resaon">{{ $row->dpch_resaon }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" style="text-align:center; color:#9ca3af; padding:32px;">Tidak ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @endif
            </div>

            @if($rows->total() > 0)
            @php
                $totalPages  = $rows->lastPage();
                $currentPage = $rows->currentPage();
                $perPageNow  = $rows->perPage();
                $startRow    = ($currentPage - 1) * $perPageNow + 1;
                $endRow      = min($currentPage * $perPageNow, $rows->total());
                $pages = [];
                if ($totalPages <= 7) {
                    $pages = range(1, $totalPages);
                } else {
                    $pages[] = 1;
                    if ($currentPage > 4) $pages[] = '...';
                    $start = max(2, $currentPage - 1);
                    $end   = min($totalPages - 1, $currentPage + 1);
                    for ($i = $start; $i <= $end; $i++) $pages[] = $i;
                    if ($currentPage < $totalPages - 3) $pages[] = '...';
                    $pages[] = $totalPages;
                }
            @endphp
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;margin-top:14px;padding-top:12px;border-top:1px solid #e5e7eb;">
                <div style="font-size:0.8rem;color:#4b5563;">
                    Menampilkan <strong>{{ number_format($startRow,0,',','.') }}</strong>–<strong>{{ number_format($endRow,0,',','.') }}</strong>
                    dari <strong>{{ number_format($rows->total(),0,',','.') }}</strong> baris
                </div>

                @if($totalPages > 1)
                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                    <a href="{{ route('pod.detail.index') . '?' . http_build_query(array_merge(request()->query(), ['page' => max(1, $currentPage - 1), 'per_page' => $perPageNow])) }}"
                       style="padding:5px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:0.78rem;color:#374151;text-decoration:none;{{ $currentPage == 1 ? 'opacity:.4;pointer-events:none;' : '' }}">
                        &laquo;
                    </a>
                    @foreach($pages as $p)
                        @if($p === '...')
                            <span style="padding:5px 8px;font-size:0.78rem;color:#9ca3af;">…</span>
                        @else
                            <a href="{{ route('pod.detail.index') . '?' . http_build_query(array_merge(request()->query(), ['page' => $p, 'per_page' => $perPageNow])) }}"
                               style="padding:5px 10px;border:1.5px solid {{ $p == $currentPage ? '#2c5364' : '#d1d5db' }};border-radius:6px;font-size:0.78rem;
                                      background:{{ $p == $currentPage ? '#2c5364' : '#fff' }};color:{{ $p == $currentPage ? '#fff' : '#374151' }};text-decoration:none;">
                                {{ $p }}
                            </a>
                        @endif
                    @endforeach
                    <a href="{{ route('pod.detail.index') . '?' . http_build_query(array_merge(request()->query(), ['page' => min($totalPages, $currentPage + 1), 'per_page' => $perPageNow])) }}"
                       style="padding:5px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:0.78rem;color:#374151;text-decoration:none;{{ $currentPage == $totalPages ? 'opacity:.4;pointer-events:none;' : '' }}">
                        &raquo;
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- ── EXPORT MODAL ── --}}
        <div id="export-modal-overlay"
             style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:14px;padding:28px 32px;width:360px;max-width:94vw;box-shadow:0 8px 40px rgba(0,0,0,.18);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <span style="font-size:1rem;font-weight:700;color:#111827;">Export CSV — Pilih Rentang Tanggal</span>
                    <button onclick="closeExportModal()" style="background:none;border:none;font-size:1.3rem;color:#6b7280;cursor:pointer;line-height:1;">&times;</button>
                </div>
                <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:24px;">
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        <label style="font-size:0.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;">Dari Tanggal</label>
                        <input type="date" id="export-date-from"
                               value="{{ request('date_from') }}"
                               style="padding:8px 11px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.85rem;color:#111827;outline:none;">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        <label style="font-size:0.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;">Sampai Tanggal</label>
                        <input type="date" id="export-date-to"
                               value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"
                               style="padding:8px 11px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.85rem;color:#111827;outline:none;">
                    </div>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button onclick="closeExportModal()"
                            style="padding:8px 18px;border:1.5px solid #d1d5db;border-radius:7px;font-size:0.83rem;background:#f9fafb;color:#374151;cursor:pointer;font-weight:600;"
                            onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f9fafb'">
                        Batal
                    </button>
                    <button onclick="doExport()"
                            style="padding:8px 18px;border:none;border-radius:7px;font-size:0.83rem;background:#16a34a;color:#fff;cursor:pointer;font-weight:600;"
                            onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                        &#x2193; Download CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const exportBaseUrl   = @json(route('pod.detail.export'));
const exportFilterCol = @json($filterCol ?? '');
const exportFilterVal = @json($filterVal ?? '');

function openExportModal() {
    document.getElementById('export-modal-overlay').style.display = 'flex';
}
function closeExportModal() {
    document.getElementById('export-modal-overlay').style.display = 'none';
}
function doExport() {
    const dateFrom = document.getElementById('export-date-from').value;
    const dateTo   = document.getElementById('export-date-to').value;
    const params   = new URLSearchParams();
    if (dateFrom)        params.set('date_from',  dateFrom);
    if (dateTo)          params.set('date_to',    dateTo);
    if (exportFilterCol) params.set('filter_col', exportFilterCol);
    if (exportFilterVal) params.set('filter_val', exportFilterVal);
    window.location.href = exportBaseUrl + '?' + params.toString();
    closeExportModal();
}
document.getElementById('export-modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeExportModal();
});

function runAggregation() {
    const filterColEl  = document.getElementById('agg-col');
    const valueEl      = document.getElementById('agg-value');
    const aggColEl     = document.getElementById('agg-agg-col');
    const aggValueEl   = document.getElementById('agg-agg-value');
    const funcEl       = document.getElementById('agg-func');
    const dateFromEl   = document.getElementById('agg-date-from');
    const dateToEl     = document.getElementById('agg-date-to');
    const resultDiv    = document.getElementById('agg-result');
    const resultLabel  = document.getElementById('agg-result-label');
    const resultValue  = document.getElementById('agg-result-value');
    const resultDesc   = document.getElementById('agg-result-desc');

    const filterCol  = filterColEl.value;
    const filterVal  = valueEl.value.trim();
    const aggCol     = aggColEl.value || filterCol;
    const aggVal     = aggValueEl.value.trim();
    const func       = funcEl.value;
    const dateFrom   = dateFromEl.value;
    const dateTo     = dateToEl.value || '{{ now()->format('Y-m-d') }}';

    const colLabels = {
        dptch_date: 'Dispatch Date', dptch_vhcl_code: 'Vehicle Code',
        dptch_drv_code: 'Driver Code', dptch_code_h: 'Dispatch Code', dptch_SO: 'SO',
        dpch_type: 'Type', dpch_status: 'Status',
        dpch_dispach_inv_total: 'Inv Total', dpch_dispach_inv_cash: 'Inv Cash',
        dpch_dispach_inv_cancel: 'Inv Cancel', dpch_dispach_inv_reschedule: 'Inv Reschedule',
        dpch_resaon: 'Reason'
    };

    if (!filterCol) {
        resultDiv.classList.remove('visible');
        return;
    }
    if (['sum','max','min'].includes(func) && !aggCol) {
        resultDiv.classList.remove('visible');
        return;
    }

    const filterLabel = colLabels[filterCol] || filterCol;
    const aggLabel    = colLabels[aggCol]    || aggCol;

    resultDiv.classList.add('visible');
    resultLabel.textContent = 'RESULT';
    resultValue.textContent = '...';
    resultDesc.textContent  = 'Menghitung dari database...';

    const params = new URLSearchParams({
        agg_col:       filterCol,
        agg_value:     filterVal,
        agg_agg_col:   aggCol,
        agg_agg_value: aggVal,
        agg_func:      func,
        date_to:       dateTo,
    });
    if (dateFrom) params.append('date_from', dateFrom);

    fetch('{{ route('pod.detail.calculate') }}?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.error) {
            resultValue.textContent = '—';
            resultDesc.textContent  = data.error;
            return;
        }
        const numResult = parseFloat(data.result);
        resultLabel.textContent = 'RESULT';
        if (isNaN(numResult)) {
            resultValue.textContent = '0';
        } else {
            resultValue.textContent = Number.isInteger(numResult)
                ? numResult.toLocaleString('id-ID')
                : numResult.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        const descParts = [func.toUpperCase()];
        if (aggCol) descParts.push(aggLabel);
        if (filterVal) descParts.push('where ' + filterLabel + ' = "' + filterVal + '"');
        if (aggCol !== filterCol && aggVal) descParts.push('and ' + aggLabel + ' = "' + aggVal + '"');
        resultDesc.textContent = descParts.join(' ') + (func === 'count' ? ' baris' : '');
    })
    .catch(function() {
        resultValue.textContent = '—';
        resultDesc.textContent  = 'Terjadi kesalahan saat menghitung.';
    });
}
</script>
@endsection
