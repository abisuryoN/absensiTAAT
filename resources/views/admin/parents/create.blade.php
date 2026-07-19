<x-app-layout>
    @section('title', 'Tambah Orang Tua / Wali')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <a href="{{ route('admin.parents.index') }}" class="text-muted text-decoration-none me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <span class="fw-bold fs-5">Tambah Orang Tua / Wali</span>
            <p class="text-muted mb-0 mt-1 small">Input data orang tua atau wali baru</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.parents.store') }}" method="POST">
                        @csrf
                        @include('admin.parents._form')
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-2">
                            <a href="{{ route('admin.parents.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>