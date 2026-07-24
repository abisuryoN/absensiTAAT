<x-app-layout>
    @section('title', 'Edit Hari Libur Sekolah')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.school-holidays.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Edit Hari Libur Sekolah</h3>
            <p class="text-muted mb-0">Perbarui informasi hari libur sekolah khusus.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.school-holidays.update', $schoolHoliday) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Judul Hari Libur <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="Contoh: Rapat Guru, Libur Isra Mi'raj Sekolah"
                                value="{{ old('title', $schoolHoliday->title) }}"
                                required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="holiday_date" class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="holiday_date" id="holiday_date"
                                class="form-control @error('holiday_date') is-invalid @enderror"
                                value="{{ old('holiday_date', $schoolHoliday->holiday_date->format('Y-m-d')) }}"
                                required>
                            @error('holiday_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Tidak boleh ada dua hari libur aktif pada tanggal yang sama.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Deskripsi / Keterangan</label>
                            <textarea name="description" id="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="3"
                                placeholder="Keterangan tambahan (opsional)...">{{ old('description', $schoolHoliday->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $schoolHoliday->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Aktif <span class="text-muted fw-normal">(Hari libur ini akan berlaku di seluruh sistem)</span>
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.school-holidays.index') }}" class="btn btn-light border me-2">Batal</a>
                            <button type="submit" class="btn btn-primary fw-semibold px-4">
                                <i class="bi bi-save me-1"></i> Perbarui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 rounded-3 p-3" style="background: #f8fafc;">
                <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-clock-history me-1"></i> Info Data</h6>
                <table class="table table-sm table-borderless mb-0" style="font-size:0.85rem;">
                    <tr>
                        <td class="text-muted">Ditambahkan oleh</td>
                        <td class="fw-semibold">{{ $schoolHoliday->creator?->name ?? 'Sistem' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat pada</td>
                        <td class="fw-semibold">{{ $schoolHoliday->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Terakhir diperbarui</td>
                        <td class="fw-semibold">{{ $schoolHoliday->updated_at->format('d M Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status saat ini</td>
                        <td>
                            @if($schoolHoliday->is_active)
                                <span class="badge bg-success-subtle text-success fw-semibold">Aktif</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary fw-semibold">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
