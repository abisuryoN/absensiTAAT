{{-- Mobile Drawer — IDENTIK dengan sidebar desktop --}}
@php
    $currentRoute = request()->route()->getName();
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

    // Helper untuk cek active route
    function isActiveRoute($routeName, $currentRoute) {
        if (!$currentRoute) return false;
        // Exact match for dashboard
        if (in_array($routeName, ['admin.dashboard', 'teacher.dashboard', 'student.dashboard', 'dashboard'])) {
            return $currentRoute === $routeName;
        }
        // Profile routes
        if (str_contains($routeName, 'profile.')) {
            return str_starts_with($currentRoute, 'profile.');
        }
        // Prefix match for grouped routes
        $prefix = explode('.', $routeName)[0] . '.';
        return str_starts_with($currentRoute, $prefix);
    }

    // Role-based menu groups (IDENTIK dengan sidebar desktop)
    if ($role === 'super_admin') {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard'],
                ['label' => 'Absensi Gerbang', 'icon' => 'bi-qr-code-scan', 'route' => 'admin.attendance.scan'],
                ['label' => 'Rekap Hari Ini', 'icon' => 'bi-calendar-check', 'route' => 'admin.attendance.today'],
            ],
            'MASTER DATA' => [
                ['label' => 'Data Siswa', 'icon' => 'bi-people', 'route' => 'admin.students.index'],
                ['label' => 'Data Orang Tua', 'icon' => 'bi-person-heart', 'route' => 'admin.parents.index'],
                ['label' => 'Data Guru', 'icon' => 'bi-person-badge', 'route' => 'admin.teachers.index'],
                ['label' => 'Data Kelas', 'icon' => 'bi-building', 'route' => 'admin.classes.index'],
                ['label' => 'Tahun Ajaran', 'icon' => 'bi-calendar-check', 'route' => 'admin.academic-years.index'],
                ['label' => 'Semester', 'icon' => 'bi-calendar-range', 'route' => 'admin.semesters.index'],
                ['label' => 'Jurusan', 'icon' => 'bi-diagram-3', 'route' => 'admin.majors.index'],
                ['label' => 'Mata Pelajaran', 'icon' => 'bi-book', 'route' => 'admin.subjects.index'],
                ['label' => 'Jadwal Pelajaran', 'icon' => 'bi-calendar3', 'route' => 'admin.schedules.index'],
                ['label' => 'Hari Libur', 'icon' => 'bi-calendar-event', 'route' => 'admin.holidays.index'],
                ['label' => 'Laporan Absensi', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.reports.index'],
                ['label' => 'Import Data', 'icon' => 'bi-file-earmark-arrow-up', 'route' => 'admin.imports.index'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
            ],
        ];

        if (class_exists(\App\Models\Settings::class) && \App\Models\Settings::count() > 0) {
            $menuGroups['LAINNYA'][] = ['label' => 'Pengaturan', 'icon' => 'bi-gear', 'route' => 'admin.settings.index'];
        }
    } elseif ($role === 'guru') {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'teacher.dashboard'],
                ['label' => 'Jadwal Mengajar', 'icon' => 'bi-calendar-event', 'route' => 'teacher.schedules'],
                ['label' => 'Absensi Mapel', 'icon' => 'bi-check2-square', 'route' => 'teacher.attendance.input'],
                ['label' => 'Rekap Mengajar', 'icon' => 'bi-file-earmark-text', 'route' => 'teacher.recap'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
            ],
        ];
    } elseif ($role === 'siswa') {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'student.dashboard'],
                ['label' => 'QR Code Absensi', 'icon' => 'bi-qr-code', 'route' => 'student.qrcode'],
                ['label' => 'Jadwal Pelajaran', 'icon' => 'bi-calendar3', 'route' => 'student.schedule'],
                ['label' => 'Riwayat Hadir', 'icon' => 'bi-clock-history', 'route' => 'student.history'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
            ],
        ];
    } else {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
            ],
        ];
    }
@endphp

{{-- Overlay (tap to close) --}}
<div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>

<aside class="mobile-drawer" id="mobileDrawer">
    {{-- ============================================================ --}}
    {{-- HEADER SIDEBAR — Logo & Branding (IDENTIK desktop)            --}}
    {{-- ============================================================ --}}
    <div class="drawer-header">
        <a href="{{ route($role === 'super_admin' ? 'admin.dashboard' : ($role === 'guru' ? 'teacher.dashboard' : ($role === 'siswa' ? 'student.dashboard' : 'dashboard'))) }}" class="drawer-logo-link">
            <img src="{{ asset('images/logo-sma.png') }}" alt="Logo" class="drawer-logo-img">
            <div class="drawer-title-container">
                <span class="drawer-title-line1">SMAN 1</span>
                <span class="drawer-title-line2">Tajurhalang</span>
            </div>
        </a>
        <span class="drawer-badge">Official</span>
    </div>

    <hr class="drawer-divider">

    {{-- ============================================================ --}}
    {{-- USER PROFILE SECTION (IDENTIK desktop)                       --}}
    {{-- ============================================================ --}}
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
            @if($identityNumber)
                <span class="drawer-profile-role" style="font-size:0.65rem;color:#94a3b8;">{{ $identityNumber }}</span>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- NAVIGATION — Grouped with section titles (IDENTIK desktop)   --}}
    {{-- ============================================================ --}}
    <nav class="drawer-nav">
        @foreach($menuGroups as $sectionTitle => $items)
            <div class="drawer-section-label">{{ $sectionTitle }}</div>
            @foreach($items as $item)
                @php
                    $isActive = isActiveRoute($item['route'], $currentRoute);
                @endphp
                <a href="{{ route($item['route']) }}" class="drawer-nav-item mobile-drawer-link {{ $isActive ? 'active' : '' }}">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>

    {{-- ============================================================ --}}
    {{-- BOTTOM — Logout (IDENTIK desktop)                            --}}
    {{-- ============================================================ --}}
    <div class="drawer-actions">
        <form method="POST" action="{{ route('logout') }}" class="w-100">
            @csrf
            <button type="submit" class="drawer-action-btn mobile-drawer-action-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>