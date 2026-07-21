{{-- 
SHARED MENU RENDERER — digunakan oleh sidebar desktop dan drawer mobile.
Satu sumber data menu, satu logika active-state.

USAGE:
    @include('layouts.partials.sidebar-menu-items', ['variant' => 'desktop'])
    @include('layouts.partials.sidebar-menu-items', ['variant' => 'mobile'])

PARAMETER:
    $variant — 'desktop' atau 'mobile' untuk menentukan CSS class
--}}

@php
    $user = auth()->user();
    $role = $user->roles->first()?->name;

    /**
     * Helper: menentukan apakah suatu route sedang aktif.
     *
     * @param string $routeName Nama route dasar (contoh: 'admin.students.index')
     * @return bool
     */
    if (!function_exists('isMenuItemActive')) {
    function isMenuItemActive($routeName)
    {
        // Exact match untuk dashboard
        if (in_array($routeName, ['admin.dashboard', 'teacher.dashboard', 'student.dashboard', 'parent.dashboard', 'piket.dashboard', 'dashboard'])) {
            return request()->routeIs($routeName);
        }

        // Route profile.* (exact prefix)
        if (str_contains($routeName, 'profile.')) {
            return request()->routeIs('profile.*');
        }

        $parts = explode('.', $routeName);

        // --- CRUD routes (3+ segment dengan aksi index/create/edit/show): gunakan wildcard ---
        if (count($parts) >= 3) {
            $lastSegment = end($parts);
            $crudActions = ['index', 'create', 'edit', 'show', 'store', 'update', 'destroy'];
            if (in_array($lastSegment, $crudActions)) {
                $wildcard = $parts[0] . '.' . $parts[1] . '.*';
                return request()->routeIs($wildcard);
            }
            // Non-CRUD routes (admin.attendance.scan, admin.attendance.today) → exact match
            return request()->routeIs($routeName);
        }

        // Untuk route 2-segment: exact match
        if (count($parts) === 2) {
            return request()->routeIs($routeName);
        }

        // Fallback
        return request()->routeIs($routeName);
    }
    }

    // ─── Role-based menu groups ─────────────────────────────────
    if ($role === 'super_admin') {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard',          'icon' => 'bi-speedometer2',       'route' => 'admin.dashboard'],
                ['label' => 'Absensi Gerbang',    'icon' => 'bi-qr-code-scan',       'route' => 'admin.attendance.scan'],
                ['label' => 'Rekap Hari Ini',     'icon' => 'bi-calendar-check',     'route' => 'admin.attendance.today'],
            ],
            'MASTER DATA' => [
                ['label' => 'Data Siswa',         'icon' => 'bi-people',             'route' => 'admin.students.index'],
                ['label' => 'Data Orang Tua',     'icon' => 'bi-person-heart',       'route' => 'admin.parents.index'],
                ['label' => 'Data Guru',          'icon' => 'bi-person-badge',       'route' => 'admin.teachers.index'],
                ['label' => 'Data Kelas',         'icon' => 'bi-building',           'route' => 'admin.classes.index'],
                ['label' => 'Tahun Ajaran',       'icon' => 'bi-calendar-check',     'route' => 'admin.academic-years.index'],
                ['label' => 'Mulai Tahun Ajaran Baru', 'icon' => 'bi-arrow-repeat',  'route' => 'admin.academic-years.transition.index'],
                ['label' => 'Semester',           'icon' => 'bi-calendar-range',     'route' => 'admin.semesters.index'],
                ['label' => 'Jurusan',            'icon' => 'bi-diagram-3',          'route' => 'admin.majors.index'],
                ['label' => 'Mata Pelajaran',     'icon' => 'bi-book',               'route' => 'admin.subjects.index'],
                ['label' => 'Jadwal Pelajaran',   'icon' => 'bi-calendar3',          'route' => 'admin.schedules.index'],
                ['label' => 'Hari Libur',         'icon' => 'bi-calendar-event',     'route' => 'admin.holidays.index'],
            ],
            'LAPORAN & PENGATURAN' => [
                ['label' => 'Laporan Absensi',    'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.reports.index'],
                ['label' => 'Import Data',        'icon' => 'bi-file-earmark-arrow-up',  'route' => 'admin.imports.index'],
                ['label' => 'Manajemen Akun',     'icon' => 'bi-shield-lock',            'route' => 'admin.accounts.index'],
                ['label' => 'Pengaturan Sistem',  'icon' => 'bi-gear',                   'route' => 'admin.settings.index'],
            ],
            'SUPER ADMIN' => [
                ['label' => 'Manajemen Super Admin', 'icon' => 'bi-person-gear',         'route' => 'admin.super-admins.index'],
                ['label' => 'Akun Guru Piket',        'icon' => 'bi-person-badge',        'route' => 'admin.guru-piket-accounts.index'],
                ['label' => 'Log Aktivitas',          'icon' => 'bi-journal-text',        'route' => 'admin.activity-logs.index'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil',             'icon' => 'bi-person-circle',      'route' => 'profile.edit'],
            ],
        ];
    } elseif ($role === 'guru') {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard',          'icon' => 'bi-speedometer2',       'route' => 'teacher.dashboard'],
                ['label' => 'Jadwal Mengajar',    'icon' => 'bi-calendar-event',     'route' => 'teacher.schedules'],
                ['label' => 'Rekap Mengajar',     'icon' => 'bi-file-earmark-text',  'route' => 'teacher.recap'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil',             'icon' => 'bi-person-circle',      'route' => 'profile.edit'],
            ],
        ];
    } elseif ($role === 'siswa') {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard',          'icon' => 'bi-speedometer2',       'route' => 'student.dashboard'],
                ['label' => 'QR Code Absensi',    'icon' => 'bi-qr-code',            'route' => 'student.qrcode'],
                ['label' => 'Jadwal Pelajaran',   'icon' => 'bi-calendar3',          'route' => 'student.schedule'],
                ['label' => 'Riwayat Hadir',      'icon' => 'bi-clock-history',      'route' => 'student.history'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil',             'icon' => 'bi-person-circle',      'route' => 'profile.edit'],
            ],
        ];
    } elseif ($role === 'parent') {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard',          'icon' => 'bi-speedometer2',       'route' => 'parent.dashboard'],
                ['label' => 'Rekap Harian',       'icon' => 'bi-calendar-check',     'route' => 'parent.rekap_harian'],
                ['label' => 'Rekap Bulanan',      'icon' => 'bi-calendar-month',     'route' => 'parent.rekap_bulanan'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil',             'icon' => 'bi-person-circle',      'route' => 'profile.edit'],
            ],
        ];
    } elseif ($role === 'guru_piket') {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard',          'icon' => 'bi-speedometer2',       'route' => 'piket.dashboard'],
                ['label' => 'Absensi Gerbang',    'icon' => 'bi-qr-code-scan',       'route' => 'piket.scan'],
                ['label' => 'Rekap Hari Ini',     'icon' => 'bi-calendar-check',     'route' => 'piket.rekap'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil',             'icon' => 'bi-person-circle',      'route' => 'profile.edit'],
            ],
        ];
    } else {
        $menuGroups = [
            'MENU UTAMA' => [
                ['label' => 'Dashboard',          'icon' => 'bi-speedometer2',       'route' => 'dashboard'],
            ],
            'LAINNYA' => [
                ['label' => 'Profil',             'icon' => 'bi-person-circle',      'route' => 'profile.edit'],
            ],
        ];
    }
