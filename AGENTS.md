# AGENTS.md

## Project Overview

Laravel 10 accounting app ("Pembukuan Agrilaras") for a poultry/egg business. PHP 8.1+, MySQL, Vite + Tailwind CSS + Alpine.js. All UI text is in Indonesian.

## Commands

```bash
php artisan serve          # dev server
php artisan migrate        # run migrations
php artisan test           # run all tests (PHPUnit 10)
php artisan test --filter=TestName   # single test
npm run dev                # Vite dev server (HMR)
npm run build              # production asset build
./vendor/bin/pint          # Laravel Pint (code formatter, no config file found — uses defaults)
```

No linter or typecheck command is configured. `composer lint` / `composer test` scripts are not defined.

## Architecture

### Routes

Routes are split into **two developer-named files** loaded from `routes/web.php`:
- `routes/aldi.php` — legacy modules (stok, penjualan lama, PO, opname, profit, piutang, etc.)
- `routes/nanda.php` — newer modules (pembukuan-baru, jurnal-perkiraan, faktur-pembelian, transaksi/*, laporan/*)
- `routes/auth.php` — Breeze auth routes

After editing routes, run `php artisan route:clear` then `php artisan route:list` to verify.

### Controller/Model Naming

Naming is **not consistent**. Expect:
- `PascalCaseController.php` (e.g., `FakturPembelianController`)
- `snake_case` names (e.g., `Stok_telur_alpaController`, `Penjualan_martadah_alpaController`)
- `NeracaController copy.php` — a stale duplicate file exists

Models: `app/Models/` has 35+ models. Some use PascalCase (`AkunPerkiraan`), others use snake_case (`Buku_besar`, `CashIbuModel`).

### Key Directories

- `app/Services/` — business logic for imports, financial reports (jurnal-perkiraan, neraca, laba-rugi, arus-kas)
- `app/Exports/` — 21 Maatwebsite Excel export classes
- `app/Imports/` — 1 import class (`JurnalImport`)
- `app/helpers.php` — global helpers: `tanggal()`, `kode()`, `buatNota()`, `sumBk()`, plus `Nonaktif` and `SettingHal` classes
- `database/sql/` — manual SQL fix scripts (not migrations)
- `resources/views/` — Blade templates organized by module name

### Custom Permission System

Role/permission checks are in `app/helpers.php` via `SettingHal::akses()` and `SettingHal::btnHal()`. Uses tables: `permission_role`, `permission_button`, `permission_perpage`. Permissions are tied to `posisi_id` on the user, **not** Laravel's built-in `can` middleware.

### Exports

All exports use `maatwebsite/excel` + `phpoffice/phpspreadsheet`. Export classes are in `app/Exports/`. Templates for import are generated server-side (see `MasterDataSpreadsheetService`).

## Gotchas

- **MySQL strict mode is off** (`'strict' => false` in `config/database.php`). Queries relying on implicit defaults will behave differently if strict is turned on.
- **`.env` is gitignored** but `outputs/` directory is tracked — check before committing any generated output files.
- **Global helpers in `app/helpers.php`** are autoloaded via `composer.json` `files` array. The `Nonaktif` and `SettingHal` classes are defined here (not in `app/Classes`).
- **No form request validation classes** — most controllers validate inline. `app/Http/Requests/` directory exists but is empty or minimal.
- **Route names use dots** (e.g., `transaksi.faktur-pembelian.index`). When adding routes, follow the existing prefix pattern for the module.
- **Tests are sparse** — only ~16 test files. PHPUnit config in `phpunit.xml` uses array cache/session/queue for testing. DB tests use MySQL (sqlite lines are commented out).
- **Stale files exist**: `NeracaController copy.php`, `NeracaAldi.php` model — be aware of duplicates.
- **Vite entry points**: `resources/css/app.css` and `resources/js/app.js`. Most Blade views load assets from `public/theme/` (non-Vite, traditional `<script>`/`<link>` tags).
