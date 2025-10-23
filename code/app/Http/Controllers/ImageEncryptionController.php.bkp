<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageEncryptionController extends Controller
{
    private $key;
    private $nonce;

    public function __construct()
    {
        // Ambil key dan nonce dari .env
        $keyEnv = env('ENCRYPTION_KEY');
        $nonceEnv = env('ENCRYPTION_NONCE');
        
        // Decode dari base64
        if ($keyEnv && str_starts_with($keyEnv, 'base64:')) {
            $this->key = base64_decode(substr($keyEnv, 7));
        } else {
            $this->key = random_bytes(32);
        }
        
        // XChaCha20 membutuhkan nonce 24 bytes (bukan 12)
        if ($nonceEnv && str_starts_with($nonceEnv, 'base64:')) {
            $this->nonce = base64_decode(substr($nonceEnv, 7));
        } else {
            $this->nonce = random_bytes(24);
        }
    }

    public function index()
    {
        return view('image-encryption');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        
        // Simpan gambar original
        $path = $image->storeAs('images/original', $imageName, 'public');
        
        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil diupload',
            'data' => [
                'filename' => $imageName,
                'path' => Storage::url($path),
                'size' => $image->getSize()
            ]
        ]);
    }

    public function encrypt(Request $request)
    {
        try {
            $request->validate([
                'filename' => 'required|string'
            ]);

            $filename = $request->filename;
            $originalPath = storage_path('app/public/images/original/' . $filename);

            if (!file_exists($originalPath)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'File tidak ditemukan: ' . $originalPath
                ], 404);
            }

            // Check if libsodium is available
            if (!function_exists('sodium_crypto_stream_xchacha20_xor')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Libsodium tidak tersedia. Install PHP dengan sodium extension.'
                ], 500);
            }

            // Validasi key dan nonce
            if (strlen($this->key) !== 32) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key harus 32 bytes. Saat ini: ' . strlen($this->key) . ' bytes'
                ], 500);
            }

            if (strlen($this->nonce) !== 24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nonce harus 24 bytes untuk XChaCha20. Saat ini: ' . strlen($this->nonce) . ' bytes'
                ], 500);
            }

            // Baca file gambar
            $imageData = file_get_contents($originalPath);
            
            if ($imageData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membaca file gambar'
                ], 500);
            }

            // Enkripsi menggunakan XChaCha20
            $encryptedData = sodium_crypto_stream_xchacha20_xor(
                $imageData,
                $this->nonce,
                $this->key
            );

            // Simpan gambar terenkripsi
            $encryptedFilename = 'encrypted_' . $filename;
            $encryptedPath = 'images/encrypted/' . $encryptedFilename;
            
            // Pastikan direktori exists
            Storage::disk('public')->makeDirectory('images/encrypted');
            Storage::disk('public')->put($encryptedPath, $encryptedData);

            // Simpan metadata untuk dekripsi
            $metadata = [
                'original_filename' => $filename,
                'encrypted_filename' => $encryptedFilename,
                'key' => base64_encode($this->key),
                'nonce' => base64_encode($this->nonce)
            ];
            
            Storage::disk('public')->makeDirectory('images/metadata');
            Storage::disk('public')->put(
                'images/metadata/' . $encryptedFilename . '.json',
                json_encode($metadata)
            );

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil dienkripsi',
                'data' => [
                    'encrypted_filename' => $encryptedFilename,
                    'encrypted_path' => Storage::url($encryptedPath),
                    'metadata' => $metadata,
                            'hash' => [
                                'original'  => $this->fileHash($originalPath),
                                'encrypted' => $this->fileHash(storage_path('app/public/' . $encryptedPath)),
                            ],
                            'binary_snippet' => [
                                'original'  => $this->fileBinarySnippet($originalPath),
                                'encrypted' => $this->fileBinarySnippet(storage_path('app/public/' . $encryptedPath)),
        ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function decrypt(Request $request)
    {
        try {
            $request->validate([
                'encrypted_filename' => 'required|string'
            ]);

            $encryptedFilename = $request->encrypted_filename;
            $encryptedPath = storage_path('app/public/images/encrypted/' . $encryptedFilename);
            $metadataPath = storage_path('app/public/images/metadata/' . $encryptedFilename . '.json');

            if (!file_exists($encryptedPath) || !file_exists($metadataPath)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'File terenkripsi atau metadata tidak ditemukan'
                ], 404);
            }

            // Baca metadata
            $metadata = json_decode(file_get_contents($metadataPath), true);
            $key = base64_decode($metadata['key']);
            $nonce = base64_decode($metadata['nonce']);

            // Validasi panjang key dan nonce
            if (strlen($key) !== 32 || strlen($nonce) !== 24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid key atau nonce length'
                ], 500);
            }

            // Baca file terenkripsi
            $encryptedData = file_get_contents($encryptedPath);

            // Dekripsi menggunakan XChaCha20
            $decryptedData = sodium_crypto_stream_xchacha20_xor(
                $encryptedData,
                $nonce,
                $key
            );

            // Simpan gambar hasil dekripsi
            $decryptedFilename = 'decrypted_' . $metadata['original_filename'];
            $decryptedPath = 'images/decrypted/' . $decryptedFilename;
            
            Storage::disk('public')->makeDirectory('images/decrypted');
            Storage::disk('public')->put($decryptedPath, $decryptedData);

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil didekripsi',
                'data' => [
                    'decrypted_filename' => $decryptedFilename,
                    'decrypted_path' => Storage::url($decryptedPath),
                        'hash' => [
                            'encrypted' => $this->fileHash($encryptedPath),
                            'decrypted' => $this->fileHash(storage_path('app/public/' . $decryptedPath)),
                        ],
                        'binary_snippet' => [
                            'encrypted' => $this->fileBinarySnippet($encryptedPath),
                            'decrypted' => $this->fileBinarySnippet(storage_path('app/public/' . $decryptedPath)),
                        ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function download($type, $filename)
    {
        $path = storage_path('app/public/images/' . $type . '/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    private function fileHash($path)
    {
        return hash_file('sha256', $path);
    }

    private function fileBinarySnippet($path, $limit = 64)
    {
        $data = file_get_contents($path, false, null, 0, $limit);
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= sprintf('%08b ', ord($char));
        }
        return trim($binary);
    }
}