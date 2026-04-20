<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Example usage in a Laravel app
|--------------------------------------------------------------------------
|
| 1) Install package:
|    composer require rakeshrai/laravel-nepali-date
|
| 2) Publish config:
|    php artisan vendor:publish --tag=nepali-date-config
|
| 3) Use in controller/blade:
*/

use LaravelNepaliDate;

$bsDate = LaravelNepaliDate::toString('2026-04-20');
$bsDateDevanagari = LaravelNepaliDate::toString('2026-04-20 15:45:00', 'bs_label_devanagari');
$bsDateCompact = LaravelNepaliDate::toString('2026-04-20 15:45:00', 'bs_label_compact');

// Helper usage
$bsFromHelper = bs_date(now(), 'Y/m/d');
$bsFromHelperDevanagari = bs_date(now(), 'bs_label_devanagari');

// Legacy aliases still work:
// bs_date(now(), 'nepalilang');

// Blade usage:
// @bs($post->published_at)
