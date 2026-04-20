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

    // Preferred preset names
    private const FORMAT_BS_LABEL_FULL = 'bs_label_full';
    private const FORMAT_BS_LABEL_FULL_NEPDAY = 'bs_label_full_nepday';
    private const FORMAT_BS_LABEL_COMPACT = 'bs_label_compact';
    private const FORMAT_BS_LABEL_COMPACT_TIME = 'bs_label_compact_time';
    private const FORMAT_BS_DATETIME_NUMERIC = 'bs_datetime_numeric';
    private const FORMAT_BS_LABEL_DEVANAGARI = 'bs_label_devanagari';

    // Legacy aliases (kept for backward compatibility)
    private const ALIAS_BS_LABEL = 'bs_label';
    private const ALIAS_BS_LABEL_NEPDAY = 'bs_label_nepday';
    private const ALIAS_BS_LABEL_SIMP = 'bs_label_simp';
    private const ALIAS_BS_LABEL_TIME = 'bs_label_time';
    private const ALIAS_BS_TIME = 'bs_time';
    private const ALIAS_NEPALILANG = 'nepalilang';

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

    /** @var array<int, string> */
    private const BS_MONTH_NAMES_DEVANAGARI = [
        1 => "\u{092C}\u{0948}\u{0936}\u{093E}\u{0916}",
        2 => "\u{091C}\u{0947}\u{0920}",
        3 => "\u{0905}\u{0938}\u{093E}\u{0930}",
        4 => "\u{0936}\u{094D}\u{0930}\u{093E}\u{0935}\u{0923}",
        5 => "\u{092D}\u{0926}\u{094C}",
        6 => "\u{0906}\u{0936}\u{094D}\u{0935}\u{093F}\u{0928}",
        7 => "\u{0915}\u{093E}\u{0930}\u{094D}\u{0924}\u{093F}\u{0915}",
        8 => "\u{092E}\u{0902}\u{0938}\u{093F}\u{0930}",
        9 => "\u{092A}\u{094C}\u{0937}",
        10 => "\u{092E}\u{093E}\u{0918}",
        11 => "\u{092B}\u{093E}\u{0932}\u{094D}\u{0917}\u{0941}\u{0923}",
        12 => "\u{091A}\u{0948}\u{0924}",
    ];

    /** @var array<string, string> */
    private const BS_WEEKDAY_NEPALI = [
        'sunday' => 'aitabar',
        'monday' => 'sombar',
        'tuesday' => 'manglabar',
        'wednesday' => 'budubar',
        'thursday' => 'bibar',
        'friday' => 'sukrabar',
        'saturday' => 'sanibar',
    ];

    /** @var array<string, string> */
    private const BS_WEEKDAY_DEVANAGARI = [
        'sunday' => "\u{0906}\u{0907}\u{0924}\u{092C}\u{093E}\u{0930}",
        'monday' => "\u{0938}\u{094B}\u{092E}\u{092C}\u{093E}\u{0930}",
        'tuesday' => "\u{092E}\u{0902}\u{0917}\u{0932}\u{092C}\u{093E}\u{0930}",
        'wednesday' => "\u{092C}\u{0941}\u{0927}\u{092C}\u{093E}\u{0930}",
        'thursday' => "\u{092C}\u{093F}\u{0939}\u{093F}\u{092C}\u{093E}\u{0930}",
        'friday' => "\u{0936}\u{0941}\u{0915}\u{094D}\u{0930}\u{092C}\u{093E}\u{0930}",
        'saturday' => "\u{0936}\u{0928}\u{093F}\u{092C}\u{093E}\u{0930}",
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

        if ($this->isPreset($preset, [self::FORMAT_BS_LABEL_FULL, self::ALIAS_BS_LABEL])) {
            return strtolower($sourceDateTime->format('l')).' '.$day.', '.$monthName.' '.$year;
        }

        if ($this->isPreset($preset, [self::FORMAT_BS_LABEL_FULL_NEPDAY, self::ALIAS_BS_LABEL_NEPDAY])) {
            $englishWeekday = strtolower($sourceDateTime->format('l'));

            return $this->bsWeekdayNepali($englishWeekday).' '.$day.', '.$monthName.' '.$year;
        }

        if ($this->isPreset($preset, [self::FORMAT_BS_LABEL_DEVANAGARI, self::ALIAS_NEPALILANG])) {
            $englishWeekday = strtolower($sourceDateTime->format('l'));
            $nepWeekday = $this->bsWeekdayDevanagari($englishWeekday);
            $nepDay = $this->toDevanagariDigits((string) $day);
            $nepMonth = $this->bsMonthNameDevanagari($month);
            $nepYear = $this->toDevanagariDigits((string) $year);

            return $nepWeekday.' '.$nepDay.', '.$nepMonth.' '.$nepYear;
        }

        if ($this->isPreset($preset, [self::FORMAT_BS_LABEL_COMPACT, self::ALIAS_BS_LABEL_SIMP])) {
            return $day.', '.$monthName.' '.$year;
        }

        if ($this->isPreset($preset, [self::FORMAT_BS_LABEL_COMPACT_TIME, self::ALIAS_BS_LABEL_TIME])) {
            return $day.', '.$monthName.' '.$year.' '.$time12hr;
        }

        if ($this->isPreset($preset, [self::FORMAT_BS_DATETIME_NUMERIC, self::ALIAS_BS_TIME])) {
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

    private function bsMonthNameDevanagari(int $month): string
    {
        return self::BS_MONTH_NAMES_DEVANAGARI[$month] ?? '';
    }

    private function bsWeekdayNepali(string $englishWeekday): string
    {
        return self::BS_WEEKDAY_NEPALI[$englishWeekday] ?? $englishWeekday;
    }

    private function bsWeekdayDevanagari(string $englishWeekday): string
    {
        return self::BS_WEEKDAY_DEVANAGARI[$englishWeekday] ?? $englishWeekday;
    }

    private function toDevanagariDigits(string $value): string
    {
        return strtr($value, [
            '0' => "\u{0966}",
            '1' => "\u{0967}",
            '2' => "\u{0968}",
            '3' => "\u{0969}",
            '4' => "\u{096A}",
            '5' => "\u{096B}",
            '6' => "\u{096C}",
            '7' => "\u{096D}",
            '8' => "\u{096E}",
            '9' => "\u{096F}",
        ]);
    }

    /**
     * @param array<int, string> $keys
     */
    private function isPreset(string $preset, array $keys): bool
    {
        return in_array($preset, $keys, true);
    }
}
