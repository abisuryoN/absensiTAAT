<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header .school-name {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header .report-title {
            font-size: 11px;
            font-weight: bold;
            color: #1d4ed8;
            margin-top: 3px;
        }
        .header .report-subtitle {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }
        .filter-info {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 10px;
            font-size: 8px;
            color: #475569;
        }
        .filter-info span {
            display: inline-block;
            margin-right: 18px;
        }
        .filter-info span::before {
            content: '▸ ';
            color: #1d4ed8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        thead tr th {
            background-color: #1d4ed8;
            color: #fff;
            font-weight: bold;
            padding: 5px 6px;
            text-align: center;
            border: 1px solid #1e40af;
            white-space: nowrap;
        }
        tbody tr td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        tbody tr:hover td {
            background-color: #eff6ff;
        }
        .td-center { text-align: center; }
        .td-right  { text-align: right; }

        /* Status badges via text styling */
        .status-hadir     { color: #16a34a; font-weight: bold; }
        .status-terlambat { color: #dc2626; font-weight: bold; }
        .status-izin      { color: #0891b2; font-weight: bold; }
        .status-sakit     { color: #d97706; font-weight: bold; }
        .status-alpha     { color: #7c3aed; font-weight: bold; }
        .status-absent    { color: #94a3b8; font-style: italic; }

        .footer {
            margin-top: 14px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 8px;
            color: #94a3b8;
        }
        .summary-box {
            margin-bottom: 10px;
            font-size: 8px;
        }
        .summary-box table {
            width: auto;
        }
        .summary-box td {
            padding: 2px 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .summary-box th {
            padding: 2px 8px;
            background: #e0e7ff;
            color: #3730a3;
            font-weight: bold;
            border: 1px solid #c7d2fe;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-style: italic;
        }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    {{-- ── HEADER ──────────────────────────────────────────── --}}
    <div class="header">
        <div class="school-name">{{ $schoolName }}</div>
        <div class="report-title">Rekap Absensi Gerbang</div>
        <div class="report-subtitle">
            @if($dateFrom->isSameDay($dateTo))
                Tanggal: {{ $dateFrom->translatedFormat('l, d F Y') }}
            @else
                Periode: {{ $dateFrom->translatedFormat('d F Y') }} – {{ $dateTo->translatedFormat('d F Y') }}
            @endif
            &nbsp;·&nbsp; Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

    {{-- ── FILTER INFO ─────────────────────────────────────── --}}
    @if(count($filterLabels) > 0)
    <div class="filter-info">
        @foreach($filterLabels as $label)
            <span>{{ $label }}</span>
        @endforeach
    </div>
    @endif

    {{-- ── SUMMARY ─────────────────────────────────────────── --}}
    @if(!$isTidakHadir && $rows->count() > 0)
    @php
        $summaryGroups = $rows->groupBy('status');
        $statusLabels  = [
            'hadir'     => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin'      => 'Izin',
            'sakit'     => 'Sakit',
            'alpha'     => 'Alpha',
        ];
    @endphp
    <div class="summary-box">
        <table>
            <thead>
                <tr>
                    <th>Total Data</th>
                    @foreach($statusLabels as $key => $label)
                        @if(isset($summaryGroups[$key]))
                        <th>{{ $label }}</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="td-center"><strong>{{ $rows->count() }}</strong></td>
                    @foreach($statusLabels as $key => $label)
                        @if(isset($summaryGroups[$key]))
                        <td class="td-center">{{ $summaryGroups[$key]->count() }}</td>
                        @endif
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    @elseif($isTidakHadir)
    <div class="summary-box">
        <table>
            <thead><tr><th>Total Siswa Tidak Hadir</th></tr></thead>
            <tbody><tr><td class="td-center"><strong>{{ $rows->count() }}</strong></td></tr></tbody>
        </table>
    </div>
    @endif

    {{-- ── DATA TABLE ──────────────────────────────────────── --}}
    @if($rows->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width:25px;">No</th>
                <th style="width:60px;">Tanggal</th>
                <th style="width:55px;">Hari</th>
                <th style="width:55px;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width:55px;">Kelas</th>
                <th style="width:55px;">Jurusan</th>
                <th style="width:45px;">Jam Masuk</th>
                <th style="width:55px;">Status</th>
                <th style="width:45px;">Metode</th>
                <th>Keterangan</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
            @php
                $statusClass = match($row->status ?? '') {
                    'hadir'       => 'status-hadir',
                    'terlambat'   => 'status-terlambat',
                    'izin'        => 'status-izin',
                    'sakit'       => 'status-sakit',
                    'alpha'       => 'status-alpha',
                    default       => 'status-absent',
                };
                $statusLabel = match($row->status ?? '') {
                    'hadir'       => 'Hadir',
                    'terlambat'   => 'Terlambat',
                    'izin'        => 'Izin',
                    'sakit'       => 'Sakit',
                    'alpha'       => 'Alpha',
                    'tidak_hadir' => 'Tidak Hadir',
                    default       => ucfirst($row->status ?? '-'),
                };
                $methodLabel = match($row->method ?? '') {
                    'barcode' => 'Barcode',
                    'qr_code' => 'QR Code',
                    'manual'  => 'Manual',
                    default   => '-',
                };
            @endphp
            <tr>
                <td class="td-center">{{ $i + 1 }}</td>
                <td class="td-center">
                    {{ $row->date instanceof \Carbon\Carbon ? $row->date->format('d/m/Y') : $row->date }}
                </td>
                <td class="td-center">
                    {{ $row->date instanceof \Carbon\Carbon ? $row->date->translatedFormat('l') : '-' }}
                </td>
                <td class="td-center">{{ $row->student->nis ?? '-' }}</td>
                <td>{{ $row->student->name ?? '-' }}</td>
                <td class="td-center">{{ $row->student->class->name ?? '-' }}</td>
                <td class="td-center">{{ $row->student->class->major->name ?? '-' }}</td>
                <td class="td-center">
                    @if($isTidakHadir || $row->time_in === '-')
                        -
                    @else
                        {{ substr($row->time_in, 0, 5) }} WIB
                    @endif
                </td>
                <td class="td-center {{ $statusClass }}">{{ $statusLabel }}</td>
                <td class="td-center">{{ $methodLabel }}</td>
                <td>{{ ($row->note && $row->note !== '-') ? $row->note : '-' }}</td>
                <td>{{ optional($row->scanner)->name ?? 'System' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>Tidak ada data absensi untuk filter yang dipilih.</p>
    </div>
    @endif

    {{-- ── FOOTER ──────────────────────────────────────────── --}}
    <div class="footer">
        <div>
            <em>{{ $schoolName }} — Rekap Absensi Gerbang</em>
        </div>
        <div style="text-align:right;">
            Dicetak oleh sistem pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

</body>
</html>