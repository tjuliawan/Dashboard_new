# Dashboard POD Last-Mile — Checklist Progress & Perencanaan

> Dokumen perencanaan & tracking pengembangan dashboard POD Last-Mile.
> Status: **Draft** · Update terakhir: 30 April 2026 (SPK & Cancel done, Data Per Driver in-progress)

---

## Legenda Status

- [ ] Belum dikerjakan
- [~] Sedang dikerjakan
- [x] Selesai
- [!] Blocked / butuh konfirmasi

---

## 1. Monitoring Penggunaan Handheld

**Tujuan:** Memastikan setiap driver benar-benar memakai handheld (aktif login & sinkron ke RCM).

### Perencanaan
- [ ] Identifikasi tabel/log aktivitas handheld di RCM (login, last-sync, last-scan)
- [ ] Tentukan definisi "aktif": ada transaksi/sync dalam X jam terakhir
- [ ] Mapping driver ↔ device (handheld ID / user ID)
- [ ] Desain widget: list driver + indikator aktif/tidak aktif (badge hijau/merah)
- [ ] Tentukan threshold idle (mis. >2 jam tanpa sync = tidak aktif)

### Progress
- [ ] Query sumber data handheld activity
- [ ] Endpoint controller `HandheldMonitorController`
- [ ] View / card di dashboard
- [ ] Auto-refresh (polling tiap N menit)
- [ ] Testing dengan data live

---

## 2. Real-Time Progress Pengiriman

**Tujuan:** Memantau jalannya pengiriman secara live per jam.

### Perencanaan
- [ ] Tentukan sumber timestamp (POD time / scan time per invoice)
- [ ] Definisi "jam tiba di toko pertama" = MIN(pod_time) per driver per hari
- [ ] Hitung persentase selesai = delivered / total_invoice per driver
- [ ] Desain timeline / progress bar per driver
- [ ] Refresh interval (mis. 5 menit)

### Progress
- [ ] Query update pengiriman per jam (group by HOUR)
- [ ] Query first arrival per driver
- [ ] Query progress % per jam
- [ ] Chart: line/bar progress per jam
- [ ] Tabel ringkasan per driver (jam mulai, % selesai, ETA)
- [ ] Auto-refresh frontend

---

## 3. SLA & Performa

**Tujuan:** Mengukur ketepatan pengiriman terhadap target SLA.

### Perencanaan
- [ ] Definisi formula SLA (mis. delivered_on_time / total_invoice * 100)
- [ ] Definisi "on time" (cut-off jam berapa? per area?)
- [ ] Cut-off harian (kapan SLA dihitung final)
- [ ] Historical store: tabel agregasi SLA harian
- [ ] Filter: harian / mingguan / tahunan

### Progress
- [ ] SLA harian (akhir hari)
  - [ ] Query agregasi
  - [ ] Card / KPI di dashboard
- [ ] Perbandingan SLA per minggu
  - [ ] Chart line week-over-week
- [ ] Perbandingan SLA per tahun
  - [ ] Chart line/bar year-over-year
- [ ] Drill-down per area / per driver
- [ ] Export laporan SLA

---

## 4. Data Per Driver

**Tujuan:** Rekap performa setiap driver dalam satu tampilan.

### Perencanaan
- [x] Mapping kolom: driver_code → driver_name (`ms_driver.Drv_FistName`)
- [!] Definisi "POD di lokasi" (geofence? GPS match?) — sumber data belum ditentukan
- [!] Sumber GPS / lokasi POD (jika ada)
- [x] Desain tabel: Tanggal | Driver | Kendaraan | Dispatch Code | Total Inv | Terkirim | Cancel | POD on-site

### Progress
- [x] Query rekap per driver (join `TGU_dispatch_h` + `ms_driver`)
- [x] Split per `Dpcth_code_h` (1 baris = 1 dispatch code)
- [x] Kolom Nama Driver
- [x] Kolom Kendaraan (`Dpcth_vhcl_code`)
- [x] Kolom Dispatch Code
- [x] Kolom Total Invoice
- [x] Kolom Terkirim (status DELIVERED)
- [x] Kolom Cancel (status CANCEL)
- [ ] Kolom POD di lokasi (Ya/Tidak / %) — menunggu sumber data
- [x] Filter tanggal (`Dptch_date`) — default **7 hari terakhir** (`today-7` s/d `today`)
- [x] Pagination 20/25/50/75/100 (default 20)
- [x] Modal drill-down daftar invoice per dispatch (SO, Status, Value)
- [ ] Filter area
- [ ] Export Excel

