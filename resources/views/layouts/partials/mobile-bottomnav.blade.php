{{-- Mobile Bottom Navigation — same on ALL pages --}}
<nav class="mobile-bottom-nav" id="mobileBottomNav">
    @php
        $currentRoute = request()->route()->getName();
        $role = auth()->user()->roles->first()?->name;

        // Role-based bottom menus (max 5 items — important ones only)
        if ($role === 'super_admin') {
            $bottomMenus = [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard'],
                ['label' => 'Absensi', 'icon' => 'bi-qr-code-scan', 'route' => 'admin.attendance.today'],
                ['label' => 'Rekap', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.reports.index'],
                ['label' => 'Jadwal', 'icon' => 'bi-calendar-week', 'route' => 'admin.schedules.index'],
                ['label' => 'Data', 'icon' => 'bi-database', 'route' => 'admin.students.index'],
            ];
        } elseif ($role === 'guru') {
            $bottomMenus = [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'teacher.dashboard'],
                ['label' => 'Absensi', 'icon' => 'bi-qr-code-scan', 'route' => 'teacher.attendance.input'],
                ['label' => 'Jadwal', 'icon' => 'bi-calendar3', 'route' => 'teacher.schedules'],
                ['label' => 'Rekap', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'teacher.recap'],
                ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
            ];
        } elseif ($role === 'siswa') {
            $bottomMenus = [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'student.dashboard'],
                ['label' => 'Jadwal', 'icon' => 'bi-calendar-week', 'route' => 'student.schedule'],
                ['label' => 'Riwayat', 'icon' => 'bi-clock-history', 'route' => 'student.history'],
                ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
            ];
        } else {
            $bottomMenus = [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
            ];
        }
    @endphp

    @foreach($bottomMenus as $menu)
        @php
            $isActive = false;
            if (isset($menu['route'])) {
                $prefix = explode('.', $menu['route'])[0] . '.';
                // Exact match for dashboard
                if ($menu['route'] === 'admin.dashboard' || $menu['route'] === 'teacher.dashboard' || $menu['route'] === 'student.dashboard' || $menu['route'] === 'dashboard') {
                    $isActive = $currentRoute === $menu['route'];
                } else {
                    $isActive = str_starts_with($currentRoute ?? '', $prefix);
                }
            }
        @endphp

        <a href="{{ route($menu['route']) }}" class="mobile-bottom-nav-item {{ $isActive ? 'active' : '' }}">
            <span class="mobile-bottom-nav-icon"><i class="bi {{ $menu['icon'] }}"></i></span>
            <span class="mobile-bottom-nav-label">{{ $menu['label'] }}</span>
        </a>
    @endforeach
</nav>