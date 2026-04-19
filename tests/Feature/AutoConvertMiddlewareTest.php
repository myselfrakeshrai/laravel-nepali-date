<?php

declare(strict_types=1);

namespace RakeshRai\LaravelNepaliDate\Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use RakeshRai\LaravelNepaliDate\Tests\TestCase;

final class AutoConvertMiddlewareTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('nepali-date.auto_convert_response_dates', true);
        $app['config']->set('nepali-date.auto_convert_format', 'Y-m-d');
        $app['config']->set('nepali-date.auto_register_web_middleware', true);
    }

    protected function defineRoutes($router): void
    {
        Route::middleware(['web', 'auto.bs.date'])->get('/sample', function () {
            return response('<div>Date: 2026-04-20</div>', 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        });
    }

    #[Test]
    public function middleware_replaces_ad_dates_in_html(): void
    {
        $response = $this->get('/sample');

        $response->assertOk();
        $response->assertSee('2083-01-07', false);
        $response->assertDontSee('2026-04-20', false);
    }
}
