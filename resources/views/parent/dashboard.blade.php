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
                flex: 0 0 calc(20% - .8rem);
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
        /* Carousel wrapper: arrow kiri - track - arrow kanan (tidak menumpuk card) */
        .selector-carousel {
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .carousel-arrow {
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            padding: 0;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            line-height: 1;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .08);
            transition: background .18s, color .18s, border-color .18s, transform .18s;
            -webkit-tap-highlight-color: transparent;
        }
        .carousel-arrow:hover  { background: #3b82f6; border-color: #3b82f6; color: #fff; }
        .carousel-arrow:active { transform: scale(.94); }
        .carousel-arrow:focus-visible { outline: 2px solid #3b82f6; outline-offset: 2px; }

        /* CENTER-PEEK: card aktif selalu di TENGAH, tetangga kiri/kanan
           terlihat SETENGAH.
           Rumus (gap = g): card = 50% - g  →  sisi kosong = (W-card)/2 = 25% + g/2
           padding track = 25% - g/2  →  tetangga terlihat = 25% + g/2 - g = card/2 */
        .selector-carousel {
            --sel-gap: .75rem;
        }
        .selector-scroll {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            gap: var(--sel-gap, .75rem);
            overflow-x: auto;
            padding-bottom: .5rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            scroll-snap-type: x mandatory;
            /* padding sisi dihitung JS (applyCenterPeek) supaya card tengah
               benar-benar exact; scroll-padding harus 0 agar snapport tidak
               menyempit dan menggeser titik snap. */
            scroll-padding: 0;
        }
        .selector-scroll::-webkit-scrollbar { display: none; }

        .student-selector-card {
            /* Lebar dihitung eksak oleh JS via --card-w (center-peek presisi);
               fallback 170px = tampilan semula */
            flex: 0 0 var(--card-w, 170px);
            width: var(--card-w, 170px);
            box-sizing: border-box;
            scroll-snap-align: center;
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
            .carousel-arrow        { width: 30px; height: 30px; font-size: .85rem; }
        }
        /* Desktop (≥992px = layout desktop): tombol < > DIHAPUS, semua card tampil
           seperti semula. Salinan cadangan (kiri/kanan) disembunyikan agar tidak
           muncul 3x. */
        @media (min-width: 992px) {
            .carousel-arrow { display: none !important; }
            .student-selector-card:not(.is-center-copy) { display: none !important; }
            .selector-scroll {
                padding-left: 0;
                padding-right: 0;
                scroll-snap-type: none;
                flex-wrap: wrap;
            }
            .student-selector-card {
                flex: 0 0 auto;
                width: 170px;
            }
        }
        /* Mobile: larang browser memakan gesture swipe carousel */
        .selector-scroll {
            touch-action: pan-y;
        }
    </style>
    @endpush

    {{-- ── Loading Spinner ───────────────────────────────────────────────── --}}
    <div id="dashboard-spinner" class="dashboard-spinner-box">
        <div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;" role="status">
            <span class="visually-hidden">Memuat...</span>
        </div>
    </div>

    {{-- ── Student Selector (outside #dashboard-content, NOT re-rendered) ──── --}}
    @if($children->count() > 1)
    <div class="selector-section">
        <div class="selector-label mb-2">
            <i class="bi bi-people me-1"></i>Pilih Anak
        </div>
        <div class="selector-carousel">
            <button type="button" class="carousel-arrow carousel-prev" aria-label="Anak sebelumnya">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="selector-scroll" id="student-selector">
            @php $childCount = $children->count(); @endphp
            @if($childCount > 1)
                {{-- INFINITE CAROUSEL: track di-triplicate (copy kiri, tengah, kanan).
                     Card aktif awal ada di copy TENGAH. Copy kiri/kanan membuat wrap
                     3→1 tetap bergeser ke KANAN dan 1→3 tetap ke KIRI (tanpa lompat
                     balik), karena JS memusatkan card aktif yang paling dekat tengah. --}}
                @for($copy = 0; $copy < 3; $copy++)
                    @foreach($children as $child)
                    <div class="student-selector-card {{ $copy === 1 && $activeStudent && $activeStudent->id === $child->id ? 'active' : '' }} {{ $copy === 1 ? 'is-center-copy' : '' }}"
                         data-student-id="{{ $child->id }}"
                         data-url="{{ route('parent.student.data', $child->id) }}"
                         role="button"
                         tabindex="{{ $copy === 1 ? '0' : '-1' }}"
                         aria-label="Pilih {{ $child->name }}">
                        <div class="selector-check"><i class="bi bi-check2"></i></div>
                        <div class="selector-avatar">
                            <img src="{{ $child->photo_url }}"
                                 alt="{{ $child->name }}"
                                 class="w-100 h-100 object-fit-cover"
                                 style="border-radius:10px;"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($child->name) }}&background=6366f1&color=fff&size=80&bold=true'">
                        </div>
                        <div class="selector-name">{{ $child->name }}</div>
                        <div class="selector-meta">
                            NIS {{ $child->nis ?? '-' }}<br>
                            {{ $child->displayClass()?->name ?? 'Kelas tidak tercatat' }}
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
                @endfor
            @else
                @foreach($children as $child)
                <div class="student-selector-card {{ $activeStudent && $activeStudent->id === $child->id ? 'active' : '' }} is-center-copy"
                     data-student-id="{{ $child->id }}"
                     data-url="{{ route('parent.student.data', $child->id) }}"
                     role="button"
                     tabindex="0"
                     aria-label="Pilih {{ $child->name }}">
                    <div class="selector-check"><i class="bi bi-check2"></i></div>
                    <div class="selector-avatar">
                        <img src="{{ $child->photo_url }}"
                             alt="{{ $child->name }}"
                             class="w-100 h-100 object-fit-cover"
                             style="border-radius:10px;"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($child->name) }}&background=6366f1&color=fff&size=80&bold=true'">
                    </div>
                    <div class="selector-name">{{ $child->name }}</div>
                    <div class="selector-meta">
                        NIS {{ $child->nis ?? '-' }}<br>
                        {{ $child->displayClass()?->name ?? 'Kelas tidak tercatat' }}
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
            @endif
            </div>
            <button type="button" class="carousel-arrow carousel-next" aria-label="Anak selanjutnya">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
    @endif

    {{-- ── Dashboard Content (replaced via AJAX) ──────────────────────────── --}}
    <div id="dashboard-content" class="dashboard-content-box">
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
    <!-- Chart.js CDN (loaded once) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
    (function () {
        'use strict';

        // Layout me-render konten DUA KALI (desktop d-none d-lg-flex + mobile d-lg-none).
        // Jadi setiap id muncul 2x di DOM. Semua helper di bawah bekerja pada
        // SEMUA instance, dan hanya instance yang terlihat yang dipakai untuk UI.
        const allSelectors  = Array.from(document.querySelectorAll('.selector-scroll'));
        const allContentBox = Array.from(document.querySelectorAll('.dashboard-content-box'));
        const allPrevBtns   = Array.from(document.querySelectorAll('.carousel-prev'));
        const allNextBtns   = Array.from(document.querySelectorAll('.carousel-next'));
        const spinners      = Array.from(document.querySelectorAll('.dashboard-spinner-box'));

        const hasSelector = allSelectors.length > 0;

        // Instance pertama (kompatibilitas kode lama)
        const selector   = allSelectors[0] || null;
        const contentBox = allContentBox[0] || null;

        // Global chart instance holder
        let attendanceChart = null;

        if (allContentBox.length === 0) return;

        // ── Chart Management ─────────────────────────────────────────────
        function initializeChart() {
            console.log('[Chart] Initializing chart...');

            // Find ALL canvases (both desktop and mobile layouts)
            const allCanvases = document.querySelectorAll('#attendanceTrendChart');
            console.log('[Chart] Found', allCanvases.length, 'canvas element(s)');

            // Find the VISIBLE canvas
            let ctx = null;
            allCanvases.forEach(function(canvas) {
                const rect = canvas.getBoundingClientRect();
                const isVisible = rect.width > 0 && rect.height > 0;
                console.log('[Chart] Canvas check - offsetWidth:', canvas.offsetWidth, 'offsetHeight:', canvas.offsetHeight, 'visible:', isVisible);
                if (isVisible && !ctx) {
                    ctx = canvas;
                }
            });

            if (!ctx) {
                console.warn('[Chart] No visible canvas found. Chart initialization skipped.');
                return;
            }

            console.log('[Chart] Using visible canvas with dimensions:', ctx.offsetWidth, 'x', ctx.offsetHeight);

            const dataEl = document.getElementById('chartTrendData');

            if (!dataEl) {
                console.warn('[Chart] Chart data element not found');
                return;
            }

            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('[Chart] Chart.js library not loaded');
                return;
            }

            // Destroy existing chart instance
            if (attendanceChart) {
                console.log('[Chart] Destroying previous chart instance');
                attendanceChart.destroy();
                attendanceChart = null;
            }

            try {
                const trendData = JSON.parse(dataEl.textContent);
                console.log('[Chart] Parsed data:', trendData);

                if (!Array.isArray(trendData) || trendData.length === 0) {
                    console.warn('[Chart] No data available for chart');
                    ctx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-info-circle me-2"></i>Belum ada data kehadiran</div>';
                    return;
                }

                const labels = trendData.map(function(m) { return m.month_short; });
                const hadirData = trendData.map(function(m) { return m.hadir; });
                const terlambatData = trendData.map(function(m) { return m.terlambat; });
                const tidakHadirData = trendData.map(function(m) { return m.tidak_hadir; });

                console.log('[Chart] Labels:', labels);
                console.log('[Chart] Creating chart with datasets:', {
                    hadir: hadirData,
                    terlambat: terlambatData,
                    tidakHadir: tidakHadirData
                });

                attendanceChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Hadir',
                                data: hadirData,
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Terlambat',
                                data: terlambatData,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Tidak Hadir',
                                data: tidakHadirData,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: { size: window.innerWidth < 768 ? 10 : 12 },
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                console.log('[Chart] Chart created successfully');
            } catch (err) {
                console.error('[Chart] Failed to initialize chart:', err);
                if (ctx && ctx.parentElement) {
                    ctx.parentElement.innerHTML = '<div class="text-center text-danger py-4"><i class="bi bi-exclamation-circle me-2"></i>Gagal memuat grafik: ' + err.message + '</div>';
                }
            }
        }

        // Initialize chart on page load with delay to ensure DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initializeChart, 100);
            });
        } else {
            setTimeout(initializeChart, 100);
        }

        // ── Helpers ──────────────────────────────────────────────────────
        function setLoading(on) {
            allContentBox.forEach(function(el) { el.classList.toggle('loading', on); });
            spinners.forEach(function(el) { el.classList.toggle('show', on); });
        }

        function setActiveCard(studentId) {
            allSelectors.forEach(function(sel) {
                sel.querySelectorAll('.student-selector-card').forEach(function(card) {
                    const isActive = String(card.dataset.studentId) === String(studentId);
                    card.classList.toggle('active', isActive);
                });
            });
        }

        // Anak yang datanya SEDANG ditampilkan (bukan sekadar class .active),
        // supaya navigasi carousel yang sudah men-set .active tetap memicu fetch.
        let displayedStudentId = (function() {
            if (!hasSelector) return null;
            const a = allSelectors[0].querySelector('.student-selector-card.is-center-copy.active')
                   || allSelectors[0].querySelector('.student-selector-card.active');
            return a ? String(a.dataset.studentId) : null;
        })();

        // ── Fetch and swap dashboard content ─────────────────────────────
        async function loadStudent(card) {
            const studentId = card.dataset.studentId;
            const url       = card.dataset.url;

            // Konten untuk anak ini sudah tampil → tidak perlu fetch ulang
            if (String(studentId) === String(displayedStudentId)) {
                setActiveCard(studentId);
                return;
            }

            // Simpan card aktif sebelumnya dari SEMUA selector (desktop + mobile)
            let previousActive = null;
            allSelectors.forEach(function(sel) {
                const a = sel.querySelector('.student-selector-card.active');
                if (a && !previousActive) previousActive = a;
            });

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

                // Layout me-render $slot dua kali (desktop + mobile): swap SEMUA copy
                allContentBox.forEach(function(el) { el.innerHTML = html; });

                displayedStudentId = String(studentId);

                // Pastikan card aktif terlihat di carousel mobile
                allSelectors.forEach(function(sel) {
                    if (isMobileCarousel()) scrollToActiveCardIn(sel);
                });

                console.log('[AJAX] Content swapped (x' + allContentBox.length + '), re-initializing chart...');

                // Re-initialize chart after content swap with proper delay
                setTimeout(function() {
                    initializeChart();
                }, 150);

                // JANGAN auto-scroll ke konten di mobile: carousel harus tetap
                // di bawah jari agar swipe/panah bisa dipakai berulang.
            } catch (err) {
                console.error('Gagal memuat data siswa:', err);
                // Restore active state on error
                allSelectors.forEach(function(sel) {
                    sel.querySelectorAll('.student-selector-card').forEach(function(c) {
                        if (String(c.dataset.studentId) === String(studentId)) c.classList.remove('active');
                    });
                });
                if (previousActive) previousActive.classList.add('active');

                // Show small error toast
                showToast('Gagal memuat data. Silakan coba lagi.');
            } finally {
                setLoading(false);
            }
        }

        // Instance dashboard-content yang sedang terlihat (mobile atau desktop)
        function visibleContentBox() {
            for (const el of allContentBox) {
                if (el.offsetParent !== null || el.getClientRects().length > 0) return el;
            }
            return allContentBox[0] || null;
        }

        // Expose to window for external navigation helpers
        window.parentDashboard = {
            loadStudentByUrl: function(url, studentId) {
                if (!hasSelector) return;
                for (const sel of allSelectors) {
                    const card = sel.querySelector('[data-student-id="' + studentId + '"]');
                    if (card) { loadStudent(card); return; }
                }
            }
        };

        // ── Event delegation untuk card click + keyboard (semua selector) ──
        if (hasSelector) {
            allSelectors.forEach(function(sel) {
                sel.addEventListener('click', function(e) {
                    const card = e.target.closest('.student-selector-card');
                    if (card) loadStudent(card);
                });
                sel.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        const card = e.target.closest('.student-selector-card');
                        if (card) { e.preventDefault(); loadStudent(card); }
                    }
                });
            });
        }

        // ── Carousel Navigation: < > memindahkan anak terpilih (infinite) ──
        // Track punya 3 salinan card (kiri/tengah/kanan). Kita selalu memusatkan
        // salinan yang paling dekat dengan tengah viewport, lalu menggeser track
        // ke KANAN untuk 'next' dan ke KIRI untuk 'prev'. Dengan salinan cadangan
        // di kedua sisi, wrap 3→1 tetap menggeser ke kanan (tidak lompat balik),
        // dan 1→3 tetap menggeser ke kiri.
        function getCards() {
            if (!hasSelector) return [];
            return Array.from(allSelectors[0].querySelectorAll('.student-selector-card'));
        }

        // Anak unik (urutan asli, tanpa duplikat salinan)
        function getUniqueIds() {
            const ids = [];
            getCards().forEach(function(c) {
                const id = String(c.dataset.studentId);
                if (!ids.includes(id)) ids.push(id);
            });
            return ids;
        }

        function currentActiveId() {
            const ids = getUniqueIds();
            if (ids.length === 0) return null;
            if (!hasSelector) return ids[0];
            const active = allSelectors[0].querySelector('.student-selector-card.active');
            return active ? String(active.dataset.studentId) : ids[0];
        }

        // Card aktif berikutnya secara logis (loop)
        function stepStudentId(currentId, direction) {
            const ids = getUniqueIds();
            if (ids.length === 0) return null;
            const cur = ids.indexOf(currentId);
            const idx = (cur < 0 ? 0 : cur + direction + ids.length) % ids.length;
            return ids[idx];
        }

        // ── Normalisasi track ────────────────────────────────────────────
        // Track berisi 3 salinan. Supaya bisa terus geser ke arah yang SAMA,
        // kartu aktif harus selalu berada di salinan TENGAH (index n..2n-1).
        // Setelah animasi geser selesai / setelah swipe, jika kartu aktif
        // sudah masuk ke salinan kiri atau kanan, kita pindahkan posisi
        // scroll satu "siklus" secara INSTAN (tanpa animasi) sehingga kartu
        // yang tampak tetap sama persis, tapi cadangan di kedua sisi kembali ada.
        function normalizeTrack(sel, animate) {
            const cards = Array.from(sel.querySelectorAll('.student-selector-card'));
            const n = getUniqueIds().length;
            if (n < 2 || cards.length < n * 3) return;

            // PENTING: pakai card aktif PERTAMA di salinan tengah (bukan sembarang
            // card .active — dengan triplicate ada 3 card .active ber-id sama).
            const activeId = currentActiveId();
            const A = cards.find(function(c, i) {
                return i >= n && i < 2 * n && String(c.dataset.studentId) === String(activeId);
            });
            if (!A) return;

            const padLeft = parseFloat(getComputedStyle(sel).paddingLeft) || 0;
            const gap = parseFloat(getComputedStyle(sel).columnGap) || 12;
            const step = A.offsetWidth + gap;          // jarak antar card (eksak)
            const center = sel.clientWidth / 2;
            const idx = cards.indexOf(A);
            if (idx < 0) return;

            // Kartu aktif harus di salinan tengah: index n-1 .. 2n-1 (0-based n..2n-1)
            let shift = 0;
            if (idx < n) {
                shift = n;                 // di salinan kiri → geser +1 siklus
            } else if (idx >= 2 * n) {
                shift = -n;                // di salinan kanan → geser -1 siklus
            }

            const targetScroll = A.offsetLeft - padLeft + A.offsetWidth / 2 - center + (shift * step);

            if (animate) {
                sel.scrollTo({ left: targetScroll, behavior: 'smooth' });
            } else {
                sel.scrollTo({ left: targetScroll, behavior: 'auto' });
            }
        }

        // Geser track satu card ke arah yang diminta, tanpa animasi ganda:
        // gunakan CSS scroll-behavior (smooth) milik track.
        function navigateCarousel(direction) {
            const sel = allSelectors.find(function(s) { return s.clientWidth > 0; }) || allSelectors[0];
            const cards = getCards();
            if (!sel || cards.length === 0) return;

            // Pastikan ada cadangan di kedua sisi SEBELUM geser, supaya wrap
            // (mis. 3→1 saat geser kanan) tidak mentok di ujung.
            normalizeTrack(sel, false);

            const A = sel.querySelector('.student-selector-card.active') || cards[0];
            const gap = parseFloat(getComputedStyle(sel).columnGap) || 12;
            const step = A.offsetWidth + gap;

            const start = sel.scrollLeft;
            const target = start + direction * step;

            sel.scrollTo({ left: target, behavior: 'smooth' });

            // Setelah animasi selesai: tentukan kartu paling dekat tengah,
            // jadikan aktif, lalu normalisasi (instan) agar siap geser lagi.
            clearTimeout(sel._navTimer);
            sel._navTimer = setTimeout(function() {
                const nearest = pickNearestCenterCard(sel);
                if (!nearest) return;

                const studentId = nearest.dataset.studentId;

                applyActiveById(studentId);
                normalizeTrack(sel, false);      // snap balik ke salinan tengah, tanpa lompatan visual

                // Fetch data anak (pakai salinan TENGAH agar konsisten)
                const centerCard = sel.querySelector('.student-selector-card.is-center-copy.active[data-url]')
                                || sel.querySelector('.student-selector-card.active[data-url]');
                if (centerCard) loadStudent(centerCard);
            }, 340);
        }

        // Card yang paling dekat dengan titik tengah track
        function pickNearestCenterCard(sel) {
            const cards = Array.from(sel.querySelectorAll('.student-selector-card'));
            if (cards.length === 0) return null;
            const trackRect = sel.getBoundingClientRect();
            const center = trackRect.x + trackRect.width / 2;
            let best = null, bestDist = Infinity;
            cards.forEach(function(c) {
                const r = c.getBoundingClientRect();
                const dist = Math.abs((r.x + r.width / 2) - center);
                if (dist < bestDist) { bestDist = dist; best = c; }
            });
            return best;
        }

        // Terapkan .active berdasarkan id (semua salinan) TANPA fetch
        function applyActiveById(studentId) {
            allSelectors.forEach(function(sel) {
                sel.querySelectorAll('.student-selector-card').forEach(function(card) {
                    card.classList.toggle('active', String(card.dataset.studentId) === String(studentId));
                });
            });
        }

        allPrevBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) { e.preventDefault(); navigateCarousel(-1); });
        });
        allNextBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) { e.preventDefault(); navigateCarousel(1); });
        });

        // ── Swipe kiri/kanan pada carousel (mobile) → pindah anak ────────
        if (hasSelector) {
            let touchStartX = 0, touchStartY = 0, touchTracking = false;

            allSelectors.forEach(function(sel) {
                sel.addEventListener('touchstart', function(e) {
                    const t = e.changedTouches[0];
                    touchStartX = t.clientX;
                    touchStartY = t.clientY;
                    touchTracking = true;
                }, { passive: true });

                sel.addEventListener('touchend', function(e) {
                    if (!touchTracking) return;
                    touchTracking = false;
                    const t = e.changedTouches[0];
                    const dx = t.clientX - touchStartX;
                    const dy = t.clientY - touchStartY;
                    // Swipe horizontal dominan & melewati threshold
                    if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.5) {
                        navigateCarousel(dx < 0 ? 1 : -1);
                    }
                }, { passive: true });
            });
        }

        // ── CENTER-PEEK: hitung lebar card & padding track secara eksak ────
        // W = lebar track terlihat, g = gap, C = lebar card
        //   C = W/2 - g           → tetangga terlihat setengah card
        //   P = (W - C)/2         → card aktif pas di tengah
        function applyCenterPeek(sel) {
            const W = sel.getBoundingClientRect().width;
            if (!W) return;

            const g = parseFloat(getComputedStyle(sel).columnGap) || 12;
            const C = W / 2 - g;          // lebar card
            if (C <= 0) return;
            const P = (W - C) / 2;        // padding kiri/kanan agar card center

            sel.style.setProperty('--card-w', C.toFixed(2) + 'px');
            sel.style.paddingLeft = P.toFixed(2) + 'px';
            sel.style.paddingRight = P.toFixed(2) + 'px';
        }

        function resetCenterPeek(sel) {
            sel.style.removeProperty('--card-w');
            sel.style.paddingLeft = '';
            sel.style.paddingRight = '';
        }

        // Scroll carousel agar card aktif EXACT di TENGAH track (center-peek).
        // Dengan triplicate, pilih card aktif di SALINAN TENGAH (.is-center-copy)
        // agar cadangan kiri/kanan tetap tersedia untuk geser berulang.
        function scrollToActiveCardIn(sel) {
            const card = sel.querySelector('.student-selector-card.is-center-copy.active')
                       || sel.querySelector('.student-selector-card.active');
            if (!card) return;

            const padLeft = parseFloat(getComputedStyle(sel).paddingLeft) || 0;

            // Posisi tengah card relatif terhadap isi track
            const cardCenter = card.offsetLeft - padLeft + card.offsetWidth / 2;

            // Target scrollLeft = cardCenter - (lebar terlihat / 2)
            let target = cardCenter - sel.clientWidth / 2;

            // Clamp ke rentang scroll yang valid
            const maxScroll = sel.scrollWidth - sel.clientWidth;
            target = Math.max(0, Math.min(target, maxScroll));

            sel.scrollTo({ left: target, behavior: 'smooth' });
        }

        // Center-peek hanya untuk layout mobile (desktop tampil semua card).
        function isMobileCarousel() {
            return window.matchMedia('(max-width: 991.98px)').matches;
        }

        // Terapkan / lepas center-peek sesuai breakpoint, lalu posisikan card
        function layoutCarousels() {
            const mobile = isMobileCarousel();
            allSelectors.forEach(function(sel) {
                if (mobile) {
                    applyCenterPeek(sel);
                    // set padding dulu, baru hitung posisi (butuh layout baru)
                    requestAnimationFrame(function() { scrollToActiveCardIn(sel); });
                } else {
                    resetCenterPeek(sel);
                    sel.scrollLeft = 0;
                }
            });
        }

        layoutCarousels();
        setTimeout(layoutCarousels, 150);

        // Reposisi saat resize / rotasi layar (mis. HP → tablet → desktop)
        let resizeTimer = null;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(layoutCarousels, 180);
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
            requestAnimationFrame(function() { toast.style.opacity = '1'; });
            setTimeout(function() {
                toast.style.opacity = '0';
                setTimeout(function() { toast.remove(); }, 300);
            }, 3000);
        }
    })();
    </script>
    @endpush

</x-app-layout>