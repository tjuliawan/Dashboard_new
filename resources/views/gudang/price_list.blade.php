@extends('layouts.user_type.auth')

@section('title', 'Price List')

@section('css')
<style>
    :root {
        --pl-bg-page:    #7491ad;
        --pl-bg-card:    #ffffff;
        --pl-bg-soft:    #f5f7fa;
        --pl-border:     #d9dee5;
        --pl-text:       #1f2937;
        --pl-muted:      #6b7280;
        --pl-heading:    #111827;
        --pl-primary:    #1e3a8a;
        --pl-primary-h:  #1e40af;
    }
    body { background: var(--pl-bg-page) !important; }

    .pl-wrap {
        display: grid;
        grid-template-columns: 1fr;
        gap: 22px;
        align-items: start;
    }

    .pl-card {
        background: var(--pl-bg-card);
        border: 1px solid var(--pl-border);
        border-radius: 8px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(15,23,42,.07);
        color: var(--pl-text);
    }

    .pl-title {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--pl-heading);
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--pl-border);
    }

    .pl-form { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 12px; margin-bottom: 12px; }
    .pl-form .pl-full { grid-column: 1 / -1; }
    .pl-form label { font-size: .72rem; color: var(--pl-muted); margin-bottom: 2px; display:block; text-transform: uppercase; letter-spacing:.4px; }
    .pl-form .form-control,
    .pl-form select.form-control { font-size: .82rem; padding: 5px 8px; height: auto; border-radius: 6px; }

    .pl-actions { display:flex; gap:8px; justify-content:flex-end; margin-bottom: 10px; }
    .pl-btn { background: var(--pl-primary); color:#fff; border:none; padding:6px 14px; border-radius:6px; font-size:.78rem; font-weight:600; cursor:pointer; }
    .pl-btn:hover { background: var(--pl-primary-h); }
    .pl-btn-ghost { background:#fff; color: var(--pl-primary); border: 1px solid var(--pl-primary); }

    .pl-table-wrap { max-height: 460px; overflow:auto; border:1px solid var(--pl-border); border-radius:8px; }
    table.pl-table { width: 100%; border-collapse: collapse; font-size: .78rem; }
    table.pl-table thead th {
        position: sticky; top: 0; z-index: 1;
        background: var(--pl-bg-soft); color: var(--pl-heading);
        text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--pl-border);
        white-space: nowrap;
    }
    table.pl-table tbody td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
    table.pl-table tbody tr:hover { background: #f8fbff; }
    table.pl-table .num { text-align: right; font-variant-numeric: tabular-nums; }
    .pl-empty { text-align:center; color: var(--pl-muted); padding: 22px 8px; font-style: italic; }
    .pl-pager { margin-top: 8px; }
    .pl-pager .pagination { margin: 0; flex-wrap: wrap; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="pl-wrap">

        {{-- =====================================================================
             Panel 1 : Update by Supplier  (FORM SAJA, tanpa tabel)
             Mirror C# TGU_Pricelist_Management → simpan harga baru per
             Branch + Supplier + SKU + SKU Supplier ke tr_TGUPriceList(_d).
        ====================================================================== --}}
        {{-- DISABLED: Panel "Update by Supplier" di-comment sesuai permintaan.
        <div class="pl-card">
            <div class="pl-title">Update by Supplier</div>

            @if (session('pl_success'))
                <div class="alert alert-success py-2 mb-2" style="font-size:.82rem;">
                    {{ session('pl_success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger py-2 mb-2" style="font-size:.82rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('gudang.price-list.update') }}">
                @csrf
                <div class="pl-form">
                    <div>
                        <label>Branch *</label>
                        <select name="branch" class="form-control" required>
                            <option value="">-- Pilih Branch --</option>
                            @foreach ($cabangList as $c)
                                <option value="{{ $c->cab_code }}" @selected(old('branch') === $c->cab_code)>
                                    {{ $c->cab_desc }} ({{ $c->cab_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Supplier *</label>
                        <select name="supplier" class="form-control" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($supplierList as $s)
                                <option value="{{ $s->supp_code }}" @selected(old('supplier') === $s->supp_code)>
                                    {{ $s->supp_desc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Principal / Business *</label>
                        <input type="text" name="principal" class="form-control"
                               value="{{ old('principal') }}" required placeholder="Heinz / Trading / dst">
                    </div>
                    <div>
                        <label>Type *</label>
                        <input type="text" name="type" class="form-control"
                               value="{{ old('type', 'by Supplier') }}" required>
                    </div>
                    <div class="pl-full">
                        <label>Description *</label>
                        <input type="text" name="description" class="form-control" maxlength="50"
                               value="{{ old('description') }}" required placeholder="Catatan pricelist (max 50 char)">
                    </div>
                    <div>
                        <label>SKU *</label>
                        <input type="text" name="sku_code" class="form-control"
                               value="{{ old('sku_code') }}" required placeholder="SKU Internal">
                    </div>
                    <div>
                        <label>SKU Supplier *</label>
                        <input type="text" name="sku_supplier" class="form-control"
                               value="{{ old('sku_supplier') }}" required placeholder="SKU dari Supplier">
                    </div>
                    <div>
                        <label>Price PCS *</label>
                        <input type="number" step="0.01" min="0" name="price_pcs" class="form-control"
                               value="{{ old('price_pcs', 0) }}" required>
                    </div>
                    <div>
                        <label>Price CTN</label>
                        <input type="number" step="0.01" min="0" name="price_ctn" class="form-control"
                               value="{{ old('price_ctn', 0) }}">
                    </div>
                    <div>
                        <label>Price PCS Jual</label>
                        <input type="number" step="0.01" min="0" name="price_pcs_jual" class="form-control"
                               value="{{ old('price_pcs_jual', 0) }}">
                    </div>
                    <div>
                        <label>Price CTN Jual</label>
                        <input type="number" step="0.01" min="0" name="price_ctn_jual" class="form-control"
                               value="{{ old('price_ctn_jual', 0) }}">
                    </div>
                    <div>
                        <label>MOQ</label>
                        <input type="text" name="MOQ" class="form-control"
                               value="{{ old('MOQ') }}">
                    </div>
                    <div>
                        <label>Unit Code</label>
                        <input type="text" name="ms_unit_code" class="form-control"
                               value="{{ old('ms_unit_code') }}">
                    </div>
                    <div>
                        <label>Price Mode Code</label>
                        <input type="text" name="MsPriceMode_code" class="form-control"
                               value="{{ old('MsPriceMode_code') }}">
                    </div>
                    <div>
                        <label>Ongkos Angkut</label>
                        <input type="number" step="0.01" min="0" name="OngkosAngkut" class="form-control"
                               value="{{ old('OngkosAngkut', 0) }}">
                    </div>
                    <div>
                        <label>Exp Date</label>
                        <input type="date" name="ExpDate" class="form-control"
                               value="{{ old('ExpDate') }}">
                    </div>
                    <div>
                        <label>Date Start *</label>
                        <input type="date" name="date_start" class="form-control"
                               value="{{ old('date_start', now()->toDateString()) }}" required>
                    </div>
                </div>

                <div class="pl-actions">
                    <button type="reset" class="pl-btn pl-btn-ghost">Clear</button>
                    <button type="submit" class="pl-btn">Save Price</button>
                </div>
            </form>
        </div>
        --}}

        {{-- =====================================================================
             Panel 2 : Price List / by SKU
             Mirror C# textE_SKU_EditValueChanged / textE_Product_EditValueChanged
                       / btnSearchSKU_Click
        ====================================================================== --}}
        <div class="pl-card">
            <div class="pl-title">Price List / by SKU</div>

            <form method="GET" action="{{ route('gudang.price-list') }}">
                <div class="pl-form">
                    <div>
                        <label>Branch</label>
                        <select name="sku_branch" class="form-control">
                            <option value="">-- Semua --</option>
                            @foreach ($cabangList as $c)
                                <option value="{{ $c->cab_code }}" @selected($skuFilters['sku_branch'] === $c->cab_code)>
                                    {{ $c->cab_desc }} ({{ $c->cab_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Supplier</label>
                        <select name="sku_supplier" class="form-control">
                            <option value="">-- Semua --</option>
                            @foreach ($supplierList as $s)
                                <option value="{{ $s->supp_code }}" @selected($skuFilters['sku_supplier'] === $s->supp_code)>
                                    {{ $s->supp_desc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>SKU</label>
                        <input type="text" name="sku_code" class="form-control"
                               value="{{ $skuFilters['sku_code'] }}" placeholder="SKU / SKU Supplier">
                    </div>
                    <div>
                        <label>Product / Description</label>
                        <input type="text" name="sku_desc" class="form-control"
                               value="{{ $skuFilters['sku_desc'] }}" placeholder="ketik nama produk...">
                    </div>
                    <div>
                        <label>Per Page</label>
                        <select name="sku_per_page" class="form-control">
                            @foreach ([25,50,100,200] as $pp)
                                <option value="{{ $pp }}" @selected((int)$skuFilters['sku_per_page']===$pp)>{{ $pp }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pl-actions">
                    <a href="{{ route('gudang.price-list') }}" class="pl-btn pl-btn-ghost">Reset</a>
                    <button type="submit" class="pl-btn">Search SKU</button>
                </div>
            </form>

            <div class="pl-table-wrap">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Branch</th>
                            <th>SKU</th>
                            <th>SKU Supplier</th>
                            <th>Product</th>
                            <th>Unit</th>
                            <th class="num">Price</th>
                            <th class="num">Price CTN</th>
                            <th>MOQ</th>
                            <th>Supplier</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($skuRows as $r)
                            <tr>
                                <td>{{ $r['no'] }}</td>
                                <td>{{ $r['cab_desc'] }}</td>
                                <td>{{ $r['SKU_code'] }}</td>
                                <td>{{ $r['SKU_Supplier'] }}</td>
                                <td>{{ $r['description'] }}</td>
                                <td>{{ $r['unit_desc'] }}</td>
                                <td class="num">{{ number_format((float)$r['Price_pcs'], 2) }}</td>
                                <td class="num">{{ number_format((float)$r['Price_ctn'], 2) }}</td>
                                <td>{{ $r['moq'] }}</td>
                                <td>{{ $r['supp_desc'] }}</td>
                                <td>
                                    @if ($r['date'])
                                        {{ \Carbon\Carbon::parse($r['date'])->format('d-m-Y') }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="pl-empty">Belum ada data. Isi Branch / Supplier / SKU / Product lalu klik Search SKU.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($skuPaginator)
                <div class="pl-pager">{{ $skuPaginator->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
