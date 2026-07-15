<x-app-layout>
    @section('title', 'Input Absensi Manual')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.attendance.today') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Absensi Manual Gerbang</h3>
            <p class="text-muted mb-0">Input kehadiran siswa secara manual (misal: jika siswa lupa membawa kartu absensi, atau membawa surat izin/sakit).</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.attendance.manual.post') }}">
                        @csrf

                        <!-- Student Selector -->
                        <div class="mb-3">
                            <label for="student_id" class="form-label fw-semibold">Pilih Siswa <span class="text-danger">*</span></label>
                            <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                <option value="">Cari Siswa...</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} (NIS: {{ $student->nis }}) - Kelas {{ $student->class->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status Selector -->
                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold">Status Kehadiran <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">Pilih Status</option>
                                <option value="hadir" {{ old('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="terlambat" {{ old('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="izin" {{ old('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ old('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ old('status') === 'alpha' ? 'selected' : '' }}>Alpha (Tidak Hadir)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes / Description -->
                        <div class="mb-4">
                            <label for="note" class="form-label fw-semibold">Catatan / Keterangan</label>
                            <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" rows="3" placeholder="Contoh: Membawa surat sakit dokter, atau terlambat karena ban motor bocor...">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                            <button type="submit" class="btn btn-primary fw-semibold px-4">
                                <i class="bi bi-save me-1"></i> Simpan Kehadiran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card glass-card border-0">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle me-1"></i> Info WhatsApp</h6>
                    <p class="text-muted fs-8 mb-0">Sistem akan secara otomatis mengirimkan notifikasi WhatsApp real-time kepada nomor HP orang tua yang terdaftar jika pengaturan WhatsApp Gateway dalam status aktif.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
