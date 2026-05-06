<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PodSummController extends Controller
{
    /**
     * POD Summary — dua chart, masing-masing filter tanggal sendiri.
     *  - Chart 1 (Summary Dispatch)        : range (today/last3/this_week/this_month/this_year)
     *  - Chart 2 (Total Dispatch per Tgl)  : date_from2 / date_to2 (date pickers)
     */
    public function index(Request $request)
    {
        // ===== Range chart 1 (Summary) — pakai dropdown =====
        $allowedRanges = ['today', 'last3', 'last7', 'this_week', 'this_month', 'this_year'];
        $range = $request->input('range', 'this_week');
        if (!in_array($range, $allowedRanges, true)) {
            $range = 'this_week';
        }
        [$dateFrom, $dateTo] = $this->resolveRange($range);

        // ===== Range Dispatch Status — dropdown sendiri =====
        $rangeStatus = $request->input('range_status', 'today');
        if (!in_array($rangeStatus, $allowedRanges, true)) {
            $rangeStatus = 'today';
        }
        [$dateFromS, $dateToS] = $this->resolveRange($rangeStatus);

        // ===== Range chart 2 (Per Tanggal) — date pickers, default 7 hari terakhir =====
        $dateFrom2 = $request->filled('date_from2')
            ? Carbon::parse($request->date_from2)->startOfDay()
            : Carbon::today()->subDays(6)->startOfDay();
        $dateTo2 = $request->filled('date_to2')
            ? Carbon::parse($request->date_to2)->endOfDay()
            : Carbon::today()->endOfDay();

        // ===== Range chart 3 (Dispatch Value) — date pickers, default 7 hari terakhir =====
        $dateFrom3 = $request->filled('date_from3')
            ? Carbon::parse($request->date_from3)->startOfDay()
            : Carbon::today()->subDays(6)->startOfDay();
        $dateTo3 = $request->filled('date_to3')
            ? Carbon::parse($request->date_to3)->endOfDay()
            : Carbon::today()->endOfDay();

        // ===== Chart 1: Total Invoice — COUNT row dari TGU_dispatch_h per status =====
        // Kolom dpch_status berisi: "Open", "Delivered", "Cancel".
        // TOTAL = jumlah seluruh row di TGU_dispatch_h dalam range tanggal.
        $invNormExpr = "LOWER(LTRIM(RTRIM(REPLACE(REPLACE(REPLACE(COALESCE(h.dpch_status, ''), CHAR(9), ''), CHAR(10), ''), CHAR(13), ''))))";

        $invRows = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_h as h')
            ->whereBetween('h.Dptch_date', [$dateFrom, $dateTo])
            ->selectRaw("$invNormExpr as status, COUNT(*) as c")
            ->groupBy(DB::raw($invNormExpr))
            ->get();

        $invAliasMap = [
            // OPEN
            'open' => 'open', 'opend' => 'open', 'opened' => 'open', 'buka' => 'open',
            // DELIVERED
            'delivered' => 'delivered', 'delivery' => 'delivered',
            'deliver'   => 'delivered', 'terkirim'  => 'delivered',
            // CANCEL
            'cancel' => 'cancel', 'canceled' => 'cancel',
            'cancelled' => 'cancel', 'batal' => 'cancel',
        ];

        $invCounts = ['open' => 0, 'delivered' => 0, 'cancel' => 0, 'total' => 0];
        foreach ($invRows as $r) {
            $invCounts['total'] += (int) $r->c; // Total = semua row apa pun statusnya
            $bucket = $invAliasMap[$r->status] ?? null;
            if ($bucket !== null && isset($invCounts[$bucket])) {
                $invCounts[$bucket] += (int) $r->c;
            }
        }

        $summary = [
            'total_dispatch' => $invCounts['total'],
            'sum_total'      => $invCounts['total'],
            'sum_open'       => $invCounts['open'],
            'sum_delivered'  => $invCounts['delivered'],
            'sum_cancel'     => $invCounts['cancel'],
        ];

        // ===== Status counts =====
        // Hitung jumlah row di TGU_dispatch_main per status (open/close/planning/...)
        // berdasarkan tanggal di m.dptch_date. dpch_status & dptch_date sama-sama
        // ada di TGU_dispatch_main, jadi tidak perlu join ke header.
        // Normalisasi: trim spasi/tab/CR/LF lalu lowercase.
        $normExpr = "LOWER(LTRIM(RTRIM(REPLACE(REPLACE(REPLACE(COALESCE(m.dpch_status, ''), CHAR(9), ''), CHAR(10), ''), CHAR(13), ''))))";

        $statusRows = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_main as m')
            ->whereBetween('m.dptch_date', [$dateFromS, $dateToS])
            ->selectRaw("$normExpr as status, COUNT(*) as c")
            ->groupBy(DB::raw($normExpr))
            ->get();

        $statusMap = [
            'open'       => 0,
            'close'      => 0,
            'planning'   => 0,
            'delivered'  => 0,
            'cancel'     => 0,
            'reschedule' => 0,
        ];

        // Map alias -> bucket utama
        $aliasMap = [
            // OPEN
            'open'        => 'open',
            'opend'       => 'open',
            'opened'      => 'open',
            'buka'        => 'open',
            // CLOSE
            'close'       => 'close',
            'closed'      => 'close',
            'tutup'       => 'close',
            'selesai'     => 'close',
            'finish'      => 'close',
            'finished'    => 'close',
            'done'        => 'close',
            // PLANNING
            'planning'    => 'planning',
            'plan'        => 'planning',
            'planed'      => 'planning',
            'planing'     => 'planning',
            'planned'     => 'planning',
            'rencana'     => 'planning',
            // DELIVERED
            'delivered'   => 'delivered',
            'delivery'    => 'delivered',
            'deliver'     => 'delivered',
            'terkirim'    => 'delivered',
            // CANCEL
            'cancel'      => 'cancel',
            'canceled'    => 'cancel',
            'cancelled'   => 'cancel',
            'batal'       => 'cancel',
            // RESCHEDULE
            'reschedule'  => 'reschedule',
            'rescheduled' => 'reschedule',
            'reschedul'   => 'reschedule',
            'reschedulle' => 'reschedule',
        ];

        foreach ($statusRows as $r) {
            $key = $aliasMap[$r->status] ?? null;
            if ($key !== null && isset($statusMap[$key])) {
                $statusMap[$key] += (int) $r->c;
            }
        }
        $statusMap['total'] = array_sum($statusMap);

        // ===== Chart 2: Total Dispatch per tanggal — pengiriman SELESAI saja =====
        // Dari TGU_dispatch_main, COUNT(DISTINCT Dpcth_code_h) supaya 1 dispatch
        // dihitung 1× walau header punya banyak invoice.
        $perDay = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_main as m')
            ->whereBetween('m.dptch_date', [$dateFrom2, $dateTo2])
            ->selectRaw('CAST(m.dptch_date AS DATE) as d, COUNT(DISTINCT m.Dpcth_code_h) as c')
            ->groupBy(DB::raw('CAST(m.dptch_date AS DATE)'))
            ->orderBy('d')
            ->get();

        $countByDate = [];
        foreach ($perDay as $r) {
            $countByDate[Carbon::parse($r->d)->format('Y-m-d')] = (int) $r->c;
        }
        $perDateLabels = [];
        $perDateCounts = [];
        $cursor = $dateFrom2->copy()->startOfDay();
        $end    = $dateTo2->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $perDateLabels[] = $cursor->format('d/m/Y');
            $perDateCounts[] = $countByDate[$key] ?? 0;
            $cursor->addDay();
        }

        // ===== Chart 3: SUM(dpch_value) per tanggal — langsung dari TGU_dispatch_main =====
        // Tanpa join ke TGU_dispatch_h supaya tidak terjadi fan-out / row hilang.
        $perDayValue = DB::connection('rcm_hgs')
            ->table('TGU_dispatch_main as m')
            ->whereBetween('m.dptch_date', [$dateFrom3, $dateTo3])
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
        $cursor = $dateFrom3->copy()->startOfDay();
        $end    = $dateTo3->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $valueLabels[] = $cursor->format('d/m/Y');
            $valueData[]   = $valueByDate[$key] ?? 0;
            $cursor->addDay();
        }

        return view('otherreport.pod_summary', compact(
            'summary',
            'statusMap',
            'range', 'rangeStatus',
            'dateFrom',  'dateTo',
            'dateFromS', 'dateToS',
            'dateFrom2', 'dateTo2',
            'dateFrom3', 'dateTo3',
            'perDateLabels', 'perDateCounts',
            'valueLabels', 'valueData'
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
