<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

class Exportexeljapfa implements FromArray, WithHeadings, WithEvents, WithStyles
{
    protected $data;

    public function __construct()
    {
        // Query data langsung
        $this->data = DB::connection('ms_sql_hgs')->select("
            WITH cte AS (
                SELECT
                    DATEPART(YEAR, himp.invoice_date) AS tahun,
                    DATEPART(MONTH, himp.invoice_date) AS bulan,
                    himp.invoice_number,
                    -- himp.retailer_code,
                    himp.retailer_name,
                    dimp.distributor_stock_keeping_unit AS [Id product],
                    sku_description AS [Product],
                    dimp.unit AS Unit,
                    dimp.eaches_quantity AS qty,
                    dimp.unit_price AS [price],
                    dimp.net_value AS [Net/value],
                    CONVERT(DATE, himp.invoice_date) AS Invoice_date,
                    CONVERT(DATE, dpch.rec_datecreated) AS [Send date],
                    SUBSTRING(himp.invoice_number, 1, 3) AS wilayah,
                    iif(SUBSTRING(himp.invoice_number, 1, 3) = 'JKT', 'Cipinang', 'Tanggerang') AS cabang,
                    dpch.dpcth_code_h,
                    dpchd.Dptch_qty_terima * CONVERT(INT, SUBSTRING(SKU_convertpcs, 0, CHARINDEX(' ', SKU_convertpcs))) AS [Qty Terima],
                    dpchd.Dptch_qty_terima * CONVERT(INT, SUBSTRING(SKU_convertpcs, 0, CHARINDEX(' ', SKU_convertpcs))) * 422 AS [Value total KG],
                    CONCAT(dpch.dpcth_code_h, '-', dpcth_so) AS dpc,
                    SKU_description

                FROM
                    tgu_tr_invoice_h_import himp
                    LEFT JOIN tgu_tr_invoice_d_import dimp
                        ON himp.invoice_number = dimp.invoice_number
                    LEFT JOIN tgu_ms_product_Business mspro
                        ON dimp.distributor_stock_keeping_unit = mspro.sku_business
                        AND mspro.business = 'japfa'
                    LEFT JOIN TGU_dispatch_h dpch
                        ON himp.invoice_number = dpch.dpcth_so
                    LEFT JOIN TGU_dispatch_d dpchd
                        ON dpch.dpcth_code_h = dpchd.dptch_code_h
                        AND dpch.Dpcth_SO = dpchd.Dptch_SO
                        AND dimp.distributor_stock_keeping_unit = dpchd.Dptch_Product
                WHERE
                    himp.client = 'Japfa'
                    AND DATEPART(YEAR, himp.invoice_date) = '2025'
                    and DATEPART(MONTH, himp.invoice_date) = '05'
            )
            SELECT
                invoice_number,
                retailer_name,
                Invoice_date,
                price,
                qty,
                price*qty total_price,
                SKU_description
            FROM
                cte
            WHERE
                wilayah = 'JKT'
            order by invoice_date desc;
        ");
    }

    public function array(): array
    {
        // Convert object to array
        return array_map(function ($item) {
            return (array) $item;
        }, $this->data);
    }

    public function headings(): array
    {
        return [
            'Tahun',
            'Bulan',
            'Invoice Number',
            'Retailer Name',
            'Id Product',
            'Product',
            'Unit',
            'Qty',
            'Unit Price',
            'Net Value',
            'Invoice Date',
            'Send Date',
            'Wilayah',
            'Cabang',
            'Kode Dispatch',
            'Qty Terima',
            'Total KG',
            'DPC'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            2 => ['font' => ['bold' => true]], // header tabel
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Tambahkan judul
                $event->sheet->mergeCells('A1:R1');
                $event->sheet->setCellValue('A1', 'Laporan Invoice Wilayah JKT - 2025');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Geser data ke bawah (judul = row 1, header = row 2, data mulai row 3)
                $event->sheet->getDelegate()->insertNewRowBefore(2, 1);
            }
        ];
    }
}
