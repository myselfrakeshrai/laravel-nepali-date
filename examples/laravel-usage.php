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

// Helper usage
$bsFromHelper = bs_date(now(), 'Y/m/d');

// Blade usage:
// @bs($post->published_at)
