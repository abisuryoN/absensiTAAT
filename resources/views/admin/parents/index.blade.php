<x-app-layout>
    @section('title', 'Data Orang Tua / Wali')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Desktop Header --}}
    <div class="row mb-4 align-items-center d-none d-md-flex">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-people-fill me-2 text-primary"></i>Daftar Orang Tua / Wali
            </h3>
            <p class="text-muted mb-0">Master data orang tua dan wali siswa untuk portal dan notifikasi.</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.parents.export') }}" class="btn btn-success fw-semibold">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Referensi
            </a>
            <a href="{{ route('admin.parents.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Orang Tua
            </a>
        </div>
    </div>

    {{-- Mobile Header --}}
    <div class="d-block d-md-none mobile-page-content">
        <div class="mobile-section-header">
            <div>
                <h3 class="mobile-heading">Data Orang Tua</h3>
                <p class="mobile-subtitle">Master data wali siswa</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.parents.export') }}" class="btn btn-success mobile-btn" style="white-space:nowrap;">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
                <a href="{{ route('admin.parents.create') }}" class="btn btn-primary mobile-btn" style="white-space:nowrap;">
                    <i class="bi bi-plus-lg"></i> Tambah
                </a>
            </div>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4 d-none d-md-block">
            {{-- Search --}}
            <form method="GET" action="{{ route('admin.parents.index') }}" class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Cari nama, NIK, atau nomor HP..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-light border fw-semibold">Cari</button>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th style="width:52px;">Foto</th>
                            <th>Nama Lengkap</th>
                            <th>NIK</th>
                            <th>No. HP</th>
                            <th>Email / Akun Portal</th>
                            <th>Siswa Terkait</th>
                            <th>Status</th>
                            <th class="text-center" style="width:160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parents as $parent)
                        <tr>
                            <td data-label="Foto">
                                <img src="{{ $parent->user?->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($parent->name).'&background=059669&color=fff&size=80&bold=true' }}"
                                     alt="{{ $parent->name }}"
                                     class="rounded-circle object-fit-cover clickable-avatar"
                                     style="width:40px;height:40px;border:2px solid #e2e8f0;cursor:pointer;"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($parent->name) }}&background=059669&color=fff&size=80&bold=true'"
                                >
                            </td>
                            <td data-label="Nama" class="fw-semibold text-dark">
                                {{ $parent->name }}
                                @if($parent->relationship)
                                    <br><small class="text-muted fw-normal">{{ ucfirst($parent->relationship) }}</small>
                                @endif
                            </td>
                            <td data-label="NIK" class="font-monospace small">{{ $parent->nik ?? '-' }}</td>
                            <td data-label="No. HP">{{ $parent->phone ?? '-' }}</td>
                            <td data-label="Email">
                                @if($parent->email)
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    <small>{{ $parent->email }}</small>
                                @else
                                    <span class="text-muted fs-8">Belum ada akun</span>
                                @endif
                            </td>
                            <td data-label="Siswa">
                                @if($parent->students->count() > 0)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $parent->students->count() }} siswa
                                    </span>
                                @else
                                    <span class="text-muted fs-8">-</span>
                                @endif
                            </td>
                            <td data-label="Status">
                                @if($parent->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-8">Nonaktif</span>
                                @endif
                            </td>
                            <td data-label="Aksi" class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.parents.show', $parent) }}"
                                       class="btn btn-light btn-sm border" title="Detail">
                                        <i class="bi bi-eye text-info"></i>
                                    </a>
                                    <a href="{{ route('admin.parents.edit', $parent) }}"
                                       class="btn btn-light btn-sm border" title="Edit">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST"
                                          onsubmit="return confirm('Hapus data orang tua ini? Siswa yang tertaut akan dilepas.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-light btn-sm border" title="Hapus">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                                Belum ada data orang tua/wali.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $parents->links() }}</div>
        </div>

        {{-- Mobile Body --}}
        <div class="d-block d-md-none mobile-card-body">
            <div class="mobile-search-card">
                <form method="GET" action="{{ route('admin.parents.index') }}" class="mobile-search-form">
                    <div class="mobile-search-row">
                        <div class="mobile-search-group">
                            <span class="mobile-search-icon"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="mobile-search-input"
                                   placeholder="Cari nama atau NIK..."
                                   value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="mobile-cari-btn">Cari</button>
                    </div>
                </form>
            </div>

            <div class="mobile-parent-list">
                @forelse($parents as $parent)
                <div class="mobile-parent-card">
                    <div class="parent-card-header">
                        <div class="parent-card-name-area">
                            <div class="parent-card-name">{{ $parent->name }}</div>
                            <div class="parent-card-badge">
                                @if($parent->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </div>
                        </div>
                        <div class="parent-card-actions">
                            <a href="{{ route('admin.parents.show', $parent) }}" class="btn-sm-icon" title="Detail">
                                <i class="bi bi-eye text-info"></i>
                            </a>
                            <a href="{{ route('admin.parents.edit', $parent) }}" class="btn-sm-icon btn-edit" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST"
                                  onsubmit="return confirm('Hapus data orang tua ini?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm-icon btn-delete" title="Hapus">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="parent-card-body">
                        <div class="parent-info-row">
                            <i class="bi bi-credit-card-2-front info-icon"></i>
                            <span class="info-value font-monospace">{{ $parent->nik ?? '-' }}</span>
                        </div>
                        <div class="parent-info-row">
                            <i class="bi bi-telephone info-icon"></i>
                            <span class="info-value">{{ $parent->phone ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="parent-card-students">
                        @forelse($parent->students as $student)
                            <span class="student-badge">{{ $student->name }}</span>
                        @empty
                            <span class="student-badge" style="color:#94a3b8;">Belum ada siswa terkait</span>
                        @endforelse
                    </div>
                </div>
                @empty
                <div class="mobile-empty-state">
                    <div class="empty-icon-wrap"><i class="bi bi-people"></i></div>
                    <h4 class="empty-title">Belum Ada Data Orang Tua</h4>
                    <p class="empty-desc">Tambahkan data orang tua/wali untuk mulai menggunakan fitur ini.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-3">{{ $parents->appends(request()->all())->links('vendor.pagination.bootstrap-5') }}</div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create lightbox HTML dynamically if not exists
            let lightbox = document.querySelector('.avatar-lightbox');
            if (!lightbox) {
                lightbox = document.createElement('div');
                lightbox.className = 'avatar-lightbox';
                lightbox.innerHTML = `
                    <div class="lightbox-backdrop"></div>
                    <div class="lightbox-content">
                        <div class="lightbox-header">
                            <h5 class="lightbox-title">Foto Profil</h5>
                            <button class="lightbox-close">&times;</button>
                        </div>
                        <div class="lightbox-body">
                            <div class="lightbox-img-wrapper">
                                <img class="lightbox-img" src="" alt="" draggable="false">
                            </div>
                        </div>
                        <div class="lightbox-controls">
                            <button class="control-btn zoom-out-btn"><i class="bi bi-zoom-out"></i></button>
                            <button class="control-btn reset-btn">1:1</button>
                            <button class="control-btn zoom-in-btn"><i class="bi bi-zoom-in"></i></button>
                        </div>
                    </div>
                `;
                document.body.appendChild(lightbox);

                // Inject Styles
                const style = document.createElement('style');
                style.textContent = `
                    .avatar-lightbox {
                        position: fixed;
                        top: 0; left: 0; width: 100%; height: 100%;
                        z-index: 10000;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        opacity: 0;
                        visibility: hidden;
                        transition: opacity 0.25s ease, visibility 0.25s ease;
                    }
                    .avatar-lightbox.show {
                        opacity: 1;
                        visibility: visible;
                    }
                    .lightbox-backdrop {
                        position: absolute;
                        top: 0; left: 0; width: 100%; height: 100%;
                        background: rgba(15, 23, 42, 0.7);
                        backdrop-filter: blur(8px);
                        -webkit-backdrop-filter: blur(8px);
                    }
                    .lightbox-content {
                        position: relative;
                        z-index: 10001;
                        background: #ffffff;
                        border-radius: 20px;
                        width: 90%;
                        max-width: 400px;
                        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15), 0 10px 10px -5px rgba(0,0,0,0.04);
                        display: flex;
                        flex-direction: column;
                        overflow: hidden;
                        transform: scale(0.9);
                        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
                    }
                    .avatar-lightbox.show .lightbox-content {
                        transform: scale(1);
                    }
                    .lightbox-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        padding: 14px 20px;
                        border-bottom: 1px solid #f1f5f9;
                    }
                    .lightbox-title {
                        margin: 0;
                        font-weight: 700;
                        color: #1e293b;
                        font-size: 1.05rem;
                    }
                    .lightbox-close {
                        border: none;
                        background: #f1f5f9;
                        color: #64748b;
                        width: 28px;
                        height: 28px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 1.15rem;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        outline: none !important;
                        box-shadow: none !important;
                        padding: 0;
                    }
                    .lightbox-close:hover {
                        background: #e2e8f0;
                        color: #0f172a;
                    }
                    .lightbox-body {
                        padding: 24px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: #f8fafc;
                    }
                    .lightbox-img-wrapper {
                        width: 260px;
                        height: 260px;
                        border-radius: 50%;
                        overflow: hidden;
                        border: 4px solid #ffffff;
                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                        position: relative;
                        cursor: grab;
                        background: #e2e8f0;
                    }
                    .lightbox-img-wrapper:active {
                        cursor: grabbing;
                    }
                    .lightbox-img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        transform-origin: center center;
                        user-select: none;
                        -webkit-user-drag: none;
                    }
                    .lightbox-controls {
                        display: flex;
                        justify-content: center;
                        gap: 12px;
                        padding: 14px;
                        background: #ffffff;
                        border-top: 1px solid #f1f5f9;
                    }
                    .control-btn {
                        border: 1px solid #e2e8f0;
                        background: #ffffff;
                        color: #475569;
                        padding: 6px 14px;
                        border-radius: 8px;
                        font-size: 0.85rem;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        gap: 6px;
                        outline: none !important;
                        box-shadow: none !important;
                    }
                    .control-btn:hover {
                        background: #f8fafc;
                        border-color: #cbd5e1;
                        color: #0f172a;
                    }
                `;
                document.head.appendChild(style);
            }

            const img = lightbox.querySelector('.lightbox-img');
            const wrapper = lightbox.querySelector('.lightbox-img-wrapper');
            const title = lightbox.querySelector('.lightbox-title');
            const closeBtn = lightbox.querySelector('.lightbox-close');
            const backdrop = lightbox.querySelector('.lightbox-backdrop');

            const zoomInBtn = lightbox.querySelector('.zoom-in-btn');
            const zoomOutBtn = lightbox.querySelector('.zoom-out-btn');
            const resetBtn = lightbox.querySelector('.reset-btn');

            let scale = 1;
            let pointX = 0;
            let pointY = 0;
            let startX = 0;
            let startY = 0;
            let isDragging = false;

            function updateTransform() {
                const maxOffset = Math.max(0, (scale - 1) * 130);
                pointX = Math.max(-maxOffset, Math.min(maxOffset, pointX));
                pointY = Math.max(-maxOffset, Math.min(maxOffset, pointY));
                img.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
            }

            function resetZoom() {
                scale = 1;
                pointX = 0;
                pointY = 0;
                updateTransform();
            }

            zoomInBtn.addEventListener('click', () => {
                scale = Math.min(4, scale + 0.35);
                updateTransform();
            });

            zoomOutBtn.addEventListener('click', () => {
                scale = Math.max(1, scale - 0.35);
                updateTransform();
            });

            resetBtn.addEventListener('click', resetZoom);

            wrapper.addEventListener('wheel', (e) => {
                e.preventDefault();
                const delta = e.deltaY;
                if (delta < 0) {
                    scale = Math.min(4, scale + 0.15);
                } else {
                    scale = Math.max(1, scale - 0.15);
                }
                updateTransform();
            });

            wrapper.addEventListener('mousedown', (e) => {
                e.preventDefault();
                if (scale <= 1) return;
                isDragging = true;
                startX = e.clientX - pointX;
                startY = e.clientY - pointY;
            });

            window.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                pointX = e.clientX - startX;
                pointY = e.clientY - startY;
                updateTransform();
            });

            window.addEventListener('mouseup', () => {
                isDragging = false;
            });

            wrapper.addEventListener('touchstart', (e) => {
                if (scale <= 1) return;
                isDragging = true;
                const touch = e.touches[0];
                startX = touch.clientX - pointX;
                startY = touch.clientY - pointY;
            });

            wrapper.addEventListener('touchmove', (e) => {
                if (!isDragging) return;
                const touch = e.touches[0];
                pointX = touch.clientX - startX;
                pointY = touch.clientY - startY;
                updateTransform();
            });

            wrapper.addEventListener('touchend', () => {
                isDragging = false;
            });

            window.openAvatarLightbox = function(src, name) {
                // Clear previous image source immediately to prevent flashing the old image
                img.removeAttribute('src');

                let hdSrc = src;
                if (hdSrc.includes('ui-avatars.com')) {
                    hdSrc = hdSrc.replace(/size=\d+/, 'size=512');
                }

                title.textContent = name;
                img.src = hdSrc;
                resetZoom();

                lightbox.classList.add('show');
                document.body.style.overflow = 'hidden';
            };

            function closeLightbox() {
                lightbox.classList.remove('show');
                document.body.style.overflow = '';
            }

            closeBtn.addEventListener('click', closeLightbox);
            backdrop.addEventListener('click', closeLightbox);

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeLightbox();
                }
            });

            // Register triggers
            document.querySelectorAll('.clickable-avatar').forEach(img => {
                img.addEventListener('click', function() {
                    const src = this.getAttribute('src');
                    const name = this.getAttribute('alt') || 'Foto Profil';
                    if (window.openAvatarLightbox) {
                        window.openAvatarLightbox(src, name);
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>