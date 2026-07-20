@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-repeat me-2"></i>Mulai Tahun Ajaran Baru
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Info tahun ajaran --}}
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Tahun Ajaran Aktif:</strong> {{ $currentYear->name }}<br>
                        <strong>Tahun Ajaran Baru:</strong> {{ $nextYear->name }}
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Penting:</strong> Pastikan Anda telah membuat kelas-kelas untuk tahun ajaran baru 
                        ({{ $nextYear->name }}) sebelum memulai proses ini.
                        <a href="{{ route('admin.classes.create') }}" class="alert-link ms-1">Tambah kelas baru →</a>
                    </div>

                    {{-- Langkah-langkah proses --}}
                    <h6 class="mb-3 mt-4">Proses Kenaikan Kelas & Kelulusan</h6>
                    <p class="text-muted">Ikuti langkah-langkah berikut secara berurutan untuk menyelesaikan proses transisi:</p>

                    <div class="list-group mt-3">
                        {{-- Langkah 1: Kelas 10 → 11 --}}
                        <a href="{{ route('admin.academic-years.transition.grade10-to-11') }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <span class="badge bg-primary me-2">1</span>
                                        Penjurusan & Kenaikan Kelas 10 → 11
                                    </h6>
                                    <p class="mb-1 text-muted small">
                                        Tentukan jurusan dan kelas untuk siswa yang naik dari kelas 10 ke kelas 11.
                                        Proses ini memerlukan assignment manual karena siswa belum memiliki jurusan.
                                    </p>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>

                        {{-- Langkah 2: Kelas 11 → 12 --}}
                        <a href="{{ route('admin.academic-years.transition.grade11-to-12') }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <span class="badge bg-success me-2">2</span>
                                        Kenaikan Kelas 11 → 12
                                    </h6>
                                    <p class="mb-1 text-muted small">
                                        Review dan konfirmasi kenaikan kelas siswa kelas 11. 
                                        Sistem melakukan auto-mapping berdasarkan jurusan yang sama.
                                    </p>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>

                        {{-- Langkah 3: Kelulusan Kelas 12 --}}
                        <a href="{{ route('admin.academic-years.transition.grade12-graduate') }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <span class="badge bg-warning text-dark me-2">3</span>
                                        Kelulusan Kelas 12
                                    </h6>
                                    <p class="mb-1 text-muted small">
                                        Tentukan status kelulusan siswa kelas 12 (Lulus atau Tinggal Kelas).
                                    </p>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>

                        {{-- Langkah 4: Aktivasi --}}
                        <a href="{{ route('admin.academic-years.transition.finalize') }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <span class="badge bg-info me-2">4</span>
                                        Aktivasi Tahun Ajaran Baru
                                    </h6>
                                    <p class="mb-1 text-muted small">
                                        Finalisasi dan aktifkan tahun ajaran {{ $nextYear->name }}.
                                    </p>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection