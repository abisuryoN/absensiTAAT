{{--
    Parent Picker Modal
    Usage: @include('admin.students._parent_picker_modal')
    Requires: a hidden input named "parent_id" and a display element with id="selectedParentInfo"
--}}

<!-- Parent Picker Modal -->
<div class="modal fade" id="parentPickerModal" tabindex="-1" aria-labelledby="parentPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="parentPickerModalLabel">
                    <i class="bi bi-people me-2 text-primary"></i>Pilih Orang Tua / Wali
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                {{-- Search bar --}}
                <div class="p-3 border-bottom bg-light">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="parentSearchInput" class="form-control border-start-0"
                               placeholder="Cari nama, NIK, atau nomor HP..."
                               autocomplete="off">
                        <button type="button" class="btn btn-primary" id="parentSearchBtn">Cari</button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="parentPickerTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">Nama Lengkap</th>
                                <th>NIK</th>
                                <th>No. HP</th>
                                <th>Siswa Terkait</th>
                                <th class="text-center pe-3" style="width:160px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="parentPickerBody">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="p-3 border-top d-flex justify-content-between align-items-center" id="parentPickerPagination">
                    <span class="text-muted small" id="parentPickerInfo"></span>
                    <div id="parentPickerPages" class="d-flex gap-1"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Parent Detail Modal (nested) -->
