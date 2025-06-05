<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Session;
use Mpdf\Mpdf;
use Carbon\Carbon;


class DN_report_Controller extends Controller
{
    public function index_list_Kwitansi()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('report.list_kwitansi.index', compact('user') );
    }
     public function index_list_dn()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('report.list_dn.index', compact('user') );
    }
    public function get_list_kwitansi(Request $request)
    {
        $allChecked = $request->input('allChecked');
        $selesaiChecked = $request->input('selesaiChecked');
        $belumChecked = $request->input('belumChecked');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $client = $request->input('client');
        $clientCondition = "";
        if($client !="" || $client != null){
            $clientCondition = "AND salesdntagih_client_code = '$client'";
        }
        if($allChecked == 'true'){
            $wherecondition = "";
        }else{
            if($selesaiChecked == 'true'){
                $wherecondition = "AND status = 1";
            }elseif($belumChecked == 'true'){
                $wherecondition = "AND status != 1";
            }else{
                $wherecondition = "AND status = 99";
            }
        }
        $startDate_condition = "";
        $endDate_condition = "";
        if ($startDate != "" || $startDate != null) {
            $startDate_condition = " AND CONVERT(date, p.created_at) >= CONVERT(date, '$startDate')";
        }
        if ($endDate != "" || $endDate != null) {
            $endDate_condition = " AND CONVERT(date, p.created_at) <= CONVERT(date, '$endDate')";
        }
        $query = "
            SELECT
                no_kwitansi, 
                salesdntagih_client_code,
                clien_id2,
                value_est_pph_4,
                value_tagihan_dn,
                iif(salesdntagih_code_cabang = '0003' and clien_id2 = 'PT. TIRTA UTAMA ABADI' , 'PT. WENANG PALM SOLUSINDO', clien_id2) clien_desc,
                kode_faktur_pajak,
                note_kwitansi,
                value_ppn,
                bukti_potong_pph_23,
                cast(p.created_at as date) tgl_kwitansi,
                status
            FROM
                tr_tagih_sales_DN_h h
                LEFT JOIN tr_tagih_sales_DN_pph4 p ON h.salesdntagih_code_h = p.no_kwitansi
                JOIN ms_client c on h.salesdntagih_client_code = c.clien_id
            WHERE 
                no_kwitansi is not null
                $wherecondition
                $startDate_condition
                $endDate_condition
                $clientCondition
        ";
        // dd($query);
        $data = DB::connection('ms_sql_hgs')->select($query); 
        return response()->json($data);
    }
    public function get_list_kwitansi_chart(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $client = $request->input('client');
        $clientCondition = "";
        if($client !="" || $client != null){
            $clientCondition = "AND salesdntagih_client_code = '$client'";
        }
        $startDate_condition = "";
        $endDate_condition = "";
        if ($startDate != "" || $startDate != null) {
            $startDate_condition = " AND CONVERT(date, p.created_at) >= CONVERT(date, '$startDate')";
        }
        if ($endDate != "" || $endDate != null) {
            $endDate_condition = " AND CONVERT(date, p.created_at) <= CONVERT(date, '$endDate')";
        }
        $query = "
            WITH tbl as (
                SELECT
                    no_kwitansi,
                    value_est_pph_4,
                    value_tagihan_dn,
                    kode_faktur_pajak,
                    note_kwitansi,
                    value_ppn,
                    bukti_potong_pph_23,
                    CAST ( p.created_at AS DATE ) tgl_kwitansi,
                    status
                FROM
                    tr_tagih_sales_DN_h h
                    LEFT JOIN tr_tagih_sales_DN_pph4 p ON h.salesdntagih_code_h = p.no_kwitansi
                    JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id 
                WHERE
                    no_kwitansi IS NOT NULL 
                    $clientCondition
                    $startDate_condition
                    $endDate_condition
            )
            SELECT 
                SUM(IIF(status = 1, value_tagihan_dn, 0)) AS value_sudah,
                SUM(IIF(status != 1, value_tagihan_dn, 0)) AS value_belum,
                SUM(IIF(status = 1, value_est_pph_4, 0)) AS value_pph_sudah,
                SUM(IIF(status != 1, value_est_pph_4, 0)) AS value_pph_belum,
                SUM(IIF(status = 1, 1, 0)) AS sudah,
                SUM(IIF(status != 1, 1, 0)) AS belum,
                CAST(SUM(IIF(status = 1, 1, 0)) AS DECIMAL(10, 2)) * 100 / COUNT(*) AS persen_sudah,
                CAST(SUM(IIF(status != 1, 1, 0)) AS DECIMAL(10, 2)) * 100 / COUNT(*) AS persen_belum
            from 
            tbl
        ";

        $data = DB::connection('ms_sql_hgs')->select($query); 
        return response()->json($data[0]);
    }
    public function get_list_dn(Request $request)
    {
        $allChecked = $request->input('allChecked');
        $selesaiChecked = $request->input('selesaiChecked');
        $belumChecked = $request->input('belumChecked');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $client = $request->input('client');
        $clientCondition = "";
        if($client !="" || $client != null){
            $clientCondition = "AND salesdntagih_client_code = '$client'";
        }
        if($allChecked == 'true'){
            $wherecondition = "";
        }else{
            if($selesaiChecked == 'true'){
                $wherecondition = "AND no_kwitansi is not null";
            }elseif($belumChecked == 'true'){
                $wherecondition = "AND no_kwitansi is null";
            }else{
                $wherecondition = "AND no_kwitansi = 'dfnjsdbsjdbjs'";
            }
        }
        $startDate_condition = "";
        $endDate_condition = "";
        if(($startDate == "" || $startDate == null) && ($endDate == "" || $endDate == null)){
            $startDate_condition = " AND salesdntagih_dateregist_tagihan >= DATEADD(MONTH, -2, GETDATE())";
        }
        if ($startDate != "" || $startDate != null) {
            $startDate_condition = " AND CONVERT(date, salesdntagih_dateregist_tagihan) >= CONVERT(date, '$startDate')";
        }
        if ($endDate != "" || $endDate != null) {
            $endDate_condition = " AND CONVERT(date, salesdntagih_dateregist_tagihan) <= CONVERT(date, '$endDate')";
        }
        $query = "
            SELECT
                iif(no_kwitansi is not null, 1, 0) status_kwitansi,
                iif(status = 1, 1, 0) status_pajak,
                salesdntagih_code_h,
                cast(salesdntagih_dateregist_tagihan as date) tgl,
                salesdntagih_client_code client,
                salesdntagih_operator operator,
                t.total,
                salesdntagih_Total_tagihan
            FROM
                tr_tagih_sales_DN_h h
                LEFT JOIN tr_tagih_sales_DN_pph4 p ON h.salesdntagih_code_h = p.no_kwitansi
                JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id
                join (
                    SELECT
                        salesdntagih_code_h kode,
                        SUM( salesdntagih_Tagih_value ) total
                    FROM
                        tr_tagih_sales_DN_d 
                    GROUP BY
                        salesdntagih_code_h
                ) t on h.salesdntagih_code_h = t.kode
            WHERE 1 = 1
                $wherecondition
                $startDate_condition
                $endDate_condition
                $clientCondition
            ORDER BY h.rec_datecreated desc
        ";
        // dd($query);
        $data = DB::connection('ms_sql_hgs')->select($query); 
        return response()->json($data);
    }
    public function get_list_dn_chart(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $client = $request->input('client');
        $clientCondition = "";
        if($client !="" || $client != null){
            $clientCondition = "AND salesdntagih_client_code = '$client'";
        }
        $startDate_condition = "";
        $endDate_condition = "";
        if(($startDate == "" || $startDate == null) && ($endDate == "" || $endDate == null)){
            $startDate_condition = " AND salesdntagih_dateregist_tagihan >= DATEADD(MONTH, -2, GETDATE())";
        }
        if ($startDate != "" || $startDate != null) {
            $startDate_condition = " AND CONVERT(date, salesdntagih_dateregist_tagihan) >= CONVERT(date, '$startDate')";
        }
        if ($endDate != "" || $endDate != null) {
            $endDate_condition = " AND CONVERT(date, salesdntagih_dateregist_tagihan) <= CONVERT(date, '$endDate')";
        }
        $query = "
            with tbl as 
            (
                SELECT
                    iif(no_kwitansi is not null, 1, 0) status_kwitansi,
                    iif(status = 1, 1, 0) status_pajak,
                    salesdntagih_code_h,
                    cast(salesdntagih_dateregist_tagihan as date) tgl,
                    salesdntagih_client_code client,
                    salesdntagih_operator operator,
                    t.total,
                    salesdntagih_Total_tagihan,
                    value_est_pph_4
                FROM
                    tr_tagih_sales_DN_h h
                    LEFT JOIN tr_tagih_sales_DN_pph4 p ON h.salesdntagih_code_h = p.no_kwitansi
                    JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id
                    join (
                        SELECT
                            salesdntagih_code_h kode,
                            SUM( salesdntagih_Tagih_value ) total
                        FROM
                            tr_tagih_sales_DN_d 
                        GROUP BY
                            salesdntagih_code_h
                    ) t on h.salesdntagih_code_h = t.kode
                WHERE 1 = 1
                    $startDate_condition
                    $endDate_condition
                    $clientCondition
            )
            SELECT
                SUM( IIF ( status_kwitansi = 1, total, 0 ) ) AS value_sudah,
                SUM( IIF ( status_kwitansi != 1, total, 0 ) ) AS value_belum,
                SUM( IIF ( status_pajak = 1, value_est_pph_4, 0 ) ) AS value_pph_sudah,
                SUM( IIF ( status_pajak != 1, value_est_pph_4, 0 ) ) AS value_pph_belum,
                SUM( IIF ( status_kwitansi = 1, 1, 0 ) ) AS sudah,
                SUM( IIF ( status_kwitansi != 1, 1, 0 ) ) AS belum,
                CAST ( SUM( IIF ( status_kwitansi = 1, 1, 0 ) ) AS DECIMAL ( 10, 2 ) ) * 100 / COUNT( * ) AS persen_sudah,
                CAST ( SUM( IIF ( status_kwitansi != 1, 1, 0 ) ) AS DECIMAL ( 10, 2 ) ) * 100 / COUNT( * ) AS persen_belum 
            FROM
                tbl
        ";

        $data = DB::connection('ms_sql_hgs')->select($query); 
        return response()->json($data[0]);
    }
} 

