@php
    $__parent = auth()->user()?->parent;
    $__hasChildren = $__parent && $__parent->students()->exists();
@endphp
<section>
    <header>
        <h2 class="fw-semibold fs-5 text-dark mb-2">
            Hapus Akun
        </h2>

        <p class="text-muted mb-4">
            Setelah akun Anda dihapus, semua data dan informasi akan dihapus secara permanen. Sebelum menghapus akun, silakan unduh data atau informasi yang ingin Anda simpan.
        </p>
    </header>

    @if($__hasChildren)
        <div class="alert alert-warning mb-0" role="alert">
            <i class="bi bi-lock-fill me-1"></i>
            Akun ini tidak dapat dihapus karena masih terhubung dengan data anak.
            Hubungi admin sekolah jika Anda memang perlu menghapus akun.
        </div>
    @else
    <button type="button" class="btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
        Hapus Akun
    </button>
    @endif

    @unless($__hasChildren)

    <!-- Delete Account Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold text-dark" id="confirmDeleteModalLabel">
                            Apakah Anda yakin ingin menghapus akun Anda?
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    
                    <div class="modal-body pt-2">
                        <p class="text-muted mb-4">
                            Setelah akun Anda dihapus, semua data dan informasi akan dihapus secara permanen. Masukkan kata sandi Anda untuk mengkonfirmasi bahwa Anda ingin menghapus akun secara permanen.
                        </p>

                        <div class="form-group mb-0">
                            <label for="password" class="visually-hidden">Kata Sandi</label>
                            <input id="password" name="password" type="password" class="form-control" placeholder="Kata Sandi" required>
                            @if ($errors->userDeletion->has('password'))
                                <div class="error-message">{{ $errors->userDeletion->first('password') }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">
                            Batal
                        </button>
                        <button type="submit" class="btn-delete">
                            Hapus Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->userDeletion->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                deleteModal.show();
            });
        </script>
    @endif
    @endunless
</section>
