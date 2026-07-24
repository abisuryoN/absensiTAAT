<x-app-layout>
    @section('title', 'Dashboard Orang Tua')

    @push('styles')
    <style>
        /* ── Stat Cards ───────────────────────────────── */
        .stat-card {
            border: none;
            border-radius: 14px;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0,0,0,.15) !important;
        }
        .stat-card .card-body {
            padding: 1.1rem 1rem;
        }
        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem;
            background: rgba(255,255,255,.22);
        }
        .stat-card .stat-value {
            font-size: 2rem; font-weight: 700; line-height: 1.1;
            color: #fff;
        }
        .stat-card .stat-label {
            font-size: .75rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .5px;
            color: rgba(255,255,255,.8);
            margin-top: 3px;
        }

        /* ── Student Profile Card ─────────────────────── */
        .student-profile-card {
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            border: none;
        }
        .student-avatar {
            width: 58px; height: 58px; border-radius: 14px;
            background: rgba(255,255,255,.22);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }

        /* ── Table Section ────────────────────────────── */
        .section-card {
            border-radius: 14px; border: none;
        }
        .section-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            border-radius: 14px 14px 0 0;
            padding: 1rem 1.25rem;
        }

        /* ── Stat Cards Grid ─────────────────────────── */
        .stat-cards-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-cards-grid .stat-card-col {
            flex: 0 0 calc(50% - .5rem);
            min-width: 0;
        }
        @media (min-width: 768px) {
            .stat-cards-grid .stat-card-col {
                flex: 0 0 calc(25% - .75rem);
            }
        }

        /* ── Student Selector ────────────────────────── */
        .selector-section {
            margin-bottom: 1.5rem;
        }
        .selector-label {
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            margin-bottom: .75rem;
        }
        .selector-scroll {
            display: flex;
            gap: .75rem;
            overflow-x: auto;
            padding-bottom: .5rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .selector-scroll::-webkit-scrollbar { display: none; }

        .student-selector-card {
            flex: 0 0 auto;
            width: 170px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            background: #fff;
            padding: .85rem .9rem;
            cursor: pointer;
            transition: border-color .18s, background .18s, box-shadow .18s, transform .18s;
            position: relative;
            text-decoration: none;
            display: block;
            user-select: none;
        }
        .student-selector-card:hover {
            border-color: #93c5fd;
            background: #f0f7ff;
            box-shadow: 0 4px 14px rgba(59,130,246,.12);
            transform: translateY(-2px);
        }
        .student-selector-card.active {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 4px 16px rgba(59,130,246,.2);
        }
        .student-selector-card.active .selector-name { color: #1d4ed8; }
        .student-selector-card .selector-avatar {
            width: 40px; height: 40px; border-radius: 10px;
            background: linear-gradient(135deg,#6366f1,#4f46e5);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 1rem;
            margin-bottom: .6rem;
        }
        .student-selector-card.active .selector-avatar {
            background: linear-gradient(135deg,#3b82f6,#1d4ed8);
        }
        .selector-name {
            font-weight: 600; font-size: .88rem;
            color: #1e293b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            margin-bottom: .2rem;
        }
        .selector-meta {
            font-size: .73rem; color: #64748b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .selector-check {
            position: absolute; top: .55rem; right: .6rem;
            width: 20px; height: 20px; border-radius: 50%;
            background: #3b82f6;
            display: flex; align-items: center; justify-content: center;
            font-size: .65rem; color: #fff;
            opacity: 0; transition: opacity .18s;
        }
        .student-selector-card.active .selector-check { opacity: 1; }

        /* ── Loading overlay on dashboard-content ────── */
        #dashboard-content {
            position: relative;
            transition: opacity .2s ease;
        }
        #dashboard-content.loading {
            opacity: .45;
            pointer-events: none;
        }
        #dashboard-spinner {
            display: none;
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        #dashboard-spinner.show { display: block; }

        /* Mobile tweaks */
        @media (max-width: 575.98px) {
            .stat-card .stat-value { font-size: 1.6rem; }
            .stat-card .card-body  { padding: 1rem !important; }
            .stat-card .stat-icon  { width: 42px; height: 42px; font-size: 1.15rem; margin-bottom: .5rem !important; }
            .student-avatar        { width: 48px; height: 48px; font-size: 1.2rem; }
            .student-selector-card { width: 150px; }
        }
    </style>
    @endpush

    {{-- ── Loading Spinner ───────────────────────────────────────────────── --}}
    <div id="dashboard-spinner">
        <div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;" role="status">
            <span class="visually-hidden">Memuat...</span>
        </div>
    </div>

    {{-- ── Student Selector (outside #dashboard-content, NOT re-rendered) ──── --}}
    @if($children->count() > 1)
    <div class="selector-section">
        <div class="selector-label">
            <i class="bi bi-people me-1"></i>Pilih Anak
        </div>
        <div class="selector-scroll" id="student-selector">
            @foreach($children as $child)
            <div class="student-selector-card {{ $activeStudent && $activeStudent->id === $child->id ? 'active' : '' }}"
                 data-student-id="{{ $child->id }}"
                 data-url="{{ route('parent.student.data', $child->id) }}"
                 role="button"
                 tabindex="0"
                 aria-label="Pilih {{ $child->name }}">
                <div class="selector-check"><i class="bi bi-check2"></i></div>
                <div class="selector-avatar">
                    @if($child->photo)
                        <img src="{{ Storage::url($child->photo) }}" alt="" class="w-100 h-100 object-fit-cover" style="border-radius:10px;">
                    @else
                        {{ strtoupper(substr($child->name, 0, 1)) }}
                    @endif
                </div>
                <div class="selector-name">{{ $child->name }}</div>
                <div class="selector-meta">
                    NIS {{ $child->nis ?? '-' }}<br>
                    {{ $child->schoolClass->name ?? '-' }}
                    <div class="mt-1">
                        @if($child->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem; padding: 2px 6px;">Aktif</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.68rem; padding: 2px 6px;">Nonaktif</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Dashboard Content (replaced via AJAX) ──────────────────────────── --}}
    <div id="dashboard-content">
        @if($activeStudent)
            @include('parent.partials.dashboard-content', [
                'activeStudent'     => $activeStudent,
                'summary'           => $summary,
                'todayRecord'       => $todayRecord,
                'recentAttendances' => $recentAttendances,
            ])
        @else
        <div class="card section-card shadow-sm">
            <div class="card-body text-center py-5">
                <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);display:flex;align-items:center;justify-content:center;margin:0 auto 1.2rem;">
                    <i class="bi bi-people fs-2" style="color:#6366f1;"></i>
                </div>
                <h5 class="fw-bold mb-2">Belum Ada Siswa Terhubung</h5>
                <p class="text-muted mb-0">Akun Anda belum ditautkan ke data siswa manapun.<br>
                Hubungi admin sekolah untuk menautkan akun Anda ke data anak Anda.</p>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
    (function () {
        'use strict';

        const selector   = document.getElementById('student-selector');
        const contentBox = document.getElementById('dashboard-content');
        const spinner    = document.getElementById('dashboard-spinner');

        if (!selector || !contentBox) return;

        // ── Helpers ──────────────────────────────────────────────────────
        function setLoading(on) {
            contentBox.classList.toggle('loading', on);
            spinner.classList.toggle('show', on);
        }

        function setActiveCard(studentId) {
            selector.querySelectorAll('.student-selector-card').forEach(card => {
                const isActive = String(card.dataset.studentId) === String(studentId);
                card.classList.toggle('active', isActive);
            });
        }

        // ── Fetch and swap dashboard content ─────────────────────────────
        async function loadStudent(card) {
            const studentId = card.dataset.studentId;
            const url       = card.dataset.url;

            if (card.classList.contains('active')) return; // already selected

            setLoading(true);
            setActiveCard(studentId);

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    }
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const html = await response.text();
                contentBox.innerHTML = html;

                // Smooth scroll to dashboard content on mobile
                if (window.innerWidth < 768) {
                    contentBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } catch (err) {
                console.error('Gagal memuat data siswa:', err);
                // Restore active state on error
                const prevActive = selector.querySelector('.student-selector-card.active');
                if (prevActive) prevActive.classList.add('active');
                card.classList.remove('active');

                // Show small error toast
                showToast('Gagal memuat data. Silakan coba lagi.');
            } finally {
                setLoading(false);
            }
        }

        // ── Event listeners (click + keyboard Enter/Space) ────────────────
        selector.querySelectorAll('.student-selector-card').forEach(card => {
            card.addEventListener('click', () => loadStudent(card));
            card.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    loadStudent(card);
                }
            });
        });

        // ── Simple toast notification ─────────────────────────────────────
        function showToast(message) {
            const toast = document.createElement('div');
            toast.style.cssText = [
                'position:fixed', 'bottom:1.5rem', 'left:50%',
                'transform:translateX(-50%)',
                'background:#1e293b', 'color:#fff',
                'padding:.55rem 1.2rem', 'border-radius:8px',
                'font-size:.85rem', 'z-index:10000',
                'box-shadow:0 4px 16px rgba(0,0,0,.2)',
                'opacity:0', 'transition:opacity .25s',
            ].join(';');
            toast.textContent = message;
            document.body.appendChild(toast);
            requestAnimationFrame(() => { toast.style.opacity = '1'; });
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    })();
    </script>
    @endpush

</x-app-layout>