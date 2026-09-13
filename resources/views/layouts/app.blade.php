<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistem Absensi') - {{ config('app.name', 'SMAN 1 Tajurhalang') }}</title>

    <!-- Vite Styles & Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js', 'resources/js/custom-dropdown.js', 'resources/js/mobile.js', 'resources/js/sweetalert-confirm.js', 'resources/js/confirmation-handlers.js'])
    @stack('styles')
</head>
@php
    $showWelcome = session('show_welcome_notification');
    $welcomeItems = [];

    if ($showWelcome) {
        $welcomeUser = auth()->user();
        if ($welcomeUser) {
            if ($welcomeUser->hasRole('guru')) {
                $welcomeUser->loadMissing('teacher.subjects', 'teacher.classes');
            } elseif ($welcomeUser->hasRole('siswa')) {
                $welcomeUser->loadMissing('student.class');
            } elseif ($welcomeUser->hasRole('parent')) {
                $welcomeUser->loadMissing('parent.students.class');
            }

            $name = $welcomeUser->name;
            $roleLabel = '';

            if ($welcomeUser->hasRole('super_admin')) {
                $roleLabel = str_contains(strtolower($welcomeUser->name), 'operator') ? 'Operator TU' : 'Administrator';
            } elseif ($welcomeUser->hasRole('guru_piket')) {
                $roleLabel = 'Piket';
                $piketNama = session('piket_nama_lengkap') ?? $welcomeUser->name;
                $name = $piketNama;

                $teacher = \App\Models\Teacher::where('name', $piketNama)->first();
                if ($teacher && $teacher->nip) {
                    $welcomeItems[] = 'NIP: ' . e($teacher->nip);
                } else {
                    $parent = \App\Models\StudentParent::where('name', $piketNama)->first();
                    if ($parent && $parent->nik) {
                        $welcomeItems[] = 'NIK: ' . e($parent->nik);
                    }
                }
            } elseif ($welcomeUser->hasRole('guru')) {
                $roleLabel = 'Guru';
                $teacher = $welcomeUser->teacher;
                if ($teacher?->nip) {
                    $welcomeItems[] = 'NIP: ' . e($teacher->nip);
                }
                $mapels = $teacher ? $teacher->subjects->pluck('name')->filter()->join(', ') : null;
                if ($mapels) {
                    $welcomeItems[] = 'Mapel: ' . e($mapels);
                }
                if ($teacher && $teacher->classes->count() > 0) {
                    $welcomeItems[] = 'Wali Kelas: ' . e($teacher->classes->pluck('name')->join(', '));
                }
            } elseif ($welcomeUser->hasRole('siswa')) {
                $roleLabel = 'Siswa';
                $student = $welcomeUser->student;
                if ($student?->nis) {
                    $welcomeItems[] = 'NIS: ' . e($student->nis);
                }
                if ($student?->class?->name) {
                    $welcomeItems[] = 'Kelas: ' . e($student->class->name);
                }
            } elseif ($welcomeUser->hasRole('parent')) {
                $roleLabel = 'Orang Tua';
                $parent = $welcomeUser->parent;
                if ($parent?->nik) {
                    $welcomeItems[] = 'NIK: ' . e($parent->nik);
                }
                if ($parent && $parent->students->count() > 0) {
                    $children = $parent->students->pluck('name')->join(', ');
                    $welcomeItems[] = 'Anak: ' . e($children);
                }
            }

            array_unshift($welcomeItems, 'Selamat datang, ' . e($name), 'Role: ' . e($roleLabel));
        }

        session()->forget('show_welcome_notification');
    }

    $flashSuccess = session('success');
    $flashError   = session('error');
    $flashWarning = session('warning');
    $flashInfo    = session('info');
@endphp
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
                @include('layouts.partials.notifications')

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
            @include('layouts.partials.notifications')

            {{ $slot }}
        </main>

        <!-- Mobile Bottom Navigation (fixed bottom) -->
        @include('layouts.partials.mobile-bottomnav')
    </div>

    @stack('scripts')
    @stack('modals')
</body>
</html>