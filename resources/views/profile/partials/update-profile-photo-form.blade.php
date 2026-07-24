<section id="profile-photo-section" class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header border-bottom py-3 px-4 d-flex align-items-center gap-2" style="background:#fff;">
        <i class="bi bi-camera-fill text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:0.95rem;color:#1e293b;">Foto Profil</h2>
    </div>

    <div class="card-body p-4" style="background:#fff;">
        <div class="d-flex flex-column flex-md-row align-items-start gap-4">

            {{-- Preview Foto --}}
            <div class="flex-shrink-0 text-center">
                <div class="position-relative d-inline-block">
                    <img id="photo-preview"
                         src="{{ auth()->user()->profile_photo_url }}"
                         alt="Foto Profil"
                         width="110" height="110"
                         class="rounded-circle object-fit-cover shadow-sm"
                         style="border:3px solid #e2e8f0;background:#f8fafc;"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff&size=128&bold=true'"
                    >
                    {{-- Kamera overlay di pojok --}}
                    <label for="photo-input-trigger"
                           class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                           style="width:30px;height:30px;cursor:pointer;background:#3b82f6;border:2px solid #fff;"
                           title="Ganti Foto">
                        <i class="bi bi-camera-fill" style="font-size:0.75rem;color:#fff;"></i>
                    </label>
                </div>
            </div>

            {{-- Kontrol Upload --}}
            <div class="flex-grow-1">
                <p style="font-size:0.85rem;color:#475569;margin-bottom:12px;line-height:1.5;">
                    Upload foto profil Anda. Format yang diizinkan: <strong style="color:#1e293b;">JPG, JPEG, PNG, WEBP</strong>.
                    Foto akan dikonversi otomatis ke format <strong style="color:#1e293b;">WebP</strong>.
                </p>

                {{-- Input file tersembunyi — dipicu dari dua tempat (label kamera + tombol Pilih Foto) --}}
                <input type="file" id="photo-input-trigger" accept="image/jpeg,image/png,image/webp" class="d-none">

                <div class="d-flex flex-wrap align-items-center gap-2">
                    {{-- Tombol utama: klik → pilih file → langsung upload --}}
                    <label for="photo-input-trigger"
                           class="btn btn-primary btn-sm fw-semibold"
                           id="btn-pilih-label"
                           style="cursor:pointer;min-width:120px;">
                        <i class="bi bi-image me-1"></i>
                        <span id="label-text">Pilih & Upload Foto</span>
                    </label>

                    @if(auth()->user()->profile_photo)
                    <button type="button" id="btn-delete-photo"
                            class="btn btn-sm fw-semibold"
                            style="border:1px solid #fca5a5;color:#dc2626;background:#fff5f5;">
                        <i class="bi bi-trash me-1"></i> Hapus Foto
                    </button>
                    @endif
                </div>

                {{-- Info ukuran & format --}}
                <p style="font-size:0.78rem;color:#94a3b8;margin-top:8px;margin-bottom:0;">
                    <i class="bi bi-info-circle me-1"></i>Maksimal <strong style="color:#64748b;">1 MB</strong>
                </p>

                {{-- Progress bar --}}
                <div id="upload-progress" class="mt-3 d-none">
                    <div class="progress mb-1" style="height:4px;background:#e2e8f0;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:100%;background:#3b82f6;"></div>
                    </div>
                    <small style="color:#64748b;font-size:0.78rem;">Mengupload dan mengkonversi foto...</small>
                </div>

                {{-- Feedback pesan --}}
                <div id="photo-feedback" class="mt-2 d-none">
                    <small id="photo-feedback-text"></small>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    const input        = document.getElementById('photo-input-trigger');
    const btnLabel     = document.getElementById('btn-pilih-label');
    const labelText    = document.getElementById('label-text');
    const btnDelete    = document.getElementById('btn-delete-photo');
    const preview      = document.getElementById('photo-preview');
    const feedback     = document.getElementById('photo-feedback');
    const feedbackText = document.getElementById('photo-feedback-text');
    const progress     = document.getElementById('upload-progress');

    const uploadUrl = "{{ route('profile.photo.update') }}";
    const deleteUrl = "{{ route('profile.photo.destroy') }}";
    const csrfToken = "{{ csrf_token() }}";

    function showFeedback(msg, ok) {
        feedback.classList.remove('d-none');
        feedbackText.textContent = msg;
        feedbackText.style.cssText = ok
            ? 'color:#16a34a;font-weight:600;font-size:0.82rem;'
            : 'color:#dc2626;font-weight:600;font-size:0.82rem;';
    }
    function hideFeedback() { feedback.classList.add('d-none'); }
    function showProgress()  { progress.classList.remove('d-none'); }
    function hideProgress()  { progress.classList.add('d-none'); }
    function setLoading(on) {
        btnLabel.style.pointerEvents = on ? 'none' : '';
        btnLabel.style.opacity = on ? '0.65' : '';
        labelText.textContent = on ? 'Mengupload...' : 'Pilih & Upload Foto';
    }

    // Saat file dipilih → langsung upload
    input.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;

        hideFeedback();

        // Validasi client-side
        const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!allowed.includes(file.type)) {
            showFeedback('Format tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.', false);
            input.value = '';
            return;
        }
        if (file.size > 1024 * 1024) {
            showFeedback('Ukuran file melebihi 1 MB. Pilih foto yang lebih kecil.', false);
            input.value = '';
            return;
        }

        // Preview lokal dulu
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; };
        reader.readAsDataURL(file);

        // Langsung upload
        setLoading(true);
        showProgress();

        const form = new FormData();
        form.append('photo', file);
        form.append('_token', csrfToken);

        try {
            const res  = await fetch(uploadUrl, { method: 'POST', body: form });
            const data = await res.json();

            if (data.success) {
                // Update semua avatar di halaman
                document.querySelectorAll('.user-avatar-element').forEach(el => { el.src = data.url; });
                preview.src = data.url;
                showFeedback(data.message, true);

                // Tampilkan tombol hapus jika belum ada
                if (!document.getElementById('btn-delete-photo')) {
                    const del = document.createElement('button');
                    del.type = 'button';
                    del.id = 'btn-delete-photo';
                    del.className = 'btn btn-sm fw-semibold';
                    del.style.cssText = 'border:1px solid #fca5a5;color:#dc2626;background:#fff5f5;';
                    del.innerHTML = '<i class="bi bi-trash me-1"></i> Hapus Foto';
                    btnLabel.parentElement.appendChild(del);
                    attachDeleteHandler(del);
                }
            } else {
                preview.src = "{{ auth()->user()->profile_photo_url }}";
                showFeedback(data.message, false);
            }
        } catch {
            preview.src = "{{ auth()->user()->profile_photo_url }}";
            showFeedback('Terjadi kesalahan jaringan. Coba lagi.', false);
        } finally {
            hideProgress();
            setLoading(false);
            input.value = '';
        }
    });

    // Hapus foto
    function attachDeleteHandler(btn) {
        btn.addEventListener('click', async function () {
            if (!confirm('Hapus foto profil? Anda akan kembali menggunakan avatar default.')) return;

            this.disabled = true;
            showProgress();
            hideFeedback();

            try {
                const res  = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                const data = await res.json();

                if (data.success) {
                    document.querySelectorAll('.user-avatar-element').forEach(el => { el.src = data.url; });
                    preview.src = data.url;
                    showFeedback(data.message, true);
                    this.remove();
                } else {
                    showFeedback(data.message, false);
                    this.disabled = false;
                }
            } catch {
                showFeedback('Terjadi kesalahan jaringan.', false);
                this.disabled = false;
            } finally {
                hideProgress();
            }
        });
    }

    if (btnDelete) attachDeleteHandler(btnDelete);
})();
</script>
