<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class TrackInOutExport extends DefaultValueBinder implements FromArray, WithHeadings, WithTitle, WithCustomValueBinder
{
    /** @var array<int, array<int, mixed>> */
    protected array $rows;

    /** @var array<int, string> */
    protected array $headings;

    protected string $title;

    /**
     * Kolom (huruf, misal 'B', 'C') yang harus dipaksa sebagai string.
     * @var array<int, string>
     */
    protected array $stringColumns;

    /**
     * @param array<int, array<int, mixed>> $rows
     * @param array<int, string>            $headings
     * @param string                        $title
     * @param array<int, string>            $stringColumns  Kolom yang dipaksa string, e.g. ['B']
     */
    public function __construct(array $rows, array $headings, string $title = 'Data', array $stringColumns = [])
    {
        $this->rows          = $rows;
        $this->headings      = $headings;
        $this->title         = $title;
        $this->stringColumns = $stringColumns;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($this->stringColumns !== [] && in_array($cell->getColumn(), $this->stringColumns, true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}
