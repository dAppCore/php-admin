<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

use Core\Bouncer\BlocklistService;
use Core\Headers\DetectLocation;
use Core\Mod\Hub\Controllers\TeapotController;
use Core\Mod\Hub\Models\HoneypotHit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Tests for the Teapot/Honeypot anti-spam system.
 *
 * The honeypot endpoint is designed to catch bots that ignore robots.txt.
 * Any request to disallowed paths indicates potentially malicious crawlers.
 */

beforeEach(function () {
    // Ensure honeypot_hits table exists for testing
    if (! \Illuminate\Support\Facades\Schema::hasTable('honeypot_hits')) {
        \Illuminate\Support\Facades\Schema::create('honeypot_hits', function ($table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('user_agent', 1000)->nullable();
            $table->string('referer', 2000)->nullable();
            $table->string('path', 255);
            $table->string('method', 10);
            $table->json('headers')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->boolean('is_bot')->default(false);
            $table->string('bot_name', 100)->nullable();
            $table->string('severity', 20)->default('warning');
            $table->timestamps();

            $table->index('ip_address');
            $table->index('created_at');
            $table->index('is_bot');
        });
    }

    // Clear rate limiter between tests
    RateLimiter::clear('honeypot:log:192.168.1.100');
    RateLimiter::clear('honeypot:log:203.0.113.50');
    RateLimiter::clear('honeypot:log:127.0.0.1');
});

afterEach(function () {
    // Clean up test data
    HoneypotHit::query()->delete();
    Mockery::close();
});

// =============================================================================
// HoneypotHit Model Tests
// =============================================================================

