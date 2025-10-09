<?php

namespace App\Http\Controllers;

use App\Services\ChaCha20Service;
use Illuminate\Http\Request;

class DecryptController extends Controller
{
    public function decrypt(Request $request) {
        $request->validate([
            'file' => 'required|file|mimetypes:application/octet-stream',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        $crypto = new ChaCha20Service();
        $result = $crypto->decrypt($content);

        // <-- TAMBAHKAN PENGECEKAN INI
        if ($result === false) {
            return response()->json([
                'message' => 'Dekripsi gagal! File mungkin rusak atau kunci tidak cocok.',
            ], 422); // 422 Unprocessable Entity
        }

        $outputPath = 'uploads/decrypted/' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.png';
        file_put_contents(storage_path('app/public/'.$outputPath), $result);

        return response()->json([
            'message' => 'File berhasil didekripsi!',
            'download_url' => asset('storage/'.$outputPath)
        ]);
    }
}