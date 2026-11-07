{{--
    parent/_keterangan.blade.php
    Kolom "Keterangan" untuk satu baris AttendanceGate.
    Variabel: $att (AttendanceGate; atribut late_minutes diisi oleh ParentPortalController)
--}}
@php
    $note = trim((string) ($att->note ?? ''));
@endphp

@switch($att->status)
    @case('terlambat')
        <div class="fw-semibold text-warning-emphasis">
            @if(!is_null($att->late_minutes) && $att->late_minutes > 0)
                Terlambat {{ $att->late_minutes >= 60
                    ? intdiv($att->late_minutes, 60).' jam '.($att->late_minutes % 60 ? ($att->late_minutes % 60).' menit' : '')
                    : $att->late_minutes.' menit' }}
            @else
                Terlambat
            @endif
        </div>
        <div>Alasan: {{ $note !== '' ? $note : 'Tidak ada alasan yang dicatat' }}</div>
        @break

    @case('sakit')
        <div class="fw-semibold text-primary-emphasis">Sakit</div>
        <div>Alasan: {{ $note !== '' ? $note : 'Tidak ada alasan yang dicatat' }}</div>
        @break

    @case('izin')
        <div class="fw-semibold text-info-emphasis">Izin</div>
        <div>Alasan: {{ $note !== '' ? $note : 'Tidak ada alasan yang dicatat' }}</div>
        @break

    @default
        {{ $note !== '' ? $note : '-' }}
@endswitch
