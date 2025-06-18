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

class TransactionController extends Controller
{
    public function index()
    {
        // Hanya mengambil transaksi yang dibuat oleh pengguna yang sedang login
        $transactions = Transaction::where('user_id', auth()->id()) // <-- Perubahan penting di sini
            ->with(['details.product'])
            ->latest()
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    // Create - Menampilkan form tambah data
    public function create(): View
    {
        $products = Products::all();
        return view('transactions.create', compact('products'));
    }

    // Store - Menyimpan data baru
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
                $stokYangDikurangi = 0; // Default 0, hanya berubah untuk satuan

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
                    $stokYangDikurangi = 0; // Stok tidak dikurangi untuk penjualan eceran
                } else {
                    throw ValidationException::withMessages([
                        "products.{$index}.jenis_penjualan" => "Jenis penjualan tidak valid untuk produk '{$product->nama_produk}'."
                    ]);
                }

                if ($stokYangDikurangi > 0) { // Hanya cek stok jika ada yang akan dikurangi
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
                'user_id' => auth()->id(), // user_id dari yang sedang login
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

    //Show - Menampilkan detail.
    public function show(Transaction $transaction)
    {
        // Pastikan hanya pemilik transaksi yang bisa melihat
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk melihat transaksi ini.'); // Forbidden
        }
        // return response()->json($transaction->load('details.product')); // Jika ini untuk API
        // Jika ini untuk view Blade, seharusnya:
        $transaction->load('details.product');
        return view('transactions.show', compact('transaction'));
    }

    // Update - Memperbarui data
    public function update(Request $request, Transaction $transaction)
    {
        // Pastikan hanya pemilik transaksi yang bisa memperbarui
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
                $stokYangDikurangiBaru = 0; // Inisialisasi

                // Penentuan Harga dan Pengurangan Stok untuk item BARU/DIUBAH
                if ($jenisPenjualanBaru === 'satuan') {
                    $hargaPerUnitFinalBaru = $product->harga_satuan;
                    $stokYangDikurangiBaru = $kuantitasDijualBaru;
                } elseif ($jenisPenjualanBaru === 'eceran') {
                    if (!$product->bisa_atau_tdk_diecer) { // Cukup cek bisa_atau_tdk_diecer, tidak perlu unit_eceran
                        throw ValidationException::withMessages([
                            'products.*.jenis_penjualan' => "Produk '{$product->nama_produk}' tidak bisa dijual eceran."
                        ]);
                    }
                    $hargaPerUnitFinalBaru = $product->harga_eceran_per_unit;
                    $stokYangDikurangiBaru = 0; // Stok tidak dikurangi untuk penjualan eceran BARU
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
                    // --- Logika Update Item yang Sudah Ada ---
                    $stokYangDikembalikanLama = 0;

                    // Dapatkan stok yang seharusnya dikembalikan dari detail lama
                    if ($existingDetail->jenis_penjualan === 'satuan') {
                        $stokYangDikembalikanLama = $existingDetail->jumlah;
                    } elseif ($existingDetail->jenis_penjualan === 'eceran') {
                        // Jika jenis penjualan lama adalah eceran, dan kita tidak mengurangi stok untuk eceran,
                        // maka tidak ada stok yang perlu dikembalikan.
                        $stokYangDikembalikanLama = 0; // <--- Perubahan di sini untuk konsistensi eceran
                    }

                    // Kembalikan stok lama ke produk (jika ada)
                    if ($stokYangDikembalikanLama > 0) {
                        $product->increment('stok', $stokYangDikembalikanLama);
                    }

                    // Cek stok baru setelah dikembalikan
                    if ($stokYangDikurangiBaru > 0 && $product->stok < $stokYangDikurangiBaru) {
                        throw ValidationException::withMessages([
                            'products.*.jumlah' => "Stok tidak cukup untuk update produk '{$product->nama_produk}'. Stok tersedia: {$product->stok} (satuan)."
                        ]);
                    }
                    // Kurangi stok dengan jumlah baru (jika $stokYangDikurangiBaru > 0)
                    if ($stokYangDikurangiBaru > 0) {
                        $product->decrement('stok', $stokYangDikurangiBaru);
                    }

                    // Update detail transaksi
                    $existingDetail->update([
                        'product_id' => $item['product_id'],
                        'jumlah' => $kuantitasDijualBaru,
                        'jenis_penjualan' => $jenisPenjualanBaru,
                        'harga_per_unit' => $hargaPerUnitFinalBaru,
                        'subtotal' => $subtotalItemBaru,
                    ]);
                    $updatedDetailIds[] = $existingDetail->id;

                } else {
                    // --- Logika Tambah Item Baru ---
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
                    $stokYangDikembalikan = 0;
                    if ($detail->jenis_penjualan === 'satuan') {
                        $stokYangDikembalikan = $detail->jumlah;
                    } elseif ($detail->jenis_penjualan === 'eceran') {
                        // Jika detail yang dihapus adalah eceran, tidak ada stok yang dikembalikan
                        $stokYangDikembalikan = 0; // <--- Perubahan di sini untuk konsistensi eceran
                    }
                    if ($stokYangDikembalikan > 0) {
                        $product->increment('stok', $stokYangDikembalikan);
                    }
                }
                $detail->delete();
            }

            // Update total transaksi utama
            $transaction->update([
                'total_transaksi' => $totalTransaksiKeseluruhan,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'pelanggan' => $request->pelanggan,
                // user_id tidak diupdate karena hanya dibuat saat store
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

    // Destroy - Menghapus data
    public function destroy(Transaction $transaction)
    {
        // Pastikan hanya pemilik transaksi yang bisa menghapus
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus transaksi ini.');
        }

        try {
            DB::beginTransaction();

            // Sebelum menghapus transaksi dan detailnya, kembalikan stok produk
            foreach ($transaction->details as $detail) {
                $product = Products::find($detail->product_id);
                if ($product) {
                    $stokYangDikembalikan = 0;
                    if ($detail->jenis_penjualan === 'satuan') {
                        $stokYangDikembalikan = $detail->jumlah;
                    } elseif ($detail->jenis_penjualan === 'eceran') {
                        // Jika jenis penjualan adalah eceran, tidak ada stok yang dikembalikan ke produk utama
                        $stokYangDikembalikan = 0; // <--- Perubahan di sini untuk konsistensi eceran
                    }
                    if ($stokYangDikembalikan > 0) {
                        $product->increment('stok', $stokYangDikembalikan);
                    }
                }
            }

            $transaction->delete(); // Hapus transaksi beserta detailnya (jika ada cascade delete di DB atau model)

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus dan stok dikembalikan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    public function search(Request $request): View
    {
        $searchValue = trim($request->search ?? '');

        // Hanya mencari transaksi yang dibuat oleh pengguna yang sedang login
        $transactions = Transaction::where('user_id', auth()->id()) // <-- Perubahan penting di sini
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