---

## 5. SPK & Cancel

**Tujuan:** Memastikan tidak ada SPK menggantung & reason cancel valid.

**Catatan domain:** `dpch_status` hanya 3 nilai → `OPEN` (ongoing), `DELIVERED` (terkirim), `CANCEL` (tidak terkirim).
**Definisi SPK Menggantung** (sesuai konfirmasi user): `dpch_status = 'CANCEL'` (tidak terkirim).

### Perencanaan
- [x] Definisi "SPK menggantung": `dpch_status = 'CANCEL'`
- [x] Validasi mandatory `dpch_resaon` untuk status cancel (data quality check di dashboard)
- [!] Daftar master reason cancel (lookup table) — saat ini text bebas di kolom `dpch_resaon`
- [x] Desain alert / list SPK pending

### Progress
- [x] SPK menggantung
  - [x] Query: `dpch_status = 'CANCEL'`
  - [x] KPI card di dashboard
- [x] Ongoing (OPEN)
  - [x] KPI card terpisah: `dpch_status = 'OPEN'`
- [x] Total Invoice — KPI card
- [x] Validasi reason cancel tidak kosong
  - [x] Query: cancel dengan reason NULL/empty (data quality check)
  - [ ] (Opsional) enforce di form input RCM
- [x] Breakdown reason cancel
  - [x] Query GROUP BY reason (`dpch_resaon`), bucket NULL/empty → `(Tanpa Reason)`
  - [x] Tabel distribusi reason + persentase
  - [x] Drill-down ke list invoice per reason (modal: Tanggal, Dispatch Code, SO, Driver, Reason)
  - [ ] Pie/bar chart distribusi reason
- [x] Filter range (3 / 7 / 30 hari terakhir, default 7) — terpisah dari Data Per Driver

---

## Milestone Ringkas

| # | Modul | Target | Status |
|---|-------|--------|--------|
| 1 | Monitoring Handheld | - | [ ] |
| 2 | Real-Time Progress | - | [ ] |
| 3 | SLA & Performa | - | [ ] |
| 4 | Data Per Driver | - | [~] |
| 5 | SPK & Cancel | - | [x] |

---

## Catatan Teknis

- DB connection: `rcm_hgs` (SQL Server)
- Tabel utama:
  - `TGU_dispatch_h` (header dispatch — kolom: `Dpcth_code_h`, `Dptch_date`, `Dpcth_drv_code`, `Dpcth_vhcl_code`, `dpcth_SO`, `dpch_status`, `dpch_value`, `dpch_resaon`)
  - `TGU_dispatch_main` (detail invoice per dispatch) — belum dipakai di modul aktif
  - `TGU_dispatch_d` (detail SO) — belum dipakai di modul aktif
  - `ms_driver` — master driver (kolom: `drv_id`, `Drv_FistName`)
- Stack: Laravel + Blade + Chart.js (Chart.js belum dipakai)
- Status `dpch_status`: hanya 3 nilai → `OPEN` (ongoing), `DELIVERED` (terkirim), `CANCEL` (tidak terkirim)
- Endpoint Last Mile aktif:
  - `GET /lastmile` — `lastmile.index`
  - `GET /lastmile/invoices` — `lastmile.invoices` (drilldown invoice per dispatch)
  - `GET /lastmile/cancel-detail` — `lastmile.cancel-detail` (drilldown reason cancel)
- Periode default per modul:
  - Data Per Driver: 7 hari terakhir (`today-7` s/d `today`), bisa di-override via `date_from`/`date_to`
  - SPK & Cancel: dropdown 3/7/30 hari terakhir (default 7)
- Pertanyaan terbuka:
  - [!] Tabel/log aktivitas handheld → nama tabel di RCM?
  - [!] Sumber GPS POD on-site → tersedia kolom lat/long?
  - [!] Master reason cancel → tabel lookup atau free text? (saat ini free text)
  - [!] Definisi cut-off SLA per area / nasional?
