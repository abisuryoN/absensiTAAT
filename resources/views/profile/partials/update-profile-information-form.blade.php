<section>
    <header>
        <h2 class="fw-semibold fs-5 text-dark mb-2">
            Informasi Profil
        </h2>

        <p class="text-muted mb-4">
            Perbarui nama dan alamat email akun Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mb-4">
        @csrf
        @method('patch')

        <div class="form-group mb-3">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @if ($errors->has('name'))
                <div class="error-message">{{ $errors->first('name') }}</div>
            @endif
        </div>

        <div class="form-group mb-4">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @if ($errors->has('email'))
                <div class="error-message">{{ $errors->first('email') }}</div>
            @endif

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3">
                    <p class="text-dark">
                        Email Anda belum terverifikasi.

                        <button form="send-verification" class="btn btn-link p-0 text-decoration-none text-primary">
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="success-message mt-2">
                            Link verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn-save">
                Simpan
            </button>

            @if (session('status') === 'profile-updated')
                <p class="text-success mb-0 fw-semibold" style="font-size: 0.875rem;">
                    <i class="bi bi-check-circle-fill me-1"></i> Profil berhasil diperbarui.
                </p>
            @endif
        </div>
    </form>
</section>