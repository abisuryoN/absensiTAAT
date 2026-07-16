<x-app-layout>
    @section('title', 'Edit Siswa')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.students.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Edit Data Siswa</h3>
            <p class="text-muted mb-0">Ubah profil siswa dan penempatan kelasnya.</p>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Column 1: Identitas Siswa -->
                    <div class="col-md-6 border-end pe-md-4">
                        <h5 class="fw-bold text-primary mb-3">Identitas Siswa</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nis" class="form-label fw-semibold">NIS <span class="text-danger">*</span></label>
                                <input type="text" name="nis" id="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis', $student->nis) }}" required>
                                @error('nis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nisn" class="form-label fw-semibold">NISN</label>
                                <input type="text" name="nisn" id="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn', $student->nisn) }}">
                                @error('nisn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $student->name) }}" required>
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
                                    <option value="L" {{ old('gender', $student->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('gender', $student->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                </div>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">No. HP</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $student->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="birth_place" class="form-label fw-semibold">Tempat Lahir</label>
                                <input type="text" name="birth_place" id="birth_place" class="form-control @error('birth_place') is-invalid @enderror" value="{{ old('birth_place', $student->birth_place) }}">
                                @error('birth_place')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="birth_date" class="form-label fw-semibold">Tanggal Lahir</label>
                                <input type="date" name="birth_date" id="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}">
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Alamat</label>
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $student->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label fw-semibold">Foto Profil</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                @if($student->photo)
                                    <img src="{{ Storage::url($student->photo) }}" alt="" class="rounded-circle object-fit-cover" style="width: 50px; height: 50px;">
                                @endif
                                <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                            </div>
                            @error('photo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Column 2: Penempatan & Akun -->
                    <div class="col-md-6 ps-md-4">
                        <h5 class="fw-bold text-primary mb-3">Penempatan & Akun</h5>

                        <div class="mb-3">
                            <label for="class_id" class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                            <div class="custom-select-wrapper" data-placeholder="Pilih Kelas">
                            <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                            </div>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-8">Mengubah kelas akan otomatis mencatat riwayat pemindahan kelas siswa.</div>
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label fw-semibold">Orang Tua / Wali</label>
                            <div class="custom-select-wrapper" data-placeholder="Pilih Orang Tua (Opsional)">
                            <select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">Pilih Orang Tua (Opsional)</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id', $student->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }} ({{ $parent->relationship }}) - {{ $parent->phone }}</option>
                                @endforeach
                            </select>
                            </div>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-key me-1"></i> Akun Login Siswa</h6>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $student->user->email) }}" required>
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

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $student->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Aktif</label>
                            </div>
                        </div>
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
