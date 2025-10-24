<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageEncryptionController extends Controller
{
    private $key; // 32 bytes

    public function __construct()
    {
        // Ambil key dari env (base64:...). Jika tidak ada, generate dan gunakan sementara.
        $keyEnv = env('ENCRYPTION_KEY');
        if ($keyEnv && str_starts_with($keyEnv, 'base64:')) {
            $this->key = base64_decode(substr($keyEnv, 7));
        } elseif ($keyEnv && strlen($keyEnv) === 32) {
            $this->key = $keyEnv;
        } else {
            // WARNING: untuk produksi gunakan key tetap dari env; random untuk testing
            $this->key = random_bytes(32);
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
        $imageName = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());

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

            if (!function_exists('sodium_crypto_stream_xchacha20_xor')) {
                return response()->json(['success'=>false,'message'=>'Libsodium tidak tersedia'], 500);
            }

            $filename = $request->filename;
            $originalPath = storage_path('app/public/images/original/' . $filename);

            if (!file_exists($originalPath)) {
                return response()->json(['success'=>false,'message'=>'File tidak ditemukan'], 404);
            }

            // Baca image sebagai string (GD)
            $raw = file_get_contents($originalPath);
            $srcImg = imagecreatefromstring($raw);
            if ($srcImg === false) {
                return response()->json(['success'=>false,'message'=>'Gagal decode image dengan GD'], 500);
            }

            $width = imagesx($srcImg);
            $height = imagesy($srcImg);

            // Ambil byte stream RGB (3 bytes per pixel)
            $pixelBytes = ImageEncryptionService::imageToRGBBytes($srcImg, $width, $height);

            // Generate nonce baru per-file (24 bytes)
            $nonce = random_bytes(24);

            // Enkripsi pixel bytes
            $encryptedBytes = sodium_crypto_stream_xchacha20_xor($pixelBytes, $nonce, $this->key);

            // Buat image dari encryptedBytes (agar bisa ditampilkan)
            $encryptedImageResource = ImageEncryptionService::bytesToImage($encryptedBytes, $width, $height);

            // Simpan encrypted image sebagai PNG (agar lossless, tampilable)
            $encryptedFilename = 'encrypted_' . pathinfo($filename, PATHINFO_FILENAME) . '.png';
            $encryptedRelPath = 'images/encrypted/' . $encryptedFilename;
            Storage::disk('public')->makeDirectory('images/encrypted');

            // simpan ke temp file lalu put
            $tmp = tmpfile();
            $meta = stream_get_meta_data($tmp);
            $tmpPath = $meta['uri'];
            imagepng($encryptedImageResource, $tmpPath);
            $contents = file_get_contents($tmpPath);
            Storage::disk('public')->put($encryptedRelPath, $contents);
            fclose($tmp);
            imagedestroy($encryptedImageResource);

            // Simpan metadata (nonce, original filename). Jangan simpan key plaintext di metadata publik!
            // Jika kamu ingin demo, kita simpan key base64 juga (TIDAK DI REKOMENDASIKAN UNTUK PRODUKSI)
            $metadata = [
                'original_filename' => $filename,
                'encrypted_filename' => $encryptedFilename,
                'nonce' => base64_encode($nonce),
                'key' => base64_encode($this->key) // OPTIONAL: remove in produksi
            ];

            Storage::disk('public')->makeDirectory('images/metadata');
            Storage::disk('public')->put('images/metadata/' . $encryptedFilename . '.json', json_encode($metadata));

            // Hitung metrics: perlu file paths untuk original dan encrypted (keduanya sebagai pixel arrays)
            $encryptedPathOnDisk = storage_path('app/public/' . $encryptedRelPath);

            // Untuk perhitungan, gunakan image resources (convert encrypted resource already created)
            // Muat ulang original & encrypted sebagai resources
            $origRes = $srcImg; // masih ada
            $encRes = imagecreatefromstring(Storage::disk('public')->get($encryptedRelPath));

            $metrics = ImageMetrics::compareImages($origRes, $encRes);

            // Release resources
            imagedestroy($origRes);
            imagedestroy($encRes);

            $hashOriginal = ImageEncryptionUtils::fileHash($originalPath);
            $hashEncrypted = ImageEncryptionUtils::fileHash($encryptedPathOnDisk);

            $snippetOriginal = ImageEncryptionUtils::binarySnippet($originalPath);
            $snippetEncrypted = ImageEncryptionUtils::binarySnippet($encryptedPathOnDisk);

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil dienkripsi',
                'data' => [
                    'encrypted_filename' => $encryptedFilename,
                    'encrypted_path' => Storage::url($encryptedRelPath),
                    'metadata' => $metadata,
                    'metrics' => $metrics,
                    'hash' => [
                        'original' => $hashOriginal,
                        'encrypted' => $hashEncrypted
                    ],
                    'binary_snippet' => [
                        'original' => $snippetOriginal,
                        'encrypted' => $snippetEncrypted
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage(),'line'=>$e->getLine()], 500);
        }
    }

    public function decrypt(Request $request)
    {
        try {
            $request->validate(['encrypted_filename' => 'required|string']);

            $encryptedFilename = $request->encrypted_filename;
            $encryptedRelPath = 'images/encrypted/' . $encryptedFilename;
            $metadataPath = storage_path('app/public/images/metadata/' . $encryptedFilename . '.json');

            if (!Storage::disk('public')->exists($encryptedRelPath) || !file_exists($metadataPath)) {
                return response()->json(['success'=>false,'message'=>'File atau metadata tidak ditemukan'], 404);
            }

            $metadata = json_decode(file_get_contents($metadataPath), true);
            $nonce = base64_decode($metadata['nonce']);
            $keyFromMeta = isset($metadata['key']) ? base64_decode($metadata['key']) : $this->key;

            // Muat encrypted image (yang kita simpan sebelumnya)
            $encRaw = Storage::disk('public')->get($encryptedRelPath);
            $encImg = imagecreatefromstring($encRaw);
            if ($encImg === false) return response()->json(['success'=>false,'message'=>'Gagal decode encrypted image'], 500);

            $width = imagesx($encImg);
            $height = imagesy($encImg);

            // Ambil byte stream RGB dari encrypted image (ini adalah hasil enkripsi yang kita simpan)
            $encPixelBytes = ImageEncryptionService::imageToRGBBytes($encImg, $width, $height);

            // XOR lagi dengan same key+nonce untuk dekripsi
            $decryptedBytes = sodium_crypto_stream_xchacha20_xor($encPixelBytes, $nonce, $keyFromMeta);

            // Kembalikan ke image resource
            $decImgResource = ImageEncryptionService::bytesToImage($decryptedBytes, $width, $height);

            // Simpan decrypted result — gunakan original extension jika mau; simpan sebagai PNG or JPEG.
            $decryptedFilename = 'decrypted_' . $metadata['original_filename'];
            $decryptedRelPath = 'images/decrypted/' . $decryptedFilename;
            Storage::disk('public')->makeDirectory('images/decrypted');

            // Simpan ke temp file lalu put
            $tmp = tmpfile();
            $meta = stream_get_meta_data($tmp);
            $tmpPath = $meta['uri'];
            // Determine original extension to choose saver (use png for lossless)
            $ext = strtolower(pathinfo($metadata['original_filename'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg'])) {
                // You can save as jpeg but it's lossy. For perfect pixel recovery save as png.
                imagepng($decImgResource, $tmpPath);
            } else {
                imagepng($decImgResource, $tmpPath);
            }
            $contents = file_get_contents($tmpPath);
            Storage::disk('public')->put($decryptedRelPath, $contents);
            fclose($tmp);
            imagedestroy($decImgResource);

            $decryptedPathOnDisk = storage_path('app/public/' . $decryptedRelPath);
            $hashDecrypted = ImageEncryptionUtils::fileHash($decryptedPathOnDisk);
            $snippetDecrypted = ImageEncryptionUtils::binarySnippet($decryptedPathOnDisk);

            $originalPath = storage_path('app/public/images/original/' . $metadata['original_filename']);
            $encryptedPath = storage_path('app/public/' . $encryptedRelPath);
            $decryptedPath = $decryptedPathOnDisk;

            $analysis = ImageMetrics::analyzeThree($originalPath, $encryptedPath, $decryptedPath);

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil didekripsi',
                'data' => [
                    'decrypted_filename' => $decryptedFilename,
                    'decrypted_path' => Storage::url($decryptedRelPath),
                    'analysis' => $analysis,
                    'hash' => [
                        'decrypted' => $hashDecrypted
                    ],
                    'binary_snippet' => [
                        'decrypted' => $snippetDecrypted
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage(),'line'=>$e->getLine()], 500);
        }
    }

    // Download function (sama seperti sebelumnya)
    public function download($type, $filename)
    {
        $path = storage_path('app/public/images/' . $type . '/' . $filename);
        if (!file_exists($path)) abort(404);
        return response()->download($path);
    }
}

/**
 * Service helper: baca image -> RGB bytes, dan konversi bytes -> image resource.
 */
class ImageEncryptionService
{
    // Ambil RGB bytes dari image resource (3 bytes per pixel: R,G,B)
    public static function imageToRGBBytes($imgRes, $width, $height)
    {
        $bytes = '';
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($imgRes, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $bytes .= chr($r) . chr($g) . chr($b);
            }
        }
        return $bytes;
    }

    // Konversi byte stream (3*width*height) -> image resource
    public static function bytesToImage($bytes, $width, $height)
    {
        $img = imagecreatetruecolor($width, $height);
        // Disable alpha blending and preserve alpha if desired
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $offset = 0;
        $total = strlen($bytes);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                // safety: if bytes exhausted, set 0
                $r = $offset < $total ? ord($bytes[$offset++]) : 0;
                $g = $offset < $total ? ord($bytes[$offset++]) : 0;
                $b = $offset < $total ? ord($bytes[$offset++]) : 0;
                $color = imagecolorallocate($img, $r, $g, $b);
                imagesetpixel($img, $x, $y, $color);
            }
        }
        return $img;
    }
}