describe('HoneypotHit model', function () {
    describe('bot detection', function () {
        it('detects known SEO bots', function () {
            expect(HoneypotHit::detectBot('Mozilla/5.0 (compatible; AhrefsBot/7.0)'))
                ->toBe('Ahrefs');

            expect(HoneypotHit::detectBot('Mozilla/5.0 (compatible; SemrushBot/7~bl)'))
                ->toBe('Semrush');

            expect(HoneypotHit::detectBot('Mozilla/5.0 (compatible; MJ12bot/v1.4.8)'))
                ->toBe('Majestic');
        });

        it('detects AI crawler bots', function () {
            expect(HoneypotHit::detectBot('Mozilla/5.0 (compatible; GPTBot/1.0)'))
                ->toBe('OpenAI');

            expect(HoneypotHit::detectBot('Mozilla/5.0 (compatible; ClaudeBot/1.0)'))
                ->toBe('Anthropic');

            expect(HoneypotHit::detectBot('anthropic-ai/1.0'))
                ->toBe('Anthropic');
        });

        it('detects search engine bots', function () {
            expect(HoneypotHit::detectBot('Googlebot/2.1 (+http://www.google.com/bot.html)'))
                ->toBe('Google');

            expect(HoneypotHit::detectBot('Mozilla/5.0 (compatible; bingbot/2.0)'))
                ->toBe('Bing');

            expect(HoneypotHit::detectBot('Mozilla/5.0 (compatible; YandexBot/3.0)'))
                ->toBe('Yandex');
        });

        it('detects scripting tools', function () {
            expect(HoneypotHit::detectBot('curl/7.79.1'))
                ->toBe('cURL');

            expect(HoneypotHit::detectBot('python-requests/2.28.1'))
                ->toBe('Python');

            expect(HoneypotHit::detectBot('Go-http-client/1.1'))
                ->toBe('Go');

            expect(HoneypotHit::detectBot('wget/1.21'))
                ->toBe('Wget');

            expect(HoneypotHit::detectBot('Scrapy/2.6.1'))
                ->toBe('Scrapy');
        });

        it('detects headless browsers', function () {
            expect(HoneypotHit::detectBot('Mozilla/5.0 HeadlessChrome/90.0.4430.93'))
                ->toBe('HeadlessChrome');

            expect(HoneypotHit::detectBot('Mozilla/5.0 PhantomJS/2.1.1'))
                ->toBe('PhantomJS');
        });

        it('returns Unknown for empty user agent', function () {
            expect(HoneypotHit::detectBot(null))
                ->toBe('Unknown (no UA)');

            expect(HoneypotHit::detectBot(''))
                ->toBe('Unknown (no UA)');
        });

        it('returns null for legitimate browsers', function () {
            // Standard browser user agents should not be detected as bots
            expect(HoneypotHit::detectBot('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'))
                ->toBeNull();

            expect(HoneypotHit::detectBot('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15'))
                ->toBeNull();
        });
    });

    describe('severity classification', function () {
        it('classifies critical paths correctly', function () {
            expect(HoneypotHit::severityForPath('/admin'))
                ->toBe(HoneypotHit::getSeverityCritical());

            expect(HoneypotHit::severityForPath('/wp-admin'))
                ->toBe(HoneypotHit::getSeverityCritical());

            expect(HoneypotHit::severityForPath('/wp-login.php'))
                ->toBe(HoneypotHit::getSeverityCritical());

            expect(HoneypotHit::severityForPath('/administrator'))
                ->toBe(HoneypotHit::getSeverityCritical());

            expect(HoneypotHit::severityForPath('/phpmyadmin'))
                ->toBe(HoneypotHit::getSeverityCritical());

            expect(HoneypotHit::severityForPath('/.env'))
                ->toBe(HoneypotHit::getSeverityCritical());

            expect(HoneypotHit::severityForPath('/.git'))
                ->toBe(HoneypotHit::getSeverityCritical());
        });

        it('classifies warning paths correctly', function () {
            // Any path not in critical list should be warning
            expect(HoneypotHit::severityForPath('/teapot'))
                ->toBe(HoneypotHit::getSeverityWarning());

            expect(HoneypotHit::severityForPath('/honeypot'))
                ->toBe(HoneypotHit::getSeverityWarning());

            expect(HoneypotHit::severityForPath('/disallowed-path'))
                ->toBe(HoneypotHit::getSeverityWarning());
        });

        it('strips leading slash before matching', function () {
            // Both with and without leading slash should work
            expect(HoneypotHit::severityForPath('admin'))
                ->toBe(HoneypotHit::getSeverityCritical());

            expect(HoneypotHit::severityForPath('/admin'))
                ->toBe(HoneypotHit::getSeverityCritical());
        });

        it('matches partial paths as critical', function () {
            // Paths that start with critical paths should be critical
            expect(HoneypotHit::severityForPath('/admin/login'))
                ->toBe(HoneypotHit::getSeverityCritical());

            expect(HoneypotHit::severityForPath('/wp-admin/admin.php'))
                ->toBe(HoneypotHit::getSeverityCritical());
        });
    });

    describe('model scopes', function () {
        beforeEach(function () {
            // Create test data
            HoneypotHit::create([
                'ip_address' => '192.168.1.1',
                'path' => '/teapot',
                'method' => 'GET',
                'is_bot' => true,
                'bot_name' => 'TestBot',
                'severity' => 'warning',
                'created_at' => now()->subHours(2),
            ]);

            HoneypotHit::create([
                'ip_address' => '192.168.1.2',
                'path' => '/admin',
                'method' => 'GET',
                'is_bot' => false,
                'severity' => 'critical',
                'created_at' => now()->subHours(12),
            ]);

            HoneypotHit::create([
                'ip_address' => '192.168.1.1',
                'path' => '/wp-login.php',
                'method' => 'POST',
                'is_bot' => true,
                'bot_name' => 'TestBot',
                'severity' => 'critical',
                'created_at' => now()->subDays(2),
            ]);
        });

        it('filters recent hits', function () {
            expect(HoneypotHit::recent(24)->count())->toBe(2);
            expect(HoneypotHit::recent(6)->count())->toBe(1);
            expect(HoneypotHit::recent(48)->count())->toBe(3);
        });

        it('filters by IP address', function () {
            expect(HoneypotHit::fromIp('192.168.1.1')->count())->toBe(2);
            expect(HoneypotHit::fromIp('192.168.1.2')->count())->toBe(1);
            expect(HoneypotHit::fromIp('192.168.1.99')->count())->toBe(0);
        });

        it('filters bots only', function () {
            expect(HoneypotHit::bots()->count())->toBe(2);
        });

        it('filters critical severity', function () {
            expect(HoneypotHit::critical()->count())->toBe(2);
        });

        it('filters warning severity', function () {
            expect(HoneypotHit::warning()->count())->toBe(1);
        });

        it('chains scopes correctly', function () {
            expect(HoneypotHit::bots()->critical()->count())->toBe(1);
            expect(HoneypotHit::fromIp('192.168.1.1')->bots()->count())->toBe(2);
            expect(HoneypotHit::recent(24)->critical()->count())->toBe(1);
        });
    });

    describe('statistics', function () {
        beforeEach(function () {
            // Create varied test data
            HoneypotHit::create([
                'ip_address' => '10.0.0.1',
                'path' => '/teapot',
                'method' => 'GET',
                'is_bot' => true,
                'bot_name' => 'Ahrefs',
                'severity' => 'warning',
                'created_at' => now(),
            ]);

            HoneypotHit::create([
                'ip_address' => '10.0.0.1',
                'path' => '/admin',
                'method' => 'GET',
                'is_bot' => true,
                'bot_name' => 'Ahrefs',
                'severity' => 'critical',
                'created_at' => now()->subDays(3),
            ]);

            HoneypotHit::create([
                'ip_address' => '10.0.0.2',
                'path' => '/wp-admin',
                'method' => 'GET',
                'is_bot' => false,
                'severity' => 'critical',
                'created_at' => now()->subDays(10),
            ]);
        });

        it('calculates total count', function () {
            $stats = HoneypotHit::getStats();
            expect($stats['total'])->toBe(3);
        });

        it('calculates today count', function () {
            $stats = HoneypotHit::getStats();
            expect($stats['today'])->toBe(1);
        });

        it('calculates this week count', function () {
            $stats = HoneypotHit::getStats();
            expect($stats['this_week'])->toBe(2);
        });

        it('counts unique IPs', function () {
            $stats = HoneypotHit::getStats();
            expect($stats['unique_ips'])->toBe(2);
        });

        it('counts bot hits', function () {
            $stats = HoneypotHit::getStats();
            expect($stats['bots'])->toBe(2);
        });

        it('returns top IPs', function () {
            $stats = HoneypotHit::getStats();
            expect($stats['top_ips'])->toHaveCount(2);
            expect($stats['top_ips']->first()->ip_address)->toBe('10.0.0.1');
            expect($stats['top_ips']->first()->hits)->toBe(2);
        });

        it('returns top bots', function () {
            $stats = HoneypotHit::getStats();
            expect($stats['top_bots'])->toHaveCount(1);
            expect($stats['top_bots']->first()->bot_name)->toBe('Ahrefs');
            expect($stats['top_bots']->first()->hits)->toBe(2);
        });
    });
});

