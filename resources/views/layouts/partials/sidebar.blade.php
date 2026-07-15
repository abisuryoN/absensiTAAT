@php
    $schoolProfile = \App\Models\SchoolProfile::first();
@endphp
<div class="sidebar d-flex flex-column flex-shrink-0">
    <!-- Sidebar Header (Photo 3 style) -->
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none text-dark gap-2">
            @if($schoolProfile && $schoolProfile->logo)
                <img src="{{ asset('storage/' . $schoolProfile->logo) }}" alt="Logo" width="32" height="32" class="rounded-circle object-fit-cover">
            @else
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; font-weight: 600; font-size: 0.8rem;">
                    {{ substr($schoolProfile->name ?? 'AS', 0, 2) }}
                </div>
            @endif
            <div class="d-flex flex-column">
                <span class="fs-6 fw-bold tracking-tight text-dark">MyAbsen</span>
            </div>
        </a>
        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 fs-9 rounded-3 px-2 py-0.5">
            Official
        </span>
    </div>
    
    <!-- Sidebar Navigation Menu (Scrollable container) -->
    <div class="sidebar-nav-container flex-grow-1">
        <ul class="nav nav-pills flex-column mt-3">
            <!-- 1. Super Admin Menu -->
            @if(auth()->user()->hasRole('super_admin'))
                <li class="nav-item-header text-uppercase text-muted px-4 py-2 fs-8 fw-bold">Menu Utama</li>
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.attendance.scan') }}" class="nav-link {{ request()->routeIs('admin.attendance.scan') ? 'active' : '' }}">
                        <i class="bi bi-qr-code-scan"></i>
                        <span>Absensi Gerbang</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.attendance.today') }}" class="nav-link {{ request()->routeIs('admin.attendance.today') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check"></i>
                        <span>Rekap Hari Ini</span>
                    </a>
                </li>

                <li class="nav-item-header text-uppercase text-muted px-4 py-2 mt-3 fs-8 fw-bold">Master Data</li>
                <li>
                    <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Data Siswa</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.parents.index') }}" class="nav-link {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}">
                        <i class="bi bi-person-heart"></i>
                        <span>Data Orang Tua</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.teachers.index') }}" class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span>Data Guru</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span>Data Kelas</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.academic-years.index') }}" class="nav-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check"></i>
                        <span>Tahun Ajaran</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.semesters.index') }}" class="nav-link {{ request()->routeIs('admin.semesters.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-range"></i>
                        <span>Semester</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.majors.index') }}" class="nav-link {{ request()->routeIs('admin.majors.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i>
                        <span>Jurusan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                        <i class="bi bi-book"></i>
                        <span>Mata Pelajaran</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.schedules.index') }}" class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span>Jadwal Pelajaran</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.holidays.index') }}" class="nav-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event"></i>
                        <span>Hari Libur</span>
                    </a>
                </li>

                <li class="nav-item-header text-uppercase text-muted px-4 py-2 mt-3 fs-8 fw-bold">Laporan & Pengaturan</li>
                <li>
                    <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Laporan Absensi</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.imports.index') }}" class="nav-link {{ request()->routeIs('admin.imports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        <span>Import Data</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Pengaturan Sistem</span>
                    </a>
                </li>
            @endif

            <!-- 2. Guru Menu -->
            @if(auth()->user()->hasRole('guru'))
                <li class="nav-item-header text-uppercase text-muted px-4 py-2 fs-8 fw-bold">Menu Guru</li>
                <li>
                    <a href="{{ route('teacher.dashboard') }}" class="nav-link {{ request()->routeIs('teacher.dashboard') && !request()->routeIs('teacher.attendance.*') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.schedules') }}" class="nav-link {{ request()->routeIs('teacher.schedules') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event"></i>
                        <span>Jadwal Mengajar</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.dashboard') }}" class="nav-link {{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-square"></i>
                        <span>Absensi Mapel</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.recap') }}" class="nav-link {{ request()->routeIs('teacher.recap') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Rekap Mengajar</span>
                    </a>
                </li>
            @endif

            <!-- 3. Siswa Menu -->
            @if(auth()->user()->hasRole('siswa'))
                <li class="nav-item-header text-uppercase text-muted px-4 py-2 fs-8 fw-bold">Menu Siswa</li>
                <li>
                    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('student.qrcode') }}" class="nav-link {{ request()->routeIs('student.qrcode') ? 'active' : '' }}">
                        <i class="bi bi-qr-code"></i>
                        <span>QR Code Absensi</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('student.schedule') }}" class="nav-link {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span>Jadwal Pelajaran</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('student.history') }}" class="nav-link {{ request()->routeIs('student.history') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i>
                        <span>Riwayat Hadir</span>
                    </a>
                </li>
            @endif

            <!-- Bottom Watermark (Photo 3 style) -->
            <li class="px-4 py-3 mt-4 text-center">
                <span class="text-muted fs-8 d-block" style="font-family: monospace; letter-spacing: -0.5px;">
                    &lt;/&gt; Dibuat oleh <strong class="text-dark">@antigravity</strong>
                </span>
            </li>
        </ul>
    </div>
    
    <!-- User Profile Dropdown Box (Photo 3 style) -->
    <div class="dropdown mt-auto p-3 border-top border-dark border-opacity-10 bg-light bg-opacity-25">
        <button class="w-100 btn btn-link text-decoration-none dropdown-toggle p-0 d-flex align-items-center justify-content-between text-dark" type="button" id="sidebarUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="d-flex align-items-center gap-2 text-start">
                <div class="position-relative">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="" width="36" height="36" class="rounded-circle border border-2 border-white shadow-sm">
                    @else
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:36px; height:36px; font-weight:600; font-size:0.95rem;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle animate-pulse" style="width: 10px; height: 10px;"></span>
                </div>
                <div class="overflow-hidden">
                    <p class="mb-0 fw-bold text-dark fs-7 text-truncate" style="max-width: 155px;">{{ auth()->user()->name }}</p>
                    <span class="text-muted d-block fs-8">Klik untuk menu</span>
                </div>
            </div>
            <i class="bi bi-chevron-expand text-muted flex-shrink-0"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-dark border-0 shadow-lg p-2 rounded-3 w-100" aria-labelledby="sidebarUserMenu" style="margin-bottom: 5px;">
            <li class="px-3 py-2 border-bottom border-secondary border-opacity-20 mb-2">
                <span class="d-block text-white fw-bold fs-7">{{ auth()->user()->name }}</span>
                <span class="text-muted fs-8 text-uppercase">{{ auth()->user()->roles->first()?->name ?? 'User' }}</span>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 px-3 py-2 fs-7 rounded-2" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person text-muted"></i>
                    <span>Profil Saya</span>
                </a>
            </li>
            <li>
                <hr class="dropdown-divider my-2">
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 px-3 py-2 text-danger bg-transparent border-0 w-100 text-start fs-7 rounded-2">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>

<style>
.fs-7 { font-size: 0.85rem; }
.fs-8 { font-size: 0.75rem; }
.fs-9 { font-size: 0.7rem; }
.nav-item-header {
    letter-spacing: 0.08em;
}
.sidebar .nav-link {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
