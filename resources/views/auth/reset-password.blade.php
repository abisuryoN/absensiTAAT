<x-guest-layout>
    <h4 class="fw-bold text-center mb-4">Reset Password</h4>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        {{-- Password Reset Token --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="email"
                class="form-control @error('email') is-invalid @enderror"
                placeholder="email@contoh.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password Baru --}}
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password Baru</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="form-control @error('password') is-invalid @enderror"
                placeholder="Minimal 8 karakter">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Konfirmasi Password --}}
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="form-control @error('password_confirmation') is-invalid @enderror"
                placeholder="Ulangi password baru">
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                <i class="bi bi-shield-lock me-1"></i> Reset Password
            </button>
        </div>
    </form>
</x-guest-layout>
