@switch($status ?? 'alpa')
    @case('hadir')
        <span class="badge bg-success-subtle text-success border border-success-subtle">Hadir</span>
        @break
    @case('terlambat')
        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Terlambat</span>
        @break
    @case('izin')
        <span class="badge bg-info-subtle text-info border border-info-subtle">Izin</span>
        @break
    @case('sakit')
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Sakit</span>
        @break
    @default
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Alpa</span>
@endswitch