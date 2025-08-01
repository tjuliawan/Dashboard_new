<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ChangePasswordController extends Controller
{
    // public function changePassword(Request $request)
    // {
        
    //     $request->validate([
    //         'token' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required|min:8|confirmed',
    //     ]);
    
    //     $status = Password::reset(
    //         $request->only('email', 'password', 'password_confirmation', 'token'),
    //         function ($user, $password) {
    //             $user->forceFill([
    //                 'password' => Hash::make($password)
    //             ])->setRememberToken(Str::random(60));
    
    //             $user->save();
    
    //             event(new PasswordReset($user));
    //         }
    //     );
    
    //     return $status === Password::PASSWORD_RESET
    //                 ? redirect('/login')->with('success', __($status))
    //                 : back()->withErrors(['email' => [__($status)]]);
    // }
    public function changePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z]).+$/'
            ],
            'token' => 'required'
        ]);
    
        $resetData = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('created_at', '>=', Carbon::now('asia/jakarta')->subMinutes(5)) 
            ->first();
    
        if (!$resetData) {
            return response()->json(['error' => 'Token tidak ditemukan atau sudah kedaluwarsa.'], 403);
        }
    
        if (!Hash::check($request->token, $resetData->token)) {
            return response()->json(['error' => 'Token tidak cocok dengan email.'], 403);
        }
    
        DB::table('users')
            ->where('email', $request->email)
            ->update([
                'password' => Hash::make($request->password),
                'created_at' => Carbon::now('asia/jakarta')
            ]);
    
        DB::table('password_resets')->where('email', $request->email)->delete();
    
        return response()->json([
            'message' => 'Password berhasil direset.'
        ]);
    }
    
}
