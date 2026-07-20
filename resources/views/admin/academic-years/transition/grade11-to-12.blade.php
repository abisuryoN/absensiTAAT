@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-arrow-up me-2"></i>Kenaikan Kelas 11 → 12
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Tahun Ajaran Aktif:</strong> {{ $currentYear->name }}<br>
                        <strong>Tahun Ajaran Baru:</strong> {{ $nextYear->name }}<br>
                        <strong>Total Siswa Kelas 11:</strong> {{ count($mappings) }} siswa
                    </div>

                    @if(count($mappings) === 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Tidak ada siswa kelas 11 yang perlu dipindahkan.
                        </div>
                        <a href="{{ route('admin.academic-years.transition.grade12-graduate') }}" class="btn btn-primary">
                            Lanjut ke Langkah 3 →
                        </a>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Sistem telah melakukan <strong>auto-mapping</strong> berdasarkan jurusan yang sama. Silakan review dan edit jika diperlukan.
                        </div>

                        <form id="grade11Form" method="POST" action="{{ route('admin.academic-years.transition.process-grade11-to-12') }}">
                            @csrf
                            <input type="hidden" name="next_year_id" value="{{ $nextYear->id }}">

                            <!-- Students Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas Saat Ini</th>
                                            <th>Jurusan</th>
                                            <th>Kelas Usulan (Auto)</th>
                                            <th style="width: 250px;">Kelas Tujuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mappings as $mapping)
                                            <tr>
                                                <td>{{ $mapping['student']->nis }}</td>
                                                <td>{{ $mapping['student']->name }}</td>
                                                <td>{{ $mapping['current_class']->name }}</td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        {{ $mapping['major']->name }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($mapping['suggested_class'])
                                                        <span class="badge bg-success">
                                                            {{ $mapping['suggested_class']->name }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">
                                                            Tidak ada kelas tersedia
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <select name="assignments[{{ $loop->index }}][class_id]" 
                                                            class="form-select form-select-sm" 
                                                            required>
                                                        <option value="">-- Pilih Kelas --</option>
                                                        @foreach($grade12_classes as $class)
                                                            @if($class->major_id == $mapping['major']->id)
                                                                <option value="{{ $class->id }}" 
                                                                        {{ $mapping['suggested_class'] && $mapping['suggested_class']->id == $class->id ? 'selected' : '' }}>
                                                                    {{ $class->name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="assignments[{{ $loop->index }}][student_id]" value="{{ $mapping['student']->id }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="{{ route('admin.academic-years.transition.grade10-to-11') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan & Lanjut ke Langkah 3
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form Validation
    document.getElementById('grade11Form')?.addEventListener('submit', function(e) {
        const selects = document.querySelectorAll('select[name*="[class_id]"]');
        let hasError = false;
        
        selects.forEach(select => {
            if (!select.value) {
                hasError = true;
                select.classList.add('is-invalid');
            } else {
                select.classList.remove('is-invalid');
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Pastikan semua siswa telah memiliki kelas tujuan');
        }
    });
});
</script>
@endpush
@endsection