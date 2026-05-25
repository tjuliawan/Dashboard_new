<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Print - Daftar Transaksi Masuk</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; margin: 18px; }
        h2 { margin: 0 0 4px; font-size: 16px; }
        .meta { font-size: 11px; color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 5px 7px; font-size: 11px; text-align: left; vertical-align: top; }
        thead th { background: #e9eef7; }
        .actions { margin-bottom: 10px; }
        .actions button {
            padding: 6px 14px; font-size: 12px; border: 1px solid #555;
            background: #f5f5f5; cursor: pointer; border-radius: 4px;
        }
        @media print {
            .actions { display: none; }
            body { margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Tutup</button>
    </div>

    <h2>Daftar Transaksi Masuk</h2>
    <div class="meta">
        Dicetak: {{ date('Y-m-d H:i') }}
        @if (!empty($dateFrom) || !empty($dateTo))
            &nbsp;|&nbsp; Periode PO:
            {{ $dateFrom ?: '...' }} s/d {{ $dateTo ?: '...' }}
        @endif
        &nbsp;|&nbsp; Total: {{ count($rows) }} baris
    </div>

    <table>
        <thead>
            <tr>
                <th>Nomor PO</th>
                <th>Tgl PO</th>
                <th>Tallysheet</th>
                <th>Tgl Tallysheet</th>
                <th>BTB</th>
                <th>Tgl BTB</th>
                <th>Putaway</th>
                <th style="text-align:right;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->po_no }}</td>
                    <td>{{ $row->po_date }}</td>
                    <td>{{ $row->tallysheet_no }}</td>
                    <td>{{ $row->tallysheet_date }}</td>
                    <td>{{ $row->btb_no }}</td>
                    <td>{{ $row->btb_date }}</td>
                    <td>{{ $row->putaway }}</td>
                    <td style="text-align:right;">{{ $row->qty }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:#888;">Tidak ada data pada rentang tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            // auto trigger print dialog
            setTimeout(function () { window.print(); }, 200);
        });
    </script>
</body>
</html>
