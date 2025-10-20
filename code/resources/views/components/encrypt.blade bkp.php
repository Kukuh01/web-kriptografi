    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-bold text-center text-gray-800 mb-2">
                Image Encryption & Decryption
            </h1>
            <p class="text-center text-gray-600 mb-8">Menggunakan Algoritma ChaCha20</p>

            <!-- Upload Section -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-semibold mb-4 text-gray-700">Upload Gambar</h2>
                <div class="flex items-center justify-center w-full">
                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> atau drag and drop</p>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF (MAX. 10MB)</p>
                        </div>
                        <input id="dropzone-file" type="file" class="hidden" accept="image/*" />
                    </label>
                </div>
                <div id="upload-status" class="mt-4 text-center"></div>
            </div>

            <!-- Control Buttons -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex flex-wrap gap-4 justify-center">
                    <button id="btn-encrypt" disabled class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        🔒 Enkripsi Gambar
                    </button>
                    <button id="btn-decrypt" disabled class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        🔓 Dekripsi Gambar
                    </button>
                    <button id="btn-reset" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        🔄 Reset
                    </button>
                </div>
            </div>

            <!-- Image Display Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Original Image -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700 text-center h-24">Gambar Original</h3>
                    <div id="original-container" class="border-2 border-gray-200 rounded-lg h-64 flex items-center justify-center bg-gray-50">
                        <p class="text-gray-400">Belum ada gambar</p>
                    </div>
                    <button id="download-original" disabled class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                        ⬇️ Download Original
                    </button>
                </div>

                <!-- Encrypted Image -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700 text-center h-24">Gambar Terenkripsi</h3>
                    <div id="encrypted-container" class="border-2 border-gray-200 rounded-lg h-64 flex items-center justify-center bg-gray-50">
                        <p class="text-gray-400">Belum dienkripsi</p>
                    </div>
                    <button id="download-encrypted" disabled class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                        ⬇️ Download Encrypted
                    </button>
                </div>

                <!-- Decrypted Image -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700 text-center h-24">Gambar Hasil Dekripsi</h3>
                    <div id="decrypted-container" class="border-2 border-gray-200 rounded-lg h-64 flex items-center justify-center bg-gray-50">
                        <p class="text-gray-400">Belum didekripsi</p>
                    </div>
                    <button id="download-decrypted" disabled class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                        ⬇️ Download Decrypted
                    </button>
                </div>
            </div>

            <!-- Hash & Binary Comparison -->
            <div id="hash-section" class="bg-white rounded-lg shadow-lg p-6 mt-6 ">
                <h3 class="text-2xl font-semibold mb-4 text-gray-700 text-center">Perbandingan Nilai Hash & Biner</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 text-sm text-left">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-2">Tipe</th>
                                <th class="border p-2">Hash (SHA-256)</th>
                                <th class="border p-2">Cuplikan Biner (64 byte pertama)</th>
                            </tr>
                        </thead>
                        <tbody id="hash-table-body"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        let currentFilename = '';
        function addHashRow(type, hash, binary) {
            const tbody = document.getElementById('hash-table-body');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="border p-2 font-semibold">${type}</td>
                <td class="border p-2 font-mono text-xs break-all">${hash}</td>
                <td class="border p-2 font-mono text-xs break-all">${binary}</td>
            `;
            tbody.appendChild(row);
            document.getElementById('hash-section').classList.remove('hidden');
        }
        let encryptedFilename = '';
        let decryptedFilename = '';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Upload Handler
        document.getElementById('dropzone-file').addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    currentFilename = result.data.filename;
                    document.getElementById('upload-status').innerHTML = `
                        <p class="text-green-600 font-semibold">✅ ${result.message}</p>
                        <p class="text-sm text-gray-600">Ukuran: ${(result.data.size / 1024).toFixed(2)} KB</p>
                    `;
                    
                    document.getElementById('original-container').innerHTML = `
                        <img src="${result.data.path}" alt="Original" class="max-w-full max-h-full object-contain">
                    `;
                    
                    document.getElementById('btn-encrypt').disabled = false;
                    document.getElementById('download-original').disabled = false;
                }
            } catch (error) {
                document.getElementById('upload-status').innerHTML = `
                    <p class="text-red-600 font-semibold">❌ Error: ${error.message}</p>
                `;
            }
        });

        // Encrypt Handler
        document.getElementById('btn-encrypt').addEventListener('click', async () => {
            if (!currentFilename) return;

            try {
                const response = await fetch('/encrypt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ filename: currentFilename })
                });

                const result = await response.json();
                
                if (result.success) {
                    encryptedFilename = result.data.encrypted_filename;
                    
                    document.getElementById('encrypted-container').innerHTML = `
                        <div class="text-center">
                            <p class="text-green-600 font-semibold mb-2">✅ Berhasil Dienkripsi</p>
                            <p class="text-xs text-gray-500 break-all px-2">${encryptedFilename}</p>
                            <div class="mt-2 bg-gray-200 h-32 flex items-center justify-center">
                                <p class="text-gray-600">Data Terenkripsi</p>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('btn-decrypt').disabled = false;
                    if (result.data.hash && result.data.binary_snippet) {
    addHashRow('Original', result.data.hash.original, result.data.binary_snippet.original);
    addHashRow('Encrypted', result.data.hash.encrypted, result.data.binary_snippet.encrypted);
}

                    document.getElementById('download-encrypted').disabled = false;
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        // Decrypt Handler
        document.getElementById('btn-decrypt').addEventListener('click', async () => {
            if (!encryptedFilename) return;

            try {
                const response = await fetch('/decrypt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ encrypted_filename: encryptedFilename })
                });

                const result = await response.json();
                
                if (result.success) {
                    decryptedFilename = result.data.decrypted_filename;
                    
                    document.getElementById('decrypted-container').innerHTML = `
                        <img src="${result.data.decrypted_path}" alt="Decrypted" class="max-w-full max-h-full object-contain">
                    `;
                    
                    document.getElementById('download-decrypted').disabled = false;
                    if (result.data.hash && result.data.binary_snippet) {
    addHashRow('Encrypted (Before Decrypt)', result.data.hash.encrypted, result.data.binary_snippet.encrypted);
    addHashRow('Decrypted', result.data.hash.decrypted, result.data.binary_snippet.decrypted);
}

                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        // Download Handlers
        document.getElementById('download-original').addEventListener('click', () => {
            if (currentFilename) {
                window.location.href = `/download/original/${currentFilename}`;
            }
        });

        document.getElementById('download-encrypted').addEventListener('click', () => {
            if (encryptedFilename) {
                window.location.href = `/download/encrypted/${encryptedFilename}`;
            }
        });

        document.getElementById('download-decrypted').addEventListener('click', () => {
            if (decryptedFilename) {
                window.location.href = `/download/decrypted/${decryptedFilename}`;
            }
        });

        // Reset Handler
        document.getElementById('btn-reset').addEventListener('click', () => {
            location.reload();
        });
    </script>