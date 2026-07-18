<x-app-layout>
    @section('title', 'Preview Import Data')

    <div class="row mb-4">
        <div class="col">
            <h3 class="fw-bold tracking-tight text-dark mb-1">Preview & Validasi Import</h3>
            <p class="text-muted mb-0">Tinjau hasil validasi baris spreadsheet sebelum menyimpannya ke database.</p>
        </div>
    </div>

    @php
        $totalRows = count($previewRows);
        $validRows = count(array_filter($previewRows, fn($r) => $r['is_valid']));
        $invalidRows = $totalRows - $validRows;
    @endphp

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary-subtle text-primary p-3">
                <span class="fs-8 fw-semibold text-uppercase">Total Baris</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $totalRows }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success-subtle text-success p-3">
                <span class="fs-8 fw-semibold text-uppercase">Baris Valid</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $validRows }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger-subtle text-danger p-3">
                <span class="fs-8 fw-semibold text-uppercase">Baris Error</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $invalidRows }}</h3>
            </div>
        </div>
        <div class="col-md-3 d-flex align-items-center justify-content-end gap-2">
            <form method="POST" action="{{ route('admin.imports.cancel') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-light border fw-semibold">Batal</button>
            </form>
            <form method="POST" action="{{ route('admin.imports.commit') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary fw-semibold" {{ $validRows === 0 ? 'disabled' : '' }}>
                    <i class="bi bi-check-lg"></i> Impor Data Valid
                </button>
            </form>
        </div>
    </div>

    <!-- Error Alert if any -->
    @if($invalidRows > 0)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4 fs-7" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                Ditemukan <strong>{{ $invalidRows }} baris error</strong> yang tidak akan diimpor. Baris valid tetap dapat diimpor dengan mengklik tombol "Impor Data Valid".
            </div>
        </div>
    @endif

    <!-- Preview Table -->
    <div class="card glass-card border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle fs-7">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">Baris Excel</th>
                            <th style="width: 100px;">Status</th>
                            <th>Data</th>
                            <th>Keterangan / Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewRows as $row)
                                    <tr class="{{ $row['is_valid'] ? 'table-success-subtle' : 'table-danger-subtle' }}">
                                        <td data-label="Baris Excel" class="text-center fw-bold">{{ $row['row_number'] }}</td>
                                        <td data-label="Status">
                                    @if($row['is_valid'])
                                        <span class="badge bg-success text-white px-2 py-1 fs-8">Valid</span>
                                    @else
                                        <span class="badge bg-danger text-white px-2 py-1 fs-8">Error</span>
                                    @endif
                                </td>
                                        <td data-label="Data">
                                            <div class="row g-1">
                                                @foreach($row['data'] as $key => $val)
                                                    <div class="col-6">
                                                        <span class="fw-semibold text-muted text-uppercase" style="font-size: 0.65rem;">{{ str_replace('_', ' ', $key) }}:</span>
                                                        <span class="text-dark d-block text-truncate fs-8">{{ $val ?: '-' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td data-label="Keterangan">
                                    @if($row['is_valid'])
                                        <span class="text-success"><i class="bi bi-check-circle me-1"></i> Data siap diimpor</span>
                                    @else
                                        <ul class="text-danger ps-3 mb-0 fs-8">
                                            @foreach($row['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .table-success-subtle {
            background-color: rgba(25, 135, 84, 0.05) !important;
        }
        .table-danger-subtle {
            background-color: rgba(220, 53, 69, 0.05) !important;
        }
    </style>
</x-app-layout>
