<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LastMileController extends Controller
{
    public function index(Request $request)
    {
        // Data Per Driver: default = 7 hari terakhir (today-7 s/d today)
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today()->subDays(7)->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->endOfDay();

        // SPK & Cancel: filter dropdown range (3 / 7 / 30 hari terakhir). Default 7.
        $allowedSpkRanges = [3, 7, 30];
        $spkRange = (int) $request->input('spk_range', 7);
        if (!in_array($spkRange, $allowedSpkRanges, true)) {
            $spkRange = 7;
        }
        $spkFrom = Carbon::today()->subDays($spkRange - 1)->startOfDay();
        $spkTo   = Carbon::today()->endOfDay();

        // Format aman untuk inject ke subquery (Carbon menjamin format ISO valid).
        $dateFromStr = $dateFrom->format('Y-m-d H:i:s');
        $dateToStr   = $dateTo->format('Y-m-d H:i:s');

        // Pagination: default 20, allowed 20/25/50/75/100
        $allowedPerPage = [20, 25, 50, 75, 100];
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 20;
        }

        // Data Per (Tanggal, Driver, Kendaraan, Dispatch Code) — 1 baris per dispatch.
        // Catatan: 1 driver bisa punya >1 Dpcth_code_h di tanggal & kendaraan yg sama,
        // jadi kita pisah per Dpcth_code_h supaya hitungan invoice akurat per dispatch.
        $drivers = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_h as h')
            ->leftJoin('ms_driver as drv', 'drv.drv_id', '=', 'h.Dpcth_drv_code')
            ->whereBetween('h.Dptch_date', [$dateFrom, $dateTo])
            ->whereNotNull('h.Dpcth_drv_code')
            ->where('h.Dpcth_drv_code', '<>', '')
            ->groupBy(
                DB::raw('CAST(h.Dptch_date AS DATE)'),
                'h.Dpcth_drv_code',
                'drv.Drv_FistName',
                'h.Dpcth_vhcl_code',
                'h.Dpcth_code_h'
            )
            ->select([
                DB::raw('CAST(h.Dptch_date AS DATE)                                                                        AS dispatch_date'),
                DB::raw('h.Dpcth_drv_code                                                                                  AS driver_code'),
                DB::raw('MAX(drv.Drv_FistName)                                                                             AS driver_name'),
                DB::raw('h.Dpcth_vhcl_code                                                                                 AS vhcl_codes'),
                DB::raw('h.Dpcth_code_h                                                                                    AS dispatch_code'),
                DB::raw('COUNT(h.dpcth_SO)                                                                                 AS total_invoice'),
                DB::raw("SUM(CASE WHEN UPPER(LTRIM(RTRIM(h.dpch_status))) = 'DELIVERED' THEN 1 ELSE 0 END)                AS total_delivered"),
                DB::raw("SUM(CASE WHEN UPPER(LTRIM(RTRIM(h.dpch_status))) = 'CANCEL'    THEN 1 ELSE 0 END)                AS total_cancel"),
            ])
            ->orderByDesc(DB::raw('CAST(h.Dptch_date AS DATE)'))
            ->orderBy('h.Dpcth_drv_code')
            ->orderBy('h.Dpcth_vhcl_code')
            ->orderBy('h.Dpcth_code_h')
            ->paginate($perPage)
            ->withQueryString();

        // ── SPK & CANCEL ──
        // Periode SPK & Cancel pakai range terpisah (default: Senin minggu ini s/d kemarin).
        $spkBase = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_h as h')
            ->whereBetween('h.Dptch_date', [$spkFrom, $spkTo]);

        $spkSummary = (clone $spkBase)
            ->selectRaw("
                COUNT(*) AS total_invoice,
                SUM(CASE WHEN UPPER(LTRIM(RTRIM(ISNULL(h.dpch_status,'')))) = 'OPEN'      THEN 1 ELSE 0 END) AS total_ongoing,
                SUM(CASE WHEN UPPER(LTRIM(RTRIM(ISNULL(h.dpch_status,'')))) = 'DELIVERED' THEN 1 ELSE 0 END) AS total_delivered,
                SUM(CASE WHEN UPPER(LTRIM(RTRIM(ISNULL(h.dpch_status,'')))) = 'CANCEL'    THEN 1 ELSE 0 END) AS spk_menggantung,
                SUM(CASE WHEN UPPER(LTRIM(RTRIM(ISNULL(h.dpch_status,'')))) = 'CANCEL'
                          AND LTRIM(RTRIM(ISNULL(h.dpch_resaon,''))) = '' THEN 1 ELSE 0 END) AS cancel_no_reason
            ")
            ->first();

        $cancelReasons = (clone $spkBase)
            ->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(h.dpch_status,'')))) = 'CANCEL'")
            ->groupBy(DB::raw("ISNULL(NULLIF(LTRIM(RTRIM(h.dpch_resaon)),''), '(Tanpa Reason)')"))
            ->select([
                DB::raw("ISNULL(NULLIF(LTRIM(RTRIM(h.dpch_resaon)),''), '(Tanpa Reason)') AS reason"),
                DB::raw('COUNT(*) AS jumlah'),
            ])
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get();

        // ── SLA & PERFORMA ──
        // Pakai periode yang sama dengan SPK & Cancel (spkFrom..spkTo).
        // Cancel Rate = CANCEL / (DELIVERED + CANCEL) × 100  (OPEN dikecualikan, belum final)
        // SLA / Success Rate = 100 - Cancel Rate
        $slaTotalDelivered = (int) ($spkSummary->total_delivered ?? 0);
        $slaTotalCancel    = (int) ($spkSummary->spk_menggantung ?? 0);
        $slaTotalOngoing   = (int) ($spkSummary->total_ongoing ?? 0);
        $slaFinalized      = $slaTotalDelivered + $slaTotalCancel;

        $cancelRate  = $slaFinalized > 0 ? ($slaTotalCancel    / $slaFinalized) * 100 : 0;
        $successRate = $slaFinalized > 0 ? ($slaTotalDelivered / $slaFinalized) * 100 : 0;

        // ── Perbandingan SLA per Driver: 7 Hari Terakhir / 30 Hari Terakhir / Tahun Ini ──
        $thisWeekTo   = Carbon::now()->endOfDay();
        $thisWeekFrom = Carbon::now()->subDays(6)->startOfDay();
        $lastWeekTo   = Carbon::now()->endOfDay();
        $lastWeekFrom = Carbon::now()->subDays(29)->startOfDay();
        $thisYearFrom = Carbon::now()->startOfYear()->startOfDay();
        $thisYearTo   = Carbon::now()->endOfYear()->endOfDay();

        $slaPerDriver = function ($from, $to) {
            return DB::connection('rcm_hgs')
                ->table('TGU_dispatch_h as h')
                ->leftJoin('ms_driver as drv', 'drv.drv_id', '=', 'h.Dpcth_drv_code')
                ->whereBetween('h.Dptch_date', [$from, $to])
                ->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(h.dpch_status,'')))) IN ('DELIVERED','CANCEL')")
                ->whereNotNull('h.Dpcth_drv_code')
                ->where('h.Dpcth_drv_code', '<>', '')
                ->groupBy('h.Dpcth_drv_code')
                ->select([
                    DB::raw('h.Dpcth_drv_code AS driver_code'),
                    DB::raw("MAX(ISNULL(NULLIF(LTRIM(RTRIM(drv.Drv_FistName)),''), h.Dpcth_drv_code)) AS driver_name"),
                    DB::raw("SUM(CASE WHEN UPPER(LTRIM(RTRIM(h.dpch_status))) = 'DELIVERED' THEN 1 ELSE 0 END) AS delivered"),
                    DB::raw("SUM(CASE WHEN UPPER(LTRIM(RTRIM(h.dpch_status))) = 'CANCEL'    THEN 1 ELSE 0 END) AS cancel"),
                ])
                ->orderBy('driver_name')
                ->get()
                ->map(function ($r) {
                    $finalized = (int) $r->delivered + (int) $r->cancel;
                    $r->finalized    = $finalized;
                    $r->success_rate = $finalized > 0 ? round(((int) $r->delivered / $finalized) * 100, 2) : 0;
                    $r->cancel_rate  = $finalized > 0 ? round(((int) $r->cancel    / $finalized) * 100, 2) : 0;
                    return $r;
                });
        };

        $slaThisWeek = $slaPerDriver($thisWeekFrom, $thisWeekTo);
        $slaLastWeek = $slaPerDriver($lastWeekFrom, $lastWeekTo);
        $slaThisYear = $slaPerDriver($thisYearFrom, $thisYearTo);

        return view('otherreport.lastmile', compact(
            'drivers', 'dateFrom', 'dateTo', 'perPage', 'allowedPerPage',
            'spkSummary', 'cancelReasons', 'spkFrom', 'spkTo',
            'spkRange', 'allowedSpkRanges',
            'cancelRate', 'successRate', 'slaFinalized', 'slaTotalOngoing',
            'slaThisWeek', 'slaLastWeek', 'slaThisYear',
            'thisWeekFrom', 'thisWeekTo', 'lastWeekFrom', 'lastWeekTo',
            'thisYearFrom', 'thisYearTo'
        ));
    }

    /**
     * AJAX: Daftar invoice yang dibawa driver pada tanggal & kendaraan tertentu.
     * Sumber: TGU_dispatch_h
     * Kolom : dpcth_SO, dpch_status, dpch_value
     */
    public function invoices(Request $request)
    {
        $date     = $request->input('date');
        $driver   = $request->input('driver_code');
        $vehicle  = $request->input('vhcl_code');
        $dispatch = $request->input('dispatch_code');

        if (!$date || !$driver) {
            return response()->json(['error' => 'Parameter date & driver_code wajib diisi.'], 400);
        }

        $q = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_h as h')
            ->whereRaw('CAST(h.Dptch_date AS DATE) = ?', [$date])
            ->where('h.Dpcth_drv_code', '=', $driver);

        if ($vehicle !== null && $vehicle !== '') {
            $q->where('h.Dpcth_vhcl_code', '=', $vehicle);
        }
        if ($dispatch !== null && $dispatch !== '') {
            $q->where('h.Dpcth_code_h', '=', $dispatch);
        }

        $rows = $q->select([
                DB::raw('h.dpcth_SO    AS dpcth_so'),
                DB::raw('h.dpch_status AS dpch_status'),
                DB::raw('h.dpch_value  AS dpch_value'),
                DB::raw('h.dpch_resaon AS dpch_resaon'),
            ])
            ->orderBy('h.dpcth_SO')
            ->get();

        return response()->json(['rows' => $rows]);
    }

    /**
     * AJAX: Detail invoice cancel berdasarkan reason (untuk drilldown breakdown reason cancel).
     * Sumber: TGU_dispatch_h
     * Kolom yang dikirim: Dptch_date, Dpcth_code_h, dpcth_SO, Dpcth_drv_code, dpch_resaon
     */
    public function cancelDetail(Request $request)
    {
        $reason = (string) $request->input('reason', '');
        $range  = (int) $request->input('spk_range', 7);
        if (!in_array($range, [3, 7, 30], true)) {
            $range = 7;
        }

        $from = Carbon::today()->subDays($range - 1)->startOfDay();
        $to   = Carbon::today()->endOfDay();

        $q = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_h as h')
            ->leftJoin('ms_driver as drv', 'drv.drv_id', '=', 'h.Dpcth_drv_code')
            ->whereBetween('h.Dptch_date', [$from, $to])
            ->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(h.dpch_status,'')))) = 'CANCEL'");

        // "(Tanpa Reason)" → reason kosong/null. Selain itu, match exact (trim).
        if ($reason === '(Tanpa Reason)' || $reason === '') {
            $q->whereRaw("LTRIM(RTRIM(ISNULL(h.dpch_resaon,''))) = ''");
        } else {
            $q->whereRaw('LTRIM(RTRIM(h.dpch_resaon)) = ?', [trim($reason)]);
        }

        $rows = $q->select([
                DB::raw('h.Dptch_date     AS dispatch_date'),
                DB::raw('h.Dpcth_code_h   AS dispatch_code'),
                DB::raw('h.dpcth_SO       AS dpcth_so'),
                DB::raw('h.Dpcth_drv_code AS driver_code'),
                DB::raw('drv.Drv_FistName AS driver_name'),
                DB::raw('h.dpch_resaon    AS dpch_resaon'),
            ])
            ->orderByDesc('h.Dptch_date')
            ->orderBy('h.Dpcth_code_h')
            ->orderBy('h.dpcth_SO')
            ->get();

        return response()->json([
            'reason' => $reason,
            'range'  => $range,
            'from'   => $from->format('Y-m-d'),
            'to'     => $to->format('Y-m-d'),
            'rows'   => $rows,
        ]);
    }
}
