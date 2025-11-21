# Multi-Warehouse & Multi-Country Inventory API

Backend API for a **Multi-Warehouse, Multi-Country Inventory Management System** built with Laravel.

The system manages products across multiple warehouses in different countries, with:

- CRUD for core entities (countries, warehouses, products, suppliers)
- Inventory tracking per warehouse
- Inventory transfers between warehouses (even across countries)
- Global inventory view per product
- Daily scheduled low-stock report (email + optional Slack)
- JWT-secured REST API
- Soft deletes where appropriate (e.g. countries, warehouses, products, suppliers)

---

##  Getting Started (Step by Step)

###  Clone & Install Dependencies

```bash
git clone https://github.com/Abdulqadr489/Multi_Warehosue_Inventory_System.git
cd Multi_Warehosue_Inventory_System

composer install
```

###  Environment Configuration

Copy the example `.env`:

```bash
cp .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

Generate JWT secret:

```bash
php artisan jwt:secret
```

Edit `.env` to set your DB and mail configuration:

```env
DB_DATABASE=multi_warehosue_inventory_system
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io      # or smtp.gmail.com
MAIL_PORT=2525                  # or 587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="Inventory API"

LOW_STOCK_REPORT_EMAIL=you@example.com

LOW_STOCK_SLACK_WEBHOOK=
```

###  Database Migrations (and Seeders if any)

```bash
php artisan migrate

php artisan db:seed   
```

### Run the Application

```bash
php artisan serve
```

Now you can:

- Explore the API docs at: `http://127.0.0.1:8000/docs/api`
- Import the Postman collection from `docs/Inventory System Management.postman_collection.json`
- Use `php artisan inventory:check-low-stock` to trigger the low stock report manually.

---

## 1. Tech Stack

