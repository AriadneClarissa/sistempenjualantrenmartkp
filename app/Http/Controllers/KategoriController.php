<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori; 
use Illuminate\Support\Str;

class KategoriController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $nama_format = ucwords(strtolower($request->nama_kategori));

        try {
            $baseSlug = Str::slug($nama_format);
            $slug = $baseSlug;
            $i = 1;
            while (\App\Models\Kategori::where('kd_kategori', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }

            $kategori = \App\Models\Kategori::create([
                'kd_kategori' => $slug,
                'nama_kategori' => $nama_format,
                'is_hidden' => false
            ]);

            // Respon untuk AJAX
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kategori berhasil ditambah!',
                    'data' => $kategori
                ]);
            }

            return redirect()->back()->with('success', 'Kategori berhasil ditambah!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan kategori.');
        }
    }
    public function toggleHidden(Request $request)
    {
        \Log::info('Toggle Kategori diklik untuk ID: ' . $request->id); // Tambahkan ini
        $data = Kategori::where('kd_kategori', $request->id)->firstOrFail();
        $data->is_hidden = !$data->is_hidden; 
        $data->save();

        return response()->json(['success' => true, 'is_hidden' => (bool)$data->is_hidden]);
    }
}