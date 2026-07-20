<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3 sticky-top">
    <div class="container-fluid p-0">
        <!-- Page Title -->
        <span class="navbar-brand mb-0 h1 fw-bold text-dark fs-4">
            @yield('title', 'Sistem Absensi')
        </span>

        <!-- Right Elements -->
        <div class="ms-auto d-flex align-items-center gap-3">
            <!-- School Profile & Active Period Info -->
            <div class="d-none d-md-flex flex-column align-items-end me-3 text-end">
                <span class="text-xs text-muted fw-semibold">TAHUN AJARAN</span>
                <span class="badge bg-blue-50 text-blue-400 fw-bold px-3 py-2 mt-1 fs-8">
                    {{ \App\Models\AcademicYear::active()->first()?->name ?? '-' }} ({{ \App\Models\Semester::active()->first()?->name ?? '-' }})
                </span>
            </div>

            <!-- Profile Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link text-decoration-none dropdown-toggle p-0 d-flex align-items-center gap-2" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="" width="36" height="36" class="rounded-circle shadow-sm">
                    @else
                        <div class="bg-blue-200 text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:36px; height:36px; font-weight:600;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="d-none d-lg-block text-start">
                        <span class="d-block text-dark fw-semibold fs-7 lh-sm">{{ auth()->user()->name }}</span>
                        <span class="text-muted fs-8">{{ auth()->user()->email }}</span>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3 p-2 rounded-3" aria-labelledby="userMenuButton" style="width: 220px;">
                    <li class="px-3 py-2 border-bottom mb-2">
                        <span class="d-block text-dark fw-bold fs-7">{{ auth()->user()->name }}</span>
                        <span class="text-muted fs-8 text-uppercase">{{ auth()->user()->roles->first()?->name }}</span>
                    </li>
                    <li>
                        <a class="dropdown-menu-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark fs-7 rounded-2" href="{{ route('profile.edit') }}">
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
                            <button type="submit" class="dropdown-menu-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-danger bg-transparent border-0 w-100 text-start fs-7 rounded-2">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
.dropdown-menu-item {
    transition: all 0.2s ease;
}
.dropdown-menu-item:hover {
    background-color: #f8fafc;
}
.bg-blue-50 {
    background-color: #e0e7ff;
}
.text-blue-400 {
    color: #4338ca;
}
.text-xs { font-size: 0.7rem; }
.fs-8 { font-size: 0.75rem; }
</style>