- **Framework:** Laravel 12 (API-only style)
- **Auth:** JWT Authentication (tymon/jwt-auth)
- **Database:** MySQL
- **Docs:** [dedoc/scramble](https://github.com/dedoc/scramble)
    - Generates OpenAPI 3.0 spec and interactive UI at: `/docs/api`
- **Scheduling:** Laravel Scheduler
- **Console Commands:** `php artisan inventory:check-low-stock`
- **Caching:** Laravel Cache (products)
- **Notifications:** Mail + Slack (via webhook)
- **Testing:** PHPUnit (`php artisan test`)

---

## 2. Core Domain & Requirements

### 2.1 Modules

- **Country**
    - Fields: `id`, `name`, `code` (ISO-like)
    - Relationships: `hasMany(Warehouse)`
    - CRUD endpoints

- **Warehouse**
    - Fields: `id`, `name`, `location`, `country_id`
    - Relationships: `belongsTo(Country)`, `hasMany(Inventory)`, `hasMany(InventoryTransaction)`
    - Must be linked to an existing country
    - CRUD endpoints

- **Product**
    - Fields: `id`, `name`, `sku`, `status`, `description`, `price`
    - Relationships: `hasMany(Inventory)`, `hasMany(InventoryTransaction)`
    - `sku` is unique
    - CRUD endpoints

- **Supplier**
    - Fields: `id`, `name`, `contact_info`, `address`
    - Relationships: `hasMany(InventoryTransaction)`
    - CRUD endpoints

- **Inventory**
    - Fields: `id`, `product_id`, `warehouse_id`, `quantity`, `minimum_quantity`
    - Represents stock of a product in a specific warehouse
    - Relationships: `belongsTo(Product)`, `belongsTo(Warehouse)`

- **InventoryTransaction**
    - Fields: `id`, `product_id`, `warehouse_id`, `supplier_id`, `quantity`,
      `transaction_type` (`IN`/`OUT`), `date`, `created_by`
    - Relationships: `belongsTo(Product)`, `belongsTo(Warehouse)`, `belongsTo(Supplier)`, `belongsTo(User, 'created_by')`
    - Records stock movements (purchases, sales, adjustments)
    - Prevents `OUT` when stock is insufficient in that warehouse

- **Inventory Transfer**
    - Implemented via service-level operation
    - Transfers stock between warehouses (even across different countries)
    - Validates source warehouse stock before completing transfer
    - Adjusts inventory for both source and destination warehouses in a single DB transaction

---

## 3. Authentication

JWT is used to protect the API.

### 3.1 Endpoints

- `POST /api/register` – Register a new user (for testing)
- `POST /api/login` – Obtain a JWT access token

### 3.2 Usage

1. Call:

   ```http
   POST /api/login
   Content-Type: application/json

   {
     "email": "user@example.com",
     "password": "secret"
   }
   ```

2. Copy the `access_token` from the response.

3. For all protected endpoints, send:

   ```http
   Authorization: Bearer <access_token>
   Accept: application/json
   ```

Most `/api/*` routes (countries, warehouses, products, suppliers, inventory, reports) are wrapped in an `auth:api` middleware group so only authenticated users can access them.

---

## 4. Main API Endpoints (Overview)

> Exact request/response schemas are visible and testable in `/docs/api` (Scramble UI) and in the provided Postman collection.

### 4.1 Countries

- `GET /api/countries` – List countries (supports pagination, search, sort)
- `POST /api/countries` – Create country
- `PUT /api/countries/{id}` – Update country
- `DELETE /api/countries/{id}` – Soft delete country

### 4.2 Warehouses

- `GET /api/warehouses`
- `POST /api/warehouses`
- `PUT /api/warehouses/{id}`
- `DELETE /api/warehouses/{id}`

Listing supports:

- Search by warehouse name, location
- Search by related country name/code
- Sorting by warehouse fields, and by country name (via join)

### 4.3 Products

- `GET /api/products`
- `POST /api/products`
- `PUT /api/products/{id}`
- `DELETE /api/products/{id}`

Notes:

- `sku` is unique
- Frequently accessed `Product` reads are cached in `ProductRepository`
- Cache is invalidated when a product is updated or deleted

### 4.4 Suppliers

- `GET /api/suppliers`
- `POST /api/suppliers`
- `PUT /api/suppliers/{id}`
- `DELETE /api/suppliers/{id}`

### 4.5 Inventory Transactions (IN / OUT)

- `GET /api/inventory_transactions` – List transactions (with filters)
- `POST /api/inventory_transactions` – Record a transaction

Behavior:

- Validates `product_id`, `warehouse_id`, `supplier_id` (if present), `quantity`, and `transaction_type` (`IN` or `OUT`).
- **IN**:
    - Finds or creates `Inventory` for `(product_id, warehouse_id)`
    - Increases `quantity`
    - Updates `minimum_quantity` (if provided)
- **OUT**:
    - Ensures `Inventory` exists and `quantity >= requested`
    - If not, throws a business exception and returns error JSON (e.g. `"Insufficient stock in this warehouse."`)
    - Otherwise reduces `quantity`
- Each transaction creates an `InventoryTransaction` with `created_by = current user`.
- After stock adjustment, if `quantity <= minimum_quantity`, a `LowStockReached` event is dispatched.

### 4.6 Inventory Transfers (Movement)

- `POST /api/inventory_transfer`

Request example:

```json
{
  "product_id": 10,
  "from_warehouse_id": 4,
  "to_warehouse_id": 5,
  "quantity": 2,
  "supplier_id": 3,
  "date": "2025-11-17T20:00:00Z",
  "minimum_quantity": 6
}
```

Logic (inside `InventoryService`):

- Runs inside a single DB transaction.
- Performs an `OUT` transaction for the source warehouse.
- Performs an `IN` transaction for the target warehouse.
- If the source does not have enough stock, the whole transfer fails and rolls back.
- Works across warehouses in different countries (countries are enforced at DB/model level).

### 4.7 Global Inventory View

- `GET /api/inventory/global-view`

Returns **total stock per product across all warehouses**, with optional filters:

Query parameters:

- `country_id` or `country_name` – filter by country
- `warehouse_id` or `warehouse_name` – filter by warehouse
- `search` – search by product name or SKU
- Pagination: `per_page` (from `BaseListRequest`)

Example response item:

```json
{
  "product_id": 10,
  "product_name": "tiger",
  "product_sku": "sku_0012",
  "total_quantity": 37
}
```

### 4.8 Low Stock Report (API)

- `GET /api/reports/low_stock`  
  (matches the requirement’s `GET /api/reports/low-stock`)

Returns all inventory rows where `quantity <= minimum_quantity`, with:

- Product name & SKU
- Current quantity
- Minimum required quantity
- Warehouse name & location
- Country name & code
- Supplier name & contact info (based on latest IN transaction with supplier)

---

## 5. Low-Stock Scheduler & Console Command

A dedicated console command is provided:

```bash
php artisan inventory:check-low-stock
```

This command:

1. Queries all `Inventory` records where `quantity <= minimum_quantity`.
2. Maps them into a report structure in `InventoryService::getLowStockProduct()`.
3. Sends an HTML email to the address from `.env`:

   ```env
   LOW_STOCK_REPORT_EMAIL=you@example.com
   ```

4. (Bonus) Attempts to send a Slack notification if `LOW_STOCK_SLACK_WEBHOOK` is configured.

The command is scheduled daily at **00:00** in `bootstrap/app.php`:

```php
use Illuminate\Console\Scheduling\Schedule;

->withSchedule(function (Schedule $schedule) {
    $schedule->command('inventory:check-low-stock')->dailyAt('00:00');
})
```

To enable scheduler in production, add a cron entry:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Bonus Tasks Implemented

The following bonus tasks from the assignment are implemented:

1. **Caching for frequently accessed products**
    - Implemented in `ProductRepository::find()`
    - Uses `Cache::remember("product:{id}", ttl, ...)`
    - Cache is cleared on update and delete.

2. **Event Listeners / Notifications for low stock**
    - When inventory quantity becomes `<= minimum_quantity`, a `LowStockReached` event is dispatched from `InventoryService`.
    - This allows attaching listeners for additional notifications (e.g. real-time alerts).

3. **Slack Notification for daily low stock report**
    - The `inventory:check-low-stock` command can send a Slack message summarizing low-stock products using `LowStockSlackNotification`.
    - Uses a configurable webhook:
      ```env
      LOW_STOCK_SLACK_WEBHOOK=https://hooks.slack.com/services/XXX/YYY/ZZZ
      ```
    To create a Slack webhook, go to Slack → Apps → search for Incoming Webhooks → Add to Slack, choose a channel, then copy the generated Webhook URL and put it in your .env as SLACK_WEBHOOK_URL.  
      - On some local environments, you may see `cURL error 60` if PHP/cURL SSL CA certificates are not configured.  
      In that case, the command logs the Slack error but still completes successfully and sends the email.  
            This does **not** affect the core business logic, and in this project me make SSL verify=false just for test and showing that logic work

---

## 7. API Documentation (Scramble)

This project uses **dedoc/scramble** for automatic OpenAPI generation.

- UI: `GET /docs/api`
- Generates OpenAPI 3.0 spec from:
    - routes (`routes/api.php`)
    - form requests (validation rules)
    - controller signatures & attributes

You can explore and test all endpoints from the browser, similar to Swagger UI.

---

## 8. Postman Collection

A Postman collection is included at:

```text
docs/Inventory System Management.postman_collection.json
```

Import it into Postman to quickly test all endpoints:

- Authentication (login)
- CRUD for countries, warehouses, products, suppliers
- Inventory transactions (IN/OUT)
- Inventory transfer
- Global inventory view
- Low stock report endpoint

---

## 9. Testing

Feature tests cover the main business flows, including:

- Countries CRUD
- Warehouse CRUD
- Supplier CRUD
- Product CRUD
- Inventory IN / OUT validation:
    - IN increases quantity
    - OUT decreases quantity
    - OUT fails when stock would go negative
- Inventory transfer:
    - Reduces stock in source warehouse
    - Increases stock in destination warehouse
- Low stock report:
    - Inventories at/below minimum appear in `GET /api/reports/low_stock`

Run the test suite with:

```bash
php artisan test
```



