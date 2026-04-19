<?php

declare(strict_types=1);

namespace RakeshRai\LaravelNepaliDate\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use RakeshRai\LaravelNepaliDate\Tests\TestCase;

final class HelperAndBladeTest extends TestCase
{
    #[Test]
    public function helper_returns_bs_formatted_string(): void
    {
        $this->assertSame('2083-01-07', bs_date('2026-04-20'));
    }

    #[Test]
    public function blade_directive_renders_bs_date(): void
    {
        $rendered = \Illuminate\Support\Facades\Blade::render('@bs("2026-04-20")');

        $this->assertStringContainsString('2083-01-07', trim($rendered));
    }
}
