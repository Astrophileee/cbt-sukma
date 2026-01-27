<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-yellow-50 via-white to-yellow-100">
        <div class="min-h-screen flex items-center justify-center p-6">
            <div class="w-full max-w-5xl grid md:grid-cols-[1.1fr_1fr] rounded-2xl shadow-2xl overflow-hidden border border-yellow-100 bg-white/80 backdrop-blur">
                <div class="relative hidden md:flex flex-col justify-between p-10 bg-gradient-to-br from-yellow-500 via-yellow-400 to-yellow-600 text-gray-900">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/90 rounded-xl flex items-center justify-center shadow-sm">
                            <img src="{{ asset('logo-sukma.png') }}" alt="Logo Sukma" class="w-8 h-8 object-contain" />
                        </div>
                        <div>
                            <div class="text-lg font-bold">CBT Sukma</div>
                            <div class="text-sm text-gray-800/80">Sistem Ujian Berbasis Komputer</div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="text-2xl font-semibold">Masuk dan kelola ujian dengan cepat.</div>
                        <div class="text-sm text-gray-800/80">Dashboard modern, proses ujian terstruktur, dan laporan nilai lebih rapi.</div>
                    </div>
                    <div class="text-xs text-gray-800/70">&copy; {{ date('Y') }} PT. Sukma Karya Sejati</div>
                </div>
                <div class="p-8 sm:p-10">
                    <div class="md:hidden mb-6 flex items-center gap-3">
                        <div class="w-11 h-11 bg-yellow-500 rounded-xl flex items-center justify-center shadow">
                            <img src="{{ asset('logo-sukma.png') }}" alt="Logo Sukma" class="w-6 h-6 object-contain" />
                        </div>
                        <div>
                            <div class="text-lg font-bold">CBT Sukma</div>
                            <div class="text-xs text-gray-500">Sistem Ujian Berbasis Komputer</div>
                        </div>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
