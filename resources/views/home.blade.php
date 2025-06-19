@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-gray-100 flex flex-col justify-between">
    {{-- Hero Section --}}
    <section class="bg-blue-600 text-white py-20 px-4 sm:px-6 lg:px-8 text-center relative overflow-hidden">
        {{-- Background wave/pattern (optional, for visual flair) --}}
        <div class="absolute bottom-0 left-0 w-full h-1/3 bg-white opacity-10 transform skew-y-12 origin-bottom-left -translate-y-1/2"></div>
        <div class="absolute top-0 right-0 w-1/2 h-full bg-white opacity-5 transform skew-y-6 origin-top-right translate-x-1/2"></div>

        <div class="max-w-4xl mx-auto relative z-10">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-6 animate-fade-in-down">
                Kelola Stok Toko Sembako Anda dengan Mudah!
            </h1>
            <p class="text-lg sm:text-xl mb-10 opacity-90 animate-fade-in-up">
                Manajemen inventaris, pencatatan transaksi, dan pemantauan produk yang intuitif dan efisien.
            </p>
            <div class="space-x-4 animate-fade-in">
                @auth {{-- Show Dashboard link if user is logged in --}}
                    <a href="{{ route('dashboard') }}" class="bg-white text-blue-600 hover:bg-blue-100 px-8 py-3 rounded-lg font-semibold text-lg transition duration-300 ease-in-out shadow-lg">
                        Lihat Dashboard
                    </a>
                @else {{-- Show Login/Register if not logged in --}}
                    <a href="{{ route('login') }}" class="bg-white text-blue-600 hover:bg-blue-100 px-8 py-3 rounded-lg font-semibold text-lg transition duration-300 ease-in-out shadow-lg">
                        Masuk Sekarang
                    </a>
                    <a href="{{ route('register') }}" class="border border-white text-white hover:bg-white hover:text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg transition duration-300 ease-in-out">
                        Daftar Gratis
                    </a>
                @endauth
            </div>
        </div>
    </section>

    {{-- Brief Feature Overview Section --}}
    <section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
        <div class="max-w-6xl mx-auto text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-12">Fitur Utama Stokku</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                {{-- Feature 1: Inventory Management --}}
                <div class="bg-gray-50 p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-2">
                    <div class="mb-4 text-blue-600">
                        {{-- Icon for Products/Inventory --}}
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Manajemen Produk & Stok</h3>
                    <p class="text-gray-600">
                        Pantau stok produk Anda secara real-time, tambahkan produk baru, dan kelola informasi harga dengan mudah.
                    </p>
                </div>

                {{-- Feature 2: Transaction Recording --}}
                <div class="bg-gray-50 p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-2">
                    <div class="mb-4 text-green-600">
                        {{-- Icon for Transactions --}}
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10M3 10V7a2 2 0 012-2h14a2 2 0 012 2v3m-2 7v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-3"></path></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Pencatatan Transaksi Cepat</h3>
                    <p class="text-gray-600">
                        Catat setiap penjualan dengan detail produk, jumlah, dan harga secara akurat. Mempermudah proses kasir.
                    </p>
                </div>

                {{-- Feature 3: Activity Monitoring --}}
                <div class="bg-gray-50 p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-2">
                    <div class="mb-4 text-purple-600">
                        {{-- Icon for Activity/Dashboard --}}
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Dashboard & Laporan</h3>
                    <p class="text-gray-600">
                        Dapatkan gambaran umum performa toko Anda dengan statistik penting dan laporan aktivitas terbaru.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-white py-6 text-center">
        <div class="max-w-6xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} Stokku. All rights reserved.</p>
            <p class="mt-2 text-sm">
                <a href="{{ url('/about') }}" class="text-blue-400 hover:underline">Tentang Kami</a>
            </p>
        </div>
    </footer>
</div>
@endsection
