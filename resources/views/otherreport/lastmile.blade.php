@extends('layouts.user_type.auth')

@section('title', 'Last Mile')

@section('css')
<style>
    .lm-card {
        background: #fff;
        border-radius: 12px;
        padding: 22px 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        margin-bottom: 24px;
    }
    .lm-card-title {
        font-size: 0.82rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .5px; color: #6b7280; margin-bottom: 16px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .lm-badge {
        display: inline-block; background: #eff6ff; color: #2563eb;
        padding: 1px 8px; border-radius: 10px; font-size: 0.72rem;
        font-weight: 600; margin-left: 6px;
    }
    .lm-table-wrap { overflow-x: auto; }
    table.lm-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
    table.lm-table thead th {
        background: #f9fafb; padding: 10px 14px;
        text-align: left; font-weight: 600; color: #374151;
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: .4px;
        border-bottom: 2px solid #e5e7eb; white-space: nowrap;
    }
    table.lm-table tbody tr { border-bottom: 1px solid #f3f4f6; }
    table.lm-table tbody tr:hover { background: #f9fafb; }
    table.lm-table tbody td { padding: 9px 14px; color: #4b5563; white-space: nowrap; }

    .lm-filter-bar {
        display: flex; flex-wrap: wrap; gap: 12px;
        align-items: flex-end; margin-bottom: 18px;
        padding: 14px 16px; background: #f8fafc;
        border: 1px solid #e5e7eb; border-radius: 9px;
    }
    .lm-filter-group { display: flex; flex-direction: column; gap: 4px; }
    .lm-filter-label { font-size: 0.70rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }
    .lm-filter-input {
        padding: 7px 10px; border: 1.5px solid #d1d5db; border-radius: 7px;
        font-size: 0.82rem; color: #111827; outline: none; background: #fff;
    }
    .lm-filter-input:focus { border-color: #2c5364; }
    .lm-btn-filter {
        background: #2c5364; color: #fff; border: none;
        padding: 8px 18px; border-radius: 7px; font-size: 0.82rem;
        font-weight: 600; cursor: pointer; align-self: flex-end;
    }
    .lm-btn-filter:hover { background: #203a43; }
    .lm-btn-reset {
        background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb;
        padding: 8px 14px; border-radius: 7px; font-size: 0.82rem;
        cursor: pointer; align-self: flex-end; text-decoration: none;
        display: inline-block;
    }
    .lm-btn-reset:hover { background: #e5e7eb; }

    /* Clickable row */
    table.lm-table tbody tr.lm-row-clickable { cursor: pointer; }
    table.lm-table tbody tr.lm-row-clickable:hover { background: #eff6ff; }
    table.lm-table tbody tr.lm-reason-clickable { cursor: pointer; }
    table.lm-table tbody tr.lm-reason-clickable:hover { background: #fef2f2; }

    /* Modal */
    .lm-modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.45); z-index: 9999;
        align-items: center; justify-content: center;
    }
    .lm-modal-overlay.visible { display: flex; }
    .lm-modal {
        background: #fff; border-radius: 14px;
        padding: 22px 26px; width: 720px; max-width: 94vw;
        max-height: 86vh; overflow-y: auto;
        box-shadow: 0 8px 40px rgba(0,0,0,.18);
    }
    .lm-modal-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 14px;
    }
    .lm-modal-title { font-size: 1rem; font-weight: 700; color: #111827; }
    .lm-modal-close {
        background: none; border: none; font-size: 1.4rem;
        color: #6b7280; cursor: pointer; line-height: 1;
    }
    .lm-modal-meta {
        font-size: 0.78rem; color: #6b7280; margin-bottom: 14px;
        padding: 10px 12px; background: #f9fafb; border-radius: 7px;
    }
    .lm-status-badge {
        display: inline-block; padding: 2px 8px; border-radius: 10px;
        font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
    }
    .lm-status-delivered { background: #dcfce7; color: #15803d; }
    .lm-status-cancel    { background: #fee2e2; color: #b91c1c; }
    .lm-status-other     { background: #e5e7eb; color: #374151; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4">Last Mile</h4>

            {{-- ── SPK & CANCEL  +  SLA & PERFORMA (split: SPK kecil, SLA besar) ── --}}
            <div style="display:grid;grid-template-columns:minmax(0, 1fr) minmax(0, 2fr);gap:24px;margin-bottom:24px;">

            {{-- ── 5. SPK & CANCEL ── --}}
            <div class="lm-card" id="lm-spk-cancel" style="margin-bottom:0;">
                <div class="lm-card-title">
                    <span>SPK &amp; Cancel</span>
                    <span style="font-size:.72rem;color:#9ca3af;font-weight:500;text-transform:none;letter-spacing:0;">
                        Periode: {{ $spkFrom->format('d M Y') }} – {{ $spkTo->format('d M Y') }}
                    </span>
                </div>

                {{-- Filter range SPK --}}
                <form method="GET" action="{{ route('lastmile.index') }}#lm-spk-cancel" class="lm-filter-bar">
                    {{-- preserve filter Data Per Driver --}}
                    @if(request('date_from'))<input type="hidden" name="date_from" value="{{ request('date_from') }}">@endif
                    @if(request('date_to'))<input type="hidden" name="date_to" value="{{ request('date_to') }}">@endif
                    @if(request('per_page'))<input type="hidden" name="per_page" value="{{ request('per_page') }}">@endif

                    <div class="lm-filter-group">
                        <label class="lm-filter-label">Rentang Waktu</label>
                        <select name="spk_range" class="lm-filter-input" onchange="this.form.submit()">
                            @foreach($allowedSpkRanges as $opt)
                                <option value="{{ $opt }}" {{ (int) $spkRange === $opt ? 'selected' : '' }}>
                                    {{ $opt }} hari terakhir
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="lm-btn-filter">Terapkan</button>
                </form>

                {{-- KPI cards --}}
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:18px;">
                    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:14px 16px;">
                        <div style="font-size:.72rem;color:#9a3412;text-transform:uppercase;font-weight:700;letter-spacing:.4px;">SPK Menggantung</div>
                        <div style="font-size:1.6rem;font-weight:700;color:#c2410c;margin-top:4px;">
                            {{ number_format((int) ($spkSummary->spk_menggantung ?? 0), 0, ',', '.') }}
                        </div>
                        <div style="font-size:.72rem;color:#9a3412;margin-top:2px;">Status CANCEL — tidak terkirim</div>
                    </div>
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;">
                        <div style="font-size:.72rem;color:#1e40af;text-transform:uppercase;font-weight:700;letter-spacing:.4px;">Ongoing (OPEN)</div>
                        <div style="font-size:1.6rem;font-weight:700;color:#1d4ed8;margin-top:4px;">
                            {{ number_format((int) ($spkSummary->total_ongoing ?? 0), 0, ',', '.') }}
                        </div>
                        <div style="font-size:.72rem;color:#1e40af;margin-top:2px;">Masih berjalan, (Open)</div>
                    </div>
                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;">
                        <div style="font-size:.72rem;color:#374151;text-transform:uppercase;font-weight:700;letter-spacing:.4px;">Total Invoice</div>
                        <div style="font-size:1.6rem;font-weight:700;color:#111827;margin-top:4px;">
                            {{ number_format((int) ($spkSummary->total_invoice ?? 0), 0, ',', '.') }}
                        </div>
                        <div style="font-size:.72rem;color:#6b7280;margin-top:2px;">Semua dispatch periode ini</div>
                    </div>
                </div>

                {{-- Breakdown reason cancel --}}
                <div style="font-size:.78rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">
                    Breakdown Reason Cancel
                </div>
                <div class="lm-table-wrap">
                    <table class="lm-table">
                        <thead>
                            <tr>
                                <th style="width:40px;text-align:right;">#</th>
                                <th>Reason</th>
                                <th style="text-align:right;width:120px;">Jumlah</th>
                                <th style="text-align:right;width:120px;">% dari Cancel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalCancel = (int) ($spkSummary->spk_menggantung ?? 0); @endphp
                            @forelse($cancelReasons as $i => $r)
                                @php
                                    $pct = $totalCancel > 0 ? ($r->jumlah / $totalCancel) * 100 : 0;
                                    $isNoReason = $r->reason === '(Tanpa Reason)';
                                @endphp
                                <tr class="lm-reason-clickable"
                                    data-reason="{{ $r->reason }}"
                                    onclick="openCancelDetailModal(this)">
                                    <td style="text-align:right;color:#9ca3af;">{{ $i + 1 }}</td>
                                    <td style="{{ $isNoReason ? 'color:#b91c1c;font-style:italic;' : '' }}">
                                        {{ $r->reason }}
                                    </td>
                                    <td style="text-align:right;font-weight:600;">{{ number_format((int) $r->jumlah, 0, ',', '.') }}</td>
                                    <td style="text-align:right;color:#6b7280;">{{ number_format($pct, 1, ',', '.') }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center;color:#9ca3af;padding:24px;">
                                        Tidak ada cancel pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── 3. SLA & PERFORMA ── --}}
            <div class="lm-card" id="lm-sla-performa" style="margin-bottom:0;">
                <div class="lm-card-title">
                    <span>SLA &amp; Performa</span>
                    <span style="font-size:.72rem;color:#9ca3af;font-weight:500;text-transform:none;letter-spacing:0;">
                        Periode: {{ $spkFrom->format('d M Y') }} – {{ $spkTo->format('d M Y') }}
                    </span>
                </div>

                @php
                    // Threshold warna untuk Cancel Rate
                    $crColor = $cancelRate >= 15 ? '#b91c1c' : ($cancelRate >= 5 ? '#d97706' : '#15803d');
                    $crBg    = $cancelRate >= 15 ? '#fef2f2' : ($cancelRate >= 5 ? '#fffbeb' : '#f0fdf4');
                    $crBd    = $cancelRate >= 15 ? '#fecaca' : ($cancelRate >= 5 ? '#fde68a' : '#bbf7d0');
                    // Threshold Success Rate (kebalikan)
                    $srColor = $successRate >= 95 ? '#15803d' : ($successRate >= 85 ? '#d97706' : '#b91c1c');
                @endphp

                {{-- KPI utama --}}
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:14px;">
                    <div style="background:{{ $crBg }};border:1px solid {{ $crBd }};border-radius:10px;padding:14px 16px;">
                        <div style="font-size:.72rem;color:{{ $crColor }};text-transform:uppercase;font-weight:700;letter-spacing:.4px;">Cancel Rate</div>
                        <div style="font-size:1.6rem;font-weight:700;color:{{ $crColor }};margin-top:4px;">
                            {{ number_format($cancelRate, 2, ',', '.') }}%
                        </div>
                        <div style="font-size:.72rem;color:{{ $crColor }};margin-top:2px;">CANCEL / (DELIVERED + CANCEL)</div>
                    </div>
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;">
                        <div style="font-size:.72rem;color:{{ $srColor }};text-transform:uppercase;font-weight:700;letter-spacing:.4px;">Success Rate</div>
                        <div style="font-size:1.6rem;font-weight:700;color:{{ $srColor }};margin-top:4px;">
                            {{ number_format($successRate, 2, ',', '.') }}%
                        </div>
                        <div style="font-size:.72rem;color:{{ $srColor }};margin-top:2px;">DELIVERED / (DELIVERED + CANCEL)</div>
                    </div>
                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;">
                        <div style="font-size:.72rem;color:#374151;text-transform:uppercase;font-weight:700;letter-spacing:.4px;">Sample Finalized</div>
                        <div style="font-size:1.6rem;font-weight:700;color:#111827;margin-top:4px;">
                            {{ number_format($slaFinalized, 0, ',', '.') }}
                        </div>
                        <div style="font-size:.72rem;color:#6b7280;margin-top:2px;">
                            DELIVERED + CANCEL
                            @if($slaTotalOngoing > 0)
                                · <span style="color:#1d4ed8;">{{ number_format($slaTotalOngoing, 0, ',', '.') }} OPEN dikecualikan</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Perbandingan SLA per Driver --}}
                @php
                    $weekSections = [
                        ['title' => 'SLA 7 Hari Terakhir',  'period' => $thisWeekFrom->format('d M') . ' – ' . $thisWeekTo->format('d M Y'), 'rows' => $slaThisWeek],
                        ['title' => 'SLA 30 Hari Terakhir', 'period' => $lastWeekFrom->format('d M') . ' – ' . $lastWeekTo->format('d M Y'), 'rows' => $slaLastWeek],
                    ];
                    $yearSection = ['title' => 'SLA Tahun Ini', 'period' => $thisYearFrom->format('d M') . ' – ' . $thisYearTo->format('d M Y'), 'rows' => $slaThisYear];
                @endphp

                {{-- Minggu Ini | Minggu Kemarin (side-by-side, kompak) --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    @foreach($weekSections as $sec)
                        <div>
                            <div style="font-size:.74rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;margin:14px 0 6px;">
                                {{ $sec['title'] }}
                                <span style="font-weight:500;color:#9ca3af;font-size:.68rem;text-transform:none;letter-spacing:0;">
                                    ({{ $sec['period'] }})
                                </span>
                            </div>
                            <div class="lm-table-wrap" style="max-height:240px;overflow-y:auto;">
                                <table class="lm-table">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th style="text-align:right;">Delv</th>
                                            <th style="text-align:right;">Cncl</th>
                                            <th style="text-align:right;">SLA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sec['rows'] as $d)
                                            @php
                                                $sColor = $d->success_rate >= 95 ? '#15803d' : ($d->success_rate >= 85 ? '#d97706' : '#b91c1c');
                                            @endphp
                                            <tr>
                                                <td>{{ $d->driver_name }}</td>
                                                <td style="text-align:right;color:#16a34a;">{{ number_format((int) $d->delivered, 0, ',', '.') }}</td>
                                                <td style="text-align:right;color:#dc2626;">{{ number_format((int) $d->cancel, 0, ',', '.') }}</td>
                                                <td style="text-align:right;font-weight:700;color:{{ $sColor }};">
                                                    {{ number_format($d->success_rate, 1, ',', '.') }}%
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:18px;">Tidak ada data.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- SLA Tahun Ini (full width, kolom lengkap) --}}
                <div style="font-size:.74rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;margin:18px 0 6px;">
                    {{ $yearSection['title'] }}
                    <span style="font-weight:500;color:#9ca3af;font-size:.68rem;text-transform:none;letter-spacing:0;">
                        ({{ $yearSection['period'] }})
                    </span>
                </div>
                <div class="lm-table-wrap" style="max-height:280px;overflow-y:auto;">
                    <table class="lm-table">
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th style="text-align:right;">Delivered</th>
                                <th style="text-align:right;">Cancel</th>
                                <th style="text-align:right;width:120px;">SLA (Success)</th>
                                <th style="text-align:right;width:120px;">Cancel Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($yearSection['rows'] as $d)
                                @php
                                    $sColor = $d->success_rate >= 95 ? '#15803d' : ($d->success_rate >= 85 ? '#d97706' : '#b91c1c');
                                @endphp
                                <tr>
                                    <td>{{ $d->driver_name }}</td>
                                    <td style="text-align:right;color:#16a34a;">{{ number_format((int) $d->delivered, 0, ',', '.') }}</td>
                                    <td style="text-align:right;color:#dc2626;">{{ number_format((int) $d->cancel, 0, ',', '.') }}</td>
                                    <td style="text-align:right;font-weight:700;color:{{ $sColor }};">
                                        {{ number_format($d->success_rate, 2, ',', '.') }}%
                                    </td>
                                    <td style="text-align:right;color:#6b7280;">
                                        {{ number_format($d->cancel_rate, 2, ',', '.') }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;color:#9ca3af;padding:18px;">
                                        Tidak ada data driver pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            </div>{{-- end split row --}}

            {{-- ── DATA PER DRIVER ── --}}
            <div class="lm-card" id="data-per-driver">
                <div class="lm-card-title">
                    <span>
                        Data Per Driver
                        <span class="lm-badge">{{ $drivers->total() }} baris</span>
                    </span>
                    <span style="font-size:.72rem;color:#9ca3af;font-weight:500;text-transform:none;letter-spacing:0;">
                        Periode: {{ $dateFrom->format('d M Y') }} – {{ $dateTo->format('d M Y') }}
                    </span>
                </div>

                {{-- Filter Dptch_date --}}
                <form method="GET" action="{{ route('lastmile.index') }}" class="lm-filter-bar">
                    @if(request('spk_range'))<input type="hidden" name="spk_range" value="{{ request('spk_range') }}">@endif
                    <div class="lm-filter-group">
                        <label class="lm-filter-label">Dari Tanggal (Dptch_date)</label>
                        <input type="date" name="date_from" class="lm-filter-input"
                               value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}">
                    </div>
                    <div class="lm-filter-group">
                        <label class="lm-filter-label">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="lm-filter-input"
                               value="{{ request('date_to', $dateTo->format('Y-m-d')) }}">
                    </div>
                    <div class="lm-filter-group">
                        <label class="lm-filter-label">Per Halaman</label>
                        <select name="per_page" class="lm-filter-input" onchange="this.form.submit()">
                            @foreach($allowedPerPage as $opt)
                                <option value="{{ $opt }}" {{ (int) $perPage === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="lm-btn-filter">Filter</button>
                    <a href="{{ route('lastmile.index') }}" class="lm-btn-reset">Reset</a>
                </form>

                <div class="lm-table-wrap">
                    <table class="lm-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Driver</th>
                                <th>Kendaraan</th>
                                <th>Dispatch Code</th>
                                <th style="text-align:right;">Total Invoice</th>
                                <th style="text-align:right;">Terkirim</th>
                                <th style="text-align:right;">Cancel</th>
                                <th>POD di Lokasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($drivers as $row)
                            <tr class="lm-row-clickable"
                                data-date="{{ $row->dispatch_date }}"
                                data-driver-code="{{ $row->driver_code }}"
                                data-driver-name="{{ $row->driver_name }}"
                                data-vhcl="{{ $row->vhcl_codes }}"
                                data-dispatch="{{ $row->dispatch_code }}"
                                onclick="openInvoiceModal(this)">
                                <td>
                                    {{ $row->dispatch_date ? \Carbon\Carbon::parse($row->dispatch_date)->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    {{ $row->driver_name }}
                                </td>
                                <td>{{ $row->vhcl_codes ?: '-' }}</td>
                                <td style="font-family:monospace;font-size:.78rem;">{{ $row->dispatch_code }}</td>
                                <td style="text-align:right;">{{ number_format((int) $row->total_invoice, 0, ',', '.') }}</td>
                                <td style="text-align:right;color:#16a34a;font-weight:600;">{{ number_format((int) $row->total_delivered, 0, ',', '.') }}</td>
                                <td style="text-align:right;color:#dc2626;font-weight:600;">{{ number_format((int) $row->total_cancel, 0, ',', '.') }}</td>
                                <td style="color:#9ca3af;">—</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align:center; color:#9ca3af; padding:32px;">
                                    Belum ada data.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-top:14px;">
                    <div style="font-size:.78rem;color:#6b7280;">
                        Menampilkan {{ $drivers->firstItem() ?? 0 }}–{{ $drivers->lastItem() ?? 0 }} dari {{ $drivers->total() }} baris
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ── CANCEL DETAIL MODAL ── --}}
<div id="lm-cancel-modal" class="lm-modal-overlay" onclick="if(event.target===this) closeCancelDetailModal()">
    <div class="lm-modal" style="width:880px;">
        <div class="lm-modal-head">
            <span class="lm-modal-title">Detail Cancel</span>
            <button class="lm-modal-close" onclick="closeCancelDetailModal()">&times;</button>
        </div>
        <div id="lm-cancel-meta" class="lm-modal-meta"></div>
        <div class="lm-table-wrap">
            <table class="lm-table" id="lm-cancel-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Dispatch Code</th>
                        <th>SO</th>
                        <th>Driver</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody id="lm-cancel-tbody">
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:24px;">Memuat...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── INVOICE DETAIL MODAL ── --}}
<div id="lm-invoice-modal" class="lm-modal-overlay" onclick="if(event.target===this) closeInvoiceModal()">
    <div class="lm-modal">
        <div class="lm-modal-head">
            <span class="lm-modal-title">Daftar Invoice</span>
            <button class="lm-modal-close" onclick="closeInvoiceModal()">&times;</button>
        </div>
        <div id="lm-modal-meta" class="lm-modal-meta"></div>
        <div class="lm-table-wrap">
            <table class="lm-table" id="lm-invoice-table">
                <thead>
                    <tr>
                        <th>SO</th>
                        <th>Status</th>
                        <th style="text-align:right;">Value</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody id="lm-invoice-tbody">
                    <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:24px;">Memuat...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const lmInvoiceUrl      = '{{ route('lastmile.invoices') }}';
const lmCancelDetailUrl = '{{ route('lastmile.cancel-detail') }}';
const lmSpkRange        = {{ (int) $spkRange }};

function openInvoiceModal(rowEl) {
    const date         = rowEl.dataset.date;
    const driverCode   = rowEl.dataset.driverCode;
    const driverName   = rowEl.dataset.driverName;
    const vhcl         = rowEl.dataset.vhcl;
    const dispatchCode = rowEl.dataset.dispatch;

    const modal = document.getElementById('lm-invoice-modal');
    const meta  = document.getElementById('lm-modal-meta');
    const tbody = document.getElementById('lm-invoice-tbody');

    const dateLabel = (function(){
        try {
            const d = new Date(date);
            if (!isNaN(d.getTime())) {
                return d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' });
            }
        } catch(e){}
        return date || '-';
    })();

    meta.innerHTML = '<strong>Tanggal:</strong> ' + dateLabel
                   + ' &nbsp;·&nbsp; <strong>Driver:</strong> ' + (driverName || driverCode)
                   + ' &nbsp;·&nbsp; <strong>Kendaraan:</strong> ' + (vhcl || '-')
                   + ' &nbsp;·&nbsp; <strong>Dispatch:</strong> <span style="font-family:monospace;">' + (dispatchCode || '-') + '</span>';
    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:24px;">Memuat...</td></tr>';
    modal.classList.add('visible');

    const params = new URLSearchParams({
        date:          date,
        driver_code:   driverCode,
        vhcl_code:     vhcl || '',
        dispatch_code: dispatchCode || '',
    });

    fetch(lmInvoiceUrl + '?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.error) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#dc2626;padding:24px;">'
                            + data.error + '</td></tr>';
            return;
        }
        const rows = data.rows || [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:24px;">Tidak ada invoice.</td></tr>';
            return;
        }
        const fmtNum = function(v) {
            const n = parseFloat(v);
            if (isNaN(n)) return v ?? '';
            return n.toLocaleString('id-ID');
        };
        const statusClass = function(s) {
            const u = (s || '').toString().trim().toUpperCase();
            if (u === 'DELIVERED') return 'lm-status-delivered';
            if (u === 'CANCEL')    return 'lm-status-cancel';
            return 'lm-status-other';
        };
        tbody.innerHTML = rows.map(function(r) {
            const reasonText = (r.dpch_resaon ?? '').toString().trim();
            return '<tr>'
                 + '<td>' + (r.dpcth_so ?? '') + '</td>'
                 + '<td><span class="lm-status-badge ' + statusClass(r.dpch_status) + '">'
                       + ((r.dpch_status ?? '') || '-') + '</span></td>'
                 + '<td style="text-align:right;">' + fmtNum(r.dpch_value) + '</td>'
                 + '<td>' + (reasonText !== '' ? reasonText : '<span style="color:#9ca3af;">-</span>') + '</td>'
                 + '</tr>';
        }).join('');
    })
    .catch(function() {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#dc2626;padding:24px;">Gagal memuat data.</td></tr>';
    });
}

function closeInvoiceModal() {
    document.getElementById('lm-invoice-modal').classList.remove('visible');
}

function openCancelDetailModal(rowEl) {
    const reason = rowEl.dataset.reason;
    const modal  = document.getElementById('lm-cancel-modal');
    const meta   = document.getElementById('lm-cancel-meta');
    const tbody  = document.getElementById('lm-cancel-tbody');

    meta.innerHTML = '<strong>Reason:</strong> '
                   + (reason === '(Tanpa Reason)'
                        ? '<em style="color:#b91c1c;">(Tanpa Reason)</em>'
                        : reason)
                   + ' &nbsp;·&nbsp; <strong>Periode:</strong> ' + lmSpkRange + ' hari terakhir';
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:24px;">Memuat...</td></tr>';
    modal.classList.add('visible');

    const params = new URLSearchParams({
        reason:    reason || '',
        spk_range: String(lmSpkRange),
    });

    fetch(lmCancelDetailUrl + '?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.error) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#dc2626;padding:24px;">'
                            + data.error + '</td></tr>';
            return;
        }
        const rows = data.rows || [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:24px;">Tidak ada data.</td></tr>';
            return;
        }
        const fmtDate = function(v) {
            if (!v) return '-';
            try {
                const d = new Date(v);
                if (!isNaN(d.getTime())) {
                    return d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' });
                }
            } catch(e){}
            return v;
        };
        tbody.innerHTML = rows.map(function(r) {
            const driver = (r.driver_name && r.driver_name.trim())
                ? r.driver_name + ' <span style="color:#9ca3af;font-size:.72rem;">(' + (r.driver_code || '') + ')</span>'
                : (r.driver_code || '-');
            const reasonCell = (r.dpch_resaon && r.dpch_resaon.trim())
                ? r.dpch_resaon
                : '<em style="color:#b91c1c;">(kosong)</em>';
            return '<tr>'
                 + '<td>' + fmtDate(r.dispatch_date) + '</td>'
                 + '<td style="font-family:monospace;font-size:.78rem;">' + (r.dispatch_code || '-') + '</td>'
                 + '<td>' + (r.dpcth_so || '-') + '</td>'
                 + '<td>' + driver + '</td>'
                 + '<td>' + reasonCell + '</td>'
                 + '</tr>';
        }).join('');
    })
    .catch(function() {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#dc2626;padding:24px;">Gagal memuat data.</td></tr>';
    });
}

function closeCancelDetailModal() {
    document.getElementById('lm-cancel-modal').classList.remove('visible');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeInvoiceModal();
    if (e.key === 'Escape') closeCancelDetailModal();
});
</script>
@endsection
