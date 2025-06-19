{{-- resources/views/components/sidenav.blade.php --}}
<aside
    class="fixed inset-y-0 flex-wrap items-center justify-between block w-full p-0 my-4 overflow-y-auto antialiased transition-transform duration-200 -translate-x-full bg-white border-0 shadow-xl dark:shadow-none dark:bg-slate-850 max-w-64 ease-nav-brand z-990 xl:ml-6 rounded-2xl xl:left-0 xl:translate-x-0 xl:relative xl:translate-x-0"
    aria-expanded="false">
    <div class="h-19">
        <i class="absolute top-0 right-0 p-4 opacity-50 cursor-pointer fas fa-times dark:text-white text-slate-400 xl:hidden"
            sidenav-close></i>
        <a class="block px-8 py-6 m-0 text-sm whitespace-nowrap dark:text-white text-slate-700"
            href="{{ route('dashboard') }}">
            <span class="ml-1 font-semibold transition-all duration-200 ease-nav-brand">Stokku Dashboard</span>
        </a>
    </div>

    <hr class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent" />

    <div class="items-center block w-auto max-h-screen overflow-auto h-sidenav grow basis-full">
        <ul class="flex flex-col pl-0 mb-0">
            {{-- Dashboard --}}
            <li class="mt-0.5 w-full">
                <a href="{{ route('dashboard') }}"
                    class="py-2.7 {{ request()->routeIs('dashboard') ? 'bg-blue-500/13 font-semibold text-slate-700' : 'text-slate-700' }} text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 transition-colors">
                    <div
                        class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center text-center xl:p-2.5">
                        <i class="text-blue-500 ni ni-tv-2"></i>
                    </div>
                    <span class="ml-1">Dashboard</span>
                </a>
            </li>

            {{-- Products --}}
            <li class="mt-0.5 w-full">
                <a href="{{ route('products.index') }}"
                    class="py-2.7 {{ request()->routeIs('products.*') ? 'bg-blue-500/13 font-semibold text-slate-700' : 'text-slate-700' }} text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 transition-colors">
                    <div
                        class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center text-center xl:p-2.5">
                        <i class="text-orange-500 ni ni-box-2"></i>
                    </div>
                    <span class="ml-1">Produk</span>
                </a>
            </li>

            {{-- Restock --}}
            <li class="mt-0.5 w-full">
                <a href="{{ route('restocks.index') }}"
                    class="py-2.7 {{ request()->routeIs('restocks.*') ? 'bg-blue-500/13 font-semibold text-slate-700' : 'text-slate-700' }} text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 transition-colors">
                    <div
                        class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center text-center xl:p-2.5">
                        <i class="text-emerald-500 ni ni-cart"></i>
                    </div>
                    <span class="ml-1">Restock</span>
                </a>
            </li>

            {{-- Transaksi --}}
            <li class="mt-0.5 w-full">
                <a href="{{ route('transactions.index') }}"
                    class="py-2.7 {{ request()->routeIs('transactions.*') ? 'bg-blue-500/13 font-semibold text-slate-700' : 'text-slate-700' }} text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 transition-colors">
                    <div
                        class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center text-center xl:p-2.5">
                        <i class="text-emerald-500 ni ni-cart"></i>
                    </div>
                    <span class="ml-1">Transaksi</span>
                </a>
            </li>

            {{-- Logout --}}
            <li class="mt-0.5 w-full">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                        class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 rounded-lg transition-colors text-slate-700 hover:bg-red-100">
                        <div
                            class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center text-center xl:p-2.5">
                            <i class="text-red-500 ni ni-user-run"></i>
                        </div>
                        <span class="ml-1">Logout</span>
                    </a>
                </form>
            </li>
        </ul>
    </div>
</aside>
