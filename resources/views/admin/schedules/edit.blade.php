<x-app-layout>
    @section('title', 'Edit Jadwal Pelajaran')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.schedules.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Edit Jadwal Pelajaran</h3>
            <p class="text-muted mb-0">Ubah jadwal mengajar. Sistem akan memvalidasi konflik jadwal otomatis.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="academic_year_id" class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Tahun Ajaran">
                                <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                                    <option value="">Pilih Tahun Ajaran</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $schedule->academic_year_id) == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                </div>
                                @error('academic_year_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="semester_id" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Semester">
                                <select name="semester_id" id="semester_id" class="form-select @error('semester_id') is-invalid @enderror" required>
                                    <option value="">Pilih Semester</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" {{ old('semester_id', $schedule->semester_id) == $semester->id ? 'selected' : '' }}>
                                            {{ $semester->name }} {{ $semester->is_active ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                </div>
                                @error('semester_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="teacher_id" class="form-label fw-semibold">Guru Pengajar <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Guru">
                                <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                    <option value="">Pilih Guru</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('teacher_id', $schedule->teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                                </div>
                                @error('teacher_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="subject_id" class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Mata Pelajaran">
                                <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                    <option value="">Pilih Mata Pelajaran</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id', $schedule->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                                </div>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="class_id" class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Kelas">
                                <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kelas</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id', $schedule->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                </div>
                                @error('class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="day" class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Hari">
                                <select name="day" id="day" class="form-select @error('day') is-invalid @enderror" required>
                                    <option value="">Pilih Hari</option>
                                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $day)
                                        <option value="{{ $day }}" {{ old('day', $schedule->day) == $day ? 'selected' : '' }}>{{ $day }}</option>
                                    @endforeach
                                </select>
                                </div>
                                @error('day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="start_time" class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="text" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" placeholder="Contoh: 09:00" pattern="[0-9]{2}:[0-9]{2}" maxlength="5" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="end_time" class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="text" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" placeholder="Contoh: 10:00" pattern="[0-9]{2}:[0-9]{2}" maxlength="5" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="room" class="form-label fw-semibold">Ruangan</label>
                                <input type="text" name="room" id="room" class="form-control @error('room') is-invalid @enderror" placeholder="Contoh: Ruang 12" value="{{ old('room', $schedule->room) }}">
                                @error('room')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}>
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

        <div class="col-md-3">
            <div class="card glass-card border-0">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle me-1"></i> Informasi</h6>
                    <p class="text-muted fs-8 mb-2">Sistem akan otomatis memvalidasi apakah jadwal bertabrakan dengan:</p>
                    <ul class="text-muted fs-8 ps-3 mb-0">
                        <li>Jadwal guru lain di jam yang sama</li>
                        <li>Jadwal kelas lain di jam yang sama</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
