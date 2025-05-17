<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    // Update foto profil
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpg,jpeg,png',
        ]);
        $user = Auth::user();
    
        if (!$user->username) {
            return back()->with('error', 'Username tidak ditemukan.');
        }

        $extensions = ['jpg', 'jpeg', 'png'];
        foreach ($extensions as $ext) {
            $path = 'public/assets/img/' . $user->username . '.' . $ext;
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        }
    
        // if ($user->profile_image) {
        //     $oldPhotoPath = 'public/' . $user->profile_image;
        //     if (Storage::exists($oldPhotoPath)) {
        //         Storage::delete($oldPhotoPath);
        //     }
        // }
    
        $extension = $request->file('profile_image')->extension();
        $filePath = $request->file('profile_image')->storeAs('public/assets/img', $user->username . '.' . $extension);
    
        // Debug: Periksa apakah file disimpan
        if ($filePath) {
            return back()->with('success', 'Foto profil berhasil diperbarui.');
        } else {
            return back()->with('error', 'Gagal menyimpan foto profil.');
        }
    }
    public function upload(Request $request)
    {
        $request->validate([
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);
    
        $gambar = $request->file('gambar');
        $namaFile = time() . '_' . $gambar->getClientOriginalName();
        $gambar->storeAs('public/assets/img', $namaFile);
    
        return response()->json([
            'message' => 'Gambar berhasil diupload!',
            'path' => asset('storage/assets/img/' . $namaFile)
        ]);
    }

    public function get_user_info(Request $request)
    {
        $email = auth()->user()->email;

        $data = DB::connection('ms_sql_hgs')->select("
            SELECT * FROM ms_employee WHERE emp_email = ?
        ", [$email]);

        return response()->json($data[0]);
    }
    public function updateProfilInfo(Request $request)
    {
        $request->validate([
            'profil_info' => 'nullable|string',
        ]);
    
        $user = auth()->user();
        $user->profil_info = $request->profil_info;
        $user->save();
    
        return response()->json(['success' => true]);
    }
}
