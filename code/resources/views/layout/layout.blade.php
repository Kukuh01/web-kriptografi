<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Website Saya' }}</title>

    {{-- Memuat CSS yang sudah dikompilasi oleh Vite --}}
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    {{-- Memanggil komponen Navbar --}}
    <x-navbar />

    {{-- Konten utama halaman akan disisipkan di sini --}}
    <main class="container mx-auto min-h-screen">
        {{ $slot }}
    </main>

    {{-- Memanggil komponen Footer --}}
    <x-footer />

</body>
</html>