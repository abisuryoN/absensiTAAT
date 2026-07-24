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
        {{-- Avatar with quick dropdown --}}
        <div class="mobile-avatar-wrapper dropdown">
            <button class="mobile-avatar-btn dropdown-toggle" type="button" id="mobileUserMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu pengguna" style="background:none;border:none;padding:0;cursor:pointer;display:flex;align-items:center;">
                @if(auth()->user()->avatar)
                    <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="" class="mobile-avatar-img" style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                @else
                    <img src="{{ auth()->user()->profile_photo_url }}"
                         alt="Avatar"
                         class="mobile-avatar-img user-avatar-element"
                         style="width:34px;height:34px;border-radius:50%;object-fit:cover;"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff&size=128&bold=true'"
                    >
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