<div class="sidebar d-flex flex-column flex-shrink-0">
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none text-white gap-2">
            <span class="fs-5 fw-bold tracking-tight">SMAN 1 TH</span>
        </a>
    </div>
    
    <ul class="nav nav-pills flex-column mb-auto mt-3">
        <!-- 1. Super Admin Menu -->
        @if(auth()->user()->hasRole('super_admin'))
            <li class="nav-item-header text-uppercase text-muted px-4 py-2 fs-7 fw-bold">Menu Utama</li>
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

            <li class="nav-item-header text-uppercase text-muted px-4 py-2 mt-3 fs-7 fw-bold">Master Data</li>
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

            <li class="nav-item-header text-uppercase text-muted px-4 py-2 mt-3 fs-7 fw-bold">Laporan & Pengaturan</li>
            <li>
                <a href="#" class="nav-link">
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
            <li class="nav-item-header text-uppercase text-muted px-4 py-2 fs-7 fw-bold">Guru</li>
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
            <li class="nav-item-header text-uppercase text-muted px-4 py-2 fs-7 fw-bold">Siswa</li>
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
    </ul>
    
    <div class="p-3 border-top border-secondary border-opacity-10 mt-auto bg-dark">
        <div class="d-flex align-items-center text-white gap-2">
            @if(auth()->user()->avatar)
                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="" width="32" height="32" class="rounded-circle">
            @else
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-weight:600; font-size:0.85rem;">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
            @endif
            <div class="overflow-hidden">
                <p class="mb-0 fw-semibold text-truncate fs-7">{{ auth()->user()->name }}</p>
                <span class="text-muted d-block text-truncate fs-8 text-uppercase">
                    {{ auth()->user()->roles->first()?->name ?? 'User' }}
                </span>
            </div>
        </div>
    </div>
</div>

<style>
.fs-7 { font-size: 0.85rem; }
.fs-8 { font-size: 0.75rem; }
.nav-item-header {
    letter-spacing: 0.08em;
    font-size: 0.7rem;
}
</style>
