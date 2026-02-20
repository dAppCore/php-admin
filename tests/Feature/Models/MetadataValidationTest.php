<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

use Core\Mod\Hub\Models\HoneypotHit;
use Core\Mod\Hub\Models\Service;

/**
 * Tests for JSON metadata field validation on Service and HoneypotHit models.
 *
 * Ensures size limits, key count limits, and key format validation
 * are enforced to prevent mass assignment of arbitrary data.
 */

beforeEach(function () {
    if (! \Illuminate\Support\Facades\Schema::hasTable('platform_services')) {
        \Illuminate\Support\Facades\Schema::create('platform_services', function ($table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('module')->nullable();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('marketing_domain')->nullable();
            $table->string('website_class')->nullable();
            $table->string('marketing_url')->nullable();
            $table->string('docs_url')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('entitlement_code')->nullable();
            $table->integer('sort_order')->default(50);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

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
});

afterEach(function () {
    Service::query()->delete();
    HoneypotHit::query()->delete();
});

// =============================================================================
// Service Metadata Validation
// =============================================================================

describe('Service metadata validation', function () {
    describe('setMetadataAttribute mutator', function () {
        it('accepts valid metadata arrays', function () {
            $service = new Service();
            $service->metadata = ['key' => 'value', 'count' => 42];

            expect($service->getAttributes()['metadata'])->toBe('{"key":"value","count":42}');
        });

        it('accepts null metadata', function () {
            $service = new Service();
            $service->metadata = null;

            expect($service->getAttributes()['metadata'])->toBeNull();
        });

        it('accepts valid JSON strings', function () {
            $service = new Service();
            $service->metadata = '{"key":"value"}';

            expect($service->getAttributes()['metadata'])->toBe('{"key":"value"}');
        });

        it('rejects invalid JSON strings', function () {
            $service = new Service();

            expect(fn () => $service->metadata = '{invalid json}')
                ->toThrow(InvalidArgumentException::class, 'Metadata must be valid JSON');
        });

        it('rejects non-array non-string values', function () {
            $service = new Service();

            expect(fn () => $service->metadata = 12345)
                ->toThrow(InvalidArgumentException::class, 'Metadata must be an array or null');
        });

        it('rejects metadata exceeding maximum key count', function () {
            $service = new Service();
            $data = [];
            for ($i = 0; $i <= Service::METADATA_MAX_KEYS; $i++) {
                $data["key_{$i}"] = 'value';
            }

            expect(fn () => $service->metadata = $data)
                ->toThrow(InvalidArgumentException::class, 'Metadata exceeds maximum of');
        });

        it('accepts metadata at the maximum key count', function () {
            $service = new Service();
            $data = [];
            for ($i = 0; $i < Service::METADATA_MAX_KEYS; $i++) {
                $data["key_{$i}"] = 'v';
            }

            $service->metadata = $data;

            expect(json_decode($service->getAttributes()['metadata'], true))
                ->toHaveCount(Service::METADATA_MAX_KEYS);
        });

        it('rejects metadata exceeding maximum size', function () {
            $service = new Service();
            // Create a payload that exceeds 64KB
            $data = ['large' => str_repeat('x', Service::METADATA_MAX_SIZE)];

            expect(fn () => $service->metadata = $data)
                ->toThrow(InvalidArgumentException::class, 'Metadata exceeds maximum size');
        });

        it('persists valid metadata to database', function () {
            $service = Service::create([
                'code' => 'test-service',
                'name' => 'Test Service',
                'metadata' => ['version' => '1.0', 'features' => ['a', 'b']],
            ]);

            $fresh = Service::find($service->id);
            expect($fresh->metadata)->toBe(['version' => '1.0', 'features' => ['a', 'b']]);
        });
    });

    describe('setMeta key validation', function () {
        it('accepts valid alphanumeric keys', function () {
            $service = new Service();
            $service->metadata = [];

            $service->setMeta('valid_key', 'value');
            expect($service->metadata['valid_key'])->toBe('value');
        });

        it('accepts keys with hyphens', function () {
            $service = new Service();
            $service->metadata = [];

            $service->setMeta('my-key', 'value');
            expect($service->metadata['my-key'])->toBe('value');
        });

        it('rejects empty keys', function () {
            $service = new Service();
            $service->metadata = [];

            expect(fn () => $service->setMeta('', 'value'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('rejects keys with special characters', function () {
            $service = new Service();
            $service->metadata = [];

            expect(fn () => $service->setMeta('key.with.dots', 'value'))
                ->toThrow(InvalidArgumentException::class);

            expect(fn () => $service->setMeta('key with spaces', 'value'))
                ->toThrow(InvalidArgumentException::class);

            expect(fn () => $service->setMeta('key/path', 'value'))
                ->toThrow(InvalidArgumentException::class);
        });
    });
});

// =============================================================================
// HoneypotHit Headers Validation
// =============================================================================

describe('HoneypotHit headers validation', function () {
    describe('setHeadersAttribute mutator', function () {
        it('accepts valid header arrays', function () {
            $hit = new HoneypotHit();
            $hit->headers = ['host' => ['example.com'], 'accept' => ['text/html']];

            $decoded = json_decode($hit->getAttributes()['headers'], true);
            expect($decoded)->toHaveKey('host');
            expect($decoded)->toHaveKey('accept');
        });

        it('accepts null headers', function () {
            $hit = new HoneypotHit();
            $hit->headers = null;

            expect($hit->getAttributes()['headers'])->toBeNull();
        });

        it('truncates headers exceeding count limit', function () {
            $hit = new HoneypotHit();
            $headers = [];
            for ($i = 0; $i < HoneypotHit::HEADERS_MAX_COUNT + 20; $i++) {
                $headers["x-header-{$i}"] = ["value-{$i}"];
            }

            $hit->headers = $headers;

            $decoded = json_decode($hit->getAttributes()['headers'], true);
            expect(count($decoded))->toBeLessThanOrEqual(HoneypotHit::HEADERS_MAX_COUNT);
        });

        it('keeps headers at the exact limit', function () {
            $hit = new HoneypotHit();
            $headers = [];
            for ($i = 0; $i < HoneypotHit::HEADERS_MAX_COUNT; $i++) {
                $headers["h{$i}"] = ['v'];
            }

            $hit->headers = $headers;

            $decoded = json_decode($hit->getAttributes()['headers'], true);
            expect(count($decoded))->toBe(HoneypotHit::HEADERS_MAX_COUNT);
        });

        it('truncates headers exceeding size limit', function () {
            $hit = new HoneypotHit();
            // Create headers with large values that exceed 16KB
            $headers = [];
            for ($i = 0; $i < 10; $i++) {
                $headers["x-large-{$i}"] = [str_repeat('x', 2000)];
            }

            $hit->headers = $headers;

            $json = $hit->getAttributes()['headers'];
            expect(strlen($json))->toBeLessThanOrEqual(HoneypotHit::HEADERS_MAX_SIZE);
        });

        it('handles invalid JSON string gracefully', function () {
            $hit = new HoneypotHit();
            $hit->headers = '{not valid json}';

            expect($hit->getAttributes()['headers'])->toBeNull();
        });

        it('handles non-array non-string values gracefully', function () {
            $hit = new HoneypotHit();
            $hit->headers = 12345;

            expect($hit->getAttributes()['headers'])->toBeNull();
        });

        it('accepts valid JSON strings', function () {
            $hit = new HoneypotHit();
            $hit->headers = '{"host":["example.com"]}';

            $decoded = json_decode($hit->getAttributes()['headers'], true);
            expect($decoded)->toHaveKey('host');
        });

        it('persists valid headers to database', function () {
            $hit = HoneypotHit::create([
                'ip_address' => '192.168.1.1',
                'path' => '/teapot',
                'method' => 'GET',
                'headers' => ['host' => ['example.com'], 'accept' => ['*/*']],
                'severity' => 'warning',
            ]);

            $fresh = HoneypotHit::find($hit->id);
            expect($fresh->headers)->toBe(['host' => ['example.com'], 'accept' => ['*/*']]);
        });
    });
});
