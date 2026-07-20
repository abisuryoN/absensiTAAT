<x-app-layout>
    @section('title', 'Log Aktivitas')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-journal-text me-2 text-primary"></i>Log Aktivitas
            </h3>
            <p class="text-muted mb-0">Riwayat aktivitas penting di seluruh sistem.</p>
        </div>
    </div>

    {{-- Filter Panel — higher z-index so custom dropdown floats above the table card --}}
    <div class="card glass-card border-0 mb-3" style="position: relative; z-index: 20; overflow: visible;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" id="filterForm">

                {{-- Baris 1: Tanggal + Tombol Hari Ini --}}
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-auto">
                        <label class="form-label fw-semibold small mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from"
                               class="form-control"
                               value="{{ request('date_from', $dateFrom->toDateString()) }}">
                    </div>

                    <div class="col-auto">
                        <label class="form-label fw-semibold small mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to"
                               class="form-control"
                               value="{{ request('date_to', $dateTo->toDateString()) }}">
                    </div>

                    <div class="col-auto">
                        {{-- spacer label to align button with inputs --}}
                        <label class="form-label d-block small mb-1">&nbsp;</label>
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="setToday()" title="Set ke Hari Ini">
                            <i class="bi bi-calendar-check me-1"></i>Hari Ini
                        </button>
                    </div>
                </div>

                {{-- Baris 2: Cari + Role + Modul + Tombol Filter/Reset --}}
                <div class="row g-2 align-items-end">

                    {{-- Search --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">Cari</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search"
                                   class="form-control border-start-0"
                                   placeholder="Nama pelaku atau kata kunci..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Role Pelaku --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">Role Pelaku</label>
                        <div class="custom-select-wrapper" data-placeholder="Semua Role" id="roleWrapper">
                            <select name="role" id="roleSelect">
                                <option value="">Semua Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                        {{ match($role) {
                                            'super_admin' => 'Super Admin',
                                            'guru'        => 'Guru',
                                            'siswa'       => 'Siswa',
                                            'parent'      => 'Orang Tua/Wali',
                                            default       => ucfirst($role),
                                        } }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Modul/Kategori --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small mb-1">Modul/Kategori</label>
                        <div class="custom-select-wrapper" data-placeholder="Semua Modul" id="moduleWrapper">
                            <select name="module" id="moduleSelect">
                                <option value="">Semua Modul</option>
                                @foreach($modules as $mod)
                                    <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>
                                        {{ $mod }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tombol Filter + Reset --}}
                    <div class="col-auto">
                        <label class="form-label d-block small mb-1">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-semibold px-4">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-circle me-1"></i>Reset
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Summary --}}
    <div class="text-muted small mb-2">
        Menampilkan <strong>{{ $logs->firstItem() ?? 0 }}</strong>–<strong>{{ $logs->lastItem() ?? 0 }}</strong>
        dari <strong>{{ $logs->total() }}</strong> entri
        &middot;
        Periode: {{ $dateFrom->format('d M Y') }} – {{ $dateTo->format('d M Y') }}
    </div>

    {{-- Table --}}
    <div class="card glass-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Waktu</th>
                            <th style="width: 180px;">Pelaku</th>
                            <th style="width: 115px;">Role</th>
                            <th>Aktivitas</th>
                            <th style="width: 160px;">Modul/Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                {{-- Waktu --}}
                                <td class="text-nowrap small">
                                    <span class="text-muted">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}
                                    </span><br>
                                    <span class="text-dark fw-semibold">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                                    </span>
                                </td>

                                {{-- Pelaku --}}
                                <td class="small">
                                    <span class="fw-semibold text-dark">
                                        {{ $log->causer_name ?? ($log->user?->name ?? '(sistem)') }}
                                    </span>
                                </td>

                                {{-- Role --}}
                                <td>
                                    @php
                                        [$roleLabel, $roleColor] = match($log->causer_role ?? '') {
                                            'super_admin' => ['Super Admin', 'danger'],
                                            'guru'        => ['Guru',        'primary'],
                                            'siswa'       => ['Siswa',       'success'],
                                            'parent'      => ['Orang Tua',   'warning'],
                                            default       => [ucfirst($log->causer_role ?? '-'), 'secondary'],
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $roleColor }}-subtle text-{{ $roleColor }} border border-{{ $roleColor }}-subtle small">
                                        {{ $roleLabel }}
                                    </span>
                                </td>

                                {{-- Aktivitas --}}
                                <td class="small">
                                    <span title="{{ $log->description }}">
                                        {{ Str::limit($log->description, 100) }}
                                    </span>
                                </td>

                                {{-- Modul --}}
                                <td>
                                    @if($log->module)
                                        <span class="badge bg-light text-dark border small">{{ $log->module }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-clock-history fs-2 d-block mb-2 opacity-50"></i>
                                    Tidak ada aktivitas untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        'use strict';

        var modulesUrl   = "{{ route('admin.activity-logs.modules-by-role') }}";
        var activeModule = "{{ request('module') }}";

        function rebuildModuleDropdown(modules) {
            var wrapper = document.getElementById('moduleWrapper');
            if (!wrapper) return;
            var existing = wrapper.querySelector('.custom-select');
            if (existing) existing.remove();
            var select = document.getElementById('moduleSelect');
            select.style.display = '';
            select.innerHTML = '<option value="">Semua Modul</option>';
            modules.forEach(function (mod) {
                var opt = document.createElement('option');
                opt.value       = mod;
                opt.textContent = mod;
                if (mod === activeModule) opt.selected = true;
                select.appendChild(opt);
            });
            if (window.CustomDropdown) {
                new window.CustomDropdown(wrapper, { placeholder: 'Semua Modul' });
            }
        }

        function onRoleChange(roleValue) {
            var url = modulesUrl + (roleValue ? '?role=' + encodeURIComponent(roleValue) : '');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.json(); })
                .then(function (modules) { rebuildModuleDropdown(modules); })
                .catch(function () {});
        }

        document.addEventListener('DOMContentLoaded', function () {
            var roleSelect = document.getElementById('roleSelect');
            if (roleSelect) {
                roleSelect.addEventListener('change', function () {
                    onRoleChange(this.value);
                });
                if (roleSelect.value) {
                    onRoleChange(roleSelect.value);
                }
            }
        });

        window.setToday = function () {
            var today = new Date().toISOString().slice(0, 10);
            document.querySelector('[name="date_from"]').value = today;
            document.querySelector('[name="date_to"]').value   = today;
            document.getElementById('filterForm').submit();
        };
    }());
    </script>
    @endpush

</x-app-layout>