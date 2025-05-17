<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function create()
    {
        return view('session.register');
    }
    public function store()
    {
        $attributes = request()->validate([
            'username' => ['required', 'max:50', Rule::unique('users', 'username')],
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'password' => ['required', 'min:5', 'max:20'],
            'ms_divisi' => ['required'],
            'sub_divisi' => ['required'],
            'company' => ['required'],
            'cabang' => ['required'],
            'agreement' => ['accepted']
        ]);
        
        $emailExists = DB::connection('ms_sql_hgs')
            ->table('ms_employee')
            ->where('emp_email', $attributes['email'])
            ->exists();

        if (!$emailExists) {
            throw ValidationException::withMessages([
                'email' => 'Email tidak terdaftar dalam data karyawan.',
            ]);
        }

        $attributes['password'] = bcrypt($attributes['password']);
    
        // Tentukan tim berdasarkan divisi
        if (in_array($attributes['ms_divisi'], ['Driver', 'Helper'])) {
            $attributes['current_team_id'] = 'mitra';
        } else {
            $attributes['current_team_id'] = 'staff';
        }
    
        // Tentukan peran pengguna
        $attributes['role'] = 'Guest';
    
        // Buat pengguna baru
        $user = User::create($attributes);
    
        // Logout pengguna setelah pendaftaran
        Auth::logout();
    
        // Log informasi sesi setelah logout (jika perlu untuk debugging)
        Log::info('Sesi setelah logout:', session()->all());
    
        // Redirect ke halaman login dengan pesan sukses
        return redirect('/login')->with('success', 'Pendaftaran berhasil. Silahkan melakukan verifikasi pendaftaran langsung ke tim IT untuk aktivasi akun.');
    }
    
}
