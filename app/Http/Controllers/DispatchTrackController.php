<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchTrackController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->format('Y-m-d');

        // Semua dispatch yang BELUM SELESAI (masih ada baris dpch_status = 'Open').
        // Sumber: TGU_dispatch_main (kolom: dptch_date, Dpcth_code_h, Dpcth_drv_code).
        // Di-group per tanggal di sisi view.
        $dispatches = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_main as m')
            ->leftJoin('ms_driver as drv', 'drv.drv_id', '=', 'm.Dpcth_drv_code')
            ->select([
                'm.Dpcth_code_h as dispatch_code',
                DB::raw('CAST(m.dptch_date AS DATE) as dispatch_date'),
                'm.Dpcth_drv_code as driver_code',
                'm.Dpcth_vhcl_code as vhcl_code',
                DB::raw("ISNULL(NULLIF(LTRIM(RTRIM(drv.Drv_FistName)),''), m.Dpcth_drv_code) as driver_name"),
            ])
            ->whereNotNull('m.Dpcth_code_h')
            ->where('m.Dpcth_code_h', '<>', '')
            ->where('m.dpch_status', 'Open')
            ->groupBy(
                'm.Dpcth_code_h',
                DB::raw('CAST(m.dptch_date AS DATE)'),
                'm.Dpcth_drv_code',
                'm.Dpcth_vhcl_code',
                'drv.Drv_FistName'
            )
            ->orderByRaw('CAST(m.dptch_date AS DATE) DESC')
            ->orderBy('m.Dpcth_code_h')
            ->get();

        // Group berdasarkan tanggal (Y-m-d) — terbaru dulu.
        $grouped = $dispatches->groupBy(function ($row) {
            return \Carbon\Carbon::parse($row->dispatch_date)->format('Y-m-d');
        });

        // Penggunaan Handheld — sumber: TGU_dispatch_main (hari ini).
        // driver: m.Dpcth_drv_code, scan: m.dpch_pod_android.
        $handheld = DB::connection('rcm_hgs')
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
        ]);
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
                DB::raw('h.Dptch_mobile_pod_delivery_time AS delivered_time'),
            ])
            ->orderBy('h.REC_DATEUPDATE')
            ->get();

        return response()->json(['rows' => $rows]);
    }
}
