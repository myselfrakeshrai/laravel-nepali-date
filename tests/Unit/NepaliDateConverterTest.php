<?php

declare(strict_types=1);

namespace RakeshRai\LaravelNepaliDate\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use RakeshRai\LaravelNepaliDate\Exceptions\UnsupportedDateRangeException;
use RakeshRai\LaravelNepaliDate\NepaliDateConverter;
use RakeshRai\LaravelNepaliDate\Tests\TestCase;

final class NepaliDateConverterTest extends TestCase
{
    #[Test]
    public function it_converts_known_ad_date_to_bs(): void
    {
        $converter = new NepaliDateConverter();

        $result = $converter->convert('2026-04-20');

        $this->assertSame(2083, $result['year']);
        $this->assertSame(1, $result['month']);
        $this->assertSame(7, $result['day']);
        $this->assertSame('Baishak', $result['month_name']);
        $this->assertSame('monday', $result['week_day']);
        $this->assertSame('12:00 AM', $result['time']);
        $this->assertSame('2083-01-07', $result['formatted']);
    }

    #[Test]
    public function it_respects_custom_format_tokens(): void
    {
        $converter = new NepaliDateConverter();

        $formatted = $converter->toString('2026-04-20 15:45:00', 'd-F-Y h:i A');

        $this->assertSame('07-Baishak-2083 03:45 PM', $formatted);
    }

    #[Test]
    public function it_supports_preferred_preset_names(): void
    {
        $converter = new NepaliDateConverter();

        $this->assertSame('monday 7, Baishak 2083', $converter->toString('2026-04-20 15:45:00', 'bs_label_full'));
        $this->assertSame('sombar 7, Baishak 2083', $converter->toString('2026-04-20 15:45:00', 'bs_label_full_nepday'));
        $this->assertSame("\u{0938}\u{094B}\u{092E}\u{092C}\u{093E}\u{0930} \u{096D}, \u{092C}\u{0948}\u{0936}\u{093E}\u{0916} \u{0968}\u{0966}\u{096E}\u{0969}", $converter->toString('2026-04-20 15:45:00', 'bs_label_devanagari'));
        $this->assertSame('7, Baishak 2083', $converter->toString('2026-04-20 15:45:00', 'bs_label_compact'));
        $this->assertSame('7, Baishak 2083 03:45 PM', $converter->toString('2026-04-20 15:45:00', 'bs_label_compact_time'));
        $this->assertSame('07-01-2083 03:45 PM', $converter->toString('2026-04-20 15:45:00', 'bs_datetime_numeric'));
    }

    #[Test]
    public function it_keeps_legacy_aliases_working(): void
    {
        $converter = new NepaliDateConverter();

        $this->assertSame('monday 7, Baishak 2083', $converter->toString('2026-04-20 15:45:00', 'bs_label'));
        $this->assertSame('sombar 7, Baishak 2083', $converter->toString('2026-04-20 15:45:00', 'bs_label_nepday'));
        $this->assertSame("\u{0938}\u{094B}\u{092E}\u{092C}\u{093E}\u{0930} \u{096D}, \u{092C}\u{0948}\u{0936}\u{093E}\u{0916} \u{0968}\u{0966}\u{096E}\u{0969}", $converter->toString('2026-04-20 15:45:00', 'nepalilang'));
        $this->assertSame('7, Baishak 2083', $converter->toString('2026-04-20 15:45:00', 'bs_label_simp'));
        $this->assertSame('7, Baishak 2083 03:45 PM', $converter->toString('2026-04-20 15:45:00', 'bs_label_time'));
        $this->assertSame('07-01-2083 03:45 PM', $converter->toString('2026-04-20 15:45:00', 'bs_time'));
    }

    #[Test]
    public function it_rejects_unsupported_lower_bound_dates(): void
    {
        $converter = new NepaliDateConverter();

        $this->expectException(UnsupportedDateRangeException::class);
        $converter->convert('1943-04-13');
    }
}
