<x-app-layout>
@section('title', 'Akun Guru Piket')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1 fw-semibold">
                <i class="bi bi-person-badge me-2 text-info"></i>Akun Guru Piket
            </h1>
            <p class="text-muted small mb-0">Kelola akun login untuk petugas piket gerbang.</p>
        </div>
        <a href="{{ route('admin.guru-piket-accounts.create') }}" class="btn btn-primary align-self-start align-self-md-auto">
            <i class="bi bi-person-plus me-1"></i> Tambah Akun Guru Piket
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Info Note --}}
    <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
        <i class="bi bi-info-circle fs-5 flex-shrink-0 mt-1"></i>
        <div class="small">
            <strong>Tentang Akun Guru Piket:</strong>
            {{-- Short version on mobile --}}
            <span class="d-md-none">
                Akun bersama untuk perangkat scan gerbang. Password default: <code>piket123</code>.
            </span>
            {{-- Full version on desktop --}}
            <span class="d-none d-md-inline">
                Akun ini adalah akun bersama yang digunakan di perangkat scan gerbang.
                Setelah login, petugas mengisi nama masing-masing untuk sesi piket. Satu akun bisa dipakai di banyak perangkat sekaligus.
                Password default saat reset: <code>piket123</code>.
            </span>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Nama Akun</th>
                            <th>Email Login</th>
                            <th>Tanggal Dibuat</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $i => $account)
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;flex-shrink:0;">
                                        <i class="bi bi-person-badge text-info"></i>
                                    </div>
                                    <div class="fw-semibold">{{ $account->name }}</div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $account->email }}</td>
                            <td class="text-muted small">{{ $account->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-center">
                                @if($account->is_active)
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    {{-- Toggle Active --}}
                                    <form method="POST"
                                          action="{{ route('admin.guru-piket-accounts.toggle-active', $account) }}"
                                          onsubmit="return confirm('{{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ addslashes($account->name) }}?')">
                                        @csrf
                                        @method('PATCH')
                                        @if($account->is_active)
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-slash-circle me-1"></i>Nonaktifkan
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-check-circle me-1"></i>Aktifkan
                                            </button>
                                        @endif
                                    </form>

                                    {{-- Delete --}}
                                    <form method="POST"
                                          action="{{ route('admin.guru-piket-accounts.destroy', $account) }}"
                                          onsubmit="return confirm('Hapus akun {{ addslashes($account->name) }}? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-person-badge fs-3 d-block mb-2 opacity-50"></i>
                                Belum ada akun Guru Piket. Klik "Tambah Akun Guru Piket" untuk membuat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</x-app-layout>