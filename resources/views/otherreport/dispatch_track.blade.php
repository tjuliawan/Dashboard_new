@extends('layouts.user_type.auth')

@section('title', 'Dispatch Track')

@section('css')
<style>
    .dt-wrap { width: 100%; padding: 16px 20px; }
    .dt-grid {
        display: grid;
        grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
        gap: 16px;
        align-items: start;
    }
    .dt-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .dt-card-head {
        padding: 14px 18px;
        border-bottom: 1px solid #eef0f3;
        font-weight: 700;
        font-size: 0.92rem;
        color: #344054;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .dt-search {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        font-size: 0.85rem;
        outline: none;
    }
    .dt-search:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .dt-list { flex: 1; overflow: auto; max-height: none; }
    .dt-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .dt-table th, .dt-table td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #f1f3f5; }
    .dt-table thead th {
        position: sticky; top: 0; background: #f8fafc; color: #475467;
        font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: .03em; z-index: 1;
    }
    .dt-row { cursor: pointer; }
    .dt-row:hover { background: #f9fafb; }
    .dt-row.active { background: #eff6ff; }
    .dt-row.active td { color: #1d4ed8; font-weight: 600; }
    .dt-code { font-family: ui-monospace, "SFMono-Regular", Menlo, monospace; font-size: 0.82rem; }
    .dt-driver { color: #475467; }
    .dt-track-body { padding: 18px 22px; flex: 1; min-height: 480px; color: #6b7280; overflow: auto; }
    .dt-track-empty {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 100%; text-align: center; color: #9ca3af;
    }
    .dt-track-empty i { font-size: 42px; margin-bottom: 10px; opacity: .6; }

    /* Tracking — modern hero + timeline */
    .dt-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #3b82f6 100%);
        color: #fff;
        border-radius: 14px;
        padding: 18px 22px;
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
        box-shadow: 0 6px 20px rgba(37, 99, 235, .25);
        position: relative; overflow: hidden;
    }
    .dt-hero::after {
        content: ""; position: absolute; right: -40px; top: -40px;
        width: 180px; height: 180px; border-radius: 50%;
        background: rgba(255,255,255,.08);
    }
    .dt-hero-icon {
        width: 48px; height: 48px; border-radius: 12px;
        background: rgba(255,255,255,.18);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; color: #fff; flex-shrink: 0;
    }
    .dt-hero-meta { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .dt-hero-code {
        font-family: ui-monospace, "SFMono-Regular", Menlo, monospace;
        font-size: 1.05rem; font-weight: 700; letter-spacing: .02em;
    }
    .dt-hero-driver { font-size: 0.85rem; opacity: .92; }
    .dt-hero-stats { margin-left: auto; display: flex; gap: 10px; flex-wrap: wrap; z-index: 1; }
    .dt-stat {
        background: rgba(255,255,255,.18);
        backdrop-filter: blur(4px);
        border-radius: 10px; padding: 8px 14px; min-width: 90px;
        text-align: center;
    }
    .dt-stat-num { font-size: 1.2rem; font-weight: 700; line-height: 1.1; }
    .dt-stat-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: .05em; opacity: .85; }

    /* Timeline */
    /* Zigzag timeline (left/right alternating) */
    .dt-timeline { position: relative; padding: 26px 4px 8px 4px; }
    .dt-timeline::before {
        content: ""; position: absolute;
        left: 50%; top: 0; bottom: 0;
        width: 3px; transform: translateX(-50%);
        background: linear-gradient(to bottom, #2563eb 0%, #93c5fd 50%, #cbd5e1 100%);
        border-radius: 2px;
    }
    .dt-tl-item {
        position: relative;
        width: calc(50% - 28px);
        padding: 12px 14px 12px 14px;
        margin-bottom: 16px;
        background: #fff;
        border: 1px solid #eef0f3;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(15,23,42,.04);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .dt-tl-item:hover {
        box-shadow: 0 8px 24px rgba(15,23,42,.10);
        border-color: #dbeafe;
        transform: translateY(-2px);
    }
    .dt-tl-item.left  { margin-right: auto; padding-right: 18px; }
    .dt-tl-item.right { margin-left:  auto; padding-left: 18px; }
    /* connector arrow from card to center axis */
    .dt-tl-item::after {
        content: ""; position: absolute; top: 18px;
        width: 0; height: 0;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
    }
    .dt-tl-item.left::after  { right: -8px;  border-left:  8px solid #fff; filter: drop-shadow(1px 0 0 #eef0f3); }
    .dt-tl-item.right::after { left:  -8px;  border-right: 8px solid #fff; filter: drop-shadow(-1px 0 0 #eef0f3); }
    /* dot on the center axis */
    .dt-tl-dot {
        position: absolute; top: 14px;
        width: 26px; height: 26px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 12px; z-index: 2;
        box-shadow: 0 0 0 4px #fff, 0 0 0 5px #e5e7eb;
    }
    .dt-tl-item.left  .dt-tl-dot { right: -42px; }
    .dt-tl-item.right .dt-tl-dot { left:  -42px; }
    .dt-tl-dot.delivered { background: #10b981; box-shadow: 0 0 0 4px #fff, 0 0 0 5px #a7f3d0; }
    .dt-tl-dot.cancel    { background: #ef4444; box-shadow: 0 0 0 4px #fff, 0 0 0 5px #fecaca; }
    .dt-tl-dot.open      { background: #3b82f6; box-shadow: 0 0 0 4px #fff, 0 0 0 5px #bfdbfe; }
    .dt-tl-dot.other     { background: #9ca3af; box-shadow: 0 0 0 4px #fff, 0 0 0 5px #e5e7eb; }
    .dt-tl-step {
        position: absolute; top: -10px;
        background: #2563eb; color: #fff;
        font-size: 0.65rem; font-weight: 700;
        padding: 2px 8px; border-radius: 999px;
        letter-spacing: .04em;
    }
    .dt-tl-item.left  .dt-tl-step { right: 14px; }
    .dt-tl-item.right .dt-tl-step { left:  14px; }
    .dt-tl-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .dt-tl-item.left  .dt-tl-row { justify-content: flex-end; }
    .dt-tl-item.right .dt-tl-row { justify-content: flex-start; }
    .dt-tl-so {
        font-family: ui-monospace, "SFMono-Regular", Menlo, monospace;
        font-size: 0.95rem; font-weight: 700; color: #111827;
    }
    .dt-tl-time {
        font-size: 0.78rem; color: #6b7280;
        display: inline-flex; align-items: center; gap: 5px;
        margin-top: 6px;
    }
    .dt-tl-item.left  .dt-tl-time { justify-content: flex-end; width: 100%; }
    .dt-tl-item.right .dt-tl-time { justify-content: flex-start; width: 100%; }
    .dt-tl-time i { color: #9ca3af; }
    .dt-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 11px; border-radius: 999px;
        font-size: 0.72rem; font-weight: 600; letter-spacing: .02em;
    }
    .dt-status-badge i { font-size: 9px; }
    .dt-status-delivered { background: #ecfdf5; color: #047857; }
    .dt-status-cancel    { background: #fef2f2; color: #b91c1c; }
    .dt-status-open      { background: #eff6ff; color: #1d4ed8; }
    .dt-status-other     { background: #f3f4f6; color: #4b5563; }

    @media (max-width: 720px) {
        .dt-timeline::before { left: 18px; }
        .dt-tl-item, .dt-tl-item.left, .dt-tl-item.right {
            width: auto; margin-left: 42px; margin-right: 0; padding: 12px 14px;
        }
        .dt-tl-item::after { display: none; }
        .dt-tl-item.left  .dt-tl-dot,
        .dt-tl-item.right .dt-tl-dot { left: -34px; right: auto; }
        .dt-tl-item.left  .dt-tl-step,
        .dt-tl-item.right .dt-tl-step { left: 14px; right: auto; }
        .dt-tl-item.left  .dt-tl-row,
        .dt-tl-item.right .dt-tl-row { justify-content: flex-start; }
        .dt-tl-item.left  .dt-tl-time,
        .dt-tl-item.right .dt-tl-time { justify-content: flex-start; }
    }

    .dt-skeleton {
        height: 60px; background: linear-gradient(90deg,#f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
        background-size: 200% 100%;
        border-radius: 12px; margin-bottom: 10px;
        animation: dtPulse 1.2s ease-in-out infinite;
    }
    @keyframes dtPulse { 0%{background-position:200% 0;} 100%{background-position:-200% 0;} }

    @media (max-width: 900px) {
        .dt-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="dt-wrap">
    <div class="dt-grid">
        {{-- Left column: Dispatch List + Penggunaan Handheld --}}
        <div class="dt-left-col">
        <div class="dt-card">
            <div class="dt-card-head">
                <span>Dispatch List <span style="font-weight:500;color:#9ca3af;font-size:.78rem;">• Status Open</span></span>
                <span style="font-weight:500;font-size:.75rem;color:#9ca3af;" id="dt-count-label">{{ count($dispatches) }} entri</span>
            </div>
            <div style="padding:10px 14px;border-bottom:1px solid #eef0f3;display:flex;flex-direction:column;gap:8px;">
                <select id="dt-date-filter" class="dt-search" data-default="{{ $today }}">
                    <option value="">Semua Tanggal</option>
                    @php $hasToday = $dispatchesGrouped->has($today); @endphp
                    @if (!$hasToday)
                        <option value="{{ $today }}" selected>
                            {{ \Carbon\Carbon::parse($today)->format('d M Y') }} (0)
                        </option>
                    @endif
                    @foreach ($dispatchesGrouped as $dateKey => $items)
                        <option value="{{ $dateKey }}" {{ $dateKey === $today ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($dateKey)->format('d M Y') }} ({{ count($items) }})
                        </option>
                    @endforeach
                </select>
                <input type="text" id="dt-search" class="dt-search" placeholder="Cari dispatch / driver...">
            </div>
            <div class="dt-list">
                <table class="dt-table" id="dt-table">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Dispatch Code</th>
                            <th>Driver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dispatches as $d)
                            @php $dateKey = \Carbon\Carbon::parse($d->dispatch_date)->format('Y-m-d'); @endphp
                            <tr class="dt-row"
                                data-code="{{ $d->dispatch_code }}"
                                data-driver="{{ $d->driver_name }}"
                                data-vhcl="{{ $d->vhcl_code }}"
                                data-date="{{ $dateKey }}"
                                onclick="dtSelect(this)">
                                <td class="dt-code">{{ $d->vhcl_code ?: '-' }}</td>
                                <td class="dt-code">{{ $d->dispatch_code }}</td>
                                <td class="dt-driver">{{ $d->driver_name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:24px;">Tidak ada dispatch dengan status Open.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dt-card" style="margin-top:16px;">
            <div class="dt-card-head">
                <span><i class="fas fa-mobile-screen-button" style="margin-right:8px;color:#2563eb;"></i>Penggunaan Handheld</span>
                <span style="font-weight:500;font-size:.75rem;color:#9ca3af;">Hari Ini</span>
            </div>
            <div style="overflow:auto;">
                <table class="dt-table">
                    <thead>
                        <tr>
                            <th>Nama Driver</th>
                            <th style="text-align:right;">Scan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($handheld ?? [] as $h)
                            <tr>
                                <td class="dt-driver">{{ $h->driver_name }}</td>
                                <td class="dt-code" style="text-align:right;">{{ (int) $h->scan_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="text-align:center;color:#9ca3af;padding:18px;font-size:.82rem;">
                                    Tidak ada data scan hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>

        {{-- Right: Tracking panel (full width remaining) --}}
        <div class="dt-card">
            <div class="dt-card-head">
                <span>Tracking</span>
                <span id="dt-selected" style="font-weight:500;font-size:.8rem;color:#9ca3af;">Belum ada dispatch dipilih</span>
            </div>
            <div class="dt-track-body" id="dt-track-body">
                <div class="dt-track-empty" id="dt-track-empty">
                    <i class="fas fa-route"></i>
                    <div>Pilih dispatch di sebelah kiri untuk melihat tracking.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const dtDetailUrl = '{{ route('dispatch-track.detail') }}';

function dtStatusClass(s) {
    const u = (s || '').toString().trim().toUpperCase();
    if (u === 'DELIVERED') return 'dt-status-delivered';
    if (u === 'CANCEL')    return 'dt-status-cancel';
    if (u === 'OPEN')      return 'dt-status-open';
    return 'dt-status-other';
}
function dtFmtDate(v) {
    if (!v) return '-';
    const d = new Date(v.replace(' ', 'T'));
    if (isNaN(d.getTime())) return v;
    const pad = function(n){ return n < 10 ? '0'+n : n; };
    return pad(d.getDate()) + '/' + pad(d.getMonth()+1) + '/' + d.getFullYear()
         + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

function dtSelect(rowEl) {
    document.querySelectorAll('#dt-table .dt-row.active').forEach(function(el){ el.classList.remove('active'); });
    rowEl.classList.add('active');
    const code   = rowEl.dataset.code   || '-';
    const driver = rowEl.dataset.driver || '-';
    document.getElementById('dt-selected').textContent = code + ' • ' + driver;

    const body = document.getElementById('dt-track-body');
    body.innerHTML = '<div class="dt-skeleton"></div><div class="dt-skeleton"></div><div class="dt-skeleton"></div>';

    fetch(dtDetailUrl + '?dispatch_code=' + encodeURIComponent(code), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        const rows = (data && data.rows) || [];
        const counts = { DELIVERED: 0, CANCEL: 0, OPEN: 0, OTHER: 0 };
        rows.forEach(function(r){
            const u = (r.dpch_status || '').toString().trim().toUpperCase();
            if (counts[u] !== undefined) counts[u]++; else counts.OTHER++;
        });

        const hero =
            '<div class="dt-hero">'
          +   '<div class="dt-hero-icon"><i class="fas fa-truck-fast"></i></div>'
          +   '<div class="dt-hero-meta">'
          +     '<div class="dt-hero-code">' + code + '</div>'
          +     '<div class="dt-hero-driver"><i class="fas fa-user" style="margin-right:6px;opacity:.85;"></i>' + driver + '</div>'
          +   '</div>'
          +   '<div class="dt-hero-stats">'
          +     '<div class="dt-stat"><div class="dt-stat-num">' + rows.length + '</div><div class="dt-stat-label">Total SO</div></div>'
          +     '<div class="dt-stat"><div class="dt-stat-num">' + counts.DELIVERED + '</div><div class="dt-stat-label">Terkirim</div></div>'
          +     '<div class="dt-stat"><div class="dt-stat-num">' + counts.OPEN + '</div><div class="dt-stat-label">Open</div></div>'
          +     '<div class="dt-stat"><div class="dt-stat-num">' + counts.CANCEL + '</div><div class="dt-stat-label">Cancel</div></div>'
          +   '</div>'
          + '</div>';

        let timeline;
        if (!rows.length) {
            timeline = '<div style="padding:30px 12px;text-align:center;color:#9ca3af;">Tidak ada SO untuk dispatch ini.</div>';
        } else {
            timeline = '<div class="dt-timeline">'
              + rows.map(function(r, idx){
                    const u = (r.dpch_status || '').toString().trim().toUpperCase();
                    const cls   = u === 'DELIVERED' ? 'delivered'
                                : u === 'CANCEL'    ? 'cancel'
                                : u === 'OPEN'      ? 'open' : 'other';
                    const icon  = u === 'DELIVERED' ? 'fa-check'
                                : u === 'CANCEL'    ? 'fa-xmark'
                                : u === 'OPEN'      ? 'fa-truck' : 'fa-circle';
                    const side  = (idx % 2 === 0) ? 'left' : 'right';
                    return '<div class="dt-tl-item ' + side + '">'
                         +   '<span class="dt-tl-step">#' + (idx + 1) + '</span>'
                         +   '<div class="dt-tl-dot ' + cls + '"><i class="fas ' + icon + '"></i></div>'
                         +   '<div class="dt-tl-row">'
                         +     '<span class="dt-tl-so">' + (r.dpcth_so ?? '-') + '</span>'
                         +     '<span class="dt-status-badge dt-status-' + cls + '"><i class="fas fa-circle"></i>' + ((r.dpch_status ?? '') || '-') + '</span>'
                         +   '</div>'
                         +   '<div class="dt-tl-time"><i class="far fa-clock"></i>Delivered: ' + (r.delivered_time ? dtFmtDate(r.delivered_time) : '-') + '</div>'
                         + '</div>';
                }).join('')
              + '</div>';
        }
        body.innerHTML = hero + timeline;
    })
    .catch(function(){
        body.innerHTML = '<div style="padding:30px 12px;text-align:center;color:#dc2626;">Gagal memuat data.</div>';
    });
}

(function(){
    const input = document.getElementById('dt-search');
    const dateSel = document.getElementById('dt-date-filter');
    const countLabel = document.getElementById('dt-count-label');

    function applyFilter() {
        const q = (input?.value || '').toLowerCase().trim();
        const d = dateSel?.value || '';
        let visible = 0;
        document.querySelectorAll('#dt-table tbody tr.dt-row').forEach(function(tr){
            const text = tr.innerText.toLowerCase();
            const matchText = !q || text.indexOf(q) >= 0;
            const matchDate = !d || tr.dataset.date === d;
            const show = matchText && matchDate;
            tr.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countLabel) countLabel.textContent = visible + ' entri';
    }

    if (input)   input.addEventListener('input', applyFilter);
    if (dateSel) dateSel.addEventListener('change', applyFilter);
    applyFilter();
})();
</script>
@endsection
