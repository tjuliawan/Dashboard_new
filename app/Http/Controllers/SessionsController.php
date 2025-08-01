<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class SessionsController extends Controller
{
    public function create()
    {
        return view('session.login-session');
    }

    // public function store()
    // {
    //     $attributes = request()->validate([
    //         'email' => 'required|email',
    //         'password' => 'required'
    //     ]);
    //     // $tets = Auth::attempt($attributes);
    //     $user = \App\Models\User::where('email', $attributes['email'])->first();
    //     dd($user);
    //     // dd($attributes);
    //     // Cek jika login berhasil
    //     if (Auth::attempt($attributes)) {
    //         $user = Auth::user();
            
    //         // dd($user);
    //         // Cek status aktivasi pengguna
    //         if ($user->activate == 1) {
    //             session()->regenerate(); // Regenerasi sesi untuk keamanan
    //             session(['email' => $attributes['email']]); 
                
    //             // Log login berhasil
    //             Log::info('Login berhasil untuk pengguna: ' . $user->email);
    //             Log::info('Sesi setelah login berhasil:', session()->all());
                
    //             return redirect('dashboard')->with([
    //                 'success' => 'Welcome, ' . $user->name . '!'
    //             ]);
    //         } else {
    //             // Logout jika akun tidak aktif
    //             Auth::logout();
    //             session()->flush(); // Bersihkan semua data sesi
    //             Log::warning('Akun tidak aktif: ' . $user->email);
    //             Log::info('Sesi setelah logout akun tidak aktif:', session()->all());
    //             return back()->withErrors(['email' => 'Your account is not active.']);
    //         }
    //     } else {
            
    //         Auth::logout();
    //         session()->flush();
            
    //         Log::warning('Login gagal untuk email: ' . $attributes['email']);
    //         Log::info('Sesi setelah login gagal:', session()->all());
            
    //         return back()->withErrors(['email' => 'Email or password invalid.']);
    //     }
    // }
    public function store()
    {
        $attributes = request()->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = \App\Models\User::where('email', $attributes['email'])->first();
        $hash = $user->password;
        // dd(Hash::check('123456', $hash));
        // dd($user);
        if ($user && \Hash::check($attributes['password'], $user->password)) {
            // Cek status aktivasi pengguna
            if ((int) $user->activate === 1) {
                Auth::login($user); // Login manual
                session()->regenerate(); // Regenerasi sesi untuk keamanan
                session(['email' => $attributes['email']]); 

                // Log login berhasil
                \Log::info('Login berhasil untuk pengguna: ' . $user->email);
                \Log::info('Sesi setelah login berhasil:', session()->all());

                return redirect('dashboard')->with([
                    'success' => 'Welcome, ' . $user->name . '!'
                ]);
            } else {
                // Logout jika akun tidak aktif
                Auth::logout();
                session()->flush(); // Bersihkan semua data sesi
                \Log::warning('Akun tidak aktif: ' . $user->email);
                \Log::info('Sesi setelah logout akun tidak aktif:', session()->all());
                return back()->withErrors(['email' => 'Your account is not active.']);
            }
        } else {
            Auth::logout();
            session()->flush();

            \Log::warning('Login gagal untuk email: ' . $attributes['email']);
            \Log::info('Sesi setelah login gagal:', session()->all());

            return back()->withErrors(['email' => 'Email or password invalid.']);
        }
    }


    public function destroy()
    {
        Auth::logout();
        session()->flush(); 
        Log::info('Sesi setelah logout:', session()->all());

        return redirect('/login')->with(['success' => 'You\'ve been logged out.']);
    }
}
