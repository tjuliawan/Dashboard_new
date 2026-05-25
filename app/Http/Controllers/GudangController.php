<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\TrackInOutExport;
use App\Exports\TrackInFullExport;
use Maatwebsite\Excel\Facades\Excel;

class GudangController extends Controller
{
    /**
     * Mirror form C# RCM_WO:
     *   Transaksi.Inventory.Reporting.ReportRekapStockRack.PrintPreviewReportRekapStockRack
     *
     * Logika SP yang aslinya dipanggil (di-reimplement di sini, tidak pakai EXEC):
     *   - Branch saja           : Sp_list_TGU_tr_inv_main_mutasi_rack_cabang
     *   - Branch + Gudang       : Sp_list_TGU_tr_inv_main_mutasi_rack_cabang_gudang3
     */
    public function rekapStockRack(Request $request)
    {
        $conn = DB::connection('rcm_ol_tgu');

        // ---- Master data dropdown ---------------------------------------------------
        $cabangList = $conn->table('ms_cabang')
            ->select('cab_code', 'cab_desc')
            ->orderBy('cab_code')
            ->get();

        $gudangList = $conn->table('TGU_ms_gudang')
            ->select('Gudang_code', 'Gudang_desc', 'Gudang_business')
            ->orderBy('Gudang_code')
            ->get();

        // ---- Filter user ------------------------------------------------------------
        $cabCode    = trim((string) $request->input('cab_code', ''));     // searchLookUpE_Branch.Text
        $gudangCode = trim((string) $request->input('gudang_code', ''));  // gridLookUpE_Gudang.Text
        $search     = trim((string) $request->input('search', ''));       // filter UI: rack internal / principal / SKU

        $allowedPerPage = [25, 50, 100, 200];
        $perPage = (int) $request->input('per_page', 50);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $rows      = collect();
        $paginator = null;

        // C# DRE_Click hanya menjalankan query bila Branch dipilih.
        if ($cabCode !== '') {

            // ---- Bangun subquery utama mengikuti SP asli ------------------------
            //
            // Mode A (branch saja)  → Sp_list_TGU_tr_inv_main_mutasi_rack_cabang
            //   FROM tgu_ms_rack_internal r OUTER APPLY (TOP 1 mutasi LEFT JOIN
            //   tgu_ms_product_Business ON ms_product_code = sku_master
            //                          AND ms_product_business_code = sku_business)
            //
            // Mode B (branch+gudang) → Sp_list_TGU_tr_inv_main_mutasi_rack_cabang_gudang3
            //   ROW_NUMBER() per (ms_product_business_code, ms_product_code, ms_rack_code),
            //   join tgu_ms_product_Business ON SKU_default + SKU_Business + Business
            //   UNION ALL bagian rack "Transit".
            //
            if ($gudangCode === '') {
                $rawSub = <<<'SQL'
                    SELECT
                        r.rack_internal_code,
                        r.rack_principal_code,
                        r.rack_business,
                        r.rack_branch,
                        ISNULL(mm.ms_product_code, '')          AS ms_product_code,
                        mm.ms_product_business_order_code,
                        mm.SKU_description                       AS description,
                        ISNULL(
                            CONVERT(int, SUBSTRING(
                                mm.SKU_convertpcs, 1,
                                NULLIF(CHARINDEX(' ', mm.SKU_convertpcs), 0) - 1
                            )),
                            1
                        )                                        AS skuconvert,
                        CONVERT(date, mm.exp_date_in)            AS exp_date_in,
                        mm.usr_cnt_last_stock,
                        r.rec_datecreated                        AS rack_rec_datecreated,
                        ISNULL(mm.Stock_akhir, 0)                AS Stock_akhir,
                        mm.unit_bisnis,
                        mm.SKU_hargabeli_pcs                     AS price
                    FROM tgu_ms_rack_internal r
                    OUTER APPLY (
                        SELECT TOP 1
                            mainmutasi.ms_rack_code,
                            mainmutasi.Stock_akhir,
                            mainmutasi.ms_product_code,
                            mainmutasi.ms_product_business_order_code,
                            mainmutasi.exp_date_in,
                            mainmutasi.usr_cnt_last_stock,
                            mainmutasi.unit_bisnis,
                            opro.SKU_description,
                            opro.SKU_convertpcs,
                            opro.SKU_hargabeli_pcs
                        FROM TGU_tr_inv_main_mutasi_rack mainmutasi
                        LEFT JOIN tgu_ms_product_Business opro
                            ON mainmutasi.ms_product_code          = opro.sku_master
                           AND mainmutasi.ms_product_business_code = opro.sku_business
                        WHERE r.rack_internal_code = mainmutasi.ms_rack_code
                          AND mainmutasi.rec_areacode = ?
                        ORDER BY mainmutasi.rec_datecreated DESC
                    ) mm
                    WHERE r.rack_branch = ?
                      AND r.rack_active != 0
                      AND ISNULL(mm.Stock_akhir, 0) > 0
                    SQL;
                $bindings = [$cabCode, $cabCode];
            } else {
                $gudangMaster = $this->mapGudangMaster($gudangCode);

                $rawSub = <<<'SQL'
                    SELECT
                        ISNULL(orackinternal.rack_internal_code,  'Transit') AS rack_internal_code,
                        ISNULL(orackinternal.rack_principal_code, 'Transit') AS rack_principal_code,
                        ISNULL(orackinternal.rack_business, ?)               AS rack_business,
                        ISNULL(orackinternal.rack_branch,   ?)               AS rack_branch,
                        racksku.ms_product_business_code                     AS ms_product_code,
                        racksku.ms_product_business_order_code,
                        CONVERT(date, racksku.exp_date_in)                   AS exp_date_in,
                        oproduct.SKU_description                             AS description,
                        ISNULL(
                            CONVERT(int, SUBSTRING(
                                oproduct.SKU_convertpcs, 1,
                                NULLIF(CHARINDEX(' ', oproduct.SKU_convertpcs), 0) - 1
                            )),
                            1
                        )                                                    AS skuconvert,
                        racksku.Stock_akhir,
                        racksku.usr_cnt_last_stock,
                        CAST(NULL AS datetime)                               AS rack_rec_datecreated,
                        racksku.unit_bisnis,
                        oproduct.SKU_hargabeli_pcs                           AS price
                    FROM (
                        SELECT
                            rec_datecreated, ms_rack_code,
                            ms_product_business_code, ms_product_code,
                            ms_product_business_order_code,
                            CONVERT(date, exp_date_in) AS exp_date_in,
                            Stock_akhir, usr_cnt_last_stock, unit_bisnis,
                            ROW_NUMBER() OVER (
                                PARTITION BY ms_product_business_code, ms_product_code, ms_rack_code
                                ORDER BY rack.rec_datecreated DESC
                            ) AS RN
                        FROM TGU_tr_inv_main_mutasi_rack rack
                        WHERE rec_areacode = ? AND unit_bisnis = ?
                    ) racksku
                    LEFT JOIN tgu_ms_rack_internal orackinternal
                        ON racksku.ms_rack_code = orackinternal.rack_internal_code
                    LEFT JOIN tgu_ms_product_Business oproduct
                        ON racksku.ms_product_business_code       = oproduct.SKU_default
                       AND oproduct.Business                       = ?
                       AND racksku.ms_product_business_order_code = oproduct.SKU_Business
                    WHERE racksku.RN = 1
                      AND racksku.Stock_akhir != 0
                      AND orackinternal.rack_branch    = ?
                      AND orackinternal.rack_business  = ?
                      AND orackinternal.rack_active   != 0
                      AND oproduct.SKU_description IS NOT NULL
                      AND CONVERT(date, racksku.rec_datecreated) >= '2022-05-01'

                    UNION ALL

                    SELECT
                        ISNULL(orackinternal.rack_internal_code,  'Transit') AS rack_internal_code,
                        ISNULL(orackinternal.rack_principal_code, 'Transit') AS rack_principal_code,
                        ISNULL(orackinternal.rack_business, ?)               AS rack_business,
                        ISNULL(orackinternal.rack_branch,   ?)               AS rack_branch,
                        racksku.ms_product_business_code                     AS ms_product_code,
                        racksku.ms_product_business_order_code,
                        CONVERT(date, racksku.exp_date_in)                   AS exp_date_in,
                        oproduct.SKU_description                             AS description,
                        ISNULL(
                            CONVERT(int, SUBSTRING(
                                oproduct.SKU_convertpcs, 1,
                                NULLIF(CHARINDEX(' ', oproduct.SKU_convertpcs), 0) - 1
                            )),
                            1
                        )                                                    AS skuconvert,
                        racksku.Stock_akhir,
                        racksku.usr_cnt_last_stock,
                        CAST(NULL AS datetime)                               AS rack_rec_datecreated,
                        racksku.unit_bisnis,
                        oproduct.SKU_hargabeli_pcs                           AS price
                    FROM (
                        SELECT
                            rec_datecreated, ms_rack_code,
                            ms_product_business_code, ms_product_code,
                            ms_product_business_order_code,
                            CONVERT(date, exp_date_in) AS exp_date_in,
                            Stock_akhir, usr_cnt_last_stock, unit_bisnis,
                            ROW_NUMBER() OVER (
                                PARTITION BY ms_product_business_code, ms_product_code, ms_rack_code
                                ORDER BY rack.rec_datecreated DESC
                            ) AS RN
                        FROM TGU_tr_inv_main_mutasi_rack rack
                        WHERE rec_areacode = ? AND unit_bisnis = ? AND ms_rack_code = 'Transit'
                    ) racksku
                    LEFT JOIN tgu_ms_rack_internal orackinternal
                        ON racksku.ms_rack_code = orackinternal.rack_internal_code
                    LEFT JOIN tgu_ms_product_Business oproduct
                        ON racksku.ms_product_business_code       = oproduct.SKU_default
                       AND oproduct.Business                       = ?
                       AND racksku.ms_product_business_order_code = oproduct.SKU_Business
                    WHERE racksku.RN = 1
                      AND racksku.Stock_akhir != 0
                      AND oproduct.SKU_description IS NOT NULL
                      AND CONVERT(date, racksku.rec_datecreated) >= '2022-05-01'
                    SQL;
                $bindings = [
                    // bagian pertama
                    $gudangMaster, $cabCode,
                    $cabCode, $gudangCode,
                    $gudangMaster,
                    $cabCode, $gudangMaster,
                    // bagian Transit (UNION ALL)
                    $gudangMaster, $cabCode,
                    $cabCode, $gudangCode,
                    $gudangMaster,
                ];
            }

            // Sub-aggregate cogs: ambil 1 baris terbaru per sku_business
            $cogsSub = $conn->query()->fromRaw("(
                SELECT sku_business, cogs_pcs, cogs_ctn, last_cogs_pcs,
                       cogs_harga_terakhir_pcs, cogs_harga_terakhir_ctn,
                       cogs_price_list_pcs, cogs_price_list_ctn,
                       ROW_NUMBER() OVER (PARTITION BY sku_business ORDER BY rec_datecreated DESC) AS rn
                FROM ms_cogs
            ) x")->where('x.rn', 1);

            $query = $conn->query()
                ->fromRaw("($rawSub) as l", $bindings)
                ->leftJoinSub($cogsSub, 'c', 'c.sku_business', '=', 'l.ms_product_code')
                ->select([
                    'l.rack_internal_code',
                    'l.rack_principal_code',
                    'l.rack_business',
                    'l.rack_branch',
                    'l.ms_product_code',
                    'l.ms_product_business_order_code',
                    'l.description',
                    'l.Stock_akhir',
                    'l.skuconvert',
                    'l.exp_date_in',
                    'l.usr_cnt_last_stock',
                    'l.price',
                    DB::raw('(ISNULL(l.price, 0) * l.Stock_akhir) AS value'),
                    'c.cogs_pcs',
                    'c.cogs_ctn',
                    'c.last_cogs_pcs',
                    'c.cogs_harga_terakhir_pcs',
                    'c.cogs_harga_terakhir_ctn',
                    'c.cogs_price_list_pcs',
                    'c.cogs_price_list_ctn',
                ])
                ->orderByDesc('l.rack_rec_datecreated')
                ->orderBy('l.rack_internal_code')
                ->orderBy('l.ms_product_code');

            // ---- Filter pencarian (rack internal / rack principal / SKU) --------
            if ($search !== '') {
                $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('l.rack_internal_code',  'like', $like)
                      ->orWhere('l.rack_principal_code', 'like', $like)
                      ->orWhere('l.ms_product_code',    'like', $like);
                });
            }

            $paginator = $query->paginate($perPage)->withQueryString();

