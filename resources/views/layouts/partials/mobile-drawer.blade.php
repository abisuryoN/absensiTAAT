@php
    $schoolProfile = \App\Models\SchoolProfile::first();
    $user = auth()->user();
    $role = $user->roles->first()?->name;

    // Identity number per role
    $identityNumber = '';
    if ($role === 'super_admin') {
        $identityNumber = 'Administrator';
    } elseif ($role === 'guru' && $user->teacher) {
        $identityNumber = 'NIP: ' . ($user->teacher->employee_id_number ?? $user->teacher->nip ?? '-');
    } elseif ($role === 'siswa' && $user->student) {
        $identityNumber = 'NIS: ' . ($user->student->student_id_number ?? $user->student->nis ?? '-');
    } elseif ($role === 'parent' && $user->studentParent) {
        $identityNumber = 'NIK: ' . ($user->studentParent->nik ?? '-');
    } elseif ($role === 'guru_piket') {
        $identityNumber = 'Petugas Piket';
    }

    // Dashboard link per role
    $dashboardRoute = match($role) {
        'super_admin' => 'admin.dashboard',
        'guru'        => 'teacher.dashboard',
        'siswa'       => 'student.dashboard',
        'parent'      => 'parent.dashboard',
        'guru_piket'  => 'piket.scan',
        default       => 'dashboard',
    };
@endphp

{{-- Overlay (tap to close) --}}
<div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>

<aside class="mobile-drawer" id="mobileDrawer">
    {{-- ============================================================ --}}
    {{-- HEADER — Logo, School Name & Official Badge                  --}}
    {{-- ============================================================ --}}
    <div class="drawer-header">
        <div class="drawer-logo-link">
            <img src="{{ asset('images.png') }}" alt="Logo" class="drawer-logo-img" style="width:28px;height:28px;border-radius:50%;object-fit:contain;" onerror="this.src='{{ asset('images/logo-placeholder.svg') }}'">
            <div class="drawer-title-container">
                <span class="drawer-title-line1">{{ $schoolProfile->name ?? 'SMAN 1' }}</span>
                <span class="drawer-title-line2">{{ $schoolProfile->address ? (strlen($schoolProfile->address) > 15 ? substr($schoolProfile->address, 0, 15) . '...' : $schoolProfile->address) : 'Tajurhalang' }}</span>
            </div>
        </div>
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
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar) }}" alt="" class="drawer-avatar-img">
                @else
                    <img src="{{ $user->profile_photo_url }}"
                         alt="Avatar"
                         class="drawer-avatar-img user-avatar-element"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4f46e5&color=fff&size=128&bold=true'"
                    >
                @endif
                <span class="drawer-online-dot"></span>
            </div>
            <div class="drawer-profile-text">
                <p class="drawer-profile-name">{{ $user->name }}</p>
                <span class="drawer-profile-role">{{ $role ?? 'User' }}</span>
            </div>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" id="mobile-drawer-logout-form" class="w-100">
            @csrf
            <button type="button" class="drawer-logout-btn" onclick="handleMobileDrawerLogout(event)">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>