# Data Source Mapping — Last Mile & Warehouse

Dokumen ini memetakan setiap halaman/menu pada modul **Last Mile** dan **Warehouse (Gudang)** ke tabel database sumbernya. Semua koneksi memakai `DB::connection('rcm_ol_tgu')` (SQL Server).

---

## Last Mile

### POD Summary
- **Route:** `GET /pod/summary` → `PodSummController@index`
- **View:** `pod.summary`
- **Tabel utama:**
  - `TGU_dispatch_h` — header dispatch (driver, tanggal SPK, status delivery)
  - `TGU_dispatch_main` — agregat status pengiriman per dispatch (Delivered / Pending / Cancel)
- **Tabel lookup:**
  - `ms_driver` (lewat `Dpcth_drv_code`) — nama driver

### POD Report
- **Route:** `GET /pod/detail` → `PodDetController@index`
  - `GET /pod/detail/calculate` — hitung ulang ringkasan
  - `GET /pod/detail/export`    — export Excel
- **View:** `pod.detail`
- **Tabel utama (subquery gabungan):**
  - `TGU_dispatch_h`     — header dispatch
  - `TGU_dispatch_main`  — status & qty pengiriman
  - `TGU_dispatch_d`     — detail line per invoice/SKU

### Last Mile Dashboard
- **Route:** `GET /lastmile` → `LastMileController@index`
- **View:** `lastmile.index`
- **Tabel utama:**
  - `TGU_dispatch_h` — header dispatch (filter per tanggal SPK / range mingguan / bulanan / tahunan)
- **Lookup:**
  - `ms_driver` — nama driver

### Last Mile — Invoices
- **Route:** `GET /lastmile/invoices` → `LastMileController@invoices`
- **Tabel utama:** `TGU_dispatch_h` + `ms_driver`

### Last Mile — Cancel Detail
- **Route:** `GET /lastmile/cancel-detail` → `LastMileController@cancelDetail`
- **Tabel utama:** `TGU_dispatch_h` + `ms_driver` (filter status cancel)

### Dispatch Track
- **Route:**
  - `GET /dispatch-track`        → `DispatchTrackController@index`
  - `GET /dispatch-track/detail` → `DispatchTrackController@detail`
- **Tabel utama:**
  - `TGU_dispatch_main` — list dispatch + status
  - `TGU_dispatch_h`    — header (untuk detail)
- **Lookup:**
  - `ms_driver`

---

## Warehouse (Gudang)

### Rekap Stock Rack
- **Route:** `GET /gudang/rekap-stock-rack` → `GudangController@rekapStockRack`
- **View:** `gudang.rekap_stock_rack`
- **Tabel utama (stok):**
  - `TGU_tr_inv_main_mutasi_rack` — sumber utama stok per rack (Stock_akhir, exp_date, last user count, unit_bisnis)
  - `tgu_ms_rack_internal`        — master rack (driver di Mode A, lookup di Mode B)
- **Lookup harga / SKU:**
  - `tgu_ms_product_Business`     — deskripsi SKU, convert pcs, harga beli pcs (`SKU_hargabeli_pcs` → `price`)
  - `ms_cogs`                     — cogs pcs/ctn, last cogs, price list (lookup terbaru per SKU via `ROW_NUMBER()`)
- **Master dropdown:**
  - `ms_cabang`     — list cabang
  - `TGU_ms_gudang` — list gudang
- **Kolom turunan:** `value = ISNULL(price,0) * Stock_akhir`
- **Mode SP yang ditiru:**
  - Mode A (branch saja)         → `Sp_list_TGU_tr_inv_main_mutasi_rack_cabang`
  - Mode B (branch + gudang)     → `Sp_list_TGU_tr_inv_main_mutasi_rack_cabang_gudang3`

### Price List (Update by Supplier + by SKU)
- **Route:**
  - `GET  /gudang/price-list`               → `GudangController@priceList`
  - `POST /gudang/price-list`               → `priceListUpdate` (mini-form, forward ke `priceListStore`)
- **View:** `gudang.price_list`
- **Panel 1 — Update by Supplier:**
  - `tr_TGUPriceList`               — header pricelist
  - `tr_TGUPricelist_d`             — detail pricelist (SKU, harga pcs/ctn)
  - `TGU_ms_product_price`          — master harga (lowest/high/avg)
  - `ms_unit`                       — satuan
  - `tgu_ms_product_internal`       — bridging SKU internal
  - `tgu_ms_product_Business`       — deskripsi SKU & MOP
  - `ms_cabang`, `ms_supplier`      — lookup nama
- **Panel 2 — Price List by SKU:**
  - `TGU_ms_product_price`          — master harga per (SKU + Supplier + Branch)
  - `ms_cabang`, `ms_supplier`, `ms_unit`, `tgu_ms_product_Business`

### Price List — Form Add New
- **Route:**
  - `GET  /gudang/price-list/create`             → `priceListCreate`
  - `POST /gudang/price-list/store`              → `priceListStore`
  - `GET  /gudang/price-list/lookup-sku`         → `priceListLookupSku`
  - `GET  /gudang/price-list/lookup-pricemode`   → `priceListLookupPriceMode`
  - `GET  /gudang/price-list/lookup-unit`        → `priceListLookupUnit`
- **View:** `gudang.price_list_form`
- **Tabel dropdown:**
  - `ms_cabang`, `ms_supplier`, `tgu_ms_product_Business` (distinct `Business`), `TGU_ms_pricemode`, `ms_unit`
