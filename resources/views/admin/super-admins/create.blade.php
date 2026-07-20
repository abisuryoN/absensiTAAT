<x-app-layout>
@section('title', 'Tambah Super Admin Baru')
<div class="container-fluid px-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-semibold">Tambah Super Admin Baru</h1>
            <p class="text-muted small mb-0">Buat akun baru dengan akses penuh ke seluruh sistem.</p>
        </div>
        <a href="{{ route('admin.super-admins.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <div class="alert alert-warning d-flex gap-2 align-items-start mb-4">
                        <i class="bi bi-shield-exclamation fs-5 flex-shrink-0"></i>
                        <div class="small">
                            Akun Super Admin memiliki akses penuh. Gunakan password yang kuat dan unik.
                            Password tidak bisa di-generate otomatis &mdash; harus diisi secara sengaja.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.super-admins.store') }}">
                        @csrf

                        {{-- Nama Lengkap --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Masukkan nama lengkap"
                                   required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="contoh@email.com"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Email digunakan untuk login. Harus unik di seluruh sistem.</div>
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Minimal 8 karakter, huruf + angka"
                                       required>
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Minimal 8 karakter, harus mengandung huruf dan angka.</div>
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                Konfirmasi Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control"
                                       placeholder="Ulangi password di atas"
                                       required>
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('password_confirmation', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-person-plus me-1"></i> Buat Akun Super Admin
                            </button>
                            <a href="{{ route('admin.super-admins.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function togglePwd(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
@endpush

</x-app-layout>
