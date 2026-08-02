{{-- Partial: _session_form.blade.php --}}
{{-- Props: $sess (SchoolSession|null) --}}
<form class="session-inline-form">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold fs-7 mb-1">Nama Sesi</label>
            <input type="text" class="form-control" name="name" placeholder="cth: Pagi" value="{{ $sess?->name }}">
        </div>
        <div class="col-md-6 d-flex align-items-center pt-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active_{{ $sess?->id ?? 'new' }}" value="1" {{ ($sess === null || $sess->is_active) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold fs-7" for="is_active_{{ $sess?->id ?? 'new' }}">Aktif</label>
            </div>
        </div>
        <div class="col-12">
            <p class="text-muted fs-8 mb-2">
                <i class="bi bi-info-circle me-1"></i>
                Urutan harus: <strong>Buka Gerbang → Mulai → Tutup Gerbang → Auto Alpha → Selesai</strong>
            </p>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold fs-7 mb-1 text-success"><i class="bi bi-door-open me-1"></i>Buka Gerbang</label>
            <input type="time" class="form-control" name="gate_open_time" value="{{ $sess ? substr($sess->gate_open_time,0,5) : '06:00' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold fs-7 mb-1 text-primary"><i class="bi bi-flag me-1"></i>Mulai Sekolah</label>
            <input type="time" class="form-control" name="school_start_time" value="{{ $sess ? substr($sess->school_start_time,0,5) : '07:00' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold fs-7 mb-1 text-warning"><i class="bi bi-door-closed me-1"></i>Tutup Gerbang</label>
            <input type="time" class="form-control" name="gate_close_time" value="{{ $sess ? substr($sess->gate_close_time,0,5) : '08:00' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold fs-7 mb-1 text-danger"><i class="bi bi-x-octagon me-1"></i>Auto Alpha</label>
            <input type="time" class="form-control" name="auto_alpha_time" value="{{ $sess ? substr($sess->auto_alpha_time,0,5) : '09:00' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold fs-7 mb-1 text-secondary"><i class="bi bi-stop-circle me-1"></i>Selesai</label>
            <input type="time" class="form-control" name="school_end_time" value="{{ $sess ? substr($sess->school_end_time,0,5) : '13:00' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold fs-7 mb-1"><i class="bi bi-hourglass-split me-1"></i>Toleransi Terlambat (mnt)</label>
            <input type="number" class="form-control" name="late_threshold_minutes" min="0" max="120" value="{{ $sess?->late_threshold_minutes ?? 15 }}">
        </div>
    </div>
</form>
