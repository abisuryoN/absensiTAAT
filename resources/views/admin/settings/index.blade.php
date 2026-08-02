<x-app-layout>
    @section('title', 'Pengaturan Sistem')

    @push('head')
    <style>
        /* ── Simulation badge on navbar ─────────────────── */
        .sim-badge-floating {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            animation: simPulse 2s infinite;
        }
        @keyframes simPulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(251,191,36,0.5); }
            50%      { box-shadow: 0 0 0 10px rgba(251,191,36,0); }
        }

        .session-card {
            border: 2px solid transparent;
            transition: border-color .25s, box-shadow .25s;
        }
        .session-card:hover { border-color: rgba(var(--bs-primary-rgb),.4); }
        .session-card.editing { border-color: rgba(var(--bs-warning-rgb),.6); }

        .time-sequence-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .time-sequence-row .arrow { color: #6c757d; font-size: .8rem; }

        .tab-settings-nav .nav-link {
            border-radius: 10px;
            padding: .6rem 1.1rem;
            font-weight: 600;
            color: #6c757d;
            border: none;
            transition: background .2s, color .2s;
        }
        .tab-settings-nav .nav-link.active {
            background: var(--bs-primary);
            color: #fff;
        }
        .tab-settings-nav .nav-link:hover:not(.active) {
            background: rgba(var(--bs-primary-rgb),.1);
            color: var(--bs-primary);
        }

        .grade-pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(var(--bs-primary-rgb),.08);
            border-radius: 999px;
            padding: 6px 14px;
            font-weight: 700;
            font-size: .85rem;
            color: var(--bs-primary);
        }

        /* Simulation toggle glow */
        #sim_enabled_switch:checked + .form-check-label { color: #f59e0b; }

        /* Day override group styling */
        #day-override-group label {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #475569;
            font-weight: 500;
            padding: .35rem .85rem;
            transition: all .2s ease;
        }
        #day-override-group label:hover {
            background-color: #e2e8f0;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        #day-override-group input:checked + label {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
            color: #fff !important;
        }
        #day-override-group input:checked + label[for^="day_Sabtu"],
        #day-override-group input:checked + label[for^="day_Minggu"] {
            background-color: var(--bs-danger) !important;
            border-color: var(--bs-danger) !important;
            color: #fff !important;
        }
        #day-override-group input:checked + label[for="day_Automatic"] {
            background-color: #64748b !important;
            border-color: #64748b !important;
            color: #fff !important;
        }
    </style>
    @endpush

    {{-- Page header --}}
    <div class="row mb-4">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-gear-fill me-2 text-primary"></i>Pengaturan Sistem
            </h3>
            <p class="text-muted mb-0">Konfigurasi jadwal sesi, simulasi waktu, notifikasi, dan QR Code.</p>
        </div>
    </div>

    <div class="row">
        {{-- LEFT COLUMN ──────────────────────────────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Tab navigation --}}
            <ul class="nav tab-settings-nav mb-4 gap-1" id="settingsTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-simulation" type="button">
                        <i class="bi bi-clock-history me-1"></i> Simulasi
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sessions" type="button">
                        <i class="bi bi-layers me-1"></i> Sesi Sekolah
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-grade-mapping" type="button">
                        <i class="bi bi-diagram-3 me-1"></i> Mapping Kelas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-attendance" type="button">
                        <i class="bi bi-clock me-1"></i> Absensi
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-qr" type="button">
                        <i class="bi bi-qr-code me-1"></i> QR Code
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                {{-- ══════════════════════════════════════════════════════════
                     TAB 1 – SIMULATION
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade show active" id="tab-simulation">
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            {{-- Header --}}
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">
                                        <i class="bi bi-clock-history text-warning me-2"></i>Simulasi Tanggal & Waktu
                                    </h5>
                                    <p class="text-muted fs-8 mb-0">
                                        Testing seluruh sistem tanpa mengubah jam server.
                                    </p>
                                </div>
                                <span id="sim-status-pill" class="badge rounded-pill {{ filter_var($settings->get('simulation_enabled')?->value, FILTER_VALIDATE_BOOLEAN) ? 'bg-warning text-dark' : 'bg-secondary' }} fs-8">
                                    {{ filter_var($settings->get('simulation_enabled')?->value, FILTER_VALIDATE_BOOLEAN) ? '⚡ Aktif' : '○ Nonaktif' }}
                                </span>
                            </div>

                            <form id="form-simulation">
                                @csrf
                                <div class="row g-3">
                                    {{-- Enable switch --}}
                                    <div class="col-12">
                                        <div class="p-3 rounded-3" style="background:rgba(245,158,11,.08);">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input fs-5" type="checkbox"
                                                       id="sim_enabled_switch"
                                                       name="simulation_enabled"
                                                       value="true"
                                                       {{ filter_var($settings->get('simulation_enabled')?->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="sim_enabled_switch">
                                                    Enable Simulation
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-7 mb-1">
                                            <i class="bi bi-calendar3 me-1 text-primary"></i>Simulation Date
                                        </label>
                                        <input type="date" class="form-control" name="simulation_date" id="sim_date"
                                               value="{{ $settings->get('simulation_date')?->value }}">
                                        <div class="form-text fs-8">Tanggal yang akan digunakan sistem (Y-m-d).</div>
                                    </div>

                                    {{-- Time --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-7 mb-1">
                                            <i class="bi bi-clock me-1 text-primary"></i>Simulation Time
                                        </label>
                                        <input type="time" class="form-control" name="simulation_time" id="sim_time"
                                               value="{{ $settings->get('simulation_time')?->value }}">
                                        <div class="form-text fs-8">Jam yang akan digunakan (HH:mm).</div>
                                    </div>

                                    {{-- Day override --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold fs-7 mb-1">
                                            <i class="bi bi-calendar-week me-1 text-primary"></i>Simulation Day
                                        </label>
                                        <div class="d-flex flex-wrap gap-2 mt-1" id="day-override-group">
                                            @php
                                                $days = ['Automatic', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                                $currentDay = $settings->get('simulation_day_override')?->value ?? 'Automatic';
                                            @endphp
                                            @foreach($days as $day)
                                                <input type="radio" class="btn-check" name="simulation_day_override"
                                                       id="day_{{ $day }}" value="{{ $day }}"
                                                       {{ $currentDay === $day ? 'checked' : '' }}>
                                                <label class="btn btn-sm {{ in_array($day, ['Sabtu','Minggu']) ? 'btn-outline-danger' : ($day === 'Automatic' ? 'btn-outline-secondary' : 'btn-outline-primary') }}" for="day_{{ $day }}">
                                                    {{ $day }}
                                                </label>
                                            @endforeach
                                        </div>
                                        <div class="form-text fs-8 mt-2">
                                            <span class="text-danger fw-semibold">Sabtu/Minggu</span> = testing hari libur weekend.
                                        </div>
                                    </div>

                                    {{-- Current simulated datetime preview --}}
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 d-flex align-items-center gap-3" style="background:rgba(99,102,241,.08);">
                                            <i class="bi bi-stopwatch fs-4 text-primary"></i>
                                            <div>
                                                <div class="fw-bold fs-7" id="sim-preview-datetime">
                                                    {{ app(\App\Services\DateTimeService::class)->now()->format('d M Y, H:i:s') }}
                                                </div>
                                                <div class="text-muted fs-8" id="sim-preview-day">
                                                    {{ app(\App\Services\DateTimeService::class)->currentDay() }}
                                                    {{ app(\App\Services\DateTimeService::class)->isSimulationEnabled() ? '(Simulasi Aktif)' : '(Waktu Nyata)' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-warning fw-semibold px-4" id="btn-save-simulation">
                                        <i class="bi bi-save me-1"></i> Simpan Simulasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     TAB 2 – SESI SEKOLAH
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade" id="tab-sessions">
                    {{-- Multiple sessions toggle --}}
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-1"><i class="bi bi-toggles me-2 text-primary"></i>Fitur Multi-Sesi</h5>
                            <p class="text-muted fs-8 mb-3">
                                Aktifkan jika sekolah memiliki sesi pagi dan siang. Jika dinonaktifkan, sistem menggunakan pengaturan jam tunggal.
                            </p>
                            <form id="form-multi-session">
                                @csrf
                                <div class="form-check form-switch">
                                    <input class="form-check-input fs-5" type="checkbox"
                                           name="multiple_sessions_enabled" id="multi_session_switch" value="true"
                                           {{ filter_var($settings->get('multiple_sessions_enabled')?->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="multi_session_switch">
                                        Aktifkan Sistem Multi-Sesi
                                    </label>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label fw-semibold fs-7">Sesi Default</label>
                                    <div class="custom-select-wrapper" data-placeholder="— Tidak ada default —">
                                        <select class="form-select" name="default_school_session_id">
                                            <option value="">— Tidak ada default —</option>
                                            @foreach($sessions as $sess)
                                                <option value="{{ $sess->id }}"
                                                    {{ ($settings->get('default_school_session_id')?->value == $sess->id) ? 'selected' : '' }}>
                                                    {{ $sess->name }} ({{ substr($sess->school_start_time,0,5) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                                        <i class="bi bi-save me-1"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Session list + editor --}}
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="fw-bold mb-0"><i class="bi bi-layers me-2 text-primary"></i>Manajemen Sesi</h5>
                                <button class="btn btn-sm btn-outline-primary fw-semibold" id="btn-add-session">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Sesi
                                </button>
                            </div>

                            <div id="sessions-container">
                                @foreach($sessions as $sess)
                                    <div class="card session-card glass-card border-0 mb-3" data-session-id="{{ $sess->id }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div>
                                                    <span class="fw-bold fs-6">{{ $sess->icon_label }}</span>
                                                    <span class="badge {{ $sess->is_active ? 'bg-success' : 'bg-secondary' }} ms-2 fs-8">
                                                        {{ $sess->is_active ? 'Aktif' : 'Nonaktif' }}
                                                    </span>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-warning btn-edit-session" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger btn-delete-session" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Time sequence visual --}}
                                            <div class="time-sequence-row mb-3">
                                                <span class="badge bg-success-subtle text-success fw-semibold">{{ substr($sess->gate_open_time,0,5) }} Buka</span>
                                                <span class="arrow">→</span>
                                                <span class="badge bg-primary-subtle text-primary fw-semibold">{{ substr($sess->school_start_time,0,5) }} Mulai</span>
                                                <span class="arrow">→</span>
                                                <span class="badge bg-warning-subtle text-warning fw-semibold">{{ substr($sess->gate_close_time,0,5) }} Tutup</span>
                                                <span class="arrow">→</span>
                                                <span class="badge bg-danger-subtle text-danger fw-semibold">{{ substr($sess->auto_alpha_time,0,5) }} Auto-Alpha</span>
                                                <span class="arrow">→</span>
                                                <span class="badge bg-secondary-subtle text-secondary fw-semibold">{{ substr($sess->school_end_time,0,5) }} Selesai</span>
                                            </div>
                                            <div class="text-muted fs-8">Toleransi terlambat: <strong>{{ $sess->late_threshold_minutes }} menit</strong></div>

                                            {{-- Edit form (hidden by default) --}}
                                            <div class="session-edit-form mt-3 d-none">
                                                @include('admin.settings._session_form', ['sess' => $sess])
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Template for new session --}}
                                <div class="card session-card glass-card border-0 mb-3 d-none" id="session-new-template">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="fw-bold text-primary">Sesi Baru</span>
                                            <button class="btn btn-sm btn-outline-danger btn-cancel-new-session">
                                                <i class="bi bi-x-circle"></i> Batal
                                            </button>
                                        </div>
                                        @include('admin.settings._session_form', ['sess' => null])
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-2">
                                <button class="btn btn-primary fw-semibold px-4" id="btn-save-all-sessions">
                                    <i class="bi bi-save me-1"></i> Simpan Semua Sesi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     TAB 3 – GRADE MAPPING
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade" id="tab-grade-mapping">
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-diagram-3 me-2 text-primary"></i>Mapping Sesi per Tingkat
                            </h5>
                            <p class="text-muted fs-8 mb-4">
                                Tentukan sesi mana yang digunakan untuk setiap tingkat (X, XI, XII) pada tahun ajaran tertentu.
                                Berlaku untuk <strong>seluruh kelas di tingkat tersebut</strong>, kecuali ada override per-kelas.
                            </p>

                            <form id="form-grade-mapping">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-7">Tahun Ajaran</label>
                                    <div class="custom-select-wrapper" data-placeholder="Pilih Tahun Ajaran">
                                        <select class="form-select" name="academic_year_id" id="mapping-academic-year">
                                            @foreach($academicYears as $ay)
                                                <option value="{{ $ay->id }}" {{ ($activeAcademicYear?->id == $ay->id) ? 'selected' : '' }}>
                                                    {{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @foreach([10 => 'X', 11 => 'XI', 12 => 'XII'] as $level => $label)
                                    <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3" style="background:rgba(99,102,241,.06);">
                                        <span class="grade-pill" style="min-width: 100px;">Kelas {{ $label }}</span>
                                        <div class="flex-grow-1">
                                            <div class="custom-select-wrapper" data-placeholder="— Gunakan Default —">
                                                <select class="form-select" name="mappings[{{ $level }}][school_session_id]">
                                                    <option value="">— Gunakan Default —</option>
                                                    @foreach($sessions as $sess)
                                                        <option value="{{ $sess->id }}"
                                                            {{ ($gradeMappings->get($level)?->school_session_id == $sess->id) ? 'selected' : '' }}>
                                                            {{ $sess->icon_label }} ({{ substr($sess->school_start_time,0,5) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" name="mappings[{{ $level }}][grade_level]" value="{{ $level }}">
                                    </div>
                                @endforeach

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                                        <i class="bi bi-save me-1"></i> Simpan Mapping
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Session preview card --}}
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold mb-0"><i class="bi bi-broadcast me-1 text-success"></i>Status Sesi Saat Ini</h6>
                                <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-preview">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <div id="session-preview-container">
                                <div class="text-center text-muted py-3">
                                    <div class="spinner-border spinner-border-sm me-2"></div> Memuat...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     TAB 4 – ATTENDANCE
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade" id="tab-attendance">
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-primary mb-3">
                                <i class="bi bi-clock me-1"></i> Aturan & Waktu Absensi
                            </h5>
                            <p class="text-muted fs-8 mb-4">Pengaturan jam global (digunakan jika multi-sesi dinonaktifkan).</p>
                            <form id="form-attendance">
                                @csrf
                                <div class="row g-3">
                                    @foreach($settings->filter(fn($s) => $s->group === 'attendance') as $setting)
                                        <div class="col-md-6">
                                            <label for="{{ $setting->key }}" class="form-label fw-semibold fs-7 mb-1">{{ Str::headline($setting->key) }}</label>
                                            @if($setting->type === 'integer')
                                                <input type="number" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}">
                                            @elseif(in_array($setting->type, ['boolean','bool']))
                                                <div class="form-check form-switch mt-2">
                                                    <input type="hidden" name="{{ $setting->key }}" value="false">
                                                    <input class="form-check-input" type="checkbox" name="{{ $setting->key }}" id="{{ $setting->key }}" value="true" {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                                    <label class="form-check-label fs-8 text-muted" for="{{ $setting->key }}">Aktif</label>
                                                </div>
                                            @else
                                                <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}">
                                            @endif
                                            <div class="form-text fs-8 mt-1 text-muted">{{ $setting->description }}</div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                                        <i class="bi bi-save me-1"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>



                {{-- ══════════════════════════════════════════════════════════
                     TAB 6 – QR CODE
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade" id="tab-qr">
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-primary mb-3">
                                <i class="bi bi-qr-code me-1"></i> Keamanan QR Code
                            </h5>
                            <form id="form-qr">
                                @csrf
                                <div class="row g-3">
                                    @foreach($settings->filter(fn($s) => $s->group === 'qr_token') as $setting)
                                        <div class="col-md-6">
                                            <label for="qr_{{ $setting->key }}" class="form-label fw-semibold fs-7 mb-1">{{ Str::headline($setting->key) }}</label>
                                            @if($setting->type === 'integer')
                                                <input type="number" name="{{ $setting->key }}" id="qr_{{ $setting->key }}" class="form-control" value="{{ $setting->value }}">
                                            @else
                                                <input type="text" name="{{ $setting->key }}" id="qr_{{ $setting->key }}" class="form-control" value="{{ $setting->value }}">
                                            @endif
                                            <div class="form-text fs-8 mt-1 text-muted">{{ $setting->description }}</div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                                        <i class="bi bi-save me-1"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>{{-- /.tab-content --}}
        </div>{{-- /.col-lg-8 --}}

        {{-- RIGHT COLUMN ─────────────────────────────────────────────────── --}}
        <div class="col-lg-4">
            {{-- Simulation badge card --}}
            <div class="card glass-card border-0 mb-4 sticky-top" style="top: 90px; margin-top: 54px; z-index: 10;">
                <div class="card-body p-4 text-center">
                    <div id="right-sim-icon" class="fs-1 mb-2">
                        @if(app(\App\Services\DateTimeService::class)->isSimulationEnabled())
                            ⚡
                        @else
                            <i class="bi bi-gear-wide-connected text-primary"></i>
                        @endif
                    </div>
                    <h5 class="fw-bold mb-1">Status Sistem</h5>
                    <div class="text-muted fs-8 mb-3">Waktu yang sedang digunakan sistem:</div>
                    <div class="p-3 rounded-3 mb-3" style="background:rgba(99,102,241,.08);">
                        <div class="fw-bold" id="right-sim-datetime">
                            {{ app(\App\Services\DateTimeService::class)->now()->format('d M Y, H:i') }}
                        </div>
                        <div class="text-muted fs-8" id="right-sim-day">
                            {{ app(\App\Services\DateTimeService::class)->currentDay() }}
                        </div>
                    </div>
                    @if(app(\App\Services\DateTimeService::class)->isSimulationEnabled())
                        <span class="badge bg-warning text-dark px-3 py-2 fs-8 mb-3 d-block">
                            ⚡ MODE SIMULASI AKTIF
                        </span>
                    @endif

                    {{-- Multi-session status --}}
                    <div class="border-top pt-3 mt-2">
                        <div class="d-flex justify-content-between align-items-center fs-8">
                            <span class="text-muted">Multi-Sesi</span>
                            <span class="badge {{ filter_var($settings->get('multiple_sessions_enabled')?->value, FILTER_VALIDATE_BOOLEAN) ? 'bg-success' : 'bg-secondary' }}">
                                {{ filter_var($settings->get('multiple_sessions_enabled')?->value, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center fs-8 mt-2">
                            <span class="text-muted">Jumlah Sesi</span>
                            <span class="fw-bold">{{ $sessions->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ── Generic AJAX form helper ──────────────────────────────────────────────
    function ajaxSave(url, formData, btn) {
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Tersimpan!', text: data.message, timer: 2000, showConfirmButton: false });
            } else {
                const errMsg = data.errors
                    ? Object.values(data.errors).flat().join('\n')
                    : (data.message || 'Terjadi kesalahan.');
                Swal.fire({ icon: 'error', title: 'Gagal', text: errMsg });
            }
            return data;
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            Swal.fire({ icon: 'error', title: 'Network Error', text: 'Gagal menghubungi server.' });
        });
    }

    // ── SIMULATION FORM ───────────────────────────────────────────────────────
    document.getElementById('form-simulation').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-simulation');
        const fd  = new FormData(this);
        // Checkbox: if unchecked, FormData won't include it, so force false
        if (!document.getElementById('sim_enabled_switch').checked) {
            fd.set('simulation_enabled', 'false');
        } else {
            fd.set('simulation_enabled', 'true');
        }

        ajaxSave("{{ route('admin.settings.simulation') }}", fd, btn).then(data => {
            if (data?.success) {
                // Update preview
                document.getElementById('sim-preview-datetime').textContent = data.current.datetime;
                document.getElementById('sim-preview-day').textContent      = data.current.day + (data.current.enabled ? ' (Simulasi Aktif)' : ' (Waktu Nyata)');
                document.getElementById('right-sim-datetime').textContent   = data.current.datetime;
                document.getElementById('right-sim-day').textContent        = data.current.day;
                const pill = document.getElementById('sim-status-pill');
                if (data.current.enabled) {
                    pill.className = 'badge rounded-pill bg-warning text-dark fs-8';
                    pill.textContent = '⚡ Aktif';
                } else {
                    pill.className = 'badge rounded-pill bg-secondary fs-8';
                    pill.textContent = '○ Nonaktif';
                }
            }
        });
    });

    // ── GENERIC SETTINGS FORMS ────────────────────────────────────────────────
    ['form-multi-session', 'form-attendance', 'form-qr'].forEach(id => {
        const form = document.getElementById(id);
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            ajaxSave("{{ route('admin.settings.update') }}", new FormData(this), btn);
        });
    });

    // ── GRADE MAPPING FORM ────────────────────────────────────────────────────
    document.getElementById('form-grade-mapping').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const fd  = new FormData(this);
        const body = {
            _token: CSRF,
            academic_year_id: fd.get('academic_year_id'),
            mappings: [],
        };
        [[10,'X'],[11,'XI'],[12,'XII']].forEach(([level]) => {
            body.mappings.push({
                grade_level: level,
                school_session_id: fd.get(`mappings[${level}][school_session_id]`) || null,
            });
        });
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        fetch("{{ route('admin.settings.grade-mappings') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false; btn.innerHTML = origHtml;
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Tersimpan!', text: data.message, timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: JSON.stringify(data.errors) });
            }
        });
    });

    // ── SESSION EDITOR ────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-edit-session').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.session-card');
            const editForm = card.querySelector('.session-edit-form');
            card.classList.toggle('editing');
            editForm.classList.toggle('d-none');
            this.innerHTML = card.classList.contains('editing')
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-pencil"></i>';
        });
    });

    document.getElementById('btn-add-session').addEventListener('click', function() {
        const tpl = document.getElementById('session-new-template');
        tpl.classList.remove('d-none');
        this.disabled = true;
    });

    document.querySelector('.btn-cancel-new-session')?.addEventListener('click', function() {
        document.getElementById('session-new-template').classList.add('d-none');
        document.getElementById('btn-add-session').disabled = false;
    });

    document.querySelectorAll('.btn-delete-session').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.session-card');
            const id   = card.dataset.sessionId;
            Swal.fire({
                title: 'Hapus Sesi?',
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch(`{{ url('admin/settings/sessions') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        card.remove();
                        Swal.fire({ icon: 'success', title: 'Dihapus!', text: data.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                    }
                });
            });
        });
    });

    document.getElementById('btn-save-all-sessions').addEventListener('click', function() {
        const btn = this;
        const cards = document.querySelectorAll('#sessions-container .session-card:not(#session-new-template)');
        const sessions = [];
        cards.forEach(card => {
            const form = card.querySelector('.session-edit-form form, form');
            if (!form) return;
            const fd = new FormData(form);
            sessions.push({
                id:                      card.dataset.sessionId || null,
                name:                    fd.get('name'),
                gate_open_time:          fd.get('gate_open_time'),
                school_start_time:       fd.get('school_start_time'),
                late_threshold_minutes:  fd.get('late_threshold_minutes'),
                gate_close_time:         fd.get('gate_close_time'),
                auto_alpha_time:         fd.get('auto_alpha_time'),
                school_end_time:         fd.get('school_end_time'),
                is_active:               fd.get('is_active') === '1' ? true : false,
            });
        });
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        fetch("{{ route('admin.settings.sessions.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ _token: CSRF, sessions }),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false; btn.innerHTML = origHtml;
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Tersimpan!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                const msg = data.errors ? Object.values(data.errors).flat().join('\n') : data.message;
                Swal.fire({ icon: 'error', title: 'Validasi Gagal', text: msg });
            }
        });
    });

    // ── SESSION PREVIEW ───────────────────────────────────────────────────────
    function loadSessionPreview() {
        fetch("{{ route('admin.settings.session-preview') }}", { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const c = document.getElementById('session-preview-container');
            if (!data.sessions.length) {
                c.innerHTML = '<p class="text-muted text-center fs-8">Tidak ada sesi aktif.</p>';
                return;
            }
            c.innerHTML = data.sessions.map(s => `
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2"
                     style="background:rgba(99,102,241,.06)">
                    <div>
                        <span class="fw-bold fs-7">${s.name}</span>
                        <span class="text-muted fs-8 ms-2">${s.gate_open_time}–${s.school_end_time}</span>
                    </div>
                    <span class="badge ${s.status === 'Sedang Berlangsung' ? 'bg-success' : (s.status === 'Selesai' ? 'bg-secondary' : 'bg-warning text-dark')} fs-8">
                        ${s.status}
                    </span>
                </div>
            `).join('');
        });
    }
    loadSessionPreview();
    document.getElementById('btn-refresh-preview')?.addEventListener('click', loadSessionPreview);
    </script>
    @endpush

</x-app-layout>
