<x-app-layout>
    @section('title', 'Log Aktivitas')

    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">
                <i class="bi bi-journal-text me-2 text-primary"></i>Log Aktivitas
            </h3>
            <p class="text-muted mb-0">Riwayat aktivitas penting di seluruh sistem.</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card glass-card border-0 mb-3" style="position: relative; z-index: 20;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" id="filterForm">

                {{-- Row 1: Tanggal --}}
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-auto">
                        <label class="form-label fw-semibold small mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="{{ request('date_from', $dateFrom->toDateString()) }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label fw-semibold small mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="{{ request('date_to', $dateTo->toDateString()) }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label fw-semibold small mb-1 d-block">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="setToday()">
                            <i class="bi bi-calendar-check me-1"></i>Hari Ini
                        </button>
                    </div>
                </div>

                {{-- Row 2: Search + Filters --}}
                <div class="row g-2 align-items-end">

                    {{-- Search --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small mb-1">Cari</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
                                         color: #9ca3af; z-index: 5; pointer-events: none; font-size: 0.875rem;">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   style="padding-left: 2rem;"
                                   placeholder="Nama pelaku atau kata kunci..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Role --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">Role Pelaku</label>
                        <div class="custom-select-wrapper" data-placeholder="Semua Role" id="roleWrapper">
                            <select name="role" id="roleSelect">
                                <option value="">Semua Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}"
                                        {{ request('role') == $role ? 'selected' : '' }}>
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

                    {{-- Filter Modul --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small mb-1">Modul/Kategori</label>
                        <div class="custom-select-wrapper" data-placeholder="Semua Modul" id="moduleWrapper">
                            <select name="module" id="moduleSelect">
                                <option value="">Semua Modul</option>
                                @foreach($modules as $mod)
                                    <option value="{{ $mod }}"
                                        {{ request('module') == $mod ? 'selected' : '' }}>
                                        {{ $mod }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="col-auto">
                        <label class="form-label fw-semibold small mb-1 d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card glass-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 155px;">Waktu</th>
                            <th>Pelaku</th>
                            <th style="width: 110px;">Role</th>
                            <th>Aktivitas</th>
                            <th style="width: 160px;">Modul/Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                {{-- Waktu --}}
                                <td class="text-nowrap small text-muted">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}<br>
                                    <span class="text-dark fw-semibold">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                                    </span>
                                </td>

                                {{-- Pelaku --}}
                                <td>
                                    <div class="fw-semibold text-dark small">
                                        {{ $log->causer_name ?? ($log->user?->name ?? '-') }}
                                    </div>
                                </td>

                                {{-- Role --}}
                                <td>
                                    @php
                                        $roleLabel = match($log->causer_role ?? '') {
                                            'super_admin' => ['Super Admin', 'danger'],
                                            'guru'        => ['Guru',        'primary'],
                                            'siswa'       => ['Siswa',       'success'],
                                            'parent'      => ['Orang Tua',   'warning'],
                                            default       => [ucfirst($log->causer_role ?? '-'), 'secondary'],
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $roleLabel[1] }}-subtle text-{{ $roleLabel[1] }} border border-{{ $roleLabel[1] }}-subtle small">
                                        {{ $roleLabel[0] }}
                                    </span>
                                </td>

                                {{-- Aktivitas --}}
                                <td class="small">{{ $log->description }}</td>

                                {{-- Modul --}}
                                <td>
                                    @if($log->module)
                                        <span class="badge bg-light text-dark border small">
                                            {{ $log->module }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                    Tidak ada aktivitas yang tercatat untuk filter ini.
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

    {{-- Footer info --}}
    <div class="mt-2 text-muted small">
        Menampilkan {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}
        dari {{ $logs->total() }} entri
        &nbsp;·&nbsp;
        Periode: {{ $dateFrom->format('d M Y') }} – {{ $dateTo->format('d M Y') }}
    </div>

    @push('scripts')
    <script>
    (function () {
        'use strict';

        const modulesUrl = "{{ route('admin.activity-logs.modules-by-role') }}";
        const currentModule = "{{ request('module') }}";

        // ── Rebuild the Module custom-dropdown after option changes ──────────
        function rebuildModuleDropdown(modules) {
            const wrapper = document.getElementById('moduleWrapper');
            if (!wrapper) return;

            // 1. Remove existing custom UI
            const existing = wrapper.querySelector('.custom-select');
            if (existing) existing.remove();

            // 2. Show native select so CustomDropdown can read it
            const select = document.getElementById('moduleSelect');
            select.style.display = '';

            // 3. Rebuild options
            select.innerHTML = '<option value="">Semua Modul</option>';
            modules.forEach(function (mod) {
                const opt = document.createElement('option');
                opt.value = mod;
                opt.textContent = mod;
                if (mod === currentModule) opt.selected = true;
                select.appendChild(opt);
            });

            // 4. Re-initialise CustomDropdown
            if (window.CustomDropdown) {
                new window.CustomDropdown(wrapper, { placeholder: 'Semua Modul' });
            }
        }

        // ── Fetch modules when Role selection changes ─────────────────────────
        function onRoleChange(roleValue) {
            const url = modulesUrl + (roleValue ? '?role=' + encodeURIComponent(roleValue) : '');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.json(); })
                .then(function (modules) { rebuildModuleDropdown(modules); })
                .catch(function () { /* silently ignore */ });
        }

        // ── Wire up the native change event on the Role <select> ─────────────
        // CustomDropdown dispatches a native 'change' event on the underlying
        // <select> after the user picks an option, so this handler fires for
        // both direct and custom-dropdown interactions.
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('roleSelect');
            if (roleSelect) {
                roleSelect.addEventListener('change', function () {
                    onRoleChange(this.value);
                });

                // If a role is already selected (e.g. back/forward nav), load
                // the matching modules immediately without changing the selection.
                if (roleSelect.value) {
                    onRoleChange(roleSelect.value);
                }
            }
        });

        // ── "Hari Ini" shortcut button ────────────────────────────────────────
        window.setToday = function () {
            const today = new Date().toISOString().slice(0, 10);
            document.querySelector('[name="date_from"]').value = today;
            document.querySelector('[name="date_to"]').value   = today;
            document.getElementById('filterForm').submit();
        };
    }());
    </script>
    @endpush

</x-app-layout>