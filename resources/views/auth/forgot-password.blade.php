<x-guest-layout>
    <h4 class="fw-bold text-center mb-3">Lupa Password?</h4>
    <p class="text-muted text-center mb-4" style="font-size: 0.875rem;">
        Tidak masalah. Masukkan email Anda dan kami akan mengirimkan link untuk mereset password.
    </p>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="alert alert-success mb-3 py-2" style="font-size: 0.875rem;">
            <i class="bi bi-check-circle-fill me-1"></i>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-4">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                class="form-control @error('email') is-invalid @enderror"
                placeholder="email@contoh.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                <i class="bi bi-envelope me-1"></i> Kirim Link Reset Password
            </button>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="text-muted fs-7 text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke halaman login
            </a>
        </div>
    </form>
</x-guest-layout>
