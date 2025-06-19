<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Activity; // Import the Activity model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth; // Import Auth facade

// Controller untuk manajemen transaksi penjualan
class TransactionController extends Controller
{
    // Menampilkan daftar transaksi milik user
    public function index(Request $request): View // Accept Request to get sort_by parameter
    {
        $searchValue = trim($request->input('search') ?? '');
        $sortBy = $request->input('sort_by', 'latest'); // Default sort to 'latest'

        $transactions = Transaction::where('user_id', Auth::id())
            ->with(['details.product'])
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
            });

        // Apply sorting logic
        switch ($sortBy) {
            case 'oldest':
                $transactions->orderBy('created_at', 'asc');
                break;
            case 'id_asc':
                $transactions->orderBy('id', 'asc');
                break;
            case 'id_desc':
                $transactions->orderBy('id', 'desc');
                break;
            case 'latest': // Default
            default:
                $transactions->orderBy('created_at', 'desc');
                break;
        }

        $transactions = $transactions->paginate(10); // Apply pagination after all filters/sorts

        return view('transactions.index', compact('transactions'));
    }

    // Menampilkan form tambah transaksi
    public function create(): View
    {
        $products = Products::where('user_id', Auth::id())->get();
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
                    $stokYangDikurangi = 0; // Stok tidak dikurangi untuk penjualan eceran
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
                'user_id' => Auth::id(), // Ensure consistency with Auth::id()
            ]);

            $transaction->details()->createMany($transactionDetailsData);

            // --- Record Activity: Create Transaction ---
            $this->recordActivity('create', $transaction);
            // --- End Record Activity ---

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

    public function edit(Transaction $transaction): View
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }
        $products = Products::all(); // Fetches all products for the dropdowns
        return view('transactions.edit', compact('transaction', 'products'));
    }

    // Menampilkan detail transaksi tertentu
    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) { // Ensure consistency with Auth::id()
            abort(403, 'Anda tidak memiliki akses untuk melihat transaksi ini.');
        }
        $transaction->load('details.product');
        return view('transactions.show', compact('transaction'));
    }

    // Memperbarui data transaksi
    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) { // Ensure consistency with Auth::id()
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

            foreach ($request->products as $index => $item) { // Added $index for error mapping
                $product = $productsFromDb->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        "products.{$index}.product_id" => "Produk dengan ID {$item['product_id']} tidak ditemukan."
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
                            "products.{$index}.jenis_penjualan" => "Produk '{$product->nama_produk}' tidak bisa dijual eceran."
                        ]);
                    }
                    $hargaPerUnitFinalBaru = $product->harga_eceran_per_unit;
                    $stokYangDikurangiBaru = 0;
                } else {
                    throw ValidationException::withMessages([
                        "products.{$index}.jenis_penjualan" => "Jenis penjualan tidak valid untuk produk '{$product->nama_produk}'."
                    ]);
                }

                $subtotalItemBaru = $hargaPerUnitFinalBaru * $kuantitasDijualBaru;
                $totalTransaksiKeseluruhan += $subtotalItemBaru;

                $detailId = $item['id'] ?? null;
                $existingDetail = $existingDetails->get($detailId);

                if ($existingDetail) {
                    // Calculate stock adjustment needed for existing item
                    $stokYangDikembalikanLama = $existingDetail->jenis_penjualan === 'satuan' ? $existingDetail->jumlah : 0;
                    $stokYangDikeluarkanBaru = $stokYangDikurangiBaru; // This is the new deduction based on new item type/qty

                    // First, return the old stock (if any was deducted)
                    if ($stokYangDikembalikanLama > 0) {
                        $product->increment('stok', $stokYangDikembalikanLama);
                    }

                    // Then, check and deduct the new stock
                    if ($stokYangDikeluarkanBaru > 0) {
                        if ($product->stok < $stokYangDikeluarkanBaru) {
                            throw ValidationException::withMessages([
                                "products.{$index}.jumlah" => "Stok tidak cukup untuk update produk '{$product->nama_produk}'. Stok tersedia: {$product->stok} (satuan)."
                            ]);
                        }
                        $product->decrement('stok', $stokYangDikeluarkanBaru);
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
                    // Tambah item baru
                    if ($stokYangDikurangiBaru > 0) {
                        if ($product->stok < $stokYangDikurangiBaru) {
                            throw ValidationException::withMessages([
                                "products.{$index}.jumlah" => "Stok tidak cukup untuk produk baru '{$product->nama_produk}'. Stok tersedia: {$product->stok} (satuan)."
                            ]);
                        }
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

            // --- Record Activity: Update Transaction ---
            $this->recordActivity('update', $transaction);
            // --- End Record Activity ---

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
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus transaksi ini.');
        }

        try {
            DB::beginTransaction();

            // Loop through details to return stock before deletion
            foreach ($transaction->details as $detail) {
                $product = Products::find($detail->product_id);
                if ($product) {
                    // Only return stock if it was a 'satuan' sale
                    $stokYangDikembalikan = $detail->jenis_penjualan === 'satuan' ? $detail->jumlah : 0;
                    if ($stokYangDikembalikan > 0) {
                        $product->increment('stok', $stokYangDikembalikan);
                    }
                }
            }

            // Get ID before deletion for activity log
            $transactionId = $transaction->id;

            $transaction->delete(); // This will also delete details if cascade is set up in migration or model

            // --- Record Activity: Delete Transaction ---
            $this->recordActivity('delete', $transaction, 'Deleted Transaction: ID ' . $transactionId);
            // --- End Record Activity ---

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
        return $this->index($request);
    }

    // --- Private method to record activity for Transactions ---
    private function recordActivity($type, $transaction, $description = null)
    {
        // Get the list of products involved for more detailed logging
        $productNames = $transaction->details->map(function ($detail) {
            return $detail->product->nama_produk . ' (' . $detail->jumlah . ' ' . $detail->jenis_penjualan . ')';
        })->implode(', ');

        Activity::create([
            'type' => $type,
            'description' => $description ?? ucfirst($type) . ' transaction: ID ' . $transaction->id . ' (Pelanggan: ' . ($transaction->pelanggan ?? 'Umum') . ') - Produk: ' . $productNames,
            'model_type' => 'Transaction',
            'model_id' => $transaction->id,
            'user_id' => Auth::id(),
            'details' => [
                'transaction_id' => $transaction->id,
                'total_transaksi' => $transaction->total_transaksi,
                'tanggal_transaksi' => $transaction->tanggal_transaksi,
                'pelanggan' => $transaction->pelanggan,
                'products_involved' => $productNames,
                'action' => $type,
                'timestamp' => now()
            ]
        ]);
    }
}
