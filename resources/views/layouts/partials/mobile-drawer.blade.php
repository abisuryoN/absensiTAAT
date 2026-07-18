{{-- Mobile Drawer: Slide in from LEFT with overlay --}}
@php
    $currentRoute = request()->route()->getName();
    $user = auth()->user();
    $role = $user->roles->first()?->name;

    // Get NIS for students or NIP for teachers
    $identityNumber = '';
    if ($role === 'siswa' && $user->student) {
        $identityNumber = 'NIS: ' . ($user->student->student_id_number ?? $user->student->nis ?? '-');
    } elseif ($role === 'guru' && $user->teacher) {
        $identityNumber = 'NIP: ' . ($user->teacher->employee_id_number ?? $user->teacher->nip ?? '-');
    } elseif ($role === 'super_admin') {
        $identityNumber = 'Administrator';
    }
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
            @if($identityNumber)
                <span class="mobile-drawer-nisnip">{{ $identityNumber }}</span>
            @endif
        </div>
    </div>

    {{-- Navigation Menu — role-based, matching desktop sidebar hierarchy --}}
    <nav class="mobile-drawer-nav">
        @if($role === 'super_admin')
            @php
                $menuItems = [
                    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard'],
                    ['label' => 'Absensi Gerbang', 'icon' => 'bi-qr-code-scan', 'route' => 'admin.attendance.scan'],
                    ['label' => 'Rekap Hari Ini', 'icon' => 'bi-calendar-check', 'route' => 'admin.attendance.today'],
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
                ['label' => 'Jadwal Mengajar', 'icon' => 'bi-calendar-event', 'route' => 'teacher.schedules'],
                ['label' => 'Absensi Mapel', 'icon' => 'bi-check2-square', 'route' => 'teacher.attendance.input'],
                ['label' => 'Rekap Mengajar', 'icon' => 'bi-file-earmark-text', 'route' => 'teacher.recap'],
                ['label' => 'Profil', 'icon' => 'bi-person-circle', 'route' => 'profile.edit'],
            ];
            @endphp
        @elseif($role === 'siswa')
            @php
                $menuItems = [
                    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'student.dashboard'],
                    ['label' => 'QR Code Absensi', 'icon' => 'bi-qr-code', 'route' => 'student.qrcode'],
                    ['label' => 'Jadwal Pelajaran', 'icon' => 'bi-calendar3', 'route' => 'student.schedule'],
                    ['label' => 'Riwayat Hadir', 'icon' => 'bi-clock-history', 'route' => 'student.history'],
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

    {{-- Bottom Actions: Settings & Logout --}}
    <div class="mobile-drawer-actions">
        {{-- Settings button --}}
        <a href="{{ $role === 'super_admin' && class_exists(\App\Models\Settings::class) && \App\Models\Settings::count() > 0 ? route('admin.settings.index') : '#' }}" class="mobile-drawer-action-btn settings {{ $role !== 'super_admin' || !class_exists(\App\Models\Settings::class) || \App\Models\Settings::count() === 0 ? 'd-none' : '' }}">
            <i class="bi bi-gear"></i>
            <span>Pengaturan</span>
        </a>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" class="w-100">
            @csrf
            <button type="submit" class="mobile-drawer-action-btn logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>