<div class="modal fade" id="parentDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Detail Orang Tua / Wali</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="parentDetailBody">
                <div class="text-center py-3">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="selectFromDetailBtn">
                    <i class="bi bi-check-lg me-1"></i> Pilih Ini
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    let currentPage = 1;
    let searchQuery = '';
    let detailParentId = null;

    const API_URL = '{{ route("admin.parents.picker") }}';

    // ── Open modal → load first page ──────────────────────────────────────────
    document.getElementById('parentPickerModal').addEventListener('show.bs.modal', function () {
        searchQuery = '';
        currentPage = 1;
        document.getElementById('parentSearchInput').value = '';
        loadParents();
    });

    // ── Search ─────────────────────────────────────────────────────────────────
    document.getElementById('parentSearchBtn').addEventListener('click', function () {
        searchQuery = document.getElementById('parentSearchInput').value.trim();
        currentPage = 1;
        loadParents();
    });

    document.getElementById('parentSearchInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchQuery = this.value.trim();
            currentPage = 1;
            loadParents();
        }
    });

    // ── Load parents via AJAX ──────────────────────────────────────────────────
    // Exposed globally so inline onclick="loadParents()" on the retry button works
    window.loadParents = function loadParents() {
        const tbody = document.getElementById('parentPickerBody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Memuat data...</td></tr>';

        const params = new URLSearchParams({ page: currentPage, search: searchQuery });

        fetch(API_URL + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => renderParents(data))
        .catch(() => {
            const retryBtn = `<button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="loadParents()"><i class="bi bi-arrow-clockwise me-1"></i>Coba Lagi</button>`;
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>Gagal memuat data. Silakan coba lagi.${retryBtn}</td></tr>`;
        });
    }

    function renderParents(data) {
        const tbody = document.getElementById('parentPickerBody');
        const info  = document.getElementById('parentPickerInfo');
        const pages = document.getElementById('parentPickerPages');

        if (!data.data || data.data.length === 0) {
            const addUrl = '{{ route("admin.parents.create") }}';
            let emptyMsg;
            if (searchQuery === '') {
                // Truly no data in the system
                emptyMsg = `
                    <i class="bi bi-people fs-2 d-block mb-2 text-muted"></i>
                    <div class="fw-semibold mb-1">Belum ada data Orang Tua / Wali yang terdaftar.</div>
                    <div class="text-muted small mb-3">Silakan tambah data terlebih dahulu.</div>
                    <a href="${addUrl}" class="btn btn-sm btn-primary" target="_blank">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Orang Tua / Wali
                    </a>`;
            } else {
                // Search returned no results
                emptyMsg = `
                    <i class="bi bi-search fs-2 d-block mb-2 text-muted"></i>
                    <div class="fw-semibold mb-1">Tidak ada orang tua / wali yang cocok dengan pencarian.</div>
                    <div class="text-muted small mb-3">Coba kata kunci lain, atau</div>
                    <a href="${addUrl}" class="btn btn-sm btn-primary" target="_blank">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Orang Tua / Wali Baru
                    </a>`;
            }
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5">${emptyMsg}</td></tr>`;
            info.textContent = '';
            pages.innerHTML = '';
            return;
        }

        // Rows
        tbody.innerHTML = data.data.map(p => `
            <tr>
                <td class="ps-3 fw-semibold">${escHtml(p.name)}
                    ${p.relationship ? `<br><small class="text-muted fw-normal">${escHtml(p.relationship)}</small>` : ''}
                </td>
                <td class="font-monospace small">${escHtml(p.nik ?? '-')}</td>
                <td class="small">${escHtml(p.phone ?? '-')}</td>
                <td class="small">${p.students_count ? `<span class="badge bg-primary-subtle text-primary">${p.students_count} siswa</span>` : '<span class="text-muted">-</span>'}</td>
                <td class="text-center pe-3">
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-light btn-sm border"
                                onclick="showParentDetail(${p.id})" title="Lihat Detail">
                            <i class="bi bi-eye text-info"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm"
                                onclick="selectParent(${p.id}, '${escJs(p.name)}', '${escJs(p.nik ?? '')}', '${escJs(p.phone ?? '')}')">
                            <i class="bi bi-check-lg me-1"></i>Pilih
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        // Info text
        info.textContent = `Menampilkan ${data.from}–${data.to} dari ${data.total} data`;

        // Pagination
        renderPagination(data);
    }

    function renderPagination(data) {
        const pages = document.getElementById('parentPickerPages');
        const lastPage = data.last_page;
        pages.innerHTML = '';

        if (lastPage <= 1) return;

        const makeBtn = (label, page, disabled, active) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `btn btn-sm ${active ? 'btn-primary' : 'btn-light border'}`;
            btn.textContent = label;
            btn.disabled = disabled;
            if (!disabled) btn.addEventListener('click', () => { currentPage = page; loadParents(); });
            return btn;
        };

        pages.appendChild(makeBtn('«', 1, currentPage === 1, false));
        pages.appendChild(makeBtn('‹', currentPage - 1, currentPage === 1, false));

        // Window around current page
        const start = Math.max(1, currentPage - 2);
        const end   = Math.min(lastPage, currentPage + 2);
        for (let i = start; i <= end; i++) {
            pages.appendChild(makeBtn(i, i, false, i === currentPage));
        }

        pages.appendChild(makeBtn('›', currentPage + 1, currentPage === lastPage, false));
        pages.appendChild(makeBtn('»', lastPage, currentPage === lastPage, false));
    }

    // ── Show detail ────────────────────────────────────────────────────────────
    window.showParentDetail = function (id) {
        detailParentId = id;
        const body = document.getElementById('parentDetailBody');
        body.innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div></div>';

        const detailModal = new bootstrap.Modal(document.getElementById('parentDetailModal'));
        detailModal.show();

        fetch('{{ url("admin/parents") }}/' + id + '/detail-json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(p => {
            body.innerHTML = `
                <dl class="row mb-0">
                    <dt class="col-5 text-muted fw-normal small">Nama</dt>
                    <dd class="col-7 fw-semibold">${escHtml(p.name)}</dd>
                    <dt class="col-5 text-muted fw-normal small">Hubungan</dt>
                    <dd class="col-7">${escHtml(p.relationship ? p.relationship.charAt(0).toUpperCase() + p.relationship.slice(1) : '-')}</dd>
                    <dt class="col-5 text-muted fw-normal small">NIK</dt>
                    <dd class="col-7 font-monospace">${escHtml(p.nik ?? '-')}</dd>
                    <dt class="col-5 text-muted fw-normal small">No. HP</dt>
                    <dd class="col-7">${escHtml(p.phone ?? '-')}</dd>
                    <dt class="col-5 text-muted fw-normal small">No. HP Cadangan</dt>
                    <dd class="col-7">${escHtml(p.phone_secondary ?? '-')}</dd>
                    <dt class="col-5 text-muted fw-normal small">Email</dt>
                    <dd class="col-7">${escHtml(p.email ?? 'Belum ada akun')}</dd>
                    <dt class="col-5 text-muted fw-normal small">Alamat</dt>
                    <dd class="col-7">${escHtml(p.address ?? '-')}</dd>
                    <dt class="col-5 text-muted fw-normal small">Status</dt>
                    <dd class="col-7">${p.is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>'}</dd>
                </dl>
            `;

            // Wire up the "Select this" button
            document.getElementById('selectFromDetailBtn').onclick = function () {
                selectParent(p.id, p.name, p.nik ?? '', p.phone ?? '');
                bootstrap.Modal.getInstance(document.getElementById('parentDetailModal')).hide();
            };
        })
        .catch(() => { body.innerHTML = '<p class="text-danger">Gagal memuat detail.</p>'; });
    };

    // ── Select parent ──────────────────────────────────────────────────────────
    window.selectParent = function (id, name, nik, phone) {
        // Set hidden input
        document.getElementById('parent_id').value = id;

        // Update display
        const info = document.getElementById('selectedParentInfo');
        info.classList.remove('d-none');
        info.querySelector('#selectedParentName').textContent = name;
        info.querySelector('#selectedParentNik').textContent  = nik || '-';
        info.querySelector('#selectedParentPhone').textContent = phone || '-';

        // Update button text
        document.getElementById('openParentPickerBtn').innerHTML =
            '<i class="bi bi-person-check me-1"></i>' + escHtml(name) + ' <small class="text-muted ms-1">(Ganti)</small>';

        // Close modals
        const pickerModal = bootstrap.Modal.getInstance(document.getElementById('parentPickerModal'));
        if (pickerModal) pickerModal.hide();
    };

    // ── Clear selection ────────────────────────────────────────────────────────
    window.clearParentSelection = function () {
        document.getElementById('parent_id').value = '';
        document.getElementById('selectedParentInfo').classList.add('d-none');
        document.getElementById('openParentPickerBtn').innerHTML =
            '<i class="bi bi-plus-lg me-1"></i> Pilih Orang Tua / Wali';
    };

    // ── Helpers ────────────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str).replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>').replace(/"/g,'"');
    }
    function escJs(str) {
        return String(str).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
    }
})();
</script>