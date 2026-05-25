<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TrackInFullExport implements WithMultipleSheets
{
    /** @var array<int, TrackInOutExport> */
    protected array $sheets;

    /**
     * @param array<int, TrackInOutExport> $sheets
     */
    public function __construct(array $sheets)
    {
        $this->sheets = $sheets;
    }

    public function sheets(): array
    {
        return $this->sheets;
    }
}
