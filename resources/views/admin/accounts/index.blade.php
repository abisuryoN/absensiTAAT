<x-app-layout>
    @section('title', 'Manajemen Akun Login')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-person-lock me-2 text-primary"></i>Manajemen Akun Login
            </h3>
            <p class="text-muted mb-0">Kelola akun login semua pengguna dan reset password jika diperlukan.</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="card glass-card border-0 mb-3" style="position: relative; z-index: 20;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.accounts.index') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    {{-- Search --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small mb-1">Cari Nama</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: #9ca3af; z-index: 5; pointer-events: none; font-size: 0.875rem;">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control"
                                   style="padding: 0.625rem 1rem 0.625rem 2.25rem; font-size: 0.875rem;"
                                   placeholder="Cari nama..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Role Filter --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">Filter Role</label>
                        <div class="custom-select-wrapper">
                            <select name="role" id="roleFilter">
                                <option value="">Semua Role</option>
                                <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="parent" {{ request('role') == 'parent' ? 'selected' : '' }}>Orang Tua/Wali</option>
                            </select>
                        </div>
                    </div>

                    {{-- Kelas Filter (disabled when role is not siswa) --}}
                    <div class="col-md-2" id="classFilterWrapper">
                        <label class="form-label fw-semibold small mb-1">Filter Kelas</label>
                        <div class="custom-select-wrapper">
                            <select name="class_id" id="classFilter"
                                    onchange="this.form.submit()"
                                    @if(request('role') != 'siswa') disabled @endif>
                                <option value="">Semua Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Jurusan Filter (disabled when role is not siswa) --}}
                    <div class="col-md-2" id="majorFilterWrapper">
                        <label class="form-label fw-semibold small mb-1">Filter Jurusan</label>
                        <div class="custom-select-wrapper">
                            <select name="major_id" id="majorFilter"
                                    onchange="this.form.submit()"
                                    @if(request('role') != 'siswa') disabled @endif>
                                <option value="">Semua Jurusan</option>
                                @foreach($majors as $major)
                                    <option value="{{ $major->id }}" {{ request('major_id') == $major->id ? 'selected' : '' }}>
                                        {{ $major->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small mb-1 invisible">Aksi</label>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.accounts.index') }}" class="btn btn-light border flex-fill text-center">
                                <i class="bi bi-x-circle me-1"></i>Reset Filter
                            </a>
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-search me-1"></i>Cari
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card glass-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Email / Username</th>
                            <th>Kelas / Jabatan</th>
                            <th>Status Password</th>
                            <th class="text-center" style="width:130px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $i => $account)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $accounts->firstItem() + $i }}</td>

                            {{-- Nama --}}
                            <td>
                                <div class="fw-semibold">{{ $account['name'] }}</div>
                                @if($account['role'] === 'siswa' && $account['nisn'])
                                    <small class="text-muted">NISN: {{ $account['nisn'] }}</small>
                                @elseif($account['role'] === 'guru' && $account['nip'])
                                    <small class="text-muted">NIP: {{ $account['nip'] }}</small>
                                @elseif($account['role'] === 'parent' && $account['nik'])
                                    <small class="text-muted">NIK: {{ $account['nik'] }}</small>
                                @endif
                            </td>

                            {{-- Role badge --}}
                            <td>
                                @if($account['role'] === 'siswa')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Siswa</span>
                                @elseif($account['role'] === 'guru')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Guru</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Orang Tua</span>
                                @endif
                            </td>

                            {{-- Email --}}
                            <td>
                                <span class="font-monospace small">{{ $account['email'] }}</span>
                            </td>

                            {{-- Kelas / Jabatan --}}
                            <td>
                                @if($account['role'] === 'siswa')
                                    <span class="small">{{ $account['class'] ?? '-' }}</span>
                                @elseif($account['role'] === 'guru')
                                    <span class="small text-muted">{{ $account['subjects'] ?? '-' }}</span>
                                @else
                                    <span class="small text-muted">
                                        Wali dari: {{ $account['children'] ?? '-' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Status Password --}}
                            <td>
                                @if($account['password_changed'])
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-shield-check me-1"></i>Sudah Diganti
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        <i class="bi bi-shield me-1"></i>Password Default
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-warning"
                                        data-id="{{ $account['id'] }}"
                                        data-name="{{ $account['name'] }}"
                                        data-role="{{ $account['role'] }}"
                                        onclick="confirmReset(this.dataset.id, this.dataset.name, this.dataset.role)"
                                        title="Reset Password ke Default">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                Tidak ada akun ditemukan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($accounts->hasPages())
                <div class="px-3 py-2 border-top">
                    {{ $accounts->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Reset Password Confirm Modal --}}
    <div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="resetModalLabel">
                        <i class="bi bi-arrow-clockwise me-2 text-warning"></i>Reset Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Anda akan mereset password <strong id="resetUserName"></strong> ke password default.</p>
                    <p class="text-muted small mt-2 mb-0" id="resetRoleHint"></p>
                    <div class="alert alert-warning mt-3 mb-0 py-2 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Password lama akan <strong>ditimpa</strong>. Pastikan menginformasikan password baru ke pengguna yang bersangkutan.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <form id="resetForm" method="POST" action="{{ route('admin.accounts.reset-password') }}">
                        @csrf
                        <input type="hidden" name="type" id="resetType">
                        <input type="hidden" name="id" id="resetId">
                        <button type="submit" class="btn btn-warning fw-semibold">
                            <i class="bi bi-arrow-clockwise me-1"></i>Ya, Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Result Modal (shown after reset) --}}
    @if(session('reset_result'))
    <div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 bg-success-subtle">
                    <h5 class="modal-title fw-bold text-success">
                        <i class="bi bi-check-circle me-2"></i>Password Berhasil Direset
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">{{ session('reset_result.message') }}</p>
                    <div class="p-3 rounded border bg-light text-center">
                        <small class="text-muted d-block mb-1">Password baru:</small>
                        <code class="fs-5 fw-bold text-dark user-select-all" id="newPasswordDisplay">{{ session('reset_result.password') }}</code>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2"
                                onclick="copyPassword()" title="Salin password">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <div class="alert alert-info mt-3 mb-0 py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Password ini hanya ditampilkan sekali. Segera informasikan ke pengguna yang bersangkutan.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif

