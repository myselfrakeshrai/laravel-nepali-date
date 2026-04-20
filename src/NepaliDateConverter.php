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
    private const FORMAT_BS_LABEL = 'bs_label';
    private const FORMAT_BS_LABEL_SIMP = 'bs_label_simp';
    private const FORMAT_BS_LABEL_TIME = 'bs_label_time';
    private const FORMAT_BS_TIME = 'bs_time';

    /** @var array<int, string> */
    private const BS_MONTH_NAMES = [
        1 => 'Baishak',
        2 => 'Jestha',
        3 => 'Ashadh',
        4 => 'Shrawan',
        5 => 'Bhadra',
        6 => 'Ashwin',
        7 => 'Kartik',
        8 => 'Mangsir',
        9 => 'Poush',
        10 => 'Magh',
        11 => 'Falgun',
        12 => 'Chaitra',
    ];

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
     * @return array{year:int,month:int,day:int,formatted:string,month_name:string,week_day:string,time:string}
     */
    public function convert(DateTimeInterface|string $englishDate, ?string $format = null): array
    {
        $sourceDateTime = $this->normalizeDateTime($englishDate);
        $date = $this->normalizeDate($sourceDateTime);

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
            'month_name' => $this->bsMonthName($month),
            'week_day' => strtolower($sourceDateTime->format('l')),
            'time' => $sourceDateTime->format('h:i A'),
            'formatted' => $this->formatBsDate($year, $month, $day, $sourceDateTime, $outFormat),
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
    private function normalizeDateTime(DateTimeInterface|string $englishDate): DateTimeImmutable
    {
        if ($englishDate instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($englishDate);
        }

        return new DateTimeImmutable($englishDate);
    }

    private function normalizeDate(DateTimeImmutable $englishDate): DateTimeImmutable
    {
        return new DateTimeImmutable($englishDate->format('Y-m-d'), new DateTimeZone('UTC'));
    }

    private function daysFromBase(DateTimeImmutable $date): int
    {
        $base = new DateTimeImmutable(self::BASE_AD, new DateTimeZone('UTC'));

        $seconds = $date->getTimestamp() - $base->getTimestamp();

        return (int) floor($seconds / 86400);
    }

    private function formatBsDate(
        int $year,
        int $month,
        int $day,
        DateTimeImmutable $sourceDateTime,
        string $format
    ): string {
        $preset = strtolower(trim($format));
        $monthName = $this->bsMonthName($month);
        $time12hr = $sourceDateTime->format('h:i A');

        if ($preset === self::FORMAT_BS_LABEL) {
            return strtolower($sourceDateTime->format('l')).' '.$day.', '.$monthName.' '.$year;
        }

        if ($preset === self::FORMAT_BS_LABEL_SIMP) {
            return $day.', '.$monthName.' '.$year;
        }

        if ($preset === self::FORMAT_BS_LABEL_TIME) {
            return $day.', '.$monthName.' '.$year.' '.$time12hr;
        }

        if ($preset === self::FORMAT_BS_TIME) {
            return sprintf('%02d-%02d-%04d %s', $day, $month, $year, $time12hr);
        }

        $replace = [
            'Y' => sprintf('%04d', $year),
            'y' => substr((string) $year, -2),
            'm' => sprintf('%02d', $month),
            'n' => (string) $month,
            'd' => sprintf('%02d', $day),
            'j' => (string) $day,
            'F' => $monthName,
            'H' => $sourceDateTime->format('H'),
            'h' => $sourceDateTime->format('h'),
            'g' => $sourceDateTime->format('g'),
            'i' => $sourceDateTime->format('i'),
            's' => $sourceDateTime->format('s'),
            'A' => $sourceDateTime->format('A'),
        ];

        return strtr($format, $replace);
    }

    private function bsMonthName(int $month): string
    {
        return self::BS_MONTH_NAMES[$month] ?? '';
    }
}
