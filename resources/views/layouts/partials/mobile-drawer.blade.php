@php
    $user = auth()->user();
    $role = $user->roles->first()?->name;

    // Get NIS for students or NIP for teachers
    $identityNumber = '';
    if ($role === 'super_admin') {
        $identityNumber = 'Administrator';
    } elseif ($role === 'guru' && $user->teacher) {
        $identityNumber = 'NIP: ' . ($user->teacher->employee_id_number ?? $user->teacher->nip ?? '-');
    } elseif ($role === 'siswa' && $user->student) {
        $identityNumber = 'NIS: ' . ($user->student->student_id_number ?? $user->student->nis ?? '-');
    }
@endphp

{{-- Overlay (tap to close) --}}
<div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>

<aside class="mobile-drawer" id="mobileDrawer">
    {{-- ============================================================ --}}
    {{-- HEADER — Logo, School Name & Official Badge                  --}}
    {{-- ============================================================ --}}
    <div class="drawer-header">
        <a href="{{ route($role === 'super_admin' ? 'admin.dashboard' : ($role === 'guru' ? 'teacher.dashboard' : ($role === 'siswa' ? 'student.dashboard' : 'dashboard'))) }}" class="drawer-logo-link">
            <img src="{{ asset('images.png') }}" alt="Logo" class="drawer-logo-img">
            <div class="drawer-title-container">
                <span class="drawer-title-line1">SMAN 1</span>
                <span class="drawer-title-line2">Tajurhalang</span>
            </div>
        </a>
        <span class="badge badge-official drawer-badge">Official</span>
        <button id="mobileDrawerClose" class="drawer-close-btn" aria-label="Tutup menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- ============================================================ --}}
    {{-- TAHUN AJARAN — Dropdown (super_admin) / badge read-only (lainnya) --}}
    {{-- ============================================================ --}}
    <div class="drawer-tahun-ajaran-wrap">
        @include('layouts.partials.sidebar-academic-year')
    </div>

    {{-- ============================================================ --}}
    {{-- NAVIGATION — Gunakan shared partial agar konsisten dengan desktop --}}
    {{-- ============================================================ --}}
    <nav class="drawer-nav">
        @include('layouts.partials.sidebar-menu-items', ['variant' => 'mobile'])
    </nav>

    {{-- ============================================================ --}}
    {{-- BOTTOM — User Profile & Logout                               --}}
    {{-- ============================================================ --}}
    <div class="drawer-footer">
        {{-- User Profile --}}
        <div class="drawer-profile">
            <div class="drawer-avatar-wrap">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="" class="drawer-avatar-img">
                @else
                    <div class="drawer-avatar-placeholder">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <span class="drawer-online-dot"></span>
            </div>
            <div class="drawer-profile-text">
                <p class="drawer-profile-name">{{ $user->name }}</p>
                <span class="drawer-profile-role">{{ $role ?? 'User' }}</span>
            </div>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" class="w-100">
            @csrf
            <button type="submit" class="drawer-logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
