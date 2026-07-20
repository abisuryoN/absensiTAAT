<x-app-layout>
@section('title', 'Manajemen Super Admin')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-semibold">Manajemen Super Admin</h1>
            <p class="text-muted small mb-0">Kelola akun dengan akses penuh ke seluruh sistem.</p>
        </div>
        <a href="{{ route('admin.super-admins.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Tambah Super Admin Baru
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

    {{-- Security Note --}}
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
        <i class="bi bi-shield-exclamation fs-5 flex-shrink-0 mt-1"></i>
        <div class="small">
            <strong>Catatan Keamanan:</strong> Akun Super Admin memiliki akses penuh ke seluruh sistem.
            Akun tidak dapat dihapus, hanya dapat dinonaktifkan. Minimal 1 Super Admin aktif harus selalu ada.
            Password hanya bisa direset masing-masing dari halaman profil sendiri.
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
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Tanggal Dibuat</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($superAdmins as $i => $admin)
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;">
                                        <i class="bi bi-person-fill text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $admin->name }}</div>
                                        @if($admin->id === auth()->id())
                                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:0.7rem;">Anda</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $admin->email }}</td>
                            <td class="text-muted small">{{ $admin->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-center">
                                @if($admin->is_active)
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                @if($admin->id !== auth()->id())
                                    <form method="POST"
                                          action="{{ route('admin.super-admins.toggle-active', $admin) }}"
                                          onsubmit="return confirm('{{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ addslashes($admin->name) }}?')">
                                        @csrf
                                        @method('PATCH')
                                        @if($admin->is_active)
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-slash-circle me-1"></i>Nonaktifkan
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-check-circle me-1"></i>Aktifkan
                                            </button>
                                        @endif
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-3 d-block mb-2"></i>
                                Belum ada data Super Admin.
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
