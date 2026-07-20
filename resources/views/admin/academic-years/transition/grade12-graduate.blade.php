@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-certificate me-2"></i>Kelulusan Kelas 12
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Tahun Ajaran Aktif:</strong> {{ $currentYear->name }}<br>
                        <strong>Tahun Ajaran Baru:</strong> {{ $nextYear->name }}<br>
                        <strong>Total Siswa Kelas 12:</strong> {{ count($mappings) }} siswa
                    </div>

                    @if(count($mappings) === 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Tidak ada siswa kelas 12 yang perlu diproses.
                        </div>
                        <a href="{{ route('admin.academic-years.transition.finalize') }}" class="btn btn-primary">
                            Lanjut ke Finalisasi →
                        </a>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Status default untuk semua siswa adalah <strong>Lulus</strong>. Ubah ke "Tinggal Kelas" jika siswa tidak lulus.
                        </div>

                        <form id="grade12Form" method="POST" action="{{ route('admin.academic-years.transition.process-grade12-graduate') }}">
                            @csrf
                            <input type="hidden" name="next_year_id" value="{{ $nextYear->id }}">

                            <!-- Students Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas</th>
                                            <th>Jurusan</th>
                                            <th style="width: 200px;">Status</th>
                                            <th style="width: 250px;">Kelas (jika Tinggal Kelas)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mappings as $mapping)
                                            <tr data-student-id="{{ $mapping['student']->id }}">
                                                <td>{{ $mapping['student']->nis }}</td>
                                                <td>{{ $mapping['student']->name }}</td>
                                                <td>{{ $mapping['current_class']->name }}</td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        {{ $mapping['current_class']->major->name }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <select name="assignments[{{ $loop->index }}][status]" 
                                                            class="form-select form-select-sm status-select" 
                                                            data-index="{{ $loop->index }}"
                                                            required>
                                                        <option value="Lulus" selected>Lulus</option>
                                                        <option value="Tinggal Kelas">Tinggal Kelas</option>
                                                    </select>
                                                    <input type="hidden" name="assignments[{{ $loop->index }}][student_id]" value="{{ $mapping['student']->id }}">
                                                </td>
                                                <td>
                                                    <select name="assignments[{{ $loop->index }}][class_id]" 
                                                            class="form-select form-select-sm class-select" 
                                                            data-index="{{ $loop->index }}"
                                                            disabled>
                                                        <option value="">-- Pilih jika Tinggal Kelas --</option>
                                                        @foreach($grade12_classes as $class)
                                                            @if($class->major_id == $mapping['current_class']->major_id)
                                                                <option value="{{ $class->id }}" 
                                                                        {{ $mapping['current_class']->id == $class->id ? 'selected' : '' }}>
                                                                    {{ $class->name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="{{ route('admin.academic-years.transition.grade11-to-12') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan & Lanjut ke Finalisasi
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
    // Handle status change
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const index = this.dataset.index;
            const classSelect = document.querySelector(`.class-select[data-index="${index}"]`);
            
            if (this.value === 'Tinggal Kelas') {
                classSelect.disabled = false;
                classSelect.required = true;
            } else {
                classSelect.disabled = true;
                classSelect.required = false;
                classSelect.value = '';
            }
        });
    });

    // Form Validation
    document.getElementById('grade12Form')?.addEventListener('submit', function(e) {
        const statusSelects = document.querySelectorAll('.status-select');
        let hasError = false;
        
        statusSelects.forEach(select => {
            const index = select.dataset.index;
            const classSelect = document.querySelector(`.class-select[data-index="${index}"]`);
            
            if (select.value === 'Tinggal Kelas' && !classSelect.value) {
                hasError = true;
                classSelect.classList.add('is-invalid');
            } else {
                classSelect.classList.remove('is-invalid');
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Pastikan siswa yang Tinggal Kelas telah memiliki kelas tujuan');
        }
    });
});
</script>
@endpush
@endsection