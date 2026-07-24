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

    @if(session('show_welcome_notification'))
        @php
            $welcomeUser = auth()->user();
            if ($welcomeUser) {
                if ($welcomeUser->hasRole('guru')) {
                    $welcomeUser->loadMissing('teacher.subjects', 'teacher.classes');
                } elseif ($welcomeUser->hasRole('siswa')) {
                    $welcomeUser->loadMissing('student.class');
                } elseif ($welcomeUser->hasRole('parent')) {
                    $welcomeUser->loadMissing('parent.students.class');
                }
            }

            $profilePhotoUrl = $welcomeUser ? $welcomeUser->profile_photo_url : '';
            $name = $welcomeUser ? $welcomeUser->name : '';
            $roleLabel = '';
            $additionalInfoHtml = '';

            if ($welcomeUser) {
                if ($welcomeUser->hasRole('super_admin')) {
                    if (str_contains(strtolower($welcomeUser->name), 'operator')) {
                        $roleLabel = 'Operator TU';
                    } else {
                        $roleLabel = 'Administrator';
                    }
                } elseif ($welcomeUser->hasRole('guru_piket')) {
                    $roleLabel = 'Piket';
                    $piketNama = session('piket_nama_lengkap') ?? $welcomeUser->name;
                    $name = $piketNama;
                    
                    $nipOrNik = null;
                    $teacher = \App\Models\Teacher::where('name', $piketNama)->first();
                    if ($teacher) {
                        $nipOrNik = 'NIP: ' . $teacher->nip;
                    } else {
                        $parent = \App\Models\StudentParent::where('name', $piketNama)->first();
                        if ($parent) {
                            $nipOrNik = 'NIK: ' . $parent->nik;
                        }
                    }
                    
                    if ($nipOrNik) {
                        $additionalInfoHtml = '<div class="d-flex justify-content-between"><span class="text-muted">ID/NIP/NIK:</span><span class="fw-semibold">' . e($nipOrNik) . '</span></div>';
                    }
                } elseif ($welcomeUser->hasRole('guru')) {
                    $roleLabel = 'Guru';
                    $teacher = $welcomeUser->teacher;
                    $nip = $teacher->nip ?? '-';
                    $mapels = $teacher ? $teacher->subjects->pluck('name')->join(', ') : '-';
                    
                    $additionalInfoHtml = '<div class="d-flex justify-content-between mb-1"><span class="text-muted">NIP:</span><span class="fw-semibold">' . e($nip) . '</span></div>';
                    $additionalInfoHtml .= '<div class="d-flex justify-content-between mb-1"><span class="text-muted">Mapel:</span><span class="fw-semibold text-end" style="max-width: 180px;">' . e($mapels ?: '-') . '</span></div>';
                    
                    if ($teacher && $teacher->classes->count() > 0) {
                        $waliKelas = $teacher->classes->pluck('name')->join(', ');
                        $additionalInfoHtml .= '<div class="d-flex justify-content-between"><span class="text-muted">Wali Kelas:</span><span class="fw-semibold">' . e($waliKelas) . '</span></div>';
                    }
                } elseif ($welcomeUser->hasRole('siswa')) {
                    $roleLabel = 'Siswa';
                    $student = $welcomeUser->student;
                    $nis = $student->nis ?? '-';
                    $kelas = $student->class->name ?? '-';
                    
                    $additionalInfoHtml = '<div class="d-flex justify-content-between mb-1"><span class="text-muted">NIS:</span><span class="fw-semibold">' . e($nis) . '</span></div>';
                    $additionalInfoHtml .= '<div class="d-flex justify-content-between"><span class="text-muted">Kelas:</span><span class="fw-semibold">' . e($kelas) . '</span></div>';
                } elseif ($welcomeUser->hasRole('parent')) {
                    $roleLabel = 'Orang Tua';
                    $parent = $welcomeUser->parent;
                    $nik = $parent->nik ?? '-';
                    
                    $additionalInfoHtml = '<div class="d-flex justify-content-between mb-2"><span class="text-muted">NIK:</span><span class="fw-semibold">' . e($nik) . '</span></div>';
                    
                    if ($parent && $parent->students->count() > 0) {
                        $additionalInfoHtml .= '<div class="mt-2"><span class="text-muted d-block mb-1 text-start">Anak:</span>';
                        foreach ($parent->students as $child) {
                            $additionalInfoHtml .= '<div class="fw-semibold ps-2 text-start" style="font-size: 0.82rem;">• ' . e($child->name) . '</div>';
                        }
                        $additionalInfoHtml .= '</div>';
                    }
                }
            }
            
            session()->forget('show_welcome_notification');
        @endphp

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            html: `
                                <div class="text-center py-2">
                                    <div class="mb-3 position-relative d-inline-block">
                                        <img src="{{ $profilePhotoUrl }}" alt="Foto Profil" class="rounded-circle object-fit-cover shadow-sm border border-3 border-success-subtle animate__animated animate__pulse animate__infinite" style="width: 90px; height: 90px; transition: all 0.3s ease;">
                                        <span class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle" style="width: 16px; height: 16px;"></span>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-1">Selamat Datang 👋</h4>
                                    <p class="text-muted mb-3" style="font-size: 0.9rem;">Selamat datang kembali, <strong>{{ $name }}</strong></p>
                                    
                                    <div class="card bg-light border-0 rounded-3 p-3 text-start mx-auto mb-3" style="max-width: 320px; font-size: 0.85rem; line-height: 1.5; color: #475569;">
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1" style="border-color: #e2e8f0 !important;">
                                            <span class="text-muted">Role:</span>
                                            <span class="fw-bold text-success">{{ $roleLabel }}</span>
                                        </div>
                                        {!! $additionalInfoHtml !!}
                                    </div>
                                    
                                    <p class="text-muted mb-0 small text-center" style="font-size: 0.8rem; font-style: italic;">
                                        Semoga aktivitas Anda hari ini berjalan lancar.
                                    </p>
                                </div>
                            `,
                            showConfirmButton: true,
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#10b981',
                            showCloseButton: true,
                            showClass: {
                                popup: 'animate__animated animate__fadeInUp animate__faster'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutDown animate__faster'
                            },
                            customClass: {
                                popup: 'rounded-4 shadow border-0',
                                confirmButton: 'btn btn-success px-4 py-2 fw-semibold rounded-3'
                            },
                            buttonsStyling: false
                        });
                    }
                }, 300);
            });
        </script>
    @endif
</body>
</html>