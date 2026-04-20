<?php

declare(strict_types=1);

namespace RakeshRai\LaravelNepaliDate;

use DateTimeInterface;

final class LaravelNepaliDate
{
    public function __construct(private readonly NepaliDateConverter $converter)
    {
    }

    /**
     * @param DateTimeInterface|string $englishDate
     * @return array{year:int,month:int,day:int,formatted:string,month_name:string,week_day:string,time:string}
     */
    public function from(DateTimeInterface|string $englishDate, ?string $format = null): array
    {
        return $this->converter->convert($englishDate, $format);
    }

    public function toString(DateTimeInterface|string $englishDate, ?string $format = null): string
    {
        return $this->converter->toString($englishDate, $format);
    }
}
