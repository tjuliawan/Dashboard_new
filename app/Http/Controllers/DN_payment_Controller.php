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


class DN_payment_Controller extends Controller
{
    public function index_payment()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('dn_transaction.payment.index', compact('user') );
    }
    public function index_faktur_pajak()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('dn_transaction.faktur_pajak.index', compact('user') );
    }
    public function index_pemotongan_kwitansi()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('dn_transaction.pemotongan_kwitansi.index', compact('user') );
    }
    public function get_header_coa_transaksi(Request $request)
    {
        $data = DB::connection('ms_sql_hgs')->select(" 		
            SELECT
                transcoa_code,
                transcoa_desc 
            FROM
                ms_acc_transaksi 
            WHERE
                rec_status = 1 
            ORDER BY
                rec_datecreated DESC
        "); 
        return response()->json($data);
    }
    public function get_detail_coa_transaksi(Request $request)
    {
        $code = $request->input('code');
        $data = DB::connection('ms_sql_hgs')->select(" 		
            SELECT
                *,
                coa1.coa_desc AS debet1_desc,
                coa2.coa_desc AS kredit1_desc,
                coa3.coa_desc AS debet2_desc,
                coa4.coa_desc AS kredit2_desc,
                coa5.coa_desc AS debet3_desc,
                coa6.coa_desc AS kredit3_desc,
                coa7.coa_desc AS debet4_desc,
                coa8.coa_desc AS kredit4_desc,
                coa9.coa_desc AS debet5_desc,
                coa10.coa_desc AS kredit5_desc,
                coa11.coa_desc AS debet6_desc,
                coa12.coa_desc AS kredit6_desc 
            FROM
                ms_acc_transaksi trans
                LEFT JOIN ms_acc_coa coa1 ON trans.transcoa_debetcode = coa1.coa_code
                LEFT JOIN ms_acc_coa coa2 ON trans.transcoa_kreditcode = coa2.coa_code
                LEFT JOIN ms_acc_coa coa3 ON trans.transcoa_debet2code = coa3.coa_code
                LEFT JOIN ms_acc_coa coa4 ON trans.transcoa_kredit2code = coa4.coa_code
                LEFT JOIN ms_acc_coa coa5 ON trans.transcoa_debet3code = coa5.coa_code
                LEFT JOIN ms_acc_coa coa6 ON trans.transcoa_kredit3code = coa6.coa_code
                LEFT JOIN ms_acc_coa coa7 ON trans.transcoa_debet4code = coa7.coa_code
                LEFT JOIN ms_acc_coa coa8 ON trans.transcoa_kredit4code = coa8.coa_code
                LEFT JOIN ms_acc_coa coa9 ON trans.transcoa_debet5code = coa9.coa_code
                LEFT JOIN ms_acc_coa coa10 ON trans.transcoa_kredit5code = coa10.coa_code
                LEFT JOIN ms_acc_coa coa11 ON trans.transcoa_debet6code = coa11.coa_code
                LEFT JOIN ms_acc_coa coa12 ON trans.transcoa_kredit6code = coa12.coa_code 
            WHERE
                transcoa_code = '$code'
            ORDER BY
                trans.rec_datecreated DESC
        "); 
        return response()->json($data[0]);
    }
    public function get_data_dn_payment(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $client_code = $request->input('client_code');
        $cabang_code = $request->input('cabang_code');

        $startDate_condition = "";
        $endDate_condition = "";
        $client_condition = "";
        $cabang_condition = "";

        if ($startDate == "" && $endDate == "" ) {
            $startDate_condition = " AND CONVERT(date, salesdntagih_dateregist_tagihan) >= CONVERT(date, GETDATE())";
        }
        if ($startDate != "" || $startDate != null) {
            $startDate_condition = " AND CONVERT(date, salesdntagih_dateregist_tagihan) >= CONVERT(date, '$startDate')";
        }
        if ($endDate != "" || $endDate != null) {
            $endDate_condition = " AND CONVERT(date, salesdntagih_dateregist_tagihan) <= CONVERT(date, '$endDate')";
        }
        if ($client_code != "" || $client_code != null) {
            $client_condition = " AND salesdntagih_client_code = '$client_code'";
        }
        if ($cabang_code != "" || $cabang_code != null) {
            $cabang_condition = " AND salesdntagih_code_cabang = '$cabang_code'";
        }
        // dd($endDate_condition);

        $query =" 		
            SELECT
                h.*,
                k.*,
                cab_desc,
                salesdnpay_tagih_code_h
            FROM
                tr_tagih_sales_DN_h h
                JOIN tr_tagih_sales_DN_pph4 k ON h.salesdntagih_code_h = k.no_kwitansi
                JOIN ms_cabang c ON h.salesdntagih_code_cabang = cab_code 
                left join (SELECT distinct salesdnpay_tagih_code_h from tr_acc_transaksi_sales_DN_payment_d) tagih on salesdntagih_code_h = salesdnpay_tagih_code_h
            WHERE
                1 = 1 
                and salesdnpay_tagih_code_h is null
                $startDate_condition
                $endDate_condition
                $client_condition
                $cabang_condition
        "; 
        // dd($query);
        $data = DB::connection('ms_sql_hgs')->select($query);

        return response()->json($data);
    }
    public function get_cabang(Request $request)
    {
        $data = DB::connection('ms_sql_hgs')->select(" 		
            SELECT * from ms_cabang WHERE rec_status = 1
        "); 
        return response()->json($data);
    }
    public function get_list_pajak(Request $request)
    {
        $query = "
            WITH PotonganTerbaru AS (
                SELECT *,
                    ROW_NUMBER() OVER (PARTITION BY no_kwitansi ORDER BY created_at DESC) AS rn
                FROM tr_tagih_sales_DN_potogan_kwitansi
            )
            SELECT
                p.no_kwitansi, 
                salesdntagih_client_code,
                clien_id2,
                value_est_pph_4,
                value_tagihan_dn,
                ISNULL(pt.value_potongan, 0) AS value_potongan,
                IIF(salesdntagih_code_cabang = '0003' AND clien_id2 = 'PT. TIRTA UTAMA ABADI', 
                    'PT. WENANG PALM SOLUSINDO', clien_id2) AS clien_desc,
                kode_faktur_pajak,
                note_kwitansi,
                p.value_ppn,
                bukti_potong_pph_23,
                CAST(p.created_at AS DATE) AS tgl_kwitansi
            FROM tr_tagih_sales_DN_h h
            LEFT JOIN tr_tagih_sales_DN_pph4 p ON h.salesdntagih_code_h = p.no_kwitansi
            JOIN ms_client c ON h.salesdntagih_client_code = c.clien_id
            LEFT JOIN PotonganTerbaru pt ON pt.no_kwitansi = p.no_kwitansi AND pt.rn = 1
            WHERE p.no_kwitansi IS NOT NULL 
            AND p.status != 1
        ";
        $data = DB::connection('ms_sql_hgs')->select($query); 
        return response()->json($data);
    }
    public function store_payment(Request $request)
    {     
        DB::beginTransaction();
        try {
        // varibel request
            $startDate = $request -> input('startDate');
            $endDate = $request -> input('endDate');
            $total_payment = $request -> input('total_payment_text');
            $tampunganData = $request -> input('tampunganData');
            $debet_code_1 = $request -> input('debet_code_1');
            $debet_code_2 = $request -> input('debet_code_2');
            $debet_code_3 = $request -> input('debet_code_3');
            $debet_code_4 = $request -> input('debet_code_4');
            $debet_code_5 = $request -> input('debet_code_5');
            $debet_code_6 = $request -> input('debet_code_6');
            $kredit_code_1 = $request -> input('kredit_code_1');
            $kredit_code_2 = $request -> input('kredit_code_2');
            $kredit_code_3 = $request -> input('kredit_code_3');
            $kredit_code_4 = $request -> input('kredit_code_4');
            $kredit_code_5 = $request -> input('kredit_code_5');
            $kredit_code_6 = $request -> input('kredit_code_6');
            $debet_val_1 = $request -> input('debet_val_1');
            $debet_val_2 = $request -> input('debet_val_2');
            $debet_val_3 = $request -> input('debet_val_3');
            $debet_val_4 = $request -> input('debet_val_4');
            $debet_val_5 = $request -> input('debet_val_5');
            $debet_val_6 = $request -> input('debet_val_6');
            $kredit_val_1 = $request -> input('kredit_val_1');
            $kredit_val_2 = $request -> input('kredit_val_2');
            $kredit_val_3 = $request -> input('kredit_val_3');
            $kredit_val_4 = $request -> input('kredit_val_4');
            $kredit_val_5 = $request -> input('kredit_val_5');
            $kredit_val_6 = $request -> input('kredit_val_6');            
            $area_code = $request -> input('area_code'); 
            $coa_main = $request -> input('coa_main'); 
            $paytipe = $request -> input('paytipe'); 
            $payment_date = $request -> input('payment_date'); 
            $client_code = $tampunganData[0]['salesdntagih_client_code'];
            $client_code = $tampunganData[0]['salesdntagih_client_code'];
            $detail_client = DB::connection('ms_sql_hgs')
                        ->table('ms_client')
                        ->select('clien_desc')
                        ->where('clien_id', $client_code)
                        ->first();
            $detail_client = $detail_client ->clien_desc;
            // dd($detail_client);
            $startDate_formated = Carbon::parse($startDate)->format('d-M-Y');
            $endDate_formated = Carbon::parse($endDate)->format('d-M-Y');
            $salesdnpay_salesdntagihcode = sprintf('%s/ %s - %s', $client_code, $startDate_formated, $endDate_formated);
            // dd($salesdnpay_salesdntagihcode);
            if($area_code == '0001'){
                $comcode = 'HGS';
            }elseif($area_code == '0002'){
                $comcode = 'HGS-Ciherang';
            }elseif($area_code == '0003'){
                $comcode = 'HGS-SUBANG';
            }
            $jakartaTime = Carbon::now('Asia/Jakarta');
            $user = auth()->user()->username;
            $now = Carbon::now('Asia/Jakarta');
            $Date_formated = Carbon::now('Asia/Jakarta')->format('d-M-Y');
            $tahun = $now->year;
            $bulan = str_pad($now->month, 4, '0', STR_PAD_LEFT);
            $bulan2 = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $kode_tahun_bulan = $tahun . $bulan;
            $kode_tahun_bulan2 = $tahun . $bulan2;
            $payment_data = DB::connection('ms_sql_hgs')
                        ->table('tr_acc_transaksi_sales_DN_payment_h')
                        ->select('salesdnpay_code_h')
                        ->whereMonth('rec_dateupdate', Carbon::now('Asia/Jakarta')->month)
                        ->whereYear('rec_dateupdate', Carbon::now('Asia/Jakarta')->year)
                        ->orderBy('rec_dateupdate', 'desc')
                        ->first();            
            $last_number_payment = 0;
            if ($payment_data) {
                $parts = explode('-', $payment_data->salesdnpay_code_h);
                $last_number_payment = (int) end($parts);
            }
            $new_number_payment = $last_number_payment ? str_pad((int)$last_number_payment + 1, 4, '0', STR_PAD_LEFT) : '0001';
            $new_Code_payment = sprintf('SDP-%s%s-%s', $tahun, $bulan2, $new_number_payment);
            // dd($new_Code_payment);
        // load detail data untuk tabel payment D
            $header_tagih = [];
            $header_tagih = array_column($tampunganData, 'salesdntagih_code_h');

            if (!empty($header_tagih)) {
                $header_tagih_list = "'" . implode("','", $header_tagih) . "'";
                $header_tagih_condition = " AND h.salesdntagih_code_h IN ($header_tagih_list)";
            } else {
                $header_tagih_condition = "";
            }

            $data_for_detail = DB::connection('ms_sql_hgs')->select(" 		
                SELECT
                    d.* , h.*, s.Sales_DN_helpercode, Sales_DN_spkno
                FROM
                    tr_tagih_sales_DN_h h 
                    LEFT JOIN tr_tagih_sales_DN_d d on h.salesdntagih_code_h = d.salesdntagih_code_h
                    join tr_acc_transaksi_sales_DN_d s on salesdntagih_Sales_dn_code = s.Sales_DN_Code_d
                WHERE
                    1 = 1 
                    $header_tagih_condition
                ORDER BY 
                    h.salesdntagih_code_h, no_urut
            ");
            // dd($data_for_detail);

            // $first_dn = $data_for_detail[0]->salesdntagih_Sales_dn_code;
            // $last_dn = end($data_for_detail)->salesdntagih_Sales_dn_code;

            // dd($first_dn, $last_dn);
        // logic untuk medapatkan kde co dan tranmain
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
            $new_Code_co_desc = sprintf('Sales Dn Payment Client :%s, Date :%s', $detail_client, $Date_formated);
            $new_number_tmain = $last_number_tmain ? str_pad((int)$last_number_tmain + 1, 6, '0', STR_PAD_LEFT) : '000001';
            $new_Code_tmain = sprintf('TMC-%s-%s',$kode_tahun_bulan2, $new_number_tmain);
            // dd($new_Code_tmain);
            // DN-S25051604556-5063375853-0003
            // $new_Code_tmain = sprintf('DN-TMCOps-%s-%s', $area_code, $kode_tahun_bulan, $new_number_tmain);
        // transakasi
        // insert kr tabel dn payment
            $detailDataArray = [];
            foreach ($data_for_detail as $index => $detail) {
                $detailDataArray[] = [
                    'rec_comcode' => $detail->rec_comcode,
                    'rec_areacode' => $detail->rec_areacode,
                    'salesdnpay_code_h' =>  $new_Code_payment,
                    'salesdnpay_code_d' => $index + 1,
                    'salesdnpay_tagih_code_h' => $detail->salesdntagih_code_h,
                    'salesdnpay_DN_code' => $detail->salesdntagih_code_d,
                    'salesdnpay_DN_date' => $detail->salesdntagih_Sales_dn_date,
                    'salesdnpay_cocode' => $detail->salesdntagih_cocode,
                    'salesdnpay_drvcode' => $detail->salesdntagih_drivercode,
                    'salesdnpay_helpercode' => $detail->Sales_DN_helpercode,
                    'salesdnpay_vhccode' => $detail->salesdntagih_vhcode,
                    'salesdnpay_spk' => $detail->Sales_DN_spkno,
                    'salesdnpay_Payment_value' => $detail->salesdntagih_Tagih_value,
                    'salesdnpay_payment_mode' => $paytipe,
                    'salesdnpay_status' => '01',
                    'salesdnpay_payment_tagih_value' => $detail->salesdntagih_Tagih_value,
                    'salesdnpay_rutvhcode' => $detail->salesdntagih_routevhcode

                ];
            }
            // dd($detailDataArray);
        // insert ke tabel coa dan trans main 
            try {
                // debit 1
                    if(!in_array($debet_val_1, ['NONE', '', '0', 0], true))  {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => $debet_val_1,
                            'transcoa_credit_value' => 0,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $debet_code_1,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // kredit 1
                    if(!in_array($kredit_val_1, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => 0,
                            'transcoa_credit_value' => $kredit_val_1,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $kredit_code_1,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // debit 2
                    if(!in_array($debet_val_2, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => $debet_val_2,
                            'transcoa_credit_value' => 0,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $debet_code_2,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // kredit 2
                    if(!in_array($kredit_val_2, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => 0,
                            'transcoa_credit_value' => $kredit_val_2,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $kredit_code_2,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // debit 3
                    if(!in_array($debet_val_3, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => $debet_val_3,
                            'transcoa_credit_value' => 0,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $debet_code_3,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // kredit 3
                    if(!in_array($debet_val_4, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => 0,
                            'transcoa_credit_value' => $kredit_val_3,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $kredit_code_3,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // debit 3
                    if(!in_array($debet_code_4, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => $debet_val_4,
                            'transcoa_credit_value' => 0,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $debet_code_4,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // kredit 4
                    if(!in_array($debet_val_5, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => 0,
                            'transcoa_credit_value' => $kredit_val_5,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $kredit_code_4,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // debit 5
                    if(!in_array($debet_code_5, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => $debet_val_5,
                            'transcoa_credit_value' => 0,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $debet_code_5,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // kredit 5
                    if(!in_array($kredit_val_5, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => 0,
                            'transcoa_credit_value' => $kredit_val_5,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $kredit_code_5,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // debit 6
                    if(!in_array($debet_val_6, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => $debet_val_6,
                            'transcoa_credit_value' => 0,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $debet_code_6,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                // kredit 6
                    if(!in_array($kredit_val_5, ['NONE', '', '0', 0], true)) {
                        $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                        // dd($new_number);
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
                            'transcoa_head_code' => $new_Code_payment,
                            'transcoa_debet_value' => 0,
                            'transcoa_credit_value' => $kredit_val_6,
                            'transcoa_coa_date' => $payment_date,
                            'transcoa_coa_code' => $kredit_code_6,
                            'transcoa_statusposting' => '01',
                            'transcoa_dateposting' => $jakartaTime,
                            'transcoa_statusapp' => '01'
                        ]);
                        
                        $last_number = $last_number + 1;
                    }
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Gagal simpan data utama: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Terjadi kesalahan saat menyimpan data',
                    'error' => $e->getMessage()
                ], 500);
            }
        // transaksi main 
            try {
                DB::connection('ms_sql_hgs')
                ->table('tr_acc_transaksi_sales_DN_payment_h')
                ->insert([
                    'rec_usercreated' => $user,
                    'rec_userupdate' => $user,
                    'rec_datecreated' => $jakartaTime,
                    'rec_dateupdate' => $jakartaTime,
                    'rec_status' => 1,
                    'rec_comcode' => 'HGS PUSAT',
                    'rec_areacode' => '0000',
                    'salesdnpay_code_h' => $new_Code_payment,
                    'salesdnpay_Value_payment' => $total_payment,
                    'salesdnpay_Client_code' => $client_code,
                    'salesdnpay_Date' => $payment_date,
                    'salesdnpay_Payment_model' => $paytipe,
                    'salesdnpay_MainCode' => $coa_main,
                    'salesdnpay_operator' => $user,
                    'salesdnpay_rutvhcode' => 'NONE',
                    'salesdnpay_salesdntagihcode' => $salesdnpay_salesdntagihcode,
                    'salesdnpay_valuesalesdntagih' => $total_payment
                ]);

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
                    'transmain_codetransaksi' => $new_Code_payment,
                    'transmain_desc' => $new_Code_co_desc,
                    'transmain_ms_transcode' => $coa_main,
                    'transmain_value' => $total_payment,
                    'transmain_date' => $jakartaTime,
                    'transmain_document_note' => $new_Code_co_desc,
                    'transmain_operator' => $user,
                    'transmain_document_date' => $jakartaTime,
                    'transmain_document_time' => $jakartaTime
                ]); 
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Gagal simpan data utama: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Terjadi kesalahan saat menyimpan data',
                    'error' => $e->getMessage()
                ], 500);
            }
            
        //
            // dd($new_Code_COA, $last_number, $new_Code_tmain, $last_number_tmain);
            // dd($data_for_detail);
        // jangan dihapus
            $chunks = array_chunk($detailDataArray, 50);

            foreach ($chunks as $chunk) {
                DB::connection('ms_sql_hgs')->table('tr_acc_transaksi_sales_DN_payment_d')->insert($chunk);
            }      
            DB::commit();
            return response()->json([
                'message' => 'Data berhasil dimasukkan',
                'code_payment' => $new_Code_payment
            ]);

    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
    public function cetakPDF_payment(Request $request)
    {
        $code = $request->get('code');
        $client_code = $request->get('client_code');
        $user = auth()->user()->username;
        $operator = auth()->user()->name;
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
        $formattedDate =  $tanggal . ' ' . $bulanIndo . ' ' . $tahun;

        $query = "
            SELECT
                transcoa_transaksi_main_code,
                transcoa_coa_desc,
                transcoa_debet_value,
                transcoa_credit_value,
                transcoa_head_code,
                transcoa_coa_code,
                coa_desc,
                salesdnpay_Client_code,
                salesdnpay_Value_payment
            FROM
                tr_acc_transaksi_coa c
                JOIN tr_acc_transaksi_sales_DN_payment_h p ON c.transcoa_head_code = p.salesdnpay_code_h 
                join ms_acc_coa coa on coa_code = transcoa_coa_code
            WHERE
                salesdnpay_code_h = '$code'
            ORDER BY 
                transcoa_credit_value, transcoa_debet_value desc
        ";
        $data = DB::connection('ms_sql_hgs')->select($query); 
        $client_code = $data[0]->salesdnpay_Client_code;
        $total_payment = $data[0]->salesdnpay_Value_payment;
        $det_transaction = $data[0]->transcoa_coa_desc;
        $total_debit = 0;
        $total_kredit = 0;

        foreach ($data as $row) {
            $total_debit += $row->transcoa_debet_value;
            $total_kredit += $row->transcoa_credit_value;
        }
        $total_debit = 'Rp ' . number_format($total_debit, 3, ',', '.');
        $total_kredit = 'Rp ' . number_format($total_kredit, 3, ',', '.');
        $total_payment = 'Rp ' . number_format($total_payment, 0, ',', '.');
        // dd($data);
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
                                    <td><h4>DN Payment</h4></td>
                                </tr>
                            </table>
                        </td>
                        <td class="custom-col-6 ">
                            <table class="table_kop">
                                <tr>
                                    <td><strong>Code : </strong> '.$code.'</td>
                                    <td><strong>Date : </strong> '.$formattedDate.'</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br>
                <table class="header-info" style=" max-width:40px;">
                    <tr>
                        <td><strong>Operator</strong></td>
                        <td><strong>:</strong></td>
                        <td>'.$operator .'</td>
                        <td><strong style="margin-left: 15px;">Total Payment</strong></td>
                        <td><strong>:</strong></td>
                        <td>'.$total_payment.'</td>
                    </tr>
                    <tr>
                        <td><strong>Client</strong></td>
                        <td><strong>:</strong></td>
                        <td style=" max-width: fit-content;">'.$client_code.'</td>
                    </tr>
                    <tr style="line-height: 1rem;">
                        <td><strong>Bisnis</strong></td>
                        <td><strong>:</strong></td>
                        <td colspan="6" style="line-height: 1rem;">' .$det_transaction. '</td>
                    </tr>
                </table>
                <table class="special-table" style="border: 1px solid black; border-collapse: collapse; font-size: 9px; vertical-align: middle;">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>COA Code</th>
                            <th>Desk</th>
                            <th>Debet</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                    ';

                    $counter = 1;

                    foreach ($data as $row) {
                        $html .= '
                            <tr>
                                <td style="text-align: left;">' . $counter . '</td>
                                <td style="text-align: right;">' . $row->transcoa_coa_code . '</td>
                                <td style="text-align: left;">' . $row->coa_desc . '</td>
                                <td style="text-align: right;">Rp ' . number_format($row->transcoa_debet_value, 0, ',', '.') . '</td>
                                <td style="text-align: right;">Rp ' . number_format($row->transcoa_credit_value, 0, ',', '.') . '</td>
                            </tr>';
                        $counter++;
                    }


                $html .= '
                        <tr>
                            <td colspan="3" style="text-align: center; font-size: 9px;">Total</td>
                            <td colspan="" style="text-align: right; font-size: 9px;">'.$total_debit.'</td>
                            <td colspan="" style="text-align: right; font-size: 9px;">'.$total_kredit.'</td>
                        </tr>
                    </tbody>
                </table>
        ';
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A5', 
            'orientation' => 'P', 
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
    public function store_faktur_pajak(Request $request)
    {
        try {
            $no_kwitansi =  $request -> input('no_kwitansi');
            $no_faktur_pajak =  $request -> input('no_faktur_pajak');
            $pajak_psl_23 = $request -> input('pajak_psl_23');
            $potongan = $request -> input('potongan');
            $potongan_awal = $request -> input('potongan_awal');
            $pajak_value_ppn = $request -> input('pajak_value_ppn');
            $kode_bukti_Potong = $request -> input('kode_bukti_Potong');
            $jakartaTime = Carbon::now('Asia/Jakarta');
            $dataToSave = $request -> input('dataToSave');
            $user = auth()->user()->username;
            $borongan = $request -> input('borongan');
            $groupedData = collect($dataToSave)
            ->groupBy('no_kwitansi')
            ->map(function ($items) {
                return [
                    'no_kwitansi' => $items->first()['no_kwitansi'],
                    'pajak_psl_23' => $items->sum('pajak_psl_23'),
                    'pajak_value_ppn' => $items->sum('pajak_value_ppn'),
                    'pajak_bukti_Potong' => $items->first()['pajak_bukti_Potong'], // asumsi sama
                    'no_faktur_pajak' => $items->first()['no_faktur_pajak'], // asumsi sama
                ];
            })->values()->all();
            // dd($groupedData);
            
            DB::beginTransaction(); 
            if($borongan == 0){
                if(($potongan != 0 || $potongan_awal != 0) && ($potongan != $potongan_awal)){
                    $last_no_potongan = DB::connection('ms_sql_hgs')
                        ->table('tr_tagih_sales_DN_potogan_kwitansi')
                        ->where('no_kwitansi', $no_kwitansi)
                        ->count();  
                    $last_no_potongan = $last_no_potongan ? str_pad((int)$last_no_potongan + 1, 4, '0', STR_PAD_LEFT) : '0001';
                    $no_detail_potongan = sprintf('PTG-%s-%s', $no_kwitansi, $last_no_potongan);
                    // dd($no_detail_potongan);
                    DB::connection('ms_sql_hgs')
                    ->table('tr_tagih_sales_DN_potogan_kwitansi')
                    ->insert([
                        'no_kwitansi' => $no_kwitansi,
                        'detail_code' => $no_detail_potongan,
                        'value_potongan' => $potongan,
                        'value_pph' => $pajak_psl_23,
                        'value_ppn' => $pajak_value_ppn,
                        'created_at' => $jakartaTime,
                        'created_by' => $user,
                    ]); 
                }
                // dd(''); 
                DB::connection('ms_sql_hgs')
                ->table('tr_tagih_sales_DN_pph4')
                ->where('no_kwitansi', $no_kwitansi)
                ->update([
                    'kode_faktur_pajak' => $no_faktur_pajak,
                    'value_est_pph_4' => $pajak_psl_23,
                    'value_ppn' => $pajak_value_ppn,
                    'bukti_potong_pph_23' => $kode_bukti_Potong,
                    'updated_at' => $jakartaTime,
                    'updated_by' => $user,
                ]); 
            }elseif($borongan == 1) {
                foreach ($dataToSave as $detail) {
                    $dataTerakhir = DB::connection('ms_sql_hgs')
                        ->table('tr_tagih_sales_DN_potogan_kwitansi')
                        ->where('no_kwitansi', $detail['no_kwitansi'])
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $ada_potongan = $dataTerakhir ? $dataTerakhir->value_potongan : 0;
                    $ada_potongan = (int) floatval($ada_potongan);
                    
                    if(($detail['potongan'] != 0 || $ada_potongan != 0) && ($detail['potongan'] != $ada_potongan )){
                        $last_no_potongan = DB::connection('ms_sql_hgs')
                        ->table('tr_tagih_sales_DN_potogan_kwitansi')
                        ->where('no_kwitansi', $detail['no_kwitansi'])
                        ->count();  
                        $last_no_potongan = $last_no_potongan ? str_pad((int)$last_no_potongan + 1, 4, '0', STR_PAD_LEFT) : '0001';
                        $no_detail_potongan = sprintf('PTG-%s-%s', $detail['no_kwitansi'], $last_no_potongan);
                        // dd($no_detail_potongan);
                        DB::connection('ms_sql_hgs')
                        ->table('tr_tagih_sales_DN_potogan_kwitansi')
                        ->insert([
                            'no_kwitansi' => $detail['no_kwitansi'],
                            'detail_code' => $no_detail_potongan,
                            'value_potongan' => $detail['potongan'],
                            'value_pph' => $detail['pajak_psl_23'],
                            'value_ppn' => $detail['pajak_value_ppn'],
                            'created_at' => $jakartaTime,
                            'created_by' => $user,
                        ]); 
                    }
                    // dd('');
                    DB::connection('ms_sql_hgs')->table('tr_tagih_sales_DN_pph4')
                        ->where('no_kwitansi', $detail['no_kwitansi'])
                        ->update([
                            'kode_faktur_pajak' => $detail['no_faktur_pajak'],
                            'value_est_pph_4' => $detail['pajak_psl_23'],
                            'value_ppn' => $detail['pajak_value_ppn'],
                            'bukti_potong_pph_23' => $detail['pajak_bukti_Potong'],
                            'updated_at' => $jakartaTime,
                            'updated_by' => $user,
                        ]);
                }
                $no_kwitansi = 'update banyak yaa';
            }
            DB::commit();
            return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $no_kwitansi]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
    public function store_faktur_pajak_confirm(Request $request)
    {
        try {
            $no_kwitansi =  $request -> input('no_kwitansi');
            $detail_client = 'test';
            $user = auth()->user()->username;
            $now = Carbon::now('Asia/Jakarta');
            $tahun = $now->year;
            $bulan = str_pad($now->month, 4, '0', STR_PAD_LEFT);
            $bulan2 = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $kode_tahun_bulan = $tahun . $bulan;
            $kode_tahun_bulan2 = $tahun . $bulan2;
            $Date_formated = Carbon::now('Asia/Jakarta')->format('d-M-Y');

            $headerdata = DB::connection('ms_sql_hgs')->select(" 		
                SELECT * FROM tr_tagih_sales_DN_h h join tr_tagih_sales_DN_pph4 p on h.salesdntagih_code_h = p.no_kwitansi where no_kwitansi = '$no_kwitansi'
            "); 

            $dataTerakhir = DB::connection('ms_sql_hgs')
                ->table('tr_tagih_sales_DN_potogan_kwitansi')
                ->where('no_kwitansi', $no_kwitansi)
                ->orderBy('created_at', 'desc')
                ->first();
            $ada_potongan = $dataTerakhir ? $dataTerakhir->value_potongan : 0;
            $ada_potongan = (int) floatval($ada_potongan);

            if($ada_potongan != 0){
                $get_data = DB::connection('ms_sql_hgs')->select(" 		
                    SELECT
                        salesdntagih_client_code client
                    FROM
                        tr_tagih_sales_DN_h 
                    WHERE
                        salesdntagih_code_h = '$no_kwitansi'
                "); 
                $client_code_ = $get_data[0]->client;
                if($client_code_ == 'SHN'){
                    $coa_code = '874';
                }elseif($client_code_ == 'TVIP'){
                    $coa_code = '875';
                }elseif($client_code_ == 'TIV'){
                    $coa_code = '876';
                }elseif($client_code_ == 'TUA'){
                    $coa_code = '877';
                } else {
                    return response()->json([
                        'error' => "Client code '{$client_code_}'. Data COA Tidak Ditemukan."
                    ], 422); 
                }
                // dd($coa_code);
            }
            // dd($ada_potongan);

            $headerdata = $headerdata[0];
            // dd($headerdata ->value_est_pph_4);
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
            $new_Code_co_desc = sprintf('Penerimaan bukti potong psl 23 - %s', $no_kwitansi);
            $new_number_tmain = $last_number_tmain ? str_pad((int)$last_number_tmain + 1, 6, '0', STR_PAD_LEFT) : '000001';
            $new_Code_tmain = sprintf('TMC-%s-%s',$kode_tahun_bulan2, $new_number_tmain);
            // dd($new_Code_tmain);

            DB::beginTransaction();  
                $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                // dd($new_number);
                $new_Code_COA = sprintf('TrCoa-%s-%s', $kode_tahun_bulan, $new_number);
                
                DB::connection('ms_sql_hgs')
                ->table('tr_acc_transaksi_coa')
                ->insert([
                    'rec_usercreated' => $user,
                    'rec_userupdate' => $user,
                    'rec_datecreated' => $now,
                    'rec_dateupdate' => $now,
                    'rec_status' => 1,
                    'rec_comcode' => 'HGS PUSAT',
                    'rec_areacode' => '0000',
                    'transcoa_code' => $new_Code_COA,
                    'transcoa_transaksi_main_code' => '870',
                    'transcoa_coa_desc' => $new_Code_co_desc,
                    'transcoa_head_code' => $no_kwitansi,
                    'transcoa_debet_value' => $headerdata ->value_est_pph_4,
                    'transcoa_credit_value' => 0,
                    'transcoa_coa_date' => $Date_formated,
                    'transcoa_coa_code' => '200',
                    'transcoa_statusposting' => '01',
                    'transcoa_dateposting' => $now,
                    'transcoa_statusapp' => '01'
                ]);
                
                $last_number = $last_number + 1;
                $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                // dd($new_number);
                $new_Code_COA = sprintf('TrCoa-%s-%s', $kode_tahun_bulan, $new_number);
                DB::connection('ms_sql_hgs')
                ->table('tr_acc_transaksi_coa')
                ->insert([
                    'rec_usercreated' => $user,
                    'rec_userupdate' => $user,
                    'rec_datecreated' => $now,
                    'rec_dateupdate' => $now,
                    'rec_status' => 1,
                    'rec_comcode' => 'HGS PUSAT',
                    'rec_areacode' => '0000',
                    'transcoa_code' => $new_Code_COA,
                    'transcoa_transaksi_main_code' => '870',
                    'transcoa_coa_desc' => $new_Code_co_desc,
                    'transcoa_head_code' => $no_kwitansi,
                    'transcoa_debet_value' => 0,
                    'transcoa_credit_value' => $headerdata ->value_est_pph_4,
                    'transcoa_coa_date' => $Date_formated,
                    'transcoa_coa_code' => '226',
                    'transcoa_statusposting' => '01',
                    'transcoa_dateposting' => $now,
                    'transcoa_statusapp' => '01'
                ]);

                if($ada_potongan != 0){
                    // dd($no_kwitansi);
                    $last_number = $last_number + 1;
                    $new_number = $last_number ? str_pad((int)$last_number + 1, 6, '0', STR_PAD_LEFT) : '000001';
                    // dd($new_number);
                    $new_Code_COA = sprintf('TrCoa-%s-%s', $kode_tahun_bulan, $new_number);
                    DB::connection('ms_sql_hgs')
                    ->table('tr_acc_transaksi_coa')
                    ->insert([
                        'rec_usercreated' => $user,
                        'rec_userupdate' => $user,
                        'rec_datecreated' => $now,
                        'rec_dateupdate' => $now,
                        'rec_status' => 1,
                        'rec_comcode' => 'HGS PUSAT',
                        'rec_areacode' => '0000',
                        'transcoa_code' => $new_Code_COA,
                        'transcoa_transaksi_main_code' => 'NONE',
                        'transcoa_coa_desc' => $new_Code_co_desc,
                        'transcoa_head_code' => $no_kwitansi,
                        'transcoa_debet_value' => $ada_potongan,
                        'transcoa_credit_value' => 0,
                        'transcoa_coa_date' => $Date_formated,
                        'transcoa_coa_code' => $coa_code,
                        'transcoa_statusposting' => '01',
                        'transcoa_dateposting' => $now,
                        'transcoa_statusapp' => '01'
                    ]);
                }
                // dd($ada_potongan);

                DB::connection('ms_sql_hgs')
                ->table('tr_acc_transaksi_main')
                ->insert([
                    'rec_usercreated' => $user,
                    'rec_userupdate' => $user,
                    'rec_datecreated' => $now,
                    'rec_dateupdate' => $now,
                    'rec_status' => 1,
                    'rec_comcode' => 'HGS PUSAT',
                    'rec_areacode' => '0000',
                    'transmain_code' => $new_Code_tmain,
                    'transmain_codetransaksi' => $no_kwitansi,
                    'transmain_desc' => $new_Code_co_desc,
                    'transmain_ms_transcode' => '870',
                    'transmain_value' => $headerdata ->value_est_pph_4,
                    'transmain_date' => $now,
                    'transmain_document_note' => $new_Code_co_desc,
                    'transmain_operator' => $user,
                    'transmain_document_date' => $now,
                    'transmain_document_time' => $now
                ]); 
                DB::connection('ms_sql_hgs')
                ->table('tr_tagih_sales_DN_pph4')
                ->where('no_kwitansi', $no_kwitansi)
                ->update([
                    'status' => 1,
                ]); 
            DB::commit();
            return response()->json(['message' => 'Data berhasil dimasukkan', 'details' => $no_kwitansi]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Data gagal dimasukkan: ' . $e->getMessage()], 500);
        }
    }
} 

