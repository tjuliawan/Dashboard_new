<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;


class SKU_Controller extends Controller
{
    public function index()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('sku.index', compact('user') );
    }
    public function table_sku(Request $request)
    {
        // $startDate = $request->input('startDate');
        // $endDate = $request->input('endDate');
        // $gantungan = $request->input('gantungan');
        // dd($startDate, $endDate);
        // if($gantungan == 'true'){
        //     $startDateCondition = "";
        //     if ($startDate == '' && $endDate == '') {
        //         $startDateCondition = "AND date(updated_at) = CURDATE()";
        //     } 
        //     if ($startDate != '') {
        //         $startDateCondition = "AND date(updated_at) >= '${startDate}'";
        //     } 
        //     $endDateCondition = "";
        //     if ($endDate != '') {
        //         $endDateCondition = "AND date(updated_at) <= '${endDate}'";
        //     }
        // }else{
        //     $startDateCondition = "";
        //     if ($startDate == '' && $endDate == '') {
        //         $startDateCondition = "AND date(created_at) = CURDATE()";
        //     } 
        //     if ($startDate != '') {
        //         $startDateCondition = "AND date(created_at) >= '${startDate}'";
        //     } 
        //     $endDateCondition = "";
        //     if ($endDate != '') {
        //         $endDateCondition = "AND date(created_at) <= '${endDate}'";
        //     } 
        // }
        // dd($startDateCondition);
        $data = DB::connection('mc')->select(" 		
            SELECT
                *
            FROM 
            Ms_SKU_Central 
        
        ");
        // dd($data); 
        // dd($branches); 
        return response()->json($data);
    }
    public function chart_sales(Request $request)
    {
        $data_Nutricia = collect(DB::connection('ms_sql_hgs')->select("
            WITH Months AS (
                SELECT 1 AS MonthNumber, 'Januari' AS Bulan
                UNION ALL
                SELECT 2, 'Februari'
                UNION ALL
                SELECT 3, 'Maret'
                UNION ALL
                SELECT 4, 'April'
                UNION ALL
                SELECT 5, 'Mei'
                UNION ALL
                SELECT 6, 'Juni'
                UNION ALL
                SELECT 7, 'Juli'
                UNION ALL
                SELECT 8, 'Agustus'
                UNION ALL
                SELECT 9, 'September'
                UNION ALL
                SELECT 10, 'Oktober'
                UNION ALL
                SELECT 11, 'November'
                UNION ALL
                SELECT 12, 'Desember'
            ),
            Data AS (
                SELECT
                    MONTH(transcoa_coa_date) AS BulanNumber,
                    CASE MONTH(transcoa_coa_date)
                        WHEN 1 THEN 'Januari'
                        WHEN 2 THEN 'Februari'
                        WHEN 3 THEN 'Maret'
                        WHEN 4 THEN 'April'
                        WHEN 5 THEN 'Mei'
                        WHEN 6 THEN 'Juni'
                        WHEN 7 THEN 'Juli'
                        WHEN 8 THEN 'Agustus'
                        WHEN 9 THEN 'September'
                        WHEN 10 THEN 'Oktober'
                        WHEN 11 THEN 'November'
                        WHEN 12 THEN 'Desember'
                    END AS Bulan,
                    SUM(transcoa_credit_value) AS Total
                FROM tr_acc_transaksi_coa
                where transcoa_coa_code in ('678', '735')
                AND YEAR(transcoa_coa_date) = YEAR(GETDATE())
                AND MONTH(transcoa_coa_date) <= MONTH(GETDATE())
                GROUP BY MONTH(transcoa_coa_date)
            )
            SELECT
                M.Bulan,
                ISNULL(D.Total, 0) AS Total
            FROM Months M
            LEFT JOIN Data D
                ON M.MonthNumber = D.BulanNumber
            WHERE M.MonthNumber <= MONTH(GETDATE())
            ORDER BY M.MonthNumber;
        "));
        $data_JAPFA = collect(DB::connection('ms_sql_hgs')->select("
            WITH Months AS (
                SELECT 1 AS MonthNumber, 'Januari' AS Bulan
                UNION ALL
                SELECT 2, 'Februari'
                UNION ALL
                SELECT 3, 'Maret'
                UNION ALL
                SELECT 4, 'April'
                UNION ALL
                SELECT 5, 'Mei'
                UNION ALL
                SELECT 6, 'Juni'
                UNION ALL
                SELECT 7, 'Juli'
                UNION ALL
                SELECT 8, 'Agustus'
                UNION ALL
                SELECT 9, 'September'
                UNION ALL
                SELECT 10, 'Oktober'
                UNION ALL
                SELECT 11, 'November'
                UNION ALL
                SELECT 12, 'Desember'
            ),
            Data AS (
                SELECT
                    MONTH(transcoa_coa_date) AS BulanNumber,
                    CASE MONTH(transcoa_coa_date)
                        WHEN 1 THEN 'Januari'
                        WHEN 2 THEN 'Februari'
                        WHEN 3 THEN 'Maret'
                        WHEN 4 THEN 'April'
                        WHEN 5 THEN 'Mei'
                        WHEN 6 THEN 'Juni'
                        WHEN 7 THEN 'Juli'
                        WHEN 8 THEN 'Agustus'
                        WHEN 9 THEN 'September'
                        WHEN 10 THEN 'Oktober'
                        WHEN 11 THEN 'November'
                        WHEN 12 THEN 'Desember'
                    END AS Bulan,
                    SUM(transcoa_credit_value) AS Total
                FROM tr_acc_transaksi_coa
                where transcoa_coa_code = '735'
                AND YEAR(transcoa_coa_date) = YEAR(GETDATE())
                AND MONTH(transcoa_coa_date) <= MONTH(GETDATE())
                and rec_comcode like'%HGS PUSAT%'
                GROUP BY MONTH(transcoa_coa_date)
            )
            SELECT
                M.Bulan,
                ISNULL(D.Total, 0) AS Total
            FROM Months M
            LEFT JOIN Data D
                ON M.MonthNumber = D.BulanNumber
            WHERE M.MonthNumber <= MONTH(GETDATE())
            ORDER BY M.MonthNumber;
        "));
        $data_Agriaku = collect(DB::connection('ms_sql_hgs')->select("
            WITH Months AS (
                SELECT 1 AS MonthNumber, 'Januari' AS Bulan
                UNION ALL
                SELECT 2, 'Februari'
                UNION ALL
                SELECT 3, 'Maret'
                UNION ALL
                SELECT 4, 'April'
                UNION ALL
                SELECT 5, 'Mei'
                UNION ALL
                SELECT 6, 'Juni'
                UNION ALL
                SELECT 7, 'Juli'
                UNION ALL
                SELECT 8, 'Agustus'
                UNION ALL
                SELECT 9, 'September'
                UNION ALL
                SELECT 10, 'Oktober'
                UNION ALL
                SELECT 11, 'November'
                UNION ALL
                SELECT 12, 'Desember'
            ),
            Data AS (
                SELECT
                    MONTH(transcoa_coa_date) AS BulanNumber,
                    CASE MONTH(transcoa_coa_date)
                        WHEN 1 THEN 'Januari'
                        WHEN 2 THEN 'Februari'
                        WHEN 3 THEN 'Maret'
                        WHEN 4 THEN 'April'
                        WHEN 5 THEN 'Mei'
                        WHEN 6 THEN 'Juni'
                        WHEN 7 THEN 'Juli'
                        WHEN 8 THEN 'Agustus'
                        WHEN 9 THEN 'September'
                        WHEN 10 THEN 'Oktober'
                        WHEN 11 THEN 'November'
                        WHEN 12 THEN 'Desember'
                    END AS Bulan,
                    SUM(transcoa_credit_value) AS Total
                FROM tr_acc_transaksi_coa
                where transcoa_coa_code = '735'
                AND YEAR(transcoa_coa_date) = YEAR(GETDATE())
                AND MONTH(transcoa_coa_date) <= MONTH(GETDATE())
                and rec_comcode like'%BDG%'
                GROUP BY MONTH(transcoa_coa_date)
            )
            SELECT
                M.Bulan,
                ISNULL(D.Total, 0) AS Total
            FROM Months M
            LEFT JOIN Data D
                ON M.MonthNumber = D.BulanNumber
            WHERE M.MonthNumber <= MONTH(GETDATE())
            ORDER BY M.MonthNumber;
        "));   
    
        $price_Nutricia = $data_Nutricia->pluck('Total');
        $price_JAPFA = $data_JAPFA->pluck('Total');
        $price_Agriaku = $data_Agriaku->pluck('Total');

        $bulan = $data_Nutricia->pluck('Bulan');
    
        return response()->json([
            'price_Nutricia' => $price_Nutricia,
            'price_JAPFA' => $price_JAPFA,
            'price_Agriaku' => $price_Agriaku,
            'bulan' => $bulan,
            
        ]);
    }
} 