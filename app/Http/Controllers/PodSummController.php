<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PodSummController extends Controller
{
    /**
     * POD Summary — single global date filter applies to all charts.
     */
    public function index(Request $request)
    {
        // ===== Single global date range =====
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today()->subDays(6)->startOfDay();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->endOfDay();

        // ===== Koneksi DB: HGS atau TGU dari session =====
        $dbConn = session('report_db', 'hgs') === 'tgu' ? 'rcm_ol_tgu' : 'rcm_hgs';

        // ===== Filter dpch_type (global, berlaku untuk semua chart) =====
        $selectedType = trim((string) $request->input('dpch_type', ''));

        // Ambil list dpch_type unik untuk dropdown
        $dpchTypes = DB::connection($dbConn)
            ->table('TGU_dispatch_h')
            ->whereNotNull('dpch_type')
            ->where('dpch_type', '<>', '')
            ->distinct()
            ->orderBy('dpch_type')
            ->pluck('dpch_type');

        // ===== Chart 1: Total Invoice — COUNT dispatch UNIK dari TGU_dispatch_h =====
        $invNormExpr = "LOWER(LTRIM(RTRIM(REPLACE(REPLACE(REPLACE(COALESCE(h.dpch_status, ''), CHAR(9), ''), CHAR(10), ''), CHAR(13), ''))))";

        $invRows = DB::connection($dbConn)
            ->table('TGU_dispatch_h as h')
            ->whereRaw('CAST(h.Dptch_date AS DATE) BETWEEN ? AND ?', [
                $dateFrom->format('Y-m-d'),
                $dateTo->format('Y-m-d'),
            ])
            ->when($selectedType !== '', fn($q) => $q->where('h.dpch_type', $selectedType))
            ->selectRaw("$invNormExpr as status, COUNT(DISTINCT h.Dpcth_code_h) as c")
            ->groupBy(DB::raw($invNormExpr))
            ->get();

        $invAliasMap = [
            'open' => 'open', 'opend' => 'open', 'opened' => 'open', 'buka' => 'open',
            'delivered' => 'delivered', 'delivery' => 'delivered',
            'deliver'   => 'delivered', 'terkirim'  => 'delivered',
            'cancel' => 'cancel', 'canceled' => 'cancel',
            'cancelled' => 'cancel', 'batal' => 'cancel',
        ];

        $invCounts = ['open' => 0, 'delivered' => 0, 'cancel' => 0, 'total' => 0];
        foreach ($invRows as $r) {
            $bucket = $invAliasMap[$r->status] ?? null;
            if ($bucket !== null && isset($invCounts[$bucket])) {
                $invCounts[$bucket] += (int) $r->c;
            }
        }
        $invCounts['total'] = (int) DB::connection($dbConn)
            ->table('TGU_dispatch_h as h')
            ->whereRaw('CAST(h.Dptch_date AS DATE) BETWEEN ? AND ?', [
                $dateFrom->format('Y-m-d'),
                $dateTo->format('Y-m-d'),
            ])
            ->when($selectedType !== '', fn($q) => $q->where('h.dpch_type', $selectedType))
            ->distinct()
            ->count('h.Dpcth_code_h');

        $summary = [
            'total_dispatch' => $invCounts['total'],
            'sum_total'      => $invCounts['total'],
            'sum_open'       => $invCounts['open'],
            'sum_delivered'  => $invCounts['delivered'],
            'sum_cancel'     => $invCounts['cancel'],
        ];

        // ===== Dispatch Status counts — dari TGU_dispatch_main, range sama =====
        $normExpr = "LOWER(LTRIM(RTRIM(REPLACE(REPLACE(REPLACE(COALESCE(m.dpch_status, ''), CHAR(9), ''), CHAR(10), ''), CHAR(13), ''))))";

        $rawStatusRows = DB::connection($dbConn)
            ->table('TGU_dispatch_main as m')
            ->whereRaw('CAST(m.dptch_date AS DATE) BETWEEN ? AND ?', [
                $dateFrom->format('Y-m-d'),
                $dateTo->format('Y-m-d'),
            ])
            ->when($selectedType !== '', fn($q) => $q->where('m.dpch_type', $selectedType))
            ->select('m.Dpcth_code_h')
            ->selectRaw("$normExpr as status")
            ->distinct()
            ->get();

        $statusMap = [
            'open'       => 0,
            'close'      => 0,
            'planning'   => 0,
            'delivered'  => 0,
            'cancel'     => 0,
            'reschedule' => 0,
        ];

        $aliasMap = [
            'open' => 'open', 'opend' => 'open', 'opened' => 'open', 'buka' => 'open',
            'close' => 'close', 'closed' => 'close', 'tutup' => 'close',
            'selesai' => 'close', 'finish' => 'close', 'finished' => 'close', 'done' => 'close',
            'planning' => 'planning', 'plan' => 'planning', 'planed' => 'planning',
            'planing' => 'planning', 'planned' => 'planning', 'rencana' => 'planning',
            'delivered' => 'delivered', 'delivery' => 'delivered',
            'deliver' => 'delivered', 'terkirim' => 'delivered',
            'cancel' => 'cancel', 'canceled' => 'cancel', 'cancelled' => 'cancel', 'batal' => 'cancel',
            'reschedule' => 'reschedule', 'rescheduled' => 'reschedule',
            'reschedul' => 'reschedule', 'reschedulle' => 'reschedule',
        ];

        $priority = [
            'open' => 1, 'planning' => 2, 'reschedule' => 3,
            'delivered' => 4, 'close' => 5, 'cancel' => 6,
        ];

        $bucketPerDispatch = [];
        foreach ($rawStatusRows as $r) {
            $code = $r->Dpcth_code_h;
            if ($code === null || $code === '') continue;
            $bucket = $aliasMap[$r->status] ?? null;
            if ($bucket === null) continue;
            $cur = $bucketPerDispatch[$code] ?? null;
            if ($cur === null || ($priority[$bucket] ?? 99) < ($priority[$cur] ?? 99)) {
                $bucketPerDispatch[$code] = $bucket;
            }
        }
        foreach ($bucketPerDispatch as $bucket) {
            if (isset($statusMap[$bucket])) $statusMap[$bucket]++;
        }
        $statusMap['total'] = array_sum($statusMap);

        // ===== Chart 2: COUNT dispatch per tanggal =====
        $perDay = DB::connection($dbConn)
            ->table('TGU_dispatch_main as m')
            ->whereBetween('m.dptch_date', [$dateFrom, $dateTo])
            ->when($selectedType !== '', fn($q) => $q->where('m.dpch_type', $selectedType))
            ->selectRaw('CAST(m.dptch_date AS DATE) as d, COUNT(m.Dpcth_code_h) as c')
            ->groupBy(DB::raw('CAST(m.dptch_date AS DATE)'))
            ->orderBy('d')
            ->get();

        $countByDate = [];
        foreach ($perDay as $r) {
            $countByDate[Carbon::parse($r->d)->format('Y-m-d')] = (int) $r->c;
        }
        $perDateLabels = [];
        $perDateCounts = [];
        $cursor = $dateFrom->copy()->startOfDay();
        $end    = $dateTo->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $perDateLabels[] = $cursor->format('d/m/Y');
            $perDateCounts[] = $countByDate[$key] ?? 0;
            $cursor->addDay();
        }

        // ===== Chart 3: SUM dispatch value per tanggal =====
        $perDayValue = DB::connection($dbConn)
            ->table('TGU_dispatch_main as m')
            ->whereBetween('m.dptch_date', [$dateFrom, $dateTo])
            ->when($selectedType !== '', fn($q) => $q->where('m.dpch_type', $selectedType))
            ->selectRaw('CAST(m.dptch_date AS DATE) as d, COALESCE(SUM(m.dpch_value), 0) as v')
            ->groupBy(DB::raw('CAST(m.dptch_date AS DATE)'))
            ->orderBy('d')
            ->get();

        $valueByDate = [];
        foreach ($perDayValue as $r) {
            $valueByDate[Carbon::parse($r->d)->format('Y-m-d')] = (float) $r->v;
        }
        $valueLabels = [];
        $valueData   = [];
        $cursor = $dateFrom->copy()->startOfDay();
        $end    = $dateTo->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $valueLabels[] = $cursor->format('d/m/Y');
            $valueData[]   = $valueByDate[$key] ?? 0;
            $cursor->addDay();
        }

        $reportDb = session('report_db', 'hgs');

        return view('otherreport.pod_summary', compact(
            'summary',
            'statusMap',
            'dateFrom', 'dateTo',
            'perDateLabels', 'perDateCounts',
            'valueLabels', 'valueData',
            'dpchTypes', 'selectedType',
            'reportDb'
        ));
    }

    /**
     * Translate a preset range key into [Carbon $from, Carbon $to].
     */
    private function resolveRange(string $range): array
    {
        $today = Carbon::today();
        switch ($range) {
            case 'today':
                return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
            case 'last3':
                // 3 hari terakhir termasuk hari ini
                return [$today->copy()->subDays(2)->startOfDay(), $today->copy()->endOfDay()];
            case 'last7':
                // 7 hari terakhir termasuk hari ini
                return [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()];
            case 'this_month':
                return [$today->copy()->startOfMonth(), $today->copy()->endOfDay()];
            case 'this_year':
                return [$today->copy()->startOfYear(), $today->copy()->endOfDay()];
            case 'this_week':
            default:
                // Senin sebagai awal minggu (id_ID)
                return [$today->copy()->startOfWeek(Carbon::MONDAY), $today->copy()->endOfDay()];
        }
    }
}
