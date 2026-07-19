<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistem Absensi') - {{ config('app.name', 'SMAN 1 Tajurhalang') }}</title>

    <!-- Vite Styles & Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js', 'resources/js/custom-dropdown.js', 'resources/js/mobile.js'])
    @stack('styles')
</head>
<body class="d-flex flex-column h-100">
    {{-- DESKTOP LAYOUT (≥992px) --}}
    <div class="d-none d-lg-flex container-fluid p-0 align-items-stretch min-vh-100 bg-white">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content Area -->
        <div class="d-flex flex-column flex-grow-1 bg-light overflow-auto">
            <!-- Navbar -->
            @include('layouts.partials.navbar')

            <!-- Main Content Slot -->
            <main class="flex-shrink-0 p-4">
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <!-- Footer -->
            @include('layouts.partials.footer')
        </div>
    </div>

    {{-- MOBILE LAYOUT (<992px) --}}
    <div class="d-lg-none mobile-layout">
        <!-- Mobile Header (sticky top) -->
        @include('layouts.partials.mobile-header', ['title' => View::hasSection('title') ? View::getSection('title') : 'Sistem Absensi'])

        <!-- Mobile Drawer (slide from left) -->
        @include('layouts.partials.mobile-drawer')

        <!-- Mobile Content -->
        <main class="mobile-main-content">
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-3 fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            {{ $slot }}
        </main>

        <!-- Mobile Bottom Navigation (fixed bottom) -->
        @include('layouts.partials.mobile-bottomnav')
    </div>

    @stack('scripts')
    @stack('modals')
</body>
</html>