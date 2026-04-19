<?php

declare(strict_types=1);

namespace RakeshRai\LaravelNepaliDate\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array from(\DateTimeInterface|string $englishDate, ?string $format = null)
 * @method static string toString(\DateTimeInterface|string $englishDate, ?string $format = null)
 */
final class LaravelNepaliDate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-nepali-date';
    }
}
