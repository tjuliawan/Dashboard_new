<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function checkLogin(Request $request)
    {
        $login = $request->input('email');
        $password = $request->input('password');
        // dd('');

        // Cek login pakai email atau username
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $login, 'password' => $password])) {
            $user = Auth::user();
            return response()->json([
                'success' => true,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'divisi' => $user->ms_divisi,
                'sub_divisi' => $user->sub_divisi,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Email atau password salah!']);
    }
}