<script>
// Toggle kelas/jurusan disabled state when role changes (no page reload needed)
(function () {
    var roleSelect  = document.getElementById('roleFilter');
    var classSelect = document.getElementById('classFilter');
    var majorSelect = document.getElementById('majorFilter');
    if (!roleSelect) return;

    roleSelect.addEventListener('change', function () {
        var disabled = this.value !== 'siswa';
        classSelect.disabled = disabled;
        majorSelect.disabled = disabled;
        // Also update custom-select UI element disabled state if present
        [classSelect, majorSelect].forEach(function (sel) {
            var cs = sel.closest('.custom-select-wrapper') && sel.closest('.custom-select-wrapper').querySelector('.custom-select');
            if (cs) cs.classList.toggle('disabled', disabled);
        });
        // Clear values when disabling so they don't filter silently
        if (disabled) {
            classSelect.value = '';
            majorSelect.value = '';
        }
    });
})();

function confirmReset(id, userName, role) {
    document.getElementById('resetId').value = id;
    document.getElementById('resetType').value = role;
    document.getElementById('resetUserName').textContent = userName;

    const hints = {
        siswa: 'Password default: NISN + Tahun Masuk (contoh: 12345678902024)',
        guru: 'Password default: NIP + Tahun Masuk Kerja (contoh: 1985010120100110012015)',
        parent: 'Password default: NIK + Tahun Masuk anak yang terhubung (contoh: 3201xxxxxxxxxxxx2024)',
    };
    document.getElementById('resetRoleHint').textContent = hints[role] || '';

    const modal = new bootstrap.Modal(document.getElementById('resetModal'));
    modal.show();
}

function copyPassword() {
    const text = document.getElementById('newPasswordDisplay').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.currentTarget;
        btn.innerHTML = '<i class="bi bi-clipboard-check"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 2000);
    });
}

const resultModalEl = document.getElementById('resultModal');
if (resultModalEl) {
    document.addEventListener('DOMContentLoaded', function () {
        const resultModal = new bootstrap.Modal(resultModalEl);
        resultModal.show();
    });
}
</script>
</x-app-layout>