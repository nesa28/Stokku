@extends('layouts.argon')

@section('content')
<div class="absolute top-0 left-0 w-full h-40 bg-blue-500 rounded-bl-xl z-0 dark:hidden"></div>

<main class="relative h-full max-h-screen xl:ml-68 rounded-xl">
    <div class="relative z-10 px-6 py-6 max-w-7xl mx-auto">
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <h1 class="text-xl font-bold text-slate-700 mb-6">Tambah Transaksi</h1>

            <form action="{{ route('transactions.store') }}" method="POST" onsubmit="disableSubmitButton(this)">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="tanggal_transaksi" class="block text-sm font-medium text-slate-600 mb-1">Tanggal
                            Transaksi</label>
                        <input type="date" name="tanggal_transaksi" id="tanggal_transaksi"
                            value="{{ old('tanggal_transaksi', date('Y-m-d')) }}" required class="border rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400
                               @error('tanggal_transaksi') border-red-500 @enderror" />
                        @error('tanggal_transaksi')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="pelanggan" class="block text-sm font-medium text-slate-600 mb-1">Pelanggan</label>
                        <input type="text" name="pelanggan" id="pelanggan" value="{{ old('pelanggan') }}" class="border rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400
                               @error('pelanggan') border-red-500 @enderror" />
                        @error('pelanggan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="my-6" />
                <h2 class="text-lg font-semibold text-slate-700 mb-4">Produk Terjual</h2>

                <div id="produk-list" class="space-y-4 mb-6">
                    {{-- This is the TEMPLATE for a single product row. It won't be rendered directly. --}}
                    <template id="product-row-template">
                        <div
                            class="grid grid-cols-1 xl:grid-cols-4 gap-4 produk-item p-3 border rounded-md bg-gray-50 relative">
                            {{-- Product Search Component --}}
                            <div x-data="productSearch(allProductsData, 'product_id_PLACEHOLDER_INDEX', 'product_name_PLACEHOLDER_INDEX', 'products.PLACEHOLDER_INDEX.product_id')"
                                class="relative">
                                <label for="product_name_PLACEHOLDER_INDEX"
                                    class="block text-sm font-medium text-slate-600 mb-1">Produk</label>
                                <input type="hidden" name="products[PLACEHOLDER_INDEX][product_id]"
                                    x-model="selectedProductId" required>
                                <input type="text" id="product_name_PLACEHOLDER_INDEX" x-model="searchQuery"
                                    @input="filterProducts" @focus="showDropdown = true"
                                    @click.away="showDropdown = false"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400"
                                    placeholder="Cari ID atau Nama Produk..." autocomplete="off" required>

                                <div x-show="showDropdown && filteredProducts.length > 0"
                                    class="absolute z-10 w-full bg-white border border-gray-300 mt-1 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="product in filteredProducts" :key="product.id">
                                        <div @click="selectProduct(product)"
                                            class="px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm">
                                            <span
                                                x-text="`${product.user_product_code} - ${product.nama_produk}`"></span>
                                            <span class="text-gray-500 text-xs"
                                                x-text="`(Stok: ${product.stock})`"></span>
                                        </div>
                                    </template>
                                </div>
                                {{-- Validation error for product_id --}}
                                @error('products.PLACEHOLDER_INDEX.product_id')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Jumlah (Quantity) --}}
                            <div>
                                <label for="jumlah_PLACEHOLDER_INDEX"
                                    class="block text-sm font-medium text-slate-600 mb-1">Jumlah</label>
                                <input type="number" name="products[PLACEHOLDER_INDEX][jumlah]"
                                    id="jumlah_PLACEHOLDER_INDEX" min="1" placeholder="Jumlah" required value="1"
                                    class="border rounded-lg px-3 py-2 text-sm w-full">
                                @error('products.PLACEHOLDER_INDEX.jumlah')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Jenis Penjualan (Sell Type) --}}
                            <div>
                                <label for="jenis_penjualan_PLACEHOLDER_INDEX"
                                    class="block text-sm font-medium text-slate-600 mb-1">Jenis Penjualan</label>
                                <select name="products[PLACEHOLDER_INDEX][jenis_penjualan]"
                                    id="jenis_penjualan_PLACEHOLDER_INDEX" required
                                    class="border rounded-lg px-3 py-2 text-sm w-full">
                                    <option value="">Pilih Jenis Penjualan</option>
                                    <option value="satuan">Satuan</option>
                                    <option value="eceran">Eceran</option>
                                </select>
                                @error('products.PLACEHOLDER_INDEX.jenis_penjualan')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Remove Button --}}
                            <div class="flex items-end justify-center">
                                <button type="button"
                                    class="remove-produk bg-red-600 hover:bg-red-700 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg w-full">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                    {{-- End of Template --}}

                    {{-- This div will hold the actual product rows --}}
                    <div id="actual-product-rows"></div>

                </div>

                <button type="button" id="tambah-produk"
                    class="mb-6 bg-gray-200 hover:bg-gray-300 text-slate-700 text-sm py-2 px-4 rounded-lg">
                    + Tambah Produk
                </button>

                @error('products')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror

                <div class="flex gap-4">
                    <button type="submit" id="submit-button"
                        class="bg-blue-600 hover:bg-blue-700 text-slate-700 text-sm font-semibold py-2 px-6 rounded-lg">
                        Simpan Transaksi
                    </button>
                    <a href="{{ route('transactions.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-slate-700 text-sm font-semibold py-2 px-6 rounded-lg">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

