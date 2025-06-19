<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;

// Controller untuk manajemen transaksi penjualan
class TransactionController extends Controller
{
    // Menampilkan daftar transaksi milik user
    public function index()
    {
        $transactions = Transaction::where('user_id', auth()->id())
            ->with(['details.product'])
            ->latest()
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    // Menampilkan form tambah transaksi
    public function create(): View
    {
        $products = Products::all();
        return view('transactions.create', compact('products'));
    }

    // Menyimpan transaksi baru
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'tanggal_transaksi' => 'required|date',
                'pelanggan' => 'nullable|string|max:255',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.jumlah' => 'required|integer|min:1',
                'products.*.jenis_penjualan' => 'required|in:satuan,eceran',
            ]);

            DB::beginTransaction();

            $totalTransaksiKeseluruhan = 0;
            $transactionDetailsData = [];

            $productIds = collect($request->products)->pluck('product_id')->unique();
            $products = Products::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($request->products as $index => $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        "products.{$index}.product_id" => "Produk dengan ID {$item['product_id']} tidak ditemukan."
                    ]);
                }

                $kuantitasDijual = $item['jumlah'];
                $jenisPenjualan = $item['jenis_penjualan'];
                $hargaPerUnitFinal = 0;
                $stokYangDikurangi = 0;

                if ($jenisPenjualan === 'satuan') {
                    $hargaPerUnitFinal = $product->harga_satuan;
                    $stokYangDikurangi = $kuantitasDijual;
                } elseif ($jenisPenjualan === 'eceran') {
                    if (!$product->bisa_atau_tdk_diecer) {
                        throw ValidationException::withMessages([
                            "products.{$index}.jenis_penjualan" => "Produk '{$product->nama_produk}' tidak bisa dijual eceran."
                        ]);
                    }
                    $hargaPerUnitFinal = $product->harga_eceran_per_unit;
                    $stokYangDikurangi = 0;
                } else {
                    throw ValidationException::withMessages([
                        "products.{$index}.jenis_penjualan" => "Jenis penjualan tidak valid untuk produk '{$product->nama_produk}'."
                    ]);
                }

                if ($stokYangDikurangi > 0) {
                    if ($product->stok < $stokYangDikurangi) {
                        throw ValidationException::withMessages([
                            "products.{$index}.jumlah" => "Stok tidak cukup untuk produk: {$product->nama_produk}. Stok tersedia: {$product->stok} (satuan)."
                        ]);
                    }
                    $product->decrement('stok', $stokYangDikurangi);
                }

                $subtotal = $hargaPerUnitFinal * $kuantitasDijual;
                $totalTransaksiKeseluruhan += $subtotal;

                $transactionDetailsData[] = [
                    'product_id' => $item['product_id'],
                    'jumlah' => $kuantitasDijual,
                    'jenis_penjualan' => $jenisPenjualan,
                    'harga_per_unit' => $hargaPerUnitFinal,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $transaction = Transaction::create([
                'total_transaksi' => $totalTransaksiKeseluruhan,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'pelanggan' => $request->pelanggan,
                'user_id' => auth()->id(),
            ]);

            $transaction->details()->createMany($transactionDetailsData);

            DB::commit();

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dicatat!');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat transaksi: ' . $e->getMessage());
        }
    }

    // Menampilkan detail transaksi tertentu
    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk melihat transaksi ini.');
        }
        $transaction->load('details.product');
        return view('transactions.show', compact('transaction'));
    }

    // Memperbarui data transaksi
    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk memperbarui transaksi ini.');
        }

        try {
            $request->validate([
                'tanggal_transaksi' => 'required|date',
                'pelanggan' => 'nullable|string|max:255',
                'products' => 'required|array',
                'products.*.id' => 'nullable|exists:transaction_details,id',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.jumlah' => 'required|integer|min:1',
                'products.*.jenis_penjualan' => 'required|in:satuan,eceran',
            ]);

            DB::beginTransaction();

            $totalTransaksiKeseluruhan = 0;
            $updatedDetailIds = [];

            $productIdsInRequest = collect($request->products)->pluck('product_id')->unique();
            $productsFromDb = Products::whereIn('id', $productIdsInRequest)->lockForUpdate()->get()->keyBy('id');

            $existingDetails = $transaction->details->keyBy('id');

            foreach ($request->products as $item) {
                $product = $productsFromDb->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'products.*.product_id' => "Produk dengan ID {$item['product_id']} tidak ditemukan."
                    ]);
                }

                $kuantitasDijualBaru = $item['jumlah'];
                $jenisPenjualanBaru = $item['jenis_penjualan'];
                $hargaPerUnitFinalBaru = 0;
                $stokYangDikurangiBaru = 0;

                if ($jenisPenjualanBaru === 'satuan') {
                    $hargaPerUnitFinalBaru = $product->harga_satuan;
                    $stokYangDikurangiBaru = $kuantitasDijualBaru;
                } elseif ($jenisPenjualanBaru === 'eceran') {
                    if (!$product->bisa_atau_tdk_diecer) {
                        throw ValidationException::withMessages([
                            'products.*.jenis_penjualan' => "Produk '{$product->nama_produk}' tidak bisa dijual eceran."
                        ]);
                    }
                    $hargaPerUnitFinalBaru = $product->harga_eceran_per_unit;
                    $stokYangDikurangiBaru = 0;
                } else {
                    throw ValidationException::withMessages([
                        'products.*.jenis_penjualan' => "Jenis penjualan tidak valid untuk produk '{$product->nama_produk}'."
                    ]);
                }

                $subtotalItemBaru = $hargaPerUnitFinalBaru * $kuantitasDijualBaru;
                $totalTransaksiKeseluruhan += $subtotalItemBaru;

                $detailId = $item['id'] ?? null;
                $existingDetail = $existingDetails->get($detailId);

                if ($existingDetail) {
                    // Kembalikan stok lama jika jenis penjualan sebelumnya satuan
                    $stokYangDikembalikanLama = $existingDetail->jenis_penjualan === 'satuan' ? $existingDetail->jumlah : 0;
                    if ($stokYangDikembalikanLama > 0) {
                        $product->increment('stok', $stokYangDikembalikanLama);
                    }

                    // Cek stok baru
                    if ($stokYangDikurangiBaru > 0 && $product->stok < $stokYangDikurangiBaru) {
                        throw ValidationException::withMessages([
                            'products.*.jumlah' => "Stok tidak cukup untuk update produk '{$product->nama_produk}'. Stok tersedia: {$product->stok} (satuan)."
                        ]);
                    }
                    if ($stokYangDikurangiBaru > 0) {
                        $product->decrement('stok', $stokYangDikurangiBaru);
                    }

                    $existingDetail->update([
                        'product_id' => $item['product_id'],
                        'jumlah' => $kuantitasDijualBaru,
                        'jenis_penjualan' => $jenisPenjualanBaru,
                        'harga_per_unit' => $hargaPerUnitFinalBaru,
                        'subtotal' => $subtotalItemBaru,
                    ]);
                    $updatedDetailIds[] = $existingDetail->id;

                } else {
                    if ($stokYangDikurangiBaru > 0 && $product->stok < $stokYangDikurangiBaru) {
                        throw ValidationException::withMessages([
                            'products.*.jumlah' => "Stok tidak cukup untuk produk baru '{$product->nama_produk}'. Stok tersedia: {$product->stok} (satuan)."
                        ]);
                    }
                    if ($stokYangDikurangiBaru > 0) {
                        $product->decrement('stok', $stokYangDikurangiBaru);
                    }

                    $newDetail = $transaction->details()->create([
                        'product_id' => $item['product_id'],
                        'jumlah' => $kuantitasDijualBaru,
                        'jenis_penjualan' => $jenisPenjualanBaru,
                        'harga_per_unit' => $hargaPerUnitFinalBaru,
                        'subtotal' => $subtotalItemBaru,
                    ]);
                    $updatedDetailIds[] = $newDetail->id;
                }
            }

            // Hapus detail yang tidak ada lagi di request dan kembalikan stok
            $detailsToDelete = $existingDetails->whereNotIn('id', $updatedDetailIds);
            foreach ($detailsToDelete as $detail) {
                $product = Products::find($detail->product_id);
                if ($product) {
                    $stokYangDikembalikan = $detail->jenis_penjualan === 'satuan' ? $detail->jumlah : 0;
                    if ($stokYangDikembalikan > 0) {
                        $product->increment('stok', $stokYangDikembalikan);
                    }
                }
                $detail->delete();
            }

            // Update transaksi utama
            $transaction->update([
                'total_transaksi' => $totalTransaksiKeseluruhan,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'pelanggan' => $request->pelanggan,
            ]);

            DB::commit();

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui!');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }

    // Menghapus transaksi dan mengembalikan stok
    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus transaksi ini.');
        }

        try {
            DB::beginTransaction();

            foreach ($transaction->details as $detail) {
                $product = Products::find($detail->product_id);
                if ($product) {
                    $stokYangDikembalikan = $detail->jenis_penjualan === 'satuan' ? $detail->jumlah : 0;
                    if ($stokYangDikembalikan > 0) {
                        $product->increment('stok', $stokYangDikembalikan);
                    }
                }
            }

            $transaction->delete();

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus dan stok dikembalikan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    // Pencarian transaksi milik user
    public function search(Request $request): View
    {
        $searchValue = trim($request->search ?? '');

        $transactions = Transaction::where('user_id', auth()->id())
            ->with(['user', 'details.product'])
            ->when(!empty($searchValue), function ($query) use ($searchValue) {
                if (ctype_digit($searchValue)) {
                    $query->where('id', (int) $searchValue);
                } else {
                    $query->orWhereHas('user', function ($q) use ($searchValue) {
                        $q->where('name', 'ILIKE', '%' . $searchValue . '%');
                    });
                    $query->orWhereHas('details.product', function ($q) use ($searchValue) {
                        $q->where('nama_produk', 'ILIKE', '%' . $searchValue . '%');
                    });
                }
            })
            ->latest()
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }
}