- **Tabel di-write saat store (mirror C# Confirm):**
  - `tr_TGUPriceList`             — INSERT header (`tr_TGU_pricelist_code_h`)
  - `tr_TGUPricelist_d`           — INSERT detail per SKU (+ snapshot harga sebelumnya `price_pcs_before`/`price_ctn_before`)
  - `tgu_ms_product_Supplier`     — UPSERT mapping SKU↔Supplier
  - `TGU_ms_product_price`        — UPSERT master harga (set Lowest/High/Avg saat baru)
  - `tgu_ms_product_Business`     — UPDATE harga jual & beli (`SKU_Hargajual_pcs/ctn`, `SKU_Hargabeli_pcs/ctn`)
- **Lookup last price:** subquery ke `tr_TGUPricelist_d` + `tr_TGUPriceList` (order by `Date_update DESC`)

### Track In / Out
- **Route:**
  - `GET /gudang/track-in-out`         → `GudangController@trackInOut`
  - `GET /gudang/track-in-out/detail`  → `GudangController@trackInOutDetail` (AJAX JSON, modal detail PO)
- **View:** `gudang.track_in_out`

#### Panel IN (Barang Masuk)
- **Tabel utama (list):**
  - `tr_tgu_purchase_order_parcial_h`  — PO header
    - `tgu_purchase_order_h_code` → **Nomor PO**
    - `tgu_purchase_order_date`   → **Tgl PO**
  - `wdms_tr_delivery_orders_h`        — DO (jembatan PO ↔ Tallysheet)
    - join: `do.do_h_po_code = po.tgu_purchase_order_h_code`
    - `do.do_h_code` dipakai sebagai key untuk join Tallysheet
  - `wdms_tr_tallysheet_h`             — Tallysheet
    - join: `tls.tls_h_no_do = do.do_h_code`
    - `tls_h_code`   → **Tallysheet**
    - `completed_at` → **Tgl Tallysheet**
  - `tgu_tr_inv_insupp_h`              — BTB
    - join: `btb.inv_insupp_dono = tls.tls_h_no_do`
    - `inv_insupp_code`       → **BTB**
    - `inv_insupp_date`       → **Tgl BTB**
    - `inv_insupp_inv_incode` → **Putaway**
  - `tgu_tr_inv_main_mutasi_rack`      — Qty
    - join via subquery `ROW_NUMBER() PARTITION BY tr_inv_rack_code ORDER BY rec_datecreated DESC` → ambil `rn = 1`
    - join: `mr.tr_inv_rack_code = tls.tls_h_code`
    - `ISNULL(QTY_in,0) + ISNULL(QTY_out,0)` → **Qty**
- **Tabel utama (detail modal):**
  - `tr_tgu_purchase_order_parcial_d`  — line PO
    - `tgu_purchase_order_sku_code` → **SKU**
    - `tgu_purchase_order_qty`      → **QTY**
    - `tgu_purchase_order_price`    → **Price**
    - filter: `LTRIM(RTRIM(tgu_purchase_order_h_code)) = ?`
  - `tgu_ms_product_Business`          — Description SKU
    - subquery `ROW_NUMBER() PARTITION BY SKU_master ORDER BY SKU_default DESC, SKU_Business`, ambil `rn = 1`
    - join: `m.SKU_master = d.tgu_purchase_order_sku_code`
    - `SKU_description` → **Description**
- **Kolom belum berisi data (placeholder):** _(semua sudah termapping)_

#### Panel OUT (Barang Keluar)
- **Tabel utama (list):**
  - `tgu_tr_inv_req_h`                 — Request
    - `req_part_h_code` → **Request**
    - `req_so_date`     → **Tgl Request**
  - `tgu_pickinglist_rack_h`           — Picking
    - join: `pl.pl_request = r.req_part_h_code`
    - `pl_rack_code` → **Picking**
    - `pl_rack_date` → **Tgl Picking**
  - `tgu_dispatch_main`                — Dispatch
    - join: `dp.dpcth_code_h = pl.pl_dispaching_h`
    - `dpcth_code_h` → **Dispatch**
    - `dptch_date`   → **Tgl Dispatch**
  - `tgu_dispatch_h`                   — POD
    - join via subquery `(SELECT dpcth_code_h, MIN(dptch_date) AS dptch_date FROM tgu_dispatch_h GROUP BY dpcth_code_h) pod`
    - `pod.dpcth_code_h = dp.dpcth_code_h`
    - `dpcth_code_h` → **POD**
    - `dptch_date`   → **Tgl POD**
- **Kolom belum berisi data (placeholder):** `BKB`, `Tgl BKB`, `BTB RV`, `Tgl BTB RV`, `Payment Kasir`, `Tgl Payment Kasir`

---

## Ringkasan Tabel per Modul

| Modul        | Tabel transaksi inti                                                                                                        | Master / lookup                                                                                                                       |
|--------------|------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| Last Mile    | `TGU_dispatch_h`, `TGU_dispatch_main`, `TGU_dispatch_d`                                                                      | `ms_driver`                                                                                                                            |
| Warehouse    | `TGU_tr_inv_main_mutasi_rack`, `tr_TGUPriceList`, `tr_TGUPricelist_d`, `tr_tgu_purchase_order_parcial_h/_d`, `wdms_tr_delivery_orders_h`, `wdms_tr_tallysheet_h`, `tgu_tr_inv_insupp_h`, `tgu_tr_inv_req_h`, `tgu_pickinglist_rack_h`, `tgu_dispatch_main`, `tgu_dispatch_h` | `tgu_ms_rack_internal`, `tgu_ms_product_Business`, `tgu_ms_product_internal`, `tgu_ms_product_Supplier`, `TGU_ms_product_price`, `ms_cogs`, `ms_cabang`, `ms_supplier`, `ms_unit`, `TGU_ms_gudang`, `TGU_ms_pricemode` |
