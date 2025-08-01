<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;


class userController extends Controller
{
    public function index()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('laravel-examples.user-management', compact('user') );
    }
    public function index_atf_user()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // session(['token' => $token]);
        return view ('user_ver.index', compact('user') );
    }
    public function index_add_emp()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        $token = $user->createToken('auth_token')->plainTextToken;
        session(['token' => $token]);
        return view ('laravel-examples.add-employee', compact('user') );
    }
    public function index_edit_emp()
    {
        Session::flash('url','Master Central');
        $user = auth()->user();
        // auth()->user()->tokens()->delete();
        $email = session('email');
        $user = User::where('email', $email)->firstOrFail();
        // dd($email);
        $token = $user->createToken('auth_token')->plainTextToken;
        session(['token' => $token]);
        return view ('laravel-examples.edit-employee', compact('user') );
    }
    public function table_user_data(Request $request)
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
                Ms_Emp_Central_Code,
                Ms_Emp_Name,
                Cek_Aktif,
                Ms_Company_Code,
                Cek_PKWT,
                HP,
                Email,
                Ms_Emp_Last_Name
            FROM 
            Ms_Emp_Central 
            where rec_status = 1
        
        ");
        // dd($data); 
        // dd($branches); 
        return response()->json($data);
    }
    public function get_emp_data(Request $request)
    {
        $emp_code = $request->input('emp_code');
        $data = DB::connection('mc')->select(" 		
            SELECT
                Ms_Emp_Central_Code,
                Ms_Emp_Name,
                Cek_Aktif,
                Ms_Company_Code,
                Cek_PKWT,
                HP,
                Email,
                Ms_Emp_Last_Name
            FROM 
            Ms_Emp_Central 
            where Ms_Emp_Central_Code = '$emp_code'
        
        ");
        return response()->json($data);
    }
    public function store_emp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'com_code' => 'required',
                'nama' => 'required',
                'email' => 'required',
                'hp' => 'required',
            ], [
                'com_code.required' => 'Commpany Wajib di isi' ,
                'nama.required' => 'Nama Wajib di isi' ,
                'email.required' => 'email Wajib di isi' ,
                'hp.required' => 'Nomor hp Wajib di isi' ,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed.'
                ], 422);
            }

            $emp_code = $request -> input('emp_code');
            $com_code = $request -> input('com_code');
            $nama = $request -> input('nama');
            $email = $request -> input('email');
            $hp = $request -> input('hp');
            $pkwt = $request -> input('pkwt');
            $aktif = $request -> input('aktif');
            $jakartaTime = Carbon::now('Asia/Jakarta');
            if($pkwt = 'true')
            {
                $inputpkwt = 1;
            }else{
                $inputpkwt = 0;
            }
            if($aktif = 'true')
            {
                $inputaktif = 1;
            }else{
                $inputaktif = 0;
            }

            $user =  auth()->user()->username;
            
            if($emp_code ==""){
                $last_no = DB::connection('mc')->select("
                SELECT convert(COUNT(1), int) total FROM `Ms_Emp_Central`
                ");
                $lastno = $last_no[0]->total;
                $new_no = $lastno + 1;
                $new_code = "EMP-$com_code-$new_no";
                
                // dd($new_code); 
                $store = DB::connection('mc')->insert("
                    INSERT INTO Ms_Emp_Central (
                        Ms_Emp_Central_Code,
                        Ms_Emp_Name,
                        Cek_Aktif,
                        Ms_Company_Code,
                        Cek_PKWT,
                        HP,
                        Email,
                        Ms_Emp_Last_Name      
                    ) VALUES (
                        '$new_code',
                        '$nama',
                        '$inputaktif',
                        '$com_code',
                        '$inputpkwt',
                        '$hp',
                        '$email',
                        '$nama'
                    )
                ");
            }else{
                $store = DB::connection('mc')->insert("
                    UPDATE Ms_Emp_Central
                    SET 
                    Ms_Emp_Name = '$nama',
                    Cek_Aktif = '$inputaktif',
                    Ms_Company_Code = '$com_code',
                    Cek_PKWT = '$inputpkwt',
                    HP = '$hp',
                    Email = '$email',
                    Ms_Emp_Last_Name =  '$nama'
                    WHERE Ms_Emp_Central_Code = '$emp_code';
                   
                ");
            }

            DB::commit(); 
            return response()->json([
                'success' => true,
                'message' => 'Data successfully saved.'
            ]);
            // DB::rollBack(); 
            
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage() 
            ], 500);
        }        
    }
    public function delete_emp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'emp' => 'required',
            ], [
                'emp.required' => 'emp Wajib di isi' ,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed.'
                ], 422);
            }

            $emp = $request -> input('emp');
            $riwayat = DB::connection('mc')->insert("
                UPDATE Ms_Emp_Central
                SET rec_status = 0
                WHERE Ms_Emp_Central_Code = '$emp';
            ");

            DB::commit(); 
            return response()->json([
                'success' => true,
                'message' => 'Data successfully saved.'
            ]);
            // DB::rollBack(); 
            
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage() 
            ], 500);
        }        
    }
    public function updateActivation($id)
    {
        // Cari user berdasarkan ID
        $user = User::findOrFail($id);

        // Toggle nilai activate antara 1 dan 0
        $user->activate = $user->activate == 1 ? 0 : 1;
        $user->save();

        // Kembalikan response yang bisa dipakai di front-end
        return response()->json(['status' => 'success', 'newState' => $user->activate]);
    }
    public function get_user(Request $request)
    {
        $data = DB::select(" 		
            select * from users where ms_divisi not like '%IT%' order by created_at desc 
        "); 
        return response()->json($data);
    }
} 