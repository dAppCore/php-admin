<?php

declare(strict_types=1);

namespace Core\Mod\Hub\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Service extends Model
{
    protected $table = 'platform_services';

    protected $fillable = [
        'code',
        'module',
        'name',
        'tagline',
        'description',
        'icon',
        'color',
        'marketing_domain',
        'website_class',
        'marketing_url',
        'docs_url',
        'is_enabled',
        'is_public',
        'is_featured',
        'entitlement_code',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Scope: only enabled services.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope: only public services (visible in catalogue).
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: only featured services.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: order by sort_order, then name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope: services with a marketing domain configured.
     */
    public function scopeWithMarketingDomain(Builder $query): Builder
    {
        return $query->whereNotNull('marketing_domain')
            ->whereNotNull('website_class');
    }

    /**
     * Find a service by its code.
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }

    /**
     * Get domain → website_class mappings for enabled services.
     *
     * Used by DomainResolver for routing marketing domains.
     *
     * @return array<string, string> domain => website_class
     */
    public static function getDomainMappings(): array
    {
        return self::enabled()
            ->withMarketingDomain()
            ->pluck('website_class', 'marketing_domain')
            ->toArray();
    }

    /**
     * Get the marketing URL, falling back to marketing_domain if no override set.
     */
    public function getMarketingUrlAttribute(?string $value): ?string
    {
        if ($value) {
            return $value;
        }

        if ($this->marketing_domain) {
            $scheme = app()->environment('local') ? 'http' : 'https';

            return "{$scheme}://{$this->marketing_domain}";
        }

        return null;
    }

    /**
     * Maximum size in bytes for the serialised metadata JSON (64 KB).
     */
    public const METADATA_MAX_SIZE = 65_535;

    /**
     * Maximum number of top-level keys allowed in metadata.
     */
    public const METADATA_MAX_KEYS = 100;

    /**
     * Validate and set the metadata attribute.
     */
    public function setMetadataAttribute(mixed $value): void
    {
        if (is_null($value)) {
            $this->attributes['metadata'] = null;

            return;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Metadata must be valid JSON');
            }
            $value = $decoded;
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException('Metadata must be an array or null');
        }

        if (count($value) > self::METADATA_MAX_KEYS) {
            throw new InvalidArgumentException(
                'Metadata exceeds maximum of ' . self::METADATA_MAX_KEYS . ' keys'
            );
        }

        $json = json_encode($value);
        if (strlen($json) > self::METADATA_MAX_SIZE) {
            throw new InvalidArgumentException(
                'Metadata exceeds maximum size of ' . self::METADATA_MAX_SIZE . ' bytes'
            );
        }

        $this->attributes['metadata'] = $json;
    }

    /**
     * Check if a specific metadata key exists.
     */
    public function hasMeta(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    /**
     * Get a specific metadata value.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set a metadata value.
     *
     * Keys must be non-empty and contain only alphanumeric characters, underscores, and hyphens.
     */
    public function setMeta(string $key, mixed $value): void
    {
        if (empty($key) || ! preg_match('/^[a-zA-Z0-9_-]+$/', $key)) {
            throw new InvalidArgumentException(
                'Metadata key must be non-empty and contain only alphanumeric characters, underscores, and hyphens'
            );
        }

        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }
}
