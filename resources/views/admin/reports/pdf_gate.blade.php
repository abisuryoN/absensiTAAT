<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Gerbang</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #334155;
            line-height: 1.4;
            background: #ffffff;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #cbd5e1;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 75px;
            text-align: left;
            vertical-align: middle;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
        }
        .header-text h2 {
            margin: 0;
            font-size: 15pt;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-text p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            color: #64748b;
        }
        .report-title {
            text-align: center;
            font-weight: 800;
            font-size: 11pt;
            text-transform: uppercase;
            color: #0f172a;
            margin-bottom: 18px;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 18px;
            font-size: 8.5pt;
            background: #f8fafc;
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
        }
        .meta-table td {
            padding: 3px 0;
            color: #475569;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 8.5pt;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
            border: 1px solid #334155;
            padding: 8px 6px;
        }
        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 6px;
            color: #334155;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7pt;
            padding: 3px 8px;
            border-radius: 4px;
            text-align: center;
            letter-spacing: 0.3px;
        }
        .status-hadir {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status-terlambat {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .status-izin {
            background-color: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
        }
        .status-sakit {
            background-color: #eef2ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }
        .status-alpha {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .footer-table {
            width: 100%;
            margin-top: 35px;
            font-size: 9pt;
            color: #334155;
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
                        <span class="status-badge status-{{ $row->status }}">{{ ucfirst(str_replace('_', ' ', $row->status)) }}</span>
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
