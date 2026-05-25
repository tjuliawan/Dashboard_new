@extends('layouts.user_type.auth')

@section('title', 'Track In/Out')

@section('css')
<style>
    .tio-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 22px;
        box-shadow: 0 1px 3px rgba(15,23,42,.07);
    }
    .tio-title {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #111827;
        margin-bottom: 14px;
    }
    .tio-trigger {
        background: #1e3a8a;
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }
    .tio-trigger:hover { background: #1e40af; }

    /* Tombol di dalam modal */
    .tio-modal-actions {
        display: flex;
        gap: 16px;
        justify-content: center;
        padding: 8px 0 4px;
    }
    .tio-btn {
        flex: 1 1 0;
        max-width: 180px;
        padding: 18px 0;
        border-radius: 10px;
        border: none;
        color: #fff;
        font-weight: 700;
        font-size: 1rem;
        letter-spacing: .5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform .12s, filter .12s;
    }
    .tio-btn:hover { transform: translateY(-1px); filter: brightness(1.05); }
    .tio-btn-in  { background: #16a34a; }   /* green */
    .tio-btn-out { background: #dc2626; }   /* red   */
    .tio-btn i   { font-size: 1.1rem; }

    /* Tabel hasil */
    .tio-table-wrap { overflow-x: auto; margin-top: 8px; }
    .tio-scroll-top {
        overflow-x: scroll;
        overflow-y: hidden;
        height: 16px;
        margin-bottom: 4px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 6px;
    }
    .tio-scroll-top > div { height: 1px; }
    /* Paksa scrollbar selalu kelihatan (WebKit) */
    .tio-scroll-top::-webkit-scrollbar,
    .tio-table-wrap::-webkit-scrollbar { height: 12px; }
    .tio-scroll-top::-webkit-scrollbar-thumb,
    .tio-table-wrap::-webkit-scrollbar-thumb {
        background: #9ca3af; border-radius: 6px;
    }
    table.tio-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.83rem;
    }
    table.tio-table thead th {
        background: #f5f7fa;
        color: #111827;
        font-weight: 600;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .4px;
        padding: 10px 12px;
        text-align: left;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }
    table.tio-table tbody td {
        padding: 8px 12px;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    table.tio-table tbody tr:hover { background: #f3f4f6; }
    .tio-link {
        color: #1e3a8a;
        text-decoration: none;
        font-weight: 600;
    }
    .tio-link:hover { text-decoration: underline; color: #1e40af; }
    .tio-empty {
        padding: 24px;
        text-align: center;
        color: #5b6b86;
        font-size: 0.86rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="tio-card">
        <div class="tio-title">Track In / Out</div>

        <p class="text-sm text-secondary mb-3">
            Klik tombol di bawah untuk memilih jenis transaksi gudang.
        </p>

        <button type="button"
                class="tio-trigger"
                data-bs-toggle="modal"
                data-bs-target="#trackInOutModal">
            <i class="fas fa-exchange-alt me-1"></i> Pilih Aksi
        </button>
    </div>

    {{-- ===================== PANEL IN (Rekap Stock Masuk) ===================== --}}
    <div class="tio-card mt-3" id="panelIn" style="display:none;">
        <div class="tio-title">
            <span class="badge bg-success me-2">IN</span>
            Daftar Transaksi Masuk
        </div>

        <form method="GET" action="{{ url('/gudang/track-in-out') }}"
              class="d-flex flex-wrap gap-2 align-items-end mb-3">
            <div class="d-flex flex-column">
                <label class="text-xs text-secondary mb-1" style="text-transform:uppercase;letter-spacing:.4px;">
                    Cari (semua kolom)
                </label>
                <input type="text" name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Ketik untuk mencari di seluruh kolom IN"
                       style="padding:7px 10px;border:1.5px solid #c9dcff;border-radius:7px;font-size:.83rem;background:#eef4ff;min-width:280px;">
            </div>
            <div class="d-flex flex-column">
                <label class="text-xs text-secondary mb-1" style="text-transform:uppercase;letter-spacing:.4px;">
                    Dari Tanggal (PO)
                </label>
                <input type="date" name="date_from"
                       value="{{ $filters['date_from'] ?? '' }}"
                       style="padding:7px 10px;border:1.5px solid #c9dcff;border-radius:7px;font-size:.83rem;background:#eef4ff;">
            </div>
            <div class="d-flex flex-column">
                <label class="text-xs text-secondary mb-1" style="text-transform:uppercase;letter-spacing:.4px;">
                    Sampai Tanggal (PO)
                </label>
                <input type="date" name="date_to"
                       value="{{ $filters['date_to'] ?? '' }}"
                       style="padding:7px 10px;border:1.5px solid #c9dcff;border-radius:7px;font-size:.83rem;background:#eef4ff;">
            </div>
            <div class="d-flex flex-column">
                <label class="text-xs text-secondary mb-1" style="text-transform:uppercase;letter-spacing:.4px;">
                    Per Halaman
                </label>
                <select name="per_page"
                        style="padding:7px 10px;border:1.5px solid #c9dcff;border-radius:7px;font-size:.83rem;background:#eef4ff;">
                    @foreach ([25, 50, 100, 200] as $pp)
                        <option value="{{ $pp }}"
                            {{ (int)($filters['per_page'] ?? 50) === $pp ? 'selected' : '' }}>
                            {{ $pp }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="tio-trigger" style="padding:8px 16px;">Cari</button>
            <a href="{{ url('/gudang/track-in-out') }}"
               class="btn btn-outline-secondary"
               style="padding:8px 14px;font-size:.82rem;">Reset</a>
            <button type="button" id="btnPrintIn"
                    class="btn btn-outline-primary"
                    style="padding:8px 14px;font-size:.82rem;">
                <i class="tio-print"></i> Print
            </button>
            <button type="button" id="btnExportIn"
                    class="btn btn-outline-success"
                    style="padding:8px 14px;font-size:.82rem;">
                <i class="tio-download"></i> Export Excel
            </button>
        </form>

        <div class="tio-table-wrap">
            <table class="tio-table" id="trackInOutTable">
                <thead>
                    <tr>
                        <th>Nomor PO</th>
                        <th>Tgl PO</th>
                        <th>Tallysheet</th>
                        <th>Tgl Tallysheet</th>
                        <th>BTB</th>
                        <th>Tgl BTB</th>
                        <th>Putaway</th>
                        <th style="display:none;">Qty</th>
                        <th style="text-align:center;">Export Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rows = $rows ?? []; @endphp
                    @forelse ($rows as $row)
                        <tr>
                            <td><a href="#" class="tio-link tio-link-po"
                                   data-value="{{ $row->po_no ?? '' }}">{{ $row->po_no ?? '' }}</a></td>
                            <td>{{ $row->po_date ?? '' }}</td>
                            <td><a href="#" class="tio-link tio-link-tls"
                                   data-value="{{ $row->tallysheet_no ?? '' }}">{{ $row->tallysheet_no ?? '' }}</a></td>
                            <td>{{ $row->tallysheet_date ?? '' }}</td>
                            <td><a href="#" class="tio-link tio-link-btb"
                                   data-value="{{ $row->btb_no ?? '' }}">{{ $row->btb_no ?? '' }}</a></td>
                            <td>{{ $row->btb_date ?? '' }}</td>
                            <td><a href="#" class="tio-link tio-link-putaway"
                                   data-value="{{ $row->tallysheet_no ?? '' }}">{{ $row->putaway ?? '' }}</a></td>
                            <td style="display:none;">{{ $row->qty ?? '' }}</td>
                            <td style="text-align:center;white-space:nowrap;">
                                @if(!empty($row->po_no))
                                <a href="{{ route('gudang.track-in-out.export-row-detail', ['po' => $row->po_no, 'tls' => $row->tallysheet_no ?? '', 'btb' => $row->btb_no ?? '', 'putaway' => $row->putaway ?? '']) }}"
                                   class="btn btn-sm btn-outline-success py-0 px-2"
                                   style="font-size:.75rem;"
                                   title="Export semua detail baris ini ke Excel">
                                    <i class="tio-download"></i> Excel
                                </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="tio-empty">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @isset($paginator)
            <div class="mt-3">
                {{ $paginator->links() }}
            </div>
        @endisset
    </div>

    {{-- ===================== PANEL OUT (Barang Keluar) ===================== --}}
    <div class="tio-card mt-3" id="panelOut" style="display:none;">
        <div class="tio-title">
            <span class="badge bg-danger me-2">OUT</span>
            Daftar Transaksi Keluar
        </div>

        <form method="GET" action="{{ url('/gudang/track-in-out') }}"
              class="d-flex flex-wrap gap-2 align-items-end mb-3">
            <div class="d-flex flex-column">
                <label class="text-xs text-secondary mb-1" style="text-transform:uppercase;letter-spacing:.4px;">
                    Cari (semua kolom)
                </label>
                <input type="text" name="search_out"
                       value="{{ $filters['search_out'] ?? '' }}"
                       placeholder="Ketik untuk mencari di seluruh kolom OUT"
                       style="padding:7px 10px;border:1.5px solid #c9dcff;border-radius:7px;font-size:.83rem;background:#eef4ff;min-width:280px;">
            </div>
            <div class="d-flex flex-column">
                <label class="text-xs text-secondary mb-1" style="text-transform:uppercase;letter-spacing:.4px;">
                    Dari Tanggal (Request)
                </label>
                <input type="date" name="date_from_out"
                       value="{{ $filters['date_from_out'] ?? '' }}"
                       style="padding:7px 10px;border:1.5px solid #c9dcff;border-radius:7px;font-size:.83rem;background:#eef4ff;">
            </div>
            <div class="d-flex flex-column">
                <label class="text-xs text-secondary mb-1" style="text-transform:uppercase;letter-spacing:.4px;">
                    Sampai Tanggal (Request)
                </label>
                <input type="date" name="date_to_out"
                       value="{{ $filters['date_to_out'] ?? '' }}"
                       style="padding:7px 10px;border:1.5px solid #c9dcff;border-radius:7px;font-size:.83rem;background:#eef4ff;">
            </div>
            <div class="d-flex flex-column">
                <label class="text-xs text-secondary mb-1" style="text-transform:uppercase;letter-spacing:.4px;">
                    Per Halaman
                </label>
                <select name="per_page_out"
                        style="padding:7px 10px;border:1.5px solid #c9dcff;border-radius:7px;font-size:.83rem;background:#eef4ff;">
                    @foreach ([25, 50, 100, 200] as $pp)
                        <option value="{{ $pp }}"
                            {{ (int)($filters['per_page_out'] ?? 50) === $pp ? 'selected' : '' }}>
                            {{ $pp }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="tio-trigger" style="padding:8px 16px;">Cari</button>
            <a href="{{ url('/gudang/track-in-out') }}"
               class="btn btn-outline-secondary"
               style="padding:8px 14px;font-size:.82rem;">Reset</a>
            <button type="button" id="btnExportOut"
                    class="btn btn-outline-success"
                    style="padding:8px 14px;font-size:.82rem;">
                <i class="tio-download"></i> Export Excel
            </button>
        </form>

        <div class="tio-table-wrap">
            <table class="tio-table" id="trackOutTable">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Tgl Request</th>
                        <th>Picking</th>
                        <th>Tgl Picking</th>
                        <th>BKB</th>
                        <th>Tgl BKB</th>
                        <th>Dispatch</th>
                        <th>Tgl Dispatch</th>
                        <th>POD</th>
                        <th>Tgl POD</th>
                        <th>BTB RV</th>
                        <th>Tgl BTB RV</th>
                        <th>Payment Kasir</th>
                        <th>Tgl Payment Kasir</th>
                        <th style="text-align:center;">Export Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowsOut = $rowsOut ?? []; @endphp
                    @forelse ($rowsOut as $row)
                        <tr>
                            <td><a href="#" class="tio-link tio-link-out-req"
                                   data-value="{{ $row->request_no ?? '' }}">{{ $row->request_no ?? '' }}</a></td>
                            <td>{{ $row->request_date ?? '' }}</td>
                            <td><a href="#" class="tio-link tio-link-out-picking"
                                   data-value="{{ $row->picking_no ?? '' }}">{{ $row->picking_no ?? '' }}</a></td>
                            <td>{{ $row->picking_date ?? '' }}</td>
                            <td><a href="#" class="tio-link tio-link-out-bkb"
                                   data-value="{{ $row->bkb_no ?? '' }}">{{ $row->bkb_no ?? '' }}</a></td>
                            <td>{{ $row->bkb_date ?? '' }}</td>
                            <td><a href="#" class="tio-link tio-link-out-dispatch"
                                   data-value="{{ $row->dispatch_no ?? '' }}">{{ $row->dispatch_no ?? '' }}</a></td>
                            <td>{{ $row->dispatch_date ?? '' }}</td>
                            <td><a href="#" class="tio-link tio-link-out-pod"
                                   data-value="{{ $row->pod_no ?? '' }}">{{ $row->pod_no ?? '' }}</a></td>
                            <td>{{ $row->pod_date ?? '' }}</td>
                            <td><a href="#" class="tio-link tio-link-out-btbrv"
                                   data-value="{{ $row->btb_rv_no ?? '' }}">{{ $row->btb_rv_no ?? '' }}</a></td>
                            <td>{{ $row->btb_rv_date ?? '' }}</td>
                            <td><a href="#" class="tio-link tio-link-out-payment"
                                   data-value="{{ $row->dispatch_no ?? '' }}"
                                   data-label="{{ $row->payment_kasir_no ?? '' }}">{{ $row->payment_kasir_no ?? '' }}</a></td>
                            <td>{{ $row->payment_kasir_date ?? '' }}</td>
                            <td style="text-align:center;white-space:nowrap;">
                                @if(!empty($row->request_no))
                                <a href="{{ route('gudang.track-in-out.export-out-row-detail', ['req' => $row->request_no ?? '', 'picking' => $row->picking_no ?? '', 'bkb' => $row->bkb_no ?? '', 'dispatch' => $row->dispatch_no ?? '', 'pod' => $row->pod_no ?? '', 'btbrv' => $row->btb_rv_no ?? '']) }}"
                                   class="btn btn-sm btn-outline-danger py-0 px-2"
                                   style="font-size:.75rem;"
                                   title="Export semua detail baris ini ke Excel">
                                    <i class="tio-download"></i> Excel
                                </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="tio-empty">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @isset($paginatorOut)
            <div class="mt-3">
                {{ $paginatorOut->links() }}
            </div>
        @endisset
    </div>
</div>

{{-- Modal Detail PO --}}
<div class="modal fade" id="poDetailModal" tabindex="-1"
     aria-labelledby="poDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="poDetailModalLabel">
                    Detail PO <span id="poDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0" id="poDetailTable">
                        <thead class="table-light">
                            <tr>
                                <th>SKU Code</th>
                                <th>Description</th>
                                <th style="text-align:right;">Order Qty</th>
                                <th style="text-align:right;">Order Price</th>
                                <th style="text-align:right;">BUM (CTN)</th>
                                <th style="text-align:right;">BUM (PCS)</th>
                                <th style="text-align:right;">Order Price (PCS)</th>
                                <th style="text-align:right;">Order Price (CTN)</th>
                                <th>Order PO</th>
                            </tr>
                        </thead>
                        <tbody id="poDetailBody">
                            <tr><td colspan="9" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Tallysheet --}}
<div class="modal fade" id="tlsDetailModal" tabindex="-1"
     aria-labelledby="tlsDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tlsDetailModalLabel">
                    Detail Tallysheet <span id="tlsDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0" id="tlsDetailTable">
                        <thead class="table-light">
                            <tr>
                                <th>SKU Order</th>
                                <th>Barcode</th>
                                <th>Deskripsi</th>
                                <th style="text-align:right;">Qty CTN</th>
                                <th style="text-align:right;">PCS per CTN</th>
                                <th style="text-align:right;">Total PCS</th>
                            </tr>
                        </thead>
                        <tbody id="tlsDetailBody">
                            <tr><td colspan="6" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail BTB --}}
<div class="modal fade" id="btbDetailModal" tabindex="-1"
     aria-labelledby="btbDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="btbDetailModalLabel">
                    Detail BTB <span id="btbDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary mb-2" style="font-size:.8rem;font-style:italic;">
                    *value dihitung dari harga pcs
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0" id="btbDetailTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Expired Date</th>
                                <th style="text-align:right;">QTY In</th>
                                <th style="text-align:right;">QTY Karton</th>
                                <th style="text-align:right;">QTY PCS</th>
                                <th style="text-align:right;">Harga PCS</th>
                                <th style="text-align:right;">Harga CTN</th>
                                <th style="text-align:right;">Gross Value</th>
                                <th style="text-align:right;">Net Value</th>
                            </tr>
                        </thead>
                        <tbody id="btbDetailBody">
                            <tr><td colspan="9" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Print IN (pilih rentang tanggal PO) --}}
<div class="modal fade" id="printInModal" tabindex="-1"
     aria-labelledby="printInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printInModalLabel">Print Daftar Transaksi Masuk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary mb-3" style="font-size:.85rem;">
                    Pilih rentang <strong>Tanggal PO</strong> yang akan dicetak.
                    Kosongkan untuk mencetak seluruh data.
                </p>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:.8rem;">Dari Tanggal</label>
                        <input type="date" id="printInDateFrom" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:.8rem;">Sampai Tanggal</label>
                        <input type="date" id="printInDateTo" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnPrintInSubmit">
                    <i class="tio-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Export Excel (panel IN & OUT) --}}
<div class="modal fade" id="exportTioModal" tabindex="-1"
     aria-labelledby="exportTioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportTioModalLabel">Export Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary mb-3" style="font-size:.85rem;">
                    Pilih rentang tanggal yang akan diexport.
                    Kosongkan untuk mengexport seluruh data.
                </p>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:.8rem;">Dari Tanggal</label>
                        <input type="date" id="exportTioDateFrom" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:.8rem;">Sampai Tanggal</label>
                        <input type="date" id="exportTioDateTo" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnExportTioSubmit">
                    <i class="tio-download"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Putaway --}}