class ImageEncryptionUtils
{
    public static function fileHash($path)
    {
        return hash_file('sha256', $path);
    }

    public static function binarySnippet($path, $length = 64)
    {
        $raw = file_get_contents($path);
        return bin2hex(substr($raw, 0, $length));
    }
}


/**
 * ImageMetrics: MSE, PSNR, NPCR, UACI, Entropy, SSIM (global approximate), NCC
 * Input: two image resources (same width,height). Returns associative array metrics.
 */
class ImageMetrics
{
    /**
     * Hitung entropi dari histogram 256-bin
     */
    private static function calculateEntropy($histogram, $totalPixels)
    {
        $entropy = 0.0;
        if ($totalPixels == 0) return 0;

        foreach ($histogram as $count) {
            if ($count == 0) continue;
            $p = $count / $totalPixels;
            $entropy -= $p * log($p, 2);
        }
        return $entropy;
    }

    /**
     * Hitung semua metrik antara dua gambar
     */
    public static function compareImages($imgA, $imgB)
    {
        if (!$imgA || !$imgB) {
            throw new \Exception("Gambar tidak valid.");
        }

        $w = imagesx($imgA);
        $h = imagesy($imgA);

        if ($w !== imagesx($imgB) || $h !== imagesy($imgB)) {
            throw new \Exception('Ukuran gambar berbeda.');
        }

        $mse = 0.0;
        $maxPixel = 255.0;
        $totalPixels = $w * $h;
        $diffCount = 0;
        $uaciSum = 0.0;

        $histR = array_fill(0, 256, 0);
        $histG = array_fill(0, 256, 0);
        $histB = array_fill(0, 256, 0);

        // Untuk SSIM global (grayscale)
        $sumA = 0.0; $sumB = 0.0;
        $sumA2 = 0.0; $sumB2 = 0.0; $sumAB = 0.0;

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgbA = imagecolorat($imgA, $x, $y);
                $rA = ($rgbA >> 16) & 0xFF;
                $gA = ($rgbA >> 8) & 0xFF;
                $bA = $rgbA & 0xFF;

                $rgbB = imagecolorat($imgB, $x, $y);
                $rB = ($rgbB >> 16) & 0xFF;
                $gB = ($rgbB >> 8) & 0xFF;
                $bB = $rgbB & 0xFF;

                // MSE per channel
                $dr = $rA - $rB; $dg = $gA - $gB; $db = $bA - $bB;
                $mse += ($dr * $dr + $dg * $dg + $db * $db) / 3.0;

                // NPCR: cek apakah pixel berubah
                if ($rA !== $rB || $gA !== $gB || $bA !== $bB) $diffCount++;

                // UACI: selisih absolut rata-rata
                $uaciSum += (abs($dr) + abs($dg) + abs($db)) / 3.0;

                // histogram untuk entropi
                $histR[$rB]++;
                $histG[$gB]++;
                $histB[$bB]++;

                // konversi ke grayscale
                $grayA = 0.299 * $rA + 0.587 * $gA + 0.114 * $bA;
                $grayB = 0.299 * $rB + 0.587 * $gB + 0.114 * $bB;

                // SSIM (global)
                $sumA += $grayA; $sumB += $grayB;
                $sumA2 += $grayA * $grayA;
                $sumB2 += $grayB * $grayB;
                $sumAB += $grayA * $grayB;
            }
        }

        // Hitung nilai akhir
        $mse = $mse / $totalPixels;
        $psnr = ($mse == 0) ? INF : 10 * log(($maxPixel * $maxPixel) / $mse, 10);

        $npcr = ($diffCount / $totalPixels) * 100.0;
        $uaci = ($uaciSum / ($totalPixels * $maxPixel)) * 100.0;

        // Entropy
        $entropyR = self::calculateEntropy($histR, $totalPixels);
        $entropyG = self::calculateEntropy($histG, $totalPixels);
        $entropyB = self::calculateEntropy($histB, $totalPixels);
        $entropy = ($entropyR + $entropyG + $entropyB) / 3.0;

        // SSIM (approximation)
        $N = $totalPixels;
        $muA = $sumA / $N;
        $muB = $sumB / $N;
        $sigmaA2 = ($sumA2 / $N) - ($muA * $muA);
        $sigmaB2 = ($sumB2 / $N) - ($muB * $muB);
        $sigmaAB = ($sumAB / $N) - ($muA * $muB);

        $L = 255;
        $K1 = 0.01;
        $K2 = 0.03;
        $C1 = pow($K1 * $L, 2);
        $C2 = pow($K2 * $L, 2);

        $ssim = ((2 * $muA * $muB + $C1) * (2 * $sigmaAB + $C2)) /
                (($muA * $muA + $muB * $muB + $C1) * ($sigmaA2 + $sigmaB2 + $C2) + 1e-12);

        // NCC
        $num = $sumAB - $N * $muA * $muB;
        $den = sqrt(($sumA2 - $N * $muA * $muA) * ($sumB2 - $N * $muB * $muB));
        $ncc = ($den == 0) ? 0 : ($num / $den);

        return [
            'MSE' => round($mse, 4),
            'PSNR' => is_infinite($psnr) ? 'INF' : round($psnr, 4),
            'NPCR_percent' => round($npcr, 4),
            'UACI_percent' => round($uaci, 4),
            'SSIM_global' => round($ssim, 6),
            'NCC' => round($ncc, 6)
        ];
    }

    /**
     * Bandingkan 3 gambar: original, encrypted, decrypted
     */
    public static function analyzeThree($originalPath, $encryptedPath, $decryptedPath)
    {
        $imgOriginal = imagecreatefromstring(file_get_contents($originalPath));
        $imgEncrypted = imagecreatefromstring(file_get_contents($encryptedPath));
        $imgDecrypted = imagecreatefromstring(file_get_contents($decryptedPath));

        $res1 = self::compareImages($imgOriginal, $imgEncrypted);
        $res2 = self::compareImages($imgOriginal, $imgDecrypted);

        return [
            'Original_vs_Encrypted' => $res1,
            'Original_vs_Decrypted' => $res2
        ];
    }
}
