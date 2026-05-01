<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Tenant\Models\Feature;
use Core\Tenant\Models\Package;
use Core\Tenant\Models\Workspace;
use Core\Tenant\Services\EntitlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Website\Hub\Boot;

#[Title('Site Settings')]
#[Layout('hub::admin.layouts.app')]
class SiteSettings extends Component
{
    public string $workspaceSlug = '';

    public string $tab = 'services';

    protected EntitlementService $entitlements;

    public function boot(EntitlementService $entitlements): void
    {
        $this->entitlements = $entitlements;
    }

    public function mount(string $workspace, ?string $tab = null): void
    {
        $this->workspaceSlug = $workspace;

        if ($tab && in_array($tab, ['services', 'general', 'deployment', 'environment', 'ssl', 'backups', 'danger'])) {
            $this->tab = $tab;
        }
    }

    /**
     * Get the current workspace by slug.
     */
    #[Computed]
    public function workspace(): ?Workspace
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user->workspaces()
            ->where('slug', $this->workspaceSlug)
            ->first();
    }

    /**
     * Available tabs for navigation.
     */
    #[Computed]
    public function tabs(): array
    {
        return [
            'services' => [
                'label' => 'Services',
                'icon' => 'puzzle-piece',
                'href' => Boot::hubRoute('hub.sites.settings', ['workspace' => $this->workspaceSlug, 'tab' => 'services']),
            ],
            'general' => [
                'label' => 'General',
                'icon' => 'gear',
                'href' => Boot::hubRoute('hub.sites.settings', ['workspace' => $this->workspaceSlug, 'tab' => 'general']),
            ],
            'deployment' => [
                'label' => 'Deployment',
                'icon' => 'rocket',
                'href' => Boot::hubRoute('hub.sites.settings', ['workspace' => $this->workspaceSlug, 'tab' => 'deployment']),
            ],
            'environment' => [
                'label' => 'Environment',
                'icon' => 'key',
                'href' => Boot::hubRoute('hub.sites.settings', ['workspace' => $this->workspaceSlug, 'tab' => 'environment']),
            ],
            'ssl' => [
                'label' => 'SSL & Security',
                'icon' => 'shield-check',
                'href' => Boot::hubRoute('hub.sites.settings', ['workspace' => $this->workspaceSlug, 'tab' => 'ssl']),
            ],
            'backups' => [
                'label' => 'Backups',
                'icon' => 'cloud-arrow-up',
                'href' => Boot::hubRoute('hub.sites.settings', ['workspace' => $this->workspaceSlug, 'tab' => 'backups']),
            ],
            'danger' => [
                'label' => 'Danger Zone',
                'icon' => 'triangle-exclamation',
                'href' => Boot::hubRoute('hub.sites.settings', ['workspace' => $this->workspaceSlug, 'tab' => 'danger']),
            ],
        ];
    }

    /**
     * Service definitions with entitlement checks.
     */
    #[Computed]
    public function serviceCards(): array
    {
        $workspace = $this->workspace;

        $services = [
            [
                'name' => 'Bio',
                'description' => 'Bio pages, short links & QR codes',
                'icon' => 'link',
                'color' => 'violet',
                'slug' => 'bio',
                'feature' => 'core.srv.bio',
                'adminRoute' => Boot::hubRoute('hub.services', ['service' => 'bio']),
                'features' => [
                    'Unlimited bio pages',
                    'Custom domains',
                    'Link analytics',
                    'QR code generation',
                ],
            ],
            [
                'name' => 'Social',
                'description' => 'Social media scheduling & management',
                'icon' => 'share-nodes',
                'color' => 'blue',
                'slug' => 'social',
                'feature' => 'core.srv.social',
                'adminRoute' => Boot::hubRoute('hub.services', ['service' => 'social']),
                'features' => [
                    'Multi-platform posting',
                    'Content calendar',
                    'Team approvals',
                    'Analytics & insights',
                ],
            ],
            [
                'name' => 'Analytics',
                'description' => 'Privacy-focused website analytics',
                'icon' => 'chart-line',
                'color' => 'cyan',
                'slug' => 'analytics',
                'feature' => 'core.srv.analytics',
                'adminRoute' => Boot::hubRoute('hub.services', ['service' => 'analytics']),
                'features' => [
                    'Real-time visitors',
                    'Goal tracking',
                    'Heatmaps',
                    'Session replays',
                ],
            ],
            [
                'name' => 'Trust',
                'description' => 'Social proof & conversion widgets',
                'icon' => 'shield-check',
                'color' => 'orange',
                'slug' => 'trust',
                'feature' => 'core.srv.trust',
                'adminRoute' => Boot::hubRoute('hub.services', ['service' => 'trust']),
                'features' => [
                    'Purchase notifications',
                    'Review widgets',
                    'Visitor counts',
                    'Custom campaigns',
                ],
            ],
            [
                'name' => 'Notify',
                'description' => 'Push notifications & campaigns',
                'icon' => 'bell',
                'color' => 'yellow',
                'slug' => 'notify',
                'feature' => 'core.srv.notify',
                'adminRoute' => Boot::hubRoute('hub.services', ['service' => 'notify']),
                'features' => [
                    'Browser push notifications',
                    'Subscriber management',
                    'Campaign scheduling',
                    'Delivery analytics',
                ],
            ],
            [
                'name' => 'Support',
                'description' => 'Help desk & live chat',
                'icon' => 'headset',
                'color' => 'teal',
                'slug' => 'support',
                'feature' => 'core.srv.support',
                'adminRoute' => Boot::hubRoute('hub.support.inbox'),
                'features' => [
                    'Email ticketing',
                    'Live chat widget',
                    'Knowledge base',
                    'Team collaboration',
                ],
            ],
        ];

        // Add entitlement status to each service
        return collect($services)->map(function ($service) use ($workspace) {
            $service['entitled'] = $workspace
                ? $this->entitlements->can($workspace, $service['feature'])->isAllowed()
                : false;

            return $service;
        })->all();
    }

    /**
     * Add a service to the workspace by provisioning its package.
     */
    public function addService(string $featureCode): void
    {
        $workspace = $this->workspace;

        if (! $workspace) {
            session()->flash('error', 'No workspace found.');

            return;
        }

        // Get service definition to get the name
        $serviceCard = collect($this->serviceCards)->firstWhere('feature', $featureCode);

        if (! $serviceCard) {
            session()->flash('error', 'Service not found.');

            return;
        }

        // Find or create the feature
        $feature = Feature::firstOrCreate(
            ['code' => $featureCode],
            [
                'name' => $serviceCard['name'].' Access',
                'description' => "Access to {$serviceCard['name']}",
                'category' => 'service',
                'type' => Feature::TYPE_BOOLEAN,
                'reset_type' => Feature::RESET_NONE,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Find or create a package for this specific service
        $packageCode = str_replace('.', '-', $featureCode).'-access';
        $package = Package::firstOrCreate(
            ['code' => $packageCode],
            [
                'name' => $feature->name,
                'description' => "Access to {$feature->name}",
                'is_stackable' => true,
                'is_base_package' => false,
                'is_active' => true,
                'is_public' => false,
                'sort_order' => 99,
            ]
        );

        // Attach feature to package if not already
        if (! $package->features()->where('feature_id', $feature->id)->exists()) {
            $package->features()->attach($feature->id, ['limit_value' => null]);
        }

        // Provision the package to the workspace
        $this->entitlements->provisionPackage($workspace, $packageCode, [
            'source' => 'user',
            'metadata' => ['added_via' => 'site_settings_page'],
        ]);

        // Clear caches
        Cache::flush();

        session()->flash('success', "{$feature->name} has been added to your site.");
    }

    /**
     * Switch to a different tab.
     */
    public function switchTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs)) {
            $this->tab = $tab;
        }
    }

    public function render(): View
    {
        return view('hub::admin.site-settings');
    }
}
