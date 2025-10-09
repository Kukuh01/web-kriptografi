<?php

namespace App\Services;

use Exception;

class ChaCha20Service
{
    private $key;

    public function __construct()
    {
        // Ambil kunci dari file .env dan decode dari base64
        $key = env('CHACHA20_KEY');

        if (!$key) {
            throw new Exception("Kunci enkripsi ChaCha20 belum diatur di file .env.");
        }

        // Penting: Kunci harus di-decode dari base64 sebelum digunakan
        $this->key = base64_decode($key);

        // Validasi panjang kunci setelah di-decode
        if (strlen($this->key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new Exception("Panjang kunci enkripsi ChaCha20 tidak valid.");
        }
    }

    public function encrypt(string $data): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES);
        $ciphertext = sodium_crypto_aead_chacha20poly1305_encrypt(
            $data,
            '', // additional data (opsional)
            $nonce,
            $this->key
        );

        // Gabungkan nonce dengan ciphertext untuk disimpan
        return $nonce . $ciphertext;
    }

    public function decrypt(string $data): string|false
    {
        // Pastikan data yang masuk cukup panjang untuk mengandung nonce
        if (strlen($data) < SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES) {
            return false;
        }

        $nonceSize = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES;
        $nonce = substr($data, 0, $nonceSize);
        $ciphertext = substr($data, $nonceSize);

        // Dekripsi akan mengembalikan 'false' jika gagal (misal: kunci/data salah)
        return sodium_crypto_aead_chacha20poly1305_decrypt(
            $ciphertext,
            '',
            $nonce,
            $this->key
        );
    }
}