<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'GearHub - Phụ kiện công nghệ' }}</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased h-full bg-white">
    <div class="min-h-full flex flex-col lg:flex-row">
        
        <!-- Left Side: Red Brand Hero (Matching Figma Page 7 & 8) -->
        <div class="lg:w-1/2 bg-primary text-white p-8 sm:p-12 lg:p-16 flex flex-col justify-between relative overflow-hidden min-h-[260px] lg:min-h-screen">
            <!-- Decorative glow overlays -->
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-black/15 blur-2xl pointer-events-none"></div>

            <!-- Top Logo -->
            <div class="relative z-10">
                <a href="{{ url('/') }}" class="inline-block group">
                    <span class="text-3xl font-black tracking-tight text-white drop-shadow-sm">
                        GEAR<span class="text-black/80">HUB</span>
                    </span>
                </a>
            </div>

            <!-- Center Slogan (Exact text from Figma) -->
            <div class="my-8 lg:my-0 relative z-10 max-w-md">
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight text-white drop-shadow-sm">
                    Gear up<br>
                    for every game.
                </h1>
                <p class="mt-4 text-sm sm:text-base text-white/85 font-normal leading-relaxed">
                    Headphones, mouse, keyboards and more.
                </p>
            </div>

            <!-- Footer copyright -->
            <div class="relative z-10 text-xs text-white/60">
                © 2026 GearHub. All rights reserved.
            </div>
        </div>

        <!-- Right Side: Form Container -->
        <div class="lg:w-1/2 flex items-center justify-center p-6 sm:p-12 lg:p-16 bg-white overflow-y-auto">
            <div class="w-full max-w-md py-4">
                {{ $slot }}
            </div>
        </div>

    </div>
</body>
</html>
