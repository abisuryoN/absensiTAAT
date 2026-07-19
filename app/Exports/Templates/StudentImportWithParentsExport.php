<?php

namespace App\Exports\Templates;

use App\Models\StudentParent;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentImportWithParentsExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new StudentImportSheet(),
            new ParentReferenceSheet(),
        ];
    }
}