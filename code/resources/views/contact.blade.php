<x-layout>
    <x-slot:title>
        Hubungi Kami {{-- UBAH INI --}}
    </x-slot:title>

    <section class="min-h-screen">
        <div class="container my-8">
            <h1 class="text-3xl font-bold mb-4">Hubungi Kami</h1> {{-- UBAH INI --}}

            <div class="prose lg:prose-xl">
                {{-- Logika ini tetap sama, hanya variabel $description-nya
                     yang sekarang berisi data 'contact' dari controller --}}
                {!! Str::markdown($description->value ?? 'Konten belum diisi.') !!}
            </div>

            @auth
                <div class="mt-8 p-4 bg-gray-100 rounded">
                    {{-- Arahkan link edit ke route 'contact.edit' --}}
                    <a href="{{ route('contact.edit') }}" class="text-blue-600 hover:underline">Edit Halaman Ini</a> {{-- UBAH INI --}}
                </div>
            @endauth

        </div>
    </section>

</x-layout>
