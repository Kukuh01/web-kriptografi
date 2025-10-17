<?php
/**
 * Script untuk Generate Encryption Key dan Nonce untuk ChaCha20
 * Jalankan: php generate-keys.php
 */

echo "==============================================\n";
echo "  ChaCha20 Key & Nonce Generator\n";
echo "==============================================\n\n";

// Generate Key (32 bytes untuk ChaCha20)
$key = random_bytes(32);
$keyBase64 = base64_encode($key);

// Generate Nonce (12 bytes untuk XChaCha20)
$nonce = random_bytes(12);
$nonceBase64 = base64_encode($nonce);

echo "✅ Keys berhasil di-generate!\n\n";

echo "📋 Copy dan paste ke file .env Anda:\n";
echo "----------------------------------------------\n";
echo "ENCRYPTION_KEY=base64:{$keyBase64}\n";
echo "ENCRYPTION_NONCE=base64:{$nonceBase64}\n";
echo "----------------------------------------------\n\n";

echo "⚠️  PENTING:\n";
echo "1. Simpan keys ini dengan AMAN\n";
echo "2. JANGAN commit keys ke repository\n";
echo "3. Gunakan keys yang berbeda untuk production\n";
?>