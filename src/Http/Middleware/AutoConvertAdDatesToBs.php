<?php

declare(strict_types=1);

namespace RakeshRai\LaravelNepaliDate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RakeshRai\LaravelNepaliDate\Exceptions\UnsupportedDateRangeException;
use RakeshRai\LaravelNepaliDate\NepaliDateConverter;
use Symfony\Component\HttpFoundation\Response;

final class AutoConvertAdDatesToBs
{
    public function __construct(private readonly NepaliDateConverter $converter)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! config('nepali-date.auto_convert_response_dates', false)) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return $response;
        }

        $dateFormat = (string) config('nepali-date.auto_convert_format', 'Y-m-d');

        $updated = preg_replace_callback(
            '/\b(19[4-9][0-9]|20[0-9]{2})[-\/.](0?[1-9]|1[0-2])[-\/.](0?[1-9]|[12][0-9]|3[01])\b/',
            function (array $matches) use ($dateFormat): string {
                $normalized = sprintf('%04d-%02d-%02d', (int) $matches[1], (int) $matches[2], (int) $matches[3]);

                try {
                    return $this->converter->toString($normalized, $dateFormat);
                } catch (UnsupportedDateRangeException | \Throwable) {
                    return $matches[0];
                }
            },
            $content
        );

        if (is_string($updated)) {
            $response->setContent($updated);
        }

        return $response;
    }
}
