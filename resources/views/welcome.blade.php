<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'App' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body class="bg-gray-100 text-gray-900 overflow-x-hidden">

    <main class="min-h-screen">
        <div class="min-h-screen bg-gray-50 flex flex-col">

            {{-- Header --}}
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
                    <a href="/" class="flex items-center space-x-3 text-xl font-bold text-yellow-500">
                        <img src="/logo-sukma.png" alt="Logo" class="w-14 h-14 object-contain" />
                        <span>CBT-SUKMA-KARYA-SEJATI</span>
                    </a>

                    {{-- Navigasi kanan --}}
                    <div class="flex items-center space-x-4">
                        @guest
                            <a href="{{ route('login') }}"
                            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-100">
                                Login
                            </a>
                            <a href="{{ route('register') }}"
                            class="px-4 py-2 text-sm font-medium text-white bg-yellow-500 rounded-md hover:bg-yellow-600">
                                Register
                            </a>
                        @endguest
                    </div>
                </div>
            </header>

            <main class="flex-grow flex flex-col justify-center items-center text-center px-6 py-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">
                    Selamat Datang di <span class="text-yellow-500">CBT PT SUKMA KARYA SEJATI</span>
                </h1>
                <p class="text-gray-600 text-lg max-w-2xl mb-6">
                    Sistem Ujian Berbasis Komputer bagi murid yang akan menjalani ujian di PT Sukma Karya Sejati.
                    Lihat jadwal dan ikuti ujian.
                </p>
            </main>
        </div>
    </main>

    <footer class="bg-white shadow px-6 py-4 text-sm text-gray-500 text-center">
        &copy; {{ date('Y') }} CBT - PT.Sukma Karya Sejati.
    </footer>

</body>

<script>



</script>
</html>
