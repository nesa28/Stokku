<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;// Untuk validasi unique pada update

class UserController extends Controller
{
    //Menampilkan daftar semua pengguna.
    public function index(): View
    {
        $users = User::latest()->paginate(10); // Ambil pengguna terbaru, 10 per halaman
        return view('users.index', compact('users'));
    }

    //Menampilkan formulir untuk membuat pengguna baru.
    public function create(): View
    {
        return view('users.create');
    }


    //Menyimpan pengguna baru ke database.
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email', // Email harus unik
            'password' => 'required|string|min:8|confirmed', // Kata sandi minimal 8 karakter dan harus dikonfirmasi
            'nomor_telepon' => 'nullable|string|max:20',
        ]);

        User::create([
            'nama' => $validatedData['nama'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), // Hash kata sandi untuk keamanan
            'nomor_telepon' => $validatedData['nomor_telepon'],
            'email_verified_at' => now(), // Anggap email langsung terverifikasi jika dibuat dari admin
        ]);

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil ditambahkan!');
    }


    //Menampilkan detail pengguna tertentu.
    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    // Menampilkan formulir untuk mengedit pengguna tertentu.
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }


    //Memperbarui informasi pengguna di database.
    public function update(Request $request, User $user): RedirectResponse
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Email harus unik, kecuali untuk pengguna ini sendiri
            ],
            'nomor_telepon' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed', // Kata sandi bersifat opsional saat update
        ]);

        $updateData = [
            'nama' => $validatedData['nama'],
            'email' => $validatedData['email'],
            'nomor_telepon' => $validatedData['nomor_telepon'],
        ];

        // Perbarui kata sandi hanya jika diisi oleh pengguna
        if (!empty($validatedData['password'])) {
            $updateData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
                         ->with('success', 'Profil pengguna berhasil diperbarui!');
    }

    // Menghapus pengguna dari database.
    public function destroy(User $user): RedirectResponse 
    {
        // Cegah penghapusan jika pengguna memiliki transaksi atau restock terkait
        if ($user->transactions()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Pengguna tidak dapat dihapus karena memiliki riwayat transaksi terkait.');
        }

        if ($user->restocks()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Pengguna tidak dapat dihapus karena memiliki riwayat restock terkait.');
        }

        $user->delete(); // Hapus pengguna
        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil dihapus!');
    }
}
