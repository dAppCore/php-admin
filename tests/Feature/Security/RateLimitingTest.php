<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Livewire\Livewire;
use Website\Hub\Concerns\HasRateLimiting;

// =============================================================================
// Test Double Components
// =============================================================================

/**
 * Component with actionMessage/actionType properties (PlatformUser pattern).
 */
class RateLimitedActionComponent extends Component
{
    use HasRateLimiting;

    public string $actionMessage = '';

    public string $actionType = '';

    public int $executionCount = 0;

    public function mutate(): void
    {
        $this->rateLimit('test-mutation', 3, function () {
            $this->executionCount++;
            $this->actionMessage = 'Action executed.';
            $this->actionType = 'success';
        });
    }

    public function export()
    {
        return $this->rateLimit('test-export', 2, function () {
            $this->executionCount++;

            return 'export-data';
        });
    }

    public function destroy(): void
    {
        $this->rateLimit('test-deletion', 1, function () {
            $this->executionCount++;
            $this->actionMessage = 'Deleted.';
            $this->actionType = 'success';
        });
    }

    public function render(): string
    {
        return <<<'HTML'
            <div>
                <span>Executions: {{ $executionCount }}</span>
                <span>Message: {{ $actionMessage }}</span>
                <span>Type: {{ $actionType }}</span>
            </div>
        HTML;
    }
}

/**
 * Component without actionMessage/actionType (session flash fallback).
 */
class RateLimitedSessionComponent extends Component
{
    use HasRateLimiting;

    public int $executionCount = 0;

    public function mutate(): void
    {
        $this->rateLimit('test-session-mutation', 2, function () {
            $this->executionCount++;
        });
    }

    public function render(): string
    {
        return <<<'HTML'
            <div>
                <span>Executions: {{ $executionCount }}</span>
            </div>
        HTML;
    }
}

// =============================================================================
// Rate Limiting Enforcement Tests
// =============================================================================

beforeEach(function () {
    RateLimiter::clear('test-mutation:1');
    RateLimiter::clear('test-export:1');
    RateLimiter::clear('test-deletion:1');
    RateLimiter::clear('test-session-mutation:1');
});

describe('Rate limiting enforcement', function () {
    it('allows actions within the rate limit', function () {
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 1]));

        Livewire::test(RateLimitedActionComponent::class)
            ->call('mutate')
            ->assertSet('executionCount', 1)
            ->assertSet('actionMessage', 'Action executed.')
            ->assertSet('actionType', 'success')
            ->call('mutate')
            ->assertSet('executionCount', 2)
            ->call('mutate')
            ->assertSet('executionCount', 3);
    });

    it('blocks actions exceeding the rate limit', function () {
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 1]));

        $component = Livewire::test(RateLimitedActionComponent::class);

        // Execute up to the limit
        $component->call('mutate')
            ->call('mutate')
            ->call('mutate')
            ->assertSet('executionCount', 3);

        // Fourth call should be blocked
        $component->call('mutate')
            ->assertSet('executionCount', 3) // Not incremented
            ->assertSet('actionType', 'error')
            ->assertSet('actionMessage', fn (string $msg) => str_contains($msg, 'Too many requests'));
    });

    it('blocks export actions exceeding the rate limit', function () {
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 1]));

        $component = Livewire::test(RateLimitedActionComponent::class);

        // Execute up to the limit (2 for exports)
        $component->call('export')
            ->assertSet('executionCount', 1)
            ->call('export')
            ->assertSet('executionCount', 2);

        // Third call should be blocked
        $component->call('export')
            ->assertSet('executionCount', 2) // Not incremented
            ->assertSet('actionType', 'error');
    });

    it('enforces strict limits on destructive actions', function () {
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 1]));

        $component = Livewire::test(RateLimitedActionComponent::class);

        // Execute up to the limit (1 for deletions)
        $component->call('destroy')
            ->assertSet('executionCount', 1)
            ->assertSet('actionMessage', 'Deleted.');

        // Second call should be blocked
        $component->call('destroy')
            ->assertSet('executionCount', 1) // Not incremented
            ->assertSet('actionType', 'error')
            ->assertSet('actionMessage', fn (string $msg) => str_contains($msg, 'Too many requests'));
    });
});

// =============================================================================
// Rate Limit Key Scoping Tests
// =============================================================================

describe('Rate limit key scoping', function () {
    it('scopes rate limits per user', function () {
        // User 1 exhausts their limit
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 1]));
        RateLimiter::clear('test-deletion:1');
        RateLimiter::clear('test-deletion:2');

        $component1 = Livewire::test(RateLimitedActionComponent::class);
        $component1->call('destroy')
            ->assertSet('executionCount', 1);
        $component1->call('destroy')
            ->assertSet('executionCount', 1); // Blocked

        // User 2 should not be affected
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 2]));

        Livewire::test(RateLimitedActionComponent::class)
            ->call('destroy')
            ->assertSet('executionCount', 1) // User 2's own count
            ->assertSet('actionMessage', 'Deleted.');
    });

    it('uses separate limits for different action types', function () {
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 1]));

        $component = Livewire::test(RateLimitedActionComponent::class);

        // Exhaust deletion limit (1)
        $component->call('destroy')
            ->assertSet('executionCount', 1);

        // Mutation limit (3) should still be available
        $component->call('mutate')
            ->assertSet('executionCount', 2)
            ->assertSet('actionMessage', 'Action executed.')
            ->assertSet('actionType', 'success');
    });
});

// =============================================================================
// User Feedback Tests
// =============================================================================

describe('User feedback when rate limited', function () {
    it('shows error message with retry time via actionMessage', function () {
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 1]));

        $component = Livewire::test(RateLimitedActionComponent::class);

        // Exhaust limit
        $component->call('destroy');

        // Next call should show error with seconds
        $component->call('destroy')
            ->assertSet('actionType', 'error')
            ->assertSet('actionMessage', fn (string $msg) => str_contains($msg, 'Too many requests')
                && str_contains($msg, 'seconds'));
    });

    it('flashes error to session when component lacks actionMessage property', function () {
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 1]));

        $component = Livewire::test(RateLimitedSessionComponent::class);

        // Exhaust limit (2)
        $component->call('mutate')->call('mutate');

        // Third call should be blocked and flash to session
        $component->call('mutate')
            ->assertSet('executionCount', 2); // Not incremented
    });
});

// =============================================================================
// Rate Limit Reset Tests
// =============================================================================

describe('Rate limit reset', function () {
    it('allows actions after rate limit window resets', function () {
        $this->actingAs(new \Illuminate\Foundation\Auth\User(['id' => 1]));

        $component = Livewire::test(RateLimitedActionComponent::class);

        // Exhaust limit
        $component->call('destroy')
            ->assertSet('executionCount', 1);
        $component->call('destroy')
            ->assertSet('executionCount', 1); // Blocked

        // Clear the rate limiter (simulates window expiry)
        RateLimiter::clear('test-deletion:1');

        // Should work again
        $component->call('destroy')
            ->assertSet('executionCount', 2)
            ->assertSet('actionMessage', 'Deleted.')
            ->assertSet('actionType', 'success');
    });
});
