<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Harian Kehadiran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
        }
        .info {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Rekap Kehadiran Harian</h2>
    <div class="info">
        <strong>Nama:</strong> {{ $student->name }}<br>
        <strong>Kelas:</strong> {{ $student->schoolClass->name ?? '-' }}<br>
        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th class="text-center">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $attendance->date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '-' }}</td>
                    <td class="text-center">
                        @if($attendance->status === 'hadir')
                            Hadir
                        @elseif($attendance->status === 'terlambat')
                            Terlambat
                        @elseif($attendance->status === 'izin')
                            Izin
                        @elseif($attendance->status === 'sakit')
                            Sakit
                        @elseif($attendance->status === 'tidak_hadir')
                            Alpha
                        @else
                            {{ ucfirst($attendance->status) }}
                        @endif
                    </td>
                    <td>{{ $attendance->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data kehadiran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
