<x-guest-layout>
    <h4 class="fw-bold text-center mb-4">Masuk ke Sistem Absensi</h4>

    <!-- Session Status -->
    @if(session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="form-control @error('email') is-invalid @enderror"
                placeholder="admin@sman1tajurhalang.sch.id">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="form-control @error('password') is-invalid @enderror"
                placeholder="Masukkan password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-3 form-check">
            <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
            <label for="remember_me" class="form-check-label fs-7">Ingat saya</label>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </div>

        @if (Route::has('password.request'))
            <div class="text-center mt-3">
                <a class="text-muted fs-7 text-decoration-none" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
