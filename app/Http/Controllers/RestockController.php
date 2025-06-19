<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Restock;
use App\Models\RestockDetail; // Make sure RestockDetail is imported
use App\Models\Activity; // Make sure Activity is imported
use App\Models\User; // Make sure User is imported
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

// Controller untuk manajemen restock barang
class RestockController extends Controller
{
    // Menampilkan daftar restock milik user
    public function index(Request $request): View // Accept Request to get sort_by parameter
    {
        $searchValue = trim($request->input('search') ?? '');
        $sortBy = $request->input('sort_by', 'latest'); // Default sort to 'latest'

        $restocks = Restock::where('user_id', Auth::id())
            ->with(['user', 'details.product'])
            ->when(!empty($searchValue), function ($query) use ($searchValue) {
                if (ctype_digit($searchValue)) {
                    $query->where('id', (int) $searchValue);
                } else {
                    $query->where('supplier', 'ILIKE', '%' . $searchValue . '%');
                    $query->orWhereHas('details.product', function ($q) use ($searchValue) {
                        $q->where('nama_produk', 'ILIKE', '%' . $searchValue . '%');
                    });
                }
            });

        // Apply sorting logic
        switch ($sortBy) {
            case 'oldest':
                $restocks->orderBy('created_at', 'asc');
                break;
            case 'id_asc':
                $restocks->orderBy('id', 'asc');
                break;
            case 'id_desc':
                $restocks->orderBy('id', 'desc');
                break;
            case 'latest': // Default
            default:
                $restocks->orderBy('created_at', 'desc');
                break;
        }

        $restocks = $restocks->paginate(10); // Apply pagination after all filters/sorts

        return view('restocks.index', compact('restocks'));
    }


    // Menampilkan form tambah restock
    public function create(): View
    {
        $products = Products::where('user_id', Auth::id())->get(); // Changed to $products for consistency with view
        return view('restocks.create', compact('products')); // Pass $products
    }

    // Menyimpan data restock baru (bisa banyak produk)
    public function store(Request $request): RedirectResponse
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

            foreach ($request->products as $index => $item) { // Added $index
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        "products.{$index}.product_id" => "Produk dengan ID {$item['product_id']} tidak ditemukan." // Use $index
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

                // Update stok produk: tambahkan jumlah restock
                $product->increment('stok', $item['jumlah']); // Use increment
            }

            // Create the main restock record AFTER stock updates and total calculation
            // This ensures total_harga_beli is accurate and user_id is set
            $restock = Restock::create([
                'total_harga_beli' => $totalHargaBeliKeseluruhan,
                'tanggal_restock' => $request->tanggal_restock,
                'supplier' => $request->supplier,
                'user_id' => Auth::id(), // Assign user_id here
            ]);

            // Simpan detail restock
            $restock->details()->createMany($restockDetailsData);

            // --- Record Activity: Create Restock ---
            $this->recordActivity('create', $restock);
            // --- End Record Activity ---

            DB::commit();

            return redirect()->route('restocks.index')->with('success', 'Restock berhasil dicatat!');

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
        if ($restock->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk melihat restock ini.');
        }
        $restock->load('details.product');
        return view('restocks.show', compact('restock'));
    }

    // Menampilkan form edit restock
    public function edit(Restock $restock): View
    {
        if ($restock->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit restock ini.');
        }
        $products = Products::all();
        return view('restocks.edit', compact('restock', 'products'));
    }

    // Memperbarui data restock (bisa banyak produk)
    public function update(Request $request, Restock $restock): RedirectResponse
    {
        if ($restock->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk memperbarui restock ini.');
        }

        try {
            $validatedData = $request->validate([
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

            foreach ($request->products as $index => $item) { // Added $index
                $product = $productsFromDb->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        "products.{$index}.product_id" => "Produk dengan ID {$item['product_id']} tidak ditemukan." // Use $index
                    ]);
                }

                $subtotalItem = $item['jumlah'] * $item['harga_beli_per_unit'];
                $totalHargaBeliKeseluruhan += $subtotalItem;

                $detailId = $item['id'] ?? null;
                $existingDetail = $existingDetails->get($detailId);

                if ($existingDetail) {
                    // Update item yang sudah ada
                    $jumlahLama = $existingDetail->jumlah;
                    $selisihJumlah = $item['jumlah'] - $jumlahLama;

                    // Adjust stock based on the difference in quantity
                    if ($selisihJumlah !== 0) { // Only adjust if there's a difference
                        $product->increment('stok', $selisihJumlah); // increment handles positive and negative (decrement)
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
                    $product->increment('stok', $item['jumlah']); // Increment stock for new items
                    $newDetail = $restock->details()->create([
                        'product_id' => $item['product_id'],
                        'jumlah' => $item['jumlah'],
                        'harga_beli_per_unit' => $item['harga_beli_per_unit'],
                        'subtotal_harga_beli' => $subtotalItem,
                    ]);
                    $updatedDetailIds[] = $newDetail->id;
                }
            }

            // Hapus detail lama yang tidak ada di request dan kurangi stok yang sebelumnya ditambahkan
            $detailsToDelete = $existingDetails->whereNotIn('id', $updatedDetailIds);
            foreach ($detailsToDelete as $detail) {
                $product = Products::find($detail->product_id); // Re-fetch product to ensure latest stock
                if ($product) {
                    // Decrement stock by the amount that was originally restocked with this detail
                    $product->decrement('stok', $detail->jumlah);
                }
                $detail->delete();
            }

            // Update restock utama
            $restock->update([
                'total_harga_beli' => $totalHargaBeliKeseluruhan,
                'tanggal_restock' => $request->tanggal_restock,
                'supplier' => $request->supplier,
                // user_id should not be updated on existing records
                // 'catatan' => $request->catatan, // If you have a 'catatan' field and it's in validatedData
            ]);

            // --- Record Activity: Update Restock ---
            $this->recordActivity('update', $restock);
            // --- End Record Activity ---

            DB::commit();

            return redirect()->route('restocks.index')->with('success', 'Restock berhasil diperbarui!');

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
        if ($restock->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus restock ini.');
        }

        try {
            DB::beginTransaction();

            // Get ID before deletion for activity log
            $restockId = $restock->id;

            // Decrement product stock for each detail being deleted
            foreach ($restock->details as $detail) {
                $product = Products::find($detail->product_id); // Re-fetch product to ensure latest stock
                if ($product) {
                    // Decrement stock by the amount that was originally restocked with this detail
                    $product->decrement('stok', $detail->jumlah);
                }
            }

            $restock->details()->delete(); // Delete all associated restock details first
            $restock->delete(); // Then delete the main restock record

            // --- Record Activity: Delete Restock ---
            $this->recordActivity('delete', $restock, 'Deleted Restock: ID ' . $restockId);
            // --- End Record Activity ---

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
        return $this->index($request);
    }

    // --- Private method to record activity for Restocks ---
    private function recordActivity($type, $restock, $description = null)
    {
        Activity::create([
            'type' => $type,
            'description' => $description ?? ucfirst($type) . ' restock: ID ' . $restock->id . ' (Supplier: ' . ($restock->supplier ?? 'N/A') . ')',
            'model_type' => 'Restock',
            'model_id' => $restock->id,
            'user_id' => Auth::id(),
            'details' => [
                'restock_id' => $restock->id,
                'supplier' => $restock->supplier,
                'total_harga_beli' => $restock->total_harga_beli,
                'action' => $type,
                'timestamp' => now()
            ]
        ]);
    }
}
