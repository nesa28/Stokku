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

// Controller untuk manajemen restock barang
class RestockController extends Controller
{
    // Menampilkan daftar restock milik user
    public function index(): View
    {
        $restocks = Restock::where('user_id', auth()->id())
            ->with(['user', 'details.product'])
            ->latest()
            ->paginate(10);

        return view('restocks.index', compact('restocks'));
    }

    // Menampilkan form tambah restock
    public function create()
    {
        $allproducts = Products::all();
        return view('restocks.create', compact('allproducts'));
    }

    // Menyimpan data restock baru (bisa banyak produk)
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'tanggal_restock' => 'required|date',
                'supplier' => 'nullable|string|max:255',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.jumlah' => 'required|integer|min:1',
                'products.*.harga_beli_per_unit' => 'required|numeric|min:0',
            ]);

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
                $product->stok += $item['jumlah'];
                $product->save();
            }

            // Buat record restock utama
            $restock = Restock::create([
                'total_harga_beli' => $totalHargaBeliKeseluruhan,
                'tanggal_restock' => $request->tanggal_restock,
                'supplier' => $request->supplier,
                'user_id' => Auth::id(),
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

    // Menampilkan detail restock tertentu
    public function show(Restock $restock): View
    {
        if ($restock->user_id !== auth()->id()) {
            abort(403);
        }
        $restock->load('details.product');
        return view('restocks.show', compact('restock'));
    }

    // Menampilkan form edit restock
    public function edit(Restock $restock): View
    {
        if ($restock->user_id !== auth()->id()) {
            abort(403);
        }
        $products = Products::all();
        return view('restocks.edit', compact('restock', 'products'));
    }

    // Memperbarui data restock (bisa banyak produk)
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
                'products.*.id' => 'nullable|exists:restock_details,id',
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
                    // Update stok jika jumlah berubah
                    $jumlahLama = $existingDetail->jumlah;
                    $selisihJumlah = $item['jumlah'] - $jumlahLama;

                    if ($selisihJumlah > 0) {
                        $product->increment('stok', $selisihJumlah);
                    } elseif ($selisihJumlah < 0) {
                        $stokTerkurangi = abs($selisihJumlah);
                        if ($product->stok >= $stokTerkurangi) {
                            $product->decrement('stok', $stokTerkurangi);
                        } else {
                            $product->stok = 0;
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
                    // Tambah detail baru dan update stok
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

            // Hapus detail lama yang tidak ada di request dan kurangi stok
            $detailsToDelete = $existingDetails->whereNotIn('id', $updatedDetailIds);
            foreach ($detailsToDelete as $detail) {
                $product = Products::find($detail->product_id);
                if ($product) {
                    if ($product->stok >= $detail->jumlah) {
                        $product->decrement('stok', $detail->jumlah);
                    } else {
                        $product->stok = 0;
                        $product->save();
                    }
                }
                $detail->delete();
            }

            // Update restock utama
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

    // Menghapus data restock dan mengurangi stok produk
    public function destroy(Restock $restock): RedirectResponse
    {
        try {
            if ($restock->user_id !== auth()->id()) {
                abort(403);
            }
            DB::beginTransaction();

            foreach ($restock->details as $detail) {
                $product = Products::find($detail->product_id);
                if ($product) {
                    if ($product->stok >= $detail->jumlah) {
                        $product->decrement('stok', $detail->jumlah);
                    } else {
                        $product->stok = 0;
                        $product->save();
                    }
                }
            }

            $restock->delete();

            DB::commit();
            return redirect()->route('restocks.index')
                ->with('success', 'Riwayat restock berhasil dihapus dan stok dikurangi!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus restock: ' . $e->getMessage());
        }
    }

    // Pencarian restock milik user
    public function search(Request $request): View
    {
        $searchValue = trim($request->search ?? '');

        $restocks = Restock::where('user_id', auth()->id())
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
