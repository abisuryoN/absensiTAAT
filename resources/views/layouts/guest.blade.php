<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistem Absensi SMAN 1 Tajurhalang') }}</title>

    <!-- Vite Styles & Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center py-5 px-3" style="background: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(139, 92, 246, 0.08) 0px, transparent 50%);">
        <div class="mb-4 text-center">
            <span class="fs-2 fw-bold tracking-tight text-indigo-700">SMAN 1 TAJURHALANG</span>
            <p class="text-muted fs-7 mt-1">Sistem Informasi Absensi Siswa & Pelajaran</p>
        </div>

        <div class="card glass-card border-0 shadow-lg p-4 w-100" style="max-width: 440px;">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
