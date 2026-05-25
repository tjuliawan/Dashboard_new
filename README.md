# 📊 Dashboard Management System

![Laravel](https://img.shields.io/badge/Laravel-8.x-red.svg)  ![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)  ![License](https://img.shields.io/badge/License-MIT-green.svg)  ![Status](https://img.shields.io/badge/Status-Active-brightgreen.svg)

**Dashboard Management System** is a web application built with **Laravel** designed to help logistics and warehouse operations monitor delivery performance, manage warehouse stock, and track dispatch activities in real-time. This application uses the **Soft UI** template for a modern and responsive user interface.

---

## 🎯 Key Features

### 🚛 Last Mile
- ✅ **POD Summary** — delivery summary with status breakdown (Delivered, Pending, Cancel) and chart visualization
- ✅ **POD Report** — detailed POD report per dispatch with invoice-level drill-down modal
- ✅ **Report Last Mile** — last mile driver performance, pagination, and detail per driver
- ✅ **Dispatch Track** — real-time dispatch tracking timeline with handheld scan monitoring per driver

### 🏭 Warehouse
- ✅ **Rekap Stock Rack** — stock recap per rack with COGS, price, and value calculation
- ✅ **Price List** — price list management by SKU with supplier and branch filter
- ✅ **Track In/Out** — inbound/outbound warehouse transaction tracking with date filters (PO date for IN, Request date for OUT), detail modals, print, and Excel export
- ✅ **Kartu Stock** — stock card history per rack/SKU/client with autocomplete filter and Excel export

### 🔧 Fleet Management
- ✅ Fleet Management module (in development)

### ⚙️ System
- ✅ **User Activation** — manage user access and activation (Administrator only)
- ✅ **Authentication** — login, forgot password, reset password
- ✅ **DB Switcher** — toggle between HGS and TGU database on-the-fly
- ✅ **Responsive Design** — fully responsive across all device sizes
- ✅ **Export Excel** — export data for Track In/Out, Rekap Stock Rack, Kartu Stock

---

## 🗂️ Module Overview

| Module | Route Prefix | Controller |
|--------|-------------|------------|
| POD Summary | `/pod/summary` | `PodSummController` |
| POD Report | `/pod/detail` | `PodDetController` |
| Last Mile | `/lastmile` | `LastMileController` |
| Dispatch Track | `/dispatch-track` | `DispatchTrackController` |
| Rekap Stock Rack | `/gudang/rekap-stock-rack` | `GudangController` |
| Price List | `/gudang/price-list` | `GudangController` |
| Track In/Out | `/gudang/track-in-out` | `GudangController` |
| Kartu Stock | `/gudang/kartu-stock` | `GudangController` |
| User Activation | `/user-activation` | `userController` |

---

## 🛠️ Technologies Used

- **Laravel 8.x**
- **PHP 8.2+**
- **MS SQL Server** (via `rcm_hgs` / `rcm_ol_tgu` connections)
- **Bootstrap** (Soft UI Template)
- **jQuery & AJAX** for dynamic modals and fetch requests
- **Laravel Excel (Maatwebsite)** for Excel export
- **Composer** & **NPM**

---

## ⚙️ Installation

### 1. Clone Repository

```bash
git clone https://github.com/tjuliawan/Dashboard_new.git
cd Dashboard_new
```

### 2. Install Dependencies

```bash
composer install
npm install && npm run dev
```

### 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` — sesuaikan koneksi database SQL Server (`DB_CONNECTION=sqlsrv`).

### 4. Run Application

```bash
php artisan serve
```

---

## 📱 Social Media

- [Instagram](https://www.instagram.com/garjuliawan/)
- [LinkedIn](https://www.linkedin.com/in/tegar-juliawan-9285891a7)

---

## Credits

- [Creative Tim](https://creative-tim.com/?ref=sudl-readme)
