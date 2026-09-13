{{--
    Notification Bar Component
    Compact, non-blocking horizontal notification bar with explicit SASS styling.
--}}
@props([
    'type' => 'info',
    'message' => null,
    'items' => [],
    'icon' => null,
    'dismissible' => true,
])

@php
    $variantClass = match($type) {
        'welcome' => 'notification-welcome',
        'success' => 'notification-success',
        'warning' => 'notification-warning',
        'error' => 'notification-error',
        default => 'notification-info',
    };

    $defaultIcon = match($type) {
        'welcome' => '👋',
        'success' => 'bi-check-lg',
        'warning' => 'bi-exclamation-triangle-fill',
        'error' => 'bi-x-circle-fill',
        default => 'bi-info-circle-fill',
    };

    $activeIcon = $icon ?? $defaultIcon;
    $isEmoji = str_starts_with($activeIcon, '👋') || str_starts_with($activeIcon, '🔔');
@endphp

<div x-data="{ show: true }"
     x-show="show"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 max-h-20"
     x-transition:leave-end="opacity-0 max-h-0"
     class="notification-bar {{ $variantClass }}"
     role="alert">

    {{-- 1. Icon Box Badge (Fixed Left) --}}
    <div class="notification-icon-badge">
        @if($isEmoji)
            <span>{{ $activeIcon }}</span>
        @else
            <i class="bi {{ $activeIcon }}"></i>
        @endif
    </div>

    {{-- 2. Scrollable Content Region (Middle) --}}
    <div class="notification-content-region">
        @if(!empty($items))
            @foreach($items as $index => $item)
                @if($index === 0)
                    <span class="notification-greeting">{!! $item !!}</span>
                @elseif(str_starts_with($item, 'Role:'))
                    <span class="notification-role-badge">
                        {!! str_replace('Role: ', '', $item) !!}
                    </span>
                @else
                    <span class="notification-detail-item">{!! $item !!}</span>
                @endif

                @if(!$loop->last)
                    <span class="notification-dot">•</span>
                @endif
            @endforeach
        @elseif($message)
            <span class="notification-greeting">{!! $message !!}</span>
        @else
            <div class="d-flex align-items-center gap-2">
                {{ $slot }}
            </div>
        @endif
    </div>

    {{-- 3. Fixed Dismiss Button (Fixed Right) --}}
    @if($dismissible)
        <button type="button"
                @click="show = false"
                onclick="this.closest('.notification-bar').remove()"
                class="notification-dismiss-btn"
                aria-label="Tutup notifikasi">
            <i class="bi bi-x-lg"></i>
        </button>
    @endif
</div>
