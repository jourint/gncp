# Laravel Shoe Manufacturing ERP - AI Coding Instructions

- Always respond in Russian.

## Project Overview

This is a Laravel 12 ERP system for shoe manufacturing with order management, material inventory tracking, and cost calculations. The system uses **Filament v4** as the primary admin interface and **Livewire/Volt** for real-time components.

**Stack**: Laravel 12, Filament 4, Livewire/Volt, TailwindCSS 4, Vite, PostgreSQL

---

## Architecture Patterns

### Filament Resource Organization

Resources follow a **Schema-based form/table builder pattern**:

- **Form classes**: `app/Filament/Resources/[Resource]/Schemas/[ResourceName]Form.php` - Defines form fields with validation
- **Table classes**: `app/Filament/Resources/[Resource]/Tables/[ResourceName]Table.php` - Defines table columns and filters
- **RelationManagers**: Nested CRUD for relationships (e.g., `Materials/RelationManagers/MovementsRelationManager.php`)

Example: [OrderResource](app/Filament/Resources/Orders/OrderResource.php) → Schema-based form/table pattern with nested RelationManagers for positions and employees.

### Polymorphic Material Movements

Use polymorphic relationships for flexible material tracking:

```php
// Material movements use morphs() - tracks inventory changes across different entities
// Type enum: 'income', 'outcome', 'write-off' with isNegative() business logic
```

See [MaterialMovement model](app/Models/MaterialMovement.php) and migration for polymorphic pattern usage.

### Price Coefficient Strategy

Shoe models use **price coefficients** (e.g., `price_coeff_cutting: 1.00`) to calculate dynamic pricing:

- Base price stored in `shoe_types` (cutting, sewing, shoemaker rates)
- Models multiply by their coefficients to get final work costs
- Used in order calculations for employee compensation

---

## Key Models & Data Flow

**Core Domain**: Shoes → Orders → Employees

- **ShoeType**: Base shoe category with base prices for each workflow
- **ShoeModel**: Specific model variant with price coefficients and available sizes (stored as JSONB)
- **ShoeTechCard**: Technical specification with materials and soles (composite of ShoeModel + Color)
- **Order** → **OrderPosition** (references ShoeTechCard) → **OrderEmployee** (work assignment with calculated price)

**Material Domain**:

- **Material** → **MaterialType** (with Unit relationship)
- **MaterialMovement** (polymorphic, tracks quantity changes via enum-based types)

---

## Developer Workflows

### Quick Start

```bash
composer run setup          # Install & migrate (creates DB, runs seeders)
composer run dev           # Start dev server: Laravel, Queue, Logs (pail), Vite all at once
npm run build              # Production CSS/JS via Vite + TailwindCSS
```

### Testing & Code Quality

```bash
composer run test          # Clear config, lint with Pint, run PHPUnit
composer run test:lint     # Check Pint formatting only (no --fix)
php artisan test --parallel # Run tests in parallel
```

### Database Operations

```bash
php artisan migrate --force              # Run pending migrations
php artisan tinker                       # Interactive REPL for testing queries
php artisan pail --timeout=0             # Stream logs (watch real-time events)
```

### Lint Configuration

**Pint** (PHP formatter) is configured via [pint.json](pint.json). Run `composer run lint` to auto-fix or `composer run test:lint` to check.

---

## Code Conventions

### Enums for Business Logic

Use enums in `app/Enums/` with methods for domain logic:

- [MovementType](app/Enums/MovementType.php): `isNegative()` determines if movement reduces inventory
- [OrderStatus](app/Enums/OrderStatus.php): Implements `HasLabel` for UI dropdowns
- [InsolesType](app/Enums/InsolesType.php): Labeled enum for shoe insole variants

### Model Casts & Timestamps

- Models use explicit `casts()` method; leverage `decimal:2` for prices
- Some models disable timestamps: `public $timestamps = false` (e.g., Counter, Puff)
- Relationships use `HasMany`, `BelongsTo` with correct delete strategy (`cascadeOnDelete`, `restrictOnDelete`)

### Observers for Side Effects

[AppServiceProvider](app/Providers/AppServiceProvider.php) registers observers:

- [MaterialMovementObserver](app/Observers/MaterialMovementObserver.php): Automatically set `user_id` on creation

---

## Common Implementation Patterns

### Adding a New Filament Resource

1. Create Resource class extending `Filament\Resources\Resource`
2. Create `Schemas/{ResourceName}Form.php` and `Tables/{ResourceName}Table.php`
3. Configure `form()` and `table()` methods to call schema classes
4. Add relation managers if needed
5. Register in navigation with `protected static ?int $navigationSort`

### Adding Material Movements

Use polymorphic relation in your model:

```php
public function movements(): MorphMany
{
    return $this->morphMany(MaterialMovement::class, 'movable');
}
```

