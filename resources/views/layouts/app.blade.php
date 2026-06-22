<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .titulo-con-logo {
            width: 100%;
            margin-bottom: 20px;
        }

        .titulo-con-logo td {
            vertical-align: middle;
        }

        .logo {
            width: 80px;
        }

        .titulo {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .seccion {
            margin-top: 20px;
        }
    </style>

    <!-- Preload CSS -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="icon" type="image/png" href="{{ asset('ordenador.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Tabler Icons CDN Webfont -->
    <link rel="stylesheet" href="https://unpkg.com/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/pelias-geocoder/dist/Control.Geocoder.css" />

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/pelias-geocoder/dist/Control.Geocoder.min.js"></script>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        window.userId = @json(auth()->user()->id);
        window.userRoleUpper = @json(strtoupper(trim(auth()->user()->rol ?? '')));
    </script>

    @livewireStyles
    @stack('styles')
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        <!-- Page Content -->
        <main class="bg-blue-50 dark:bg-gray-900 min-h-screen">
        {{ $slot}}


        </main>
        <x-footer></x-footer>
    </div>

    <button
        id="notification-sound-toggle"
        type="button"
        class="fixed bottom-5 right-5 z-[2000] inline-flex h-11 w-11 items-center justify-center rounded-full bg-gray-500 text-white shadow-lg transition hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        aria-label="Activar o silenciar sonidos de notificación"
        aria-pressed="false"
        title="Sonidos de notificación silenciados"
    >
        <i id="notification-sound-icon" class="ti ti-volume-off" aria-hidden="true"></i>
    </button>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @livewireScripts
    @stack('scripts')
</body>

</html>
