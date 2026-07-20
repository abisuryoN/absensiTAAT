@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Penjurusan & Kenaikan Kelas 10 → 11
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Tahun Ajaran Aktif:</strong> {{ $currentYear->name }}<br>
                        <strong>Tahun Ajaran Baru:</strong> {{ $nextYear->name }}<br>
                        <strong>Total Siswa Kelas 10:</strong> {{ $students->count() }} siswa
                    </div>

                    @if($students->count() === 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Tidak ada siswa kelas 10 yang perlu dipindahkan.
                        </div>
                        <a href="{{ route('admin.academic-years.transition.grade11-to-12') }}" class="btn btn-primary">
                            Lanjut ke Langkah 2 →
                        </a>
                    @else
                        <form id="grade10Form" method="POST" action="{{ route('admin.academic-years.transition.process-grade10-to-11') }}">
                            @csrf
                            <input type="hidden" name="next_year_id" value="{{ $nextYear->id }}">

                            <!-- Batch Assignment Controls -->
                            <div class="card mb-4 border-primary">
                                <div class="card-header bg-primary-subtle">
                                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Penempatan Massal</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Pilih Jurusan</label>
                                            <select id="batchMajor" class="form-select">
                                                <option value="">-- Pilih Jurusan --</option>
                                                @foreach($majors as $major)
                                                    <option value="{{ $major->id }}">{{ $major->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Pilih Kelas</label>
                                            <select id="batchClass" class="form-select" disabled>
                                                <option value="">-- Pilih kelas terlebih dahulu --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" id="applyBatch" class="btn btn-success w-100" disabled>
                                                <i class="fas fa-check me-2"></i>Terapkan ke Siswa Terpilih
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-muted small">
                                        <i class="fas fa-lightbulb me-1"></i>Centang siswa di tabel, pilih jurusan & kelas, lalu klik "Terapkan" untuk penempatan massal
                                    </div>
                                </div>
                            </div>

                            <!-- Students Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas Saat Ini</th>
                                            <th style="width: 200px;">Jurusan Pilihan</th>
                                            <th style="width: 200px;">Kelas Tujuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr data-student-id="{{ $student->id }}">
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input student-checkbox" value="{{ $student->id }}">
                                                </td>
                                                <td>{{ $student->nis }}</td>
                                                <td>{{ $student->name }}</td>
                                                <td>{{ $student->schoolClass->name }}</td>
                                                <td>
                                                    <select name="students[{{ $student->id }}][major_id]" 
                                                            class="form-select form-select-sm major-select" 
                                                            data-student-id="{{ $student->id }}"
                                                            required>
                                                        <option value="">-- Pilih --</option>
                                                        @foreach($majors as $major)
                                                            <option value="{{ $major->id }}">{{ $major->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="students[{{ $student->id }}][class_id]" 
                                                            class="form-select form-select-sm class-select" 
                                                            data-student-id="{{ $student->id }}"
                                                            disabled
                                                            required>
                                                        <option value="">-- Pilih jurusan dahulu --</option>
                                                    </select>
                                                    <input type="hidden" name="students[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="{{ route('admin.academic-years.transition.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Simpan & Lanjut ke Langkah 2
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
    const nextYearId = parseInt(document.querySelector('input[name="next_year_id"]').value);
    
    // Select All Checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Batch Major Selection
    document.getElementById('batchMajor')?.addEventListener('change', function() {
        const majorId = this.value;
        const batchClassSelect = document.getElementById('batchClass');
        const applyBtn = document.getElementById('applyBatch');
        
        if (majorId) {
            fetch(`/admin/academic-years/transition/get-grade11-classes?major_id=${majorId}&year_id=${nextYearId}`)
                .then(response => response.json())
                .then(data => {
                    batchClassSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                    data.classes.forEach(cls => {
                        batchClassSelect.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
                    });
                    batchClassSelect.disabled = false;
                });
        } else {
            batchClassSelect.innerHTML = '<option value="">-- Pilih jurusan terlebih dahulu --</option>';
            batchClassSelect.disabled = true;
            applyBtn.disabled = true;
        }
    });

    // Enable apply button when class is selected
    document.getElementById('batchClass')?.addEventListener('change', function() {
        document.getElementById('applyBatch').disabled = !this.value;
    });

    // Apply Batch Assignment
    document.getElementById('applyBatch')?.addEventListener('click', function() {
        const majorId = document.getElementById('batchMajor').value;
        const classId = document.getElementById('batchClass').value;
        const majorText = document.getElementById('batchMajor').selectedOptions[0].text;
        const classText = document.getElementById('batchClass').selectedOptions[0].text;
        
        const checkedStudents = document.querySelectorAll('.student-checkbox:checked');
        
        if (checkedStudents.length === 0) {
            alert('Pilih minimal satu siswa terlebih dahulu');
            return;
        }
        
        if (confirm(`Terapkan ${majorText} - ${classText} untuk ${checkedStudents.length} siswa terpilih?`)) {
            checkedStudents.forEach(checkbox => {
                const studentId = checkbox.value;
                const row = checkbox.closest('tr');
                const majorSelect = row.querySelector('.major-select');
                const classSelect = row.querySelector('.class-select');
                
                // Set major
                majorSelect.value = majorId;
                majorSelect.dispatchEvent(new Event('change'));
                
                // Set class after a small delay to ensure classes are loaded
                setTimeout(() => {
                    classSelect.value = classId;
                }, 100);
            });
            
            // Uncheck all
            document.getElementById('selectAll').checked = false;
            checkedStudents.forEach(cb => cb.checked = false);
        }
    });

    // Individual Major Selection
    document.querySelectorAll('.major-select').forEach(select => {
        select.addEventListener('change', function() {
            const studentId = this.dataset.studentId;
            const majorId = this.value;
            const classSelect = document.querySelector(`.class-select[data-student-id="${studentId}"]`);
            
            if (majorId) {
                fetch(`/admin/academic-years/transition/get-grade11-classes?major_id=${majorId}&year_id=${nextYearId}`)
                    .then(response => response.json())
                    .then(data => {
                        classSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                        data.classes.forEach(cls => {
                            classSelect.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
                        });
                        classSelect.disabled = false;
                    });
            } else {
                classSelect.innerHTML = '<option value="">-- Pilih jurusan dahulu --</option>';
                classSelect.disabled = true;
                classSelect.value = '';
            }
        });
    });

    // Form Validation
    document.getElementById('grade10Form')?.addEventListener('submit', function(e) {
        const majorSelects = document.querySelectorAll('.major-select');
        const classSelects = document.querySelectorAll('.class-select');
        let hasError = false;
        
        majorSelects.forEach(select => {
            if (!select.value) {
                hasError = true;
                select.classList.add('is-invalid');
            } else {
                select.classList.remove('is-invalid');
            }
        });
        
        classSelects.forEach(select => {
            if (!select.value) {
                hasError = true;
                select.classList.add('is-invalid');
            } else {
                select.classList.remove('is-invalid');
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Pastikan semua siswa telah memiliki jurusan dan kelas tujuan');
        }
    });
});
</script>
@endpush
@endsection