<div class="modal fade" id="putawayDetailModal" tabindex="-1"
     aria-labelledby="putawayDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="putawayDetailModalLabel">
                    Detail Putaway <span id="putawayDetailCode" style="display:none;"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0" id="putawayDetailTable">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Rack</th>
                                <th>Kode Product</th>
                                <th>Deskripsi</th>
                                <th style="text-align:right;">QTY Out</th>
                                <th style="text-align:right;">QTY In</th>
                                <th style="text-align:right;">Stock Akhir</th>
                                <th>Tipe Transaksi</th>
                            </tr>
                        </thead>
                        <tbody id="putawayDetailBody">
                            <tr><td colspan="7" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Modal Placeholder Panel OUT (Request / Picking / BKB / Dispatch / POD / BTB RV / Payment Kasir) ===== --}}
@php
    $outModals = [
        ['id' => 'outReqDetailModal',      'code' => 'outReqDetailCode',      'title' => 'Detail Request', 'skip' => true],
        ['id' => 'outPickingDetailModal',  'code' => 'outPickingDetailCode',  'title' => 'Detail Picking', 'skip' => true],
        ['id' => 'outBkbDetailModal',      'code' => 'outBkbDetailCode',      'title' => 'Detail BKB', 'skip' => true],
        ['id' => 'outDispatchDetailModal', 'code' => 'outDispatchDetailCode', 'title' => 'Detail Dispatch', 'skip' => true],
        ['id' => 'outPodDetailModal',      'code' => 'outPodDetailCode',      'title' => 'Detail POD', 'skip' => true],
        ['id' => 'outBtbRvDetailModal',    'code' => 'outBtbRvDetailCode',    'title' => 'Detail BTB RV', 'skip' => true],
        ['id' => 'outPaymentDetailModal',  'code' => 'outPaymentDetailCode',  'title' => 'Detail Payment Kasir', 'skip' => true],
    ];
