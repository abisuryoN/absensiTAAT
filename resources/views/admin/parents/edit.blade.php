<x-app-layout>
    @section('title', 'Edit Orang Tua / Wali')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <a href="{{ route('admin.parents.index') }}" class="text-muted text-decoration-none me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <span class="fw-bold fs-5">Edit Orang Tua / Wali</span>
            <p class="text-muted mb-0 mt-1 small">Ubah data: {{ $parent->name }}</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.parents.update', $parent) }}" method="POST">
                        @csrf @method('PUT')
                        @include('admin.parents._form')
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-2">
                            <a href="{{ route('admin.parents.show', $parent) }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>