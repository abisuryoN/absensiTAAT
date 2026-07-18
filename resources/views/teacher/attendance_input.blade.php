<x-app-layout>
    @section('title', 'Input Absensi Mapel')

    <div class="row g-4">
        <!-- Header & Date Filter -->
        <div class="col-12">
            <div class="card glass-card border-0 shadow-sm p-4">
                <div class="row g-3 align-items-center justify-content-between">
                    <div class="col-12 col-md-6">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold text-uppercase px-3 py-1 mb-2 fs-8">
                            Absensi Mata Pelajaran
                        </span>
                        <h4 class="fw-bold mb-1">{{ $schedule->subject->name }}</h4>
                        <p class="text-muted mb-0 fs-7">
                            Kelas: <strong>{{ $schedule->class->name }}</strong> &bull; Waktu: {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                        </p>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <form method="GET" action="{{ route('teacher.attendance.input', $schedule->id) }}">
                            <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Tanggal KBM</label>
                            <div class="input-group">
                                <input type="date"
                                       name="date"
                                       value="{{ $date }}"
                                       class="form-control form-control-sm border-end-0"
                                       max="{{ Carbon\Carbon::today()->format('Y-m-d') }}"
                                       onchange="this.form.submit()">
                                <span class="input-group-text bg-white border-start-0 text-muted">
                                    <i class="bi bi-calendar-event fs-8"></i>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Status Alert -->
        <div class="col-12">
            @if($attendance->status === 'submitted')
                <div class="alert alert-success d-flex align-items-center gap-2 mb-0" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>
                        <strong>Absensi telah dikirim!</strong> Telah disimpan secara permanen pada {{ $attendance->submitted_at->translatedFormat('d M Y H:i') }}. Anda masih dapat mengubah data ini jika diperlukan.
                    </div>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>
                        <strong>Absensi masih berupa Draf.</strong> Jangan lupa untuk mengklik tombol <strong>Kirim Absensi</strong> di bagian bawah halaman setelah mengisi data.
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Form -->
        <div class="col-12">
            <form method="POST" action="{{ route('teacher.attendance.store') }}">
                @csrf
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                <input type="hidden" name="status" id="attendance-status" value="draft">

                <div class="card glass-card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-premium align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase px-4" style="width: 5%;">No</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase" style="width: 30%;">Siswa</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase text-center" style="width: 40%;">Status Kehadiran</th>
                                        <th class="fs-8 fw-semibold text-muted text-uppercase px-4" style="width: 25%;">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach($attendance->details as $detail)
                                        @php
                                            $student = $detail->student;
                                        @endphp
                                        <tr>
                                            <td data-label="No" class="px-4 text-muted fs-7">{{ $no++ }}</td>
                                            <td data-label="Siswa">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if($student->photo)
                                                        <img src="{{ asset('storage/' . $student->photo) }}" alt="" width="36" height="36" class="rounded-circle object-fit-cover border">
                                                    @else
                                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 600; font-size: 0.8rem;">
                                                            {{ substr($student->name, 0, 2) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <span class="fw-semibold text-dark d-block fs-7">{{ $student->name }}</span>
                                                        <span class="text-muted fs-8">NIS: {{ $student->nis ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Status Kehadiran" class="text-center">
                                                <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="students[{{ $student->id }}][status]"
                                                           id="status_{{ $student->id }}_hadir"
                                                           value="hadir"
                                                           {{ $detail->status === 'hadir' ? 'checked' : '' }}>
                                                    <label class="btn btn-sm btn-outline-success fs-8 px-2.5 py-1" for="status_{{ $student->id }}_hadir">
                                                        Hadir
                                                    </label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="students[{{ $student->id }}][status]"
                                                           id="status_{{ $student->id }}_izin"
                                                           value="izin"
                                                           {{ $detail->status === 'izin' ? 'checked' : '' }}>
                                                    <label class="btn btn-sm btn-outline-info fs-8 px-2.5 py-1" for="status_{{ $student->id }}_izin">
                                                        Izin
                                                    </label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="students[{{ $student->id }}][status]"
                                                           id="status_{{ $student->id }}_sakit"
                                                           value="sakit"
                                                           {{ $detail->status === 'sakit' ? 'checked' : '' }}>
                                                    <label class="btn btn-sm btn-outline-primary fs-8 px-2.5 py-1" for="status_{{ $student->id }}_sakit">
                                                        Sakit
                                                    </label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="students[{{ $student->id }}][status]"
                                                           id="status_{{ $student->id }}_alpha"
                                                           value="alpha"
                                                           {{ $detail->status === 'alpha' ? 'checked' : '' }}>
                                                    <label class="btn btn-sm btn-outline-danger fs-8 px-2.5 py-1" for="status_{{ $student->id }}_alpha">
                                                        Alpha
                                                    </label>

                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="students[{{ $student->id }}][status]"
                                                           id="status_{{ $student->id }}_dispensasi"
                                                           value="dispensasi"
                                                           {{ $detail->status === 'dispensasi' ? 'checked' : '' }}>
                                                    <label class="btn btn-sm btn-outline-warning fs-8 px-2.5 py-1" for="status_{{ $student->id }}_dispensasi">
                                                        Disp.
                                                    </label>
                                                </div>
                                            </td>
                                            <td data-label="Catatan" class="px-4">
                                                <input type="text"
                                                       name="students[{{ $student->id }}][note]"
                                                       value="{{ $detail->note }}"
                                                       placeholder="Catatan..."
                                                       class="form-control form-control-sm border-light bg-light bg-opacity-50">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Footer Form Actions -->
                    <div class="card-footer bg-transparent border-0 p-4 d-flex justify-content-between flex-wrap gap-2">
                        <div class="flex-grow-1" style="max-width: 400px;">
                            <label class="form-label fs-8 fw-semibold text-muted text-uppercase mb-1">Catatan KBM / Jurnal</label>
                            <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Catatan materi atau kendala pembelajaran hari ini...">{{ $attendance->note }}</textarea>
                        </div>
                        <div class="d-flex align-items-end gap-2">
                            <button type="submit" onclick="setStatus('draft')" class="btn btn-outline-secondary btn-sm px-4">
                                <i class="bi bi-save me-1"></i>Simpan Draf
                            </button>
                            <button type="submit" onclick="setStatus('submitted')" class="btn btn-primary btn-sm px-4">
                                <i class="bi bi-send me-1"></i>Kirim Absensi
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function setStatus(status) {
            document.getElementById('attendance-status').value = status;
        }
    </script>
    @endpush
</x-app-layout>