// =============================================================================
// TeapotController Tests
// =============================================================================

describe('TeapotController', function () {
    it('returns 418 I\'m a Teapot status code', function () {
        $controller = new TeapotController();

        // Create a mock request
        $request = Request::create('/teapot', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; TestBot/1.0)');

        // Mock DetectLocation
        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn('GB');
        $mockGeoIp->shouldReceive('getCity')->andReturn('London');
        app()->instance(DetectLocation::class, $mockGeoIp);

        // Mock BlocklistService to prevent actual blocking
        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $response = $controller($request);

        expect($response->getStatusCode())->toBe(418);
    });

    it('returns HTML content with teapot information', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $response = $controller($request);

        expect($response->headers->get('Content-Type'))->toBe('text/html; charset=utf-8');
        expect($response->getContent())->toContain('418 I\'m a Teapot');
        expect($response->getContent())->toContain('RFC 2324');
    });

    it('includes custom headers in response', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $response = $controller($request);

        expect($response->headers->get('X-Powered-By'))->toBe('Earl Grey');
        expect($response->headers->has('X-Severity'))->toBeTrue();
    });

    it('logs honeypot hit to database', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');
        $request->headers->set('User-Agent', 'curl/7.79.1');
        $request->headers->set('Referer', 'https://example.com');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn('US');
        $mockGeoIp->shouldReceive('getCity')->andReturn('New York');
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $controller($request);

        expect(HoneypotHit::count())->toBe(1);

        $hit = HoneypotHit::first();
        expect($hit->path)->toBe('teapot');
        expect($hit->method)->toBe('GET');
        expect($hit->user_agent)->toBe('curl/7.79.1');
        expect($hit->is_bot)->toBeTrue();
        expect($hit->bot_name)->toBe('cURL');
        expect($hit->country)->toBe('US');
        expect($hit->city)->toBe('New York');
    });

    it('detects and records bot information', function () {
        $controller = new TeapotController();

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        // Test with AhrefsBot
        $request = Request::create('/teapot', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; AhrefsBot/7.0)');
        $controller($request);

        $hit = HoneypotHit::first();
        expect($hit->is_bot)->toBeTrue();
        expect($hit->bot_name)->toBe('Ahrefs');
    });

    it('records warning severity for non-critical paths', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $controller($request);

        $hit = HoneypotHit::first();
        expect($hit->severity)->toBe('warning');
    });

    it('records critical severity for admin paths', function () {
        $controller = new TeapotController();
        $request = Request::create('/admin', 'GET');
        $request->server->set('REMOTE_ADDR', '203.0.113.50');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        // Critical path should trigger auto-block for non-private IPs
        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->once();
        app()->instance(BlocklistService::class, $mockBlocklist);

        $response = $controller($request);

        $hit = HoneypotHit::first();
        expect($hit->severity)->toBe('critical');
        expect($response->headers->get('X-Severity'))->toBe('critical');
    });

    it('uses whitelist to only store allowed headers', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');
        $request->headers->set('User-Agent', 'TestBot/1.0');
        $request->headers->set('Accept-Language', 'en-GB');
        $request->headers->set('Cookie', 'session=secret123');
        $request->headers->set('Authorization', 'Bearer token123');
        $request->headers->set('X-CSRF-Token', 'csrf123');
        $request->headers->set('X-Custom-Header', 'safe-value');
        $request->headers->set('X-Forwarded-For', '203.0.113.50');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $controller($request);

        $hit = HoneypotHit::first();
        $headers = $hit->headers;

        // Sensitive headers must not be stored
        expect($headers)->not->toHaveKey('cookie');
        expect($headers)->not->toHaveKey('authorization');
        expect($headers)->not->toHaveKey('x-csrf-token');

        // Non-whitelisted headers must not be stored
        expect($headers)->not->toHaveKey('x-custom-header');

        // Whitelisted headers should be preserved
        expect($headers)->toHaveKey('user-agent');
        expect($headers)->toHaveKey('accept-language');
        expect($headers)->toHaveKey('x-forwarded-for');
    });

    it('truncates long user agent strings', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');

        // Create a very long user agent (over 1000 chars)
        $longUserAgent = str_repeat('A', 1500);
        $request->headers->set('User-Agent', $longUserAgent);

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $controller($request);

        $hit = HoneypotHit::first();
        expect(strlen($hit->user_agent))->toBe(1000);
    });

    it('handles rate limiting to prevent log flooding', function () {
        $controller = new TeapotController();

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        // Set a low rate limit for testing
        config(['core.bouncer.honeypot.rate_limit_max' => 3]);
        config(['core.bouncer.honeypot.rate_limit_window' => 60]);

        // Make multiple requests from same IP
        for ($i = 0; $i < 5; $i++) {
            $request = Request::create('/teapot', 'GET');
            $request->server->set('REMOTE_ADDR', '192.168.1.100');
            $controller($request);
        }

        // Should only log up to the rate limit, not all 5
        expect(HoneypotHit::count())->toBeLessThanOrEqual(3);
    });
});

