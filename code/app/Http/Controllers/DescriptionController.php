<?php

namespace App\Http\Controllers;

use App\Models\Description;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Kita akan butuh ini

class DescriptionController extends Controller
{
    /**
     * Menampilkan halaman 'About' publik.
     */
    public function showAbout()
    {
        // Ambil deskripsi 'about'. Jika tidak ada, buat baru dengan value kosong.
        $description = Description::firstOrNew(['key' => 'about']);

        return view('about', compact('description'));
    }

    /**
     * Menampilkan form edit untuk halaman 'About'.
     */
    public function editAbout()
    {
        // Ambil data 'about', atau buat instance baru jika belum ada di DB
        $description = Description::firstOrNew(['key' => 'about']);

        // Kirim data ke view
        return view('descriptions.edit-about', compact('description'));
    }

    /**
     * Menyimpan (update atau create) deskripsi.
     */
    public function update(Request $request)
    {
        // Validasi
        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'nullable|string',
        ]);

        // Gunakan updateOrCreate untuk efisiensi.
        // Jika 'key' ada, 'value'-nya di-update.
        // Jika 'key' tidak ada, 'value'-nya di-create.
        Description::updateOrCreate(
            ['key' => $request->key],
            ['value' => $request->value]
        );

        return redirect()->back()->with('success', 'Deskripsi berhasil diperbarui!');
    }
}
