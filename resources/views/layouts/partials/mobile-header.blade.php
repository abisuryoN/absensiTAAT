{{-- Mobile Sticky Header — same on ALL pages --}}
<header class="mobile-header" id="mobileHeader">
    <div class="mobile-header-left">
        {{-- Hamburger menu toggle — opens drawer from left --}}
        <button class="mobile-hamburger" id="mobileMenuToggle" aria-label="Buka menu navigasi">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="mobile-header-title">{{ $title ?? 'Sistem Absensi' }}</h1>
    </div>
    <div class="mobile-header-right">
        {{-- Simulation badge (admin only) --}}
        @if(auth()->user()?->hasAnyRole(['super_admin', 'admin']) && app(\App\Services\DateTimeService::class)->isSimulationEnabled())
            <a href="{{ route('admin.settings.index') }}"
               class="badge bg-warning text-dark text-decoration-none fw-bold me-2"
               style="font-size:.7rem; border-radius:999px; padding:4px 8px;">
                ⚡ SIM
            </a>
        @endif

        {{-- Avatar with quick dropdown --}}
        <div class="mobile-avatar-wrapper dropdown">
            <button class="mobile-avatar-btn dropdown-toggle" type="button" id="mobileUserMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu pengguna" style="background:none;border:none;padding:0;cursor:pointer;display:flex;align-items:center;">
                @if(auth()->user()->profile_photo)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" 
                         alt="Profile Photo" 
                         class="mobile-avatar-img user-avatar-element" 
                         style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                @else
                    <div style="width:34px;height:34px;border-radius:50%;background:#4f46e5;display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.9rem;font-weight:700;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-2 rounded-3" aria-labelledby="mobileUserMenu" style="width: 200px;">
                <li class="px-3 py-2 border-bottom mb-2">
                    <span class="d-block text-dark fw-bold fs-7">{{ auth()->user()->name }}</span>
                    <span class="text-muted fs-8 text-uppercase">{{ auth()->user()->roles->first()?->name }}</span>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 fs-7 rounded-2" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person text-muted"></i> Profil Saya
                    </a>
                </li>
                <li><hr class="dropdown-divider my-2"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger fs-7 rounded-2" style="background:none;border:none;width:100%;text-align:left;">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>