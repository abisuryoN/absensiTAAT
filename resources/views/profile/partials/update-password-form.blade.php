<section>
    <header>
        <h2 class="fw-semibold fs-5 text-dark mb-2">
            Ganti Password
        </h2>

        <p class="text-muted mb-4">
            Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mb-4">
        @csrf
        @method('put')

        <div class="form-group mb-3">
            <label for="update_password_current_password" class="form-label">Password Lama</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
            @if ($errors->updatePassword->has('current_password'))
                <div class="error-message">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        <div class="form-group mb-3">
            <label for="update_password_password" class="form-label">Password Baru</label>
            <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password">
            @if ($errors->updatePassword->has('password'))
                <div class="error-message">{{ $errors->updatePassword->first('password') }}</div>
            @endif
            <small class="text-muted">Minimal 8 karakter.</small>
        </div>

        <div class="form-group mb-4">
            <label for="update_password_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
            @if ($errors->updatePassword->has('password_confirmation'))
                <div class="error-message">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn-save">
                Simpan Password
            </button>

            @if (session('status') === 'password-updated')
                <p class="text-success mb-0 fw-semibold" style="font-size: 0.875rem;">
                    <i class="bi bi-check-circle-fill me-1"></i> Password berhasil diperbarui.
                </p>
            @endif
        </div>
    </form>
</section>