@endphp
{{-- Modal Detail Request (panel OUT) --}}
<div class="modal fade" id="outReqDetailModal" tabindex="-1"
     aria-labelledby="outReqDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outReqDetailModalLabel">
                    Detail Request <span id="outReqDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Deskripsi</th>
                                <th>Business Code</th>
                                <th>Order Code</th>
                                <th style="text-align:right;">Request QTY</th>
                            </tr>
                        </thead>
                        <tbody id="outReqDetailBody">
                            <tr><td colspan="5" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Picking (panel OUT) --}}
<div class="modal fade" id="outPickingDetailModal" tabindex="-1"
     aria-labelledby="outPickingDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outPickingDetailModalLabel">
                    Detail Picking <span id="outPickingDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rack</th>
                                <th style="text-align:right;">QTY</th>
                                <th style="text-align:right;">Stock Awal</th>
                                <th style="text-align:right;">Stock Akhir</th>
                                <th>Expired Date</th>
                                <th>Type Transaksi</th>
                                <th>Deskripsi</th>
                                <th>Kode Product</th>
                                <th>Supplier Code</th>
                                <th>Business Code</th>
                            </tr>
                        </thead>
                        <tbody id="outPickingDetailBody">
                            <tr><td colspan="10" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail BKB (panel OUT) --}}
