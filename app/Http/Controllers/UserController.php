<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Untuk validasi unique pada update

// Controller untuk manajemen data pengguna
class UserController extends Controller
{
    // Menampilkan daftar semua pengguna
    public function index(): View
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    // Menampilkan form tambah pengguna baru
    public function create(): View
    {
        return view('users.create');
    }

    // Menyimpan pengguna baru ke database
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'nomor_telepon' => 'nullable|string|max:20',
        ]);

        User::create([
            'nama' => $validatedData['nama'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'nomor_telepon' => $validatedData['nomor_telepon'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil ditambahkan!');
    }

    // Menampilkan detail pengguna tertentu
    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    // Menampilkan form edit pengguna
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    // Memperbarui informasi pengguna
    public function update(Request $request, User $user): RedirectResponse
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'nomor_telepon' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'nama' => $validatedData['nama'],
            'email' => $validatedData['email'],
            'nomor_telepon' => $validatedData['nomor_telepon'],
        ];

        // Update password jika diisi
        if (!empty($validatedData['password'])) {
            $updateData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
                         ->with('success', 'Profil pengguna berhasil diperbarui!');
    }

    // Menghapus pengguna dari database
    public function destroy(User $user): RedirectResponse
    {
        // Cegah hapus jika user punya transaksi/restock
        if ($user->transactions()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Pengguna tidak dapat dihapus karena memiliki riwayat transaksi terkait.');
        }

        if ($user->restocks()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Pengguna tidak dapat dihapus karena memiliki riwayat restock terkait.');
        }

        $user->delete();
        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil dihapus!');
    }
}