// =============================================================================
// Private IP Detection Tests
// =============================================================================

describe('Private IP detection', function () {
    it('detects IPv4 loopback as private', function () {
        $controller = new TeapotController();
        $reflection = new ReflectionMethod($controller, 'isPrivateIp');
        $reflection->setAccessible(true);

        expect($reflection->invoke($controller, '127.0.0.1'))->toBeTrue();
    });

    it('detects IPv6 loopback as private', function () {
        $controller = new TeapotController();
        $reflection = new ReflectionMethod($controller, 'isPrivateIp');
        $reflection->setAccessible(true);

        expect($reflection->invoke($controller, '::1'))->toBeTrue();
    });

    it('detects IPv4-mapped IPv6 loopback as private', function () {
        $controller = new TeapotController();
        $reflection = new ReflectionMethod($controller, 'isPrivateIp');
        $reflection->setAccessible(true);

        expect($reflection->invoke($controller, '::ffff:127.0.0.1'))->toBeTrue();
    });

    it('detects private network ranges as private', function () {
        $controller = new TeapotController();
        $reflection = new ReflectionMethod($controller, 'isPrivateIp');
        $reflection->setAccessible(true);

        expect($reflection->invoke($controller, '10.0.0.1'))->toBeTrue();
        expect($reflection->invoke($controller, '172.17.0.1'))->toBeTrue();
        expect($reflection->invoke($controller, '192.168.1.1'))->toBeTrue();
    });

    it('allows public IP addresses', function () {
        $controller = new TeapotController();
        $reflection = new ReflectionMethod($controller, 'isPrivateIp');
        $reflection->setAccessible(true);

        expect($reflection->invoke($controller, '8.8.8.8'))->toBeFalse();
        expect($reflection->invoke($controller, '203.0.113.50'))->toBeFalse();
    });

    it('skips auto-block for private IPs on critical paths', function () {
        $controller = new TeapotController();
        $request = Request::create('/admin', 'GET');
        $request->server->set('REMOTE_ADDR', '192.168.1.100');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldNotReceive('block');
        app()->instance(BlocklistService::class, $mockBlocklist);

        $response = $controller($request);

        expect($response->getStatusCode())->toBe(418);
    });
});

