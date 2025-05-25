<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Index - Menampilkan semua data
    public function index()
    {
        $user = User::all();
        return view('user.index', compact('user'));
    }

    // Create - Menampilkan form tambah data
    public function create()
    {
        return view('user.create');
    }

    // Store - Menyimpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'nim' => 'required',
            'nama' => 'required',
            'email' => 'required',
            'jurusan' => 'required',
        ]);

        // Cara 1: Eloquent create
        $mahasiswa = Mahasiswa::create($request->all());

        return redirect()->route('mahasiswa')
            ->with('success', 'Mahasiswa created successfully.');
    }

    // Edit - Menampilkan form edit
    public function edit($id)
    {
        $mhs = Mahasiswa::find($id);
        return view('mahasiswa.edit', compact('mhs'));
    }

    // Update - Memperbarui data
    public function update(Request $request, $id)
    {
        $request->validate([
            'nim' => 'required',
            'nama' => 'required',
            'email' => 'required',
            'jurusan' => 'required',
        ]);

        $update = [
            'nim' => $request->nim,
            'nama' => $request->nama,
            'email' => $request->email,
            'jurusan' => $request->jurusan,
        ];

        Mahasiswa::whereId($id)->update($update);
        return redirect()->route('mahasiswa')
            ->with('success', 'Mahasiswa updated successfully');
    }

    // Destroy - Menghapus data
    public function destroy($id)
    {
        $mhs = Mahasiswa::find($id);
        $mhs->delete();
        return redirect()->route('mahasiswa')
            ->with('success', 'Mahasiswa deleted successfully');
    }
}