            // Tambahkan kolom "no" mengikuti grdD_format() di C#.
            $offset = ($paginator->currentPage() - 1) * $paginator->perPage();
            $rows = collect($paginator->items())->map(function ($r, $i) use ($offset) {
                return [
                    'no'                             => $offset + $i + 1,
                    'rack_internal_code'             => $r->rack_internal_code,
                    'rack_principal_code'            => $r->rack_principal_code,
                    'rack_business'                  => $r->rack_business,
                    'rack_branch'                    => $r->rack_branch,
                    'ms_product_code'                => $r->ms_product_code,
                    'ms_product_business_order_code' => $r->ms_product_business_order_code,
                    'description'                    => $r->description,
                    'Stock_akhir'                    => $r->Stock_akhir,
                    'skuconvert'                     => $r->skuconvert,
                    'exp_date_in'                    => $r->exp_date_in,
                    'usr_cnt_last_stock'             => $r->usr_cnt_last_stock,
                    'price'                          => $r->price,
                    'value'                          => $r->value,
                    'cogs_pcs'                       => $r->cogs_pcs,
                    'cogs_ctn'                       => $r->cogs_ctn,
                    'last_cogs_pcs'                  => $r->last_cogs_pcs,
                    'cogs_harga_terakhir_pcs'        => $r->cogs_harga_terakhir_pcs,
                    'cogs_harga_terakhir_ctn'        => $r->cogs_harga_terakhir_ctn,
                    'cogs_price_list_pcs'            => $r->cogs_price_list_pcs,
                    'cogs_price_list_ctn'            => $r->cogs_price_list_ctn,
                ];
            })->values();
        }

        return view('gudang.rekap_stock_rack', [
            'cabangList' => $cabangList,
            'gudangList' => $gudangList,
            'rows'       => $rows,
            'paginator'  => $paginator,
            'filters'    => [
                'cab_code'    => $cabCode,
                'gudang_code' => $gudangCode,
                'search'      => $search,
                'per_page'    => $perPage,
            ],
        ]);
    }

    /**
     * Halaman Price List — dua panel mirror form C# RCM_WO
     *   Master.Logistic.Pricelist.TGU_Pricelist_Management
     *
     * Panel 1 "Update by Supplier" (gridControl1) ==============================
     *   Display:
     *     - GridControlPriceBySupplier()           → Sp_List_tr_TGUPriceList_Supplier
     *     - btn_Search_Click(branch, supplier)     → Sp_List_tr_TGUPriceList_SupplierCode
     *   Source: tr_TGUPriceList + tr_TGUPricelist_d, JOIN ms_cabang, ms_supplier,
     *           TGU_ms_product_price (last/low/high/avg), ms_unit, tgu_ms_product_Business
     *   Logika harga "Last_price" = harga PCS dari row ke-2 (harga sebelumnya);
     *   Lowest/Highest/Average diambil dari TGU_ms_product_price.
     *
     * Panel 2 "Price List / by SKU" (gridControl2) =============================
     *   Display:
     *     - textE_SKU_EditValueChanged(sku)        → Sp_Get_TGU_ms_product_price_SKU_code
     *     - textE_Product_EditValueChanged(desc)   → Sp_get_TGU_ms_product_price_description
     *     - btnSearchSKU_Click(branch, supplier)   → Sp_Get_tgu_ms_product_price_branch_supplier
     *   Source: TGU_ms_product_price (master harga per SKU+Supplier+Branch),
     *           lookup ms_cabang, ms_unit, ms_supplier, tgu_ms_product_Business.
     */
    public function priceList(Request $request)
    {
        $conn = DB::connection('rcm_ol_tgu');

        // ---- Master dropdown (FillGridBranch / FillGridSupplier) ---------------
        $cabangList = $conn->table('ms_cabang')
            ->select('cab_code', 'cab_desc')
            ->orderBy('cab_code')
            ->get();

        $supplierList = $conn->table('ms_supplier')
            ->select('supp_code', 'supp_desc')
            ->orderBy('supp_desc')
            ->get();

        $allowedPerPage = [25, 50, 100, 200];

        // ===== Panel 2 : Price List / by SKU =================================
        $skuBranch   = trim((string) $request->input('sku_branch',   ''));
        $skuSupplier = trim((string) $request->input('sku_supplier', ''));
        $skuCode     = trim((string) $request->input('sku_code',     ''));
        $skuDesc     = trim((string) $request->input('sku_desc',     ''));
        $skuPerPage  = (int) $request->input('sku_per_page', 50);
        if (!in_array($skuPerPage, $allowedPerPage, true)) {
            $skuPerPage = 50;
        }

        $skuRows = $this->priceListBySku(
            $conn, $skuBranch, $skuSupplier, $skuCode, $skuDesc, $skuPerPage
        );

        return view('gudang.price_list', [
            'cabangList'   => $cabangList,
            'supplierList' => $supplierList,

            // Panel 2
            'skuRows'      => $skuRows['rows'],
            'skuPaginator' => $skuRows['paginator'],
            'skuFilters'   => [
                'sku_branch'   => $skuBranch,
                'sku_supplier' => $skuSupplier,
                'sku_code'     => $skuCode,
                'sku_desc'     => $skuDesc,
                'sku_per_page' => $skuPerPage,
            ],
        ]);
    }

    /**
     * Panel 1 (mini-form) — simpan 1 row pricelist baru.
     * Forwarder: ubah field mini-form jadi struktur details[] lalu panggil
     * priceListStore() agar mapping DB 100% sama dengan C# Add New Pricelist.
     */
    public function priceListUpdate(Request $request)
    {
        $data = $request->validate([
            'branch'           => 'required|string|max:50',
            'supplier'         => 'required|string|max:50',
            'principal'        => 'required|string|max:50',
            'type'             => 'required|string|max:50',
            'description'      => 'required|string|max:50',
            'sku_code'         => 'required|string|max:100',
            'sku_supplier'     => 'required|string|max:100',
            'price_pcs'        => 'required|numeric|min:0',
            'price_ctn'        => 'nullable|numeric|min:0',
            'price_pcs_jual'   => 'nullable|numeric|min:0',
            'price_ctn_jual'   => 'nullable|numeric|min:0',
            'MOQ'              => 'nullable|string|max:50',
            'ms_unit_code'     => 'nullable|string|max:50',
            'MsPriceMode_code' => 'nullable|string|max:50',
            'OngkosAngkut'     => 'nullable|numeric|min:0',
            'ExpDate'          => 'nullable|date',
            'date_start'       => 'required|date',
        ]);

        // Susun ulang ke struktur yang diharapkan priceListStore()
        $request->merge([
            'description'    => $data['description'],
            'operator'       => optional(auth()->user())->name ?? 'web',
            'supplier_code'  => $data['supplier'],
            'branch_code'    => $data['branch'],
            'principal'      => $data['principal'],
            'type'           => $data['type'],
            'pricelist_date' => now()->toDateString(),
            'details' => [[
                'SKU_code'         => $data['sku_code'],
                'SKU_Supplier'     => $data['sku_supplier'],
                'MOQ'              => $data['MOQ']              ?? '',
                'MsPriceMode_code' => $data['MsPriceMode_code'] ?? '',
                'ms_unit_code'     => $data['ms_unit_code']     ?? '',
                'OngkosAngkut'     => $data['OngkosAngkut']     ?? 0,
                'Price_pcs'        => $data['price_pcs'],
                'Price_ctn'        => $data['price_ctn']        ?? 0,
                'price_pcs_jual'   => $data['price_pcs_jual']   ?? 0,
                'price_ctn_jual'   => $data['price_ctn_jual']   ?? 0,
                'ExpDate'          => $data['ExpDate']          ?? null,
                'date_start'       => $data['date_start'],
                'discount_percentage'       => 0,
                'discount_whole_seller'     => 0,
                'discount_toko'             => 0,
                'discount_star_outlet'      => 0,
                'discount_semi_wholeseller' => 0,
                'discount_special'          => 0,
            ]],
        ]);

        return $this->priceListStore($request);
    }

    /**
     * Reimplement Sp_List_tr_TGUPriceList_Supplier / _SupplierCode
     * tanpa EXEC (pure query builder).
     */
    private function priceListBySupplier($conn, string $branch, string $supplier, string $search, int $perPage): array
    {
        $rows      = collect();
        $paginator = null;

        // Selalu jalankan (panel ini mode "Update by Supplier" ditampilkan duluan
        // oleh GridControlPriceBySupplier). Bila list terlalu besar, user harus
        // memfilter via branch/supplier/search.
        // Catatan: CTE tidak boleh berada di dalam derived-table SQL Server
        // (paginate() membungkus query ke `select count(*) from (...)`).
        // Karena itu CTE diganti dengan inline derived-table.
        $rawSub = <<<'SQL'
            SELECT
                ISNULL(a.tr_TGU_pricelist_code_h, '')   AS tr_TGU_pricelist_code_h,
                ISNULL(a.Branch, '')                    AS Branch,
                ISNULL(e.cab_desc, '')                  AS cab_desc,
                ISNULL(a.MsSupplier_code, '')           AS MsSupplier_code,
                ISNULL(f.supp_desc, '')                 AS supp_desc,
                ISNULL(a.SKU_code, '')                  AS SKU_code,
                ISNULL(a.SKU_Supplier, '')              AS SKU_Supplier,
                ISNULL(i.SKU_description, '')           AS description,
                ISNULL(c.Price_pcs, 0)                  AS Price_pcs,
                ISNULL(c.Price_ctn, 0)                  AS Price_ctn,
                ISNULL(c.ms_unit_code, '')              AS ms_unit_code,
                ISNULL(h.unit_desc, 'NONE')             AS unit_desc,
                ISNULL(i.SKU_MOP, '')                   AS moq,
                ISNULL(b.Price_pcs, a.Price_pcs)        AS last_price,
                ISNULL(c.LowestPrice, 0)                AS LowestPrice,
                ISNULL(c.HighPrice, 0)                  AS HighPrice,
                ISNULL(c.AveragePrice, 0)               AS AveragePrice
            FROM (
                SELECT * FROM (
                    SELECT
                        a.tr_TGU_pricelist_code_h,
                        a.Branch,
                        a.MsSupplier_code,
                        a.rec_datecreated,
                        b.SKU_code,
                        b.SKU_Supplier,
                        ISNULL(b.Price_pcs, 0) AS Price_pcs,
                        ROW_NUMBER() OVER (
                            PARTITION BY b.SKU_code, a.Branch, b.SKU_Supplier
                            ORDER BY a.rec_datecreated DESC
                        ) AS RowNum
                    FROM tr_TGUPriceList a
                    LEFT JOIN tr_TGUPricelist_d b
                        ON a.tr_TGU_pricelist_code_h = b.pricelist_code_h
                ) x WHERE x.RowNum = 1
            ) a
            LEFT JOIN (
                SELECT * FROM (
                    SELECT
                        a.tr_TGU_pricelist_code_h,
                        a.Branch,
                        a.MsSupplier_code,
                        a.rec_datecreated,
                        b.SKU_code,
                        b.SKU_Supplier,
                        ISNULL(b.Price_pcs, 0) AS Price_pcs,
                        ROW_NUMBER() OVER (
                            PARTITION BY b.SKU_code, a.Branch, b.SKU_Supplier
                            ORDER BY a.rec_datecreated DESC
                        ) AS RowNum
                    FROM tr_TGUPriceList a
                    LEFT JOIN tr_TGUPricelist_d b
                        ON a.tr_TGU_pricelist_code_h = b.pricelist_code_h
                ) y WHERE y.RowNum = 2
            ) b
                ON a.Branch = b.Branch
               AND a.MsSupplier_code = b.MsSupplier_code
               AND a.SKU_code = b.SKU_code
            LEFT JOIN TGU_ms_product_price c
                ON a.Branch = c.Branch
               AND a.MsSupplier_code = c.Supplier_code
               AND a.SKU_code = c.SKU_code
            LEFT JOIN ms_cabang e   ON a.Branch = e.cab_code
            LEFT JOIN ms_supplier f ON a.MsSupplier_code = f.supp_code
            LEFT JOIN tgu_ms_product_internal g ON a.SKU_code = g.SKUInternal_code
            LEFT JOIN ms_unit h     ON c.ms_unit_code = h.unit_code
            LEFT JOIN tgu_ms_product_Business i
                ON g.SKUInternal_code = i.SKU_master
               AND a.SKU_Supplier      = i.SKU_Business
            GROUP BY
                a.Branch, a.MsSupplier_code, a.SKU_code, a.SKU_Supplier,
                c.LowestPrice, a.Price_pcs, b.Price_pcs, c.Price_pcs,
                c.Price_ctn, c.ms_unit_code, h.unit_desc, e.cab_desc,
                f.supp_desc, i.SKU_description, i.SKU_MOP,
                b.tr_TGU_pricelist_code_h, a.tr_TGU_pricelist_code_h,
                c.HighPrice, c.AveragePrice
            SQL;

        $query = $conn->query()
            ->fromRaw("($rawSub) as p")
            ->orderBy('p.cab_desc')
            ->orderBy('p.supp_desc')
            ->orderBy('p.SKU_code');

        if ($branch !== '') {
            $query->where('p.Branch', $branch);
        }
        if ($supplier !== '') {
            $query->where('p.MsSupplier_code', $supplier);
        }
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('p.SKU_code',     'like', $like)
                  ->orWhere('p.SKU_Supplier', 'like', $like)
                  ->orWhere('p.description',  'like', $like);
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'sup_page')->withQueryString();

        $offset = ($paginator->currentPage() - 1) * $paginator->perPage();
        $rows = collect($paginator->items())->map(function ($r, $i) use ($offset) {
            return [
                'no'                       => $offset + $i + 1,
                'tr_TGU_pricelist_code_h'  => $r->tr_TGU_pricelist_code_h,
                'Branch'                   => $r->Branch,
                'cab_desc'                 => $r->cab_desc,
                'MsSupplier_code'          => $r->MsSupplier_code,
                'supp_desc'                => $r->supp_desc,
                'SKU_code'                 => $r->SKU_code,
                'SKU_Supplier'             => $r->SKU_Supplier,
                'description'              => $r->description,
                'Price_pcs'                => $r->Price_pcs,
                'Price_ctn'                => $r->Price_ctn,
                'ms_unit_code'             => $r->ms_unit_code,
                'unit_desc'                => $r->unit_desc,
                'moq'                      => $r->moq,
                'last_price'               => $r->last_price,
                'LowestPrice'              => $r->LowestPrice,
                'HighPrice'                => $r->HighPrice,
                'AveragePrice'             => $r->AveragePrice,
            ];
        })->values();

        return ['rows' => $rows, 'paginator' => $paginator];
    }

    /**
     * Reimplement Sp_Get_TGU_ms_product_price_SKU_code,
     *            Sp_get_TGU_ms_product_price_description,
     *            Sp_Get_tgu_ms_product_price_branch_supplier
     * tanpa EXEC.
     *
     * Untuk setiap row hasil, C# tambahan lookup:
     *   - Ms_Cabang.DAL_Load(branch)               → cab_desc
     *   - tgu_ms_product_Business.DAL_LoadMasterBusiness(sku, sku_supplier)
     *                                              → SKU_description, SKU_MOP
     *   - Ms_Unit.DAL_Load(unit)                   → unit_desc
     *   - ms_supplier.DAL_Load(supplier)           → supp_desc
     * Semua lookup ini sudah di-JOIN di SQL berikut sehingga 1× round-trip.
     */
    private function priceListBySku($conn, string $branch, string $supplier, string $skuCode, string $skuDesc, int $perPage): array
    {
        $rows      = collect();
        $paginator = null;

        // C# init GridControlPriceBySKU() membuat tabel kosong; data baru muncul
        // setelah user mengisi salah satu textE_SKU / textE_Product / btnSearchSKU.
        $hasFilter = ($branch !== '' || $supplier !== '' || $skuCode !== '' || $skuDesc !== '');
        if (!$hasFilter) {
            return ['rows' => $rows, 'paginator' => null];
        }

        $query = $conn->table(DB::raw('(
                SELECT *,
                       ROW_NUMBER() OVER (
                           PARTITION BY Branch, SKU_code, SKU_Supplier, Supplier_code
                           ORDER BY [Date] DESC
                       ) AS rn_latest
                FROM TGU_ms_product_price
            ) as p'))
            ->where('p.rn_latest', 1)
            ->leftJoin('ms_cabang as e',   'p.Branch',        '=', 'e.cab_code')
            ->leftJoin('ms_supplier as f', 'p.Supplier_code', '=', 'f.supp_code')
            ->leftJoin('ms_unit as h',     'p.ms_unit_code',  '=', 'h.unit_code')
            ->leftJoin(DB::raw('(
                SELECT SKU_master, SKU_Business, SKU_description, SKU_MOP,
                       ROW_NUMBER() OVER (
                           PARTITION BY SKU_master, SKU_Business
                           ORDER BY SKU_default DESC
                       ) AS rn_pb
                FROM tgu_ms_product_Business
            ) as i'), function ($j) {
                $j->on('p.SKU_code',     '=', 'i.SKU_master')
                  ->on('p.SKU_Supplier', '=', 'i.SKU_Business')
                  ->where('i.rn_pb', '=', 1);
            })
            ->select(
                'p.Branch',
                DB::raw("ISNULL(e.cab_desc, '')        AS cab_desc"),
                'p.SKU_code',
                'p.SKU_Supplier',
                DB::raw("ISNULL(i.SKU_description, '') AS description"),
                'p.ms_unit_code',
                DB::raw("ISNULL(h.unit_desc, 'NONE')   AS unit_desc"),
                DB::raw('ISNULL(p.Price_pcs, 0)        AS Price_pcs'),
                DB::raw('ISNULL(p.Price_ctn, 0)        AS Price_ctn'),
                DB::raw("ISNULL(i.SKU_MOP, '')         AS moq"),
                'p.Supplier_code',
                DB::raw("ISNULL(f.supp_desc, '')       AS supp_desc"),
                'p.Date',
                DB::raw('ISNULL(p.LowestPrice, 0)      AS LowestPrice'),
                DB::raw('ISNULL(p.HighPrice, 0)        AS HighPrice'),
                DB::raw('ISNULL(p.AveragePrice, 0)     AS AveragePrice')
            )
            ->orderBy('e.cab_desc')
            ->orderBy('p.SKU_code');

        // Sp_Get_tgu_ms_product_price_branch_supplier
        if ($branch !== '') {
            $query->where('p.Branch', $branch);
        }
        if ($supplier !== '') {
            $query->where('p.Supplier_code', $supplier);
        }
        // Sp_Get_TGU_ms_product_price_SKU_code  (LIKE supaya seperti EditValueChanged C#)
        if ($skuCode !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $skuCode) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('p.SKU_code',     'like', $like)
                  ->orWhere('p.SKU_Supplier', 'like', $like);
            });
        }
        // Sp_get_TGU_ms_product_price_description
        if ($skuDesc !== '') {
            $likeDesc = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $skuDesc) . '%';
            $query->where('i.SKU_description', 'like', $likeDesc);
        }

        $paginator = $query->paginate($perPage, ['*'], 'sku_page')->withQueryString();

        $offset = ($paginator->currentPage() - 1) * $paginator->perPage();
        $rows = collect($paginator->items())->map(function ($r, $i) use ($offset) {
            return [
                'no'            => $offset + $i + 1,
                'Branch'        => $r->Branch,
                'cab_desc'      => $r->cab_desc,
                'SKU_code'      => $r->SKU_code,
                'SKU_Supplier'  => $r->SKU_Supplier,
                'description'   => $r->description,
                'unit_desc'     => $r->unit_desc,
                'Price_pcs'     => $r->Price_pcs,
                'Price_ctn'     => $r->Price_ctn,
                'moq'           => $r->moq,
                'Supplier_code' => $r->Supplier_code,
                'supp_desc'     => $r->supp_desc,
                'date'          => $r->Date,
                'LowestPrice'   => $r->LowestPrice,
                'HighPrice'     => $r->HighPrice,
                'AveragePrice'  => $r->AveragePrice,
            ];
        })->values();

        return ['rows' => $rows, 'paginator' => $paginator];
    }

    // ============================================================================
    // ============== ADD NEW PRICELIST (mirror C# Formaddupdatepricelist) ========
    // ============================================================================

    /**
     * GET /gudang/price-list/create
     * Mirror constructor C#: Formaddupdatepricelist(caller, supplier, type, branch, principal)
     * Menyiapkan dropdown supplier, branch, principal/business, pricemode, unit.
     */
    public function priceListCreate(Request $request)
    {
        $conn = DB::connection('rcm_ol_tgu');

        $cabangList = $conn->table('ms_cabang')
            ->select('cab_code', 'cab_desc')->orderBy('cab_desc')->get();

        $supplierList = $conn->table('ms_supplier')
            ->select('supp_code', 'supp_desc')->orderBy('supp_desc')->get();

        // Daftar Principal/Business (distinct dari tgu_ms_product_Business)
        $businessList = $conn->table('tgu_ms_product_Business')
            ->select('Business')->whereNotNull('Business')->where('Business', '<>', '')
            ->distinct()->orderBy('Business')->get();

        $pricemodeList = $conn->table('TGU_ms_pricemode')
            ->select('ms_pricemode', 'pricemode')->orderBy('pricemode')->get();

        $unitList = $conn->table('ms_unit')
            ->select('unit_code', 'unit_desc')->orderBy('unit_desc')->get();

        return view('gudang.price_list_form', [
            'cabangList'    => $cabangList,
            'supplierList'  => $supplierList,
            'businessList'  => $businessList,
            'pricemodeList' => $pricemodeList,
            'unitList'      => $unitList,
            'prefill'       => [
                'sup_branch'   => trim((string) $request->input('sup_branch', '')),
                'sup_supplier' => trim((string) $request->input('sup_supplier', '')),
            ],
        ]);
    }

    /**
     * GET /gudang/price-list/lookup-sku?business=&q=
     * Mirror C# FillgridProductBusiness(business) → tgu_ms_product_BusinessCollection.DAL_LoadBusiness
     * Dipakai oleh autocomplete SKU Supplier di form.
     */
    public function priceListLookupSku(Request $request)
    {
        $conn = DB::connection('rcm_ol_tgu');
        $business = trim((string) $request->input('business', ''));
        $q        = trim((string) $request->input('q', ''));

        $query = $conn->table('tgu_ms_product_Business')
            ->select(
                'SKU_Business',
                'SKU_master',
                'SKU_description',
                'Business',
                'SKU_convertpcs',
                'SKU_statusPPN',
                'SKU_MOP'
            )
            ->orderBy('SKU_Business')
            ->limit(50);

        if ($business !== '') {
            $query->where('Business', $business);
        }
        if ($q !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where(function ($w) use ($like) {
                $w->where('SKU_Business', 'like', $like)
                  ->orWhere('SKU_master', 'like', $like)
                  ->orWhere('SKU_description', 'like', $like);
            });
        }
        return response()->json($query->get());
    }

    public function priceListLookupPriceMode(Request $request)
    {
        return response()->json(
            DB::connection('rcm_ol_tgu')->table('TGU_ms_pricemode')
                ->select('ms_pricemode', 'pricemode')->orderBy('pricemode')->get()
        );
    }

    public function priceListLookupUnit(Request $request)
    {
        return response()->json(
            DB::connection('rcm_ol_tgu')->table('ms_unit')
                ->select('unit_code', 'unit_desc')->orderBy('unit_desc')->get()
        );
    }

    /**
     * POST /gudang/price-list/store
     *
     * Mirror C# Formaddupdatepricelist.simpleButton_Confirm_Click (mode "Confirm")
     * Logika identik dengan SP-SP berikut, tapi DI-CODE (tanpa EXEC):
     *   - Sp_Add_tr_TGUPriceList1                        → INSERT tr_TGUPriceList
     *   - Sp_add_tr_TGUPricelist_d                       → INSERT tr_TGUPricelist_d (loop)
     *   - Sp_list_tr_tgupricelist_d_last_record          → SELECT last price (loop)
     *   - Sp_LoadMasterSKUSupplier / Sp_Add_/Sp_Update_  → UPSERT tgu_ms_product_supplier
     *   - Sp_Get_tgu_ms_product_price_branch_supplier... → UPSERT TGU_ms_product_price
     *   - Sp_Update_TGU_ms_product_Business_hargajual    → UPDATE tgu_ms_product_Business
     */
    public function priceListStore(Request $request)
    {
        $validated = $request->validate([
            'description'   => 'required|string|max:50',
            'operator'      => 'required|string|max:50',
            'supplier_code' => 'required|string|max:50',
            'branch_code'   => 'required|string|max:50',
            'principal'     => 'required|string|max:50',
            'type'          => 'required|string|max:50',
            'pricelist_date'=> 'nullable|date',
            'details'                              => 'required|array|min:1',
            'details.*.SKU_code'                   => 'required|string|max:50',
            'details.*.SKU_Supplier'               => 'required|string|max:50',
            'details.*.MOQ'                        => 'nullable|string|max:50',
            'details.*.MsPriceMode_code'           => 'nullable|string|max:50',
            'details.*.ms_unit_code'               => 'nullable|string|max:50',
            'details.*.OngkosAngkut'               => 'nullable|numeric',
            'details.*.Price_pcs'                  => 'nullable|numeric',
            'details.*.Price_ctn'                  => 'nullable|numeric',
            'details.*.price_pcs_jual'             => 'nullable|numeric',
            'details.*.price_ctn_jual'             => 'nullable|numeric',
            'details.*.ExpDate'                    => 'nullable|date',
            'details.*.date_start'                 => 'required|date',
            'details.*.discount_percentage'        => 'nullable|numeric',
            'details.*.discount_whole_seller'      => 'nullable|numeric',
            'details.*.discount_toko'              => 'nullable|numeric',
            'details.*.discount_star_outlet'       => 'nullable|numeric',
            'details.*.discount_semi_wholeseller'  => 'nullable|numeric',
            'details.*.discount_special'           => 'nullable|numeric',
        ]);

        $conn = DB::connection('rcm_ol_tgu');
        $user = auth()->user()->name ?? auth()->user()->email ?? 'web';
        $now  = now();

        // ===== Generate tr_TGU_pricelist_code_h =====
        // Mirror Sp_Add_tr_TGUPriceList1 yang mengembalikan code baru.
        // Pola: PL + yymmdd + 4-digit-seq harian (urut bertambah)
        $prefix = 'PL' . $now->format('ymd');
        $maxCode = $conn->table('tr_TGUPriceList')
            ->where('tr_TGU_pricelist_code_h', 'like', $prefix . '%')
            ->max('tr_TGU_pricelist_code_h');
        $seq = 1;
        if ($maxCode) {
            $tail = (int) substr($maxCode, strlen($prefix));
            $seq = $tail + 1;
        }
        $codeH = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        // Cek duplikat (mirror tgupricelist.DAL_LoadCode)
        if ($conn->table('tr_TGUPriceList')
                 ->where('tr_TGU_pricelist_code_h', $codeH)
                 ->where('rec_status', '<>', '0')
                 ->exists()) {
            return back()->withErrors(['code' => 'ID Pricelist already exists. Please retry.'])
                         ->withInput();
        }

        try {
            $conn->transaction(function () use (
                $conn, $validated, $codeH, $user, $now
            ) {
                // ============== 1) INSERT tr_TGUPriceList (header) ==================
                // Sp_Add_tr_TGUPriceList1
                $conn->table('tr_TGUPriceList')->insert([
                    'rec_usercreated'         => $user,
                    'rec_userupdate'          => $user,
                    'rec_datecreated'         => $now,
                    'rec_dateupdate'          => $now,
                    'rec_status'              => '1',
                    'tr_TGU_pricelist_code_h' => $codeH,
                    'date_update'             => $now,
                    'Operator'                => $validated['operator'],
                    'MsSupplier_code'         => $validated['supplier_code'],
                    'Pricipal_code'           => $validated['principal'],
                    'pricelist_desc'          => $validated['description'],
                    'Branch'                  => $validated['branch_code'],
                    'Type'                    => $validated['type'],
                ]);

                // Loop tiap detail row
                foreach ($validated['details'] as $idx => $row) {
                    $sku        = $row['SKU_code'];
                    $skuSupp    = $row['SKU_Supplier'];
                    $priceCtn   = (float) ($row['Price_ctn']      ?? 0);
                    $pricePcs   = (float) ($row['Price_pcs']      ?? 0);
                    $pricePcsJ  = (float) ($row['price_pcs_jual'] ?? 0);
                    $priceCtnJ  = (float) ($row['price_ctn_jual'] ?? 0);

                    // ====== last price lookup (mirror DAL_Load_last_tr → Sp_list_tr_tgupricelist_d_last_record)
                    $last = $conn->table('tr_TGUPricelist_d as d')
                        ->join('tr_TGUPriceList as h', 'd.pricelist_code_h', '=', 'h.tr_TGU_pricelist_code_h')
                        ->where('d.SKU_Supplier', $skuSupp)
                        ->where('d.SKU_code',     $sku)
                        ->where('h.Pricipal_code', $validated['principal'])
                        ->orderByDesc('d.Date_update')
                        ->select('d.Price_pcs', 'd.Price_ctn', 'd.pricelist_code_d')
                        ->first();

                    $lastPricePcs = $last ? (float) $last->Price_pcs : 0;
                    $lastPriceCtn = $last ? (float) $last->Price_ctn : 0;
                    $lastCodeD    = $last ? (string) $last->pricelist_code_d : '';

                    // ====== 2) INSERT tr_TGUPricelist_d (Sp_add_tr_TGUPricelist_d)
                    $codeD = $codeH . '-' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
                    $conn->table('tr_TGUPricelist_d')->insert([
                        'pricelist_code_h'  => $codeH,
                        'pricelist_code_d'  => $codeD,
                        'SKU_code'          => $sku,
                        'SKU_Supplier'      => $skuSupp,
                        'Price_ctn'         => $priceCtn,
                        'Date_update'       => $now,
                        'MOQ'               => $row['MOQ']              ?? '',
                        'ms_unit_code'      => $row['ms_unit_code']     ?? '',
                        'MsPriceMode_code'  => $row['MsPriceMode_code'] ?? '',
                        'OngkosAngkut'      => (float) ($row['OngkosAngkut'] ?? 0),
                        'Price_pcs'         => $pricePcs,
                        'price_pcs_before'  => $lastPricePcs,
                        'price_ctn_before'  => $lastPriceCtn,
                        'code_before'       => $lastCodeD,
                        'price_pcs_after'   => 0,
                        'price_ctn_after'   => 0,
                        'code_after'        => '',
                        'date_start'        => $row['date_start'],
                        'date_end'          => null,
                        'active_last'       => '1',
                    ]);

                    // ====== 3) UPSERT tgu_ms_product_Supplier
                    // Mirror DAL_LoadMasterSKUSupplier (Sp_Get_tgu_ms_product_Supplier_MasterSupplierVendor)
                    //   → ada  : DAL_Update (Sp_Update_tgu_ms_product_Supplier)
                    //   → tidak: DAL_Add    (Sp_Add_tgu_ms_product_Supplier)
                    // Catatan: kolom DB di reader C# = "SKU_master" (m kecil)
                    $exists = $conn->table('tgu_ms_product_Supplier')
                        ->where('SKU_master',   $sku)
                        ->where('SKU_Supplier', $skuSupp)
                        ->where('Vendor',       $validated['supplier_code'])
                        ->exists();
                    if ($exists) {
                        // C# DAL_Update menulis: rec_userupdate, rec_dateupdate, rec_status,
                        // SKU_Master, SKU_Supplier, Vendor, Temp_SKU_Supplier, Temp_Supplier
                        $conn->table('tgu_ms_product_Supplier')
                            ->where('SKU_master',   $sku)
                            ->where('SKU_Supplier', $skuSupp)
                            ->where('Vendor',       $validated['supplier_code'])
                            ->update([
                                'rec_userupdate'    => $user,
                                'rec_dateupdate'    => $now,
                                'rec_status'        => '1',
                                'SKU_master'        => $sku,
                                'SKU_Supplier'      => $skuSupp,
                                'Vendor'            => $validated['supplier_code'],
                                'Temp_SKU_Supplier' => $skuSupp,
                                'Temp_Supplier'     => $validated['supplier_code'],
                            ]);
                    } else {
                        // C# DAL_Add menulis: rec_*, SKU_Master, SKU_Supplier, Vendor
                        $conn->table('tgu_ms_product_Supplier')->insert([
                            'rec_usercreated' => $user,
                            'rec_userupdate'  => $user,
                            'rec_datecreated' => $now,
                            'rec_dateupdate'  => $now,
                            'rec_status'      => '1',
                            'SKU_master'      => $sku,
                            'SKU_Supplier'    => $skuSupp,
                            'Vendor'          => $validated['supplier_code'],
                        ]);
                    }

                    // ====== 4) UPSERT TGU_ms_product_price
                    // Mirror DAL_LoadSupplierSKU + cek match → Update / Add
                    $exp = !empty($row['ExpDate']) ? $row['ExpDate'] : $now;
                    $existsPrice = $conn->table('TGU_ms_product_price')
                        ->where('SKU_code',      $sku)
                        ->where('Supplier_code', $validated['supplier_code'])
                        ->where('Branch',        $validated['branch_code'])
                        ->where('SKU_Supplier',  $skuSupp)
                        ->where('principal_code', $validated['principal'])
                        ->exists();

                    $priceData = [
                        'rec_userupdate'  => $user,
                        'rec_dateupdate'  => $now,
                        'rec_status'      => '1',
                        'SKU_code'        => $sku,
                        'SKU_Supplier'    => $skuSupp,
                        'Price_ctn'       => $priceCtn,
                        'price_ctn_jual'  => $priceCtnJ,
                        'Supplier_code'   => $validated['supplier_code'],
                        'Date'            => $now,
                        'MsStatusPrice'   => 'Active',
                        'ExpDate'         => $exp,
                        'principal_code'  => $validated['principal'],
                        'Price_pcs'       => $pricePcs,
                        'price_pcs_jual'  => $pricePcsJ,
                        'ms_unit_code'    => $row['ms_unit_code'] ?? '',
                        'Branch'          => $validated['branch_code'],
                        'discount_percentage'        => (float) ($row['discount_percentage']        ?? 0),
                        'discount_whole_seller'      => (float) ($row['discount_whole_seller']      ?? 0),
                        'discount_toko'              => (float) ($row['discount_toko']              ?? 0),
                        'discount_star_outlet'       => (float) ($row['discount_star_outlet']       ?? 0),
                        'discount_semi_wholeseller'  => (float) ($row['discount_semi_wholeseller']  ?? 0),
                        'discount_special'           => (float) ($row['discount_special']           ?? 0),
                    ];

                    if ($existsPrice) {
                        // Mirror branch C# (existing): LowestPrice/High/Avg = 0 (tetap), RetailPrice tetap.
                        $priceData['LowestPrice']  = 0;
                        $priceData['HighPrice']    = 0;
                        $priceData['AveragePrice'] = 0;
                        $conn->table('TGU_ms_product_price')
                            ->where('SKU_code',      $sku)
                            ->where('Supplier_code', $validated['supplier_code'])
                            ->where('Branch',        $validated['branch_code'])
                            ->where('SKU_Supplier',  $skuSupp)
                            ->where('principal_code', $validated['principal'])
                            ->update($priceData);
                    } else {
                        // Mirror branch C# (new): Lowest/High/Avg = Price_pcs, RetailPrice=0
                        $priceData['rec_usercreated'] = $user;
                        $priceData['rec_datecreated'] = $now;
                        $priceData['LowestPrice']  = $pricePcs;
                        $priceData['HighPrice']    = $pricePcs;
                        $priceData['AveragePrice'] = $pricePcs;
                        $priceData['RetailPrice']  = 0;
                        $conn->table('TGU_ms_product_price')->insert($priceData);
                    }

                    // ====== 5) UPDATE tgu_ms_product_Business — harga jual & beli
                    // Mirror DAL_LoadSKUBusiness2 + DAL_Update2
                    $conn->table('tgu_ms_product_Business')
                        ->where('SKU_Business', $skuSupp)
                        ->where('Business',     $validated['principal'])
                        ->update([
                            'rec_userupdate'    => $user,
                            'rec_dateupdate'    => $now,
                            'SKU_Hargajual_pcs' => $pricePcsJ,
                            'SKU_Hargajual_ctn' => $priceCtnJ,
                            'SKU_Hargabeli_pcs' => $pricePcs,
                            'SKU_Hargabeli_ctn' => $priceCtn,
                        ]);
                }
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['save' => 'Gagal menyimpan: ' . $e->getMessage()])
                         ->withInput();
        }

        return redirect()
            ->route('gudang.price-list', ['sup_branch' => $validated['branch_code'], 'sup_supplier' => $validated['supplier_code']])
            ->with('success', "Pricelist {$codeH} berhasil disimpan dan harga jual ke Product Bisnis ter-update.");
    }

    /**
     * Salinan logika di C#: PrintPreviewReportRekapStockRack.DRE_Click → blok "Filer rack".
     */
    private function mapGudangMaster(string $gudang): string
    {
        if ($gudang === 'Heinz') {
            return 'Heinz';
        }
        if ($gudang === 'Mitra Bukalapak' || $gudang === 'Bukamart') {
            return 'Bukalapak';
        }
        if ($gudang === 'Unilever' || $gudang === 'Trading' || $gudang === 'B2B') {
            return 'Trading';
        }
        return $gudang;
    }

    /**
     * Halaman Track In / Out gudang.
     *
     * Sumber data tabel:
     *   - tr_tgu_purchase_order_parcial_h (PO)
     *       po_code  : tgu_purchase_order_h_code
     *       po_date  : tgu_purchase_order_date
     *   - wdms_tr_delivery_orders_h        (jembatan PO -> Tallysheet)
     *       link PO         : do_h_po_code
     *   - wdms_tr_tallysheet_h             (Tallysheet)
     *       tls_no   : tls_h_code
     *       tls_date : completed_at
     *       link DO  : (tls_h_code = wdms_tr_delivery_orders_h join field)
     *   - wdms_tr_btb_h                    (BTB)
     *       btb_no   : btb_h_code
     *       btb_date : btb_h_received_at
     *       link TLS : btb_h_tallysheet_code -> tls_h_code
     *
     * Rack: dibiarkan kosong dulu.
     */
    public function trackInOut(Request $request)
    {
        $conn = DB::connection('rcm_ol_tgu');

        $search   = trim((string) $request->input('search', ''));
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo   = trim((string) $request->input('date_to', ''));
        $allowed  = [25, 50, 100, 200];
        $perPage  = (int) $request->input('per_page', 50);
        if (!in_array($perPage, $allowed, true)) {
            $perPage = 50;
        }

        $query = $conn->table('tr_tgu_purchase_order_parcial_h as po')
            ->leftJoin('wdms_tr_delivery_orders_h as do', 'do.do_h_po_code', '=', 'po.tgu_purchase_order_h_code')
            ->leftJoin('wdms_tr_tallysheet_h as tls', 'tls.tls_h_no_do', '=', 'do.do_h_code')
            ->leftJoin('tgu_tr_inv_insupp_h as btb', 'btb.inv_insupp_dono', '=', 'tls.tls_h_no_do')
            ->leftJoin(DB::raw('(
                SELECT tr_inv_rack_code,
                       ISNULL(QTY_in, 0) + ISNULL(QTY_out, 0) AS qty
                FROM (
                    SELECT tr_inv_rack_code, QTY_in, QTY_out,
                           ROW_NUMBER() OVER (PARTITION BY tr_inv_rack_code ORDER BY rec_datecreated DESC) AS rn
                    FROM tgu_tr_inv_main_mutasi_rack
                ) x WHERE x.rn = 1
            ) as mr'), 'mr.tr_inv_rack_code', '=', 'tls.tls_h_code')
            ->select([
                'po.tgu_purchase_order_h_code as po_no',
                'po.rec_dateupdate            as po_date',
                'tls.tls_h_code               as tallysheet_no',
                'tls.completed_at             as tallysheet_date',
                'btb.inv_insupp_code          as btb_no',
                'btb.rec_dateupdate           as btb_date',
                'btb.inv_insupp_inv_incode    as putaway',
                'mr.qty                       as qty',
            ])
            ->orderByDesc('po.tgu_purchase_order_date')
            ->orderBy('po.tgu_purchase_order_h_code');

        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('po.tgu_purchase_order_h_code', 'like', $like)
                  ->orWhere('tls.tls_h_code',             'like', $like)
                  ->orWhere('btb.inv_insupp_code',        'like', $like)
                  ->orWhere('btb.inv_insupp_inv_incode',  'like', $like)
                  ->orWhereRaw("CONVERT(VARCHAR(10), po.tgu_purchase_order_date, 120) LIKE ?", [$like])
                  ->orWhereRaw("CONVERT(VARCHAR(19), tls.completed_at, 120) LIKE ?", [$like])
                  ->orWhereRaw("CONVERT(VARCHAR(19), btb.inv_insupp_date, 120) LIKE ?", [$like])
                  ->orWhereRaw("CAST(mr.qty AS VARCHAR(30)) LIKE ?", [$like]);
            });
        }

        if ($dateFrom !== '') {
            $query->whereRaw('CAST(po.tgu_purchase_order_date AS DATE) >= ?', [$dateFrom]);
        }
        if ($dateTo !== '') {
            $query->whereRaw('CAST(po.tgu_purchase_order_date AS DATE) <= ?', [$dateTo]);
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        $rows = collect($paginator->items())->map(function ($r) {
            return (object) [
                'po_no'           => $r->po_no,
                'po_date'         => $r->po_date ? date('Y-m-d', strtotime((string) $r->po_date)) : '',
                'tallysheet_no'   => $r->tallysheet_no,
                'tallysheet_date' => $r->tallysheet_date ? date('Y-m-d H:i', strtotime((string) $r->tallysheet_date)) : '',
                'btb_no'          => $r->btb_no,
                'btb_date'        => $r->btb_date ? date('Y-m-d H:i', strtotime((string) $r->btb_date)) : '',
                'putaway'         => $r->putaway ?? '',
                'qty'             => $r->qty ?? '',
            ];
        });

        // ===== OUT panel =====
        // Sumber:
        //   tgu_tr_inv_req_h
        //     request_no   : req_part_h_code
        //     request_date : req_so_date
        // Kolom lain (Picking, BKB, Dispatch, POD, BTB RV, Payment Kasir) menyusul.
        $searchOut    = trim((string) $request->input('search_out', ''));
        $dateFromOut  = trim((string) $request->input('date_from_out', ''));
        $dateToOut    = trim((string) $request->input('date_to_out', ''));
        $perPageOut   = (int) $request->input('per_page_out', 50);
        if (!in_array($perPageOut, $allowed, true)) {
            $perPageOut = 50;
        }

        $queryOut = $conn->table('tgu_pickinglist_rack_h as pl')
            ->leftJoin('tgu_tr_inv_req_h as r', 'r.req_part_h_code', '=', 'pl.pl_request')
            ->leftJoin('tgu_tr_inv_out_h as bkb', 'bkb.bkb_code', '=', 'pl.pl_rack_code')
            ->leftJoin(DB::raw('(
                SELECT invinreturvh_bkbcode,
                       MAX(invinreturvh_code) AS invinreturvh_code,
                       MAX(invinreturvh_date) AS invinreturvh_date,
                       MAX(rec_dateupdate)    AS rec_dateupdate
                FROM tgu_tr_invinreturvh_h
                GROUP BY invinreturvh_bkbcode
            ) as btbrv'), 'btbrv.invinreturvh_bkbcode', '=', 'bkb.inv_out_code')
            ->leftJoin('tgu_dispatch_main as dp', 'dp.dpcth_code_h', '=', 'pl.pl_dispaching_h')
            ->leftJoin(DB::raw('(
                SELECT dpcth_code_h,
                       MIN(dptch_date)      AS dptch_date,
                       MAX(rec_dateupdate)  AS rec_dateupdate
                FROM tgu_dispatch_h
                GROUP BY dpcth_code_h
            ) as pod'), 'pod.dpcth_code_h', '=', 'dp.dpcth_code_h')
            ->leftJoin(DB::raw('(
                SELECT inv_dpcthcodeh,
                       MAX(inv_code)       AS inv_code,
                       MAX(rec_dateupdate) AS rec_dateupdate
                FROM tgu_tr_invoice_h_driver
                GROUP BY inv_dpcthcodeh
            ) as pay'), 'pay.inv_dpcthcodeh', '=', 'pod.dpcth_code_h')
            ->select([
                'pl.pl_request          as request_no',
                'r.rec_datecreated      as request_date',
                'pl.pl_rack_code        as picking_no',
                'pl.rec_dateupdate      as picking_date',
                'bkb.inv_out_code       as bkb_no',
                'bkb.rec_dateupdate     as bkb_date',
                'pl.pl_dispaching_h     as dispatch_no',
                'pod.rec_dateupdate     as dispatch_date',
                'pod.dpcth_code_h       as pod_no',
                'pod.rec_dateupdate     as pod_date',
                'btbrv.invinreturvh_code as btb_rv_no',
                'btbrv.rec_dateupdate    as btb_rv_date',
                'pay.inv_code            as payment_kasir_no',
                'pay.rec_dateupdate      as payment_kasir_date',
            ])
            ->orderByDesc('pl.pl_rack_date')
            ->orderBy('pl.pl_rack_code');

        if ($searchOut !== '') {
            $likeOut = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $searchOut) . '%';
            $queryOut->where(function ($q) use ($likeOut) {
                $q->where('pl.pl_request', 'like', $likeOut)
                  ->orWhere('pl.pl_rack_code', 'like', $likeOut)
                  ->orWhere('pl.pl_dispaching_h', 'like', $likeOut)
                  ->orWhere('bkb.inv_out_code', 'like', $likeOut)
                  ->orWhere('btbrv.invinreturvh_code', 'like', $likeOut)
                  ->orWhere('pod.dpcth_code_h', 'like', $likeOut)
                  ->orWhere('pay.inv_code', 'like', $likeOut)
                  ->orWhereRaw("CONVERT(VARCHAR(19), r.rec_datecreated, 120) LIKE ?", [$likeOut])
                  ->orWhereRaw("CONVERT(VARCHAR(19), pl.rec_dateupdate, 120) LIKE ?", [$likeOut])
                  ->orWhereRaw("CONVERT(VARCHAR(19), bkb.rec_dateupdate, 120) LIKE ?", [$likeOut])
                  ->orWhereRaw("CONVERT(VARCHAR(19), pod.rec_dateupdate, 120) LIKE ?", [$likeOut])
                  ->orWhereRaw("CONVERT(VARCHAR(19), pod.dptch_date, 120) LIKE ?", [$likeOut])
                  ->orWhereRaw("CONVERT(VARCHAR(19), btbrv.invinreturvh_date, 120) LIKE ?", [$likeOut])
                  ->orWhereRaw("CONVERT(VARCHAR(19), btbrv.rec_dateupdate, 120) LIKE ?", [$likeOut])
                  ->orWhereRaw("CONVERT(VARCHAR(19), pay.rec_dateupdate, 120) LIKE ?", [$likeOut]);
            });
        }

        if ($dateFromOut !== '') {
            $queryOut->whereRaw('CAST(r.rec_datecreated AS DATE) >= ?', [$dateFromOut]);
        }
        if ($dateToOut !== '') {
            $queryOut->whereRaw('CAST(r.rec_datecreated AS DATE) <= ?', [$dateToOut]);
        }

        $paginatorOut = $queryOut->paginate($perPageOut, ['*'], 'page_out')->withQueryString();

        $rowsOut = collect($paginatorOut->items())->map(function ($r) {
            return (object) [
                'request_no'         => $r->request_no,
                'request_date'       => $r->request_date ? date('Y-m-d H:i:s', strtotime((string) $r->request_date)) : '',
                'picking_no'         => $r->picking_no,
                'picking_date'       => $r->picking_date ? date('Y-m-d H:i:s', strtotime((string) $r->picking_date)) : '',
                'bkb_no'             => $r->bkb_no ?? '',
                'bkb_date'           => $r->bkb_date ? date('Y-m-d H:i:s', strtotime((string) $r->bkb_date)) : '',
                'dispatch_no'        => $r->dispatch_no,
                'dispatch_date'      => $r->dispatch_date ? date('Y-m-d H:i:s', strtotime((string) $r->dispatch_date)) : '',
                'pod_no'             => $r->pod_no,
                'pod_date'           => $r->pod_date ? date('Y-m-d H:i:s', strtotime((string) $r->pod_date)) : '',
                'btb_rv_no'          => $r->btb_rv_no ?? '',
                'btb_rv_date'        => $r->btb_rv_date ? date('Y-m-d H:i:s', strtotime((string) $r->btb_rv_date)) : '',
                'payment_kasir_no'   => $r->payment_kasir_no ?? '',
                'payment_kasir_date' => $r->payment_kasir_date ? date('Y-m-d H:i:s', strtotime((string) $r->payment_kasir_date)) : '',
            ];
        });

        return view('gudang.track_in_out', [
            'rows'         => $rows,
            'paginator'    => $paginator,
            'rowsOut'      => $rowsOut,
            'paginatorOut' => $paginatorOut,
            'filters'   => [
                'search'        => $search,
                'date_from'     => $dateFrom,
                'date_to'       => $dateTo,
                'per_page'      => $perPage,
                'search_out'    => $searchOut,
                'date_from_out' => $dateFromOut,
                'date_to_out'   => $dateToOut,
                'per_page_out'  => $perPageOut,
            ],
        ]);
    }

    /**
     * GET /gudang/track-in-out/print
     * Halaman cetak panel IN: semua baris dalam rentang tanggal PO yang dipilih.
     */
    public function trackInOutPrint(Request $request)
    {
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo   = trim((string) $request->input('date_to', ''));

        $query = DB::connection('rcm_ol_tgu')
            ->table('tr_tgu_purchase_order_parcial_h as po')
            ->leftJoin('wdms_tr_delivery_orders_h as do', 'do.do_h_po_code', '=', 'po.tgu_purchase_order_h_code')
            ->leftJoin('wdms_tr_tallysheet_h as tls', 'tls.tls_h_no_do', '=', 'do.do_h_code')
            ->leftJoin('tgu_tr_inv_insupp_h as btb', 'btb.inv_insupp_dono', '=', 'tls.tls_h_no_do')
            ->leftJoin(DB::raw('(
                SELECT tr_inv_rack_code,
                       ISNULL(QTY_in, 0) + ISNULL(QTY_out, 0) AS qty
                FROM (
                    SELECT tr_inv_rack_code, QTY_in, QTY_out,
                           ROW_NUMBER() OVER (PARTITION BY tr_inv_rack_code ORDER BY rec_datecreated DESC) AS rn
                    FROM tgu_tr_inv_main_mutasi_rack
                ) x WHERE x.rn = 1
            ) as mr'), 'mr.tr_inv_rack_code', '=', 'tls.tls_h_code')
            ->select([
                'po.tgu_purchase_order_h_code as po_no',
                'po.rec_dateupdate            as po_date',
                'tls.tls_h_code               as tallysheet_no',
                'tls.completed_at             as tallysheet_date',
                'btb.inv_insupp_code          as btb_no',
                'btb.rec_dateupdate           as btb_date',
                'btb.inv_insupp_inv_incode    as putaway',
                'mr.qty                       as qty',
            ])
            ->orderByDesc('po.tgu_purchase_order_date')
            ->orderBy('po.tgu_purchase_order_h_code');

        if ($dateFrom !== '') {
            $query->whereRaw('CAST(po.tgu_purchase_order_date AS DATE) >= ?', [$dateFrom]);
        }
        if ($dateTo !== '') {
            $query->whereRaw('CAST(po.tgu_purchase_order_date AS DATE) <= ?', [$dateTo]);
        }

        $rows = collect($query->get())->map(function ($r) {
            return (object) [
                'po_no'           => $r->po_no,
                'po_date'         => $r->po_date ? date('Y-m-d', strtotime((string) $r->po_date)) : '',
                'tallysheet_no'   => $r->tallysheet_no,
                'tallysheet_date' => $r->tallysheet_date ? date('Y-m-d H:i', strtotime((string) $r->tallysheet_date)) : '',
                'btb_no'          => $r->btb_no,
                'btb_date'        => $r->btb_date ? date('Y-m-d H:i', strtotime((string) $r->btb_date)) : '',
                'putaway'         => $r->putaway ?? '',
                'qty'             => $r->qty ?? '',
            ];
        });

        return view('gudang.track_in_out_print', [
            'rows'     => $rows,
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
        ]);
    }

    /**
     * GET /gudang/track-in-out/export?type=in|out
     * Export panel IN atau OUT (sesuai filter aktif) ke XLSX.
     */
    public function trackInOutExport(Request $request)
    {
        $type = strtolower(trim((string) $request->input('type', 'in')));
        $conn = DB::connection('rcm_ol_tgu');

        if ($type === 'out') {
            $searchOut = trim((string) $request->input('search_out', ''));
            $dateFromOut = trim((string) $request->input('date_from', ''));
            $dateToOut   = trim((string) $request->input('date_to', ''));

            $q = $conn->table('tgu_pickinglist_rack_h as pl')
                ->leftJoin('tgu_tr_inv_req_h as r', 'r.req_part_h_code', '=', 'pl.pl_request')
                ->leftJoin('tgu_tr_inv_out_h as bkb', 'bkb.bkb_code', '=', 'pl.pl_rack_code')
                ->leftJoin(DB::raw('(
                    SELECT invinreturvh_bkbcode,
                           MAX(invinreturvh_code) AS invinreturvh_code,
                           MAX(invinreturvh_date) AS invinreturvh_date,
                           MAX(rec_dateupdate)    AS rec_dateupdate
                    FROM tgu_tr_invinreturvh_h
                    GROUP BY invinreturvh_bkbcode
                ) as btbrv'), 'btbrv.invinreturvh_bkbcode', '=', 'bkb.inv_out_code')
                ->leftJoin('tgu_dispatch_main as dp', 'dp.dpcth_code_h', '=', 'pl.pl_dispaching_h')
                ->leftJoin(DB::raw('(
                    SELECT dpcth_code_h,
                           MIN(dptch_date)      AS dptch_date,
                           MAX(rec_dateupdate)  AS rec_dateupdate
                    FROM tgu_dispatch_h
                    GROUP BY dpcth_code_h
                ) as pod'), 'pod.dpcth_code_h', '=', 'pl.pl_dispaching_h')
                ->leftJoin(DB::raw('(
                    SELECT inv_dpcthcodeh,
                           MAX(inv_code)        AS inv_code,
                           MAX(rec_dateupdate)  AS rec_dateupdate
                    FROM tgu_tr_invoice_h_driver
                    GROUP BY inv_dpcthcodeh
                ) as pay'), 'pay.inv_dpcthcodeh', '=', 'pod.dpcth_code_h')
                ->select([
                    'pl.pl_request          as request_no',
                    'r.rec_datecreated      as request_date',
                    'pl.pl_rack_code        as picking_no',
                    'pl.rec_dateupdate      as picking_date',
                    'bkb.inv_out_code       as bkb_no',
                    'bkb.rec_dateupdate     as bkb_date',
                    'pl.pl_dispaching_h     as dispatch_no',
                    'pod.rec_dateupdate     as dispatch_date',
                    'pod.dpcth_code_h       as pod_no',
                    'pod.rec_dateupdate     as pod_date',
                    'btbrv.invinreturvh_code as btb_rv_no',
                    'btbrv.rec_dateupdate    as btb_rv_date',
                    'pay.inv_code            as payment_kasir_no',
                    'pay.rec_dateupdate      as payment_kasir_date',
                ])
                ->orderByDesc('pl.pl_rack_date')
                ->orderBy('pl.pl_rack_code');

            if ($searchOut !== '') {
                $likeOut = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $searchOut) . '%';
                $q->where(function ($qq) use ($likeOut) {
                    $qq->where('pl.pl_request', 'like', $likeOut)
                       ->orWhere('pl.pl_rack_code', 'like', $likeOut)
                       ->orWhere('pl.pl_dispaching_h', 'like', $likeOut)
                       ->orWhere('bkb.inv_out_code', 'like', $likeOut)
                       ->orWhere('btbrv.invinreturvh_code', 'like', $likeOut)
                       ->orWhere('pod.dpcth_code_h', 'like', $likeOut)
                       ->orWhere('pay.inv_code', 'like', $likeOut)
                       ->orWhereRaw("CONVERT(VARCHAR(19), r.rec_datecreated, 120) LIKE ?", [$likeOut])
                       ->orWhereRaw("CONVERT(VARCHAR(19), pl.rec_dateupdate, 120) LIKE ?", [$likeOut])
                       ->orWhereRaw("CONVERT(VARCHAR(19), bkb.rec_dateupdate, 120) LIKE ?", [$likeOut])
                       ->orWhereRaw("CONVERT(VARCHAR(19), pod.rec_dateupdate, 120) LIKE ?", [$likeOut])
                       ->orWhereRaw("CONVERT(VARCHAR(19), btbrv.rec_dateupdate, 120) LIKE ?", [$likeOut])
                       ->orWhereRaw("CONVERT(VARCHAR(19), pay.rec_dateupdate, 120) LIKE ?", [$likeOut]);
                });
            }

            if ($dateFromOut !== '') {
                $q->whereRaw('CAST(pl.pl_rack_date AS DATE) >= ?', [$dateFromOut]);
            }
            if ($dateToOut !== '') {
                $q->whereRaw('CAST(pl.pl_rack_date AS DATE) <= ?', [$dateToOut]);
            }

            $headings = [
                'Request', 'Tgl Request', 'Picking', 'Tgl Picking', 'BKB', 'Tgl BTB',
                'Dispatch', 'Tgl Dispatch', 'POD', 'Tgl POD', 'BTB RV', 'Tgl BTB RV',
                'Payment Kasir', 'Tgl Payment Kasir',
            ];
            $rows = $q->get()->map(function ($r) {
                $fmt = function ($v) {
                    return $v ? date('Y-m-d H:i:s', strtotime((string) $v)) : '';
                };
                return [
                    $r->request_no, $fmt($r->request_date),
                    $r->picking_no, $fmt($r->picking_date),
                    $r->bkb_no ?? '', $fmt($r->bkb_date),
                    $r->dispatch_no, $fmt($r->dispatch_date),
                    $r->pod_no, $fmt($r->pod_date),
                    $r->btb_rv_no ?? '', $fmt($r->btb_rv_date),
                    $r->payment_kasir_no ?? '', $fmt($r->payment_kasir_date),
                ];
            })->all();

            $filename = 'track-out-' . date('Ymd_His') . '.xlsx';
            return Excel::download(
                new TrackInOutExport($rows, $headings, 'Track OUT'),
                $filename
            );
        }

        // === Type IN ===
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo   = trim((string) $request->input('date_to', ''));
        $search   = trim((string) $request->input('search', ''));

        $q = $conn->table('tr_tgu_purchase_order_parcial_h as po')
            ->leftJoin('wdms_tr_delivery_orders_h as do', 'do.do_h_po_code', '=', 'po.tgu_purchase_order_h_code')
            ->leftJoin('wdms_tr_tallysheet_h as tls', 'tls.tls_h_no_do', '=', 'do.do_h_code')
            ->leftJoin('tgu_tr_inv_insupp_h as btb', 'btb.inv_insupp_dono', '=', 'tls.tls_h_no_do')
            ->leftJoin(DB::raw('(
                SELECT tr_inv_rack_code,
                       ISNULL(QTY_in, 0) + ISNULL(QTY_out, 0) AS qty
                FROM (
                    SELECT tr_inv_rack_code, QTY_in, QTY_out,
                           ROW_NUMBER() OVER (PARTITION BY tr_inv_rack_code ORDER BY rec_datecreated DESC) AS rn
                    FROM tgu_tr_inv_main_mutasi_rack
                ) x WHERE x.rn = 1
            ) as mr'), 'mr.tr_inv_rack_code', '=', 'tls.tls_h_code')
            ->select([
                'po.tgu_purchase_order_h_code as po_no',
                'po.rec_dateupdate            as po_date',
                'tls.tls_h_code               as tallysheet_no',
                'tls.completed_at             as tallysheet_date',
                'btb.inv_insupp_code          as btb_no',
                'btb.rec_dateupdate           as btb_date',
                'btb.inv_insupp_inv_incode    as putaway',
                'mr.qty                       as qty',
            ])
            ->orderByDesc('po.tgu_purchase_order_date')
            ->orderBy('po.tgu_purchase_order_h_code');

        if ($dateFrom !== '') {
            $q->whereRaw('CAST(po.tgu_purchase_order_date AS DATE) >= ?', [$dateFrom]);
        }
        if ($dateTo !== '') {
            $q->whereRaw('CAST(po.tgu_purchase_order_date AS DATE) <= ?', [$dateTo]);
        }
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $q->where(function ($qq) use ($like) {
                $qq->where('po.tgu_purchase_order_h_code', 'like', $like)
                   ->orWhere('tls.tls_h_code',             'like', $like)
                   ->orWhere('btb.inv_insupp_code',        'like', $like)
                   ->orWhere('btb.inv_insupp_inv_incode',  'like', $like);
            });
        }

        $headings = ['PO', 'Tgl PO', 'Tallysheet', 'Tgl Tallysheet', 'BTB', 'Tgl BTB', 'Putaway', 'QTY'];
        $rows = $q->get()->map(function ($r) {
            return [
                $r->po_no,
                $r->po_date ? date('Y-m-d', strtotime((string) $r->po_date)) : '',
                $r->tallysheet_no,
                $r->tallysheet_date ? date('Y-m-d H:i', strtotime((string) $r->tallysheet_date)) : '',
                $r->btb_no,
                $r->btb_date ? date('Y-m-d H:i', strtotime((string) $r->btb_date)) : '',
                $r->putaway ?? '',
                $r->qty ?? '',
            ];
        })->all();

        $filename = 'track-in-' . date('Ymd_His') . '.xlsx';
        return Excel::download(
            new TrackInOutExport($rows, $headings, 'Track IN'),
            $filename
        );
    }

    /**
     * GET /gudang/track-in-out/export-row-detail?po=...&tls=...&btb=...&putaway=...
     * Export satu baris Track IN lengkap dengan semua detail (4 sheet).
     */
    public function trackInOutExportRowDetail(Request $request)
    {
        $po      = trim((string) $request->input('po', ''));
        $tls     = trim((string) $request->input('tls', ''));
        $btb     = trim((string) $request->input('btb', ''));
        $putaway = trim((string) $request->input('putaway', ''));
        $conn    = DB::connection('rcm_ol_tgu');

        $fmt = fn($v) => $v ? date('Y-m-d H:i:s', strtotime((string) $v)) : '';
        $fmtD = fn($v) => $v ? date('Y-m-d', strtotime((string) $v)) : '';

        // ── Sheet 1: PO Detail ──────────────────────────────────────────────
        $poRows = $po === '' ? [] : $conn->table('tr_tgu_purchase_order_parcial_d as d')
            ->leftJoin(DB::raw('(
                SELECT SKU_master, SKU_description,
                       ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                FROM tgu_ms_product_Business
            ) as m'), function ($j) {
                $j->on('m.SKU_master', '=', 'd.tgu_purchase_order_sku_code')->where('m.rn', '=', 1);
            })
            ->whereRaw('LTRIM(RTRIM(d.tgu_purchase_order_h_code)) = ?', [$po])
            ->select([
                'd.tgu_purchase_order_sku_code as sku_code',
                DB::raw("ISNULL(m.SKU_description, '') as deskripsi"),
                'd.tgu_purchase_order_qty       as qty',
                'd.tgu_purchase_order_qty_ctn   as qty_ctn',
                'd.tgu_purchase_order_qty_satuan as qty_pcs',
                'd.tgu_purchase_order_price     as harga_pcs',
                'd.tgu_purchase_order_price_ctn as harga_ctn',
                'd.tgu_purchase_order_po        as order_po',
            ])
            ->orderBy('d.tgu_purchase_order_sku_code')
            ->get()
            ->map(fn($r) => [
                $r->sku_code, $r->deskripsi, $r->qty, $r->qty_ctn,
                $r->qty_pcs, $r->harga_pcs, $r->harga_ctn, $r->order_po,
            ])->all();

        // ── Sheet 2: Tallysheet Detail ──────────────────────────────────────
        $tlsRows = $tls === '' ? [] : $conn->table('wdms_tr_tallysheet_det as d')
            ->join('wdms_tr_tallysheet_h as h', 'h.id', '=', 'd.tls_det_id_header')
            ->whereRaw('LTRIM(RTRIM(h.tls_h_code)) = ?', [$tls])
            ->select([
                'd.tls_det_sku_order      as sku_order',
                'd.tls_det_barcode        as barcode',
                'd.tls_det_category       as deskripsi',
                'd.tls_det_qty_karton     as qty_ctn',
                'd.tls_det_pcs_per_karton as pcs_per_ctn',
                'd.tls_det_total_pcs      as total_pcs',
            ])
            ->orderBy('d.tls_det_sku_order')
            ->get()
            ->map(fn($r) => [
                $r->sku_order, $r->barcode, $r->deskripsi,
                $r->qty_ctn, $r->pcs_per_ctn, $r->total_pcs,
            ])->all();

        // ── Sheet 3: BTB Detail ─────────────────────────────────────────────
        $btbRows = $btb === '' ? [] : $conn->table('tgu_tr_inv_in_h as h')
            ->leftJoin('tgu_tr_inv_insupp_h as s', 's.inv_insupp_inv_incode', '=', 'h.inv_in_code')
            ->join('tgu_tr_inv_in_d as d', 'd.inv_in_code', '=', 'h.inv_in_code')
            ->leftJoin(DB::raw('(
                SELECT SKU_master, SKU_description,
                       ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                FROM tgu_ms_product_Business
            ) as pb'), function ($j) {
                $j->on('pb.SKU_master', '=', 'd.inv_in_item')->where('pb.rn', '=', 1);
            })
            ->where(function ($q) use ($btb) {
                $q->whereRaw('LTRIM(RTRIM(s.inv_insupp_code)) = ?', [$btb])
                  ->orWhereRaw('LTRIM(RTRIM(h.inv_in_code)) = ?', [$btb]);
            })
            ->select([
                DB::raw('COALESCE(pb.SKU_description, d.inv_in_item) as produk'),
                'd.inv_in_ed          as exp_date',
                'd.inv_in_qty         as qty_in',
                'd.inv_in_qty_ctn     as qty_ctn',
                'd.inv_in_qty_satuan  as qty_satuan',
                'd.inv_in_price       as harga_pcs',
                'd.inv_in_price_ctn   as harga_ctn',
                DB::raw('(d.inv_in_price * d.inv_in_qty) as gross_value'),
                DB::raw('(d.inv_in_price * d.inv_in_qty * 1.11) as net_value'),
            ])
            ->orderBy('d.inv_in_item')
            ->get()
            ->map(fn($r) => [
                $r->produk, $fmtD($r->exp_date), $r->qty_in, $r->qty_ctn,
                $r->qty_satuan, $r->harga_pcs, $r->harga_ctn, $r->gross_value, $r->net_value,
            ])->all();

        // ── Sheet 4: Putaway Detail ─────────────────────────────────────────
        $putawayRows = $putaway === '' ? [] : $conn->table('TGU_tr_inv_main_mutasi_rack as m')
            ->leftJoin(DB::raw('(
                SELECT SKU_master, SKU_description,
                       ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                FROM tgu_ms_product_Business
            ) as pb'), function ($j) {
                $j->on('pb.SKU_master', '=', 'm.ms_product_code')->where('pb.rn', '=', 1);
            })
            ->whereRaw('LTRIM(RTRIM(m.tr_inv_rack_code)) = ?', [$putaway])
            ->select([
                'm.ms_rack_code        as rack_code',
                'm.ms_product_code     as product_code',
                DB::raw("ISNULL(pb.SKU_description, '') as deskripsi"),
                'm.QTY_out             as qty_out',
                'm.QTY_in              as qty_in',
                'm.Stock_akhir         as stock_akhir',
                'm.main_type_transaksi as type_transaksi',
                'm.rec_dateupdate      as tgl_update',
            ])
            ->orderByDesc('m.rec_dateupdate')
            ->get()
            ->map(fn($r) => [
                $r->rack_code, $r->product_code, $r->deskripsi,
                $r->qty_out, $r->qty_in, $r->stock_akhir,
                $r->type_transaksi, $fmt($r->tgl_update),
            ])->all();

        $sheets = [
            new TrackInOutExport($poRows,
                ['SKU', 'Deskripsi', 'Qty', 'Qty Ctn', 'Qty Pcs', 'Harga Pcs', 'Harga Ctn', 'Order PO'],
                'PO Detail'),
            new TrackInOutExport($tlsRows,
                ['SKU Order', 'Barcode', 'Deskripsi', 'Qty Ctn', 'Pcs/Ctn', 'Total Pcs'],
                'TLS Detail',
                ['B']),  // kolom B (Barcode) = force string agar tidak scientific notation
            new TrackInOutExport($btbRows,
                ['Produk', 'Exp Date', 'Qty In', 'Qty Ctn', 'Qty Satuan', 'Harga Pcs', 'Harga Ctn', 'Gross Value', 'Net Value'],
                'BTB Detail'),
            new TrackInOutExport($putawayRows,
                ['Rack Code', 'Product Code', 'Deskripsi', 'Qty Out', 'Qty In', 'Stock Akhir', 'Type Transaksi', 'Tgl Update'],
                'Putaway Detail'),
        ];

        $filename = 'track-in-detail-' . ($po ?: 'all') . '-' . date('Ymd_His') . '.xlsx';
        return Excel::download(new TrackInFullExport($sheets), $filename);
    }

    /**
     * GET /gudang/track-in-out/export-out-row-detail
     * Export satu baris Track OUT lengkap (7 sheet).
     */
    public function trackInOutExportOutRowDetail(Request $request)
    {
        $req      = trim((string) $request->input('req', ''));
        $picking  = trim((string) $request->input('picking', ''));
        $bkb      = trim((string) $request->input('bkb', ''));
        $dispatch = trim((string) $request->input('dispatch', ''));
        $pod      = trim((string) $request->input('pod', ''));
        $btbrv    = trim((string) $request->input('btbrv', ''));
        $conn     = DB::connection('rcm_ol_tgu');

        $fmt  = fn($v) => $v ? date('Y-m-d H:i:s', strtotime((string) $v)) : '';
        $fmtD = fn($v) => $v ? date('Y-m-d', strtotime((string) $v)) : '';

        $pbSubquery = DB::raw('(
            SELECT SKU_master, SKU_description,
                   ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
            FROM tgu_ms_product_Business
        ) as pb');

        // ── Sheet 1: Request Detail ─────────────────────────────────────────
        $reqRows = $req === '' ? [] : $conn->table('tgu_tr_inv_req_d as d')
            ->leftJoin($pbSubquery, function ($j) {
                $j->on('pb.SKU_master', '=', 'd.req_productcode')->where('pb.rn', '=', 1);
            })
            ->whereRaw('LTRIM(RTRIM(d.req_inv_h_code)) = ?', [$req])
            ->select([
                'd.req_productcode              as product',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'd.req_businessproductcode      as business_code',
                'd.req_businessproductordercode as order_code',
                'd.req_qty                      as qty',
            ])
            ->orderBy('d.req_productcode')->get()
            ->map(fn($r) => [$r->product, $r->description, $r->business_code, $r->order_code, $r->qty])->all();

        // ── Sheet 2: Picking Detail ─────────────────────────────────────────
        $pickingRows = $picking === '' ? [] : $conn->table('TGU_tr_inv_main_mutasi_rack as m')
            ->leftJoin($pbSubquery, function ($j) {
                $j->on('pb.SKU_master', '=', 'm.ms_product_code')->where('pb.rn', '=', 1);
            })
            ->whereRaw('LTRIM(RTRIM(m.tr_inv_rack_code)) = ?', [$picking])
            ->where('m.QTY_out', '<>', 0)
            ->select([
                'm.ms_rack_code             as rack',
                'm.ms_product_code          as product_code',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'm.QTY_out                  as qty',
                'm.stock_awal               as stock_awal',
                'm.Stock_akhir              as stock_akhir',
                'm.exp_date_in              as exp_date',
                'm.main_type_transaksi      as type_transaksi',
            ])
            ->orderBy('m.ms_rack_code')->get()
            ->map(fn($r) => [
                $r->rack, $r->product_code, $r->description,
                $r->qty, $r->stock_awal, $r->stock_akhir, $fmtD($r->exp_date), $r->type_transaksi,
            ])->all();

        // ── Sheet 3: BKB Detail ─────────────────────────────────────────────
        $bkbRows = $bkb === '' ? [] : $conn->table('tgu_tr_inv_out_h as h')
            ->join('tgu_tr_inv_out_d as d', 'd.inv_out_code', '=', 'h.inv_out_code')
            ->leftJoin($pbSubquery, function ($j) {
                $j->on('pb.SKU_master', '=', 'd.inv_out_partcode')->where('pb.rn', '=', 1);
            })
            ->whereRaw('LTRIM(RTRIM(h.inv_out_code)) = ?', [$bkb])
            ->select([
                'd.inv_out_partcode      as code',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'd.inv_out_qty           as qty',
                'd.inv_out_price         as price',
                'd.inv_out_stockmanual   as stock_manual',
                'd.inv_out_stocksystem   as stock_system',
                'd.cogs                  as price_cogs',
                'd.cogs_last             as price_cogs_last',
            ])
            ->orderBy('d.inv_out_partcode')->get()
            ->map(fn($r) => [
                $r->code, $r->description, $r->qty, $r->price,
                $r->stock_manual, $r->stock_system, $r->price_cogs, $r->price_cogs_last,
            ])->all();

        // ── Sheet 4: Dispatch Detail ────────────────────────────────────────
        $dispatchRows = $dispatch === '' ? [] : $conn->table('tgu_dispatch_h as h')
            ->leftJoin('tgu_dispatch_d as d', 'd.dptch_so', '=', 'h.dpcth_so')
            ->leftJoin($pbSubquery, function ($j) {
                $j->on('pb.SKU_master', '=', 'd.dptch_product_internal')->where('pb.rn', '=', 1);
            })
            ->whereRaw('LTRIM(RTRIM(h.dpcth_code_h)) = ?', [$dispatch])
            ->select([
                'h.dpcth_vhcl_code         as vehicle',
                'h.dpcth_so                as so',
                'd.dptch_product_internal  as product',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'd.dptch_unit_quantity     as qty',
                'd.dptch_unit              as satuan',
                'h.dpcth_drv_code          as driver',
                'h.dpch_status             as status',
                'h.dptch_est_delivery_date as eta',
            ])
            ->orderBy('h.dpcth_so')->get()
            ->map(fn($r) => [
                $r->vehicle, $r->so, $r->product, $r->description,
                $r->qty, $r->satuan, $r->driver, $r->status, $fmtD($r->eta),
            ])->all();

        // ── Sheet 5: POD REPORT ─────────────────────────────────────────────
        $podRows = $pod === '' ? [] : $conn->table('tgu_dispatch_main')
            ->whereRaw('LTRIM(RTRIM(dpcth_code_h)) = ?', [$pod])
            ->select([
                'dpcth_vhcl_code         as vehicle',
                'dpcth_drv_code          as driver',
                'rec_dateupdate          as date',
                'dpch_value              as value',
                'dpch_dispach_inv_total  as total_inv',
                'dpch_dispach_inv_cash   as terkirim',
                'dpch_dispach_inv_cancel as cancel',
            ])
            ->orderByDesc('rec_dateupdate')->get()
            ->map(fn($r) => [
                $r->vehicle, $r->driver, $fmt($r->date),
                $r->value, $r->total_inv, $r->terkirim, $r->cancel,
            ])->all();

        // ── Sheet 6: BTB RV Detail ──────────────────────────────────────────
        $btbrvRows = $btbrv === '' ? [] : $conn->table('tgu_tr_inv_main_mutasi_rack as m')
            ->leftJoin($pbSubquery, function ($j) {
                $j->on('pb.SKU_master', '=', 'm.ms_product_code')->where('pb.rn', '=', 1);
            })
            ->whereRaw('LTRIM(RTRIM(m.tr_inv_rack_code)) = ?', [$btbrv])
            ->select([
                'm.ms_rack_code             as rack',
                'm.ms_product_code          as product_code',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'm.qty_in                   as qty_in',
                'm.qty_out                  as qty_out',
                'm.stock_akhir              as stock_akhir',
                'm.ms_product_supplier_code as supplier_code',
                'm.ms_product_business_code as business_code',
            ])
            ->orderByDesc('m.rec_dateupdate')->get()
            ->map(fn($r) => [
                $r->rack, $r->product_code, $r->description,
                $r->qty_in, $r->qty_out, $r->stock_akhir, $r->supplier_code, $r->business_code,
            ])->all();

        // ── Sheet 7: Payment Kasir Detail ───────────────────────────────────
        $payRows = $dispatch === '' ? [] : $conn->table('tgu_tr_invoice_d_driver')
            ->whereRaw('LTRIM(RTRIM(inv_dpcthcodeh)) = ?', [$dispatch])
            ->select([
                'inv_dpcthcodeh        as dispatch_code',
                'inv_invoice_number    as no_so',
                'inv_type              as pembayaran',
                'inv_retailer_code     as kode_retail',
                'inv_remarks           as status',
                'inv_valuegiro         as giro',
                'inv_value             as value',
                'inv_cabcode           as branch',
                'inv_valuevalidation   as value_validation',
                'inv_repay             as repay',
                'inv_valuepay          as pay_value',
            ])
            ->orderBy('inv_invoice_number')->get()
            ->map(fn($r) => [
                $r->dispatch_code, $r->no_so, $r->pembayaran, $r->kode_retail,
                $r->status, $r->giro, $r->value, $r->branch, $r->value_validation,
                $r->repay, $r->pay_value,
            ])->all();

        $sheets = [
            new TrackInOutExport($reqRows,
                ['Product', 'Description', 'Business Code', 'Order Code', 'Qty'],
                'Request Detail'),
            new TrackInOutExport($pickingRows,
                ['Rack', 'Product Code', 'Description', 'Qty', 'Stock Awal', 'Stock Akhir', 'Exp Date', 'Type'],
                'Picking Detail'),
            new TrackInOutExport($bkbRows,
                ['Code', 'Description', 'Qty', 'Price', 'Stock Manual', 'Stock System', 'COGS', 'COGS Last'],
                'BKB Detail'),
            new TrackInOutExport($dispatchRows,
                ['Vehicle', 'SO', 'Product', 'Description', 'Qty', 'Satuan', 'Driver', 'Status', 'ETA'],
                'Dispatch Detail'),
            new TrackInOutExport($podRows,
                ['Vehicle', 'Driver', 'Date', 'Value', 'Total Inv', 'Terkirim', 'Cancel'],
                'POD REPORT'),
            new TrackInOutExport($btbrvRows,
                ['Rack', 'Product Code', 'Description', 'Qty In', 'Qty Out', 'Stock Akhir', 'Supplier Code', 'Business Code'],
                'BTB RV Detail'),
            new TrackInOutExport($payRows,
                ['Dispatch Code', 'No SO', 'Pembayaran', 'Kode Retail', 'Status', 'Giro', 'Value', 'Branch', 'Value Validation', 'Repay', 'Pay Value'],
                'Payment Kasir'),
        ];

        $filename = 'track-out-detail-' . ($req ?: 'all') . '-' . date('Ymd_His') . '.xlsx';
        return Excel::download(new TrackInFullExport($sheets), $filename);
    }

    /**
     * AJAX: GET /gudang/track-in-out/detail?po=...
     * Detail row PO dari tr_tgu_purchase_order_parcial_h:
     *   sku    : tgu_purchase_order_sku_code
     *   qty    : tgu_purchase_order_qty
     *   price  : tgu_purchase_order_price
     */
    public function trackInOutDetail(Request $request)
    {
        $po = trim((string) $request->input('po', ''));
        if ($po === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('tr_tgu_purchase_order_parcial_d as d')
            ->leftJoin(
                DB::raw('(
                    SELECT SKU_master, SKU_description,
                           ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                    FROM tgu_ms_product_Business
                ) as m'),
                function ($j) {
                    $j->on('m.SKU_master', '=', 'd.tgu_purchase_order_sku_code')
                      ->where('m.rn', '=', 1);
                }
            )
            ->whereRaw('LTRIM(RTRIM(d.tgu_purchase_order_h_code)) = ?', [$po])
            ->select([
                'd.tgu_purchase_order_sku_code  as sku_code',
                DB::raw("ISNULL(m.SKU_description, '') as sku_description"),
                'd.tgu_purchase_order_qty        as order_qty',
                'd.tgu_purchase_order_price      as order_price',
                'd.tgu_purchase_order_qty_ctn    as qty_ctn',
                'd.tgu_purchase_order_qty_satuan as qty_pcs',
                'd.tgu_purchase_order_price      as price_pcs',
                'd.tgu_purchase_order_price_ctn  as price_ctn',
                'd.tgu_purchase_order_po         as order_po',
            ])
            ->orderBy('d.tgu_purchase_order_sku_code')
            ->get();

        return response()->json([
            'po'   => $po,
            'rows' => $rows,
        ]);
    }

    /**
     * AJAX: GET /gudang/track-in-out/tallysheet-detail?tls=...
     * Detail Tallysheet dari wdms_tr_tallysheet_det,
     * join ke wdms_tr_tallysheet_h.id melalui kolom tls_det_id_header.
     */
    public function trackInOutTallysheetDetail(Request $request)
    {
        $tls = trim((string) $request->input('tls', ''));
        if ($tls === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('wdms_tr_tallysheet_det as d')
            ->join('wdms_tr_tallysheet_h as h', 'h.id', '=', 'd.tls_det_id_header')
            ->whereRaw('LTRIM(RTRIM(h.tls_h_code)) = ?', [$tls])
            ->select([
                'd.tls_det_sku_order   as sku_order',
                'd.tls_det_barcode     as barcode',
                'd.tls_det_category    as deskripsi',
                'd.tls_det_qty_karton  as qty_ctn',
                'd.tls_det_pcs_per_karton  as pcs_per_ctn',
                'd.tls_det_total_pcs   as total_pcs',
            ])
            ->orderBy('d.tls_det_sku_order')
            ->get();

        return response()->json([
            'tls'  => $tls,
            'rows' => $rows,
        ]);
    }

    /**
     * AJAX: GET /gudang/track-in-out/btb-detail?btb=...
     * Detail BTB dari tgu_tr_inv_in_h join tgu_tr_inv_d via inv_in_code.
     */
    public function trackInOutBtbDetail(Request $request)
    {
        $btb = trim((string) $request->input('btb', ''));
        if ($btb === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('tgu_tr_inv_in_h as h')
            ->leftJoin('tgu_tr_inv_insupp_h as s', 's.inv_insupp_inv_incode', '=', 'h.inv_in_code')
            ->join('tgu_tr_inv_in_d as d', 'd.inv_in_code', '=', 'h.inv_in_code')
            ->leftJoin(
                DB::raw('(
                    SELECT SKU_master, SKU_description,
                           ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                    FROM tgu_ms_product_Business
                ) as pb'),
                function ($j) {
                    $j->on('pb.SKU_master', '=', 'd.inv_in_item')
                      ->where('pb.rn', '=', 1);
                }
            )
            ->where(function ($q) use ($btb) {
                $q->whereRaw('LTRIM(RTRIM(s.inv_insupp_code)) = ?', [$btb])
                  ->orWhereRaw('LTRIM(RTRIM(h.inv_in_code)) = ?', [$btb]);
            })
            ->select([
                DB::raw('COALESCE(pb.SKU_description, d.inv_in_item) as product'),
                'd.inv_in_ed         as expired_date',
                'd.inv_in_qty        as qty_in',
                'd.inv_in_qty_ctn    as qty_ctn',
                'd.inv_in_qty_satuan as qty_satuan',
                'd.inv_in_price      as harga_pcs',
                'd.inv_in_price_ctn  as harga_ctn',
                DB::raw('(d.inv_in_price * d.inv_in_qty) as gross_value'),
                DB::raw('(d.inv_in_price * d.inv_in_qty * 1.11) as net_value'),
            ])
            ->orderBy('d.inv_in_item')
            ->get();

        return response()->json([
            'btb'  => $btb,
            'rows' => $rows,
        ]);
    }

    /**
     * AJAX: GET /gudang/track-in-out/putaway-detail?putaway=...
     * Detail Putaway dari TGU_tr_inv_main_mutasi_rack berdasarkan kode putaway
     * yang disimpan di kolom tr_inv_rack_code.
     */
    public function trackInOutPutawayDetail(Request $request)
    {
        $putaway = trim((string) $request->input('putaway', ''));
        if ($putaway === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('TGU_tr_inv_main_mutasi_rack as m')
            ->leftJoin(
                DB::raw('(
                    SELECT SKU_master, SKU_description,
                           ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                    FROM tgu_ms_product_Business
                ) as pb'),
                function ($j) {
                    $j->on('pb.SKU_master', '=', 'm.ms_product_code')
                      ->where('pb.rn', '=', 1);
                }
            )
            ->whereRaw('LTRIM(RTRIM(m.tr_inv_rack_code)) = ?', [$putaway])
            ->select([
                'm.ms_rack_code         as rack_code',
                'm.ms_product_code      as product_code',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'm.QTY_out              as qty_out',
                'm.QTY_in               as qty_in',
                'm.Stock_akhir          as stock_akhir',
                'm.main_type_transaksi  as type_transaksi',
                'm.rec_dateupdate       as rec_dateupdate',
            ])
            ->orderByDesc('m.rec_dateupdate')
            ->get();

        return response()->json([
            'putaway' => $putaway,
            'rows'    => $rows,
        ]);
    }

    /**
     * AJAX: GET /gudang/track-in-out/out-request-detail?req=...
     * Detail Request dari tgu_tr_inv_req_d via req_inv_h_code.
     */
    public function trackInOutOutRequestDetail(Request $request)
    {
        $req = trim((string) $request->input('req', ''));
        if ($req === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('tgu_tr_inv_req_d as d')
            ->leftJoin(
                DB::raw('(
                    SELECT SKU_master, SKU_description,
                           ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                    FROM tgu_ms_product_Business
                ) as pb'),
                function ($j) {
                    $j->on('pb.SKU_master', '=', 'd.req_productcode')
                      ->where('pb.rn', '=', 1);
                }
            )
            ->whereRaw('LTRIM(RTRIM(d.req_inv_h_code)) = ?', [$req])
            ->select([
                'd.req_productcode              as product',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'd.req_businessproductcode      as business_code',
                'd.req_businessproductordercode as order_code',
                'd.req_qty                      as qty',
            ])
            ->orderBy('d.req_productcode')
            ->get();

        return response()->json([
            'req'  => $req,
            'rows' => $rows,
        ]);
    }

    /**
     * AJAX: GET /gudang/track-in-out/out-picking-detail?picking=...
     * Detail Picking dari TGU_tr_inv_main_mutasi_rack berdasarkan kode picking
     * yang disimpan di kolom tr_inv_rack_code.
     */
    public function trackInOutOutPickingDetail(Request $request)
    {
        $picking = trim((string) $request->input('picking', ''));
        if ($picking === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('TGU_tr_inv_main_mutasi_rack as m')
            ->leftJoin(
                DB::raw('(
                    SELECT SKU_master, SKU_description,
                           ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                    FROM tgu_ms_product_Business
                ) as pb'),
                function ($j) {
                    $j->on('pb.SKU_master', '=', 'm.ms_product_code')
                      ->where('pb.rn', '=', 1);
                }
            )
            ->whereRaw('LTRIM(RTRIM(m.tr_inv_rack_code)) = ?', [$picking])
            ->where('m.QTY_out', '<>', 0)
            ->select([
                'm.ms_rack_code               as rack',
                'm.QTY_out                    as qty',
                'm.stock_awal                 as stock_awal',
                'm.Stock_akhir                as stock_akhir',
                'm.exp_date_in                as expired_date',
                'm.main_type_transaksi        as type_transaksi',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'm.ms_product_code            as product_code',
                'm.ms_product_supplier_code   as supplier_code',
                'm.ms_product_business_code   as business_code',
            ])
            ->orderBy('m.ms_rack_code')
            ->get();

        return response()->json([
            'picking' => $picking,
            'rows'    => $rows,
        ]);
    }

    /**
     * AJAX: GET /gudang/track-in-out/out-bkb-detail?bkb=...
     * Detail BKB dari tgu_tr_inv_out_d via inv_out_code.
     */
    public function trackInOutOutBkbDetail(Request $request)
    {
        $bkb = trim((string) $request->input('bkb', ''));
        if ($bkb === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('tgu_tr_inv_out_h as h')
            ->join('tgu_tr_inv_out_d as d', 'd.inv_out_code', '=', 'h.inv_out_code')
            ->leftJoin(
                DB::raw('(
                    SELECT SKU_master, SKU_description,
                           ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                    FROM tgu_ms_product_Business
                ) as pb'),
                function ($j) {
                    $j->on('pb.SKU_master', '=', 'd.inv_out_partcode')
                      ->where('pb.rn', '=', 1);
                }
            )
            ->whereRaw('LTRIM(RTRIM(h.inv_out_code)) = ?', [$bkb])
            ->select([
                'd.inv_out_partcode      as code',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'd.inv_out_qty           as qty',
                'd.inv_out_price         as price',
                'd.inv_out_stockmanual   as stock_manual',
                'd.inv_out_stocksystem   as stock_system',
                'd.cogs                  as price_cogs',
                'd.cogs_last             as price_cogs_last',
            ])
            ->orderBy('d.inv_out_partcode')
            ->get();

        return response()->json([
            'bkb'  => $bkb,
            'rows' => $rows,
        ]);
    }

    /**
     * AJAX: GET /gudang/track-in-out/out-dispatch-detail?dispatch=...
     * Detail Dispatch dari tgu_dispatch_h via dpcth_code_h.
     */
    public function trackInOutOutDispatchDetail(Request $request)
    {
        $dispatch = trim((string) $request->input('dispatch', ''));
        if ($dispatch === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('tgu_dispatch_h as h')
            ->leftJoin('tgu_dispatch_d as d', 'd.dptch_so', '=', 'h.dpcth_so')
            ->leftJoin(
                DB::raw('(
                    SELECT SKU_master, SKU_description,
                           ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                    FROM tgu_ms_product_Business
                ) as pb'),
                function ($j) {
                    $j->on('pb.SKU_master', '=', 'd.dptch_product_internal')
                      ->where('pb.rn', '=', 1);
                }
            )
            ->whereRaw('LTRIM(RTRIM(h.dpcth_code_h)) = ?', [$dispatch])
            ->select([
                'h.dpcth_vhcl_code         as vehicle',
                'h.dpcth_so                as so',
                'd.dptch_product_internal  as product',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'd.dptch_unit_quantity     as qty',
                'd.dptch_unit              as satuan',
                'h.dpcth_drv_code          as driver',
                'h.dpch_status             as status',
                'h.dptch_est_delivery_date as eta',
            ])
            ->orderBy('h.dpcth_so')
            ->get();

        return response()->json([
            'dispatch' => $dispatch,
            'rows'     => $rows,
        ]);
    }

    /**
     * AJAX: GET /gudang/track-in-out/out-pod-detail?pod=...
     * Detail POD dari tgu_dispatch_main via dpcth_code_h.
     */
    public function trackInOutOutPodDetail(Request $request)
    {
        $pod = trim((string) $request->input('pod', ''));
        if ($pod === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('tgu_dispatch_main')
            ->whereRaw('LTRIM(RTRIM(dpcth_code_h)) = ?', [$pod])
            ->select([
                'dpcth_vhcl_code            as vehicle',
                'dpcth_drv_code             as driver',
                'rec_dateupdate             as date',
                'dpch_value                 as value',
                'dpch_dispach_inv_total     as total_inv',
                'dpch_dispach_inv_cash      as terkirim',
                'dpch_dispach_inv_cancel    as cancel',
            ])
            ->orderByDesc('rec_dateupdate')
            ->get();

        return response()->json([
            'pod'  => $pod,
            'rows' => $rows,
        ]);
    }

    public function trackInOutOutBtbRvDetail(Request $request)
    {
        $btbrv = trim((string) $request->input('btbrv', ''));
        if ($btbrv === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('tgu_tr_inv_main_mutasi_rack as m')
            ->leftJoin(
                DB::raw('(
                    SELECT SKU_master, SKU_description,
                           ROW_NUMBER() OVER (PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business) AS rn
                    FROM tgu_ms_product_Business
                ) as pb'),
                function ($j) {
                    $j->on('pb.SKU_master', '=', 'm.ms_product_code')
                      ->where('pb.rn', '=', 1);
                }
            )
            ->whereRaw('LTRIM(RTRIM(m.tr_inv_rack_code)) = ?', [$btbrv])
            ->select([
                'm.ms_rack_code                as rack',
                'm.ms_product_code             as product_code',
                DB::raw("ISNULL(pb.SKU_description, '') as description"),
                'm.qty_in                      as qty_in',
                'm.qty_out                     as qty_out',
                'm.stock_akhir                 as stock_akhir',
                'm.ms_product_supplier_code    as supplier_code',
                'm.ms_product_business_code    as business_code',
            ])
            ->orderBy('m.rec_dateupdate', 'desc')
            ->get();

        return response()->json([
            'btbrv' => $btbrv,
            'rows'  => $rows,
        ]);
    }

    /**
     * AJAX: GET /gudang/track-in-out/out-payment-detail?dispatch=...
     * Detail Payment Kasir dari tgu_tr_invoice_d_driver via inv_dpcthcodeh
     * (kode dispatch / POD).
     */
    public function trackInOutOutPaymentDetail(Request $request)
    {
        $dispatch = trim((string) $request->input('dispatch', ''));
        if ($dispatch === '') {
            return response()->json(['rows' => []]);
        }

        $rows = DB::connection('rcm_ol_tgu')
            ->table('tgu_tr_invoice_d_driver')
            ->whereRaw('LTRIM(RTRIM(inv_dpcthcodeh)) = ?', [$dispatch])
            ->select([
                'inv_dpcthcodeh          as dispatch_code',
                'inv_invoice_number      as no_so',
                'inv_type                as pembayaran',
                'inv_retailer_code       as kode_retail',
                'inv_remarks             as status',
                'inv_valuegiro           as giro',
                'inv_value               as value',
                'inv_cabcode             as branch',
                'inv_valuevalidation     as value_validation',
                'inv_repay               as repay',
                'inv_valuepay            as pay_value',
                'inv_validationgiro      as validation_giro',
                'inv_status_pengiriman   as status_pengiriman',
            ])
            ->orderBy('inv_invoice_number')
            ->get();

        return response()->json([
            'dispatch' => $dispatch,
            'rows'     => $rows,
        ]);
    }

    /**
     * GET /gudang/kartu-stock
     * Mirror C# PrintPreviewKartuStock (RCM_WO).
     * Filter: Branch (ms_cabang), Client (ms_client_bl), Rack (tgu_ms_rack_internal), SKU.
     * Data utama: SP Sp_list_TGU_stok_by_rack_and_sku_order(cabang, client, rack, sku_default, sku_order, sku_internal).
     * Kolom grid (sesuai o_tableDM di C#):
     *   count, rec_userupdate, tr_inv_rack_code, ms_product_code,
     *   ms_product_business_code, ms_product_business_order_code, description,
     *   skuconvert (convert), Qty_in, Qty_out, Stock_akhir, usr_cnt_last_stock, exp_date_in.
     */
    public function kartuStock(Request $request)
    {
        $conn = DB::connection('rcm_ol_tgu');

        $branch       = trim((string) $request->input('branch', ''));
        $client       = trim((string) $request->input('client', ''));
        $rack         = trim((string) $request->input('rack', ''));
        $sku          = trim((string) $request->input('sku', ''));
        $allowed      = [25, 50, 100, 200];
        $perPage      = (int) $request->input('per_page', 50);
        if (!in_array($perPage, $allowed, true)) {
            $perPage = 50;
        }

        // Lookup dropdowns (sesuai C# FillGridCabang / FillgridChamber / FillgridRack)
        $cabangList = $conn->table('ms_cabang')
            ->select('cab_code', 'cab_desc')
            ->orderBy('cab_desc')->get();
        $clientList = $conn->table('ms_client_bl')
            ->select('clien_id', 'clien_desc')
            ->orderBy('clien_desc')->get();
        $rackList = $conn->table('tgu_ms_rack_internal')
            ->select('rack_internal_code as ms_rack_code')
            ->distinct()
            ->orderBy('rack_internal_code')
            ->get();

        $rows      = collect();
        $paginator = null;

        // C# simpleButton_MutasiRack_Click hanya menjalankan SP saat ditekan.
        // Kita lakukan query saat filter minimal (Branch + Client) terisi.
        $hasFilter = ($branch !== '' && $client !== '');

        if ($hasFilter) {
            // Port dari Sp_list_TGU_stok_by_rack_and_sku_order:
            //   FROM TGU_tr_inv_main_mutasi_rack mutasi
            //   LEFT JOIN tgu_ms_product_Business sku_bisnis
            //        ON  mutasi.ms_product_business_order_code = sku_bisnis.SKU_Business
            //        AND mutasi.unit_bisnis                    = sku_bisnis.Business
            //   LEFT JOIN tgu_ms_rack_internal rack
            //        ON  mutasi.ms_rack_code = rack.rack_internal_code
            //   WHERE rec_areacode = @branch
            //     AND unit_bisnis  = @gudangmaster
            //     AND ms_rack_code = @rack
            //     AND ms_product_business_code       = @default
            //     AND ms_product_business_order_code = @order
            //     AND ms_product_code                = @internal
            //   ORDER BY mutasi.[count] DESC;
            // (Semua filter dibuat opsional di sini — skip kalau kosong.)
            $query = $conn->table('TGU_tr_inv_main_mutasi_rack as mutasi')
                ->leftJoin('tgu_ms_product_Business as sku_bisnis', function ($j) {
                    $j->on('mutasi.ms_product_business_order_code', '=', 'sku_bisnis.SKU_Business')
                      ->on('mutasi.unit_bisnis', '=', 'sku_bisnis.Business');
                })
                ->leftJoin('tgu_ms_rack_internal as rack', 'mutasi.ms_rack_code', '=', 'rack.rack_internal_code')
                ->select([
                    'mutasi.count                            as count',
                    'mutasi.rec_userupdate                   as rec_userupdate',
                    'mutasi.tr_inv_rack_code                 as tr_inv_rack_code',
                    'mutasi.ms_rack_code                     as ms_rack_code',
                    'rack.rack_internal_code                 as rack_internal_code',
                    'rack.rack_principal_code                as rack_principal_code',
                    'rack.rack_business                      as rack_business',
                    'rack.rack_branch                        as rack_branch',
                    'mutasi.ms_product_code                  as ms_product_code',
                    'mutasi.ms_product_business_code         as ms_product_business_code',
                    'mutasi.ms_product_business_order_code   as ms_product_business_order_code',
                    DB::raw('sku_bisnis.SKU_description AS description'),
                    'mutasi.Qty_in                           as qty_in',
                    'mutasi.Qty_out                          as qty_out',
                    'mutasi.Stock_akhir                      as stock_akhir',
                    DB::raw('sku_bisnis.SKU_convertpcs AS skuconvert'),
                    DB::raw('CONVERT(date, mutasi.exp_date_in) AS exp_date_in'),
                    DB::raw('ISNULL(mutasi.usr_cnt_last_stock, 0) AS usr_cnt_last_stock'),
                    'mutasi.rec_dateupdate                   as rec_dateupdate',
                ])
                ->distinct()
                ->orderByDesc('mutasi.count');

            if ($branch !== '') {
                $query->where('mutasi.rec_areacode', $branch);
            }
            if ($client !== '') {
                $query->where('mutasi.unit_bisnis', $client);
            }
            if ($rack !== '') {
                $query->where('mutasi.ms_rack_code', $rack);
            }
            if ($sku !== '') {
                // SP punya 3 param (default/order/internal). UI hanya 1 input SKU —
                // cocokkan ke salah satu dari ketiga kolom.
                $query->where(function ($q) use ($sku) {
                    $q->where('mutasi.ms_product_business_code', $sku)
                      ->orWhere('mutasi.ms_product_business_order_code', $sku)
                      ->orWhere('mutasi.ms_product_code', $sku)
                      ->orWhere('sku_bisnis.SKU_description', 'like', '%' . $sku . '%');
                });
            }

            $paginator = $query->paginate($perPage)->withQueryString();
            $offset    = ($paginator->currentPage() - 1) * $paginator->perPage();
            $rows = collect($paginator->items())->map(function ($r, $i) use ($offset) {
                $dateUpdate = '';
                if ($r->rec_dateupdate) {
                    try {
                        $dateUpdate = date('Y-m-d H:i', strtotime((string) $r->rec_dateupdate));
                    } catch (\Exception $e) {
                        $dateUpdate = '';
                    }
                }
                return (object) [
                    'no'              => $r->count !== null ? $r->count : ($offset + $i + 1),
                    'user_update'     => $r->rec_userupdate ?? '',
                    'tr_inv_rack_code' => $r->tr_inv_rack_code ?? '',
                    'rec_dateupdate'  => $dateUpdate,
                    'ms_rack_code'    => $r->ms_rack_code ?? '',
                    'rack'            => $r->rack_internal_code ?? '',
                    'product_code'    => $r->ms_product_code ?? '',
                    'business_code'   => $r->ms_product_business_code ?? '',
                    'order_code'      => $r->ms_product_business_order_code ?? '',
                    'description'    => $r->description ?? '',
                    'skuconvert'     => $r->skuconvert ?? '',
                    'qty_in'         => $r->qty_in ?? 0,
                    'qty_out'        => $r->qty_out ?? 0,
                    'stock_akhir'    => $r->stock_akhir ?? 0,
                    'cnt_last_stock' => $r->usr_cnt_last_stock ?? 0,
                    'exp_date_in'    => $r->exp_date_in ? date('Y-m-d', strtotime((string) $r->exp_date_in)) : '',
                ];
            });
        }

        // Maksimum nilai No (mutasi.count) untuk default range export — mengikuti filter aktif
        $maxCount = 0;
        if ($hasFilter && isset($query)) {
            $maxQuery = clone $query;
            $maxQuery->orders = null;
            $maxQuery->limit  = null;
            $maxQuery->offset = null;
            $maxCount = (int) ($maxQuery->max('mutasi.count') ?? 0);
        } else {
            $maxCount = (int) ($conn->table('TGU_tr_inv_main_mutasi_rack')->max('count') ?? 0);
        }

        return view('gudang.kartu_stock', [
            'rows'       => $rows,
            'paginator'  => $paginator,
            'cabangList' => $cabangList,
            'clientList' => $clientList,
            'rackList'   => $rackList,
            'maxCount'   => $maxCount,
            'filters'    => [
                'branch'   => $branch,
                'client'   => $client,
                'rack'     => $rack,
                'sku'      => $sku,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * GET /gudang/kartu-stock/export
     * Export hasil filter Kartu Stock ke XLSX.
     */
    public function kartuStockExport(Request $request)
    {
        $conn = DB::connection('rcm_ol_tgu');

        $branch = trim((string) $request->input('branch', ''));
        $client = trim((string) $request->input('client', ''));
        $rack   = trim((string) $request->input('rack', ''));
        $sku    = trim((string) $request->input('sku', ''));
        $countFrom = $request->filled('count_from') ? (int) $request->input('count_from') : null;
        $countTo   = $request->filled('count_to')   ? (int) $request->input('count_to')   : null;

        // Filter Branch dan Client wajib diisi (Rack opsional)
        if ($branch === '' || $client === '') {
            abort(422, 'Filter Branch dan Client wajib diisi sebelum export.');
        }

        $query = $conn->table('TGU_tr_inv_main_mutasi_rack as mutasi')
            ->leftJoin('tgu_ms_product_Business as sku_bisnis', function ($j) {
                $j->on('mutasi.ms_product_business_order_code', '=', 'sku_bisnis.SKU_Business')
                  ->on('mutasi.unit_bisnis', '=', 'sku_bisnis.Business');
            })
            ->leftJoin('tgu_ms_rack_internal as rack', 'mutasi.ms_rack_code', '=', 'rack.rack_internal_code')
            ->select([
                'mutasi.count                            as count',
                'mutasi.rec_userupdate                   as rec_userupdate',
                'mutasi.tr_inv_rack_code                 as tr_inv_rack_code',
                'rack.rack_internal_code                 as rack_internal_code',
                'rack.rack_principal_code                as rack_principal_code',
                'rack.rack_business                      as rack_business',
                'rack.rack_branch                        as rack_branch',
                'mutasi.ms_product_code                  as ms_product_code',
                'mutasi.ms_product_business_code         as ms_product_business_code',
                'mutasi.ms_product_business_order_code   as ms_product_business_order_code',
                DB::raw('sku_bisnis.SKU_description AS description'),
                'mutasi.Qty_in                           as qty_in',
                'mutasi.Qty_out                          as qty_out',
                'mutasi.Stock_akhir                      as stock_akhir',
                DB::raw('sku_bisnis.SKU_convertpcs AS skuconvert'),
                DB::raw('CONVERT(date, mutasi.exp_date_in) AS exp_date_in'),
                DB::raw('ISNULL(mutasi.usr_cnt_last_stock, 0) AS usr_cnt_last_stock'),
                'mutasi.rec_dateupdate                   as rec_dateupdate',
            ])
            ->distinct()
            ->orderByDesc('mutasi.count');

        if ($branch !== '') { $query->where('mutasi.rec_areacode', $branch); }
        if ($client !== '') { $query->where('mutasi.unit_bisnis', $client); }
        if ($rack   !== '') { $query->where('mutasi.ms_rack_code', $rack); }
        if ($sku    !== '') {
            $query->where(function ($q) use ($sku) {
                $q->where('mutasi.ms_product_business_code', $sku)
                  ->orWhere('mutasi.ms_product_business_order_code', $sku)
                  ->orWhere('mutasi.ms_product_code', $sku)
                  ->orWhere('sku_bisnis.SKU_description', 'like', '%' . $sku . '%');
            });
        }
        if ($countFrom !== null) { $query->where('mutasi.count', '>=', $countFrom); }
        if ($countTo   !== null) { $query->where('mutasi.count', '<=', $countTo); }

        $headings = [
            'No', 'User Update', 'Tr Inv Rack Code', 'Rack Internal', 'Rack Principal',
            'Bisnis', 'Branch', 'Product Code', 'Business Code', 'Order Code',
            'Description', 'Convert', 'QTY In', 'QTY Out', 'Stock Akhir',
            'Cnt Last Stock', 'Exp Date In',
        ];

        $no = 0;
        $rows = $query->get()->map(function ($r) use (&$no) {
            $no++;
            return [
                $r->count ?? $no,
                $r->rec_userupdate ?? '',
                $r->tr_inv_rack_code ?? '',
                $r->rack_internal_code ?? '',
                $r->rack_principal_code ?? '',
                $r->rack_business ?? '',
                $r->rack_branch ?? '',
                $r->ms_product_code ?? '',
                $r->ms_product_business_code ?? '',
                $r->ms_product_business_order_code ?? '',
                $r->description ?? '',
                $r->skuconvert ?? '',
                $r->qty_in ?? 0,
                $r->qty_out ?? 0,
                $r->stock_akhir ?? 0,
                $r->usr_cnt_last_stock ?? 0,
                $r->exp_date_in ? date('Y-m-d', strtotime((string) $r->exp_date_in)) : '',
            ];
        })->all();

        $filename = 'kartu-stock-' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TrackInOutExport($rows, $headings, 'Kartu Stock'),
            $filename
        );
    }

    /**
     * GET /gudang/kartu-stock/rack-options
     * Ambil daftar rack berdasarkan filter Branch + Client + SKU/Deskripsi.
     */
    public function kartuStockRackOptions(Request $request)
    {
        $conn = DB::connection('rcm_ol_tgu');

        $branch = trim((string) $request->input('branch', ''));
        $client = trim((string) $request->input('client', ''));
        $sku    = trim((string) $request->input('sku', ''));

        if ($branch === '' || $client === '' || $sku === '') {
            return response()->json([
                'message' => 'Filter Branch, Client, dan SKU/Deskripsi wajib diisi.',
                'data'    => [],
            ], 422);
        }

        // Ambil stock hanya dari baris TOP (teratas) per rack sesuai urutan tabel
        $racksWithStock = DB::connection('rcm_ol_tgu')->select(
            "WITH RankedRacks AS (
                SELECT 
                    mutasi.ms_rack_code,
                    mutasi.Stock_akhir,
                    ROW_NUMBER() OVER (PARTITION BY mutasi.ms_rack_code ORDER BY mutasi.count DESC) as rn
                FROM TGU_tr_inv_main_mutasi_rack mutasi
                LEFT JOIN tgu_ms_product_Business sku_bisnis
                    ON mutasi.ms_product_business_order_code = sku_bisnis.SKU_Business
                    AND mutasi.unit_bisnis = sku_bisnis.Business
                WHERE mutasi.rec_areacode = ?
                  AND mutasi.unit_bisnis = ?
                  AND (mutasi.ms_product_business_code = ?
                       OR mutasi.ms_product_business_order_code = ?
                       OR mutasi.ms_product_code = ?
                       OR sku_bisnis.SKU_description LIKE ?)
                  AND mutasi.ms_rack_code IS NOT NULL
                  AND mutasi.ms_rack_code != ''
            )
            SELECT ms_rack_code, Stock_akhir
            FROM RankedRacks
            WHERE rn = 1
            ORDER BY ms_rack_code",
            [$branch, $client, $sku, $sku, $sku, '%' . $sku . '%']
        );

        $racksWithStock = collect($racksWithStock)->map(function ($r) {
            return [
                'code' => $r->ms_rack_code,
                'stock' => (int) ($r->Stock_akhir ?? 0),
            ];
        })->values();

        return response()->json([
            'data' => $racksWithStock,
        ]);
    }
}