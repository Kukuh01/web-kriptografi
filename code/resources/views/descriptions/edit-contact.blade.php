<x-layout>
    <x-slot:title>
        Edit Deskripsi Contact {{-- UBAH INI --}}
    </x-slot:title>

    {{-- ... (bagian <head> dengan SimpleMDE sama saja) ... --}}
    <head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.css">
        <script src="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.js"></script>
    </head>

    <section>
        <div class="container my-8">
            <div class="mb-8">
                <h1 class="text-2xl">Edit Deskripsi</h1>
                <p>Edit deskripsi untuk halaman Contact</p> {{-- UBAH INI --}}
            </div>

            {{-- ... (Form message sukses sama saja) ... --}}

            <form action="{{ route('descriptions.update') }}" method="POST">
                @csrf

                {{-- INI PALING PENTING: Ubah 'value' dari 'about' ke 'contact' --}}
                <input type="hidden" name="key" value="{{ $description->key ?? 'contact' }}"> {{-- UBAH INI --}}

                <textarea id="markdown-editor" name="value">{{ old('value', $description->value) }}</textarea>

                <button type="submit" class="bg-blue-700 text-white py-2 px-4 rounded-lg mt-4">Simpan</button>
            </form>
        </div>

        <script>
            // Inisialisasi SimpleMDE (sama saja)
            var simplemde = new SimpleMDE({ element: document.getElementById("markdown-editor") });
        </script>
    </section>

</x-layout>
