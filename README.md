# GRADIN — Courier Master Data API

A small Laravel 12 application that provides a JSON REST API to manage the
**Courier (Kurir)** master data. The scope is a single module: CRUD for
couriers, with listing that supports pagination, sorting, tokenised search and
level filtering. No front-end UI is included, as required by the brief.

---

## Tech Stack

- PHP 8.2+
- Laravel 12
- SQLite (default, zero-configuration) — easily switchable to MySQL
- PHPUnit feature tests
- Laravel Pint (code style)

---

## Getting Started

```bash
# 1. Install dependencies
composer install

# 2. Prepare the environment file
cp .env.example .env
php artisan key:generate

# 3. Create the SQLite database file (if it does not exist)
touch database/database.sqlite

# 4. Run migrations + seed demo data
php artisan migrate --seed

# 5. Serve the application
php artisan serve
```

The API is then available under `http://127.0.0.1:8000/api`.

### Running the tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`), so
no extra setup is required.

### Code style

```bash
./vendor/bin/pint
```

---

## Data Model — `couriers`

A courier is a delivery rider/driver. The table holds the fields that are
typically needed for a courier master record:

| Column                   | Type              | Notes                                              |
|--------------------------|-------------------|----------------------------------------------------|
| `id`                     | bigint (PK)       |                                                    |
| `name`                   | string            | Courier full name. Indexed.                        |
| `email`                  | string (unique)   |                                                    |
| `phone`                  | string, nullable  |                                                    |
| `level`                  | tinyint           | Courier level, **1–5**. Defaults to `1`. Indexed.   |
| `vehicle_type`           | string, nullable  | One of `motorcycle`, `car`, `van`, `truck`.         |
| `vehicle_plate_number`   | string, nullable  | Unique.                                            |
| `city`                   | string, nullable  |                                                    |
| `address`                | text, nullable    |                                                    |
| `status`                 | string            | `active`, `inactive`, `suspended`. Default `active`.|
| `registered_at`          | date, nullable    | Date the courier was registered ("tanggal didaftarkan"). Indexed. |
| `notes`                  | text, nullable    |                                                    |
| `created_at`/`updated_at`| timestamps        |                                                    |

Enumerations are represented by PHP backed enums (`App\Enums\VehicleType`,
`App\Enums\CourierStatus`) and cast automatically on the model.

---

## API Endpoints

Base path: `/api`

| Method   | URI                | Description                     |
|----------|--------------------|---------------------------------|
| `GET`    | `/couriers`        | List couriers (see filters)     |
| `POST`   | `/couriers`        | Create a courier                |
| `GET`    | `/couriers/{id}`   | Show a single courier           |
| `PUT`    | `/couriers/{id}`   | Update a courier                |
| `DELETE` | `/couriers/{id}`   | Delete a courier                |

All responses are JSON. Validation errors return HTTP `422` with the standard
Laravel error payload.

### `GET /couriers` — query parameters

| Parameter   | Example            | Behaviour                                                                 |
|-------------|--------------------|---------------------------------------------------------------------------|
| `search`    | `budi+agung`       | Tokenised, case-insensitive search. **Every** token must match one of the searchable fields (name, email, phone, plate). `budi agung` matches `Budiono Hadi Agung`. |
| `level`     | `2,3`              | Comma-separated list of levels. Only returns couriers whose level is in the list. Out-of-range values are ignored. |
| `sort`      | `registered_at`    | Column to sort by. Allowed: `name`, `registered_at`, `created_at`, `updated_at`, `level`. **Defaults to `name`.** Unknown values safely fall back to `name`. |
| `direction` | `asc` \| `desc`    | Sort direction. Defaults to `asc`.                                        |
| `per_page`  | `15`               | Items per page. Defaults to `15`, clamped to `100`.                        |

**Examples**

```bash
# Default listing — sorted by name, paginated
GET /api/couriers

# Sort by registration date (override default sort)
GET /api/couriers?sort=registered_at&direction=desc

# Search by name tokens
GET /api/couriers?search=budi+agung

# Only level 2 or 3
GET /api/couriers?level=2,3

# Combine filters + custom page size
GET /api/couriers?search=budi&level=2,3&sort=registered_at&per_page=25
```

### `POST /couriers` — example body

```json
{
    "name": "Budiono Hadi Agung",
    "email": "budiono.agung@example.com",
    "phone": "081234567890",
    "level": 3,
    "vehicle_type": "motorcycle",
    "vehicle_plate_number": "B 1234 XYZ",
    "city": "Bandung",
    "address": "Jl. Merdeka No. 1",
    "status": "active",
    "registered_at": "2024-01-15",
    "notes": "Experienced courier."
}
```

**Validation rules**

- `name` — required, string, max 255
- `email` — required, valid email, unique (on update: unique ignoring self)
- `phone` — optional, digits/spaces and `+ - ( )`
- `level` — required, integer between **1 and 5**
- `vehicle_type` — required, must be a valid `VehicleType`
- `vehicle_plate_number` — optional, unique (ignoring self on update)
- `status` — optional, must be a valid `CourierStatus`
- `registered_at` — required, valid date
- `city`, `address`, `notes` — optional strings with length limits

---

## Project Structure

```
app/
  Enums/                     VehicleType, CourierStatus backed enums
  Http/
    Controllers/Api/         CourierController (index/store/show/update/destroy)
    Requests/Api/            StoreCourierRequest, UpdateCourierRequest (validation)
    Resources/               CourierResource (JSON shaping)
  Models/                    Courier model (+ scopeOfLevels)
database/
  factories/                 CourierFactory
  migrations/                create_couriers_table
  seeders/                   CourierSeeder (incl. "Budiono Hadi Agung" sample)
routes/
  api.php                    apiResource route registration
tests/
  Feature/CourierApiTest.php Full HTTP feature tests
```

---

## Design Notes

- **Sorting is whitelisted.** The `sort` query parameter is mapped against a
  fixed allow-list in the controller, so arbitrary/invalid column names can never
  reach the database (protects against SQL injection through ordering).
- **Search is token based.** The search string is split on whitespace; each token
  must be found in at least one searchable column (AND across tokens, OR across
  columns). This is what allows `budi agung` to match `Budiono Hadi Agung`.
- **Validation lives in Form Requests**, keeping the controller thin and the
  rules reusable/testable.
- **Responses are shaped by an API Resource** so the JSON contract is stable and
  decoupled from the database schema.
- **Stable ordering.** A secondary `orderBy('id')` guarantees deterministic
  pagination even when the primary sort column has ties.

