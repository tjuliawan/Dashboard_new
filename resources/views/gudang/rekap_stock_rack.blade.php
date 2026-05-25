@extends('layouts.user_type.auth')

@section('title', 'Rekap Stock Rack')

@section('css')
<style>
    :root {
        --rsr-bg-page:    #7491ad;
        --rsr-bg-card:    #ffffff;
        --rsr-bg-soft:    #f5f7fa;
        --rsr-border:     #d9dee5;
        --rsr-text:       #1f2937;
        --rsr-muted:      #6b7280;
        --rsr-heading:    #111827;
        --rsr-primary:    #334155;
        --rsr-primary-h:  #1f2937;
        --rsr-accent:     #1f2937;
        --rsr-row-hover:  #f3f4f6;
    }

    body { background: var(--rsr-bg-page) !important; }

    .rsr-card {
        background: var(--rsr-bg-card);
        border: 1px solid var(--rsr-border);
        border-radius: 8px; padding: 20px 22px;
        box-shadow: 0 1px 3px rgba(15,23,42,.08);
        margin-bottom: 20px;
        color: var(--rsr-text);
    }
    .rsr-title {
        font-size: 0.85rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .5px; color: var(--rsr-heading); margin-bottom: 14px;
    }
    .rsr-filter {
        display: grid; grid-template-columns: repeat(4, minmax(160px, 1fr)) auto;
        gap: 12px; align-items: end;
    }
    @media (max-width: 1024px) {
        .rsr-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rsr-actions { grid-column: 1 / -1; }
    }
    @media (max-width: 600px) {
        .rsr-card { padding: 14px; border-radius: 10px; }
        .rsr-filter { grid-template-columns: 1fr; gap: 10px; }
        .rsr-actions { flex-wrap: wrap; }
        .rsr-btn, .rsr-btn-reset { flex: 1 1 auto; justify-content: center; text-align: center; }
        table.rsr-table { font-size: 0.78rem; }
        table.rsr-table thead th, table.rsr-table tbody td { padding: 7px 9px; }
    }
    .rsr-field { display: flex; flex-direction: column; gap: 4px; }
    .rsr-field label {
        font-size: 0.70rem; font-weight: 600; color: var(--rsr-muted);
        text-transform: uppercase; letter-spacing: .4px;
    }
    .rsr-field select, .rsr-field input {
        padding: 7px 10px;
        border: 1.5px solid var(--rsr-border);
        border-radius: 7px;
        font-size: 0.83rem;
        color: var(--rsr-text);
        background: var(--rsr-bg-soft);
        outline: none;
    }
    .rsr-field select:focus, .rsr-field input:focus {
        border-color: var(--rsr-primary);
        box-shadow: 0 0 0 3px rgba(59,130,246,.18);
    }
    .rsr-field select option { background: var(--rsr-bg-card); color: var(--rsr-text); }
    .rsr-field input::placeholder { color: #64748b; }

    .rsr-actions { display: flex; gap: 8px; }
    .rsr-btn {
        background: var(--rsr-primary); color: #fff; border: none; padding: 8px 16px;
        border-radius: 7px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
        transition: background .15s;
    }
    .rsr-btn:hover { background: var(--rsr-primary-h); }
    .rsr-btn-reset {
        background: transparent; color: var(--rsr-muted);
        border: 1px solid var(--rsr-border);
        padding: 8px 14px; border-radius: 7px; font-size: 0.82rem;
        cursor: pointer; text-decoration: none; display: inline-flex; align-items: center;
        transition: all .15s;
    }
    .rsr-btn-reset:hover { color: var(--rsr-text); border-color: var(--rsr-primary); }

    .rsr-table-wrap { overflow-x: auto; }
    .rsr-scroll-top { overflow-x: auto; overflow-y: hidden; }
    .rsr-scroll-top > div { height: 1px; }
    table.rsr-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    table.rsr-table thead th {
        background: var(--rsr-bg-soft);
        padding: 10px 12px; text-align: left;
        font-weight: 600; color: var(--rsr-heading); font-size: 0.72rem;
        text-transform: uppercase; letter-spacing: .4px;
        border-bottom: 1px solid var(--rsr-border);
        white-space: nowrap;
    }
    table.rsr-table tbody tr { border-bottom: 1px solid var(--rsr-border); }
    table.rsr-table tbody tr:hover { background: var(--rsr-row-hover); }
    table.rsr-table tbody td {
        padding: 8px 12px; color: var(--rsr-text); white-space: nowrap;
    }
    table.rsr-table tbody td.num {
        text-align: right; font-variant-numeric: tabular-nums; color: var(--rsr-accent);
    }
    .rsr-empty {
        padding: 28px; text-align: center; color: var(--rsr-muted);
        font-size: 0.86rem;
    }
    .rsr-summary { font-size: 0.78rem; color: var(--rsr-muted); margin-top: 10px; }

    /* Pagination (Bootstrap) di tema gelap */
    .pagination .page-link {
        background: var(--rsr-bg-soft);
        border-color: var(--rsr-border);
        color: var(--rsr-text);
    }
    .pagination .page-link:hover { background: var(--rsr-row-hover); color: var(--rsr-accent); }
    .pagination .page-item.active .page-link {
        background: var(--rsr-primary);
        border-color: var(--rsr-primary);
        color: #fff;
    }
    .pagination .page-item.disabled .page-link {
        background: var(--rsr-bg-card);
        border-color: var(--rsr-border);
        color: #475569;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="rsr-card">
        <div class="rsr-title">Rekap Stock Rack</div>

        <form method="GET" action="{{ url('/gudang/rekap-stock-rack') }}" class="rsr-filter">
            <div class="rsr-field">
                <label>Branch / Cabang <span style="color:#dc2626">*</span></label>
                <select name="cab_code" required>
                    <option value="">-- Pilih Cabang --</option>
                    @foreach ($cabangList as $c)
                        <option value="{{ $c->cab_code }}" {{ $filters['cab_code'] === $c->cab_code ? 'selected' : '' }}>
                            {{ $c->cab_code }} — {{ $c->cab_desc }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="rsr-field">
                <label>Gudang (opsional)</label>
                <select name="gudang_code">
                    <option value="">-- Semua Gudang --</option>
                    @foreach ($gudangList as $g)
                        <option value="{{ $g->Gudang_code }}" {{ $filters['gudang_code'] === $g->Gudang_code ? 'selected' : '' }}>
                            {{ $g->Gudang_code }} — {{ $g->Gudang_desc }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="rsr-field">
                <label>Cari (Rack Internal / Rack Principal / SKU)</label>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Ketik rack internal, rack principal, atau SKU">
            </div>

            <div class="rsr-field">
                <label>Per Halaman</label>
                <select name="per_page">
                    @foreach ([25, 50, 100, 200] as $pp)
                        <option value="{{ $pp }}" {{ $filters['per_page'] === $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rsr-actions">
                <button type="submit" class="rsr-btn">Cari</button>
                <a href="{{ url('/gudang/rekap-stock-rack') }}" class="rsr-btn-reset">Reset</a>
            </div>
        </form>
    </div>

    <div class="rsr-card">
        <div class="rsr-title">Hasil</div>

        @if ($filters['cab_code'] === '')
            <div class="rsr-empty">Pilih cabang terlebih dahulu, lalu klik <b>Tampilkan</b>.</div>
        @elseif ($rows->isEmpty())
            <div class="rsr-empty">Tidak ada data untuk filter yang dipilih.</div>
        @else
            <div class="rsr-scroll-top" id="rsrScrollTop"><div></div></div>
            <div class="rsr-table-wrap" id="rsrTableWrap">
                <table class="rsr-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Rack Internal</th>
                            <th>Rack Principal</th>
                            <th>Bisnis</th>
                            <th>Branch</th>
                            <th>SKU</th>
                            <th>Order Code</th>
                            <th>Description</th>
                            <th class="num">Stock Akhir</th>
                            <th>Convert</th>
                            <th>Exp Date</th>
                            <th class="num">Perhitungan user</th>
                            <th class="num">Price</th>
                            <th class="num">Value</th>
                            <th class="num">COGS Pcs</th>
                            <th class="num">COGS Ctn</th>
                            <th class="num">Last COGS Pcs</th>
                            <th class="num">COGS Harga Terakhir Pcs</th>
                            <th class="num">COGS Harga Terakhir Ctn</th>
                            <th class="num">COGS Price List Pcs</th>
                            <th class="num">COGS Price List Ctn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = $paginator
                                ? (($paginator->currentPage() - 1) * $paginator->perPage()) + 1
                                : 1;
                        @endphp
                        @foreach ($rows as $r)
                            <tr>
                                <td>{{ $r['no'] ?? $no++ }}</td>
                                <td>{{ $r['rack_internal_code'] }}</td>
                                <td>{{ $r['rack_principal_code'] }}</td>
                                <td>{{ $r['rack_business'] }}</td>
                                <td>{{ $r['rack_branch'] }}</td>
                                <td>{{ $r['ms_product_code'] }}</td>
                                <td>{{ $r['ms_product_business_order_code'] }}</td>
                                <td>{{ $r['description'] }}</td>
                                <td class="num">{{ number_format((float) $r['Stock_akhir'], 0, ',', '.') }}</td>
                                <td>{{ $r['skuconvert'] }}</td>
                                <td>{{ $r['exp_date_in'] ? \Carbon\Carbon::parse($r['exp_date_in'])->format('d-m-Y') : '' }}</td>
                                <td class="num">
                                    {{ $r['usr_cnt_last_stock'] !== null ? number_format((float) $r['usr_cnt_last_stock'], 0, ',', '.') : '' }}
                                </td>
                                <td class="num">{{ $r['price'] !== null ? number_format((float) $r['price'], 0, ',', '.') : '' }}</td>
                                <td class="num">{{ $r['value'] !== null ? number_format((float) $r['value'], 0, ',', '.') : '' }}</td>
                                <td class="num">{{ $r['cogs_pcs']                !== null ? number_format((float) $r['cogs_pcs'],                0, ',', '.') : '' }}</td>
                                <td class="num">{{ $r['cogs_ctn']                !== null ? number_format((float) $r['cogs_ctn'],                0, ',', '.') : '' }}</td>
                                <td class="num">{{ $r['last_cogs_pcs']           !== null ? number_format((float) $r['last_cogs_pcs'],           0, ',', '.') : '' }}</td>
                                <td class="num">{{ $r['cogs_harga_terakhir_pcs'] !== null ? number_format((float) $r['cogs_harga_terakhir_pcs'], 0, ',', '.') : '' }}</td>
                                <td class="num">{{ $r['cogs_harga_terakhir_ctn'] !== null ? number_format((float) $r['cogs_harga_terakhir_ctn'], 0, ',', '.') : '' }}</td>
                                <td class="num">{{ $r['cogs_price_list_pcs']     !== null ? number_format((float) $r['cogs_price_list_pcs'],     0, ',', '.') : '' }}</td>
                                <td class="num">{{ $r['cogs_price_list_ctn']     !== null ? number_format((float) $r['cogs_price_list_ctn'],     0, ',', '.') : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($paginator)
                <div class="rsr-summary">
                    Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
                    dari {{ number_format($paginator->total(), 0, ',', '.') }} baris.
                </div>
                <div class="mt-2">
                    {{ $paginator->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<script>
    (function () {
        const wrap = document.getElementById('rsrTableWrap');
        const top  = document.getElementById('rsrScrollTop');
        if (!wrap || !top) return;
        const inner = top.firstElementChild;
        function sync() {
            const tbl = wrap.querySelector('table');
            if (!tbl) return;
            inner.style.width = tbl.scrollWidth + 'px';
            top.style.display = tbl.scrollWidth > wrap.clientWidth ? 'block' : 'none';
        }
        let lock = false;
        top.addEventListener('scroll', function () {
            if (lock) return; lock = true; wrap.scrollLeft = top.scrollLeft; lock = false;
        });
        wrap.addEventListener('scroll', function () {
            if (lock) return; lock = true; top.scrollLeft = wrap.scrollLeft; lock = false;
        });
        window.addEventListener('resize', sync);
        sync();
    })();
</script>
@endsection
