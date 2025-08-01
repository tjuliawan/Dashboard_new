<?php

namespace App\Http\Controllers;

use DB;
use Validator;
use Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use UAParser\Parser;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\PasswordChanged;


class GantipwController extends Controller
{
    public function changePassword(Request $request)
    {
        $user = Auth::user();
    
        if ($user) {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'password' => [
                    'required',
                    'confirmed',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                    'different:old_password',
                ],
            ], [
                'password.confirmed' => 'Konfirmasi password baru tidak cocok dengan password baru',
                'password.required' => 'Password baru harus diisi',
                'password.min' => 'Password harus terdiri dari minimal 8 karakter',
                'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka',
                'old_password.required' => 'Password lama harus diisi',
                'password.different' => 'Password baru harus berbeda dengan password lama',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
    
            if (Hash::check($request->old_password, $user->password)) {
                try {
                    $currentDate = Carbon::now();
                    
                    $days = [
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu',
                        'Sunday' => 'Minggu',
                    ];
    
                    $months = [
                        'January' => 'Januari',
                        'February' => 'Februari',
                        'March' => 'Maret',
                        'April' => 'April',
                        'May' => 'Mei',
                        'June' => 'Juni',
                        'July' => 'Juli',
                        'August' => 'Agustus',
                        'September' => 'September',
                        'October' => 'Oktober',
                        'November' => 'November',
                        'December' => 'Desember',
                    ];
    
                    $dayName = $days[$currentDate->format('l')];
                    $monthName = $months[$currentDate->format('F')];
    
                    $tanggal = $dayName . ', ' . $currentDate->format('j') . ' ' . $monthName . ' ' . $currentDate->format('Y');
                    $ip = $request->ip();
    
                    $userAgentString = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
                    // Pastikan Parser class tersedia
                    // if (!class_exists(\UAParser\Parser::class)) {
                    //     throw new \Exception('Parser class not found. Make sure ua-parser is installed.');
                    // }
    
                    // $parser = \UAParser\Parser::create();
                    // $result = $parser->parse($userAgentString);
    
                    // $deviceInfo = [
                    //     'userAgent' => $userAgentString,
                    //     'platform' => $this->getPlatform($result),
                    //     'browser' => $this->getBrowser($result),
                    //     'deviceType' => $this->getDeviceType($result),
                    //     'model' => $this->getDeviceModel($result),
                    //     'isBot' => $result->device->isBot ?? false,
                    //     'language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown',
                    //     'cookieEnabled' => isset($_COOKIE) ? 'Yes' : 'No',
                    //     'online' => $this->isOnline(),
                    //     'ipAddress' => $this->getClientIP()
                    // ];
    
                    // $details = [
                    //     'name' => $user->name,
                    //     'email' => $user->email,
                    //     'tanggal' => $tanggal,
                    //     'ip' => $ip,
                    //     'deviceInfo' => $deviceInfo
                    // ];
                    
                    // Mail::send('emails.mailresetpassword', ['details' => $details], function ($message) use ($user) {
                    //     $message->to($user->email)
                    //             ->subject('Notifikasi: Password Anda Berhasil Diubah');
                    // });
                    // dd('sss');
                    
                    $user->update([
                        'password' => Hash::make($request->password)
                    ]);
    
                    return response()->json(['success' => true]);
    
                } catch (\Exception $e) {
                    // Tangani error internal
                    return response()->json([
                        'error' => 'Terjadi kesalahan saat memproses permintaan: ' . $e->getMessage()
                    ], 500);
                }
    
            } else {
                return response()->json(['error' => 'Password lama salah'], 422);
            }
        } else {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    }
    
    private function getPlatform($result)
    {
        return $result->device->family ?? 'Unknown';
    }

    private function getBrowser($result)
    {
        return $result->ua->family ?? 'Unknown';
    }

    private function getDeviceType($result)
    {
        if ($result->device->family === 'Mobile' || $result->device->family === 'Tablet') {
            return 'Mobile/Tablet';
        }
        return 'Desktop';
    }

    private function getDeviceModel($result)
    {
        return $result->device->model ?? 'Unknown Model';
    }

    private function isOnline()
    {
        return checkdnsrr('example.com', 'A');
    }

    private function getClientIP()
    {
        // Mencari IP dari header HTTP
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ipList[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return 'Unknown IP';
        }
    }
}