<div class="modal fade" id="outBkbDetailModal" tabindex="-1"
     aria-labelledby="outBkbDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outBkbDetailModalLabel">
                    Detail BKB <span id="outBkbDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Deskripsi</th>
                                <th style="text-align:right;">QTY</th>
                                <th style="text-align:right;">Price</th>
                                <th style="text-align:right;">Stock Manual</th>
                                <th style="text-align:right;">Stock System</th>
                                <th style="text-align:right;">Price COGS</th>
                                <th style="text-align:right;">Price COGS (Last)</th>
                            </tr>
                        </thead>
                        <tbody id="outBkbDetailBody">
                            <tr><td colspan="8" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Dispatch (panel OUT) --}}
<div class="modal fade" id="outDispatchDetailModal" tabindex="-1"
     aria-labelledby="outDispatchDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outDispatchDetailModalLabel">
                    Detail Dispatch <span id="outDispatchDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Vehicle</th>
                                <th>SO</th>
                                <th>Product</th>
                                <th>Deskripsi</th>
                                <th style="text-align:right;">QTY</th>
                                <th>Satuan</th>
                                <th>Driver</th>
                                <th>Status</th>
                                <th>ETA</th>
                            </tr>
                        </thead>
                        <tbody id="outDispatchDetailBody">
                            <tr><td colspan="9" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail POD (panel OUT) --}}
<div class="modal fade" id="outPodDetailModal" tabindex="-1"
     aria-labelledby="outPodDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outPodDetailModalLabel">
                    Detail POD <span id="outPodDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Date</th>
                                <th style="text-align:right;">Value</th>
                                <th style="text-align:right;">Total Inv</th>
                                <th style="text-align:right;">Terkirim</th>
                                <th style="text-align:right;">Cancel</th>
                            </tr>
                        </thead>
                        <tbody id="outPodDetailBody">
                            <tr><td colspan="7" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail BTB RV (panel OUT) --}}
<div class="modal fade" id="outBtbRvDetailModal" tabindex="-1"
     aria-labelledby="outBtbRvDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outBtbRvDetailModalLabel">
                    Detail BTB RV <span id="outBtbRvDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rack</th>
                                <th>Product Code</th>
                                <th>Deskripsi</th>
                                <th style="text-align:right;">QTY In</th>
                                <th style="text-align:right;">QTY Out</th>
                                <th style="text-align:right;">Stock Akhir</th>
                                <th>Supplier Code</th>
                                <th>Business Code</th>
                            </tr>
                        </thead>
                        <tbody id="outBtbRvDetailBody">
                            <tr><td colspan="8" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Payment Kasir (panel OUT) --}}
<div class="modal fade" id="outPaymentDetailModal" tabindex="-1"
     aria-labelledby="outPaymentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outPaymentDetailModalLabel">
                    Detail Payment Kasir <span id="outPaymentDetailCode"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Dispatch Code</th>
                                <th>No SO</th>
                                <th>Pembayaran</th>
                                <th>Kode Retail</th>
                                <th>Status</th>
                                <th style="text-align:right;">Giro</th>
                                <th style="text-align:right;">Value</th>
                                <th>Branch</th>
                                <th style="text-align:right;">Value Validation</th>
                                <th style="text-align:right;">Repay</th>
                                <th style="text-align:right;">Pay Value</th>
                                <th>Validation Giro</th>
                                <th>Status Pengiriman</th>
                            </tr>
                        </thead>
                        <tbody id="outPaymentDetailBody">
                            <tr><td colspan="13" class="tio-empty">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@foreach ($outModals as $m)
