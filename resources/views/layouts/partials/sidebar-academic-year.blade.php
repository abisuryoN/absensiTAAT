@php
    use App\Models\AcademicYear;

    // Fetch all academic years newest first for the dropdown
    $allAcademicYears = AcademicYear::orderByDesc('start_date')->orderByDesc('name')->get();

    if (auth()->user()->hasRole('super_admin')) {
        // Super Admin: respect the session selection, fallback to the system-active year
        $selectedYearId = session('selected_academic_year_id');
        $currentAcademicYear = $selectedYearId
            ? $allAcademicYears->firstWhere('id', (int) $selectedYearId)
            : null;
        $currentAcademicYear ??= $allAcademicYears->firstWhere('is_active', true);
        $currentAcademicYear ??= $allAcademicYears->first();
    } else {
        // Other roles: always show the system-active academic year (read-only)
        $currentAcademicYear = $allAcademicYears->firstWhere('is_active', true)
            ?? $allAcademicYears->first();
    }
@endphp

@if($currentAcademicYear)
<div class="sidebar-tahun-ajaran sidebar-collapsible-content">

    @if(auth()->user()->hasRole('super_admin'))
        {{-- =============================================
             SUPER ADMIN: Interactive Dropdown
        ============================================== --}}
        <div class="dropdown">
            <button
                class="sidebar-ta-btn w-100 border-0 d-flex align-items-center justify-content-between"
                type="button"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                aria-expanded="false"
                title="Ganti Tahun Ajaran"
            >
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <i class="bi bi-calendar3 sidebar-ta-icon flex-shrink-0"></i>
                    <div class="sidebar-ta-text min-w-0">
                        <span class="sidebar-ta-label d-block">Tahun Ajaran</span>
                        <span class="sidebar-ta-value d-block text-truncate">{{ $currentAcademicYear->name }}</span>
                    </div>
                </div>
                <i class="bi bi-chevron-down sidebar-ta-chevron flex-shrink-0"></i>
            </button>

            <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-3 sidebar-ta-menu"
                style="min-width: 100%; max-height: 280px; overflow-y: auto;">
                <li class="px-3 pb-1 mb-1">
                    <span class="d-block" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.06em; color: #94a3b8; text-transform: uppercase;">
                        Pilih Tahun Ajaran
                    </span>
                </li>
                @forelse($allAcademicYears as $year)
                    <li>
                        <form method="POST" action="{{ route('admin.academic-year.switch') }}" class="m-0 p-0">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $year->id }}">
                            <button
                                type="submit"
                                class="dropdown-item d-flex align-items-center justify-content-between gap-2 px-3 py-2 rounded-2 sidebar-ta-option {{ $year->id === $currentAcademicYear->id ? 'active' : '' }}"
                            >
                                <span class="sidebar-ta-option-name">{{ $year->name }}</span>
                                @if($year->is_active)
                                    <span class="sidebar-ta-aktif-badge">Aktif</span>
                                @endif
                            </button>
                        </form>
                    </li>
                @empty
                    <li>
                        <span class="dropdown-item-text text-muted fs-7 px-3">Belum ada tahun ajaran</span>
                    </li>
                @endforelse
            </ul>
        </div>

    @else
        {{-- =============================================
             OTHER ROLES: Read-only static badge
        ============================================== --}}
        <div class="sidebar-ta-static d-flex align-items-center gap-2">
            <i class="bi bi-calendar3 sidebar-ta-icon flex-shrink-0"></i>
            <div class="sidebar-ta-text min-w-0">
                <span class="sidebar-ta-label d-block">Tahun Ajaran</span>
                <span class="sidebar-ta-value d-block text-truncate">{{ $currentAcademicYear->name }}</span>
            </div>
        </div>
    @endif

</div>
@endif

<style>
/* ================================================
   Sidebar: Tahun Ajaran Component
   ================================================ */

.sidebar-tahun-ajaran {
    padding: 0 0.75rem;
    margin-bottom: 0.25rem;
}

