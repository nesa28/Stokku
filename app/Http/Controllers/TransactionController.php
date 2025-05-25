<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    // Store - Menyimpan data baru
    public function store(Request $request)
    {
        try {
            // Validasi input untuk membuat transaksi baru
            $request->validate([
                'tanggal_transaksi' => 'required|date',
                'pelanggan' => 'nullable|string|max:255',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.jumlah' => 'required|integer|min:1',
                'products.*.jenis_penjualan' => 'required|in:satuan,eceran', // Validasi jenis penjualan
            ]);

            DB::beginTransaction();

            $totalTransaksiKeseluruhan = 0;
            $transactionDetailsData = [];

            $productIds = collect($request->products)->pluck('product_id')->unique();
            // Gunakan Products::class
            $products = Products::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($request->products as $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'products.*.product_id' => "Produk dengan ID {$item['product_id']} tidak ditemukan."
                    ]);
                }

                $kuantitasDijual = $item['jumlah'];
                $jenisPenjualan = $item['jenis_penjualan'];
                $hargaPerUnitFinal = 0;
                $stokYangDikurangi = 0; // Berapa yang harus dikurangi dari kolom 'stok' di tabel products

                // Penentuan Harga dan Pengurangan Stok
                if ($jenisPenjualan === 'satuan') {
                    $hargaPerUnitFinal = $product->harga_satuan;
                    $stokYangDikurangi = $kuantitasDijual;
                } elseif ($jenisPenjualan === 'eceran') {
                    // Gunakan 'bisa_atau_tdk_diecer'
                    if (!$product->bisa_atau_tdk_diecer || $product->unit_eceran <= 0) {
                        throw ValidationException::withMessages([
                            'products.*.jenis_penjualan' => "Produk '{$product->nama_produk}' tidak bisa dijual eceran."
                        ]);
                    }
                    $hargaPerUnitFinal = $product->harga_eceran_per_unit;
                    $stokYangDikurangi = $kuantitasDijual / $product->unit_eceran;
                } else {
                    throw ValidationException::withMessages([
                        'products.*.jenis_penjualan' => "Jenis penjualan tidak valid untuk produk '{$product->nama_produk}'."
                    ]);
                }

                // Cek stok sebelum mengurangi
                if ($product->stok < $stokYangDikurangi) {
                    throw ValidationException::withMessages([
                        'products.*.jumlah' => "Stok tidak cukup untuk produk: {$product->nama_produk}. Stok tersedia: {$product->stok} (satuan)."
                    ]);
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

                // Kurangi stok produk secara langsung
                $product->decrement('stok', $stokYangDikurangi);
            }

            $transaction = Transaction::create([
                'total_transaksi' => $totalTransaksiKeseluruhan,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'pelanggan' => $request->pelanggan,
                'user_id' => auth()->id(),
            ]);

            $transaction->details()->createMany($transactionDetailsData);

            DB::commit();

            return response()->json(['message' => 'Transaksi berhasil dicatat!', 'transaction' => $transaction->load('details.product')], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mencatat transaksi: ' . $e->getMessage()], 500);
        }
    }

    //Show - Menampilkan detail.
    public function show(Transaction $transaction)
    {
        return response()->json($transaction->load('details.product'));
    }

    // Update - Memperbarui data
    public function update(Request $request, Transaction $transaction)
    {
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

                // Penentuan Harga dan Pengurangan Stok untuk item BARU/DIUBAH
                if ($jenisPenjualanBaru === 'satuan') {
                    $hargaPerUnitFinalBaru = $product->harga_satuan;
                    $stokYangDikurangiBaru = $kuantitasDijualBaru;
                } elseif ($jenisPenjualanBaru === 'eceran') {
                    if (!$product->bisa_atau_tdk_diecer || $product->unit_eceran <= 0) {
                        throw ValidationException::withMessages([
                            'products.*.jenis_penjualan' => "Produk '{$product->nama_produk}' tidak bisa dijual eceran."
                        ]);
                    }
                    $hargaPerUnitFinalBaru = $product->harga_eceran_per_unit;
                    $stokYangDikurangiBaru = $kuantitasDijualBaru / $product->unit_eceran;
                }

                $subtotalItemBaru = $hargaPerUnitFinalBaru * $kuantitasDijualBaru;
                $totalTransaksiKeseluruhan += $subtotalItemBaru;

                $detailId = $item['id'] ?? null;
                $existingDetail = $existingDetails->get($detailId);

                if ($existingDetail) {
                    // Update item yang sudah ada
                    $stokYangDikembalikan = 0;

                    // Hitung berapa stok satuan yang terpakai oleh detail yang lama
                    //Products::find() untuk memastikan mengambil data produk yang benar sesuai detail lama
                    $productLama = Products::find($existingDetail->product_id);
                    if ($productLama) { // Pastikan produk lama masih ada
                        if ($existingDetail->jenis_penjualan === 'satuan') {
                            $stokYangDikembalikan = $existingDetail->jumlah;
                        } elseif ($existingDetail->jenis_penjualan === 'eceran' && $productLama->unit_eceran > 0) {
                            $stokYangDikembalikan = $existingDetail->jumlah / $productLama->unit_eceran;
                        }
                    }

                    // Kembalikan stok lama sebelum menghitung stok baru
                    $product->increment('stok', $stokYangDikembalikan);

                    // Cek stok baru
                    if ($product->stok < $stokYangDikurangiBaru) {
                        throw ValidationException::withMessages([
                            'products.*.jumlah' => "Stok tidak cukup untuk update produk '{$product->nama_produk}'. Stok tersedia: {$product->stok} (satuan)."
                        ]);
                    }
                    $product->decrement('stok', $stokYangDikurangiBaru);

                    $existingDetail->update([
                        'product_id' => $item['product_id'],
                        'jumlah' => $kuantitasDijualBaru,
                        'jenis_penjualan' => $jenisPenjualanBaru,
                        'harga_per_unit' => $hargaPerUnitFinalBaru,
                        'subtotal' => $subtotalItemBaru,
                    ]);
                    $updatedDetailIds[] = $existingDetail->id;

                } else {
                    // Tambah item baru
                    if ($product->stok < $stokYangDikurangiBaru) {
                        throw ValidationException::withMessages([
                            'products.*.jumlah' => "Stok tidak cukup untuk produk baru '{$product->nama_produk}'. Stok tersedia: {$product->stok} (satuan)."
                        ]);
                    }
                    $product->decrement('stok', $stokYangDikurangiBaru);

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
                    $stokYangDikembalikan = 0;
                    if ($detail->jenis_penjualan === 'satuan') {
                        $stokYangDikembalikan = $detail->jumlah;
                    } elseif ($detail->jenis_penjualan === 'eceran' && $product->unit_eceran > 0) {
                        $stokYangDikembalikan = $detail->jumlah / $product->unit_eceran;
                    }
                    $product->increment('stok', $stokYangDikembalikan);
                }
                $detail->delete();
            }

            // Update total transaksi utama
            $transaction->update([
                'total_transaksi' => $totalTransaksiKeseluruhan,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'pelanggan' => $request->pelanggan,
            ]);

            DB::commit();

            return response()->json(['message' => 'Transaksi berhasil diperbarui!', 'transaction' => $transaction->load('details.product')], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui transaksi: ' . $e->getMessage()], 500);
        }
    }

    // Destroy - Menghapus data
    public function destroy(Transaction $transaction)
    {
        try {
            DB::beginTransaction();

            // Sebelum menghapus transaksi dan detailnya, kembalikan stok produk
            foreach ($transaction->details as $detail) {

                $product = Products::find($detail->product_id);
                if ($product) {
                    $stokYangDikembalikan = 0;
                    if ($detail->jenis_penjualan === 'satuan') {
                        $stokYangDikembalikan = $detail->jumlah;
                    } elseif ($detail->jenis_penjualan === 'eceran' && $product->unit_eceran > 0) {
                        $stokYangDikembalikan = $detail->jumlah / $product->unit_eceran;
                    }
                    $product->increment('stok', $stokYangDikembalikan);
                }
            }

            $transaction->delete();

            DB::commit();
            return response()->json(['message' => 'Transaksi berhasil dihapus dan stok dikembalikan!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus transaksi: ' . $e->getMessage()], 500);
        }
    }
}
