<x-app-layout>
    @section('title', 'Pengaturan Sistem')

    <div class="row mb-4">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-gear-fill me-2 text-primary"></i>Pengaturan Sistem
            </h3>
            <p class="text-muted mb-0">Sesuaikan jam operasional absensi, durasi token QR, dan gateway WhatsApp.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        <div class="row">
            <!-- Left Side: Tabs / Accordions of Settings -->
            <div class="col-md-8">
                <!-- Group: Attendance -->
                @if(isset($settings['attendance']))
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-primary mb-3">
                                <i class="bi bi-clock me-1"></i> Aturan & Waktu Absensi
                            </h5>
                            <p class="text-muted fs-8 mb-4">Pengaturan waktu buka gerbang, toleransi masuk, dan kepulangan.</p>

                            <div class="row g-3">
                                @foreach($settings['attendance'] as $setting)
                                    <div class="col-md-6">
                                        <label for="{{ $setting->key }}" class="form-label fw-semibold fs-7 mb-1">{{ Str::headline($setting->key) }}</label>
                                        
                                        @if($setting->type === 'integer')
                                            <input type="number" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}" required>
                                        @elseif($setting->type === 'boolean' || $setting->type === 'bool')
                                            <div class="form-check form-switch mt-2">
                                                <input type="hidden" name="{{ $setting->key }}" value="false">
                                                <input class="form-check-input" type="checkbox" name="{{ $setting->key }}" id="{{ $setting->key }}" value="true" {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                                <label class="form-check-label fs-8 text-muted" for="{{ $setting->key }}">Aktif</label>
                                            </div>
                                        @else
                                            <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}" required>
                                        @endif
                                        <div class="form-text fs-8 mt-1 text-muted">{{ $setting->description }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Group: QR Token -->
                @if(isset($settings['qr_token']))
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-primary mb-3">
                                <i class="bi bi-qr-code me-1"></i> Keamanan QR Code
                            </h5>
                            <p class="text-muted fs-8 mb-4">Konfigurasi token dinamis QR Code untuk mencegah duplikasi absensi.</p>

                            <div class="row g-3">
                                @foreach($settings['qr_token'] as $setting)
                                    <div class="col-md-6">
                                        <label for="{{ $setting->key }}" class="form-label fw-semibold fs-7 mb-1">{{ Str::headline($setting->key) }}</label>
                                        
                                        @if($setting->type === 'integer')
                                            <input type="number" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}" required>
                                        @elseif($setting->type === 'boolean' || $setting->type === 'bool')
                                            <div class="form-check form-switch mt-2">
                                                <input type="hidden" name="{{ $setting->key }}" value="false">
                                                <input class="form-check-input" type="checkbox" name="{{ $setting->key }}" id="{{ $setting->key }}" value="true" {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                                <label class="form-check-label fs-8 text-muted" for="{{ $setting->key }}">Aktif</label>
                                            </div>
                                        @else
                                            <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}" required>
                                        @endif
                                        <div class="form-text fs-8 mt-1 text-muted">{{ $setting->description }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Group: WhatsApp Gateway -->
                @if(isset($settings['whatsapp']))
                    <div class="card glass-card border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-primary mb-3">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp Gateway Integration
                            </h5>
                            <p class="text-muted fs-8 mb-4">Atur endpoint API WhatsApp Gateway untuk pengiriman real-time kehadiran kepada orang tua.</p>

                            <div class="row g-3">
                                @foreach($settings['whatsapp'] as $setting)
                                    <div class="col-md-6">
                                        <label for="{{ $setting->key }}" class="form-label fw-semibold fs-7 mb-1">{{ Str::headline($setting->key) }}</label>
                                        
                                        @if($setting->type === 'boolean' || $setting->type === 'bool')
                                            <div class="form-check form-switch mt-2">
                                                <input type="hidden" name="{{ $setting->key }}" value="false">
                                                <input class="form-check-input" type="checkbox" name="{{ $setting->key }}" id="{{ $setting->key }}" value="true" {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                                <label class="form-check-label fs-8 text-muted" for="{{ $setting->key }}">Aktifkan Pengiriman Notifikasi</label>
                                            </div>
                                        @elseif($setting->key === 'whatsapp_provider')
                                            <select name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-select">
                                                <option value="fonnte" {{ old($setting->key, $setting->value) === 'fonnte' ? 'selected' : '' }}>Fonnte</option>
                                                <option value="wablas" {{ old($setting->key, $setting->value) === 'wablas' ? 'selected' : '' }}>WABlas</option>
                                                <option value="woowa" {{ old($setting->key, $setting->value) === 'woowa' ? 'selected' : '' }}>Woowa</option>
                                            </select>
                                        @else
                                            <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}">
                                        @endif
                                        <div class="form-text fs-8 mt-1 text-muted">{{ $setting->description }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Side: Save Panel -->
            <div class="col-md-4">
                <div class="card glass-card border-0 sticky-top" style="top: 24px; z-index: 10;">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-gear-wide-connected text-primary fs-1 mb-3 d-block"></i>
                        <h5 class="fw-bold mb-2">Simpan Perubahan</h5>
                        <p class="text-muted fs-8 mb-4">Pastikan data yang diubah sesuai dengan format yang telah ditentukan di deskripsi masing-masing setelan.</p>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-semibold py-2">
                                <i class="bi bi-save me-1"></i> Terapkan Pengaturan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
