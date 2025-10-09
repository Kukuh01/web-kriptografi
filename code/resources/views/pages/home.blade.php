<x-layout>
    <x-slot:title>
        Homepag
    </x-slot:title>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold mb-8">🔒 CryptImage - Enkripsi Gambar ChaCha20</h1>

        <div class="space-y-6 w-96">
            <form id="encryptForm" enctype="multipart/form-data">
                <label class="block text-sm font-medium">Upload Gambar:</label>
                <input type="file" name="image" class="file-input w-full" required>
                <button class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded w-full mt-3">Enkripsi Gambar</button>
            </form>

            <form id="decryptForm" enctype="multipart/form-data">
                <label class="block text-sm font-medium">Upload File Enkripsi:</label>
                <input type="file" name="file" class="file-input w-full" required>
                <button class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded w-full mt-3">Dekripsi Gambar</button>
            </form>
        </div>

        <script>
            // Ambil CSRF token dari meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            document.querySelector('#encryptForm').addEventListener('submit', async e => {
                e.preventDefault();
                let form = new FormData(e.target);
                try {
                    let res = await fetch('/encrypt', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken, // <-- TAMBAHKAN INI
                            'Accept': 'application/json'
                        },
                        body: form
                    });
                    let data = await res.json();
                    alert(data.message);
                    if (data.download_url) {
                        window.open(data.download_url, '_blank');
                    }
                } catch (error) {
                    console.error('Encryption error:', error);
                    alert('Terjadi kesalahan saat enkripsi.');
                }
            });

            document.querySelector('#decryptForm').addEventListener('submit', async e => {
                e.preventDefault();
                let form = new FormData(e.target);
                try {
                    let res = await fetch('/decrypt', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken, // <-- TAMBAHKAN INI
                            'Accept': 'application/json'
                        },
                        body: form
                    });
                    let data = await res.json();
                    alert(data.message);
                    if (data.download_url) {
                        window.open(data.download_url, '_blank');
                    }
                } catch (error) {
                    console.error('Decryption error:', error);
                    alert('Terjadi kesalahan saat dekripsi. Pastikan file dan kunci cocok.');
                }
            });
        </script>
    </div>

</x-layout>