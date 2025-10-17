<x-layout>
    <x-slot:title>
        Homepage
    </x-slot:title>

    <x-hero/>

    <div class="bg-neutral-800 p-6 flex justify-around">

        <div class="space-y-6 w-md">
            <div class="bg-gray-400 w-md h-80 rounded-2xl">

            </div>
            <form class="flex flex-col items-center" id="encryptForm" enctype="multipart/form-data">
                @csrf
                <label class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 rounded cursor-pointer">
                Pilih File
                <input type="file" name="image" class="hidden" required>
                </label>
                <button class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded w-full mt-3">Enkripsi Gambar</button>
            </form>
        </div>
        <div class="space-y-6 w-md">
            <div class="bg-gray-400 w-md h-80 rounded-2xl">

            </div>
            <form class="flex flex-col items-center" id="decryptForm" enctype="multipart/form-data" >
                <label class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded cursor-pointer">
                Pilih File
                <input type="file" name="file" class="hidden" required>
                </label>
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
                            'X-CSRF-TOKEN': csrfToken,
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
                            'X-CSRF-TOKEN': csrfToken,
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