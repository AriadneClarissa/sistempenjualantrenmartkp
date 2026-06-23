<?php

namespace App\Http\Controllers;

use App\Helpers\MediaStorage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use App\Models\Keranjang;
use App\Models\BerandaSetting;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\OrderActivityNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $user_id = Auth::id();
        
        // 1. Ambil item keranjang milik user
        $cartItems = Keranjang::with(['produk', 'bundling'])->where('user_id', $user_id)->get();

        // Jika keranjang kosong, balikkan ke halaman keranjang
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        // 2. VALIDASI KEAMANAN: Cek apakah ada barang bermasalah sebelum masuk checkout
        $total = 0;
        foreach ($cartItems as $item) {
            $isBundling = $item->bundling_id != null && $item->bundling;
            
            if ($isBundling) {
                // Asumsi tabel bundling menggunakan is_active, jika menggunakan status silakan sesuaikan
                $isActive = $item->bundling->is_active ?? true;
                $maxStock = $item->bundling->availableStock();
                $nama = $item->bundling->name ?? 'Paket Bundling';
            } else {
                // Berdasarkan ProdukController, menggunakan kolom 'status' dengan nilai 'aktif'
                $isActive = $item->produk->status === 'aktif';
                $maxStock = $item->produk->stok_tersedia ?? 0;
                $nama = $item->produk->nama_produk ?? 'Produk';
            }

            // Tolak jika nonaktif atau stok kurang dari yang diminta
            if (!$isActive) {
                return redirect()->route('cart.index')->with('error', "Produk '{$nama}' saat ini dinonaktifkan. Silakan hapus dari keranjang Anda.");
            }

            if ($maxStock < $item->jumlah) {
                return redirect()->route('cart.index')->with('error', "Stok '{$nama}' tidak mencukupi (Sisa: {$maxStock}). Silakan kurangi jumlah atau hapus dari keranjang.");
            }

            // Hitung total jika aman
            $harga = $isBundling 
                     ? $item->bundling->bundling_price 
                     : ($item->harga_at_time ?? $item->produk->harga_jual_umum);
            $total += $harga * $item->jumlah;
        }

        // 3. Ambil Metode Pembayaran
        $paymentMethods = PaymentMethod::all();

        $storeAddress = 'Jl. Jenderal Ahmad Yani, Tangga Takat, Kec. Seberang Ulu II, Kota Palembang, Sumatera Selatan 30265';
        $customerAddress = (string) (Auth::user()->home_address ?? '');
        $shippingPreview = $this->calculateShipping();

        // 4. Kirim semua variabel ke view select_payment
        return view('checkout.select_payment', compact('cartItems', 'paymentMethods', 'total', 'storeAddress', 'customerAddress', 'shippingPreview'));
    }

    public function shippingQuote(Request $request)
    {
        $data = $request->validate([
            'pickup_method' => 'required|in:delivery,pickup',
            'shipping_address' => 'nullable|string|max:500',
        ]);

        if ($data['pickup_method'] === 'pickup') {
            return response()->json([
                'success' => true,
                'distance_km' => null,
                'shipping_cost' => 0,
            ]);
        }

        $quote = $this->calculateShipping();

        return response()->json([
            'success' => true,
            'distance_km' => null,
            'shipping_cost' => $quote['shipping_cost'],
        ]);
    }

    public function placeOrder(Request $request)
    {
        try {
            $data = $request->validate([
                'payment_method_id' => 'required|exists:payment_methods,id',
                'pickup_method' => 'required|in:delivery,pickup',
                'shipping_address' => 'nullable|string|max:500',
            ]);

            $user = Auth::user();
            $cartItems = Keranjang::with(['produk', 'bundling'])->where('user_id', $user->id)->get();

            if ($cartItems->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
            }

            // Hitung Subtotal SEKALIGUS Validasi Ulang (Benteng terakhir sebelum masuk database)
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $isBundling = $item->bundling_id != null && $item->bundling;
                
                if ($isBundling) {
                    $isActive = $item->bundling->is_active ?? true;
                    $maxStock = $item->bundling->availableStock();
                    $nama = $item->bundling->name ?? 'Paket Bundling';
                    $harga = $item->bundling->bundling_price;
                } else {
                    $isActive = $item->produk->status === 'aktif';
                    $maxStock = $item->produk->stok_tersedia ?? 0;
                    $nama = $item->produk->nama_produk ?? 'Produk';
                    $harga = $item->harga_at_time ?? $item->produk->harga_jual_umum;
                }

                // Cek Validasi
                if (!$isActive) {
                    return redirect()->route('cart.index')->with('error', "Pesanan dibatalkan. Produk '{$nama}' baru saja dinonaktifkan.");
                }

                if ($maxStock < $item->jumlah) {
                    return redirect()->route('cart.index')->with('error', "Pesanan dibatalkan. Stok '{$nama}' baru saja habis dibeli orang lain (Sisa: {$maxStock}).");
                }

                $subtotal += $harga * $item->jumlah;
            }

            $shippingAddress = trim((string) ($data['shipping_address'] ?? ''));
            $shippingCost = 0;

            $order = DB::transaction(function () use ($data, $user, $cartItems, $subtotal, $shippingAddress, &$shippingCost) {
                if ($data['pickup_method'] === 'delivery') {
                    if ($shippingAddress === '') {
                        throw new \RuntimeException('Alamat pengiriman wajib diisi untuk delivery.');
                    }

                    $quote = $this->calculateShipping();
                    $shippingCost = $quote['shipping_cost'];
                }

                $order = Order::create([
                    'order_number' => 'TRM-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'user_id' => $user->id,
                    'total' => $subtotal + $shippingCost,
                    'payment_method_id' => $data['payment_method_id'],
                    'pickup_method' => $data['pickup_method'],
                    'shipping_address' => $data['pickup_method'] === 'delivery' ? $shippingAddress : null,
                    'shipping_distance_km' => null,
                    'shipping_cost' => $shippingCost,
                    'payment_status' => 'pending',
                    'order_status' => 'new',
                ]);

                foreach ($cartItems as $item) {
                    $harga = $item->bundling_id
                        ? $item->bundling->bundling_price
                        : ($item->harga_at_time ?? $item->produk->harga_jual_umum);

                    $order->items()->create([
                        'kd_produk' => $item->kd_produk,
                        'quantity' => $item->jumlah,
                        'price' => $harga,
                    ]);
                }

                $order->load('items.produk');
                
                // Method ini diharapkan mengurangkan stok di database agar tidak ada minus stok
                $order->deductStockForCompletedOrder(); 

                return $order;
            });

            // Clear the user's cart immediately after order creation to prevent duplicate orders
            try {
                Keranjang::where('user_id', $user->id)->delete();
            } catch (\Throwable $e) {
                report($e);
            }

            $this->notifyOrderRecipients($order, $user->name ?? 'Pelanggan');

            return redirect()->route('checkout.upload_proof', $order->id)->with('success', 'Pesanan berhasil dibuat. Silakan unggah bukti pembayaran.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', $e->getMessage() ?: 'Gagal membuat pesanan.');
        }
    }

    // HALAMAN UPLOAD BUKTI (Setelah buat order)
    public function uploadProof($orderId)
    {
        $order = Order::with(['paymentMethod', 'items'])->findOrFail($orderId);
        return view('checkout.upload_proof', compact('order'));
    }

    // PROSES SIMPAN BUKTI TRANSFER
    public function storeProof(Request $request, $orderId)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:1024',
        ]);

        $order = Order::findOrFail($orderId);
        $user = Auth::user();

        if ($request->hasFile('bukti_pembayaran')) {
            MediaStorage::delete($order->payment_proof);
            $upload = app(\Cloudinary\Cloudinary::class)->uploadApi()->upload($request->file('bukti_pembayaran')->getRealPath(), [
                'upload_preset' => 'produk',
                'folder' => 'bukti_transfer',
            ]);
            $path = (string) ($upload->offsetGet('secure_url') ?? $upload['secure_url'] ?? null);
            
            // Save to the `payment_proof` column which the Order model expects
            $order->update([
                'payment_proof' => $path,
                'status' => 'menunggu_verifikasi',
                'payment_status' => 'waiting_confirmation',
            ]);

            // HAPUS KERANJANG setelah payment proof sukses diunggah
            Keranjang::where('user_id', $user->id)->delete();

            return redirect()->route('pesanan.show', $order->id)
                             ->with('success', 'Bukti transfer berhasil diunggah!');
        }

        return back()->with('error', 'Gagal mengunggah foto.');
    }

    // HALAMAN TUNGGU VERIFIKASI
    public function waiting($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('checkout.waiting', compact('order'));
    }

    public function calculateShipping(): array
    {
        $settings = \App\Models\ShippingSetting::first();
        $shippingCost = (int) (
            $settings->flat_rate
            ?? $settings->price_per_km
            ?? 15000
        );

        return [
            'distance_km' => null,
            'shipping_cost' => $shippingCost,
        ];
    }

    private function notifyOrderRecipients(Order $order, string $customerName): void
    {
        $recipients = User::query()
            ->whereIn('role', ['owner', 'admin', 'kasir'])
            ->where('is_active', true)
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new OrderActivityNotification(
                title: 'Pesanan baru masuk',
                body: 'Pesanan #' . $order->order_number . ' baru saja dibuat oleh ' . $customerName . '.',
                url: route('admin.orders.show', $order->id),
                type: 'new_order',
                orderNumber: $order->order_number,
                actorName: $customerName,
            ));
        }
    }
}