<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class KeranjangController extends Controller
{
    /**
     * Menampilkan isi keranjang belanja
     */
    public function index()
    {
        $previousUrl = URL::previous();
        if ($previousUrl && !str_contains($previousUrl, '/keranjang')) {
            session(['cart_back_url' => $previousUrl]);
        }

        $backUrl = session('cart_back_url', route('katalog'));

        $items = Keranjang::with(['produk.merk', 'bundling'])
                            ->where('user_id', Auth::id())
                            ->get();
        
        $total = 0;
        $hasInvalidItem = false; // Penanda jika ada barang bermasalah di keranjang

        foreach ($items as $item) {
            $isBundling = $item->bundling_id != null && $item->bundling;
            
            // 1. Cek Status Aktif (Catatan: Sesuaikan 'is_active' dengan nama kolom status di database Anda)
            // Jika kolom tersebut tidak ada, Anda bisa menghapus pengecekan $isActive ini atau membuat nilainya selalu true.
            if ($isBundling) {
                $isActive = $item->bundling->is_active ?? true;
            } else {
                $isActive = $item->produk->is_active ?? true;
            }
            
            // 2. Cek Stok Habis
            $maxStock = $isBundling 
                ? $item->bundling->availableStock() 
                : ($item->produk->stok_tersedia ?? 0);
            $isHabis = $maxStock <= 0;

            // Jika produk dinonaktifkan ATAU stok habis
            if (!$isActive || $isHabis) {
                $hasInvalidItem = true;
                $item->is_invalid = true;
                $item->invalid_reason = !$isActive ? 'nonaktif' : 'habis';
                $item->harga_at_time = 0; // Harga tidak dihitung ke total bayar
            } else {
                $item->is_invalid = false;
                
                if ($isBundling) {
                    $harga = $item->bundling->bundling_price; 
                } else {
                    $harga = (Auth::user()->customer_type === 'langganan') 
                              ? ($item->produk->harga_jual_langganan ?? $item->produk->harga_jual_umum) 
                              : $item->produk->harga_jual_umum;
                }
                
                $item->harga_at_time = $harga;
                $total += $harga * $item->jumlah;
            }
        }

        return view('keranjang', compact('items', 'total', 'backUrl', 'hasInvalidItem'));
    }

    /**
     * Menambahkan produk ke keranjang atau update jumlah jika sudah ada
     */
    public function store(Request $request, $id)
    {
        $type = $request->route('type') ?? $request->input('type', 'reguler');

        if ($type === 'bundling') {
            $bundling = \App\Models\Bundling::with('items.produk')->findOrFail($id);
            
            // Pengecekan produk aktif
            if (isset($bundling->is_active) && !$bundling->is_active) {
                return $this->errorResponse($request, 'Paket bundling saat ini tidak tersedia atau dinonaktifkan.');
            }

            $bundlingItems = $bundling->items()->with('produk')->get();

            if ($bundlingItems->isEmpty()) {
                return $this->errorResponse($request, 'Paket bundling tidak memiliki isi produk.');
            }

            $stokTersedia = $bundling->availableStock();

            if ($stokTersedia <= 0) {
                return $this->errorResponse($request, 'Paket bundling sedang habis karena salah satu produk di dalamnya habis.');
            }

            $identifierColumn = 'bundling_id'; 
            $kdProdukValue = $bundlingItems->first()->product_id;
            $bundlingIdValue = $id;
        } else {
            $produk = Produk::where('kd_produk', $id)->firstOrFail();
            
            // Pengecekan produk aktif
            if (isset($produk->is_active) && !$produk->is_active) {
                return $this->errorResponse($request, 'Produk saat ini tidak tersedia atau dinonaktifkan.');
            }

            $stokTersedia = $produk->stok_tersedia;
            $identifierColumn = 'kd_produk';
            $kdProdukValue = $id;
            $bundlingIdValue = null;

            if ($stokTersedia <= 0) {
                return $this->errorResponse($request, 'Stok produk sudah habis.');
            }
        }

        $itemExist = Keranjang::where('user_id', Auth::id())
                        ->when($type === 'bundling', function($q) use ($id) {
                            return $q->where('bundling_id', $id);
                        })
                        ->when($type === 'reguler', function($q) use ($id) {
                            return $q->where('kd_produk', $id);
                        })
                        ->first();

        if ($itemExist) {
            if ($itemExist->jumlah < $stokTersedia) {
                $itemExist->increment('jumlah');
            } else {
                return $this->errorResponse($request, 'Stok tidak mencukupi.');
            }
        } else {
            Keranjang::create([
                'user_id'   => Auth::id(),
                'kd_produk' => $kdProdukValue,
                'bundling_id' => $bundlingIdValue, 
                'jumlah'    => 1
            ]);
        }

        $cartCount = Keranjang::where('user_id', Auth::id())->sum('jumlah');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'cartCount' => $cartCount]);
        }

        return back()->with('success', 'Berhasil ditambahkan ke keranjang!');
    }

    private function errorResponse($request, $message) {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }
        return back()->with('error', $message);
    }

    /**
     * Menghapus item dari keranjang
     */
    public function destroy($id)
    {
        Keranjang::where('id', $id)
                  ->where('user_id', Auth::id())
                  ->delete();

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    /**
     * Menghapus semua item di keranjang milik user login
     */
    public function clearAll()
    {
        Keranjang::where('user_id', Auth::id())->delete();

        return back()->with('success', 'Semua item berhasil dihapus dari keranjang.');
    }

    /**
     * Update jumlah dari halaman keranjang
     */
    public function update(Request $request, $id)
    {
        $item = Keranjang::with(['produk', 'bundling.items.produk'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $quantity = (int) $validated['quantity'];

        if ($quantity <= 0) {
            $item->delete();
            return back()->with('success', 'Item berhasil dihapus dari keranjang.');
        }

        if ($item->bundling_id != null && $item->bundling) {
            $maxStock = (int) $item->bundling->availableStock();
        } else {
            $maxStock = (int) (($item->produk->stok_tersedia ?? 0));
        }

        if ($maxStock <= 0) {
            $item->delete();
            return back()->with('error', 'Stok produk sudah habis, item dihapus dari keranjang.');
        }

        if ($quantity > $maxStock) {
            return back()->with('error', 'Kuantitas produk tidak dapat melebihi stok yang ada (Sisa stok: ' . $maxStock . ').');
        }

        $item->update(['jumlah' => $quantity]);

        return back()->with('success', 'Jumlah item berhasil diperbarui.');
    }
}