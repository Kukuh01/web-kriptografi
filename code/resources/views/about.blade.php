<x-layout>
    <x-slot:title>
        Tentang Kami
    </x-slot:title>

    <section class="min-h-screen">
        <div class="my-8">
            <h1 class="text-3xl font-semibold mb-4">Tentang Kami</h1>
            <hr>

            <div class="prose lg:prose-xl">
                {{--
                  Gunakan {!! !!} agar HTML tidak di-escape.
                  Gunakan Str::markdown() untuk mengubah simpanan Markdown Anda menjadi HTML.
                --}}
                {!! Str::markdown($description->value ?? 'Konten belum diisi.') !!}
            </div>

            {{-- Opsional: Link untuk admin --}}
            @auth
                <div class="mt-8 p-4 bg-gray-100 rounded">
                    <a href="{{ route('about.edit') }}" class="text-blue-600 hover:underline">Edit Halaman Ini</a>
                </div>
            @endauth

        </div>
    </section>

</x-layout>
