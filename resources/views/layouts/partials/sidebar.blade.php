@php
    $schoolProfile = \App\Models\SchoolProfile::first();
@endphp
<div class="sidebar d-flex flex-column flex-shrink-0 position-relative">
    <!-- Collapse Toggle Button -->
    <button id="toggle-sidebar-btn" class="sidebar-toggle-btn border d-flex align-items-center justify-content-center shadow-sm" onclick="toggleSidebar()">
        <i id="toggle-icon" class="bi bi-chevron-left"></i>
    </button>

    <!-- Sidebar Header: School Profile Card -->
    <div class="sidebar-header d-flex align-items-center justify-content-between gap-3">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none text-dark gap-2">
            <img src="{{ asset('images.png') }}" alt="Logo" width="32" height="32" class="rounded-circle object-fit-cover">
            <div class="d-flex flex-column sidebar-title-container">
                <span class="fw-bold tracking-tight text-dark" style="font-size: 0.95rem; line-height: 1.1;">
                    SMAN 1
                </span>
                <span class="text-muted" style="font-size: 0.75rem; line-height: 1;">Tajurhalang</span>
            </div>
        </a>
        <span class="badge badge-official sidebar-badge">
            Official
        </span>
    </div>
    
    <div class="sidebar-nav-container flex-grow-1">
        <ul class="nav nav-pills flex-column sidebar-menu">
            @include('layouts.partials.sidebar-menu-items', ['variant' => 'desktop'])
        </ul>
    </div>
    
    <!-- User Profile Section -->
    <div class="sidebar-profile-container dropdown mt-auto">
        <button class="w-100 btn btn-link text-decoration-none dropdown-toggle p-0 d-flex align-items-center justify-content-between text-dark" type="button" id="sidebarUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="d-flex align-items-center gap-2 text-start">
                <div class="position-relative">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="" width="36" height="36" class="rounded-circle border border-2 border-white shadow-sm">
                    @else
                        <div class="sidebar-avatar-placeholder">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle animate-pulse" style="width: 10px; height: 10px;"></span>
                </div>
                <div class="overflow-hidden profile-text-container">
                    <p class="mb-0 fw-bold text-dark fs-7 text-truncate" style="max-width: 155px;">{{ auth()->user()->name }}</p>
                    <span class="text-muted d-block fs-8">Klik untuk menu</span>
                </div>
            </div>
            <i class="bi bi-chevron-down text-muted flex-shrink-0 profile-expand-icon"></i>
        </button>
        <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-3 w-100" aria-labelledby="sidebarUserMenu" style="margin-bottom: 5px;">
            <li class="px-3 py-2 border-bottom mb-2">
                <span class="d-block fw-bold fs-7" style="color: #1e293b;">{{ auth()->user()->name }}</span>
                <span class="fs-8 text-uppercase" style="color: #64748b;">{{ auth()->user()->roles->first()?->name ?? 'User' }}</span>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 px-3 py-2 fs-7 rounded-2" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person" style="color: #64748b;"></i>
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

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const toggleIcon = document.getElementById('toggle-icon');
        
        sidebar.classList.toggle('collapsed');
        
        if (sidebar.classList.contains('collapsed')) {
            toggleIcon.className = 'bi bi-chevron-right';
            localStorage.setItem('sidebar-collapsed', 'true');
        } else {
            toggleIcon.className = 'bi bi-chevron-left';
            localStorage.setItem('sidebar-collapsed', 'false');
        }
    }

    (function() {
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.querySelector('.sidebar').classList.add('collapsed');
            document.getElementById('toggle-icon').className = 'bi bi-chevron-right';
        }
    })();
</script>

<style>
.fs-7 { font-size: 0.85rem; }
.fs-8 { font-size: 0.75rem; }
.fs-9 { font-size: 0.7rem; }
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<script>
(function() {
    var container = document.querySelector('.sidebar-nav-container');
    if (!container) return;
    var activeItem = container.querySelector('.nav-link.active');
    if (!activeItem) return;

    // Calling getBoundingClientRect() here forces the browser to do a synchronous
    // layout calculation. Since this is an inline script, it runs BEFORE the browser
    // gets a chance to paint — so scrollTop is set correctly on the very first frame.
    var itemRect = activeItem.getBoundingClientRect();
    var containerRect = container.getBoundingClientRect();
    var offset = itemRect.top - containerRect.top;
    var target = container.scrollTop + offset - container.clientHeight / 2 + activeItem.clientHeight / 2;
    target = Math.max(0, Math.min(target, container.scrollHeight - container.clientHeight));
    container.scrollTop = target;
})();
</script>
