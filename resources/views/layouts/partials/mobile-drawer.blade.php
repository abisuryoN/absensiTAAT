{{-- Mobile Drawer: Slide in from RIGHT with overlay --}}
@php
    $currentRoute = request()->route()->getName();
    $user = auth()->user();
    $role = $user->roles->first()?->name;
@endphp

{{-- Overlay (tap to close) --}}
<div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>

<aside class="mobile-drawer" id="mobileDrawer">
    {{-- Close button (X) at top-right --}}
    <button class="mobile-drawer-header-close" id="mobileDrawerClose" aria-label="Tutup menu">
        <i class="bi bi-x-lg"></i>
    </button>

    {{-- User Profile Header --}}
    <div class="mobile-drawer-profile">
        <div class="mobile-drawer-avatar">
            @if($user->avatar)
                <img src="{{ Storage::url($user->avatar) }}" alt="">
            @else
                <div class="mobile-drawer-avatar-placeholder">
                    {{ substr($user->name, 0, 2) }}
                </div>
            @endif
        </div>
        <div class="mobile-drawer-userinfo">
            <span class="mobile-drawer-name">{{ $user->name }}</span>
            <span class="mobile-drawer-role">{{ $role ?? 'User' }}</span>
        </div>
    </div>

    {{-- Navigation Menu — role-based --}}
    <nav class="mobile-drawer-nav">
        @if($role === 'super_admin')
            @php
                $menuItems = [
                    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard'],
                    ['label' => 'Absensi Hari Ini', 'icon' => 'bi-qr-code-scan', 'route' => 'admin.attendance.today'],
                    ['label' => 'Rekap', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.reports.index'],
                    ['label' => 'Data', 'icon' => 'bi-database', 'route' => 'admin.students.index'],
                    ['label' => 'Kelas', 'icon' => 'bi-building', 'route' => 'admin.classes.index'],
                    ['label' => 'Guru', 'icon' => 'bi-person-badge', 'route' => 'admin.teachers.index'],
                    ['label' => 'Jadwal', 'icon' => 'bi-calendar-week', 'route' => 'admin.schedules.index'],
                    ['label' => 'Mata Pelajaran', 'icon' => 'bi-book', 'route' => 'admin.subjects.index'],
                    ['label' => 'Jurusan', 'icon' => 'bi-diagram-3', 'route' => 'admin.majors.index'],
                    ['label' => 'Tahun Ajaran', 'icon' => 'bi-calendar-range', 'route' => 'admin.academic-years.index'],
                    ['label' => 'Semester', 'icon' => 'bi-layers', 'route' => 'admin.semesters.index'],
                    ['label' => 'Orang Tua', 'icon' => 'bi-people', 'route' => 'admin.parents.index'],
                    ['label' => 'Libur Nasional', 'icon' => 'bi-tree', 'route' => 'admin.holidays.index'],
                    ['label' => 'Import Data', 'icon' => 'bi-upload', 'route' => 'admin.imports.index'],
                    ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
                ];

                if (class_exists(\App\Models\Settings::class) && \App\Models\Settings::count() > 0) {
                    $menuItems[] = ['label' => 'Pengaturan', 'icon' => 'bi-gear', 'route' => 'admin.settings.index'];
                }
            @endphp
        @elseif($role === 'guru')
            @php
            $menuItems = [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'teacher.dashboard'],
                ['label' => 'Absensi', 'icon' => 'bi-qr-code-scan', 'route' => 'teacher.attendance.input'],
                ['label' => 'Jadwal', 'icon' => 'bi-calendar-week', 'route' => 'teacher.schedules'],
                ['label' => 'Rekap', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'teacher.recap'],
                ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
            ];
            @endphp
        @elseif($role === 'siswa')
            @php
                $menuItems = [
                    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'student.dashboard'],
                    ['label' => 'Jadwal', 'icon' => 'bi-calendar-week', 'route' => 'student.schedule'],
                    ['label' => 'Riwayat', 'icon' => 'bi-clock-history', 'route' => 'student.history'],
                    ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
                ];
            @endphp
        @else
            @php
                $menuItems = [
                    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                    ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
                ];
            @endphp
        @endif

        <ul class="mobile-drawer-menu">
            @foreach($menuItems as $item)
                @php
                    $prefix = explode('.', $item['route'])[0] . '.';
                    $isActive = str_starts_with($currentRoute ?? '', $prefix);
                    // Exact match for dashboard
                    if ($item['route'] === 'admin.dashboard' || $item['route'] === 'teacher.dashboard' || $item['route'] === 'student.dashboard' || $item['route'] === 'dashboard') {
                        $isActive = $currentRoute === $item['route'];
                    }
                    // Profile
                    if (str_contains($item['route'], 'profile.')) {
                        $isActive = str_starts_with($currentRoute ?? '', 'profile.');
                    }
                @endphp
                <li>
                    <a href="{{ route($item['route']) }}" class="mobile-drawer-link {{ $isActive ? 'active' : '' }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- Bottom Actions: Quick Access & Logout --}}
    <div class="mobile-drawer-actions">
        {{-- Mini bottom nav inside drawer (replicates bottom nav) --}}
        @php
            if ($role === 'super_admin') {
                $drawerBottomMenus = [
                    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard'],
                    ['label' => 'Absensi', 'icon' => 'bi-qr-code-scan', 'route' => 'admin.attendance.today'],
                    ['label' => 'Rekap', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.reports.index'],
                    ['label' => 'Jadwal', 'icon' => 'bi-calendar-week', 'route' => 'admin.schedules.index'],
                ];
            } elseif ($role === 'guru') {
                $drawerBottomMenus = [
                    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'teacher.dashboard'],
                    ['label' => 'Absensi', 'icon' => 'bi-qr-code-scan', 'route' => 'teacher.attendance.input'],
                    ['label' => 'Jadwal', 'icon' => 'bi-calendar3', 'route' => 'teacher.schedules'],
                    ['label' => 'Rekap', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'teacher.recap'],
                ];
            } elseif ($role === 'siswa') {
                $drawerBottomMenus = [
                    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'student.dashboard'],
                    ['label' => 'Jadwal', 'icon' => 'bi-calendar-week', 'route' => 'student.schedule'],
                    ['label' => 'Riwayat', 'icon' => 'bi-clock-history', 'route' => 'student.history'],
                ];
            } else {
                $drawerBottomMenus = [
                    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ];
            }
        @endphp
        <div class="drawer-inline-bottomnav">
            @foreach($drawerBottomMenus as $item)
                @php
                    $isActive = str_starts_with($currentRoute ?? '', explode('.', $item['route'])[0] . '.');
                    if ($item['route'] === 'admin.dashboard' || $item['route'] === 'teacher.dashboard' || $item['route'] === 'student.dashboard' || $item['route'] === 'dashboard') {
                        $isActive = $currentRoute === $item['route'];
                    }
                @endphp
                <a href="{{ route($item['route']) }}" class="drawer-inline-bn-item {{ $isActive ? 'active' : '' }}">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>

        <form method="POST" action="{{ route('logout') }}" class="w-100">
            @csrf
            <button type="submit" class="mobile-drawer-action-btn logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
