{{--
    Global DB Switch — HGS / TGU
    Usage: @include('partials.db-switch', ['reportDb' => $reportDb])
--}}
@php $reportDb = $reportDb ?? session('report_db', 'hgs'); @endphp
<div class="d-flex align-items-center gap-2" style="font-size:.78rem;">
    <a href="{{ route('set-report-db', ['db' => 'hgs']) }}"
       class="btn btn-sm {{ $reportDb === 'hgs' ? 'btn-primary' : 'btn-outline-secondary' }}"
       style="font-size:.75rem;padding:.22rem .7rem;min-width:52px;">
        HGS
    </a>
    <a href="{{ route('set-report-db', ['db' => 'tgu']) }}"
       class="btn btn-sm {{ $reportDb === 'tgu' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}"
       style="font-size:.75rem;padding:.22rem .7rem;min-width:52px;">
        TGU
    </a>
</div>
