<?php

namespace App\Http\Controllers;

use App\Services\ChaCha20Service;
use Illuminate\Http\Request;

class EncryptController extends Controller
{
    public function encrypt(Request $request) {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('image');
        $path = $file->store('uploads/original','public');
        $content = file_get_contents($file->getRealPath());

        $crypto = new ChaCha20Service();
        $result = $crypto->encrypt($content);

        $outputPath = 'uploads/encrypted/' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.enc';
        file_put_contents(storage_path('app/public/'.$outputPath), $result);

        return response()->json([
            'message' => 'File berhasil di enkripsi!',
            'download_url' => asset('storage/'.$outputPath)
        ]);
    }
}
