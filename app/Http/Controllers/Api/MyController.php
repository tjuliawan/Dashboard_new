<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Pastikan baris ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class MyController extends Controller
{
    public function sendEmail(Request $request)
    {
        $request->validate([
            'email_input' => 'required|email|exists:users,email' 
        ]);

        $token = Str::random(64);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email_input],
            ['token' => $token, 'created_at' => Carbon::now()]
        );
        
        $resetLink = url('/reset-password/' . $token);
        // dd($resetLink);

        Mail::send('emails.reset_password', [
            'name' => $request->email_input,
            'resetLink' => $resetLink
        ], function ($message) use ($request) {
            $message->to($request->email_input)
                    ->subject('Reset Password Akun Anda');
        });
        return response()->json(['message' => 'Password reset email sent.']);
    }
}
