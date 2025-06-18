<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Restock;
use App\Models\RestockDetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Untuk transaksi database
use Illuminate\Validation\ValidationException; // Untuk penanganan validasi
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RestockController extends Controller
{
    // Index - Menampilkan semua data restock utama dengan detailnya
    public function index(): View
    {
        // Only fetch restocks created by the currently authenticated user
        $restocks = Restock::where('user_id', auth()->id())
            ->with(['user', 'details.product'])
            ->latest()
            ->paginate(10);

        return view('restocks.index', compact('restocks'));
    }

    // Create - Menampilkan form tambah data
    public function create()
    {
        $allproducts = Products::all(); // Ambil semua produk
        return view('restocks.create', compact('allproducts'));
    }

    // Store - Menyimpan data restock baru (bisa banyak produk)
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'tanggal_restock' => 'required|date',
                'supplier' => 'nullable|string|max:255',
                'products' => 'required|array|min:1', // Harus ada setidaknya satu produk
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.jumlah' => 'required|integer|min:1',
                'products.*.harga_beli_per_unit' => 'required|numeric|min:0',

            ]);

            // Create the restock, ensuring user_id is set
            $restock = Restock::create(array_merge($validatedData, [
                'user_id' => auth()->id(), // <-- Add this line
            ]));
            DB::beginTransaction();

            $totalHargaBeliKeseluruhan = 0;
            $restockDetailsData = [];

            $productIds = collect($request->products)->pluck('product_id')->unique();
            $products = Products::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($request->products as $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        "products.{$item['product_id']}.product_id" => "Produk dengan ID {$item['product_id']} tidak ditemukan."
                    ]);
                }

                $subtotalItem = $item['jumlah'] * $item['harga_beli_per_unit'];
                $totalHargaBeliKeseluruhan += $subtotalItem;

                $restockDetailsData[] = [
                    'product_id' => $item['product_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_beli_per_unit' => $item['harga_beli_per_unit'],
                    'subtotal_harga_beli' => $subtotalItem,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                // Update stok produk
                $product = Products::find($item['product_id']);
                if ($product) {
                    $product->stok += $item['jumlah'];
                    $product->save();
                }

                // Jika produk tidak ditemukan, lemparkan exception
                if (!$product) {
                    throw ValidationException::withMessages([
                        "products.{$item['product_id']}.product_id" => "Produk dengan ID {$item['product_id']} tidak ditemukan."
                    ]);
                }

            }

            // Buat record restock utama
            $restock = Restock::create([
                'total_harga_beli' => $totalHargaBeliKeseluruhan,
                'tanggal_restock' => $request->tanggal_restock,
                'supplier' => $request->supplier,
                'user_id' => Auth::id(), // User yang sedang login

            ]);

            // Simpan detail restock
            $restock->details()->createMany($restockDetailsData);

            DB::commit();

            return redirect()->route('restocks.index')
                ->with('success', 'Restock berhasil dicatat!');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat restock: ' . $e->getMessage())->withInput();
        }
    }

    // Show - Menampilkan detail restock tertentu
    public function show(Restock $restock): View
    {
        if ($restock->user_id !== auth()->id()) {
            abort(403);
        }
        $restock->load('details.product');
        return view('restocks.show', compact('restock'));
    }

    // Edit - Menampilkan form edit
    public function edit(Restock $restock): View
    {
        if ($restock->user_id !== auth()->id()) {
            abort(403);
        }
        $products = Products::all(); // Assuming edit needs products for dropdown
        return view('restocks.edit', compact('restock', 'products'));
    }

    // Update - Memperbarui data restock (bisa banyak produk)
    public function update(Request $request, Restock $restock): RedirectResponse
    {
        try {
            if ($restock->user_id !== auth()->id()) {
                abort(403);
            }
            $request->validate([
                'tanggal_restock' => 'required|date',
                'supplier' => 'nullable|string|max:255',
                'products' => 'required|array|min:1',
                'products.*.id' => 'nullable|exists:restock_details,id', // ID detail restock yang sudah ada
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.jumlah' => 'required|integer|min:1',
                'products.*.harga_beli_per_unit' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            $totalHargaBeliKeseluruhan = 0;
            $updatedDetailIds = [];

            $productIdsInRequest = collect($request->products)->pluck('product_id')->unique();
            $productsFromDb = Products::whereIn('id', $productIdsInRequest)->lockForUpdate()->get()->keyBy('id');

            $existingDetails = $restock->details->keyBy('id');

            foreach ($request->products as $item) {
                $product = $productsFromDb->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        "products.{$item['product_id']}.product_id" => "Produk dengan ID {$item['product_id']} tidak ditemukan."
                    ]);
                }

                $subtotalItem = $item['jumlah'] * $item['harga_beli_per_unit'];
                $totalHargaBeliKeseluruhan += $subtotalItem;

                $detailId = $item['id'] ?? null;
                $existingDetail = $existingDetails->get($detailId);

                if ($existingDetail) {
                    // Item sudah ada, update kuantitas dan harga
                    $jumlahLama = $existingDetail->jumlah;
                    $selisihJumlah = $item['jumlah'] - $jumlahLama;

                    if ($selisihJumlah > 0) {
                        // Jumlah restock bertambah, tambahkan stok
                        $product->increment('stok', $selisihJumlah);
                    } elseif ($selisihJumlah < 0) {
                        // Jumlah restock berkurang, kurangi stok
                        // Pastikan stok tidak menjadi negatif
                        $stokTerkurangi = abs($selisihJumlah);
                        if ($product->stok >= $stokTerkurangi) {
                            $product->decrement('stok', $stokTerkurangi);
                        } else {
                            $product->stok = 0; // Jika tidak cukup, set ke 0
                            $product->save();
                        }
                    }

                    $existingDetail->update([
                        'product_id' => $item['product_id'],
                        'jumlah' => $item['jumlah'],
                        'harga_beli_per_unit' => $item['harga_beli_per_unit'],
                        'subtotal_harga_beli' => $subtotalItem,
                    ]);
                    $updatedDetailIds[] = $existingDetail->id;

                } else {
                    // PENAMBAHAN STOK SAAT MENAMBAH DETAIL BARU
                    $product->increment('stok', $item['jumlah']);
                    $newDetail = $restock->details()->create([
                        'product_id' => $item['product_id'],
                        'jumlah' => $item['jumlah'],
                        'harga_beli_per_unit' => $item['harga_beli_per_unit'],
                        'subtotal_harga_beli' => $subtotalItem,
                    ]);
                    $updatedDetailIds[] = $newDetail->id;
                }
            }

            //PENGURANGAN STOK SAAT MENGHAPUS DETAIL LAMA
            $detailsToDelete = $existingDetails->whereNotIn('id', $updatedDetailIds);
            foreach ($detailsToDelete as $detail) {
                $product = Products::find($detail->product_id); // Gunakan Products::find() agar selalu mendapatkan instance terbaru
                if ($product) {
                    // Kurangi stok yang sebelumnya ditambahkan oleh detail ini
                    if ($product->stok >= $detail->jumlah) {
                        $product->decrement('stok', $detail->jumlah);
                    } else {
                        $product->stok = 0; // Jika stok tidak cukup, set ke 0
                        $product->save();
                    }
                }
                $detail->delete();
            }

            // Update record restock utama
            $restock->update([
                'total_harga_beli' => $totalHargaBeliKeseluruhan,
                'tanggal_restock' => $request->tanggal_restock,
                'supplier' => $request->supplier,
                'user_id' => Auth::id(),
                'catatan' => $request->catatan,
            ]);

            DB::commit();

            return redirect()->route('restocks.index')
                ->with('success', 'Restock berhasil diupdate!');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui restock: ' . $e->getMessage())->withInput();
        }
    }

    // Destroy - Menghapus data restock
    public function destroy(Restock $restock): RedirectResponse
    {
        try {
            if ($restock->user_id !== auth()->id()) {
                abort(403);
            }
            DB::beginTransaction();

            //LOGIKA PENGURANGAN STOK SAAT MENGHAPUS RESTOCK UTAMA
            // Sebelum menghapus restock dan detailnya, kurangi stok produk
            foreach ($restock->details as $detail) {
                $product = Products::find($detail->product_id); // Gunakan Products::find()
                if ($product) {
                    // Kurangi stok yang sebelumnya ditambahkan
                    if ($product->stok >= $detail->jumlah) {
                        $product->decrement('stok', $detail->jumlah);
                    } else {
                        $product->stok = 0; // Jika stok tidak cukup, set ke 0
                        $product->save();
                    }
                }
            }

            $restock->delete(); // Ini akan menghapus restock utama dan detailnya karena onDelete('cascade')

            DB::commit();
            return redirect()->route('restocks.index')
                ->with('success', 'Riwayat restock berhasil dihapus dan stok dikurangi!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus restock: ' . $e->getMessage());
        }
    }

    // New search function
    public function search(Request $request): View
    {
        $searchValue = trim($request->search ?? '');

        // Only search restocks created by the currently authenticated user
        $restocks = Restock::where('user_id', auth()->id()) // <-- Added this line
            ->with(['user', 'details.product'])
            ->when(ctype_digit($searchValue), function ($query) use ($searchValue) {
                $query->where('id', (int) $searchValue);
            })
            ->when(!ctype_digit($searchValue) && !empty($searchValue), function ($query) use ($searchValue) {
                $query->where('supplier', 'ILIKE', '%' . $searchValue . '%');
                $query->orWhereHas('details.product', function ($q) use ($searchValue) {
                    $q->where('nama_produk', 'ILIKE', '%' . $searchValue . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('restocks.index', compact('restocks'));
    }
}

