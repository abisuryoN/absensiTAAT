<x-app-layout>
    @section('title', 'Edit Orang Tua / Wali')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.parents.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Edit Orang Tua / Wali</h3>
            <p class="text-muted mb-0">Ubah informasi orang tua atau wali siswa.</p>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.parents.update', $parent) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Column 1: Profil Details -->
                    <div class="col-md-6 border-end pe-md-4">
                        <h5 class="fw-bold text-primary mb-3">Profil Wali</h5>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $parent->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="relationship" class="form-label fw-semibold">Hubungan Keluarga <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Hubungan">
                                <select name="relationship" id="relationship" class="form-select @error('relationship') is-invalid @enderror" required>
                                    <option value="">Pilih Hubungan</option>
                                    <option value="Ayah" {{ old('relationship', $parent->relationship) == 'Ayah' ? 'selected' : '' }}>Ayah</option>
                                    <option value="Ibu" {{ old('relationship', $parent->relationship) == 'Ibu' ? 'selected' : '' }}>Ibu</option>
                                    <option value="Wali" {{ old('relationship', $parent->relationship) == 'Wali' ? 'selected' : '' }}>Wali</option>
                                </select>
                                </div>
                                @error('relationship')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">No. HP (WhatsApp) <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $parent->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone_secondary" class="form-label fw-semibold">No. HP Cadangan</label>
                            <input type="text" name="phone_secondary" id="phone_secondary" class="form-control @error('phone_secondary') is-invalid @enderror" value="{{ old('phone_secondary', $parent->phone_secondary) }}">
                            @error('phone_secondary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Alamat Lengkap</label>
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $parent->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Column 2: Optional Akun Portal -->
                    <div class="col-md-6 ps-md-4">
                        <h5 class="fw-bold text-primary mb-3">Akun Login Portal (Opsional)</h5>
                        <p class="text-muted fs-8">Isi bagian ini hanya jika orang tua menginginkan akses mandiri ke portal web absensi sekolah.</p>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="orangtua@email.com" value="{{ old('email', $parent->user?->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Ganti Password</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Biarkan kosong jika tidak ingin mengubah">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($parent->students->count() > 0)
                            <div class="mt-4">
                                <h6 class="fw-bold text-dark mb-2">Siswa Terkait</h6>
                                <div class="list-group list-group-flush">
                                    @foreach($parent->students as $student)
                                        <div class="list-group-item bg-transparent px-0 py-2 d-flex align-items-center gap-2">
                                            <i class="bi bi-mortarboard text-primary"></i>
                                            <div>
                                                <span class="fw-semibold fs-7">{{ $student->name }}</span>
                                                <span class="text-muted fs-8 d-block">{{ $student->class->name ?? '-' }} &middot; NIS: {{ $student->nis }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 border-top pt-3">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
