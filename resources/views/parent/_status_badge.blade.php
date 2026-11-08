@switch($status ?? 'tidak_hadir')
    @case('hadir')
        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Hadir</span>
        @break
    @case('terlambat')
        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Terlambat</span>
        @break
    @case('izin')
        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Izin</span>
        @break
    @case('sakit')
        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Sakit</span>
        @break
    @default
        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Tidak Hadir</span>
@endswitch
