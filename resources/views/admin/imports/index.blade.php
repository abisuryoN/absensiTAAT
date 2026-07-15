<x-app-layout>
    @section('title', 'Import Data Excel')

    <div class="row mb-4">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Import Data Excel</h3>
            <p class="text-muted mb-0">Impor data Master secara massal menggunakan file spreadsheet (.xlsx, .xls, .csv).</p>
        </div>
    </div>

    <div class="row">
        <!-- Import Form -->
        <div class="col-md-6">
            <div class="card glass-card border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload File
                    </h5>
                    
                    <form method="POST" action="{{ route('admin.imports.preview') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="type" class="form-label fw-semibold">Tipe Data Master <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">Pilih Tipe Data</option>
                                <option value="students">Data Siswa</option>
                                <option value="teachers">Data Guru</option>
                                <option value="classes">Data Kelas</option>
                                <option value="schedules">Data Jadwal Pelajaran</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="file" class="form-label fw-semibold">Pilih File Spreadsheet <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text fs-8 mt-1">Format file harus .xlsx, .xls, atau .csv. Maksimal 5MB.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="bi bi-eye me-1"></i> Preview & Validasi Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Formatting & Instructions -->
        <div class="col-md-6">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-info-circle me-1"></i> Panduan Format Kolom
                    </h5>
                    
                    <div class="accordion" id="formatAccordion">
                        <!-- Siswa -->
                        <div class="accordion-item bg-transparent">
                            <h2 class="accordion-header" id="headingSiswa">
                                <button class="accordion-button collapsed fw-semibold fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswa" aria-expanded="false" aria-controls="collapseSiswa">
                                    Format Data Siswa
                                </button>
                            </h2>
                            <div id="collapseSiswa" class="accordion-collapse collapse" aria-labelledby="headingSiswa" data-bs-parent="#formatAccordion">
                                <div class="accordion-body fs-8 text-muted">
                                    <p class="mb-1">Kolom yang dibutuhkan (baris pertama sebagai header):</p>
                                    <ul class="ps-3 mb-0">
                                        <li><code>nis</code> (Wajib, unik)</li>
                                        <li><code>nisn</code> (Opsional)</li>
                                        <li><code>name</code> (Wajib)</li>
                                        <li><code>email</code> (Wajib, unik)</li>
                                        <li><code>gender</code> (Wajib: L / P)</li>
                                        <li><code>phone</code> (Opsional)</li>
                                        <li><code>class_name</code> (Wajib, harus sesuai nama kelas terdaftar)</li>
                                        <li><code>parent_name</code> (Opsional)</li>
                                        <li><code>parent_phone</code> (Opsional)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Guru -->
                        <div class="accordion-item bg-transparent">
                            <h2 class="accordion-header" id="headingGuru">
                                <button class="accordion-button collapsed fw-semibold fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuru" aria-expanded="false" aria-controls="collapseGuru">
                                    Format Data Guru
                                </button>
                            </h2>
                            <div id="collapseGuru" class="accordion-collapse collapse" aria-labelledby="headingGuru" data-bs-parent="#formatAccordion">
                                <div class="accordion-body fs-8 text-muted">
                                    <p class="mb-1">Kolom yang dibutuhkan:</p>
                                    <ul class="ps-3 mb-0">
                                        <li><code>nip</code> (Opsional, unik)</li>
                                        <li><code>nuptk</code> (Opsional, unik)</li>
                                        <li><code>name</code> (Wajib)</li>
                                        <li><code>email</code> (Wajib, unik)</li>
                                        <li><code>gender</code> (Wajib: L / P)</li>
                                        <li><code>phone</code> (Opsional)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Kelas -->
                        <div class="accordion-item bg-transparent">
                            <h2 class="accordion-header" id="headingKelas">
                                <button class="accordion-button collapsed fw-semibold fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKelas" aria-expanded="false" aria-controls="collapseKelas">
                                    Format Data Kelas
                                </button>
                            </h2>
                            <div id="collapseKelas" class="accordion-collapse collapse" aria-labelledby="headingKelas" data-bs-parent="#formatAccordion">
                                <div class="accordion-body fs-8 text-muted">
                                    <p class="mb-1">Kolom yang dibutuhkan:</p>
                                    <ul class="ps-3 mb-0">
                                        <li><code>academic_year</code> (Wajib, harus sesuai tahun ajaran terdaftar cth: 2025/2026)</li>
                                        <li><code>major_code</code> (Wajib, harus sesuai kode jurusan terdaftar cth: IPA)</li>
                                        <li><code>grade_level</code> (Wajib: 10 / 11 / 12)</li>
                                        <li><code>name</code> (Wajib, nama kelas cth: XI IPA 1)</li>
                                        <li><code>capacity</code> (Wajib, kapasitas siswa cth: 36)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Jadwal -->
                        <div class="accordion-item bg-transparent">
                            <h2 class="accordion-header" id="headingJadwal">
                                <button class="accordion-button collapsed fw-semibold fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#collapseJadwal" aria-expanded="false" aria-controls="collapseJadwal">
                                    Format Jadwal Pelajaran
                                </button>
                            </h2>
                            <div id="collapseJadwal" class="accordion-collapse collapse" aria-labelledby="headingJadwal" data-bs-parent="#formatAccordion">
                                <div class="accordion-body fs-8 text-muted">
                                    <p class="mb-1">Kolom yang dibutuhkan:</p>
                                    <ul class="ps-3 mb-0">
                                        <li><code>teacher_email</code> (Wajib, harus email guru terdaftar)</li>
                                        <li><code>subject_code</code> (Wajib, harus kode mapel terdaftar)</li>
                                        <li><code>class_name</code> (Wajib, harus nama kelas terdaftar)</li>
                                        <li><code>day</code> (Wajib: Senin / Selasa / Rabu / Kamis / Jumat / Sabtu)</li>
                                        <li><code>start_time</code> (Wajib, format HH:MM cth: 07:00)</li>
                                        <li><code>end_time</code> (Wajib, format HH:MM cth: 08:30)</li>
                                        <li><code>room</code> (Opsional)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
