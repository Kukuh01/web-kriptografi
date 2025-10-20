<x-layout>
    <x-slot:title>
        Edit Deskripsi About
    </x-slot:title>

    <head>
        {{-- Ini adalah dependensi untuk SimpleMDE --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.css">
        <script src="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.js"></script>
    </head>

    <section>
        <div class="container my-8">
            <div class="mb-8">
                <h1 class="text-2xl">Edit Deskripsi</h1>
                <p>Edit deskripsi untuk halaman About</p>
            </div>

            {{-- Menampilkan pesan sukses jika ada --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            {{--
              - Arahkan 'action' ke route yang kita buat
              - Ganti 'name' textarea menjadi 'value' agar sesuai nama kolom DB
              - Tambah hidden input untuk 'key'
              - Isi textarea dengan data dari controller
            --}}
            <form action="{{ route('descriptions.update') }}" method="POST">
                @csrf

                {{-- Input tersembunyi untuk memberi tahu controller 'key' mana yang di-update --}}
                <input type="hidden" name="key" value="{{ $description->key ?? 'about' }}">

                {{-- Beri nama 'value' & isi dengan data dari DB --}}
                <textarea id="markdown-editor" name="value">{{ old('value', $description->value) }}</textarea>

                <button type="submit" class="bg-blue-700 text-white py-2 px-4 rounded-lg mt-4">Simpan</button>
            </form>
        </div>

        <script>
            // Inisialisasi SimpleMDE
            var simplemde = new SimpleMDE({ element: document.getElementById("markdown-editor") });
        </script>
    </section>

</x-layout>
