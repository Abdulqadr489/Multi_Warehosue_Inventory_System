# Multi-Warehouse & Multi-Country Inventory API

Backend API for a **Multi-Warehouse, Multi-Country Inventory Management System** built with Laravel.

The system manages products across multiple warehouses in different countries, with:

- Full CRUD for core entities (countries, warehouses, products, suppliers)
- Inventory tracking per warehouse
- Inventory transfers between warehouses (even across countries)
- Global inventory view per product
- Daily scheduled low-stock report (email + optional Slack)
- JWT-secured API with auto-generated documentation

This project is implemented as part of an interview assignment.

---

## Tech Stack

- **Framework:** Laravel (API only)
- **Auth:** JWT Authentication (`auth:api` guard)
- **Database:** MySQL
- **Docs:** [dedoc/scramble](https://github.com/dedoc/scramble) (OpenAPI 3, UI at `/docs/api`)
- **Scheduler:** Laravel Scheduler & Console Commands
- **Testing:** PHPUnit (`php artisan test`)

---

## Requirements & Features Overview

### Core Modules

- **Country**
    - Fields: `id`, `name`, `code` (ISO)
    - CRUD endpoints

- **Warehouse**
    - Fields: `id`, `name`, `location`, `country_id`
    - Belongs to `Country`
    - CRUD endpoints

- **Product**
    - Fields: `id`, `name`, `sku`, `status`, `description`, `price`
    - CRUD endpoints

- **Supplier**
    - Fields: `id`, `name`, `contact_info`, `address`
    - CRUD endpoints

- **Inventory**
    - Fields: `id`, `product_id`, `warehouse_id`, `quantity`, `minimum_quantity`
    - Represents stock of a product in a specific warehouse

- **InventoryTransaction**
    - Fields: `id`, `product_id`, `warehouse_id`, `supplier_id`, `quantity`,
      `transaction_type` (`IN`/`OUT`), `date`, `created_by`
    - Records stock movements (purchases, sales, adjustments)
    - Prevents `OUT` when stock is insufficient in that warehouse

- **InventoryTransfer**
    - Transfers stock between warehouses (even across different countries)
    - Validates source warehouse stock before completing transfer
    - Adjusts inventory for both source and destination warehouses

---

## API Design

All API routes are prefixed with `/api` and protected by **JWT** authentication, except `register` & `login`.

### Authentication

- `POST /api/register` – Create a new user
- `POST /api/login` – Obtain a JWT access token

**Usage:**

1. Call `POST /api/login` with email/password.
2. Copy `access_token` from the response.
3. For all subsequent requests, send:

   ```http
   Authorization: Bearer <access_token>
   Accept: application/json

## Main Endpoints

> Exact request/response schemas are visible and testable in `/docs/api`.

### Countries

- `GET /api/countries` – List countries (supports pagination, search/sort via `BaseListRequest`)
- `POST /api/countries` – Create country
- `PUT /api/countries/{id}` – Update country
- `DELETE /api/countries/{id}` – Delete country

### Warehouses

- `GET /api/warehouses`
- `POST /api/warehouses`
- `PUT /api/warehouses/{id}`
- `DELETE /api/warehouses/{id}`

### Products

- `GET /api/products`
- `POST /api/products`
- `PUT /api/products/{id}`
- `DELETE /api/products/{id}`

### Suppliers

- `GET /api/suppliers`
- `POST /api/suppliers`
- `PUT /api/suppliers/{id}`
- `DELETE /api/suppliers/{id}`

### Inventory Transactions

- `POST /api/inventory_transactions`  

Records an `IN` or `OUT` transaction:

- Validates `product_id`, `warehouse_id`, `supplier_id`
- Validates `transaction_type` ∈ `{IN, OUT}`
- For **IN**:
    - Increases inventory for that product/warehouse, or creates it if missing
- For **OUT**:
    - Checks available quantity in that warehouse
    - Rejects with a clear error if stock is insufficient (e.g. “Insufficient stock in this warehouse.”)
    - Otherwise, decreases quantity

### Inventory Transfers

- `POST /api/inventory_transfer`

Transfers a quantity of a product from one warehouse to another:

- Validates source warehouse stock
- Decreases inventory in the source warehouse
- Increases (or creates) inventory in the destination warehouse

### Global Inventory View

- `GET /api/inventory/global-view`

Returns aggregated stock per product across all warehouses:

- Aggregated total quantity per product
- Optional filters:
    - `country_id` or `country_name`
    - `warehouse_id` or `warehouse_name`

### Low Stock Report

- `GET /api/reports/low_stock`  
  (matches the requirement’s `GET /api/reports/low-stock`)

Returns products where `quantity <= minimum_quantity` per warehouse, including:

- Product Name
- SKU
- Current Quantity
- Minimum Required Quantity
- Warehouse Location
- Country
- Supplier Contact Information (if linked)

## Project Structure

Key directories and patterns:

### `app/Models/`

- `Country`, `Warehouse`, `Product`, `Supplier`, `Inventory`,
  `InventoryTransaction`, `InventoryTransfer`, `User`, etc.

### `app/Http/Controllers/`

- Resource controllers (e.g. `Countries\CountryController`, `Warehouses\WarehouseController`, etc.)
- Controllers are thin: they handle HTTP, authorize, validate, and delegate to services.

### `app/Http/Requests/`

FormRequests for validation:

- `Countries/CreateCountryRequest`, `Countries/UpdateCountryRequest`
- Similar requests for Warehouse, Product, Supplier
- `BaseList\BaseListRequest` for list filters & pagination
- `Inventories/InventoryGlobalViewRequest` for global view filters

### `app/Repositories/`

- Repository classes that encapsulate Eloquent queries
- Used by services so controllers don’t touch DB directly

### `app/Services/`

Service layer for business logic:

- `CountryService`, `WarehouseService`, `ProductService`, `SupplierService`
- `InventoryService`, `InventoryTransferService`, `InventoryReportService`, etc.

### `app/Console/Commands/`

- `InventoryCheckLowStock` – console command to generate/send low stock report

### `app/bootstrap/app.php`

- Schedules the low-stock job daily at `00:00`

### `app/Repositories/Traits/ApiResponse.php`

- Reusable trait providing uniform JSON `success()` and `error()` responses

### `tests/`

- Unit & feature tests for core flows (countries CRUD, inventory transactions,inventory transfer,low stock report)

## Getting Started

### 1. Clone & Install Dependencies

git clone https://github.com/<your-username>/<your-repo>.git

cd <your-repo>

composer install
### 2. Environment Configuration

Copy the example env file:

- cp .env.example .env


Generate the app key:

- php artisan key:generate

Generate the JWT key:

- php artisan jwt:secret

### 3. Database Migrations

- php artisan migrate
### 4. Run the Application

- php artisan serve


