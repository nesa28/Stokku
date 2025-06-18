<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //validasi data yang dikirim dari client
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        //membuat user baru
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password), // mengenkripsi password
        ]);


        //redirect ke halaman login atau mengembalikan response sukses
        return response()->json(['message' => 'User created successfully'], 201);
        
    }

    
    public function login(Request $request)
    {
        //credentials adalah data yang digunakan untuk otentikasi user
        //request->only adalah untuk mengambil data dari client yang dikirim melalui request
        $credentials = $request->only(['email', 'password']);
        //jika tidak ada token dan auth('api')->attempt mengembalikan false,
        //artinya otentikasi gagal karena email atau password salah saat login
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        //mengembalikan token yang digunakan untuk otentikasi selanjutnya
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            //expires_in adalah waktu kedaluwarsa token dalam detik
            //getTTL adalah fungsi dari JWTAuth yang mengembalikan waktu kedaluwarsa token dalam menit
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function logout()
    {
        //menghapus token yang digunakan untuk otentikasi
        auth('api')->logout();
        //mengembalikan response sukses
        return response()->json(['message' => 'Successfully logged out']);
    }

    //profil mengembalikan data user yang sedang login
    public function profile()
    {
        return response()->json(auth('api')->user());
    }
}
