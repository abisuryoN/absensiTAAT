<x-app-layout>
    @section('title', 'Tambah Siswa')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.students.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Tambah Siswa Baru</h3>
            <p class="text-muted mb-0">Buat profil peserta didik beserta akun login sistemnya.</p>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Column 1: Identitas Siswa -->
                    <div class="col-md-6 border-end pe-md-4">
                        <h5 class="fw-bold text-primary mb-3">Identitas Siswa</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nis" class="form-label fw-semibold">NIS <span class="text-danger">*</span></label>
                                <input type="text" name="nis" id="nis" class="form-control @error('nis') is-invalid @enderror" placeholder="Nomor Induk Siswa" value="{{ old('nis') }}" required>
                                @error('nis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nisn" class="form-label fw-semibold">NISN</label>
                                <input type="text" name="nisn" id="nisn" class="form-control @error('nisn') is-invalid @enderror" placeholder="Nomor Induk Siswa Nasional" value="{{ old('nisn') }}">
                                @error('nisn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Rina Amelia Putri" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="gender" class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Gender">
                                <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Pilih Gender</option>
                                    <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                </div>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">No. HP</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="08..." value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="birth_place" class="form-label fw-semibold">Tempat Lahir</label>
                                <input type="text" name="birth_place" id="birth_place" class="form-control @error('birth_place') is-invalid @enderror" placeholder="Contoh: Bogor" value="{{ old('birth_place') }}">
                                @error('birth_place')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="birth_date" class="form-label fw-semibold">Tanggal Lahir</label>
                                <input type="date" name="birth_date" id="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}">
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Alamat</label>
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="Alamat rumah siswa...">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label fw-semibold">Foto Profil</label>
                            <div class="d-flex align-items-start gap-3">
                                <div id="photoPreviewWrapper" class="d-none">
                                    <img id="photoPreview" src="#" alt="Preview"
                                         class="rounded-circle object-fit-cover border"
                                         style="width: 80px; height: 80px;">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text fs-8">Format JPG/PNG, Maksimal 2MB.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Penempatan & Akun -->
                    <div class="col-md-6 ps-md-4">
                        <h5 class="fw-bold text-primary mb-3">Penempatan & Akun</h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Orang Tua / Wali</label>
                            <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id') }}">

                            <div>
                                <button type="button" id="openParentPickerBtn"
                                        class="btn btn-light border w-100 text-start @error('parent_id') is-invalid @enderror"
                                        data-bs-toggle="modal" data-bs-target="#parentPickerModal">
                                    @if(old('parent_id'))
                                        <i class="bi bi-person-check me-1"></i>
                                        <span id="pickerBtnLabel">Memuat...</span>
                                        <small class="text-muted ms-1">(Ganti)</small>
                                    @else
                                        <i class="bi bi-plus-lg me-1"></i> Pilih Orang Tua / Wali
                                    @endif
                                </button>
                            </div>

                            {{-- Selected parent info card --}}
                            <div id="selectedParentInfo" class="mt-2 p-3 rounded border bg-light {{ old('parent_id') ? '' : 'd-none' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold" id="selectedParentName">-</div>
                                        <small class="text-muted">NIK: <span id="selectedParentNik">-</span></small>
                                        <small class="text-muted ms-2">HP: <span id="selectedParentPhone">-</span></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2"
                                            onclick="clearParentSelection()" title="Hapus pilihan">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>

                        {{-- parent_id error message --}}
                        @error('parent_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        </div>{{-- end parent picker mb-3 --}}

                        <hr class="my-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-key me-1"></i> Akun Login Siswa</h6>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="siswa@sman1tajurhalang.sch.id" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan untuk default NIS/siswa123">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-8">Default password jika dikosongkan adalah NIS siswa, atau "siswa123" jika NIS kosong.</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 border-top pt-3">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Data Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
@pushOnce('modals')
    @include('admin.students._parent_picker_modal')
@endPushOnce

<script>
// Photo preview
document.getElementById('photo').addEventListener('change', function () {
    const file = this.files[0];
    const wrapper = document.getElementById('photoPreviewWrapper');
    const preview = document.getElementById('photoPreview');
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            wrapper.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        wrapper.classList.add('d-none');
        preview.src = '#';
    }
});
</script>

@if(old('parent_id'))
<script>
// Restore picker state after validation failure
document.addEventListener('DOMContentLoaded', function () {
    const parentId = '{{ old("parent_id") }}';
    if (!parentId) return;

    fetch('{{ url("admin/parents") }}/' + parentId + '/detail-json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(p => {
        document.getElementById('selectedParentName').textContent = p.name;
        document.getElementById('selectedParentNik').textContent  = p.nik || '-';
        document.getElementById('selectedParentPhone').textContent = p.phone || '-';
        document.getElementById('selectedParentInfo').classList.remove('d-none');
        document.getElementById('openParentPickerBtn').innerHTML =
            '<i class="bi bi-person-check me-1"></i>' + p.name + ' <small class="text-muted ms-1">(Ganti)</small>';
    })
    .catch(() => {});
});
</script>
@endif
</x-app-layout>
