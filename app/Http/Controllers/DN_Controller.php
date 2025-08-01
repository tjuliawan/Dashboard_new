<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Mpdf\Mpdf;
use Carbon\Carbon;
use App\Exports\Exportexeljapfa;
use Maatwebsite\Excel\Facades\Excel;

class DN_Controller extends Controller
{
    public function index()
    {
        Session::flash('url', 'Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view('dn_transaction.Add_New_Tagih_Sales_DN.index', compact('user'));
    }
    public function index_list_tr_tagih_sales_DN_d_date()
    {
        Session::flash('url', 'Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view('dn_transaction.list_tr_tagih_sales_DN_d_date.index', compact('user'));
    }
    public function index_edit_tagih_sales_dn()
    {
        Session::flash('url', 'Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view('dn_transaction.edit_tagih_sales_dn.index', compact('user'));
    }
    public function index_kwitansi()
    {
        Session::flash('url', 'Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        // dd($email);
        $user = User::where('email', $email)->firstOrFail();
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view('dn_transaction.kwitansi.index', compact('user'));
    }
    public function index_import_japfa()
    {
        return view('dn_transaction.import.import_japfa');
    }
    public function index_kwitansi_japfa()
    {
        Session::flash('url', 'Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        // dd($email);
        $user = User::where('email', $email)->firstOrFail();
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view('dn_transaction.kwitansi.index_japfa', compact('user'));
    }
    public function index_edit_tagih_sales_dn_from_ba()
    {
        Session::flash('url', 'Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view('dn_transaction.edit_tagih_sales_dn_from_ba.index', compact('user'));
    }
    public function get_client(Request $request)
    {
        $data = DB::connection('ms_sql_hgs')->select("
            SELECT * from ms_client WHERE rec_status = 1 ORDER BY clien_id
        ");
        return response()->json($data);
    }
    public function get_chaimber(Request $request)
    {
        $data = DB::connection('ms_sql_hgs')->select("
            SELECT DISTINCT
                Sales_DN_route_product_client_vehicle rute
            FROM
                tr_acc_transaksi_sales_DN_d
            WHERE
                YEAR( Sales_DN_date ) = YEAR( GETDATE( ) )
            ORDER BY
                rute
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
        ini_set('max_execution_time', 1200);
        $client =  $request->input('client');
        $cabang =  $request->input('cabang');
        $business =  $request->input('business');
        $vehicle =  $request->input('vehicle');
        $start_date =  $request->input('start_date');
        $end_date =  $request->input('end_date');
        $product =  $request->input('product');
        $chaimber =  $request->input('chaimber');
        $dn_code =  $request->input('dn_code');
        // dd($product);
        $startDate_condition = "";
        $endDate_condition = "";
        $cabang_condition = "";
        $business_condition = "";
        $registerDate_condition = "";
        $client_condition = "";
        $vehicle_condition = "";
        $product_condition = "";
        $chaimber_condition = "";
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
        if (!empty($chaimber)) {
            // dd($chaimber);
            $chaimber_list = "'" . implode("','", $chaimber) . "'";
            $chaimber_condition = " AND otranssalesdn.Sales_DN_route_product_client_vehicle IN ($chaimber_list)";
        } else {
            $chaimber_condition = "";
        }
        if (!empty($product)) {
            $product_list = "'" . implode("','", $product) . "'";
            $product_condition = " AND osalesdnpro.sales_dn_productcode IN ($product_list)";
        } else {
            $product_condition = "";
        }
        if ($dn_code != "" || $vehicle != null) {
            $dn_code_condition = " AND otranssalesdn.Sales_DN_Code_d like '%$dn_code%'";
        }
        DB::connection('ms_sql_hgs')->statement("

        ");
        // dd($chaimber_condition);
        $query = "
            with tagih_temp as (
                SELECT
                    salesdntagih_cocode,
                    salesdntagih_Sales_dn_code,
                    no_urut,
                    isnull(trash_data, 0) trash_data
                FROM tr_tagih_sales_DN_d
                WHERE year(salesdntagih_Sales_dn_date) >= YEAR(GETDATE()) -- sementara tahun ini
            ),tbl as(
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
                    iif(cab_desc = 'HGS', 'HGS-Sentul', cab_desc) cab_desc1,
                    cab_code cab_code1,
                    otranssalesdnh.client_code,
                    osalesdnpro.sales_dn_productcode,
                    otagissalesdn.trash_data
                FROM
                    tr_acc_transaksi_sales_DN_d otranssalesdn left join
                    ms_driver odrv on otranssalesdn.Sales_DN_Driver = odrv.Drv_Id left join
                    ms_helper ohlper on otranssalesdn.Sales_DN_helpercode = ohlper.Hlper_Id left join
                    tr_acc_transaksi_sales_DN_h otranssalesdnh on otranssalesdn.Sales_DN_Code = otranssalesdnh.Sales_DN_Code left join
                    tr_acc_transaksi_sales_DN_d_product osalesdnpro on  otranssalesdn.Sales_DN_Code = osalesdnpro.Sales_DN_Code and otranssalesdn.Sales_DN_Code_d = osalesdnpro.Sales_DN_Code_d left join
                    tagih_temp DN_d on otranssalesdn.Sales_DN_Code_d = DN_d.salesdntagih_Sales_dn_code left join
                    ms_routevehicle orutvh on otranssalesdn.Sales_DN_route_product_client_vehicle = orutvh.routveh_code left join
                    tagih_temp otagissalesdn on otranssalesdn.Sales_DN_COno = otagissalesdn.salesdntagih_cocode
                    left join ms_cabang area on SUBSTRING(Sales_DN_closingcode, 1, 4) = area.cab_code
                WHERE
                    (1=1
                    $client_condition
                    $cabang_condition
                    $vehicle_condition
                    $product_condition
                    $startDate_condition
                    $endDate_condition
                    $dn_code_condition
                    $chaimber_condition
                    AND otranssalesdnh.rec_status != '0') and
                    (otagissalesdn.trash_data is null or otagissalesdn.trash_data = 1) and otranssalesdnh.rec_status = '2'
            )
            SELECT distinct
                tbl.* ,
                isnull(
                    iif ( isnull( cab_desc1, dn.rec_comcode ) = 'HGS', 'HGS-Sentul', cab_desc1 ),
                    IIF (
                        dn.rec_areacode = '0001',
                        'HGS-Sentul',
                        IIF ( dn.rec_areacode = '0002', 'HGS-Ciherang', IIF ( dn.rec_areacode = '0003', 'HGS-SUBANG', 'Cabang Tidak Dikenal' ) )
                    )
                ) cab_desc,
                isnull( cab_code1, dn.rec_areacode ) cab_code
            FROM
                tbl
                LEFT JOIN tr_kp_order_DN dn ON kporderdn_DN_final_code = Sales_DN_Code_d
            ORDER BY
                Sales_DN_date DESC
        ";
        // dd($query);

        $data = DB::connection('ms_sql_hgs')->select($query);
        return response()->json($data);
    }
    public function get_table_list_tr_tagih_sales_DN_d_date(Request $request)
    {
        $client =  $request->input('client');
        $vehicle =  $request->input('vehicle');
        $business =  $request->input('business');
        $register_date =  $request->input('register_date');
        $start_date =  $request->input('start_date');
        $end_date =  $request->input('end_date');
        $input_main_code =  $request->input('input_main_code');
        $product =  $request->input('product');

        $startDate_condition = "";
        $endDate_condition = "";
        $vehicle_condition = "";
        $business_condition = "";
        $registerDate_condition = "";
        $input_main_code_condition = "";
        $client_condition = "";
        $product_condition = "";
        // dd($client);
        if ($client == 'TUA') {
            $more_order_condition = 'salesdntagih_vhcode,';
        } else {
            $more_order_condition = "";
        }

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
            $data_h = DB::connection('ms_sql_hgs')->select("
                SELECT
                    *
                From
                    tr_tagih_sales_DN_h
                WHERE
                    salesdntagih_code_h = '$input_main_code'
            ");
            $client = $data_h[0]->salesdntagih_client_code;
            if ($client == 'TUA') {
                $more_order_condition = 'salesdntagih_vhcode,';
            } else {
                $more_order_condition = "";
            }
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
                LEFT JOIN tr_acc_transaksi_sales_DN_d p ON p.Sales_DN_Code_d = Tagih_det.salesdntagih_Sales_dn_code and p.Sales_DN_Code = Tagih_det.salesdntagih_Sales_dn_codeheader
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
                $more_order_condition
                no_urut
        ";
        // dd($query);
        $data = DB::connection('ms_sql_hgs')->select($query);
        // dd($data);

        return response()->json($data);
    }
    public function get_table_for_edit_dn_tgih(Request $request)
    {
        $client =  $request->input('client');
        $vehicle =  $request->input('vehicle');
        $business =  $request->input('business');
        $register_date =  $request->input('register_date');
        $start_date =  $request->input('start_date');
        $end_date =  $request->input('end_date');
        $input_main_code =  $request->input('input_main_code');
        $product =  $request->input('product');

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
                LEFT JOIN tr_acc_transaksi_sales_DN_d p ON p.Sales_DN_Code_d = Tagih_det.salesdntagih_Sales_dn_code and p.Sales_DN_Code = Tagih_det.salesdntagih_Sales_dn_codeheader
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
    public function get_table_add_tagih_sales_dn_from_ba(Request $request)
    {
        $co_code =  $request->input('co_code');

        if ($co_code != "" || $vehicle != null) {
            $co_code_condition = " AND kporderdn_co_final_code like '%$co_code%'";
        }
        // dd($co_code_condition);
        $query = "
            SELECT
                h.rec_comcode,
                h.rec_comcode cab_desc,
                h.rec_areacode,
                h.rec_areacode cab_code,
                concat('BR-',h.kporderdn_cocode )Sales_DN_Code,
                kporderdn_DN_final_code Sales_DN_Code_d,
                cast(kporderdn_DN_date as date) Sales_DN_date,
                routveh_code Sales_DN_route_product_client_vehicle,
                kporderdn_vhcodereal Sales_DN_vehicle,
                kporderdn_Drivercodereal Sales_DN_Driver,
                kporderdn_co_final_code Sales_DN_COno,
                kporderdn_Helper_code Sales_DN_helpercode,
                kporderdn_spk Sales_DN_spkno,
                routveh_salesbotol * routveh_qty totalsales,
                routveh_salesbotol routveh_salesbotol,
                routveh_qty Sales_DN_Productcodeqty,
                kporderdn_Client_order client_code,
                kporderdn_prodsubcode sales_dn_productcode,
                coh.acc_co_code Sales_DN_COcode
            FROM
                tr_kp_order_DN h
                JOIN ms_routevehicle r ON h.kporderdn_route_vhcl_code_real = r.routveh_code
                join tr_kp_order_product_d p on p.kporderdn_cocode = kporderdn_co_final_code
                left join tr_acc_co_d cod on cod.acc_co_noco = kporderdn_co_final_code
                left join tr_acc_co_h coh on coh.acc_co_code = cod.acc_co_code
                left join tr_tagih_sales_DN_d tsd on tsd.salesdntagih_cocode = kporderdn_co_final_code
                left join tr_acc_transaksi_sales_DN_d val on val.Sales_DN_COno = kporderdn_co_final_code
            WHERE 1=1
                and tsd.salesdntagih_cocode is null
                and val.Sales_DN_COno is null
                $co_code_condition
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
                -- clien_id2 clien_desc,
                iif(p.no_kwitansi is null, 0, 1) no_kwitansi ,
                iif(salesdntagih_code_cabang = '0003' and clien_id2 = 'PT. TIRTA UTAMA ABADI' , 'PT. WENANG PALM SOLUSINDO', iif(clien_id2 is null, clien_desc, clien_id2)) clien_desc,
                note_kwitansi, total
            FROM
                tr_tagih_sales_DN_h h
                JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id
                LEFT JOIN tr_tagih_sales_DN_pph4 p ON p.no_kwitansi = h.salesdntagih_code_h
                 LEFT JOIN (
                    SELECT
                        salesdntagih_code_h kode,
                        SUM( salesdntagih_Tagih_value ) total
                    FROM
                        tr_tagih_sales_DN_d
                    WHERE
                        salesdntagih_code_h = '$code' and (trash_data is null or trash_data != 1)
                    GROUP BY
                        salesdntagih_code_h
                ) v on v.kode = h.salesdntagih_code_h
            WHERE
                salesdntagih_code_h = '$code'
        ");
        return response()->json($data[0]);
    }
    public function get_header_dn_tagih_japfa(Request $request)
    {
        $tahun = $request->get('tahun');
        $lokasi = $request->get('lokasi');
        $bulan = $request->get('bulan');
        // dd($bulan);
        $data = DB::connection('ms_sql_hgs')->select("
            WITH cte AS (
                SELECT
                    DATEPART(YEAR, himp.invoice_date) AS tahun,
                    DATEPART(MONTH, himp.invoice_date) AS bulan,
                    DATEPART(YEAR, SO_Date) AS tahun_so,
                    DATEPART(MONTH, SO_Date) AS bulan_so,
                    himp.invoice_number,
                    number invoice_number2,
                    -- himp.retailer_code,
                    isnull(partner_name, himp.retailer_name) retailer_name,
                    dimp.distributor_stock_keeping_unit AS [Id product],
                    sku_description AS [Product],
                    dimp.unit AS Unit,
                    dimp.eaches_quantity AS qty,
                    dimp.unit_price AS [price],
                    dimp.net_value AS [Net/value],
                    CONVERT(DATE, himp.invoice_date) AS Invoice_date,
                    CONVERT(DATE, dpch.rec_datecreated) AS [Send date],
                    dpch.dpcth_code_h,
                    dpchd.Dptch_qty_terima * CONVERT(INT, SUBSTRING(SKU_convertpcs, 0, CHARINDEX(' ', SKU_convertpcs))) AS [Qty Terima],
                    dpchd.Dptch_qty_terima * CONVERT(INT, SUBSTRING(SKU_convertpcs, 0, CHARINDEX(' ', SKU_convertpcs))) * 422 AS [Value total KG],
                    SUBSTRING(himp.invoice_number, 1, 3) AS wilayah,
                    iif(SUBSTRING(himp.invoice_number, 1, 3) = 'JKT', 'Cipinang', 'Tanggerang') AS cabang,
                    CONCAT(dpch.dpcth_code_h, '-', dpcth_so) AS dpc,
                    himp.invimp_code,
                    SKU_description
                FROM
                    tgu_tr_invoice_h_import himp
                    LEFT JOIN tgu_tr_invoice_d_import dimp
                        ON himp.invoice_number = dimp.invoice_number
                    LEFT JOIN tgu_ms_product_Business mspro
                        ON dimp.distributor_stock_keeping_unit = mspro.sku_business
                        AND mspro.business = 'japfa'
                    LEFT JOIN TGU_dispatch_h dpch
                        ON himp.invoice_number = dpch.dpcth_so
                    LEFT JOIN TGU_dispatch_d dpchd
                        ON dpch.dpcth_code_h = dpchd.dptch_code_h
                        AND dpch.Dpcth_SO = dpchd.Dptch_SO
                        AND dimp.distributor_stock_keeping_unit = dpchd.Dptch_Product
                    JOIN tr_so_main so on so.invoice_number = himp.invoice_number
                    LEFT JOIN (select distinct sale_order, partner_name, number from tgu_tr_invoice_import_for_japfa) imp on imp.sale_order = himp.invoice_number
                WHERE
                    himp.client = 'Japfa'
                    AND DATEPART(YEAR, SO_Date) = '$tahun'
                    AND DATEPART(month, SO_Date) = '$bulan'
            ),

            operasi AS (
                SELECT
                    tahun,
                    SUBSTRING(invoice_number, 1, 3) AS wilayah,
                    SUM(CONVERT(INT, qty)) AS berat
                FROM
                    cte c1
                    LEFT JOIN tr_acc_transaksi_coa coa
                        ON c1.dpc = coa.transcoa_coa_desc
                        AND coa.transcoa_transaksi_main_code = '601'
                        AND coa.transcoa_coa_code = '698'
                GROUP BY
                    tahun,
                    SUBSTRING(invoice_number, 1, 3)
            )
            SELECT
                tahun,
                wilayah,
                berat,
                CASE
                    WHEN wilayah = 'TNG' AND berat < 250000 THEN 105000000
                    WHEN wilayah = 'TNG' AND berat >= 400000 THEN berat * 350
                    WHEN wilayah = 'TNG' AND berat >= 350000 THEN berat * 367
                    WHEN wilayah = 'TNG' AND berat >= 300000 THEN berat * 406
                    WHEN wilayah = 'TNG' AND berat >= 250000 THEN berat * 422
                    WHEN wilayah = 'JKT' AND berat > 400000 THEN berat * 366
                    WHEN wilayah = 'JKT' AND berat < 400000 THEN 122305236
                    ELSE 0
                END biaya,
                CASE
                    WHEN wilayah = 'TNG' AND berat < 250000 THEN 105000000
                    WHEN wilayah = 'TNG' AND berat >= 400000 THEN berat * 350
                    WHEN wilayah = 'TNG' AND berat >= 350000 THEN berat * 367
                    WHEN wilayah = 'TNG' AND berat >= 300000 THEN berat * 406
                    WHEN wilayah = 'TNG' AND berat >= 250000 THEN berat * 422
                    WHEN wilayah = 'JKT' AND berat > 400000 THEN berat * 366
                    WHEN wilayah = 'JKT' AND berat < 400000 THEN 122305236
                    ELSE 0
                END * 0.02 pajak
            FROM
                operasi
            WHERE wilayah = '$lokasi';
        ");
        return response()->json($data[0]);
    }
    public function exportExcel()
    {
        return Excel::download(new Exportexeljapfa, 'laporan_invoice_jkt_2025.xlsx');
    }
    public function get_header_dn_tagih_japfa2(Request $request)
    {
        $tahun = $request->get('tahun');
        $lokasi = $request->get('lokasi');
        $bulan = $request->get('bulan');
        if ($lokasi == 'JKT') {
            $lokasicode = 'CPG';
        } else {
            $lokasicode = $lokasi;
        }
        $data = DB::connection('ms_sql_hgs')->select("
            select * from tr_tagih_sales_DN_kwitansi_japfa where bulan_inv = '$bulan' and tahun_inv = '$tahun' and lokasi ='$lokasicode'
        ");
        return response()->json($data);
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
                kasirbranchcash_suratjalan kporderdn_BKB_no
            from
                tr_tagih_sales_DN_h TagihH
                inner Join  tr_tagih_sales_DN_d TagihDet on TagihH.salesdntagih_code_h= TagihDet.salesdntagih_code_h
                left join ms_client Cli on tagihH.salesdntagih_client_code = Cli.clien_id
                left Join ms_driver drv on TagihDet.salesdntagih_drivercode= drv.Drv_Id
                left join tr_Kp_order_DN_depo depo on depo.kporderdn_cocode = TagihDet.salesdntagih_cocode
                left join tr_acc_transaksi_kasir_cash_branch_uang_jalan u on TagihDet.salesdntagih_cocode = u.kasirbranchcash_COno
            WHERE TagihH.salesdntagih_code_h = '$code' and (trash_data is null or trash_data != 1)
            ORDER BY
                no_urut
        ";
        // dd($query);
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
                                    <td><strong>Code : </strong> ' . $code . '</td>
                                    <td><strong>Date :  </strong>' . $date . ' </td>
                                </tr>
                                <tr>

                                    <td><strong>Operator : </strong>' . $operator . '</td>
                                    <td><strong>Branch :</strong>    ' . $branch . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table class="header-info" style=" max-width:40px;">
                    <tr>
                        <td><strong>Periode date</strong></td>
                        <td><strong>:</strong></td>
                        <td colspan="4">' . $data_sumary->salesdntagih_start_date . ' - ' . $data_sumary->salesdntagih_end_date . '</td>
                    </tr>
                    <tr>
                        <td><strong>Operator</strong></td>
                        <td><strong>:</strong></td>
                        <td>' . $operator . '</td>
                        <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                        <td><strong>:</strong></td>
                        <td>' . $total_tagihan_sales . '</td>
                    </tr>
                    <tr>
                        <td><strong>Client</strong></td>
                        <td><strong>:</strong></td>
                        <td style=" max-width: fit-content;">' . $data_sumary->salesdntagih_client_code . '</td>
                        <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                        <td><strong>:</strong></td>
                        <td>' . $total_inv . '</td>
                    </tr>
                    <tr>
                        <td><strong>Bisnis</strong></td>
                        <td><strong>:</strong></td>
                        <td>' . $data_sumary->salesdntagih_bisniscode . '</td>
                        <td><strong style="margin-left: 15px;">No. PO</strong></td>
                        <td><strong>:</strong></td>
                        <td>' . $data_sumary->salesdntagih_no_po . '</td>
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
        } elseif ($client_code == 'SHN') {
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
                                        <td><strong>Code : </strong> ' . $code . '</td>
                                        <td><strong>Date :  </strong>' . $date . ' </td>
                                    </tr>
                                    <tr>

                                        <td><strong>Operator : </strong>' . $operator . '</td>
                                        <td><strong>Branch :</strong>    ' . $branch . '</td>
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
                            <td colspan="4">' . $data_sumary->salesdntagih_start_date . ' - ' . $data_sumary->salesdntagih_end_date . '</td>
                        </tr>
                        <tr>
                            <td><strong>Operator</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $operator . '</td>
                            <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $total_tagihan_sales . '</td>
                        </tr>
                        <tr>
                            <td><strong>Client</strong></td>
                            <td><strong>:</strong></td>
                            <td style=" max-width: fit-content;">' . $data_sumary->salesdntagih_client_code . '</td>
                            <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $total_inv . '</td>
                        </tr>
                        <tr>
                            <td><strong>Bisnis</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $data_sumary->salesdntagih_bisniscode . '</td>
                            <td><strong style="margin-left: 15px;">No. PO</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $data_sumary->salesdntagih_no_po . '</td>
                        </tr>
                    </table>
                    <table class="special-table" style="border: 1px solid black; border-collapse: collapse; font-size: 9px; vertical-align: middle;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>DN/Invoice</th>
                                <th>TGL DN</th>
                                <th>CO/SO</th>
                                <th>Driver</th>
                                <th>Cust</th>
                                <th>Vehicle</th>
                                <th>Qty</th>
                                <th>Rate (Rp)</th>
                                <th>Route</th>
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
                                    <td style="text-align: right;">' . number_format($row->salesdntagih_Tagih_value, 0, ',', '.') . '</td>
                                    <td style="text-align: left;">' . $row->salesdntagih_routevhcode . '</td>
                                    <td style="text-align: left;">' . $row->salesdntagih_NoBkb . '</td>
                                    <td style="text-align: left;">' . $row->kporderdn_BKB_no . '</td>
                                </tr>';
                $counter++;
            }


            $html .= '
                            <tr>
                                <td colspan="6" style="text-align: center; font-size: 9px;">Total</td>
                                <td colspan="3" style="text-align: right; font-size: 9px;">' . $total_tagihan_sales . '</td>
                                <td colspan="4" style="text-align: center; font-size: 9px;"></td>
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
                                    <td style="text-decoration: underline;">' . $operator . '</td>
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
                                        <td><strong>Code : </strong> ' . $code . '</td>
                                        <td><strong>Date :  </strong>' . $date . ' </td>
                                    </tr>
                                    <tr>

                                        <td><strong>Operator : </strong>' . $operator . '</td>
                                        <td><strong>Branch :</strong>    ' . $branch . '</td>
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
                            <td colspan="4">' . $data_sumary->salesdntagih_start_date . ' - ' . $data_sumary->salesdntagih_end_date . '</td>
                        </tr>
                        <tr>
                            <td><strong>Operator</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $operator . '</td>
                            <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $total_tagihan_sales . '</td>
                        </tr>
                        <tr>
                            <td><strong>Client</strong></td>
                            <td><strong>:</strong></td>
                            <td style=" max-width: fit-content;">' . $data_sumary->salesdntagih_client_code . '</td>
                            <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $total_inv . '</td>
                        </tr>
                        <tr>
                            <td><strong>Bisnis</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $data_sumary->salesdntagih_bisniscode . '</td>
                            <td><strong style="margin-left: 15px;">No. PO</strong></td>
                            <td><strong>:</strong></td>
                            <td>' . $data_sumary->salesdntagih_no_po . '</td>
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
                                <td colspan="3" style="text-align: right; font-size: 9px;">' . $total_tagihan_sales . '</td>
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
                                    <td style="text-decoration: underline;">' . $operator . '</td>
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
        // dd($client_code);

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $tanggal = $jakartaTime->day;
        $bulanIndo = $bulan[$jakartaTime->month];
        $tahun = $jakartaTime->year;

        // Formatkan hasil menjadi "3 Mei 2025"

        $query = "
            SELECT
                h.*,
                -- clien_id2 clien_desc,
                iif(p.no_kwitansi is null, 0, 1) no_kwitansi ,
                iif(salesdntagih_code_cabang = '0003' and clien_id2 = 'PT. TIRTA UTAMA ABADI' , 'PT. WENANG PALM SOLUSINDO', iif(clien_id2 is null, clien_desc, clien_id2)) clien_desc,
                note_kwitansi,
                cast(created_at as date) tgl_kwitansi, total
            FROM
                tr_tagih_sales_DN_h h
                JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id
                LEFT JOIN tr_tagih_sales_DN_pph4 p ON p.no_kwitansi = h.salesdntagih_code_h
                LEFT JOIN (
                    SELECT
                        salesdntagih_code_h kode,
                        SUM( salesdntagih_Tagih_value ) total
                    FROM
                        tr_tagih_sales_DN_d
                    WHERE
                        salesdntagih_code_h = '$code' and (trash_data is null or trash_data != 1)
                    GROUP BY
                        salesdntagih_code_h
                ) v on v.kode = h.salesdntagih_code_h
            WHERE
                YEAR( h.rec_datecreated ) = YEAR( GETDATE( ) )
                and  salesdntagih_code_h = '$code'

        ";
        $data = DB::connection('ms_sql_hgs')->select($query);
        $data_sumary = $data[0];
        $cabang_transaksi = $data_sumary->salesdntagih_code_cabang;
        $tgl_kwitansi = Carbon::parse($data_sumary->tgl_kwitansi);
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $tanggal = $tgl_kwitansi->day;
        $bulanIndo = $bulan[$tgl_kwitansi->month];
        $tahun = $tgl_kwitansi->year;
        $formattedDate =  $tanggal . ' ' . $bulanIndo . ' ' . $tahun;
        if ($cabang_transaksi == '0001') {
            $cabang_transaksi = 'Sentul';
        } elseif ($cabang_transaksi  == '0002') {
            $cabang_transaksi = 'Ciherang';
        } elseif ($cabang_transaksi  == '0003') {
            $cabang_transaksi = 'Subang';
        }
        $branch = $data_sumary->salesdntagih_code_cabang;
        function formatTanggalIndo($tanggal)
        {
            $bulan = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
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

        $salesdntagih_Total_tagihan = round($data_sumary->total);
        $intValuetotal = floatval($data_sumary->total);
        // dd($salesdntagih_Total_tagihan);

        $salesdntagih_Total_tagihan = 'Rp ' . number_format(round($salesdntagih_Total_tagihan), 0, ',', '.');
        function terbilang($angka)
        {
            $angka = abs($angka);
            $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
            $hasil = "";

            if ($angka < 12) {
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
                $hasil = terbilang(floor($angka / 1000000000)) . " milyar " . terbilang($angka % 1000000000);
            }

            return trim(preg_replace('/\s+/', ' ', $hasil));
        }

        function terbilang_rupiah_koma($angka)
        {
            $pecah = explode('.', number_format($angka, 2, '.', ''));
            $bulat = (int) $pecah[0];
            $desimal = (int) ltrim($pecah[1], '0');

            $hasil = ucwords(terbilang($bulat));

            if ($desimal > 0) {
                $hasil .= ' Koma ' . ucwords(terbilang($desimal));
            }

            return $hasil . ' Rupiah';
        }
        $jumlah_terbilang = terbilang_rupiah_koma(round(floatval($data_sumary->total)));
        $periode = ($startDate === $endDate) ? $startDate : $startDate . ' - ' . $endDate;
        if($client_code == 'SHN'){
            $clien_desc = 'PT Sarihusada Generasi Mahardhika';
        }else{
            $clien_desc = $data_sumary->clien_desc;
        }
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
                    <td><span style="font-weight: ; border:1px solid #000;">' . $code . '</span></td>
                </tr>
                <tr>
                    <td style=" width:130px;"><strong>Sudah terima dari</strong></td>
                    <td><strong>:</strong></td>
                    <td><span style="font-weight: bold;">' . $clien_desc . '</span></td>
                </tr>
                <tr>
                    <td><strong>Uang sejumlah</strong></td>
                    <td><strong>:</strong></td>
                    <td style="border:1px solid #000;"><span style="font-size:14px; font-weight: bold; font-style: italic;">' . $jumlah_terbilang . '</span></td>
                </tr>
                    <tr>
                    <td><strong>Untuk pembayaran</strong></td>
                    <td><strong>:</strong></td>
                    <td style:"max-width: 390px;"><span style="font-weight: bold;">' . $data_sumary->note_kwitansi . '</span></td>
                </tr>
                <tr>
                    <td><strong>Jumlah</strong></td>
                    <td><strong>:</strong></td>
                    <td><span style="font-size:18px; font-weight: bold; font-style: italic;">' . $salesdntagih_Total_tagihan . '</span></td>
                </tr>
            </table>
            <table class="header-info 2">
                <tr>
                    <td colspan="3"><strong>Informasi Rekening Pembayaran</strong></td>
                    <td style="width:230px; text-align: center;"><span style="text-align: center;">Jakarta, ' . $formattedDate . '</span></td>
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
    public function cetakPDF_kwitansi_japfa(Request $request)
    {
        $code = $request->get('code');
        $client_code = $request->get('client_code');
        $user = auth()->user()->username;
        $jakartaTime = Carbon::now('Asia/Jakarta');

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $tanggal = $jakartaTime->day;
        $bulanIndo = $bulan[$jakartaTime->month];
        $tahun = $jakartaTime->year;

        // Formatkan hasil menjadi "3 Mei 2025"

        $query = "
            SELECT
                *
            FROM
                tr_tagih_sales_DN_kwitansi_japfa
            where no_kwitansi ='$code'

        ";
        $data = DB::connection('ms_sql_hgs')->select($query);
        $data_sumary = $data[0];

        $tgl_kwitansi = Carbon::parse($data_sumary->created_at);

        $tanggal = $tgl_kwitansi->day;
        $bulanIndo = $bulan[$tgl_kwitansi->month];
        $tahun = $tgl_kwitansi->year;
        $formattedDate =  $tanggal . ' ' . $bulanIndo . ' ' . $tahun;
        // dd($data_sumary);

        $operator = $data_sumary->created_by;

        $salesdntagih_Total_tagihan = round($data_sumary->value_tagihan_dn);
        $intValuetotal = floatval($data_sumary->value_tagihan_dn);

        $salesdntagih_Total_tagihan = 'Rp ' . number_format(round($salesdntagih_Total_tagihan), 0, ',', '.');
        function terbilang($angka)
        {
            $angka = abs($angka);
            $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
            $hasil = "";

            if ($angka < 12) {
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
                $hasil = terbilang(floor($angka / 1000000000)) . " milyar " . terbilang($angka % 1000000000);
            }

            return trim(preg_replace('/\s+/', ' ', $hasil));
        }

        function terbilang_rupiah_koma($angka)
        {
            $pecah = explode('.', number_format($angka, 2, '.', ''));
            $bulat = (int) $pecah[0];
            $desimal = (int) ltrim($pecah[1], '0');

            $hasil = ucwords(terbilang($bulat));

            if ($desimal > 0) {
                $hasil .= ' Koma ' . ucwords(terbilang($desimal));
            }

            return $hasil . ' Rupiah';
        }
        $jumlah_terbilang = terbilang_rupiah_koma(round(floatval($data_sumary->value_tagihan_dn)));
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
            <table class="header-info" style="font-size: 12px; border-collapse: collapse;">
                <tr>
                    <td style="width: 150px;"><strong>No</strong></td>
                    <td style="width: 10px;">:</td>
                    <td style="">' . $code . '</td>
                </tr>
                <tr>
                    <td><strong>Sudah terima dari</strong></td>
                    <td>:</td>
                    <td><strong>PT. SANTOSA UTAMA LESATARI</strong></td>
                </tr>
                <tr>
                    <td><strong>Uang sejumlah</strong></td>
                    <td>:</td>
                    <td style="border: 1px solid #000; padding: 4px 8px;">
                        <span style="font-style: italic; font-weight: bold;">' . $jumlah_terbilang . '</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Untuk pembayaran</strong></td>
                    <td>:</td>
                    <td><strong>' . $data_sumary->note_kwitansi . '</strong></td>
                </tr>
                <tr>
                    <td><strong>Jumlah</strong></td>
                    <td>:</td>
                    <td>
                        <span style="font-size: 18px; font-weight: bold; font-style: italic;">
                            ' . $salesdntagih_Total_tagihan . '
                        </span>
                    </td>
                </tr>
            </table>
            <table class="header-info 2">
                <tr>
                    <td colspan="3"><strong>Informasi Rekening Pembayaran</strong></td>
                    <td style="width:230px; text-align: center;"><span style="text-align: center;">Jakarta, ' . $formattedDate . '</span></td>
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
    public function cetakPDF_inv_japfa(Request $request)
    {
        $code = $request->get('code');
        $user = auth()->user()->username;
        $jakartaTime = Carbon::now('Asia/Jakarta');

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $tanggal = $jakartaTime->day;
        $bulanIndo = $bulan[$jakartaTime->month];
        $tahun = $jakartaTime->year;
        // Formatkan hasil menjadi "3 Mei 2025"
        $query = "
            SELECT
                *
            FROM
                tr_tagih_sales_DN_kwitansi_japfa
            where no_kwitansi ='$code'

        ";
        $query2 = "
            SELECT
                *
            FROM
                tr_acc_transaksi_coa h
                left join
                (
                SELECT
                coa_code,
                coa_desc
            FROM
                ms_acc_coa
                ) d on h.transcoa_coa_code = d.coa_code
            WHERE
                transcoa_head_code = '$code'

        ";
        $data = DB::connection('ms_sql_hgs')->select($query);
        $data_table = DB::connection('ms_sql_hgs')->select($query2);
        $total_debet = 0;
        $total_kredit = 0;

        foreach ($data_table as $row) {
            $total_debet += $row->transcoa_debet_value;
            $total_kredit += $row->transcoa_credit_value;
        }
        $total_debet = 'Rp ' . number_format($total_debet, 0, ',', '.');
        $total_kredit = 'Rp ' . number_format($total_kredit, 0, ',', '.');
        $data_sumary = $data[0];

        $tgl_kwitansi = Carbon::parse($data_sumary->created_at);
        $tanggal = $tgl_kwitansi->day;
        $bulanIndo = $bulan[$tgl_kwitansi->month];
        $tahun = $tgl_kwitansi->year;
        $formattedDate =  $tanggal . ' ' . $bulanIndo . ' ' . $tahun;
        $salesdntagih_Total_tagihan = round($data_sumary->value_tagihan_dn);
        $salesdntagih_Total_tagihan = 'Rp ' . number_format(round($salesdntagih_Total_tagihan), 0, ',', '.');

        $html = '
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
                    width: 60%;
                    border-collapse: collapse; /* Menghilangkan jarak antar border */
                    margin-left: 20px; /* Memberikan jarak antara h2 dan tabel */
                    border: 1px solid #ccc; /* Garis tipis di luar tabel */
                }

                .table_kop td {
                    padding: 3px;
                    border: 1px solid #ccc; /* Garis tipis di setiap sel */
                    font-size: 9px; /* Ukuran font di dalam tabel */
                    /* font-style: italic; */
                }
            </style>
            <table class="custom-row">
                <tr>
                    <td class="custom-col-6">
                        <table class="">
                            <tr>
                                <td><img class="custom-img" src="https://i.imgur.com/MHpXScU.jpeg" alt="Image"></td>
                                <td><h3>DN/INVOICE TAGIH JAPFA</h3></td>
                            </tr>
                        </table>

                    </td>
                    <td class="custom-col-6 ">
                        <table class="table_kop">
                            <tr>
                                <td><strong>Code : </strong> ' . $code . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table class="header-info" style=" max-width:40px;">
                <tr>
                    <td><strong>Operator</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $formattedDate . '</td>
                    <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $salesdntagih_Total_tagihan . '</td>
                </tr>
                    <tr>
                    <td><strong>Operator</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $data_sumary->created_by . '</td>
                    <td><strong style="margin-left: 15px;">Client</strong></td>
                    <td><strong>:</strong></td>
                    <td>PT. SANTOSA UTAMA LESATARI</td>
                </tr>
                    <tr>
                    <td><strong>Periode date</strong></td>
                    <td><strong>:</strong></td>
                    <td colspan="4">' . $data_sumary->note_kwitansi . '</td>
                </tr>
            </table>
            <table class="special-table" style="border: 0.7px solid black; border-collapse: collapse; font-size: 9px; vertical-align: middle;">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>COA id</th>
                        <th>Description</th>
                        <th>Debet</th>
                        <th>Credit</th>
                    </tr>
                </thead>
              <tbody>';
        $counter = 1;

        foreach ($data_table as $row) {
            $html .= '
                                <tr>
                                    <td style="text-align: left;">' . $counter . '</td>
                                    <td style="text-align: left;">' . $row->transcoa_coa_code . '</td>
                                    <td style="text-align: left;">' . $row->coa_desc . '</td>
                                    <td style="text-align: right;">Rp ' . number_format(round($row->transcoa_debet_value), 0, ',', '.') . '</td>
                                    <td style="text-align: right;">Rp ' . number_format(round($row->transcoa_credit_value), 0, ',', '.') . '</td>
                                </tr>';
            $counter++;
        }


        $html .= '
                            <tr>
                                <td colspan="3" style="text-align: center; font-size: 9px; font-weight: bold">Total</td>
                                <td colspan="1" style="text-align: right; font-size: 9px; font-weight: bold">' . $total_debet . '</td>
                                <td colspan="1" style="text-align: right; font-size: 9px; font-weight: bold">' . $total_kredit . '</td>
                            </tr>
                        </tbody>
                    </table>

                   <div style="width: 100%; margin-top: 40px;">
                        <div style="float: right; width: 30%;">
                            <table style="text-align: center; font-size: 9px; font-weight: bold; width: 100%;">
                                <tr>
                                    <td>Operator</td>
                                </tr>
                                <tr>
                                    <td style="height: 80px;"></td> <!-- Jarak tanda tangan -->
                                </tr>
                                <tr>
                                    <td style="text-decoration: underline;">' . $data_sumary->created_by . '</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <style>
                        table.print_info {
                            border-collapse: collapse;
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
            'format' => 'A4',
            'orientation' => 'p',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 12,
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

                left JOIN tr_acc_transaksi_kasir_branch_closing_d_tripcost_out otripcost
                    ON uangjalan.kasirbranchcash_code = otripcost.kasirbranchclosing_kasirbranchcashcode
            )
                SELECT
                    tahun,
                    bulan,
                    isnull(salesdntagih_Sales_dn_date, cast(salesdntagih_Sales_dn_date as date))tgl,
                    closing_code,
                    pabrik1,
                    isnull(dn, salesdntagih_Sales_dn_code) dn,
                    isnull(surat_jalan, salesdntagih_cocode) surat_jalan,
                    isnull(liter, salesdntagih_qty) liter,
                    isnull(hrg_ltr, salesdntagih_salesbotol) hrg_ltr,
                    salesdntagih_Tagih_value total_price_value,
                    pabrik2,
                    kode,
                    nomor,
                    salesdntagih_vhcode vehicle,
                    isnull(driver,salesdntagih_drivercode) driver,
                    isnull(rit, 1) rit,
                    no_doc,
                    no_po,
                    spk_2 spk,
                    d.*
                FROM
                    tbl
                    LEFT JOIN ( SELECT Sales_DN_spkno AS spk_2, d1.* FROM tr_tagih_sales_DN_d d1 JOIN tr_acc_transaksi_sales_DN_d d2 ON d2.Sales_DN_Code_d = d1.salesdntagih_Sales_dn_code ) d ON tbl.spk = d.spk_2 and salesdntagih_cocode = surat_jalan
                WHERE
                    salesdntagih_code_h = '$code'
                    AND ( trash_data IS NULL OR trash_data != 1 )
                ORDER BY
                    no_urut;
        ";
        // dd($query2, $query);
        $data = DB::connection('ms_sql_hgs')->select($query);
        $data2 = DB::connection('ms_sql_hgs')->select($query2);
        $data_sumary = $data[0];
        $data_sumary2 = $data2[0];
        // dd($data_sumary2);
        $branch = $data_sumary->rec_comcode;
        $operator = $data_sumary->rec_usercreated;
        $date = $data_sumary->rec_datecreated;
        $code = $data_sumary->salesdntagih_code_h;

        $total_inv = count($data2);
        $rotal_rit = count($data2);
        $total_tagihan_sales = 0;
        $total_tagihan_sales_header = 0;
        $total_qty = 0;
        foreach ($data2 as $row) {
            $total_tagihan_sales += $row->total_price_value;
            $total_tagihan_sales_header += $row->total_price_value;
            $total_qty += $row->salesdntagih_qty;
        }
        $total_tagihan_sales = 'Rp ' . number_format($total_tagihan_sales, 3, ',', '.');
        // dd('');
        $total_tagihan_sales_header = 'Rp ' . number_format($total_tagihan_sales_header, 0, ',', '.');
        $total_qty = number_format($total_qty, 0, ',', '.');
        // dd($total_qty);

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
                                <td><strong>Code : </strong> ' . $code . '</td>
                                <td><strong>Date :  </strong>' . $date . ' </td>
                            </tr>
                            <tr>

                                <td><strong>Operator : </strong>' . $operator . '</td>
                                <td><strong>Branch :</strong>    ' . $branch . '</td>
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
                    <td colspan="4">' . $data_sumary->salesdntagih_start_date . ' - ' . $data_sumary->salesdntagih_end_date . '</td>
                </tr>
                <tr>
                    <td><strong>Operator</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $operator . '</td>
                    <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $total_tagihan_sales_header . '</td>
                     <td><strong style="margin-left: 15px;">No. Po</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $data_sumary->salesdntagih_no_po . '</td>
                </tr>
                <tr>
                    <td><strong>Client</strong></td>
                    <td><strong>:</strong></td>
                    <td style=" max-width: fit-content;">' . $data_sumary->salesdntagih_client_code . '</td>
                    <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $total_inv . '</td>
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
                            <td style="text-align: right;">Rp ' . number_format($row->total_price_value, 3, ',', '.') . '</td>
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
                        <td style="text-align: center; font-size: 9px;">' . $rotal_rit . '</td>
                        <td colspan="5" style="text-align: right; font-size: 9px;">' . $total_tagihan_sales . '</td>
                        <td colspan="2" style="text-align: right; font-size: 9px;">' . $total_qty . '</td>
                        <td colspan="5" style="text-align: center; font-size: 9px;"></td>
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
                            <td style="text-decoration: underline;">' . $operator . '</td>
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
                                <td><strong>Code : </strong> ' . $code . '</td>
                                <td><strong>Date :  </strong>' . $date . ' </td>
                            </tr>
                            <tr>

                                <td><strong>Operator : </strong>' . $operator . '</td>
                                <td><strong>Branch :</strong>    ' . $branch . '</td>
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
                    <td colspan="4">' . $data_sumary->salesdntagih_start_date . ' - ' . $data_sumary->salesdntagih_end_date . '</td>
                </tr>
                <tr>
                    <td><strong>Operator</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $operator . '</td>
                    <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $total_tagihan_sales . '</td>
                </tr>
                <tr>
                    <td><strong>Client</strong></td>
                    <td><strong>:</strong></td>
                    <td style=" max-width: fit-content;">' . $data_sumary->salesdntagih_client_code . '</td>
                    <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $total_inv . '</td>
                </tr>
                <tr>
                    <td><strong>Bisnis</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $data_sumary->salesdntagih_bisniscode . '</td>
                    <td><strong style="margin-left: 15px;">Total Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $total_tagihan_sales . '</td>
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
                        <td colspan="3" style="text-align: right; font-size: 9px;">' . $total_tagihan_sales . '</td>
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
                            <td style="text-decoration: underline;">' . $operator . '</td>
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
                                <td><strong>Code : </strong> ' . $code . '</td>
                                <td><strong>Date :  </strong>' . $date . ' </td>
                            </tr>
                            <tr>

                                <td><strong>Operator : </strong>' . $operator . '</td>
                                <td><strong>Branch :</strong>    ' . $branch . '</td>
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
                    <td colspan="4">' . $data_sumary->salesdntagih_start_date . ' - ' . $data_sumary->salesdntagih_end_date . '</td>
                </tr>
                <tr>
                    <td><strong>Operator</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $operator . '</td>
                    <td><strong style="margin-left: 15px;">Total Tagihan Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $total_tagihan_sales . '</td>
                </tr>
                <tr>
                    <td><strong>Client</strong></td>
                    <td><strong>:</strong></td>
                    <td style=" max-width: fit-content;">' . $data_sumary->salesdntagih_client_code . '</td>
                    <td><strong style="margin-left: 15px;">Total Invoice</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $total_inv . '</td>
                </tr>
                <tr>
                    <td><strong>Bisnis</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $data_sumary->salesdntagih_bisniscode . '</td>
                    <td><strong style="margin-left: 15px;">Total Sales</strong></td>
                    <td><strong>:</strong></td>
                    <td>' . $total_tagihan_sales . '</td>
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
                            <td style="text-decoration: underline;">' . $operator . '</td>
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
        try {
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
            $user = auth()->user()?->username ?? 'system';
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
            $new_Code_Header_kwitansi = sprintf('KW/HGS/%s%s%s/%s', $day, $month, $year_small, $new_number_kwitansi);
            // dd($new_Code_Header);

            if ($exp_client == 'TUA') {
                $data_kwitansi = DB::connection('ms_sql_hgs')->select("SELECT count(1) tot FROM [dbo].[tr_tagih_sales_DN_h] WHERE salesdntagih_client_code = 'TUA' and year(rec_datecreated) = year(GETDATE())");
                $kwitansi_last_number = $data_kwitansi[0]->tot;
                if ($cabang_code == '0001') {
                    $cabang_kwitansi_code = 2;
                } elseif ($cabang_code == '0002') {
                    $cabang_kwitansi_code = 1;
                } elseif ($cabang_code == '0003') {
                    $cabang_kwitansi_code = 3;
                }
                $Code_kwitansi_new_by_adit = sprintf('%s.%s/KW/HGS/%s/%s', $kwitansi_last_number, $cabang_kwitansi_code, $romanMonth, $year);
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
                    'trash_data' => 0,
                ];
            }
            $headerDataArray[] = [
                'rec_usercreated' => $user,
                'rec_userupdate' => $user,
                'rec_datecreated' => $jakartaTime,
                'rec_dateupdate' => $jakartaTime,
                'rec_status' => 1,
                'rec_comcode' => $header_data['rec_comcode'],
                'rec_areacode' => $header_data['rec_areacode'],
                'salesdntagih_code_h' => $new_Code_Header,
                'salesdntagih_dateregist_tagihan' => $jakartaDate,
                'salesdntagih_Total_tagihan' => $total_sales,
                'salesdntagih_Total_sales' => $total_sales,
                'salesdntagih_client_code' => $header_data['client_code'],
                'salesdntagih_start_date' => $start_date,
                'salesdntagih_end_date' => $end_date,
                'salesdntagih_operator' => $user,
                'salesdntagih_code_kwitansi' => $new_Code_Header_kwitansi,
                'salesdntagih_bisniscode' => "",
                'salesdntagih_branchcode' => $header_data['rec_areacode'],
                'salesdntagih_code_kwitansi_new' => $exp_client !== 'TUA' ? null : ($Code_kwitansi_new_by_adit),
                'salesdntagih_code_cabang' => $header_data['cab_code'],
            ];

            $pph4DataArray[] = [
                'no_kwitansi' => $new_Code_Header,
                'value_tagihan_dn' => $total_sales,
                'value_est_pph_4' => $total_sales / 10,
                'created_at' => $jakartaTime,
                'created_by' => $user,
                'value_ppn' => $total_sales / 11,
                'value_pembebasan_ppn' => $total_sales / 11 * -1,
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
        try {
            $code_header =  $request->input('code_header');
            $code_po =  $request->input('code_po');
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
            return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $code_header]);
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
            $is_from_ba = $request->input('is_from_ba');
            if (!empty($tampunganData)) {
                $head_data = $tampunganData[0];
                $header_code = $head_data['salesdntagih_code_h'];
                $dn_code = array_column($tampunganData, 'salesdntagih_Sales_dn_code');
                $co_code = array_column($tampunganData, 'salesdntagih_cocode');
                $dn_for_history_1 = array_map(function ($item) {
                    return [
                        'dn_code' => $item['salesdntagih_Sales_dn_code'],
                        'transaksi' => 'Delete'
                    ];
                }, $tampunganData);
                $totalTagihan_minus = array_sum(array_column($tampunganData, 'salesdntagih_Tagih_value'));
            }
            // dd($tampunganData_add);
            if (!empty($tampunganData_add)) {
                // Hilangkan duplikat berdasarkan Sales_DN_Code_d
                $seen = [];
                $tampunganData_add = array_filter($tampunganData_add, function ($item) use (&$seen) {
                    if (!isset($item['Sales_DN_Code_d'])) return false;

                    $key = $item['Sales_DN_Code_d'];
                    if (isset($seen[$key])) {
                        return false; // Sudah pernah, skip
                    }
                    $seen[$key] = true;
                    return true;
                });

                // Hitung total
                $totalTagihan_plus = array_sum(array_column($tampunganData_add, 'Sales_DN_Sales_value'));

                // Pisahkan trash dan not-trash
                $tampunganData_add_trash_1 = array_values(array_filter($tampunganData_add, function ($detail) {
                    return isset($detail['trash_data']) && $detail['trash_data'] == 1;
                }));

                $tampunganData_add_not_trash_1 = array_values(array_filter($tampunganData_add, function ($detail) {
                    return !isset($detail['trash_data']) || $detail['trash_data'] != 1;
                }));

                // Siapkan untuk history
                $dn_code_2 = array_column($tampunganData_add_trash_1, 'Sales_DN_Code_d');
                $dn_for_history_2 = array_map(function ($item) {
                    return [
                        'dn_code' => $item['Sales_DN_Code_d'],
                        'transaksi' => 'Insert'
                    ];
                }, $tampunganData_add);
            } else {
            }

            $gabungan_dn_history = array_merge($dn_for_history_1, $dn_for_history_2);
            // dd($gabungan_dn_history);
            $total_tagihan_baru = $total_tagihan - $totalTagihan_minus + $totalTagihan_plus;
            // main transaction
            DB::beginTransaction();

            // if (!empty($tampunganData)) {
            //     DB::connection('ms_sql_hgs')
            //     ->table('tr_tagih_sales_DN_d')
            //     ->where('salesdntagih_code_h', $header_code)
            //     ->whereIn('salesdntagih_Sales_dn_code', $dn_code)
            //     ->update([
            //         'trash_data' => 1
            //     ]);
            // }

            if (!empty($tampunganData)) {
                foreach ($tampunganData as $data) {
                    DB::connection('ms_sql_hgs')
                        ->table('tr_tagih_sales_DN_d')
                        ->where('salesdntagih_Sales_dn_code', $data['salesdntagih_Sales_dn_code'])
                        ->where('salesdntagih_cocode', $data['salesdntagih_cocode'])
                        ->update([
                            'trash_data' => 1
                        ]);
                }
            }

            $last_number = DB::connection('ms_sql_hgs')
                ->table('tr_tagih_sales_DN_d')
                ->where('salesdntagih_code_h', $header_code)
                ->orderBy('no_urut', 'desc')
                ->select('no_urut')
                ->first();
            $last_number = $last_number->no_urut;

            // dd('');
            $last_number = (int)$last_number;

            if (!empty($tampunganData_add_trash_1)) {
                // dd($tampunganData_add_trash_1);
                foreach ($tampunganData_add_trash_1 as $data) {
                    $last_number++;
                    // dd($data['Sales_DN_Code_d'], $data['Sales_DN_COno']);
                    DB::connection('ms_sql_hgs')
                        ->table('tr_tagih_sales_DN_d')
                        ->where('salesdntagih_Sales_dn_code', $data['Sales_DN_Code_d'])
                        ->where('salesdntagih_cocode', $data['Sales_DN_COno'])
                        ->update([
                            'trash_data' => 0,
                            'salesdntagih_code_h' => $header_code,
                            'no_urut' => $last_number,
                            'salesdntagih_code_d' => $last_number,
                        ]);
                }
            }
            // dd('');
            $last_number = DB::connection('ms_sql_hgs')
                ->table('tr_tagih_sales_DN_d')
                ->where('salesdntagih_code_h', $header_code)
                ->orderBy('no_urut', 'desc')
                ->select('no_urut')
                ->first();
            $last_number = $last_number->no_urut;
            $last_number = (int)$last_number;
            // dd($last_number);
            $detailDataArray = [];
            if (!empty($tampunganData_add_not_trash_1)) {
                // dd('');
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
                // dd($detailDataArray);
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
            $last_number_history = $last_number_history + 1;
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

            return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $dn_code]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
    public function store_kwitansi(Request $request)
    {
        try {
            // dd('');
            $header_code =  $request->input('header_code');
            $tgl_kwitansi =  $request->input('tgl_kwitansi');
            $note_kwitansi =  $request->input('note_kwitansi');
            $data = DB::connection('ms_sql_hgs')->select("
                SELECT
                    h.*,
                    clien_desc,
                    iif ( p.no_kwitansi IS NULL, 0, 1 ) no_kwitansi , total
                FROM
                    tr_tagih_sales_DN_h h
                    JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id
                    LEFT JOIN tr_tagih_sales_DN_pph4 p ON p.no_kwitansi = h.salesdntagih_code_h
                    LEFT JOIN (
                    SELECT
                    SUM( salesdntagih_Tagih_value ) total,
                    salesdntagih_code_h kode
                FROM
                    tr_tagih_sales_DN_d
                WHERE
                    salesdntagih_code_h = '$header_code'
                GROUP BY
                salesdntagih_code_h
                    ) n on n.kode = h.salesdntagih_code_h
                WHERE
                    salesdntagih_code_h = '$header_code';

            ");
            $area_code = $data[0]->salesdntagih_code_cabang;
            $coa_data = DB::connection('ms_sql_hgs')
                ->table('tr_acc_transaksi_coa')
                ->select('transcoa_code')
                ->where('transcoa_code', 'like', '%TrCoaOps%')
                ->where('rec_areacode', $area_code)
                ->whereMonth('rec_dateupdate', Carbon::now('Asia/Jakarta')->month)
                ->whereYear('rec_dateupdate', Carbon::now('Asia/Jakarta')->year)
                ->orderBy('transcoa_code', 'desc')
                ->first();

            $last_number = 0;

            if ($coa_data) {
                $parts = explode('-', $coa_data->transcoa_code);
                $last_number = (int) end($parts);
            }
            $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '0001';
            $now = Carbon::now('Asia/Jakarta');
            $tahun = $now->year;
            $bulan = str_pad($now->month, 4, '0', STR_PAD_LEFT);
            $kode_tahun_bulan = $tahun . $bulan;
            $new_Code_COA = sprintf('%s-TrCoaOps-%s-%s', $area_code, $kode_tahun_bulan, $new_number);
            // dd($new_Code_COA);

            DB::beginTransaction();
            DB::connection('ms_sql_hgs')
                ->table('tr_tagih_sales_DN_pph4')
                ->insert([
                    'no_kwitansi' => $data[0]->salesdntagih_code_h,
                    'value_tagihan_dn' => $data[0]->total,
                    'value_est_pph_4' => $data[0]->total / 50,
                    'created_by' => auth()->user()->username,
                    'created_at2' => now('Asia/Jakarta'),
                    'created_at' => $tgl_kwitansi,
                    'value_ppn' => $data[0]->total / 11,
                    'value_pembebasan_ppn' => $data[0]->total / 11 * -1,
                    'note_kwitansi' => $note_kwitansi,
                ]);
            DB::commit();
            return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $data[0]]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
    public function store_kwitansi_japfa(Request $request)
    {
        try {
            // dd($request->all());
            $header_code =  $request->input('header_code');
            $tgl_kwitansi =  $request->input('tgl_kwitansi');
            $note_kwitansi =  $request->input('note_kwitansi');
            $total_tagihan =  $request->input('total_tagihan');
            $tahun = $request->input('tahun');
            $bulan = $request->input('bulan');
            $lokasi = $request->input('lokasi');
            $total_payment = $request->input('total_payment_text');
            $payment_date = $request->input('payment_date');

            $debet_code_1 = $request->input('debet_code_1');
            $debet_code_2 = $request->input('debet_code_2');
            $debet_code_3 = $request->input('debet_code_3');
            $debet_code_4 = $request->input('debet_code_4');
            $debet_code_5 = $request->input('debet_code_5');
            $debet_code_6 = $request->input('debet_code_6');

            $kredit_code_1 = $request->input('kredit_code_1');
            $kredit_code_2 = $request->input('kredit_code_2');
            $kredit_code_3 = $request->input('kredit_code_3');
            $kredit_code_4 = $request->input('kredit_code_4');
            $kredit_code_5 = $request->input('kredit_code_5');
            $kredit_code_6 = $request->input('kredit_code_6');

            $debet_val_1 = $request->input('debet_val_1');
            $debet_val_2 = $request->input('debet_val_2');
            $debet_val_3 = $request->input('debet_val_3');
            $debet_val_4 = $request->input('debet_val_4');
            $debet_val_5 = $request->input('debet_val_5');
            $debet_val_6 = $request->input('debet_val_6');

            $kredit_val_1 = $request->input('kredit_val_1');
            $kredit_val_2 = $request->input('kredit_val_2');
            $kredit_val_3 = $request->input('kredit_val_3');
            $kredit_val_4 = $request->input('kredit_val_4');
            $kredit_val_5 = $request->input('kredit_val_5');
            $kredit_val_6 = $request->input('kredit_val_6');

            $area_code = $request->input('area_code');
            $coa_main = $request->input('coa_main');
            $Date_formated = Carbon::now('Asia/Jakarta')->format('d-M-Y');
            $user = auth()->user()->username;
            $paytipe = $request->input('paytipe');
            if ($lokasi == 'JKT') {
                $lokasicode = 'CPG';
            } else {
                $lokasicode = $lokasi;
            }
            $last_number = DB::connection('ms_sql_hgs')
                ->table('tr_tagih_sales_DN_kwitansi_japfa')
                ->where('lokasi', $lokasicode)
                ->whereMonth('created_at', intval($bulan))
                ->whereYear('created_at', intval($tahun))
                ->count();
            $jakartaTime = Carbon::now('Asia/Jakarta');
            $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '0001';
            $now = Carbon::now('Asia/Jakarta');
            $tahun2 = $now->year;
            $bulan3 = str_pad($now->month, 4, '0', STR_PAD_LEFT);
            $kode_tahun_bulan = $tahun2 . $bulan3;
            $bulan2 = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $kode_tahun_bulan2 = $tahun2 . $bulan2;
            $new_header_code = sprintf('HGS-JAPFA-%s-%s/%s/%s', $lokasicode, $tahun, $bulan, $new_number);
            $new_Code_co_desc = sprintf('Tagihan JAPFA: %s', $new_header_code);

            $coa_data = DB::connection('ms_sql_hgs')
                ->table('tr_acc_transaksi_coa')
                ->select('transcoa_code')
                ->where('transcoa_code', 'like', '%TrCoa%')
                ->whereMonth('rec_dateupdate', Carbon::now('Asia/Jakarta')->month)
                ->whereYear('rec_dateupdate', Carbon::now('Asia/Jakarta')->year)
                ->orderBy('rec_datecreated', 'desc')
                ->orderBy('transcoa_code', 'desc')
                ->first();
            // dd($coa_data);
            $last_number = 0;
            if ($coa_data) {
                $parts = explode('-', $coa_data->transcoa_code);
                $last_number = (int) end($parts);
            }
            $tmain_data = DB::connection('ms_sql_hgs')
                ->table('tr_acc_transaksi_main')
                ->select('transmain_code')
                ->where('transmain_code', 'like', '%TMC%')
                ->whereMonth('rec_dateupdate', Carbon::now('Asia/Jakarta')->month)
                ->whereYear('rec_dateupdate', Carbon::now('Asia/Jakarta')->year)
                ->orderBy('transmain_code', 'desc')
                ->first();
            $last_number_tmain = 0;
            if ($tmain_data) {
                $parts = explode('-', $tmain_data->transmain_code);
                $last_number_tmain = (int) end($parts);
            }
            $new_number_tmain = $last_number_tmain ? str_pad((int)$last_number_tmain + 1, 6, '0', STR_PAD_LEFT) : '000001';
            $new_Code_tmain = sprintf('TMC-%s-%s', $kode_tahun_bulan2, $new_number_tmain);
            // dd($new_Code_tmain);
            // dd($new_Code_COA);

            DB::beginTransaction();
            for ($i = 1; $i <= 6; $i++) {
                $debet_val = ${"debet_val_$i"};
                $debet_code = ${"debet_code_$i"};
                $kredit_val = ${"kredit_val_$i"};
                $kredit_code = ${"kredit_code_$i"};

                // Proses DEBET
                if (!in_array($debet_val, ['NONE', '', '0', 0], true) && !in_array($debet_code, ['NONE', '', '0', 0], true)) {
                    $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                    $new_Code_COA = sprintf('TrCoa-%s-%s', $kode_tahun_bulan, $new_number);

                    DB::connection('ms_sql_hgs')
                        ->table('tr_acc_transaksi_coa')
                        ->insert([
                            'rec_usercreated' => $user,
                            'rec_userupdate' => $user,
                            'rec_datecreated' => $jakartaTime,
                            'rec_dateupdate' => $jakartaTime,
                            'rec_status' => 1,
                            'rec_comcode' => 'HGS PUSAT',
                            'rec_areacode' => '0000',
                            'transcoa_code' => $new_Code_COA,
                            'transcoa_transaksi_main_code' => $coa_main,
                            'transcoa_coa_desc' => $new_Code_co_desc,
                            'transcoa_head_code' => $new_header_code,
                            'transcoa_debet_value' => $debet_val,
                            'transcoa_credit_value' => 0,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $debet_code,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);

                    $last_number++;
                }

                // Proses KREDIT
                if (!in_array($kredit_val, ['NONE', '', '0', 0], true) && !in_array($kredit_code, ['NONE', '', '0', 0], true)) {
                    $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                    $new_Code_COA = sprintf('TrCoa-%s-%s', $kode_tahun_bulan, $new_number);

                    DB::connection('ms_sql_hgs')
                        ->table('tr_acc_transaksi_coa')
                        ->insert([
                            'rec_usercreated' => $user,
                            'rec_userupdate' => $user,
                            'rec_datecreated' => $jakartaTime,
                            'rec_dateupdate' => $jakartaTime,
                            'rec_status' => 1,
                            'rec_comcode' => 'HGS PUSAT',
                            'rec_areacode' => '0000',
                            'transcoa_code' => $new_Code_COA,
                            'transcoa_transaksi_main_code' => $coa_main,
                            'transcoa_coa_desc' => $new_Code_co_desc,
                            'transcoa_head_code' => $new_header_code,
                            'transcoa_debet_value' => 0,
                            'transcoa_credit_value' => $kredit_val,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $kredit_code,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);

                    $last_number++;
                }
            }

            // trans main
            DB::connection('ms_sql_hgs')
                ->table('tr_acc_transaksi_main')
                ->insert([
                    'rec_usercreated' => $user,
                    'rec_userupdate' => $user,
                    'rec_datecreated' => $jakartaTime,
                    'rec_dateupdate' => $jakartaTime,
                    'rec_status' => 1,
                    'rec_comcode' => 'HGS PUSAT',
                    'rec_areacode' => '0000',
                    'transmain_code' => $new_Code_tmain,
                    'transmain_codetransaksi' => $new_header_code,
                    'transmain_desc' => $new_Code_co_desc,
                    'transmain_ms_transcode' => $coa_main,
                    'transmain_value' => $total_payment,
                    'transmain_date' => $jakartaTime,
                    'transmain_document_note' => $new_Code_co_desc,
                    'transmain_operator' => $user,
                    'transmain_document_date' => $jakartaTime,
                    'transmain_document_time' => $jakartaTime
                ]);
            // kwitansi
            DB::connection('ms_sql_hgs')
                ->table('tr_tagih_sales_DN_kwitansi_japfa')
                ->insert([
                    'no_kwitansi' => $new_header_code,
                    'value_tagihan_dn' => $total_tagihan,
                    'value_est_pph_4' => $total_tagihan / 50,
                    'created_by' => auth()->user()->username,
                    'created_at2' => now('Asia/Jakarta'),
                    'created_at' => $tgl_kwitansi,
                    'value_ppn' => $total_tagihan / 11,
                    'value_pembebasan_ppn' => $total_tagihan / 11 * -1,
                    'note_kwitansi' => $note_kwitansi,
                    'bulan_inv' => $bulan,
                    'tahun_inv' => $tahun,
                    'lokasi' => $lokasicode,
                ]);
            DB::commit();
            return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $new_header_code]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
    public function get_detail_japfa(Request $request)
    {
        $tahun = $request->get('tahun');
        $lokasi = $request->get('lokasi');
        $bulan = $request->get('bulan');
        $query = "
            WITH cte AS (
                SELECT
                    DATEPART(YEAR, himp.invoice_date) AS tahun,
                    DATEPART(MONTH, himp.invoice_date) AS bulan,
                    DATEPART(YEAR, SO_Date) AS tahun_so,
                    DATEPART(MONTH, SO_Date) AS bulan_so,
                    SO_Date,
                    himp.invoice_number,
                    number invoice_number2,
                    -- himp.retailer_code,
                    isnull(partner_name, himp.retailer_name) retailer_name,
                    dimp.distributor_stock_keeping_unit AS [Id product],
                    sku_description AS [Product],
                    dimp.unit AS Unit,
                    dimp.eaches_quantity AS qty,
                    dimp.unit_price AS [price],
                    dimp.net_value AS [Net/value],
                    CONVERT(DATE, himp.invoice_date) AS Invoice_date,
                    CONVERT(DATE, dpch.rec_datecreated) AS [Send date],
                    dpch.dpcth_code_h,
                    dpchd.Dptch_qty_terima * CONVERT(INT, SUBSTRING(SKU_convertpcs, 0, CHARINDEX(' ', SKU_convertpcs))) AS [Qty Terima],
                    dpchd.Dptch_qty_terima * CONVERT(INT, SUBSTRING(SKU_convertpcs, 0, CHARINDEX(' ', SKU_convertpcs))) * 422 AS [Value total KG],
                    SUBSTRING(himp.invoice_number, 1, 3) AS wilayah,
                    iif(SUBSTRING(himp.invoice_number, 1, 3) = 'JKT', 'Cipinang', 'Tanggerang') AS cabang,
                    CONCAT(dpch.dpcth_code_h, '-', dpcth_so) AS dpc,
                    himp.invimp_code,
                    SKU_description
                FROM
                    tgu_tr_invoice_h_import himp
                    LEFT JOIN tgu_tr_invoice_d_import dimp
                        ON himp.invoice_number = dimp.invoice_number
                    LEFT JOIN tgu_ms_product_Business mspro
                        ON dimp.distributor_stock_keeping_unit = mspro.sku_business
                        AND mspro.business = 'japfa'
                    LEFT JOIN TGU_dispatch_h dpch
                        ON himp.invoice_number = dpch.dpcth_so
                    LEFT JOIN TGU_dispatch_d dpchd
                        ON dpch.dpcth_code_h = dpchd.dptch_code_h
                        AND dpch.Dpcth_SO = dpchd.Dptch_SO
                        AND dimp.distributor_stock_keeping_unit = dpchd.Dptch_Product
                    JOIN tr_so_main so on so.invoice_number = himp.invoice_number
                    LEFT JOIN (select distinct sale_order, partner_name, number from tgu_tr_invoice_import_for_japfa) imp on imp.sale_order = himp.invoice_number
                WHERE
                    himp.client = 'Japfa'
                    AND DATEPART(YEAR, himp.invoice_date) = '$tahun'
                    AND DATEPART(month, himp.invoice_date) = '$bulan'
            )
            SELECT
                invoice_number2 invimp_code,
                invoice_number,
                retailer_name,
                Invoice_date,
                price,
                qty,
                price * qty total_price,
                SKU_description,
                SO_Date
            FROM
                cte
            WHERE
                wilayah = '$lokasi'
            ORDER BY
                invoice_date DESC,
                invoice_number DESC;
        ";
        // dd($query);
        $data = DB::connection('ms_sql_hgs')->select($query);
        return response()->json($data);
    }
    public function importjapfa(Request $request)
    {
        try {
            $data = $request->input('data');
            $user = auth()->user()?->username ?? 'system';
            $jakartaTime = Carbon::now('Asia/Jakarta');
            $hasil = [];
            foreach ($data as $row) {
                $hasil[] = [
                    'number' => $row['Number'],
                    'partner_name' => $row['Invoice Partner Display Name'],
                    'invoice_date' => is_numeric($row['Invoice/Bill Date'])
                        ? \Carbon\Carbon::createFromDate(1900, 1, 1)->addDays($row['Invoice/Bill Date'] - 2)->format('Y-m-d')
                        : $row['Invoice/Bill Date'],
                    'sale_order' => $row['Sale Order'],
                    'total_signed' => $row['Total Signed'],
                    'status' => $row['Status'],
                    'payment_status' => $row['Payment Status'],
                    'salesman' => $row['Salesman Toko'],
                    'product_name' => $row['Invoice lines/Product'],
                    'quantity' => $row['Invoice lines/Quantity'],
                    'created_at' => $jakartaTime,
                    'created_by' => $user
                ];
            }
            DB::beginTransaction();
            $chunks = array_chunk($hasil, 50);

            foreach ($chunks as $chunk) {
                DB::connection('ms_sql_hgs')->table('tgu_tr_invoice_import_for_japfa')->insert($chunk);
            }
            return response()->json([
                'success' => true,
                'total' => count($hasil),
                'preview' => $hasil,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
}
