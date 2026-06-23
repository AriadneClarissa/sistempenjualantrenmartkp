<?php

namespace App\Http\Controllers;

use App\Models\Merk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MerkController extends Controller
{
    /**
     * Menangani penyimpanan Merk baru
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            // Tambahkan unique agar tidak ada merk ganda
            'nama_merk' => 'required|string|max:255|unique:merk,nama_merk',
        ], [
            'nama_merk.unique' => 'Nama merk sudah terdaftar.'
        ]);

        // 2. Format menjadi Kapital Awal Kata
        $nama_format = ucwords(strtolower($request->nama_merk));

        // 3. Simpan ke Database (pastikan kd_merk unik)
        try {
            $baseSlug = Str::slug($nama_format);
            $slug = $baseSlug;
            $i = 1;
            while (Merk::where('kd_merk', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }

            $merk = Merk::create([
                'kd_merk' => $slug,
                'nama_merk' => $nama_format,
                'is_hidden' => false // Default tampil
            ]);
        } catch (\Exception $e) {
            // Jika request AJAX atau mengharapkan JSON, kembalikan JSON error yang jelas
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan merk.');
        }

        // 4. Jika permintaan AJAX atau mengharapkan JSON, kembalikan JSON sukses
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Merk ' . $nama_format . ' berhasil ditambahkan!',
                'data' => $merk
            ]);
        }

        // Redirect biasa jika bukan AJAX
        return redirect()->back()->with('success', 'Merk berhasil ditambahkan!');
    }

    /**
     * Fitur Pencarian Merk (Opsional - Jika ingin pencarian via Server-Side)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        $merks = Merk::where('nama_merk', 'LIKE', "%$search%")->get();
        
        return response()->json($merks);
    }

    /**
     * Mengubah status visibilitas merk (Sembunyikan/Tampilkan)
     */
    public function toggleVisible($id)
    {
        // Cari merk berdasarkan Primary Key
        $merk = Merk::findOrFail($id);
        
        // Mengubah status: jika true jadi false, jika false jadi true
        $merk->is_hidden = !$merk->is_hidden; 
        $merk->save();

        return response()->json([
            'success' => true, 
            'is_hidden' => $merk->is_hidden
        ]);
    }

    /**
     * Menghapus Merk
     */
    public function destroy($id)
    {
        $merk = Merk::findOrFail($id);
        $merk->delete();

        return redirect()->back()->with('success', 'Merk berhasil dihapus!');
    }
    public function toggleHidden(Request $request)
    {
        // Contoh untuk Kategori, ulangi untuk Merk & Satuan
        $data = Merk::where('kd_merk', $request->id)->firstOrFail();
        $data->is_hidden = !$data->is_hidden; // Membalik nilai (true jadi false, dst)
        $data->save();

        return response()->json(['success' => true, 'is_hidden' => $data->is_hidden]);
    }
}