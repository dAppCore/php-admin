<?php

declare(strict_types=1);

namespace Website\Hub\Concerns;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Provides rate limiting helpers for Livewire admin components.
 *
 * Usage:
 *   $this->rateLimit('tier-change', 10, function () { ... });
 *   $this->rateLimit('waitlist-export', 5, function () { ... });
 */
trait HasRateLimiting
{
    /**
     * Execute a callback with rate limiting for mutation actions.
     *
     * @param  string  $action  Short action identifier (e.g. 'tier-change')
     * @param  int  $maxAttempts  Maximum attempts per minute
     * @param  callable  $callback  The action to execute if within limits
     * @param  int  $decaySeconds  Rate limit window in seconds
     */
    protected function rateLimit(string $action, int $maxAttempts, callable $callback, int $decaySeconds = 60): mixed
    {
        $key = $this->rateLimitKey($action);

        $executed = false;
        $result = null;

        RateLimiter::attempt(
            $key,
            $maxAttempts,
            function () use ($callback, &$executed, &$result) {
                $executed = true;
                $result = $callback();
            },
            $decaySeconds,
        );

        if (! $executed) {
            $this->onRateLimited($action, $key);
        }

        return $result;
    }

    /**
     * Build a rate limit key scoped to the authenticated user.
     */
    protected function rateLimitKey(string $action): string
    {
        return $action.':'.auth()->id();
    }

    /**
     * Handle rate limit exceeded - sets action message on the component.
     */
    protected function onRateLimited(string $action, string $key): void
    {
        $seconds = RateLimiter::availableIn($key);

        if (property_exists($this, 'actionMessage') && property_exists($this, 'actionType')) {
            $this->actionMessage = "Too many requests. Please wait {$seconds} seconds before trying again.";
            $this->actionType = 'error';
        } else {
            session()->flash('error', "Too many requests. Please wait {$seconds} seconds before trying again.");
        }
    }
}
