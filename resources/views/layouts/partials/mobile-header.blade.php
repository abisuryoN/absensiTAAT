{{-- Mobile Sticky Header — same on ALL pages --}}
<header class="mobile-header" id="mobileHeader">
    <div class="mobile-header-inner">
        <div class="mobile-header-left">
            {{-- Hamburger menu toggle — opens drawer from left --}}
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Buka menu navigasi">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            <h1 class="mobile-page-title">{{ $title ?? 'Sistem Absensi' }}</h1>
        </div>
        <div class="mobile-header-right">
            {{-- Avatar with quick dropdown --}}
            <div class="mobile-avatar-wrapper dropdown">
                <button class="mobile-avatar-btn dropdown-toggle" type="button" id="mobileUserMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu pengguna">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="" class="mobile-avatar-img">
                    @else
                        <div class="mobile-avatar-placeholder">
                            {{ substr(auth()->user()->name, 0, 1) }}
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
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger fs-7 rounded-2">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
