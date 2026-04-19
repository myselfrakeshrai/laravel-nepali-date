<?php

declare(strict_types=1);

namespace RakeshRai\LaravelNepaliDate;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use RakeshRai\LaravelNepaliDate\Exceptions\UnsupportedDateRangeException;
use RakeshRai\LaravelNepaliDate\Support\CalendarData;

final class NepaliDateConverter
{
    /**
     * Reference mapping: 1943-04-14 AD == 2000-01-01 BS.
     */
    private const BASE_AD = '1943-04-14';

    private const BASE_BS_YEAR = 2000;
    private const BASE_BS_MONTH = 1;
    private const BASE_BS_DAY = 1;

    /** @var array<int, array<int, int>> */
    private array $bsCalendar;

    private string $defaultFormat;

    public function __construct(array $config = [])
    {
        $this->bsCalendar = CalendarData::monthLengthsByYear();
        $this->defaultFormat = (string) ($config['default_format'] ?? 'Y-m-d');
    }

    /**
     * @param DateTimeInterface|string $englishDate
     * @return array{year:int,month:int,day:int,formatted:string}
     */
    public function convert(DateTimeInterface|string $englishDate, ?string $format = null): array
    {
        $date = $this->normalizeDate($englishDate);

        $daysDiff = $this->daysFromBase($date);

        if ($daysDiff < 0) {
            throw new UnsupportedDateRangeException('Date is below supported range. Minimum AD date is 1943-04-14.');
        }

        $year = self::BASE_BS_YEAR;
        $month = self::BASE_BS_MONTH;
        $day = self::BASE_BS_DAY;

        while (true) {
            $months = $this->bsCalendar[$year] ?? null;

            if ($months === null) {
                throw new UnsupportedDateRangeException('Date is above supported BS calendar range.');
            }

            $daysInCurrentMonth = $months[$month - 1];

            if ($daysDiff === 0) {
                break;
            }

            $day++;
            $daysDiff--;

            if ($day > $daysInCurrentMonth) {
                $day = 1;
                $month++;

                if ($month > 12) {
                    $month = 1;
                    $year++;
                }
            }
        }

        $outFormat = $format ?? $this->defaultFormat;

        return [
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'formatted' => $this->formatBsDate($year, $month, $day, $outFormat),
        ];
    }

    public function toString(DateTimeInterface|string $englishDate, ?string $format = null): string
    {
        return $this->convert($englishDate, $format)['formatted'];
    }

    public function isSupported(DateTimeInterface|string $englishDate): bool
    {
        try {
            $this->convert($englishDate);

            return true;
        } catch (UnsupportedDateRangeException) {
            return false;
        }
    }

    /**
     * @param DateTimeInterface|string $englishDate
     */
    private function normalizeDate(DateTimeInterface|string $englishDate): DateTimeImmutable
    {
        if ($englishDate instanceof DateTimeInterface) {
            return new DateTimeImmutable($englishDate->format('Y-m-d'), new DateTimeZone('UTC'));
        }

        return new DateTimeImmutable((new DateTimeImmutable($englishDate))->format('Y-m-d'), new DateTimeZone('UTC'));
    }

    private function daysFromBase(DateTimeImmutable $date): int
    {
        $base = new DateTimeImmutable(self::BASE_AD, new DateTimeZone('UTC'));

        $seconds = $date->getTimestamp() - $base->getTimestamp();

        return (int) floor($seconds / 86400);
    }

    private function formatBsDate(int $year, int $month, int $day, string $format): string
    {
        $replace = [
            'Y' => sprintf('%04d', $year),
            'y' => substr((string) $year, -2),
            'm' => sprintf('%02d', $month),
            'n' => (string) $month,
            'd' => sprintf('%02d', $day),
            'j' => (string) $day,
        ];

        return strtr($format, $replace);
    }
}
