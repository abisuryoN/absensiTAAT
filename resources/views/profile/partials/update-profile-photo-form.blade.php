<section id="profile-photo-section" class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center gap-2">
        <i class="bi bi-camera-fill text-primary"></i>
        <h2 class="fw-bold fs-6 mb-0">Foto Profil</h2>
    </div>

    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
            {{-- Preview Foto --}}
            <div class="text-center flex-shrink-0">
                <div class="position-relative d-inline-block">
                    <img id="photo-preview"
                         src="{{ auth()->user()->profile_photo_url }}"
                         alt="Foto Profil"
                         width="120" height="120"
                         class="rounded-circle object-fit-cover border border-3 border-white shadow"
                         style="background:#f1f5f9;"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff&size=128&bold=true'"
                    >
                    <label for="photo-input"
                           class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow"
                           style="width:32px;height:32px;cursor:pointer;font-size:0.85rem;"
                           title="Ganti Foto">
                        <i class="bi bi-camera-fill"></i>
                    </label>
                </div>
                <p class="text-muted fs-8 mt-2 mb-0">Maks. 1 MB</p>
            </div>

            {{-- Kontrol Upload --}}
            <div class="flex-grow-1 w-100">
                <p class="text-secondary mb-3" style="font-size:0.875rem;">
                    Upload foto profil Anda. Format yang diizinkan: <strong>JPG, JPEG, PNG, WEBP</strong>.
                    Foto akan dikonversi otomatis ke format <strong>WebP</strong> untuk mengoptimalkan ukuran file.
                </p>

                <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp" class="d-none">

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="btn-upload-photo" class="btn btn-primary btn-sm" disabled>
                        <i class="bi bi-cloud-upload me-1"></i> Upload Foto
                    </button>
                    <label for="photo-input" class="btn btn-outline-secondary btn-sm" style="cursor:pointer;">
                        <i class="bi bi-image me-1"></i> Pilih Foto
                    </label>
                    @if(auth()->user()->profile_photo)
                    <button type="button" id="btn-delete-photo" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i> Hapus Foto
                    </button>
                    @endif
                </div>

                <div id="photo-feedback" class="mt-2 d-none">
                    <small id="photo-feedback-text" class=""></small>
                </div>

                <div id="upload-progress" class="mt-2 d-none">
                    <div class="progress" style="height:4px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:100%"></div>
                    </div>
                    <small class="text-muted">Mengupload dan mengkonversi foto...</small>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    const photoInput   = document.getElementById('photo-input');
    const btnUpload    = document.getElementById('btn-upload-photo');
    const btnDelete    = document.getElementById('btn-delete-photo');
    const preview      = document.getElementById('photo-preview');
    const feedback     = document.getElementById('photo-feedback');
    const feedbackText = document.getElementById('photo-feedback-text');
    const progress     = document.getElementById('upload-progress');

    // URL-nya di-inline dari Blade
    const uploadUrl    = "{{ route('profile.photo.update') }}";
    const deleteUrl    = "{{ route('profile.photo.destroy') }}";
    const csrfToken    = "{{ csrf_token() }}";

    function showFeedback(message, isSuccess) {
        feedback.classList.remove('d-none');
        feedbackText.textContent = message;
        feedbackText.className = isSuccess ? 'text-success fw-semibold' : 'text-danger fw-semibold';
    }

    function hideFeedback() {
        feedback.classList.add('d-none');
    }

    function showProgress() { progress.classList.remove('d-none'); }
    function hideProgress() { progress.classList.add('d-none'); }

    // Saat file dipilih: tampilkan preview & aktifkan tombol upload
    photoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        // Validasi client-side
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            showFeedback('Format tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.', false);
            btnUpload.disabled = true;
            return;
        }
        if (file.size > 1024 * 1024) {
            showFeedback('Ukuran file melebihi 1 MB.', false);
            btnUpload.disabled = true;
            return;
        }

        hideFeedback();
        btnUpload.disabled = false;

        // Preview lokal
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; };
        reader.readAsDataURL(file);
    });

    // Upload foto
    btnUpload.addEventListener('click', async function () {
        const file = photoInput.files[0];
        if (!file) return;

        this.disabled = true;
        showProgress();
        hideFeedback();

        const form = new FormData();
        form.append('photo', file);
        form.append('_token', csrfToken);

        try {
            const res  = await fetch(uploadUrl, { method: 'POST', body: form });
            const data = await res.json();

            if (data.success) {
                // Update semua avatar di halaman
                document.querySelectorAll('.user-avatar-element').forEach(el => {
                    el.src = data.url;
                });
                preview.src = data.url;
                showFeedback(data.message, true);

                // Tampilkan tombol hapus jika belum ada
                if (!btnDelete) {
                    const btnArea = btnUpload.parentElement;
                    const del = document.createElement('button');
                    del.type = 'button';
                    del.id = 'btn-delete-photo';
                    del.className = 'btn btn-outline-danger btn-sm';
                    del.innerHTML = '<i class="bi bi-trash me-1"></i> Hapus Foto';
                    btnArea.appendChild(del);
                    attachDeleteHandler(del);
                }
            } else {
                showFeedback(data.message, false);
                // Kembalikan preview ke foto sebelumnya
                preview.src = "{{ auth()->user()->profile_photo_url }}";
            }
        } catch (err) {
            showFeedback('Terjadi kesalahan jaringan. Coba lagi.', false);
        } finally {
            hideProgress();
            this.disabled = false;
            photoInput.value = '';
            btnUpload.disabled = true;
        }
    });

    // Hapus foto
    function attachDeleteHandler(btn) {
        btn.addEventListener('click', async function () {
            if (!confirm('Hapus foto profil? Anda akan menggunakan avatar default.')) return;

            this.disabled = true;
            showProgress();
            hideFeedback();

            try {
                const res  = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();

                if (data.success) {
                    document.querySelectorAll('.user-avatar-element').forEach(el => {
                        el.src = data.url;
                    });
                    preview.src = data.url;
                    showFeedback(data.message, true);
                    this.remove(); // hapus tombol delete
                } else {
                    showFeedback(data.message, false);
                }
            } catch (err) {
                showFeedback('Terjadi kesalahan jaringan.', false);
            } finally {
                hideProgress();
            }
        });
    }

    if (btnDelete) attachDeleteHandler(btnDelete);
})();
</script>