@if (!empty($m['skip']))
@continue
@endif
<div class="modal fade" id="{{ $m['id'] }}" tabindex="-1"
     aria-labelledby="{{ $m['id'] }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $m['id'] }}Label">
                    {{ $m['title'] }} <span id="{{ $m['code'] }}"></span>
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary text-center mb-0">
                    Desain detail menyusul (mapping kolom akan diisi nanti).
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- Modal Track In / Out --}}
<div class="modal fade" id="trackInOutModal" tabindex="-1"
     aria-labelledby="trackInOutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trackInOutModalLabel">
                    Pilih Jenis Transaksi
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-center text-secondary mb-3">
                    Silakan pilih salah satu aksi:
                </p>
                <div class="tio-modal-actions">
                    <button type="button"
                            class="tio-btn tio-btn-in"
                            id="btnTrackIn">
                        <i class="fas fa-arrow-down"></i> IN
                    </button>
                    <button type="button"
                            class="tio-btn tio-btn-out"
                            id="btnTrackOut">
                        <i class="fas fa-arrow-up"></i> OUT
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnIn  = document.getElementById('btnTrackIn');
        const btnOut = document.getElementById('btnTrackOut');
        const panelIn  = document.getElementById('panelIn');
        const panelOut = document.getElementById('panelOut');
        const trackModalEl = document.getElementById('trackInOutModal');
        const trackModal   = trackModalEl ? bootstrap.Modal.getOrCreateInstance(trackModalEl) : null;

        function showPanel(mode) {
            if (panelIn)  panelIn.style.display  = (mode === 'in')  ? '' : 'none';
            if (panelOut) panelOut.style.display = (mode === 'out') ? '' : 'none';
            try { sessionStorage.setItem('tio_mode', mode); } catch (e) {}
            // Recalculate top scrollbar widths for newly-visible panels
            if (typeof window.tioSyncScrollbars === 'function') {
                window.tioSyncScrollbars();
            }
        }

        // Restore last selected panel (IN by default jika ada query/search aktif)
        let initial = null;
        try { initial = sessionStorage.getItem('tio_mode'); } catch (e) {}
        const params = new URLSearchParams(window.location.search);
        if (!initial && (params.get('search') || params.get('page'))) {
            initial = 'in';
        }
        if (initial) showPanel(initial);

        if (btnIn) {
            btnIn.addEventListener('click', function () {
                showPanel('in');
                if (trackModal) trackModal.hide();
            });
        }
        if (btnOut) {
            btnOut.addEventListener('click', function () {
                showPanel('out');
                if (trackModal) trackModal.hide();
            });
        }

        // ====== Klik nomor di tabel untuk lihat detail (placeholder) ======
        const detailUrl        = @json(route('gudang.track-in-out.detail'));
        const tlsDetailUrl     = @json(route('gudang.track-in-out.tallysheet-detail'));
        const btbDetailUrl     = @json(route('gudang.track-in-out.btb-detail'));
        const putawayDetailUrl = @json(route('gudang.track-in-out.putaway-detail'));
        const outReqDetailUrl     = @json(route('gudang.track-in-out.out-request-detail'));
        const outPickingDetailUrl = @json(route('gudang.track-in-out.out-picking-detail'));
        const outBkbDetailUrl     = @json(route('gudang.track-in-out.out-bkb-detail'));
        const outDispatchDetailUrl = @json(route('gudang.track-in-out.out-dispatch-detail'));
        const outPodDetailUrl      = @json(route('gudang.track-in-out.out-pod-detail'));
        const outBtbRvDetailUrl    = @json(route('gudang.track-in-out.out-btbrv-detail'));
        const outPaymentDetailUrl  = @json(route('gudang.track-in-out.out-payment-detail'));

        function fmtNum(v) {
            if (v === null || v === undefined || v === '') return '';
            const n = Number(v);
            if (isNaN(n)) return v;
            return n.toLocaleString('id-ID', { maximumFractionDigits: 2 });
        }
        function escapeHtml(s) {
            return String(s ?? '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // === Detail PO (fetch JSON) ===
        const poModalEl = document.getElementById('poDetailModal');
        const poModal   = poModalEl ? bootstrap.Modal.getOrCreateInstance(poModalEl) : null;
        const poBody    = document.getElementById('poDetailBody');
        const poCode    = document.getElementById('poDetailCode');
        const PO_COLS   = 9;

        document.querySelectorAll('.tio-link-po').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const po = this.getAttribute('data-value') || '';
                if (!po || !poModal) return;
                if (poCode) poCode.textContent = po;
                poBody.innerHTML = '<tr><td colspan="' + PO_COLS + '" class="tio-empty">Memuat...</td></tr>';
                poModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(detailUrl + '?po=' + encodeURIComponent(po), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        poBody.innerHTML = '<tr><td colspan="' + PO_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    poBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.sku_code) + '</td>' +
                            '<td>' + escapeHtml(r.sku_description) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.order_qty))   + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.order_price)) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_ctn))     + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_pcs))     + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.price_pcs))   + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.price_ctn))   + '</td>' +
                            '<td>' + escapeHtml(r.order_po) + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    poBody.innerHTML = '<tr><td colspan="' + PO_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // === Modal placeholder lain (BTB / Putaway) ===
        function bindSimpleModal(linkClass, modalId, codeId) {
            const modalEl = document.getElementById(modalId);
            if (!modalEl) return;
            const modal   = bootstrap.Modal.getOrCreateInstance(modalEl);
            const label   = document.getElementById(codeId);
            document.querySelectorAll(linkClass).forEach(function (a) {
                a.addEventListener('click', function (ev) {
                    ev.preventDefault();
                    const val = this.getAttribute('data-value') || '';
                    if (!val) return;
                    if (label) label.textContent = val;
                    modal.show();
                });
            });
        }

        // === Placeholder kolom-kolom panel OUT (mapping menyusul) ===

        // === Detail POD panel OUT (fetch JSON) ===
        const outPodModalEl = document.getElementById('outPodDetailModal');
        const outPodModal   = outPodModalEl ? bootstrap.Modal.getOrCreateInstance(outPodModalEl) : null;
        const outPodBody    = document.getElementById('outPodDetailBody');
        const outPodCode    = document.getElementById('outPodDetailCode');
        const OUT_POD_COLS  = 7;

        document.querySelectorAll('.tio-link-out-pod').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const pod = this.getAttribute('data-value') || '';
                if (!pod || !outPodModal) return;
                if (outPodCode) outPodCode.textContent = pod;
                outPodBody.innerHTML = '<tr><td colspan="' + OUT_POD_COLS + '" class="tio-empty">Memuat...</td></tr>';
                outPodModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(outPodDetailUrl + '?pod=' + encodeURIComponent(pod), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        outPodBody.innerHTML = '<tr><td colspan="' + OUT_POD_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    outPodBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.vehicle) + '</td>' +
                            '<td>' + escapeHtml(r.driver)  + '</td>' +
                            '<td>' + escapeHtml(r.date)    + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.value))     + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.total_inv)) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.terkirim))  + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.cancel))    + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    outPodBody.innerHTML = '<tr><td colspan="' + OUT_POD_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // === Detail BTB RV panel OUT (fetch JSON) ===
        const outBtbRvModalEl = document.getElementById('outBtbRvDetailModal');
        const outBtbRvModal   = outBtbRvModalEl ? bootstrap.Modal.getOrCreateInstance(outBtbRvModalEl) : null;
        const outBtbRvBody    = document.getElementById('outBtbRvDetailBody');
        const outBtbRvCode    = document.getElementById('outBtbRvDetailCode');
        const OUT_BTBRV_COLS  = 8;

        document.querySelectorAll('.tio-link-out-btbrv').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const btbrv = this.getAttribute('data-value') || '';
                if (!btbrv || !outBtbRvModal) return;
                if (outBtbRvCode) outBtbRvCode.textContent = btbrv;
                outBtbRvBody.innerHTML = '<tr><td colspan="' + OUT_BTBRV_COLS + '" class="tio-empty">Memuat...</td></tr>';
                outBtbRvModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(outBtbRvDetailUrl + '?btbrv=' + encodeURIComponent(btbrv), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        outBtbRvBody.innerHTML = '<tr><td colspan="' + OUT_BTBRV_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    outBtbRvBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.rack)          + '</td>' +
                            '<td>' + escapeHtml(r.product_code)  + '</td>' +
                            '<td>' + escapeHtml(r.description)   + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_in))      + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_out))     + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.stock_akhir)) + '</td>' +
                            '<td>' + escapeHtml(r.supplier_code) + '</td>' +
                            '<td>' + escapeHtml(r.business_code) + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    outBtbRvBody.innerHTML = '<tr><td colspan="' + OUT_BTBRV_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // === Detail Payment Kasir panel OUT (fetch JSON) ===
        const outPayModalEl = document.getElementById('outPaymentDetailModal');
        const outPayModal   = outPayModalEl ? bootstrap.Modal.getOrCreateInstance(outPayModalEl) : null;
        const outPayBody    = document.getElementById('outPaymentDetailBody');
        const outPayCode    = document.getElementById('outPaymentDetailCode');
        const OUT_PAY_COLS  = 13;

        document.querySelectorAll('.tio-link-out-payment').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const dispatch = this.getAttribute('data-value') || '';
                const label    = this.getAttribute('data-label') || dispatch;
                if (!dispatch || !outPayModal) return;
                if (outPayCode) outPayCode.textContent = label;
                outPayBody.innerHTML = '<tr><td colspan="' + OUT_PAY_COLS + '" class="tio-empty">Memuat...</td></tr>';
                outPayModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(outPaymentDetailUrl + '?dispatch=' + encodeURIComponent(dispatch), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        outPayBody.innerHTML = '<tr><td colspan="' + OUT_PAY_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    outPayBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.dispatch_code)     + '</td>' +
                            '<td>' + escapeHtml(r.no_so)             + '</td>' +
                            '<td>' + escapeHtml(r.pembayaran)        + '</td>' +
                            '<td>' + escapeHtml(r.kode_retail)       + '</td>' +
                            '<td>' + escapeHtml(r.status)            + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.giro))              + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.value))             + '</td>' +
                            '<td>' + escapeHtml(r.branch)            + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.value_validation)) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.repay))            + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.pay_value))        + '</td>' +
                            '<td>' + escapeHtml(r.validation_giro)   + '</td>' +
                            '<td>' + escapeHtml(r.status_pengiriman) + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    outPayBody.innerHTML = '<tr><td colspan="' + OUT_PAY_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // === Detail Dispatch panel OUT (fetch JSON) ===
        const outDispModalEl = document.getElementById('outDispatchDetailModal');
        const outDispModal   = outDispModalEl ? bootstrap.Modal.getOrCreateInstance(outDispModalEl) : null;
        const outDispBody    = document.getElementById('outDispatchDetailBody');
        const outDispCode    = document.getElementById('outDispatchDetailCode');
        const OUT_DISP_COLS  = 9;

        document.querySelectorAll('.tio-link-out-dispatch').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const dp = this.getAttribute('data-value') || '';
                if (!dp || !outDispModal) return;
                if (outDispCode) outDispCode.textContent = dp;
                outDispBody.innerHTML = '<tr><td colspan="' + OUT_DISP_COLS + '" class="tio-empty">Memuat...</td></tr>';
                outDispModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(outDispatchDetailUrl + '?dispatch=' + encodeURIComponent(dp), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        outDispBody.innerHTML = '<tr><td colspan="' + OUT_DISP_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    outDispBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.vehicle) + '</td>' +
                            '<td>' + escapeHtml(r.so)      + '</td>' +
                            '<td>' + escapeHtml(r.product) + '</td>' +
                            '<td>' + escapeHtml(r.description) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty)) + '</td>' +
                            '<td>' + escapeHtml(r.satuan)  + '</td>' +
                            '<td>' + escapeHtml(r.driver)  + '</td>' +
                            '<td>' + escapeHtml(r.status)  + '</td>' +
                            '<td>' + escapeHtml(r.eta)     + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    outDispBody.innerHTML = '<tr><td colspan="' + OUT_DISP_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // === Detail BKB panel OUT (fetch JSON) ===
        const outBkbModalEl = document.getElementById('outBkbDetailModal');
        const outBkbModal   = outBkbModalEl ? bootstrap.Modal.getOrCreateInstance(outBkbModalEl) : null;
        const outBkbBody    = document.getElementById('outBkbDetailBody');
        const outBkbCode    = document.getElementById('outBkbDetailCode');
        const OUT_BKB_COLS  = 8;

        document.querySelectorAll('.tio-link-out-bkb').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const bkb = this.getAttribute('data-value') || '';
                if (!bkb || !outBkbModal) return;
                if (outBkbCode) outBkbCode.textContent = bkb;
                outBkbBody.innerHTML = '<tr><td colspan="' + OUT_BKB_COLS + '" class="tio-empty">Memuat...</td></tr>';
                outBkbModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(outBkbDetailUrl + '?bkb=' + encodeURIComponent(bkb), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        outBkbBody.innerHTML = '<tr><td colspan="' + OUT_BKB_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    outBkbBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.code) + '</td>' +
                            '<td>' + escapeHtml(r.description) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty))             + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.price))           + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.stock_manual))    + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.stock_system))    + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.price_cogs))      + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.price_cogs_last)) + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    outBkbBody.innerHTML = '<tr><td colspan="' + OUT_BKB_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // === Detail Picking panel OUT (fetch JSON) ===
        const outPickModalEl = document.getElementById('outPickingDetailModal');
        const outPickModal   = outPickModalEl ? bootstrap.Modal.getOrCreateInstance(outPickModalEl) : null;
        const outPickBody    = document.getElementById('outPickingDetailBody');
        const outPickCode    = document.getElementById('outPickingDetailCode');
        const OUT_PICK_COLS  = 10;

        document.querySelectorAll('.tio-link-out-picking').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const pick = this.getAttribute('data-value') || '';
                if (!pick || !outPickModal) return;
                if (outPickCode) outPickCode.textContent = pick;
                outPickBody.innerHTML = '<tr><td colspan="' + OUT_PICK_COLS + '" class="tio-empty">Memuat...</td></tr>';
                outPickModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(outPickingDetailUrl + '?picking=' + encodeURIComponent(pick), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        outPickBody.innerHTML = '<tr><td colspan="' + OUT_PICK_COLS + '" class="tio-empty">Proses picking dalam proses.</td></tr>';
                        return;
                    }
                    outPickBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.rack) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty))          + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.stock_awal))   + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.stock_akhir))  + '</td>' +
                            '<td>' + escapeHtml(r.expired_date)   + '</td>' +
                            '<td>' + escapeHtml(r.type_transaksi) + '</td>' +
                            '<td>' + escapeHtml(r.description)    + '</td>' +
                            '<td>' + escapeHtml(r.product_code)   + '</td>' +
                            '<td>' + escapeHtml(r.supplier_code)  + '</td>' +
                            '<td>' + escapeHtml(r.business_code)  + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    outPickBody.innerHTML = '<tr><td colspan="' + OUT_PICK_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // === Detail Request panel OUT (fetch JSON) ===
        const outReqModalEl = document.getElementById('outReqDetailModal');
        const outReqModal   = outReqModalEl ? bootstrap.Modal.getOrCreateInstance(outReqModalEl) : null;
        const outReqBody    = document.getElementById('outReqDetailBody');
        const outReqCode    = document.getElementById('outReqDetailCode');
        const OUT_REQ_COLS  = 5;

        document.querySelectorAll('.tio-link-out-req').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const req = this.getAttribute('data-value') || '';
                if (!req || !outReqModal) return;
                if (outReqCode) outReqCode.textContent = req;
                outReqBody.innerHTML = '<tr><td colspan="' + OUT_REQ_COLS + '" class="tio-empty">Memuat...</td></tr>';
                outReqModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(outReqDetailUrl + '?req=' + encodeURIComponent(req), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        outReqBody.innerHTML = '<tr><td colspan="' + OUT_REQ_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    outReqBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.product)       + '</td>' +
                            '<td>' + escapeHtml(r.description)   + '</td>' +
                            '<td>' + escapeHtml(r.business_code) + '</td>' +
                            '<td>' + escapeHtml(r.order_code)    + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty)) + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    outReqBody.innerHTML = '<tr><td colspan="' + OUT_REQ_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });
        // === Detail Putaway (fetch JSON) ===
        const pwModalEl = document.getElementById('putawayDetailModal');
        const pwModal   = pwModalEl ? bootstrap.Modal.getOrCreateInstance(pwModalEl) : null;
        const pwBody    = document.getElementById('putawayDetailBody');
        const pwCode    = document.getElementById('putawayDetailCode');
        const PW_COLS   = 7;

        document.querySelectorAll('.tio-link-putaway').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const pw = this.getAttribute('data-value') || '';
                if (!pw || !pwModal) return;
                if (pwCode) pwCode.textContent = pw;
                pwBody.innerHTML = '<tr><td colspan="' + PW_COLS + '" class="tio-empty">Memuat...</td></tr>';
                pwModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(putawayDetailUrl + '?putaway=' + encodeURIComponent(pw), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        pwBody.innerHTML = '<tr><td colspan="' + PW_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    pwBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.rack_code)    + '</td>' +
                            '<td>' + escapeHtml(r.product_code) + '</td>' +
                            '<td>' + escapeHtml(r.description)  + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_out))     + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_in))      + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.stock_akhir)) + '</td>' +
                            '<td>' + escapeHtml(r.type_transaksi) + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    pwBody.innerHTML = '<tr><td colspan="' + PW_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // === Detail BTB (fetch JSON) ===
        const btbModalEl = document.getElementById('btbDetailModal');
        const btbModal   = btbModalEl ? bootstrap.Modal.getOrCreateInstance(btbModalEl) : null;
        const btbBody    = document.getElementById('btbDetailBody');
        const btbCode    = document.getElementById('btbDetailCode');
        const BTB_COLS   = 9;

        document.querySelectorAll('.tio-link-btb').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const btb = this.getAttribute('data-value') || '';
                if (!btb || !btbModal) return;
                if (btbCode) btbCode.textContent = btb;
                btbBody.innerHTML = '<tr><td colspan="' + BTB_COLS + '" class="tio-empty">Memuat...</td></tr>';
                btbModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(btbDetailUrl + '?btb=' + encodeURIComponent(btb), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        btbBody.innerHTML = '<tr><td colspan="' + BTB_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    btbBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.product)      + '</td>' +
                            '<td>' + escapeHtml(r.expired_date) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_in))     + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_ctn))    + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_satuan)) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.harga_pcs))  + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.harga_ctn))  + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.gross_value)) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.net_value))   + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    btbBody.innerHTML = '<tr><td colspan="' + BTB_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // === Detail Tallysheet (fetch JSON) ===
        const tlsModalEl = document.getElementById('tlsDetailModal');
        const tlsModal   = tlsModalEl ? bootstrap.Modal.getOrCreateInstance(tlsModalEl) : null;
        const tlsBody    = document.getElementById('tlsDetailBody');
        const tlsCode    = document.getElementById('tlsDetailCode');
        const TLS_COLS   = 6;

        document.querySelectorAll('.tio-link-tls').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                const tls = this.getAttribute('data-value') || '';
                if (!tls || !tlsModal) return;
                if (tlsCode) tlsCode.textContent = tls;
                tlsBody.innerHTML = '<tr><td colspan="' + TLS_COLS + '" class="tio-empty">Memuat...</td></tr>';
                tlsModal.show();
                if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();

                fetch(tlsDetailUrl + '?tls=' + encodeURIComponent(tls), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const rows = (res && res.rows) ? res.rows : [];
                    if (!rows.length) {
                        tlsBody.innerHTML = '<tr><td colspan="' + TLS_COLS + '" class="tio-empty">Tidak ada detail.</td></tr>';
                        return;
                    }
                    tlsBody.innerHTML = rows.map(function (r) {
                        return '<tr>' +
                            '<td>' + escapeHtml(r.sku_order) + '</td>' +
                            '<td>' + escapeHtml(r.barcode)   + '</td>' +
                            '<td>' + escapeHtml(r.deskripsi) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.qty_ctn))     + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.pcs_per_ctn)) + '</td>' +
                            '<td style="text-align:right;">' + escapeHtml(fmtNum(r.total_pcs))   + '</td>' +
                        '</tr>';
                    }).join('');
                    if (typeof window.tioSyncScrollbars === 'function') window.tioSyncScrollbars();
                })
                .catch(function () {
                    tlsBody.innerHTML = '<tr><td colspan="' + TLS_COLS + '" class="tio-empty">Gagal memuat detail.</td></tr>';
                });
            });
        });

        // ====== Top scrollbar untuk tiap .tio-table-wrap ======
        const scrollPairs = [];
        document.querySelectorAll('.tio-table-wrap').forEach(function (wrap) {
            const top   = document.createElement('div');
            const inner = document.createElement('div');
            top.className = 'tio-scroll-top';
            top.appendChild(inner);
            wrap.parentNode.insertBefore(top, wrap);
            scrollPairs.push({ wrap: wrap, top: top, inner: inner });

            let lock = false;
            top.addEventListener('scroll', function () {
                if (lock) return;
                lock = true; wrap.scrollLeft = top.scrollLeft; lock = false;
            });
            wrap.addEventListener('scroll', function () {
                if (lock) return;
                lock = true; top.scrollLeft = wrap.scrollLeft; lock = false;
            });
        });

        window.tioSyncScrollbars = function () {
            scrollPairs.forEach(function (p) {
                const tbl = p.wrap.querySelector('table');
                // Skip kalau panel masih hidden (offsetParent null)
                if (!p.wrap.offsetParent && p.wrap.offsetWidth === 0) {
                    p.top.style.display = 'none';
                    return;
                }
                p.top.style.display = '';
                const w = tbl ? tbl.scrollWidth : p.wrap.scrollWidth;
                p.inner.style.width = w + 'px';
            });
        };
        window.tioSyncScrollbars();
        window.addEventListener('resize', window.tioSyncScrollbars);

        // === Filter pencarian untuk semua modal detail panel OUT ===
        // Menyisipkan input search di atas tabel dan memfilter baris berdasarkan
        // pencocokan teks di seluruh kolom.
        const outDetailFilters = [
            { body: 'outReqDetailBody',      placeholder: 'Cari di detail Request...' },
            { body: 'outPickingDetailBody',  placeholder: 'Cari di detail Picking...' },
            { body: 'outBkbDetailBody',      placeholder: 'Cari di detail BKB...' },
            { body: 'outDispatchDetailBody', placeholder: 'Cari di detail Dispatch...' },
            { body: 'outPodDetailBody',      placeholder: 'Cari di detail POD...' },
            { body: 'outBtbRvDetailBody',    placeholder: 'Cari di detail BTB RV...' },
            { body: 'outPaymentDetailBody',  placeholder: 'Cari di detail Payment Kasir...' },
            { body: 'poDetailBody',          placeholder: 'Cari di detail PO...' },
            { body: 'tlsDetailBody',         placeholder: 'Cari di detail Tallysheet...' },
            { body: 'btbDetailBody',         placeholder: 'Cari di detail BTB...' },
            { body: 'putawayDetailBody',     placeholder: 'Cari di detail Putaway...' },
        ];
        outDetailFilters.forEach(function (cfg) {
            const tbody = document.getElementById(cfg.body);
            if (!tbody) return;
            const responsive = tbody.closest('.table-responsive') || tbody.closest('.tio-table-wrap');
            const modalBody  = responsive ? responsive.parentElement : null;
            if (!responsive || !modalBody) return;

            const wrap = document.createElement('div');
            wrap.className = 'mb-2';
            const input = document.createElement('input');
            input.type = 'search';
            input.placeholder = cfg.placeholder;
            input.className = 'form-control form-control-sm';
            input.style.maxWidth = '320px';
            wrap.appendChild(input);
            modalBody.insertBefore(wrap, responsive);

            input.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                Array.from(tbody.rows).forEach(function (tr) {
                    // skip baris empty/loading yang span semua kolom
                    if (tr.cells.length === 1 && tr.cells[0].classList.contains('tio-empty')) {
                        return;
                    }
                    const txt = tr.textContent.toLowerCase();
                    tr.style.display = (!q || txt.indexOf(q) !== -1) ? '' : 'none';
                });
            });

            // Reset filter setiap kali modal dibuka.
            const modalEl = responsive.closest('.modal');
            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', function () {
                    input.value = '';
                });
            }
        });

        // === Print IN table ===
        const btnPrintIn = document.getElementById('btnPrintIn');
        const printInModalEl = document.getElementById('printInModal');
        const printInModal   = printInModalEl ? bootstrap.Modal.getOrCreateInstance(printInModalEl) : null;
        if (btnPrintIn && printInModal) {
            btnPrintIn.addEventListener('click', function () {
                printInModal.show();
            });
        }
        const btnPrintInSubmit = document.getElementById('btnPrintInSubmit');
        if (btnPrintInSubmit) {
            btnPrintInSubmit.addEventListener('click', function () {
                const df = (document.getElementById('printInDateFrom') || {}).value || '';
                const dt = (document.getElementById('printInDateTo')   || {}).value || '';
                const params = new URLSearchParams();
                if (df) params.set('date_from', df);
                if (dt) params.set('date_to', dt);
                const url = @json(route('gudang.track-in-out.print')) +
                            (params.toString() ? ('?' + params.toString()) : '');
                window.open(url, '_blank', 'width=1200,height=800');
                if (printInModal) printInModal.hide();
            });
        }

        // === Export Excel IN & OUT ===
        const exportBaseUrl = @json(route('gudang.track-in-out.export'));
        const currentParams = new URLSearchParams(window.location.search);
        const exportTioModalEl = document.getElementById('exportTioModal');
        const exportTioModal   = exportTioModalEl ? bootstrap.Modal.getOrCreateInstance(exportTioModalEl) : null;
        let exportTioType = 'in';

        const openExportModal = function (type) {
            exportTioType = type;
            const dfEl = document.getElementById('exportTioDateFrom');
            const dtEl = document.getElementById('exportTioDateTo');
            if (dfEl) dfEl.value = '';
            if (dtEl) dtEl.value = '';
            const titleEl = document.getElementById('exportTioModalLabel');
            if (titleEl) {
                titleEl.textContent = (type === 'out')
                    ? 'Export Excel - Track OUT'
                    : 'Export Excel - Track IN';
            }
            if (exportTioModal) exportTioModal.show();
        };

        const btnExportIn = document.getElementById('btnExportIn');
        if (btnExportIn) {
            btnExportIn.addEventListener('click', function () {
                openExportModal('in');
            });
        }
        const btnExportOut = document.getElementById('btnExportOut');
        if (btnExportOut) {
            btnExportOut.addEventListener('click', function () {
                openExportModal('out');
            });
        }
        const btnExportTioSubmit = document.getElementById('btnExportTioSubmit');
        if (btnExportTioSubmit) {
            btnExportTioSubmit.addEventListener('click', function () {
                const df = (document.getElementById('exportTioDateFrom') || {}).value || '';
                const dt = (document.getElementById('exportTioDateTo')   || {}).value || '';
                const p = new URLSearchParams();
                p.set('type', exportTioType);
                if (df) p.set('date_from', df);
                if (dt) p.set('date_to', dt);
                if (exportTioType === 'out') {
                    const s = currentParams.get('search_out');
                    if (s) p.set('search_out', s);
                } else {
                    const s = currentParams.get('search');
                    if (s) p.set('search', s);
                }
                window.location.href = exportBaseUrl + '?' + p.toString();
                if (exportTioModal) exportTioModal.hide();
            });
        }
    });
</script>
@endsection
