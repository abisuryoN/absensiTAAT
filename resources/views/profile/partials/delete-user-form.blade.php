@php
    $__parent = auth()->user()?->parent;
    $__hasChildren = $__parent && $__parent->students()->exists();
@endphp
<section>
    <header>
        <h2 class="fw-semibold fs-5 text-dark mb-2">
            {{ __('Delete Account') }}
        </h2>

        <p class="text-muted mb-4">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
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
        {{ __('Delete Account') }}
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
                            {{ __('Are you sure you want to delete your account?') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body pt-2">
                        <p class="text-muted mb-4">
                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>

                        <div class="form-group mb-0">
                            <label for="password" class="visually-hidden">{{ __('Password') }}</label>
                            <input id="password" name="password" type="password" class="form-control" placeholder="{{ __('Password') }}" required>
                            @if ($errors->userDeletion->has('password'))
                                <div class="error-message">{{ $errors->userDeletion->first('password') }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn-delete">
                            {{ __('Delete Account') }}
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