/* Shared trigger / static container */
.sidebar-ta-btn,
.sidebar-ta-static {
    background: rgba(99, 102, 241, 0.08);
    border-radius: 10px;
    padding: 0.5rem 0.65rem;
    transition: background 0.18s ease, box-shadow 0.18s ease;
    text-decoration: none;
    color: inherit;
}

.sidebar-ta-btn {
    cursor: pointer;
}
.sidebar-ta-btn:hover,
.sidebar-ta-btn:focus,
.sidebar-ta-btn[aria-expanded="true"] {
    background: rgba(99, 102, 241, 0.15);
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
    outline: none;
}

/* Icon */
.sidebar-ta-icon {
    font-size: 0.85rem;
    color: #6366f1;
}

/* Text labels */
.sidebar-ta-label {
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    color: #94a3b8;
    text-transform: uppercase;
    line-height: 1.2;
}

.sidebar-ta-value {
    font-size: 0.8rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.3;
    max-width: 150px;
}

/* Chevron icon for dropdown button */
.sidebar-ta-chevron {
    font-size: 0.7rem;
    color: #94a3b8;
    transition: transform 0.2s ease;
}
.sidebar-ta-btn[aria-expanded="true"] .sidebar-ta-chevron {
    transform: rotate(-180deg);
}

/* Dropdown menu */
.sidebar-ta-menu {
    background: #ffffff;
}

/* Dropdown options */
.sidebar-ta-option {
    font-size: 0.8rem;
    color: #334155;
    transition: background 0.12s, color 0.12s;
    border: none;
    width: 100%;
    text-align: left;
    background: transparent;
}
.sidebar-ta-option:hover {
    background: rgba(99, 102, 241, 0.08) !important;
    color: #4f46e5 !important;
}
.sidebar-ta-option.active {
    background: rgba(99, 102, 241, 0.12) !important;
    color: #4338ca !important;
    font-weight: 600;
}

/* "Aktif" badge inside dropdown */
.sidebar-ta-aktif-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 20px;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    background: rgba(22, 163, 74, 0.12);
    color: #16a34a;
    white-space: nowrap;
    flex-shrink: 0;
}

/* Option name text */
.sidebar-ta-option-name {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ---- Collapsed sidebar: hide text, keep icon ---- */
.sidebar.collapsed .sidebar-tahun-ajaran .sidebar-ta-text,
.sidebar.collapsed .sidebar-tahun-ajaran .sidebar-ta-chevron,
.sidebar.collapsed .sidebar-tahun-ajaran .sidebar-ta-label,
.sidebar.collapsed .sidebar-tahun-ajaran .sidebar-ta-value {
    display: none !important;
}

.sidebar.collapsed .sidebar-tahun-ajaran .sidebar-ta-btn,
.sidebar.collapsed .sidebar-tahun-ajaran .sidebar-ta-static {
    justify-content: center;
    padding: 0.5rem;
}

.sidebar.collapsed .sidebar-tahun-ajaran .sidebar-ta-icon {
    font-size: 1rem;
    margin: 0 auto;
}

/* ---- Mobile drawer: full-width with proper spacing ---- */
.drawer-tahun-ajaran-wrap .sidebar-tahun-ajaran {
    padding: 0 1rem;
    margin-bottom: 0;
}

.drawer-tahun-ajaran-wrap {
    border-bottom: 1px solid rgba(226, 232, 240, 0.6);
    padding-bottom: 0.75rem;
    margin-bottom: 0.25rem;
}

.drawer-tahun-ajaran-wrap .sidebar-ta-value {
    max-width: 100%;
}

/* Ensure dropdown menu in drawer doesn't overflow the drawer panel */
.drawer-tahun-ajaran-wrap .sidebar-ta-menu {
    position: fixed !important;
    left: 0 !important;
    right: 0 !important;
    width: auto !important;
    margin: 0 1rem !important;
    transform: none !important;
    top: auto !important;
}
</style>