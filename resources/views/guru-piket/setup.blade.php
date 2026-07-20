<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Piket - Absensi Sekolah</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-card { width: 100%; max-width: 440px; }
        .piket-badge {
            background: #e3f2fd; color: #1565c0;
            border-radius: 20px; padding: 4px 14px;
            font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="setup-card p-3">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-person-badge-fill text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Absensi Gerbang</h4>
                    <span class="piket-badge">GURU PIKET</span>
                    <p class="text-muted mt-2 mb-0 small">Isi nama Anda sebagai petugas piket untuk sesi ini.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show py-2" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('piket.setup.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label fw-semibold">
                            <i class="bi bi-person me-1"></i> Nama Lengkap Petugas Piket
                        </label>
                        <input
                            type="text"
                            id="nama_lengkap"
                            name="nama_lengkap"
                            class="form-control form-control-lg @error('nama_lengkap') is-invalid @enderror"
                            placeholder="Contoh: Abi Suryo Negoro"
                            value="{{ old('nama_lengkap') }}"
                            autocomplete="off"
                            autofocus
                            required
                        >
                        @error('nama_lengkap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted small mt-1">
                            <i class="bi bi-info-circle me-1"></i>
                            Nama ini akan tercatat di setiap absensi yang Anda scan pada sesi ini.
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Mulai Sesi Piket
                        </button>
                    </div>
                </form>

                <hr class="my-4">
                <div class="text-center">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <p class="text-center text-white-50 small mt-3">
            <i class="bi bi-shield-check me-1"></i>
            Setiap perangkat memiliki sesi piket yang terpisah dan independen.
        </p>
    </div>
</body>
</html>