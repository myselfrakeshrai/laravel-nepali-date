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
    public function it_supports_preset_label_formats_with_time(): void
    {
        $converter = new NepaliDateConverter();

        $this->assertSame('monday 7, Baishak 2083', $converter->toString('2026-04-20 15:45:00', 'bs_label'));
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