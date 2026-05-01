# Mini ERP — Laravel Inventory & Sales Management System

## Tech Stack
- **Laravel 11** (PHP 8.2+)
- **MySQL / SQLite**
- **Tailwind CSS** via Vite
- **Laravel Breeze** (auth scaffolding)

---

## Quick Setup

```bash
# 1. Create fresh Laravel project
composer create-project laravel/laravel mini-erp
cd mini-erp

# 2. Install Breeze (auth)
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build

# 3. Copy all source files from this archive into the project root
#    (overwrite where prompted)

# 4. Configure database in .env
DB_CONNECTION=mysql
DB_DATABASE=mini_erp
DB_USERNAME=root
DB_PASSWORD=your_password

# 5. Run migrations + seeder
php artisan migrate --seed

# 6. Start dev server
php artisan serve
# → http://localhost:8000
# Login: admin@erp.test / password
```

---

## Architecture

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php   ← stats aggregation
│   │   ├── ProductController.php     ← CRUD + search/filter
│   │   ├── SupplierController.php    ← CRUD
│   │   ├── PurchaseController.php    ← delegates to InventoryService
│   │   ├── SaleController.php        ← delegates to InventoryService
│   │   └── ReportController.php      ← delegates to ReportService
│   └── Requests/
│       ├── StoreProductRequest.php
│       ├── StorePurchaseRequest.php
│       └── StoreSaleRequest.php
├── Models/
│   ├── Product.php       (scopes: active, lowStock, search)
│   ├── Category.php
│   ├── Supplier.php
│   ├── Purchase.php      (scope: dateRange, generateReference)
│   ├── PurchaseItem.php
│   ├── Sale.php          (scope: dateRange, generateReference)
│   └── SaleItem.php
└── Services/
    ├── InventoryService.php   ← ALL stock mutation logic (DB::transaction)
    └── ReportService.php      ← ALL report queries
```

---

## Database Schema

```
categories          products
id, name            id, category_id, name, sku, price, cost_price,
                    stock_quantity, low_stock_threshold, unit, ...

suppliers           purchases               purchase_items
id, name,           id, supplier_id,        id, purchase_id,
phone, email,       reference, date,        product_id, qty, cost_price,
address             total_amount            subtotal (stored computed)

                    sales                   sale_items
                    id, reference,          id, sale_id, product_id,
                    customer_name,          qty, selling_price,
                    sale_date,              cost_price (snapshot),
                    total_amount            subtotal (stored computed)
```

---

## Key Business Rules (enforced in InventoryService)

| Rule | Where Enforced |
|------|---------------|
| Stock only changes via Purchase/Sale, never directly | `update()` strips `stock_quantity` |
| Negative stock impossible | Pre-flight check + row-level `lockForUpdate()` |
| Concurrent oversell prevented | `DB::transaction()` + pessimistic locking |
| Deleting purchase reverses stock | `deletePurchase()` checks before decrementing |
| Deleting sale restores stock | `deleteSale()` increments back |
| Profit uses cost_price at time of sale | `cost_price` snapshot in `sale_items` |

---

## Routes

| Method | URL | Action |
|--------|-----|--------|
| GET | / | Dashboard |
| GET/POST | /products | List / Create |
| GET/PUT/DELETE | /products/{id} | Show / Edit / Delete |
| GET/POST | /purchases | List / Create (triggers stock IN) |
| DELETE | /purchases/{id} | Reverse purchase (triggers stock reversal) |
| GET/POST | /sales | List / Create (triggers stock OUT) |
| DELETE | /sales/{id} | Cancel sale (restores stock) |
| GET | /reports/sales | Date-filtered sales report |
| GET | /reports/purchases | Date-filtered purchase report |
| GET | /reports/profit | Product-wise profit/margin breakdown |

---

## Things To Add For Production

1. **Role-based access** — use Spatie Laravel Permission
2. **Barcode scanner input** on sale/purchase forms
3. **PDF invoice export** — use `barryvdh/laravel-dompdf`
4. **CSV/Excel export** for reports — use `maatwebsite/excel`
5. **Stock adjustment** module (write-offs, corrections)
6. **Email/SMS low stock alerts** via Laravel notifications
7. **Multi-warehouse** support (location tracking per item)
8. **Audit log** — use `owen-it/laravel-auditing`
