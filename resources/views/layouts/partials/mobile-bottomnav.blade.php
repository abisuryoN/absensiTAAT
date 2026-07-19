{{-- Mobile Bottom Navigation — same on ALL pages --}}
<nav class="mobile-bottom-nav" id="mobileBottomNav">
    @php
        $currentRoute = request()->route()->getName();
        $role = auth()->user()->roles->first()?->name;

        // Role-based bottom menus (max 5 items — important ones only)
        if ($role === 'super_admin') {
            $bottomMenus = [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard'],
                ['label' => 'Gerbang', 'icon' => 'bi-qr-code-scan', 'route' => 'admin.attendance.scan'],
                ['label' => 'Hari Ini', 'icon' => 'bi-calendar-check', 'route' => 'admin.attendance.today'],
                ['label' => 'Data', 'icon' => 'bi-people', 'route' => 'admin.students.index'],
                ['label' => 'Rekap', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.reports.index'],
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
                if ($menu['route'] === 'profile.edit') {
                    $isActive = str_starts_with($currentRoute ?? '', 'profile.');
                } else {
                    $isActive = $currentRoute === $menu['route'];
                }
            }
        @endphp

        <a href="{{ route($menu['route']) }}" class="bottom-nav-item {{ $isActive ? 'active' : '' }}">
            <i class="bi {{ $menu['icon'] }}"></i>
            <span>{{ $menu['label'] }}</span>
        </a>
    @endforeach
</nav>