// =============================================================================
// Referer Validation Tests
// =============================================================================

describe('Referer validation', function () {
    it('stores valid URL referers', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');
        $request->headers->set('Referer', 'https://example.com/page');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $controller($request);

        $hit = HoneypotHit::first();
        expect($hit->referer)->toBe('https://example.com/page');
    });

    it('replaces invalid referers with marker', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');
        $request->headers->set('Referer', '<script>alert("xss")</script>');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $controller($request);

        $hit = HoneypotHit::first();
        expect($hit->referer)->toBe('invalid-url');
    });

    it('stores empty string for missing referer', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');
        // No Referer header set

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $controller($request);

        $hit = HoneypotHit::first();
        expect($hit->referer)->toBe('');
    });
});

// =============================================================================
// Integration Tests
// =============================================================================

describe('Honeypot integration', function () {
    it('creates hit record with all fields populated', function () {
        $controller = new TeapotController();
        $request = Request::create('/wp-admin/admin.php', 'POST');
        $request->server->set('REMOTE_ADDR', '203.0.113.50');
        $request->headers->set('User-Agent', 'python-requests/2.28.1');
        $request->headers->set('Referer', 'https://malicious-site.com/scanner');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn('RU');
        $mockGeoIp->shouldReceive('getCity')->andReturn('Moscow');
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->once();
        app()->instance(BlocklistService::class, $mockBlocklist);

        $response = $controller($request);

        expect($response->getStatusCode())->toBe(418);

        $hit = HoneypotHit::first();
        expect($hit)->not->toBeNull();
        expect($hit->path)->toBe('wp-admin/admin.php');
        expect($hit->method)->toBe('POST');
        expect($hit->user_agent)->toBe('python-requests/2.28.1');
        expect($hit->referer)->toBe('https://malicious-site.com/scanner');
        expect($hit->is_bot)->toBeTrue();
        expect($hit->bot_name)->toBe('Python');
        expect($hit->severity)->toBe('critical');
        expect($hit->country)->toBe('RU');
        expect($hit->city)->toBe('Moscow');
        expect($hit->headers)->toBeArray();
    });

    it('handles non-bot requests correctly', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36');

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn('GB');
        $mockGeoIp->shouldReceive('getCity')->andReturn('London');
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $controller($request);

        $hit = HoneypotHit::first();
        expect($hit->is_bot)->toBeFalse();
        expect($hit->bot_name)->toBeNull();
    });

    it('handles requests with missing optional fields', function () {
        $controller = new TeapotController();
        $request = Request::create('/teapot', 'GET');
        // No User-Agent, no Referer

        $mockGeoIp = Mockery::mock(DetectLocation::class);
        $mockGeoIp->shouldReceive('getCountryCode')->andReturn(null);
        $mockGeoIp->shouldReceive('getCity')->andReturn(null);
        app()->instance(DetectLocation::class, $mockGeoIp);

        $mockBlocklist = Mockery::mock(BlocklistService::class);
        $mockBlocklist->shouldReceive('block')->andReturn(null);
        app()->instance(BlocklistService::class, $mockBlocklist);

        $response = $controller($request);

        expect($response->getStatusCode())->toBe(418);

        $hit = HoneypotHit::first();
        expect($hit)->not->toBeNull();
        expect($hit->is_bot)->toBeTrue(); // Unknown bot for missing UA
        expect($hit->bot_name)->toBe('Unknown (no UA)');
    });
});
