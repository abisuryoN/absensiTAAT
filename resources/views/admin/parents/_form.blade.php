{{-- Reusable form partial for parent create/edit --}}
@php $editing = isset($parent); @endphp

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    {{-- Nama Lengkap --}}
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $parent->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Hubungan --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">Hubungan</label>
        <input type="hidden" name="relationship" value="wali">
        <input type="text" class="form-control bg-light" value="Wali" readonly>
        @error('relationship') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    {{-- NIK --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">NIK <span class="text-danger">*</span></label>
        <input type="text" name="nik" class="form-control font-monospace @error('nik') is-invalid @enderror"
               value="{{ old('nik', $parent->nik ?? '') }}" maxlength="20" required
               placeholder="Nomor Induk Kependudukan (16 digit)">
        @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- No. HP Utama --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">No. HP Utama</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $parent->phone ?? '') }}" placeholder="08xx-xxxx-xxxx">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- No. HP Cadangan --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">No. HP Cadangan</label>
        <input type="text" name="phone_secondary" class="form-control @error('phone_secondary') is-invalid @enderror"
               value="{{ old('phone_secondary', $parent->phone_secondary ?? '') }}" placeholder="Opsional">
        @error('phone_secondary') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Status Aktif --}}
    <div class="col-md-6 d-flex align-items-end pb-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                   value="1" {{ old('is_active', $parent->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="is_active">Status Aktif</label>
        </div>
    </div>

    {{-- Alamat --}}
    <div class="col-12">
        <label class="form-label fw-semibold">Alamat</label>
        <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                  placeholder="Alamat lengkap (opsional)">{{ old('address', $parent->address ?? '') }}</textarea>
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Login Account Section --}}
<hr class="my-4">
<h6 class="fw-bold mb-3">
    <i class="bi bi-key me-2 text-primary"></i>
    Akun Portal Login (Opsional)
</h6>
<p class="text-muted small mb-3">
    Isi email untuk membuat akun login portal orang tua. Jika dikosongkan, orang tua tidak bisa login ke portal.
</p>

<div class="row g-3">
    {{-- Email --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Email Login</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $parent->email ?? '') }}"
               placeholder="email@contoh.com (opsional)">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Password --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Password
            @if($editing)
                <small class="text-muted fw-normal">(kosongkan jika tidak diubah)</small>
            @else
                <small class="text-muted fw-normal">(default: NIK jika dikosongkan)</small>
            @endif
        </label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="{{ $editing ? 'Kosongkan untuk tidak diubah' : 'Default: NIK' }}"
               autocomplete="new-password">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>