Then in forms: `Select::make('type')->options(MovementType::cases())`

### Working with JSONB Fields

ShoeModel uses `available_sizes` and `workflows` as JSONB:

- Migration: `$table->jsonb('available_sizes')`; model should cast the field: `protected $casts = ['available_sizes' => 'array'];`
- Access in code: `$model->available_sizes` (returns PHP array)
- Queries:
    - Eloquent: `ShoeModel::whereJsonContains('available_sizes', 40)->get();`
    - Raw Postgres operator: `ShoeModel::whereRaw("available_sizes @> ?", ['[40]'])->get();`

---

## File Organization Reminders

- **Forms/Tables**: Keep `Schemas/` folder per resource for schema builder classes
- **Services**: Place business logic in `app/Services/` (e.g., pricing calculations)
- **Migrations**: Timestamp format: `YYYY_MM_DD_HHMMSS_operation.php`
- **Tests**: Unit tests in `tests/Unit/`, feature tests in `tests/Feature/`

---

## Integration Points & Dependencies

- **Filament**: Admin panel auto-discovery in `app/Filament/` namespace
- **Livewire/Volt**: Real-time components (configured via VoltServiceProvider)
- **Vite**: CSS/JS entry points: `resources/css/app.css`, `resources/js/app.js`
- **TailwindCSS**: Configured as Vite plugin; scans `app/Filament/**` for class discovery
- **dompdf**: For PDF exports (used in Reports classes in `__demo/`)
- **Laravel Trend**: Time-series charting for analytics

---

## Development Tips

1. **Filament Debugging**: Run `php artisan filament:assets --force` if UI breaks
2. **Vite Hot Reload**: Vite dev server ignores `storage/framework/views/` (cached Blade templates)
3. **Polymorphic Queries**: Always eager-load with types: `MaterialMovement::with('movable')->get()`
4. **Seed on Demand**: Run seeders from tinker: `$this->call(ShoeTypeSeeder::class)`
5. **Queue Debugging**: `php artisan queue:listen --tries=1` logs job output inline (dev mode)

---

## Database

- Migrations: located in `database/migrations/`. Filenames are timestamped (e.g. `2026_01_17_221415_create_shoe_models_table.php`). Follow existing patterns when adding migrations: use `foreignId(...)->constrained()->cascadeOnDelete()` or `->restrictOnDelete()` consistent with domain intent.

- Important tables:
    - `material_movements` — polymorphic (`morphs('movable')`), `type` enum, `user_id` and `quantity`.
    - `shoe_models` — contains JSONB columns `available_sizes` and `workflows` and `price_coeff_*` fields.
    - `shoe_tech_cards`, `order_positions`, `order_employees`, `materials`, `material_types`.

- JSONB usage:
    - Models cast JSONB fields to arrays; access via `$model->available_sizes`.
    - Query arrays with Eloquent: `ShoeModel::whereJsonContains('available_sizes', 40)->get();`.
    - For advanced Postgres jsonb operations, use `DB::raw()` or query builder with appropriate operators.

- Polymorphic movements:
    - The `MaterialMovement` model uses `morphTo()` and casts `type` to `App\Enums\MovementType` which provides `isNegative()` logic.
    - Observers (registered in `app/Providers/AppServiceProvider.php`) set `user_id` automatically on create.

- Seeders:
    - See `database/seeders/DatabaseSeeder.php` (calls `ColorSeeder`, `ShoeTypeSeeder`, `MaterialTypeSeeder`). Use `composer run setup` to run migrations+seeders as defined in `composer.json`.

- DB connections & config:
    - `config/database.php` contains multiple connection templates (default port shows `1433` for MSSQL). The active connection is controlled via `.env`.
    - Verify `.env` before running destructive commands.

- Useful commands:

```bash
php artisan migrate --force
php artisan migrate:fresh --seed   # local/dev only
php artisan db:seed --class=SeederClass
php artisan tinker
```

- Tests & CI:
    - Check `phpunit.xml` for test database configuration; consider an in-memory sqlite connection for fast unit tests.
    - In CI, use `php artisan migrate --force` and seed only what tests require.

- Performance & safety:
    - Migrations frequently add `->index()` to lookup and FK fields; keep these indices when refactoring.
    - Many lookups use `restrictOnDelete` — avoid deleting reference rows (e.g. `shoe_types`, `material_types`) without migration-safe replacements.
    - Eager-load relations (`with('movable')`, etc.) to prevent N+1 queries in reporting/UI.

---

## Code Conventions (highlights)

- Enums in `app/Enums/` include domain methods (e.g. `MovementType::isNegative()`).
- Models define a `casts()` method; prefer `decimal:2` for money fields.
- Some small lookup models disable timestamps (`public $timestamps = false`).
- Filament resources use schema classes in `app/Filament/Resources/*/Schemas` and `Tables` subfolders.

---
