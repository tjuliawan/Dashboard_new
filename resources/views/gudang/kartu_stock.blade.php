@extends('layouts.user_type.auth')

@section('title', 'Kartu Stock')

@section('css')
<style>
    .ks-card {
        background: #ffffff;
        border: 1px solid #c9dcff;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 4px 18px rgba(0,0,0,.18);
        margin-bottom: 18px;
    }
    .ks-title {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #0b1d3a;
        margin-bottom: 14px;
    }
    .ks-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .8rem;
    }
    .ks-table th, .ks-table td {
        padding: 7px 9px;
        border: 1px solid #d8e2f1;
        white-space: nowrap;
    }
    .ks-table thead th {
        background: #eef4ff;
        color: #0b1d3a;
        font-weight: 700;
        text-transform: uppercase;
        font-size: .72rem;
        letter-spacing: .3px;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .ks-table tbody tr:nth-child(odd) { background: #fafcff; }
    .ks-table tbody tr:hover         { background: #eef4ff; }
    .ks-empty { text-align: center; color: #6b7280; padding: 14px; }
    .ks-table-wrap { overflow-x: auto; max-height: 70vh; overflow-y: auto; }
    .ks-num { text-align: right; font-variant-numeric: tabular-nums; }
    .ks-in  { color: #047857; font-weight: 600; }
    .ks-out { color: #b91c1c; font-weight: 600; }
    .ks-trigger {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: .82rem;
        font-weight: 600;
    }
    .ks-input {
        padding: 7px 10px;
        border: 1.5px solid #c9dcff;
        border-radius: 7px;
        font-size: .83rem;
        background: #eef4ff;
    }
    .ks-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #475569;
        margin-bottom: 4px;
        font-weight: 600;
    }
    .ks-ac-wrap { position: relative; }
    .ks-ac-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 50;
        background: #fff;
        border: 1px solid #c9dcff;
        border-radius: 7px;
        margin-top: 2px;
        max-height: 260px;
        overflow-y: auto;
        box-shadow: 0 6px 18px rgba(0,0,0,.12);
        display: none;
    }
    .ks-ac-list.show { display: block; }
    .ks-ac-item {
        padding: 7px 10px;
        font-size: .82rem;
        cursor: pointer;
        border-bottom: 1px solid #eef4ff;
    }
    .ks-ac-item:last-child { border-bottom: none; }
    .ks-ac-item:hover,
    .ks-ac-item.active { background: #eef4ff; }
    .ks-ac-item small { color: #6b7280; }
    .ks-ac-empty { padding: 8px 10px; color: #9ca3af; font-size: .78rem; font-style: italic; }
    .ks-modal-note { font-size: .82rem; color: #64748b; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="ks-card">
        <div class="ks-title">Print Preview Kartu Stock</div>

        <form method="GET" action="{{ url('/gudang/kartu-stock') }}" id="ksFilterForm"
              class="d-flex flex-wrap gap-3 align-items-end mb-3">

            <div class="d-flex flex-column ks-ac-wrap">
                <label class="ks-label">Branch <span class="text-danger">*</span></label>
                <input type="text" name="branch"
                       value="{{ $filters['branch'] ?? '' }}"
                       placeholder="ketik branch / kode"
                       class="ks-input ks-ac" data-ac="ksBranchData"
                       style="min-width:220px;" required autocomplete="off">
                <div class="ks-ac-list"></div>
            </div>

            <div class="d-flex flex-column ks-ac-wrap">
                <label class="ks-label">Client / Business <span class="text-danger">*</span></label>
                <input type="text" name="client"
                       value="{{ $filters['client'] ?? '' }}"
                       placeholder="ketik client / kode"
                       class="ks-input ks-ac" data-ac="ksClientData"
                       style="min-width:200px;" required autocomplete="off">
                <div class="ks-ac-list"></div>
            </div>

            <input type="hidden" name="rack" id="ksRackInput" value="{{ $filters['rack'] ?? '' }}">

            @if (!empty($filters['branch']) && !empty($filters['client']) && !empty($filters['sku']))
                <div class="d-flex flex-column">
                    <label class="ks-label">Rack Terpilih</label>
                    <select id="ksRackTopSelect" class="ks-input" style="min-width:220px;">
                        <option value="">Semua Rack</option>
                        @if (!empty($filters['rack']))
                            <option value="{{ $filters['rack'] }}" selected>{{ $filters['rack'] }}</option>
                        @endif
                    </select>
                </div>
            @endif

            <div class="d-flex flex-column">
                <label class="ks-label">SKU / Deskripsi</label>
                <input type="text" name="sku"
                       value="{{ $filters['sku'] ?? '' }}"
                       placeholder="SKU Internal / Business / Order"
                       class="ks-input" style="min-width:260px;">
            </div>

            <div class="d-flex flex-column">
                <label class="ks-label">Per Halaman</label>
                <select name="per_page" class="ks-input">
                    @foreach ([25, 50, 100, 200] as $pp)
                        <option value="{{ $pp }}"
                            {{ (int)($filters['per_page'] ?? 50) === $pp ? 'selected' : '' }}>
                            {{ $pp }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="ks-trigger" style="padding:8px 16px;">
                <i class="fas fa-search"></i> Tampilkan
            </button>
            <a href="{{ url('/gudang/kartu-stock') }}"
               class="btn btn-outline-secondary"
               style="padding:8px 14px;font-size:.82rem;">Reset</a>
            <button type="button" id="ksExportBtn"
                    class="btn btn-outline-success"
                    style="padding:8px 14px;font-size:.82rem;">
                <i class="tio-download"></i> Export Excel
            </button>
        </form>

        <div class="ks-table-wrap">
            <table class="ks-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>User Update</th>
                        <th>Last Mutasi</th>
                        <th>Date</th>
                        <th>Rack</th>
                        <th>Product Code</th>
                        <th>Business Code</th>
                        <th>Order Code</th>
                        <th>Description</th>
                        <th class="ks-num">Convert</th>
                        <th class="ks-num">QTY In</th>
                        <th class="ks-num">QTY Out</th>
                        <th class="ks-num">Stock Akhir</th>
                        <th class="ks-num">Cnt Last Stock</th>
                        <th>Exp Date In</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                        <tr>
                            <td class="ks-num">{{ $r->no }}</td>
                            <td>{{ $r->user_update }}</td>
                            <td>{{ $r->tr_inv_rack_code }}</td>
                            <td>{{ $r->rec_dateupdate }}</td>
                            <td>{{ $r->ms_rack_code }}</td>
                            <td>{{ $r->product_code }}</td>
                            <td>{{ $r->business_code }}</td>
                            <td>{{ $r->order_code }}</td>
                            <td>{{ $r->description }}</td>
                            <td class="ks-num">{{ $r->skuconvert }}</td>
                            <td class="ks-num ks-in">{{ $r->qty_in ? number_format($r->qty_in, 0, ',', '.') : '' }}</td>
                            <td class="ks-num ks-out">{{ $r->qty_out ? number_format($r->qty_out, 0, ',', '.') : '' }}</td>
                            <td class="ks-num">{{ number_format($r->stock_akhir, 0, ',', '.') }}</td>
                            <td class="ks-num">{{ $r->cnt_last_stock }}</td>
                            <td>{{ $r->exp_date_in }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="15" class="ks-empty">
                            @if (empty($filters['branch']) || empty($filters['client']))
                                Branch dan Client <b>wajib diisi</b> untuk menampilkan kartu stock.
                            @else
                                Tidak ada data.
                            @endif
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($paginator)
            <div class="d-flex justify-content-end mt-3">
                {!! $paginator->links() !!}
            </div>
        @endif
    </div>
</div>

<script>
    // ===== Data autocomplete (label = teks yang ditampilkan & dicari; value = yang disubmit) =====
    window.ksBranchData = @json($cabangList->map(fn ($c) => [
        'value' => $c->cab_code,
        'label' => $c->cab_code . ' — ' . $c->cab_desc,
    ])->values());
    window.ksClientData = @json($clientList->map(fn ($b) => [
        'value' => $b->clien_id,
        'label' => $b->clien_id . ' — ' . $b->clien_desc,
    ])->values());
    window.ksRackData = @json($rackList->map(fn ($r) => [
        'value' => $r->ms_rack_code,
        'label' => $r->ms_rack_code,
    ])->values());

    document.addEventListener('DOMContentLoaded', function () {
        const rackOptionsUrl = @json(route('gudang.kartu-stock.rack-options'));

        // ---- Autocomplete generic ----
        function setupAutocomplete(input) {
            const dataName = input.dataset.ac;
            const data     = (window[dataName] || []);
            const listEl   = input.parentElement.querySelector('.ks-ac-list');
            let activeIdx  = -1;
            let items      = [];

            function render(query) {
                const q = (query || '').toLowerCase().trim();
                items = (q === '')
                    ? data.slice(0, 50)
                    : data.filter(d =>
                        (d.label || '').toLowerCase().includes(q) ||
                        (d.value || '').toString().toLowerCase().includes(q)
                    ).slice(0, 50);

                if (items.length === 0) {
                    listEl.innerHTML = '<div class="ks-ac-empty">Tidak ada referensi cocok</div>';
                } else {
                    listEl.innerHTML = items.map((d, i) =>
                        `<div class="ks-ac-item" data-idx="${i}"><b>${d.value}</b> <small>— ${(d.label || '').replace(d.value + ' — ', '')}</small></div>`
                    ).join('');
                }
                listEl.classList.add('show');
                activeIdx = -1;
            }

            function pick(idx) {
                if (idx < 0 || idx >= items.length) return;
                input.value = items[idx].value;
                listEl.classList.remove('show');
            }

            input.addEventListener('focus', () => render(input.value));
            input.addEventListener('input', () => render(input.value));
            input.addEventListener('keydown', (e) => {
                if (!listEl.classList.contains('show')) return;
                if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, items.length - 1); highlight(); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); highlight(); }
                else if (e.key === 'Enter')   {
                    if (activeIdx >= 0) { e.preventDefault(); pick(activeIdx); }
                }
                else if (e.key === 'Escape')  { listEl.classList.remove('show'); }
            });
            function highlight() {
                listEl.querySelectorAll('.ks-ac-item').forEach((el, i) => {
                    el.classList.toggle('active', i === activeIdx);
                    if (i === activeIdx) el.scrollIntoView({ block: 'nearest' });
                });
            }
            listEl.addEventListener('mousedown', (e) => {
                const it = e.target.closest('.ks-ac-item');
                if (!it) return;
                e.preventDefault();
                pick(parseInt(it.dataset.idx, 10));
            });
            document.addEventListener('click', (e) => {
                if (!input.parentElement.contains(e.target)) listEl.classList.remove('show');
            });
        }
        document.querySelectorAll('.ks-ac').forEach(setupAutocomplete);

        // ---- Export modal submit ----
        const submitBtn = document.getElementById('ksExportSubmit');
        const form      = document.getElementById('ksFilterForm');
        const rackInput = form ? form.querySelector('[name=rack]') : null;
        const rackTopSelect = document.getElementById('ksRackTopSelect');
        let bypassRackModalOnce = false;

        // Modal pilih rack saat Branch + Client + SKU/Deskripsi terisi
        const rackModalEl       = document.getElementById('ksRackPickModal');
        const rackModal         = rackModalEl ? bootstrap.Modal.getOrCreateInstance(rackModalEl) : null;
        const rackSelect        = document.getElementById('ksRackPickSelect');
        const rackLoadInfo      = document.getElementById('ksRackPickLoadInfo');
        const rackEmptyInfo     = document.getElementById('ksRackPickEmptyInfo');
        const rackConfirmBtn    = document.getElementById('ksRackPickConfirm');
        const rackContinueAllBtn= document.getElementById('ksRackPickContinueAll');

        async function loadRackOptions(branch, client, sku) {
            const url = new URL(rackOptionsUrl, window.location.origin);
            url.searchParams.set('branch', branch);
            url.searchParams.set('client', client);
            url.searchParams.set('sku', sku);

            const res = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (!res.ok) {
                throw new Error(json.message || 'Gagal mengambil daftar rack.');
            }
            return Array.isArray(json.data) ? json.data : [];
        }

        if (form && rackModal && rackInput && rackSelect) {
            // Sinkronkan dropdown rack di atas tabel berdasarkan filter aktif
            if (rackTopSelect) {
                const branch = (form.querySelector('[name=branch]')?.value || '').trim();
                const client = (form.querySelector('[name=client]')?.value || '').trim();
                const sku    = (form.querySelector('[name=sku]')?.value || '').trim();
                const selectedRack = (rackInput.value || '').trim();

                if (branch && client && sku) {
                    loadRackOptions(branch, client, sku)
                        .then(function (racks) {
                            const options = ['<option value="">Semua Rack</option>'].concat(
                                racks.map(function (item) {
                                    const code = String(item.code || item).replace(/"/g, '&quot;');
                                    const stock = item.stock ? parseInt(item.stock, 10) : 0;
                                    const label = item.stock !== undefined ? `${code} — ${stock.toLocaleString()}` : code;
                                    const sel = (selectedRack === String(item.code || item)) ? ' selected' : '';
                                    return `<option value="${code}"${sel}>${label}</option>`;
                                })
                            );
                            rackTopSelect.innerHTML = options.join('');
                        })
                        .catch(function () {
                            // Biarkan opsi awal jika gagal memuat.
                        });
                }

                rackTopSelect.addEventListener('change', function () {
                    rackInput.value = (rackTopSelect.value || '').trim();
                    bypassRackModalOnce = true;
                    form.submit();
                });
            }

            form.addEventListener('submit', async function (e) {
                if (bypassRackModalOnce) {
                    bypassRackModalOnce = false;
                    return;
                }

                const branch = (form.querySelector('[name=branch]')?.value || '').trim();
                const client = (form.querySelector('[name=client]')?.value || '').trim();
                const sku    = (form.querySelector('[name=sku]')?.value || '').trim();
                const rack   = (rackInput.value || '').trim();

                // Trigger modal hanya saat Branch+Client+SKU/Deskripsi terisi dan Rack belum dipilih.
                if (!branch || !client || !sku || rack) {
                    return;
                }

                e.preventDefault();
                rackSelect.innerHTML = '';
                rackSelect.disabled = true;
                rackConfirmBtn.disabled = true;
                rackLoadInfo.classList.remove('d-none');
                rackEmptyInfo.classList.add('d-none');
                rackModal.show();

                try {
                    const racks = await loadRackOptions(branch, client, sku);
                    rackLoadInfo.classList.add('d-none');

                    if (!racks.length) {
                        rackEmptyInfo.classList.remove('d-none');
                        rackConfirmBtn.disabled = true;
                        return;
                    }

                    rackSelect.innerHTML = racks.map(function (item) {
                        const code = String(item.code || item).replace(/"/g, '&quot;');
                        const stock = item.stock ? parseInt(item.stock, 10) : 0;
                        const label = item.stock !== undefined ? `${code} — ${stock.toLocaleString()}` : code;
                        return `<option value="${code}">${label}</option>`;
                    }).join('');

                    rackSelect.disabled = false;
                    rackConfirmBtn.disabled = false;
                } catch (err) {
                    rackLoadInfo.classList.add('d-none');
                    rackEmptyInfo.classList.remove('d-none');
                    rackEmptyInfo.textContent = err.message || 'Gagal memuat daftar rack.';
                }
            });

            rackConfirmBtn.addEventListener('click', function () {
                const selectedRack = (rackSelect.value || '').trim();
                if (!selectedRack) {
                    alert('Silakan pilih rack terlebih dahulu.');
                    return;
                }
                rackInput.value = selectedRack;
                rackModal.hide();
                form.submit();
            });

            rackContinueAllBtn.addEventListener('click', function () {
                rackInput.value = '';
                rackModal.hide();
                bypassRackModalOnce = true;
                form.submit();
            });
        }

        // Cegah modal export terbuka jika filter wajib belum diisi
        const exportBtn = document.getElementById('ksExportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function () {
                const branch = (form.querySelector('[name=branch]')?.value || '').trim();
                const client = (form.querySelector('[name=client]')?.value || '').trim();
                if (!branch || !client) {
                    alert('Harap isi filter Branch dan Client terlebih dahulu, lalu klik Tampilkan sebelum Export.');
                    return;
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('ksExportModal')).show();
            });
        }

        if (submitBtn && form) {
            submitBtn.addEventListener('click', function () {
                const fd = new FormData(form);
                const params = new URLSearchParams();
                for (const [k, v] of fd.entries()) {
                    if (v) params.set(k, v);
                }
                const cf = document.getElementById('ksCountFrom').value;
                const ct = document.getElementById('ksCountTo').value;
                if (cf !== '') params.set('count_from', cf);
                if (ct !== '') params.set('count_to', ct);
                window.location.href = @json(route('gudang.kartu-stock.export')) +
                    (params.toString() ? ('?' + params.toString()) : '');
            });
        }
    });
</script>

<!-- Modal Pilih Rack Berdasarkan Filter -->
<div class="modal fade" id="ksRackPickModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Rack</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="ks-modal-note mb-2">Rack yang tersedia berdasarkan filter Branch, Client, dan SKU/Deskripsi:</p>
                <div id="ksRackPickLoadInfo" class="ks-modal-note">Memuat daftar rack...</div>
                <div id="ksRackPickEmptyInfo" class="alert alert-warning d-none mb-0" role="alert">
                        Tidak ada rack yang tersedia untuk kombinasi filter ini.
                </div>
                <select id="ksRackPickSelect" class="form-select" disabled></select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="ksRackPickContinueAll" class="btn btn-outline-primary">Lanjut Tanpa Rack</button>
                <button type="button" id="ksRackPickConfirm" class="btn btn-primary" disabled>Pilih Rack & Tampilkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Export Range No -->
<div class="modal fade" id="ksExportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Export Excel — Pilih Range No</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted" style="font-size:.82rem;">
            Pilih rentang nomor (kolom <b>No</b>) untuk diexport. Default: dari 1 sampai No tertinggi saat ini ({{ number_format($maxCount, 0, ',', '.') }}).
        </p>
        <div class="row g-3">
            <div class="col-6">
                <label class="ks-label">Dari No</label>
                <input type="number" id="ksCountFrom" class="form-control" min="1" value="1">
            </div>
            <div class="col-6">
                <label class="ks-label">Sampai No</label>
                <input type="number" id="ksCountTo" class="form-control" min="1" value="{{ $maxCount }}">
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="ksExportSubmit" class="btn btn-success" data-bs-dismiss="modal">
            <i class="tio-download"></i> Download
        </button>
      </div>
    </div>
  </div>
</div>
@endsection