@push('scripts')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Make product data globally available for Alpine components and dynamic JS
        const allProductsData = @json($products);

        // Make Laravel errors available globally for Alpine components
        window.laravelErrors = @json($errors->messages());

        // --- Alpine.js Component Definition ---
        document.addEventListener('alpine:init', () => {
            Alpine.data('productSearch', (allProducts, productIdInputId, productNameInputId, errorNamePath) => ({
                allProducts: allProducts,
                searchQuery: '',
                filteredProducts: [],
                selectedProductId: '',
                showDropdown: false,
                productIdInputId: productIdInputId,
                productNameInputId: productNameInputId,
                errorNamePath: errorNamePath,

                init() {
                    const hiddenInput = document.getElementById(this.productIdInputId);
                    const oldProductId = hiddenInput ? hiddenInput.value : '';
                    if (oldProductId) {
                        const product = this.allProducts.find(p => p.id == oldProductId);
                        if (product) {
                            this.searchQuery = `${product.user_product_code} - ${product.nama_produk}`;
                            this.selectedProductId = product.id; // <-- FIXED THIS LINE
                        }
                    }
                    if (window.laravelErrors && window.laravelErrors[this.errorNamePath]) {
                        const productNameInput = document.getElementById(this.productNameInputId);
                        if (productNameInput) {
                            productNameInput.classList.add('border-red-500');
                        }
                    }
                    this.filterProducts();
                },

                filterProducts() {
                    const query = this.searchQuery.toLowerCase();
                    if (query.length < 1) {
                        this.filteredProducts = this.allProducts;
                        return;
                    }
                    this.filteredProducts = this.allProducts.filter(product => {
                        const nameMatch = product.nama_produk.toLowerCase().includes(query);
                        const idMatch = String(product.user_product_code).includes(query);
                        return nameMatch || idMatch;
                    });
                },

                selectProduct(product) {
                    this.searchQuery = `${product.user_product_code} - ${product.nama_produk}`;
                    this.selectedProductId = product.id;
                    this.showDropdown = false;
                    const productNameInput = document.getElementById(this.productNameInputId);
                    if (productNameInput && productNameInput.classList.contains('border-red-500')) {
                        productNameInput.classList.remove('border-red-500');
                    }
                },

                clearIfInvalid() {
                    const selected = this.allProducts.find(p => p.id == this.selectedProductId);
                    if (!selected || this.searchQuery !== `${selected.id} - ${selected.nama_produk}`) {
                        this.selectedProductId = '';
                        this.searchQuery = '';
                    }
                }
            }));
        });

        // --- DOM Loaded and Button Event Listeners ---
        document.addEventListener('DOMContentLoaded', function () {
            const productListTemplate = document.getElementById('product-row-template').content;
            const actualProductRowsContainer = document.getElementById('actual-product-rows');
            const addProductButton = document.getElementById('tambah-produk'); // The static "Tambah Produk" button
            const submitButton = document.getElementById('submit-button'); // The static "Simpan Transaksi" button

            let produkIndex = 0; // Initialize produkIndex correctly here

            // Function to initialize a new product row (either initial or added)
            function initializeProductRow(container, index) {
                const clone = document.importNode(productListTemplate, true);
                const productItem = clone.querySelector('.produk-item');

                // Replace placeholders with dynamic index
                const html = productItem.outerHTML
                    .replace(/PLACEHOLDER_INDEX/g, index)
                    .replace(/product_id_PLACEHOLDER_INDEX/g, `product_id_${index}`)
                    .replace(/product_name_PLACEHOLDER_INDEX/g, `product_name_${index}`)
                    .replace(/jumlah_PLACEHOLDER_INDEX/g, `jumlah_${index}`)
                    .replace(/jenis_penjualan_PLACEHOLDER_INDEX/g, `jenis_penjualan_${index}`);
                const errorNamePath = `products.${index}.product_id`;

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html.trim();
                const newProductRow = tempDiv.firstChild;

                // Manually apply Alpine x-data with correct parameters for this row
                newProductRow.querySelector('div[x-data]').setAttribute('x-data', `productSearch(allProductsData, 'product_id_${index}', 'product_name_${index}', '${errorNamePath}')`);

                // Apply old values if available (for validation errors)
                const currentOldProducts = @json(old('products', []));
                const oldProductData = currentOldProducts[index] || {};

                if (oldProductData.product_id) {
                    const hiddenInput = newProductRow.querySelector(`input[name="products[${index}][product_id]"]`);
                    if (hiddenInput) {
                        hiddenInput.value = oldProductData.product_id;
                    }
                }
                if (oldProductData.jumlah) {
                    newProductRow.querySelector(`input[name="products[${index}][jumlah]"]`).value = oldProductData.jumlah;
                }
                if (oldProductData.jenis_penjualan) {
                    newProductRow.querySelector(`select[name="products[${index}][jenis_penjualan]"]`).value = oldProductData.jenis_penjualan;
                }

                container.appendChild(newProductRow);

                // Crucial: Re-initialize Alpine component for the newly added row
                Alpine.initTree(newProductRow);
            }

            // --- Initial Load: Render existing (old) products or one empty row ---
            const oldProducts = @json(old('products', []));

            if (Object.keys(oldProducts).length > 0) {
                Object.keys(oldProducts).sort().forEach(index => {
                    initializeProductRow(actualProductRowsContainer, parseInt(index));
                });
                produkIndex = Object.keys(oldProducts).length;
            } else {
                initializeProductRow(actualProductRowsContainer, produkIndex);
                produkIndex++;
            }

            // --- Attach Event Listeners ---

            // "Tambah Produk" button handler
            addProductButton.addEventListener('click', function () {
                initializeProductRow(actualProductRowsContainer, produkIndex);
                produkIndex++;
            });

            // "Hapus" button handler (using event delegation on parent container)
            actualProductRowsContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-produk')) {
                    // Ensure at least one product row remains if 'products' is required
                    if (actualProductRowsContainer.children.length > 1) {
                        e.target.closest('.produk-item').remove();
                    } else {
                        alert('Minimal harus ada satu produk untuk transaksi.');
                    }
                }
            });
        }); // End of DOMContentLoaded

        // --- Disable Submit Button on Click (Global Function) ---
        function disableSubmitButton(form) {
            const submitButton = form.querySelector('#submit-button'); // Get button from the specific form
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Menyimpan...';
                // Also, trigger validation for selected products before final submit
                document.querySelectorAll('.produk-item div[x-data]').forEach(el => {
                    const alpineData = el._x_dataStack[0];
                    if (alpineData && typeof alpineData.clearIfInvalid === 'function') {
                        alpineData.clearIfInvalid();
                    }
                });
            }
        }
    </script>
@endpush
