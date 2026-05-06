<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchTrackController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->format('Y-m-d');

        $dispatches = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_h as h')
            ->leftJoin('ms_driver as drv', 'drv.drv_id', '=', 'h.Dpcth_drv_code')
            ->select([
                'h.Dpcth_code_h as dispatch_code',
                'h.Dptch_date   as dispatch_date',
                'h.Dpcth_drv_code as driver_code',
                'h.Dpcth_vhcl_code as vhcl_code',
                DB::raw("ISNULL(NULLIF(LTRIM(RTRIM(drv.Drv_FistName)),''), h.Dpcth_drv_code) as driver_name"),
            ])
            ->whereNotNull('h.Dpcth_code_h')
            ->where('h.Dpcth_code_h', '<>', '')
            ->whereRaw('CAST(h.Dptch_date AS DATE) = ?', [$today])
            ->groupBy(
                'h.Dpcth_code_h',
                'h.Dptch_date',
                'h.Dpcth_drv_code',
                'h.Dpcth_vhcl_code',
                'drv.Drv_FistName'
            )
            ->orderBy('h.Dpcth_code_h')
            ->get();

        return view('otherreport.dispatch_track', compact('dispatches', 'today'));
    }

    public function detail(Request $request)
    {
        $code = trim((string) $request->query('dispatch_code', ''));
        if ($code === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_h as h')
            ->where('h.Dpcth_code_h', $code)
            ->select([
                DB::raw('h.dpcth_SO       AS dpcth_so'),
                DB::raw('h.dpch_status    AS dpch_status'),
                DB::raw('h.REC_DATEUPDATE AS rec_dateupdate'),
            ])
            ->orderBy('h.REC_DATEUPDATE')
            ->get();

        return response()->json(['rows' => $rows]);
    }
}
