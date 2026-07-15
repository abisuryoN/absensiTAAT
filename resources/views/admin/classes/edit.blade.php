<x-app-layout>
    @section('title', 'Edit Kelas')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.classes.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Edit Kelas</h3>
            <p class="text-muted mb-0">Ubah detail kelas dan wali kelas.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.classes.update', $class) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="academic_year_id" class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                                    <option value="">Pilih Tahun Ajaran</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $class->academic_year_id) == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('academic_year_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="major_id" class="form-label fw-semibold">Jurusan <span class="text-danger">*</span></label>
                                <select name="major_id" id="major_id" class="form-select @error('major_id') is-invalid @enderror" required>
                                    <option value="">Pilih Jurusan</option>
                                    @foreach($majors as $major)
                                        <option value="{{ $major->id }}" {{ old('major_id', $class->major_id) == $major->id ? 'selected' : '' }}>
                                            {{ $major->name }} ({{ $major->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('major_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="grade_level" class="form-label fw-semibold">Tingkat Kelas <span class="text-danger">*</span></label>
                                <select name="grade_level" id="grade_level" class="form-select @error('grade_level') is-invalid @enderror" required>
                                    <option value="">Pilih Tingkat</option>
                                    <option value="10" {{ old('grade_level', $class->grade_level) == 10 ? 'selected' : '' }}>Kelas 10</option>
                                    <option value="11" {{ old('grade_level', $class->grade_level) == 11 ? 'selected' : '' }}>Kelas 11</option>
                                    <option value="12" {{ old('grade_level', $class->grade_level) == 12 ? 'selected' : '' }}>Kelas 12</option>
                                </select>
                                @error('grade_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: X MIPA 1" value="{{ old('name', $class->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="capacity" class="form-label fw-semibold">Kapasitas (Daya Tampung Siswa) <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror" placeholder="Contoh: 36" value="{{ old('capacity', $class->capacity) }}" min="1" max="100" required>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="homeroom_teacher_id" class="form-label fw-semibold">Wali Kelas</label>
                                <select name="homeroom_teacher_id" id="homeroom_teacher_id" class="form-select @error('homeroom_teacher_id') is-invalid @enderror">
                                    <option value="">Pilih Wali Kelas (Opsional)</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id', $class->homeroom_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('homeroom_teacher_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $class->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Aktif</label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary fw-semibold px-4">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
