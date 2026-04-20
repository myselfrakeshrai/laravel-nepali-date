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
bs_date('2026-04-20'); // 2083-01-07
bs_date(now(), 'Y/m/d');

// preferred preset labels
bs_date('2026-04-20 15:45:00', 'bs_label_full');          // monday 7, Baishak 2083
bs_date('2026-04-20 15:45:00', 'bs_label_full_nepday');   // sombar 7, Baishak 2083
bs_date('2026-04-20 15:45:00', 'bs_label_devanagari');    // सोमबार ७, बैशाख २०८३
bs_date('2026-04-20 15:45:00', 'bs_label_compact');       // 7, Baishak 2083
bs_date('2026-04-20 15:45:00', 'bs_label_compact_time');  // 7, Baishak 2083 03:45 PM
bs_date('2026-04-20 15:45:00', 'bs_datetime_numeric');    // 07-01-2083 03:45 PM
```

### Supported preset formats (preferred)

- `bs_label_full` => `sunday 12, Baishak 2083`
- `bs_label_full_nepday` => `sombar 12, Baishak 2083`
- `bs_label_devanagari` => `सोमबार १२, बैशाख २०८३`
- `bs_label_compact` => `12, Baishak 2083`
- `bs_label_compact_time` => `12, Baishak 2083 hh:mm AM`
- `bs_datetime_numeric` => `12-01-2083 hh:mm AM`

### Legacy aliases (still supported)

- `bs_label` => alias of `bs_label_full`
- `bs_label_nepday` => alias of `bs_label_full_nepday`
- `nepalilang` => alias of `bs_label_devanagari`
- `bs_label_simp` => alias of `bs_label_compact`
- `bs_label_time` => alias of `bs_label_compact_time`
- `bs_time` => alias of `bs_datetime_numeric`

### Custom format tokens

- BS date: `Y`, `y`, `m`, `n`, `d`, `j`, `F`
- AD time/day passthrough: `l`, `H`, `h`, `i`, `s`, `A`

### Facade

```php
use LaravelNepaliDate;

LaravelNepaliDate::toString('2026-04-20');
LaravelNepaliDate::from('2026-04-20');
// [
//   'year' => 2083,
//   'month' => 1,
//   'day' => 7,
//   'month_name' => 'Baishak',
//   'week_day' => 'monday',
//   'time' => '12:00 AM',
//   'formatted' => '2083-01-07',
// ]
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
