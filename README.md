# Monitoring Sales System

Laravel 12 + Filament 3.3  
Multi-Panel • Dynamic Resource • Dynamic Approval Workflow

## 📌 Ringkasan

Sistem **Monitoring Sales** ini dibangun menggunakan **Laravel 12** dan **Filament 3.3** dengan konsep:

-   **Multi Panel Filament** (`/admin`, `/sales`, `/akunting`, `/gudang`, `/teknisi`)
-   **Resource dinamis per panel** (diatur dari panel `/admin`, tanpa hardcode)
-   **Workflow Persetujuan (Approval) dinamis**
-   **End-to-end sales flow**: Visit → Lead → Closing → Penagihan → Pengiriman → Instalasi → After Sales
-   **Role & Permission berbasis Spatie**

Sistem ini dirancang untuk kebutuhan enterprise dan **siap dikembangkan lebih lanjut**.

---

## 🧩 Arsitektur Panel

| Panel    | Path        | Fungsi                                                   |
| -------- | ----------- | -------------------------------------------------------- |
| Admin    | `/admin`    | Manajemen panel, resource, approval workflow, user, role |
| Sales    | `/sales`    | Monitoring Visit, Lead, Pipeline, Closing, After Sales   |
| Akunting | `/akunting` | Pembayaran, Penagihan, Approval Invoice                  |
| Gudang   | `/gudang`   | Pengiriman, DO, Dokumen Pendukung                        |
| Teknisi  | `/teknisi`  | Instalasi, Assignment, Surat Penagihan                   |

---

## ⚙️ Teknologi Utama

-   Laravel 12
-   Filament 3.3
-   PHP 8.3+
-   MySQL / PostgreSQL
-   spatie/laravel-permission

---

## 🔁 Alur Bisnis

Visit Plan → Visit Real → Lead → Pipeline → Closing → SO → Penagihan → Pembayaran → Pengiriman → Instalasi → After Sales

---

## ✅ Sistem Persetujuan (Approval)

-   Approve: catatan opsional
-   Reject: alasan WAJIB diisi
-   Multi-step approval
-   Approver berbasis User atau Role
-   Rule: any / all

---

## 🚀 Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan filament:install
```

Akses:

-   /admin
-   /sales
-   /akunting
-   /gudang
-   /teknisi

---

## 📄 Lisensi

Internal / Proprietary
