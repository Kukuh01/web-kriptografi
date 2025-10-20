<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Menampilkan halaman untuk mengedit deskripsi
    public function editDescription()
    {
        $description = 'Contoh Text'; // Anda dapat mengambil ini dari database
        return view('admin.main', compact('description'));
    }

    // Menyimpan deskripsi yang telah diedit
    public function saveDescription(Request $request)
    {
        // Menyimpan deskripsi yang sudah di-edit ke database
        $description = $request->input('description');

        // Misalnya, simpan ke dalam tabel "settings" atau tabel khusus
        \DB::table('settings')->where('key', 'about_description')->update([
            'value' => $description
        ]);

        return redirect('/admin')->with('message', 'Deskripsi berhasil disimpan!');
    }
}
