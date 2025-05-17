<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ResetController extends Controller
{
    public function create()
    {
        return view('session/reset-password/sendEmail');
        
    }

    public function sendEmail_bak(Request $request)
    {
        if(env('IS_DEMO'))
        {
            return redirect()->back()->withErrors(['msg2' => 'You are in a demo version, you can\'t recover your password.']);
        }
        else{
            $request->validate(['email' => 'required|email']);

            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status === Password::RESET_LINK_SENT
                        ? back()->with(['success' => __($status)])
                        : back()->withErrors(['email' => __($status)]);
        }
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'email_input' => [
                'required',
                'email',
                Rule::exists('users', 'email')->where(function ($query) {
                    $query->whereNotNull('email');
                }),
            ]
        ], [
            'email_input.exists' => 'Email tidak terdaftar di sistem.',
        ]);
    
        $plainToken = Str::random(64); // ini yang akan dikirim ke email
        $hashedToken = Hash::make($plainToken); // ini yang disimpan di database
    
        // Simpan token ke database (hapus dulu token sebelumnya)
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email_input],
            [
                'token' => $hashedToken,
                'created_at' => Carbon::now('asia/jakarta')
            ]
        );
    
        $resetLink = url('/reset-password/' . urlencode($plainToken) . '?email=' . urlencode($request->email_input));
    
        // Kirim email
        Mail::send('emails.reset_password', [
            'name' => $request->email_input,
            'resetLink' => $resetLink
        ], function ($message) use ($request) {
            $message->to($request->email_input)
                    ->subject('Reset Password Akun Anda');
        });
    
        return response()->json(['message' => 'Password reset email sent.']);
    }
    
    public function resetPass($token)
    {
        return view('session/reset-password/resetPassword', ['token' => $token]);
    }
    public function showResetForm($token)
    {
        return view('session/reset-password/reset-password', ['token' => $token]);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:6',
            'token' => 'required'
        ]);

        $check = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$check) {
            return back()->withErrors(['token' => 'Invalid or expired token.']);
        }

        // Update password
        DB::table('users')->where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        // Hapus token setelah digunakan
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect('/login')->with('status', 'Password has been reset!');
    }

}
