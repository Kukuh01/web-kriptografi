

<x-layout>
<x-slot:title>
    Edit Deskripsi
</x-slot:title>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.css">
    <script src="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.js"></script>
</head>
<section >
    <div class="container my-8">
        <div class="mb-8">
            <h1 class="text-2xl">Edit Deskripsi</h1>
            <p>Edit deskripsi untuk halaman About</p>
        </div>
        <form action="{{ route('save.description') }}" method="POST">
            @csrf
            <textarea id="markdown-editor" name="description">{{ $description }}</textarea>
            <button type="submit" class="bg-blue-700 text-white py-2 px-4 rounded-lg">Simpan</button>
        </form>
    </div>

    <script>
        var simplemde = new SimpleMDE({ element: document.getElementById("markdown-editor") });
    </script>
</section>

</x-layout>
