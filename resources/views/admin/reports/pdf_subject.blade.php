<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Mata Pelajaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9.5pt;
            color: #333;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header-logo {
            width: 70px;
            text-align: left;
        }
        .header-text {
            text-align: center;
        }
        .header-text h2 {
            margin: 0;
            font-size: 15pt;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            color: #555;
        }
        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        .meta-table td {
            padding: 1.5px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8.5pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #ccc;
            padding: 6px 5px;
            text-align: left;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-badge {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        .status-hadir { color: #155724; }
        .status-izin { color: #0c5460; }
        .status-sakit { color: #004085; }
        .status-alpha { color: #721c24; }
        .status-dispensasi { color: #856404; }
        .footer-table {
            width: 100%;
            margin-top: 20px;
            font-size: 9pt;
        }
        .footer-sign {
            text-align: right;
            width: 35%;
        }
    </style>
</head>
<body>

    <!-- Letterhead / Kop Surat -->
    <table class="header-table">
        <tr>
            @if($schoolProfile && $schoolProfile->logo)
                <td class="header-logo">
                    <img src="{{ public_path('storage/' . $schoolProfile->logo) }}" alt="Logo" style="max-height: 60px;">
                </td>
            @endif
            <td class="header-text">
                <h2>{{ $schoolProfile->name ?? 'SMAN 1 Tajurhalang' }}</h2>
                <p>{{ $schoolProfile->address ?? 'Alamat Sekolah' }}</p>
                <p>Telp: {{ $schoolProfile->phone ?? '-' }} &bull; Email: {{ $schoolProfile->email ?? '-' }}</p>
            </td>
        </tr>
    </table>

    <div class="report-title">Laporan Kehadiran Absensi Mata Pelajaran</div>

    <!-- Metadata Laporan -->
    <table class="meta-table">
        <tr>
            <td style="width: 12%;">Periode</td>
            <td style="width: 2%;">:</td>
            <td>
                {{ $startDate ? $startDate->format('d F Y') : '-' }} s/d {{ $endDate ? $endDate->format('d F Y') : '-' }}
            </td>
            <td style="width: 12%; text-align: right;">Kelas</td>
            <td style="width: 2%; text-align: right;">:</td>
            <td style="width: 15%; text-align: right; font-weight: bold;">
                {{ request('class_id') ? (App\Models\SchoolClass::find(request('class_id'))->name ?? 'Semua') : 'Semua Kelas' }}
            </td>
        </tr>
        <tr>
            <td>Tanggal Cetak</td>
            <td>:</td>
            <td>{{ now()->translatedFormat('d F Y H:i') }}</td>
            <td style="text-align: right;">Mapel</td>
            <td style="text-align: right;">:</td>
            <td style="text-align: right; font-weight: bold;">
                {{ request('subject_id') ? (App\Models\Subject::find(request('subject_id'))->name ?? 'Semua') : 'Semua Mapel' }}
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%; text-align: center;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 8%;">Hari</th>
                <th style="width: 10%;">Jam Pelajaran</th>
                <th style="width: 8%;">Kelas</th>
                <th style="width: 15%;">Mata Pelajaran</th>
                <th style="width: 12%;">Guru</th>
                <th style="width: 9%;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 10%; text-align: center;">Status</th>
                <th style="width: 10%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($data as $row)
                @php
                    $attendance = $row->attendanceSubject;
                    $schedule = $attendance->schedule ?? null;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>{{ $attendance && $attendance->date ? $attendance->date->format('d-m-Y') : '-' }}</td>
                    <td>{{ $attendance && $attendance->date ? $attendance->date->translatedFormat('l') : '-' }}</td>
                    <td>{{ $schedule ? substr($schedule->start_time, 0, 5) . '-' . substr($schedule->end_time, 0, 5) : '-' }}</td>
                    <td>{{ $schedule->class->name ?? '-' }}</td>
                    <td>{{ $schedule->subject->name ?? '-' }}</td>
                    <td>{{ $schedule->teacher->name ?? '-' }}</td>
                    <td>{{ $row->student->nis ?? '-' }}</td>
                    <td><strong>{{ $row->student->name ?? '-' }}</strong></td>
                    <td style="text-align: center;">
                        <span class="status-badge status-{{ $row->status }}">{{ $row->status }}</span>
                    </td>
                    <td>{{ $row->note ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: #666; font-style: italic;">
                        Tidak ada data absensi ditemukan untuk filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature / Tanda Tangan -->
    <table class="footer-table">
        <tr>
            <td></td>
            <td class="footer-sign">
                <p>Bogor, {{ now()->translatedFormat('d F Y') }}</p>
                <p style="margin-top: 5px;">Mengetahui,</p>
                <p style="font-weight: bold; margin-bottom: 50px;">Operator TU SMAN 1 Tajurhalang</p>
                <p style="text-decoration: underline; font-weight: bold;">{{ auth()->user()->name }}</p>
                <p style="font-size: 8pt; color: #666; margin-top: 1px;">NIP. -</p>
            </td>
        </tr>
    </table>

</body>
</html>
