<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Tenant\Models\Feature;
use Livewire\Component;

class BoostPurchase extends Component
{
    /**
     * Available boost options from config.
     */
    public array $boostOptions = [];

    public function mount(): void
    {
        // Require authenticated user with a workspace
        if (! auth()->check()) {
            abort(403, 'Authentication required.');
        }

        // Get boost options from config
        $addonMapping = config('services.blesta.addon_mapping', []);

        $this->boostOptions = collect($addonMapping)->map(function ($config, $blestaId) {
            $feature = Feature::where('code', $config['feature_code'])->first();

            return [
                'blesta_id' => $blestaId,
                'feature_code' => $config['feature_code'],
                'feature_name' => $feature?->name ?? $config['feature_code'],
                'boost_type' => $config['boost_type'],
                'limit_value' => $config['limit_value'] ?? null,
                'duration_type' => $config['duration_type'],
                'description' => $this->getBoostDescription($config),
            ];
        })->values()->toArray();
    }

    protected function getBoostDescription(array $config): string
    {
        $type = $config['boost_type'];
        $value = $config['limit_value'] ?? null;
        $duration = $config['duration_type'];

        $description = match ($type) {
            'add_limit' => "+{$value} additional",
            'unlimited' => 'Unlimited access',
            'enable' => 'Feature enabled',
            default => 'Boost',
        };

        $durationText = match ($duration) {
            'cycle_bound' => 'until billing cycle ends',
            'duration' => 'for limited time',
            'permanent' => 'permanently',
            default => '',
        };

        return trim("{$description} {$durationText}");
    }

    public function purchaseBoost(string $blestaId): void
    {
        // Redirect to Blesta for purchase
        // TODO: Implement when Blesta is configured
        $blestaUrl = config('services.blesta.url', 'https://billing.host.uk.com');

        $this->redirect("{$blestaUrl}/order/addon/{$blestaId}");
    }

    public function render()
    {
        return view('hub::admin.boost-purchase')
            ->layout('hub::admin.layouts.app', ['title' => 'Purchase Boost']);
    }
}
