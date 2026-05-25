<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchTrackController extends Controller
{
    public function index(Request $request)
    {
        $today  = now()->format('Y-m-d');
        $dbConn = session('report_db', 'hgs') === 'tgu' ? 'rcm_ol_tgu' : 'rcm_hgs';
        $reportDb = session('report_db', 'hgs');

        // Batasi payload list supaya render awal tetap cepat.
        $listLimit = 600;

        // Kode dispatch valid: hanya yang punya REC_DATEUPDATE >= 2026-01-01.
        $validDispatch = DB::connection($dbConn)
            ->table('TGU_dispatch_h as h')
            ->select([
                'h.Dpcth_code_h',
                DB::raw('MAX(h.REC_DATEUPDATE) as last_update'),
            ])
            ->whereNotNull('h.Dpcth_code_h')
            ->where('h.Dpcth_code_h', '<>', '')
            ->where('h.REC_DATEUPDATE', '>=', '2026-01-01')
            ->groupBy('h.Dpcth_code_h');

        // Dispatch list: join ke dispatch valid agar query tidak scan korelatif per-row.
        $dispatches = DB::connection($dbConn)
            ->table('TGU_dispatch_main as m')
            ->joinSub($validDispatch, 'vh', function ($join) {
                $join->on('vh.Dpcth_code_h', '=', 'm.Dpcth_code_h');
            })
            ->leftJoin('ms_driver as drv', 'drv.drv_id', '=', 'm.Dpcth_drv_code')
            ->select([
                'm.Dpcth_code_h as dispatch_code',
                DB::raw('CAST(m.dptch_date AS DATE) as dispatch_date'),
                'm.Dpcth_drv_code as driver_code',
                'm.Dpcth_vhcl_code as vhcl_code',
                DB::raw('vh.last_update as last_update'),
                DB::raw("ISNULL(NULLIF(LTRIM(RTRIM(drv.Drv_FistName)),''), m.Dpcth_drv_code) as driver_name"),
            ])
            ->whereNotNull('m.Dpcth_code_h')
            ->where('m.Dpcth_code_h', '<>', '')
            ->whereRaw("CAST(m.dptch_date AS DATE) >= '2026-01-01'")
            ->groupBy(
                'm.Dpcth_code_h',
                DB::raw('CAST(m.dptch_date AS DATE)'),
                'm.Dpcth_drv_code',
                'm.Dpcth_vhcl_code',
                'vh.last_update',
                'drv.Drv_FistName'
            )
            ->orderByRaw('CAST(m.dptch_date AS DATE) DESC')
            ->orderBy('m.Dpcth_code_h')
            ->limit($listLimit)
            ->get();

        // Group berdasarkan tanggal (Y-m-d) — terbaru dulu.
        $grouped = $dispatches->groupBy(function ($row) {
            return \Carbon\Carbon::parse($row->dispatch_date)->format('Y-m-d');
        });

        // Penggunaan Handheld — sumber: TGU_dispatch_main (hari ini).
        // driver: m.Dpcth_drv_code, scan: m.dpch_pod_android.
        $handheld = DB::connection($dbConn)
            ->table('TGU_dispatch_main as m')
            ->leftJoin('ms_driver as drv', 'drv.drv_id', '=', 'm.Dpcth_drv_code')
            ->whereRaw('CAST(m.dptch_date AS DATE) = ?', [$today])
            ->whereNotNull('m.Dpcth_drv_code')
            ->where('m.Dpcth_drv_code', '<>', '')
            ->select([
                'm.Dpcth_drv_code as driver_code',
                DB::raw("ISNULL(NULLIF(LTRIM(RTRIM(drv.Drv_FistName)),''), m.Dpcth_drv_code) as driver_name"),
                DB::raw('SUM(CASE WHEN m.dpch_pod_android IS NULL OR LTRIM(RTRIM(CAST(m.dpch_pod_android AS NVARCHAR(50)))) = \'\' THEN 0 ELSE 1 END) as scan_count'),
            ])
            ->groupBy('m.Dpcth_drv_code', 'drv.Drv_FistName')
            ->orderBy('driver_name')
            ->get();

        return view('otherreport.dispatch_track', [
            'dispatches'        => $dispatches,
            'dispatchesGrouped' => $grouped,
            'handheld'          => $handheld,
            'today'             => $today,
            'reportDb'          => $reportDb,
        ]);
    }

    public function detail(Request $request)
    {
        $code = trim((string) $request->query('dispatch_code', ''));
        if ($code === '') {
            return response()->json(['rows' => []]);
        }

        $dbConn = session('report_db', 'hgs') === 'tgu' ? 'rcm_ol_tgu' : 'rcm_hgs';

        $rows = DB::connection($dbConn)
            ->table('TGU_dispatch_h as h')
            ->where('h.Dpcth_code_h', $code)
            ->select([
                DB::raw('h.dpcth_SO       AS dpcth_so'),
                DB::raw('h.dpch_status    AS dpch_status'),
                DB::raw('h.REC_DATEUPDATE AS rec_dateupdate'),
                DB::raw('h.Dptch_mobile_pod_delivery_time AS delivered_time'),
                DB::raw('h.dpch_resaon AS dpch_resaon'),
            ])
            ->orderBy('h.REC_DATEUPDATE')
            ->get();

        return response()->json(['rows' => $rows]);
    }
}
