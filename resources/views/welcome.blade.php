<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Portal Internal - Premiere Group</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#e11d48', // Red-600 like
                        secondary: '#f3f4f6',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
    </style>
</head>

<body class="antialiased bg-white text-gray-900">
    <div class="min-h-screen flex flex-col items-center pt-8 sm:pt-16 pb-10">
        <!-- Header / Nav -->
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center mb-12">
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                    SI
                </div>
                <div class="text-sm border-l-2 border-gray-300 pl-2">
                    <p class="font-bold text-gray-900 leading-tight">SISTEM INTERNAL SAP</p>
                    <p class="text-xs text-gray-500">Premiere Group</p>
                </div>
            </div>
            <div>
                <!-- Theme toggle or User profile could go here -->
                <button
                    class="flex items-center gap-2 text-sm text-gray-500 border border-gray-200 rounded-full px-3 py-1 hover:bg-gray-50">
                    <span>Theme</span>
                </button>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
            <div class="flex flex-col lg:flex-row justify-between items-start gap-10">
                <div class="lg:w-1/2">
                    <span
                        class="inline-block bg-red-100 text-red-600 text-xs font-semibold px-2.5 py-0.5 rounded-full mb-4">
                        ● Internal Access Portal
                    </span>
                    <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight mb-4">
                        Satu pintu untuk semua <br>
                        <span class="text-red-600">sistem internal</span>
                    </h1>
                    <p class="text-gray-600 mb-8 max-w-lg">
                        Pilih modul sesuai kebutuhan: RAB Perjalanan Dinas, Form Pengajuan, Pickup & QC, dan panel
                        lainnya. Akses diatur oleh Tim IT, jika ada perubahan hubungi Tim IT.
                    </p>

                    <div class="flex flex-wrap gap-4 items-center">
                        <a href="#"
                            class="bg-red-600 text-white px-6 py-3 rounded-full font-semibold hover:bg-red-700 transition flex items-center gap-2">
                            Hubungi Tim IT
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                        <span class="text-sm text-gray-500 flex items-center gap-1">
                            <span class="text-green-500">●</span> Terhubung dengan SAP & sistem internal lainnya
                        </span>
                    </div>
                </div>

                <!-- Right Side Card (Ringkasan Akses - Static for now) -->
                <div class="lg:w-1/2 w-full">
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-gray-900">RINGKASAN AKSES</h3>
                                <p class="text-sm text-gray-500">Panel utama yang tersedia</p>
                            </div>
                            <span
                                class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
                                ● AKTIF
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-4">
                            Akses modul diatur oleh Tim IT. Jika ada perubahan hubungi Tim IT.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid Section -->
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Pilih Panel</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($links as $link)
                    <a href="{{ $link->url }}"
                        class="bg-white border boundary border-gray-100 rounded-xl p-6 hover:shadow-lg transition duration-200 flex flex-col h-full group cursor-pointer block">

                        <div class="flex justify-between items-start mb-4">
                            <!-- Icon/Acronym -->
                            <div
                                class="w-10 h-10 bg-red-50 text-red-600 rounded-lg flex items-center justify-center font-bold text-sm">
                                @if($link->icon && str_starts_with($link->icon, 'heroicon'))
                                    {{-- If using Blade Heroicons package, we could use <x-icon />,
                                    but to be safe without knowing installed packages,
                                    we'll just show acronym from title if icon is complex,
                                    or render SVGs if we had them.
                                    For now, let's just use the first 2 letters of Badge Text or Title --}}
                                    {{ strtoupper(substr($link->badge_text ?? $link->title, 0, 2)) }}
                                @elseif($link->icon)
                                    {{ $link->icon }}
                                @else
                                    {{ strtoupper(substr($link->title, 0, 2)) }}
                                @endif
                            </div>

                            <!-- Badge -->
                            @if($link->badge_text)
                                <span class="bg-gray-50 text-gray-600 text-[10px] font-medium px-2 py-1 rounded-md">
                                    {{ $link->badge_text }}
                                </span>
                            @endif
                        </div>

                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-red-600 transition">{{ $link->title }}</h3>

                        <p class="text-sm text-gray-500 mb-6 flex-grow leading-relaxed">
                            {{ $link->description }}
                        </p>

                        <span
                            class="text-red-500 text-sm font-semibold flex items-center gap-1 group-hover:gap-2 transition-all mt-auto">
                            Masuk panel
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</body>

</html>