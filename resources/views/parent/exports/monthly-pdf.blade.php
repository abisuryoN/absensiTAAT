<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Bulanan Kehadiran</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 5px; }
        .info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>Rekap Kehadiran Bulanan</h2>
    <div class="info">
        <strong>Nama:</strong> {{ $student->name }}<br>
        <strong>Kelas:</strong> {{ $student->schoolClass->name ?? '-' }}<br>
        <strong>Tahun:</strong> {{ $year }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Terlambat</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Sakit</th>
                <th class="text-center">Alpha</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($months as $month)
                <tr>
                    <td>{{ $month['month_label'] }}</td>
                    <td class="text-center">{{ $month['hadir'] }}</td>
                    <td class="text-center">{{ $month['terlambat'] }}</td>
                    <td class="text-center">{{ $month['izin'] }}</td>
                    <td class="text-center">{{ $month['sakit'] }}</td>
                    <td class="text-center">{{ $month['tidak_hadir'] }}</td>
                    <td class="text-center">{{ $month['total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
