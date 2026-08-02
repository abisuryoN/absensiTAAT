<x-app-layout>
    @section('title', 'Data Guru')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-person-workspace me-2 text-primary"></i>Daftar Guru
            </h3>
            <p class="text-muted mb-0">Kelola tenaga pendidik, data kontak, dan mata pelajaran yang diampu.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Data Guru
            </a>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.teachers.index') }}" class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, NIP, atau no HP..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-select-wrapper" data-placeholder="Semua Status">
                    <select name="is_active" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    </div>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-light border fw-semibold">Filter</button>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>NIP / NUPTK</th>
                            <th>Nama Lengkap</th>
                            <th>Gender</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Mata Pelajaran Diampu</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $teacher)
                            <tr>
                                <td data-label="Foto">
                                    <img src="{{ $teacher->user?->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($teacher->name).'&background=4f46e5&color=fff&size=80&bold=true' }}"
                                         alt="{{ $teacher->name }}"
                                         class="rounded-circle object-fit-cover clickable-avatar"
                                         style="width:40px;height:40px;border:2px solid #e2e8f0;cursor:pointer;"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($teacher->name) }}&background=4f46e5&color=fff&size=80&bold=true'"
                                    >
                                </td>
                                <td data-label="NIP / NUPTK">
                                    <span class="d-block fw-semibold text-dark fs-7">NIP: {{ $teacher->nip ?: '-' }}</span>
                                    <span class="text-muted fs-8">NUPTK: {{ $teacher->nuptk ?: '-' }}</span>
                                </td>
                                <td data-label="Nama" class="fw-semibold text-dark">{{ $teacher->name }}</td>
                                <td data-label="Gender">
                                    @if($teacher->gender == 'L')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-8">Laki-laki</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-8">Perempuan</span>
                                    @endif
                                </td>
                                <td data-label="Email">{{ $teacher->user->email }}</td>
                                <td data-label="No. HP">{{ $teacher->phone ?: '-' }}</td>
                                <td data-label="Mapel Diampu">
                                    @forelse($teacher->subjects as $subject)
                                        <span class="badge bg-light text-dark border fs-8 mb-1">{{ $subject->name }}</span>
                                    @empty
                                        <span class="text-muted fs-8">-</span>
                                    @endforelse
                                </td>
                                <td data-label="Status">
                                    @if($teacher->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-8">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-8">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-light btn-sm border" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini? Akun login terkait juga akan ikut dihapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm border" title="Hapus">
                                                <i class="bi bi-trash3 text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data guru ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $teachers->links() }}
            </div>
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
