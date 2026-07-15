<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Gerbang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 80px;
            text-align: left;
        }
        .header-text {
            text-align: center;
        }
        .header-text h2 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 4px 0 0 0;
            font-size: 9pt;
            color: #666;
        }
        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
        }
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .status-badge {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .status-hadir { color: #155724; }
        .status-terlambat { color: #856404; }
        .status-izin { color: #0c5460; }
        .status-sakit { color: #004085; }
        .status-alpha { color: #721c24; }
        .footer-table {
            width: 100%;
            margin-top: 30px;
            font-size: 9.5pt;
        }
        .footer-sign {
            text-align: right;
            width: 40%;
        }
    </style>
</head>
<body>

    <!-- Letterhead / Kop Surat -->
    <table class="header-table">
        <tr>
            @if($schoolProfile && $schoolProfile->logo)
                <td class="header-logo">
                    <img src="{{ public_path('storage/' . $schoolProfile->logo) }}" alt="Logo" style="max-height: 70px;">
                </td>
            @endif
            <td class="header-text">
                <h2>{{ $schoolProfile->name ?? 'SMAN 1 Tajurhalang' }}</h2>
                <p>{{ $schoolProfile->address ?? 'Alamat Sekolah' }}</p>
                <p>Telp: {{ $schoolProfile->phone ?? '-' }} &bull; Email: {{ $schoolProfile->email ?? '-' }}</p>
            </td>
        </tr>
    </table>

    <div class="report-title">Laporan Kehadiran Absensi Gerbang</div>

    <!-- Metadata Laporan -->
    <table class="meta-table">
        <tr>
            <td style="width: 15%;">Periode</td>
            <td style="width: 2%;">:</td>
            <td>
                {{ $startDate ? $startDate->format('d F Y') : '-' }} s/d {{ $endDate ? $endDate->format('d F Y') : '-' }}
            </td>
            <td style="width: 15%; text-align: right;">Kelas</td>
            <td style="width: 2%; text-align: right;">:</td>
            <td style="width: 20%; text-align: right; font-weight: bold;">
                {{ request('class_id') ? (App\Models\SchoolClass::find(request('class_id'))->name ?? 'Semua') : 'Semua Kelas' }}
            </td>
        </tr>
        <tr>
            <td>Tanggal Cetak</td>
            <td>:</td>
            <td>{{ now()->translatedFormat('d F Y H:i') }}</td>
            <td style="text-align: right;">Status</td>
            <td style="text-align: right;">:</td>
            <td style="text-align: right; text-transform: uppercase;">
                {{ request('status') ?: 'Semua Status' }}
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 12%;">Hari</th>
                <th style="width: 12%;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 10%; text-align: center;">Waktu</th>
                <th style="width: 12%; text-align: center;">Status</th>
                <th style="width: 10%;">Metode</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($data as $row)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>{{ $row->date->format('d-m-Y') }}</td>
                    <td>{{ $row->date->translatedFormat('l') }}</td>
                    <td>{{ $row->student->nis ?? '-' }}</td>
                    <td><strong>{{ $row->student->name ?? '-' }}</strong></td>
                    <td>{{ $row->student->class->name ?? '-' }}</td>
                    <td style="text-align: center;">
                        {{ $row->time_in && $row->time_in !== '00:00:00' ? substr($row->time_in, 0, 5) : '-' }}
                    </td>
                    <td style="text-align: center;">
                        <span class="status-badge status-{{ $row->status }}">{{ $row->status }}</span>
                    </td>
                    <td>{{ ucfirst($row->method) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #666; font-style: italic;">
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
                <p style="font-weight: bold; margin-bottom: 60px;">Operator TU SMAN 1 Tajurhalang</p>
                <p style="text-decoration: underline; font-weight: bold;">{{ auth()->user()->name }}</p>
                <p style="font-size: 8pt; color: #666; margin-top: 2px;">NIP. -</p>
            </td>
        </tr>
    </table>

</body>
</html>