@endphp

{{-- ──────────────────────────────────────────────────────────────── --}}
{{-- RENDER: DESKTOP VARIANT                                          --}}
{{-- ──────────────────────────────────────────────────────────────── --}}
@if(!isset($variant) || $variant === 'desktop')
    @foreach($menuGroups as $sectionTitle => $items)
        <li class="nav-item-header sidebar-section-label">{{ $sectionTitle }}</li>
        @foreach($items as $item)
            @php $isActive = isMenuItemActive($item['route']); @endphp
            <li class="sidebar-menu-item">
                <a href="{{ route($item['route']) }}"
                   class="nav-link {{ $isActive ? 'active' : '' }}"
                   title="{{ $item['label'] }}">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    @endforeach
@endif

{{-- ──────────────────────────────────────────────────────────────── --}}
{{-- RENDER: MOBILE VARIANT                                           --}}
{{-- ──────────────────────────────────────────────────────────────── --}}
@if(isset($variant) && $variant === 'mobile')
    @foreach($menuGroups as $sectionTitle => $items)
        <div class="drawer-section-label">{{ $sectionTitle }}</div>
        @foreach($items as $item)
            @php $isActive = isMenuItemActive($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               class="drawer-nav-item {{ $isActive ? 'active' : '' }}">
                <i class="bi {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    @endforeach
@endif