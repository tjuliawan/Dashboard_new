<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Mpdf\Mpdf;
use Carbon\Carbon;


class DN_Controller extends Controller
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
        return view ('dn_transaction.Add_New_Tagih_Sales_DN.index', compact('user') );
    }
    public function index_list_tr_tagih_sales_DN_d_date()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('dn_transaction.list_tr_tagih_sales_DN_d_date.index', compact('user') );
    }
    public function index_edit_tagih_sales_dn()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('dn_transaction.edit_tagih_sales_dn.index', compact('user') );
    }
    public function index_kwitansi()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('dn_transaction.kwitansi.index', compact('user') );
    }
    public function get_client(Request $request)
    {
        $data = DB::connection('ms_sql_hgs')->select(" 		
            SELECT * from ms_client WHERE rec_status = 1 ORDER BY clien_id
        "); 
        return response()->json($data);
    }
    public function get_vehicle(Request $request)
    {
        $data = DB::connection('ms_sql_hgs')->select(" 		
            SELECT * from ms_vehicle WHERE rec_status = 1 ORDER BY Vh_Code
        "); 
        return response()->json($data);
    }
    public function data_for_chart_1(Request $request)
    {
        $data = DB::connection('ms_sql_hgs')->select(" 		
            SELECT
                FORMAT(rec_datecreated, 'MMM') AS Month,
                SUM(salesdntagih_Total_tagihan) AS total,
                COUNT(0) AS tol_transaction
            FROM
                tr_tagih_sales_DN_h 
            WHERE
                YEAR(rec_datecreated) = YEAR(GETDATE())
            GROUP BY
                FORMAT(rec_datecreated, 'MMM'),
                MONTH(rec_datecreated)
            ORDER BY
                MONTH(rec_datecreated)
        "); 
    
        // Ubah ke collection agar bisa pluck
        $collection = collect($data);
    
        return response()->json([
            'month' => $collection->pluck('Month'),
            'total' => $collection->pluck('total'),
            'tol_transaction' => $collection->pluck('tol_transaction'),
        ]);
    }
    public function get_business(Request $request)
    {
        $data = DB::connection('ms_sql_hgs')->select(" 		
            SELECT * from TGU_ms_gudang WHERE rec_status = 1 ORDER BY Gudang_code
        "); 
        return response()->json($data);
    }
    public function get_table_add_tagih_sales_dn(Request $request)
    {
        $client =  $request -> input('client');
        $cabang =  $request -> input('cabang');
        $business =  $request -> input('business');
        $vehicle =  $request -> input('vehicle');
        $start_date =  $request -> input('start_date');
        $end_date =  $request -> input('end_date');
        $product =  $request -> input('product');
        $dn_code =  $request -> input('dn_code');
        // dd($dn_code);
        // dd($product);
        $startDate_condition = "";
        $endDate_condition = "";
        $cabang_condition = "";
        $business_condition = "";   
        $registerDate_condition = "";
        $client_condition = "";
        $vehicle_condition = "";
        $product_condition = "";
        $dn_code_condition = "";
        if ($cabang != "" || $cabang != null) {
            $cabang_condition = " AND cab_code = '$cabang'";
        }
        if ($vehicle != "" || $vehicle != null) {
            $vehicle_condition = " AND otranssalesdn.Sales_DN_vehicle = '$vehicle'";
        }
        if ($client != "" || $client != null) {
            $client_condition = " AND otranssalesdnh.client_code = '$client'";
        }
        if ($start_date != "" || $start_date != null) {
            $startDate_condition = " AND CONVERT(date, otranssalesdn.Sales_DN_date) >= CONVERT(date, '$start_date')";
        }
        if ($end_date != "" || $end_date != null) {
            $endDate_condition = " AND CONVERT(date, otranssalesdn.Sales_DN_date) <= CONVERT(date, '$end_date')";
        }
        if (!empty($product)) {
            $product_list = "'" . implode("','", $product) . "'";
            $product_condition = " AND osalesdnpro.sales_dn_productcode IN ($product_list)";
        } else {
            $product_condition = "";
        }
        if ($dn_code != "" || $vehicle != null) {
            $dn_code_condition = " AND otranssalesdn.Sales_DN_Code_d = '$dn_code'";
        }
        // dd($product_condition);
        $query = "
            SELECT distinct
                otranssalesdn.[rec_comcode],
                otranssalesdn.[rec_areacode],
                otranssalesdn.[Sales_DN_Code],
                otranssalesdn.[Sales_DN_Code_d],
                otranssalesdn.[Sales_DN_date],
                otranssalesdn.[Sales_DN_route_product_client_vehicle],
                otranssalesdn.[Sales_DN_Sales_value],
                otranssalesdn.[Sales_DN_vehicle],
                otranssalesdn.[Sales_DN_Driver],
                odrv.Drv_FistName + ' ' + odrv.Drv_LastName as drivername,
                otranssalesdn.[Sales_DN_BBM_code],
                otranssalesdn.[Sales_DN_uang_jalan_code],
                otranssalesdn.[Sales_DN_driver_fee_code],
                otranssalesdn.Sales_DN_driver_fee,
                otranssalesdn.[Sales_DN_BBM_date],
                otranssalesdn.[Sales_DN_BBM_value],
                otranssalesdn.[Sales_DN_uang_jalan_date],
                otranssalesdn.[Sales_DN_uang_jalan_value],
                otranssalesdn.[Sales_DN_uang_driver_date],
                otranssalesdn.[Sales_DN_COcode],
                otranssalesdn.Sales_DN_COno,
                otranssalesdn.[Sales_DN_helpercode],
                ohlper.Hlper_FistName + ' ' + ohlper.Hlper_LastName as helpername,
                otranssalesdn.[Sales_DN_helper_fee_code],
                otranssalesdn.[data_status_code],
                otranssalesdn.[Sales_DN_helper_fee_date],
                Sales_DN_spkno,
                otranssalesdn.Sales_DN_helper_fee,
                otranssalesdn.transaksi_main_code,
                isnull(osalesdnpro.Sales_DN_Productcodeqty, 0) as Sales_DN_Productcodeqty,
                isnull(orutvh.routveh_salesbotol, 0) as routveh_salesbotol,
                isnull(orutvh.routveh_salesbotol * osalesdnpro.Sales_DN_Productcodeqty, 0) as totalsales,
                Sales_DN_km1,
                Sales_DN_km2,
                Sales_DN_BBM_liter,
                Sales_DN_BBM_bonno,
                otranssalesdn.Sales_DN_check,
                Sales_DN_notecheck,
                Sales_DN_closingcode,
                SUBSTRING(Sales_DN_closingcode, 1, 4) AS hasil,
                otranssalesdn.Sales_DN_DN2,
                iif(cab_desc = 'HGS', 'HGS-Sentul', cab_desc) cab_desc,
                cab_code,
                otranssalesdnh.client_code,
                osalesdnpro.sales_dn_productcode,
                otagissalesdn.trash_data
            FROM
                tr_acc_transaksi_sales_DN_d otranssalesdn left join
                ms_driver odrv on otranssalesdn.Sales_DN_Driver = odrv.Drv_Id left join
                ms_helper ohlper on otranssalesdn.Sales_DN_helpercode = ohlper.Hlper_Id left join
                tr_acc_transaksi_sales_DN_h otranssalesdnh on otranssalesdn.Sales_DN_Code = otranssalesdnh.Sales_DN_Code left join
                tr_acc_transaksi_sales_DN_d_product osalesdnpro on  otranssalesdn.Sales_DN_Code = osalesdnpro.Sales_DN_Code and otranssalesdn.Sales_DN_Code_d = osalesdnpro.Sales_DN_Code_d left join
                tr_tagih_sales_DN_d DN_d on otranssalesdn.Sales_DN_Code_d = DN_d.salesdntagih_Sales_dn_code left join
                ms_routevehicle orutvh on otranssalesdn.Sales_DN_route_product_client_vehicle = orutvh.routveh_code left join
                tr_tagih_sales_DN_d otagissalesdn on otranssalesdn.Sales_DN_Code_d = otagissalesdn.salesdntagih_Sales_dn_code
                join ms_cabang area on SUBSTRING(Sales_DN_closingcode, 1, 4) = area.cab_code
            WHERE   
            	(1=1
                $client_condition
                $cabang_condition
                $vehicle_condition
                $product_condition
                $startDate_condition
                $endDate_condition
                $dn_code_condition
                AND otranssalesdnh.rec_status != '0') and
                (otagissalesdn.salesdntagih_Sales_dn_code is null or otagissalesdn.trash_data = 1) and otranssalesdnh.rec_status = '2'
        ";
        // dd($query);
        $data = DB::connection('ms_sql_hgs')->select($query); 
        return response()->json($data);
    }
    public function get_table_list_tr_tagih_sales_DN_d_date(Request $request)
    {
        $client =  $request -> input('client');
        $vehicle =  $request -> input('vehicle');
        $business =  $request -> input('business');
        $register_date =  $request -> input('register_date');
        $start_date =  $request -> input('start_date');
        $end_date =  $request -> input('end_date');
        $input_main_code =  $request -> input('input_main_code');
        $product =  $request -> input('product');

        $startDate_condition = "";
        $endDate_condition = "";
        $vehicle_condition = "";
        $business_condition = "";   
        $registerDate_condition = "";
        $input_main_code_condition = "";
        $client_condition = "";
        $product_condition = "";
        if ($vehicle != "" || $vehicle != null) {
            $vehicle_condition = " AND salesdntagih_vhcode = '$vehicle'";
        }
        if ($client != "" || $client != null) {
            $client_condition = " AND salesdntagih_client_code = '$client'";
        }
        if ($start_date != "" || $start_date != null) {
            $startDate_condition = " AND CONVERT ( DATE, Tagih_h.[rec_datecreated] ) >= CONVERT(date, '$start_date')";
        }
        if ($end_date != "" || $end_date != null) {
            $endDate_condition = " AND CONVERT ( DATE, Tagih_h.[rec_datecreated] )  <= CONVERT(date, '$end_date')";
        }
        if ($input_main_code != "" || $input_main_code != null) {
            $input_main_code_condition = " AND Tagih_det.salesdntagih_code_h = '$input_main_code'";
        }
        if (!empty($product)) {
            $product_list = "'" . implode("','", $product) . "'";
            $product_condition = " AND sales_dn_productcode IN ($product_list)";
        } else {
            $product_condition = "";
        }
        $query = "
            SELECT
                Tagih_det.rec_comcode,
                Tagih_det.rec_areacode,
                Tagih_det.salesdntagih_code_h,
                salesdntagih_code_d,
                salesdntagih_Sales_dn_code,
                cast(ISNULL( salesdntagih_Sales_dn_date, '2014-01-10' ) as date) AS salesdntagih_Sales_dn_date,
                salesdntagih_Tagih_value,
                salesdntagih_Sales_dn_codeheader,
                salesdntagih_cocode_header,
                salesdntagih_cocode,
                drv.Drv_FistName AS salesdntagih_drivercode,
                salesdntagih_routevhcode,
                salesdntagih_qty,
                salesdntagih_cost,
                salesdntagih_salesbotol,
                salesdntagih_vhcode,
                salesdntagih_status,
                salesdntagih_note,
                salesdntagih_NoBkb,
                salesdntagih_suratjalan,
                salesdntagih_customer,
                salesdntagih_client_code,
                salesdntagih_no_po,
                sales_dn_productcode
            FROM
                [tr_tagih_sales_DN_d] Tagih_det
                LEFT JOIN [tr_tagih_sales_DN_h] Tagih_h ON Tagih_det.salesdntagih_code_h = Tagih_h.salesdntagih_code_h
                LEFT JOIN ms_driver drv ON Tagih_det.salesdntagih_drivercode = drv.Drv_Id 
                LEFT JOIN tr_acc_transaksi_sales_DN_d p ON p.Sales_DN_Code_d = Tagih_det.salesdntagih_Sales_dn_code
                left join tr_acc_transaksi_sales_DN_d_product osalesdnpro on  p.Sales_DN_Code = osalesdnpro.Sales_DN_Code and p.Sales_DN_Code_d = osalesdnpro.Sales_DN_Code_d 
            WHERE 1=1
                -- @rec_comcode IS NULL 
                -- OR Tagih_det.[rec_comcode] LIKE '%' + @rec_comcode + '%' 
                -- AND @rec_areacode IS NULL 
                -- OR Tagih_det.[rec_areacode] LIKE '%' + @rec_areacode + '%' 
                $client_condition
                $vehicle_condition
                $startDate_condition
                $endDate_condition
                $input_main_code_condition
                $product_condition
                AND Tagih_h.rec_status != '0'
                and (trash_data is null or trash_data != 1)
            ORDER BY 
                no_urut
        ";
        // dd($query);
        $data = DB::connection('ms_sql_hgs')->select($query); 
        return response()->json($data);
    }
    public function get_table_for_edit_dn_tgih(Request $request)
    {
        $client =  $request -> input('client');
        $vehicle =  $request -> input('vehicle');
        $business =  $request -> input('business');
        $register_date =  $request -> input('register_date');
        $start_date =  $request -> input('start_date');
        $end_date =  $request -> input('end_date');
        $input_main_code =  $request -> input('input_main_code');
        $product =  $request -> input('product');

        $startDate_condition = "";
        $endDate_condition = "";
        $vehicle_condition = "";
        $business_condition = "";   
        $registerDate_condition = "";
        $input_main_code_condition = "";
        $client_condition = "";
        $product_condition = "";
        if ($vehicle != "" || $vehicle != null) {
            $vehicle_condition = " AND salesdntagih_vhcode = '$vehicle'";
        }
        if ($client != "" || $client != null) {
            $client_condition = " AND salesdntagih_client_code = '$client'";
        }
        if ($start_date != "" || $start_date != null) {
            $startDate_condition = " AND CONVERT ( DATE, Tagih_h.[rec_datecreated] ) >= CONVERT(date, '$start_date')";
        }
        if ($end_date != "" || $end_date != null) {
            $endDate_condition = " AND CONVERT ( DATE, Tagih_h.[rec_datecreated] )  <= CONVERT(date, '$end_date')";
        }
        if ($input_main_code != "" || $input_main_code != null) {
            $input_main_code_condition = " AND Tagih_det.salesdntagih_code_h = '$input_main_code'";
        }
        if (!empty($product)) {
            $product_list = "'" . implode("','", $product) . "'";
            $product_condition = " AND sales_dn_productcode IN ($product_list)";
        } else {
            $product_condition = "";
        }
        $query = "
            SELECT
                Tagih_det.rec_comcode,
                Tagih_det.rec_areacode,
                Tagih_det.salesdntagih_code_h,
                salesdntagih_code_d,
                salesdntagih_Sales_dn_code,
                cast(ISNULL( salesdntagih_Sales_dn_date, '2014-01-10' ) as date) AS salesdntagih_Sales_dn_date,
                salesdntagih_Tagih_value,
                salesdntagih_Sales_dn_codeheader,
                salesdntagih_cocode_header,
                salesdntagih_cocode,
                drv.Drv_FistName AS salesdntagih_drivercode,
                salesdntagih_routevhcode,
                salesdntagih_qty,
                salesdntagih_cost,
                salesdntagih_salesbotol,
                salesdntagih_vhcode,
                salesdntagih_status,
                salesdntagih_note,
                salesdntagih_NoBkb,
                salesdntagih_suratjalan,
                salesdntagih_customer,
                salesdntagih_client_code,
                salesdntagih_no_po,
                sales_dn_productcode
            FROM
                [tr_tagih_sales_DN_d] Tagih_det
                LEFT JOIN [tr_tagih_sales_DN_h] Tagih_h ON Tagih_det.salesdntagih_code_h = Tagih_h.salesdntagih_code_h
                LEFT JOIN ms_driver drv ON Tagih_det.salesdntagih_drivercode = drv.Drv_Id 
                LEFT JOIN tr_acc_transaksi_sales_DN_d p ON p.Sales_DN_Code_d = Tagih_det.salesdntagih_Sales_dn_code
                left join tr_acc_transaksi_sales_DN_d_product osalesdnpro on  p.Sales_DN_Code = osalesdnpro.Sales_DN_Code and p.Sales_DN_Code_d = osalesdnpro.Sales_DN_Code_d 
            WHERE 1=1
                -- @rec_comcode IS NULL 
                -- OR Tagih_det.[rec_comcode] LIKE '%' + @rec_comcode + '%' 
                -- AND @rec_areacode IS NULL 
                -- OR Tagih_det.[rec_areacode] LIKE '%' + @rec_areacode + '%' 
                $client_condition
                $vehicle_condition
                $startDate_condition
                $endDate_condition
                $input_main_code_condition
                $product_condition
                AND Tagih_h.rec_status != '0'
                and (trash_data is null or trash_data != 1)
            ORDER BY 
                no_urut

        ";
        // dd($query);
        $data = DB::connection('ms_sql_hgs')->select($query); 
        return response()->json($data);
    }
    public function get_header_dn_tagih(Request $request)
    {
        $code = $request->get('code');
        $data = DB::connection('ms_sql_hgs')->select(" 		
            SELECT
                h.*,
                clien_desc,
                iif(p.no_kwitansi is null, 0, 1) no_kwitansi ,
                note_kwitansi
            FROM
                tr_tagih_sales_DN_h h
                JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id
                LEFT JOIN tr_tagih_sales_DN_pph4 p ON p.no_kwitansi = h.salesdntagih_code_h 
            WHERE
                salesdntagih_code_h = '$code'
        "); 
        return response()->json($data[0]);
    }
    public function cetakPDF_inv(Request $request)
    {
        $code = $request->get('code');
        $client_code = $request->get('client_code');
        $user = auth()->user()->username;
        $jakartaTime = Carbon::now('Asia/Jakarta');
        $query = "
            select 
                TagihH.rec_usercreated,
                TagihH.rec_datecreated,
                TagihH.salesdntagih_code_h,
                TagihH.salesdntagih_dateregist_tagihan,
                TagihH.salesdntagih_Total_tagihan,
                TagihH.salesdntagih_Total_sales,
                cli.clien_desc as salesdntagih_client_code,
                TagihH.salesdntagih_operator,
                TagihDet.salesdntagih_Sales_dn_code,
                TagihDet.salesdntagih_Sales_dn_date,
                TagihDet.salesdntagih_Tagih_value,
                TagihDet.salesdntagih_cocode,
                drv.Drv_FistName as salesdntagih_drivercode,
                TagihDet.salesdntagih_routevhcode,
                TagihDet.salesdntagih_qty,
                TagihDet.salesdntagih_cost,
                TagihDet.salesdntagih_salesbotol,
                TagihDet.salesdntagih_vhcode,
                TagihDet.salesdntagih_note,
                TagihDet.salesdntagih_NoBkb,
                TagihDet.salesdntagih_suratjalan,
                TagihH.salesdntagih_bisniscode,
                TagihH.salesdntagih_start_date,
                TagihH.salesdntagih_end_date,
                TagihH.rec_comcode,
                TagihH.rec_areacode,
                TagihDet.salesdntagih_customer,
                1 rit,
                salesdntagih_no_po,
                kporderdn_BTB_no,
                kporderdn_BKB_no
            from 
                tr_tagih_sales_DN_h TagihH
                inner Join  tr_tagih_sales_DN_d TagihDet on TagihH.salesdntagih_code_h= TagihDet.salesdntagih_code_h
                left join ms_client Cli on tagihH.salesdntagih_client_code = Cli.clien_id
                left Join ms_driver drv on TagihDet.salesdntagih_drivercode= drv.Drv_Id
                left join tr_Kp_order_DN_depo depo on depo.kporderdn_cocode = TagihDet.salesdntagih_cocode
            WHERE TagihH.salesdntagih_code_h = '$code' and (trash_data is null or trash_data != 1)
            ORDER BY
                TagihDet.salesdntagih_vhcode,
                drv.Drv_FistName,
                TagihDet.salesdntagih_Sales_dn_date desc
        ";
        $data = DB::connection('ms_sql_hgs')->select($query); 
        $data_sumary = $data[0];
        // dd($data_sumary);
        $branch = $data_sumary->rec_comcode;
        $operator = $data_sumary->rec_usercreated;
        $date = $data_sumary->rec_datecreated;
        $code = $data_sumary->salesdntagih_code_h;

        $total_inv = count($data);
        $total_tagihan_sales = 0;

        foreach ($data as $row) {
            $total_tagihan_sales += $row->salesdntagih_Tagih_value;
        }
        $total_tagihan_sales = 'Rp ' . number_format($total_tagihan_sales, 0, ',', '.');
        // dd($total_tagihan_sales);
        // dd($client_code);
        if ($client_code == 'TUA') {
            // dd($client_code);
            $html =
                '
                <style>
                    .special-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 9px;
                        vertical-align: middle;
                    }
                    .special-table td {
                        border: 0.5px solid black;
                        padding: 3px;
                    }
                    .special-table th{
                        border: 0.5px solid black;
                        padding: 3px;
                    }
                    .table-header {
                        font-weight: bold;
                    }
                    .header-info {
                        width: 80%;
                        margin-bottom: 20px;
                        font-size: 9px;
                        line-height: 0.3rem;
                        vertical-align: middle;
                    }
                    .header-info td {
                        padding: 5px;
                    }
                </style>
                <style>
                    .custom-row {
                        width: 100%;
                    }
    
                    .custom-col-12 {
                        width: 100%;
                    }
    
                    .custom-col-6 {
                        width: 50%;
                    }
    
                    .custom-img {
                        width: 50px;
                        height: auto;
                        margin-bottom: 0;
                    }
    
                    .custom-text-right {
                        text-align: right;
                    }
    
                    .custom-text-purple {
                        color: #ffffff;
                        background-color: #25bc4d;
                        border-radius: 12px;
                        margin: 12px 12px ;
                    }
    
                    table {
                        width: 100%;
                        border: none;
                    }
    
                    td {
                        vertical-align: top;
                    }
                    .table_kop {
                        border-collapse: collapse; /* Menghilangkan jarak antar border */
                        margin-left: 20px; /* Memberikan jarak antara h2 dan tabel */
                        border: 1px solid #ccc; /* Garis tipis di luar tabel */
                    }
    
                    .table_kop td {
                        padding: 3px;
                        border: 1px solid #ccc; /* Garis tipis di setiap sel */
                        font-size: 9px; /* Ukuran font di dalam tabel */
                        font-style: italic;
                    }
                </style>
                <table class="custom-row">
                    <tr>
                        <td class="custom-col-6">
                            <table class="">
                                <tr>
                                    <td><img class="custom-img" src="https://i.imgur.com/MHpXScU.jpeg" alt="Image"></td>
                                    <td><h3>DN/INVOICE TAGIH</h3></td>
                                </tr>
                            </table>
                            
                        </td>
                        <td class="custom-col-6 ">
                            <table class="table_kop">
                                <tr>
                                    <td><strong>Code : </strong> '.$code.'</td>
                                    <td><strong>Date :  </strong>'.$date.' </td>
                                </tr>
                                <tr>
                                    
                                    <td><strong>Operator : </strong>'.$operator.'</td>
                                    <td><strong>Branch :</strong>    ' .$branch. '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table class="header-info" style=" max-width:40px;">
                    <tr>
                        <td><strong>Periode date</strong></td>
                        <td><strong>:</strong></td>
                        <td colspan="4">'.$data_sumary->salesdntagih_start_date.' - '.$data_sumary->salesdntagih_end_date.'</td>
                    </tr>
                    <tr>
                        <td><strong>Operator</strong></td>
                        <td><strong>:</strong></td>
                        <td>'.$operator.'</td>
                        <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                        <td><strong>:</strong></td>
                        <td>'.$total_tagihan_sales.'</td>
                    </tr>
                    <tr>
                        <td><strong>Client</strong></td>
                        <td><strong>:</strong></td>
                        <td style=" max-width: fit-content;">'.$data_sumary->salesdntagih_client_code.'</td>
                        <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                        <td><strong>:</strong></td>
                        <td>'.$total_inv.'</td>
                    </tr>
                    <tr>
                        <td><strong>Bisnis</strong></td>
                        <td><strong>:</strong></td>
                        <td>'.$data_sumary->salesdntagih_bisniscode.'</td>
                        <td><strong style="margin-left: 15px;">No. PO</strong></td>
                        <td><strong>:</strong></td>
                        <td>'.$data_sumary->salesdntagih_no_po.'</td>
                    </tr>
                </table>
                <table class="special-table" style="border: 0.7px solid black; border-collapse: collapse; font-size: 9px; vertical-align: middle;">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Vehicle</th>
                            <th>DN Date</th>
                            <th>CO/SO</th>
                            <th>Driver</th>
                            <th>Route Vehicle</th>
                            <th>BTB/ Mat Doc</th>
                            <th>DO</th>
                            <th>DN/Invoice</th>
                            <th>Rit</th>
                            <th>Qty</th>
                            <th>Sales/ botol</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    ';
                    $groupedData = [];
                    foreach ($data as $row) {
                        $vehicle = $row->salesdntagih_vhcode;
                        if (!isset($groupedData[$vehicle])) {
                            $groupedData[$vehicle] = [];
                        }
                        $groupedData[$vehicle][] = $row;
                    }
                    
                    $counter = 1;
                    foreach ($groupedData as $vehicle => $rows) {
                        $totalValue = 0;
                        $totalQty = 0;
                        $totalRit = 0;
                    
                        foreach ($rows as $index => $row) {
                            $html .= '<tr>';
                            $html .= '<td style="text-align: left;">' . $counter . '</td>';
                    
                            // hanya tampilkan vehicle di baris pertama
                            if ($index === 0) {
                                $html .= '<td style="text-align: left; background-color:rgb(255, 255, 255); border: none;">' . $vehicle . '</td>';
                            } else {
                                $html .= '<td style="text-align: left; border: none; background-color:rgb(255, 255, 255);"></td>';
                            }
                    
                            $html .= '
                                <td>' . date('Y-m-d', strtotime($row->salesdntagih_Sales_dn_date)) . '</td>
                                <td style="text-align: left;">' . $row->salesdntagih_cocode . '</td>
                                <td style="text-align: left;">' . $row->salesdntagih_drivercode . '</td>
                                <td style="text-align: left;">' . $row->salesdntagih_routevhcode . '</td>
                                <td style="text-align: left;">' . $row->kporderdn_BTB_no . '</td>
                                <td style="text-align: left;">' . $row->kporderdn_BKB_no . '</td>
                                <td style="text-align: left;">' . $row->salesdntagih_Sales_dn_code . '</td>
                                <td style="text-align: right;">' . $row->rit . '</td>
                                <td style="text-align: right;">' . number_format($row->salesdntagih_qty, 0, ',', '.') . '</td>
                                <td style="text-align: right;">Rp ' . number_format($row->salesdntagih_salesbotol, 0, ',', '.') . '</td>
                                <td style="text-align: right;">Rp ' . number_format($row->salesdntagih_Tagih_value, 0, ',', '.') . '</td>
                            ';
                            
                            $html .= '</tr>';
                    
                            $totalValue += $row->salesdntagih_Tagih_value;
                            $totalQty += $row->salesdntagih_qty;
                            $totalRit += $row->rit;
                            $counter++;
                        }
                    
                        $html .= '
                            <tr style="font-weight: bold; background-color: #eeffee;">
                                <td colspan="9" style="text-align: left;">' . $vehicle . ' Total </td>
                                <td colspan="" style="text-align: right;">' . number_format($totalRit, 0, ',', '.') . '</td>
                                <td colspan="" style="text-align: right;">' . number_format($totalQty, 0, ',', '.') . '</td>
                                <td colspan="" style="text-align: right;"></td>
                                <td colspan="" style="text-align: right;">Rp ' . number_format($totalValue, 0, ',', '.') . '</td>
                            </tr>
                        ';
                    }
                    
    
                $html .= '
                        <tr style="background-color: #eeffee;">
                            <td colspan="9" style="text-align: left; font-size: 9px; font-weight: bold;">Grand Total</td>
                            <td colspan="4" style="text-align: right; font-size: 9px; font-weight: bold;">' . $total_tagihan_sales . '</td>
                        </tr>
    
                    </tbody>
                </table>
                <br>
                <br>
                <style>
                    table.print_info {
                        border-collapse: collapse;=
                    }
    
                    table.print_info td {
                        padding: 4px 9px;
                        white-space: nowrap;
                        vertical-align: top;
                    }
    
                    table.print_info td:nth-child(1) {
                        width: 50px;
                    }
    
                    table.print_info td:nth-child(2) {
                        width: 10px; 
                    }
    
                    table.print_info td:nth-child(3) {
                        max-width: 200px;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                </style>
    
                <table class="print_info"  style="font-size: 9px;">
                    <tr>
                        <td><strong>Print User</strong></td>
                        <td><strong>:</strong></td>
                        <td>' . $user . '</td>
                    </tr>
                    <tr>
                        <td><strong>Print Time</strong></td>
                        <td><strong>:</strong></td>
                        <td>' . $jakartaTime . '</td>
                    </tr>
                </table>
                    
    
            ';
            // <td style="text-align: right;">' . number_format($totalQty, 0, ',', '.') . '</td>
            $mpdf = new \Mpdf\Mpdf([
                'orientation' => 'L', // 'L' untuk landscape, 'P' untuk portrait (default)
                'margin_bottom' => 7,
            ]);
            
            // $footer = '<div style="width:100%; text-align:center; border-top: 1px solid black; padding-top:0px;"></div>';
            // $mpdf->SetHTMLFooter($footer);
            $mpdf->SetTitle('DN Tagih -' . $code . ' - ' . $jakartaTime);
            $mpdf->WriteHTML($html);
            $filename = 'DN-Tagih-' . $code . '-' . $jakartaTime . '.pdf';
            return $mpdf->Output($filename, 'I'); 
        } else {
            $html =
                '
                    <style>
                        .special-table {
                            width: 100%;
                            border-collapse: collapse;
                            font-size: 9px;
                            vertical-align: middle;
                        }
                        .special-table td {
                            border: 0.5px solid black;
                            padding: 3px;
                        }
                        .special-table th{
                            border: 0.5px solid black;
                            padding: 3px;
                        }
                        .table-header {
                            font-weight: bold;
                        }
                        .header-info {
                            width: 80%;
                            margin-bottom: 20px;
                            font-size: 9px;
                            line-height: 0.3rem;
                            vertical-align: middle;
                        }
                        .header-info td {
                            padding: 5px;
                        }
                    </style>
                    <style>
                        .custom-row {
                            width: 100%;
                        }

                        .custom-col-12 {
                            width: 100%;
                        }

                        .custom-col-6 {
                            width: 50%;
                        }

                        .custom-img {
                            width: 50px;
                            height: auto;
                            margin-bottom: 0;
                        }

                        .custom-text-right {
                            text-align: right;
                        }

                        .custom-text-purple {
                            color: #ffffff;
                            background-color: #25bc4d;
                            border-radius: 12px;
                            margin: 12px 12px ;
                        }

                        table {
                            width: 100%;
                            border: none;
                        }

                        td {
                            vertical-align: top;
                        }
                        .table_kop {
                            border-collapse: collapse; /* Menghilangkan jarak antar border */
                            margin-left: 20px; /* Memberikan jarak antara h2 dan tabel */
                            border: 1px solid #ccc; /* Garis tipis di luar tabel */
                        }

                        .table_kop td {
                            padding: 3px;
                            border: 1px solid #ccc; /* Garis tipis di setiap sel */
                            font-size: 9px; /* Ukuran font di dalam tabel */
                            font-style: italic;
                        }
                    </style>
                    <table class="custom-row">
                        <tr>
                            <td class="custom-col-6">
                                <table class="">
                                    <tr>
                                        <td><img class="custom-img" src="https://i.imgur.com/MHpXScU.jpeg" alt="Image"></td>
                                        <td><h3>DN/INVOICE TAGIH</h3></td>
                                    </tr>
                                </table>
                                
                            </td>
                            <td class="custom-col-6 ">
                                <table class="table_kop">
                                    <tr>
                                        <td><strong>Code : </strong> '.$code.'</td>
                                        <td><strong>Date :  </strong>'.$date.' </td>
                                    </tr>
                                    <tr>
                                        
                                        <td><strong>Operator : </strong>'.$operator.'</td>
                                        <td><strong>Branch :</strong>    ' .$branch. '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <table class="header-info" style=" max-width:40px;">
                        <tr>
                            <td><strong>Periode date</strong></td>
                            <td><strong>:</strong></td>
                            <td colspan="4">'.$data_sumary->salesdntagih_start_date.' - '.$data_sumary->salesdntagih_end_date.'</td>
                        </tr>
                        <tr>
                            <td><strong>Operator</strong></td>
                            <td><strong>:</strong></td>
                            <td>'.$operator.'</td>
                            <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                            <td><strong>:</strong></td>
                            <td>'.$total_tagihan_sales.'</td>
                        </tr>
                        <tr>
                            <td><strong>Client</strong></td>
                            <td><strong>:</strong></td>
                            <td style=" max-width: fit-content;">'.$data_sumary->salesdntagih_client_code.'</td>
                            <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                            <td><strong>:</strong></td>
                            <td>'.$total_inv.'</td>
                        </tr>
                        <tr>
                            <td><strong>Bisnis</strong></td>
                            <td><strong>:</strong></td>
                            <td>'.$data_sumary->salesdntagih_bisniscode.'</td>
                            <td><strong style="margin-left: 15px;">No. PO</strong></td>
                            <td><strong>:</strong></td>
                            <td>'.$data_sumary->salesdntagih_no_po.'</td>
                        </tr>
                    </table>
                    <table class="special-table" style="border: 1px solid black; border-collapse: collapse; font-size: 9px; vertical-align: middle;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>DN/Invoice</th>
                                <th>Date</th>
                                <th>CO/SO</th>
                                <th>Driver</th>
                                <th>Cust</th>
                                <th>Vehicle</th>
                                <th>Qty</th>
                                <th>Sales/ botol/kg</th>
                                <th>Value</th>
                                <th>BKB</th>
                                <th>DO</th>
                            </tr>
                        </thead>
                        <tbody>
                        ';

                        $counter = 1;

                        foreach ($data as $row) {
                            $html .= '
                                <tr>
                                    <td style="text-align: left;">' . $counter . '</td>
                                    <td>' . $row->salesdntagih_Sales_dn_code . '</td>
                                    <td>' . date('Y-m-d', strtotime($row->salesdntagih_Sales_dn_date)) . '</td>
                                    <td style="text-align: left;">' . $row->salesdntagih_cocode . '</td>
                                    <td style="text-align: left;">' . $row->salesdntagih_drivercode . '</td>
                                    <td style="text-align: left;">' . $row->salesdntagih_customer . '</td>
                                    <td style="text-align: left;">' . $row->salesdntagih_vhcode . '</td>
                                    <td style="text-align: right;">' . number_format($row->salesdntagih_qty, 0, ',', '.') . '</td>
                                    <td style="text-align: right;">Rp ' . number_format($row->salesdntagih_salesbotol, 0, ',', '.') . '</td>
                                    <td style="text-align: right;">Rp ' . number_format($row->salesdntagih_Tagih_value, 0, ',', '.') . '</td>
                                    <td style="text-align: left;">' . $row->salesdntagih_NoBkb . '</td>
                                    <td style="text-align: left;">' . $row->kporderdn_BKB_no . '</td>
                                </tr>';
                            $counter++;
                        }


                    $html .= '
                            <tr>
                                <td colspan="7" style="text-align: center; font-size: 9px;">Total</td>
                                <td colspan="3" style="text-align: right; font-size: 9px;">'.$total_tagihan_sales.'</td>
                                <td colspan="3" style="text-align: center; font-size: 9px;"></td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <table style="text-align: center; font-size: 9px; font-weight: bold;">
                        <tr>
                            <th>
                                <table>
                                    <tr>
                                        <td>Mengetahui</td>
                                    </tr>
                                    <tr>
                                        <td><br><br><br><br><br><br></td>
                                    </tr>
                                    <tr>
                                        <td>______________</td>
                                    </tr>
                                </table>
                            </th>
                            <th>
                            <table>
                                <tr>
                                    <td>Operator</td>
                                </tr>
                                <tr>
                                    <td><br><br><br><br><br><br><br></td>
                                </tr>
                                <tr>
                                    <td style="text-decoration: underline;">'.$operator.'</td>
                                </tr>
                            </table>
                            </th>
                        </tr>
                    </table>
                    <br>
                    <br>
                    <style>
                        table.print_info {
                            border-collapse: collapse;=
                        }

                        table.print_info td {
                            padding: 4px 9px;
                            white-space: nowrap;
                            vertical-align: top;
                        }

                        table.print_info td:nth-child(1) {
                            width: 50px;
                        }

                        table.print_info td:nth-child(2) {
                            width: 10px; 
                        }

                        table.print_info td:nth-child(3) {
                            max-width: 200px;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }
                    </style>

                    <table class="print_info"  style="font-size: 9px;">
                        <tr>
                            <td><strong>Print User</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $user . '</td>
                        </tr>
                        <tr>
                            <td><strong>Print Time</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $jakartaTime . '</td>
                        </tr>
                    </table>


                ';
            $mpdf = new \Mpdf\Mpdf([
                'orientation' => 'P', // 'L' untuk landscape, 'P' untuk portrait (default)
            ]);
            $mpdf->SetTitle('DN Tagih -' . $code . ' - ' . $jakartaTime);
            $mpdf->WriteHTML($html);
            $filename = 'DN-Tagih-' . $code . '-' . $jakartaTime . '.pdf';
            return $mpdf->Output($filename, 'I'); 
        }
    }
    public function cetakPDF_kwitansi(Request $request)
    {
        $code = $request->get('code');
        $client_code = $request->get('client_code');
        $user = auth()->user()->username;
        $jakartaTime = Carbon::now('Asia/Jakarta');
        $jakartaTime = Carbon::now('Asia/Jakarta');

        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei',
            6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober',
            11 => 'November', 12 => 'Desember'
        ];

        $tanggal = $jakartaTime->day; 
        $bulanIndo = $bulan[$jakartaTime->month]; 
        $tahun = $jakartaTime->year; 

        // Formatkan hasil menjadi "3 Mei 2025"
        $formattedDate =  $tanggal . ' ' . $bulanIndo . ' ' . $tahun;
        $query = "
            SELECT
                h.*,
                clien_desc,
                iif(p.no_kwitansi is null, 0, 1) no_kwitansi ,
                note_kwitansi
            FROM
                tr_tagih_sales_DN_h h
                JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id
                LEFT JOIN tr_tagih_sales_DN_pph4 p ON p.no_kwitansi = h.salesdntagih_code_h 
            WHERE
                YEAR( h.rec_datecreated ) = YEAR( GETDATE( ) )
                and  salesdntagih_code_h = '$code'

        ";
        $data = DB::connection('ms_sql_hgs')->select($query); 
        $data_sumary = $data[0];
        $cabang_transaksi = $data_sumary->salesdntagih_code_cabang;
        if($cabang_transaksi == '0001')
        {
            $cabang_transaksi = 'Sentul';
        }elseif($cabang_transaksi  == '0002')
        {
            $cabang_transaksi = 'Ciherang';
        }elseif($cabang_transaksi  == '0003'){
            $cabang_transaksi = 'Subang';
        }
        $branch = $data_sumary->salesdntagih_code_cabang;
        function formatTanggalIndo($tanggal) {
            $bulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei',
                6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober',
                11 => 'November', 12 => 'Desember'
            ];
        
            if ($tanggal && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                $tanggalParts = explode('-', $tanggal);
                if (count($tanggalParts) === 3) {
                    $tahun = $tanggalParts[0];
                    $bulanIndex = (int)$tanggalParts[1];
                    $hari = (int)$tanggalParts[2];
                    return "$hari {$bulan[$bulanIndex]} $tahun";
                }
            }
        
            return '-';
        }
        $startDate = formatTanggalIndo($data_sumary->salesdntagih_start_date);
        $endDate = formatTanggalIndo($data_sumary->salesdntagih_end_date);
        $operator = $data_sumary->rec_usercreated;
        // $date = $data_sumary->rec_datecreated;
        // $code = $data_sumary->salesdntagih_code_h;

        $salesdntagih_Total_tagihan = round($data_sumary->salesdntagih_Total_tagihan);
        // dd($salesdntagih_Total_tagihan);

        $salesdntagih_Total_tagihan = 'Rp ' . number_format(round($salesdntagih_Total_tagihan), 0, ',', '.');
        function terbilang($angka) {
            $angka = abs($angka);
            $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
            $hasil = "";
        
            if ($angka < 15) {
                $hasil = " " . $baca[$angka];
            } elseif ($angka < 20) {
                $hasil = terbilang($angka - 10) . " belas";
            } elseif ($angka < 100) {
                $hasil = terbilang(floor($angka / 10)) . " puluh " . terbilang($angka % 10);
            } elseif ($angka < 200) {
                $hasil = " seratus " . terbilang($angka - 100);
            } elseif ($angka < 1000) {
                $hasil = terbilang(floor($angka / 100)) . " ratus " . terbilang($angka % 100);
            } elseif ($angka < 2000) {
                $hasil = " seribu " . terbilang($angka - 1000);
            } elseif ($angka < 1000000) {
                $hasil = terbilang(floor($angka / 1000)) . " ribu " . terbilang($angka % 1000);
            } elseif ($angka < 1000000000) {
                $hasil = terbilang(floor($angka / 1000000)) . " juta " . terbilang($angka % 1000000);
            } elseif ($angka < 1000000000000) {
                $hasil = terbilang(floor($angka / 1000000000)) . " Milyar " . terbilang($angka % 1000000000);
            } 
            
        
            return trim(preg_replace('/\s+/', ' ', $hasil)); // bersihkan spasi berlebihan
        }        

        function terbilang_rupiah_koma($angka) {
            $pecah = explode('.', number_format($angka, 2, '.', ''));
            $bulat = (int) $pecah[0];
            $desimal = (int) ltrim($pecah[1], '0');
        
            $hasil = ucwords(terbilang($bulat));
            
            if ($desimal > 0) {
                $hasil .= ' Koma ' . ucwords(terbilang($desimal));
            }
        
            return $hasil . ' Rupiah';
        }
        $jumlah_terbilang = terbilang_rupiah_koma(round($data_sumary->salesdntagih_Total_tagihan));
        $periode = ($startDate === $endDate) ? $startDate : $startDate . ' - ' . $endDate;
        // dd($total_tagihan_sales);
            // dd($client_code);
        $html =
            '
            <style>
                body {
                    font-family: calibri, sans-serif;
                }

                .header-info {
                    width: 80%;
                    margin-bottom: 20px;
                    font-size: 12px;
                    vertical-align: middle;
                }
                .header-info td {
                    padding: 5px;
                }
                .header-info.2 td {
                    padding: 0px;
                }
            </style>
            <h4 style="font-family: Times New Roman, Times, serif;">PT. HANDAL GUNA SARANA</h4>
            <h2 style="text-align: center; text-decoration: underline; font-family: Times New Roman, Times, serif;">
                KWITANSI
            </h2>
            <table class="header-info" style=" max-width:40px;">
                <tr>
                    <td style=" max-width: fit-content;"><strong>No</strong></td>
                    <td><strong>:</strong></td>
                    <td><span style="font-weight: ; border:1px solid #000;">'.$code.'</span></td>
                </tr>
                <tr>
                    <td style=" width:130px;"><strong>Sudah terima dari</strong></td>
                    <td><strong>:</strong></td>
                    <td><span style="font-weight: bold;">'.$data_sumary->clien_desc.'</span></td>
                </tr>
                <tr>
                    <td><strong>Uang sejumlah</strong></td>
                    <td><strong>:</strong></td>
                    <td style="border:1px solid #000;"><span style="font-size:14px; font-weight: bold; font-style: italic;">'.$jumlah_terbilang.'</span></td>
                </tr>
                    <tr>
                    <td><strong>Untuk pembayaran</strong></td>
                    <td><strong>:</strong></td>
                    <td style:"max-width: 390px;"><span style="font-weight: bold;">'.$data_sumary->note_kwitansi.'</span></td>
                </tr>
                <tr>
                    <td><strong>Jumlah</strong></td>
                    <td><strong>:</strong></td>
                    <td><span style="font-size:18px; font-weight: bold; font-style: italic;">'.$salesdntagih_Total_tagihan.'</span></td>
                </tr>
            </table>
            <table class="header-info 2">
                <tr>
                    <td colspan="3"><strong>Informasi Rekening Pembayaran</strong></td>
                    <td style="width:230px; text-align: center;"><span style="text-align: center;">Jakarta, '.$formattedDate.'</span></td>
                </tr>
                <tr>
                    <td style=" width:130px;"><strong>Nama</strong></td>
                    <td><strong>:</strong></td>
                    <td style=" width:390px;"><span style="font-weight: bold;">PT Handal Guna Sarana</span></td>
                </tr>
                <tr>
                    <td><strong>No Rek</strong></td>
                    <td><strong>:</strong></td>
                    <td><span style="font-weight: bold;">5050338080</span></td>
                </tr>
                <tr>
                    <td><strong>Bank</strong></td>
                    <td><strong>:</strong></td>
                    <td><span style="font-weight: bold;">BCA</span></td>
                </tr>
                <tr>
                    <td><strong>Cabang</strong></td>
                    <td><strong>:</strong></td>
                    <td><span style="font-weight: bold;">Permata Hijau</span></td>
                </tr>
                 <tr>
                    <td><strong></strong></td>
                    <td><strong></strong></td>
                    <td><span></span></td>
                    <td style="text-decoration: underline; text-align: center;"></td>
                </tr>
                 <tr>
                    <td><strong></strong></td>
                    <td><strong></strong></td>
                    <td><span></span></td>
                    <td style="text-decoration: underline; text-align: center;">Tri Hartati</td>
                </tr>
            </table>
        ';
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A5', 
            'orientation' => 'L', 
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'margin_bottom' => 3,
            'margin_header' => 3,
            'margin_footer' => 3,
        ]);
        
        $mpdf->SetTitle('DN Tagih -' . $code . ' - ' . $jakartaTime);
        $mpdf->WriteHTML($html);
        $filename = 'DN-Tagih-' . $code . '-' . $jakartaTime . '.pdf';
        return $mpdf->Output($filename, 'I'); 
        
    }
    public function cetakPDF_inv_for_water_tanker(Request $request)
    {
        $code = $request->get('code');
        $user = auth()->user()->username;
        $jakartaTime = Carbon::now('Asia/Jakarta');
        $query = "
            select 
                TagihH.rec_usercreated,
                TagihH.rec_datecreated,
                TagihH.salesdntagih_code_h,
                TagihH.salesdntagih_dateregist_tagihan,
                TagihH.salesdntagih_Total_tagihan,
                TagihH.salesdntagih_Total_sales,
                cli.clien_desc as salesdntagih_client_code,
                TagihH.salesdntagih_operator,
                TagihDet.salesdntagih_Sales_dn_code,
                TagihDet.salesdntagih_Sales_dn_date,
                TagihDet.salesdntagih_Tagih_value,
                TagihDet.salesdntagih_cocode,
                drv.Drv_FistName as salesdntagih_drivercode,
                TagihDet.salesdntagih_routevhcode,
                TagihDet.salesdntagih_qty,
                TagihDet.salesdntagih_cost,
                TagihDet.salesdntagih_salesbotol,
                TagihDet.salesdntagih_vhcode,
                TagihDet.salesdntagih_note,
                TagihDet.salesdntagih_NoBkb,
                TagihDet.salesdntagih_suratjalan,
                TagihH.salesdntagih_bisniscode,
                TagihH.salesdntagih_start_date,
                TagihH.salesdntagih_end_date,
                TagihH.rec_comcode,
                TagihH.rec_areacode,
                TagihDet.salesdntagih_customer,
                salesdntagih_no_po
            from 
                tr_tagih_sales_DN_h TagihH
                inner Join  tr_tagih_sales_DN_d TagihDet on TagihH.salesdntagih_code_h= TagihDet.salesdntagih_code_h
                left join ms_client Cli on tagihH.salesdntagih_client_code = Cli.clien_id
                left Join ms_driver drv on TagihDet.salesdntagih_drivercode= drv.Drv_Id
            WHERE TagihH.salesdntagih_code_h = '$code' and (trash_data is null or trash_data != 1)
            ORDER BY
                TagihDet.salesdntagih_vhcode,
                drv.Drv_FistName,
                TagihDet.salesdntagih_Sales_dn_date
        ";
        $query2 = "
            WITH tbl AS (
                SELECT
                    DATEPART(YEAR, OrderDn.rec_datecreated) AS tahun,
                    DATEPART(MONTH, OrderDn.rec_datecreated) AS bulan,
                    CONVERT(DATE, OrderDn.rec_datecreated) AS tgl,
                    otripcost.kasirbranchclosing_code AS closing_code,
                    from_desc AS pabrik1,
                    
                    COALESCE(kasirbranchcash_dn_valid, kporderdn_dn_final_code) AS dn,
                    kasirbranchcash_COno AS surat_jalan,
                    kasirbranchcash_productqty AS liter,
                    routveh_salesbotol AS hrg_ltr,
                    kasirbranchcash_productqty * routveh_salesbotol AS total_price_value,
                    
                    CASE 
                        WHEN dest_desc = 'LIDO-CIHERANG' THEN 'CIHERANG'
                        WHEN dest_desc = 'LIDO-SENTUL' THEN 'SENTUL'
                        ELSE dest_desc
                    END AS pabrik2,

                    '' AS kode,
                    '' AS nomor,

                    COALESCE(kasirbranchcash_vhcode_valid, kporderdn_vhcodereal) AS vehicle,
                    COALESCE(drv.drv_fistname, drv1.drv_fistname) AS driver,

                    1 AS rit,
                    kasirbranchcash_btb AS no_doc,
                    kasirbranchcash_suratjalan AS no_po,
                    kasirbranchcash_spkno AS spk

                FROM tr_acc_transaksi_kasir_cash_branch_uang_jalan uangjalan

                LEFT JOIN Tr_KP_Order_DN OrderDn 
                    ON uangjalan.kasirbranchcash_COno = OrderDn.kporderdn_cocode

                LEFT JOIN tr_Kp_order_DN_depo dndepo 
                    ON OrderDn.kporderdn_co_final_code = dndepo.kporderdn_cocode

                LEFT JOIN ms_routevehicle routevehicle 
                    ON uangjalan.kasirbranchcash_routevhcode = routevehicle.routveh_code

                LEFT JOIN ms_route routex 
                    ON routevehicle.routveh_routecode = routex.route_code

                LEFT JOIN ms_from fromx 
                    ON OrderDn.kporderdn_fromcode = fromx.from_code

                LEFT JOIN ms_driver drv 
                    ON uangjalan.kasirbranchcash_drivercode_valid = drv.drv_id

                LEFT JOIN ms_driver drv1 
                    ON kporderdn_drivercodereal = drv1.drv_id

                LEFT JOIN ms_destination desti 
                    ON uangjalan.kasirbranchcash_destcode = desti.dest_code

                INNER JOIN tr_acc_transaksi_kasir_branch_closing_d_tripcost_out otripcost 
                    ON uangjalan.kasirbranchcash_code = otripcost.kasirbranchclosing_kasirbranchcashcode
            )

            SELECT * 
            FROM tbl 
            LEFT JOIN (
                SELECT
                    Sales_DN_spkno AS spk_2,
                    salesdntagih_code_h, no_urut, trash_data
                FROM tr_tagih_sales_DN_d d1
                JOIN tr_acc_transaksi_sales_DN_d d2 
                    ON d2.Sales_DN_Code_d = d1.salesdntagih_Sales_dn_code
            ) d 
                ON tbl.spk = d.spk_2
            WHERE salesdntagih_code_h = '$code' and (trash_data is null or trash_data != 1)
            ORDER BY no_urut;
        ";
        $data = DB::connection('ms_sql_hgs')->select($query); 
        $data2 = DB::connection('ms_sql_hgs')->select($query2); 
        $data_sumary = $data[0];
        $data_sumary2 = $data2[0];
        // dd($data_sumary2);
        $branch = $data_sumary->rec_comcode;
        $operator = $data_sumary->rec_usercreated;
        $date = $data_sumary->rec_datecreated;
        $code = $data_sumary->salesdntagih_code_h;

        $total_inv = count($data);
        $rotal_rit = count($data2);
        $total_tagihan_sales = 0;

        foreach ($data as $row) {
            $total_tagihan_sales += $row->salesdntagih_Tagih_value;
        }
        $total_tagihan_sales = 'Rp ' . number_format($total_tagihan_sales, 0, ',', '.');
        // dd($total_tagihan_sales);
        $html =
            '
            <style>
                .special-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 9px;
                    vertical-align: middle;
                }
                .special-table td {
                    border: 0.5px solid black;
                    padding: 3px;
                }
                .special-table th{
                    border: 0.5px solid black;
                    padding: 3px;
                }
                .table-header {
                    font-weight: bold;
                }
                .header-info {
                    width: 80%;
                    margin-bottom: 20px;
                    font-size: 9px;
                    line-height: 0.3rem;
                    vertical-align: middle;
                }
                .header-info td {
                    padding: 5px;
                }
            </style>
            <style>
                .custom-row {
                    width: 100%;
                }

                .custom-col-12 {
                    width: 100%;
                }

                .custom-col-6 {
                    width: 50%;
                }

                .custom-img {
                    width: 50px;
                    height: auto;
                    margin-bottom: 0;
                }

                .custom-text-right {
                    text-align: right;
                }

                .custom-text-purple {
                    color: #ffffff;
                    background-color: #25bc4d;
                    border-radius: 12px;
                    margin: 12px 12px ;
                }

                table {
                    width: 100%;
                    border: none;
                }

                td {
                    vertical-align: top;
                }
                .table_kop {
                    border-collapse: collapse; /* Menghilangkan jarak antar border */
                    margin-left: 20px; /* Memberikan jarak antara h2 dan tabel */
                    border: 1px solid #ccc; /* Garis tipis di luar tabel */
                }

                .table_kop td {
                    padding: 3px;
                    border: 1px solid #ccc; /* Garis tipis di setiap sel */
                    font-size: 9px; /* Ukuran font di dalam tabel */
                    font-style: italic;
                }
            </style>
            <table class="custom-row">
                <tr>
                    <td class="custom-col-6">
                        <table class="">
                            <tr>
                                <td><img class="custom-img" src="https://i.imgur.com/MHpXScU.jpeg" alt="Image"></td>
                                <td><h3>DN/INVOICE TAGIH WATER TANKER</h3></td>
                            </tr>
                        </table>               
                    </td>
                    <td class="custom-col-6 ">
                        <table class="table_kop">
                            <tr>
                                <td><strong>Code : </strong> '.$code.'</td>
                                <td><strong>Date :  </strong>'.$date.' </td>
                            </tr>
                            <tr>
                                
                                <td><strong>Operator : </strong>'.$operator.'</td>
                                <td><strong>Branch :</strong>    ' .$branch. '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br>
            <table class="header-info" style=" max-width:40px;">
                <tr>
                    <td><strong>Periode date</strong></td>
                    <td><strong>:</strong></td>
                    <td colspan="4">'.$data_sumary->salesdntagih_start_date.' - '.$data_sumary->salesdntagih_end_date.'</td>
                </tr>
                <tr>
                    <td><strong>Operator</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$operator.'</td>
                    <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$total_tagihan_sales.'</td>
                     <td><strong style="margin-left: 15px;">No. Po</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$data_sumary->salesdntagih_no_po.'</td>
                </tr>
                <tr>
                    <td><strong>Client</strong></td>
                    <td><strong>:</strong></td>
                    <td style=" max-width: fit-content;">'.$data_sumary->salesdntagih_client_code.'</td>
                    <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$total_inv.'</td>
                </tr>
            </table>
            <table class="special-table" style="border: 1px solid black; border-collapse: collapse; font-size: 9px; vertical-align: middle;">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>SPK</th>
                        <th>No PO</th>
                        <th>No.Doc</th>
                        <th>Rit</th>
                        <th>Driver</th>
                        <th>Vehicle</th>
                        <th>Nomor</th>
                        <th>Pabrik</th>
                        <th>Total Price Value</th>
                        <th>Hrg/Ltr</th>
                        <th>Liter</th>
                        <th>Surat Jalan</th>
                        <th>DN</th>
                        <th>Pabrik</th>
                        <th>Closing Code</th>
                        <th>Tgl.</th>
                    </tr>
                </thead>
                <tbody>
                ';

                $counter = 1;

                foreach ($data2 as $row) {
                    $html .= '
                        <tr>
                            <td style="text-align: left;">' . $counter . '</td>
                            <td>' . $row->spk . '</td>
                            <td style="text-align: left;">' . $row->no_po . '</td>
                            <td style="text-align: left;">' . $row->no_doc . '</td>
                            <td style="text-align: right;">' . $row->rit . '</td>
                            <td style="text-align: left;">' . $row->driver . '</td>
                            <td style="text-align: left;">' . $row->vehicle . '</td>
                            <td style="text-align: right;">' . $row->no_urut . '</td>
                            <td style="text-align: left;">' . $row->pabrik1 . '</td>
                            <td style="text-align: right;">Rp ' . number_format($row->total_price_value, 0, ',', '.') . '</td>
                            <td style="text-align: right;">Rp ' . number_format($row->hrg_ltr, 3, ',', '.') . '</td>
                            <td style="text-align: right;">' . number_format($row->liter, 0, ',', '.') . '</td>
                            <td style="text-align: left;">' . $row->surat_jalan . '</td>
                            <td style="text-align: left;">' . $row->dn . '</td>
                            <td style="text-align: left;">' . $row->pabrik2 . '</td>
                            <td style="text-align: left;">' . $row->closing_code . '</td>
                            <td style="text-align: left;">' . $row->tgl . '</td>
                        </tr>';
                    $counter++;
                }
            $html .= '
                    <tr>
                        <td colspan="4" style="text-align: center; font-size: 9px;">Total</td>
                        <td style="text-align: center; font-size: 9px;">'.$rotal_rit.'</td>
                        <td colspan="5" style="text-align: right; font-size: 9px;">'.$total_tagihan_sales.'</td>
                        <td colspan="7" style="text-align: center; font-size: 9px;"></td>
                    </tr>
                </tbody>
            </table>
            <br>
            <table style="text-align: center; font-size: 9px; font-weight: bold;">
                <tr>
                    <th>
                        <table>
                            <tr>
                                <td>Mengetahui</td>
                            </tr>
                            <tr>
                                <td><br><br><br><br><br><br></td>
                            </tr>
                            <tr>
                                <td>______________</td>
                            </tr>
                        </table>
                    </th>
                    <th>
                    <table>
                        <tr>
                            <td>Operator</td>
                        </tr>
                        <tr>
                            <td><br><br><br><br><br><br><br></td>
                        </tr>
                        <tr>
                            <td style="text-decoration: underline;">'.$operator.'</td>
                        </tr>
                    </table>
                    </th>
                </tr>
            </table>
            <br>
            <br>
            <style>
                table.print_info {
                    border-collapse: collapse;=
                }

                table.print_info td {
                    padding: 4px 9px;
                    white-space: nowrap;
                    vertical-align: top;
                }

                table.print_info td:nth-child(1) {
                    width: 50px;
                }

                table.print_info td:nth-child(2) {
                    width: 10px; 
                }

                table.print_info td:nth-child(3) {
                    max-width: 200px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            </style>

            <table class="print_info"  style="font-size: 9px;">
                <tr>
                    <td><strong>Print User</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $user . '</td>
                </tr>
                <tr>
                    <td><strong>Print Time</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $jakartaTime . '</td>
                </tr>
            </table>


        ';
        $mpdf = new \Mpdf\Mpdf([
            'orientation' => 'L', // 'L' untuk landscape, 'P' untuk portrait (default)
        ]);
        $mpdf->SetTitle('DN Tagih -' . $code . ' - ' . $jakartaTime);
        $mpdf->WriteHTML($html);
        $filename = 'DN-Tagih-' . $code . '-' . $jakartaTime . '.pdf';
        return $mpdf->Output($filename, 'I'); 
    }
    public function cetakPDF_bak(Request $request)
    {
        $code = $request->get('code');
        $user = auth()->user()->username;
        $jakartaTime = Carbon::now('Asia/Jakarta');
        $query = "
            select 
                TagihH.rec_usercreated,
                TagihH.rec_datecreated,
                TagihH.salesdntagih_code_h,
                TagihH.salesdntagih_dateregist_tagihan,
                TagihH.salesdntagih_Total_tagihan,
                TagihH.salesdntagih_Total_sales,
                cli.clien_desc as salesdntagih_client_code,
                TagihH.salesdntagih_operator,
                TagihDet.salesdntagih_Sales_dn_code,
                TagihDet.salesdntagih_Sales_dn_date,
                TagihDet.salesdntagih_Tagih_value,
                TagihDet.salesdntagih_cocode,
                drv.Drv_FistName as salesdntagih_drivercode,
                TagihDet.salesdntagih_routevhcode,
                TagihDet.salesdntagih_qty,
                TagihDet.salesdntagih_cost,
                TagihDet.salesdntagih_salesbotol,
                TagihDet.salesdntagih_vhcode,
                TagihDet.salesdntagih_note,
                TagihDet.salesdntagih_NoBkb,
                TagihDet.salesdntagih_suratjalan,
                TagihH.salesdntagih_bisniscode,
                TagihH.salesdntagih_start_date,
                TagihH.salesdntagih_end_date,
                TagihH.rec_comcode,
                TagihH.rec_areacode,
                TagihDet.salesdntagih_customer
            from 
                tr_tagih_sales_DN_h TagihH
                inner Join  tr_tagih_sales_DN_d TagihDet on TagihH.salesdntagih_code_h= TagihDet.salesdntagih_code_h
                left join ms_client Cli on tagihH.salesdntagih_client_code = Cli.clien_id
                left Join ms_driver drv on TagihDet.salesdntagih_drivercode= drv.Drv_Id
            WHERE TagihH.salesdntagih_code_h = '$code'
            ORDER BY
                TagihDet.salesdntagih_vhcode,
                drv.Drv_FistName,
                TagihDet.salesdntagih_Sales_dn_date


        ";
        $data = DB::connection('ms_sql_hgs')->select($query); 
        $data_sumary = $data[0];
        // dd($data_sumary);
        $branch = $data_sumary->rec_comcode;
        $operator = $data_sumary->rec_usercreated;
        $date = $data_sumary->rec_datecreated;
        $code = $data_sumary->salesdntagih_code_h;

        $total_inv = count($data);
        $total_tagihan_sales = 0;

        foreach ($data as $row) {
            $total_tagihan_sales += $row->salesdntagih_Tagih_value;
        }
        $total_tagihan_sales = 'Rp ' . number_format($total_tagihan_sales, 0, ',', '.');
        // dd($total_tagihan_sales);
        $html =
            '
            <style>
                .special-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 9px;
                    vertical-align: middle;
                }
                .special-table td {
                    border: 0.5px solid black;
                    padding: 3px;
                }
                .special-table th{
                    border: 0.5px solid black;
                    padding: 3px;
                }
                .table-header {
                    font-weight: bold;
                }
                .header-info {
                    width: 80%;
                    margin-bottom: 20px;
                    font-size: 9px;
                    line-height: 0.3rem;
                    vertical-align: middle;
                }
                .header-info td {
                    padding: 5px;
                }
            </style>
            <style>
                .custom-row {
                    width: 100%;
                }

                .custom-col-12 {
                    width: 100%;
                }

                .custom-col-6 {
                    width: 50%;
                }

                .custom-img {
                    width: 50px;
                    height: auto;
                    margin-bottom: 0;
                }

                .custom-text-right {
                    text-align: right;
                }

                .custom-text-purple {
                    color: #ffffff;
                    background-color: #25bc4d;
                    border-radius: 12px;
                    margin: 12px 12px ;
                }

                table {
                    width: 100%;
                    border: none;
                }

                td {
                    vertical-align: top;
                }
                .table_kop {
                    border-collapse: collapse; /* Menghilangkan jarak antar border */
                    margin-left: 20px; /* Memberikan jarak antara h2 dan tabel */
                    border: 1px solid #ccc; /* Garis tipis di luar tabel */
                }

                .table_kop td {
                    padding: 3px;
                    border: 1px solid #ccc; /* Garis tipis di setiap sel */
                    font-size: 9px; /* Ukuran font di dalam tabel */
                    font-style: italic;
                }
            </style>
            <table class="custom-row">
                <tr>
                    <td class="custom-col-6">
                        <table class="">
                            <tr>
                                <td><img class="custom-img" src="https://i.imgur.com/MHpXScU.jpeg" alt="Image"></td>
                                <td><h3>DN/INVOICE TAGIH</h3></td>
                            </tr>
                        </table>
                        
                    </td>
                    <td class="custom-col-6 ">
                        <table class="table_kop">
                            <tr>
                                <td><strong>Code : </strong> '.$code.'</td>
                                <td><strong>Date :  </strong>'.$date.' </td>
                            </tr>
                            <tr>
                                
                                <td><strong>Operator : </strong>'.$operator.'</td>
                                <td><strong>Branch :</strong>    ' .$branch. '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br>
            <table class="header-info" style=" max-width:40px;">
                <tr>
                    <td><strong>Periode date</strong></td>
                    <td><strong>:</strong></td>
                    <td colspan="4">'.$data_sumary->salesdntagih_start_date.' - '.$data_sumary->salesdntagih_end_date.'</td>
                </tr>
                <tr>
                    <td><strong>Operator</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$operator.'</td>
                    <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$total_tagihan_sales.'</td>
                </tr>
                <tr>
                    <td><strong>Client</strong></td>
                    <td><strong>:</strong></td>
                    <td style=" max-width: fit-content;">'.$data_sumary->salesdntagih_client_code.'</td>
                    <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$total_inv.'</td>
                </tr>
                <tr>
                    <td><strong>Bisnis</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$data_sumary->salesdntagih_bisniscode.'</td>
                    <td><strong style="margin-left: 15px;">Total Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$total_tagihan_sales.'</td>
                </tr>
            </table>
            <table class="special-table" style="border: 1px solid black; border-collapse: collapse; font-size: 9px; vertical-align: middle;">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>DN/Invoice</th>
                        <th>Date</th>
                        <th>CO/SO</th>
                        <th>Driver</th>
                        <th>Cust</th>
                        <th>Vehicle</th>
                        <th>Qty</th>
                        <th>Sales/ botol/kg</th>
                        <th>Value</th>
                        <th>BKB</th>
                        <th>DO</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                ';

                $counter = 1;

                foreach ($data as $row) {
                    $html .= '
                        <tr>
                            <td style="text-align: left;">' . $counter . '</td>
                            <td>' . $row->salesdntagih_Sales_dn_code . '</td>
                            <td>' . date('Y-m-d', strtotime($row->salesdntagih_Sales_dn_date)) . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_cocode . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_drivercode . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_customer . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_vhcode . '</td>
                            <td style="text-align: right;">' . number_format($row->salesdntagih_qty, 0, ',', '.') . '</td>
                            <td style="text-align: right;">Rp ' . number_format($row->salesdntagih_salesbotol, 0, ',', '.') . '</td>
                            <td style="text-align: right;">Rp ' . number_format($row->salesdntagih_Tagih_value, 0, ',', '.') . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_NoBkb . '</td>
                            <td style="text-align: left;"></td>
                            <td style="text-align: left;">' . $row->salesdntagih_note . '</td>
                        </tr>';
                    $counter++;
                }


            $html .= '
                    <tr>
                        <td colspan="7" style="text-align: center; font-size: 9px;">Total</td>
                        <td colspan="3" style="text-align: right; font-size: 9px;">'.$total_tagihan_sales.'</td>
                        <td colspan="3" style="text-align: center; font-size: 9px;"></td>
                    </tr>
                </tbody>
            </table>
            <br>
            <table style="text-align: center; font-size: 9px; font-weight: bold;">
                <tr>
                    <th>
                        <table>
                            <tr>
                                <td>Mengetahui</td>
                            </tr>
                            <tr>
                                <td><br><br><br><br><br><br></td>
                            </tr>
                            <tr>
                                <td>______________</td>
                            </tr>
                        </table>
                    </th>
                    <th>
                    <table>
                        <tr>
                            <td>Operator</td>
                        </tr>
                        <tr>
                            <td><br><br><br><br><br><br><br></td>
                        </tr>
                        <tr>
                            <td style="text-decoration: underline;">'.$operator.'</td>
                        </tr>
                    </table>
                    </th>
                </tr>
            </table>
            <br>
            <br>
            <style>
                table.print_info {
                    border-collapse: collapse;=
                }

                table.print_info td {
                    padding: 4px 9px;
                    white-space: nowrap;
                    vertical-align: top;
                }

                table.print_info td:nth-child(1) {
                    width: 50px;
                }

                table.print_info td:nth-child(2) {
                    width: 10px; 
                }

                table.print_info td:nth-child(3) {
                    max-width: 200px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            </style>

            <table class="print_info"  style="font-size: 9px;">
                <tr>
                    <td><strong>Print User</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $user . '</td>
                </tr>
                <tr>
                    <td><strong>Print Time</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $jakartaTime . '</td>
                </tr>
            </table>


        ';
        $mpdf = new \Mpdf\Mpdf([
            'orientation' => 'P', // 'L' untuk landscape, 'P' untuk portrait (default)
        ]);
        $mpdf->SetTitle('DN Tagih -' . $code . ' - ' . $jakartaTime);
        $mpdf->WriteHTML($html);
        $filename = 'DN-Tagih-' . $code . '-' . $jakartaTime . '.pdf';
        return $mpdf->Output($filename, 'I'); 
    }
    public function cetakPDF_bak2(Request $request)
    {
        $code = $request->get('code');
        $user = auth()->user()->username;
        $jakartaTime = Carbon::now('Asia/Jakarta');
        $query = "
            select 
                TagihH.rec_usercreated,
                TagihH.rec_datecreated,
                TagihH.salesdntagih_code_h,
                TagihH.salesdntagih_dateregist_tagihan,
                TagihH.salesdntagih_Total_tagihan,
                TagihH.salesdntagih_Total_sales,
                cli.clien_desc as salesdntagih_client_code,
                TagihH.salesdntagih_operator,
                TagihDet.salesdntagih_Sales_dn_code,
                TagihDet.salesdntagih_Sales_dn_date,
                TagihDet.salesdntagih_Tagih_value,
                TagihDet.salesdntagih_cocode,
                drv.Drv_FistName as salesdntagih_drivercode,
                TagihDet.salesdntagih_routevhcode,
                TagihDet.salesdntagih_qty,
                TagihDet.salesdntagih_cost,
                TagihDet.salesdntagih_salesbotol,
                TagihDet.salesdntagih_vhcode,
                TagihDet.salesdntagih_note,
                TagihDet.salesdntagih_NoBkb,
                TagihDet.salesdntagih_suratjalan,
                TagihH.salesdntagih_bisniscode,
                TagihH.salesdntagih_start_date,
                TagihH.salesdntagih_end_date,
                TagihH.rec_comcode,
                TagihH.rec_areacode,
                TagihDet.salesdntagih_customer,
                1 rit
            from 
                tr_tagih_sales_DN_h TagihH
                inner Join  tr_tagih_sales_DN_d TagihDet on TagihH.salesdntagih_code_h= TagihDet.salesdntagih_code_h
                left join ms_client Cli on tagihH.salesdntagih_client_code = Cli.clien_id
                left Join ms_driver drv on TagihDet.salesdntagih_drivercode= drv.Drv_Id
            WHERE TagihH.salesdntagih_code_h = '$code'
            ORDER BY
                TagihDet.salesdntagih_vhcode,
                drv.Drv_FistName,
                TagihDet.salesdntagih_Sales_dn_date desc


        ";
        $data = DB::connection('ms_sql_hgs')->select($query); 
        $data_sumary = $data[0];
        // dd($data_sumary);
        $branch = $data_sumary->rec_comcode;
        $operator = $data_sumary->rec_usercreated;
        $date = $data_sumary->rec_datecreated;
        $code = $data_sumary->salesdntagih_code_h;

        $total_inv = count($data);
        $total_tagihan_sales = 0;

        foreach ($data as $row) {
            $total_tagihan_sales += $row->salesdntagih_Tagih_value;
        }
        $total_tagihan_sales = 'Rp ' . number_format($total_tagihan_sales, 0, ',', '.');
        // dd($total_tagihan_sales);
        $html =
            '
            <style>
                .special-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 9px;
                    vertical-align: middle;
                }
                .special-table td {
                    border: 0.5px solid black;
                    padding: 3px;
                }
                .special-table th{
                    border: 0.5px solid black;
                    padding: 3px;
                }
                .table-header {
                    font-weight: bold;
                }
                .header-info {
                    width: 80%;
                    margin-bottom: 20px;
                    font-size: 9px;
                    line-height: 0.3rem;
                    vertical-align: middle;
                }
                .header-info td {
                    padding: 5px;
                }
            </style>
            <style>
                .custom-row {
                    width: 100%;
                }

                .custom-col-12 {
                    width: 100%;
                }

                .custom-col-6 {
                    width: 50%;
                }

                .custom-img {
                    width: 50px;
                    height: auto;
                    margin-bottom: 0;
                }

                .custom-text-right {
                    text-align: right;
                }

                .custom-text-purple {
                    color: #ffffff;
                    background-color: #25bc4d;
                    border-radius: 12px;
                    margin: 12px 12px ;
                }

                table {
                    width: 100%;
                    border: none;
                }

                td {
                    vertical-align: top;
                }
                .table_kop {
                    border-collapse: collapse; /* Menghilangkan jarak antar border */
                    margin-left: 20px; /* Memberikan jarak antara h2 dan tabel */
                    border: 1px solid #ccc; /* Garis tipis di luar tabel */
                }

                .table_kop td {
                    padding: 3px;
                    border: 1px solid #ccc; /* Garis tipis di setiap sel */
                    font-size: 9px; /* Ukuran font di dalam tabel */
                    font-style: italic;
                }
            </style>
            <table class="custom-row">
                <tr>
                    <td class="custom-col-6">
                        <table class="">
                            <tr>
                                <td><img class="custom-img" src="https://i.imgur.com/MHpXScU.jpeg" alt="Image"></td>
                                <td><h3>DN/INVOICE TAGIH</h3></td>
                            </tr>
                        </table>
                        
                    </td>
                    <td class="custom-col-6 ">
                        <table class="table_kop">
                            <tr>
                                <td><strong>Code : </strong> '.$code.'</td>
                                <td><strong>Date :  </strong>'.$date.' </td>
                            </tr>
                            <tr>
                                
                                <td><strong>Operator : </strong>'.$operator.'</td>
                                <td><strong>Branch :</strong>    ' .$branch. '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br>
            <table class="header-info" style=" max-width:40px;">
                <tr>
                    <td><strong>Periode date</strong></td>
                    <td><strong>:</strong></td>
                    <td colspan="4">'.$data_sumary->salesdntagih_start_date.' - '.$data_sumary->salesdntagih_end_date.'</td>
                </tr>
                <tr>
                    <td><strong>Operator</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$operator.'</td>
                    <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$total_tagihan_sales.'</td>
                </tr>
                <tr>
                    <td><strong>Client</strong></td>
                    <td><strong>:</strong></td>
                    <td style=" max-width: fit-content;">'.$data_sumary->salesdntagih_client_code.'</td>
                    <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$total_inv.'</td>
                </tr>
                <tr>
                    <td><strong>Bisnis</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$data_sumary->salesdntagih_bisniscode.'</td>
                    <td><strong style="margin-left: 15px;">Total Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>'.$total_tagihan_sales.'</td>
                </tr>
            </table>
            <table class="special-table" style="border: 0.7px solid black; border-collapse: collapse; font-size: 9px; vertical-align: middle;">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Vehicle</th>
                        <th>DN Date</th>
                        <th>CO/SO</th>
                        <th>Driver</th>
                        <th>Route Vehicle</th>
                        <th>BTB/ Mat Doc</th>
                        <th>DO</th>
                        <th>DN/Invoice</th>
                        <th>Rit</th>
                        <th>Qty</th>
                        <th>Sales/ botol/kg</th>
                        <th>Value</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                ';
                $groupedData = [];
                foreach ($data as $row) {
                    $vehicle = $row->salesdntagih_vhcode;
                    if (!isset($groupedData[$vehicle])) {
                        $groupedData[$vehicle] = [];
                    }
                    $groupedData[$vehicle][] = $row;
                }
                
                $counter = 1;
                foreach ($groupedData as $vehicle => $rows) {
                    $totalValue = 0;
                    $totalQty = 0;
                
                    foreach ($rows as $index => $row) {
                        $html .= '<tr>';
                        $html .= '<td style="text-align: left;">' . $counter . '</td>';
                
                        // hanya tampilkan vehicle di baris pertama
                        if ($index === 0) {
                            $html .= '<td style="text-align: left; background-color:rgb(255, 255, 255); border: none;">' . $vehicle . '</td>';
                        } else {
                            $html .= '<td style="text-align: left; border: none; background-color:rgb(255, 255, 255);"></td>';
                        }
                
                        $html .= '
                            <td>' . date('Y-m-d', strtotime($row->salesdntagih_Sales_dn_date)) . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_cocode . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_drivercode . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_routevhcode . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_NoBkb . '</td>
                            <td style="text-align: left;"></td>
                            <td style="text-align: left;">' . $row->salesdntagih_Sales_dn_code . '</td>
                            <td style="text-align: left;">' . $row->rit . '</td>
                            <td style="text-align: right;">' . number_format($row->salesdntagih_qty, 0, ',', '.') . '</td>
                            <td style="text-align: right;">Rp ' . number_format($row->salesdntagih_salesbotol, 0, ',', '.') . '</td>
                            <td style="text-align: right;">Rp ' . number_format($row->salesdntagih_Tagih_value, 0, ',', '.') . '</td>
                            <td style="text-align: left;">' . $row->salesdntagih_note . '</td>
                        ';
                        
                        $html .= '</tr>';
                
                        $totalValue += $row->salesdntagih_Tagih_value;
                        $totalQty += $row->salesdntagih_qty;
                        $counter++;
                    }
                
                    $html .= '
                        <tr style="font-weight: bold; background-color: #eeffee;">
                            <td colspan="9" style="text-align: left;">' . $vehicle . ' Total </td>
                            <td colspan="4" style="text-align: right;">Rp ' . number_format($totalValue, 0, ',', '.') . '</td>
                            <td colspan="1"></td>
                        </tr>
                    ';
                }
                

            $html .= '
                    <tr style="background-color: #eeffee;">
                        <td colspan="9" style="text-align: left; font-size: 9px; font-weight: bold;">Grand Total</td>
                        <td colspan="4" style="text-align: right; font-size: 9px; font-weight: bold;">' . $total_tagihan_sales . '</td>
                        <td colspan="1" style="text-align: center; font-size: 9px; font-weight: bold;"></td>
                    </tr>

                </tbody>
            </table>
            <br>
            <table style="text-align: center; font-size: 9px; font-weight: bold;">
                <tr>
                    <th>
                        <table>
                            <tr>
                                <td>Mengetahui</td>
                            </tr>
                            <tr>
                                <td><br><br><br><br><br><br></td>
                            </tr>
                            <tr>
                                <td>______________</td>
                            </tr>
                        </table>
                    </th>
                    <th>
                    <table>
                        <tr>
                            <td>Operator</td>
                        </tr>
                        <tr>
                            <td><br><br><br><br><br><br><br></td>
                        </tr>
                        <tr>
                            <td style="text-decoration: underline;">'.$operator.'</td>
                        </tr>
                    </table>
                    </th>
                </tr>
            </table>
            <br>
            <style>
                table.print_info {
                    border-collapse: collapse;=
                }

                table.print_info td {
                    padding: 4px 9px;
                    white-space: nowrap;
                    vertical-align: top;
                }

                table.print_info td:nth-child(1) {
                    width: 50px;
                }

                table.print_info td:nth-child(2) {
                    width: 10px; 
                }

                table.print_info td:nth-child(3) {
                    max-width: 200px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            </style>

            <table class="print_info"  style="font-size: 9px;">
                <tr>
                    <td><strong>Print User</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $user . '</td>
                </tr>
                <tr>
                    <td><strong>Print Time</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $jakartaTime . '</td>
                </tr>
            </table>
                

        ';
        // <td style="text-align: right;">' . number_format($totalQty, 0, ',', '.') . '</td>
        $mpdf = new \Mpdf\Mpdf([
            'orientation' => 'L', // 'L' untuk landscape, 'P' untuk portrait (default)
            'margin_bottom' => 7,
        ]);
        
        // $footer = '<div style="width:100%; text-align:center; border-top: 1px solid black; padding-top:0px;"></div>';
        // $mpdf->SetHTMLFooter($footer);
        $mpdf->SetTitle('DN Tagih -' . $code . ' - ' . $jakartaTime);
        $mpdf->WriteHTML($html);
        $filename = 'DN-Tagih-' . $code . '-' . $jakartaTime . '.pdf';
        return $mpdf->Output($filename, 'I'); 
    }
    public function store_dn_tagih(Request $request)
    {
        try 
        {
            $tampunganData = $request->input('tampunganData', []);
            $jakartaTime = Carbon::now('Asia/Jakarta');
            $jakartaDate = Carbon::now('Asia/Jakarta')->startOfDay();
            $total_sales = $request->input('total_sales');
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $cabang_code = $request->input('cabang_code');
            $year = $jakartaTime->format('Y');
            $year_small = $jakartaTime->format('y');
            $month = $jakartaTime->format('m');
            $exp_data = $tampunganData[0];
            $exp_client = $exp_data['client_code'];
            $romanMonths = [
                '01' => 'I',
                '02' => 'II',
                '03' => 'III',
                '04' => 'IV',
                '05' => 'V',
                '06' => 'VI',
                '07' => 'VII',
                '08' => 'VIII',
                '09' => 'IX',
                '10' => 'X',
                '11' => 'XI',
                '12' => 'XII',
            ];
            $romanMonth = $romanMonths[$month];
            $day = $jakartaTime->format('d');
            $header_data = $tampunganData[0];
            $user = auth()->user()->username;
            $load_last_header = DB::connection('ms_sql_hgs')
                ->table('tr_tagih_sales_DN_h')
                ->select('SalesDNTagih_Code_h', 'salesdntagih_code_kwitansi', 'rec_datecreated')
                ->whereMonth('rec_datecreated', Carbon::now()->month)
                ->whereYear('rec_datecreated', Carbon::now()->year)
                ->orderByDesc('rec_datecreated')
                ->first();
            // dd($header_data);
            $last_number = null;
            if ($load_last_header && isset($load_last_header->SalesDNTagih_Code_h)) {
                $parts = explode('-', $load_last_header->SalesDNTagih_Code_h);
                $last_number = end($parts); 
            }
            $last_datte = !empty($load_last_header->rec_datecreated) 
                ? date('Y-m-d', strtotime($load_last_header->rec_datecreated)) 
                : date('Y-m-d');
            $date_now = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $las_kwitansi = null;
            if ($load_last_header && isset($load_last_header->salesdntagih_code_kwitansi)) {
                $parts = explode('/', $load_last_header->salesdntagih_code_kwitansi);
                $las_kwitansi = end($parts); 
            }
             if ($last_datte == $date_now) {
                $new_number_kwitansi = $las_kwitansi ? str_pad((int)$las_kwitansi, 4, '0', STR_PAD_LEFT) : '0001';
            } else {
                $new_number_kwitansi = $las_kwitansi ? str_pad((int)$las_kwitansi + 1, 4, '0', STR_PAD_LEFT) : '0001';
            }
            $new_number = $last_number ? str_pad((int)$last_number + 1, 4, '0', STR_PAD_LEFT) : '0001';
            $new_Code_Header = sprintf('INV-TSD-%s%s-%s', $year, $month, $new_number);
            $new_Code_Header_kwitansi = sprintf('KW/HGS/%s%s%s/%s',$day, $month, $year_small, $new_number_kwitansi);
            // dd($new_Code_Header);
            
            if($exp_client == 'TUA'){
                $data_kwitansi = DB::connection('ms_sql_hgs')->select("SELECT count(1) tot FROM [dbo].[tr_tagih_sales_DN_h] WHERE salesdntagih_client_code = 'TUA' and year(rec_datecreated) = year(GETDATE())"); 
                $kwitansi_last_number = $data_kwitansi[0]->tot;
                if($cabang_code == '0001'){
                    $cabang_kwitansi_code = 2;
                }elseif($cabang_code == '0002'){
                    $cabang_kwitansi_code = 1;
                }elseif($cabang_code == '0003'){
                    $cabang_kwitansi_code = 3;
                }
                $Code_kwitansi_new_by_adit = sprintf('%s.%s/KW/HGS/%s/%s',$kwitansi_last_number, $cabang_kwitansi_code, $romanMonth, $year);
            }

            // dd($Code_kwitansi_new_by_adit);

            $detailDataArray = [];
    
            foreach ($tampunganData as $index => $detail) {
                $note = empty($detail['note']) ? 'sesuai' : $detail['note'];
    
                $detailDataArray[] = [
                    'rec_comcode' => $detail['rec_comcode'],
                    'rec_areacode' => $detail['rec_areacode'],
                    'salesdntagih_code_h' => $new_Code_Header,
                    'salesdntagih_code_d' => $index + 1,           //str_pad($index + 1, 4, '0', STR_PAD_LEFT), 
                    'salesdntagih_Sales_dn_code' => $detail['Sales_DN_Code_d'],
                    'salesdntagih_Sales_dn_date' => $detail['Sales_DN_date'],
                    'salesdntagih_Tagih_value' => $detail['totalsales'],
                    'salesdntagih_Sales_dn_codeheader' => $detail['Sales_DN_Code'],
                    'salesdntagih_cocode_header' => $detail['Sales_DN_COcode'],
                    'salesdntagih_cocode' => $detail['Sales_DN_COno'],
                    'salesdntagih_drivercode' => $detail['Sales_DN_Driver'],
                    'salesdntagih_routevhcode' => $detail['Sales_DN_route_product_client_vehicle'],
                    'salesdntagih_qty' => $detail['Sales_DN_Productcodeqty'],
                    'salesdntagih_cost' => 0,
                    'salesdntagih_salesbotol' => $detail['routveh_salesbotol'],
                    'salesdntagih_vhcode' => $detail['Sales_DN_vehicle'],
                    'salesdntagih_status' => 1,
                    'salesdntagih_note' => $note,
                    'salesdntagih_NoBkb' => '',
                    'salesdntagih_suratjalan' => '',
                    'salesdntagih_customer' => '',
                    'no_urut' => $index + 1, 
                ];
            }
            $headerDataArray[] = [
                'rec_usercreated' => $user,
                'rec_userupdate' => $user,
                'rec_datecreated' => $jakartaTime,
                'rec_dateupdate' => $jakartaTime,
                'rec_status' => 1,
                'rec_comcode' => $header_data ['rec_comcode'],
                'rec_areacode' => $header_data ['rec_areacode'],
                'salesdntagih_code_h' => $new_Code_Header,
                'salesdntagih_dateregist_tagihan' => $jakartaDate,
                'salesdntagih_Total_tagihan' => $total_sales,
                'salesdntagih_Total_sales' => $total_sales,
                'salesdntagih_client_code' => $header_data ['client_code'],
                'salesdntagih_start_date' => $start_date,
                'salesdntagih_end_date' => $end_date,
                'salesdntagih_operator' => $user,
                'salesdntagih_code_kwitansi' => $new_Code_Header_kwitansi,
                'salesdntagih_bisniscode' => "",
                'salesdntagih_branchcode' => $header_data ['rec_areacode'],
                'salesdntagih_code_kwitansi_new' => $exp_client !== 'TUA'? null : ($Code_kwitansi_new_by_adit),
                'salesdntagih_code_cabang' => $header_data ['cab_code'],
            ];

            $pph4DataArray[] = [
                'no_kwitansi' => $new_Code_Header,
                'value_tagihan_dn' =>$total_sales,
                'value_est_pph_4' => $total_sales/10,
                'created_at' => $jakartaTime,
                'created_by' => $user,
                'value_ppn' => $total_sales/11,
                'value_pembebasan_ppn' => $total_sales/11 * -1,
            ];
            // dd($pph4DataArray);
            DB::connection('ms_sql_hgs')->table('tr_tagih_sales_DN_h')->insert($headerDataArray);
            $chunks = array_chunk($detailDataArray, 50);

            foreach ($chunks as $chunk) {
                DB::connection('ms_sql_hgs')->table('tr_tagih_sales_DN_d')->insert($chunk);
            }
            // DB::connection('ms_sql_hgs')->table('tr_tagih_sales_DN_pph4')->insert($pph4DataArray);
            DB::commit();
            return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $headerDataArray]);
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
    public function update_dn_tagih_po_code(Request $request)
    {
        try 
        {
            $code_header =  $request -> input('code_header');
            $code_po =  $request -> input('code_po');
            $jakartaTime = Carbon::now('Asia/Jakarta');
            $user = auth()->user()->username;
            // dd($code_po);
            DB::connection('ms_sql_hgs')
            ->table('tr_tagih_sales_DN_h')
            ->where('salesdntagih_code_h', $code_header)
            ->update([
                'salesdntagih_no_po' => $code_po,
                'rec_userupdate' => $user,
                'rec_dateupdate' => $jakartaTime,
            ]);
                        return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $code_header ]);
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
    public function update_dn_tagih_detail(Request $request)
    {
        try {
            // Inisialisasi variabel
                $tampunganData = $request->input('tampunganData', []);
                $tampunganData_add = $request->input('tampunganData_add', []);
                $total_tagihan = $request->input('total_tagihan');
                $akses_dari = $request->input('akses_dari');
                $header_code = $request->input('header_code');
                $username_pemeberi_akses = $request->input('username_pemeberi_akses');
                $totalTagihan_minus = 0;
                $totalTagihan_plus = 0;
                $dn_code = [];
                $jakartaTime = Carbon::now('Asia/Jakarta');
                $user = auth()->user()->username;
                $dn_for_history_1 = [];
                $dn_for_history_2 = [];
            // dd($dn_code_2);
            
                if (!empty($tampunganData)) {                
                    $head_data = $tampunganData[0];
                    $header_code = $head_data['salesdntagih_code_h'];
                    $dn_code = array_column($tampunganData, 'salesdntagih_Sales_dn_code');
                    $dn_for_history_1 = array_map(function($item) {
                        return [
                            'dn_code' => $item['salesdntagih_Sales_dn_code'],
                            'transaksi' => 'Delete'
                        ];
                    }, $tampunganData);
                    $totalTagihan_minus = array_sum(array_column($tampunganData, 'salesdntagih_Tagih_value'));
                }
                if (!empty($tampunganData_add)) {
                    $totalTagihan_plus = array_sum(array_column($tampunganData_add, 'Sales_DN_Sales_value'));
                    $tampunganData_add_trash_1 = array_filter($tampunganData_add, function($detail) {
                        return isset($detail['trash_data']) && $detail['trash_data'] == 1;
                    });
                    $tampunganData_add_not_trash_1 = array_filter($tampunganData_add, function($detail) {
                        return !isset($detail['trash_data']) || $detail['trash_data'] != 1;
                    }); 
                    $tampunganData_add_trash_1 = array_values($tampunganData_add_trash_1);
                    $tampunganData_add_not_trash_1 = array_values($tampunganData_add_not_trash_1);

                    $dn_code_2 = [];
                    $dn_code_2 = array_column($tampunganData_add_trash_1, 'Sales_DN_Code_d');
                    $dn_for_history_2 = array_map(function($item) {
                        return [
                            'dn_code' => $item['Sales_DN_Code_d'],
                            'transaksi' => 'Insert'
                        ];
                    }, $tampunganData_add);
                } else {
                }
            
                $gabungan_dn_history = array_merge($dn_for_history_1, $dn_for_history_2);
            // dd($gabungan_dn_history);
                $total_tagihan_baru = $total_tagihan - $totalTagihan_minus + $totalTagihan_plus ;
            // main transaction 
            DB::beginTransaction(); 
        
                if (!empty($tampunganData)) {
                    DB::connection('ms_sql_hgs')
                    ->table('tr_tagih_sales_DN_d')
                    ->where('salesdntagih_code_h', $header_code)
                    ->whereIn('salesdntagih_Sales_dn_code', $dn_code)
                    ->update([
                        'trash_data' => 1
                    ]);
                }
                if (!empty($tampunganData_add_trash_1)) {
                    DB::connection('ms_sql_hgs')
                    ->table('tr_tagih_sales_DN_d')
                    ->where('salesdntagih_code_h', $header_code)
                    ->whereIn('salesdntagih_Sales_dn_code', $dn_code_2)
                    ->update([
                        'trash_data' => 0
                    ]);
                }

                $last_number = DB::connection('ms_sql_hgs')
                    ->table('tr_tagih_sales_DN_d')
                    ->where('salesdntagih_code_h', $header_code)
                    ->count();
                
                $detailDataArray = [];
                
                if (!empty($tampunganData_add_not_trash_1)) {
                    foreach ($tampunganData_add as $index => $detail) {
                        $note = empty($detail['note']) ? 'sesuai' : $detail['note'];
                        $nomorUrut = $last_number + $index + 1;
        
                        $detailDataArray[] = [
                            'rec_comcode' => $detail['rec_comcode'],
                            'rec_areacode' => $detail['rec_areacode'],
                            'salesdntagih_code_h' => $header_code,
                            'salesdntagih_code_d' => $nomorUrut,
                            'salesdntagih_Sales_dn_code' => $detail['Sales_DN_Code_d'],
                            'salesdntagih_Sales_dn_date' => $detail['Sales_DN_date'],
                            'salesdntagih_Tagih_value' => $detail['totalsales'],
                            'salesdntagih_Sales_dn_codeheader' => $detail['Sales_DN_Code'],
                            'salesdntagih_cocode_header' => $detail['Sales_DN_COcode'],
                            'salesdntagih_cocode' => $detail['Sales_DN_COno'],
                            'salesdntagih_drivercode' => $detail['Sales_DN_Driver'],
                            'salesdntagih_routevhcode' => $detail['Sales_DN_route_product_client_vehicle'],
                            'salesdntagih_qty' => $detail['Sales_DN_Productcodeqty'],
                            'salesdntagih_cost' => 0,
                            'salesdntagih_salesbotol' => $detail['routveh_salesbotol'],
                            'salesdntagih_vhcode' => $detail['Sales_DN_vehicle'],
                            'salesdntagih_status' => 1,
                            'salesdntagih_note' => $note,
                            'salesdntagih_NoBkb' => '',
                            'salesdntagih_suratjalan' => '',
                            'salesdntagih_customer' => '',
                            'no_urut' => $nomorUrut,
                        ];
                    }
        
                    $chunks = array_chunk($detailDataArray, 50);
                    
                    // Insert data per chunk
                    foreach ($chunks as $chunk) {
                        DB::connection('ms_sql_hgs')->table('tr_tagih_sales_DN_d')->insert($chunk);
                    }
                }
                DB::connection('ms_sql_hgs')
                    ->table('tr_tagih_sales_DN_h')
                    ->where('salesdntagih_code_h', $header_code)
                    ->update([
                        'salesdntagih_Total_tagihan' => $total_tagihan_baru,
                        'salesdntagih_Total_sales' => $total_tagihan_baru,
                        'rec_dateupdate' => $jakartaTime,
                        'rec_userupdate' => $username_pemeberi_akses,
                    ]);
            // pencatatan history
            
                $last_number_history = DB::connection('ms_sql_hgs')
                    ->table('Tr_tagih_sales_DN_updated_record_h')
                    ->where('salesdntagih_code_h', $header_code)
                    ->count();
                $last_number_history = $last_number_history+1;
                $newcode_history = sprintf('%s-%s', $header_code, $last_number_history);

                DB::connection('ms_sql_hgs')
                    ->table('Tr_tagih_sales_DN_updated_record_h')
                    ->insert([
                        'salesdntagih_code_h' => $header_code,
                        'head_code' => $newcode_history,
                        'created_by' => $username_pemeberi_akses,
                        'created_at' => $jakartaTime,
                    ]);
                $detailDataArray_history = [];
                foreach ($gabungan_dn_history as $index => $detail) {
                    $detail_code = $newcode_history . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                    $detailDataArray_history[] = [
                        'head_code' => $newcode_history,
                        'detail_code' => $detail_code,
                        'dn_code' => $detail['dn_code'],
                        'transaction' => $detail['transaksi']
                    ];
                }
            
            // dd($detailDataArray_history);
            
                DB::connection('ms_sql_hgs')->table('Tr_tagih_sales_DN_updated_record_d')->insert($detailDataArray_history);
            DB::commit();
    
            return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $dn_code ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
    public function store_kwitansi(Request $request)
    {
        try {
            $header_code =  $request -> input('header_code');
            $note_kwitansi =  $request -> input('note_kwitansi');
            $data = DB::connection('ms_sql_hgs')->select(" 		
                SELECT
                    h.*,
                    clien_desc,
                    iif(p.no_kwitansi is null, 0, 1) no_kwitansi 
                FROM
                    tr_tagih_sales_DN_h h
                    JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id
                    LEFT JOIN tr_tagih_sales_DN_pph4 p ON p.no_kwitansi = h.salesdntagih_code_h 
                WHERE
                    salesdntagih_code_h = '$header_code'
            "); 
            // dd($data[0]);
            DB::beginTransaction();
                DB::connection('ms_sql_hgs')
                    ->table('tr_tagih_sales_DN_pph4')
                    ->insert([
                        'no_kwitansi' => $data[0]->salesdntagih_code_h,
                        'value_tagihan_dn' => $data[0]->salesdntagih_Total_tagihan,
                        'value_est_pph_4' => $data[0]->salesdntagih_Total_tagihan/10,
                        'created_by' => auth()->user()->username,
                        'created_at' => now('Asia/Jakarta'),
                        'value_ppn' => $data[0]->salesdntagih_Total_tagihan/11,
                        'value_pembebasan_ppn' => $data[0]->salesdntagih_Total_tagihan/11 * -1,
                        'note_kwitansi' => $note_kwitansi,
                    ]);            
                DB::commit();
            return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $data[0] ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
    
} 