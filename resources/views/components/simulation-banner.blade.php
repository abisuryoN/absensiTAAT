{{--
    Simulation Banner Component
    Shows a dismissible warning banner when simulation mode is active.

    Usage:
        <x-simulation-banner />                   — auto-resolves current time
        <x-simulation-banner :compact="true" />   — compact inline badge variant

    Props:
        $compact  bool  default false  — use compact single-line badge instead of full banner
--}}
@props(['compact' => false])

@php
    /** @var \App\Services\DateTimeService $dts */
    $dts = app(\App\Services\DateTimeService::class);
    $simEnabled = $dts->isSimulationEnabled();
    if (!$simEnabled) return; // render nothing when sim is off
    $simNow = $dts->now();
@endphp

@if($compact)
    {{-- ─── Compact pill badge ─── --}}
    <span class="badge rounded-pill text-bg-warning d-inline-flex align-items-center gap-1 fw-bold"
          style="font-size: 0.72rem; letter-spacing: 0.02em; animation: simPulseBadge 2s infinite;"
          title="Mode simulasi aktif">
        ⚡ SIM
        <span class="fw-normal">{{ $simNow->translatedFormat('D, d M H:i') }}</span>
    </span>
    <style>
        @keyframes simPulseBadge {
            0%,100% { box-shadow: 0 0 0 0 rgba(251,191,36,.5); }
            50%      { box-shadow: 0 0 0 5px rgba(251,191,36,0); }
        }
    </style>
@else
    {{-- ─── Full dismissible banner ─── --}}
    <div class="alert alert-warning d-flex align-items-center gap-3 py-2 px-3 mb-3 rounded-3 border-warning border-2 shadow-sm sim-mode-banner"
         role="alert"
         id="simBanner"
         style="background: linear-gradient(90deg, #fef9c3 0%, #fefce8 100%); animation: simBannerPulse 3s ease infinite;">
        <div class="flex-shrink-0 fs-4 lh-1">⚡</div>
        <div class="flex-grow-1 small">
            <strong>MODE SIMULASI AKTIF</strong>
            &mdash;
            Waktu sistem: <strong>{{ $simNow->translatedFormat('l, d F Y — H:i:s') }}</strong>.
            Data absensi dan logika hari akan mengikuti waktu simulasi ini.
        </div>
        <a href="{{ route('admin.settings.index') }}"
           class="btn btn-sm btn-warning fw-semibold text-dark px-3 py-1 flex-shrink-0"
           style="font-size: 0.78rem;">
            <i class="bi bi-gear-fill me-1"></i>Pengaturan
        </a>
        <button type="button" class="btn-close btn-close-sm flex-shrink-0" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <style>
        @keyframes simBannerPulse {
            0%,100% { border-color: #f59e0b !important; }
            50%      { border-color: #fbbf24 !important; }
        }
    </style>
@endif
