<x-app-layout>
    @section('title', 'Edit Data Guru')

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-light border btn-sm mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bold tracking-tight text-dark mb-1">Edit Data Guru</h3>
            <p class="text-muted mb-0">Ubah profil guru dan akun loginnnya.</p>
        </div>
    </div>

    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Column 1: Akun Details -->
                    <div class="col-md-6 border-end pe-md-4">
                        <h5 class="fw-bold text-primary mb-3">Informasi Akun</h5>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="guru@sman1tajurhalang.sch.id" value="{{ old('email', $teacher->user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-8">Digunakan untuk masuk ke portal guru.</div>
                        </div>

                        <div class="mb-3">
                            <label for="tahun_masuk_kerja" class="form-label fw-semibold">Tahun Masuk Kerja</label>
                            <input type="number" name="tahun_masuk_kerja" id="tahun_masuk_kerja"
                                   class="form-control @error('tahun_masuk_kerja') is-invalid @enderror"
                                   placeholder="Contoh: 2015" min="1980" max="2100"
                                   value="{{ old('tahun_masuk_kerja', $teacher->tahun_masuk_kerja) }}">
                            @error('tahun_masuk_kerja')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-8">Tahun mulai mengajar di sekolah ini.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Ganti Password</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Biarkan kosong jika tidak ingin mengubah">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-8">Password default: <strong>NIP + Tahun Masuk Kerja</strong> (contoh: <code>1985010120100110012015</code>).</div>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label fw-semibold">Status Aktif</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $teacher->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fs-8" for="is_active">Aktif (bisa login dan mengajar)</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label fw-semibold">Foto Profil</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                @if($teacher->photo)
                                    <img src="{{ Storage::url($teacher->photo) }}" alt="" class="rounded-circle object-fit-cover" style="width: 50px; height: 50px;">
                                @endif
                                <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                            </div>
                            @error('photo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-8">Format JPG/PNG, Maksimal 2MB.</div>
                        </div>
                    </div>

                    <!-- Column 2: Profil Details -->
                    <div class="col-md-6 ps-md-4">
                        <h5 class="fw-bold text-primary mb-3">Profil Guru</h5>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Drs. Ahmad Fauzi, M.Pd" value="{{ old('name', $teacher->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nip" class="form-label fw-semibold">NIP</label>
                                <input type="text" name="nip" id="nip" class="form-control @error('nip') is-invalid @enderror" placeholder="Contoh: 198203..." value="{{ old('nip', $teacher->nip) }}">
                                @error('nip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nuptk" class="form-label fw-semibold">NUPTK</label>
                                <input type="text" name="nuptk" id="nuptk" class="form-control @error('nuptk') is-invalid @enderror" placeholder="Masukkan NUPTK" value="{{ old('nuptk', $teacher->nuptk) }}">
                                @error('nuptk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="gender" class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                                <div class="custom-select-wrapper" data-placeholder="Pilih Gender">
                                <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Pilih Gender</option>
                                    <option value="L" {{ old('gender', $teacher->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('gender', $teacher->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                </div>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">No. HP (WhatsApp) <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Contoh: 08123456789" value="{{ old('phone', $teacher->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Alamat Rumah</label>
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="Masukkan alamat lengkap...">{{ old('address', $teacher->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold d-block">Mata Pelajaran yang Diampu</label>
                            <div class="row g-2 overflow-auto @error('subjects') is-invalid @enderror" style="max-height: 120px;">
                                @php
                                    $assignedSubjects = $teacher->subjects->pluck('id')->toArray();
                                @endphp
                                @foreach($subjects as $subject)
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="subjects[]" value="{{ $subject->id }}" id="subject_{{ $subject->id }}" {{ in_array($subject->id, old('subjects', $assignedSubjects)) ? 'checked' : '' }}>
                                            <label class="form-check-label fs-8" for="subject_{{ $subject->id }}">
                                                {{ $subject->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('subjects')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 border-top pt-3">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
