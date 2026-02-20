<?php

declare(strict_types=1);

namespace Core\Mod\Hub\Controllers;

use Core\Bouncer\BlocklistService;
use Core\Headers\DetectLocation;
use Core\Mod\Hub\Models\HoneypotHit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Honeypot endpoint that returns 418 I'm a Teapot.
 *
 * This endpoint is listed as disallowed in robots.txt. Any request to it
 * indicates a crawler that doesn't respect robots.txt, which is often
 * malicious or at least poorly behaved.
 */
class TeapotController
{
    public function __invoke(Request $request): Response
    {
        // Log the hit
        $userAgent = $request->userAgent();
        $botName = HoneypotHit::detectBot($userAgent);
        $path = $request->path();
        $severity = HoneypotHit::severityForPath($path);
        $ip = $request->ip();

        // Rate limit honeypot logging to prevent DoS via log flooding.
        // Each IP gets limited to N log entries per time window.
        $rateLimitKey = 'honeypot:log:'.$ip;
        $maxAttempts = (int) config('core.bouncer.honeypot.rate_limit_max', 10);
        $decaySeconds = (int) config('core.bouncer.honeypot.rate_limit_window', 60);

        if (! RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            RateLimiter::hit($rateLimitKey, $decaySeconds);

            // Optional services - use app() since route skips web middleware
            $geoIp = app(DetectLocation::class);

            HoneypotHit::create([
                'ip_address' => $ip,
                'user_agent' => substr($userAgent ?? '', 0, 1000),
                'referer' => $this->sanitizeReferer($request->header('Referer', '')),
                'path' => $path,
                'method' => $request->method(),
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'country' => $geoIp?->getCountryCode($ip),
                'city' => $geoIp?->getCity($ip),
                'is_bot' => $botName !== null,
                'bot_name' => $botName,
                'severity' => $severity,
            ]);
        }

        // Auto-block critical hits (active probing) if enabled in config.
        // Skip localhost in dev to avoid blocking yourself.
        $autoBlockEnabled = config('core.bouncer.honeypot.auto_block_critical', true);
        $isLocalhost = $this->isPrivateIp($ip);
        $isCritical = $severity === HoneypotHit::getSeverityCritical();

        if ($autoBlockEnabled && $isCritical && ! $isLocalhost) {
            app(BlocklistService::class)->block($ip, 'honeypot_critical');
        }

        // Return the 418 I'm a teapot response
        return response($this->teapotBody(), 418, [
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Powered-By' => 'Earl Grey',
            'X-Severity' => $severity,
        ]);
    }

    /**
     * Validate and truncate the referer header.
     */
    protected function sanitizeReferer(string $referer): string
    {
        if ($referer === '') {
            return '';
        }

        if (filter_var($referer, FILTER_VALIDATE_URL) === false) {
            return 'invalid-url';
        }

        return substr($referer, 0, 2000);
    }

    /**
     * Remove sensitive headers and enforce size limits before storing.
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $allowed = [
            'user-agent',
            'accept',
            'accept-language',
            'accept-encoding',
            'referer',
            'origin',
            'x-requested-with',
            'x-forwarded-for',
            'x-real-ip',
            'cf-connecting-ip',
            'x-client-ip',
        ];

        $headers = array_intersect_key($headers, array_flip($allowed));

        // Enforce header count limit before passing to the model
        if (count($headers) > HoneypotHit::HEADERS_MAX_COUNT) {
            $headers = array_slice($headers, 0, HoneypotHit::HEADERS_MAX_COUNT, true);
        }

        return $headers;
    }

    /**
     * Check whether an IP address is private or reserved.
     */
    protected function isPrivateIp(string $ip): bool
    {
        // Normalise IPv4-mapped IPv6 addresses
        $ip = preg_replace('/^::ffff:/i', '', $ip);

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    /**
     * The teapot response body.
     */
    protected function teapotBody(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>418 I'm a Teapot</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 2rem;
        }
        .teapot {
            font-size: 8rem;
            margin-bottom: 1rem;
            animation: wobble 2s ease-in-out infinite;
        }
        @keyframes wobble {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        p {
            font-size: 1.25rem;
            opacity: 0.9;
            max-width: 500px;
        }
        .rfc {
            margin-top: 2rem;
            font-size: 0.875rem;
            opacity: 0.7;
        }
        a {
            color: inherit;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="teapot">🫖</div>
    <h1>418 I'm a Teapot</h1>
    <p>The server refuses to brew coffee because it is, permanently, a teapot.</p>
    <p class="rfc">
        <a href="https://www.rfc-editor.org/rfc/rfc2324" target="_blank" rel="noopener">RFC 2324</a> &middot;
        <a href="https://www.rfc-editor.org/rfc/rfc7168" target="_blank" rel="noopener">RFC 7168</a>
    </p>
</body>
</html>
HTML;
    }
}
