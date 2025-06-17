<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Inline script para aplicar dark mode de inmediato --}}
    <script>
        (function () {
            const appearance = '{{ $appearance ?? "system" }}';
            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    {{-- Fondo del HTML para evitar flash blanco al cargar --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    {{-- Precarga de fuentes para evitar re-render de texto --}}
    <link rel="preload" href="/fonts/SF-Pro-Display-Regular.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/SF-Pro-Display-Medium.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/SF-Pro-Display-Semibold.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/SF-Pro-Display-Bold.otf" as="font" type="font/otf" crossorigin>

    {{-- Solo si mantienes fuentes externas, como fallback --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @routes
    @vite(['resources/js/app.ts'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
