# Laravel Neplai Date

Author: Rakesh Rai

Convert English (AD) dates to Nepali (BS) in Laravel.

## Features

- AD -> BS converter class
- `bs_date()` helper
- `LaravelNepaliDate` facade
- `@bs($date)` Blade directive
- Optional middleware to auto-convert AD date text in HTML responses

## Installation

```bash
composer require rakeshrai/laravel-nepali-date-converter
```

## Development setup

```bash
composer install
composer test
```

## Continuous integration

GitHub Actions runs tests automatically on every push and pull request:

- `.github/workflows/tests.yml`

## Publish config

```bash
php artisan vendor:publish --tag=nepali-date-config
```

## Usage

### Helper

```php
bs_date('2026-04-20'); // 2083-01-07 (example)
bs_date(now(), 'Y/m/d');
```

### Facade

```php
use LaravelNepaliDate;

LaravelNepaliDate::toString('2026-04-20');
LaravelNepaliDate::from('2026-04-20');
// ['year' => 2083, 'month' => 1, 'day' => 7, 'formatted' => '2083-01-07']
```

### Blade

```blade
@bs($user->created_at)
```

### Auto-convert AD date strings in HTML output

In `config/nepali-date.php`:

```php
'auto_convert_response_dates' => true,
```

This scans rendered HTML and converts patterns like:

- `2026-04-20`
- `2026/04/20`
- `2026.04.20`

If you do not want global auto registration in `web` group:

```php
'auto_register_web_middleware' => false,
```

Then register manually in your app:

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    // ...
    'auto.bs.date' => \RakeshRai\LaravelNepaliDate\Http\Middleware\AutoConvertAdDatesToBs::class,
];
```

## Supported range

- Minimum AD: `1943-04-14`
- Maximum depends on packaged BS data table (currently through BS 2099 data)

## Notes

- Auto conversion is text pattern based. For precise control, prefer `bs_date()` or `@bs(...)` in Blade.
- For APIs/JSON, use explicit conversion in transformers/resources.

## Demo file

A quick sample integration is available at:

- `examples/laravel-usage.php`
