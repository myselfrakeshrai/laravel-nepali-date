<?php

declare(strict_types=1);

use RakeshRai\LaravelNepaliDate\NepaliDateConverter;

if (! function_exists('bs_date')) {
    /**
     * @param \DateTimeInterface|string $englishDate
     */
    function bs_date(\DateTimeInterface|string $englishDate, ?string $format = null): string
    {
        return app(NepaliDateConverter::class)->toString($englishDate, $format);
    }
}
