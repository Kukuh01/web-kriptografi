<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-2">
            Image Encryption & Decryption
        </h1>
        <p class="text-center text-gray-600 mb-8">Menggunakan Algoritma ChaCha20 (Visual Encrypted Image)</p>

        {{-- Upload Section --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Upload Gambar</h2>
            <div class="flex items-center justify-center w-full">
                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> atau drag & drop</p>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF (MAX. 10MB)</p>
                    </div>
                    <input id="dropzone-file" type="file" class="hidden" accept="image/*" />
                </label>
            </div>
            <div id="upload-status" class="mt-4 text-center"></div>
        </div>

        {{-- Control Buttons --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex flex-wrap gap-4 justify-center">
                <button id="btn-encrypt" disabled
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                    🔒 Enkripsi Gambar
                </button>
                <button id="btn-decrypt" disabled
                    class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                    🔓 Dekripsi Gambar
                </button>
                <button id="btn-reset"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                    🔄 Reset
                </button>
            </div>
        </div>

        {{-- Image Display Section --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Original Image --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700 text-center">Gambar Original</h3>
                <div id="original-container"
                    class="border-2 border-gray-200 rounded-lg h-64 flex items-center justify-center bg-gray-50">
                    <p class="text-gray-400">Belum ada gambar</p>
                </div>
                <button id="download-original" disabled
                    class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                    ⬇️ Download Original
                </button>
            </div>

            {{-- Encrypted Image --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700 text-center">Gambar Terenkripsi</h3>
                <div id="encrypted-container"
                    class="border-2 border-gray-200 rounded-lg h-64 flex items-center justify-center bg-gray-50">
                    <p class="text-gray-400">Belum dienkripsi</p>
                </div>
                <button id="download-encrypted" disabled
                    class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                    ⬇️ Download Encrypted
                </button>
            </div>

            {{-- Decrypted Image --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700 text-center">Gambar Hasil Dekripsi</h3>
                <div id="decrypted-container"
                    class="border-2 border-gray-200 rounded-lg h-64 flex items-center justify-center bg-gray-50">
                    <p class="text-gray-400">Belum didekripsi</p>
                </div>
                <button id="download-decrypted" disabled
                    class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                    ⬇️ Download Decrypted
                </button>
            </div>
        </div>

        {{-- Metrics Section --}}
        <div id="metrics-container" class="mt-4 hidden">
        <h2 class="text-lg font-semibold mb-2">🔍 Hasil Analisis Kualitas Citra</h2>
        <table class="table-auto border-collapse border border-gray-300 w-full text-sm">
            <thead class="bg-gray-100">
            <tr>
                <th class="border border-gray-300 px-3 py-2">Metrik</th>
                <th class="border border-gray-300 px-3 py-2">Nilai</th>
            </tr>
            </thead>
            <tbody id="metrics-table"></tbody>
        </table>
        </div>

        <!-- Histogram Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 text-center">📊 Perbandingan Histogram (Original vs Enkripsi)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-center text-gray-600 mb-2">Histogram Gambar Asli</h4>
                    <canvas id="originalHistogram"></canvas>
                </div>
                <div>
                    <h4 class="text-center text-gray-600 mb-2">Histogram Gambar Terenkripsi</h4>
                    <canvas id="encryptedHistogram"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let currentFilename = '', encryptedFilename = '', decryptedFilename = '';

    //Upload Handler
    document.getElementById('dropzone-file').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);

        const res = await fetch('/upload', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });

        const result = await res.json();
        if (result.success) {
            currentFilename = result.data.filename;
            document.getElementById('upload-status').innerHTML = `
                <p class="text-green-600 font-semibold">✅ ${result.message}</p>
                <p class="text-sm text-gray-600">Ukuran: ${(result.data.size / 1024).toFixed(2)} KB</p>
            `;
            document.getElementById('original-container').innerHTML =
                `<img src="${result.data.path}" class="max-w-full max-h-full object-contain">`;
            document.getElementById('btn-encrypt').disabled = false;
            document.getElementById('download-original').disabled = false;
        }
    });

    //Fungsi untuk menghitung histogram dari gambar
    function getHistogramData(imgElement) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = imgElement.naturalWidth;
        canvas.height = imgElement.naturalHeight;
        ctx.drawImage(imgElement, 0, 0);
        const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;

        const histogram = new Array(256).fill(0);
        for (let i = 0; i < imgData.length; i += 4) {
            const brightness = Math.round(0.299 * imgData[i] + 0.587 * imgData[i + 1] + 0.114 * imgData[i + 2]);
            histogram[brightness]++;
        }
        return histogram;
    }

    //Fungsi untuk render chart
    function renderHistogram(canvasId, histogramData, color, label) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Array.from({ length: 256 }, (_, i) => i),
                datasets: [{
                    label,
                    data: histogramData,
                    backgroundColor: color,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: { display: true, text: 'Tingkat Intensitas (0–255)' },
                        ticks: { maxTicksLimit: 16 }
                    },
                    y: {
                        title: { display: true, text: 'Frekuensi Piksel' },
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    //Encrypt Handler
    document.getElementById('btn-encrypt').addEventListener('click', async () => {
        const res = await fetch('/encrypt', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ filename: currentFilename })
        });
        const result = await res.json();

        if (result.success) {
            encryptedFilename = result.data.encrypted_filename;
            document.getElementById('encrypted-container').innerHTML =
                `<img id="encryptedImage" src="${result.data.encrypted_path}" class="max-w-full max-h-full object-contain">`;
            document.getElementById('btn-decrypt').disabled = false;
            document.getElementById('download-encrypted').disabled = false;

            //Menampilkan tabel metrik
            const metrics = result.data.metrics;
            const metricsTable = document.getElementById('metrics-table');
            metricsTable.innerHTML = '';
            for (const [key, value] of Object.entries(metrics)) {
                metricsTable.innerHTML += `
                    <tr>
                        <td class="border border-gray-300 px-3 py-2 font-medium">${key}</td>
                        <td class="border border-gray-300 px-3 py-2">${Number.isFinite(value) ? value.toFixed(4) : value}</td>
                    </tr>
                `;
            }
            document.getElementById('metrics-container').classList.remove('hidden');

            //Menampilkan histogram
            const originalImg = document.querySelector('#original-container img');
            const encryptedImg = document.getElementById('encryptedImage');
            encryptedImg.onload = () => {
                const histOriginal = getHistogramData(originalImg);
                const histEncrypted = getHistogramData(encryptedImg);
                renderHistogram('originalHistogram', histOriginal, 'rgba(54, 162, 235, 0.7)', 'Original');
                renderHistogram('encryptedHistogram', histEncrypted, 'rgba(255, 99, 132, 0.7)', 'Encrypted');
            };
        }
    });

    //Decrypt Handler
    document.getElementById('btn-decrypt').addEventListener('click', async () => {
        const res = await fetch('/decrypt', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ encrypted_filename: encryptedFilename })
        });
        const result = await res.json();

        if (result.success) {
            decryptedFilename = result.data.decrypted_filename;
            document.getElementById('decrypted-container').innerHTML =
                `<img src="${result.data.decrypted_path}" class="max-w-full max-h-full object-contain">`;
            document.getElementById('download-decrypted').disabled = false;

            addHashRow('Encrypted (Before Decrypt)', result.data.hash.encrypted, result.data.binary_snippet.encrypted);
            addHashRow('Decrypted', result.data.hash.decrypted, result.data.binary_snippet.decrypted);
        }
    });

    //Download Handlers
    document.getElementById('download-original').onclick = () => {
        if (currentFilename) window.location.href = `/download/original/${currentFilename}`;
    };
    document.getElementById('download-encrypted').onclick = () => {
        if (encryptedFilename) window.location.href = `/download/encrypted/${encryptedFilename}`;
    };
    document.getElementById('download-decrypted').onclick = () => {
        if (decryptedFilename) window.location.href = `/download/decrypted/${decryptedFilename}`;
    };

    //Reset
    document.getElementById('btn-reset').onclick = () => location.reload();

</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
