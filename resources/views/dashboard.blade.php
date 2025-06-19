@extends('layouts.argon')

@section('content')
    <div id="app">
        <div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

        <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
            <nav
                class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 transition-all ease-in shadow-none duration-250 rounded-2xl lg:flex-nowrap lg:justify-start">
                <div class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
                    <nav>
                        <h6 class="mb-0 font-bold text-white capitalize">Dashboard</h6>
                    </nav>
                    {{-- You might have user profile/logout links here --}}
                </div>
            </nav>

            <div class="w-full px-6 py-6 mx-auto">
                <div class="flex flex-wrap -mx-3">

                    <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                        <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl rounded-2xl bg-clip-border">
                            <div class="flex-auto p-4">
                                <div class="flex flex-row -mx-3">
                                    <div class="flex-none w-2/3 max-w-full px-3">
                                        <div>
                                            <p class="mb-0 font-sans text-sm font-semibold leading-normal">Total Produk</p>
                                            <h5 class="mb-2 font-bold">{{ $totalProducts ?? 0 }}</h5>
                                        </div>
                                    </div>
                                    <div class="px-3 text-right basis-1/3">
                                        <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-to-tl from-blue-500 to-blue-600">
                                            <i class="ni leading-none ni-box-2 text-lg relative top-3.5 text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                        <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl rounded-2xl bg-clip-border">
                            <div class="flex-auto p-4">
                                <div class="flex flex-row -mx-3">
                                    <div class="flex-none w-2/3 max-w-full px-3">
                                        <div>
                                            <p class="mb-0 font-sans text-sm font-semibold leading-normal">Total Transaksi</p>
                                            <h5 class="mb-2 font-bold">{{ $totalTransactions ?? 0 }}</h5>
                                        </div>
                                    </div>
                                    <div class="px-3 text-right basis-1/3">
                                        <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-to-tl from-green-500 to-green-600">
                                            <i class="ni leading-none ni-cart text-lg relative top-3.5 text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                        <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl rounded-2xl bg-clip-border">
                            <div class="flex-auto p-4">
                                <div class="flex flex-row -mx-3">
                                    <div class="flex-none w-2/3 max-w-full px-3">
                                        <div>
                                            <p class="mb-0 font-sans text-sm font-semibold leading-normal">Total Restock</p>
                                            <h5 class="mb-2 font-bold">{{ $totalRestocks ?? 0 }}</h5>
                                        </div>
                                    </div>
                                    <div class="px-3 text-right basis-1/3">
                                        <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-to-tl from-orange-500 to-orange-600">
                                            <i class="ni leading-none ni-box-full text-lg relative top-3.5 text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap mt-6 -mx-3">
                    <div class="w-full max-w-full px-3 mt-0 mb-6 sm:w-full xl:w-2/3"> {{-- Adjusted width for better layout if adding low stock --}}
                        <div class="border-black/12.5 shadow-xl relative flex min-w-0 flex-col break-words rounded-2xl border-0 border-solid bg-white bg-clip-border">
                            <div class="p-4">
                                <div class="bg-white shadow-xl rounded-2xl p-6"> {{-- Nested p-6 here is redundant, check your CSS --}}
                                    <h2 class="text-xl font-bold text-slate-700 mb-4">Aktivitas Terbaru</h2>
                                    <div class="space-y-4">
                                        @forelse($recentActivities as $activity)
                                            <div class="flex items-start space-x-4">
                                                <div class="flex-shrink-0">
                                                    @if($activity->type === 'create')
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100">
                                                            <i class="ni leading-none ni-fat-add text-lg relative top-3.5 text-green-500"></i> {{-- Changed ni-box-2 to ni-fat-add --}}
                                                        </span>
                                                    @elseif($activity->type === 'update')
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100">
                                                            <i class="ni leading-none ni-settings text-lg relative top-3.5 text-blue-500"></i> {{-- Changed ni-pencil to ni-settings --}}
                                                        </span>
                                                    @elseif($activity->type === 'delete')
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100">
                                                            <i class="ni leading-none ni-fat-remove text-lg relative top-3.5 text-red-500"></i> {{-- Changed ni-box-2 to ni-fat-remove --}}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100">
                                                            <i class="ni leading-none ni-bulb-61 text-lg relative top-3.5 text-gray-500"></i> {{-- Generic icon --}}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600">{{ $activity->description }}</p>
                                                    <p class="text-xs text-gray-400">
                                                        {{ $activity->created_at->diffForHumans() }}
                                                        @if($activity->user)
                                                            by {{ $activity->user->name }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-gray-500">Tidak ada aktivitas terbaru.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full max-w-full px-3 mt-0 sm:w-full xl:w-1/3"> {{-- Adjusted width --}}
                        <div class="border-black/12.5 shadow-xl relative flex min-w-0 flex-col break-words rounded-2xl border-0 border-solid bg-white bg-clip-border">
                            <div class="p-4">
                                <div class="bg-white shadow-xl rounded-2xl p-6"> {{-- Nested p-6 here is redundant, check your CSS --}}
                                    <h2 class="text-xl font-bold text-slate-700 mb-4">Produk Stok Rendah (≤ {{ $lowStockThreshold }})</h2>
                                    <div class="space-y-3">
                                        @forelse($lowStockProducts as $product)
                                            <div class="flex items-center justify-between p-2 rounded-md bg-red-50">
                                                <p class="text-sm font-medium text-red-700">{{ $product->nama_produk }}</p>
                                                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-200 rounded-full">{{ $product->stok }} {{ $product->satuan }}</span>
                                            </div>
                                        @empty
                                            <p class="text-gray-500">Semua produk memiliki stok yang cukup.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Footer (if applicable, usually not inside main content div) --}}
                {{-- <footer class="pt-4 pb-1">
                    <div class="w-full px-6 mx-auto">
                        <div class="flex flex-wrap items-center -mx-3 lg:justify-between">
                            <div class="w-full max-w-full px-3 mt-0 mb-6 shrink-0 lg:mb-0 lg:w-1/2">
                                <div class="text-sm leading-normal text-center text-slate-500 lg:text-left">
                                    © <script> document.write(new Date().getFullYear() + ","); </script>
                                    made with <i class="fa fa-heart"></i> by
                                    <a href="https://www.creative-tim.com" class="font-semibold text-slate-700" target="_blank">Creative Tim</a>
                                    for a better web.
                                </div>
                            </div>
                            <div class="w-full max-w-full px-3 mt-0 lg:w-1/2">
                                <ul class="flex flex-wrap justify-center pl-0 mb-0 list-none lg:justify-end">
                                    <li class="nav-item">
                                        <a href="https://www.creative-tim.com" class="block px-4 pt-0 pb-1 text-sm font-normal text-slate-500" target="_blank">Creative Tim</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="https://www.creative-tim.com/presentation" class="block px-4 pt-0 pb-1 text-sm font-normal text-slate-500" target="_blank">About Us</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="http://blog.creative-tim.com" class="block px-4 pt-0 pb-1 text-sm font-normal text-slate-500" target="_blank">Blog</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="https://www.creative-tim.com/license" class="block px-4 pt-0 pb-1 text-sm font-normal text-slate-500" target="_blank">License</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </footer> --}}

            </div>
        </main>
    </div>
@endsection
