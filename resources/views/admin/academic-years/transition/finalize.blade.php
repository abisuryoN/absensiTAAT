@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>Aktivasi Tahun Ajaran Baru
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Selamat!</strong> Semua proses kenaikan kelas dan kelulusan telah selesai.
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Ringkasan Proses</h6>
                        </div>
                        <div class="card-body">
                            @if(session('transition_results'))
                                <div class="row">
                                    @if(isset(session('transition_results')['success']))
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded bg-success-subtle">
                                                <h3 class="text-success mb-1">{{ count(session('transition_results')['success']) }}</h3>
                                                <p class="mb-0 small">Berhasil Diproses</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset(session('transition_results')['graduated']))
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded bg-primary-subtle">
                                                <h3 class="text-primary mb-1">{{ count(session('transition_results')['graduated']) }}</h3>
                                                <p class="mb-0 small">Siswa Lulus</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset(session('transition_results')['repeated']))
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded bg-warning-subtle">
                                                <h3 class="text-warning mb-1">{{ count(session('transition_results')['repeated']) }}</h3>
                                                <p class="mb-0 small">Tinggal Kelas</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset(session('transition_results')['failed']) && count(session('transition_results')['failed']) > 0)
                                        <div class="col-md-4 mt-3">
                                            <div class="text-center p-3 border rounded bg-danger-subtle">
                                                <h3 class="text-danger mb-1">{{ count(session('transition_results')['failed']) }}</h3>
                                                <p class="mb-0 small">Gagal Diproses</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Silakan selesaikan semua langkah transisi terlebih dahulu.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-warning-subtle">
                            <h6 class="mb-0 text-dark">
                                <i class="fas fa-exclamation-triangle me-2"></i>Perhatian
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Sebelum mengaktifkan tahun ajaran baru, pastikan:</strong></p>
                            <ul class="mb-0">
                                <li>Semua proses kenaikan kelas dan kelulusan telah selesai</li>
                                <li>Data siswa telah ter-update dengan benar</li>
                                <li>Kelas-kelas untuk tahun ajaran baru sudah dibuat</li>
                                <li>Semester baru telah dibuat dan dikonfigurasi</li>
                            </ul>
                            <hr>
                            <p class="mb-0 text-danger">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <strong>Penting:</strong> Setelah tahun ajaran baru diaktifkan, tahun ajaran saat ini akan dinonaktifkan dan sistem akan beralih ke tahun ajaran baru.
                            </p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Informasi Tahun Ajaran</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Tahun Ajaran Aktif Saat Ini:</h6>
                                    <p class="h5 text-primary">{{ $currentYear->name }}</p>
                                    <p class="small text-muted mb-0">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $currentYear->start_date->format('d M Y') }} - {{ $currentYear->end_date->format('d M Y') }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Tahun Ajaran Baru:</h6>
                                    <p class="h5 text-success">{{ $nextYear->name }}</p>
                                    <p class="small text-muted mb-0">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $nextYear->start_date->format('d M Y') }} - {{ $nextYear->end_date->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.academic-years.transition.activate-new-year') }}" onsubmit="return confirm('Apakah Anda yakin ingin mengaktifkan tahun ajaran baru? Tindakan ini akan menonaktifkan tahun ajaran saat ini.');">
                        @csrf
                        <input type="hidden" name="next_year_id" value="{{ $nextYear->id }}">
                        
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('admin.academic-years.transition.grade12-graduate') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-rocket me-2"></i>Aktifkan Tahun Ajaran Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection