<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PodDetController extends Controller
{
    /**
     * Column whitelist. 'd.*' columns are referenced via OUTER APPLY alias `d`,
     * not via a real LEFT JOIN, to avoid header→detail row fan-out.
     */
    private function colMap(): array
    {
        return [
            'dptch_date'                 => 'h.Dptch_date',
            'dptch_vhcl_code'            => 'h.Dpcth_vhcl_code',
            'dptch_drv_code'             => 'h.Dpcth_drv_code',
            'dptch_code_h'               => 'm.Dpcth_code_h',
            'dptch_SO'                   => 'd.dptch_SO',
            'dpch_type'                  => 'h.dpch_type',
            'dpch_status'                => 'h.dpch_status',
            'dpch_dispach_inv_total'     => 'm.dpch_dispach_inv_total',
            'dpch_dispach_inv_cash'      => 'm.dpch_dispach_inv_cash',
            'dpch_dispach_inv_cancel'    => 'm.dpch_dispach_inv_cancel',
            'dpch_dispach_inv_reschedule'=> 'm.dpch_dispach_inv_reschedule',
            'dpch_resaon'                => 'h.dpch_resaon',
        ];
    }

    /**
     * Build the base query: header + main joined, dispatch_d via OUTER APPLY (TOP 1)
     * so each header produces exactly one row (no fan-out).
     */
    private function dbConn(): string
    {
        return session('report_db', 'hgs') === 'tgu' ? 'rcm_ol_tgu' : 'rcm_hgs';
    }

    private function baseQuery($dateFrom, $dateTo, string $filterCol, string $filterVal, string $dbConn = '')
    {
        if ($dbConn === '') {
            $dbConn = $this->dbConn();
        }
        $colMap = $this->colMap();

        $q = DB::connection($dbConn)
            ->table(DB::raw('(
                SELECT * FROM (
                    SELECT
                        h0.*,
                        ROW_NUMBER() OVER (PARTITION BY h0.Dpcth_code_h ORDER BY h0.Dptch_date DESC) AS __rn
                    FROM TGU_dispatch_h h0
                ) hx WHERE hx.__rn = 1
            ) h
                INNER JOIN (
                    SELECT
                        Dpcth_code_h,
                        SUM(dpch_dispach_inv_total)      AS dpch_dispach_inv_total,
                        SUM(dpch_dispach_inv_cash)       AS dpch_dispach_inv_cash,
                        SUM(dpch_dispach_inv_cancel)     AS dpch_dispach_inv_cancel,
                        SUM(dpch_dispach_inv_reschedule) AS dpch_dispach_inv_reschedule
                    FROM TGU_dispatch_main
                    GROUP BY Dpcth_code_h
                ) m ON h.Dpcth_code_h = m.Dpcth_code_h
                LEFT JOIN (
                    SELECT
                        Dpcth_code_h,
                        COUNT(DISTINCT dpcth_SO) AS so_count,
                        COUNT(DISTINCT CASE
                            WHEN UPPER(LTRIM(RTRIM(dpch_status))) = '."'DELIVERED'".'
                            THEN dpcth_SO END) AS so_delivered
                    FROM TGU_dispatch_h
                    GROUP BY Dpcth_code_h
                ) ms ON ms.Dpcth_code_h = h.Dpcth_code_h
                OUTER APPLY (
                    SELECT TOP 1 dptch_SO
                    FROM TGU_dispatch_d d2
                    WHERE d2.dptch_code_h = h.Dpcth_code_h
                ) d'))
            ->when($dateFrom, fn($qq) => $qq->where('h.Dptch_date', '>=', $dateFrom))
            ->where('h.Dptch_date', '<=', $dateTo);

        if ($filterCol !== '' && $filterVal !== '' && isset($colMap[$filterCol])) {
            $escaped = str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $filterVal);
            $q->whereRaw("CAST({$colMap[$filterCol]} AS NVARCHAR(MAX)) COLLATE SQL_Latin1_General_CP1_CI_AS LIKE ?", ["%{$escaped}%"]);
        }

        return $q;
    }

    public function index(Request $request)
    {
        // Default: last 30 days. Without a lower bound the query scans full history.
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today()->subDays(30)->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->endOfDay();

        $view = $request->input('view') === 'invoice' ? 'invoice' : 'pod';

        $colMap    = $this->colMap();
        $filterCol = $request->input('filter_col', '');
        $filterVal = trim((string) $request->input('filter_val', ''));
        if (!array_key_exists($filterCol, $colMap)) $filterCol = '';

        $perPage = in_array((int) $request->input('per_page'), [20, 25, 50, 100])
            ? (int) $request->input('per_page')
            : 25;
        $page    = max(1, (int) $request->input('page', 1));

        // Cap at 1000 rows total. Fetch only the current page (+1 to detect more).
        $maxRows = 1000;
        $offset  = ($page - 1) * $perPage;

        // If we're already past the cap, return empty page.
        $remaining = max(0, $maxRows - $offset);
        $fetchN    = min($perPage + 1, $remaining);

        $items = collect();
        if ($fetchN > 0) {
            if ($view === 'invoice') {
                $items = $this->baseQuery($dateFrom, $dateTo, $filterCol, $filterVal)
                    ->select([
                        DB::raw('h.Dptch_date              as dptch_date'),
                        DB::raw('h.Dpcth_code_h            as dptch_code_h'),
                        DB::raw('h.rec_comcode             as rec_comcode'),
                        DB::raw('h.Dpcth_vhcl_code         as dpcth_vhcl_code'),
                        DB::raw('h.Dpcth_drv_code          as dpcth_driver_code'),
                        DB::raw('h.dpch_status             as dpch_status'),
                        DB::raw('h.dpch_salesresentatip    as dpch_salesresentatip'),
                        DB::raw('h.dpch_value              as dpch_value'),
                    ])
                    ->orderByDesc('h.Dptch_date')
                    ->offset($offset)
                    ->limit($fetchN)
                    ->get();
            } else {
                $items = $this->baseQuery($dateFrom, $dateTo, $filterCol, $filterVal)
                    ->select([
                        DB::raw('h.Dptch_date                   as dptch_date'),
                        DB::raw('h.Dpcth_vhcl_code              as dptch_vhcl_code'),
                        DB::raw('h.Dpcth_drv_code               as dptch_drv_code'),
                        DB::raw('m.Dpcth_code_h                 as dptch_code_h'),
                        DB::raw('d.dptch_SO                     as dptch_SO'),
                        DB::raw('h.dpch_type                    as dpch_type'),
                        DB::raw('h.dpch_status                  as dpch_status'),
                        DB::raw('ISNULL(ms.so_count, 0)         as dpch_dispach_inv_total'),
                        DB::raw('ISNULL(ms.so_delivered, 0)     as dpch_dispach_inv_cash'),
                        DB::raw('m.dpch_dispach_inv_cancel      as dpch_dispach_inv_cancel'),
                        DB::raw('m.dpch_dispach_inv_reschedule  as dpch_dispach_inv_reschedule'),
                        DB::raw('h.dpch_resaon                  as dpch_resaon'),
                    ])
                    ->orderByDesc('h.Dptch_date')
                    ->offset($offset)
                    ->limit($fetchN)
                    ->get();
            }
        }

        $hasMore = $items->count() > $perPage;
        $items   = $items->take($perPage)->values();

        // Approximate total: enough to know if there's a next page without a COUNT(*).
        $total = $hasMore ? min($maxRows, $offset + $perPage + 1) : $offset + $items->count();

        $rows = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $reportDb = session('report_db', 'hgs');
        return view('otherreport.pod', compact('rows', 'dateFrom', 'dateTo', 'filterCol', 'filterVal', 'view', 'reportDb'));
    }

    public function calculate(Request $request)
    {
        $allowed = [
            'dptch_date', 'dptch_vhcl_code', 'dptch_drv_code', 'dptch_code_h',
            'dptch_SO', 'dpch_type', 'dpch_status', 'dpch_dispach_inv_cash',
            'dpch_dispach_inv_total', 'dpch_dispach_inv_cancel', 'dpch_dispach_inv_reschedule',
        ];
        $colMap = [
            'dptch_date'                 => 'h.Dptch_date',
            'dptch_vhcl_code'            => 'h.Dpcth_vhcl_code',
            'dptch_drv_code'             => 'h.Dpcth_drv_code',
            'dptch_code_h'               => 'm.Dpcth_code_h',
            'dptch_SO'                   => 'd.dptch_SO',
            'dpch_type'                  => 'h.dpch_type',
            'dpch_status'                => 'h.dpch_status',
            'dpch_dispach_inv_cash'      => 'm.dpch_dispach_inv_cash',
            'dpch_dispach_inv_total'     => 'm.dpch_dispach_inv_total',
            'dpch_dispach_inv_cancel'    => 'm.dpch_dispach_inv_cancel',
            'dpch_dispach_inv_reschedule'=> 'm.dpch_dispach_inv_reschedule',
            'dpch_resaon'               => 'h.dpch_resaon',
        ];
        $funcs = ['sum', 'count', 'max', 'min'];

        $filterCol  = $request->input('agg_col',   $request->input('col', ''));
        $filterVal  = trim((string) $request->input('agg_value', ''));
        $aggCol     = $request->input('agg_agg_col', $filterCol);
        $aggVal     = trim((string) $request->input('agg_agg_value', ''));
        $func       = strtolower($request->input('agg_func', $request->input('func', 'count')));

        if (!in_array($filterCol, $allowed) || !in_array($func, $funcs)) {
            return response()->json(['error' => 'Parameter tidak valid.'], 422);
        }
        if (!in_array($aggCol, $allowed)) {
            $aggCol = $filterCol;
        }
        if (in_array($func, ['sum', 'max', 'min']) && !$aggCol) {
            return response()->json(['error' => '"di Kolom" wajib diisi untuk ' . strtoupper($func) . '.'], 422);
        }

        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today()->subDays(30)->startOfDay();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->endOfDay();

        $base = $this->baseQuery($dateFrom, $dateTo, $filterCol, $filterVal)
            ->when($aggVal !== '' && $aggCol !== $filterCol, function ($q) use ($colMap, $aggCol, $aggVal) {
                $escaped = str_replace(['%', '_', '['], ['[%]', '[_]', '[[]'], $aggVal);
                $q->whereRaw("CAST({$colMap[$aggCol]} AS NVARCHAR(MAX)) COLLATE SQL_Latin1_General_CP1_CI_AS LIKE ?", ["%{$escaped}%"]);
            });

        $dbAggCol = $colMap[$aggCol];

        try {
            if ($func === 'count') {
                $result = (clone $base)->selectRaw("COUNT({$dbAggCol}) as agg")->value('agg');
            } else {
                $result = (clone $base)->selectRaw("{$func}({$dbAggCol}) as agg")->value('agg');
            }

            return response()->json(['result' => $result ?? 0]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PodDetController::calculate error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Query gagal dijalankan.'], 500);
        }
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today()->subDays(30)->startOfDay();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->endOfDay();

        $colMap    = $this->colMap();
        $filterCol = $request->input('filter_col', '');
        $filterVal = trim((string) $request->input('filter_val', ''));
        if (!array_key_exists($filterCol, $colMap)) $filterCol = '';

        $query = $this->baseQuery($dateFrom, $dateTo, $filterCol, $filterVal)
            ->select([
                DB::raw('h.Dptch_date                   as dptch_date'),
                DB::raw('h.Dpcth_vhcl_code              as dptch_vhcl_code'),
                DB::raw('h.Dpcth_drv_code               as dptch_drv_code'),
                DB::raw('m.Dpcth_code_h                 as dptch_code_h'),
                DB::raw('d.dptch_SO                     as dptch_SO'),
                DB::raw('h.dpch_type                    as dpch_type'),
                DB::raw('h.dpch_status                  as dpch_status'),
                DB::raw('m.dpch_dispach_inv_total       as dpch_dispach_inv_total'),
                DB::raw('m.dpch_dispach_inv_cash        as dpch_dispach_inv_cash'),
                DB::raw('m.dpch_dispach_inv_cancel      as dpch_dispach_inv_cancel'),
                DB::raw('m.dpch_dispach_inv_reschedule  as dpch_dispach_inv_reschedule'),
                DB::raw('h.dpch_resaon                  as dpch_resaon'),
            ])
            ->orderByDesc('h.Dptch_date');

        $headers = ['Dispatch Date','Vehicle Code','Driver Code','Dispatch Code','SO','Type','Status',
                    'Inv Total','Inv Cash','Inv Cancel','Inv Reschedule','Reason'];
        $aliases = ['dptch_date','dptch_vhcl_code','dptch_drv_code','dptch_code_h','dptch_SO',
                    'dpch_type','dpch_status','dpch_dispach_inv_total','dpch_dispach_inv_cash',
                    'dpch_dispach_inv_cancel','dpch_dispach_inv_reschedule','dpch_resaon'];

        $dateLabel = ($dateFrom ? Carbon::parse($dateFrom)->format('Ymd') : 'all')
                   . '_' . Carbon::parse($dateTo)->format('Ymd');
        $filename  = 'pod_report_' . $dateLabel . '.csv';

        return response()->streamDownload(function () use ($query, $headers, $aliases) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM for Excel UTF-8
            fputcsv($out, $headers);

            $query->chunk(500, function ($rows) use ($out, $aliases) {
                foreach ($rows as $row) {
                    $rowArr = (array) $row;
                    fputcsv($out, array_map(function ($alias) use ($rowArr) {
                        $val = $rowArr[$alias] ?? '';
                        if (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}/', $val)) {
                            try {
                                $dt = new \DateTime($val);
                                return $dt->format('H:i:s') === '00:00:00'
                                    ? $dt->format('d/m/Y')
                                    : $dt->format('d/m/Y H:i:s');
                            } catch (\Exception $e) {}
                        }
                        return $val;
                    }, $aliases));
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * AJAX: return detail rows from TGU_dispatch_d for a given dispatch code.
     */
    public function rowDetail(Request $request)
    {
        $code = trim((string) $request->input('dispatch_code', ''));
        if ($code === '') {
            return response()->json(['error' => 'dispatch_code wajib diisi.'], 422);
        }

        $dbConn = $this->dbConn();

        try {
            // Deduplicate dispatch_h by (code_h, SO) before joining to prevent fan-out.
            $rows = DB::connection($dbConn)
                ->table('TGU_dispatch_d as d')
                ->leftJoin(DB::raw('(
                    SELECT *,
                        ROW_NUMBER() OVER (
                            PARTITION BY LTRIM(RTRIM(Dpcth_code_h)), LTRIM(RTRIM(dpcth_SO))
                            ORDER BY Dptch_date DESC
                        ) AS __hrn
                    FROM TGU_dispatch_h
                ) h'), function ($j) {
                    $j->on(DB::raw('LTRIM(RTRIM(h.Dpcth_code_h))'), '=', DB::raw('LTRIM(RTRIM(d.dptch_code_h))'))
                      ->on(DB::raw('LTRIM(RTRIM(h.dpcth_SO))'),     '=', DB::raw('LTRIM(RTRIM(d.dptch_SO))'))
                      ->where('h.__hrn', '=', 1);
                })
                ->whereRaw('LTRIM(RTRIM(d.dptch_code_h)) = ?', [$code])
                ->select([
                    DB::raw('d.dptch_SO                               as so'),
                    DB::raw('d.dptch_product_internal                 as sku'),
                    DB::raw("COALESCE(d.dptch_status, h.dpch_status) as status"),
                    DB::raw('d.dptch_unit_quantity                    as qty'),
                    DB::raw('d.dptch_unit                             as unit'),
                    DB::raw('d.dptch_qty_terima                       as qty_received'),
                    DB::raw('d.dptch_valuebongkaran                   as value_bongkaran'),
                ])
                ->distinct()
                ->orderBy('d.dptch_SO')
                ->orderBy('d.dptch_product_internal')
                ->get();

            return response()->json(['rows' => $rows]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PodDetController::rowDetail error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Gagal mengambil detail: ' . $e->getMessage()], 500);
        }
    }
}
